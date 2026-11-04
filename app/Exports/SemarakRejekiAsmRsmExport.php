<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SemarakRejekiAsmRsmExport implements WithMultipleSheets
{
    protected array $data;
    protected int $year;

    public function __construct(array $data, int $year = 2026)
    {
        $this->data = $data;
        $this->year = $year;
    }

    public function sheets(): array
    {
        return [
            new SemarakRejekiRsmSheet($this->data['regions'] ?? [], $this->year),
            new SemarakRejekiAsmSheet($this->data['flat_asms'] ?? [], $this->year),
        ];
    }
}
