<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\InsentifSeSheet;
use App\Exports\Sheets\InsentifSpvSheet;
use App\Exports\Sheets\InsentifKacabSheet;

class InsentifGlobalExport implements WithMultipleSheets
{
    protected $bulan;
    protected $region;
    protected $areas;

    public function __construct($bulan, $region, $areas = [])
    {
        $this->bulan = $bulan;
        $this->region = $region;
        $this->areas = $areas;
    }

    public function sheets(): array
    {
        return [
            new InsentifSeSheet($this->bulan, $this->region, $this->areas),
            new InsentifSpvSheet($this->bulan, $this->region, $this->areas),
            new InsentifKacabSheet($this->bulan, $this->region, $this->areas),
        ];
    }
}
