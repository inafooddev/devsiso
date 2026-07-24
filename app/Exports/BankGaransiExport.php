<?php

namespace App\Exports;

use App\Models\BankGaransi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class BankGaransiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $garansis;

    public function __construct(Collection $garansis)
    {
        $this->garansis = $garansis;
    }

    public function collection()
    {
        return $this->garansis;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Distributor',
            'Nama Distributor',
            'Region',
            'Status Distributor',
            'Nama Bank',
            'Nomor Jaminan',
            'Nomor Seri',
            'Nilai Jaminan',
            'Tanggal Terbit',
            'Tanggal Jatuh Tempo',
            'Masa Berlaku (Hari)',
            'Status BG',
            'Status Perpanjangan',
            'Keterangan',
        ];
    }

    public function map($garansi): array
    {
        static $row = 0;
        $row++;
        
        $daysLeft = \Carbon\Carbon::now()->startOfDay()->diffInDays($garansi->tanggal_jatuh_tempo, false);
        
        return [
            $row,
            $garansi->distributor_code,
            $garansi->distributor->short_name ?? '-',
            $garansi->distributor->region_code ?? '-',
            ($garansi->distributor && $garansi->distributor->is_active) ? 'Aktif' : 'Inaktif',
            $garansi->nama_bank,
            $garansi->nomor_jaminan,
            $garansi->nomor_seri,
            $garansi->nilai_jaminan,
            $garansi->tanggal_terbit->format('Y-m-d'),
            $garansi->tanggal_jatuh_tempo->format('Y-m-d'),
            $daysLeft,
            $garansi->status,
            $garansi->status_perpanjangan,
            $garansi->keterangan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
