<div>
    <x-slot name="title">Laporan Produk Belum Terpetakan</x-slot>

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

        <x-card flush title="Unmapped Products" icon="document-magnifying-glass" subtitle="Laporan produk distributor yang belum memiliki pemetaan ke produk principal" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari kode/nama produk..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Filter Button --}}
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        Filter
                        @if($hasAppliedFilters)
                            <span class="badge badge-xs badge-primary rounded-full">ON</span>
                        @endif
                    </button>

                    {{-- Export --}}
                    @canExport('mapping.unmapped-products')
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export Excel
                    </button>
                    @endcanExport
                </div>
            </x-slot:actions>

            {{-- State: Filter Belum Diterapkan --}}
            @if (!$hasAppliedFilters)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-funnel class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Filter Belum Diterapkan</h3>
                    <p class="text-sm text-center max-w-xs">Silakan tentukan wilayah dan periode waktu untuk memuat laporan produk yang belum terpetakan.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter Laporan
                    </button>
                </div>
            @else
                {{-- Tabel --}}
                <x-ui.table empty="Semua produk untuk periode ini sudah memiliki pemetaan.">
                    <x-slot:head>
                        <tr>
                            <th class="w-12 text-center text-xs">No</th>
                            <th>Distributor</th>
                            <th>Kode Produk (Dist)</th>
                            <th>Nama Produk (Dist)</th>
                            <th class="text-center w-24">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($products as $index => $product)
                        <tr wire:key="product-{{ $product->distributor_code }}-{{ $product->product_code }}" class="group text-sm">
                            <td class="text-center"><span class="text-xs font-semibold text-base-content/40">{{ $products->firstItem() + $index }}</span></td>
                            <td>
                                <div>
                                    <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">
                                        {{ $product->distributor_name }}
                                    </span>
                                    <div class="text-xs text-base-content/40 font-mono mt-0.5">{{ $product->distributor_code }}</div>
                                </div>
                            </td>
                            <td><span class="font-mono text-base-content/70">{{ $product->product_code }}</span></td>
                            <td><span class="font-medium text-base-content/80">{{ $product->product_name }}</span></td>
                            <td>
                                <div class="flex justify-center">
                                    @canEdit('mapping.unmapped-products')
                                    <button wire:click="openMapModal('{{ $product->distributor_code }}', '{{ $product->product_code }}', '{{ addslashes($product->product_name) }}')"
                                            class="btn btn-primary btn-xs rounded-lg normal-case gap-1 shadow-sm shadow-primary/20 transition-all duration-200">
                                        <x-heroicon-s-link class="w-3.5 h-3.5" />
                                        Petakan
                                    </button>
                                    @else
                                    <span class="text-xs text-base-content/50 italic">View Only</span>
                                    @endcanEdit
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if($products->hasPages())
                    <div class="mt-4 px-6">{{ $products->links() }}</div>
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Laporan</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Tentukan wilayah dan periode waktu</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters">
                <div class="p-6 space-y-4">
                    {{-- Region & Area --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                            <select wire:model.live="regionFilter"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                <option value="">Semua Region</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                            <select wire:model.live="areaFilter"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                    @if(!$regionFilter) disabled @endif>
                                <option value="">Semua Area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Distributor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
                        <select wire:model="distributorFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$areaFilter) disabled @endif>
                            <option value="">Semua Distributor</option>
                            @foreach($distributors as $distributor)
                                <option value="{{ $distributor->distributor_code }}">{{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Bulan</label>
                            <select wire:model="monthFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tahun</label>
                            <select wire:model="yearFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50">
                                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" wire:click="resetFilters" @click="open = false"
                            class="btn btn-ghost rounded-xl normal-case text-error hover:bg-error/10 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" /> Reset
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL PETAKAN PRODUK ========== --}}
    <div x-data="{ open: @entangle('isMapModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-xl ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-link class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Petakan Produk</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Tautkan produk distributor ke master principal</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="saveMapping">
                <div class="p-6 space-y-6">
                    {{-- Info Produk Distributor --}}
                    @if($currentProductToMap)
                        <div class="p-5 bg-base-200 rounded-2xl border border-base-300 grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-base-content/40">Produk Distributor</span>
                                <div class="font-bold text-base-content/80 leading-tight">{{ $currentProductToMap['product_name_dist'] }}</div>
                                <div class="text-xs font-mono text-base-content/50">{{ $currentProductToMap['product_code_dist'] }}</div>
                            </div>
                            <div class="space-y-1 border-l border-base-300 pl-4">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-base-content/40">Distributor</span>
                                <div class="font-bold text-base-content/80 leading-tight uppercase">{{ $currentProductToMap['distributor_code'] }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Pemilihan Produk Principal --}}
                    <div class="space-y-1.5" x-data="{ searchOpen: false }" @click.away="searchOpen = false">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Produk Principal <span class="text-error">*</span></label>
                        
                        <div class="relative">
                            {{-- Input Pencarian --}}
                            <input type="text" 
                                wire:model.live.debounce.300ms="productSearch"
                                @focus="searchOpen = true"
                                placeholder="Ketik kode atau nama produk..."
                                class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300"
                                x-show="!$wire.selectedPrincipalProduct">

                            {{-- Selected Item --}}
                            <div x-show="$wire.selectedPrincipalProduct" class="flex items-center justify-between p-3 bg-primary/10 border border-primary/20 rounded-2xl text-primary font-bold">
                                <div class="flex flex-col">
                                    <span class="leading-tight text-sm">{{ $selectedPrincipalProductName }}</span>
                                    <span class="text-[10px] font-mono opacity-60">{{ $selectedPrincipalProduct }}</span>
                                </div>
                                <button type="button" wire:click="selectProduct(null, null)" class="btn btn-ghost btn-xs btn-circle text-primary hover:bg-primary/20">
                                    <x-heroicon-s-x-mark class="w-4 h-4" />
                                </button>
                            </div>
                            
                            {{-- Dropdown Hasil --}}
                            <div x-show="searchOpen && $wire.productSearch.length >= 2 && !$wire.selectedPrincipalProduct" x-transition 
                                 class="absolute z-[70] w-full mt-2 bg-base-100 border border-base-300 shadow-2xl rounded-2xl max-h-60 overflow-y-auto ring-1 ring-base-content/5 py-1">
                                @forelse($principalProducts as $product)
                                    <div wire:click="selectProduct('{{ $product->product_id }}', '{{ addslashes($product->product_name) }}')" 
                                         @click="searchOpen = false"
                                         class="cursor-pointer px-4 py-2.5 hover:bg-base-200 transition-colors flex items-center justify-between border-b border-base-300/50 last:border-none">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-sm text-base-content">{{ $product->product_name }}</span>
                                            <span class="text-[10px] font-mono text-base-content/50">{{ $product->product_id }}</span>
                                        </div>
                                        @if($product->is_active)
                                            <span class="badge badge-success badge-xs opacity-70">AKTIF</span>
                                        @else
                                            <span class="badge badge-error badge-xs opacity-70">TIDAK AKTIF</span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-base-content/40 italic">Produk tidak ditemukan dalam database master.</div>
                                @endforelse
                            </div>
                        </div>
                        @error('selectedPrincipalProduct') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="saveMapping">Simpan Pemetaan</span>
                        <span wire:loading wire:target="saveMapping" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="saveMapping" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
