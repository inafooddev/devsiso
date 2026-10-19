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

    protected ?string $startDate;
    protected ?string $endDate;

    protected $teamCache = [];
    protected $distributorCache = [];
    protected $customerCache = [];

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection(Collection $rows)
    {
        $inserts = [];
        $rowNumber = 1;

        // 1. Ekstraksi seluruh ID unik untuk Bulk Pre-loading Cache
        $allTeamCodes = [];
        $allDistCodes = [];
        $allCustCodes = [];

        foreach ($rows as $row) {
            if (empty($row['kode_team_wajib']) && empty($row['kode_team']) && empty($row['distributor_code']) && empty($row['custno'])) {
                continue;
            }

            $t = isset($row['kode_team_wajib']) && trim((string)$row['kode_team_wajib']) !== '' 
                ? trim((string)$row['kode_team_wajib']) 
                : (isset($row['kode_team']) && trim((string)$row['kode_team']) !== '' ? trim((string)$row['kode_team']) : null);
            if ($t) $allTeamCodes[] = $t;

            $d = isset($row['distributor_code_wajib']) && trim((string)$row['distributor_code_wajib']) !== '' 
                ? trim((string)$row['distributor_code_wajib']) 
                : (isset($row['distributor_code']) && trim((string)$row['distributor_code']) !== '' ? trim((string)$row['distributor_code']) : null);
            if ($d) $allDistCodes[] = $d;

            $c = isset($row['custno_wajib']) && trim((string)$row['custno_wajib']) !== '' 
                ? trim((string)$row['custno_wajib']) 
                : (isset($row['custno']) && trim((string)$row['custno']) !== '' ? trim((string)$row['custno']) : null);
            if ($c) $allCustCodes[] = $c;
        }

        $allTeamCodes = array_values(array_unique($allTeamCodes));
        $allDistCodes = array_values(array_unique($allDistCodes));
        $allCustCodes = array_values(array_unique($allCustCodes));

        // 2. Bulk Pre-fetch (Mengurangi ribuan query menjadi hanya 3 query massal)
        if (!empty($allTeamCodes)) {
            $this->teamCache = DB::table('fsalesman')
                ->whereIn('SLSNO', $allTeamCodes)
                ->where('TEAM', 'SPI')
                ->get()
                ->keyBy('SLSNO')
                ->all();
        }

        if (!empty($allDistCodes)) {
            $this->distributorCache = DB::table('master_distributors')
                ->whereIn('distributor_code', $allDistCodes)
                ->get()
                ->keyBy('distributor_code')
                ->all();
        }

        if (!empty($allDistCodes) && !empty($allCustCodes)) {
            foreach (array_chunk($allCustCodes, 1000) as $custChunk) {
                $customers = DB::table('list_toko_pareto_team_elite')
                    ->whereIn('distributor_code', $allDistCodes)
                    ->whereIn('customer_code_prc', $custChunk)
                    ->select('distributor_code', 'customer_code_prc', 'customer_name', 'customer_address')
                    ->get();

                foreach ($customers as $cust) {
                    $this->customerCache[$cust->distributor_code . '|' . $cust->customer_code_prc] = $cust;
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

            $kodeTeam = isset($row['kode_team_wajib']) && trim((string)$row['kode_team_wajib']) !== '' 
                ? trim((string)$row['kode_team_wajib']) 
                : (isset($row['kode_team']) && trim((string)$row['kode_team']) !== '' ? trim((string)$row['kode_team']) : null);

            $distCode = isset($row['distributor_code_wajib']) && trim((string)$row['distributor_code_wajib']) !== '' 
                ? trim((string)$row['distributor_code_wajib']) 
                : (isset($row['distributor_code']) && trim((string)$row['distributor_code']) !== '' ? trim((string)$row['distributor_code']) : null);

            $custNo = isset($row['custno_wajib']) && trim((string)$row['custno_wajib']) !== '' 
                ? trim((string)$row['custno_wajib']) 
                : (isset($row['custno']) && trim((string)$row['custno']) !== '' ? trim((string)$row['custno']) : null);

            $tanggal = null;
            if (isset($row['tanggal'])) {
                try {
                    if (is_numeric($row['tanggal'])) {
                        $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d');
                    } else {
                        $tanggal = Carbon::parse($row['tanggal'])->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $tanggal = null;
                }
            }

            if (empty($tanggal) || empty($kodeTeam) || empty($distCode) || empty($custNo)) {
                $this->errors[] = "Baris {$rowNumber}: Data wajib (Tanggal, Kode Team, Distributor Code, CustNo) tidak boleh kosong.";
                continue;
            }

            // Validasi Rentang Tanggal terhadap Filter Import (Date Range Guard)
            if (!empty($this->startDate) && !empty($this->endDate)) {
                if ($tanggal < $this->startDate || $tanggal > $this->endDate) {
                    $this->errors[] = "Baris {$rowNumber}: Tanggal '{$tanggal}' berada di luar rentang tanggal import yang dipilih ({$this->startDate} s/d {$this->endDate}).";
                    continue;
                }
            }

            // Validasi & Lookup Team (dari pre-loaded cache)
            if (!isset($this->teamCache[$kodeTeam])) {
                $this->errors[] = "Baris {$rowNumber}: Kode Team '{$kodeTeam}' tidak ditemukan di tabel fsalesman.";
                continue;
            }

            // Validasi & Lookup Distributor (dari pre-loaded cache)
            if (!isset($this->distributorCache[$distCode])) {
                $this->errors[] = "Baris {$rowNumber}: Distributor Code '{$distCode}' tidak ditemukan di tabel master_distributors.";
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
                    continue;
                }
            }

            // Validasi & Lookup Customer (Memastikan kombinasi distributor_code + custno cocok di list_toko_pareto_team_elite)
            $custKey = $distCode . '|' . $custNo;
            if (!isset($this->customerCache[$custKey])) {
                $this->errors[] = "Baris {$rowNumber}: Customer '{$custNo}' dengan Distributor '{$distCode}' tidak cocok atau tidak terdaftar di tabel list_toko_pareto_team_elite.";
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

        // 4. Deduplikasi data & perhitungan metrik jika tidak ada error
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
        }
    }
}
