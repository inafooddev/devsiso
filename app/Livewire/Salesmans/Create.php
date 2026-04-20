<?php

namespace App\Livewire\Salesmans;

use Livewire\Component;
use App\Models\Salesman;
use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterDistributor;
use Illuminate\Validation\Rule;

class Create extends Component
{
    // Filter
    public $regionFilter;
    public $areaFilter;

    // Form
    public $distributor_code;
    public $salesman_code;
    public $manual_number; 
    public $salesman_name;
    public $is_active = true;

    // Dropdown Data
    public $regions = [];
    public $areas = [];
    public $distributors = [];

    /**
     * Mendefinisikan aturan validasi.
     * Menggunakan Rule::unique dengan tambahan kondisi where untuk 
     * mengecek keunikan kombinasi distributor_code dan salesman_code.
     */
    protected function rules()
    {
        return [
            'distributor_code' => 'required|string|exists:master_distributors,distributor_code',
            'manual_number' => 'required|string',
            'salesman_code' => [
                'required',
                'string',
                'max:15',
                // Validasi Unik Gabungan: 
                // salesman_code harus unik di dalam distributor_code yang sama
                Rule::unique(Salesman::class, 'salesman_code')->where(function ($query) {
                    return $query->where('distributor_code', $this->distributor_code);
                }),
            ],
            'salesman_name' => 'required|string|max:150',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Pesan error kustom untuk memberikan penjelasan yang lebih spesifik.
     */
    protected function messages()
    {
        return [
            'salesman_code.unique' => 'Kombinasi Distributor dan Kode Salesman ini sudah terdaftar.',
            'manual_number.required' => 'Nomor manual harus diisi untuk membentuk kode.',
        ];
    }

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }

        return $query;
    }

    /**
     * Helper untuk memastikan distributor terkait berada di dalam wilayah user
     */
    private function checkDistributorAccess($distributorCode)
    {
        $query = MasterDistributor::where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }

    public function mount()
    {
        // 1. Terapkan akses region ke dropdown
        $regionQuery = MasterRegion::query()->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national'
        $this->applyRegionAccess($regionQuery);
        $this->regions = $regionQuery->orderBy('region_name')->get();

        // 2. Auto-select region jika user hanya memiliki akses ke 1 region
        if (!auth()->user()->hasRole('admin') && count($this->regions) === 1) {
            $this->regionFilter = $this->regions->first()->region_code;
            // Panggil trigger secara manual agar area otomatis termuat
            $this->updatedRegionFilter($this->regionFilter);
        }
    }

    public function updatedRegionFilter($value)
    {
        $this->reset(['areaFilter', 'distributor_code', 'salesman_code', 'manual_number']);
        
        $query = MasterArea::query();
        if ($value) {
            $query->where('region_code', $value);
        }
        
        // Amankan area
        $this->applyRegionAccess($query);
        
        $this->areas = $value ? $query->orderBy('area_name')->get() : collect();
    }

    public function updatedAreaFilter($value)
    {
        $this->reset(['distributor_code', 'salesman_code', 'manual_number']);
        
        $query = MasterDistributor::query();
        if ($value) {
            $query->where('area_code', $value);
        }
        
        // Amankan distributor
        $this->applyRegionAccess($query);
        
        $this->distributors = $value ? $query->orderBy('is_active', 'desc')
            ->orderBy('distributor_code','asc')
            ->get() : collect();
    }

    public function updatedDistributorCode()
    {
        $this->generateSalesmanCode();
    }

    public function updatedManualNumber()
    {
        $this->generateSalesmanCode();
    }

    private function generateSalesmanCode()
    {
        if ($this->distributor_code) {
            // "SEI" + karakter ke 3-5 distributor (index 2, length 3) + nomor manual
            $distPart = substr($this->distributor_code, 2, 3);
            $this->salesman_code = 'SEI' . $distPart . $this->manual_number;
        } else {
            $this->salesman_code = '';
        }
    }

    public function save()
    {
        $validatedData = $this->validate();
        
        // Security Check Ekstra:
        // Pastikan kode distributor yang disubmit benar-benar ada dalam wilayah otoritas user
        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menambahkan salesman ke distributor tersebut.');
            return;
        }

        // Pastikan kita hanya menyimpan data yang ada di tabel salesman
        // (menghapus manual_number dari array validasi jika tidak ada di kolom tabel)
        $dataToSave = collect($validatedData)->except(['manual_number'])->toArray();

        Salesman::create($dataToSave);
        
        session()->flash('message', 'Salesman berhasil ditambahkan.');
        return redirect()->route('salesmans.index');
    }

    public function render()
    {
        return view('livewire.salesmans.create')->layout('layouts.app');
    }
}