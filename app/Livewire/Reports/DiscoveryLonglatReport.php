<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\DiscoveryLonglatExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DiscoveryLonglatReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $datePreset = '1_month';
    public $diffDays = 0;
    public $durationLabel = '';
    public $isReady = true;
    
    public $selectedRegion = '';
    public $selectedArea = '';
    public $filterStatus = 'clean'; // 'clean', 'all', 'at_distributor', 'dup_visit', 'dup_master', 'dup_all'

    protected $queryString = [
        'startDate', 
        'endDate', 
        'selectedRegion', 
        'selectedArea',
        'filterStatus' => ['except' => 'clean'],
        'datePreset' => ['except' => '1_month']
    ];

    public $regions = [];
    public $areas = [];

    public function mount()
    {
        if (empty($this->startDate) || empty($this->endDate)) {
            $this->applyDatePreset($this->datePreset ?: '1_month');
        } else {
            $this->syncPresetFromDates();
            $this->calculateDuration();
        }
        
        // Exclude region HOINA dari daftar filter region
        $this->regions = DB::table('master_regions')
            ->where('region_code', '<>', 'HOINA')
            ->orderBy('region_name')
            ->get()
            ->toArray();

        if ($this->selectedRegion && $this->selectedRegion !== 'HOINA') {
            $this->areas = DB::table('master_areas')
                ->where('region_code', trim($this->selectedRegion))
                ->where('area_code', '<>', 'HOINA')
                ->orderBy('area_name')
                ->get()
                ->toArray();
        }
    }

    /**
     * Daftar preset smart rentang tanggal (1 s.d 6 bulan).
     */
    public function getDatePresetsProperty(): array
    {
        $now = Carbon::now();
        return [
            '1_month' => '1 Bulan (' . $now->translatedFormat('M Y') . ')',
            '2_months' => '2 Bulan (' . $now->copy()->subMonthsNoOverflow(1)->translatedFormat('M') . ' - ' . $now->translatedFormat('M Y') . ')',
            '3_months' => '3 Bulan (' . $now->copy()->subMonthsNoOverflow(2)->translatedFormat('M') . ' - ' . $now->translatedFormat('M Y') . ')',
            '4_months' => '4 Bulan (' . $now->copy()->subMonthsNoOverflow(3)->translatedFormat('M') . ' - ' . $now->translatedFormat('M Y') . ')',
            '5_months' => '5 Bulan (' . $now->copy()->subMonthsNoOverflow(4)->translatedFormat('M') . ' - ' . $now->translatedFormat('M Y') . ')',
            '6_months' => '6 Bulan (' . $now->copy()->subMonthsNoOverflow(5)->translatedFormat('M') . ' - ' . $now->translatedFormat('M Y') . ')',
            'last_30_days' => '30 Hari Terakhir',
            'last_month' => 'Bulan Lalu (' . $now->copy()->subMonthNoOverflow()->translatedFormat('M Y') . ')',
            'custom' => 'Custom Tanggal Manual...',
        ];
    }

    /**
     * Menghitung total hari dari rentang tanggal yang dipilih.
     */
    public function getDiffDaysProperty(): int
    {
        if ($this->diffDays <= 0 && $this->startDate && $this->endDate) {
            $this->calculateDuration();
        }
        return $this->diffDays;
    }

    /**
     * Label durasi rentang tanggal yang ramah dibaca.
     */
    public function getDurationLabelProperty(): string
    {
        if (empty($this->durationLabel) && $this->startDate && $this->endDate) {
            $this->calculateDuration();
        }
        return $this->durationLabel ?: '-';
    }

    /**
     * Hitung total hari dan label durasi dari rentang tanggal.
     */
    public function calculateDuration(): void
    {
        try {
            if ($this->startDate && $this->endDate) {
                $this->diffDays = (int) Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1;
            } else {
                $this->diffDays = 0;
            }
        } catch (\Throwable $e) {
            $this->diffDays = 0;
        }

        if ($this->diffDays <= 0) {
            $this->durationLabel = '-';
        } else {
            $months = round($this->diffDays / 30, 1);
            if ($months == round($months)) {
                $months = (int) $months;
            }
            $this->durationLabel = "{$this->diffDays} hari (~{$months} bln)";
        }
    }

    /**
     * Terapkan preset rentang tanggal (1 s.d 6 bulan).
     */
    public function applyDatePreset(string $preset): void
    {
        $this->datePreset = $preset;
        $now = Carbon::now();

        switch ($preset) {
            case '1_month':
                $this->startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case '2_months':
                $this->startDate = $now->copy()->subMonthsNoOverflow(1)->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case '3_months':
                $this->startDate = $now->copy()->subMonthsNoOverflow(2)->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case '4_months':
                $this->startDate = $now->copy()->subMonthsNoOverflow(3)->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case '5_months':
                $this->startDate = $now->copy()->subMonthsNoOverflow(4)->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case '6_months':
                $this->startDate = $now->copy()->subMonthsNoOverflow(5)->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->startDate = $now->copy()->subDays(29)->format('Y-m-d');
                $this->endDate = $now->copy()->format('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = $now->copy()->subMonthNoOverflow();
                $this->startDate = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
            default:
                break;
        }

        $this->calculateDuration();
    }

    /**
     * Sinkronisasi pilihan dropdown preset jika tanggal diubah secara manual.
     */
    protected function syncPresetFromDates(): void
    {
        if (empty($this->startDate) || empty($this->endDate)) {
            $this->datePreset = 'custom';
            return;
        }

        $now = Carbon::now();
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        for ($i = 1; $i <= 6; $i++) {
            $expectedStart = $now->copy()->subMonthsNoOverflow($i - 1)->startOfMonth()->format('Y-m-d');
            if ($this->startDate === $expectedStart && $this->endDate === $endOfMonth) {
                $this->datePreset = "{$i}_month" . ($i > 1 ? 's' : '');
                return;
            }
        }

        if ($this->startDate === $now->copy()->subDays(29)->format('Y-m-d') && $this->endDate === $now->copy()->format('Y-m-d')) {
            $this->datePreset = 'last_30_days';
            return;
        }

        $lastMonth = $now->copy()->subMonthNoOverflow();
        if ($this->startDate === $lastMonth->copy()->startOfMonth()->format('Y-m-d') && $this->endDate === $lastMonth->copy()->endOfMonth()->format('Y-m-d')) {
            $this->datePreset = 'last_month';
            return;
        }

        $this->datePreset = 'custom';
    }

    public function updatedDatePreset($value)
    {
        $this->applyDatePreset($value);
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->syncPresetFromDates();
        $this->calculateDuration();
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->syncPresetFromDates();
        $this->calculateDuration();
        $this->resetPage();
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->resetPage();
        if ($value && $value !== 'HOINA') {
            $this->areas = DB::table('master_areas')
                ->where('region_code', trim($value))
                ->where('area_code', '<>', 'HOINA')
                ->orderBy('area_name')
                ->get()
                ->toArray();
        } else {
            $this->areas = [];
        }
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function processData()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ], [
            'startDate.required' => 'Tanggal mulai wajib diisi.',
            'endDate.required' => 'Tanggal akhir wajib diisi.',
            'endDate.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ]);
        
        if ($this->diffDays > 186) {
            $this->addError('endDate', 'Maksimal rentang periode adalah 6 bulan (maks. 186 hari). Silakan perpendek rentang tanggal.');
            return;
        }

        $this->resetPage();
        $this->isReady = true;
    }

    public function resetFilter()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->filterStatus = 'clean';
        $this->applyDatePreset('1_month');
        $this->areas = [];
        $this->resetPage();
        $this->isReady = true;
    }

    /**
     * Base query CTE penemuan titik koordinat dengan pengecekan duplikasi & radius distributor:
     * Mapping kode distributor eskalink ke master distributor melalui distributor_implementasi_eskalink.
     * Mengabaikan Region HOINA dari perhitungan & pemrosesan data.
     * Mengeliminasi:
     * 1. Region dan Cabang HOINA.
     * 2. Titik koordinat 0 / null / di luar batas bumi (-90 s/d 90, -180 s/d 180).
     * 3. Titik yang berada di kantor/gudang distributor/depo (radius <= 100 meter).
     * 4. Titik duplikat antar hasil kunjungan sales (visit_coord_count > 1).
     * 5. Titik yang sudah terdaftar pada toko lain di master customer_prc_eska.
     */
    public static function getBaseQuery($startDate, $endDate)
    {
        try {
            $safeStart = Carbon::parse($startDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            $safeStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        try {
            $safeEnd = Carbon::parse($endDate)->format('Y-m-d');
        } catch (\Throwable $e) {
            $safeEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $rawSql = "
            WITH latest_visits AS (
                SELECT 
                    \"BID\",
                    \"CUSTNO\",
                    ROUND(\"V_LA\"::numeric, 6) as v_la,
                    ROUND(\"V_LG\"::numeric, 6) as v_lg,
                    \"TANGGAL\",
                    ROW_NUMBER() OVER (PARTITION BY \"BID\", \"CUSTNO\" ORDER BY \"TANGGAL\" DESC, \"ID\" DESC) as rn
                FROM rpt_visit_an_h
                WHERE \"FLAG_PJP\" = 'R' 
                  AND \"FLAG_VISIT\" = 'Y'
                  AND \"TANGGAL\"::date BETWEEN '{$safeStart}' AND '{$safeEnd}'
                  AND \"V_LA\" IS NOT NULL AND \"V_LA\" <> 0 AND \"V_LA\" BETWEEN -90 AND 90
                  AND \"V_LG\" IS NOT NULL AND \"V_LG\" <> 0 AND \"V_LG\" BETWEEN -180 AND 180
                  AND \"BID\" <> 'HOINA'
            ),
            target_stores AS (
                SELECT 
                    cpe.kodecabang,
                    cpe.custno,
                    cpe.custname,
                    cpe.custadd1,
                    cpe.ccity,
                    cpe.cterm,
                    cpe.typeout,
                    cpe.grupout,
                    cpe.gharga,
                    cpe.region_code,
                    cpe.area_code,
                    lv.v_la,
                    lv.v_lg,
                    lv.\"TANGGAL\" as visit_date,
                    die.distributor_code,
                    md.distributor_name as mapped_distributor_name
                FROM customer_prc_eska cpe
                JOIN latest_visits lv 
                  ON cpe.kodecabang = lv.\"BID\" AND cpe.custno = lv.\"CUSTNO\"
                LEFT JOIN distributor_implementasi_eskalink die 
                  ON cpe.kodecabang = die.eskalink_code
                LEFT JOIN master_distributors md 
                  ON die.distributor_code = md.distributor_code
                WHERE (cpe.la = '0' OR cpe.la IS NULL OR cpe.la = '')
                  AND (cpe.lg = '0' OR cpe.lg IS NULL OR cpe.lg = '')
                  AND cpe.region_code <> 'HOINA'
                  AND cpe.kodecabang <> 'HOINA'
                  AND lv.rn = 1
            ),
            existing_master_coords AS (
                SELECT DISTINCT 
                    ROUND(la::numeric, 6) as master_la, 
                    ROUND(lg::numeric, 6) as master_lg
                FROM customer_prc_eska
                WHERE la IS NOT NULL AND la <> '' AND la <> '0'
                  AND lg IS NOT NULL AND lg <> '' AND lg <> '0'
                  AND la ~ '^-?[0-9]+(\.[0-9]+)?$'
                  AND lg ~ '^-?[0-9]+(\.[0-9]+)?$'
                  AND la::numeric BETWEEN -90 AND 90
                  AND lg::numeric BETWEEN -180 AND 180
                  AND region_code <> 'HOINA'
            ),
            -- Master distributor aktif yang dimapping melalui distributor_implementasi_eskalink (exclude HOINA)
            active_distributors AS (
                SELECT 
                    die.eskalink_code,
                    md.distributor_code,
                    md.distributor_name,
                    md.latitude as dist_la,
                    md.longitude as dist_lg
                FROM distributor_implementasi_eskalink die
                JOIN master_distributors md 
                  ON die.distributor_code = md.distributor_code
                WHERE md.latitude IS NOT NULL AND md.latitude <> 0
                  AND md.longitude IS NOT NULL AND md.longitude <> 0
                  AND md.distributor_code <> 'HOINA'
            ),
            distributor_proximity AS (
                SELECT 
                    ts.kodecabang,
                    ts.custno,
                    ad.distributor_code as near_dist_code,
                    ad.distributor_name as near_dist_name,
                    ROUND((6371000 * acos(
                        LEAST(1.0, GREATEST(-1.0,
                            cos(radians(ad.dist_la)) * cos(radians(ts.v_la)) *
                            cos(radians(ts.v_lg) - radians(ad.dist_lg)) +
                            sin(radians(ad.dist_la)) * sin(radians(ts.v_la))
                        ))
                    ))::numeric, 1) as dist_meters,
                    ROW_NUMBER() OVER (
                        PARTITION BY ts.kodecabang, ts.custno 
                        ORDER BY (6371000 * acos(
                            LEAST(1.0, GREATEST(-1.0,
                                cos(radians(ad.dist_la)) * cos(radians(ts.v_la)) *
                                cos(radians(ts.v_lg) - radians(ad.dist_lg)) +
                                sin(radians(ad.dist_la)) * sin(radians(ts.v_la))
                            ))
                        )) ASC
                    ) as rn_dist
                FROM target_stores ts
                JOIN active_distributors ad
                  ON ABS(ts.v_la - ad.dist_la) <= 0.0015
                 AND ABS(ts.v_lg - ad.dist_lg) <= 0.0015
                WHERE (6371000 * acos(
                    LEAST(1.0, GREATEST(-1.0,
                        cos(radians(ad.dist_la)) * cos(radians(ts.v_la)) *
                        cos(radians(ts.v_lg) - radians(ad.dist_lg)) +
                        sin(radians(ad.dist_la)) * sin(radians(ts.v_la))
                    ))
                )) <= 100
            ),
            evaluated_stores AS (
                SELECT 
                    ts.*,
                    COUNT(*) OVER (PARTITION BY ts.v_la, ts.v_lg) as visit_coord_count,
                    CASE WHEN emc.master_la IS NOT NULL THEN true ELSE false END as is_master_duplicate,
                    CASE WHEN dp.dist_meters IS NOT NULL THEN true ELSE false END as is_at_distributor,
                    dp.dist_meters as dist_to_depo_meters,
                    COALESCE(dp.near_dist_name, ts.mapped_distributor_name) as near_dist_name
                FROM target_stores ts
                LEFT JOIN existing_master_coords emc 
                  ON ts.v_la = emc.master_la AND ts.v_lg = emc.master_lg
                LEFT JOIN distributor_proximity dp
                  ON ts.kodecabang = dp.kodecabang AND ts.custno = dp.custno AND dp.rn_dist = 1
            ),
            discovery_data AS (
                SELECT 
                    *,
                    CASE 
                        WHEN is_at_distributor THEN 'AT_DISTRIBUTOR'
                        WHEN visit_coord_count > 1 AND is_master_duplicate THEN 'DUP_BOTH'
                        WHEN visit_coord_count > 1 THEN 'DUP_VISIT'
                        WHEN is_master_duplicate THEN 'DUP_MASTER'
                        ELSE 'CLEAN'
                    END as status_clean
                FROM evaluated_stores
            )
            SELECT * FROM discovery_data
        ";

        return DB::table(DB::raw("($rawSql) as d"));
    }

    public function downloadExcel()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ], [
            'startDate.required' => 'Tanggal mulai wajib diisi.',
            'endDate.required' => 'Tanggal akhir wajib diisi.',
            'endDate.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ]);

        if ($this->diffDays > 186) {
            $this->addError('endDate', 'Maksimal rentang periode adalah 6 bulan (maks. 186 hari). Silakan perpendek rentang tanggal.');
            return;
        }

        $filterSuffix = match($this->filterStatus) {
            'clean' => 'Clean_Only_',
            'at_distributor' => 'At_Distributor_Only_',
            'dup_visit' => 'Dup_Visit_Only_',
            'dup_master' => 'Dup_Master_Only_',
            'dup_all' => 'Anomali_All_',
            default => 'All_'
        };

        $fileName = 'Discovery_LongLat_' . $filterSuffix . $this->startDate . '_to_' . $this->endDate . '.xlsx';
        return Excel::download(
            new DiscoveryLonglatExport(
                $this->startDate, 
                $this->endDate, 
                $this->selectedRegion, 
                $this->selectedArea, 
                $this->filterStatus
            ), 
            $fileName
        );
    }

    public function render()
    {
        $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 100);
        $summaryData = collect();
        $kpiStats = [
            'total_found' => 0,
            'total_clean' => 0,
            'total_at_distributor' => 0,
            'total_dup_visit' => 0,
            'total_dup_master' => 0,
        ];
        $diffDays = $this->diffDays;
        $isRangeTooLong = ($diffDays > 186);

        if ($this->isReady && !$isRangeTooLong) {
            try {
                $safeStart = Carbon::parse($this->startDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                $safeStart = Carbon::now()->startOfMonth()->format('Y-m-d');
            }
            try {
                $safeEnd = Carbon::parse($this->endDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                $safeEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
            }

            // 1. Ambil KPI Summary global & per region (exclude HOINA)
            $metricsQuery = "
                WITH latest_visits AS (
                    SELECT 
                        \"BID\",
                        \"CUSTNO\",
                        ROUND(\"V_LA\"::numeric, 6) as v_la,
                        ROUND(\"V_LG\"::numeric, 6) as v_lg,
                        \"TANGGAL\",
                        ROW_NUMBER() OVER (PARTITION BY \"BID\", \"CUSTNO\" ORDER BY \"TANGGAL\" DESC, \"ID\" DESC) as rn
                    FROM rpt_visit_an_h
                    WHERE \"FLAG_PJP\" = 'R' 
                      AND \"FLAG_VISIT\" = 'Y'
                      AND \"TANGGAL\"::date BETWEEN '{$safeStart}' AND '{$safeEnd}'
                      AND \"V_LA\" IS NOT NULL AND \"V_LA\" <> 0 AND \"V_LA\" BETWEEN -90 AND 90
                      AND \"V_LG\" IS NOT NULL AND \"V_LG\" <> 0 AND \"V_LG\" BETWEEN -180 AND 180
                      AND \"BID\" <> 'HOINA'
                ),
                target_stores AS (
                    SELECT 
                        cpe.kodecabang,
                        cpe.custno,
                        cpe.region_code,
                        lv.v_la,
                        lv.v_lg
                    FROM customer_prc_eska cpe
                    JOIN latest_visits lv 
                      ON cpe.kodecabang = lv.\"BID\" AND cpe.custno = lv.\"CUSTNO\"
                    WHERE (cpe.la = '0' OR cpe.la IS NULL OR cpe.la = '')
                      AND (cpe.lg = '0' OR cpe.lg IS NULL OR cpe.lg = '')
                      AND cpe.region_code <> 'HOINA'
                      AND cpe.kodecabang <> 'HOINA'
                      AND lv.rn = 1
                ),
                existing_master_coords AS (
                    SELECT DISTINCT 
                        ROUND(la::numeric, 6) as master_la, 
                        ROUND(lg::numeric, 6) as master_lg
                    FROM customer_prc_eska
                    WHERE la IS NOT NULL AND la <> '' AND la <> '0'
                      AND lg IS NOT NULL AND lg <> '' AND lg <> '0'
                      AND la ~ '^-?[0-9]+(\.[0-9]+)?$'
                      AND lg ~ '^-?[0-9]+(\.[0-9]+)?$'
                      AND la::numeric BETWEEN -90 AND 90
                      AND lg::numeric BETWEEN -180 AND 180
                      AND region_code <> 'HOINA'
                ),
                active_distributors AS (
                    SELECT 
                        die.eskalink_code,
                        md.distributor_code,
                        md.latitude as dist_la,
                        md.longitude as dist_lg
                    FROM distributor_implementasi_eskalink die
                    JOIN master_distributors md 
                      ON die.distributor_code = md.distributor_code
                    WHERE md.latitude IS NOT NULL AND md.latitude <> 0
                      AND md.longitude IS NOT NULL AND md.longitude <> 0
                      AND md.distributor_code <> 'HOINA'
                ),
                distributor_proximity AS (
                    SELECT 
                        ts.kodecabang,
                        ts.custno,
                        ROUND((6371000 * acos(
                            LEAST(1.0, GREATEST(-1.0,
                                cos(radians(ad.dist_la)) * cos(radians(ts.v_la)) *
                                cos(radians(ts.v_lg) - radians(ad.dist_lg)) +
                                sin(radians(ad.dist_la)) * sin(radians(ts.v_la))
                            ))
                        ))::numeric, 1) as dist_meters,
                        ROW_NUMBER() OVER (
                            PARTITION BY ts.kodecabang, ts.custno 
                            ORDER BY (6371000 * acos(
                                LEAST(1.0, GREATEST(-1.0,
                                    cos(radians(ad.dist_la)) * cos(radians(ts.v_la)) *
                                    cos(radians(ts.v_lg) - radians(ad.dist_lg)) +
                                    sin(radians(ad.dist_la)) * sin(radians(ts.v_la))
                                ))
                            )) ASC
                        ) as rn_dist
                    FROM target_stores ts
                    JOIN active_distributors ad
                      ON ABS(ts.v_la - ad.dist_la) <= 0.0015
                     AND ABS(ts.v_lg - ad.dist_lg) <= 0.0015
                    WHERE (6371000 * acos(
                        LEAST(1.0, GREATEST(-1.0,
                            cos(radians(ad.dist_la)) * cos(radians(ts.v_la)) *
                            cos(radians(ts.v_lg) - radians(ad.dist_lg)) +
                            sin(radians(ad.dist_la)) * sin(radians(ts.v_la))
                        ))
                    )) <= 100
                ),
                evaluated_stores AS (
                    SELECT 
                        ts.region_code,
                        COUNT(*) OVER (PARTITION BY ts.v_la, ts.v_lg) as visit_coord_count,
                        CASE WHEN emc.master_la IS NOT NULL THEN true ELSE false END as is_master_duplicate,
                        CASE WHEN dp.dist_meters IS NOT NULL THEN true ELSE false END as is_at_distributor
                    FROM target_stores ts
                    LEFT JOIN existing_master_coords emc 
                      ON ts.v_la = emc.master_la AND ts.v_lg = emc.master_lg
                    LEFT JOIN distributor_proximity dp
                      ON ts.kodecabang = dp.kodecabang AND ts.custno = dp.custno AND dp.rn_dist = 1
                ),
                discovery_data AS (
                    SELECT 
                        *,
                        CASE 
                            WHEN is_at_distributor THEN 'AT_DISTRIBUTOR'
                            WHEN visit_coord_count > 1 AND is_master_duplicate THEN 'DUP_BOTH'
                            WHEN visit_coord_count > 1 THEN 'DUP_VISIT'
                            WHEN is_master_duplicate THEN 'DUP_MASTER'
                            ELSE 'CLEAN'
                        END as status_clean
                    FROM evaluated_stores
                )
                SELECT 
                    GROUPING(region_code) as is_grand_total,
                    region_code,
                    COUNT(*) as total_found,
                    COUNT(*) FILTER (WHERE status_clean = 'CLEAN') as total_clean,
                    COUNT(*) FILTER (WHERE status_clean = 'AT_DISTRIBUTOR') as total_at_distributor,
                    COUNT(*) FILTER (WHERE status_clean IN ('DUP_VISIT', 'DUP_BOTH')) as total_dup_visit,
                    COUNT(*) FILTER (WHERE status_clean IN ('DUP_MASTER', 'DUP_BOTH')) as total_dup_master
                FROM discovery_data
                GROUP BY GROUPING SETS ((), (region_code))
            ";

            $metrics = DB::select($metricsQuery);
            $regionCleanMap = [];

            foreach ($metrics as $m) {
                if ($m->is_grand_total == 1) {
                    $kpiStats['total_found'] = (int) $m->total_found;
                    $kpiStats['total_clean'] = (int) $m->total_clean;
                    $kpiStats['total_at_distributor'] = (int) $m->total_at_distributor;
                    $kpiStats['total_dup_visit'] = (int) $m->total_dup_visit;
                    $kpiStats['total_dup_master'] = (int) $m->total_dup_master;
                } else {
                    $regKey = $m->region_code ?: 'NO_REGION';
                    $regionCleanMap[$regKey] = (int) $m->total_clean;
                }
            }

            $summaryData = collect($this->regions)->map(function($region) use ($regionCleanMap) {
                return (object) [
                    'region_code' => $region->region_code,
                    'region_name' => $region->region_name,
                    'total' => $regionCleanMap[$region->region_code] ?? 0
                ];
            });

            // 2. Query data untuk tabel paginasi
            $query = self::getBaseQuery($this->startDate, $this->endDate);

            if ($this->selectedRegion) {
                $query->where('region_code', $this->selectedRegion);
            }
            if ($this->selectedArea) {
                $query->where('area_code', $this->selectedArea);
            }

            if ($this->filterStatus === 'clean') {
                $query->where('status_clean', 'CLEAN');
            } elseif ($this->filterStatus === 'at_distributor') {
                $query->where('status_clean', 'AT_DISTRIBUTOR');
            } elseif ($this->filterStatus === 'dup_visit') {
                $query->whereIn('status_clean', ['DUP_VISIT', 'DUP_BOTH']);
            } elseif ($this->filterStatus === 'dup_master') {
                $query->whereIn('status_clean', ['DUP_MASTER', 'DUP_BOTH']);
            } elseif ($this->filterStatus === 'dup_all') {
                $query->whereIn('status_clean', ['AT_DISTRIBUTOR', 'DUP_VISIT', 'DUP_MASTER', 'DUP_BOTH']);
            }

            $data = $query
                ->orderBy('region_code')
                ->orderBy('area_code')
                ->orderBy('custno')
                ->paginate(100);
        }

        return view('livewire.reports.discovery-longlat-report', [
            'data' => $data,
            'summaryData' => $summaryData,
            'kpiStats' => $kpiStats,
            'isReady' => $this->isReady,
            'regions' => $this->regions,
            'areas' => $this->areas,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'datePreset' => $this->datePreset,
            'datePresets' => $this->datePresets,
            'durationLabel' => $this->durationLabel,
            'filterStatus' => $this->filterStatus,
            'diffDays' => $diffDays,
            'isRangeTooLong' => $isRangeTooLong,
        ])->layout('layouts.app');
    }
}
