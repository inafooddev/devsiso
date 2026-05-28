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

    protected $includeKtp = true;
    protected $includeToko = true;
    protected $includeToko2 = true;
    protected $includeToko3 = true;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        $this->includeKtp = $filters['export_foto_ktp'] ?? true;
        $this->includeToko = $filters['export_foto_toko'] ?? true;
        $this->includeToko2 = $filters['export_foto_toko2'] ?? true;
        $this->includeToko3 = $filters['export_foto_toko3'] ?? true;
        
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
            } elseif ($filterType === 'complete') {
                $query->whereNotNull('nama_pemilik_toko')->where('nama_pemilik_toko', '!=', '')
                      ->whereNotNull('nama_ktp')->where('nama_ktp', '!=', '')
                      ->whereNotNull('nik_ktp')->where('nik_ktp', '!=', '')
                      ->whereNotNull('nama_bank')->where('nama_bank', '!=', '')
                      ->whereNotNull('no_rekening')->where('no_rekening', '!=', '')
                      ->whereNotNull('nama_pemilik_norek')->where('nama_pemilik_norek', '!=', '');
            } elseif ($filterType === 'not_complete') {
                $query->where(function($q) {
                    $q->whereNull('nama_pemilik_toko')->orWhere('nama_pemilik_toko', '')
                      ->orWhereNull('nama_ktp')->orWhere('nama_ktp', '')
                      ->orWhereNull('nik_ktp')->orWhere('nik_ktp', '')
                      ->orWhereNull('nama_bank')->orWhere('nama_bank', '')
                      ->orWhereNull('no_rekening')->orWhere('no_rekening', '')
                      ->orWhereNull('nama_pemilik_norek')->orWhere('nama_pemilik_norek', '');
                });
            }
        }

        if (!empty($this->filters['filter_region_code'])) {
            $query->where('region_code', $this->filters['filter_region_code']);
        }
        if (!empty($this->filters['filter_area_code'])) {
            $query->where('area_code', $this->filters['filter_area_code']);
        }
        if (!empty($this->filters['filter_branch_name'])) {
            $query->where('branch_name', $this->filters['filter_branch_name']);
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

        $this->items = $query->orderBy('id', 'desc')->get();
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        $headers = [
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
        ];

        if ($this->includeKtp) {
            $headers[] = 'Foto KTP';
        }

        $headers = array_merge($headers, [
            'Nama Bank',
            'No Rekening',
            'Nama Pemilik Norek',
        ]);

        if ($this->includeToko) {
            $headers[] = 'Foto Toko by GPS';
        }
        if ($this->includeToko2) {
            $headers[] = 'Foto Toko by team Elite (Tampak Depan)';
        }
        if ($this->includeToko3) {
            $headers[] = 'Foto Toko by team Elite tampak dalam';
        }

        $headers = array_merge($headers, [
            'Keterangan',
            'Status',
            'Status Validasi',
        ]);

        return $headers;
    }

    public function map($item): array
    {
        $row = [
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
        ];

        if ($this->includeKtp) {
            $row[] = ''; // Placeholder Foto KTP
        }

        $row = array_merge($row, [
            $item->nama_bank,
            $item->no_rekening ? "'" . $item->no_rekening : '',
            $item->nama_pemilik_norek,
        ]);

        if ($this->includeToko) {
            $row[] = ''; // Placeholder Foto Toko GPS
        }
        if ($this->includeToko2) {
            $row[] = ''; // Placeholder Foto Toko Depan
        }
        if ($this->includeToko3) {
            $row[] = ''; // Placeholder Foto Toko Dalam
        }

        $row = array_merge($row, [
            $item->keterangan,
            $item->status,
            $item->is_valid ? 'Valid (Toko Ada)' : 'Tidak Valid (Toko Tidak Ada)',
        ]);

        return $row;
    }

    public function columnFormats(): array
    {
        $formats = [
            'J' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // No HP
            'K' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // Latitude
            'L' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // Longitude
            'O' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // NIK KTP
        ];

        $rekIndex = 15; // NIK KTP is 15th
        if ($this->includeKtp) $rekIndex++;
        $rekIndex += 2; // Nama Bank is next, then No Rekening
        
        $rekLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($rekIndex);
        $formats[$rekLetter] = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT;

        return $formats;
    }

    private function createDrawing($name, $path, $coordinates, $row)
    {
        if (file_exists($path)) {
            $drawing = new Drawing();
            $drawing->setName($name);
            $drawing->setDescription($name);
            $drawing->setPath($path);
            $drawing->setCoordinates($coordinates . $row);
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(10);
            
            // Proportional resize to fit within a 230x190px box
            // Max Width = 230, Max Height = 190 (ratio = 1.21)
            $imageSize = @getimagesize($path);
            if ($imageSize) {
                $w = $imageSize[0];
                $h = $imageSize[1];
                if ($h > 0 && ($w / $h) > 1.2105) {
                    $drawing->setWidth(230);
                } else {
                    $drawing->setHeight(190);
                }
            } else {
                $drawing->setHeight(190);
            }
            $drawing->setResizeProportional(true);
            return $drawing;
        }
        return null;
    }

    private function getPhotoColumns()
    {
        $cols = [];
        $currentIndex = 16; // Column after NIK KTP (1-based index)
        
        if ($this->includeKtp) {
            $cols['ktp'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIndex);
            $currentIndex++;
        }
        
        // Add Nama Bank, No Rekening, Nama Pemilik Norek
        $currentIndex += 3;
        
        if ($this->includeToko) {
            $cols['toko'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIndex);
            $currentIndex++;
        }
        if ($this->includeToko2) {
            $cols['toko2'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIndex);
            $currentIndex++;
        }
        if ($this->includeToko3) {
            $cols['toko3'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIndex);
            $currentIndex++;
        }
        
        return $cols;
    }

    public function drawings()
    {
        $drawings = [];
        $photoCols = $this->getPhotoColumns();
        
        foreach ($this->items as $index => $item) {
            $row = $index + 2; // Row starts at 2 (1 is header)
            
            // Foto KTP
            if ($this->includeKtp && $item->foto_ktp) {
                $path = storage_path('app/public/' . $item->foto_ktp);
                $drawing = $this->createDrawing('Foto KTP', $path, $photoCols['ktp'], $row);
                if ($drawing) {
                    $drawings[] = $drawing;
                }
            }

            // Foto Toko by GPS
            if ($this->includeToko && $item->foto_toko) {
                $path = storage_path('app/public/' . $item->foto_toko);
                $drawing = $this->createDrawing('Foto Toko by GPS', $path, $photoCols['toko'], $row);
                if ($drawing) {
                    $drawings[] = $drawing;
                }
            }

            // Foto Toko 2 (Tampak Depan)
            if ($this->includeToko2 && $item->foto_toko2) {
                $path = storage_path('app/public/' . $item->foto_toko2);
                $drawing = $this->createDrawing('Foto Tampak Depan', $path, $photoCols['toko2'], $row);
                if ($drawing) {
                    $drawings[] = $drawing;
                }
            }

            // Foto Toko 3 (Tampak Dalam)
            if ($this->includeToko3 && $item->foto_toko3) {
                $path = storage_path('app/public/' . $item->foto_toko3);
                $drawing = $this->createDrawing('Foto Tampak Dalam', $path, $photoCols['toko3'], $row);
                if ($drawing) {
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
                $photoCols = $this->getPhotoColumns();
                $hasAnyPhotos = count($photoCols) > 0;
                
                // Set baris header lebih tinggi
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Atur tinggi setiap baris data agar pas dengan tinggi gambar (190 + offset = ~210)
                // Hanya jika ada foto yang di-export
                if ($hasAnyPhotos) {
                    for ($row = 2; $row <= $totalRows; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(160);
                    }
                }
                
                $totalCols = 15 + ($this->includeKtp ? 1 : 0) + 3 + ($this->includeToko ? 1 : 0) + ($this->includeToko2 ? 1 : 0) + ($this->includeToko3 ? 1 : 0) + 3;
                $lastLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // Atur lebar kolom otomatis untuk kolom teks, dan lebar tetap 36 untuk kolom foto
                foreach (range('A', $lastLetter) as $col) {
                    if (in_array($col, array_values($photoCols))) {
                        $sheet->getColumnDimension($col)->setWidth(36);
                    } else {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                }
                
                // Set alignment vertical center untuk semua sel data
                $sheet->getStyle('A1:' . $lastLetter . $totalRows)
                      ->getAlignment()
                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
        ];
    }
}
