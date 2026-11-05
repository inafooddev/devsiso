<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class JksMasterCustomerExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder, WithColumnWidths
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function bindValue(Cell $cell, $value)
    {
        // Pastikan kolom kode dan koordinat disimpan sebagai teks eksplisit agar tidak terpotong nol awal atau floating point
        if (in_array($cell->getColumn(), ['G', 'I', 'J', 'O', 'P', 'Q'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function query()
    {
        // Clone query dan hapus limit/offset pagination agar data 1 region ter-export penuh
        $q = clone $this->query;
        $q->limit = null;
        $q->offset = null;
        return $q;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, // Region Code
            'B' => 20, // Region Name
            'C' => 14, // Area Code
            'D' => 22, // Area Name
            'E' => 16, // Supervisor Code
            'F' => 24, // Supervisor Name
            'G' => 16, // Distributor Code
            'H' => 30, // Distributor Name
            'I' => 18, // Customer Code
            'J' => 18, // Uniq Kd
            'K' => 34, // Customer Name
            'L' => 45, // Customer Address
            'M' => 20, // Kabupaten
            'N' => 20, // Kecamatan
            'O' => 20, // Desa
            'P' => 14, // Latitude
            'Q' => 14, // Longitude
            'R' => 16, // Channel
            'S' => 18, // Classification
            'T' => 16, // Segment
            'U' => 14, // Pilar
            'V' => 10, // Pilar Q1
            'W' => 10, // Pilar Q2
            'X' => 10, // Pilar Q3
            'Y' => 10, // Pilar Q4
            'Z' => 12, // Target
            'AA' => 22, // Remarks SPM
            'AB' => 10, // On Plan
        ];
    }

    public function headings(): array
    {
        return [
            'Region Code',
            'Region Name',
            'Area Code',
            'Area Name',
            'Supervisor Code',
            'Supervisor Name',
            'Distributor Code',
            'Distributor Name',
            'Customer Code',
            'Uniq Kd',
            'Customer Name',
            'Customer Address',
            'Kabupaten',
            'Kecamatan',
            'Desa',
            'Latitude',
            'Longitude',
            'Channel',
            'Classification',
            'Segment',
            'Pilar',
            'Pilar Q1',
            'Pilar Q2',
            'Pilar Q3',
            'Pilar Q4',
            'Target',
            'Remarks SPM',
            'On Plan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_code,
            $row->region_name,
            $row->area_code,
            $row->area_name,
            $row->supervisor_code,
            $row->supervisor_name,
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_code,
            $row->uniq_kd,
            $row->customer_name,
            $row->customer_address,
            $row->kabupaten,
            $row->kecamatan,
            $row->desa,
            $row->latitude !== null ? str_replace(',', '.', (string) $row->latitude) : null,
            $row->longitude !== null ? str_replace(',', '.', (string) $row->longitude) : null,
            $row->channel_outlet,
            $row->classification_outlet,
            $row->segment_outlet,
            $row->pilar,
            $row->pilar_q1,
            $row->pilar_q2,
            $row->pilar_q3,
            $row->pilar_q4,
            $row->target,
            $row->keterangan,
            $row->on_plan,
        ];
    }
}
