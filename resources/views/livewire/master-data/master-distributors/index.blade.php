<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Data Master Distributor</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-2 md:gap-3 w-full h-full">
        {{-- Notifikasi --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0">
                <x-heroicon-s-check-circle class="w-5 h-5" />
                <div>
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                    <div class="text-xs">{{ session('message') }}</div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0">
                <x-heroicon-s-x-circle class="w-5 h-5" />
                <div>
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                    <div class="text-xs">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 shrink-0">
            <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-2 flex flex-col items-center justify-center text-center">
                <p class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider mb-0.5">Aktif</p>
                <h4 class="text-lg md:text-xl font-black text-success">{{ $this->kpiData['aktif'] }}</h4>
            </div>
            <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-2 flex flex-col items-center justify-center text-center">
                <p class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider mb-0.5" title="Jawa 1 Aktif">Jawa 1</p>
                <h4 class="text-lg md:text-xl font-black text-success">{{ $this->kpiData['jawa1'] }}</h4>
            </div>
            <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-2 flex flex-col items-center justify-center text-center">
                <p class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider mb-0.5" title="Jawa 2 Aktif">Jawa 2</p>
                <h4 class="text-lg md:text-xl font-black text-success">{{ $this->kpiData['jawa2'] }}</h4>
            </div>
            <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-2 flex flex-col items-center justify-center text-center">
                <p class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider mb-0.5" title="Pulau Aktif">Pulau</p>
                <h4 class="text-lg md:text-xl font-black text-success">{{ $this->kpiData['pulau'] }}</h4>
            </div>
            <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-2 flex flex-col items-center justify-center text-center">
                <p class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider mb-0.5" title="Sumatera 1 Aktif">Sumatera 1</p>
                <h4 class="text-lg md:text-xl font-black text-success">{{ $this->kpiData['sumatera1'] }}</h4>
            </div>
            <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-2 flex flex-col items-center justify-center text-center">
                <p class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider mb-0.5" title="Sumatera 2 Aktif">Sumatera 2</p>
                <h4 class="text-lg md:text-xl font-black text-success">{{ $this->kpiData['sumatera2'] }}</h4>
            </div>
            <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-2 flex flex-col items-center justify-center text-center">
                <p class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider mb-0.5" title="Remote Aktif">Remote</p>
                <h4 class="text-lg md:text-xl font-black text-success">{{ $this->kpiData['remote'] }}</h4>
            </div>
        </div>

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">

            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Master Distributor</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data distributor</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <!-- Search Component -->
                    <x-ui.search-input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari Distributor..." 
                    />

                    <!-- Filter Status -->
                    <select wire:model.live="statusFilter" class="select select-sm select-bordered w-32 sm:w-36 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>

                    <!-- Filter Region Component -->
                    <select wire:model.live="regionFilter" class="select select-sm select-bordered w-36 sm:w-40 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <!-- Filter Area Component -->
                    <select wire:model.live="areaFilter" @disabled(!$regionFilter) class="select select-sm select-bordered w-36 sm:w-40 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-50">
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    <div class="flex flex-wrap items-center gap-1 md:gap-2">
                        @canEdit('master-distributors.index')
                            <x-ui.action-button type="default" icon="arrow-path" wire:click="synchronize" title="Sinkronisasi dengan Data Master Dasar" label="Sync" />
                        @endcanEdit
                        <x-ui.action-button type="default" class="btn-info text-white shadow-sm shadow-info/20" icon="map" wire:click="showAllMaps" title="Tampilkan Peta Sebaran" label="Maps" />
                        @canExport('master-distributors.index')
                            <x-ui.action-button type="export" wire:click="export" />
                        @endcanExport

                        <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>

                        @canEdit('master-distributors.index')
                        <x-ui.action-button
                            type="add"
                            wire:click="openCreateModal"
                        />
                        @endcanEdit
                    </div>
                </div>
            </div>

            {{-- Body Card (Tabel Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                @if($distributors->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-base-content/50 p-6">
                        <x-heroicon-o-truck class="w-12 h-12 mb-2 opacity-50" />
                        <p class="text-sm font-medium">Tidak ada data distributor ditemukan.</p>
                    </div>
                @else
                    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                        <thead class="text-[11px] uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th class="w-32">Region</th>
                                <th class="w-40">Area</th>
                                <th class="w-48">Supervisor</th>
                                <th class="min-w-[250px] w-full">Distributor</th>
                                <th class="w-24 text-center">Status</th>
                                <th class="w-28 text-center">Join Date</th>
                                <th class="w-28 text-center">Resign Date</th>
                                <th class="w-32 text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-[11px]">
                            @foreach ($distributors as $index => $distributor)
                                <tr wire:key="distributor-{{ $distributor->distributor_code }}" class="hover:bg-base-200/50 transition-colors group">
                                    <th class="text-center">{{ $distributors->firstItem() + $index }}</th>
                                    
                                    {{-- Region --}}
                                    <td>
                                        <div class="flex flex-col gap-0.5 w-32">
                                            <span class="font-mono text-[11px] font-semibold text-base-content/70 truncate" title="{{ $distributor->region_code ?? '-' }}">{{ $distributor->region_code ?? '-' }}</span>
                                            <span class="text-[10px] uppercase tracking-wider font-bold text-base-content/90 truncate" title="{{ $distributor->region_name ?? '-' }}">{{ $distributor->region_name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    {{-- Area --}}
                                    <td>
                                        <div class="flex flex-col gap-0.5 w-40">
                                            <span class="font-mono text-[11px] font-semibold text-base-content/70 truncate" title="{{ $distributor->area_code ?? '-' }}">{{ $distributor->area_code ?? '-' }}</span>
                                            <span class="text-[10px] uppercase tracking-wider font-bold text-base-content/90 truncate" title="{{ $distributor->area_name ?? '-' }}">{{ $distributor->area_name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    {{-- Supervisor --}}
                                    <td>
                                        <div class="flex flex-col gap-0.5 w-48">
                                            <span class="font-mono text-[11px] font-semibold text-base-content/70 truncate" title="{{ $distributor->supervisor_code ?? '-' }}">{{ $distributor->supervisor_code ?? '-' }}</span>
                                            <span class="text-[10px] uppercase tracking-wider font-bold text-base-content/90 truncate" title="{{ $distributor->supervisor->description ?? $distributor->supervisor_name ?? '-' }}">{{ $distributor->supervisor->description ?? $distributor->supervisor_name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    {{-- Distributor --}}
                                    <td>
                                        <div class="flex flex-col gap-0.5 min-w-[250px] w-full max-w-[250px] sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl">
                                            <span class="font-mono text-[11px] font-semibold text-primary truncate" title="{{ $distributor->distributor_code }}">{{ $distributor->distributor_code }}</span>
                                            <span class="font-bold text-[11px] text-base-content/80 group-hover:text-primary transition-colors truncate w-full" title="{{ $distributor->distributor_name }}">{{ $distributor->distributor_name }}</span>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @if ($distributor->is_active)
                                            <span class="badge badge-sm border-none bg-success/20 text-success font-bold px-3 py-2 rounded-lg text-[10px]">Aktif</span>
                                        @else
                                            <span class="badge badge-sm border-none bg-error/20 text-error font-bold px-3 py-2 rounded-lg text-[10px]">Nonaktif</span>
                                        @endif
                                    </td>

                                    {{-- Join Date --}}
                                    <td class="text-center text-base-content/70">
                                        {{ $distributor->join_date ? $distributor->join_date->translatedFormat('d M Y') : '-' }}
                                    </td>

                                    {{-- Resign Date --}}
                                    <td class="text-center text-base-content/70">
                                        {{ $distributor->resign_date ? $distributor->resign_date->translatedFormat('d M Y') : '-' }}
                                    </td>

                                    <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="showDetail('{{ $distributor->distributor_code }}')" 
                                                    class="btn btn-ghost btn-sm btn-square rounded-xl text-info hover:bg-info/10 transition-all duration-200" title="Detail">
                                                <x-heroicon-s-information-circle class="w-4 h-4" />
                                            </button>
                                            @canEdit('master-distributors.index')
                                            <button wire:click="openEditModal('{{ $distributor->distributor_code }}')" 
                                                    class="btn btn-ghost btn-sm btn-square rounded-xl text-warning hover:bg-warning/10 transition-all duration-200" title="Edit">
                                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                            </button>
                                            <button wire:click="confirmDelete('{{ $distributor->distributor_code }}')" 
                                                    class="btn btn-ghost btn-sm btn-square rounded-xl text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                                <x-heroicon-s-trash class="w-4 h-4" />
                                            </button>
                                            @endcanEdit
                                        </div>
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if($distributors->hasPages())
                <div class="p-3 md:p-4 border-t border-base-300 bg-base-50 shrink-0">
                    {{ $distributors->links() }}
                </div>
            @endif
        </div>
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-3xl overflow-hidden ring-1 ring-base-content/5 max-h-[90vh] flex flex-col text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Distributor' : 'Tambah Distributor Baru' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data kemitraan distributor' : 'Daftarkan mitra distributor baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save" class="overflow-y-auto">
                <div class="p-6 space-y-8 bg-base-100">
                    {{-- Section: Profil Distributor --}}
                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary/60 mb-5 px-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary/40"></span> Profil Distributor
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label for="distributor_code" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Distributor</label>
                                <div class="relative group">
                                    <input wire:model.blur="distributor_code" type="text" id="distributor_code" placeholder="Cth: DIST-001"
                                           class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('distributor_code') input-error @enderror"
                                           {{ $isEditing ? 'disabled' : '' }}>
                                    @if($isEditing)
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                            <x-heroicon-s-lock-closed class="w-4 h-4" />
                                        </div>
                                    @endif
                                </div>
                                @error('distributor_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="distributor_name" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Distributor</label>
                                <input wire:model.blur="distributor_name" type="text" id="distributor_name" placeholder="Cth: PT. Sukses Jaya"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('distributor_name') input-error @enderror">
                                @error('distributor_name') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="join_date" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal Bergabung</label>
                                <input wire:model.blur="join_date" type="date" id="join_date"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>

                            <div class="space-y-1.5">
                                <label for="resign_date" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal Berhenti</label>
                                <input wire:model.blur="resign_date" type="date" id="resign_date"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Penugasan Cabang --}}
                    <div class="border-t border-base-200 pt-6">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary/60 mb-5 px-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary/40"></span> Penugasan Cabang & Wilayah
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5 relative">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Cabang</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base-content/30">
                                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                                    </div>
                                    <input wire:model.live.debounce.300ms="branchSearch" type="text" placeholder="Ketik nama atau kode cabang..."
                                           class="input input-bordered w-full pl-11 bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    
                                    @if(count($this->branchesSearch) > 0)
                                        <div class="absolute z-50 w-full mt-2 bg-base-100 border border-base-300 rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                            @foreach($this->branchesSearch as $branch)
                                                <button type="button" wire:click="selectBranch('{{ $branch->branch_code }}', '{{ $branch->branch_name }}')"
                                                        class="w-full px-4 py-3 text-left hover:bg-base-200 flex items-center justify-between border-b border-base-200 last:border-0 transition-colors">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-bold text-base-content/80">{{ $branch->branch_name }}</span>
                                                        <span class="text-[10px] text-base-content/40 font-mono">{{ $branch->branch_code }}</span>
                                                    </div>
                                                    <x-heroicon-s-chevron-right class="w-4 h-4 text-base-content/20" />
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif(strlen($branchSearch) >= 2)
                                        <div class="absolute z-50 w-full mt-2 p-4 bg-base-100 border border-base-300 rounded-2xl shadow-xl text-center text-xs text-base-content/40 italic">
                                            Cabang tidak ditemukan
                                        </div>
                                    @endif
                                </div>
                                
                                @if($selectedBranchName)
                                    <div class="mt-3 p-4 rounded-2xl bg-primary/5 border border-primary/10 flex items-center justify-between group/sel animate-in slide-in-from-top-2 duration-300">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                                <x-heroicon-s-building-office-2 class="w-4 h-4" />
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-primary">{{ $selectedBranchName }}</span>
                                                <span class="text-[10px] text-base-content/40 font-mono tracking-tighter">{{ $branch_code }}</span>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="$set('branch_code', '')" class="btn btn-ghost btn-xs btn-circle text-base-content/20 hover:text-error hover:bg-error/10">
                                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                @endif
                                @error('branch_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                            </div>

                            <div class="bg-base-200/50 rounded-2xl p-5 border border-base-300 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-base-content/30">Auto Info</span>
                                    <x-heroicon-s-information-circle class="w-4 h-4 text-base-content/20" />
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-base-content/40">Region</span>
                                        <span class="font-bold text-base-content/70">{{ $region_name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-base-content/40">Area</span>
                                        <span class="font-bold text-base-content/70">{{ $area_name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-base-content/40">Supervisor</span>
                                        <span class="font-bold text-base-content/70 italic">{{ $supervisor_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Geolocation --}}
                    <div class="border-t border-base-200 pt-6">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary/60 mb-5 px-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary/40"></span> Titik Lokasi (Geotagging)
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label for="latitude" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Latitude</label>
                                <input wire:model.blur="latitude" type="text" id="latitude" placeholder="Cth: -6.123456"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>

                            <div class="space-y-1.5">
                                <label for="longitude" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Longitude</label>
                                <input wire:model.blur="longitude" type="text" id="longitude" placeholder="Cth: 106.123456"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Status --}}
                    <div class="border-t border-base-200 pt-6">
                        <div class="flex items-center justify-between bg-base-200/50 p-4 rounded-2xl border border-base-300">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70">Status Distributor</h4>
                                <p class="text-[10px] text-base-content/40">Kontrol aktifasi distributor dalam sistem</p>
                            </div>
                            <input type="checkbox" wire:model="is_active" class="toggle toggle-primary toggle-lg shadow-sm" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 shrink-0">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Daftarkan Distributor' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5">
            
            <div class="p-8 text-center text-base-content">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold mb-2 leading-none text-base-content">Hapus Distributor?</h3>
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Seluruh data riwayat yang terkait dengan distributor ini akan terdampak. Tindakan ini <span class="text-error font-bold italic">permanen</span>.</p>
            </div>

            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case transition-all duration-200">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 transition-all duration-200 text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div x-data="{ 
        open: @entangle('isDetailModalOpen'),
        detail: @entangle('detailData'),
        latitude: @entangle('mapLatitude'),
        longitude: @entangle('mapLongitude'),
        initMap() {
            if (!this.open) return;
            const waitForLeaflet = setInterval(() => {
                if (typeof L !== 'undefined') {
                    clearInterval(waitForLeaflet);
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (!this.latitude || !this.longitude) return;
                            const mapElement = document.getElementById('distributorMap');
                            if (!mapElement) return;
                            
                            const lat = parseFloat(this.latitude);
                            const lng = parseFloat(this.longitude);
                            
                            if (isNaN(lat) || isNaN(lng)) return;
                            
                            if (window.distributorMapInstance) {
                                window.distributorMapInstance.remove();
                                window.distributorMapInstance = null;
                            }
                            
                            try {
                                const map = L.map('distributorMap').setView([lat, lng], 16);
                                window.distributorMapInstance = map;
                                
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
                                }).addTo(map);
                                
                                const colorClass = this.detail?.is_active ? 'text-success' : 'text-error';
                                const customIcon = L.divIcon({
                                    className: 'custom-pin bg-transparent border-none',
                                    html: `<svg class='w-8 h-8 ${colorClass} drop-shadow-lg' fill='currentColor' viewBox='0 0 24 24'><path d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'/></svg>`,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 32],
                                    popupAnchor: [0, -32]
                                });

                                L.marker([lat, lng], {icon: customIcon}).addTo(map)
                                 .bindPopup(`<div class='font-sans px-1 py-0.5 text-slate-800'><strong class='text-slate-800'>${this.detail?.distributor_name}</strong><br><span class='text-[10px] text-slate-500 font-mono'>${lat}, ${lng}</span></div>`)
                                 .openPopup();
                                
                                setTimeout(() => { map.invalidateSize(); }, 200);
                            } catch (error) { console.error('Map error:', error); }
                        }, 300);
                    });
                }
            }, 100);
            setTimeout(() => { clearInterval(waitForLeaflet); }, 5000);
        }
    }" 
    x-show="open" 
    @detail-opened.window="initMap()"
    x-cloak 
    class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-5xl overflow-hidden ring-1 ring-base-content/5 flex flex-col text-base-content max-h-[90vh]">
            
            <div class="px-6 py-5 border-b border-base-300 flex items-center justify-between bg-base-200/30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                        <x-heroicon-s-information-circle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none" x-text="detail?.distributor_name || 'Detail Distributor'"></h3>
                        <p class="text-[10px] text-base-content/50 mt-1 font-mono">KODE: <span x-text="detail?.distributor_code"></span></p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-6 bg-base-100 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Data Detail -->
                    <div class="space-y-4 text-sm">
                        <div class="grid grid-cols-3 gap-2 border-b border-base-300 pb-2">
                            <span class="text-base-content/60 font-medium">Status</span>
                            <div class="col-span-2">
                                <template x-if="detail?.is_active">
                                    <span class="badge badge-sm border-none bg-success/20 text-success font-bold px-3 py-2 rounded-lg text-[10px]">Aktif</span>
                                </template>
                                <template x-if="!detail?.is_active">
                                    <span class="badge badge-sm border-none bg-error/20 text-error font-bold px-3 py-2 rounded-lg text-[10px]">Nonaktif</span>
                                </template>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 border-b border-base-300 pb-2">
                            <span class="text-base-content/60 font-medium">Region</span>
                            <span class="col-span-2 font-semibold" x-text="detail?.region_name"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 border-b border-base-300 pb-2">
                            <span class="text-base-content/60 font-medium">Area</span>
                            <span class="col-span-2 font-semibold" x-text="detail?.area_name"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 border-b border-base-300 pb-2">
                            <span class="text-base-content/60 font-medium">Supervisor</span>
                            <span class="col-span-2 font-semibold" x-text="detail?.supervisor_name"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 border-b border-base-300 pb-2">
                            <span class="text-base-content/60 font-medium">Cabang</span>
                            <span class="col-span-2 font-semibold" x-text="detail?.branch_name"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 border-b border-base-300 pb-2">
                            <span class="text-base-content/60 font-medium">Tanggal Join</span>
                            <span class="col-span-2 font-semibold" x-text="detail?.join_date"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 border-b border-base-300 pb-2">
                            <span class="text-base-content/60 font-medium">Tanggal Resign</span>
                            <span class="col-span-2 font-semibold" x-text="detail?.resign_date"></span>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="flex flex-col h-full min-h-[400px]">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-s-map-pin class="w-4 h-4 text-base-content/50" />
                            <span class="text-sm font-bold text-base-content/70">Lokasi Koordinat</span>
                        </div>
                        <template x-if="latitude && longitude">
                            <div id="distributorMap" class="w-full flex-1 rounded-2xl shadow-inner border border-base-300 bg-base-200 relative overflow-hidden z-0">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-base-content/20">
                                    <span class="loading loading-spinner loading-md mb-2"></span>
                                    <p class="text-[10px] font-bold uppercase tracking-widest">Memuat Peta...</p>
                                </div>
                            </div>
                        </template>
                        <template x-if="!latitude || !longitude">
                            <div class="w-full flex-1 rounded-2xl border border-dashed border-base-300 bg-base-200/50 flex flex-col items-center justify-center text-base-content/40 p-6 text-center">
                                <x-heroicon-o-map class="w-12 h-12 mb-3 opacity-50" />
                                <p class="text-sm font-medium">Koordinat tidak tersedia</p>
                                <p class="text-[10px] mt-1">Data latitude dan longitude kosong.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal All Maps --}}
    <div x-data="{ 
        open: @entangle('isAllMapsModalOpen'),
        locations: @entangle('allMapLocations'),
        initMap() {
            if (!this.open) return;
            const waitForLeaflet = setInterval(() => {
                if (typeof L !== 'undefined') {
                    clearInterval(waitForLeaflet);
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const mapElement = document.getElementById('allDistributorsMap');
                            if (!mapElement) return;
                            
                            if (window.allDistributorsMapInstance) {
                                window.allDistributorsMapInstance.remove();
                                window.allDistributorsMapInstance = null;
                            }
                            
                            try {
                                const map = L.map('allDistributorsMap');
                                window.allDistributorsMapInstance = map;
                                
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; OpenStreetMap &copy; CARTO'
                                }).addTo(map);
                                
                                const markers = [];
                                
                                this.locations.forEach(loc => {
                                    const lat = parseFloat(loc.lat);
                                    const lng = parseFloat(loc.lng);
                                    if (isNaN(lat) || isNaN(lng)) return;
                                    
                                    const colorClass = loc.active ? 'text-success' : 'text-error';
                                    const customIcon = L.divIcon({
                                        className: 'custom-pin bg-transparent border-none',
                                        html: `<svg class='w-8 h-8 ${colorClass} drop-shadow-lg' fill='currentColor' viewBox='0 0 24 24'><path d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'/></svg>`,
                                        iconSize: [32, 32],
                                        iconAnchor: [16, 32],
                                        popupAnchor: [0, -32]
                                    });

                                    const marker = L.marker([lat, lng], {icon: customIcon})
                                        .bindPopup(`<div class='font-sans px-1 py-0.5 text-slate-800'><strong class='text-slate-800'>${loc.name}</strong><br><span class='text-[10px] text-slate-500 font-mono'>${loc.code}</span></div>`);
                                    marker.addTo(map);
                                    markers.push([lat, lng]);
                                });
                                
                                // Selalu fokus ke Indonesia secara default
                                map.setView([-0.7893, 113.9213], 5);
                                
                                setTimeout(() => { map.invalidateSize(); }, 200);
                            } catch (error) { console.error('Map error:', error); }
                        }, 300);
                    });
                }
            }, 100);
            setTimeout(() => { clearInterval(waitForLeaflet); }, 5000);
        }
    }" 
    x-show="open" 
    @all-maps-opened.window="initMap()"
    x-cloak 
    class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl max-h-[90vh] overflow-hidden ring-1 ring-base-content/5 flex flex-col text-base-content">
            
            <div class="px-6 py-5 border-b border-base-300 flex items-center justify-between bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                        <x-heroicon-s-map class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Peta Sebaran Distributor</h3>
                        <p class="text-[10px] text-base-content/50 mt-1 font-mono">Menampilkan titik distributor (<span x-text="locations.length"></span> lokasi)</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-4 bg-base-100 flex-1">
                <div id="allDistributorsMap" style="height: 60vh; width: 100%;" class="rounded-2xl shadow-inner border border-base-300 bg-base-200 relative overflow-hidden z-0">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-base-content/20">
                        <span class="loading loading-spinner loading-md mb-2"></span>
                        <p class="text-[10px] font-bold uppercase tracking-widest">Memuat Peta...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        Livewire.on('detail-opened', () => {
            setTimeout(() => { window.dispatchEvent(new CustomEvent('detail-opened')); }, 100);
        });
        Livewire.on('all-maps-opened', () => {
            setTimeout(() => { window.dispatchEvent(new CustomEvent('all-maps-opened')); }, 100);
        });
    </script>
    @endscript
</div>
