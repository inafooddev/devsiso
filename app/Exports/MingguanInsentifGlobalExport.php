<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\MingguanInsentifSeSheet;
use App\Exports\Sheets\MingguanInsentifSpvSheet;
use App\Exports\Sheets\MingguanInsentifKacabSheet;
use App\Exports\Sheets\MingguanInsentifSummarySheet;

class MingguanInsentifGlobalExport implements WithMultipleSheets
{
    protected $bulan;
    protected $region;
    protected $areas;
    protected $selectedSheets;

    public function __construct($bulan, $region, $areas = [], $selectedSheets = ['SE', 'SPV', 'KACAB'])
    {
        $this->bulan = $bulan;
        $this->region = $region;
        $this->areas = $areas;
        $this->selectedSheets = $selectedSheets;
    }

    public function sheets(): array
    {
        $sheets = [];

        if (in_array('SE', $this->selectedSheets)) {
            $sheets[] = new MingguanInsentifSeSheet($this->bulan, $this->region, $this->areas);
        }

        if (in_array('SPV', $this->selectedSheets)) {
            $sheets[] = new MingguanInsentifSpvSheet($this->bulan, $this->region, $this->areas);
        }

        if (in_array('KACAB', $this->selectedSheets)) {
            $sheets[] = new MingguanInsentifKacabSheet($this->bulan, $this->region, $this->areas);
        }

        if (in_array('SUMMARY', $this->selectedSheets)) {
            $sheets[] = new MingguanInsentifSummarySheet($this->bulan, $this->region, $this->areas);
        }

        return $sheets;
    }
}
