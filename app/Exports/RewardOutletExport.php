<?php

namespace App\Exports;

use App\Models\RewardOutlet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class RewardOutletExport implements FromCollection, WithHeadings, WithMapping, WithDrawings, WithEvents, WithColumnFormatting
{
    protected $filters;
    protected $items;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        
        // Fetch items now to use in both collection() and drawings()
        $query = RewardOutlet::query();

        // Apply region access based on login region role
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }

        // Apply filter type
        if (!empty($this->filters['filter_type'])) {
            $filterType = $this->filters['filter_type'];
            if ($filterType === 'tanpa_ktp') {
                $query->where(function($q) {
                    $q->whereNull('nik_ktp')->orWhere('nik_ktp', '');
                });
            } elseif ($filterType === 'tanpa_foto_ktp') {
                $query->where(function($q) {
                    $q->whereNull('foto_ktp')->orWhere('foto_ktp', '');
                });
            } elseif ($filterType === 'tanpa_rekening') {
                $query->where(function($q) {
                    $q->whereNull('no_rekening')->orWhere('no_rekening', '');
                });
            } elseif ($filterType === 'tanpa_foto_toko') {
                $query->where(function($q) {
                    $q->whereNull('foto_toko')->orWhere('foto_toko', '');
                });
            } elseif ($filterType === 'tanpa_tikor') {
                $query->where(function($q) {
                    $q->whereNull('latitude')->orWhere('latitude', '')
                      ->orWhereNull('longitude')->orWhere('longitude', '');
                });
            }
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('region_code', 'ilike', '%' . $search . '%')
                  ->orWhere('region_name', 'ilike', '%' . $search . '%')
                  ->orWhere('area_code', 'ilike', '%' . $search . '%')
                  ->orWhere('area_name', 'ilike', '%' . $search . '%')
                  ->orWhere('branch_name', 'ilike', '%' . $search . '%')
                  ->orWhere('customer_code', 'ilike', '%' . $search . '%')
                  ->orWhere('customer_name', 'ilike', '%' . $search . '%')
                  ->orWhere('eskalink_code', 'ilike', '%' . $search . '%')
                  ->orWhere('nama_pemilik_toko', 'ilike', '%' . $search . '%');
            });
        }

        $this->items = $query->latest()->get();
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'Region Code',
            'Region Name',
            'Area Code',
            'Area Name',
            'Branch Name',
            'Eskalink Code',
            'Customer Code',
            'Customer Name',
            'Alamat',
            'No HP',
            'Latitude',
            'Longitude',
            'Nama Pemilik Toko',
            'Nama KTP',
            'NIK KTP',
            'Foto KTP',
            'Nama Bank',
            'No Rekening',
            'Nama Pemilik Norek',
            'Foto Toko',
        ];
    }

    public function map($item): array
    {
        return [
            $item->region_code,
            $item->region_name,
            $item->area_code,
            $item->area_name,
            $item->branch_name,
            $item->eskalink_code,
            $item->customer_code,
            $item->customer_name,
            $item->alamat,
            $item->no_hp ? "'" . $item->no_hp : '',
            $item->latitude ? "'" . $item->latitude : '',
            $item->longitude ? "'" . $item->longitude : '',
            $item->nama_pemilik_toko,
            $item->nama_ktp,
            $item->nik_ktp ? "'" . $item->nik_ktp : '',
            '', // Placeholder untuk Foto KTP (Drawing)
            $item->nama_bank,
            $item->no_rekening ? "'" . $item->no_rekening : '',
            $item->nama_pemilik_norek,
            '', // Placeholder untuk Foto Toko (Drawing)
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // No HP
            'K' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // Latitude
            'L' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // Longitude
            'O' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // NIK KTP
            'R' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // No Rekening
        ];
    }

    public function drawings()
    {
        $drawings = [];
        
        foreach ($this->items as $index => $item) {
            $row = $index + 2; // Row starts at 2 (1 is header)
            
            // Foto KTP
            if ($item->foto_ktp) {
                $path = storage_path('app/public/' . $item->foto_ktp);
                if (file_exists($path)) {
                    $drawing = new Drawing();
                    $drawing->setName('Foto KTP');
                    $drawing->setDescription('Foto KTP');
                    $drawing->setPath($path);
                    $drawing->setHeight(130);
                    $drawing->setCoordinates('P' . $row);
                    // Tambahkan offset agar gambar berada di tengah sel
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawings[] = $drawing;
                }
            }

            // Foto Toko
            if ($item->foto_toko) {
                $path = storage_path('app/public/' . $item->foto_toko);
                if (file_exists($path)) {
                    $drawing = new Drawing();
                    $drawing->setName('Foto Toko');
                    $drawing->setDescription('Foto Toko');
                    $drawing->setPath($path);
                    $drawing->setHeight(130);
                    $drawing->setCoordinates('T' . $row);
                    // Tambahkan offset agar gambar berada di tengah sel
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawings[] = $drawing;
                }
            }
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalRows = count($this->items) + 1;
                
                // Set baris header lebih tinggi
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Atur tinggi setiap baris data agar pas dengan tinggi gambar (130 + offset = ~160)
                for ($row = 2; $row <= $totalRows; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(160);
                }
                
                // Atur lebar kolom otomatis untuk kolom teks, dan lebar tetap 36 untuk kolom foto (P & T)
                foreach (range('A', 'T') as $col) {
                    if ($col === 'P' || $col === 'T') {
                        $sheet->getColumnDimension($col)->setWidth(36);
                    } else {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                }
                
                // Set alignment vertical center untuk semua sel data
                $sheet->getStyle('A1:T' . $totalRows)
                      ->getAlignment()
                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
        ];
    }
}
