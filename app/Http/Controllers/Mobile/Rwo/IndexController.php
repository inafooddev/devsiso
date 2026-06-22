<?php

namespace App\Http\Controllers\Mobile\Rwo;

use App\Http\Controllers\Controller;
use App\Models\RewardOutlet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        // Get offline master data for local storage caching (same as Livewire)
        $offlineRegions = \App\Models\MasterRegion::orderBy('region_name')->get();
        $offlineAreas = \App\Models\MasterArea::orderBy('area_name')->get();
        $offlineSupervisors = \App\Models\MasterSupervisor::all();
        $offlineBranches = \App\Models\MasterBranch::orderBy('branch_name')->get();
        
        $allOutlets = RewardOutlet::orderBy('customer_name')->get();

        $offlineMasterData = [
            'regions' => $offlineRegions,
            'areas' => $offlineAreas,
            'supervisors' => $offlineSupervisors,
            'branches' => $offlineBranches,
            'outlets' => $allOutlets,
        ];

        return Inertia::render('Mobile/Rwo/Index', [
            'offlineMasterData' => $offlineMasterData,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|exists:reward_outlets,id',
            'foto_depan' => 'nullable|image|max:10240',
            'foto_dalam' => 'nullable|image|max:10240',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $outlet = RewardOutlet::findOrFail($request->outlet_id);
        $data = [];
        $updated = false;

        // Update GPS coordinates if outlet is not valid and coordinates are present
        if (!$outlet->is_valid && $request->filled('latitude') && $request->filled('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
            $updated = true;
        }

        // Save Tampak Depan
        if ($request->hasFile('foto_depan')) {
            if ($outlet->foto_toko2) {
                Storage::disk('public')->delete($outlet->foto_toko2);
            }
            $this->compressImageGD($request->file('foto_depan')->getRealPath(), $request->file('foto_depan')->getMimeType());
            $data['foto_toko2'] = $request->file('foto_depan')->store('rwo/toko', 'public');
            $updated = true;
        }

        // Save Tampak Dalam
        if ($request->hasFile('foto_dalam')) {
            if ($outlet->foto_toko3) {
                Storage::disk('public')->delete($outlet->foto_toko3);
            }
            $this->compressImageGD($request->file('foto_dalam')->getRealPath(), $request->file('foto_dalam')->getMimeType());
            $data['foto_toko3'] = $request->file('foto_dalam')->store('rwo/toko', 'public');
            $updated = true;
        }

        if ($updated) {
            $outlet->update($data);
            return back()->with('message', 'Foto toko berhasil diunggah.');
        }

        return back()->with('error', 'Tidak ada foto yang diunggah.');
    }

    public function edit(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|exists:reward_outlets,id',
            'nama_pemilik_toko' => 'nullable|string',
            'nama_ktp' => 'nullable|string',
            'nik_ktp' => 'nullable|string',
            'no_hp' => 'nullable|string',
            'nama_bank' => 'nullable|string',
            'no_rekening' => 'nullable|string',
            'nama_pemilik_norek' => 'nullable|string',
            'foto_ktp' => 'nullable|image|max:10240',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $outlet = RewardOutlet::findOrFail($request->outlet_id);
        
        $data = $request->only([
            'nama_pemilik_toko', 'nama_ktp', 'nik_ktp', 'no_hp',
            'nama_bank', 'no_rekening', 'nama_pemilik_norek'
        ]);

        if (!$outlet->is_valid && $request->filled('latitude') && $request->filled('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
        }

        if ($request->hasFile('foto_ktp')) {
            if ($outlet->foto_ktp) {
                Storage::disk('public')->delete($outlet->foto_ktp);
            }
            $this->compressImageGD($request->file('foto_ktp')->getRealPath(), $request->file('foto_ktp')->getMimeType());
            $data['foto_ktp'] = $request->file('foto_ktp')->store('rwo/toko', 'public');
        }

        $outlet->update($data);
        return back()->with('message', 'Data outlet berhasil diperbarui.');
    }

    /**
     * Compress and resize an image on the server side using GD library
     */
    private function compressImageGD($filePath, $mimeType = null)
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            return;
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return;
        }

        $mime = $mimeType ?? $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'])) {
            return;
        }

        // Load image
        $image = null;
        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = @imagecreatefromjpeg($filePath);
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($filePath);
        }

        if (!$image) {
            return;
        }

        // Max dimension
        $maxDimension = 1200;
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width > $height) {
                $newHeight = (int) round(($height * $maxDimension) / $width);
                $newWidth = $maxDimension;
            } else {
                $newWidth = (int) round(($width * $maxDimension) / $height);
                $newHeight = $maxDimension;
            }

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($mime === 'image/png') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resizedImage;
        }

        // Save back with compression
        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            @imagejpeg($image, $filePath, 75);
        } elseif ($mime === 'image/png') {
            @imagepng($image, $filePath, 7);
        }
        imagedestroy($image);
    }
}
