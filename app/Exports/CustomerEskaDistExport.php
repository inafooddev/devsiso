<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;

class CustomerEskaDistExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
        // Query sesuai permintaan
        return DB::table('customer_dist_eska as cde')
            ->select(
                'md.region_name',
                'md.area_name',
                'cde.distid',
                'cde.branch',
                'md.distributor_name',
                'cde.custno',
                'cde.custname'
            )
            ->leftJoin('distributor_implementasi_eskalink as die', 'cde.distid', '=', 'die.eskalink_code_dist')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->where('md.region_code', $this->region)
            ->where('md.area_code', $this->area)
            ->where('md.distributor_code', $this->distributor)
            ->orderBy('cde.custname');
    }

    public function headings(): array
    {
        return [
            'Region Name',
            'Area Name',
            'Dist ID',
            'Branch',
            'Distributor Name',
            'Cust No',
            'Cust Name'
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_name,
            $row->area_name,
            $row->distid,
            $row->branch,
            $row->distributor_name,
            (string) $row->custno, // Memaksa format text untuk mencegah hilangnya 0 di depan
            $row->custname,
        ];
    }
}