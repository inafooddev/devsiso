<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class JksMasterCustomerExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithCustomValueBinder, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['O', 'P'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function collection()
    {
        // Remove limit/offset if pagination was applied
        $this->query->limit = null;
        $this->query->offset = null;
        return $this->query->get();
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
