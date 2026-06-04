<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use App\Models\RewardOutlet;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class MobileUpdate extends Component
{
    use WithFileUploads;

    // Filters and Search
    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedBranch = '';
    public $search = '';

    // Active Outlet Form fields
    public $activeOutletId = null;
    public $outletName = '';
    public $outletCode = '';
    public $outletAlamat = '';

    // File Uploads
    public $foto_depan; // Uploaded file
    public $foto_dalam; // Uploaded file
    public $existing_foto_depan; // DB path
    public $existing_foto_dalam; // DB path

    // Edit Form fields
    public $editOutletId = null;
    public $edit_nama_pemilik_toko = '';
    public $edit_nama_ktp = '';
    public $edit_nik_ktp = '';
    public $edit_no_hp = '';
    public $edit_nama_bank = '';
    public $edit_no_rekening = '';
    public $edit_nama_pemilik_norek = '';
    public $foto_ktp; // Uploaded KTP file
    public $existing_foto_ktp; // DB KTP path

    // GPS Coordinates
    public $latitude = null;
    public $longitude = null;

    protected $queryString = [
        'selectedRegion' => ['except' => ''],
        'selectedArea' => ['except' => ''],
        'selectedBranch' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    /**
     * Chained Updates
     */
    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->selectedBranch = '';
        $this->activeOutletId = null;
    }

    public function updatedSelectedArea($value)
    {
        $this->selectedBranch = '';
        $this->activeOutletId = null;
    }

    public function updatedSelectedBranch($value)
    {
        $this->activeOutletId = null;
    }

    public function updatingSearch()
    {
        $this->activeOutletId = null;
    }

    /**
     * Get list of regions
     */
    public function getRegions()
    {
        return \App\Models\MasterRegion::orderBy('region_name')->get();
    }

    /**
     * Get list of areas based on selected region
     */
    public function getAreas()
    {
        $query = \App\Models\MasterArea::query();
        if ($this->selectedRegion) {
            $query->where('region_code', $this->selectedRegion);
        }
        return $query->orderBy('area_name')->get();
    }

    /**
     * Get list of branches based on selected area or region
     */
    public function getBranches()
    {
        $query = \App\Models\MasterBranch::query();

        if ($this->selectedArea) {
            $supervisorCodes = \App\Models\MasterSupervisor::where('area_code', $this->selectedArea)
                ->pluck('supervisor_code');
            $query->whereIn('supervisor_code', $supervisorCodes);
        } elseif ($this->selectedRegion) {
            $areaCodes = \App\Models\MasterArea::where('region_code', $this->selectedRegion)
                ->pluck('area_code');
            $supervisorCodes = \App\Models\MasterSupervisor::whereIn('area_code', $areaCodes)
                ->pluck('supervisor_code');
            $query->whereIn('supervisor_code', $supervisorCodes);
        }

        return $query->orderBy('branch_name')->get();
    }

    /**
     * Select outlet for upload
     */
    public function selectOutlet($id)
    {
        $this->resetValidation();
        $outlet = RewardOutlet::findOrFail($id);

        $this->activeOutletId = $outlet->id;
        $this->outletName = $outlet->customer_name;
        $this->outletCode = $outlet->customer_code;
        $this->outletAlamat = $outlet->alamat;

        $this->existing_foto_depan = $outlet->foto_toko2;
        $this->existing_foto_dalam = $outlet->foto_toko3;

        $this->foto_depan = null;
        $this->foto_dalam = null;
    }

    /**
     * Cancel upload panel
     */
    public function cancelUpload()
    {
        $this->activeOutletId = null;
        $this->resetValidation();
    }

    /**
     * Save photos (Tampak Depan / Tampak Dalam)
     */
    public function savePhotos()
    {
        if (!$this->activeOutletId) {
            return;
        }

        $this->validate([
            'foto_depan' => 'nullable|image|max:10240', // 10MB limit for high-res mobile photos
            'foto_dalam' => 'nullable|image|max:10240',
        ]);

        $outlet = RewardOutlet::findOrFail($this->activeOutletId);
        $data = [];
        $updated = false;

        // Update GPS coordinates if outlet is not valid and coordinates are present
        if (!$outlet->is_valid && $this->latitude && $this->longitude) {
            $data['latitude'] = $this->latitude;
            $data['longitude'] = $this->longitude;
            $updated = true;
        }

        // Save Tampak Depan
        if ($this->foto_depan) {
            if ($outlet->foto_toko2) {
                Storage::disk('public')->delete($outlet->foto_toko2);
            }
            $this->compressImageGD($this->foto_depan->getRealPath());
            $data['foto_toko2'] = $this->foto_depan->store('rwo/toko', 'public');
            $updated = true;
        }

        // Save Tampak Dalam
        if ($this->foto_dalam) {
            if ($outlet->foto_toko3) {
                Storage::disk('public')->delete($outlet->foto_toko3);
            }
            $this->compressImageGD($this->foto_dalam->getRealPath());
            $data['foto_toko3'] = $this->foto_dalam->store('rwo/toko', 'public');
            $updated = true;
        }

        if ($updated) {
            $outlet->update($data);
            session()->flash('message', 'Foto toko berhasil diunggah.');
        } else {
            session()->flash('error', 'Silakan pilih foto terlebih dahulu.');
            return;
        }

        // Close upload panel and reset uploads and coordinates
        $this->activeOutletId = null;
        $this->foto_depan = null;
        $this->foto_dalam = null;
        $this->latitude = null;
        $this->longitude = null;
    }

    /**
     * Compress and resize an image on the server side using GD library
     */
    private function compressImageGD($filePath)
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            return;
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return;
        }

        $mime = $imageInfo['mime'];
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

    public function getFotoDepanPreview()
    {
        if ($this->foto_depan && method_exists($this->foto_depan, 'temporaryUrl')) {
            try {
                return $this->foto_depan->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function getFotoDalamPreview()
    {
        if ($this->foto_dalam && method_exists($this->foto_dalam, 'temporaryUrl')) {
            try {
                return $this->foto_dalam->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Save photos for a specific outlet during offline sync
     */
    public function savePhotosForOutletOffline($outletId, $latitude = null, $longitude = null)
    {
        $this->validate([
            'foto_depan' => 'nullable|image|max:10240',
            'foto_dalam' => 'nullable|image|max:10240',
        ]);

        $outlet = RewardOutlet::findOrFail($outletId);
        $data = [];
        $updated = false;

        $lat = $latitude ?? $this->latitude;
        $lon = $longitude ?? $this->longitude;

        // Update GPS coordinates if outlet is not valid and coordinates are present
        if (!$outlet->is_valid && $lat && $lon) {
            $data['latitude'] = $lat;
            $data['longitude'] = $lon;
            $updated = true;
        }

        // Save Tampak Depan
        if ($this->foto_depan) {
            if ($outlet->foto_toko2) {
                Storage::disk('public')->delete($outlet->foto_toko2);
            }
            $this->compressImageGD($this->foto_depan->getRealPath());
            $data['foto_toko2'] = $this->foto_depan->store('rwo/toko', 'public');
            $updated = true;
        }

        // Save Tampak Dalam
        if ($this->foto_dalam) {
            if ($outlet->foto_toko3) {
                Storage::disk('public')->delete($outlet->foto_toko3);
            }
            $this->compressImageGD($this->foto_dalam->getRealPath());
            $data['foto_toko3'] = $this->foto_dalam->store('rwo/toko', 'public');
            $updated = true;
        }

        if ($updated) {
            $outlet->update($data);
        }

        // Reset the uploads and coordinates for the next item in the sync queue
        $this->foto_depan = null;
        $this->foto_dalam = null;
        $this->latitude = null;
        $this->longitude = null;
    }

    /**
     * Save edited data (Identitas, Rekening, Foto KTP) for an outlet during offline sync
     */
    public function saveEditsForOutletOffline(
        $outletId,
        $nama_pemilik_toko,
        $nama_ktp,
        $nik_ktp,
        $no_hp,
        $nama_bank,
        $no_rekening,
        $nama_pemilik_norek
    ) {
        $outlet = RewardOutlet::findOrFail($outletId);
        $data = [
            'nama_pemilik_toko' => $nama_pemilik_toko,
            'nama_ktp' => $nama_ktp,
            'nama_bank' => $nama_bank,
            'nama_pemilik_norek' => $nama_pemilik_norek,
        ];

        // Masking Safety: Only update sensitive values if they do not contain 'xxxx'
        if ($nik_ktp && !str_contains($nik_ktp, 'xxxx')) {
            $cleanNik = trim($nik_ktp);
            if (strlen($cleanNik) === 16 && ctype_digit($cleanNik)) {
                $data['nik_ktp'] = $cleanNik;
            }
        }
        if ($no_hp && !str_contains($no_hp, 'xxxx')) {
            $cleanHp = trim($no_hp);
            if (ctype_digit($cleanHp)) {
                $data['no_hp'] = $cleanHp;
            }
        }
        if ($no_rekening && !str_contains($no_rekening, 'xxxx')) {
            $data['no_rekening'] = $no_rekening;
        }

        // Save Foto KTP if uploaded
        if ($this->foto_ktp) {
            if ($outlet->foto_ktp) {
                Storage::disk('public')->delete($outlet->foto_ktp);
            }
            $this->compressImageGD($this->foto_ktp->getRealPath());
            $data['foto_ktp'] = $this->foto_ktp->store('rwo/ktp', 'public');
        }

        $outlet->update($data);

        // Reset state for next item in sync queue
        $this->foto_ktp = null;
    }

    /**
     * Get temporary URL preview for KTP upload
     */
    public function getFotoKtpPreview()
    {
        if ($this->foto_ktp && method_exists($this->foto_ktp, 'temporaryUrl')) {
            try {
                return $this->foto_ktp->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function render()
    {
        $outlets = collect();

        // Retrieve outlets only if some filter is selected to avoid rendering massive lists
        if ($this->selectedRegion || $this->selectedArea || $this->selectedBranch || !empty($this->search)) {
            $query = RewardOutlet::query();

            if ($this->selectedRegion) {
                $query->where('region_code', $this->selectedRegion);
            }
            if ($this->selectedArea) {
                $query->where('area_code', $this->selectedArea);
            }
            if ($this->selectedBranch) {
                $query->where('branch_name', $this->selectedBranch);
            }

            if (!empty($this->search)) {
                $search = $this->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_code', 'ilike', '%' . $search . '%')
                      ->orWhere('customer_name', 'ilike', '%' . $search . '%');
                });
            }

            $outlets = $query->orderBy('customer_name')->get();
        }

        // Get offline master data for local storage caching
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

        return view('livewire.rwo.mobile-update', [
            'outlets' => $outlets,
            'regions' => $this->getRegions(),
            'areas' => $this->getAreas(),
            'branches' => $this->getBranches(),
            'offlineMasterDataJson' => json_encode($offlineMasterData),
        ])->layout('layouts.mobile-guest');
    }
}
