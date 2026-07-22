<?php

namespace App\Http\Controllers\Mobile\Audit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRegionCodes = !empty($user->region_code) ? (array) $user->region_code : [];
        $userAreaCodes = !empty($user->area_code) ? (array) $user->area_code : [];

        $outletsQuery = DB::table('list_outlet_audit as l')
            ->selectRaw("
                md.region_name,
                md.area_name,
                l.distributor_code,
                md.distributor_name,
                md.branch_name AS cabang,
                l.customer_code,
                l.customer_name,
                l.customer_address,
                ro.no_hp,
                ro.nama_pemilik_toko,
                ro.nama_ktp,
                ro.nik_ktp,
                ro.nama_bank,
                ro.no_rekening,
                ro.nama_pemilik_norek,
                ro.foto_ktp,
                ro.foto_toko2 AS tampak_depan,
                ro.foto_toko3 AS tampak_dalam,
                CASE
                    WHEN ro.eskalink_code IS NOT NULL THEN 'RWO'
                    ELSE 'Non RWO'
                END AS rwo_status,
                CASE
                    WHEN hat.customer_code IS NOT NULL THEN 'Sudah'
                    ELSE 'Belum'
                END AS status_audit,
                hat.keterangan_hasil_audit,
                hat.is_toko_fisik,
                hat.is_nama_pemilik,
                hat.is_nama_ktp,
                hat.is_nik_ktp,
                hat.is_no_hp,
                hat.is_no_rekening,
                hat.is_an_rekening,
                hat.is_titik_koordinat,
                hat.auditor,
                hat.foto_audit1,
                hat.foto_audit2,
                hat.foto_audit3,
                hat.foto_audit4,
                hat.foto_audit5,
                hat.foto_audit6,
                hat.foto_audit7,
                hat.foto_audit8,
                l.latitude AS master_latitude,
                l.longitude AS master_longitude,
                hat.latitude AS audit_latitude,
                hat.longitude AS audit_longitude,
                hat.id AS id
            ")
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('reward_outlet as ro', 'l.customer_code', '=', 'ro.eskalink_code')
            ->leftJoin('hasil_audit_toko as hat', 'hat.customer_code', '=', 'l.customer_code')
            ->distinct();

        if (!empty($userAreaCodes)) {
            $outletsQuery->whereIn('md.area_code', $userAreaCodes);
        } elseif (!empty($userRegionCodes)) {
            $outletsQuery->whereIn('md.region_code', $userRegionCodes);
        }

        $outlets = $outletsQuery->get();

        $auditReportsQuery = DB::table('hasil_audit_toko as hat')
            ->selectRaw('
                md.distributor_code,
                md.distributor_name,
                md.branch_name AS cabang,
                hat.auditor,
                hat.customer_code,
                hat.customer_name,
                hat.customer_address,
                hat.latitude,
                hat.longitude,
                l.latitude AS master_latitude,
                l.longitude AS master_longitude,
                hat.foto_audit1,
                hat.foto_audit2,
                hat.foto_audit3,
                hat.foto_audit4,
                hat.foto_audit5,
                hat.foto_audit6,
                hat.foto_audit7,
                hat.foto_audit8,
                hat.keterangan_hasil_audit,
                hat.is_toko_fisik,
                hat.is_nama_pemilik,
                hat.is_nama_ktp,
                hat.is_nik_ktp,
                hat.is_no_hp,
                hat.is_no_rekening,
                hat.is_an_rekening,
                hat.is_titik_koordinat,
                hat.created_at,
                hat.id
            ')
            ->leftJoin('master_distributors as md', 'hat.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('list_outlet_audit as l', 'hat.customer_code', '=', 'l.customer_code');

        if (!empty($userAreaCodes)) {
            $auditReportsQuery->whereIn('md.area_code', $userAreaCodes);
        } elseif (!empty($userRegionCodes)) {
            $auditReportsQuery->whereIn('md.region_code', $userRegionCodes);
        }

        $auditReports = $auditReportsQuery->get();

        return Inertia::render('Mobile/Audit/Index', [
            'outlets' => $outlets,
            'auditReports' => $auditReports,
            'sessionAuditor' => $user->name,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required',
            'distributor_code' => 'required',
            'auditor' => 'required',
            'foto_audit1' => 'nullable|image',
            'foto_audit2' => 'nullable|image',
            'foto_audit3' => 'nullable|image',
            'foto_audit4' => 'nullable|image',
            'foto_audit5' => 'nullable|image',
            'foto_audit6' => 'nullable|image',
            'foto_audit7' => 'nullable|image',
            'foto_audit8' => 'nullable|image',
            'is_toko_fisik' => 'nullable|boolean',
            'is_nama_pemilik' => 'nullable|boolean',
            'is_nama_ktp' => 'nullable|boolean',
            'is_nik_ktp' => 'nullable|boolean',
            'is_no_hp' => 'nullable|boolean',
            'is_no_rekening' => 'nullable|boolean',
            'is_an_rekening' => 'nullable|boolean',
            'is_titik_koordinat' => 'nullable|boolean',
        ]);

        $data = [
            'auditor' => Auth::user()->name,
            'distributor_code' => $request->distributor_code,
            'customer_name' => $request->customer_name,
            'customer_address' => $request->customer_address,
            'latitude' => ($request->latitude && $request->latitude !== '0') ? $request->latitude : null,
            'longitude' => ($request->longitude && $request->longitude !== '0') ? $request->longitude : null,
            'keterangan_hasil_audit' => $request->keterangan_hasil_audit,
            'is_toko_fisik' => filter_var($request->is_toko_fisik, FILTER_VALIDATE_BOOLEAN),
            'is_nama_pemilik' => filter_var($request->is_nama_pemilik, FILTER_VALIDATE_BOOLEAN),
            'is_nama_ktp' => filter_var($request->is_nama_ktp, FILTER_VALIDATE_BOOLEAN),
            'is_nik_ktp' => filter_var($request->is_nik_ktp, FILTER_VALIDATE_BOOLEAN),
            'is_no_hp' => filter_var($request->is_no_hp, FILTER_VALIDATE_BOOLEAN),
            'is_no_rekening' => filter_var($request->is_no_rekening, FILTER_VALIDATE_BOOLEAN),
            'is_an_rekening' => filter_var($request->is_an_rekening, FILTER_VALIDATE_BOOLEAN),
            'is_titik_koordinat' => filter_var($request->is_titik_koordinat, FILTER_VALIDATE_BOOLEAN),
            'updated_at' => now(),
        ];

        // Ensure created_at is set if it's a new record
        $existing = DB::table('hasil_audit_toko')->where('customer_code', $request->customer_code)->first();
        if (!$existing) {
            $data['created_at'] = now();
        }

        // Handle File Uploads
        $fileFields = ['foto_audit1', 'foto_audit2', 'foto_audit3', 'foto_audit4', 'foto_audit5', 'foto_audit6', 'foto_audit7', 'foto_audit8'];
        
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $auditDir = storage_path('app/public/audit');
        if (!file_exists($auditDir)) {
            mkdir($auditDir, 0755, true);
        }

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = "{$request->customer_code}_{$field}_" . time() . ".{$extension}";
                
                $image = $manager->read($file->getRealPath());
                if ($image->width() > 1024) {
                    $image->scaleDown(width: 1024);
                }
                
                // Add Watermark
                $timestamp = date('d-m-Y H:i:s');
                $auditor = $request->auditor ?? session('audit_user', 'Unknown');
                $lat = $request->latitude ?? '-';
                $lng = $request->longitude ?? '-';
                $watermarkText = "Waktu: {$timestamp}\nAuditor: {$auditor}\nLokasi: {$lat}, {$lng}";
                
                $x = 15;
                $y = $image->height() - 75;
                $fontPath = 'C:/Windows/Fonts/arial.ttf';
                
                if (file_exists($fontPath)) {
                    // Shadow
                    $image->text($watermarkText, $x + 2, $y + 2, function ($font) use ($fontPath) {
                        $font->file($fontPath);
                        $font->size(18);
                        $font->color('#000000');
                        $font->lineHeight(1.5);
                    });
                    
                    // Main Text
                    $image->text($watermarkText, $x, $y, function ($font) use ($fontPath) {
                        $font->file($fontPath);
                        $font->size(18);
                        $font->color('#ffffff');
                        $font->lineHeight(1.5);
                    });
                }
                
                $image->save($auditDir . '/' . $filename, quality: 75);
                
                $data[$field] = 'audit/' . $filename;
            }
        }

        DB::table('hasil_audit_toko')->updateOrInsert(
            ['customer_code' => $request->customer_code],
            $data
        );

        return redirect()->back()->with('success', 'Data audit berhasil disimpan.');
    }

    public function export(Request $request)
    {
        $auditor = $request->query('auditor');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        
        return Excel::download(new AuditExport($auditor, $startDate, $endDate), 'hasil_audit_' . date('Ymd_His') . '.xlsx');
    }

    public function destroy($id)
    {
        $audit = DB::table('hasil_audit_toko')->where('id', $id)->first();
        if ($audit) {
            foreach (['foto_audit1', 'foto_audit2', 'foto_audit3', 'foto_audit4', 'foto_audit5', 'foto_audit6', 'foto_audit7', 'foto_audit8'] as $field) {
                if (!empty($audit->$field)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($audit->$field);
                }
            }
            DB::table('hasil_audit_toko')->where('id', $id)->delete();
        }
        return redirect()->back()->with('success', 'Data audit berhasil dihapus.');
    }

    public function thumbnail(Request $request)
    {
        $path = $request->query('path');
        if (!$path) {
            return abort(404);
        }
        
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            return abort(404);
        }
        
        $cacheDir = storage_path('app/public/thumbnails');
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $thumbName = md5($path) . '.jpg';
        $thumbPath = $cacheDir . '/' . $thumbName;
        
        if (!file_exists($thumbPath)) {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            try {
                $image = $manager->read($fullPath);
                $image->scaleDown(width: 300);
                $image->save($thumbPath, quality: 60);
            } catch (\Exception $e) {
                return response()->file($fullPath); // Fallback to original if fails
            }
        }
        
        return response()->file($thumbPath);
    }
}

class AuditExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, \Maatwebsite\Excel\Concerns\WithDrawings, \Maatwebsite\Excel\Concerns\WithEvents
{
    protected $auditor;
    protected $startDate;
    protected $endDate;
    protected $drawings = [];
    protected $rowNumber = 2;

    public function __construct($auditor = null, $startDate = null, $endDate = null)
    {
        $this->auditor = $auditor;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = DB::table('hasil_audit_toko as hat')
            ->selectRaw('
                hat.created_at,
                md.distributor_name,
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

        if (!empty($this->auditor)) {
            $query->where('hat.auditor', $this->auditor);
        }
        
        if (!empty($this->startDate) && !empty($this->endDate)) {
            // Include entire end date by appending time
            $query->whereBetween('hat.created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        } elseif (!empty($this->startDate)) {
            $query->where('hat.created_at', '>=', $this->startDate . ' 00:00:00');
        } elseif (!empty($this->endDate)) {
            $query->where('hat.created_at', '<=', $this->endDate . ' 23:59:59');
        }

        $user = Auth::user();
        if ($user) {
            $userRegionCodes = !empty($user->region_code) ? (array) $user->region_code : [];
            $userAreaCodes = !empty($user->area_code) ? (array) $user->area_code : [];
            
            if (!empty($userAreaCodes)) {
                $query->whereIn('md.area_code', $userAreaCodes);
            } elseif (!empty($userRegionCodes)) {
                $query->whereIn('md.region_code', $userRegionCodes);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Audit',
            'Distributor Name',
            'Auditor',
            'Customer Code',
            'Customer Name',
            'Customer Address',
            'Latitude',
            'Longitude',
            'Toko Fisik Sesuai',
            'Nama Pemilik Sesuai',
            'Nama KTP Sesuai',
            'NIK KTP Sesuai',
            'No HP Sesuai',
            'No Rekening Sesuai',
            'A/N Rekening Sesuai',
            'Titik Koordinat Sesuai',
            'Keterangan Hasil Audit',
            'Foto Audit 1',
            'Foto Audit 2',
            'Foto Audit 3',
            'Foto Audit 4',
            'Foto Audit 5',
            'Foto Audit 6',
            'Foto Audit 7',
            'Foto Audit 8'
        ];
    }

    public function map($row): array
    {
        $fields = [
            'foto_audit1' => 'R',
            'foto_audit2' => 'S',
            'foto_audit3' => 'T',
            'foto_audit4' => 'U',
            'foto_audit5' => 'V',
            'foto_audit6' => 'W',
            'foto_audit7' => 'X',
            'foto_audit8' => 'Y'
        ];

        foreach ($fields as $field => $col) {
            if (!empty($row->$field)) {
                $path = storage_path('app/public/' . $row->$field);
                if (file_exists($path)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto');
                    $drawing->setDescription('Foto Audit');
                    $drawing->setPath($path);
                    $drawing->setHeight(100);
                    $drawing->setCoordinates($col . $this->rowNumber);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $this->drawings[] = $drawing;
                }
            }
        }

        $this->rowNumber++;

        return [
            $row->created_at ? date('Y-m-d', strtotime($row->created_at)) : '-',
            $row->distributor_name ?? '-',
            $row->auditor,
            $row->customer_code,
            $row->customer_name,
            $row->customer_address ?? '-',
            $row->latitude ?? '0',
            $row->longitude ?? '0',
            $row->is_toko_fisik ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->is_nama_pemilik ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->is_nama_ktp ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->is_nik_ktp ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->is_no_hp ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->is_no_rekening ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->is_an_rekening ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->is_titik_koordinat ? '✓ Sesuai' : '✗ Tidak Sesuai',
            $row->keterangan_hasil_audit ?? '-',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' '
        ];
    }

    public function drawings()
    {
        return $this->drawings;
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                for ($i = 2; $i < $this->rowNumber; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(85);
                }
                
                $event->sheet->getDelegate()->getColumnDimension('R')->setAutoSize(false)->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('S')->setAutoSize(false)->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('T')->setAutoSize(false)->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('U')->setAutoSize(false)->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('V')->setAutoSize(false)->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('W')->setAutoSize(false)->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('X')->setAutoSize(false)->setWidth(25);
                $event->sheet->getDelegate()->getColumnDimension('Y')->setAutoSize(false)->setWidth(25);
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '@',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ]
            ],
        ];
    }
}
