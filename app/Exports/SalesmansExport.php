<?php

namespace App\Exports;

use App\Models\Salesman;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SalesmansExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithDrawings, WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Salesman::query()
            ->with('masterDistributor.area.region')
            ->join('master_distributors', 'salesmans.distributor_code', '=', 'master_distributors.distributor_code')
            ->join('master_areas', 'master_distributors.area_code', '=', 'master_areas.area_code')
            ->join('master_regions', 'master_distributors.region_code', '=', 'master_regions.region_code');

        // Terapkan filter
        if (!empty($this->filters['regionFilter'])) {
            $query->where('master_distributors.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('master_distributors.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('salesmans.distributor_code', $this->filters['distributorFilter']);
        }
        if (!empty($this->filters['search'])) {
            // Gunakan ILIKE untuk PostgreSQL
            $query->where(function ($q) {
                $q->where('salesmans.salesman_code', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('salesmans.salesman_name', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('master_distributors.distributor_name', 'ILIKE', '%' . $this->filters['search'] . '%');
            });
        }

        return $query->select('salesmans.*', 'master_distributors.distributor_name', 'master_areas.area_name', 'master_regions.region_name')
            ->latest('salesmans.created_at');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Region',
            'Area',
            'Code',
            'Distributor',
            'Salesman Code',
            'Salesman Name',
            'Tipe',
            'Status',
            'Join Date',
            'Nama Bank',
            'Nama Rekening',
            'Nomor Rekening',
            'Foto KTP',
            'Foto NPWP',
            'Foto Bank',
            'Foto SKB',
        ];
    }

    /**
     * @param Salesman $salesman
     * @return array
     */
    public function map($salesman): array
    {
        return [
            $salesman->region_name, // Dari join
            $salesman->area_name, // Dari join
            $salesman->distributor_code,
            $salesman->distributor_name, // Dari join
            $salesman->salesman_code,
            $salesman->salesman_name,
            $salesman->is_principle ? 'Principal' : 'Distributor',
            $salesman->is_active ? 'Aktif' : 'Tidak Aktif',
            $salesman->join_date ? \Carbon\Carbon::parse($salesman->join_date)->format('Y-m-d') : '',
            $salesman->bank,
            $salesman->bank_name,
            $salesman->bank_no,
            $this->formatFileCell($salesman->foto_ktp),
            $this->formatFileCell($salesman->foto_npwp),
            $this->formatFileCell($salesman->foto_bank),
            $this->formatFileCell($salesman->foto_skb),
        ];
    }

    private function formatFileCell($file)
    {
        if (!$file) return '';
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return asset('storage/' . $file);
        }
        return ''; // Kosongkan cell untuk gambar, karena akan digambar via drawings()
    }

    public function drawings()
    {
        $drawings = [];
        $salesmans = $this->query()->get();
        $row = 2; // Baris 1 adalah header

        foreach ($salesmans as $salesman) {
            $files = [
                'M' => $salesman->foto_ktp,
                'N' => $salesman->foto_npwp,
                'O' => $salesman->foto_bank,
                'P' => $salesman->foto_skb,
            ];

            foreach ($files as $col => $file) {
                if ($file && Storage::disk('public')->exists($file)) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $absolutePath = storage_path('app/public/' . $file);
                        $drawing = new Drawing();
                        $drawing->setName('Foto');
                        $drawing->setDescription('Foto Salesman');
                        $drawing->setPath($absolutePath);
                        $drawing->setCoordinates($col . $row);
                        
                        // Kembalikan ke pengaturan tinggi sederhana agar tidak overflow
                        $drawing->setHeight(55);
                        $drawing->setOffsetX(15); // Margin kiri agar agak ke tengah
                        $drawing->setOffsetY(5);  // Margin atas
                        
                        $drawings[] = $drawing;
                    }
                }
            }
            $row++;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $salesmansCount = $this->query()->count();

                // Set lebar kolom untuk kolom gambar
                $sheet->getColumnDimension('M')->setWidth(25);
                $sheet->getColumnDimension('N')->setWidth(25);
                $sheet->getColumnDimension('O')->setWidth(25);
                $sheet->getColumnDimension('P')->setWidth(25);

                // Set tinggi baris
                for ($row = 2; $row <= $salesmansCount + 1; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(65);
                }

                // Middle & Center Align untuk seluruh data
                $sheet->getStyle('A1:P' . ($salesmansCount + 1))->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
