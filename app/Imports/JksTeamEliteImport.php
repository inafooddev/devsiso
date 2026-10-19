<?php

namespace App\Imports;

use App\Models\JksTeamElite;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JksTeamEliteImport implements ToCollection, WithHeadingRow
{
    public $errors = [];
    public $validInserts = [];
    public $distinctTeams = [];
    public $successCount = 0;
    public $duplicateCount = 0;
    public $rawRowsCount = 0;
    public $samplePreview = [];
    public ?string $minDate = null;
    public ?string $maxDate = null;

    public $categorizedErrors = [
        'mismatch_cust'      => 0,
        'mismatch_team_dist' => 0,
        'out_of_range_date'  => 0,
        'missing_team'       => 0,
        'missing_dist'       => 0,
        'authority_dist'     => 0,
        'empty_required'     => 0,
        'invalid_date'       => 0,
        'other'              => 0,
    ];

    protected ?string $startDate;
    protected ?string $endDate;

    protected $teamCache = [];
    protected $teamMappingCache = [];
    protected $distributorCache = [];
    protected $customerCache = [];

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Helper parsing tanggal yang tahan banting untuk berbagai format Indonesia & Excel
     */
    protected function parseDateValue($rawDate): ?string
    {
        if (empty($rawDate)) {
            return null;
        }

        // 1. Format Numeric Serial Excel
        if (is_numeric($rawDate)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $cleanStr = trim((string)$rawDate);

        // 2. Format Indonesia d/m/Y atau d-m-Y (contoh: 15/10/2026 atau 05-10-2026)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $cleanStr, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year = $m[3];
            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        // 3. Format ISO Y-m-d atau Y/m/d (contoh: 2026-10-15)
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $cleanStr, $m)) {
            $year = $m[1];
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[3], 2, '0', STR_PAD_LEFT);
            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        // 4. Fallback umum Carbon (ganti slash menjadi dash agar tidak tertukar bulan dan hari)
        try {
            return Carbon::parse(str_replace('/', '-', $cleanStr))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function collection(Collection $rows)
    {
        $inserts = [];
        $rowNumber = 1;

        // 1. Ekstraksi seluruh ID unik untuk Bulk Pre-loading Cache (dengan Normalisasi Kapital strtoupper)
        $allTeamCodes = [];
        $allDistCodes = [];
        $allCustCodes = [];

        foreach ($rows as $row) {
            if (empty($row['kode_team_wajib']) && empty($row['kode_team']) && empty($row['distributor_code']) && empty($row['custno'])) {
                continue;
            }

            $t = isset($row['kode_team_wajib']) && trim((string)$row['kode_team_wajib']) !== '' 
                ? strtoupper(trim((string)$row['kode_team_wajib'])) 
                : (isset($row['kode_team']) && trim((string)$row['kode_team']) !== '' ? strtoupper(trim((string)$row['kode_team'])) : null);
            if ($t) $allTeamCodes[] = $t;

            $d = isset($row['distributor_code_wajib']) && trim((string)$row['distributor_code_wajib']) !== '' 
                ? strtoupper(trim((string)$row['distributor_code_wajib'])) 
                : (isset($row['distributor_code']) && trim((string)$row['distributor_code']) !== '' ? strtoupper(trim((string)$row['distributor_code'])) : null);
            if ($d) $allDistCodes[] = $d;

            $c = isset($row['custno_wajib']) && trim((string)$row['custno_wajib']) !== '' 
                ? strtoupper(trim((string)$row['custno_wajib'])) 
                : (isset($row['custno']) && trim((string)$row['custno']) !== '' ? strtoupper(trim((string)$row['custno'])) : null);
            if ($c) $allCustCodes[] = $c;
        }

        $allTeamCodes = array_values(array_unique($allTeamCodes));
        $allDistCodes = array_values(array_unique($allDistCodes));
        $allCustCodes = array_values(array_unique($allCustCodes));

        // 2. Bulk Pre-fetch (Mengurangi ribuan query menjadi query massal terindeks)
        if (!empty($allTeamCodes)) {
            $teams = DB::table('fsalesman')
                ->whereIn('SLSNO', $allTeamCodes)
                ->where('TEAM', 'SPI')
                ->get();
            foreach ($teams as $team) {
                $this->teamCache[strtoupper(trim($team->SLSNO))] = $team;
            }

            $mappings = DB::table('team_elite_code_mappings')
                ->whereIn('team_elite_code', $allTeamCodes)
                ->get();
            foreach ($mappings as $map) {
                $this->teamMappingCache[strtoupper(trim($map->team_elite_code))] = $map;
            }
        }

        if (!empty($allDistCodes)) {
            $dists = DB::table('master_distributors')
                ->whereIn('distributor_code', $allDistCodes)
                ->get();
            foreach ($dists as $dist) {
                $this->distributorCache[strtoupper(trim($dist->distributor_code))] = $dist;
            }
        }

        if (!empty($allDistCodes) && !empty($allCustCodes)) {
            foreach (array_chunk($allCustCodes, 1000) as $custChunk) {
                $customers = DB::table('list_toko_pareto_team_elite')
                    ->whereIn('distributor_code', $allDistCodes)
                    ->whereIn('customer_code_prc', $custChunk)
                    ->select('distributor_code', 'customer_code_prc', 'customer_name', 'customer_address')
                    ->get();

                foreach ($customers as $cust) {
                    $key = strtoupper(trim($cust->distributor_code)) . '|' . strtoupper(trim($cust->customer_code_prc));
                    $this->customerCache[$key] = $cust;
                }
            }
        }

        // 3. Iterasi validasi data per baris di memori
        $user = auth()->user();

        foreach ($rows as $row) {
            $rowNumber++;

            // Skip completely empty rows
            if (empty($row['kode_team_wajib']) && empty($row['kode_team']) && empty($row['distributor_code']) && empty($row['custno'])) {
                continue;
            }

            $this->rawRowsCount++;

            // Normalisasi kapital & trim
            $kodeTeam = isset($row['kode_team_wajib']) && trim((string)$row['kode_team_wajib']) !== '' 
                ? strtoupper(trim((string)$row['kode_team_wajib'])) 
                : (isset($row['kode_team']) && trim((string)$row['kode_team']) !== '' ? strtoupper(trim((string)$row['kode_team'])) : null);

            $distCode = isset($row['distributor_code_wajib']) && trim((string)$row['distributor_code_wajib']) !== '' 
                ? strtoupper(trim((string)$row['distributor_code_wajib'])) 
                : (isset($row['distributor_code']) && trim((string)$row['distributor_code']) !== '' ? strtoupper(trim((string)$row['distributor_code'])) : null);

            $custNo = isset($row['custno_wajib']) && trim((string)$row['custno_wajib']) !== '' 
                ? strtoupper(trim((string)$row['custno_wajib'])) 
                : (isset($row['custno']) && trim((string)$row['custno']) !== '' ? strtoupper(trim((string)$row['custno'])) : null);

            $rawDate = $row['tanggal'] ?? null;
            $tanggal = $this->parseDateValue($rawDate);

            // Cek kelengkapan kolom wajib
            if (empty($tanggal) || empty($kodeTeam) || empty($distCode) || empty($custNo)) {
                if (empty($tanggal) && !empty($rawDate)) {
                    $this->errors[] = "Baris {$rowNumber}: Format tanggal '{$rawDate}' tidak dapat dipahami. Gunakan format YYYY-MM-DD atau DD/MM/YYYY.";
                    $this->categorizedErrors['invalid_date']++;
                } else {
                    $this->errors[] = "Baris {$rowNumber}: Data wajib (Tanggal, Kode Team, Distributor Code, CustNo) tidak boleh kosong.";
                    $this->categorizedErrors['empty_required']++;
                }
                continue;
            }

            // Validasi Rentang Tanggal terhadap Filter Import (Date Range Guard)
            if (!empty($this->startDate) && !empty($this->endDate)) {
                if ($tanggal < $this->startDate || $tanggal > $this->endDate) {
                    $this->errors[] = "Baris {$rowNumber}: Tanggal '{$tanggal}' berada di luar rentang tanggal import yang dipilih ({$this->startDate} s/d {$this->endDate}).";
                    $this->categorizedErrors['out_of_range_date']++;
                    continue;
                }
            }

            // Validasi & Lookup Team (dari pre-loaded cache)
            if (!isset($this->teamCache[$kodeTeam])) {
                $this->errors[] = "Baris {$rowNumber}: Kode Team '{$kodeTeam}' tidak ditemukan di tabel fsalesman.";
                $this->categorizedErrors['missing_team']++;
                continue;
            }

            // Validasi & Lookup Distributor (dari pre-loaded cache)
            if (!isset($this->distributorCache[$distCode])) {
                $this->errors[] = "Baris {$rowNumber}: Distributor Code '{$distCode}' tidak ditemukan di tabel master_distributors.";
                $this->categorizedErrors['missing_dist']++;
                continue;
            }

            // Security / Hierarchy Check for Distributor
            if ($user && !$user->hasRole('admin')) {
                $allowed = false;
                $md = $this->distributorCache[$distCode];
                if (!empty($user->supervisor_code) && $md->supervisor_code === $user->supervisor_code) {
                    $allowed = true;
                }
                if (!empty($user->area_code) && in_array($md->area_code, (array) $user->area_code)) {
                    $allowed = true;
                }
                if (!empty($user->region_code) && in_array($md->region_code, (array) $user->region_code)) {
                    $allowed = true;
                }
                if (!$allowed) {
                    $this->errors[] = "Baris {$rowNumber}: Distributor Code '{$distCode}' di luar wilayah otoritas Anda.";
                    $this->categorizedErrors['authority_dist']++;
                    continue;
                }
            }

            // Validasi Kesesuaian Wewenang Tim vs Distributor (Team-to-Distributor Guard)
            $tecm = $this->teamMappingCache[$kodeTeam] ?? null;
            $md = $this->distributorCache[$distCode];

            if (!$tecm) {
                $this->errors[] = "Baris {$rowNumber}: Tim '{$kodeTeam}' belum memiliki konfigurasi mapping wilayah di sistem (team_elite_code_mappings).";
                $this->categorizedErrors['mismatch_team_dist']++;
                continue;
            }

            $isMatch = false;
            $expectedInfo = '';
            $level = strtolower(trim($tecm->level ?? ''));

            if ($level === 'supervisor') {
                $tecmSiso = strtoupper(trim((string)($tecm->siso_code ?? '')));
                $mdSiso = strtoupper(trim((string)($md->supervisor_code ?? '')));

                if (!empty($tecmSiso) && $mdSiso === $tecmSiso) {
                    $isMatch = true;
                } else {
                    $expectedInfo = "Supervisor wilayah untuk distributor ini adalah '{$md->supervisor_code}' (bukan '{$tecm->siso_code}')";
                }
            } elseif ($level === 'area') {
                $tecmArea = strtoupper(trim((string)($tecm->area_code ?? '')));
                $mdArea = strtoupper(trim((string)($md->area_code ?? '')));

                if (!empty($tecmArea) && $mdArea === $tecmArea) {
                    $isMatch = true;
                } else {
                    $expectedInfo = "Area distributor ini adalah '{$md->area_code}', sedangkan tim '{$kodeTeam}' berada di area '{$tecm->area_code}'";
                }
            } elseif ($level === 'region') {
                $tecmRegion = strtoupper(trim((string)($tecm->region_code ?? '')));
                $mdRegion = strtoupper(trim((string)($md->region_code ?? '')));

                if (!empty($tecmRegion) && $mdRegion === $tecmRegion) {
                    $isMatch = true;
                } else {
                    $expectedInfo = "Region distributor ini adalah '{$md->region_code}', sedangkan tim '{$kodeTeam}' berada di region '{$tecm->region_code}'";
                }
            } else {
                if (!empty($tecm->siso_code) && strtoupper(trim((string)$md->supervisor_code)) === strtoupper(trim((string)$tecm->siso_code))) {
                    $isMatch = true;
                } elseif (!empty($tecm->area_code) && strtoupper(trim((string)$md->area_code)) === strtoupper(trim((string)$tecm->area_code))) {
                    $isMatch = true;
                } elseif (!empty($tecm->region_code) && strtoupper(trim((string)$md->region_code)) === strtoupper(trim((string)$tecm->region_code))) {
                    $isMatch = true;
                } else {
                    $expectedInfo = "Distributor '{$distCode}' tidak berada dalam cakupan wilayah tim '{$kodeTeam}'";
                }
            }

            if (!$isMatch) {
                $this->errors[] = "Baris {$rowNumber}: Distributor '{$distCode}' ({$md->distributor_name}) bukan wilayah wewenang Team '{$kodeTeam}'. {$expectedInfo}.";
                $this->categorizedErrors['mismatch_team_dist']++;
                continue;
            }

            // Validasi & Lookup Customer (Memastikan kombinasi distributor_code + custno cocok di list_toko_pareto_team_elite)
            $custKey = $distCode . '|' . $custNo;
            if (!isset($this->customerCache[$custKey])) {
                $this->errors[] = "Baris {$rowNumber}: Customer '{$custNo}' dengan Distributor '{$distCode}' tidak cocok atau tidak terdaftar di tabel list_toko_pareto_team_elite.";
                $this->categorizedErrors['mismatch_cust']++;
                continue;
            }

            $customerObj = $this->customerCache[$custKey];
            $teamObj = $this->teamCache[$kodeTeam];
            $distObj = $this->distributorCache[$distCode];

            // Kumpulkan data yang valid
            $inserts[] = [
                'tanggal'          => $tanggal,
                'kode_team'        => $kodeTeam,
                'nama_team'        => $teamObj->SLSNAME ?? $teamObj->slsname ?? $kodeTeam,
                'kode_region'      => $distObj->region_code,
                'nama_region'      => $distObj->region_name,
                'kode_area'        => $distObj->area_code,
                'nama_area'        => $distObj->area_name,
                'distributor_code' => $distCode,
                'distributor_name' => $distObj->distributor_name,
                'custno'           => $custNo,
                'custname'         => $customerObj->customer_name,
                'addres'           => $customerObj->customer_address,
                'created_at'       => Carbon::now()->toDateTimeString(),
                'updated_at'       => Carbon::now()->toDateTimeString(),
            ];
        }

        // 4. Deteksi File Kosong atau Format Template Tidak Sesuai
        if ($this->rawRowsCount === 0) {
            $this->errors[] = "File Excel tidak memiliki baris data yang valid atau format kolom tidak sesuai template. Pastikan kolom 'Tanggal', 'Kode Team', 'Distributor Code', dan 'CustNo' tersedia.";
            $this->categorizedErrors['empty_required']++;
        }

        // 5. Deduplikasi data & perhitungan metrik jika tidak ada error
        $rawCount = count($inserts);
        if (count($this->errors) === 0 && $rawCount > 0) {
            $uniqueInserts = [];
            foreach ($inserts as $item) {
                $key = $item['tanggal'] . '|' . $item['kode_team'] . '|' . $item['distributor_code'] . '|' . $item['custno'];
                $uniqueInserts[$key] = $item;
            }

            $this->validInserts = array_values($uniqueInserts);
            $this->duplicateCount = $rawCount - count($this->validInserts);
            $this->distinctTeams = array_values(array_unique(array_column($this->validInserts, 'kode_team')));
            $this->successCount = count($this->validInserts);
            
            $dates = array_column($this->validInserts, 'tanggal');
            $this->minDate = !empty($dates) ? min($dates) : null;
            $this->maxDate = !empty($dates) ? max($dates) : null;

            // Ambil 5 baris pertama untuk sampel pratinjau di layar konfirmasi Step 2
            $this->samplePreview = array_slice($this->validInserts, 0, 5);
        }
    }
}
