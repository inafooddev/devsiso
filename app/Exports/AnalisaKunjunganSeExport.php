<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaKunjunganSeExport implements FromGenerator, WithHeadings, ShouldAutoSize, WithStyles
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
        $subtotalOrder = 0;

        foreach ($this->query->cursor() as $row) {
            $rowDate = $row->tanggal;

            if ($currentDate !== null && $currentDate !== $rowDate) {
                yield [
                    '', '', '', '', '', '', '', '', '', '', '', '', '',
                    'Subtotal Tanggal ' . \Carbon\Carbon::parse($currentDate)->format('d-m-Y') . ':',
                    '',
                    $subtotalOrder,
                    '', '', '', '', '', '', '', '', '', ''
                ];
                
                $subtotalOrder = 0;
            }

            $currentDate = $rowDate;
            $rowNumber++;
            
            $subtotalOrder += (float) ($row->val_order ?? 0);

            yield [
                $rowNumber,
                $row->tanggal,
                $row->time_in,
                $row->time_out,
                $row->time_consume,
                $row->time_travel,
                $row->time_pause,
                $row->region_name ?? $row->region_code,
                $row->area_name ?? $row->area_code,
                $row->distributor_code,
                $row->distributor_name,
                $row->salesman_code,
                $row->salesman_name,
                $row->custno,
                $row->custname,
                $row->address,
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
            ];
        }

        if ($currentDate !== null) {
            yield [
                '', '', '', '', '', '', '', '', '', '', '', '', '',
                'Subtotal Tanggal ' . \Carbon\Carbon::parse($currentDate)->format('d-m-Y') . ':',
                '',
                $subtotalOrder,
                '', '', '', '', '', '', '', '', '', ''
            ];
        }
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tanggal',
            'Start (In)',
            'End (Out)',
            'Durasi Kunjungan',
            'Time Travel',
            'Time Pause',
            'Region',
            'Area',
            'Distributor Code',
            'Distributor Name',
            'SE Code',
            'SE Name',
            'Cust No',
            'Cust Name',
            'Alamat Toko',
            'Qty Order',
            'Val Order (Rp)',
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
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F2937']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    protected function getDistance($lat1, $lon1, $lat2, $lon2)
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
        return round($angle * $earthRadius);
    }
}
