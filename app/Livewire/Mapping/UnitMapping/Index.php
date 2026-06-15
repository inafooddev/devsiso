<?php

namespace App\Livewire\Mapping\UnitMapping;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UnitMapping;
use App\Models\UnmappedUnit;
use Livewire\Attributes\Layout;
use App\Traits\EnforcesMenuPermissions;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    protected string $menuRoute = 'product-unit-mappings.index';

    public $search = '';
    public $filterUnit = '';
    public $isModalOpen = false;
    
    public $mappingId;
    public $distributor_code;
    public $raw_unit;
    public $mapped_unit;

    protected $queryString = ['search', 'filterUnit'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterUnit()
    {
        $this->resetPage();
    }

    public function create()
    {
        // Dinonaktifkan sesuai permintaan: harus lewat unmapped list
        session()->flash('error', 'Penambahan mapping hanya bisa dilakukan melalui daftar "Unit Perlu Mapping" di atas.');
    }

    public function edit($id)
    {
        $this->resetFields();
        $mapping = UnitMapping::findOrFail($id);
        
        $this->mappingId = $mapping->id;
        $this->distributor_code = $mapping->distributor_code;
        $this->raw_unit = $mapping->raw_unit;
        $this->mapped_unit = $mapping->mapped_unit;
        
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->authorizeAction('can_edit');

        if (!$this->checkDistributorAccess($this->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki akses ke region distributor ini.');
            return;
        }

        $this->distributor_code = strtoupper(trim($this->distributor_code));
        $this->raw_unit = strtoupper(trim($this->raw_unit));
        $this->mapped_unit = strtoupper(trim($this->mapped_unit));

        $this->validate([
            'distributor_code' => 'required|string|max:20',
            'raw_unit' => 'required|string|max:50',
            'mapped_unit' => 'required|in:CTN,PCK,PCS',
        ]);

        // Check for uniqueness manually since unique constraint is composite
        $exists = UnitMapping::where('distributor_code', $this->distributor_code)
            ->where('raw_unit', $this->raw_unit)
            ->where('id', '!=', $this->mappingId)
            ->exists();
            
        if ($exists) {
            $this->addError('raw_unit', 'Mapping untuk distributor dan unit ini sudah ada.');
            return;
        }

        if ($this->mappingId) {
            $mapping = UnitMapping::findOrFail($this->mappingId);
            $oldDistributor = $mapping->distributor_code;
            $mapping->update([
                'mapped_unit' => $this->mapped_unit,
            ]);
            \App\Helpers\ActivityLogger::log('Update Unit Mapping', "Memperbarui unit mapping untuk distributor: {$this->distributor_code}. Raw: {$this->raw_unit} -> {$this->mapped_unit}");
            \App\Helpers\UnitHelper::clearCache($oldDistributor);
            \Illuminate\Support\Facades\Cache::forget('mapping_notification_counts_' . auth()->id());
            $this->dispatch('refreshNotifications');
            session()->flash('message', 'Unit mapping berhasil diupdate.');
        } else {
            UnitMapping::create([
                'distributor_code' => $this->distributor_code,
                'raw_unit' => $this->raw_unit,
                'mapped_unit' => $this->mapped_unit,
            ]);
            
            // Hapus dari tabel unmapped_units
            UnmappedUnit::where('distributor_code', $this->distributor_code)
                ->where('raw_unit', $this->raw_unit)
                ->delete();

            \App\Helpers\UnitHelper::clearCache($this->distributor_code);
            \Illuminate\Support\Facades\Cache::forget('mapping_notification_counts_' . auth()->id());
            $this->dispatch('refreshNotifications');
            \App\Helpers\ActivityLogger::log('Create Unit Mapping', "Menambahkan unit mapping baru untuk distributor: {$this->distributor_code}. Raw: {$this->raw_unit} -> {$this->mapped_unit}");
            session()->flash('message', 'Unit mapping berhasil ditambahkan.');
        }

        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function delete($id)
    {
        $this->authorizeAction('can_edit');

        $mapping = UnitMapping::findOrFail($id);
        
        if (!$this->checkDistributorAccess($mapping->distributor_code)) {
            session()->flash('error', 'Anda tidak memiliki akses ke region distributor ini.');
            return;
        }

        $distCode = $mapping->distributor_code;
        \App\Helpers\ActivityLogger::log('Delete Unit Mapping', "Menghapus unit mapping untuk distributor: {$distCode}. Raw: {$mapping->raw_unit} -> {$mapping->mapped_unit}");
        $mapping->delete();
        \App\Helpers\UnitHelper::clearCache($distCode);
        \Illuminate\Support\Facades\Cache::forget('mapping_notification_counts_' . auth()->id());
        $this->dispatch('refreshNotifications');
        session()->flash('message', 'Unit mapping berhasil dihapus.');
    }

    public function mapUnmapped($distCode, $rawUnit)
    {
        $this->authorizeAction('can_edit');

        $this->resetFields();
        $this->distributor_code = $distCode;
        $this->raw_unit = $rawUnit;
        $this->isModalOpen = true;
    }

    public function clearUnmapped()
    {
        $this->authorizeAction('can_edit');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            UnmappedUnit::truncate();
        } else {
            $userRegions = $user->region_code;
            if (!empty($userRegions)) {
                $distributors = \App\Models\MasterDistributor::whereIn('region_code', $userRegions)->pluck('distributor_code');
                UnmappedUnit::whereIn('distributor_code', $distributors)->delete();
            }
        }

        \Illuminate\Support\Facades\Cache::forget('mapping_notification_counts_' . auth()->id());
        $this->dispatch('refreshNotifications');
        \App\Helpers\ActivityLogger::log('Clear Unmapped Unit', 'Menghapus semua data unit yang belum di-mapping.');
        session()->flash('message', 'Semua data unit yang belum di-mapping berhasil dihapus.');
    }

    private function resetFields()
    {
        $this->mappingId = null;
        $this->distributor_code = '';
        $this->raw_unit = '';
        $this->mapped_unit = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $mappings = UnitMapping::query()
            ->select('unit_mappings.*')
            ->leftJoin('master_distributors', 'unit_mappings.distributor_code', '=', 'master_distributors.distributor_code');
            
        $this->applyRegionAccess($mappings, 'master_distributors.region_code');

        $mappings = $mappings->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('unit_mappings.distributor_code', 'ilike', '%' . $this->search . '%')
                      ->orWhere('master_distributors.distributor_name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('unit_mappings.raw_unit', 'ilike', '%' . $this->search . '%')
                      ->orWhere('unit_mappings.mapped_unit', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->filterUnit, function($query) {
                $query->where('unit_mappings.mapped_unit', $this->filterUnit);
            })
            ->orderBy('master_distributors.distributor_name')
            ->orderBy('unit_mappings.mapped_unit')
            ->orderBy('unit_mappings.raw_unit')
            ->paginate(100);

        $unmappedUnits = UnmappedUnit::query()
            ->select('unmapped_units.*')
            ->leftJoin('master_distributors', 'unmapped_units.distributor_code', '=', 'master_distributors.distributor_code');
            
        $this->applyRegionAccess($unmappedUnits, 'master_distributors.region_code');
        
        $unmappedUnits = $unmappedUnits->orderBy('master_distributors.distributor_name')
            ->get();

        return view('livewire.mapping.unit-mapping.index', [
            'mappings' => $mappings,
            'unmappedUnits' => $unmappedUnits
        ]);
    }

    private function applyRegionAccess($query, $column = 'region_code')
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn($column, $user->region_code);
        }

        return $query;
    }

    private function checkDistributorAccess($distributorCode)
    {
        $query = \App\Models\MasterDistributor::where('distributor_code', $distributorCode);
        $this->applyRegionAccess($query);
        return $query->exists();
    }
}
