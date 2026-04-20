<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Carbon\Carbon;

class CustomerCsvExport implements FromQuery, WithHeadings, WithCustomCsvSettings, WithEvents, WithMapping
{
    use Exportable;

    protected $month;
    protected $regions;
    protected $areas;
    protected $distributors;
    protected $timestamp;

    // Update Constructor untuk menerima Filter Area dan Distributor
    public function __construct(string $month, array $regions, array $areas, array $distributors, ?Carbon $timestamp = null)
    {
        $this->month = $month;
        $this->regions = $regions;
        $this->areas = $areas;
        $this->distributors = $distributors;
        $this->timestamp = $timestamp ?: Carbon::now();
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';', // Dummy delimiter
            'enclosure' => '',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
        ];
    }

    public function headings(): array
    {
        return [
            "00|PDAMASTER|" . $this->timestamp->format('Ymd') . "|" . $this->timestamp->format('H:i:s') . "|ADMIN"
        ];
    }

    public function map($row): array
    {
        return [
            implode('|', (array) $row)
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastRow = $event->sheet->getDelegate()->getHighestRow();
                $event->sheet->getDelegate()->setCellValue('A' . ($lastRow + 1), '//END');
            },
        ];
    }

    public function query()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromFormat('Y-m', $this->month)->endOfMonth()->format('Y-m-d');

        // Main Query sesuai permintaan terbaru
        $query = DB::table('customer_prc_eska as cpe')
            ->select(
                DB::raw("'01' as col1"),
                DB::raw("'MCUST' as col2"),
                'cpe.custno',
                DB::raw("'' as col4"),
                'cpe.custname',
                'cpe.custadd1',
                DB::raw("'' as col7"),
                'cpe.ccity',
                DB::raw("'' as col9"),
                DB::raw("'' as col10"),
                DB::raw("'' as col11"),
                DB::raw("'014' as col12"),
                DB::raw("'' as col13"),
                DB::raw("'' as col14"),
                DB::raw("'' as col15"),
                DB::raw("'GT04' as col16"),
                DB::raw("'GT' as col17"),
                'cpe.gharga',
                DB::raw("'K' as col19"),
                DB::raw("'C' as col20"),
                DB::raw("'' as col21"),
                DB::raw("'' as col22"),
                DB::raw("'' as col23"),
                DB::raw("'' as col24"),
                DB::raw("'' as col25"),
                DB::raw("'' as col26"),
                DB::raw("'' as col27"),
                DB::raw("'' as col28"),
                DB::raw("'GT04' as col29"),
                DB::raw("'' as col30"),
                'cpe.kodecabang',
                DB::raw("'' as col32"),
                DB::raw("'' as col33")
            )
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'cpe.kodecabang')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->whereBetween('cpe.bln', [$startDate, $endDate]);

        // Filter Region (Jika tidak kosong/pilih semua)
        if (!empty($this->regions)) {
            $query->whereIn('md.region_code', $this->regions);
        }

        // Filter Area (Jika tidak kosong/pilih semua)
        if (!empty($this->areas)) {
            $query->whereIn('md.area_code', $this->areas);
        }

        // Filter Distributor (Jika tidak kosong/pilih semua)
        if (!empty($this->distributors)) {
            $query->whereIn('md.distributor_code', $this->distributors);
        }
        $query->orderBy('cpe.custno');

        return $query;
    }
}
