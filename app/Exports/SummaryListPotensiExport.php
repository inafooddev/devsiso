<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SummaryListPotensiExport implements FromView, ShouldAutoSize
{
    public $records;
    
    public function __construct($records)
    {
        $this->records = $records;
    }

    public function view(): View
    {
        return view('exports.summary-list-potensi-excel', [
            'records' => $this->records
        ]);
    }
}
