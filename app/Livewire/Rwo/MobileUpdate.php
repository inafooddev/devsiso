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

        // Close upload panel and reset uploads
        $this->activeOutletId = null;
        $this->foto_depan = null;
        $this->foto_dalam = null;
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

            // Only retrieve essential fields (id, customer_code, customer_name, alamat, foto_toko2, foto_toko3)
            $outlets = $query->select('id', 'customer_code', 'customer_name', 'alamat', 'foto_toko2', 'foto_toko3')
                ->orderBy('customer_name')
                ->get();
        }

        return view('livewire.rwo.mobile-update', [
            'outlets' => $outlets,
            'regions' => $this->getRegions(),
            'areas' => $this->getAreas(),
            'branches' => $this->getBranches(),
        ])->layout('layouts.mobile-guest');
    }
}
