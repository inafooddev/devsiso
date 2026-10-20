<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AnalisaKunjunganSeFindingsExport implements WithMultipleSheets
{
    protected $seHierarchies;
    protected $findings;

    public function __construct($seHierarchies, $findings)
    {
        $this->seHierarchies = $seHierarchies;
        $this->findings = $findings;
    }

    public function sheets(): array
    {
        return [
            new AnalisaKunjunganSeFindingsSummarySheet($this->seHierarchies),
            new AnalisaKunjunganSeFindingsDetailSheet($this->findings),
        ];
    }
}

class AnalisaKunjunganSeFindingsSummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $seHierarchies;

    public function __construct($seHierarchies)
    {
        $this->seHierarchies = $seHierarchies;
    }

    public function title(): string
    {
        return 'Ringkasan SE';
    }

    public function headings(): array
    {
        return [
            'No',
            'Distributor',
            'Kode SE',
            'Nama SE',
            'Toko Terdampak',
            'Total Kasus Temuan',
            'Kasus Kritis (High)',
            'Kasus Sedang (Medium)',
            'Kasus Rendah (Low)',
            'Total EC',
            'Total Nilai Order (Rp)',
        ];
    }

    public function collection()
    {
        $result = collect();
        $no = 1;

        $totStores = 0;
        $totFindings = 0;
        $totHigh = 0;
        $totMed = 0;
        $totLow = 0;
        $totEc = 0;
        $totVal = 0;

        foreach ($this->seHierarchies as $se) {
            $totStores += $se->unique_stores;
            $totFindings += $se->total_findings;
            $totHigh += $se->high_count;
            $totMed += $se->medium_count;
            $totLow += $se->low_count;
            $totEc += $se->total_ec;
            $totVal += $se->total_val_order;

            $result->push([
                $no++,
                $se->distributor_name,
                $se->salesman_code,
                $se->salesman_name,
                $se->unique_stores,
                $se->total_findings,
                $se->high_count,
                $se->medium_count,
                $se->low_count,
                $se->total_ec,
                $se->total_val_order,
            ]);
        }

        // Baris Grand Total
        $result->push([
            'TOTAL',
            '',
            '',
            count($this->seHierarchies) . ' Salesman',
            $totStores,
            $totFindings,
            $totHigh,
            $totMed,
            $totLow,
            $totEc,
            $totVal,
        ]);

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = count($this->seHierarchies) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            $totalRows => [
                'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9'],
                ],
            ],
        ];
    }
}

class AnalisaKunjunganSeFindingsDetailSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $findings;

    public function __construct($findings)
    {
        $this->findings = $findings;
    }

    public function title(): string
    {
        return 'Detail Temuan';
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Jam In',
            'Jam Out',
            'Durasi Toko',
            'Distributor',
            'Kode SE',
            'Nama SE',
            'Kode Toko',
            'Nama Toko',
            'Alamat Toko',
            'Kategori Anomali',
            'Tingkat Keparahan',
            'Bukti / Detail Temuan',
            'Jarak ke Depo (m)',
            'Jarak ke Master (m)',
            'Status Visit',
            'Status EC',
            'Qty Order',
            'Nilai Order (Rp)',
        ];
    }

    public function collection()
    {
        $result = collect();
        $no = 1;

        $severityLabels = [
            'high' => 'Kritis (High)',
            'medium' => 'Sedang (Medium)',
            'low' => 'Rendah (Low)',
        ];

        foreach ($this->findings as $item) {
            // Urai setiap anomali yang dimiliki oleh kunjungan toko ini
            $anomalies = !empty($item->anomalies) ? $item->anomalies : [[
                'label' => 'Anomali Kunjungan',
                'severity' => $item->primary_severity ?? 'low',
                'detail' => '-',
            ]];

            foreach ($anomalies as $anomaly) {
                $sevKey = $anomaly['severity'] ?? ($item->primary_severity ?? 'low');
                $sevLabel = $severityLabels[$sevKey] ?? ucfirst($sevKey);

                $result->push([
                    $no++,
                    $item->formatted_date ?? $item->tanggal,
                    $item->time_in ?? '-',
                    $item->time_out ?? '-',
                    $item->time_consume ?? '-',
                    $item->distributor_name ?? '-',
                    $item->salesman_code ?? '-',
                    $item->salesman_name ?? '-',
                    $item->custno ?? '-',
                    $item->custname ?? '-',
                    $item->address ?? '-',
                    $anomaly['label'] ?? '-',
                    $sevLabel,
                    $anomaly['detail'] ?? '-',
                    $item->dist_to_distributor !== null ? $item->dist_to_distributor : '-',
                    $item->dist_to_master !== null ? $item->dist_to_master : '-',
                    $item->flag_visit ?? '-',
                    $item->flag_ec ?? '-',
                    $item->qty_order ?? 0,
                    $item->val_order ?? 0,
                ]);
            }
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
