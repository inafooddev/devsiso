<?php

namespace App\Exports\Report\FundamentalSalesHierarchy;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FundamentalSalesMultiSheetExport implements WithMultipleSheets
{
    use Exportable;

    protected $supervisor;
    protected $year;
    protected $dashboardData;
    protected $branchData;
    protected $kpis;

    public function __construct($supervisor, $year, $dashboardData, $branchData, $kpis)
    {
        $this->supervisor = $supervisor;
        $this->year = $year;
        $this->dashboardData = $dashboardData;
        $this->branchData = $branchData;
        $this->kpis = $kpis;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Dashboard
        $sheets[] = new DashboardSheetExport($this->supervisor, $this->year, $this->dashboardData, $this->kpis);

        // Sheet 2-8: KPI Details
        foreach ($this->kpis as $key => $kpi) {
            $sheets[] = new KpiDetailSheetExport($this->supervisor, $this->year, $this->branchData, $kpi['label'], $key);
        }

        return $sheets;
    }
}
