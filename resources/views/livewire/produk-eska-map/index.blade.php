<div>
    <x-slot name="title">Mapping Produk Eska</x-slot>

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

        <x-card flush title="Mapping Produk Eska" icon="arrows-right-left" subtitle="Informasi pemetaan kode produk distributor ke kode produk sistem principal" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    @if ($isFiltered)
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.500ms="search" type="text"
                               placeholder="Cari produk dist/prc..."
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
                    <button wire:click="openExportModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export Excel
                    </button>
                    @endif
                </div>
            </x-slot:actions>

            {{-- State: Filter Belum Diterapkan --}}
            @if (!$isFiltered)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-arrows-right-left class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Data Belum Ditampilkan</h3>
                    <p class="text-sm text-center max-w-xs">Silakan tentukan wilayah distributor untuk melihat data pemetaan produk yang tersedia.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter Pemetaan
                    </button>
                </div>
            @else
                {{-- Tabel --}}
                <x-ui.table empty="Tidak ada data pemetaan produk yang ditemukan untuk kriteria filter ini.">
                    <x-slot:head>
                        <tr>
                            <th class="w-24">Kode Dist</th>
                            <th class="w-40 bg-base-200/30">Produk Distributor</th>
                            <th class="w-10 text-center"><x-heroicon-s-chevron-double-right class="w-4 h-4 mx-auto text-base-content/20" /></th>
                            <th class="w-40 bg-primary/5">Produk Principal (ESKA)</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($products as $row)
                        <tr class="group text-sm">
                            <td><span class="font-mono text-[11px] text-base-content/40 uppercase tracking-widest">{{ $row->eskalink_code_dist }}</span></td>
                            <td class="bg-base-200/10">
                                <div class="flex flex-col gap-1.5">
                                    <div class="badge badge-md bg-base-300 border-base-content/10 text-base-content/70 font-mono  rounded-lg px-2.5">
                                        {{ $row->product_code_dist }}
                                    </div>
                                    <span class="font-semibold text-base-content/80 text-sm tracking-tight leading-tight">{{ $row->product_name_dist }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <x-heroicon-s-link class="w-4 h-4 mx-auto text-base-content/10 group-hover:text-primary transition-colors" />
                            </td>
                            <td class="bg-primary/5">
                                <div class="flex flex-col gap-1.5">
                                    <div class="badge badge-md bg-primary/10 border-primary/20 text-primary font-mono rounded-lg px-2.5">
                                        {{ $row->product_code_prc }}
                                    </div>
                                    <span class="font-semibold text-primary text-sm tracking-tight leading-tight">{{ $row->product_name }}</span>
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Mapping Produk</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Tentukan wilayah distributor</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters">
                <div class="p-6 space-y-5">
                    {{-- Region --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                        <select wire:model.live="regionFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <option value="">Pilih Region</option>
                            @foreach ($regionsOption as $r)
                                <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                            @endforeach
                        </select>
                        @error('regionFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Area --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1 {{ empty($regionFilter) ? 'opacity-30' : '' }}">Area</label>
                        <select wire:model.live="areaFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300"
                                @if (empty($regionFilter)) disabled @endif>
                            <option value="">Pilih Area</option>
                            @foreach ($areasOption as $a)
                                <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                            @endforeach
                        </select>
                        @error('areaFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Distributor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1 {{ empty($areaFilter) ? 'opacity-30' : '' }}">Distributor</label>
                        <select wire:model.live="distributorFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300"
                                @if (empty($areaFilter)) disabled @endif>
                            <option value="">Pilih Distributor</option>
                            @foreach ($distributorsOption as $d)
                                <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                            @endforeach
                        </select>
                        @error('distributorFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
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
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Tampilkan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL EXPORT ========== --}}
    <div x-data="{ 
            open: @entangle('isExportModalOpen'),
            search: '' 
         }"
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
                    <div class="p-2.5 rounded-2xl bg-success/10 text-success">
                        <x-heroicon-s-arrow-down-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Export Mapping Produk</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Pilih produk yang ingin diexport</p>
                    </div>
                </div>
                <button @click="open = false; search = ''" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="export">
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">Daftar Produk (Multi-Select)</label>
                            
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                                    <x-heroicon-s-magnifying-glass class="w-3.5 h-3.5" />
                                </div>
                                <input x-model="search" type="text"
                                       placeholder="Cari kode/nama di list ini..."
                                       class="input input-xs input-bordered pl-8 w-full sm:w-56 rounded-lg bg-base-200 border-base-300 focus:ring-1 focus:ring-primary/50 transition-all">
                            </div>

                            <button type="button" wire:click="selectAllProducts" class="text-[10px] font-bold text-primary hover:underline italic">Pilih Semua Produk</button>
                        </div>
                        
                        <div class="bg-base-200 rounded-2xl border border-base-300 h-80 overflow-y-auto p-3 scrollbar-thin scrollbar-thumb-base-300">
                            <div class="space-y-1">
                                @forelse($productOptions as $p)
                                    <label x-show="search === '' || 
                                                 '{{ strtolower($p->product_code_dist) }}'.includes(search.toLowerCase()) || 
                                                 '{{ strtolower($p->product_name_dist) }}'.includes(search.toLowerCase())"
                                           class="flex items-center gap-4 p-2.5 hover:bg-base-300 rounded-xl cursor-pointer transition-colors group">
                                        <div class="flex-none">
                                            <input type="checkbox" wire:model="selectedProducts" value="{{ $p->product_code_dist }}" 
                                                   class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-all duration-300 group-hover:scale-110">
                                        </div>
                                        <div class="flex-none w-32">
                                            <span class="badge badge-sm badge-outline border-base-content/20 font-mono text-[10px] text-base-content/60 group-hover:border-primary/50 group-hover:text-primary transition-colors">
                                                {{ $p->product_code_dist }}
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-xs font-bold text-base-content/80 truncate group-hover:text-primary transition-colors">
                                                {{ $p->product_name_dist }}
                                            </span>
                                        </div>
                                    </label>
                                @empty
                                    <div class="h-full flex flex-col items-center justify-center text-base-content/30 py-20 italic">
                                        <x-heroicon-o-cube class="w-10 h-10 mb-2 opacity-20" />
                                        <p class="text-xs">Tidak ada data produk yang tersedia.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <p class="text-[10px] text-base-content/40 italic px-2">* Kosongkan pilihan untuk mengexport semua produk yang tersedia.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false; search = ''" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-success rounded-xl px-8 normal-case text-white shadow-sm shadow-success/20 gap-2">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" /> Unduh Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>