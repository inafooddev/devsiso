<?php

namespace App\Livewire\Others\ValidasiGeotag;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Traits\EnforcesMenuPermissions;
use App\Services\ValidasiGeotagService;
use Illuminate\Pagination\LengthAwarePaginator;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions;

    public $menuRoute = 'others.validasi-geotag';
    public bool $canManage = false;
    public bool $canExport = false;

    // Filter Form Inputs (UI pending selection)
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

    // Applied Filters State (Active in current database query)
    public bool $hasAppliedFilters = false;
    public string $appliedSearch = '';
    public string $appliedRegion = '';
    public string $appliedArea = '';
    public string $appliedDistributor = '';
    public string $appliedAnomaly = 'all';
    public string $appliedValidation = 'all';

    // Cascading Dropdown Options
    public array $regions = [];
    public array $areas = [];
    public array $distributors = [];

    public int $perPage = 50;

    // Modal & Action State
    public $selectedStore = null;
    public string $catatan = '';
    public string $activeModalTab = 'streetview'; // 'streetview' | 'satellite'

    // Duplicate Stores Modal State
    public bool $showDuplicateModal = false;
    public ?string $duplicateKodecabang = null;
    public ?float $duplicateLa = null;
    public ?float $duplicateLg = null;
    public ?string $duplicateSourceCustno = null;
    public ?string $duplicateSourceStore = null;
    public array $duplicateStores = [];
    public bool $loadingDuplicates = false;

    protected $service;

    public function boot(ValidasiGeotagService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->authorizeAction('can_view');
        $user = auth()->user();
        // Seluruh user terautentikasi yang mengakses halaman ini diizinkan melakukan validasi
        $this->canManage = (bool) $user;
        $this->canExport = (bool) $user;

        $this->loadFilterChain();

        // Jika URL membawa filter eksplisit, langsung terapkan
        if (!empty($this->regionFilter) || !empty($this->areaFilter) || !empty($this->distributorFilter) || !empty($this->search) || $this->anomalyFilter !== 'all' || $this->validationFilter !== 'all') {
            $this->applyFilters();
        }
    }

    /**
     * Muat opsi filter cascading berdasarkan territory scope user.
     */
    public function loadFilterChain()
    {
        $user = auth()->user();
        $this->regions = $this->service->getAccessibleRegions($user);

        // Jika user hanya punya 1 region (misal role region), otomatis pilihkan
        if (count($this->regions) === 1 && empty($this->regionFilter)) {
            $this->regionFilter = (string) array_key_first($this->regions);
        }

        if (!empty($this->regionFilter)) {
            $this->areas = $this->service->getAccessibleAreas($user, $this->regionFilter);
            if (count($this->areas) === 1 && empty($this->areaFilter)) {
                $this->areaFilter = (string) array_key_first($this->areas);
            }
        } else {
            $this->areas = [];
        }

        if (!empty($this->regionFilter) || !empty($this->areaFilter)) {
            $this->distributors = $this->service->getAccessibleDistributors($user, $this->regionFilter, $this->areaFilter);
            if (count($this->distributors) === 1 && empty($this->distributorFilter)) {
                $this->distributorFilter = (string) array_key_first($this->distributors);
            }
        } else {
            // Jika belum pilih region/area, ambil distributor yang dapat diakses jika jumlahnya terbatas (misal level supervisor)
            $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';
            if ($accessLevel === 'supervisor') {
                $this->distributors = $this->service->getAccessibleDistributors($user);
                if (count($this->distributors) === 1 && empty($this->distributorFilter)) {
                    $this->distributorFilter = (string) array_key_first($this->distributors);
                }
            } else {
                $this->distributors = [];
            }
        }
    }

    /**
     * Handler saat Region diubah: cascade ke Area & Distributor.
     */
    public function updatedRegionFilter($value)
    {
        $this->areaFilter = '';
        $this->distributorFilter = '';
        $user = auth()->user();

        if (!empty($value)) {
            $this->areas = $this->service->getAccessibleAreas($user, $value);
            if (count($this->areas) === 1) {
                $this->areaFilter = (string) array_key_first($this->areas);
            }

            $this->distributors = $this->service->getAccessibleDistributors($user, $value, $this->areaFilter);
            if (count($this->distributors) === 1) {
                $this->distributorFilter = (string) array_key_first($this->distributors);
            }
        } else {
            $this->areas = [];
            $this->distributors = [];
        }
    }

    /**
     * Handler saat Area diubah: cascade ke Distributor.
     */
    public function updatedAreaFilter($value)
    {
        $this->distributorFilter = '';
        $user = auth()->user();

        if (!empty($value) || !empty($this->regionFilter)) {
            $this->distributors = $this->service->getAccessibleDistributors($user, $this->regionFilter, $value);
            if (count($this->distributors) === 1) {
                $this->distributorFilter = (string) array_key_first($this->distributors);
            }
        } else {
            $this->distributors = [];
        }
    }

    /**
     * Terapkan Filter: jalankan query database hanya saat tombol ini diklik.
     */
    public function applyFilters()
    {
        $this->appliedSearch = trim($this->search);
        $this->appliedRegion = $this->regionFilter;
        $this->appliedArea = $this->areaFilter;
        $this->appliedDistributor = $this->distributorFilter;
        $this->appliedAnomaly = $this->anomalyFilter;
        $this->appliedValidation = $this->validationFilter;

        $this->hasAppliedFilters = true;
        $this->resetPage();
    }

    /**
     * Shortcut klik badge anomali pada summary card.
     */
    public function setAnomalyFilter($type)
    {
        $this->anomalyFilter = $type;
        $this->appliedAnomaly = $type;
        $this->hasAppliedFilters = true;
        $this->resetPage();
    }

    /**
     * Reset semua filter ke kondisi awal.
     */
    public function resetFilters()
    {
        $this->reset([
            'search',
            'regionFilter',
            'areaFilter',
            'distributorFilter',
            'appliedSearch',
            'appliedRegion',
            'appliedArea',
            'appliedDistributor',
            'hasAppliedFilters',
        ]);

        $this->anomalyFilter = 'all';
        $this->validationFilter = 'all';
        $this->appliedAnomaly = 'all';
        $this->appliedValidation = 'all';

        $this->resetPage();
        $this->loadFilterChain();
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

    /**
     * Buka modal daftar toko yang memiliki koordinat kembar/duplikat.
     */
    public function openDuplicateModal(string $kodecabang, float|string $la, float|string $lg, string $sourceCustno = '', string $sourceStore = '')
    {
        $this->duplicateKodecabang = $kodecabang;
        $this->duplicateLa = (float) $la;
        $this->duplicateLg = (float) $lg;
        $this->duplicateSourceCustno = $sourceCustno;
        $this->duplicateSourceStore = $sourceStore;
        $this->showDuplicateModal = true;
        $this->loadingDuplicates = true;

        $stores = $this->service->getDuplicateStores(
            auth()->user(),
            $this->duplicateKodecabang,
            $this->duplicateLa,
            $this->duplicateLg
        );

        $this->duplicateStores = array_map(function ($st) {
            return (array) $st;
        }, $stores);

        $this->loadingDuplicates = false;
    }

    /**
     * Tutup modal duplikat toko.
     */
    public function closeDuplicateModal()
    {
        $this->showDuplicateModal = false;
        $this->duplicateStores = [];
        $this->duplicateKodecabang = null;
        $this->duplicateLa = null;
        $this->duplicateLg = null;
        $this->duplicateSourceCustno = null;
        $this->duplicateSourceStore = null;
        $this->loadingDuplicates = false;
    }

    /**
     * Navigasi langsung untuk memverifikasi salah satu toko dari list duplikat.
     */
    public function inspectStoreFromDuplicate(string $kodecabang, string $custno)
    {
        $this->closeDuplicateModal();
        $this->openDetail($kodecabang, $custno);
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

        if ($this->hasAppliedFilters) {
            $filters = [
                'search' => $this->appliedSearch,
                'region' => $this->appliedRegion,
                'area' => $this->appliedArea,
                'distributor' => $this->appliedDistributor,
                'anomaly' => $this->appliedAnomaly,
                'validation_status' => $this->appliedValidation,
            ];

            $kpi = $this->service->getSummaryMetrics($user, $filters);
            $stores = $this->service->getPaginatedStores($user, $filters, $this->perPage, $this->getPage());
        } else {
            // Lazy / Deferred state: hindari query berat sebelum user klik "Terapkan Filter"
            $stores = new LengthAwarePaginator([], 0, $this->perPage, 1, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);

            $kpi = [
                'total_stores' => 0,
                'unvalidated_stores' => 0,
                'unvalidated_pct' => 0,
                'valid_stores' => 0,
                'invalid_stores' => 0,
                'has_coordinate' => 0,
                'no_coordinate' => 0,
                'at_distributor' => 0,
                'duplicate_stores' => 0,
                'drift_stores' => 0,
                'normal_stores' => 0,
            ];
        }

        return view('livewire.others.validasi-geotag.index', [
            'stores' => $stores,
            'kpi' => $kpi,
            'regions' => $this->regions,
            'areas' => $this->areas,
            'distributors' => $this->distributors,
            'hasAppliedFilters' => $this->hasAppliedFilters,
            'googleMapsApiKey' => config('services.google_maps.api_key', ''),
        ])->layout('layouts.app');
    }
}
