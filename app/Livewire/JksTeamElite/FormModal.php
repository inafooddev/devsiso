<?php

namespace App\Livewire\JksTeamElite;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\JksTeamElite;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\EnforcesMenuPermissions;

class FormModal extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'jks-team-elite.index';

    public $isFormModalOpen = false;
    public $isEditing = false;
    public $formError = null;
    
    // Common Fields
    public $tanggal;
    
    // Team Fields
    public $teams = [];
    public $selectedTeamCode = '';
    public $selectedTeamName = '';

    // Search Distributor
    public $searchDistributor = '';
    public $distributorOptions = [];
    public $selectedDistributorCode = '';
    
    // Search Customer
    public $searchCustomer = '';
    public $customerOptions = [];
    
    // Cart for multiple customers
    public $selectedCustomers = [];

    // Edit Target (Grouping)
    public $oldGroupParams = [];

    // Recommended Stores
    public $recommendedStores = [];

    private function applyHierarchyAccess($query, $distributorCodeColumn = 'jks_team_elite.distributor_code')
    {
        $user = auth()->user();

        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        $isJksQuery = str_contains($distributorCodeColumn, 'jks_team_elite.');

        if (!empty($user->supervisor_code)) {
            if ($isJksQuery) {
                return $query->where('jks_team_elite.kode_team', $user->supervisor_code);
            } else {
                $sisoCodes = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
                    ->whereRaw("TRIM(team_elite_code) = TRIM(?)", [$user->supervisor_code])
                    ->pluck('siso_code')
                    ->toArray();

                if (!empty($sisoCodes)) {
                    return $query->whereExists(function ($sub) use ($sisoCodes, $distributorCodeColumn) {
                        $sub->selectRaw('1')
                            ->from('master_distributors as md_auth')
                            ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                            ->whereIn('md_auth.supervisor_code', $sisoCodes);
                    });
                } else {
                    return $query->whereRaw('1 = 0');
                }
            }
        }

        if (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
            if ($isJksQuery) {
                return $query->whereIn('jks_team_elite.kode_area', $user->area_code);
            } else {
                return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md_auth')
                        ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                        ->whereIn('md_auth.area_code', $user->area_code);
                });
            }
        }

        if (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
            if ($isJksQuery) {
                return $query->whereIn('jks_team_elite.kode_region', $user->region_code);
            } else {
                return $query->whereExists(function ($sub) use ($user, $distributorCodeColumn) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md_auth')
                        ->whereColumn('md_auth.distributor_code', $distributorCodeColumn)
                        ->whereIn('md_auth.region_code', $user->region_code);
                });
            }
        }

        return $query->whereRaw('1 = 0');
    }

    public function mount()
    {
        try {
            $query = DB::table('team_elite_code_mappings as tecm')
                ->select(
                    'tecm.region_code',
                    'tecm.area_code',
                    'tecm.team_elite_code as kode_team',
                    'f.SLSNAME as nama_team'
                )
                ->leftJoin('fsalesman as f', 'tecm.team_elite_code', '=', 'f.SLSNO');

            $user = auth()->user();

            if ($user && !$user->hasRole('admin')) {
                if (!empty($user->supervisor_code)) {
                    $query->where('tecm.team_elite_code', $user->supervisor_code);
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    $query->whereIn('tecm.area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    $query->whereIn('tecm.region_code', $user->region_code);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $this->teams = $query->get()->unique('kode_team')->values()->toArray();
        } catch (\Exception $e) {
            $this->teams = [];
        }
    }

    #[On('open-create-modal')]
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
        
        $this->dispatchMapUpdate();
    }

    #[On('open-edit-modal')]
    public function openEditModal($tanggal, $kode_team, $kode_region)
    {
        $this->resetValidation();
        $this->resetForm();
        
        $this->oldGroupParams = [
            'tanggal' => $tanggal,
            'kode_team' => $kode_team,
            'kode_region' => $kode_region
        ];
        
        $customers = JksTeamElite::whereDate('jks_team_elite.tanggal', $tanggal)
            ->where('jks_team_elite.kode_team', $kode_team)
            ->where('jks_team_elite.kode_region', $kode_region)
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->select('jks_team_elite.*', 'l.latitude', 'l.longitude')
            ->get();
            
        if ($customers->count() > 0) {
            $first = $customers->first();
            $this->tanggal = $first->tanggal ? Carbon::parse($first->tanggal)->format('Y-m-d') : null;
            $this->selectedTeamCode = $first->kode_team;
            $this->selectedTeamName = $first->nama_team;
            
            foreach ($customers as $cust) {
                $this->selectedCustomers[] = [
                    'kode_region' => $cust->kode_region,
                    'nama_region' => $cust->nama_region,
                    'kode_area' => $cust->kode_area,
                    'nama_area' => $cust->nama_area,
                    'distributor_code' => $cust->distributor_code,
                    'distributor_name' => $cust->distributor_name,
                    'custno' => $cust->custno,
                    'custname' => $cust->custname,
                    'addres' => $cust->addres,
                    'latitude' => $cust->latitude,
                    'longitude' => $cust->longitude,
                ];
            }
        }
        
        $this->dispatchMapUpdate();
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    public function updatedSelectedTeamCode($value)
    {
        $team = collect($this->teams)->firstWhere('kode_team', $value);
        if ($team) {
            $this->selectedTeamName = $team->nama_team ?? $team['nama_team'] ?? '';
        } else {
            $this->selectedTeamName = '';
        }
    }

    public function updatedSearchDistributor()
    {
        if (strlen($this->searchDistributor) >= 2) {
            $query = DB::table('master_distributors')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('distributor_code', 'ilike', '%' . $this->searchDistributor . '%')
                      ->orWhere('distributor_name', 'ilike', '%' . $this->searchDistributor . '%');
                });
            
            $this->applyHierarchyAccess($query, 'distributor_code');

            $this->distributorOptions = $query->select('distributor_code', 'distributor_name')
                ->limit(20)
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } else {
            $this->distributorOptions = [];
        }
    }

    public function selectDistributor($code, $name)
    {
        $this->selectedDistributorCode = $code;
        $this->searchDistributor = $code . ' - ' . $name;
        $this->distributorOptions = [];
        
        $this->searchCustomer = '';
        $this->customerOptions = [];
    }

    public function clearDistributor()
    {
        $this->selectedDistributorCode = '';
        $this->searchDistributor = '';
        $this->distributorOptions = [];
        $this->searchCustomer = '';
        $this->customerOptions = [];
    }

    public function updatedSearchCustomer()
    {
        if (strlen($this->searchCustomer) >= 3) {
            $query = DB::table('list_toko_pareto_team_elite as l')
                ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
                ->where('md.is_active', true);
            
            $this->applyHierarchyAccess($query, 'l.distributor_code');

            if (!empty($this->selectedDistributorCode)) {
                $query->where('md.distributor_code', $this->selectedDistributorCode);
            }

            $query->where(function($q) {
                $q->where('l.customer_code_prc', 'ilike', '%' . $this->searchCustomer . '%')
                  ->orWhere('l.customer_name', 'ilike', '%' . $this->searchCustomer . '%')
                  ->orWhere('l.customer_address', 'ilike', '%' . $this->searchCustomer . '%');
            });

            $this->customerOptions = $query->select(
                    'md.region_code as kode_region',
                    'md.region_name as nama_region',
                    'md.area_code as kode_area',
                    'md.area_name as nama_area',
                    'l.distributor_code',
                    'md.distributor_name',
                    'l.customer_code_prc as custno',
                    'l.customer_name as custname',
                    'l.customer_address as addres',
                    'l.latitude',
                    'l.longitude',
                    'l.pilar'
                )
                ->limit(20)
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } else {
            $this->customerOptions = [];
        }
    }

    public function addCustomerToCart($custno, $distributorCode = null)
    {
        $customer = collect($this->customerOptions)
            ->where('custno', $custno)
            ->when($distributorCode, fn($q) => $q->where('distributor_code', $distributorCode))
            ->first();
            
        if (!$customer) {
            $query = DB::table('list_toko_pareto_team_elite as l')
                ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
                ->where('l.customer_code_prc', $custno);
                
            if ($distributorCode) {
                $query->where('l.distributor_code', $distributorCode);
            }
                
            $customer = $query->select(
                    'md.region_code as kode_region',
                    'md.region_name as nama_region',
                    'md.area_code as kode_area',
                    'md.area_name as nama_area',
                    'l.distributor_code',
                    'md.distributor_name',
                    'l.customer_code_prc as custno',
                    'l.customer_name as custname',
                    'l.customer_address as addres',
                    'l.latitude',
                    'l.longitude',
                    'l.pilar'
                )->first();
        }
        
        if ($customer) {
            $custArray = is_array($customer) ? $customer : (array) $customer;
            
            $exists = collect($this->selectedCustomers)->contains(function ($item) use ($custArray) {
                return $item['custno'] === $custArray['custno'] && $item['distributor_code'] === $custArray['distributor_code'];
            });
            
            if (!$exists) {
                $this->selectedCustomers[] = $custArray;
            } else {
                $this->addError('selectedCustomers', 'Toko ' . $custArray['custno'] . ' sudah ada di daftar.');
            }
        }
        
        $this->searchCustomer = '';
        $this->customerOptions = [];
        
        $this->dispatchMapUpdate();
    }

    public function removeCustomerFromCart($custno, $distributorCode = null)
    {
        $this->selectedCustomers = collect($this->selectedCustomers)
            ->filter(function ($item) use ($custno, $distributorCode) {
                if ($distributorCode) {
                    return !($item['custno'] === $custno && $item['distributor_code'] === $distributorCode);
                }
                return $item['custno'] !== $custno;
            })
            ->values()
            ->toArray();
            
        $this->dispatchMapUpdate();
    }

    public function addRecommendedStore($custno, $distributorCode = null)
    {
        $customer = collect($this->recommendedStores)
            ->where('custno', $custno)
            ->when($distributorCode, fn($q) => $q->where('distributor_code', $distributorCode))
            ->first();
        
        if ($customer) {
            $custArray = is_array($customer) ? $customer : (array) $customer;
            $exists = collect($this->selectedCustomers)->contains(function($item) use ($custArray) {
                return $item['custno'] === $custArray['custno'] && $item['distributor_code'] === $custArray['distributor_code'];
            });
            if (!$exists) {
                $this->selectedCustomers[] = $custArray;
            } else {
                $this->addError('selectedCustomers', 'Toko ' . $custArray['custno'] . ' sudah ada di daftar.');
            }
        }
        
        $this->dispatchMapUpdate();
    }

    private function dispatchMapUpdate()
    {
        $this->loadRecommendedStores(false);
        $this->dispatch('update-form-map-markers', 
            selected: array_values($this->selectedCustomers), 
            recommended: array_values($this->recommendedStores)
        );
    }

    public function loadRecommendedStores($shouldDispatch = true)
    {
        $this->recommendedStores = [];
        if (empty($this->selectedCustomers)) {
            if ($shouldDispatch) $this->dispatchMapUpdate();
            return;
        }

        $selectedCustnos = collect($this->selectedCustomers)->pluck('custno')->toArray();
        $selectedDistributors = collect($this->selectedCustomers)->pluck('distributor_code')->unique()->toArray();

        $query = DB::table('list_toko_pareto_team_elite as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->where('md.is_active', true)
            ->whereNotIn('l.customer_code_prc', $selectedCustnos);
            
        $this->applyHierarchyAccess($query, 'l.distributor_code');

        if (count($selectedDistributors) > 0) {
            $query->whereIn('l.distributor_code', $selectedDistributors);
        }

        $hasValidCoordinates = false;
        $query->where(function($q) use (&$hasValidCoordinates) {
            foreach ($this->selectedCustomers as $store) {
                $lat = floatval($store['latitude'] ?? 0);
                $lng = floatval($store['longitude'] ?? 0);
                if ($lat != 0 && $lng != 0 && !is_nan($lat) && !is_nan($lng)) {
                    $hasValidCoordinates = true;
                    $q->orWhereRaw("(6371 * acos(cos(radians(?)) * cos(radians(l.latitude)) * cos(radians(l.longitude) - radians(?)) + sin(radians(?)) * sin(radians(l.latitude)))) <= 1.0", [$lat, $lng, $lat]);
                }
            }
        });

        if ($hasValidCoordinates) {
            $this->recommendedStores = $query->select(
                    'md.region_code as kode_region',
                    'md.region_name as nama_region',
                    'md.area_code as kode_area',
                    'md.area_name as nama_area',
                    'l.distributor_code',
                    'md.distributor_name',
                    'l.customer_code_prc as custno',
                    'l.customer_name as custname',
                    'l.customer_address as addres',
                    'l.latitude',
                    'l.longitude',
                    'l.pilar'
                )
                ->orderBy('l.pilar', 'asc')
                ->limit(50)
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        }
        
        if ($shouldDispatch) {
            $this->dispatchMapUpdate();
        }
    }

    private function resetForm()
    {
        $this->tanggal          = null;
        $this->selectedTeamCode = '';
        $this->selectedTeamName = '';
        $this->formError        = null;
        
        $this->searchDistributor = '';
        $this->distributorOptions = [];
        $this->selectedDistributorCode = '';
        
        $this->searchCustomer = '';
        $this->customerOptions = [];
        
        $this->selectedCustomers = [];
        $this->recommendedStores = [];
        $this->oldGroupParams = [];
    }

    public function save()
    {
        try {
            $this->authorizeAction('can_edit');

            $this->validate([
                'tanggal' => 'required|date',
                'selectedTeamCode' => 'required|string',
                'selectedCustomers' => 'required|array|min:1',
            ], [
                'tanggal.required' => 'Tanggal harus diisi.',
                'selectedTeamCode.required' => 'Team harus dipilih.',
                'selectedCustomers.required' => 'Minimal pilih 1 customer untuk disimpan.',
            ]);

            if ($this->isEditing && !empty($this->oldGroupParams)) {
                JksTeamElite::whereDate('tanggal', $this->oldGroupParams['tanggal'])
                    ->where('kode_team', $this->oldGroupParams['kode_team'])
                    ->where('kode_region', $this->oldGroupParams['kode_region'])
                    ->delete();
            }

            $teamInfo = collect($this->teams)->firstWhere('kode_team', $this->selectedTeamCode);
            $this->selectedTeamName = $teamInfo ? ($teamInfo->nama_team ?? $teamInfo['nama_team'] ?? '') : '';

            $inserts = [];
            foreach ($this->selectedCustomers as $cust) {
                $inserts[] = [
                    'tanggal'          => $this->tanggal,
                    'kode_team'        => $this->selectedTeamCode,
                    'nama_team'        => $this->selectedTeamName,
                    'kode_region'      => $cust['kode_region'],
                    'nama_region'      => $cust['nama_region'],
                    'kode_area'        => $cust['kode_area'],
                    'nama_area'        => $cust['nama_area'],
                    'distributor_code' => $cust['distributor_code'],
                    'distributor_name' => $cust['distributor_name'],
                    'custno'           => $cust['custno'],
                    'custname'         => $cust['custname'],
                    'addres'           => $cust['addres'],
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ];
            }
            
            JksTeamElite::insert($inserts);
            
            if ($this->isEditing) {
                \App\Helpers\ActivityLogger::log('Update JKS Team Elite', "Memperbarui grup customer JKS untuk team: {$this->selectedTeamName} ({$this->tanggal})");
                $message = 'Grup customer berhasil diperbarui.';
            } else {
                \App\Helpers\ActivityLogger::log('Create JKS Team Elite', "Membuat grup customer JKS baru untuk team: {$this->selectedTeamName} ({$this->tanggal}) sejumlah " . count($inserts) . " data");
                $message = count($inserts) . ' Data customer berhasil disimpan.';
            }

            $this->isFormModalOpen = false;
            $this->resetForm();
            
            // Beritahu parent component untuk me-refresh data
            $this->dispatch('refresh-jks-data', message: $message);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23505') {
                $this->formError = 'Gagal menyimpan. Jadwal JKS untuk toko tersebut sudah dibuat pada tanggal yang sama. Silakan cek kembali.';
                return;
            }
            return $this->downloadExceptionLog($e, 'Menyimpan Data Grup JKS');
        } catch (\Exception $e) {
            return $this->downloadExceptionLog($e, 'Menyimpan Data Grup JKS');
        }
    }

    public function render()
    {
        return view('livewire.jks-team-elite.form-modal');
    }
}
