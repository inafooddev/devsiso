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

class JksApprovalDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    use JksApprovalParserTrait;

    protected Collection $items;
    private int $rowNumber = 0;

    public function __construct(Collection $rows, array $salesmanMap, Collection $jksMap)
    {
        $this->items = $this->buildFlattenedItems($rows, $salesmanMap, $jksMap);
    }

    protected function buildFlattenedItems(Collection $rows, array $salesmanMap, Collection $jksMap): Collection
    {
        $flattened = [];

        foreach ($rows as $row) {
            $payload = json_decode($row->payload, true) ?: [];

            $salesman = $this->parseSalesman($row, $payload, $salesmanMap);
            $hariStr = $this->parseHari($payload, $row->reason);
            $mingguStr = $this->parseMinggu($payload, $row->reason);
            $bulanStr = $this->parseBulan($row, $payload, $jksMap);
            $tokoList = $this->parseTokoList($row, $payload, $jksMap);

            $tanggalPengajuan = $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '-';
            $tanggalDiperiksa = ($row->status !== 'PENDING' && $row->updated_at) ? Carbon::parse($row->updated_at)->format('Y-m-d H:i:s') : '-';

            $baseData = [
                'id' => $row->id,
                'created_at' => $tanggalPengajuan,
                'status' => $row->status,
                'action_type' => str_replace('_', ' ', $row->action_type),
                'delete_type' => $row->delete_type ?? '-',
                'region' => $row->region_name ?? $row->region_code ?? '-',
                'area' => $row->area_name ?? $row->area_code ?? '-',
                'distributor_code' => $row->distributor_code,
                'distributor_name' => $row->distributor_name ?? '-',
                'bulan' => $bulanStr,
                'salesman_code' => $salesman['code'],
                'salesman_name' => $salesman['name'],
                'hari' => $hariStr,
                'minggu' => $mingguStr,
                'reason' => $row->reason ?: '-',
                'maker_name' => $row->maker_name ?? '-',
                'checker_name' => $row->checker_name ?? '-',
                'updated_at' => $tanggalDiperiksa,
                'reject_reason' => $row->reject_reason ?: '-',
            ];

            if (!empty($tokoList)) {
                foreach ($tokoList as $toko) {
                    $item = $baseData;
                    $item['customer_code'] = $toko['code'] ?? '-';
                    $item['customer_name'] = $toko['name'] ?? '-';
                    $flattened[] = $item;
                }
            } else {
                $item = $baseData;
                $item['customer_code'] = '-';
                $item['customer_name'] = 'Pertukaran Jadwal / Massal';
                $flattened[] = $item;
            }
        }

        return collect($flattened);
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    public function title(): string
    {
        return 'Rincian Toko';
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
            'Kode Toko',
            'Nama Toko',
            'Hari Kunjungan',
            'Minggu Kunjungan',
            'Alasan Pengajuan',
            'Diajukan Oleh',
            'Diperiksa Oleh',
            'Tanggal Diperiksa',
            'Alasan Penolakan',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $item['id'],
            $item['created_at'],
            $item['status'],
            $item['action_type'],
            $item['delete_type'],
            $item['region'],
            $item['area'],
            $item['distributor_code'],
            $item['distributor_name'],
            $item['bulan'],
            $item['salesman_code'],
            $item['salesman_name'],
            $item['customer_code'],
            $item['customer_name'],
            $item['hari'],
            $item['minggu'],
            $item['reason'],
            $item['maker_name'],
            $item['checker_name'],
            $item['updated_at'],
            $item['reject_reason'],
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
                    'color' => ['rgb' => '0F766E'], // Teal 700
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

                    // Apply borders
                    $sheet->getStyle("A1:{$highestCol}{$highestRow}")->getBorders()->getAllBorders()->applyFromArray([
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ]);

                    // Alignments
                    $sheet->getStyle("A2:I{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Column J (Nama Distributor): Left
                    $sheet->getStyle("J2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    
                    // Column K - L (Bulan, Kode Salesman): Center
                    $sheet->getStyle("K2:L{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Column M (Nama Salesman): Left
                    $sheet->getStyle("M2:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Column N (Kode Toko): Center
                    $sheet->getStyle("N2:N{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Column O (Nama Toko): Left
                    $sheet->getStyle("O2:O{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Column P - Q (Hari, Minggu): Center
                    $sheet->getStyle("P2:Q{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Columns R - T (Alasan, Maker, Checker): Left
                    $sheet->getStyle("R2:T{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

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
