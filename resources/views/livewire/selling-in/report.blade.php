<div>
    <x-slot name="title">Laporan Selling In</x-slot>

    <!-- Full Page Loading Overlay -->
    <div wire:loading wire:target="search" class="fixed inset-0 z-50 bg-base-300/40 flex items-center justify-center backdrop-blur-sm transition-opacity" style="z-index: 9999;">
        <div class="bg-base-100 p-8 rounded-2xl shadow-xl flex flex-col items-center border border-base-200">
            <span class="loading loading-spinner loading-lg text-primary mb-4"></span>
            <h3 class="text-lg font-bold text-base-content">Menarik Data...</h3>
            <p class="text-sm text-base-content/60 mt-1">Mengkalkulasi total, mohon tunggu sebentar.</p>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 py-8">
        
        <x-card flush x-data="{
            globalSearch: '',
            get items() { return $wire.data || []; },
            get filteredItems() {
                if (this.globalSearch === '') return this.items;
                const term = this.globalSearch.toLowerCase();
                return this.items.filter(i => {
                    return (String(i.region || '').toLowerCase().includes(term)) ||
                           (String(i.area || '').toLowerCase().includes(term)) ||
                           (String(i.cabang || '').toLowerCase().includes(term)) ||
                           (String(i.kd_distributor || '').toLowerCase().includes(term)) ||
                           (String(i.nama_distributor_fix || '').toLowerCase().includes(term));
                });
            },
            get totalQty() {
                return this.filteredItems.reduce((sum, i) => sum + Number(i.qty_ktn || 0), 0);
            },
            get totalValue() {
                return this.filteredItems.reduce((sum, i) => sum + Number(i.value_net || 0), 0);
            },
            formatNumber(num) {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
            }
        }">
            
            <!-- Custom Header layout: Search on Left, Buttons on Right -->
            <div class="px-6 pt-5 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-base-200">
                <!-- Left: Global Search -->
                <div class="flex items-center gap-4 w-full md:w-auto">
                    @if($hasSearched)
                        <div class="relative w-full md:w-64">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/40" />
                            </div>
                            <input type="text" x-model="globalSearch" class="input input-sm input-bordered w-full pl-9 focus:ring-1 focus:ring-primary rounded-xl transition-all" placeholder="Cari di semua kolom...">
                        </div>
                    @endif
                </div>

                <!-- Right: Buttons -->
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($hasSearched && !empty($startMonth) && !empty($endMonth))
                        <!-- Active Filters Badge -->
                        <div class="hidden lg:flex items-center gap-2 bg-info/10 border border-info/20 px-3 py-1.5 rounded-xl text-xs text-info font-medium shadow-sm">
                            <x-heroicon-o-funnel class="w-4 h-4" />
                            {{ $this->activeFilters['period'] ?? '' }}
                            <span class="border-l border-info/30 h-4 mx-1"></span>
                            <span>{{ $this->activeFilters['regions'] ?? 0 }} Reg, {{ $this->activeFilters['areas'] ?? 0 }} Area, {{ $this->activeFilters['distributors'] ?? 0 }} Dist</span>
                        </div>
                    @endif

                    <!-- FILTER BUTTON -->
                    <x-ui.button size="sm" variant="outline" wire:click="$set('isFilterModalOpen', true)">
                        <x-heroicon-o-funnel class="w-4 h-4 mr-2" />
                        {{ $hasSearched ? 'Ubah Filter' : 'Filter' }}
                    </x-ui.button>
                    
                    <x-ui.button tag="a" href="{{ route('selling-in.index') }}" size="sm" variant="primary">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                        Import
                    </x-ui.button>
                </div>
            </div>

            <!-- Table Wrapper -->
            @if (!$hasSearched)
                <div class="flex flex-col items-center justify-center p-12 text-center text-base-content/50 min-h-[400px]">
                    <div class="bg-base-200 p-8 rounded-2xl shadow-sm border border-base-300 max-w-sm mx-auto flex flex-col items-center">
                        <div class="h-16 w-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-heroicon-o-funnel class="h-8 w-8" />
                        </div>
                        <h3 class="text-base font-semibold text-base-content">Silakan Terapkan Filter</h3>
                        <p class="mt-2 text-sm text-base-content/60 leading-relaxed">Klik tombol "Filter" di atas untuk memilih kriteria dan menampilkan data laporan.</p>
                    </div>
                </div>
            @else
                <div class="pb-4">
                    <x-ui.table hover sticky class="overflow-y-auto custom-scrollbar shadow-sm" style="max-height: 650px;" empty="Tidak ada transaksi penjualan untuk filter yang dipilih.">
                        <x-slot:head>
                            <tr>
                                <th>Region</th>
                                <th>Area</th>
                                <th>Cabang</th>
                                <th>Kode Distributor</th>
                                <th>Nama Distributor</th>
                                <th class="text-right">Qty (KTN)</th>
                                <th class="text-right">Value Net</th>
                            </tr>
                        </x-slot:head>

                        <!-- Empty State if filtered array is empty but raw data is not -->
                        <tr x-show="filteredItems.length === 0 && items.length > 0" x-cloak>
                            <td colspan="7">
                                <div class="flex flex-col items-center justify-center py-10 gap-3 text-base-content/40">
                                    <x-heroicon-o-magnifying-glass class="w-10 h-10" />
                                    <p class="text-sm">Tidak ada data yang cocok dengan kata kunci <span class="font-bold text-base-content/70" x-text="'&quot;' + globalSearch + '&quot;'"></span>.</p>
                                </div>
                            </td>
                        </tr>

                        <template x-for="(row, index) in filteredItems" :key="index">
                            <tr class="hover:bg-base-200 transition-colors duration-200">
                                <td x-text="row.region"></td>
                                <td x-text="row.area"></td>
                                <td x-text="row.cabang"></td>
                                <td class="font-mono text-base-content/70 text-xs" x-text="row.kd_distributor"></td>
                                <td class="font-medium" x-text="row.nama_distributor_fix"></td>
                                <td class="text-right font-mono text-base-content/80 text-xs" x-text="formatNumber(row.qty_ktn)"></td>
                                <td class="text-right font-bold text-primary font-mono text-xs" x-text="formatNumber(row.value_net)"></td>
                            </tr>
                        </template>

                        <x-slot:foot>
                            <tr x-show="filteredItems.length > 0" x-cloak>
                                <th colspan="5" class="text-right text-xs font-bold uppercase tracking-wider text-base-content/70">
                                    GRAND TOTAL :
                                </th>
                                <th class="text-right text-xs font-bold font-mono text-base-content" x-text="formatNumber(totalQty)">
                                </th>
                                <th class="text-right text-sm font-black text-primary font-mono" x-text="'Rp ' + formatNumber(totalValue)">
                                </th>
                            </tr>
                        </x-slot:foot>
                    </x-ui.table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- Modal Filter Laporan -->
    <dialog class="modal" :class="{ 'modal-open': $wire.isFilterModalOpen }" style="z-index: 9999;">
        <div class="modal-box max-w-2xl border border-base-300 bg-base-100 overflow-visible p-0 shadow-2xl rounded-2xl">
            
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-base-200/50 bg-base-200/20 flex justify-between items-center rounded-t-2xl">
                <div>
                    <h3 class="font-bold text-lg text-base-content tracking-tight">Filter Laporan</h3>
                    <p class="text-xs text-base-content/50 mt-0.5">Tentukan kriteria penjualan yang ingin ditampilkan</p>
                </div>
                <button wire:click="$set('isFilterModalOpen', false)" class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:text-base-content hover:bg-base-200 transition-colors">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <form wire:submit.prevent="search">
                <div class="p-6 space-y-6">
                    
                    <!-- Modal Error Handling -->
                    @if ($errors->any())
                        <div class="flex items-start gap-3 bg-error/10 border border-error/20 p-4 rounded-xl text-error">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0 mt-0.5" />
                            <div>
                                <div class="font-semibold text-sm">Mohon periksa kembali:</div>
                                <ul class="list-disc list-inside text-xs mt-1.5 space-y-0.5 opacity-90">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Area Geografis: Region & Area -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Region -->
                        <div x-data="{ openRegion: false }" class="relative">
                            <label class="text-[11px] font-semibold text-base-content/50 uppercase tracking-wider mb-1.5 block">Region <span class="text-error">*</span></label>
                            <div @click="openRegion = !openRegion"
                                 class="min-h-[42px] px-3.5 py-2 border border-base-300 rounded-xl bg-base-100 flex items-center justify-between cursor-pointer hover:border-primary/50 transition-colors shadow-sm"
                                 :class="{ 'border-primary ring-1 ring-primary/20': openRegion }">
                                <span class="text-sm truncate text-base-content">
                                    @if (count($selectedRegions) == count($regionOptions) && count($regionOptions) > 0)
                                        <span class="text-base-content/80">Semua Region</span>
                                    @elseif(count($selectedRegions) > 0)
                                        <span class="font-semibold text-primary">{{ count($selectedRegions) }}</span> Region terpilih
                                    @else
                                        <span class="text-base-content/40">Pilih Region</span>
                                    @endif
                                </span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-base-content/40 transition-transform duration-200" x-bind:class="{ 'rotate-180': openRegion }" />
                            </div>
                            
                            <div x-show="openRegion" @click.away="openRegion = false" x-cloak
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 mt-2 w-full bg-base-100 shadow-xl rounded-xl border border-base-200 overflow-hidden">
                                <div class="px-3 py-2.5 flex justify-between items-center border-b border-base-200 bg-base-200/30">
                                    <button type="button" wire:click="$set('selectedRegions', @js($regionOptions))" class="text-[11px] font-bold text-primary hover:text-primary/80 transition-colors uppercase tracking-wider">Pilih Semua</button>
                                    <button type="button" wire:click="$set('selectedRegions', [])" class="text-[11px] font-bold text-base-content/40 hover:text-base-content transition-colors uppercase tracking-wider">Reset</button>
                                </div>
                                <div class="max-h-48 overflow-y-auto p-1.5 custom-scrollbar">
                                    @forelse ($regionOptions as $r)
                                        <label class="flex items-center px-2.5 py-2 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors group">
                                            <input type="checkbox" wire:model.live="selectedRegions" value="{{ $r }}" class="checkbox checkbox-sm checkbox-primary rounded-md shadow-sm border-base-300">
                                            <span class="ml-3 text-sm text-base-content/80 group-hover:text-base-content transition-colors">{{ $r }}</span>
                                        </label>
                                    @empty
                                        <div class="p-4 text-center text-xs text-base-content/40 italic">Data kosong</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Area -->
                        <div x-data="{ openArea: false }" class="relative">
                            <label class="text-[11px] font-semibold text-base-content/50 uppercase tracking-wider mb-1.5 block">Area <span class="text-error">*</span></label>
                            <div @click="if($wire.selectedRegions.length > 0) openArea = !openArea"
                                 class="min-h-[42px] px-3.5 py-2 border rounded-xl flex items-center justify-between transition-colors shadow-sm"
                                 :class="{ 
                                    'border-primary ring-1 ring-primary/20 bg-base-100': openArea, 
                                    'border-base-300 bg-base-100 hover:border-primary/50 cursor-pointer': $wire.selectedRegions.length > 0 && !openArea,
                                    'border-base-200 bg-base-200/50 cursor-not-allowed opacity-70': $wire.selectedRegions.length === 0 
                                 }">
                                <span class="text-sm truncate text-base-content">
                                    @if (count($selectedAreas) == count($areaOptions) && count($areaOptions) > 0)
                                        <span class="text-base-content/80">Semua Area</span>
                                    @elseif(count($selectedAreas) > 0)
                                        <span class="font-semibold text-primary">{{ count($selectedAreas) }}</span> Area terpilih
                                    @else
                                        <span class="text-base-content/40">Pilih Area</span>
                                    @endif
                                </span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-base-content/40 transition-transform duration-200" x-bind:class="{ 'rotate-180': openArea }" />
                            </div>
                            
                            <div x-show="openArea" @click.away="openArea = false" x-cloak
                                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-40 mt-2 w-full bg-base-100 shadow-xl rounded-xl border border-base-200 overflow-hidden">
                                <div class="px-3 py-2.5 flex justify-between items-center border-b border-base-200 bg-base-200/30">
                                    <button type="button" wire:click="$set('selectedAreas', @js($areaOptions))" class="text-[11px] font-bold text-primary hover:text-primary/80 transition-colors uppercase tracking-wider">Pilih Semua</button>
                                    <button type="button" wire:click="$set('selectedAreas', [])" class="text-[11px] font-bold text-base-content/40 hover:text-base-content transition-colors uppercase tracking-wider">Reset</button>
                                </div>
                                <div class="max-h-48 overflow-y-auto p-1.5 custom-scrollbar">
                                    @forelse($areaOptions as $a)
                                        <label class="flex items-center px-2.5 py-2 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors group">
                                            <input type="checkbox" wire:model.live="selectedAreas" value="{{ $a }}" class="checkbox checkbox-sm checkbox-primary rounded-md shadow-sm border-base-300">
                                            <span class="ml-3 text-sm text-base-content/80 group-hover:text-base-content transition-colors">{{ $a }}</span>
                                        </label>
                                    @empty
                                        <div class="p-4 text-center text-xs text-base-content/40 italic">Pilih Region terlebih dahulu</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distributor -->
                    <div x-data="{ openDist: false }" class="relative">
                        <label class="text-[11px] font-semibold text-base-content/50 uppercase tracking-wider mb-1.5 block">Distributor <span class="text-error">*</span></label>
                        <div @click="if($wire.selectedAreas.length > 0) openDist = !openDist"
                             class="min-h-[42px] px-3.5 py-2 border rounded-xl flex items-center justify-between transition-colors shadow-sm"
                             :class="{ 
                                'border-primary ring-1 ring-primary/20 bg-base-100': openDist, 
                                'border-base-300 bg-base-100 hover:border-primary/50 cursor-pointer': $wire.selectedAreas.length > 0 && !openDist,
                                'border-base-200 bg-base-200/50 cursor-not-allowed opacity-70': $wire.selectedAreas.length === 0 
                             }">
                            <span class="text-sm truncate text-base-content">
                                @if (count($selectedDistributors) == count($distributorOptions) && count($distributorOptions) > 0)
                                    <span class="text-base-content/80">Semua Distributor</span>
                                @elseif(count($selectedDistributors) > 0)
                                    <span class="font-semibold text-primary">{{ count($selectedDistributors) }}</span> Distributor terpilih
                                @else
                                    <span class="text-base-content/40">Pilih Distributor</span>
                                @endif
                            </span>
                            <x-heroicon-o-chevron-down class="w-4 h-4 text-base-content/40 transition-transform duration-200" x-bind:class="{ 'rotate-180': openDist }" />
                        </div>
                        
                        <div x-show="openDist" @click.away="openDist = false" x-cloak
                            x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-30 mt-2 w-full bg-base-100 shadow-xl rounded-xl border border-base-200 overflow-hidden">
                            <div class="px-3 py-2.5 flex justify-between items-center border-b border-base-200 bg-base-200/30">
                                <button type="button" wire:click="$set('selectedDistributors', @js(collect($distributorOptions)->pluck('kd_distributor')->toArray()))" class="text-[11px] font-bold text-primary hover:text-primary/80 transition-colors uppercase tracking-wider">Pilih Semua</button>
                                <button type="button" wire:click="$set('selectedDistributors', [])" class="text-[11px] font-bold text-base-content/40 hover:text-base-content transition-colors uppercase tracking-wider">Reset</button>
                            </div>
                            <div class="max-h-56 overflow-y-auto p-1.5 custom-scrollbar">
                                @forelse($distributorOptions as $dist)
                                    <label class="flex items-start px-2.5 py-2 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors group">
                                        <input type="checkbox" wire:model.defer="selectedDistributors" value="{{ $dist->kd_distributor }}" class="mt-0.5 checkbox checkbox-sm checkbox-primary rounded-md shadow-sm border-base-300">
                                        <div class="ml-3 flex flex-col">
                                            <span class="text-sm text-base-content/90 group-hover:text-base-content font-medium transition-colors">{{ $dist->nama_distributor_fix }}</span>
                                            <span class="text-[10px] text-base-content/50 uppercase tracking-wide mt-0.5">{{ $dist->kd_distributor }} <span class="mx-1">•</span> {{ $dist->cabang }}</span>
                                        </div>
                                    </label>
                                @empty
                                    <div class="p-4 text-center text-xs text-base-content/40 italic">Pilih Area terlebih dahulu</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="text-[11px] font-semibold text-base-content/50 uppercase tracking-wider mb-1.5 block">Periode Awal <span class="text-error">*</span></label>
                            <input type="month" wire:model.defer="startMonth" 
                                class="input input-bordered w-full min-h-[42px] h-[42px] rounded-xl text-sm border-base-300 shadow-sm focus:border-primary focus:ring-1 focus:ring-primary transition-colors bg-base-100">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold text-base-content/50 uppercase tracking-wider mb-1.5 block">Periode Akhir <span class="text-error">*</span></label>
                            <input type="month" wire:model.defer="endMonth" 
                                class="input input-bordered w-full min-h-[42px] h-[42px] rounded-xl text-sm border-base-300 shadow-sm focus:border-primary focus:ring-1 focus:ring-primary transition-colors bg-base-100">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-base-200/30 border-t border-base-200/50 rounded-b-2xl flex items-center justify-end gap-3">
                    <x-ui.button type="button" variant="ghost" 
                        wire:click="$set('hasSearched', false); $set('data', []); $set('selectedRegions', []); $set('selectedAreas', []); $set('selectedDistributors', []); $set('startMonth', ''); $set('endMonth', '');">
                        Reset Filter
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="px-6">
                        Terapkan Filter
                    </x-ui.button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button wire:click="$set('isFilterModalOpen', false)">close</button>
        </form>
    </dialog>

    <!-- Styling Tambahan Khusus Halaman Ini -->
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: oklch(var(--n) / 0.5); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: oklch(var(--n) / 0.8); }
    </style>
</div>