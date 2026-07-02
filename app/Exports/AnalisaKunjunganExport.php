<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaKunjunganExport implements FromGenerator, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function generator(): \Generator
    {
        $currentDate = null;
        $rowNumber = 0;
        $subtotalTarget = 0;
        $subtotalOrder = 0;

        foreach ($this->query->cursor() as $row) {
            $rowDate = $row->tanggal;

            if ($currentDate !== null && $currentDate !== $rowDate) {
                yield [
                    '', '', '', '', '', '', '', '', '', '', '', '',
                    'Subtotal Tanggal ' . \Carbon\Carbon::parse($currentDate)->format('d-m-Y') . ':',
                    $subtotalTarget,
                    '',
                    $subtotalOrder,
                    '', '', '', '', '', '', '', '', '', '', ''
                ];
                
                $subtotalTarget = 0;
                $subtotalOrder = 0;
            }

            $currentDate = $rowDate;
            $rowNumber++;
            
            $subtotalTarget += (float) $row->target;
            $subtotalOrder += (float) $row->val_order;

            yield [
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
                $this->getDistance($row->master_lat ?? null, $row->master_lon ?? null, $row->visit_lat ?? null, $row->visit_lon ?? null),
                $row->reason_type,
                $row->reason_desc,
                $row->action_remark,
            ];
        }

        if ($currentDate !== null) {
            yield [
                '', '', '', '', '', '', '', '', '', '', '', '',
                'Subtotal Tanggal ' . \Carbon\Carbon::parse($currentDate)->format('d-m-Y') . ':',
                $subtotalTarget,
                '',
                $subtotalOrder,
                '', '', '', '', '', '', '', '', '', '', ''
            ];
        }
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
            'Distance (m)',
            'Reason Type',
            'Reason Desc',
            'Action Remark',
        ];
    }


    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;
        
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float)$lat1);
        $lonFrom = deg2rad((float)$lon1);
        $latTo = deg2rad((float)$lat2);
        $lonTo = deg2rad((float)$lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return round($angle * $earthRadius); // in meters
    }
}
