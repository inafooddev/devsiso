<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaKunjunganSummaryExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $exportData = [];
        $no = 1;

        $totPc = 0; $totAc = 0; $totEc = 0; $totTarget = 0; $totOrder = 0;
        $totRwo = 0; $totPnr = 0; $totNgvo = 0; $totOoa = 0;

        $currentSupervisor = null;
        $currentSupervisorName = null;
        $subPc = 0; $subAc = 0; $subEc = 0; $subTarget = 0; $subOrder = 0;
        $subRwo = 0; $subPnr = 0; $subNgvo = 0; $subOoa = 0;

        foreach ($this->data as $row) {
            $rowSupervisor = $row['supervisor_code'] ?? null;
            $rowSupervisorName = $row['supervisor_name'] ?? null;

            if ($currentSupervisor !== null && $currentSupervisor !== $rowSupervisor) {
                $subPareto = $subRwo + $subPnr + $subNgvo;
                $exportData[] = [
                    'No' => '',
                    'Region' => '',
                    'Area' => '',
                    'Level' => '',
                    'Supervisor' => 'SUBTOTAL ' . $currentSupervisorName,
                    'Tanggal' => '',
                    'PC' => $subPc,
                    'AC' => $subAc,
                    'Visit %' => ($subPc > 0 ? round(($subAc / $subPc) * 100, 1) : 0) . '%',
                    'EC' => $subEc,
                    'EC %' => ($subPc > 0 ? round(($subEc / $subPc) * 100, 1) : 0) . '%',
                    'Target' => $subTarget,
                    'Order' => $subOrder,
                    'Order %' => ($subTarget > 0 ? round(($subOrder / $subTarget) * 100, 1) : 0) . '%',
                    'RWO' => $subRwo,
                    'RWO %' => ($subPc > 0 ? round(($subRwo / $subPc) * 100, 1) : 0) . '%',
                    'PNR' => $subPnr,
                    'PNR %' => ($subPc > 0 ? round(($subPnr / $subPc) * 100, 1) : 0) . '%',
                    'NGVO' => $subNgvo,
                    'NGVO %' => ($subPc > 0 ? round(($subNgvo / $subPc) * 100, 1) : 0) . '%',
                    'Pareto' => $subPareto,
                    'Pareto %' => ($subPc > 0 ? round(($subPareto / $subPc) * 100, 1) : 0) . '%',
                    'Out of Area' => $subOoa,
                    'Out of Area %' => ($subAc > 0 ? round(($subOoa / $subAc) * 100, 1) : 0) . '%'
                ];

                $subPc = 0; $subAc = 0; $subEc = 0; $subTarget = 0; $subOrder = 0;
                $subRwo = 0; $subPnr = 0; $subNgvo = 0; $subOoa = 0;
            }

            $currentSupervisor = $rowSupervisor;
            $currentSupervisorName = $rowSupervisorName;
            $exportData[] = [
                'No' => $no++,
                'Region' => $row['region_name'],
                'Area' => $row['area_name'],
                'Level' => $row['level'] ?? '',
                'Supervisor' => $row['supervisor_name'],
                'Tanggal' => $row['tanggal'] ?? '',
                'PC' => $row['pc'],
                'AC' => $row['ac'],
                'Visit %' => $row['pc_ac_pct'] . '%',
                'EC' => $row['ec'],
                'EC %' => $row['ec_pct'] . '%',
                'Target' => $row['target'],
                'Order' => $row['order'],
                'Order %' => $row['target_order_pct'] . '%',
                'RWO' => $row['rwo'],
                'RWO %' => $row['rwo_pct'] . '%',
                'PNR' => $row['pnr'],
                'PNR %' => $row['pnr_pct'] . '%',
                'NGVO' => $row['ngvo'],
                'NGVO %' => $row['ngvo_pct'] . '%',
                'Pareto' => $row['pareto'],
                'Pareto %' => $row['pareto_pct'] . '%',
                'Out of Area' => $row['out_of_area'],
                'Out of Area %' => $row['out_of_area_pct'] . '%'
            ];

            $totPc += $row['pc'];
            $totAc += $row['ac'];
            $totEc += $row['ec'];
            $totTarget += $row['target'];
            $totOrder += $row['order'];
            $totRwo += $row['rwo'];
            $totPnr += $row['pnr'];
            $totNgvo += $row['ngvo'];
            $totOoa += $row['out_of_area'];

            $subPc += $row['pc'];
            $subAc += $row['ac'];
            $subEc += $row['ec'];
            $subTarget += $row['target'];
            $subOrder += $row['order'];
            $subRwo += $row['rwo'];
            $subPnr += $row['pnr'];
            $subNgvo += $row['ngvo'];
            $subOoa += $row['out_of_area'];
        }

        if ($currentSupervisor !== null) {
            $subPareto = $subRwo + $subPnr + $subNgvo;
            $exportData[] = [
                'No' => '',
                'Region' => '',
                'Area' => '',
                'Level' => '',
                'Supervisor' => 'SUBTOTAL ' . $currentSupervisorName,
                'Tanggal' => '',
                'PC' => $subPc,
                'AC' => $subAc,
                'Visit %' => ($subPc > 0 ? round(($subAc / $subPc) * 100, 1) : 0) . '%',
                'EC' => $subEc,
                'EC %' => ($subPc > 0 ? round(($subEc / $subPc) * 100, 1) : 0) . '%',
                'Target' => $subTarget,
                'Order' => $subOrder,
                'Order %' => ($subTarget > 0 ? round(($subOrder / $subTarget) * 100, 1) : 0) . '%',
                'RWO' => $subRwo,
                'RWO %' => ($subPc > 0 ? round(($subRwo / $subPc) * 100, 1) : 0) . '%',
                'PNR' => $subPnr,
                'PNR %' => ($subPc > 0 ? round(($subPnr / $subPc) * 100, 1) : 0) . '%',
                'NGVO' => $subNgvo,
                'NGVO %' => ($subPc > 0 ? round(($subNgvo / $subPc) * 100, 1) : 0) . '%',
                'Pareto' => $subPareto,
                'Pareto %' => ($subPc > 0 ? round(($subPareto / $subPc) * 100, 1) : 0) . '%',
                'Out of Area' => $subOoa,
                'Out of Area %' => ($subAc > 0 ? round(($subOoa / $subAc) * 100, 1) : 0) . '%'
            ];
        }

        if (count($exportData) > 0) {
            $totPareto = $totRwo + $totPnr + $totNgvo;
            $exportData[] = [
                'No' => '',
                'Region' => '',
                'Area' => '',
                'Level' => '',
                'Supervisor' => 'TOTAL KUMULATIF',
                'Tanggal' => '',
                'PC' => $totPc,
                'AC' => $totAc,
                'Visit %' => ($totPc > 0 ? round(($totAc / $totPc) * 100, 1) : 0) . '%',
                'EC' => $totEc,
                'EC %' => ($totPc > 0 ? round(($totEc / $totPc) * 100, 1) : 0) . '%',
                'Target' => $totTarget,
                'Order' => $totOrder,
                'Order %' => ($totTarget > 0 ? round(($totOrder / $totTarget) * 100, 1) : 0) . '%',
                'RWO' => $totRwo,
                'RWO %' => ($totPc > 0 ? round(($totRwo / $totPc) * 100, 1) : 0) . '%',
                'PNR' => $totPnr,
                'PNR %' => ($totPc > 0 ? round(($totPnr / $totPc) * 100, 1) : 0) . '%',
                'NGVO' => $totNgvo,
                'NGVO %' => ($totPc > 0 ? round(($totNgvo / $totPc) * 100, 1) : 0) . '%',
                'Pareto' => $totPareto,
                'Pareto %' => ($totPc > 0 ? round(($totPareto / $totPc) * 100, 1) : 0) . '%',
                'Out of Area' => $totOoa,
                'Out of Area %' => ($totAc > 0 ? round(($totOoa / $totAc) * 100, 1) : 0) . '%'
            ];
        }

        return $exportData;
    }

    public function headings(): array
    {
        return [
            'No',
            'Region',
            'Area',
            'Level',
            'Supervisor',
            'Tanggal',
            'PC',
            'AC',
            'Visit %',
            'EC',
            'EC %',
            'Target',
            'Order',
            'Order %',
            'RWO',
            'RWO %',
            'PNR',
            'PNR %',
            'NGVO',
            'NGVO %',
            'Pareto',
            'Pareto %',
            'Out of Area',
            'Out of Area %'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
