<div>
    <x-slot name="title">JKS Team Elite</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">
        {{-- Notifikasi --}}
        <div class="mb-6">
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">{{ session('message') }}</div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                </div>
            @endif
        </div>

        @if(!empty($filterTeam) && !empty($filterStartDate) && !empty($filterEndDate))
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-base-100 rounded-2xl p-4 border border-base-200 shadow-sm flex flex-col items-center justify-center text-center">
                    <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">Total Toko</span>
                    <span class="text-2xl font-black text-primary">{{ number_format($kpi['total_toko']) }}</span>
                </div>
                <div class="bg-base-100 rounded-2xl p-4 border border-base-200 shadow-sm flex flex-col items-center justify-center text-center">
                    <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">Total Target</span>
                    <span class="text-2xl font-black text-success">{{ number_format($kpi['total_target']) }}</span>
                </div>
                <div class="bg-base-100 rounded-2xl p-4 border border-base-200 shadow-sm flex flex-col items-center justify-center text-center">
                    <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">Total RWO</span>
                    <span class="text-2xl font-black text-info">{{ number_format($kpi['total_rwo']) }}</span>
                </div>
                <div class="bg-base-100 rounded-2xl p-4 border border-base-200 shadow-sm flex flex-col items-center justify-center text-center">
                    <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">Total PNR</span>
                    <span class="text-2xl font-black text-secondary">{{ number_format($kpi['total_pnr']) }}</span>
                </div>
                <div class="bg-base-100 rounded-2xl p-4 border border-base-200 shadow-sm flex flex-col items-center justify-center text-center">
                    <span class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">Total NGVO</span>
                    <span class="text-2xl font-black text-warning">{{ number_format($kpi['total_ngvo']) }}</span>
                </div>
            </div>
        @endif

        <x-card flush title="JKS Team Elite" icon="users" subtitle="Kelola data JKS Team Elite" class="pb-6">
            <x-slot:actions>
                <div class="flex items-center gap-3 flex-wrap">

                    {{-- Filter Team --}}
                    <div class="relative w-72" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="select select-sm select-bordered w-full rounded-xl bg-base-200 border-base-300 flex items-center justify-between px-3 text-left">
                            <span class="truncate text-base-content/70">
                                @if(count($filterTeam) === 0)
                                    Pilih Team...
                                @elseif(count($filterTeam) === 1)
                                    {{ collect($teams)->firstWhere('kode_team', $filterTeam[0])->nama_team ?? '1 Team' }}
                                @else
                                    {{ count($filterTeam) }} Team Terpilih
                                @endif
                            </span>
                            <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/50" />
                        </button>
                        
                        <div x-show="open" 
                             x-transition
                             x-cloak
                             class="absolute z-50 w-80 mt-1 bg-base-100 border border-base-300 rounded-xl shadow-xl left-0 flex flex-col overflow-hidden">
                             
                            <div class="px-3 py-2 border-b border-base-300 bg-base-100 z-10 flex flex-col gap-2 shrink-0">
                                <div class="flex items-center justify-between gap-2">
                                    <button type="button" wire:click="selectAllTeams" class="btn btn-xs btn-ghost text-primary hover:bg-primary/10">Pilih Semua</button>
                                    <button type="button" wire:click="resetTeams" class="btn btn-xs btn-ghost text-error hover:bg-error/10">Reset</button>
                                </div>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="searchTeamFilter" placeholder="Cari nama/kode team..." class="input input-sm input-bordered w-full rounded-lg pl-8 bg-base-200" />
                                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/50" />
                                </div>
                            </div>
                             
                            <div class="p-2 space-y-1 overflow-y-auto overflow-x-auto max-h-60">
                                @php
                                    $filteredTeams = empty($searchTeamFilter) 
                                        ? collect($teams) 
                                        : collect($teams)->filter(fn($t) => stripos($t->nama_team, $searchTeamFilter) !== false || stripos($t->kode_team, $searchTeamFilter) !== false);
                                @endphp

                                @forelse($filteredTeams as $team)
                                    <label class="flex items-center gap-3 p-2 hover:bg-base-200 rounded-lg cursor-pointer transition-colors w-max pr-4">
                                        <input type="checkbox" wire:model.live="filterTeam" value="{{ $team->kode_team }}" class="checkbox checkbox-sm checkbox-primary rounded-md shrink-0" />
                                        <span class="text-sm select-none whitespace-nowrap">{{ $team->nama_team }}</span>
                                    </label>
                                @empty
                                    <div class="text-center py-4 text-sm text-base-content/50">Team tidak ditemukan</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Filter Date Range --}}
                    <div class="flex items-center gap-2">
                        <input wire:model.live.debounce.300ms="filterStartDate" type="date" class="input input-sm input-bordered w-36 rounded-xl bg-base-200 border-base-300">
                        <span class="text-base-content/50 text-sm">s/d</span>
                        <input wire:model.live.debounce.300ms="filterEndDate" type="date" class="input input-sm input-bordered w-36 rounded-xl bg-base-200 border-base-300">
                    </div>

                    {{-- Global Search --}}
                    <div class="relative w-48">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari data..." class="input input-sm input-bordered w-full rounded-xl pl-8 bg-base-200 border-base-300" />
                        <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/50" />
                    </div>

                    {{-- Tombol Export --}}
                    <button wire:click="export" class="btn btn-sm btn-success rounded-xl normal-case gap-2 shadow-sm text-white">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export
                    </button>

                    {{-- Tombol Import --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openImportModal" class="btn btn-sm btn-warning rounded-xl normal-case gap-2 shadow-sm text-white">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import
                    </button>
                    @endunless

                    {{-- Tombol Tambah --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            <div class="px-6 py-4">
                <div class="overflow-x-auto rounded-xl border border-base-200 shadow-sm">
                <table class="table table-sm table-zebra w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Region</th>
                            <th>Kode Team</th>
                            <th>Nama Team</th>
                            <th class="text-center">Total Toko</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(empty($filterTeam) || empty($filterStartDate) || empty($filterEndDate))
                            <tr>
                                <td colspan="8" class="text-center py-8 text-base-content/50">
                                    <x-heroicon-o-funnel class="w-12 h-12 mx-auto mb-3 opacity-30" />
                                    Silakan pilih **Team** dan **Range Tanggal** terlebih dahulu untuk menampilkan data.
                                </td>
                            </tr>
                        @else
                            @forelse ($records as $index => $record)
                                <tr wire:key="group-{{ $record->tanggal }}-{{ $record->kode_team }}-{{ $record->kode_region }}" class="group hover">
                                    <td>{{ $records->firstItem() + $index }}</td>
                                    <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($record->tanggal)->format('d M Y') }}</td>
                                    <td>{{ $record->nama_region }} ({{ $record->kode_region }})</td>
                                    <td>{{ $record->kode_team }}</td>
                                    <td><div class="font-bold">{{ $record->nama_team }}</div></td>
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}')" class="badge badge-primary badge-outline font-bold cursor-pointer hover:bg-primary hover:text-white transition-colors">
                                            {{ $record->total_toko }}
                                        </button>
                                    </td>
                                    <td>
                                        @unless(auth()->user()->hasRole('guest'))
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="openEditModal('{{ $record->tanggal }}', '{{ $record->kode_team }}', '{{ $record->kode_region }}')" 
                                                    class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit Grup">
                                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                            </button>
                                            <button wire:click="confirmDelete('{{ $record->tanggal }}', '{{ $record->kode_team }}', '{{ $record->kode_region }}')" 
                                                    class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus Grup">
                                                <x-heroicon-s-trash class="w-4 h-4" />
                                            </button>
                                        </div>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-base-content/50">Tidak ada data ditemukan untuk kriteria tersebut.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="mt-4 px-2">
                    {{ $records->links() }}
                </div>
            @endif
        </x-card>
    </div>

    {{-- Modal Form (Create/Edit) --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-5xl max-h-[90vh] overflow-hidden ring-1 ring-base-content/5 flex flex-col">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Grup JKS' : 'Tambah JKS Multiple Customer' }}</h3>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col md:flex-row bg-base-100">
                {{-- Kiri: Form Input & Search --}}
                <div class="w-full md:w-1/2 p-6 border-r border-base-300 overflow-y-auto">
                    <form id="form-jks" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Tanggal --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal</label>
                                <input wire:model.blur="tanggal" type="date" class="input input-sm input-bordered w-full rounded-xl">
                                @error('tanggal') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Team (fsalesman) --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Team</label>
                                <select wire:model.live="selectedTeamCode" class="select select-sm select-bordered w-full rounded-xl">
                                    <option value="">-- Pilih Team --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->kode_team }}">{{ $team->nama_team }}</option>
                                    @endforeach
                                </select>
                                @error('selectedTeamCode') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="my-4 border-base-300">

                        {{-- Search Distributor --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Distributor (Opsional)</label>
                            @if($selectedDistributorCode)
                                <div class="flex items-center gap-2 p-2 border border-primary/30 bg-primary/5 rounded-xl text-sm">
                                    <div class="flex-1 font-semibold text-primary">{{ $selectedDistributorCode }} - {{ $searchDistributor }}</div>
                                    <button type="button" wire:click="clearDistributor" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error hover:text-white">
                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @else
                                <input wire:model.live.debounce.300ms="searchDistributor" type="text" placeholder="Ketik nama atau kode distributor..." class="input input-sm input-bordered w-full rounded-xl">
                                
                                @if(count($distributorOptions) > 0)
                                    <ul class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-1">
                                        @foreach($distributorOptions as $dist)
                                            <li>
                                                <button type="button" wire:click="selectDistributor('{{ $dist->distributor_code }}', '{{ addslashes($dist->distributor_name) }}')" class="w-full text-left px-3 py-2 text-sm hover:bg-base-200 rounded-lg">
                                                    <div class="font-bold">{{ $dist->distributor_code }}</div>
                                                    <div class="text-xs opacity-70">{{ $dist->distributor_name }}</div>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>

                        {{-- Search Customer --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Customer</label>
                            <div class="relative">
                                <input wire:model.live.debounce.500ms="searchCustomer" type="text" placeholder="Ketik kode, nama, atau alamat..." class="input input-sm input-bordered w-full rounded-xl pr-10">
                                <div wire:loading wire:target="searchCustomer" class="absolute right-3 top-2">
                                    <span class="loading loading-spinner loading-xs text-primary"></span>
                                </div>
                            </div>
                            
                            @if(count($customerOptions) > 0)
                                <ul class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-1">
                                    @foreach($customerOptions as $cust)
                                        <li>
                                            <div class="w-full text-left px-3 py-2 hover:bg-base-200 rounded-lg flex justify-between items-center group cursor-default">
                                                <div class="flex-1">
                                                    <div class="font-bold text-sm">{{ $cust->custno }} - {{ $cust->custname }}</div>
                                                    <div class="text-xs opacity-70 truncate">{{ $cust->distributor_name }} ({{ $cust->distributor_code }})</div>
                                                    <div class="text-[10px] opacity-50 truncate">{{ $cust->addres }}</div>
                                                </div>
                                                <button type="button" wire:click="addCustomerToCart('{{ $cust->custno }}')" class="btn btn-xs btn-primary btn-square opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <x-heroicon-s-plus class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif(strlen($searchCustomer) >= 3)
                                <div class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-3 text-center text-xs text-base-content/50">
                                    <span wire:loading.remove wire:target="searchCustomer">Tidak ditemukan customer yang sesuai.</span>
                                    <span wire:loading wire:target="searchCustomer">Mencari...</span>
                                </div>
                            @endif
                        </div>

                        @error('selectedCustomers') 
                            <div class="alert alert-error bg-error/10 text-error text-xs p-2 rounded-lg mt-4 border-none">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                {{ $message }}
                            </div>
                        @enderror
                    </form>
                </div>

                {{-- Kanan: Daftar Customer (Cart) --}}
                <div class="w-full md:w-1/2 p-0 flex flex-col bg-base-200/20 overflow-y-auto">
                    <div class="p-4 border-b border-base-300 flex justify-between items-center bg-base-100 sticky top-0 z-10">
                        <h4 class="font-bold text-sm uppercase tracking-wide">Daftar Customer Terpilih</h4>
                        <span class="badge badge-primary">{{ count($selectedCustomers) }} Toko</span>
                    </div>
                    
                    <div class="p-4 flex-1">
                        @if(count($selectedCustomers) == 0)
                            <div class="h-full flex flex-col items-center justify-center text-base-content/30 space-y-3">
                                <x-heroicon-o-shopping-bag class="w-16 h-16" />
                                <p class="text-sm">Belum ada customer yang dipilih.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($selectedCustomers as $idx => $cartItem)
                                    <div class="bg-base-100 border border-base-300 rounded-xl p-3 shadow-sm flex items-start gap-3 relative">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ $idx + 1 }}
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <div class="font-bold text-sm">{{ $cartItem['custno'] }} - {{ $cartItem['custname'] }}</div>
                                            <div class="text-xs text-base-content/70 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                                                <span class="flex items-center gap-1"><x-heroicon-s-building-storefront class="w-3 h-3"/> {{ $cartItem['distributor_code'] }}</span>
                                                <span class="flex items-center gap-1"><x-heroicon-s-map-pin class="w-3 h-3"/> {{ $cartItem['nama_area'] }}, {{ $cartItem['nama_region'] }}</span>
                                            </div>
                                            <div class="text-[10px] text-base-content/50 mt-1 truncate">{{ $cartItem['addres'] }}</div>
                                        </div>
                                        <button type="button" wire:click="removeCustomerFromCart('{{ $cartItem['custno'] }}')" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error hover:text-white shrink-0">
                                            <x-heroicon-s-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50 mt-auto">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case">Batal</button>
                <button wire:click="save" type="button" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                    <span wire:loading.remove wire:target="save">Simpan Daftar ({{ count($selectedCustomers) }})</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Import Excel --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <h3 class="font-bold text-lg text-base-content">Import Data Excel</h3>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            <form wire:submit.prevent="import">
                <div class="p-6">
                    @if($importStep === 1)
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Start Date</label>
                                <input type="date" wire:model="importStartDate" class="input input-bordered input-sm w-full rounded-xl">
                                @error('importStartDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">End Date</label>
                                <input type="date" wire:model="importEndDate" class="input input-bordered input-sm w-full rounded-xl">
                                @error('importEndDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-1.5 mb-4">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Import Method</label>
                            <select wire:model="importMethod" class="select select-bordered select-sm w-full rounded-xl">
                                <option value="full_sync">Full Sync (Hapus & Timpa Data Terkait)</option>
                                <option value="partial_update">Partial Update (Hanya Tambah/Update Data Baru)</option>
                            </select>
                            @error('importMethod') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">File Excel (xls, xlsx, csv)</label>
                            <input type="file" wire:model="excel_file" class="file-input file-input-bordered file-input-sm w-full rounded-xl" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            @error('excel_file') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            
                            <div wire:loading wire:target="excel_file" class="text-xs text-info mt-2">Mengunggah...</div>
                        </div>

                        @if(count($importErrors) > 0)
                            <div class="mt-4 bg-error/10 border border-error/20 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-error shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="font-bold text-sm text-error mb-1">Import Gagal</h4>
                                        <p class="text-xs text-base-content/70 mb-3">
                                            Terdapat <strong>{{ count($importErrors) }}</strong> baris data yang bermasalah (kode tidak ditemukan di database atau kosong). Import dibatalkan untuk mencegah data tidak lengkap.
                                        </p>
                                        <button type="button" wire:click="downloadErrorLog" class="btn btn-sm btn-error text-white rounded-lg text-xs gap-2 shadow-sm">
                                            <x-heroicon-s-document-text class="w-4 h-4" />
                                            Download Log Error (.txt)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($importStep === 2)
                        <div class="mb-5 bg-warning/10 p-4 rounded-xl border border-warning/20">
                            <h4 class="font-bold text-sm text-warning-content mb-3 flex items-center gap-2">
                                <x-heroicon-s-eye class="w-5 h-5 text-warning" />
                                Preview Import
                            </h4>
                            
                            <div class="grid grid-cols-2 gap-y-3 gap-x-6 text-sm text-base-content/80 mb-4">
                                <div>
                                    <span class="block text-xs font-semibold text-base-content/50 uppercase tracking-wider">Method</span>
                                    <span class="font-medium text-base-content">{{ $importMethod === 'full_sync' ? 'Full Sync' : 'Partial Update' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-semibold text-base-content/50 uppercase tracking-wider">Date Range</span>
                                    <span class="font-medium text-base-content">{{ Carbon\Carbon::parse($importStartDate)->format('d M Y') }} - {{ Carbon\Carbon::parse($importEndDate)->format('d M Y') }}</span>
                                </div>
                            </div>

                            <div class="bg-base-100 rounded-lg p-4 border border-base-200 grid grid-cols-3 gap-4 text-center divide-x divide-base-200 mb-4">
                                <div>
                                    <span class="block text-xs text-base-content/50 mb-1">Upload Rows</span>
                                    <span class="text-xl font-bold text-primary">{{ number_format($previewTotalRows) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-base-content/50 mb-1">Affected Teams</span>
                                    <span class="text-xl font-bold text-secondary">{{ number_format($previewTotalTeams) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-base-content/50 mb-1">Existing DB Rows</span>
                                    <span class="text-xl font-bold text-neutral">{{ number_format($previewExistingRows) }}</span>
                                </div>
                            </div>

                            @if($importMethod === 'full_sync')
                                <div class="flex items-start gap-2 text-xs text-error font-medium bg-error/10 p-3 rounded-lg">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 shrink-0" />
                                    <p><strong>Warning:</strong> Existing schedule data in selected scope will be replaced!</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <div>
                        @if($importStep === 1)
                            <button type="button" wire:click="downloadTemplate" class="btn btn-ghost rounded-xl normal-case text-info hover:bg-info/10">
                                <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-1" />
                                Download Template
                            </button>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        @if($importStep === 1)
                            <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case">Batal</button>
                            <button type="button" wire:click="previewImport" class="btn btn-primary rounded-xl px-8 normal-case text-white" wire:loading.attr="disabled" wire:target="previewImport, excel_file">
                                <span wire:loading.remove wire:target="previewImport">Preview Import</span>
                                <span wire:loading wire:target="previewImport" class="loading loading-spinner loading-xs"></span>
                            </button>
                        @endif

                        @if($importStep === 2)
                            <button type="button" wire:click="$set('importStep', 1)" class="btn btn-ghost rounded-xl normal-case">Kembali</button>
                            <button type="button" wire:click="executeImport" class="btn btn-success rounded-xl px-8 normal-case text-white" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="executeImport">Execute Import</span>
                                <span wire:loading wire:target="executeImport" class="loading loading-spinner loading-xs"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden text-center">
            <div class="p-8">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Grup Data?</h3>
                <p class="text-sm text-base-content/60">Tindakan ini akan menghapus semua customer pada grup ini secara permanen.</p>
            </div>
            <div class="flex justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Detail Toko --}}
    <div x-data="{ open: @entangle('isStoreModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <h3 class="font-bold text-lg text-base-content">{{ $storeModalTitle }}</h3>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-0 overflow-hidden">
                <div class="max-h-[60vh] overflow-y-auto">
                    <table class="table table-sm table-pin-rows table-zebra w-full">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th class="whitespace-nowrap">CustNo</th>
                                <th>Nama Toko</th>
                                <th>Distributor</th>
                                <th class="text-center whitespace-nowrap">Pilar</th>
                                <th class="text-right whitespace-nowrap">Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storeModalData as $store)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="font-medium text-base-content/80 whitespace-nowrap">{{ $store['custno'] }}</td>
                                    <td>{{ $store['custname'] }}</td>
                                    <td class="text-base-content/60">{{ $store['distributor_name'] }}</td>
                                    <td class="text-center whitespace-nowrap"><span class="badge badge-sm badge-outline">{{ $store['pilar'] ?? '-' }}</span></td>
                                    <td class="text-right font-medium text-success whitespace-nowrap">{{ $store['target'] ? number_format($store['target']) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-base-content/50">Tidak ada data toko.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end px-6 py-4 border-t border-base-300 bg-base-200/50">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case">Tutup</button>
            </div>
        </div>
    </div>
</div>
