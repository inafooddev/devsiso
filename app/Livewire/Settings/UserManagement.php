<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\User;
use App\Models\MasterDistributor;
use App\Models\MasterArea;
use App\Models\MasterSupervisor;
use App\Models\Menu;
use App\Models\AccessGroup;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('layouts.app')]
class UserManagement extends Component
{
    use WithPagination;
    
    // ─── Properti Filter & Search ─────────────────────────────────────────────
    public $search = '';
    public $roleFilter = '';
    public $accessLevelFilter = '';
    public $regionFilter = '';

    protected $queryString = [
        'search'            => ['except' => ''],
        'roleFilter'        => ['except' => ''],
        'accessLevelFilter' => ['except' => ''],
        'regionFilter'      => ['except' => ''],
    ];

    // ─── Properti Form Dasar ──────────────────────────────────────────────────
    public $userId, $userid, $name, $email, $password, $role;

    // ─── Level Akses Wilayah ─────────────────────────────────────────────────
    // 'nasional' | 'region' | 'area' | 'supervisor'
    public string $accessLevel = 'nasional';

    // Level Region (multi) — sudah ada sebelumnya
    public array $region_code = [];

    // Level Area (multi)
    public array $area_code = [];

    // Level Supervisor (single string — 1 akun = 1 supervisor)
    public string $supervisor_code = '';

    // ─── Filter Cascading untuk UI ────────────────────────────────────────────
    // Dipakai saat memilih area: filter region dulu untuk mempersempit daftar area
    public string $filterRegionForArea = '';

    // Dipakai saat memilih supervisor: filter region → area dulu
    public string $filterRegionForSpv = '';
    public string $filterAreaForSpv   = '';

    // ─── State Modal ─────────────────────────────────────────────────────────
    public $isModalOpen     = false;
    public $isRoleModalOpen = false;
    public $access_group_id;

    // Properti untuk Tambah Role Baru
    public $newRoleName;

    // ─────────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        // Daftar region unik dari master_distributors
        $availableRegions = MasterDistributor::select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name')
            ->get();

        // Daftar area (cascading dari filter region jika ada)
        $availableAreas = $this->loadAvailableAreas();

        // Daftar supervisor (cascading dari filter area jika ada)
        $availableSupervisors = $this->loadAvailableSupervisors();

        // Daftar region untuk filter dropdown di section Area & Supervisor
        $regionsForFilter = MasterDistributor::select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name')
            ->get();

