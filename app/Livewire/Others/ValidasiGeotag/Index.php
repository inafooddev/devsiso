<?php

namespace App\Livewire\Others\ValidasiGeotag;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Traits\EnforcesMenuPermissions;
use App\Services\ValidasiGeotagService;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    public $menuRoute = 'others.validasi-geotag';
    public bool $canManage = false;
    public bool $canExport = false;

    #[Url]
    public string $search = '';

    #[Url]
    public string $regionFilter = '';

    #[Url]
    public string $areaFilter = '';

    #[Url]
    public string $distributorFilter = '';

    #[Url]
    public string $anomalyFilter = 'all';

    #[Url]
    public string $validationFilter = 'all';

    public int $perPage = 50;

    // Modal & Action State
    public $selectedStore = null;
    public string $catatan = '';
    public string $activeModalTab = 'streetview'; // 'streetview' | 'satellite' | 'leaflet'

    protected $service;

    public function boot(ValidasiGeotagService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->authorizeAction('can_view');
        $user = auth()->user();
        $this->canManage = $user && ($user->hasRole(['admin', 'spm']) || $user->hasMenuAccess($this->menuRoute, 'can_edit'));
        $this->canExport = $user && ($user->hasRole(['admin', 'spm']) || $user->hasMenuAccess($this->menuRoute, 'can_export'));
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRegionFilter()
    {
        $this->resetPage();
        $this->areaFilter = '';
        $this->distributorFilter = '';
    }

    public function updatingAreaFilter()
    {
        $this->resetPage();
        $this->distributorFilter = '';
    }

    public function updatingDistributorFilter()
    {
        $this->resetPage();
    }

    public function updatingAnomalyFilter()
    {
        $this->resetPage();
    }

    public function updatingValidationFilter()
    {
        $this->resetPage();
    }

    public function setAnomalyFilter($type)
    {
        $this->anomalyFilter = $type;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'regionFilter',
            'areaFilter',
            'distributorFilter',
            'anomalyFilter',
            'validationFilter',
        ]);
        $this->resetPage();
    }

    public function setModalStore($kodecabang, $custno, $catatan = '', $la = null, $lg = null, $validatedLa = null, $validatedLg = null)
    {
        $this->selectedStore = [
            'kodecabang' => $kodecabang,
            'custno' => $custno,
            'la' => $la,
            'lg' => $lg,
            'validated_la' => $validatedLa,
            'validated_lg' => $validatedLg,
        ];
        $this->catatan = $catatan ?? '';
    }

    public function openDetail(string $kodecabang, string $custno)
    {
        $detail = $this->service->getStoreDetail(auth()->user(), $kodecabang, $custno);
        if (!$detail) {
            $this->dispatch('show-toast', type: 'error', message: 'Data detail toko tidak ditemukan.');
            return;
        }

        $this->selectedStore = (array) $detail;
        $this->catatan = $detail->validation_notes ?? '';

        $this->dispatch('open-verification-modal', store: $this->selectedStore);
    }

    public function closeDetail()
    {
        $this->selectedStore = null;
        $this->catatan = '';
        $this->dispatch('close-verification-modal');
    }

    public function markAsValid(?string $kodecabang = null, ?string $custno = null, $la = null, $lg = null)
    {
        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk memvalidasi koordinat toko.');
        }

        $kodecabang = $kodecabang ?: ($this->selectedStore['kodecabang'] ?? null);
        $custno = $custno ?: ($this->selectedStore['custno'] ?? null);
        $la = ($la !== null && $la !== '') ? $la : ($this->selectedStore['la'] ?? null);
        $lg = ($lg !== null && $lg !== '') ? $lg : ($this->selectedStore['lg'] ?? null);

        if (!$kodecabang || !$custno) {
            $this->dispatch('show-toast', type: 'error', message: 'Toko belum dipilih.');
            return;
        }

        if (empty($la) || empty($lg) || $la == 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Toko belum memiliki koordinat sah untuk disetujui.');
            return;
        }

        $this->service->saveValidation(
            $kodecabang,
            $custno,
            'VALID',
            (float) $la,
            (float) $lg,
            $this->catatan,
            auth()->user()
        );

        $this->dispatch('show-toast', type: 'success', message: 'Toko berhasil divalidasi sebagai VALID.');
        $this->closeDetail();
    }

    public function markAsInvalid(?string $kodecabang = null, ?string $custno = null)
    {
        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah status validasi toko.');
        }

        $kodecabang = $kodecabang ?: ($this->selectedStore['kodecabang'] ?? null);
        $custno = $custno ?: ($this->selectedStore['custno'] ?? null);

        if (!$kodecabang || !$custno) {
            $this->dispatch('show-toast', type: 'error', message: 'Toko belum dipilih.');
            return;
        }

        $this->service->saveValidation(
            $kodecabang,
            $custno,
            'TIDAK_VALID',
            null,
            null,
            $this->catatan,
            auth()->user()
        );

        $this->dispatch('show-toast', type: 'warning', message: 'Toko ditandai sebagai TIDAK VALID.');
        $this->closeDetail();
    }

    public function resetToValidated(?string $kodecabang = null, ?string $custno = null, $validatedLa = null, $validatedLg = null)
    {
        if (!$this->canManage) {
            abort(403, 'Anda tidak memiliki hak akses untuk mereset titik koordinat.');
        }

        $kodecabang = $kodecabang ?: ($this->selectedStore['kodecabang'] ?? null);
        $custno = $custno ?: ($this->selectedStore['custno'] ?? null);
        $validatedLa = ($validatedLa !== null && $validatedLa !== '') ? $validatedLa : ($this->selectedStore['validated_la'] ?? null);
        $validatedLg = ($validatedLg !== null && $validatedLg !== '') ? $validatedLg : ($this->selectedStore['validated_lg'] ?? null);

        if (!$kodecabang || !$custno || empty($validatedLa)) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada riwayat titik sah sebelumnya.');
            return;
        }

        // Simpan konfirmasi mempertahankan titik tervalidasi
        $this->service->saveValidation(
            $kodecabang,
            $custno,
            'VALID',
            (float) $validatedLa,
            (float) $validatedLg,
            'Koordinat dikembalikan ke titik sah hasil validasi sebelumnya (' . ($this->catatan ? $this->catatan : 'Drift Recovery') . ')',
            auth()->user()
        );

        $this->dispatch('show-toast', type: 'success', message: 'Koordinat toko berhasil dipulihkan ke titik sah tervalidasi.');
        $this->closeDetail();
    }

    public function render()
    {
        $user = auth()->user();
        $filters = [
            'search' => $this->search,
            'region' => $this->regionFilter,
            'area' => $this->areaFilter,
            'distributor' => $this->distributorFilter,
            'anomaly' => $this->anomalyFilter,
            'validation_status' => $this->validationFilter,
        ];

        $filterOptions = $this->service->getFilterOptions($user);
        $kpi = $this->service->getSummaryMetrics($user, $filters);
        $stores = $this->service->getPaginatedStores($user, $filters, $this->perPage, $this->getPage());

        return view('livewire.others.validasi-geotag.index', [
            'stores' => $stores,
            'kpi' => $kpi,
            'filterOptions' => $filterOptions,
            'cartoApiKey' => config('services.carto.api_key', 'cb1_2llg_1_d426d0746a26eebc6607cafc'),
        ])->layout('layouts.app');
    }
}
