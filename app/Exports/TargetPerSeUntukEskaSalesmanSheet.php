<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TargetPerSeUntukEskaSalesmanSheet implements FromView, WithTitle, ShouldAutoSize
{
    protected $salesman;
    protected $meta;
    protected $rows;

    public function __construct(string $salesman, array $meta, $rows)
    {
        $this->salesman = $salesman;
        $this->meta = $meta;
        $this->rows = $rows;
    }

    /**
     * Nama Sheet Excel (Maksimal 31 karakter & bersih dari karakter ilegal).
     */
    public function title(): string
    {
        $title = preg_replace('/[\\\\\\/*?:\[\]]/', '', $this->salesman ?: 'SALESMAN');
        $title = trim($title);
        return substr($title ?: 'SALESMAN', 0, 30);
    }

    /**
     * Render Blade View.
     */
    public function view(): View
    {
        return view('exports.target_per_se_eska_sheet', [
            'salesman' => $this->salesman,
            'meta'     => $this->meta,
            'rows'     => $this->rows,
        ]);
    }
}
