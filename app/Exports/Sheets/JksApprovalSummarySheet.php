<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\JksApprovalParserTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JksApprovalSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    use JksApprovalParserTrait;

    protected Collection $rows;
    protected array $salesmanMap;
    protected Collection $jksMap;
    private int $rowNumber = 0;

    public function __construct(Collection $rows, array $salesmanMap, Collection $jksMap)
    {
        $this->rows = $rows;
        $this->salesmanMap = $salesmanMap;
        $this->jksMap = $jksMap;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Ringkasan Pengajuan';
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Tiket',
            'Tanggal Pengajuan',
            'Status',
            'Tipe Aksi',
            'Tipe Hapus',
            'Region',
            'Area',
            'Kode Distributor',
            'Nama Distributor',
            'Bulan Target',
            'Kode Salesman',
            'Nama Salesman',
            'Jadwal Hari',
            'Jadwal Minggu',
            'Jumlah Toko',
            'Rincian Toko',
            'Alasan Pengajuan',
            'Diajukan Oleh',
            'Diperiksa Oleh',
            'Tanggal Diperiksa',
            'Alasan Penolakan',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $payload = json_decode($row->payload, true) ?: [];

        $salesman = $this->parseSalesman($row, $payload, $this->salesmanMap);
        $hariStr = $this->parseHari($payload, $row->reason);
        $mingguStr = $this->parseMinggu($payload, $row->reason);
        $bulanStr = $this->parseBulan($row, $payload, $this->jksMap);
        $tokoList = $this->parseTokoList($row, $payload, $this->jksMap);

        $tokoCount = count($tokoList);
        $tokoSummary = '-';
        if ($tokoCount === 1) {
            $code = $tokoList[0]['code'] ?? '-';
            $name = $tokoList[0]['name'] ?? '-';
            $tokoSummary = $code !== '-' ? "{$code} - {$name}" : $name;
        } elseif ($tokoCount > 1) {
            $preview = [];
            foreach (array_slice($tokoList, 0, 2) as $t) {
                $preview[] = ($t['code'] ?? '') . ' (' . ($t['name'] ?? '') . ')';
            }
            $tokoSummary = "{$tokoCount} Toko: " . implode(', ', $preview) . ($tokoCount > 2 ? ', dst.' : '');
        }

        $tanggalPengajuan = $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '-';
        $tanggalDiperiksa = ($row->status !== 'PENDING' && $row->updated_at) ? Carbon::parse($row->updated_at)->format('Y-m-d H:i:s') : '-';

        return [
            $this->rowNumber,
            $row->id,
            $tanggalPengajuan,
            $row->status,
            str_replace('_', ' ', $row->action_type),
            $row->delete_type ?? '-',
            $row->region_name ?? $row->region_code ?? '-',
            $row->area_name ?? $row->area_code ?? '-',
            $row->distributor_code,
            $row->distributor_name ?? '-',
            $bulanStr,
            $salesman['code'],
            $salesman['name'],
            $hariStr,
            $mingguStr,
            $tokoCount > 0 ? $tokoCount : '-',
            $tokoSummary,
            $row->reason ?: '-',
            $row->maker_name ?? '-',
            $row->checker_name ?? '-',
            $tanggalDiperiksa,
            $row->reject_reason ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Freeze header row
        $sheet->freezePane('A2');

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '1E293B'], // Slate 800
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // Set header height
                $sheet->getRowDimension(1)->setRowHeight(30);

                if ($highestRow >= 2) {
                    // Enable AutoFilter
                    $sheet->setAutoFilter("A1:{$highestCol}{$highestRow}");

                    // Apply borders to all cells
                    $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()->applyFromArray([
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ]);

                    // Alignments
                    $sheet->getStyle("A2:I{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Column J (Nama Distributor): Left
                    $sheet->getStyle("J2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    
                    // Columns K - P: Center
                    $sheet->getStyle("K2:P{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Columns Q - T: Left
                    $sheet->getStyle("Q2:T{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Column U (Tanggal Diperiksa): Center
                    $sheet->getStyle("U2:U{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Column V (Alasan Penolakan): Left
                    $sheet->getStyle("V2:V{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Conditional colors for Status column (D)
                    for ($r = 2; $r <= $highestRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(22);
                        $statusValue = strtoupper(trim((string) $sheet->getCell("D{$r}")->getValue()));
                        $cellStyle = $sheet->getStyle("D{$r}");

                        if ($statusValue === 'APPROVED') {
                            $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
                            $cellStyle->getFont()->getColor()->setRGB('065F46');
                            $cellStyle->getFont()->setBold(true);
                        } elseif ($statusValue === 'PENDING') {
                            $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                            $cellStyle->getFont()->getColor()->setRGB('92400E');
                            $cellStyle->getFont()->setBold(true);
                        } elseif ($statusValue === 'REJECTED') {
                            $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                            $cellStyle->getFont()->getColor()->setRGB('991B1B');
                            $cellStyle->getFont()->setBold(true);
                        }
                    }
                }
            },
        ];
    }
}
