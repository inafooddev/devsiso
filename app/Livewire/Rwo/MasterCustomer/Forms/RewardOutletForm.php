<?php

namespace App\Livewire\Rwo\MasterCustomer\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Models\RewardOutlet;
use Illuminate\Validation\Rule;

class RewardOutletForm extends Form
{
    public ?int $outletId = null;

    public $region_code = '';
    public $region_name = '';
    public $area_code = '';
    public $area_name = '';
    public $branch_name = '';
    public $eskalink_code = '';
    public $customer_code = '';
    public $customer_name = '';
    public $alamat = '';
    public $no_hp = '';
    public $latitude = '';
    public $longitude = '';
    public $nama_pemilik_toko = '';
    public $nama_ktp = '';
    public $nik_ktp = '';
    
    // File uploads objects (can be string path or temporary uploaded file)
    public $foto_ktp;
    public $existing_foto_ktp;
    
    public $nama_bank = '';
    public $no_rekening = '';
    public $nama_pemilik_norek = '';
    
    public $foto_toko;
    public $existing_foto_toko;
    
    public $foto_toko2;
    public $existing_foto_toko2;
    
    public $foto_toko3;
    public $existing_foto_toko3;
    
    public $keterangan = '';
    public $is_valid = false;
    public $validasi_rekening = false;

    public function rules()
    {
        return [
            'region_code' => 'required|string|max:50',
            'region_name' => 'required|string|max:100',
            'area_code' => 'required|string|max:50',
            'area_name' => 'required|string|max:100',
            'branch_name' => 'required|string|max:100',
            'eskalink_code' => 'nullable|string|max:50',
            'customer_code' => [
                'required',
                'string',
                'max:50',
                $this->outletId 
                    ? Rule::unique('reward_outlet', 'customer_code')->ignore($this->outletId)
                    : Rule::unique('reward_outlet', 'customer_code'),
            ],
            'customer_name' => 'required|string|max:100',
            'alamat' => 'required|string',
            'no_hp' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'nama_pemilik_toko' => 'nullable|string|max:100',
            'nama_ktp' => 'nullable|string|max:100',
            'nik_ktp' => 'nullable|string|min:15|max:25',
            'foto_ktp' => 'nullable|image|max:2048', // 2MB Max
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'nama_pemilik_norek' => 'nullable|string|max:100',
            'foto_toko' => 'nullable|image|max:2048', // 2MB Max
            'foto_toko2' => 'nullable|image|max:2048', // 2MB Max
            'foto_toko3' => 'nullable|image|max:2048', // 2MB Max
            'keterangan' => 'nullable|string',
            'is_valid' => 'nullable|boolean',
            'validasi_rekening' => 'nullable|boolean',
        ];
    }

    public function setOutlet(RewardOutlet $outlet)
    {
        $this->outletId = $outlet->id;
        $this->region_code = $outlet->region_code;
        $this->region_name = $outlet->region_name;
        $this->area_code = $outlet->area_code;
        $this->area_name = $outlet->area_name;
        $this->branch_name = $outlet->branch_name;
        $this->eskalink_code = $outlet->eskalink_code;
        $this->customer_code = $outlet->customer_code;
        $this->customer_name = $outlet->customer_name;
        $this->alamat = $outlet->alamat;
        $this->no_hp = $outlet->no_hp;
        $this->latitude = $outlet->latitude;
        $this->longitude = $outlet->longitude;
        $this->nama_pemilik_toko = $outlet->nama_pemilik_toko;
        $this->nama_ktp = $outlet->nama_ktp;
        $this->nik_ktp = $outlet->nik_ktp;
        $this->nama_bank = $outlet->nama_bank;
        $this->no_rekening = $outlet->no_rekening;
        $this->nama_pemilik_norek = $outlet->nama_pemilik_norek;
        $this->keterangan = $outlet->keterangan;
        $this->is_valid = (bool) $outlet->is_valid;
        $this->validasi_rekening = (bool) $outlet->validasi_rekening;

        // Reset uploads
        $this->foto_ktp = null;
        $this->foto_toko = null;
        $this->foto_toko2 = null;
        $this->foto_toko3 = null;

        // Set existings
        $this->existing_foto_ktp = $outlet->foto_ktp;
        $this->existing_foto_toko = $outlet->foto_toko;
        $this->existing_foto_toko2 = $outlet->foto_toko2;
        $this->existing_foto_toko3 = $outlet->foto_toko3;
    }
}
