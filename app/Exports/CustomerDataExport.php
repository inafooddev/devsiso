<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths; // 1. Import Interface ini
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class CustomerDataExport implements FromQuery, WithHeadings, WithColumnFormatting, WithMapping, WithColumnWidths, WithTitle
{
    use Exportable;

    protected $month;
    protected $regions;

    public function __construct(string $month, array $regions)
    {
        $this->month = $month;
        $this->regions = $regions;
    }

    public function title(): string
    {
        return 'DATA 01';
    }

    public function query()
    {
        // Menentukan range tanggal berdasarkan bulan yang dipilih
        $startDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromFormat('Y-m', $this->month)->endOfMonth()->format('Y-m-d');

        // Subquery 1: RP (ro_penjualan)
        $rpSub = DB::table('ro_penjualan')
            ->select('kd_distributor', 'kd_toko', DB::raw('min(nama_toko) as nama_toko'))
            ->groupBy('kd_distributor', 'kd_toko');

        // Subquery 2: DIE (distributor_implementasi_eskalink)
        $dieSub = DB::table('distributor_implementasi_eskalink')
            ->select('*')
            ->distinct('distributor_code')
            ->orderBy('distributor_code');

        // Main Query
        return DB::table('customer_mappings as cm')
            ->select(
                'die.eskalink_code_dist as Kode Distributor',
                'die.eskalink_code_dist as Kode Cabang Distributor',
                'cm.customer_code_dist as Kode Customer Distributor',
                'rp.nama_toko as Nama Customer Distributor',
                'die.eskalink_code as Kode Cabang',
                'cm.customer_code_prc as Customer Code',
                'rp.nama_toko as Customer Name'
            )
            ->leftJoinSub($rpSub, 'rp', function ($join) {
                $join->on('cm.distributor_code', '=', 'rp.kd_distributor')
                    ->on('cm.customer_code_dist', '=', 'rp.kd_toko');
            })
            ->leftJoinSub($dieSub, 'die', function ($join) {
                $join->on('die.distributor_code', '=', 'cm.distributor_code');
            })
            ->leftJoin('master_distributors as md', 'cm.distributor_code', '=', 'md.distributor_code')
            ->whereIn('md.region_code', $this->regions)
            ->whereBetween('cm.bln', [$startDate, $endDate])
            ->where('die.implementasi', 'Y')
            ->whereNotNull('rp.nama_toko')
            ->orderBy('cm.distributor_code')
            ->orderBy('cm.customer_code_dist');
    }

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
            (string) $row->{'Kode Distributor'},
            (string) $row->{'Kode Cabang Distributor'},
            (string) $row->{'Kode Customer Distributor'},
            (string) $row->{'Nama Customer Distributor'},
            (string) $row->{'Kode Cabang'},
            (string) $row->{'Customer Code'},
            (string) $row->{'Customer Name'},
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
        ];
    }

    /**
     * 2. Implementasi method columnWidths untuk mengatur lebar kolom.
     * Angka merepresentasikan jumlah karakter (kurang lebih).
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Kode Distributor
            'B' => 25, // Kode Cabang Distributor
            'C' => 25, // Kode Customer Distributor
            'D' => 45, // Nama Customer Distributor (Lebih Lebar)
            'E' => 15, // Kode Cabang
            'F' => 20, // Customer Code
            'G' => 45, // Customer Name (Lebih Lebar)
        ];
    }
}
