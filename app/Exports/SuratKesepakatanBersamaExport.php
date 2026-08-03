<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class SuratKesepakatanBersamaExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomValueBinder
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function bindValue(Cell $cell, $value)
    {
        // Forcing specific columns to be strictly TEXT
        // L = No Hp, M = NIK/NPWP, P = No Rekening
        if (in_array($cell->getColumn(), ['L', 'M', 'P'])) {
            $cell->setValueExplicit(strval($value), DataType::TYPE_STRING);
            return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return [
            'Kuartal',
            'Region Code',
            'Region Name',
            'Area Code',
            'Area Name',
            'Supervisor Code',
            'Distributor Code',
            'Distributor Name',
            'Customer Code',
            'Customer Name',
            'Nama Pemilik',
            'No Hp',
            'NIK/NPWP',
            'Nama di KTP',
            'Bank',
            'No Rekening',
            'Nama Pemilik Rekening',
            'Status Approval',
            'Alasan Penolakan',
            'Status Validasi HO',
            'Catatan Validasi HO'
        ];
    }

    public function map($row): array
    {
        $status = 'Pending';
        if ($row->is_approved === true) $status = 'Approve';
        if ($row->is_approved === false) $status = 'Reject';
        
        $statusHO = 'Belum Dicek';
        if ($row->ho_is_valid === true) $statusHO = 'Diterima';
        if ($row->ho_is_valid === false) $statusHO = 'Ditolak';

        return [
            $row->kuartal,
            $row->region_code,
            $row->region_name,
            $row->area_code,
            $row->area_name,
            $row->supervisor_code,
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_code,
            $row->customer_name,
            $row->nama_pemilik_toko ?? '-',
            $row->no_hp ?? '-',
            $row->nik_ktp ?? '-',
            $row->nama_ktp ?? '-',
            $row->nama_bank ?? '-',
            $row->no_rekening ?? '-',
            $row->nama_pemilik_norek ?? '-',
            $status,
            $row->reason ?? '-',
            $statusHO,
            $row->ho_notes ?? '-',
        ];
    }
}
