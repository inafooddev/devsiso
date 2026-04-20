<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Tambahkan ini

class CallPlanExport implements WithMultipleSheets
{
    protected $region, $entity, $branch, $slsIds;

    public function __construct($region, $entity, $branch, $slsIds) {
        $this->region = $region;
        $this->entity = $entity;
        $this->branch = $branch;
        $this->slsIds = $slsIds;
    }

    public function sheets(): array {
        $sheets = [];
        foreach ($this->slsIds as $index => $slsno) {
            $sheets[] = new CallPlanSlsSheet($this->region, $this->entity, $this->branch, $slsno, $index + 1);
        }
        return $sheets;
    }
}

class CallPlanSlsSheet implements 
    FromCollection, 
    WithHeadings, 
    WithTitle, 
    WithCustomStartCell,
    ShouldAutoSize,
    WithStyles // Implementasikan WithStyles
{
    protected $region, $entity, $branch, $slsno, $ruteNumber;

    public function __construct($region, $entity, $branch, $slsno, $ruteNumber) {
        $this->region = $region; 
        $this->entity = $entity; 
        $this->branch = $branch; 
        $this->slsno = $slsno;
        $this->ruteNumber = $ruteNumber;
    }

   public function styles(Worksheet $sheet)
    {
        // Baris ke-7 adalah baris header rute
        return [
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'], // Teks Putih
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0070C0'], // Warna Biru
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function collection() {
        return DB::table('frute')
            ->where('region', $this->region) 
            ->where('cabang', $this->entity)
            ->where('kodecabang', $this->branch)
            ->where('slsno', $this->slsno)
            ->select('norute','custno','h1','h2','h3','h4','h5','h6','h7','m1','m2','m3','m4')
            ->get();
    }

    public function headings(): array {
        return [
            ["REGION:", $this->region],
            ["ENTITY:", $this->entity],
            ["BRANCH:", $this->branch],
            ["SLSNO:", $this->slsno],
            ["FLAG DELETE:", "Y"],
            ["NORUTE", "CUSTNO", "H1", "H2", "H3", "H4", "H5", "H6", "H7", "M1", "M2", "M3", "M4"]
        ];
    }

    public function title(): string {
        return "RUTE " . $this->ruteNumber;
    }

    public function startCell(): string {
        return 'A1';
    }
}