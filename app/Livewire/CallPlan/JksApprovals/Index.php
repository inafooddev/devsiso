<?php

namespace App\Livewire\CallPlan\JksApprovals;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JksApprovalExport;
use App\Exports\Concerns\JksApprovalParserTrait;

class Index extends Component
{
    use WithPagination;
    use JksApprovalParserTrait;

    public $filterStatus = 'PENDING';
    public $search = '';
    public $filterType = '';
    public $filterDistributor = '';
    public $startDate = null;
    public $endDate = null;
    
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

    public function updatingStartDate() {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingEndDate() {
        $this->resetPage();
        $this->resetSelection();
    }

    public function setDatePreset(string $preset): void
    {
        $this->resetPage();
        $this->resetSelection();
        $today = \Carbon\Carbon::today();

        switch ($preset) {
            case 'today':
                $this->startDate = $today->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $yesterday = $today->copy()->subDay()->format('Y-m-d');
                $this->startDate = $yesterday;
                $this->endDate = $yesterday;
                break;
            case 'this_week':
                $this->startDate = $today->copy()->startOfWeek()->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'last_7_days':
                $this->startDate = $today->copy()->subDays(6)->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->startDate = $today->copy()->subDays(29)->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = $today->copy()->subMonthNoOverflow();
                $this->startDate = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'clear':
            case 'all':
                $this->startDate = null;
                $this->endDate = null;
                break;
        }
    }

    public function previousMonth(): void
    {
        $this->resetPage();
        $this->resetSelection();
        $currentStart = !empty($this->startDate) ? \Carbon\Carbon::parse($this->startDate) : \Carbon\Carbon::today();
        $prevMonth = $currentStart->copy()->subMonthNoOverflow();
        $this->startDate = $prevMonth->copy()->startOfMonth()->format('Y-m-d');
        $this->endDate = $prevMonth->copy()->endOfMonth()->format('Y-m-d');
    }

    public function nextMonth(): void
    {
        $this->resetPage();
        $this->resetSelection();
        $currentStart = !empty($this->startDate) ? \Carbon\Carbon::parse($this->startDate) : \Carbon\Carbon::today();
        $nextMonth = $currentStart->copy()->addMonthNoOverflow();
        $today = \Carbon\Carbon::today();

        $this->startDate = $nextMonth->copy()->startOfMonth()->format('Y-m-d');
        $end = $nextMonth->copy()->endOfMonth();
        $this->endDate = $end->gt($today) ? $today->format('Y-m-d') : $end->format('Y-m-d');

        if (\Carbon\Carbon::parse($this->startDate)->gt($today)) {
            $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
            $this->endDate = $today->format('Y-m-d');
        }
    }

    public function resetDateFilter(): void
    {
        $this->startDate = null;
        $this->endDate = null;
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

    public function getApprovalsQuery($allStatus = false)
    {
        $query = DB::table('jks_approvals as ja')
            ->join('users as u', 'ja.maker_id', '=', 'u.id')
            ->leftJoin('users as c', 'ja.checker_id', '=', 'c.id')
            ->select('ja.*', 'u.name as maker_name', 'c.name as checker_name');

        if (!$allStatus) {
            $query->where('ja.status', $this->filterStatus);
        }
            
        $query = $this->applyHierarchyAccess($query, 'ja.');

        if (!empty($this->search)) {
            $searchTerm = trim($this->search);

            $matchingDistributors = DB::table('master_distributors')
                ->where('distributor_name', 'ilike', '%' . $searchTerm . '%')
                ->orWhere('branch_name', 'ilike', '%' . $searchTerm . '%')
                ->pluck('distributor_code')
                ->toArray();

            $matchingSalesmans = DB::table('salesmans')
                ->where('salesman_name', 'ilike', '%' . $searchTerm . '%')
                ->pluck('salesman_code')
                ->toArray();

            $query->where(function($q) use ($searchTerm, $matchingDistributors, $matchingSalesmans) {
                $q->where('ja.reason', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('ja.payload', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('u.name', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('c.name', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('ja.distributor_code', 'ilike', '%' . $searchTerm . '%');

                if (!empty($matchingDistributors)) {
                    $q->orWhereIn('ja.distributor_code', $matchingDistributors);
                }

                if (!empty($matchingSalesmans)) {
                    foreach ($matchingSalesmans as $scode) {
                        $q->orWhere('ja.payload', 'ilike', '%' . $scode . '%');
                    }
                }
            });
        }

        if (!empty($this->filterType)) {
            $query->where('ja.action_type', $this->filterType);
        }

        if (!empty($this->filterDistributor)) {
            $query->where('ja.distributor_code', $this->filterDistributor);
        }

        $dateColumn = ($this->filterStatus === 'PENDING') ? 'ja.created_at' : 'ja.updated_at';

        if (!empty($this->startDate)) {
            $query->where($dateColumn, '>=', $this->startDate . ' 00:00:00');
        }

        if (!empty($this->endDate)) {
            $query->where($dateColumn, '<=', $this->endDate . ' 23:59:59');
        }

        return $query;
    }

    public function exportExcel($allStatus = false)
    {
        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'spm'])) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Akses ditolak. Fitur export laporan persetujuan hanya dapat diakses oleh Admin dan SPM.'
            ]);
            return;
        }

        $query = $this->getApprovalsQuery($allStatus);

        // Pastikan join ke master_distributors ada untuk nama distributor, region, dan area
        $hasMdJoin = collect($query->joins ?? [])->contains(function ($join) {
            return str_contains($join->table, 'master_distributors');
        });
        if (!$hasMdJoin) {
            $query->leftJoin('master_distributors as md', 'ja.distributor_code', '=', 'md.distributor_code');
        }

        $query->addSelect([
            'md.distributor_name',
            'md.region_name',
            'md.area_name',
        ]);

        $statusLabel = $allStatus ? 'SEMUA_STATUS' : $this->filterStatus;
        $timestamp = date('Ymd_His');
        $fileName = "Laporan_Persetujuan_JKS_{$statusLabel}_{$timestamp}.xlsx";

        return Excel::download(
            new JksApprovalExport($query),
            $fileName
        );
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
                    case 'DELETE_INDIVIDU':
                        $exists = DB::table('jks_salesmans')->where('id', $payload['id'])->exists();
                        if (!$exists) {
                            DB::table('jks_approvals')->where('id', $id)->update([
                                'status' => 'REJECTED',
                                'reject_reason' => 'Pengajuan otomatis dibatalkan: Data jadwal fisik sudah tidak ditemukan di sistem.',
                                'checker_id' => auth()->id(),
                                'updated_at' => now()
                            ]);
                            return 'data_not_found';
                        }
                        
                        if ($approval->action_type === 'EDIT_INDIVIDU') {
                            DB::table('jks_salesmans')
                                ->where('id', $payload['id'])
                                ->update(array_merge($payload['update'], ['updated_at' => now()]));
                        } else {
                            if (($approval->delete_type ?? 'SOFT') === 'HARD') {
                                DB::table('jks_salesmans')->where('id', $payload['id'])->delete();
                            } else {
                                DB::table('jks_salesmans')
                                    ->where('id', $payload['id'])
                                    ->update(array_merge($payload['update'], ['updated_at' => now()]));
                            }
                        }
                        break;

                    case 'DELETE_MASSAL':
                        $existingIds = DB::table('jks_salesmans')->whereIn('id', $payload['ids'])->pluck('id');
                        if ($existingIds->isEmpty()) {
                            DB::table('jks_approvals')->where('id', $id)->update([
                                'status' => 'REJECTED',
                                'reject_reason' => 'Pengajuan otomatis dibatalkan: Seluruh data jadwal sudah tidak ditemukan di sistem.',
                                'checker_id' => auth()->id(),
                                'updated_at' => now()
                            ]);
                            return 'data_not_found';
                        }
                        
                        if (($approval->delete_type ?? 'SOFT') === 'HARD') {
                            DB::table('jks_salesmans')
                                ->whereIn('id', $existingIds)
                                ->where('distributor_code', $approval->distributor_code)
                                ->delete();
                        } else {
                            DB::table('jks_salesmans')
                                ->whereIn('id', $existingIds)
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
        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'user', 'spm'])) {
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
            'data_not_found' => $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan dibatalkan otomatis: Data jadwal fisik sudah tidak ditemukan di sistem.']),
            'duplicate_prc' => $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal: Kode PRC sudah terpakai oleh toko lain di distributor ini.']),
            'error' => $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal mengeksekusi pengajuan! Pastikan data masih valid.']),
        };
    }

    public function submitPrcAndApprove()
    {
        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'user', 'spm'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan.']);
            return;
        }

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
            'data_not_found' => $this->dispatch('toast', ['type' => 'warning', 'message' => 'Pengajuan dibatalkan otomatis: Data jadwal fisik sudah tidak ditemukan di sistem.']),
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
        if (empty($this->selected)) return;

        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'user', 'spm'])) {
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
        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'user', 'spm'])) {
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
                    ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                        $join->on('jks_salesmans.distributor_code', '=', 'ltpte.distributor_code')
                             ->on('jks_salesmans.customer_code', '=', 'ltpte.uniq_kd');
                    })
                    ->select('jks_salesmans.customer_code as code', 'ltpte.customer_name as name')
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
                    ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                        $join->on('jks_salesmans.distributor_code', '=', 'ltpte.distributor_code')
                             ->on('jks_salesmans.customer_code', '=', 'ltpte.uniq_kd');
                    })
                    ->select('jks_salesmans.customer_code as code', 'ltpte.customer_name as name')
                    ->distinct()
                    ->get();
            } elseif ($approval->action_type === 'TUKAR_SALESMAN' && isset($payload['salesman_asal']) && isset($payload['salesman_tujuan'])) {
                $tokos = DB::table('jks_salesmans')
                    ->where('jks_salesmans.distributor_code', $approval->distributor_code)
                    ->where(function($query) use ($payload) {
                        $query->where('jks_salesmans.salesman_code', $payload['salesman_asal'])
                              ->orWhere('jks_salesmans.salesman_code', $payload['salesman_tujuan']);
                    })
                    ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                        $join->on('jks_salesmans.distributor_code', '=', 'ltpte.distributor_code')
                             ->on('jks_salesmans.customer_code', '=', 'ltpte.uniq_kd');
                    })
                    ->select('jks_salesmans.customer_code as code', 'ltpte.customer_name as name')
                    ->distinct()
                    ->get();
            } elseif ($approval->action_type === 'DELETE_MASSAL' && isset($payload['ids'])) {
                $tokos = DB::table('jks_salesmans')
                    ->whereIn('jks_salesmans.id', $payload['ids'])
                    ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                        $join->on('jks_salesmans.distributor_code', '=', 'ltpte.distributor_code')
                             ->on('jks_salesmans.customer_code', '=', 'ltpte.uniq_kd');
                    })
                    ->select('jks_salesmans.*', 'ltpte.customer_name as name', 'jks_salesmans.customer_code as code')
                    ->distinct()
                    ->get();
            } elseif ($approval->action_type === 'DELETE_INDIVIDU' && isset($payload['id'])) {
                $tokos = DB::table('jks_salesmans')
                    ->where('jks_salesmans.id', $payload['id'])
                    ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                        $join->on('jks_salesmans.distributor_code', '=', 'ltpte.distributor_code')
                             ->on('jks_salesmans.customer_code', '=', 'ltpte.uniq_kd');
                    })
                    ->select('jks_salesmans.*', 'ltpte.customer_name as name', 'jks_salesmans.customer_code as code')
                    ->distinct()
                    ->get();
            }

            if ($tokos->isNotEmpty()) {
                $enrichedPayload['tokos'] = $tokos->toArray();
            }
        }

        $this->dispatch('open-dynamic-drawer', $enrichedPayload);
    }

    protected function applyHierarchyAccess($query, $prefix = '')
    {
        return static::applyHierarchyAccessQuery($query, $prefix, auth()->user());
    }

    public static function applyHierarchyAccessQuery($query, $prefix = '', $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user || $user->hasRole(['admin', 'spm'])) return $query;
        
        $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';
        
        if (in_array($accessLevel, ['region', 'area'])) {
            $query->leftJoin('master_distributors as md', $prefix . 'distributor_code', '=', 'md.distributor_code');
            
            if ($accessLevel === 'region' && !empty($user->region_code)) {
                return $query->whereIn('md.region_code', (array) $user->region_code);
            }
            if ($accessLevel === 'area' && !empty($user->area_code)) {
                return $query->whereIn('md.area_code', (array) $user->area_code);
            }
        }
        
        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            $allowedDistributors = DB::table('jks_se_master_toko_ool')
                 ->whereIn('supervisor_code', (array)$user->supervisor_code)
                 ->pluck('distributor_code')->toArray();
                 
            $query->where(function($q) use ($allowedDistributors, $user, $prefix) {
                if (!empty($allowedDistributors)) {
                    $q->whereIn($prefix . 'distributor_code', $allowedDistributors)
                      ->orWhere($prefix . 'maker_id', $user->id);
                } else {
                    $q->where($prefix . 'maker_id', $user->id);
                }
            });
            return $query;
        }

        return $query;
    }

    public static function getPendingCount($user = null): int
    {
        $user = $user ?: auth()->user();
        if (!$user) return 0;

        $query = DB::table('jks_approvals as ja')->where('ja.status', 'PENDING');
        $query = static::applyHierarchyAccessQuery($query, 'ja.', $user);

        return $query->count();
    }

    public function approveGroup(array $approvalIds)
    {
        if (!auth()->check() || !auth()->user()->hasRole(['admin', 'user', 'spm'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan.']);
            return;
        }

        $userDistributorCode = auth()->user()->distributor_code ?? null;
        if ($userDistributorCode) {
            $unauthorizedCount = DB::table('jks_approvals')
                ->whereIn('id', $approvalIds)
                ->where('distributor_code', '!=', $userDistributorCode)
                ->count();
            if ($unauthorizedCount > 0) {
                $this->dispatch('toast', [
                    'type' => 'error',
                    'message' => 'Terdapat pengajuan yang bukan milik distributor Anda. Akses ditolak.'
                ]);
                return;
            }
        }

        // Pre-validasi PRC untuk TAMBAH_JADWAL
        $tambahJadwalApprovals = DB::table('jks_approvals')
            ->whereIn('id', $approvalIds)
            ->where('action_type', 'TAMBAH_JADWAL')
            ->get();
        $prcIssues = $this->calculatePrcIssues($tambahJadwalApprovals);
        if (!empty($prcIssues)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Grup ini memiliki pengajuan TAMBAH JADWAL dengan masalah Kode PRC (kosong/duplikat). Buka rincian dan proses per toko.'
            ]);
            return;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($approvalIds as $id) {
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
            $this->dispatch('toast', ['type' => 'success', 'message' => "Seluruh {$successCount} pengajuan pada grup berhasil disetujui & dieksekusi!"]);
        }
    }

    public function confirmRejectGroup(array $approvalIds, $salesmanName = '')
    {
        if (empty($approvalIds)) return;

        $this->selected = array_map('strval', $approvalIds);
        $this->actionIdToReject = null;
        $this->isBulkReject = true;
        $this->rejectReason = '';
        $this->dispatch('open-reject-modal');
    }

    public function toggleSelectGroup(array $groupApprovalIds)
    {
        $strIds = array_map('strval', $groupApprovalIds);
        $allIn = count(array_intersect($strIds, $this->selected)) === count($strIds);
        if ($allIn) {
            $this->selected = array_values(array_diff($this->selected, $strIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $strIds)));
        }
    }

    public function buildGroupedApprovals($approvals, array $salesmanMap, array $distributorMap, array $prcIssues = []): array
    {
        $jksIds = [];
        foreach ($approvals as $r) {
            $p = json_decode($r->payload, true) ?: [];
            if (isset($p['id'])) $jksIds[] = $p['id'];
            if (isset($p['ids']) && is_array($p['ids'])) {
                foreach ($p['ids'] as $id) $jksIds[] = $id;
            }
        }

        $jksMap = collect();
        if (!empty($jksIds)) {
            $jksMap = DB::table('jks_salesmans')
                ->whereIn('id', array_unique($jksIds))
                ->get(['id', 'customer_code', 'salesman_code', 'bulan', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'w1', 'w2', 'w3', 'w4'])
                ->keyBy('id');
        }

        $grouped = [];

        foreach ($approvals as $row) {
            $payload = json_decode($row->payload, true) ?: [];
            $salesman = $this->parseSalesman($row, $payload, $salesmanMap);
            $distCode = $row->distributor_code ?: '-';
            $distName = $distributorMap[$distCode] ?? $distCode;
            $salesmanCode = $salesman['code'] ?: '-';
            $salesmanName = $salesman['name'] ?: '-';

            $groupKey = $distCode . '_' . $salesmanCode;

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'key' => $groupKey,
                    'distributor_code' => $distCode,
                    'distributor_name' => $distName,
                    'salesman_code' => $salesmanCode,
                    'salesman_name' => $salesmanName,
                    'total_items' => 0,
                    'approval_ids' => [],
                    'action_counts' => [],
                    'latest_created_at' => $row->created_at,
                    'oldest_created_at' => $row->created_at,
                    'has_prc_issues' => false,
                    'aging' => null,
                    'items' => [],
                ];
            }

            $grouped[$groupKey]['total_items']++;
            $grouped[$groupKey]['approval_ids'][] = $row->id;

            $actionKey = $row->action_type;
            $grouped[$groupKey]['action_counts'][$actionKey] = ($grouped[$groupKey]['action_counts'][$actionKey] ?? 0) + 1;

            if ($row->created_at > $grouped[$groupKey]['latest_created_at']) {
                $grouped[$groupKey]['latest_created_at'] = $row->created_at;
            }
            if ($row->created_at < $grouped[$groupKey]['oldest_created_at']) {
                $grouped[$groupKey]['oldest_created_at'] = $row->created_at;
            }

            $hasPrc = isset($prcIssues[$row->id]);
            if ($hasPrc) {
                $grouped[$groupKey]['has_prc_issues'] = true;
            }

            $tokoList = $this->parseTokoList($row, $payload, $jksMap);
            $hariStr = $this->parseHari($payload, $row->reason);
            $mingguStr = $this->parseMinggu($payload, $row->reason);
            $cleanReason = $this->parseCleanReason($row, $payload);

            // Ekstraksi Jadwal Lama untuk EDIT_INDIVIDU
            $oldHari = '-';
            $oldMinggu = '-';
            if ($row->action_type === 'EDIT_INDIVIDU' && isset($payload['id']) && isset($jksMap[$payload['id']])) {
                $oldSchedule = $this->parseScheduleFromRecord($jksMap[$payload['id']]);
                $oldHari = $oldSchedule['hari'];
                $oldMinggu = $oldSchedule['minggu'];
            }

            // Hitung Aging Item
            $itemCreated = \Carbon\Carbon::parse($row->created_at);
            $daysAgo = (int) $itemCreated->diffInDays(now());
            $hoursAgo = (int) $itemCreated->diffInHours(now());
            if ($daysAgo === 0) {
                $itemAging = [
                    'days' => 0,
                    'label' => $hoursAgo === 0 ? 'Baru saja' : "{$hoursAgo} jam lalu",
                    'badge_class' => 'badge-info',
                    'text_class' => 'text-info',
                ];
            } elseif ($daysAgo <= 2) {
                $itemAging = [
                    'days' => $daysAgo,
                    'label' => "{$daysAgo} hari lalu",
                    'badge_class' => 'badge-warning',
                    'text_class' => 'text-warning font-semibold',
                ];
            } else {
                $itemAging = [
                    'days' => $daysAgo,
                    'label' => "Mengendap {$daysAgo} hari",
                    'badge_class' => 'badge-error',
                    'text_class' => 'text-error font-bold',
                ];
            }

            $primaryTokoName = !empty($tokoList) ? ($tokoList[0]['name'] ?? '-') : '-';
            $primaryTokoCode = !empty($tokoList) ? ($tokoList[0]['code'] ?? '-') : '-';

            $grouped[$groupKey]['items'][] = [
                'approval_id' => $row->id,
                'action_type' => $row->action_type,
                'delete_type' => $row->delete_type ?? 'SOFT',
                'customer_code' => $primaryTokoCode,
                'customer_name' => $primaryTokoName,
                'all_tokos' => $tokoList,
                'hari' => $hariStr,
                'minggu' => $mingguStr,
                'old_hari' => $oldHari,
                'old_minggu' => $oldMinggu,
                'reason' => $row->reason,
                'clean_reason' => $cleanReason,
                'maker_name' => $row->maker_name ?? '-',
                'checker_name' => $row->checker_name ?? '-',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'aging' => $itemAging,
                'reject_reason' => $row->reject_reason ?? null,
                'has_prc_issue' => $hasPrc,
                'payload' => $payload,
                'raw_approval' => $row,
            ];
        }

        // Kalkulasi Aging di Level Grup (berdasarkan tiket terlama yang mengendap)
        foreach ($grouped as &$g) {
            $oldestCreated = \Carbon\Carbon::parse($g['oldest_created_at']);
            $gDays = (int) $oldestCreated->diffInDays(now());
            $gHours = (int) $oldestCreated->diffInHours(now());
            if ($gDays === 0) {
                $g['aging'] = [
                    'days' => 0,
                    'label' => $gHours === 0 ? 'Hari ini' : "{$gHours} jam lalu",
                    'badge_class' => 'badge-info',
                ];
            } elseif ($gDays <= 2) {
                $g['aging'] = [
                    'days' => $gDays,
                    'label' => "{$gDays} hari lalu",
                    'badge_class' => 'badge-warning',
                ];
            } else {
                $g['aging'] = [
                    'days' => $gDays,
                    'label' => "Mengendap {$gDays} hari",
                    'badge_class' => 'badge-error',
                ];
            }
        }
        unset($g);

        return $grouped;
    }

    public function render()
    {
        $approvals = $this->getApprovalsQuery()
            ->orderBy('ja.created_at', 'desc')
            ->paginate(100);

        // KPI Counts
        $baseKpiQuery = $this->applyHierarchyAccess(DB::table('jks_approvals as ja'), 'ja.');
        $kpi = [
            'PENDING' => (clone $baseKpiQuery)->where('ja.status', 'PENDING')->count(),
            'APPROVED' => (clone $baseKpiQuery)->where('ja.status', 'APPROVED')->count(),
            'REJECTED' => (clone $baseKpiQuery)->where('ja.status', 'REJECTED')->count(),
        ];

        $actionTypes = $this->applyHierarchyAccess(DB::table('jks_approvals as ja'), 'ja.')->select('ja.action_type')->distinct()->pluck('action_type');
        $distributorOptions = $this->applyHierarchyAccess(DB::table('jks_approvals as ja'), 'ja.')->select('ja.distributor_code')->distinct()->pluck('distributor_code');

        // Pre-calculate PRC issues for PENDING TAMBAH_JADWAL approvals to prevent N+1 queries
        $prcIssues = [];
        if ($this->filterStatus === 'PENDING') {
            $tambahJadwalApprovals = collect($approvals->items())->where('action_type', 'TAMBAH_JADWAL');
            $prcIssues = $this->calculatePrcIssues($tambahJadwalApprovals);
        }

        // Preload lookup maps for grouped approvals
        $distributorCodes = collect($approvals->items())->pluck('distributor_code')->unique()->filter()->values()->toArray();
        $distributorMap = DB::table('master_distributors')
            ->whereIn('distributor_code', $distributorCodes)
            ->pluck('distributor_name', 'distributor_code')
            ->toArray();

        $salesmanMap = DB::table('salesmans')
            ->pluck('salesman_name', 'salesman_code')
            ->toArray();

        $groupedApprovals = $this->buildGroupedApprovals($approvals->items(), $salesmanMap, $distributorMap, $prcIssues);

        if (!empty(trim($this->search ?? ''))) {
            $this->dispatch('expand-all-groups', keys: array_keys($groupedApprovals));
        }

        return view('livewire.call-plan.jks-approvals.index', [
            'approvals' => $approvals,
            'groupedApprovals' => $groupedApprovals,
            'kpi' => $kpi,
            'actionTypes' => $actionTypes,
            'distributorOptions' => $distributorOptions,
            'prcIssues' => $prcIssues
        ])->layout('layouts.app')->title('Pusat Persetujuan JKS');
    }
}
