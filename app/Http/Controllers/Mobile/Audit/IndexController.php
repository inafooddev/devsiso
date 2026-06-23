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
        // Execute the raw query using Laravel Query Builder
        $outlets = DB::table('list_outlet_audit as l')
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
                hat.auditor,
                hat.foto_audit1,
                hat.foto_audit2,
                hat.foto_audit3,
                l.latitude AS master_latitude,
                l.longitude AS master_longitude,
                hat.latitude AS audit_latitude,
                hat.longitude AS audit_longitude,
                hat.id AS id
            ")
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('reward_outlet as ro', 'l.customer_code', '=', 'ro.eskalink_code')
            ->leftJoin('hasil_audit_toko as hat', 'hat.customer_code', '=', 'l.customer_code')
            ->distinct()
            ->get();

        $auditReports = DB::table('hasil_audit_toko as hat')
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
                hat.foto_audit1,
                hat.foto_audit2,
                hat.foto_audit3,
                hat.keterangan_hasil_audit,
                hat.created_at,
                hat.id
            ')
            ->leftJoin('master_distributors as md', 'hat.distributor_code', '=', 'md.distributor_code')
            ->when(session('audit_user'), function ($q) {
                $q->where('hat.auditor', session('audit_user'));
            })
            ->get();

        return Inertia::render('Mobile/Audit/Index', [
            'outlets' => $outlets,
            'auditReports' => $auditReports,
            'sessionAuditor' => session('audit_user'),
        ]);
    }

    public function loginAuditor(Request $request)
    {
        $request->validate(['auditor' => 'required|string']);
        session(['audit_user' => $request->auditor]);
        return redirect()->back();
    }

    public function logoutAuditor(Request $request)
    {
        session()->forget('audit_user');
        return redirect()->back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required',
            'distributor_code' => 'required',
            'auditor' => 'required',
            'foto_audit1' => 'nullable|image|max:5120',
            'foto_audit2' => 'nullable|image|max:5120',
            'foto_audit3' => 'nullable|image|max:5120',
        ]);

        $data = [
            'auditor' => session('audit_user'),
            'distributor_code' => $request->distributor_code,
            'customer_name' => $request->customer_name,
            'customer_address' => $request->customer_address,
            'latitude' => ($request->latitude && $request->latitude !== '0') ? $request->latitude : null,
            'longitude' => ($request->longitude && $request->longitude !== '0') ? $request->longitude : null,
            'keterangan_hasil_audit' => $request->keterangan_hasil_audit,
            'updated_at' => now(),
        ];

        // Ensure created_at is set if it's a new record
        $existing = DB::table('hasil_audit_toko')->where('customer_code', $request->customer_code)->first();
        if (!$existing) {
            $data['created_at'] = now();
        }

        // Handle File Uploads
        $fileFields = ['foto_audit1', 'foto_audit2', 'foto_audit3'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Get extension
                $extension = $request->file($field)->getClientOriginalExtension();
                // Create unique filename
                $filename = "{$request->customer_code}_{$field}_" . time() . ".{$extension}";
                // Store file
                $path = $request->file($field)->storeAs('audit', $filename, 'public');
                $data[$field] = $path;
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
        return Excel::download(new AuditExport($auditor), 'hasil_audit_' . date('Ymd_His') . '.xlsx');
    }

    public function destroy($id)
    {
        $audit = DB::table('hasil_audit_toko')->where('id', $id)->first();
        if ($audit) {
            foreach (['foto_audit1', 'foto_audit2', 'foto_audit3'] as $field) {
                if (!empty($audit->$field)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($audit->$field);
                }
            }
            DB::table('hasil_audit_toko')->where('id', $id)->delete();
        }
        return redirect()->back()->with('success', 'Data audit berhasil dihapus.');
    }
}

class AuditExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles
{
    protected $auditor;

    public function __construct($auditor = null)
    {
        $this->auditor = $auditor;
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
                hat.keterangan_hasil_audit
            ')
            ->leftJoin('master_distributors as md', 'hat.distributor_code', '=', 'md.distributor_code');

        if (!empty($this->auditor)) {
            $query->where('hat.auditor', $this->auditor);
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
            'Keterangan Hasil Audit'
        ];
    }

    public function map($row): array
    {
        return [
            $row->created_at ? date('Y-m-d', strtotime($row->created_at)) : '-',
            $row->distributor_name ?? '-',
            $row->auditor,
            $row->customer_code,
            $row->customer_name,
            $row->customer_address ?? '-',
            $row->latitude ?? '0',
            $row->longitude ?? '0',
            $row->keterangan_hasil_audit ?? '-'
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
