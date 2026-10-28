<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SisoAnalyticsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $viewMode;
    protected $year;
    protected $month;
    protected $regFest;

    public function __construct($data, $viewMode, $year, $month, $regFest = 'ALL')
    {
        $this->data = $data;
        $this->viewMode = $viewMode;
        $this->year = $year;
        $this->month = $month;
        $this->regFest = $regFest;
    }

    public function collection()
    {
        if ($this->viewMode === 'product') {
            return $this->data->map(function ($row) {
                return [
                    'product_id' => $row->product_id,
                    'product_name' => $row->product_name,
                    'brand_name' => $row->brand_name,
                    'sub_brand_name' => $row->sub_brand_name,
                    'reg_fest' => $row->reg_fest ?? '-',
                    'qty_in_ktn' => $row->qty_in_ktn,
                    'qty_out_ktn' => $row->qty_out_ktn,
                    'gap_ktn' => $row->gap_qty ?? ($row->qty_in_ktn - $row->qty_out_ktn),
                    'value_in' => $row->value_in,
                    'value_out' => $row->value_out,
                    'gap_val' => $row->gap_val ?? ($row->value_in - $row->value_out),
                    'ratio' => $row->ratio . '%',
                    'status' => strtoupper($row->status_label ?? $row->status),
                    'action' => $row->analysis_narrative ?? '-',
                ];
            });
        } else {
            return $this->data->map(function ($row) {
                return [
                    'distributor_code' => $row->distributor_code,
                    'distributor_name' => $row->distributor_name,
                    'region_name' => $row->region_name,
                    'area_name' => $row->area_name,
                    'branch_name' => $row->branch_name,
                    'reg_fest' => $this->regFest,
                    'qty_in_ktn' => $row->qty_in_ktn,
                    'qty_out_ktn' => $row->qty_out_ktn,
                    'gap_ktn' => $row->gap_qty ?? ($row->qty_in_ktn - $row->qty_out_ktn),
                    'value_in' => $row->value_in,
                    'value_out' => $row->value_out,
                    'target_val' => $row->target_val ?? 0,
                    'ach_in_pct' => ($row->ach_in_pct !== null) ? $row->ach_in_pct . '%' : '-',
                    'gap_val' => $row->gap_val ?? ($row->value_in - $row->value_out),
                    'ratio' => $row->ratio . '%',
                    'status' => strtoupper($row->status_label ?? $row->status),
                    'action' => $row->analysis_narrative ?? '-',
                ];
            });
        }
    }

    public function headings(): array
    {
        if ($this->viewMode === 'product') {
            return [
                'Kode Produk',
                'Nama Produk',
                'Brand',
                'Sub Brand',
                'Tipe (Reg/Fest)',
                'Qty In (Ktn)',
                'Qty Out (Ktn)',
                'Gap Qty (Ktn)',
                'Value In (Rp)',
                'Value Out (Rp)',
                'Gap Value (Rp)',
                'Rasio Out / In (%)',
                'Status',
                'Keterangan Analisa',
            ];
        } else {
            return [
                'Kode Distributor',
                'Nama Distributor',
                'Region',
                'Area',
                'Cabang',
                'Tipe (Reg/Fest)',
                'Qty In (Ktn)',
                'Qty Out (Ktn)',
                'Gap Qty (Ktn)',
                'Value In (Rp)',
                'Value Out (Rp)',
                'Target Depo (' . ($this->regFest === 'ALL' ? 'REG+FEST' : $this->regFest) . ') (Rp)',
                '% Ach Target',
                'Gap Value (Rp)',
                'Rasio Out / In (%)',
                'Status',
                'Keterangan Analisa',
            ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Q1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
