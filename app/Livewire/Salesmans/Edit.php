<?php

namespace App\Livewire\Salesmans;

use Livewire\Component;
use App\Models\Salesman;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    // Mengubah menjadi nullable agar tidak error saat diakses sebelum diisi (uninitialized)
    public ?Salesman $salesman = null;

    // Properti Kunci (Business Key) untuk pencarian record di DB
    public $originalDistributorCode;
    public $originalSalesmanCode;

    // Filter Cascading
    public $regionFilter;
    public $areaFilter;

    // Properti Form
    public $distributor_code;
    public $salesman_code;
    public $salesman_name;
    public $is_active;

    // Dropdown Data
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    public function mount($id)
    {
        try {
            $decrypted = base64_decode($id);
            
            if (strpos($decrypted, '|') !== false) {
                [$dCode, $sCode] = explode('|', $decrypted);
                
                // Cari salesman berdasarkan kombinasi Distributor + Salesman Code
                $this->salesman = Salesman::with('masterDistributor.area.region')
                    ->where('distributor_code', $dCode)
                    ->where('salesman_code', $sCode)
                    ->first();
            }

            // Gunakan isset() atau null check karena property sekarang nullable
            if (!$this->salesman) {
                session()->flash('error', 'Data salesman tidak ditemukan.');
                return redirect()->route('salesmans.index');
            }

            // Kunci data lama agar query UPDATE di fungsi update() tidak salah sasaran
            $this->originalDistributorCode = $this->salesman->distributor_code;
            $this->originalSalesmanCode = $this->salesman->salesman_code;

            // Mapping data ke properti form
            $this->distributor_code = $this->salesman->distributor_code;
            $this->salesman_code = $this->salesman->salesman_code;
            $this->salesman_name = $this->salesman->salesman_name;
            $this->is_active = $this->salesman->is_active;

            // Inisialisasi Dropdown berdasarkan data yang ada
            $this->initDropdowns();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat memuat data.');
            return redirect()->route('salesmans.index');
        }
    }

    protected function initDropdowns()
    {
        $this->regions = MasterRegion::orderBy('region_name')->get();
        
        if ($this->salesman && $this->salesman->masterDistributor && $this->salesman->masterDistributor->area) {
            $area = $this->salesman->masterDistributor->area;
            
            $this->regionFilter = $area->region_code;
            $this->areas = MasterArea::where('region_code', $this->regionFilter)->orderBy('area_name')->get();
            
            $this->areaFilter = $area->area_code;
            $this->distributors = MasterDistributor::where('area_code', $this->areaFilter)->orderBy('distributor_name')->get();
        }
    }

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributor_code', 'areas', 'distributors']);
        if ($value) {
            $this->areas = MasterArea::where('region_code', $value)->orderBy('area_name')->get();
        }
    }

    public function updatedAreaFilter($value)
    {
        $this->reset(['distributor_code', 'distributors']);
        if ($value) {
            $this->distributors = MasterDistributor::where('area_code', $value)->orderBy('distributor_name')->get();
        }
    }

    protected function rules()
    {
        return [
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'salesman_code' => [
                'required',
                'string',
                'max:15',
                Rule::unique('salesmans')
                    ->where('distributor_code', $this->distributor_code)
                    ->ignore($this->salesman?->id) 
            ],
            'salesman_name' => 'required|string|max:150',
            'is_active' => 'required|boolean',
        ];
    }

    public function update()
    {
        $validatedData = $this->validate();

        DB::beginTransaction();
        try {
            // Update menggunakan original key untuk memastikan record yang benar yang berubah
            Salesman::where('distributor_code', $this->originalDistributorCode)
                ->where('salesman_code', $this->originalSalesmanCode)
                ->update([
                    'distributor_code' => $this->distributor_code,
                    'salesman_code'    => $this->salesman_code,
                    'salesman_name'    => $this->salesman_name,
                    'is_active'        => $this->is_active,
                    'updated_at'       => now(),
                ]);

            DB::commit();
            session()->flash('message', 'Salesman berhasil diperbarui.');
            return redirect()->route('salesmans.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.salesmans.edit')->layout('layouts.app');
    }
}