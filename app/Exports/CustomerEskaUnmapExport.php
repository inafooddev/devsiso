<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomerEskaUnmapExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    use Exportable;

    protected $month;
    protected $regions;
    protected $areas;
    protected $distributors;
    protected $search; // Tambahkan properti search

    // Terima parameter search di constructor
    public function __construct($month, array $regions, array $areas, array $distributors, $search = '')
    {
        $this->month = $month;
        $this->regions = $regions;
        $this->areas = $areas;
        $this->distributors = $distributors;
        $this->search = $search;
    }

    public function title(): string
    {
        return 'DATA 01';
    }

    public function query()
    {
        $query = DB::table('customer_map_eska as cme')
            ->select(
                'cme.bln',
                'md.region_name',
                'md.area_name',
                'cme.distid',
                'cme.branch_dist',
                'cme.custno_dist',
                'cde.custname as dist_cust_name',
                'cme.branch',
                'cme.custno',
                'cpe.custname as prc_cust_name'
            )
            ->leftJoin('customer_dist_eska as cde', function ($join) {
                $join->on('cme.distid', '=', 'cde.distid')
                    ->on('cme.branch_dist', '=', 'cde.branch')
                    ->on('cme.custno_dist', '=', 'cde.custno');
            })
            ->leftJoin('customer_prc_eska as cpe', function ($join) {
                $join->on('cme.branch', '=', 'cpe.kodecabang')
                    ->on('cme.custno', '=', 'cpe.custno');
            })
            ->leftJoin('distributor_implementasi_eskalink as die', function ($join) {
                $join->on('cme.distid', '=', 'die.eskalink_code_dist')
                    ->on('cme.branch_dist', '=', 'die.eskalink_code_dist')
                    ->on('cme.branch', '=', 'die.eskalink_code');
            })
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->where('cme.bln', $this->month);

        // --- TAMBAHKAN LOGIKA FILTER DI SINI ---

        if (!empty($this->regions)) {
            $query->whereIn('md.region_code', $this->regions);
        }

        if (!empty($this->areas)) {
            $query->whereIn('md.area_code', $this->areas);
        }

        if (!empty($this->distributors)) {
            $query->whereIn('md.distributor_code', $this->distributors);
        }

        // Logika Search (agar sama persis dengan tampilan tabel)
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('cme.custno_dist', 'ilike', '%' . $this->search . '%')
                    ->orWhere('cme.custno', 'ilike', '%' . $this->search . '%')
                    ->orWhere('cde.custname', 'ilike', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('cme.distid', 'asc');
    }

    // ... (sisa method headings, map, columnFormats tetap sama)
    public function headings(): array
    {
        return [
            "Kode Distributor",
            "Kode Cabang Distributor",
            "Kode Customer Distributor",
            "Nama Customer Distributor",
            "Kode Cabang",
            "Customer Code",
            "Customer Name"
        ];
    }

    public function map($row): array
    {
        return [
            $row->distid,
            $row->branch_dist,
            $row->custno_dist,
            $row->dist_cust_name,
            $row->branch,
            $row->custno,
            $row->prc_cust_name,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
