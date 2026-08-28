<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitoringTopItemExport implements FromArray, WithEvents
{
    protected $filters;
    protected $topItems;
    protected $kpiData;
    protected $detailData;
    protected $periodName;
    protected $totalTopItems;
    
    // Track row indices for styling
    protected $headerRowIndex = 10;
    protected $lastDataRowIndex = 10;

    public function __construct($filters)
    {
        $this->filters = $filters;
        $this->fetchData();
    }

    private function fetchData()
    {
        $month = $this->filters['month'];
        $year = $this->filters['year'];
        $filterRegion = $this->filters['filterRegion'] ?? '';
        $filterArea = $this->filters['filterArea'] ?? '';
        $filterSupervisor = $this->filters['filterSupervisor'] ?? '';
        $filterDistributor = $this->filters['filterDistributor'] ?? '';
        $filterBucket = $this->filters['filterBucket'] ?? null;

        $this->periodName = date('F Y', mktime(0, 0, 0, $month, 1, $year));
        $period = sprintf('%04d-%02d-01', $year, $month);

        $this->topItems = DB::table('master_produk_lama')
            ->whereIn('kategory', ['NPD', 'TOPITEM'])
            ->select('kategory', 'topitem')
            ->distinct()
            ->orderBy('kategory', 'desc')
            ->orderBy('topitem')
            ->get()
            ->toArray();
            
        $this->totalTopItems = count($this->topItems);

        $applyMasterFilters = function ($query) use ($filterRegion, $filterArea, $filterSupervisor, $filterDistributor) {
            if (!empty($filterRegion)) $query->where('md.region_name', $filterRegion);
            if (!empty($filterArea)) $query->where('md.area_name', $filterArea);
            if (!empty($filterDistributor)) $query->where('md.distributor_name', $filterDistributor);
            if (!empty($filterSupervisor)) $query->where('f.SLSNAME', $filterSupervisor);
        };

        // KPI Query
        $totalTokoQuery = DB::table('top_item_achievement as tia')
            ->leftJoin('master_distributors as md', 'tia.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code')
            ->where('tia.period', $period)
            ->where('tia.qty', '>', 0);
            
        $applyMasterFilters($totalTokoQuery);
        $totalTokoAll = $totalTokoQuery->count(DB::raw("DISTINCT CONCAT(tia.distributor_code, '-', tia.uniq_code)"));

        $kpiSubquery = DB::table('top_item_achievement as tia')
            ->leftJoin('master_distributors as md', 'tia.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code')
            ->leftJoin('master_produk_lama as mpl', 'tia.pcode_prc', '=', 'mpl.pcode_prc')
            ->where('tia.period', $period)
            ->whereIn('mpl.kategory', ['NPD', 'TOPITEM'])
            ->select('tia.distributor_code', 'tia.uniq_code', DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END) as prd_count'))
            ->groupBy('tia.distributor_code', 'tia.uniq_code');
            
        $applyMasterFilters($kpiSubquery);

        $this->kpiData = DB::query()
            ->fromSub($kpiSubquery, 'store_stats')
            ->select([
                DB::raw('SUM(CASE WHEN prd_count = 1 THEN 1 ELSE 0 END) as beli_1'),
                DB::raw('SUM(CASE WHEN prd_count = 2 THEN 1 ELSE 0 END) as beli_2'),
                DB::raw('SUM(CASE WHEN prd_count = 3 THEN 1 ELSE 0 END) as beli_3'),
                DB::raw('SUM(CASE WHEN prd_count = 4 THEN 1 ELSE 0 END) as beli_4'),
                DB::raw('SUM(CASE WHEN prd_count = 5 THEN 1 ELSE 0 END) as beli_5'),
                DB::raw('SUM(CASE WHEN prd_count = 6 THEN 1 ELSE 0 END) as beli_6')
            ])
            ->first();
            
        $this->kpiData->total_toko = $totalTokoAll;

        // Detail Table Query
        $selects = [
            'md.region_name',
            'md.area_name',
            'md.distributor_name',
            'f.SLSNAME as supervisor_name',
            'tia.uniq_code',
            'timc.customer_name',
            'timc.address',
            DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END) as total_prd_transaksi')
        ];

        foreach ($this->topItems as $index => $item) {
            $selects[] = DB::raw("SUM(CASE WHEN mpl.topitem = '" . addslashes($item->topitem) . "' THEN tia.qty ELSE 0 END) as prd" . ($index + 1) . "_qty");
        }

        $query = DB::table('top_item_achievement as tia')
            ->leftJoin('top_item_master_customer as timc', function($join) {
                $join->on('tia.distributor_code', '=', 'timc.distributor_code')
                     ->on('tia.uniq_code', '=', 'timc.uniq_code');
            })
            ->leftJoin('master_produk_lama as mpl', 'tia.pcode_prc', '=', 'mpl.pcode_prc')
            ->leftJoin('master_distributors as md', 'tia.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'tecm.team_elite_code')
            ->where('tia.period', $period)
            ->whereIn('mpl.kategory', ['NPD', 'TOPITEM']);
            
        $applyMasterFilters($query);

        $query->select($selects)
            ->groupBy(
                'md.region_name',
                'md.area_name',
                'md.distributor_name',
                'f.SLSNAME',
                'tia.uniq_code',
                'timc.customer_name',
                'timc.address'
            );

        if ($filterBucket !== null && $filterBucket !== '') {
            $query->having(DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END)'), '=', (int) $filterBucket);
        } else {
            $query->having(DB::raw('COUNT(DISTINCT CASE WHEN tia.qty > 0 THEN mpl.topitem END)'), '>', 0);
        }

        $query->orderBy('md.region_name', 'asc')
              ->orderBy('md.area_name', 'asc')
              ->orderBy('md.distributor_name', 'asc')
              ->orderBy('f.SLSNAME', 'asc');

        // Execute query (get all, no pagination)
        $this->detailData = $query->get();
    }

    public function array(): array
    {
        $rows = [];

        // --- TITLE & HEADER INFO ---
        $rows[] = ['MONITORING TOP ITEM & NPD'];
        $rows[] = ['Periode', ': ' . $this->periodName];
        $rows[] = ['Tanggal Tarik', ': ' . now()->format('d M Y H:i:s')];
        $rows[] = ['']; // Empty row

        // --- KPI SUMMARY ---
        $rows[] = ['SUMMARY KPI'];
        
        $kpiHeader = ['Total Toko Aktif'];
        $kpiValues = [$this->kpiData->total_toko];
        $kpiPercents = ['100%'];
        
        for ($i = 1; $i <= 6; $i++) {
            $kpiHeader[] = "Toko Beli {$i} Produk";
            
            $col = "beli_{$i}";
            $val = $this->kpiData->$col ?? 0;
            $kpiValues[] = $val;
            
            $pct = $this->kpiData->total_toko > 0 ? ($val / $this->kpiData->total_toko) : 0;
            $kpiPercents[] = $pct; // Send as pure float, will format in AfterSheet
        }

        $rows[] = $kpiHeader;
        $rows[] = $kpiValues;
        $rows[] = $kpiPercents;
        $rows[] = ['']; // Empty row
        
        // --- DETAIL TABLE ---
        $rows[] = ['DETAIL TRANSAKSI'];
        
        $tableHeader = [
            'Region',
            'Area',
            'Distributor',
            'Supervisor',
            'Kode Toko',
            'Nama Toko',
            'Alamat',
        ];
        
        foreach ($this->topItems as $item) {
            $tableHeader[] = $item->topitem . "\n(" . $item->kategory . ")";
        }
        $tableHeader[] = "Total Produk\nTransaksi";
        
        $rows[] = $tableHeader;
        
        // Data Rows
        foreach ($this->detailData as $data) {
            $row = [
                $data->region_name ?? '-',
                $data->area_name ?? '-',
                $data->distributor_name ?? '-',
                $data->supervisor_name ?? '-',
                $data->uniq_code,
                $data->customer_name ?? '-',
                $data->address ?? '-',
            ];
            
            foreach ($this->topItems as $index => $item) {
                $col = 'prd' . ($index + 1) . '_qty';
                $row[] = (float) $data->$col; // Float so Excel treats it as number
            }
            
            $row[] = (int) $data->total_prd_transaksi;
            $rows[] = $row;
        }

        $this->lastDataRowIndex = count($rows);

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // 1. Title Styling
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '2C3E50']
                    ]
                ]);
                $sheet->getStyle('A2:A3')->getFont()->setBold(true);

                // 2. KPI Summary Styling
                $sheet->mergeCells('A5:G5');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'EAECEE']
                    ]
                ]);
                
                // KPI Header
                $sheet->getStyle('A6:G6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '34495E'] // Dark Header
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ]);
                
                // KPI Values (A7:G8)
                $sheet->getStyle('A7:G8')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ]);
                
                $sheet->getStyle('A7:G7')->getFont()->setBold(true);
                $sheet->getStyle('A7:G7')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('A8:G8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

                // 3. Detail Table Styling
                $sheet->mergeCells("A10:G10");
                $sheet->getStyle('A10')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'EAECEE']
                    ]
                ]);
                
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $this->totalTopItems + 1);
                $headerRange = "A11:{$lastColLetter}11";
                
                // Freeze pane below header
                $sheet->freezePane('A12');
                
                // AutoFilter
                $sheet->setAutoFilter($headerRange);

                // Table Header
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '2980B9'] // Blue Header
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ]);
                $sheet->getRowDimension(11)->setRowHeight(30);

                // Data Rows Formatting
                if ($this->lastDataRowIndex >= 12) {
                    $dataRange = "A12:{$lastColLetter}{$this->lastDataRowIndex}";
                    
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP
                        ]
                    ]);
                    
                    // Format Qty columns (H to 2nd to last)
                    $firstQtyCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(8);
                    $lastQtyCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $this->totalTopItems);
                    
                    $sheet->getStyle("{$firstQtyCol}12:{$lastQtyCol}{$this->lastDataRowIndex}")
                          ->getNumberFormat()
                          ->setFormatCode('#,##0.##;-#,##0.##;"-"');
                          
                    $sheet->getStyle("{$firstQtyCol}12:{$lastQtyCol}{$this->lastDataRowIndex}")
                          ->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Format Total Produk Transaksi
                    $sheet->getStyle("{$lastColLetter}12:{$lastColLetter}{$this->lastDataRowIndex}")
                          ->getNumberFormat()
                          ->setFormatCode('#,##0');
                          
                    $sheet->getStyle("{$lastColLetter}12:{$lastColLetter}{$this->lastDataRowIndex}")
                          ->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                          ->setWrapText(true);
                          
                    $sheet->getStyle("{$lastColLetter}12:{$lastColLetter}{$this->lastDataRowIndex}")
                          ->getFont()
                          ->setBold(true);
                }

                // 4. Column Widths
                $sheet->getColumnDimension('A')->setWidth(18); // Region
                $sheet->getColumnDimension('B')->setWidth(20); // Area
                $sheet->getColumnDimension('C')->setWidth(30); // Distributor
                $sheet->getColumnDimension('D')->setWidth(25); // Supervisor
                $sheet->getColumnDimension('E')->setWidth(15); // Kode Toko
                $sheet->getColumnDimension('F')->setWidth(35); // Nama Toko
                $sheet->getColumnDimension('G')->setWidth(40); // Alamat
                
                $sheet->getStyle('G12:G' . $this->lastDataRowIndex)->getAlignment()->setWrapText(true);

                // Product Columns
                for ($i = 1; $i <= $this->totalTopItems; $i++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $i);
                    $sheet->getColumnDimension($col)->setWidth(15);
                }
                
                $sheet->getColumnDimension($lastColLetter)->setWidth(15); // Total Prd Transaksi
            }
        ];
    }
}
