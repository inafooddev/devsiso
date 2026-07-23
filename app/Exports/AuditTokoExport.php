<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditTokoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $search;
    protected $statusFilter;
    protected $selectedRegion;
    protected $selectedArea;
    protected $exportDistributors;
    protected $dateStart;
    protected $dateEnd;

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
                hat.approved_at
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

        return $query->orderBy('hat.created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
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
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B']
                ]
            ],
        ];
    }
}
