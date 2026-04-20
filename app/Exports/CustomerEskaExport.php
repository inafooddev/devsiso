<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;

class CustomerEskaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
        return DB::table('customer_prc_eska as cpe')
            ->select(
                'md.region_name',
                'md.area_name',
                'cpe.kodecabang',
                'md.distributor_name',
                'cpe.custno',
                'cpe.custname',
                'cpe.custadd1',
                'cpe.ccity',
                'cpe.cterm',
                'cpe.typeout',
                'cpe.grupout',
                'cpe.gharga',
                'cpe.flagpay',
                'cpe.flagout',
                'cpe.kodecabang'
            )
            ->leftJoin('distributor_implementasi_eskalink as die', 'cpe.kodecabang', '=', 'die.eskalink_code')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->where('md.region_code', $this->region)
            ->where('md.area_code', $this->area)
            ->where('md.distributor_code', $this->distributor)
            ->orderBy('cpe.custname');
    }

    public function headings(): array
    {
        return [
            'Region Name',
            'Area Name',
            'Kode Cabang',
            'Distributor Name',
            'Cust No',
            'Cust Name',
            'Address',
            'City',
            'TOP',
            'Type',
            'Group',
            'Price Group',
            'Payment Flag',
            'Outlet Flag',
            'Kode Cabang Eska'
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_name,
            $row->area_name,
            $row->kodecabang,
            $row->distributor_name,
            (string) $row->custno, // Memastikan format text agar tidak dianggap angka
            $row->custname,
            $row->custadd1,
            $row->ccity,
            $row->cterm,
            $row->typeout,
            $row->grupout,
            $row->gharga,
            $row->flagpay,
            $row->flagout,
            $row->kodecabang
        ];
    }
}
