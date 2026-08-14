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
use App\Livewire\Others\Insentif\Perhitungan\InsentifSe;
use Carbon\Carbon;

class InsentifSeSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithTitle
{
    protected $bulan;
    protected $region;
    protected $areas;
    
    protected $salesmenData = [];
    protected $headers = [];
    protected $monthName;

    // Grand totals
    protected $grandTotals = [];
    protected $grandTotalValue = [];
    protected $grandTotalVtkp = 0;
    protected $grandTotalEc = [];
    protected $grandTotalIpt = [];
    protected $grandTotalSfa = [];
    protected $grandTotalKeseluruhan = 0;
    protected $grandTotalPph = 0;
    protected $grandTotalThp = 0;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->bulan = $bulan;
        $this->region = $region;
        $this->areas = $areas;
        $this->monthName = Carbon::parse($bulan . '-01')->translatedFormat("F'y");
        
        $this->fetchData();
    }
    
    protected function fetchData()
    {
        $component = app(InsentifSe::class);
        $component->filterBulan = $this->bulan;
        $component->filterRegion = $this->region;
        $component->filterArea = $this->areas;
        
        $view = $component->render();
        $data = $view->getData();
        
        $this->salesmenData = $data['salesmenData'] ?? [];
        $this->headers = $data['headers'] ?? [];
        
        $this->grandTotals = $data['grandTotals'] ?? [];
        $this->grandTotalValue = $data['grandTotalValue'] ?? [];
        $this->grandTotalVtkp = $data['grandTotalVtkp'] ?? 0;
        $this->grandTotalEc = $data['grandTotalEc'] ?? [];
        $this->grandTotalIpt = $data['grandTotalIpt'] ?? [];
        $this->grandTotalSfa = $data['grandTotalSfa'] ?? [];
        $this->grandTotalKeseluruhan = $data['grandTotalKeseluruhan'] ?? 0;
        $this->grandTotalPph = $data['grandTotalPph'] ?? 0;
        $this->grandTotalThp = $data['grandTotalThp'] ?? 0;
    }
    
    public function title(): string
    {
        return 'SE';
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->salesmenData as $s) {
            $row = [];
            $row[] = $no++;
            $row[] = $s['kode_se'];
            $row[] = $s['nama_se'];
            $row[] = $s['area'];
            $row[] = $s['kd_dist'];
            $row[] = $s['distributor'];
            $row[] = $s['cabang'];
            
            // Value
            $row[] = $s['value_target'] ?? 0;
            $row[] = $s['value_real'] ?? 0;
            $row[] = ($s['value_ach'] ?? 0) . '%';
            $row[] = $s['value_insentif'] ?? 0;
            
            // VTKP
            foreach ($this->headers as $h) {
                $ach = $s['achievements'][$h->nama_header] ?? ['target'=>0, 'real'=>0, 'growth'=>0, 'insentif'=>0];
                $row[] = $ach['target'];
                $row[] = $ach['real'];
                $row[] = $ach['growth'] . '%';
                $row[] = $ach['insentif'];
            }
            $row[] = $s['total_insentif_vtkp'] ?? 0;
            
            // EC
            $row[] = $s['ro'] ?? 0;
            $row[] = $s['ac'] ?? 0;
            $row[] = $s['ec'] ?? 0;
            $row[] = ($s['persen_ec'] ?? 0) . '%';
            $row[] = $s['ec_harian'] ?? 0;
            $row[] = $s['insentif_ec'] ?? 0;
            
            // IPT
            $row[] = $s['ipt_sku'] ?? 0;
            $row[] = $s['ipt_ec'] ?? 0;
            $row[] = number_format($s['ipt'] ?? 0, 1, ',', '.');
            $row[] = $s['insentif_ipt'] ?? 0;
            
            // SFA
            $row[] = $s['sfa_pc'] ?? 0;
            $row[] = $s['sfa_ac'] ?? 0;
            $row[] = ($s['sfa_persen'] ?? 0) . '%';
            
            // Grand Total
            $row[] = $s['total_insentif'] ?? 0;
            $row[] = $s['pph_5'] ?? 0;
            $row[] = $s['thp'] ?? 0;
            
            $rows[] = $row;
        }

        // Add empty row
        $emptyRow = array_fill(0, 7 + 4 + (count($this->headers) * 4) + 1 + 6 + 4 + 3 + 3, '');
        $rows[] = $emptyRow;

        // Add Grand Total row
        if (!empty($this->salesmenData)) {
            $gtRow = array_fill(0, count($emptyRow), '');
            $gtRow[0] = ''; $gtRow[1] = ''; $gtRow[2] = 'GRAND TOTAL';
            
            $gtRow[7] = $this->grandTotalValue['target'] ?? 0;
            $gtRow[8] = $this->grandTotalValue['real'] ?? 0;
            $gtRow[9] = ($this->grandTotalValue['ach'] ?? 0) . '%';
            $gtRow[10] = $this->grandTotalValue['insentif'] ?? 0;
            
            $colIdx = 11;
            foreach ($this->headers as $h) {
                $ach = $this->grandTotals[$h->nama_header] ?? ['target'=>0, 'real'=>0, 'growth'=>0, 'insentif'=>0];
                $gtRow[$colIdx++] = $ach['target'];
                $gtRow[$colIdx++] = $ach['real'];
                $gtRow[$colIdx++] = $ach['growth'] . '%';
                $gtRow[$colIdx++] = $ach['insentif'];
            }
            
            $gtRow[$colIdx++] = $this->grandTotalVtkp;
            
            $gtRow[$colIdx++] = $this->grandTotalEc['ro'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalEc['ac'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalEc['ec'] ?? 0;
            $gtRow[$colIdx++] = ($this->grandTotalEc['persen_ec'] ?? 0) . '%';
            $gtRow[$colIdx++] = $this->grandTotalEc['ec_harian'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalEc['insentif'] ?? 0;
            
            $gtRow[$colIdx++] = $this->grandTotalIpt['sku'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalIpt['ec'] ?? 0;
            $gtRow[$colIdx++] = number_format($this->grandTotalIpt['ipt'] ?? 0, 1, ',', '.');
            $gtRow[$colIdx++] = $this->grandTotalIpt['insentif'] ?? 0;
            
            $gtRow[$colIdx++] = $this->grandTotalSfa['pc'] ?? 0;
            $gtRow[$colIdx++] = $this->grandTotalSfa['ac'] ?? 0;
            $gtRow[$colIdx++] = ($this->grandTotalSfa['persen'] ?? 0) . '%';
            
            $gtRow[$colIdx++] = $this->grandTotalKeseluruhan;
            $gtRow[$colIdx++] = $this->grandTotalPph;
            $gtRow[$colIdx++] = $this->grandTotalThp;
            
            $rows[] = $gtRow;
        }

        return $rows;
    }

    public function headings(): array
    {
        $h1 = [
            'No', 'SE Code', 'SE Name', 'Area', 'Dist Code', 'Distributor Name', 'Cabang',
            'PENCAPAIAN BULAN ' . mb_strtoupper($this->monthName), '', '', ''
        ];
        
        foreach ($this->headers as $h) {
            $h1[] = $h->nama_header;
            $h1[] = ''; $h1[] = ''; $h1[] = '';
        }
        $h1[] = 'TOTAL INSENTIF VTKP';
        
        $h1[] = 'EC'; $h1[] = ''; $h1[] = ''; $h1[] = ''; $h1[] = ''; $h1[] = '';
        $h1[] = 'IPT'; $h1[] = ''; $h1[] = ''; $h1[] = '';
        $h1[] = 'SFA'; $h1[] = ''; $h1[] = '';
        
        $h1[] = 'Total Insentif Keseluruhan';
        $h1[] = 'PPH 5%';
        $h1[] = 'THP (TOTAL TRF)';

        $h2 = [
            '', '', '', '', '', '', '',
            'Target (Rp)', 'Aktual (Rp)', 'Ach', 'Insentif'
        ];
        
        foreach ($this->headers as $h) {
            $h2[] = 'Target'; $h2[] = 'Real'; $h2[] = 'Growth %'; $h2[] = 'Insentif';
        }
        $h2[] = '';
        
        $h2[] = 'RO'; $h2[] = 'AC'; $h2[] = 'EC'; $h2[] = '% EC'; $h2[] = 'EC HARIAN'; $h2[] = 'Insentif';
        $h2[] = 'SKU'; $h2[] = 'EC'; $h2[] = 'IPT'; $h2[] = 'Insentif';
        $h2[] = 'PC'; $h2[] = 'AC'; $h2[] = '%';
        
        $h2[] = ''; $h2[] = ''; $h2[] = '';

        return [$h1, $h2];
    }

    public function styles(Worksheet $sheet)
    {
        $cols = count($this->headings()[0]);
        $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols);
        $lastRow = count($this->salesmenData) > 0 ? (count($this->array()) + 2) : 2;
        
        $sheet->getStyle('A1:' . $highestColumn . ($lastRow - 2))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);
        
        $sheet->getStyle('A' . $lastRow . ':' . $highestColumn . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        return [
            1 => ['alignment' => ['horizontal' => 'center', 'vertical' => 'center'], 'font' => ['bold' => true]],
            2 => ['alignment' => ['horizontal' => 'center', 'vertical' => 'center'], 'font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Merge common headers
                $event->sheet->getDelegate()->mergeCells('A1:A2');
                $event->sheet->getDelegate()->mergeCells('B1:B2');
                $event->sheet->getDelegate()->mergeCells('C1:C2');
                $event->sheet->getDelegate()->mergeCells('D1:D2');
                $event->sheet->getDelegate()->mergeCells('E1:E2');
                $event->sheet->getDelegate()->mergeCells('F1:F2');
                $event->sheet->getDelegate()->mergeCells('G1:G2');
                $event->sheet->getDelegate()->mergeCells('H1:K1'); // Pencapaian Bulan
                
                $colIdx = 12; // L
                foreach ($this->headers as $h) {
                    $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                    $event->sheet->getDelegate()->mergeCells($startCol . '1:' . $endCol . '1');
                    $colIdx += 4;
                }
                
                // TOTAL INSENTIF VTKP
                $totalVtkpCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $event->sheet->getDelegate()->mergeCells($totalVtkpCol . '1:' . $totalVtkpCol . '2');
                $colIdx++;
                
                // EC
                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 5);
                $event->sheet->getDelegate()->mergeCells($startCol . '1:' . $endCol . '1');
                $colIdx += 6;
                
                // IPT
                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                $event->sheet->getDelegate()->mergeCells($startCol . '1:' . $endCol . '1');
                $colIdx += 4;
                
                // SFA
                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 2);
                $event->sheet->getDelegate()->mergeCells($startCol . '1:' . $endCol . '1');
                $colIdx += 3;
                
                // Totals
                for ($i = 0; $i < 3; $i++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + $i);
                    $event->sheet->getDelegate()->mergeCells($col . '1:' . $col . '2');
                }
                
                // Format numbers
                $lastRow = count($this->salesmenData) > 0 ? (count($this->array()) + 2) : 2;
                $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 2);
                $event->sheet->getStyle('H3:' . $highestCol . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}