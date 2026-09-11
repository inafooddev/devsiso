<?php

namespace App\Livewire\CallPlan\JksApprovals;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $filterStatus = 'PENDING';
    public $search = '';
    public $filterType = '';
    
    public $selected = [];
    public $selectAll = false;
    
    public $rejectReason = '';
    public $actionIdToReject = null;
    public $isBulkReject = false;

    // PRC Input State
    public $showPrcModal = false;
    public $prcApprovalId = null;
    public $prcCustomerCode = '';
    public $prcCustomerName = '';
    public $prcCustomerEska = '';

    public function mount()
    {
        // if (!auth()->user()->hasRole('user')) {
        //     abort(403, 'Akses Ditolak. Hanya Bagian Data yang bisa mengakses halaman ini.');
        // }
    }

    public function updatingSearch() {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingFilterType() {
        $this->resetPage();
        $this->resetSelection();
    }
    
    public function updatingFilterStatus() {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->getApprovalsQuery()->pluck('ja.id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    private function resetSelection()
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function getApprovalsQuery()
    {
        $query = DB::table('jks_approvals as ja')
            ->join('users as u', 'ja.maker_id', '=', 'u.id')
            ->leftJoin('users as c', 'ja.checker_id', '=', 'c.id')
            ->select('ja.*', 'u.name as maker_name', 'c.name as checker_name')
            ->where('ja.status', $this->filterStatus);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('ja.reason', 'ilike', '%' . $this->search . '%')
                  ->orWhere('u.name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('c.name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ja.distributor_code', 'ilike', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filterType)) {
            $query->where('ja.action_type', $this->filterType);
        }

        return $query;
    }

    private function executeApprovalLogic($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $approval = DB::table('jks_approvals')->where('id', $id)->lockForUpdate()->first();
                if (!$approval || $approval->status !== 'PENDING') return false;

                $payload = json_decode($approval->payload, true);
                
                switch ($approval->action_type) {
                    case 'EDIT_INDIVIDU':
                    case 'DELETE_INDIVIDU':
                        DB::table('jks_salesmans')
                            ->where('id', $payload['id'])
                            ->update(array_merge($payload['update'], ['updated_at' => now()]));
                        break;

                    case 'DELETE_MASSAL':
                        DB::table('jks_salesmans')
                            ->whereIn('id', $payload['ids'])
                            ->where('distributor_code', $approval->distributor_code)
                            ->update(array_merge($payload['update'], ['updated_at' => now()]));
                        break;

                    case 'TUKAR_SALESMAN':
                        $asalIds = DB::table('jks_salesmans')
                            ->where('salesman_code', $payload['salesman_asal'])
                            ->where('distributor_code', $approval->distributor_code)
                            ->pluck('id');

                        $tujuanIds = DB::table('jks_salesmans')
                            ->where('salesman_code', $payload['salesman_tujuan'])
                            ->where('distributor_code', $approval->distributor_code)
                            ->pluck('id');

                        if ($asalIds->isNotEmpty()) {
                            DB::table('jks_salesmans')
                                ->whereIn('id', $asalIds)
                                ->update(['salesman_code' => $payload['salesman_tujuan'], 'updated_at' => now()]);
                        }
                        if ($tujuanIds->isNotEmpty()) {
                            DB::table('jks_salesmans')
                                ->whereIn('id', $tujuanIds)
                                ->update(['salesman_code' => $payload['salesman_asal'], 'updated_at' => now()]);
                        }
                        break;

                    case 'TUKAR_MINGGU':
                        $rows = DB::table('jks_salesmans')
                            ->where('salesman_code', $payload['salesman_code'])
                            ->where('distributor_code', $approval->distributor_code)
                            ->where(function($query) use ($payload) {
                                $query->where($payload['minggu_asal'], 'Y')
                                      ->orWhere($payload['minggu_tujuan'], 'Y');
                            })
                            ->get();

                        foreach ($rows as $row) {
                            $newAsalVal = $row->{$payload['minggu_tujuan']};
                            $newTujuanVal = $row->{$payload['minggu_asal']};

                            DB::table('jks_salesmans')
                                ->where('id', $row->id)
                                ->update([
                                    $payload['minggu_asal'] => $newAsalVal,
                                    $payload['minggu_tujuan'] => $newTujuanVal,
                                    'updated_at' => now(),
                                ]);
                        }
                        break;

                    case 'TUKAR_HARI':
                        $rows = DB::table('jks_salesmans')
                            ->where('salesman_code', $payload['salesman_code'])
                            ->where('distributor_code', $approval->distributor_code)
                            ->where(function($query) use ($payload) {
                                foreach ($payload['minggu'] as $week) {
                                    $query->where($week, 'Y');
                                }
                            })
                            ->where(function($query) use ($payload) {
                                $query->where($payload['hari_asal'], 'Y')
                                      ->orWhere($payload['hari_tujuan'], 'Y');
                            })
                            ->get();

                        foreach ($rows as $row) {
                            $unselectedWeeks = array_diff(['w1', 'w2', 'w3', 'w4'], $payload['minggu']);
                            $hasUnselected = false;
                            foreach ($unselectedWeeks as $uw) {
                                if ($row->$uw === 'Y') {
                                    $hasUnselected = true;
                                    break;
                                }
                            }

                            if ($hasUnselected) {
                                $updateOriginal = ['updated_at' => now()];
                                foreach ($payload['minggu'] as $sw) {
                                    $updateOriginal[$sw] = 'T';
                                }
                                DB::table('jks_salesmans')
                                    ->where('id', $row->id)
                                    ->update($updateOriginal);

                                $newRow = (array) $row;
                                unset($newRow['id']);
                                $newRow['created_at'] = now();
                                $newRow['updated_at'] = now();
                                
                                foreach ($unselectedWeeks as $uw) {
                                    $newRow[$uw] = 'T';
                                }
                                
                                $temp = $newRow[$payload['hari_asal']];
                                $newRow[$payload['hari_asal']] = $newRow[$payload['hari_tujuan']];
                                $newRow[$payload['hari_tujuan']] = $temp;
                                
                                DB::table('jks_salesmans')->insert($newRow);
                            } else {
                                DB::table('jks_salesmans')
                                    ->where('id', $row->id)
                                    ->update([
                                        $payload['hari_asal'] => $row->{$payload['hari_tujuan']},
                                        $payload['hari_tujuan'] => $row->{$payload['hari_asal']},
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                        break;
                        
                    case 'TAMBAH_JADWAL':
                        $now = now();
                        $insertData = [];
                        foreach ($payload['tokos'] as $toko) {
                            $customerCode = $toko['code'];

                            // 1. Get OOL data for Pareto Injection
                            $oolData = DB::table('jks_se_master_toko_ool')->where('customer_code', $customerCode)->first();
                            
                            if ($oolData) {
                                // Calculate Pilar
                                $avg = floatval($oolData->avg_value_net);
                                $pilar = null;
                                if ($avg >= 3000000) {
                                    $pilar = '2. PNR';
                                } elseif ($avg >= 1500000) {
                                    $pilar = '3. NGVO';
                                } else {
                                    $pilar = '4. GRO';
                                }

                                // Check if Pareto exists
                                $paretoExists = DB::table('list_toko_pareto_team_elite')
                                    ->where('distributor_code', $payload['distributor_code'])
                                    ->where('uniq_kd', $customerCode)
                                    ->exists();
                                    
                                if (!$paretoExists) {
                                    DB::table('list_toko_pareto_team_elite')->insert([
                                        'distributor_code' => $payload['distributor_code'],
                                        'uniq_kd' => $customerCode,
                                        'customer_code_prc' => $oolData->customer_eska,
                                        'customer_name' => $oolData->customer_name,
                                        'customer_address' => $oolData->alamat,
                                        'target' => $avg,
                                        'pilar' => $pilar,
                                        'kabupaten' => null,
                                        'desa' => null,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ]);
                                }
                            }

                            // 2. Prepare JKS Schedule
                            $row = [
                                'bulan' => $payload['bulan'],
                                'distributor_code' => $payload['distributor_code'],
                                'salesman_code' => $payload['salesman_code'],
                                'customer_code' => $toko['code'],
                                'h1' => 'T', 'h2' => 'T', 'h3' => 'T', 'h4' => 'T', 'h5' => 'T', 'h6' => 'T', 'h7' => 'T',
                                'w1' => 'T', 'w2' => 'T', 'w3' => 'T', 'w4' => 'T',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            foreach ($payload['hari'] ?? [] as $h) {
                                $row[$h] = 'Y';
                            }
                            foreach ($payload['minggu'] ?? [] as $w) {
                                $row[$w] = 'Y';
                            }
                            $insertData[] = $row;
                        }
                        if (!empty($insertData)) {
                            DB::table('jks_salesmans')->insert($insertData);
                        }
                        break;
                }

                DB::table('jks_approvals')->where('id', $id)->update([
                    'status' => 'APPROVED',
                    'checker_id' => auth()->id(),
                    'updated_at' => now()
                ]);

                return true;
            });
        } catch (\Exception $e) {
            // Log the error if necessary: \Log::error("Approval failed for ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    public function approve($id)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('user')) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan.']);
            return;
        }

        $approval = DB::table('jks_approvals')->where('id', $id)->first();
        if ($approval && $approval->action_type === 'TAMBAH_JADWAL') {
            $payload = json_decode($approval->payload, true);
            // Check for missing customer_eska on the first toko (assuming single request for now)
            if (!empty($payload['tokos'])) {
                $toko = $payload['tokos'][0];
                $oolToko = DB::table('jks_se_master_toko_ool')->where('customer_code', $toko['code'])->first();
                if ($oolToko && empty($oolToko->customer_eska)) {
                    $this->prcApprovalId = $id;
                    $this->prcCustomerCode = $toko['code'];
                    $this->prcCustomerName = $toko['name'];
                    $this->prcCustomerEska = '';
                    $this->showPrcModal = true;
                    return; // Pause approval to show modal
                }
            }
        }

        $success = $this->executeApprovalLogic($id);
        
        if ($success) {
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan berhasil disetujui & dieksekusi!']);
        } else {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pengajuan! Pastikan data masih valid.']);
        }
    }

    public function submitPrcAndApprove()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('user')) return;

        $this->validate([
            'prcCustomerEska' => 'required|min:3'
        ], [
            'prcCustomerEska.required' => 'Customer Kode PRC wajib diisi.',
            'prcCustomerEska.min' => 'Kode PRC minimal 3 karakter.'
        ]);

        // Update master OOL
        DB::table('jks_se_master_toko_ool')
            ->where('customer_code', $this->prcCustomerCode)
            ->update(['customer_eska' => $this->prcCustomerEska]);
            
        $this->showPrcModal = false;
        
        // Proceed with approval
        $success = $this->executeApprovalLogic($this->prcApprovalId);
        
        if ($success) {
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Kode PRC disimpan & Pengajuan disetujui!']);
        } else {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pengajuan!']);
        }
    }

    public function bulkApprove()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('user')) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan.']);
            return;
        }

        if (empty($this->selected)) return;

        $successCount = 0;
        $failCount = 0;
        
        foreach ($this->selected as $id) {
            if ($this->executeApprovalLogic($id)) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $this->resetSelection();
        
        if ($failCount > 0) {
            if ($successCount > 0) {
                $this->dispatch('toast', ['type' => 'warning', 'message' => "{$successCount} disetujui, {$failCount} gagal dieksekusi."]);
            } else {
                $this->dispatch('toast', ['type' => 'error', 'message' => "Gagal mengeksekusi {$failCount} pengajuan."]);
            }
        } else {
            $this->dispatch('toast', ['type' => 'success', 'message' => "{$successCount} pengajuan berhasil disetujui & dieksekusi!"]);
        }
    }

    public function confirmReject($id)
    {
        $this->actionIdToReject = $id;
        $this->isBulkReject = false;
        $this->rejectReason = '';
        $this->dispatch('open-reject-modal');
    }

    public function confirmBulkReject()
    {
        if (empty($this->selected)) return;
        
        $this->actionIdToReject = null;
        $this->isBulkReject = true;
        $this->rejectReason = '';
        $this->dispatch('open-reject-modal');
    }

    public function executeReject()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('user')) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menolak pengajuan.']);
            return;
        }

        $this->validate([
            'rejectReason' => 'required|min:5'
        ], [
            'rejectReason.required' => 'Alasan penolakan wajib diisi.',
            'rejectReason.min' => 'Alasan penolakan minimal 5 karakter.'
        ]);

        if ($this->isBulkReject) {
            DB::table('jks_approvals')
                ->whereIn('id', $this->selected)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'REJECTED',
                    'checker_id' => auth()->id(),
                    'reject_reason' => $this->rejectReason,
                    'updated_at' => now()
                ]);
            $count = count($this->selected);
            $this->resetSelection();
            $this->dispatch('toast', ['type' => 'success', 'message' => "{$count} pengajuan berhasil ditolak!"]);
        } else {
            DB::table('jks_approvals')->where('id', $this->actionIdToReject)->update([
                'status' => 'REJECTED',
                'checker_id' => auth()->id(),
                'reject_reason' => $this->rejectReason,
                'updated_at' => now()
            ]);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan ditolak!']);
        }

        $this->dispatch('close-reject-modal');
    }

    public function render()
    {
        $approvals = $this->getApprovalsQuery()
            ->orderBy('ja.created_at', 'desc')
            ->paginate(100);

        // KPI Counts
        $kpi = [
            'PENDING' => DB::table('jks_approvals')->where('status', 'PENDING')->count(),
            'APPROVED' => DB::table('jks_approvals')->where('status', 'APPROVED')->count(),
            'REJECTED' => DB::table('jks_approvals')->where('status', 'REJECTED')->count(),
        ];

        $actionTypes = DB::table('jks_approvals')->select('action_type')->distinct()->pluck('action_type');

        return view('livewire.call-plan.jks-approvals.index', [
            'approvals' => $approvals,
            'kpi' => $kpi,
            'actionTypes' => $actionTypes
        ])->layout('layouts.app')->title('Pusat Persetujuan JKS');
    }
}
