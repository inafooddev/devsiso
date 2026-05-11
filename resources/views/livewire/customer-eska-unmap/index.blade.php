<div>
    <x-slot name="title">Laporan Customer Belum Terpetakan (ESKA)</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
        {{-- Notifikasi --}}
        <div class="mb-6 space-y-3">
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                     class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">{{ session('message') }}</div>
                    </div>
                </div>
            @endif
            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                </div>
            @endif
        </div>

        <x-card flush title="Unmapped Customers" icon="user-minus" subtitle="Laporan customer distributor yang belum memiliki pemetaan valid di sistem principal" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    @if ($isFiltered)
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.500ms="search" type="text"
                               placeholder="Cari kode/nama customer..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                    @endif

                    {{-- Filter Button --}}
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        Filter
                        @if($isFiltered)
                            <span class="badge badge-xs badge-primary rounded-full">ON</span>
                        @endif
                    </button>

                    {{-- Export --}}
                    @if ($isFiltered)
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export Excel
                    </button>
                    @endif
                </div>
            </x-slot:actions>

            {{-- State: Filter Belum Diterapkan --}}
            @if (!$isFiltered)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-funnel class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Filter Belum Diterapkan</h3>
                    <p class="text-sm text-center max-w-xs">Silakan tentukan periode dan wilayah untuk memuat laporan customer yang belum terpetakan.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter Laporan
                    </button>
                </div>
            @else
                {{-- Tabel --}}
                <x-ui.table empty="Semua customer untuk wilayah dan periode ini sudah terpetakan dengan benar.">
                    <x-slot:head>
                        <tr>
                            <th>Bulan</th>
                            <th>Region / Area</th>
                            <th>Distributor</th>
                            <th class="w-48 bg-base-200/30">Data Distributor (ESKA)</th>
                            <th class="w-48 bg-primary/5">Data Principal (ESKA)</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($customers as $row)
                        <tr class="group text-sm">
                            <td><span class="font-mono text-xs font-bold text-base-content/60">{{ Carbon\Carbon::parse($row->bln)->format('M Y') }}</span></td>
                            <td>
                                <div>
                                    <span class="font-bold text-base-content/80">{{ $row->region_name }}</span>
                                    <div class="text-[10px] text-base-content/40 font-bold uppercase tracking-widest mt-0.5">{{ $row->area_name }}</div>
                                </div>
                            </td>
                            <td><span class="font-medium text-base-content/80">{{ $row->distributor_name }}</span></td>
                            <td class="bg-base-200/10">
                                <div class="flex flex-col gap-0.5">
                                    <span class="badge badge-xs badge-outline rounded-md font-mono text-[10px]">{{ $row->custno_dist }}</span>
                                    <span class="font-bold text-base-content/80 text-xs">{{ $row->dist_cust_name }}</span>
                                    <span class="text-[10px] text-base-content/40 italic">{{ $row->distid }} - {{ $row->branch_dist }}</span>
                                </div>
                            </td>
                            <td class="bg-primary/5">
                                <div class="flex flex-col gap-0.5">
                                    <span class="badge badge-xs badge-primary rounded-md font-mono text-[10px]">{{ $row->custno }}</span>
                                    <span class="font-bold text-primary text-xs">{{ $row->prc_cust_name }}</span>
                                    <span class="text-[10px] text-base-content/50">Cabang: {{ $row->branch }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if($customers->hasPages())
                    <div class="mt-4 px-6">{{ $customers->links() }}</div>
                @endif
            @endif
        </x-card>
    </div>

    {{-- ========== MODAL FILTER ========== --}}
    <div x-data="{ open: @entangle('isFilterModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Unmapped Customer</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Gunakan multi-select untuk wilayah</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters">
                <div class="p-6 space-y-6">
                    {{-- Periode --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Bulan <span class="text-error">*</span></label>
                        <input type="month" wire:model="monthFilter"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        @error('monthFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Multi-Select List (Vertical Stack) --}}
                    <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-base-300">
                        {{-- Region --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-1">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">Region</label>
                                <button type="button" wire:click="selectAllRegions" class="text-[10px] font-bold text-primary hover:underline">Select All</button>
                            </div>
                            <div class="bg-base-200 rounded-2xl border border-base-300 h-32 overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-base-300">
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($regionsOption as $r)
                                        <label class="flex items-center gap-3 p-2 hover:bg-base-300 rounded-xl cursor-pointer transition-colors group">
                                            <input type="checkbox" wire:model.live="regionFilter" value="{{ $r->region_code }}" 
                                                   class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-all duration-300 group-hover:scale-110">
                                            <span class="text-xs font-medium text-base-content/70">{{ $r->region_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @error('regionFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Area --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-1">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 {{ empty($regionFilter) ? 'opacity-30' : '' }}">Area</label>
                                @if(!empty($areasOption))
                                <button type="button" wire:click="selectAllAreas" class="text-[10px] font-bold text-primary hover:underline">Select All</button>
                                @endif
                            </div>
                            <div class="bg-base-200 rounded-2xl border border-base-300 h-40 overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-base-300 {{ empty($regionFilter) ? 'opacity-40' : '' }}">
                                @if(!empty($areasOption))
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($areasOption as $a)
                                            <label class="flex items-center gap-3 p-2 hover:bg-base-300 rounded-xl cursor-pointer transition-colors group">
                                                <input type="checkbox" wire:model.live="areaFilter" value="{{ $a->area_code }}" 
                                                       class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-all duration-300 group-hover:scale-110">
                                                <span class="text-xs font-medium text-base-content/70">{{ $a->area_name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-full flex items-center justify-center text-[10px] text-base-content/30 italic text-center px-4">
                                        Pilih region terlebih dahulu untuk memuat area.
                                    </div>
                                @endif
                            </div>
                            @error('areaFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Distributor --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-1">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 {{ empty($areaFilter) ? 'opacity-30' : '' }}">Distributor</label>
                                @if(!empty($distributorsOption))
                                <button type="button" wire:click="selectAllDistributors" class="text-[10px] font-bold text-primary hover:underline">Select All</button>
                                @endif
                            </div>
                            <div class="bg-base-200 rounded-2xl border border-base-300 h-48 overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-base-300 {{ empty($areaFilter) ? 'opacity-40' : '' }}">
                                @if(!empty($distributorsOption))
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($distributorsOption as $d)
                                            <label class="flex items-center gap-3 p-2 hover:bg-base-300 rounded-xl cursor-pointer transition-colors group">
                                                <input type="checkbox" wire:model="distributorFilter" value="{{ $d->distributor_code }}" 
                                                       class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-all duration-300 group-hover:scale-110">
                                                <div class="flex flex-col">
                                                    <span class="text-[11px] font-bold text-base-content/70 leading-tight">{{ $d->distributor_name }}</span>
                                                    <span class="text-[9px] font-mono text-base-content/40">{{ $d->distributor_code }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-full flex items-center justify-center text-[10px] text-base-content/30 italic text-center px-4">
                                        Pilih area terlebih dahulu untuk memuat distributor.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- testing -->
                <div class="flex items-center justify-between gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" wire:click="resetFilters" @click="open = false"
                            class="btn btn-ghost rounded-xl normal-case text-error hover:bg-error/10 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" /> Reset
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Tampilkan Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
