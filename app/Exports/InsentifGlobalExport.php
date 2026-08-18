<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\InsentifSeSheet;
use App\Exports\Sheets\InsentifSpvSheet;
use App\Exports\Sheets\InsentifKacabSheet;
use App\Exports\Sheets\InsentifSummarySheet;

class InsentifGlobalExport implements WithMultipleSheets
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
            $sheets[] = new InsentifSeSheet($this->bulan, $this->region, $this->areas);
        }

        if (in_array('SPV', $this->selectedSheets)) {
            $sheets[] = new InsentifSpvSheet($this->bulan, $this->region, $this->areas);
        }

        if (in_array('KACAB', $this->selectedSheets)) {
            $sheets[] = new InsentifKacabSheet($this->bulan, $this->region, $this->areas);
        }

        if (in_array('SUMMARY', $this->selectedSheets)) {
            $sheets[] = new InsentifSummarySheet($this->bulan, $this->region, $this->areas);
        }

        return $sheets;
    }
}
