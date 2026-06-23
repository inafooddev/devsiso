<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaKunjunganExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tanggal',
            'Time In',
            'Time Out',
            'Time Consume',
            'Time Travel',
            'Time Pause',
            'Supervisor Code',
            'Supervisor Name',
            'Customer No',
            'Customer Name',
            'Alamat',
            'Pilar',
            'Target',
            'Qty Order',
            'Value Order',
            'Flag PJP',
            'Flag Visit',
            'Flag EC',
            'Flag Buy',
            'Flag Pause',
            'Visit Lat',
            'Visit Lon',
            'Reason Type',
            'Reason Desc',
            'Action Remark',
        ];
    }

    public function map($row): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $row->tanggal,
            $row->time_in,
            $row->time_out,
            $row->time_consume,
            $row->time_travel,
            $row->time_pause,
            $row->supervisor_code,
            $row->supervisor_name,
            $row->custno,
            $row->custname,
            $row->address,
            $row->pilar,
            $row->target,
            $row->qty_order,
            $row->val_order,
            $row->flag_pjp,
            $row->flag_visit,
            $row->flag_ec,
            $row->flag_buy,
            $row->flag_pause,
            $row->visit_lat,
            $row->visit_lon,
            $row->reason_type,
            $row->reason_desc,
            $row->action_remark,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
