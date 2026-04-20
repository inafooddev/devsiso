<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;

class CustomerEskaMapExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $region;
    protected $area;
    protected $distributor;

    public function __construct($region, $area, $distributor)
    {
        $this->region = $region;
        $this->area = $area;
        $this->distributor = $distributor;
    }

    public function query()
    {
        return DB::table('customer_map_eska as cme')
            ->select(
                'md.region_name',
                'md.area_name',
                'cme.distid',
                'cme.branch_dist',
                'cme.custno_dist',
                'cde.custname as dist_cust_name', // Alias agar tidak bentrok
                'cme.branch',
                'cme.custno',
                'cpe.custname as prc_cust_name'  // Alias agar tidak bentrok
            )
            // Join 1: Customer Dist Eska
            ->leftJoin('customer_dist_eska as cde', function ($join) {
                $join->on('cme.distid', '=', 'cde.distid')
                    ->on('cme.branch_dist', '=', 'cde.branch')
                    ->on('cme.custno_dist', '=', 'cde.custno');
            })
            // Join 2: Customer PRC Eska
            ->leftJoin('customer_prc_eska as cpe', function ($join) {
                $join->on('cme.branch', '=', 'cpe.kodecabang')
                    ->on('cme.custno', '=', 'cpe.custno');
            })
            // Join 3: Distributor Implementasi Eskalink (Complex Join)
            ->leftJoin('distributor_implementasi_eskalink as die', function ($join) {
                $join->on('cme.distid', '=', 'die.eskalink_code_dist')
                    ->on('cme.branch_dist', '=', 'die.eskalink_code_dist')
                    ->on('cme.branch', '=', 'die.eskalink_code');
            })
            // Join 4: Master Distributors
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            // Filter
            ->where('md.region_code', $this->region)
            ->where('md.area_code', $this->area)
            ->where('md.distributor_code', $this->distributor)
            ->orderBy('cme.custno_dist');
    }

    public function headings(): array
    {
        return [
            'Region',
            'Area',
            'Dist ID',
            'Branch Dist',
            'Cust No Dist',
            'Cust Name Dist',
            'Branch PRC',
            'Cust No PRC',
            'Cust Name PRC'
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_name,
            $row->area_name,
            $row->distid,
            $row->branch_dist,
            (string) $row->custno_dist,
            $row->dist_cust_name,
            $row->branch,
            (string) $row->custno,
            $row->prc_cust_name,
        ];
    }
}
