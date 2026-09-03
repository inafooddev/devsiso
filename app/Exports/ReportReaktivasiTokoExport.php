<?php

namespace App\Exports;

use App\Models\ReportReaktivasiToko;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportReaktivasiTokoExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;
    protected $user;
    protected $rowNumber = 0;

    public function __construct(array $filters, $user)
    {
        $this->filters = $filters;
        $this->user = $user;
    }

    public function query()
    {
        $selectedDate = Carbon::createFromDate($this->filters['tahun'] ?: date('Y'), $this->filters['bulan'] ?: date('m'), 1);
        $monthStart = $selectedDate->copy()->startOfMonth()->format('Y-m-d');
        $monthEnd = $selectedDate->copy()->endOfMonth()->format('Y-m-d');
        
        $avgMonthStart = $selectedDate->copy()->subMonths(6)->format('Y-m-01');
        $avgMonthEnd = $selectedDate->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $yearStart = "{$this->filters['tahun']}-01-01";
        $yearEnd = "{$this->filters['tahun']}-12-31";

        $query = ReportReaktivasiToko::query()
            ->select(
                'uniq_kd', 'custno',
                DB::raw("MAX(custname) as custname"),
                DB::raw("MAX(alamat) as alamat"),
                DB::raw("MAX(region) as region"),
                DB::raw("MAX(area) as area"),
                DB::raw("MAX(supervisor) as supervisor"),
                DB::raw("MAX(distributor) as distributor"),
                DB::raw("MAX(bulan) as last_transaksi"),
                DB::raw("SUM(CASE WHEN bulan >= '$yearStart' AND bulan <= '$yearEnd' THEN neto ELSE 0 END) as total_transaksi"),
                DB::raw("SUM(CASE WHEN bulan >= '$avgMonthStart' AND bulan <= '$avgMonthEnd' THEN neto ELSE 0 END) / 6 as avg_6_months"),
                DB::raw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END) as pencapaian_bulan_ini")
            );

        for ($i = 1; $i <= 12; $i++) {
            $m = str_pad($i, 2, '0', STR_PAD_LEFT);
            $start = "{$this->filters['tahun']}-$m-01";
            $end = Carbon::createFromFormat('Y-m-d', $start)->endOfMonth()->format('Y-m-d');
            $query->addSelect(DB::raw("SUM(CASE WHEN bulan >= '$start' AND bulan <= '$end' THEN neto ELSE 0 END) as bln_$m"));
        }

        if ($this->user) {
            $accessLevel = $this->user->getAccessLevel();
            if ($accessLevel === 'supervisor') {
                $query->where('supervisor_code', $this->user->supervisor_code);
            } elseif ($accessLevel === 'area') {
                $query->whereIn('area_code', (array) $this->user->area_code);
            } elseif ($accessLevel === 'region') {
                $query->whereIn('region_code', (array) $this->user->region_code);
            }
        }

        $query->groupBy('uniq_kd', 'custno')
              ->orderByRaw("MAX(region) ASC")
              ->orderByRaw("MAX(area) ASC")
              ->orderByRaw("MAX(distributor) ASC")
              ->orderByRaw("(SUM(CASE WHEN bulan >= '$avgMonthStart' AND bulan <= '$avgMonthEnd' THEN neto ELSE 0 END) / 6) DESC");

        if (!empty($this->filters['search'])) {
            $query->where(function($q) {
                $q->where('custname', 'ilike', '%' . $this->filters['search'] . '%')
                  ->orWhere('custno', 'ilike', '%' . $this->filters['search'] . '%');
            });
        }
        if (!empty($this->filters['region'])) $query->where('region', $this->filters['region']);
        if (!empty($this->filters['area'])) $query->where('area', $this->filters['area']);
        if (!empty($this->filters['supervisor'])) $query->where('supervisor', $this->filters['supervisor']);
        if (!empty($this->filters['distributor'])) $query->where('distributor', $this->filters['distributor']);

        if ($this->filters['status'] === 'aktif') {
            $query->having(DB::raw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END)"), '>', 0);
        } elseif ($this->filters['status'] === 'tidak_aktif') {
            $query->having(DB::raw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END)"), '<=', 0)
                  ->orHavingRaw("SUM(CASE WHEN bulan >= '$monthStart' AND bulan <= '$monthEnd' THEN neto ELSE 0 END) IS NULL");
        }

        $avgCalc = "SUM(CASE WHEN bulan >= '$avgMonthStart' AND bulan <= '$avgMonthEnd' THEN neto ELSE 0 END) / 6";
        if (!empty($this->filters['type'])) {
            if ($this->filters['type'] === 'SO') {
                $query->having(DB::raw($avgCalc), '>', 10000000);
            } elseif ($this->filters['type'] === 'G') {
                $query->having(DB::raw($avgCalc), '>=', 5000000)
                      ->having(DB::raw($avgCalc), '<=', 10000000);
            } elseif ($this->filters['type'] === 'SG') {
                $query->having(DB::raw($avgCalc), '>=', 3000000)
                      ->having(DB::raw($avgCalc), '<', 5000000);
            } elseif ($this->filters['type'] === 'R') {
                $query->having(DB::raw($avgCalc), '<', 3000000)
                      ->orHavingRaw("($avgCalc) IS NULL");
            }
        }

        return $query;
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Region',
            'Area',
            'Distributor',
            'Supervisor',
            'Uniq KD',
            'Cust No',
            'Nama Toko',
            'Alamat',
            'Type',
            'Class',
            'Status Aktif',
            'Last Transaksi',
            'Total Transaksi (' . $this->filters['tahun'] . ')',
            'Avg 6 Bulan',
            'Pencapaian Bln Ini'
        ];

        for ($i = 1; $i <= 12; $i++) {
            $headers[] = date('M', mktime(0, 0, 0, $i, 1));
        }

        return $headers;
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $avg = $row->avg_6_months;
        $type = 'R';
        if ($avg > 10000000) $type = 'SO';
        elseif ($avg >= 5000000) $type = 'G';
        elseif ($avg >= 3000000) $type = 'SG';

        $class = ($type === 'R') ? 'Non Pareto' : 'Pareto';
        $isAktif = $row->pencapaian_bulan_ini > 0;

        $mapped = [
            $this->rowNumber,
            $row->region,
            $row->area,
            $row->distributor,
            $row->supervisor,
            $row->uniq_kd,
            $row->custno,
            $row->custname,
            $row->alamat,
            $type,
            $class,
            $isAktif ? 'Aktif' : 'Tidak Aktif',
            $row->last_transaksi ? Carbon::parse($row->last_transaksi)->format('d M Y') : '-',
            $row->total_transaksi ?: 0,
            $row->avg_6_months ?: 0,
            $row->pencapaian_bulan_ini ?: 0
        ];

        for ($i = 1; $i <= 12; $i++) {
            $col = 'bln_' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $mapped[] = $row->$col ?: 0;
        }

        return $mapped;
    }

    public function columnFormats(): array
    {
        return [
            'N' => '#,##0',
            'O' => '#,##0',
            'P' => '#,##0',
            'Q' => '#,##0',
            'R' => '#,##0',
            'S' => '#,##0',
            'T' => '#,##0',
            'U' => '#,##0',
            'V' => '#,##0',
            'W' => '#,##0',
            'X' => '#,##0',
            'Y' => '#,##0',
            'Z' => '#,##0',
            'AA' => '#,##0',
            'AB' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:AB1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A5568'] 
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        $sheet->freezePane('A2');

        return [];
    }
}
