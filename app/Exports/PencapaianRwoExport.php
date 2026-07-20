<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\RemarkListPotensiRwo;

class PencapaianRwoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    protected $query;
    protected $kuartal;

    public function __construct($query, $kuartal)
    {
        $this->query = $query;
        $this->kuartal = $kuartal;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'Region',
            'Area',
            'Supervisor',
            'Distributor Name',
            'Customer Code',
            'Customer PRC',
            'Customer Name',
            'Status SKB',
            'Status Data',
            'Reward %',
            'Target Total',
            'Target Prorata',
            'Actual Total',
            '%',
            'Gap',
            'Month 1',
            'Month 2',
            'Month 3',
            'Kolom/KPI',
            'Reason SKB',
            'Remark Khusus',
        ];
    }

    public function columnFormats(): array
    {
        $accountingFormat = '_(* #,##0_);_(* \(#,##0\);_(* "-"??_);_(@_)';
        
        return [
            'K' => $accountingFormat,
            'L' => $accountingFormat,
            'M' => $accountingFormat,
            'O' => $accountingFormat,
            'P' => $accountingFormat,
            'Q' => $accountingFormat,
            'R' => $accountingFormat,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'J' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            'N' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    public function map($row): array
    {
        $target = $row->total_target ?? 0;
                            
        $rewardPercent = '1,5%';
        if ($target >= 90000000) {
            $rewardPercent = '2,5%';
        } elseif ($target >= 30000000) {
            $rewardPercent = '2%';
        }

        $proratedData = $this->getProratedData($target, $row, $this->kuartal);
        $percent = $proratedData['percent'];
        $activeAchievement = $proratedData['active_achievement'];
        $proratedTarget = $proratedData['prorated_target'];
        $colorLabel = $proratedData['color_label'];
        $gap = $proratedTarget - $activeAchievement;

        $statusSkb = 'Belum';
        if ($row->status_skb === 'Sudah') {
            if ($row->is_approved === 1 || $row->is_approved === true) {
                $statusSkb = 'Approved';
            } elseif ($row->is_approved === 0 || $row->is_approved === false) {
                $statusSkb = 'Rejected';
            } else {
                $statusSkb = 'Submitted';
            }
        }

        $statusData = $row->status_data_lengkap === 'Lengkap' ? 'Lengkap' : 'Belum';

        $remarkData = RemarkListPotensiRwo::where('kuartal', $this->kuartal)
                ->where('distributor_code', $row->distributor_code)
                ->where('customer_code', $row->customer_code)
                ->first();

        return [
            $row->region_name ?? '-',
            $row->area_name ?? '-',
            $row->supervisor_name ?? '-',
            $row->distributor_name ?? '-',
            $row->customer_code,
            $row->customer_prc ?? '-',
            $row->customer_name,
            $statusSkb,
            $statusData,
            $rewardPercent,
            $target,
            $proratedTarget,
            $activeAchievement,
            number_format($percent, 1, ',', '.') . '%',
            $gap,
            $row->month_1_value ?? 0,
            $row->month_2_value ?? 0,
            $row->month_3_value ?? 0,
            $colorLabel,
            $row->skb_reason,
            $remarkData ? $remarkData->remark : '',
        ];
    }

    private function getProratedData($target, $row, $kuartalStr)
    {
        $currentMonth = (int)date('n');
        $currentQuarter = (int)ceil($currentMonth / 3);
        $kuartal = (int)$kuartalStr;
        
        $m1 = (float)($row->month_1_value ?? 0);
        $m2 = (float)($row->month_2_value ?? 0);
        $m3 = (float)($row->month_3_value ?? 0);
        
        $multiplier = 3;
        if ($kuartal === $currentQuarter) {
            $firstMonthOfQ = ($kuartal - 1) * 3 + 1;
            $multiplier = $currentMonth - $firstMonthOfQ + 1;
            if ($multiplier < 1) $multiplier = 1;
            if ($multiplier > 3) $multiplier = 3;
        } elseif ($kuartal > $currentQuarter) {
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }
        
        $proratedTarget = ($target / 3) * $multiplier;
        
        if ($multiplier === 1) {
            $activeAchievement = $m1;
        } elseif ($multiplier === 2) {
            $activeAchievement = $m1 + $m2;
        } else {
            $activeAchievement = $m1 + $m2 + $m3;
        }
        
        $percent = $proratedTarget > 0 ? ($activeAchievement / $proratedTarget) * 100 : 0;
        
        $colorLabel = '3. MERAH';
        if ($percent >= 100) {
            $colorLabel = '1. HIJAU';
        } elseif ($percent >= 80) {
            $colorLabel = '2. KUNING';
        }
        
        return [
            'multiplier' => $multiplier,
            'prorated_target' => $proratedTarget,
            'active_achievement' => $activeAchievement,
            'percent' => $percent,
            'color_label' => $colorLabel,
        ];
    }
}
