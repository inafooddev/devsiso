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

    protected $teamCache = [];
    protected $distributorCache = [];
    protected $customerCache = [];

    public function collection(Collection $rows)
    {
        $inserts = [];
        $rowNumber = 1; // WithHeadingRow treats data starting from row 2 essentially in Excel terms, but we'll track loops.

        foreach ($rows as $row) {
            $rowNumber++;

            // Skip completely empty rows
            if (empty($row['kode_team_wajib']) && empty($row['kode_team']) && empty($row['distributor_code']) && empty($row['custno'])) {
                continue;
            }

            $kodeTeam = $row['kode_team_wajib'] ?? ($row['kode_team'] ?? null);
            $distCode = $row['distributor_code_wajib'] ?? ($row['distributor_code'] ?? null);
            $custNo = $row['custno_wajib'] ?? ($row['custno'] ?? null);

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

            // Validasi & Lookup Team
            if (!array_key_exists($kodeTeam, $this->teamCache)) {
                $this->teamCache[$kodeTeam] = DB::table('fsalesman')->where('SLSNO', $kodeTeam)->where('TEAM', 'SPI')->first();
            }
            if (!$this->teamCache[$kodeTeam]) {
                $this->errors[] = "Baris {$rowNumber}: Kode Team '{$kodeTeam}' tidak ditemukan di tabel fsalesman.";
                continue;
            }

            // Validasi & Lookup Distributor
            if (!array_key_exists($distCode, $this->distributorCache)) {
                $this->distributorCache[$distCode] = DB::table('master_distributors')->where('distributor_code', $distCode)->first();
            }
            if (!$this->distributorCache[$distCode]) {
                $this->errors[] = "Baris {$rowNumber}: Distributor Code '{$distCode}' tidak ditemukan di tabel master_distributors.";
                continue;
            }

            // Validasi & Lookup Customer
            if (!array_key_exists($custNo, $this->customerCache)) {
                $this->customerCache[$custNo] = DB::table('list_toko_pareto_team_elite')->where('customer_code_prc', $custNo)->first();
            }
            if (!$this->customerCache[$custNo]) {
                $this->errors[] = "Baris {$rowNumber}: CustNo '{$custNo}' tidak ditemukan di tabel list_toko_pareto_team_elite.";
                continue;
            }

            // Kumpulkan data yang valid
            $inserts[] = [
                'tanggal'          => $tanggal,
                'kode_team'        => $kodeTeam,
                'nama_team'        => $this->teamCache[$kodeTeam]->SLSNAME,
                'kode_region'      => $this->distributorCache[$distCode]->region_code,
                'nama_region'      => $this->distributorCache[$distCode]->region_name,
                'kode_area'        => $this->distributorCache[$distCode]->area_code,
                'nama_area'        => $this->distributorCache[$distCode]->area_name,
                'distributor_code' => $distCode,
                'distributor_name' => $this->distributorCache[$distCode]->distributor_name,
                'custno'           => $custNo,
                'custname'         => $this->customerCache[$custNo]->customer_name,
                'addres'           => $this->customerCache[$custNo]->customer_address,
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ];
        }

        // Jika tidak ada error sama sekali, simpan ke array validInserts
        if (count($this->errors) === 0 && count($inserts) > 0) {
            // Deduplikasi data berdasarkan composite key untuk mencegah error unique constraint
            $uniqueInserts = [];
            foreach ($inserts as $item) {
                $key = $item['tanggal'] . '|' . $item['kode_team'] . '|' . $item['distributor_code'] . '|' . $item['custno'];
                $uniqueInserts[$key] = $item;
            }

            $this->validInserts = array_values($uniqueInserts);
            $this->distinctTeams = array_unique(array_column($this->validInserts, 'kode_team'));
            $this->successCount = count($this->validInserts);
        }
    }
}
