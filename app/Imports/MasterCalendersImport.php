<?php

namespace App\Imports;

use App\Models\MasterCalender;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MasterCalendersImport implements OnEachRow, WithStartRow
{
    public int $importedCount = 0;
    public int $skippedCount = 0;
    public array $logs = [];
    private int $currentRow = 1;

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $this->currentRow++;
        $data = $row->toArray();

        $rawDate = $data[0] ?? null;

        if (empty($rawDate)) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Kolom Tanggal kosong.";
            return;
        }

        try {
            // Handle numeric Excel date serial vs string
            if (is_numeric($rawDate)) {
                $dateObj = Carbon::instance(ExcelDate::excelToDateTimeObject($rawDate));
            } else {
                $dateObj = Carbon::parse(trim($rawDate));
            }
            $dateFormatted = $dateObj->format('Y-m-d');
        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow}: GAGAL - Format tanggal '{$rawDate}' tidak valid.";
            return;
        }

        // Auto calculate default attributes
        $calculated = MasterCalender::calculateAttributes($dateFormatted);

        $day = !empty($data[1]) ? trim($data[1]) : $calculated['day'];
        $dayNumber = is_numeric($data[2] ?? null) ? (int)$data[2] : $calculated['day_number'];
        $weekYear = is_numeric($data[3] ?? null) ? (int)$data[3] : $calculated['week_year'];
        $weekMonth = is_numeric($data[4] ?? null) ? (int)$data[4] : $calculated['week_month'];
        $month = is_numeric($data[5] ?? null) ? (int)$data[5] : $calculated['month'];
        $quarter = is_numeric($data[6] ?? null) ? (int)$data[6] : $calculated['quarter'];
        $semester = is_numeric($data[7] ?? null) ? (int)$data[7] : $calculated['semester'];
        $year = is_numeric($data[8] ?? null) ? (int)$data[8] : $calculated['year'];

        $rawLibur = $data[9] ?? null;
        if ($rawLibur !== null && $rawLibur !== '') {
            $liburNasional = in_array(strtolower(trim((string)$rawLibur)), ['1', 'true', 'ya', 'yes', 'libur']);
        } else {
            $liburNasional = $calculated['libur_nasional'];
        }

        try {
            MasterCalender::updateOrCreate(
                ['date' => $dateFormatted],
                [
                    'day' => $day,
                    'day_number' => $dayNumber,
                    'week_year' => $weekYear,
                    'week_month' => $weekMonth,
                    'month' => $month,
                    'quarter' => $quarter,
                    'semester' => $semester,
                    'year' => $year,
                    'libur_nasional' => $liburNasional,
                ]
            );
            $this->importedCount++;
        } catch (\Exception $e) {
            $this->skippedCount++;
            $this->logs[] = "Baris {$this->currentRow} ({$dateFormatted}): GAGAL simpan - " . $e->getMessage();
        }
    }
}
