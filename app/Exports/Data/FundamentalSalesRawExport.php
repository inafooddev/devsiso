<?php

namespace App\Exports\Data;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FundamentalSalesRawExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $month; // Format YYYY-MM

    public function __construct($month)
    {
        $this->month = $month;
    }

    public function collection()
    {
        // Extract year and month
        $year = substr($this->month, 0, 4);
        $monthNum = substr($this->month, 5, 2);

        return DB::table('ipm_fundamental_sales')
            ->whereYear('bulan', $year)
            ->whereMonth('bulan', $monthNum)
            ->select(
                'bulan',
                'cabang',
                'target',
                'selling_out',
                'jumlah_se',
                'pc',
                'ac',
                'ec',
                'ro',
                'ao',
                'sku',
                'nota',
                'potensi_rwo',
                'capai_rwo'
            )
            ->get();
            
        $data->transform(function ($item) {
            $item->bulan = substr($item->bulan, 0, 10);
            return $item;
        });
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Cabang',
            'Target SO',
            'Selling Out',
            'Jumlah SE',
            'PC',
            'AC',
            'EC',
            'RO',
            'AO',
            'Total SKU',
            'Total Nota',
            'Potensi RWO',
            'Capai RWO'
        ];
    }

    public function title(): string
    {
        return 'Raw Fundamental Sales';
    }
}
