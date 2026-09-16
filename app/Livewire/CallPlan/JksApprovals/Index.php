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
    public $filterDistributor = '';
    
    public $selected = [];
    public $selectAll = false;
    
    public $rejectReason = '';
    public $actionIdToReject = null;
    public $isBulkReject = false;

    public $prcApprovalId = null;
    public $missingPrcTokos = []; // Array of missing tokos

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

    public function updatingFilterDistributor() {
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
            $query = $this->getApprovalsQuery();
            $allIds = (clone $query)->pluck('ja.id')->map(fn($id) => (string) $id)->toArray();
            
            $tambahJadwalApprovals = (clone $query)->where('action_type', 'TAMBAH_JADWAL')->get();
            $prcIssues = $this->calculatePrcIssues($tambahJadwalApprovals);
            $prcIssuesIds = array_map('strval', array_keys($prcIssues));

            $this->selected = array_values(array_diff($allIds, $prcIssuesIds));
        } else {
            $this->selected = [];
        }
    }

    private function resetSelection()
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    private function calculatePrcIssues($tambahJadwalApprovals)
    {
        $prcIssues = [];
        $allTokoCodes = [];
        
        foreach ($tambahJadwalApprovals as $approval) {
            $payload = json_decode($approval->payload, true);
            if (!empty($payload['tokos'])) {
                foreach ($payload['tokos'] as $toko) {
                    $allTokoCodes[] = $toko['code'];
                }
            }
        }

        if (empty($allTokoCodes)) {
            return [];
        }

        $allTokoCodes = array_unique($allTokoCodes);

        $oolTokos = DB::table('jks_se_master_toko_ool')
            ->whereIn('customer_code', $allTokoCodes)
            ->get()
            ->keyBy('customer_code');
            
        $paretoTokos = DB::table('list_toko_pareto_team_elite')
            ->whereIn('uniq_kd', $allTokoCodes)
            ->get(['uniq_kd', 'distributor_code'])
            ->groupBy('distributor_code');

        foreach ($tambahJadwalApprovals as $approval) {
            $payload = json_decode($approval->payload, true);
            if (!empty($payload['tokos'])) {
                foreach ($payload['tokos'] as $toko) {
                    $distCode = $approval->distributor_code;
                    $tokoCode = $toko['code'];

                    $alreadyInPareto = false;
                    if (isset($paretoTokos[$distCode])) {
                        $alreadyInPareto = $paretoTokos[$distCode]->where('uniq_kd', $tokoCode)->isNotEmpty();
                    }

                    if ($alreadyInPareto) {
                        continue;
                    }

                    $oolData = $oolTokos->get($tokoCode);
                    if ($oolData) {
                        if (empty($oolData->customer_eska)) {
                            $prcIssues[$approval->id] = true;
                            break;
                        } else {
                            $existsInPareto = DB::table('list_toko_pareto_team_elite')
                                ->where('customer_code_prc', $oolData->customer_eska)
                                ->where('distributor_code', $distCode)
                                ->where('uniq_kd', '!=', $tokoCode)
                                ->exists();
                            if ($existsInPareto) {
                                $prcIssues[$approval->id] = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        return $prcIssues;
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

        if (!empty($this->filterDistributor)) {
            $query->where('ja.distributor_code', $this->filterDistributor);
        }

        return $query;
    }

    private function executeApprovalLogic($id, $newPrcs = [])
    {
        try {
            return DB::transaction(function () use ($id, $newPrcs) {
                $approval = DB::table('jks_approvals')->where('id', $id)->lockForUpdate()->first();
                if (!$approval) return 'not_found';
                if ($approval->status !== 'PENDING') return 'already_processed';

                $payload = json_decode($approval->payload, true);
                $status = 'success';
                
                switch ($approval->action_type) {
                    case 'EDIT_INDIVIDU':
                        DB::table('jks_salesmans')
                            ->where('id', $payload['id'])
                            ->update(array_merge($payload['update'], ['updated_at' => now()]));
                        break;
                    case 'DELETE_INDIVIDU':
                        if (($approval->delete_type ?? 'SOFT') === 'HARD') {
                            DB::table('jks_salesmans')->where('id', $payload['id'])->delete();
                        } else {
                            DB::table('jks_salesmans')
                                ->where('id', $payload['id'])
                                ->update(array_merge($payload['update'], ['updated_at' => now()]));
                        }
                        break;

                    case 'DELETE_MASSAL':
                        if (($approval->delete_type ?? 'SOFT') === 'HARD') {
                            DB::table('jks_salesmans')
                                ->whereIn('id', $payload['ids'])
                                ->where('distributor_code', $approval->distributor_code)
                                ->delete();
                        } else {
                            DB::table('jks_salesmans')
                                ->whereIn('id', $payload['ids'])
                                ->where('distributor_code', $approval->distributor_code)
                                ->update(array_merge($payload['update'], ['updated_at' => now()]));
                        }
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

                        $partialSwap = $asalIds->isEmpty() || $tujuanIds->isEmpty();

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
                        
                        if ($partialSwap) {
                            $status = 'partial_success';
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
                                    $query->orWhere($week, 'Y');
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

                        // 0. Update PRCs in OOL safely inside this transaction
                        foreach ($newPrcs as $custCode => $eskaCode) {
                            DB::table('jks_se_master_toko_ool')
                                ->where('customer_code', $custCode)
                                ->update(['customer_eska' => $eskaCode]);
                        }

                        foreach ($payload['tokos'] as $toko) {
                            $customerCode = $toko['code'];

                            // 1. Get OOL data for Pareto Injection
                            $oolData = DB::table('jks_se_master_toko_ool')->where('customer_code', $customerCode)->first();
                            
                            if ($oolData) {
                                // Calculate Pilar (ensure avg_value_net is not null, fallback to 0)
                                $avg = floatval($oolData->avg_value_net ?? 0);
                                $pilar = null;
                                if ($avg >= 3000000) {
                                    $pilar = '2. PNR';
                                } elseif ($avg >= 1500000) {
                                    $pilar = '3. NGVO';
                                } else {
                                    $pilar = '4. GRO';
                                }

                                // Determine final target based on rules
                                $finalTarget = $avg;
                                if ($finalTarget < 300000) {
                                    $finalTarget = 300000;
                                } else {
                                    $finalTarget = ceil($finalTarget / 1000) * 1000;
                                }

                                // Check if Pareto exists
                                $paretoExists = DB::table('list_toko_pareto_team_elite')
                                    ->where('distributor_code', $payload['distributor_code'])
                                    ->where('uniq_kd', $customerCode)
                                    ->exists();
                                    
                                if (!$paretoExists) {
                                    // Safeguard: Check if customer_code_prc is already used by another toko in the same distributor
                                    $prcExists = DB::table('list_toko_pareto_team_elite')
                                        ->where('customer_code_prc', $oolData->customer_eska)
                                        ->where('distributor_code', $payload['distributor_code'])
                                        ->exists();
                                        
                                    if ($prcExists) {
                                        DB::rollBack();
                                        return 'duplicate_prc';
                                    }

                                    DB::table('list_toko_pareto_team_elite')->insert([
                                        'distributor_code' => $payload['distributor_code'],
                                        'uniq_kd' => $customerCode,
                                        'customer_code_prc' => $oolData->customer_eska,
                                        'customer_name' => $oolData->customer_name,
                                        'customer_address' => $oolData->alamat,
                                        'target' => $finalTarget,
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

                return $status;
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('JKS Approval Execution Failed', [
                'approval_id' => $id,
                'checker_id'  => auth()->id(),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            return 'error';
        }
    }

    public function approve($id)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('user')) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan.']);
            return;
        }

        $approval = DB::table('jks_approvals')->where('id', $id)->first();
        
        if (!$approval) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan tidak ditemukan.']);
            return;
        }

        // IDOR Guard: pastikan admin hanya bisa approve distributor yang menjadi haknya
        // Jika user punya distributor_code di profil, validasi; jika superadmin biarkan lewat
        $userDistributorCode = auth()->user()->distributor_code ?? null;
        if ($userDistributorCode && $approval->distributor_code !== $userDistributorCode) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk pengajuan distributor ini.']);
            return;
        }

        if ($approval->action_type === 'TAMBAH_JADWAL') {
            $payload = json_decode($approval->payload, true);
            $missingTokos = [];
            
            // Check for missing customer_eska on all tokos
            if (!empty($payload['tokos'])) {
                foreach ($payload['tokos'] as $toko) {
                    $isAlreadyInPareto = DB::table('list_toko_pareto_team_elite')
                        ->where('uniq_kd', $toko['code'])
                        ->where('distributor_code', $approval->distributor_code)
                        ->exists();

                    if ($isAlreadyInPareto) continue;

                    $oolToko = DB::table('jks_se_master_toko_ool')->where('customer_code', $toko['code'])->first();
                    if ($oolToko) {
                        if (empty($oolToko->customer_eska)) {
                            $missingTokos[] = [
                                'code' => $toko['code'],
                                'name' => $toko['name'],
                                'eska' => '',
                            ];
                        } else {
                            // Cek apakah PRC dari OOL ini sudah terpakai di Pareto di distributor ini oleh toko lain
                            $existsInPareto = DB::table('list_toko_pareto_team_elite')
                                ->where('customer_code_prc', $oolToko->customer_eska)
                                ->where('distributor_code', $approval->distributor_code)
                                ->where('uniq_kd', '!=', $toko['code'])
                                ->exists();
                                
                            if ($existsInPareto) {
                                $missingTokos[] = [
                                    'code' => $toko['code'],
                                    'name' => $toko['name'],
                                    'eska' => $oolToko->customer_eska,
                                ];
                            }
                        }
                    }
                }
                
                if (count($missingTokos) > 0) {
                    $this->prcApprovalId = $id;
                    $this->missingPrcTokos = $missingTokos;
                    $this->dispatch('open-prc-modal');
                    return; // Pause approval to show modal
                }
            }
        }

        $result = $this->executeApprovalLogic($id);
        
        match($result) {
            'success' => $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan berhasil disetujui & dieksekusi!']),
            'partial_success' => $this->dispatch('toast', ['type' => 'warning', 'message' => 'Disetujui, namun salah satu salesman tidak memiliki jadwal aktif di bulan ini.']),
            'already_processed' => $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan sudah diproses pihak lain.']),
            'not_found' => $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan tidak ditemukan.']),
            'duplicate_prc' => $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal: Kode PRC sudah terpakai oleh toko lain di distributor ini.']),
            'error' => $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pengajuan! Pastikan data masih valid.']),
        };
    }

    public function submitPrcAndApprove()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('user')) return;

        $this->validate([
            'missingPrcTokos.*.eska' => 'required|min:3'
        ], [
            'missingPrcTokos.*.eska.required' => 'Customer Kode PRC wajib diisi.',
            'missingPrcTokos.*.eska.min' => 'Kode PRC minimal 3 karakter.'
        ]);

        $approval = DB::table('jks_approvals')->where('id', $this->prcApprovalId)->first();
        if (!$approval) return;
        $distributorCode = $approval->distributor_code;

        // Cek duplikasi PRC di Master Pareto agar tidak kena SQL Error 23505
        $hasDuplicate = false;
        $inputPrcs = [];

        foreach ($this->missingPrcTokos as $index => $toko) {
            // Cek duplikasi di dalam form
            if (in_array($toko['eska'], $inputPrcs)) {
                $this->addError('missingPrcTokos.'.$index.'.eska', 'Kode PRC ini sama dengan input toko lainnya di form ini.');
                $hasDuplicate = true;
                continue;
            }
            $inputPrcs[] = $toko['eska'];

            // Cek duplikasi ke database khusus untuk distributor ini
            $exists = DB::table('list_toko_pareto_team_elite')
                ->where('customer_code_prc', $toko['eska'])
                ->where('distributor_code', $distributorCode)
                ->where('uniq_kd', '!=', $toko['code'])
                ->exists();

            if ($exists) {
                $this->addError('missingPrcTokos.'.$index.'.eska', 'Kode PRC "'.$toko['eska'].'" sudah terpakai di Pareto untuk distributor ini.');
                $hasDuplicate = true;
            }
        }

        if ($hasDuplicate) {
            return; // Hentikan proses jika ada duplikat, biarkan modal tetap terbuka menampilkan pesan error
        }

        // Prepare new PRCs to be updated atomically inside executeApprovalLogic
        $newPrcs = [];
        foreach ($this->missingPrcTokos as $toko) {
            $newPrcs[$toko['code']] = $toko['eska'];
        }
        
        // Proceed with approval (now fully atomic!)
        $result = $this->executeApprovalLogic($this->prcApprovalId, $newPrcs);
        
        // Reset state
        $this->closePrcModal();
        
        match($result) {
            'success' => $this->dispatch('toast', ['type' => 'success', 'message' => 'Kode PRC disimpan & Pengajuan disetujui!']),
            'already_processed' => $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan sudah diproses pihak lain.']),
            'not_found' => $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan tidak ditemukan.']),
            'duplicate_prc' => $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal: Kode PRC sudah terpakai oleh toko lain di distributor ini.']),
            'error' => $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pengajuan!']),
            default => $this->dispatch('toast', ['type' => 'success', 'message' => 'Kode PRC disimpan & Pengajuan disetujui!'])
        };
    }

    public function closePrcModal()
    {
        $this->prcApprovalId = null;
        $this->missingPrcTokos = [];
        $this->dispatch('close-prc-modal');
    }

    public function bulkApprove()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('user')) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan.']);
            return;
        }

        if (empty($this->selected)) return;

        // IDOR Guard Fix #5
        $userDistributorCode = auth()->user()->distributor_code ?? null;
        if ($userDistributorCode) {
            $unauthorizedCount = DB::table('jks_approvals')
                ->whereIn('id', $this->selected)
                ->where('distributor_code', '!=', $userDistributorCode)
                ->count();
            if ($unauthorizedCount > 0) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => $unauthorizedCount . ' pengajuan bukan milik distributor Anda. Akses ditolak.'
                ]);
                return;
            }
        }

        // Pre-validasi PRC untuk semua TAMBAH_JADWAL (Fix #2)
        $blockedIds = [];
        foreach ($this->selected as $id) {
            $approval = DB::table('jks_approvals')->where('id', $id)->first();
            if ($approval && $approval->action_type === 'TAMBAH_JADWAL') {
                $payload = json_decode($approval->payload, true);
                foreach ($payload['tokos'] ?? [] as $toko) {
                    $isAlreadyInPareto = DB::table('list_toko_pareto_team_elite')
                        ->where('uniq_kd', $toko['code'])
                        ->where('distributor_code', $approval->distributor_code)
                        ->exists();

                    if ($isAlreadyInPareto) continue;

                    $ool = DB::table('jks_se_master_toko_ool')
                        ->where('customer_code', $toko['code'])->first();
                    if ($ool) {
                        if (empty($ool->customer_eska)) {
                            $blockedIds[] = $id;
                            break;
                        } else {
                            $existsInPareto = DB::table('list_toko_pareto_team_elite')
                                ->where('customer_code_prc', $ool->customer_eska)
                                ->where('distributor_code', $approval->distributor_code)
                                ->where('uniq_kd', '!=', $toko['code'])
                                ->exists();
                            if ($existsInPareto) {
                                $blockedIds[] = $id;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($blockedIds)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => count($blockedIds) . ' pengajuan TAMBAH JADWAL memiliki toko tanpa Kode PRC atau PRC duplikat. Proses satu per satu.'
            ]);
            return;
        }

        $successCount = 0;
        $failCount = 0;
        
        foreach ($this->selected as $id) {
            $result = $this->executeApprovalLogic($id);
            if (in_array($result, ['success', 'partial_success'])) {
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
        // Jika dipanggil dari dalam PRC modal, tutup dulu
        if ($this->prcApprovalId !== null) {
            $this->closePrcModal();
        }

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

        $userDistributorCode = auth()->user()->distributor_code ?? null;

        if ($this->isBulkReject) {
            if ($userDistributorCode) {
                $unauthorizedCount = \Illuminate\Support\Facades\DB::table('jks_approvals')
                    ->whereIn('id', $this->selected)
                    ->where('distributor_code', '!=', $userDistributorCode)
                    ->count();
                if ($unauthorizedCount > 0) {
                    $this->dispatch('toast', [
                        'type' => 'error',
                        'message' => $unauthorizedCount . ' pengajuan bukan milik distributor Anda. Akses ditolak.'
                    ]);
                    return;
                }
            }

            $actualCount = 0;
            DB::transaction(function () use (&$actualCount) {
                $pendingIds = DB::table('jks_approvals')
                    ->whereIn('id', $this->selected)
                    ->where('status', 'PENDING')
                    ->lockForUpdate()
                    ->pluck('id');
                
                if ($pendingIds->isNotEmpty()) {
                    DB::table('jks_approvals')
                        ->whereIn('id', $pendingIds)
                        ->update([
                            'status' => 'REJECTED',
                            'checker_id' => auth()->id(),
                            'reject_reason' => $this->rejectReason,
                            'updated_at' => now()
                        ]);
                    $actualCount = $pendingIds->count();
                }
            });
            $this->resetSelection();
            if ($actualCount > 0) {
                $this->dispatch('toast', ['type' => 'success', 'message' => "{$actualCount} pengajuan berhasil ditolak!"]);
            } else {
                $this->dispatch('toast', ['type' => 'warning', 'message' => 'Tidak ada pengajuan yang berhasil ditolak. Mungkin sudah diproses oleh pihak lain.']);
            }
        } else {
            if ($userDistributorCode) {
                $checkApproval = \Illuminate\Support\Facades\DB::table('jks_approvals')->where('id', $this->actionIdToReject)->first();
                if ($checkApproval && $checkApproval->distributor_code !== $userDistributorCode) {
                    $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menolak pengajuan distributor ini.']);
                    return;
                }
            }

            $dispatched = false;
            DB::transaction(function () use (&$dispatched) {
                $approval = DB::table('jks_approvals')
                    ->where('id', $this->actionIdToReject)
                    ->where('status', 'PENDING')
                    ->lockForUpdate()
                    ->first();

                if ($approval) {
                    DB::table('jks_approvals')->where('id', $approval->id)->update([
                        'status' => 'REJECTED',
                        'checker_id' => auth()->id(),
                        'reject_reason' => $this->rejectReason,
                        'updated_at' => now()
                    ]);
                    $dispatched = true;
                }
            });

            if ($dispatched) {
                $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan ditolak!']);
            } else {
                $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan sudah diproses oleh pihak lain.']);
            }
            $this->resetSelection(); // Fix #6
        }

        $this->dispatch('close-reject-modal');
    }

    public function loadDrawerDetail($id)
    {
        $approval = DB::table('jks_approvals')->where('id', $id)->first();
        if (!$approval) return;

        $payload = json_decode($approval->payload, true);
        if (!$payload) return;

        $enrichedPayload = $payload;
        $enrichedPayload['action_type'] = $approval->action_type;

        // Lazy load store lists for specific actions
        if (in_array($approval->action_type, ['TUKAR_HARI', 'TUKAR_MINGGU', 'TUKAR_SALESMAN', 'DELETE_MASSAL', 'DELETE_INDIVIDU'])) {
            $tokos = collect();

            if ($approval->action_type === 'TUKAR_HARI' && isset($payload['salesman_code']) && isset($payload['hari_asal']) && isset($payload['hari_tujuan']) && isset($payload['minggu'])) {
                $tokos = DB::table('jks_salesmans')
                    ->where('jks_salesmans.salesman_code', $payload['salesman_code'])
                    ->where('jks_salesmans.distributor_code', $approval->distributor_code)
                    ->where(function($query) use ($payload) {
                        foreach ($payload['minggu'] as $week) {
                            $query->orWhere("jks_salesmans.$week", 'Y');
                        }
                    })
                    ->where(function($query) use ($payload) {
                        $query->where("jks_salesmans.{$payload['hari_asal']}", 'Y')
                              ->orWhere("jks_salesmans.{$payload['hari_tujuan']}", 'Y');
                    })
                    ->join('jks_se_master_toko_ool', 'jks_salesmans.customer_code', '=', 'jks_se_master_toko_ool.customer_code')
                    ->select('jks_se_master_toko_ool.customer_code as code', 'jks_se_master_toko_ool.customer_name as name')
                    ->distinct()
                    ->get();
            } elseif ($approval->action_type === 'TUKAR_MINGGU' && isset($payload['salesman_code']) && isset($payload['minggu_asal']) && isset($payload['minggu_tujuan'])) {
                $tokos = DB::table('jks_salesmans')
                    ->where('jks_salesmans.salesman_code', $payload['salesman_code'])
                    ->where('jks_salesmans.distributor_code', $approval->distributor_code)
                    ->where(function($query) use ($payload) {
                        $query->where("jks_salesmans.{$payload['minggu_asal']}", 'Y')
                              ->orWhere("jks_salesmans.{$payload['minggu_tujuan']}", 'Y');
                    })
                    ->join('jks_se_master_toko_ool', 'jks_salesmans.customer_code', '=', 'jks_se_master_toko_ool.customer_code')
                    ->select('jks_se_master_toko_ool.customer_code as code', 'jks_se_master_toko_ool.customer_name as name')
                    ->distinct()
                    ->get();
            } elseif ($approval->action_type === 'TUKAR_SALESMAN' && isset($payload['salesman_asal']) && isset($payload['salesman_tujuan'])) {
                $tokos = DB::table('jks_salesmans')
                    ->where('jks_salesmans.distributor_code', $approval->distributor_code)
                    ->where(function($query) use ($payload) {
                        $query->where('jks_salesmans.salesman_code', $payload['salesman_asal'])
                              ->orWhere('jks_salesmans.salesman_code', $payload['salesman_tujuan']);
                    })
                    ->join('jks_se_master_toko_ool', 'jks_salesmans.customer_code', '=', 'jks_se_master_toko_ool.customer_code')
                    ->select('jks_se_master_toko_ool.customer_code as code', 'jks_se_master_toko_ool.customer_name as name')
                    ->distinct()
                    ->get();
            } elseif ($approval->action_type === 'DELETE_MASSAL' && isset($payload['ids'])) {
                $tokos = DB::table('jks_salesmans')
                    ->whereIn('jks_salesmans.id', $payload['ids'])
                    ->join('jks_se_master_toko_ool', 'jks_salesmans.customer_code', '=', 'jks_se_master_toko_ool.customer_code')
                    ->select('jks_salesmans.*', 'jks_se_master_toko_ool.customer_name as name', 'jks_se_master_toko_ool.customer_code as code')
                    ->distinct()
                    ->get();
            } elseif ($approval->action_type === 'DELETE_INDIVIDU' && isset($payload['id'])) {
                $tokos = DB::table('jks_salesmans')
                    ->where('jks_salesmans.id', $payload['id'])
                    ->join('jks_se_master_toko_ool', 'jks_salesmans.customer_code', '=', 'jks_se_master_toko_ool.customer_code')
                    ->select('jks_salesmans.*', 'jks_se_master_toko_ool.customer_name as name', 'jks_se_master_toko_ool.customer_code as code')
                    ->distinct()
                    ->get();
            }

            if ($tokos->isNotEmpty()) {
                $enrichedPayload['tokos'] = $tokos->toArray();
            }
        }

        $this->dispatch('open-dynamic-drawer', $enrichedPayload);
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
        $distributorOptions = DB::table('jks_approvals')->select('distributor_code')->distinct()->pluck('distributor_code');

        // Pre-calculate PRC issues for PENDING TAMBAH_JADWAL approvals to prevent N+1 queries
        $prcIssues = [];
        if ($this->filterStatus === 'PENDING') {
            $tambahJadwalApprovals = collect($approvals->items())->where('action_type', 'TAMBAH_JADWAL');
            $prcIssues = $this->calculatePrcIssues($tambahJadwalApprovals);
        }

        return view('livewire.call-plan.jks-approvals.index', [
            'approvals' => $approvals,
            'kpi' => $kpi,
            'actionTypes' => $actionTypes,
            'distributorOptions' => $distributorOptions,
            'prcIssues' => $prcIssues
        ])->layout('layouts.app')->title('Pusat Persetujuan JKS');
    }
}
