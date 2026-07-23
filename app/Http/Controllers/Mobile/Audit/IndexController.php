<?php

namespace App\Http\Controllers\Mobile\Audit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
                hat.status_approval,
                hat.alasan_reject,
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
                hat.status_approval,
                hat.alasan_reject,
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

    public function showLogin()
    {
        return Inertia::render('Mobile/Audit/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = \Illuminate\Support\Str::lower($request->user_id) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (Auth::attempt(['userid' => $request->user_id, 'password' => $request->password], $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Periksa apakah user memiliki role audit atau admin (case-insensitive checks)
            if (!$user->hasAnyRole(['audit', 'admin', 'Audit', 'Admin', 'super-admin', 'Super Admin'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                \App\Helpers\ActivityLogger::log('Audit Login Failed', "Gagal login audit (Role tidak sesuai) dengan userid: {$request->user_id}");

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'user_id' => 'Akun Anda tidak memiliki akses untuk masuk ke fitur ini.',
                ]);
            }

            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            
            \App\Helpers\ActivityLogger::log('Audit Login', 'User berhasil login ke sistem Audit Mobile.');

            return redirect()->route('mobile.audit.index');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        \App\Helpers\ActivityLogger::log('Audit Login Failed', "Gagal login audit dengan userid: {$request->user_id} dari IP: " . $request->ip());

        throw \Illuminate\Validation\ValidationException::withMessages([
            'user_id' => 'User ID atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        \App\Helpers\ActivityLogger::log('Audit Logout', 'User berhasil logout dari sistem Audit Mobile.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.audit.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required',
            'distributor_code' => 'required',
            'auditor' => 'required',
            'foto_audit1' => 'nullable',
            'foto_audit2' => 'nullable',
            'foto_audit3' => 'nullable',
            'foto_audit4' => 'nullable',
            'foto_audit5' => 'nullable',
            'foto_audit6' => 'nullable',
            'foto_audit7' => 'nullable',
            'foto_audit8' => 'nullable',
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
            'status_approval' => 'Pending',
            'alasan_reject' => null,
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
                $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = "{$request->customer_code}_{$field}_" . time() . ".{$extension}";
                
                try {
                    $image = $manager->read($file->getRealPath());
                    if ($image->width() > 1024) {
                        $image->scaleDown(width: 1024);
                    }
                    
                    // Add Watermark
                    $timestamp = date('d/m/Y H:i:s');
                    $auditor = $request->auditor ?? session('audit_user', 'Unknown');
                    $customerName = $request->customer_name ?? '-';
                    $lat = $request->latitude;
                    $lng = $request->longitude;

                    $addressText = $request->customer_address ?? '-';
                    if (!empty($lat) && !empty($lng) && $lat !== '0' && $lng !== '0') {
                        try {
                            $geoUrl = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lng}";
                            $ctx = stream_context_create([
                                'http' => [
                                    'timeout' => 2,
                                    'header' => "User-Agent: DevsisoAudit/1.0\r\n"
                                ]
                            ]);
                            $geoJson = @file_get_contents($geoUrl, false, $ctx);
                            if ($geoJson) {
                                $geoData = json_decode($geoJson, true);
                                if (!empty($geoData['display_name'])) {
                                    $addressText = $geoData['display_name'];
                                }
                            }
                        } catch (\Throwable $e) {
                            // Fallback to customer_address
                        }
                    }

                    // Trim address if too long for watermark text
                    if (mb_strlen($addressText) > 80) {
                        $addressText = mb_substr($addressText, 0, 77) . '...';
                    }

                    $watermarkText = "Toko: {$customerName}\nWaktu: {$timestamp}\nAlamat: {$addressText}\nAuditor: {$auditor}";
                    
                    $x = 15;
                    $y = max(15, $image->height() - 110);
                    $fontPath = 'C:/Windows/Fonts/arial.ttf';
                    
                    if (file_exists($fontPath)) {
                        // Shadow
                        $image->text($watermarkText, $x + 2, $y + 2, function ($font) use ($fontPath) {
                            $font->file($fontPath);
                            $font->size(16);
                            $font->color('#000000');
                            $font->lineHeight(1.4);
                        });
                        
                        // Main Text
                        $image->text($watermarkText, $x, $y, function ($font) use ($fontPath) {
                            $font->file($fontPath);
                            $font->size(16);
                            $font->color('#ffffff');
                            $font->lineHeight(1.4);
                        });
                    }
                    
                    $image->save($auditDir . '/' . $filename, quality: 75);
                    $data[$field] = 'audit/' . $filename;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to process image {$field}: " . $e->getMessage());
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        $field => "Foto {$field} tidak dapat diproses. Pastikan format foto adalah JPG/PNG dan ukurannya tidak terlalu besar."
                    ]);
                }
            } elseif ($request->$field === 'delete') {
                $data[$field] = null;
                // Optional: Delete physical file if exists
                if ($existing && !empty($existing->$field)) {
                    @unlink(storage_path('app/public/' . $existing->$field));
                }
            }
        }

        DB::table('hasil_audit_toko')->updateOrInsert(
            ['customer_code' => $request->customer_code],
            $data
        );

        \App\Helpers\ActivityLogger::log(
            $existing ? 'Update Audit Toko (Mobile)' : 'Create Audit Toko (Mobile)',
            ($existing ? 'Memperbarui' : 'Menyimpan') . " data hasil audit toko: {$request->customer_code} - {$request->customer_name} via Mobile"
        );

        return redirect()->back()->with('success', 'Data audit berhasil disimpan.');
    }



    public function destroy($id)
    {
        $audit = DB::table('hasil_audit_toko')->where('id', $id)->first();
        if ($audit) {
            \App\Helpers\ActivityLogger::log(
                'Delete Audit Toko (Mobile)',
                "Menghapus data hasil audit toko ID: {$id} - {$audit->customer_name} secara permanen via Mobile."
            );

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

