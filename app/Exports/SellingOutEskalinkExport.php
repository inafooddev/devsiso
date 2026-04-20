<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Carbon\Carbon;

class SellingOutEskalinkExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $regions;
    protected $areas;
    protected $distributors;
    protected $startDate;
    protected $endDate;

    public function __construct($regions, $areas, $distributors, $month)
    {
        $this->regions = $regions;
        $this->areas = $areas;
        $this->distributors = $distributors;

        $this->startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->format('Y-m-d');
    }

    public function query()
    {
        $query = DB::table('selling_out_eskalink as soe')
            ->select(
                'soe.region_code',
                'soe.region_name',
                'soe.entity_code',
                'soe.entity_name',
                'soe.branch_code',
                'soe.branch_name',
                DB::raw('count(soe.area_code) as row_count'),
                DB::raw('sum(soe.qty3_pcs) as qty_pcs'),
                DB::raw('sum(soe.gross_amount) as gross'),
                DB::raw('sum(soe.line_discount_4) as ld4'),
                DB::raw('sum(soe.line_discount_8) as bb'),
                DB::raw('sum(soe.dpp) as dpp'),
                DB::raw('sum(soe.tax) as tax'),
                DB::raw('sum(soe.nett_amount) as nett_amount')
            )
            ->leftJoin('distributor_implementasi_eskalink as die', 'soe.branch_code', '=', 'die.eskalink_code')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'die.distributor_code')
            ->whereBetween('soe.invoice_date', [$this->startDate, $this->endDate]);

        // Filter Multi-Select
        if (!empty($this->regions)) {
            $query->whereIn('md.region_code', $this->regions);
        }
        if (!empty($this->areas)) {
            $query->whereIn('md.area_code', $this->areas);
        }
        if (!empty($this->distributors)) {
            $query->whereIn('md.distributor_code', $this->distributors);
        }

        return $query->groupBy(
            'soe.region_code',
            'soe.region_name',
            'soe.entity_code',
            'soe.entity_name',
            'soe.branch_code',
            'soe.branch_name'
        )
            ->orderBy('soe.branch_name');
    }

    public function headings(): array
    {
        return [
            'Region Code',
            'Region Name',
            'Entity Code',
            'Entity Name',
            'Branch Code',
            'Branch Name',
            'Total Row',
            'Qty (Pcs)',
            'Gross Amount',
            'Line Disc 4',
            'Line Disc 8 (BB)',
            'DPP',
            'Tax',
            'Nett Amount'
        ];
    }

    public function map($row): array
    {
        return (array) $row;
    }
}
