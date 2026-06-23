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

        $totPc = 0; $totAc = 0; $totTarget = 0; $totOrder = 0;
        $totRwo = 0; $totPnr = 0; $totNgvo = 0; $totOoa = 0;

        foreach ($this->data as $row) {
            $exportData[] = [
                'No' => $no++,
                'Region' => $row['region_name'],
                'Area' => $row['area_name'],
                'Supervisor' => $row['supervisor_name'],
                'PC' => $row['pc'],
                'AC' => $row['ac'],
                'Visit %' => $row['pc_ac_pct'] . '%',
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
            $totTarget += $row['target'];
            $totOrder += $row['order'];
            $totRwo += $row['rwo'];
            $totPnr += $row['pnr'];
            $totNgvo += $row['ngvo'];
            $totOoa += $row['out_of_area'];
        }

        if (count($exportData) > 0) {
            $totPareto = $totRwo + $totPnr + $totNgvo;
            $exportData[] = [
                'No' => '',
                'Region' => '',
                'Area' => '',
                'Supervisor' => 'TOTAL KUMULATIF',
                'PC' => $totPc,
                'AC' => $totAc,
                'Visit %' => ($totPc > 0 ? round(($totAc / $totPc) * 100, 1) : 0) . '%',
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
            'Supervisor',
            'PC',
            'AC',
            'Visit %',
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
