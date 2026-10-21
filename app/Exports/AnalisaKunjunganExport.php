<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class AnalisaKunjunganExport implements FromGenerator, WithHeadings, WithStyles
{
    protected $query;
    protected $filterDepo;

    public function __construct($query, $filterDepo = 'all')
    {
        $this->query = $query;
        $this->filterDepo = $filterDepo;
    }

    public function generator(): \Generator
    {
        $currentDate = null;
        $rowNumber = 0;
        $subtotalTarget = 0;
        $subtotalOrder = 0;

        // Preload all depos
        $allDepos = DB::table('master_distributors as md')
            ->whereNotNull('md.latitude')
            ->whereNotNull('md.longitude')
            ->where('md.latitude', '!=', 0)
            ->where('md.longitude', '!=', 0)
            ->select('md.distributor_code', 'md.distributor_name', 'md.supervisor_code', 'md.area_code', 'md.region_code', 'md.latitude', 'md.longitude')
            ->get();

        foreach ($this->query->cursor() as $row) {
            // Evaluasi jarak ke Depo
            $cleanVisit = $this->cleanCoordinate($row->visit_lat, $row->visit_lon);
            $distToDepo = null;
            $depoName = '';
            $isBriefingEva = \App\Livewire\Report\AnalisaKunjungan\Index::isBriefingOrEvaluasi($row->custno ?? '', $row->custname ?? '');
            $isAtDepo = false;

            if ($cleanVisit && $allDepos->isNotEmpty()) {
                $spvDepos = $allDepos->where('supervisor_code', $row->siso_code ?? '');
                if ($spvDepos->isEmpty() && !empty($row->area_code)) {
                    $spvDepos = $allDepos->where('area_code', $row->area_code);
                }
                if ($spvDepos->isEmpty()) {
                    $spvDepos = $allDepos;
                }

                $closest = null;
                $minDist = null;
                foreach ($spvDepos as $depo) {
                    $d = $this->getDistance($cleanVisit['lat'], $cleanVisit['lon'], $depo->latitude, $depo->longitude);
                    if ($d !== null && ($minDist === null || $d < $minDist)) {
                        $minDist = (int)$d;
                        $closest = $depo;
                    }
                }

                if ($minDist === null || $minDist > 100) {
                    foreach ($allDepos as $depo) {
                        $d = $this->getDistance($cleanVisit['lat'], $cleanVisit['lon'], $depo->latitude, $depo->longitude);
                        if ($d !== null && $d <= 100) {
                            $minDist = (int)$d;
                            $closest = $depo;
                            break;
                        }
                    }
                }

                if ($closest !== null && $minDist !== null) {
                    $distToDepo = $minDist;
                    $depoName = trim(preg_replace('/[\r\n\t]+/', ' ', (string)$closest->distributor_name));
                    // Abaikan briefing pagi & evaluasi sore karena memang dilakukan di distributor/depo
                    $isAtDepo = (!$isBriefingEva && $row->flag_visit === 'Y' && $minDist <= 100);
                }
            }

            // Filter depo check
            if ($this->filterDepo === 'at_depo' && !$isAtDepo) {
                continue;
            } elseif ($this->filterDepo === 'not_depo' && ($isAtDepo || $isBriefingEva)) {
                continue;
            }

            $rowDate = $row->tanggal;

            if ($currentDate !== null && $currentDate !== $rowDate) {
                yield [
                    '', '', '', '', '', '', '', '', '', '', '', '',
                    'Subtotal Tanggal ' . \Carbon\Carbon::parse($currentDate)->format('d-m-Y') . ':',
                    $subtotalTarget,
                    '',
                    $subtotalOrder,
                    '', '', '', '', '', '', '', '', '', '', '', '', '', ''
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
                $distToDepo !== null ? $distToDepo : '-',
                $depoName ?: '-',
                $isAtDepo ? 'Ya (<= 100m)' : ($isBriefingEva ? 'Briefing/Evaluasi' : 'Tidak'),
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
                '', '', '', '', '', '', '', '', '', '', '', '', '', ''
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
            'Distance to Master (m)',
            'Distance to Depo (m)',
            'Depo Name',
            'Kunjungan di Depo',
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

    public function cleanCoordinate($lat, $lon): ?array
    {
        if ($lat === null || $lon === null) return null;
        $lat = (float) $lat;
        $lon = (float) $lon;
        if ($lat == 0.0 && $lon == 0.0) return null;
        if (abs($lat) > 90) {
            $temp = $lat; $lat = $lon; $lon = $temp;
        }
        if (abs($lat) > 90 || abs($lon) > 180) return null;
        return ['lat' => $lat, 'lon' => $lon];
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return null;
        $c1 = $this->cleanCoordinate($lat1, $lon1);
        $c2 = $this->cleanCoordinate($lat2, $lon2);
        if (!$c1 || !$c2) return null;

        $theta = $c1['lon'] - $c2['lon'];
        $dist = sin(deg2rad($c1['lat'])) * sin(deg2rad($c2['lat'])) + cos(deg2rad($c1['lat'])) * cos(deg2rad($c2['lat'])) * cos(deg2rad($theta));
        $dist = acos(min(max($dist, -1.0), 1.0));
        $dist = rad2deg($dist);
        return (int) round($dist * 60 * 1.1515 * 1.609344 * 1000);
    }
}
