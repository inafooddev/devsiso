<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JksEskalinkSheetExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $schedules;
    protected $sheetName;
    protected $flagDelete;

    public function __construct($schedules, $sheetName, $flagDelete)
    {
        $this->schedules = $schedules;
        $this->sheetName = $sheetName;
        $this->flagDelete = $flagDelete;
    }

    public function view(): View
    {
        // Hitung NORUTE per toko menggunakan customer_code asli (agar unik)
        $storeCounts = [];
        foreach ($this->schedules as $row) {
            $key = $row->original_custno;
            $storeCounts[$key] = ($storeCounts[$key] ?? 0) + 1;
            $row->calculated_norute = $storeCounts[$key];
        }

        return view('exports.jks_eskalink_sheet', [
            'schedules' => $this->schedules,
            'flagDelete' => $this->flagDelete,
        ]);
    }

    public function title(): string
    {
        return $this->sheetName;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style table header row (A6 to M6)
            'A6:M6' => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '4A86E8'], // Blue
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Alignment for NORUTE and CUSTNO values
            'A7:M' . ($sheet->getHighestRow()) => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                ],
            ],
        ];
    }
}
