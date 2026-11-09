<?php

namespace App\Http\Controllers\Mobile\JksSalesman;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    /**
     * Display the mobile JKS Salesman (Call Plan) view.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('mobile.login');
        }

        $bulan = $request->input('bulan', date('Y-m'));
        $bulanPrefix = substr($bulan, 0, 7) . '%';

        // 1. Dapatkan daftar distributor yang dapat diakses user dan memiliki data di tabel jks_salesmans
        $distributorsQuery = DB::table('master_distributors as md')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('jks_salesmans as js')
                  ->whereColumn('js.distributor_code', 'md.distributor_code');
            })
            ->select('md.distributor_code', 'md.distributor_name')
            ->distinct();

        if (!$user->hasRole(['admin', 'spm'])) {
            $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'all';
            if ($accessLevel === 'region' && !empty($user->region_code)) {
                $distributorsQuery->whereIn('md.region_code', (array) $user->region_code);
            } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
                $distributorsQuery->whereIn('md.area_code', (array) $user->area_code);
            } elseif ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
                $oolDistributors = DB::table('jks_se_master_toko_ool')
                    ->whereIn('supervisor_code', (array) $user->supervisor_code)
                    ->pluck('distributor_code')
                    ->toArray();

                $distributorsQuery->where(function ($q) use ($user, $oolDistributors) {
                    $q->whereIn('tecm.team_elite_code', (array) $user->supervisor_code);
                    if (!empty($oolDistributors)) {
                        $q->orWhereIn('md.distributor_code', $oolDistributors);
                    }
                });
            }
        }

        $distributors = $distributorsQuery->orderBy('md.distributor_name')->get();
        $allowedDistributorCodes = $distributors->pluck('distributor_code')->toArray();

        // Tentukan distributor aktif (default kosong saat pertama kali dibuka, dan pastikan berada dalam hak akses user)
        $selectedDistributor = $request->input('distributor', '');
        if (!empty($selectedDistributor) && !$user->hasRole(['admin', 'spm']) && !in_array($selectedDistributor, $allowedDistributorCodes, true)) {
            $selectedDistributor = '';
        }

        // 2. Dapatkan daftar salesman untuk distributor terpilih
        $salesmans = collect();
        if (!empty($selectedDistributor)) {
            $salesmans = DB::table('salesmans')
                ->where('distributor_code', $selectedDistributor)
                ->where('is_active', true)
                ->select('salesman_code', 'salesman_name')
                ->orderBy('salesman_name')
                ->get();
        }

        // Tentukan salesman aktif (wajib 1 salesman, default kosong agar data awal kosong)
        $selectedSalesman = $request->input('salesman', '');

        // 3. Query Toko JKS (Wajib pilih Distributor dan 1 Salesman)
        $jksList = collect();
        $visitSummary = [];
        $kpiSummary = [
            'total_toko' => 0,
            'total_rute' => 0,
            'total_non_rute' => 0,
            'total_non_gps' => 0,
            'total_visited' => 0,
            'total_ec' => 0,
            'total_order_val' => 0,
            'strike_rate' => 0,
        ];

        if (!empty($selectedDistributor) && !empty($selectedSalesman)) {
            $query = DB::table('jks_salesmans as js')
                ->leftJoin('master_distributors as md', 'js.distributor_code', '=', 'md.distributor_code')
                ->leftJoin('salesmans as s', function ($join) {
                    $join->on('js.distributor_code', '=', 's.distributor_code')
                         ->on('js.salesman_code', '=', 's.salesman_code');
                })
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function ($join) {
                    $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                         ->on('js.customer_code', '=', 'ltpte.uniq_kd');
                })
                ->leftJoin('jks_se_master_toko_ool as ool', function ($join) {
                    $join->on('js.distributor_code', '=', 'ool.distributor_code')
                         ->on('js.customer_code', '=', 'ool.customer_code');
                })
                ->where('js.distributor_code', $selectedDistributor)
                ->where('js.salesman_code', $selectedSalesman)
                ->where('js.bulan', 'like', $bulanPrefix)
                ->where('s.is_active', true)
                ->select([
                    'js.id',
                    'js.bulan',
                    'js.distributor_code',
                    'md.distributor_name',
                    'js.salesman_code',
                    's.salesman_name',
                    'js.customer_code as uniq_kode',
                    DB::raw('COALESCE(ltpte.customer_code_prc, ool.customer_eska, js.customer_code) as customer_code'),
                    DB::raw('COALESCE(ltpte.customer_name, ool.customer_name, js.customer_code) as customer_name'),
                    DB::raw('COALESCE(ltpte.customer_address, ool.alamat, \'-\') as customer_address'),
                    'ltpte.kecamatan',
                    'ltpte.desa',
                    'ltpte.pilar',
                    'ltpte.target',
                    'ltpte.latitude',
                    'ltpte.longitude',
                    'js.h1', 'js.h2', 'js.h3', 'js.h4', 'js.h5', 'js.h6', 'js.h7',
                    'js.w1', 'js.w2', 'js.w3', 'js.w4'
                ])
                ->orderByRaw('COALESCE(ltpte.customer_name, ool.customer_name, js.customer_code) ASC');

            $jksList = $query->get();

            // 4. Hitung Realisasi Kunjungan Bulanan dari rpt_visit_an_h
            if ($jksList->isNotEmpty()) {
                $eskalinkCode = DB::table('distributor_implementasi_eskalink')
                    ->where('distributor_code', $selectedDistributor)
                    ->value('eskalink_code') ?? $selectedDistributor;

                $targetCustnos = $jksList->map(function ($item) {
                    return !empty($item->customer_code) ? $item->customer_code : $item->uniq_kode;
                })->filter()->unique()->values()->all();

                $aggregates = DB::table('rpt_visit_an_h')
                    ->where('BID', $eskalinkCode)
                    ->where('TANGGAL', 'like', $bulanPrefix)
                    ->whereIn('CUSTNO', $targetCustnos)
                    ->select(
                        'CUSTNO',
                        DB::raw('COUNT(CASE WHEN "FLAG_VISIT" = \'Y\' AND "FLAG_PJP" = \'R\' THEN 1 END) as total_visit'),
                        DB::raw('COUNT(CASE WHEN "FLAG_EC" = \'Y\' AND "FLAG_PJP" = \'R\' THEN 1 END) as total_ec'),
                        DB::raw('COALESCE(SUM(CASE WHEN "FLAG_PJP" = \'R\' THEN "ORDER_VAL" ELSE 0 END), 0) as total_order_val'),
                        DB::raw('MAX(CASE WHEN "FLAG_PJP" = \'R\' THEN "TANGGAL" END) as latest_visit_date')
                    )
                    ->groupBy('CUSTNO')
                    ->get()
                    ->keyBy('CUSTNO');

                foreach ($jksList as $item) {
                    $targetCode = !empty($item->customer_code) ? $item->customer_code : $item->uniq_kode;
                    if (isset($aggregates[$targetCode])) {
                        $agg = $aggregates[$targetCode];
                        $visitSummary[$item->uniq_kode] = [
                            'total_visit' => (int) $agg->total_visit,
                            'total_ec' => (int) $agg->total_ec,
                            'total_order_val' => (float) $agg->total_order_val,
                            'latest_visit_date' => $agg->latest_visit_date,
                        ];
                    } else {
                        $visitSummary[$item->uniq_kode] = [
                            'total_visit' => 0,
                            'total_ec' => 0,
                            'total_order_val' => 0,
                            'latest_visit_date' => null,
                        ];
                    }
                }

                // Kalkulasi KPI
                $totalToko = $jksList->count();
                $totalNonGps = $jksList->filter(fn($i) => empty($i->latitude) || empty($i->longitude))->count();
                
                $totalRute = $jksList->filter(function($i) {
                    $hasDay = ($i->h1 === 'Y' || $i->h2 === 'Y' || $i->h3 === 'Y' || $i->h4 === 'Y' || $i->h5 === 'Y' || $i->h6 === 'Y');
                    $hasWeek = ($i->w1 === 'Y' || $i->w2 === 'Y' || $i->w3 === 'Y' || $i->w4 === 'Y');
                    return $hasDay && $hasWeek && ($i->h7 !== 'Y');
                })->count();

                $totalNonRute = $totalToko - $totalRute;

                $totalVisited = 0;
                $totalEc = 0;
                $totalOrderVal = 0;

                foreach ($visitSummary as $vs) {
                    if ($vs['total_visit'] > 0) $totalVisited++;
                    if ($vs['total_ec'] > 0) $totalEc++;
                    $totalOrderVal += $vs['total_order_val'];
                }

                $strikeRate = $totalVisited > 0 ? round(($totalEc / $totalVisited) * 100, 1) : 0;

                $kpiSummary = [
                    'total_toko' => $totalToko,
                    'total_rute' => $totalRute,
                    'total_non_rute' => $totalNonRute,
                    'total_non_gps' => $totalNonGps,
                    'total_visited' => $totalVisited,
                    'total_ec' => $totalEc,
                    'total_order_val' => $totalOrderVal,
                    'strike_rate' => $strikeRate,
                ];
            }
        }

        // 4. Data Pengajuan Approval (Default: PENDING & ALL Distributor - Cepat & Lengkap)
        $approvalData = $this->buildApprovalData($user, 'PENDING', 'ALL');

        return Inertia::render('mobile/Pages/JksSalesman/Index', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'userid' => $user->userid,
                'role' => $user->getRoleNames()->first() ?? 'User',
            ],
            'distributors' => $distributors,
            'salesmans' => $salesmans,
            'currentFilters' => [
                'distributor' => $selectedDistributor,
                'salesman' => $selectedSalesman,
                'bulan' => $bulan,
            ],
            'jksList' => $jksList,
            'visitSummary' => $visitSummary,
            'kpiSummary' => $kpiSummary,
            'approvals' => $approvalData['items'],
            'approvalCounts' => $approvalData['counts'],
            'pendingApprovalsCount' => $approvalData['global_pending_count'],
        ]);
    }

    /**
     * Endpoint JSON On-Demand untuk mengambil daftar pengajuan per Status & Distributor
     * tanpa membebani halaman utama.
     */
    public function getApprovals(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $status = strtoupper($request->input('status', 'PENDING'));
        if (!in_array($status, ['PENDING', 'APPROVED', 'REJECTED'])) {
            $status = 'PENDING';
        }

        $distributor = $request->input('distributor', 'ALL') ?: 'ALL';

        $data = $this->buildApprovalData($user, $status, $distributor);

        return response()->json($data);
    }

    /**
     * Helper terpusat untuk mengambil & mengurai data pengajuan JKS secara akurat & optimal.
     */
    private function buildApprovalData($user, string $status = 'PENDING', string $distributorCode = 'ALL'): array
    {
        // 1. Base Query dengan Hak Akses Hierarki yang identik dengan Web JksApprovals
        $baseCountQuery = DB::table('jks_approvals as ja');
        \App\Livewire\CallPlan\JksApprovals\Index::applyHierarchyAccessQuery($baseCountQuery, 'ja.', $user);

        // Global pending count untuk badge di BottomNav (seluruh distributor yang diakses user)
        $globalPendingCount = (clone $baseCountQuery)->where('ja.status', 'PENDING')->count();

        // Filter distributor untuk KPI counts pada halaman Pengajuan
        if (!empty($distributorCode) && $distributorCode !== 'ALL') {
            $baseCountQuery->where('ja.distributor_code', $distributorCode);
        }

        $statusCountsRaw = (clone $baseCountQuery)
            ->select('ja.status', DB::raw('COUNT(*) as total'))
            ->groupBy('ja.status')
            ->pluck('total', 'ja.status')
            ->toArray();

        $counts = [
            'pending' => (int) ($statusCountsRaw['PENDING'] ?? 0),
            'approved' => (int) (($statusCountsRaw['APPROVED'] ?? 0) + ($statusCountsRaw['AUTO_APPROVED'] ?? 0)),
            'rejected' => (int) ($statusCountsRaw['REJECTED'] ?? 0),
        ];

        // 2. Query Data Baris Pengajuan khusus untuk Status & Distributor yang sedang aktif
        $approvalsQuery = DB::table('jks_approvals as ja')
            ->leftJoin('users as u_maker', 'ja.maker_id', '=', 'u_maker.id')
            ->leftJoin('users as u_checker', 'ja.checker_id', '=', 'u_checker.id')
            ->leftJoin('master_distributors as md_info', 'ja.distributor_code', '=', 'md_info.distributor_code')
            ->select([
                'ja.id',
                'ja.distributor_code',
                'md_info.distributor_name',
                'ja.action_type',
                'ja.payload',
                'ja.reason',
                'ja.reject_reason',
                'ja.status',
                'ja.delete_type',
                'ja.created_at',
                'ja.updated_at',
                'u_maker.name as maker_name',
                'u_checker.name as checker_name',
            ]);

        \App\Livewire\CallPlan\JksApprovals\Index::applyHierarchyAccessQuery($approvalsQuery, 'ja.', $user);

        if (!empty($distributorCode) && $distributorCode !== 'ALL') {
            $approvalsQuery->where('ja.distributor_code', $distributorCode);
        }

        if ($status === 'APPROVED') {
            $approvalsQuery->whereIn('ja.status', ['APPROVED', 'AUTO_APPROVED']);
        } else {
            $approvalsQuery->where('ja.status', $status);
        }

        $approvalsQuery->orderByDesc('ja.id');

        // Untuk APPROVED nasional tanpa filter distributor, batasi 1500 terbaru agar respons tetap < 50ms.
        // Untuk PENDING, REJECTED, atau saat filter distributor dipilih, tarik 100% semua datanya.
        if ($status === 'APPROVED' && ($distributorCode === 'ALL' || empty($distributorCode))) {
            $approvalsQuery->limit(1500);
        }

        $rawApprovals = $approvalsQuery->get();

        // 3. Preload Salesman Map & JKS Record Map untuk resolusi nama salesman & toko yang akurat
        $salesmanCodes = [];
        $jksIds = [];
        foreach ($rawApprovals as $r) {
            $p = json_decode($r->payload, true) ?: [];
            if (!empty($p['salesman_code'])) $salesmanCodes[] = $p['salesman_code'];
            if (!empty($p['update']['salesman_code'])) $salesmanCodes[] = $p['update']['salesman_code'];
            if (!empty($p['records'][0]['salesman_code'])) $salesmanCodes[] = $p['records'][0]['salesman_code'];
            if (!empty($p['salesman_asal'])) $salesmanCodes[] = $p['salesman_asal'];
            if (!empty($p['salesman_tujuan'])) $salesmanCodes[] = $p['salesman_tujuan'];

            if (isset($p['id']) && is_numeric($p['id'])) {
                $jksIds[] = (int) $p['id'];
            }
        }

        $salesmanMap = [];
        $salesmanByDistMap = [];
        if (!empty($salesmanCodes)) {
            $salesmanRows = DB::table('salesmans')
                ->whereIn('salesman_code', array_unique($salesmanCodes))
                ->select('distributor_code', 'salesman_code', 'salesman_name')
                ->get();
            foreach ($salesmanRows as $sr) {
                $salesmanByDistMap[$sr->distributor_code . '_' . $sr->salesman_code] = $sr->salesman_name;
                $salesmanMap[$sr->salesman_code] = $sr->salesman_name;
            }
        }

        $jksRecordMap = [];
        if (!empty($jksIds)) {
            $jksRecords = DB::table('jks_salesmans as js')
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function ($join) {
                    $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                         ->on('js.customer_code', '=', 'ltpte.uniq_kd');
                })
                ->leftJoin('jks_se_master_toko_ool as ool', function ($join) {
                    $join->on('js.distributor_code', '=', 'ool.distributor_code')
                         ->on('js.customer_code', '=', 'ool.customer_code');
                })
                ->leftJoin('salesmans as s', function ($join) {
                    $join->on('js.distributor_code', '=', 's.distributor_code')
                         ->on('js.salesman_code', '=', 's.salesman_code');
                })
                ->whereIn('js.id', array_unique($jksIds))
                ->select([
                    'js.id',
                    'js.customer_code',
                    DB::raw('COALESCE(ltpte.customer_name, ool.customer_name, js.customer_code) as customer_name'),
                    'js.salesman_code',
                    's.salesman_name',
                ])
                ->get();

            foreach ($jksRecords as $rec) {
                $jksRecordMap[$rec->id] = $rec;
            }
        }

        $actionLabels = [
            'EDIT_INDIVIDU' => 'Edit Jadwal',
            'DELETE_INDIVIDU' => 'Hapus Jadwal',
            'TAMBAH_JADWAL' => 'Tambah Toko',
            'TUKAR_HARI' => 'Tukar Hari',
            'TUKAR_MINGGU' => 'Tukar Minggu',
            'TUKAR_SALESMAN' => 'Tukar Salesman',
            'DELETE_MASSAL' => 'Hapus Massal',
        ];

        $statusLabels = [
            'PENDING' => 'Menunggu',
            'APPROVED' => 'Disetujui',
            'AUTO_APPROVED' => 'Disetujui Otomatis',
            'REJECTED' => 'Ditolak',
        ];

        $daysMap = ['h1' => 'Senin', 'h2' => 'Selasa', 'h3' => 'Rabu', 'h4' => 'Kamis', 'h5' => 'Jumat', 'h6' => 'Sabtu', 'h7' => 'Minggu'];
        $weeksMap = ['w1' => 'M1', 'w2' => 'M2', 'w3' => 'M3', 'w4' => 'M4'];

        $items = [];

        foreach ($rawApprovals as $row) {
            $payload = json_decode($row->payload, true) ?: [];
            $actionLabel = $actionLabels[$row->action_type] ?? ucwords(strtolower(str_replace('_', ' ', $row->action_type)));

            // Resolusi Nama Salesman (Prioritaskan kombinasi distributor_code + salesman_code agar akurat)
            $salesmanCode = $payload['salesman_code']
                ?? ($payload['update']['salesman_code']
                ?? ($payload['records'][0]['salesman_code']
                ?? '-'));

            $salesmanName = '-';
            if ($row->action_type === 'TUKAR_SALESMAN') {
                $asalCode = $payload['salesman_asal'] ?? '-';
                $tujuanCode = $payload['salesman_tujuan'] ?? '-';
                $asalName = $salesmanByDistMap[$row->distributor_code . '_' . $asalCode] ?? ($salesmanMap[$asalCode] ?? $asalCode);
                $tujuanName = $salesmanByDistMap[$row->distributor_code . '_' . $tujuanCode] ?? ($salesmanMap[$tujuanCode] ?? $tujuanCode);
                $salesmanName = "{$asalName} -> {$tujuanName}";
            } elseif ($salesmanCode !== '-' && isset($salesmanByDistMap[$row->distributor_code . '_' . $salesmanCode])) {
                $salesmanName = $salesmanByDistMap[$row->distributor_code . '_' . $salesmanCode];
            } elseif ($salesmanCode !== '-' && isset($salesmanMap[$salesmanCode])) {
                $salesmanName = $salesmanMap[$salesmanCode];
            } elseif (preg_match('/SE:\s*([^|\(\)\],]+)/i', (string) $row->reason, $m)) {
                $salesmanName = trim($m[1], " \t\n\r\0\x0B)");
            } elseif (isset($payload['id']) && isset($jksRecordMap[$payload['id']]) && !empty($jksRecordMap[$payload['id']]->salesman_name)) {
                $salesmanName = $jksRecordMap[$payload['id']]->salesman_name;
            } elseif ($salesmanCode !== '-') {
                $salesmanName = $salesmanCode;
            }

            if (empty($salesmanName) || $salesmanName === '-') {
                $salesmanName = 'Tanpa Salesman';
            }

            // Resolusi Hari & Minggu
            $hariStr = '-';
            $mingguStr = '-';
            if (isset($payload['update']) && is_array($payload['update'])) {
                $d = [];
                $w = [];
                foreach ($daysMap as $k => $v) {
                    if (($payload['update'][$k] ?? '') === 'Y') $d[] = $v;
                }
                foreach ($weeksMap as $k => $v) {
                    if (($payload['update'][$k] ?? '') === 'Y') $w[] = $v;
                }
                if (!empty($d)) $hariStr = implode(', ', $d);
                if (!empty($w)) $mingguStr = implode(', ', $w);
            } elseif (isset($payload['hari']) && is_array($payload['hari'])) {
                $hariStr = implode(', ', array_map(fn($h) => $daysMap[$h] ?? $h, $payload['hari']));
            } elseif (isset($payload['hari_asal']) && isset($payload['hari_tujuan'])) {
                $hariStr = ($daysMap[$payload['hari_asal']] ?? $payload['hari_asal']) . ' -> ' . ($daysMap[$payload['hari_tujuan']] ?? $payload['hari_tujuan']);
            }

            if ($mingguStr === '-' && isset($payload['minggu']) && is_array($payload['minggu'])) {
                $mingguStr = implode(', ', array_map(fn($w) => $weeksMap[$w] ?? $w, $payload['minggu']));
            } elseif ($mingguStr === '-' && isset($payload['minggu_asal']) && isset($payload['minggu_tujuan'])) {
                $mingguStr = ($weeksMap[$payload['minggu_asal']] ?? $payload['minggu_asal']) . ' -> ' . ($weeksMap[$payload['minggu_tujuan']] ?? $payload['minggu_tujuan']);
            }

            if ($hariStr === '-' && preg_match('/Hari:\s*\[([^\]]+)\]/', (string) $row->reason, $m)) {
                $hariStr = trim($m[1]);
            }
            if ($mingguStr === '-' && preg_match('/Minggu:\s*\[([^\]]+)\]/', (string) $row->reason, $m)) {
                $mingguStr = str_replace('Minggu ', 'M', trim($m[1]));
            }

            // Resolusi Alasan Bersih
            $cleanReason = (string) $row->reason;
            if (!empty($payload['update']['reason'])) {
                $cleanReason = trim($payload['update']['reason']);
            } elseif (!empty($payload['reason'])) {
                $cleanReason = trim($payload['reason']);
            } elseif (preg_match('/Alasan:\s*(.+)$/i', (string) $row->reason, $m)) {
                $cleanReason = trim($m[1]);
            }

            // Resolusi Daftar Toko (Mendukung Multi-Toko di dalam 1 Pengajuan)
            $tokoEntries = [];
            if (!empty($payload['tokos']) && is_array($payload['tokos'])) {
                foreach ($payload['tokos'] as $t) {
                    $tokoEntries[] = [
                        'name' => $t['name'] ?? '-',
                        'code' => $t['code'] ?? ($payload['customer_code'] ?? '-'),
                    ];
                }
            } elseif (!empty($payload['records']) && is_array($payload['records'])) {
                foreach ($payload['records'] as $rec) {
                    $tokoEntries[] = [
                        'name' => $rec['customer_name'] ?? '-',
                        'code' => $rec['customer_code'] ?? '-',
                    ];
                }
            } else {
                $storeName = $payload['customer_name'] ?? '-';
                $storeCode = $payload['customer_code'] ?? '-';

                if ($storeName === '-' && preg_match('/Toko:\s*([^|\(]+)/', (string) $row->reason, $m)) {
                    $storeName = trim($m[1], " \t\n\r\0\x0B-()");
                }
                if (isset($payload['id']) && isset($jksRecordMap[$payload['id']])) {
                    $jksRec = $jksRecordMap[$payload['id']];
                    if ($storeName === '-' && !empty($jksRec->customer_name)) {
                        $storeName = $jksRec->customer_name;
                    }
                    if ($storeCode === '-' && !empty($jksRec->customer_code)) {
                        $storeCode = $jksRec->customer_code;
                    }
                }
                if ($storeName === '-' && $row->action_type === 'DELETE_MASSAL' && !empty($payload['ids'])) {
                    $storeName = 'Hapus Massal (' . count($payload['ids']) . ' Toko)';
                }

                $tokoEntries[] = [
                    'name' => $storeName,
                    'code' => $storeCode,
                ];
            }

            $createdAtFormatted = \Carbon\Carbon::parse($row->created_at)->format('d M Y, H:i');
            $createdTimeAgo = \Carbon\Carbon::parse($row->created_at)->diffForHumans();
            $updatedAtFormatted = $row->updated_at ? \Carbon\Carbon::parse($row->updated_at)->format('d M Y, H:i') : null;

            foreach ($tokoEntries as $idx => $toko) {
                $items[] = [
                    'id' => count($tokoEntries) > 1 ? ($row->id * 1000 + $idx) : $row->id,
                    'action_type' => $row->action_type,
                    'action_label' => $actionLabel,
                    'status' => $row->status,
                    'status_label' => $statusLabels[$row->status] ?? $row->status,
                    'distributor_code' => $row->distributor_code,
                    'distributor_name' => $row->distributor_name ?? $row->distributor_code,
                    'maker_name' => $row->maker_name ?? 'User',
                    'checker_name' => $row->checker_name,
                    'store_name' => $toko['name'],
                    'store_code' => $toko['code'],
                    'salesman_name' => $salesmanName,
                    'hari' => $hariStr,
                    'minggu' => $mingguStr,
                    'delete_type' => $row->delete_type,
                    'reason' => $cleanReason,
                    'raw_reason' => $row->reason,
                    'reject_reason' => $row->reject_reason,
                    'created_at' => $createdAtFormatted,
                    'created_time_ago' => $createdTimeAgo,
                    'updated_at' => $updatedAtFormatted,
                ];
            }
        }

        return [
            'items' => $items,
            'counts' => $counts,
            'global_pending_count' => $globalPendingCount,
        ];
    }

    /**
     * Ambil detail riwayat kunjungan & catatan alasan (RPT Visit) untuk 1 toko spesifik.
     */
    public function getVisitDetail(Request $request, $customerCode)
    {
        $distributorCode = $request->input('distributor');
        $bulan = $request->input('bulan', date('Y-m'));
        $bulanPrefix = substr($bulan, 0, 7) . '%';

        if (empty($distributorCode) || empty($customerCode)) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        $eskalinkCode = DB::table('distributor_implementasi_eskalink')
            ->where('distributor_code', $distributorCode)
            ->value('eskalink_code') ?? $distributorCode;

        $uniqKode = $request->input('uniq_kode');

        // Ambil riwayat dari rpt_visit_an_h leftJoin rpt_visit_an_r
        $historyQuery = DB::table('rpt_visit_an_h as h')
            ->leftJoin('rpt_visit_an_r as r', function ($join) {
                $join->on('h.ID', '=', 'r.HID')
                     ->on('h.BID', '=', 'r.BID')
                     ->on('h.CUSTNO', '=', 'r.CUSTNO')
                     ->on('h.TANGGAL', '=', DB::raw('r."TANGGAL"::text'));
            })
            ->where('h.BID', $eskalinkCode)
            ->where('h.TANGGAL', 'like', $bulanPrefix);

        if (!empty($uniqKode) && $uniqKode !== $customerCode) {
            $historyQuery->whereIn('h.CUSTNO', [$customerCode, $uniqKode]);
        } else {
            $historyQuery->where('h.CUSTNO', $customerCode);
        }

        $history = $historyQuery
            ->select([
                'h.ID',
                'h.TANGGAL',
                'h.MUID as salesman_code',
                'h.MUNAME as salesman_name',
                'h.FLAG_PJP as flag_pjp',
                'h.FLAG_VISIT as flag_visit',
                'h.FLAG_EC as flag_ec',
                'h.ORDER_VAL as order_val',
                'h.ORDER_QTY as order_qty',
                'h.TOTAL_ITEM as total_item',
                'h.TIME_IN as time_in',
                'h.TIME_OUT as time_out',
                'r.REASON_TYPE as reason_type',
                'r.REASON_ID as reason_code',
                'r.REASON_DESC as reason_desc',
                'r.REASON_NOTE as reason_note',
            ])
            ->orderByDesc('h.TANGGAL')
            ->orderByDesc('h.TIME_IN')
            ->get();

        return response()->json([
            'customer_code' => $customerCode,
            'history' => $history,
        ]);
    }

    /**
     * Simpan modifikasi jadwal (Hari, Minggu, Salesman).
     */
    public function updateSchedule(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'id' => 'required|integer',
            'distributor_code' => 'required|string',
            'salesman_code' => 'required|string',
            'h1' => 'nullable|string',
            'h2' => 'nullable|string',
            'h3' => 'nullable|string',
            'h4' => 'nullable|string',
            'h5' => 'nullable|string',
            'h6' => 'nullable|string',
            'h7' => 'nullable|string',
            'w1' => 'nullable|string',
            'w2' => 'nullable|string',
            'w3' => 'nullable|string',
            'w4' => 'nullable|string',
            'reason' => 'required|string|min:5',
        ], [
            'reason.min' => 'Alasan perubahan minimal 5 karakter.',
            'salesman_code.required' => 'Salesman wajib dipilih.',
        ]);

        $jks = DB::table('jks_salesmans as js')
            ->select('js.*', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.id', $validated['id'])
            ->first();

        if (!$jks) {
            return response()->json(['error' => 'Data jadwal tidak ditemukan.'], 404);
        }

        $salesman = DB::table('salesmans')
            ->where('distributor_code', $validated['distributor_code'])
            ->where('salesman_code', $validated['salesman_code'])
            ->first();

        $seName = $salesman ? $salesman->salesman_name : $validated['salesman_code'];

        $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
        $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];

        $activeHari = [];
        foreach ($hariNames as $hk => $hl) {
            if (($validated[$hk] ?? 'T') === 'Y') $activeHari[] = $hl;
        }
        $activeMinggu = [];
        foreach ($weekNames as $wk => $wl) {
            if (($validated[$wk] ?? 'T') === 'Y') $activeMinggu[] = $wl;
        }

        $custName = $jks->customer_name ?? $jks->customer_code;
        $reasonFull = "Edit Jadwal Toko: {$custName} (SE: {$seName}) | Hari: [" . implode(', ', $activeHari) . "] | Minggu: [" . implode(', ', $activeMinggu) . "] | Alasan: " . $validated['reason'];

        $payloadUpdate = [
            'salesman_code' => $validated['salesman_code'],
            'h1' => ($validated['h1'] ?? '') === 'Y' ? 'Y' : 'T',
            'h2' => ($validated['h2'] ?? '') === 'Y' ? 'Y' : 'T',
            'h3' => ($validated['h3'] ?? '') === 'Y' ? 'Y' : 'T',
            'h4' => ($validated['h4'] ?? '') === 'Y' ? 'Y' : 'T',
            'h5' => ($validated['h5'] ?? '') === 'Y' ? 'Y' : 'T',
            'h6' => ($validated['h6'] ?? '') === 'Y' ? 'Y' : 'T',
            'h7' => ($validated['h7'] ?? '') === 'Y' ? 'Y' : 'T',
            'w1' => ($validated['w1'] ?? '') === 'Y' ? 'Y' : 'T',
            'w2' => ($validated['w2'] ?? '') === 'Y' ? 'Y' : 'T',
            'w3' => ($validated['w3'] ?? '') === 'Y' ? 'Y' : 'T',
            'w4' => ($validated['w4'] ?? '') === 'Y' ? 'Y' : 'T',
            'reason' => $validated['reason'],
        ];

        $payload = [
            'id' => $validated['id'],
            'bulan' => $jks->bulan,
            'customer_code' => $jks->customer_code,
            'customer_name' => $custName,
            'update' => $payloadUpdate,
        ];

        // Jika user adalah admin atau spm, update langsung
        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole(['admin', 'spm']);

        if ($isAdmin) {
            DB::table('jks_salesmans')
                ->where('id', $validated['id'])
                ->update(array_merge($payloadUpdate, [
                    'updated_at' => now(),
                    'update_by' => $user->name,
                ]));

            DB::table('jks_approvals')->insert([
                'maker_id' => $user->id,
                'checker_id' => $user->id,
                'distributor_code' => $validated['distributor_code'],
                'action_type' => 'EDIT_INDIVIDU',
                'delete_type' => null,
                'payload' => json_encode($payload),
                'reason' => $reasonFull,
                'status' => 'AUTO_APPROVED',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil diperbarui!',
                'direct_update' => true,
            ]);
        } else {
            DB::table('jks_approvals')->insert([
                'maker_id' => $user->id,
                'checker_id' => null,
                'distributor_code' => $validated['distributor_code'],
                'action_type' => 'EDIT_INDIVIDU',
                'delete_type' => null,
                'payload' => json_encode($payload),
                'reason' => $reasonFull,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan modifikasi jadwal berhasil dikirim (Menunggu Approval).',
                'direct_update' => false,
            ]);
        }
    }

    /**
     * Hapus jadwal toko (Soft / Hard Delete).
     */
    public function deleteSchedule(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'id' => 'required|integer',
            'distributor_code' => 'required|string',
            'delete_type' => 'required|in:soft,hard',
            'reason' => 'required|string|min:5',
        ], [
            'reason.min' => 'Alasan hapus minimal 5 karakter.',
        ]);

        $jks = DB::table('jks_salesmans as js')
            ->select('js.*', 'ltpte.customer_name')
            ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                $join->on('js.distributor_code', '=', 'ltpte.distributor_code')
                     ->on('js.customer_code', '=', 'ltpte.uniq_kd');
            })
            ->where('js.id', $validated['id'])
            ->first();

        if (!$jks) {
            return response()->json(['error' => 'Data jadwal tidak ditemukan.'], 404);
        }

        $custName = $jks->customer_name ?? $jks->customer_code;
        $salesman = DB::table('salesmans')
            ->where('distributor_code', $jks->distributor_code)
            ->where('salesman_code', $jks->salesman_code)
            ->first();
        $seName = $salesman ? $salesman->salesman_name : ($jks->salesman_code ?? '-');

        $hariNames = ['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'];
        $weekNames = ['w1'=>'Minggu 1', 'w2'=>'Minggu 2', 'w3'=>'Minggu 3', 'w4'=>'Minggu 4'];

        $activeHari = [];
        foreach ($hariNames as $hk => $hl) {
            if (isset($jks->$hk) && $jks->$hk === 'Y') $activeHari[] = $hl;
        }
        $activeMinggu = [];
        foreach ($weekNames as $wk => $wl) {
            if (isset($jks->$wk) && $jks->$wk === 'Y') $activeMinggu[] = $wl;
        }

        $methodName = $validated['delete_type'] === 'hard' ? 'Hard Delete (Hapus Permanen)' : 'Soft Delete (Kosongkan Jadwal)';
        $reasonFull = "{$methodName} Jadwal Toko: {$custName} | SE: {$seName} | Hari: [" . implode(', ', $activeHari) . "] | Minggu: [" . implode(', ', $activeMinggu) . "] | Alasan: " . $validated['reason'];

        $payload = [
            'id' => $validated['id'],
            'bulan' => $jks->bulan,
            'delete_type' => strtoupper($validated['delete_type']),
            'records' => [
                [
                    'id' => $jks->id,
                    'bulan' => $jks->bulan,
                    'salesman_code' => $jks->salesman_code,
                    'distributor_code' => $jks->distributor_code,
                    'customer_code' => $jks->customer_code,
                    'customer_name' => $custName,
                ],
            ],
            'update' => [
                'h1' => 'T', 'h2' => 'T', 'h3' => 'T', 'h4' => 'T', 'h5' => 'T', 'h6' => 'T', 'h7' => 'T',
                'w1' => 'T', 'w2' => 'T', 'w3' => 'T', 'w4' => 'T',
                'reason' => $validated['reason'],
            ]
        ];

        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole(['admin', 'spm']);

        if ($isAdmin) {
            if ($validated['delete_type'] === 'hard') {
                DB::table('jks_salesmans')->where('id', $validated['id'])->delete();
            } else {
                DB::table('jks_salesmans')->where('id', $validated['id'])->update([
                    'h1' => 'T', 'h2' => 'T', 'h3' => 'T', 'h4' => 'T', 'h5' => 'T', 'h6' => 'T', 'h7' => 'T',
                    'w1' => 'T', 'w2' => 'T', 'w3' => 'T', 'w4' => 'T',
                    'updated_at' => now(),
                    'update_by' => $user->name,
                ]);
            }

            DB::table('jks_approvals')->insert([
                'maker_id' => $user->id,
                'checker_id' => $user->id,
                'distributor_code' => $validated['distributor_code'],
                'action_type' => 'DELETE_INDIVIDU',
                'delete_type' => strtoupper($validated['delete_type']),
                'payload' => json_encode($payload),
                'reason' => $reasonFull,
                'status' => 'AUTO_APPROVED',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dihapus!',
                'direct_update' => true,
            ]);
        } else {
            DB::table('jks_approvals')->insert([
                'maker_id' => $user->id,
                'checker_id' => null,
                'distributor_code' => $validated['distributor_code'],
                'action_type' => 'DELETE_INDIVIDU',
                'delete_type' => strtoupper($validated['delete_type']),
                'payload' => json_encode($payload),
                'reason' => $reasonFull,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan hapus jadwal berhasil dikirim (Menunggu Approval).',
                'direct_update' => false,
            ]);
        }
    }
}
