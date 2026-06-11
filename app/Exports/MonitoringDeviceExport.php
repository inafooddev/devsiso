<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MonitoringDeviceExport implements FromView, WithDrawings, WithEvents, ShouldAutoSize
{
    protected $search;
    protected $filter_region;
    protected $filter_area;
    protected $filter_distributor;
    protected $start_month;
    protected $end_month;

    protected $drawings = [];
    protected $rowCount = 0;

    public function __construct($filters)
    {
        $this->search = $filters['search'];
        $this->filter_region = $filters['filter_region'];
        $this->filter_area = $filters['filter_area'];
        $this->filter_distributor = $filters['filter_distributor'];
        $this->start_month = $filters['start_month'];
        $this->end_month = $filters['end_month'];
    }

    public function view(): View
    {
        // 1. Query
        $masterQuery = DB::table('fsalesman as f')
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'f.KD')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->select([
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                'f.KD as distributor_code',
                'md.distributor_name',
                'md.branch_name',
                'f.SLSNO as sales_code',
                'f.SLSNAME as sales_name'
            ])
            ->where('f.TEAM', 'SEI')
            ->where('f.FLAG_ACTIVE', 'Y')
            ->where('f.FLAG_OFFICE', 'N')
            ->where('md.is_active', true);

        if (!empty($this->search)) {
            $masterQuery->where(function ($q) {
                $q->where('f.SLSNAME', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('f.SLSNO', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('md.distributor_name', 'ILIKE', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filter_region)) {
            $masterQuery->where('md.region_code', $this->filter_region);
        }

        if (!empty($this->filter_area)) {
            $masterQuery->where('md.area_code', $this->filter_area);
        }

        if (!empty($this->filter_distributor)) {
            $masterQuery->where('md.distributor_code', $this->filter_distributor);
        }

        $salesData = $masterQuery->get();

        // 2. Determine months
        $months = [];
        if ($this->start_month && $this->end_month) {
            $start = Carbon::createFromFormat('Y-m', $this->start_month)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $this->end_month)->startOfMonth();
            
            if ($start->gt($end)) {
                $temp = $start;
                $start = $end;
                $end = $temp;
            }

            if ($start->diffInMonths($end) > 12) {
                $start = $end->copy()->subMonths(11);
                $this->start_month = $start->format('Y-m');
            }

            $current = $start->copy();
            while ($current->lte($end)) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }
        }

        if (empty($months)) {
            $months = [Carbon::now()->format('Y-m')];
        }

        // 3. Get monitoring data
        $salesCodes = $salesData->pluck('sales_code')->toArray();
        $rawMonitoring = DB::table('monitoring_device_se')
            ->whereIn('sales_code', $salesCodes)
            ->get();

        $monitoringData = [];
        foreach ($rawMonitoring as $row) {
            if ($row->tanggal) {
                $monthKey = Carbon::parse($row->tanggal)->format('Y-m');
                $monitoringData[$row->distributor_code . '_' . $row->sales_code][$monthKey] = (array) $row;
            }
        }

        $monthHeaders = [];
        foreach ($months as $m) {
            $monthHeaders[$m] = Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y');
        }

        // 4. PREPARE DRAWINGS
        $rowNum = 3;
        foreach ($salesData as $row) {
            $startCol = 7; // G
            foreach ($months as $m) {
                $mData = $monitoringData[$row->distributor_code . '_' . $row->sales_code][$m] ?? null;
                
                if ($mData) {
                    if (!empty($mData['foto_tampak_depan']) && Storage::disk('public')->exists($mData['foto_tampak_depan'])) {
                        $path = storage_path('app/public/' . $mData['foto_tampak_depan']);
                        $drawing = new Drawing();
                        $drawing->setName('Foto Depan');
                        $drawing->setDescription('Foto Depan');
                        $drawing->setPath($path);
                        
                        $iWidth = 0; $iHeight = 0;
                        if (file_exists($path)) {
                            $info = @getimagesize($path);
                            if ($info) {
                                $iWidth = $info[0];
                                $iHeight = $info[1];
                            }
                        }
                        
                        if ($iWidth && $iHeight) {
                            $ratio = $iWidth / $iHeight;
                            if ($ratio > (120 / 80)) {
                                $drawing->setWidth(120);
                            } else {
                                $drawing->setHeight(80);
                            }
                        } else {
                            $drawing->setHeight(80);
                        }
                        
                        $drawing->setOffsetX(10);
                        $drawing->setOffsetY(10);
                        $drawing->setCoordinates(Coordinate::stringFromColumnIndex($startCol) . $rowNum);
                        $this->drawings[] = $drawing;
                    }
                    
                    if (!empty($mData['foto_tampak_belakang']) && Storage::disk('public')->exists($mData['foto_tampak_belakang'])) {
                        $path = storage_path('app/public/' . $mData['foto_tampak_belakang']);
                        $drawing = new Drawing();
                        $drawing->setName('Foto Belakang');
                        $drawing->setDescription('Foto Belakang');
                        $drawing->setPath($path);
                        
                        $iWidth = 0; $iHeight = 0;
                        if (file_exists($path)) {
                            $info = @getimagesize($path);
                            if ($info) {
                                $iWidth = $info[0];
                                $iHeight = $info[1];
                            }
                        }
                        
                        if ($iWidth && $iHeight) {
                            $ratio = $iWidth / $iHeight;
                            if ($ratio > (120 / 80)) {
                                $drawing->setWidth(120);
                            } else {
                                $drawing->setHeight(80);
                            }
                        } else {
                            $drawing->setHeight(80);
                        }
                        
                        $drawing->setOffsetX(10);
                        $drawing->setOffsetY(10);
                        $drawing->setCoordinates(Coordinate::stringFromColumnIndex($startCol + 1) . $rowNum);
                        $this->drawings[] = $drawing;
                    }
                }
                $startCol += 4;
            }
            $rowNum++;
        }
        $this->rowCount = $rowNum - 1;

        return view('exports.monitoring-device', [
            'salesData' => $salesData,
            'months' => $months,
            'monthHeaders' => $monthHeaders,
            'monitoringData' => $monitoringData,
        ]);
    }

    public function drawings()
    {
        return $this->drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set fixed row heights for data rows
                for ($row = 3; $row <= $this->rowCount; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(80);
                }
                
                // Adjust header row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(25);
                
                // Set explicit column widths for image columns
                $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
                for ($col = 7; $col <= $highestColIndex; $col++) {
                    // Column index arithmetic for the dynamic months
                    // StartCol=7 (Depan), 8 (Belakang), 9 (HP), 10 (Kartu)
                    // Col 7 % 4 == 3 => Depan
                    // Col 8 % 4 == 0 => Belakang
                    if ($col % 4 == 3 || $col % 4 == 0) {
                        $colStr = Coordinate::stringFromColumnIndex($col);
                        $sheet->getColumnDimension($colStr)->setWidth(22); 
                    }
                }
            }
        ];
    }
}