        $usersQuery = User::with(['roles', 'accessGroup'])
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('userid', 'ilike', '%' . $this->search . '%')
                        ->orWhere('name', 'ilike', '%' . $this->search . '%')
                        ->orWhere('email', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->when($this->accessLevelFilter, function ($query) {
                if ($this->accessLevelFilter === 'nasional') {
                    $query->where(function ($q) {
                        $q->whereNull('supervisor_code')
                          ->where(function ($sub) {
                              $sub->whereNull('area_code')
                                  ->orWhere('area_code', '[]')
                                  ->orWhere('area_code', '');
                          })
                          ->where(function ($sub) {
                              $sub->whereNull('region_code')
                                  ->orWhere('region_code', '[]')
                                  ->orWhere('region_code', '');
                          });
                    })->orWhereHas('roles', function($q) {
                        $q->where('name', 'admin');
                    });
                } elseif ($this->accessLevelFilter === 'supervisor') {
                    $query->whereNotNull('supervisor_code')
                          ->where('supervisor_code', '!=', '');
                } elseif ($this->accessLevelFilter === 'area') {
                    $query->whereNull('supervisor_code')
                          ->whereNotNull('area_code')
                          ->where('area_code', '!=', '[]')
                          ->where('area_code', '!=', '');
                } elseif ($this->accessLevelFilter === 'region') {
                    $query->whereNull('supervisor_code')
                          ->where(function ($sub) {
                              $sub->whereNull('area_code')
                                  ->orWhere('area_code', '[]')
                                  ->orWhere('area_code', '');
                          })
                          ->whereNotNull('region_code')
                          ->where('region_code', '!=', '[]')
                          ->where('region_code', '!=', '');
                }
            })
            ->when($this->regionFilter, function ($query) {
                $query->where(function ($q) {
                    // 1. Region level
                    $q->whereJsonContains('region_code', $this->regionFilter);
                    
                    // 2. Area level
                    $areaCodesInRegion = MasterArea::where('region_code', $this->regionFilter)->pluck('area_code')->toArray();
                    if (!empty($areaCodesInRegion)) {
                        $q->orWhere(function ($sub) use ($areaCodesInRegion) {
                            foreach ($areaCodesInRegion as $area) {
                                $sub->orWhereJsonContains('area_code', $area);
                            }
                        });
                        
                        // 3. Supervisor level
                        $spvCodesInRegion = MasterSupervisor::whereIn('area_code', $areaCodesInRegion)->pluck('supervisor_code')->toArray();
                        if (!empty($spvCodesInRegion)) {
                            $q->orWhereIn('supervisor_code', $spvCodesInRegion);
                        }
                    }
                });
            });

        $users = $usersQuery->latest()->paginate(10);

        return view('livewire.settings.user-management', [
            'users'                => $users,
            'roles'                => Role::all(),
            'accessGroups'         => AccessGroup::all(),
            'availableRegions'     => $availableRegions,
            'availableAreas'       => $availableAreas,
            'availableSupervisors' => $availableSupervisors,
            'regionsForFilter'     => $regionsForFilter,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CASCADING LOADERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Load daftar area.
     * Jika user sedang di level 'area', filter berdasarkan filterRegionForArea.
     * Jika user sedang di level 'supervisor', filter berdasarkan filterRegionForSpv.
     */
    private function loadAvailableAreas()
    {
        $query = MasterArea::orderBy('area_name');

        if ($this->accessLevel === 'area' && !empty($this->filterRegionForArea)) {
            $query->where('region_code', $this->filterRegionForArea);
        } elseif ($this->accessLevel === 'supervisor' && !empty($this->filterRegionForSpv)) {
            $query->where('region_code', $this->filterRegionForSpv);
        }

        return $query->get();
    }

    /**
     * Load daftar supervisor berdasarkan area yang dipilih di filterAreaForSpv.
     */
    private function loadAvailableSupervisors()
    {
        $query = MasterSupervisor::orderBy('supervisor_name')
            ->where('supervisor_code', '!=', 'HOINA');

        if (!empty($this->filterAreaForSpv)) {
            $query->where('area_code', $this->filterAreaForSpv);
        }

        return $query->get();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WATCHERS — Cascading Reset
    // ─────────────────────────────────────────────────────────────────────────

    /** Saat level berubah, reset semua pilihan wilayah */
    public function updatedAccessLevel(): void
    {
        $this->region_code         = [];
        $this->area_code           = [];
        $this->supervisor_code     = '';
        $this->filterRegionForArea = '';
        $this->filterRegionForSpv  = '';
        $this->filterAreaForSpv    = '';
    }

    // ─── Filter Watchers ─────────────────────────────────────────────────────
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAccessLevelFilter(): void
    {
        $this->resetPage();
    }

    public function updatingRegionFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'roleFilter', 'accessLevelFilter', 'regionFilter']);
        $this->resetPage();
    }

    /** Saat filter region untuk area berubah, reset pilihan area */
    public function updatedFilterRegionForArea(): void
    {
        $this->area_code = [];
    }

    /** Saat filter region untuk supervisor berubah, reset pilihan area & supervisor */
    public function updatedFilterRegionForSpv(): void
    {
        $this->filterAreaForSpv = '';
        $this->supervisor_code  = '';
    }

    /** Saat filter area untuk supervisor berubah, reset pilihan supervisor */
    public function updatedFilterAreaForSpv(): void
    {
        $this->supervisor_code = '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CRUD MODAL
    // ─────────────────────────────────────────────────────────────────────────

    public function create(): void
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function edit($id): void
    {
        $this->resetFields();
        $user = User::findOrFail($id);

        $this->userId         = $user->id;
        $this->userid         = $user->userid;
        $this->name           = $user->name;
        $this->email          = $user->email;
        $this->role           = $user->roles->first()?->name ?? '';
        $this->access_group_id = $user->access_group_id;

        // ── Load region_code ──────────────────────────────────────
        if (is_string($user->region_code)) {
            $decoded = json_decode($user->region_code, true);
            $this->region_code = is_array($decoded) ? $decoded : [$user->region_code];
        } elseif (is_array($user->region_code)) {
            $this->region_code = $user->region_code;
        }

        // ── Load area_code ────────────────────────────────────────
        if (is_string($user->area_code)) {
            $decoded = json_decode($user->area_code, true);
            $this->area_code = is_array($decoded) ? $decoded : [$user->area_code];
        } elseif (is_array($user->area_code)) {
            $this->area_code = $user->area_code;
        }

        // ── Load supervisor_code ──────────────────────────────────
        $this->supervisor_code = $user->supervisor_code ?? '';

        // ── Deteksi accessLevel otomatis ──────────────────────────
        $this->accessLevel = $user->getAccessLevel();

        $this->isModalOpen = true;
    }

    public function store(): void
    {
        $rules = [
            'userid'          => ['required', 'string', Rule::unique('users', 'userid')->ignore($this->userId)],
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'role'            => 'required|string',
            'access_group_id' => 'required|exists:access_groups,id',
        ];

        // Password wajib saat buat baru
        if (!$this->userId) {
            $rules['password'] = 'required|min:6';
        } elseif (!empty($this->password)) {
            $rules['password'] = 'min:6';
        }

        // Validasi tambahan per level
        if ($this->accessLevel === 'supervisor') {
            $rules['supervisor_code'] = 'required|exists:master_supervisors,supervisor_code';
        }

        $this->validate($rules);

        // ── Tentukan nilai yang disimpan berdasarkan level ────────
        $savedRegion     = null;
        $savedArea       = null;
        $savedSupervisor = null;

        switch ($this->accessLevel) {
            case 'region':
                $savedRegion = !empty($this->region_code) ? $this->region_code : null;
                break;
            case 'area':
                $savedArea = !empty($this->area_code) ? $this->area_code : null;
                break;
            case 'supervisor':
                $savedSupervisor = !empty($this->supervisor_code) ? $this->supervisor_code : null;
                break;
            // 'nasional': semua null (tidak ada batasan)
        }

        $data = [
            'userid'          => $this->userid,
            'name'            => $this->name,
            'email'           => $this->email,
            'access_group_id' => empty($this->access_group_id) ? null : $this->access_group_id,
            'region_code'     => $savedRegion,
            'area_code'       => $savedArea,
            'supervisor_code' => $savedSupervisor,
        ];

        if (!empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            $user->syncRoles([$this->role]);

            \App\Helpers\ActivityLogger::log('Update User', "Memperbarui data user: {$user->userid} ({$user->name}) — Level: {$this->accessLevel}");
            session()->flash('message', 'User berhasil diperbarui.');
        } else {
            $user = User::create($data);
            $user->assignRole($this->role);

            \App\Helpers\ActivityLogger::log('Create User', "Membuat user baru: {$user->userid} ({$user->name}) — Level: {$this->accessLevel}");
            session()->flash('message', 'User berhasil ditambahkan.');
        }

        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function delete($id): void
    {
        $user = User::find($id);
        if ($user) {
            \App\Helpers\ActivityLogger::log('Delete User', "Menghapus user: {$user->userid} ({$user->name})");
            $user->delete();
        }
        session()->flash('message', 'User berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROLE MODAL
    // ─────────────────────────────────────────────────────────────────────────

    public function openRoleModal(): void
    {
        $this->newRoleName    = '';
        $this->isRoleModalOpen = true;
    }

    public function storeRole(): void
    {
        $this->validate([
            'newRoleName' => 'required|string|max:255|unique:roles,name',
        ], [
            'newRoleName.required' => 'Nama role tidak boleh kosong.',
            'newRoleName.unique'   => 'Nama role sudah digunakan.',
        ]);

        Role::create([
            'name'       => strtolower(trim($this->newRoleName)),
            'guard_name' => 'web',
        ]);

        \App\Helpers\ActivityLogger::log('Create Role', "Menambahkan role baru: {$this->newRoleName}");

        $this->isRoleModalOpen = false;
        $this->newRoleName     = '';

        session()->flash('message', 'Role sistem berhasil ditambahkan.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function resetFields(): void
    {
        $this->userId          = null;
        $this->userid          = '';
        $this->name            = '';
        $this->email           = '';
        $this->password        = '';
        $this->role            = '';
        $this->access_group_id = null;

        $this->accessLevel         = 'nasional';
        $this->region_code         = [];
        $this->area_code           = [];
        $this->supervisor_code     = '';
        $this->filterRegionForArea = '';
        $this->filterRegionForSpv  = '';
        $this->filterAreaForSpv    = '';
    }
}