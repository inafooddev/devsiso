<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class PerbaikanTikorExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithCustomValueBinder
{
    use Exportable;

    protected $search;
    protected $statusFilter;
    protected $dateStart;
    protected $dateEnd;
    protected $selectedIds;

    public function __construct($search = '', $statusFilter = '', $dateStart = '', $dateEnd = '', $selectedIds = [])
    {
        $this->search = $search;
        $this->statusFilter = $statusFilter;
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
        $this->selectedIds = $selectedIds;
    }

    public function query()
    {
        $query = DB::table('perbaikan_tikor_toko as p')
            ->leftJoin('customer_prc_eska as cpe', function ($join) {
                $join->on('cpe.kodecabang', '=', 'p.distributor_code')
                     ->on('cpe.custno', '=', 'p.customer_code');
            })
            ->select(
                'cpe.custno',
                'cpe.custname',
                'cpe.custadd1',
                'cpe.ccity',
                'cpe.cterm',
                'cpe.typeout',
                'cpe.grupout',
                'cpe.gharga',
                'cpe.flagpay',
                'cpe.flagout',
                'cpe.kodecabang',
                'p.latitude',
                'p.longitude'
            );

        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('p.region_code', is_array($user->region_code) ? $user->region_code : [$user->region_code]);
        }

        if (!empty($this->selectedIds)) {
            $query->whereIn('p.id', $this->selectedIds);
        } else {
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('p.customer_code', 'like', '%' . $this->search . '%')
                      ->orWhere('p.distributor_code', 'like', '%' . $this->search . '%')
                      ->orWhere('p.sales_code', 'like', '%' . $this->search . '%')
                      ->orWhere('cpe.custname', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->statusFilter && $this->statusFilter !== 'Semua Kategori' && $this->statusFilter !== 'Semua Status') {
                $query->where('p.status', $this->statusFilter);
            }

            if ($this->dateStart) {
                $query->whereDate('p.created_at', '>=', $this->dateStart);
            }

            if ($this->dateEnd) {
                $query->whereDate('p.created_at', '<=', $this->dateEnd);
            }
        }

        return $query->orderBy('p.created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'CUSTNO',
            'CUSTNAME',
            'CUSTADD1',
            'CCITY',
            'CTERM',
            'TYPEOUT',
            'GRUPOUT',
            'GHARGA',
            'FLAGPAY',
            'FLAGOUT',
            'KODECABANG',
            'LATITUDE',
            'LONGITUDE'
        ];
    }

    public function map($row): array
    {
        return [
            $row->custno,
            $row->custname,
            $row->custadd1,
            $row->ccity,
            $row->cterm,
            $row->typeout,
            $row->grupout,
            $row->gharga,
            $row->flagpay,
            $row->flagout,
            $row->kodecabang,
            str_replace(',', '.', (string) $row->latitude),
            str_replace(',', '.', (string) $row->longitude),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();

        // Paksa kolom L (Latitude) dan M (Longitude) menjadi String murni
        if (in_array($column, ['L', 'M'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        // Kembalikan ke format bawaan untuk kolom lainnya
        return parent::bindValue($cell, $value);
    }
}
