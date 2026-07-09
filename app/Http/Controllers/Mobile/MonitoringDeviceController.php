<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MonitoringDeviceController extends Controller
{
    public function index(Request $request)
    {
        $salesCode = session('sales_code');
        $salesName = session('sales_name');

        if ($salesCode && !$salesName) {
            $salesName = DB::table('fsalesman')->where('SLSNO', $salesCode)->value('SLSNAME') ?? $salesCode;
        }

        $filterYear = $request->input('year', date('Y'));
        
        $flatList = collect();
        $monitoringData = [];
        $months = [];

        if ($salesCode) {
            // Define Months
            $year = $filterYear ?: date('Y');
            $currentMonth = ($year == date('Y')) ? date('n') : 12;
            
            for ($i = $currentMonth; $i >= 1; $i--) {
                $monthVal = $year . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $monthName = Carbon::parse($monthVal . '-01')->translatedFormat('F');
                $months[] = [
                    'value' => $monthVal,
                    'name' => $monthName . ' ' . $year
                ];
            }

            // Get Sales & their Distributors
            $salesDistributors = DB::table('fsalesman as f')
                ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'f.KD')
                ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
                ->select([
                    'f.KD as distributor_code',
                    'md.distributor_name',
                    'f.SLSNO as sales_code',
                    'f.SLSNAME as sales_name'
                ])
                ->where('f.SLSNO', $salesCode)
                ->where('f.TEAM', 'SEI')
                ->where('f.FLAG_ACTIVE', 'Y')
                ->where('f.FLAG_OFFICE', 'N')
                ->where('md.is_active', true)
                ->orderBy('md.distributor_name')
                ->get();

            // Cross join SEs and Months
            foreach ($salesDistributors as $sales) {
                foreach ($months as $month) {
                    $flatList->push([
                        'sales_code' => $sales->sales_code,
                        'sales_name' => $sales->sales_name,
                        'distributor_code' => $sales->distributor_code,
                        'distributor_name' => $sales->distributor_name,
                        'month_value' => $month['value'],
                        'month_name' => $month['name']
                    ]);
                }
            }

            // Fetch actual monitoring data
            $monthValues = array_map(function($m) { return $m['value'] . '-01'; }, $months);
            $monitoringData = DB::table('monitoring_device_se')
                ->where('sales_code', $salesCode)
                ->whereIn('tanggal', $monthValues)
                ->get()
                ->keyBy(function ($item) {
                    return $item->distributor_code . '_' . $item->sales_code . '_' . substr($item->tanggal, 0, 7);
                })->map(function ($item) {
                    // Inject storage urls for photos
                    if ($item->foto_tampak_depan) {
                        $item->foto_tampak_depan_url = Storage::url($item->foto_tampak_depan);
                    }
                    if ($item->foto_tampak_belakang) {
                        $item->foto_tampak_belakang_url = Storage::url($item->foto_tampak_belakang);
                    }
                    $item->tanggal_formatted = Carbon::parse($item->tanggal)->translatedFormat('F Y');
                    return (array) $item;
                })->toArray();
        }

        return Inertia::render('Mobile/MonitoringDevice/Index', [
            'salesData' => $flatList,
            'monitoringData' => $monitoringData,
            'months' => $months,
            'filterYear' => $filterYear,
            'sessionSalesCode' => $salesCode,
            'sessionSalesName' => $salesName,
        ]);
    }

    public function loginSales(Request $request)
    {
        $request->validate(['sales_code' => 'required|string']);
        
        $salesCode = strtoupper($request->sales_code);

        $sales = DB::table('fsalesman')
            ->select('SLSNAME')
            ->where('SLSNO', $salesCode)
            ->where('TEAM', 'SEI')
            ->where('FLAG_ACTIVE', 'Y')
            ->where('FLAG_OFFICE', 'N')
            ->first();

        if (!$sales) {
            return redirect()->back()->withErrors(['sales_code' => 'Kode sales tidak ditemukan atau tidak aktif di team SEI.']);
        }

        session([
            'sales_code' => $salesCode,
            'sales_name' => $sales->SLSNAME
        ]);

        return redirect()->route('mobile.app.monitoring-device.index');
    }

    public function logoutSales(Request $request)
    {
        session()->forget(['sales_code', 'sales_name']);
        return redirect()->route('mobile.app.monitoring-device.index');
    }

    public function store(Request $request)
    {
        $salesCode = session('sales_code');
        if (!$salesCode) {
            return redirect()->back()->withErrors(['error' => 'Sesi tidak valid. Harap login kembali.']);
        }

        $existing = null;
        if ($request->filled('id')) {
            $existing = DB::table('monitoring_device_se')
                ->where('id', $request->id)
                ->where('sales_code', $salesCode)
                ->first();
        }

        if (!$existing) {
            $existing = DB::table('monitoring_device_se')
                ->where('sales_code', $request->form_sales_code)
                ->where('distributor_code', $request->form_distributor_code)
                ->where('tanggal', $request->tanggal . '-01')
                ->first();
        }

        $rules = [
            'id' => 'nullable|integer',
            'tanggal' => 'required',
            'form_distributor_code' => 'required|string',
            'form_sales_code' => 'required|string',
            'kondisi_hp' => 'required|string|not_in:__others__',
            'kondisi_kartu' => 'required|string',
        ];

        if (!$existing || !$existing->foto_tampak_depan) {
            $rules['foto_tampak_depan'] = 'required|image';
        } else {
            $rules['foto_tampak_depan'] = 'nullable|image';
        }

        if (!$existing || !$existing->foto_tampak_belakang) {
            $rules['foto_tampak_belakang'] = 'required|image';
        } else {
            $rules['foto_tampak_belakang'] = 'nullable|image';
        }

        $request->validate($rules, [
            'foto_tampak_depan.required' => 'Foto tampak depan wajib diunggah.',
            'foto_tampak_belakang.required' => 'Foto tampak belakang wajib diunggah.',
        ]);

        // Security check
        if ($request->form_sales_code !== $salesCode) {
            return redirect()->back()->withErrors(['error' => 'Tidak diizinkan mengubah data sales lain.']);
        }

        $data = [
            'tanggal' => $request->tanggal . '-01',
            'distributor_code' => $request->form_distributor_code,
            'sales_code' => $request->form_sales_code,
            'kondisi_hp' => $request->kondisi_hp,
            'kondisi_kartu' => $request->kondisi_kartu,
            'updated_at' => now(),
        ];

        $salesName = session('sales_name');
        $salesInfo = $request->form_sales_code . ' - ' . $salesName;

        if ($request->hasFile('foto_tampak_depan')) {
            $data['foto_tampak_depan'] = $request->file('foto_tampak_depan')->store('monitoring_device', 'public');
            $this->addTimestampWatermark($data['foto_tampak_depan'], $salesInfo);
        }
        if ($request->hasFile('foto_tampak_belakang')) {
            $data['foto_tampak_belakang'] = $request->file('foto_tampak_belakang')->store('monitoring_device', 'public');
            $this->addTimestampWatermark($data['foto_tampak_belakang'], $salesInfo);
        }

        if ($existing) {
            DB::table('monitoring_device_se')->where('id', $existing->id)->update($data);
            $message = 'Data monitoring berhasil diubah.';
        } else {
            $data['created_at'] = now();
            DB::table('monitoring_device_se')->insert($data);
            $message = 'Data monitoring berhasil ditambahkan.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroyImage(Request $request)
    {
        $salesCode = session('sales_code');
        if (!$salesCode) {
            return redirect()->back()->withErrors(['error' => 'Sesi tidak valid. Harap login kembali.']);
        }

        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:depan,belakang',
        ]);

        $record = DB::table('monitoring_device_se')->where('id', $request->id)->where('sales_code', $salesCode)->first();
        if (!$record) {
            return redirect()->back()->withErrors(['error' => 'Data tidak ditemukan.']);
        }

        if ($request->type === 'depan' && $record->foto_tampak_depan) {
            Storage::disk('public')->delete($record->foto_tampak_depan);
            DB::table('monitoring_device_se')->where('id', $record->id)->update(['foto_tampak_depan' => null]);
        } elseif ($request->type === 'belakang' && $record->foto_tampak_belakang) {
            Storage::disk('public')->delete($record->foto_tampak_belakang);
            DB::table('monitoring_device_se')->where('id', $record->id)->update(['foto_tampak_belakang' => null]);
        }

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }

    private function addTimestampWatermark($path, $salesInfo)
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            if (!file_exists($fullPath)) return;

            $info = getimagesize($fullPath);
            if (!$info) return;

            $mime = $info['mime'];
            $image = null;

            if ($mime == 'image/jpeg') {
                $image = imagecreatefromjpeg($fullPath);
            } elseif ($mime == 'image/png') {
                $image = imagecreatefrompng($fullPath);
            }

            if (!$image) return;

            $width = imagesx($image);
            $height = imagesy($image);

            // Resize if too large
            $maxSize = 1280;
            if ($width > $height && $width > $maxSize) {
                $newWidth = $maxSize;
                $newHeight = (int) round(($height * $maxSize) / $width);
            } elseif ($height > $maxSize) {
                $newHeight = $maxSize;
                $newWidth = (int) round(($width * $maxSize) / $height);
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            if ($newWidth != $width || $newHeight != $height) {
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
                $width = $newWidth;
                $height = $newHeight;
            }

            // Background box
            $boxHeight = max(110, (int) round($height * 0.12));
            $padding = 20;
            
            $blackAlpha = imagecolorallocatealpha($image, 0, 0, 0, 50); 
            imagefilledrectangle($image, 0, $height - $boxHeight, $width, $height, $blackAlpha);

            $timestamp = 'Waktu: ' . Carbon::now()->translatedFormat('d/m/Y H:i:s');
            $seText = 'Sales: ' . $salesInfo;
            $appText = 'Aplikasi: Monitoring Device SE';

            $white = imagecolorallocate($image, 255, 255, 255);

            // Fetch or use TTF Font
            $fontPath = storage_path('app/public/Roboto-Medium.ttf');
            if (!file_exists($fontPath)) {
                try {
                    $fontData = @file_get_contents('https://github.com/googlefonts/roboto/raw/main/src/hinted/Roboto-Medium.ttf');
                    if ($fontData) {
                        file_put_contents($fontPath, $fontData);
                    } else {
                        $fontPath = 'C:\\Windows\\Fonts\\segoeui.ttf';
                        if (!file_exists($fontPath)) $fontPath = 'C:\\Windows\\Fonts\\arial.ttf';
                    }
                } catch (\Exception $e) {
                    $fontPath = 'C:\\Windows\\Fonts\\segoeui.ttf';
                    if (!file_exists($fontPath)) $fontPath = 'C:\\Windows\\Fonts\\arial.ttf';
                }
            }

            // Calculate Font Size
            $fontSize = max(14, (int) round($height * 0.016));
            $lineHeight = $fontSize * 1.8;
            $startY = $height - $boxHeight + ($fontSize * 1.5) + 5;

            if (file_exists($fontPath)) {
                imagettftext($image, $fontSize, 0, $padding, $startY, $white, $fontPath, $timestamp);
                imagettftext($image, $fontSize, 0, $padding, $startY + $lineHeight, $white, $fontPath, $seText);
                imagettftext($image, $fontSize, 0, $padding, $startY + ($lineHeight * 2), $white, $fontPath, $appText);
            } else {
                $font = 5;
                $charHeight = imagefontheight($font);
                imagestring($image, $font, $padding, $height - $boxHeight + 10, $timestamp, $white);
                imagestring($image, $font, $padding, $height - $boxHeight + 10 + $charHeight + 5, $seText, $white);
                imagestring($image, $font, $padding, $height - $boxHeight + 10 + ($charHeight * 2) + 10, $appText, $white);
            }

            if ($mime == 'image/jpeg') {
                imagejpeg($image, $fullPath, 85);
            } elseif ($mime == 'image/png') {
                imagepng($image, $fullPath);
            }
            
            imagedestroy($image);

        } catch (\Exception $e) {
            // Silently fail if GD fails
        }
    }
}
