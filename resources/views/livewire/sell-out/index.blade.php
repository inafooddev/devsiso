<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Sales Invoices Distributor</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
        
        @include('livewire.sell-out._tabs')

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Data Ringkasan Sell Out</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Ringkasan transaksi invoice sales distributor</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <!-- Search Component -->
                    <x-ui.search-input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari Region, Area, Distributor..." 
                    />

                    <!-- Custom Month Picker Component -->
                    <div x-data="{
                            open: false,
                            month: @entangle('monthFilter').live,
                            year: @entangle('yearFilter').live,
                            monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            get monthName() {
                                return this.month ? this.monthNames[this.month - 1] : '';
                            },
                            get displayText() {
                                if (this.month && this.year) return this.monthName + ' ' + this.year;
                                if (this.year) return 'Semua Bulan ' + this.year;
                                if (this.month) return this.monthName + ' Semua Tahun';
                                return 'Semua Waktu';
                            },
                            get safeYear() {
                                return this.year || new Date().getFullYear();
                            },
                            decYear() {
                                this.year = this.safeYear - 1;
                            },
                            incYear() {
                                this.year = this.safeYear + 1;
                            },
                            setThisMonth() {
                                let d = new Date();
                                this.month = d.getMonth() + 1;
                                this.year = d.getFullYear();
                                this.open = false;
                            },
                            clear() {
                                this.month = null;
                                this.year = null;
                                this.open = false;
                            }
                        }" 
                        class="relative"
                    >
                        <!-- Trigger Button -->
                        <button type="button" @click="open = !open" class="btn btn-sm bg-base-100 border-base-300 hover:bg-base-200 text-base-content/80 font-medium rounded-xl gap-2 min-w-[120px] justify-between shadow-sm">
                            <span x-text="displayText" class="flex-1 text-left"></span>
                            <x-heroicon-m-calendar class="w-4 h-4 opacity-50 shrink-0" />
                        </button>

                        <!-- Popover -->
                        <div x-show="open" @click.away="open = false" style="display: none;"
                             class="absolute right-0 mt-2 w-64 bg-base-100 border border-base-300 shadow-xl rounded-2xl z-50 p-4"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95">
                             
                             <!-- Header / Year Selector -->
                             <div class="flex items-center justify-between mb-4 bg-base-200/50 rounded-lg p-1">
                                 <button type="button" @click="decYear()" class="btn btn-ghost btn-xs btn-square">
                                     <x-heroicon-m-chevron-left class="w-4 h-4" />
                                 </button>
                                 <span class="font-bold text-base-content text-sm" x-text="safeYear"></span>
                                 <button type="button" @click="incYear()" class="btn btn-ghost btn-xs btn-square">
                                     <x-heroicon-m-chevron-right class="w-4 h-4" />
                                 </button>
                             </div>

                             <!-- Month Grid -->
                             <div class="grid grid-cols-4 gap-1.5 mb-4">
                                 <template x-for="(name, index) in monthNames">
                                     <button type="button" 
                                             @click="month = index + 1; open = false;"
                                             class="px-1 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                             :class="month === (index + 1) ? 'bg-primary text-primary-content shadow-md shadow-primary/20' : 'hover:bg-base-200 text-base-content/80'"
                                             x-text="name">
                                     </button>
                                 </template>
                             </div>

                             <!-- Footer -->
                             <div class="flex items-center justify-between pt-3 border-t border-base-200">
                                 <button type="button" @click="clear()" class="text-xs font-semibold text-primary hover:text-primary/80">Clear</button>
                                 <button type="button" @click="setThisMonth()" class="text-xs font-semibold text-primary hover:text-primary/80">This month</button>
                             </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-1 md:gap-2">
                        {{-- FILTER --}}
                        <x-ui.action-button
                            type="filter"
                            wire:click="$set('isFilterModalOpen', true)"
                        />

                        {{-- EXPORT --}}
                        @canExport('sales-invoice-report.index')
                        <x-ui.action-button
                            type="export"
                            wire:click="$set('isExportModalOpen', true)"
                        />
                        @endcanExport
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="flex-1 min-h-0 overflow-hidden relative flex flex-col">
                @if (!$hasAppliedFilters)
                    <div class="flex-1 flex items-center justify-center text-center p-4">
                        <div class="p-8 rounded-2xl max-w-sm mx-auto">
                            <div class="h-16 w-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
                                <x-heroicon-o-funnel class="h-8 w-8" />
                            </div>
                            <h3 class="text-base font-semibold text-base-content">Silakan Terapkan Filter</h3>
                            <p class="mt-2 text-sm text-base-content/60 leading-relaxed">Klik tombol "Filter" di atas untuk memilih kriteria dan menampilkan data penjualan.</p>
                        </div>
                    </div>
                @else
                    <div class="flex-1 overflow-auto custom-scrollbar bg-base-100 rounded-b-2xl min-h-0">
                        <table class="table table-sm table-zebra w-full whitespace-nowrap">

                                <thead class="sticky top-0 bg-base-200/90 backdrop-blur-sm z-10 border-b border-base-300">
                                    <tr>
                                        @foreach(['Region','Area','Distributor','Config','Last Update','Row','CTN','Pak','Pcs','Qty','Gross','Disc4','Disc8','DPP','Tax','Nett'] as $col)
                                        <th class="text-left font-semibold text-base-content/60 uppercase tracking-wider whitespace-nowrap {{ in_array($col, ['Row','CTN','Pak','Pcs','Qty','Gross','Disc4','Disc8','DPP','Tax','Nett']) ? 'text-right' : '' }} {{ $col === 'Config' ? 'text-center' : '' }}">{{ $col }}</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-base-300/50">
                                    @forelse ($summaryData as $data)
                                        @php $isZeroNett = ($data->nett_raw ?? 0) == 0; @endphp
                                        <tr class="hover:bg-base-300/40 transition-colors duration-150 {{ $isZeroNett ? 'bg-error/5' : '' }}">
                                            <td class="whitespace-nowrap text-base-content/60">{{ $data->region_name }}</td>
                                            <td class="whitespace-nowrap text-base-content/60">{{ $data->area_name }}</td>
                                            <td class="whitespace-nowrap">
                                                <div class="font-medium text-base-content/90">{{ $data->distributor_name }}</div>
                                                <div class="text-[11px] text-base-content/40 mt-0.5">{{ $data->distributor_code }}</div>
                                            </td>
                                            <td class="whitespace-nowrap text-center">
                                                @if ($data->kodemaping)
                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-success/10 ring-1 ring-success/30 text-success" title="Telah di-mapping">
                                                        <x-heroicon-s-check class="h-3.5 w-3.5" />
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-error/10 ring-1 ring-error/30 text-error" title="Belum di-mapping">
                                                        <x-heroicon-s-x-mark class="h-3.5 w-3.5" />
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap text-base-content/60">{{ $data->last_up ? \Carbon\Carbon::parse($data->last_up)->format('d M Y') : '-' }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->baris ?? 0, 0) }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->ktn ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->pak ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->pcs ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->qty ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->gross_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->discount4_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->discount8_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->dpp_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right text-base-content/70">{{ number_format($data->tax_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="whitespace-nowrap text-right font-bold {{ $isZeroNett ? 'text-error' : 'text-primary' }}">{{ number_format($data->nett_raw ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="16" class="px-6 py-12 text-center">
                                                <x-heroicon-o-inbox class="h-10 w-10 text-base-content/30 mx-auto mb-3" />
                                                <h3 class="text-sm font-semibold text-base-content/70">Tidak Ada Data</h3>
                                                <p class="mt-1 text-xs text-base-content/50">Tidak ada data yang cocok dengan kriteria filter Anda.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if ($summaryData->isNotEmpty() && $grandTotals)
                                    <tfoot class="sticky bottom-0 bg-base-200/90 backdrop-blur-sm border-t border-base-300 z-10">
                                        <tr>
                                            <td colspan="5" class="text-right text-xs font-bold text-base-content/70 uppercase tracking-wider whitespace-nowrap">TOTAL KESELURUHAN</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_baris'] ?? 0, 0) }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_ktn'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_pak'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_pcs'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_quantity'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_gross'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_cashback'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_bonusbarang'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_dpp'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-xs font-bold text-base-content whitespace-nowrap">{{ number_format($grandTotals['total_tax'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right text-sm font-black text-primary whitespace-nowrap">{{ number_format($grandTotals['total_nett'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Filter -->
    <div x-data="{ open: @entangle('isFilterModalOpen') }" x-show="open" x-cloak wire:ignore.self class="fixed z-50 inset-0 flex items-center justify-center">
        <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-200 rounded-2xl shadow-2xl ring-1 ring-base-300 w-full max-w-lg mx-4 overflow-visible">

            <form wire:submit.prevent="applyFilters">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-bold text-base-content">Filter Laporan</h3>
                        <button @click="open = false" type="button" class="btn btn-ghost btn-sm btn-square rounded-xl">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-base-content/70 mb-1.5">Status Distributor</label>
                            <select wire:model.defer="statusFilter" class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50">
                                <option value="">Semua Status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Region Multi-select -->
                        <div x-data="{ openRegion: false }" class="relative">
                            <label class="block text-sm font-medium text-base-content/70 mb-1.5">Region</label>
                            <button type="button" @click="openRegion = !openRegion"
                                class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl text-left font-normal text-base-content flex items-center justify-between">
                                <span>
                                    @if(count($regionFilter) == count($regions)) Semua Region
                                    @elseif(count($regionFilter) > 0) <span class="font-semibold text-primary">{{ count($regionFilter) }}</span> Region terpilih
                                    @else Pilih Region @endif
                                </span>
                            </button>
                            <div x-show="openRegion" @click.away="openRegion = false"
                                class="absolute z-20 mt-1 w-full bg-base-100 shadow-xl rounded-xl border border-base-300 overflow-hidden">
                                <div class="px-3 py-2 flex justify-between border-b border-base-300 bg-base-200">
                                    <button type="button" wire:click="selectAllRegions" class="text-xs font-medium text-primary hover:text-primary/80">Pilih Semua</button>
                                    <button type="button" wire:click="clearRegions" class="text-xs font-medium text-base-content/50 hover:text-base-content">Hapus</button>
                                </div>
                                <div class="max-h-40 overflow-y-auto p-2 space-y-1">
                                    @foreach ($regions as $region)
                                        <label class="flex items-center p-1.5 hover:bg-base-200 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox" wire:model.live="regionFilter" value="{{ $region->region_code }}" class="checkbox checkbox-primary checkbox-xs rounded">
                                            <span class="ml-2.5 text-sm text-base-content/80">{{ $region->region_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Area Multi-select -->
                        <div x-data="{ openArea: false }" class="relative">
                            <label class="block text-sm font-medium text-base-content/70 mb-1.5">Area</label>
                            <button type="button" @click="openArea = !openArea"
                                class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl text-left font-normal text-base-content flex items-center justify-between {{ empty($regionFilter) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                @if(empty($regionFilter)) disabled @endif>
                                <span>
                                    @if(count($areaFilter) == count($areas) && count($areas) > 0) Semua Area
                                    @elseif(count($areaFilter) > 0) <span class="font-semibold text-primary">{{ count($areaFilter) }}</span> Area terpilih
                                    @else Pilih Area @endif
                                </span>
                            </button>
                            <div x-show="openArea" @click.away="openArea = false"
                                class="absolute z-10 mt-1 w-full bg-base-100 shadow-xl rounded-xl border border-base-300 overflow-hidden">
                                <div class="px-3 py-2 flex justify-between border-b border-base-300 bg-base-200">
                                    <button type="button" wire:click="selectAllAreas" class="text-xs font-medium text-primary hover:text-primary/80">Pilih Semua</button>
                                    <button type="button" wire:click="clearAreas" class="text-xs font-medium text-base-content/50 hover:text-base-content">Hapus</button>
                                </div>
                                <div class="max-h-40 overflow-y-auto p-2 space-y-1">
                                    @forelse($areas as $area)
                                        <label class="flex items-center p-1.5 hover:bg-base-200 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox" wire:model.live="areaFilter" value="{{ $area->area_code }}" class="checkbox checkbox-primary checkbox-xs rounded">
                                            <span class="ml-2.5 text-sm text-base-content/80">{{ $area->area_name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-base-content/40 p-2 text-center">Pilih region terlebih dahulu</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="px-6 py-4 border-t border-base-300 flex flex-wrap items-center sm:justify-end gap-3">
                    <x-ui.action-button
                        type="default"
                        label="Batal"
                        @click="open = false"
                        class="btn-ghost sm:mr-auto shrink-0"
                    />

                    <x-ui.action-button
                        type="default"
                        label="Reset"
                        wire:click="resetFilters"
                        class="btn-ghost border border-base-300 hover:bg-base-200 shrink-0"
                    />

                    <x-ui.action-button
                        type="save"
                        label="Terapkan"
                        icon=""
                        class="shrink-0"
                    />
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Export -->
    <div x-data="{ open: @entangle('isExportModalOpen') }" x-show="open" x-cloak wire:ignore.self class="fixed z-50 inset-0 flex items-center justify-center">
        <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-200 rounded-2xl shadow-2xl ring-1 ring-base-300 w-full max-w-lg mx-4">

            <form wire:submit.prevent="export">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-bold text-base-content">Filter Data Ekspor</h3>
                        <button @click="open = false" type="button" class="btn btn-ghost btn-sm btn-square rounded-xl">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="exportRegionFilter" class="block text-sm font-medium text-base-content/70 mb-1.5">Region</label>
                            <select wire:model.live="exportRegionFilter" id="exportRegionFilter" class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-success/50">
                                <option value="">Pilih Region</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="exportAreaFilter" class="block text-sm font-medium text-base-content/70 mb-1.5">Area</label>
                            <select wire:model.live="exportAreaFilter" id="exportAreaFilter"
                                class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-success/50 {{ !$exportRegionFilter ? 'opacity-50 cursor-not-allowed' : '' }}"
                                @if(!$exportRegionFilter) disabled @endif>
                                <option value="">Pilih Area</option>
                                @foreach ($exportAreas as $area)
                                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="exportDistributorFilter" class="block text-sm font-medium text-base-content/70 mb-1.5">Distributor</label>
                            <select wire:model.live="exportDistributorFilter" id="exportDistributorFilter"
                                class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-success/50 {{ !$exportAreaFilter ? 'opacity-50 cursor-not-allowed' : '' }}"
                                @if(!$exportAreaFilter) disabled @endif>
                                <option value="">Pilih Distributor</option>
                                @foreach ($exportDistributors as $distributor)
                                    <option value="{{ $distributor->distributor_code }}">{{ $distributor->distributor_name }} ({{ $distributor->distributor_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="exportMonthFilter" class="block text-sm font-medium text-base-content/70 mb-1.5">Bulan</label>
                                <select wire:model.defer="exportMonthFilter" id="exportMonthFilter" class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-success/50">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="exportYearFilter" class="block text-sm font-medium text-base-content/70 mb-1.5">Tahun</label>
                                <select wire:model.defer="exportYearFilter" id="exportYearFilter" class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-success/50">
                                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-base-300 flex flex-wrap items-center sm:justify-end gap-3">
                    <x-ui.action-button
                        type="default"
                        label="Batal"
                        @click="open = false"
                        class="btn-ghost border border-base-300 hover:bg-base-200 shrink-0"
                    />
                    <x-ui.action-button
                        type="export"
                        label=""
                        icon=""
                        class="shrink-0"
                        wire:loading.attr="disabled"
                        wire:target="export"
                    >
                        <span wire:loading.remove wire:target="export" class="flex items-center gap-2">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            Ekspor Sekarang
                        </span>
                        <span wire:loading wire:target="export" class="flex items-center gap-2">
                            <span class="loading loading-spinner loading-sm"></span>
                            Mengekspor...
                        </span>
                    </x-ui.action-button>
                </div>
            </form>
        </div>
    </div>

</div>
