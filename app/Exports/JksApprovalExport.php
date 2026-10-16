<?php

namespace App\Exports;

use App\Exports\Sheets\JksApprovalSummarySheet;
use App\Exports\Sheets\JksApprovalDetailSheet;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class JksApprovalExport implements WithMultipleSheets
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function sheets(): array
    {
        // Clone query and ensure sorted by newest
        $rows = (clone $this->query)->orderBy('ja.id', 'desc')->get();

        // Preload Salesman Map for fast lookup
        $salesmanMap = DB::table('salesmans')
            ->pluck('salesman_name', 'salesman_code')
            ->toArray();

        // Preload JKS Salesmans by ID for referenced records
        $jksIds = [];
        foreach ($rows as $r) {
            $p = json_decode($r->payload, true) ?: [];
            if (isset($p['id'])) {
                $jksIds[] = $p['id'];
            }
            if (isset($p['ids']) && is_array($p['ids'])) {
                foreach ($p['ids'] as $id) {
                    $jksIds[] = $id;
                }
            }
        }

        $jksMap = collect();
        if (!empty($jksIds)) {
            $jksMap = DB::table('jks_salesmans')
                ->whereIn('id', array_unique($jksIds))
                ->get(['id', 'customer_code', 'salesman_code', 'bulan'])
                ->keyBy('id');
        }

        return [
            new JksApprovalSummarySheet($rows, $salesmanMap, $jksMap),
            new JksApprovalDetailSheet($rows, $salesmanMap, $jksMap),
        ];
    }
}
