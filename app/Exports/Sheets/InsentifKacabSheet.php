<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\InsentifMasterDistributor;
use App\Models\TargetKacab;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsentifKacabSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithTitle
{
    protected $kacabData = [];
    protected $totals = [];
    protected $monthName;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->monthName = Carbon::parse($bulan . '-01')->translatedFormat("F'y");
        $yearFilter = Carbon::parse($bulan . '-01')->format('Y');

        $query = InsentifMasterDistributor::where('bulan', $bulan);
        if ($region) $query->where('region_name', $region);
        if (!empty($areas)) $query->whereIn('area_name', $areas);
        
        $masterData = $query->orderBy('area_name')->orderBy('cabang')->get();

        $targets = TargetKacab::where('tahun', $yearFilter)->get()->keyBy(function($item) {
            return strtoupper(trim($item->cabang));
        });

        $actuals = DB::table('insentif_value_per_salesmans')
            ->select('distributor_code', DB::raw('SUM(actual) as total_actual'))
            ->where('bulan', $bulan)
            ->groupBy('distributor_code')
            ->get()
            ->keyBy(function($item) {
                return strtoupper(trim($item->distributor_code));
            });

        $totalTarget = 0; $totalInsentif = 0; $totalSellOut = 0;
        $totalNilaiInsentif = 0; $totalPph = 0; $totalTrf = 0;

        foreach ($masterData as $md) {
            $cabang = strtoupper(trim($md->cabang));
            $distCode = strtoupper(trim($md->distributor_code));
            
            $targetData = $targets->get($cabang);
            $target = $targetData ? (float) $targetData->target : 0;
            $insentif = $targetData ? (float) $targetData->insentif : 0;
            $namaKacab = $targetData ? $targetData->nama_kacab : '-';

            $actualData = $actuals->get($distCode);
            $sellOut = $actualData ? (float) $actualData->total_actual : 0;

            $percentage = $target > 0 ? ($sellOut / $target) * 100 : 0;
            $nilaiInsentif = $percentage >= 100 ? $insentif : 0;
            $pph = $nilaiInsentif * 0.05;
            $trf = $nilaiInsentif - $pph;

            $this->kacabData[] = [
                'area_name' => $md->area_name,
                'distributor_name' => $md->distributor_name,
                'cabang' => $md->cabang,
                'nama_kacab' => $namaKacab,
                'target' => $target,
                'insentif' => $insentif,
                'sell_out' => $sellOut,
                'percentage' => $percentage,
                'nilai_insentif' => $nilaiInsentif,
                'pph' => $pph,
                'trf' => $trf,
            ];

            $totalTarget += $target; $totalInsentif += $insentif; $totalSellOut += $sellOut;
            $totalNilaiInsentif += $nilaiInsentif; $totalPph += $pph; $totalTrf += $trf;
        }

        $this->totals = [
            'target' => $totalTarget, 'insentif' => $totalInsentif, 'sell_out' => $totalSellOut,
            'nilai_insentif' => $totalNilaiInsentif, 'pph' => $totalPph, 'trf' => $totalTrf,
        ];
    }

    public function title(): string
    {
        return 'Kacab';
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->kacabData as $row) {
            $rows[] = [
                $no++,
                $row['area_name'],
                $row['distributor_name'],
                $row['cabang'],
                $row['nama_kacab'],
                $row['target'],
                $row['insentif'],
                $row['sell_out'],
                number_format($row['percentage'], 1, ',', '.') . '%',
                $row['nilai_insentif'],
                $row['pph'],
                $row['trf'],
            ];
        }

        // Add empty row
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', ''];

        // Add totals
        $totalPercentage = $this->totals['target'] > 0 ? ($this->totals['sell_out'] / $this->totals['target']) * 100 : 0;
        
        $rows[] = [
            '', '', '', '', 'GRAND TOTAL',
            $this->totals['target'],
            $this->totals['insentif'],
            $this->totals['sell_out'],
            number_format($totalPercentage, 1, ',', '.') . '%',
            $this->totals['nilai_insentif'],
            $this->totals['pph'],
            $this->totals['trf'],
        ];

        return $rows;
    }

    public function headings(): array
    {
        return [
            [
                'No', 'Area', 'Distributor', 'Cabang', 'Nama Kacab', 
                'Target', 'Insentif', 'PENCAPAIAN BULAN ' . mb_strtoupper($this->monthName),
                '', '', '', ''
            ],
            [
                '', '', '', '', '', 
                '', '', 'Sell Out', '%', 'Nilai Insentif', 'PPH 5%', 'TOTAL TRF'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->kacabData) + 4; // 2 headings + data + 1 empty + 1 grand total
        
        // Borders for all data
        $sheet->getStyle('A1:L' . ($lastRow - 2))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Borders for grand total
        $sheet->getStyle('E' . $lastRow . ':L' . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        return [
            // Center headers
            1    => ['alignment' => ['horizontal' => 'center', 'vertical' => 'center'], 'font' => ['bold' => true]],
            2    => ['alignment' => ['horizontal' => 'center', 'vertical' => 'center'], 'font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Merge cells for headers
                $event->sheet->getDelegate()->mergeCells('A1:A2');
                $event->sheet->getDelegate()->mergeCells('B1:B2');
                $event->sheet->getDelegate()->mergeCells('C1:C2');
                $event->sheet->getDelegate()->mergeCells('D1:D2');
                $event->sheet->getDelegate()->mergeCells('E1:E2');
                $event->sheet->getDelegate()->mergeCells('F1:F2');
                $event->sheet->getDelegate()->mergeCells('G1:G2');
                
                // Merge Super Header
                $event->sheet->getDelegate()->mergeCells('H1:L1');
                
                // Format numbers
                $lastRow = count($this->kacabData) + 4;
                $event->sheet->getStyle('F3:H' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
                $event->sheet->getStyle('J3:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
                
                // Colors
                $event->sheet->getStyle('F1:G2')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE2E8F0'); // Indigo-like
                
                $event->sheet->getStyle('H1:L1')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD1FAE5'); // Emerald-like
                    
                $event->sheet->getStyle('H2:L2')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFECFDF5'); // Light emerald
            },
        ];
    }
}
