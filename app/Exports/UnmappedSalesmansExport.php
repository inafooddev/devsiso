<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class UnmappedSalesmansExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        // Logika query ini identik dengan di komponen Livewire
        $salesmanMappingsSub = DB::table('salesman_mappings')
            ->select('distributor_code', 'salesman_code_dist', DB::raw('MIN(salesman_code_prc) as salesman_code_prc'))
            ->groupBy('distributor_code', 'salesman_code_dist');

        $query = DB::table('sales_invoice_distributor as a')
            ->join('master_distributors as b', 'a.distributor_code', '=', 'b.distributor_code')
            ->leftJoinSub($salesmanMappingsSub, 'c', function ($join) {
                $join->on('a.distributor_code', '=', 'c.distributor_code')
                    ->on('a.salesman_code', '=', 'c.salesman_code_dist');
            })
            ->leftJoin('salesmans as d', 'c.salesman_code_prc', '=', 'd.salesman_code')
            ->select(
                'b.distributor_name',
                'a.distributor_code',
                'a.salesman_code',
                'a.salesman_name'
            )
            ->whereNull('d.salesman_code')
            ->whereNotNull('a.salesman_code')
            ->where('a.salesman_code', '!=', '')
            ->groupBy(
                'a.distributor_code',
                'b.distributor_name',
                'a.salesman_code',
                'a.salesman_name'
            );

        if (!empty($this->filters['regionFilter'])) {
            $query->where('b.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('b.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('a.distributor_code', $this->filters['distributorFilter']);
        }

        if (!empty($this->filters['monthFilter']) && !empty($this->filters['yearFilter'])) {
            $startDate = Carbon::create($this->filters['yearFilter'], $this->filters['monthFilter'], 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
            $query->whereBetween('a.invoice_date', [$startDate, $endDate]);
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('a.salesman_code', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('a.salesman_name', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('b.distributor_name', 'ILIKE', '%' . $this->filters['search'] . '%');
            });
        }

        return $query->orderBy('b.distributor_name')->orderBy('a.salesman_name');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Distributor',
            'Nama Distributor',
            'Kode Salesman (Distributor)',
            'Nama Salesman (Distributor)',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->distributor_code,
            $row->distributor_name,
            $row->salesman_code,
            $row->salesman_name,
        ];
    }
}
