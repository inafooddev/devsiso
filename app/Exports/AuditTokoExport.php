<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditTokoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDrawings
{
    protected $search;
    protected $statusFilter;
    protected $selectedRegion;
    protected $selectedArea;
    protected $exportDistributors;
    protected $dateStart;
    protected $dateEnd;
    protected $exportData;

    public function __construct($search = '', $statusFilter = '', $selectedRegion = '', $selectedArea = '', $exportDistributors = [], $dateStart = '', $dateEnd = '')
    {
        $this->search = $search;
        $this->statusFilter = $statusFilter;
        $this->selectedRegion = $selectedRegion;
        $this->selectedArea = $selectedArea;
        $this->exportDistributors = $exportDistributors;
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
    }

    public function collection()
    {
        $user = Auth::user();
        $userRegionCodes = !empty($user->region_code) ? (array) $user->region_code : [];
        $userAreaCodes = !empty($user->area_code) ? (array) $user->area_code : [];

        $query = DB::table('hasil_audit_toko as hat')
            ->selectRaw('
                hat.created_at,
                md.region_name,
                md.area_name,
                md.distributor_name,
                md.branch_name AS cabang,
                hat.auditor,
                hat.customer_code,
                hat.customer_name,
                hat.customer_address,
                hat.latitude,
                hat.longitude,
                hat.is_toko_fisik,
                hat.is_nama_pemilik,
                hat.is_nama_ktp,
                hat.is_nik_ktp,
                hat.is_no_hp,
                hat.is_no_rekening,
                hat.is_an_rekening,
                hat.is_titik_koordinat,
                hat.keterangan_hasil_audit,
                hat.status_approval,
                hat.alasan_reject,
                hat.approved_by,
                hat.approved_at,
                hat.foto_audit1,
                hat.foto_audit2,
                hat.foto_audit3,
                hat.foto_audit4,
                hat.foto_audit5,
                hat.foto_audit6,
                hat.foto_audit7,
                hat.foto_audit8
            ')
            ->leftJoin('master_distributors as md', 'hat.distributor_code', '=', 'md.distributor_code');

        if (!empty($userAreaCodes)) {
            $query->whereIn('md.area_code', $userAreaCodes);
        } elseif (!empty($userRegionCodes)) {
            $query->whereIn('md.region_code', $userRegionCodes);
        }

        if (!empty($this->statusFilter)) {
            $query->where('hat.status_approval', $this->statusFilter);
        }

        if (!empty($this->selectedRegion)) {
            $query->where('md.region_name', $this->selectedRegion);
        }

        if (!empty($this->selectedArea)) {
            $query->where('md.area_name', $this->selectedArea);
        }

        if (!empty($this->exportDistributors)) {
            $query->whereIn('md.distributor_name', $this->exportDistributors);
        }

        if (!empty($this->dateStart) && !empty($this->dateEnd)) {
            $query->whereBetween('hat.created_at', [$this->dateStart . ' 00:00:00', $this->dateEnd . ' 23:59:59']);
        } elseif (!empty($this->dateStart)) {
            $query->where('hat.created_at', '>=', $this->dateStart . ' 00:00:00');
        } elseif (!empty($this->dateEnd)) {
            $query->where('hat.created_at', '<=', $this->dateEnd . ' 23:59:59');
        }

        if (!empty($this->search)) {
            $q = '%' . trim($this->search) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('hat.customer_name', 'like', $q)
                    ->orWhere('hat.customer_code', 'like', $q)
                    ->orWhere('hat.auditor', 'like', $q)
                    ->orWhere('md.distributor_name', 'like', $q)
                    ->orWhere('md.branch_name', 'like', $q);
            });
        }

        $this->exportData = $query->orderBy('hat.created_at', 'desc')->get();
        return $this->exportData;
    }

    public function headings(): array
    {
        $distributorText = empty($this->exportDistributors) ? 'Semua Distributor' : implode(', ', $this->exportDistributors);
        $dateText = (!empty($this->dateStart) && !empty($this->dateEnd)) ? ($this->dateStart . ' s/d ' . $this->dateEnd) : 'Semua Waktu';
        $statusText = empty($this->statusFilter) ? 'Semua Status' : $this->statusFilter;

        return [
            ['LAPORAN HASIL AUDIT TOKO'],
            ['Tanggal Cetak', ':', date('d-m-Y H:i:s')],
            ['Filter Tanggal', ':', $dateText],
            ['Filter Distributor', ':', $distributorText],
            ['Filter Status', ':', $statusText],
            [''], // Empty row as separator
            [
                'Tanggal Audit',
                'Region',
                'Area',
                'Distributor',
                'Cabang',
                'Auditor',
                'Kode Toko',
                'Nama Toko',
                'Alamat Toko',
                'Latitude',
                'Longitude',
                'Toko Fisik',
                'Nama Pemilik',
                'Nama KTP',
                'NIK KTP',
                'No HP',
                'No Rekening',
                'A/N Rekening',
                'Titik Koordinat',
                'Total Checklist Sesuai',
                'Catatan Audit',
                'Status Approval',
                'Alasan Reject',
                'Approved By',
                'Approved At',
                'Foto 1',
                'Foto 2',
                'Foto 3',
                'Foto 4',
                'Foto 5',
                'Foto 6',
                'Foto 7',
                'Foto 8',
            ]
        ];
    }

    public function map($row): array
    {
        $verifiedCount = collect([
            $row->is_toko_fisik,
            $row->is_nama_pemilik,
            $row->is_nama_ktp,
            $row->is_nik_ktp,
            $row->is_no_hp,
            $row->is_no_rekening,
            $row->is_an_rekening,
            $row->is_titik_koordinat,
        ])->filter()->count();

        return [
            $row->created_at ? date('Y-m-d H:i:s', strtotime($row->created_at)) : '-',
            $row->region_name ?? '-',
            $row->area_name ?? '-',
            $row->distributor_name ?? '-',
            $row->cabang ?? '-',
            $row->auditor ?? '-',
            $row->customer_code ?? '-',
            $row->customer_name ?? '-',
            $row->customer_address ?? '-',
            $row->latitude ?? '-',
            $row->longitude ?? '-',
            $row->is_toko_fisik ? 'Ya' : 'Tidak',
            $row->is_nama_pemilik ? 'Ya' : 'Tidak',
            $row->is_nama_ktp ? 'Ya' : 'Tidak',
            $row->is_nik_ktp ? 'Ya' : 'Tidak',
            $row->is_no_hp ? 'Ya' : 'Tidak',
            $row->is_no_rekening ? 'Ya' : 'Tidak',
            $row->is_an_rekening ? 'Ya' : 'Tidak',
            $row->is_titik_koordinat ? 'Ya' : 'Tidak',
            $verifiedCount . '/8 Sesuai',
            $row->keterangan_hasil_audit ?? '-',
            $row->status_approval ?? 'Pending',
            $row->alasan_reject ?? '-',
            $row->approved_by ?? '-',
            $row->approved_at ? date('Y-m-d H:i:s', strtotime($row->approved_at)) : '-',
            '', // Z: Foto 1
            '', // AA: Foto 2
            '', // AB: Foto 3
            '', // AC: Foto 4
            '', // AD: Foto 5
            '', // AE: Foto 6
            '', // AF: Foto 7
            '', // AG: Foto 8
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $rowNum = 8; // Data starts at row 8
        $colLetters = ['Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG'];

        if ($this->exportData) {
            foreach ($this->exportData as $row) {
                for ($i = 1; $i <= 8; $i++) {
                    $fotoField = 'foto_audit' . $i;
                    if (!empty($row->$fotoField)) {
                        $imagePath = storage_path('app/public/' . $row->$fotoField);
                        if (file_exists($imagePath)) {
                            $drawing = new Drawing();
                            $drawing->setName('Foto ' . $i);
                            $drawing->setDescription('Foto ' . $i);
                            $drawing->setPath($imagePath);
                            $drawing->setHeight(75); // Slightly smaller height (75px)
                            $drawing->setCoordinates($colLetters[$i - 1] . $rowNum);
                            $drawing->setOffsetX(5);
                            $drawing->setOffsetY(5);
                            $drawings[] = $drawing;
                        }
                    }
                }
                $rowNum++;
            }
        }

        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for headers so they don't affect column C auto-sizing
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('C2:H2');
        $sheet->mergeCells('C3:H3');
        $sheet->mergeCells('C4:J4');
        $sheet->mergeCells('C5:H5');

        // Allow text to wrap in the distributor filter row if it's too long
        $sheet->getStyle('C4')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(-1); // Auto-adjust row height

        // Set column widths for photo columns to be wider (prevents horizontal overlapping)
        $photoCols = ['Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG'];
        foreach ($photoCols as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }

        // Set row heights for data rows to fit the images (prevents vertical overlapping)
        if ($this->exportData) {
            $startRow = 8;
            $endRow = 7 + $this->exportData->count();
            for ($i = $startRow; $i <= $endRow; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(70); // Row height 70 points (~93px)
            }
        }

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
            ],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B']
                ]
            ],
        ];
    }
}
