<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Individual Performance Management</x-slot>

    @push('styles')
    <style>
        .kpi-table th, .kpi-table td {
            min-width: 80px;
            text-align: center;
        }
        
        /* Freeze the first column (Indikator) */
        .kpi-table th.sticky-col, .kpi-table td.sticky-col {
            position: sticky !important;
            left: 0;
            text-align: left;
            min-width: 250px;
            max-width: 250px;
        }
        
        /* Freeze the second column (Bobot) */
        .kpi-table th.bobot-col, .kpi-table td.bobot-col {
            position: sticky !important;
            left: 250px;
            min-width: 80px;
            max-width: 80px;
            text-align: center;
        }

        /* --- Z-INDEX HIERARCHY --- */
        /* 1. Base table data (z-index default / 1) */
        .kpi-table tbody td {
            z-index: 1;
        }
        
        /* 2. Sticky columns in body (z-index 10) */
        .kpi-table tbody td.sticky-col, 
        .kpi-table tbody td.bobot-col {
            z-index: 10 !important;
            background-color: var(--fallback-b1,oklch(var(--b1))) !important;
        }
        
        /* 3. Sticky header normal columns (z-index 20) */
        .kpi-table thead th {
            position: sticky !important;
            top: 0;
            z-index: 20 !important;
            background-color: var(--fallback-n,oklch(var(--n))) !important;
            color: var(--fallback-nc,oklch(var(--nc))) !important;
        }
        
        /* 4. Intersections / Corners in Header (z-index 30) */
        .kpi-table thead th.sticky-col, 
        .kpi-table thead th.bobot-col {
            z-index: 30 !important;
            background-color: var(--fallback-n,oklch(var(--n))) !important;
        }
        
        /* Footer sticky logic */
        .kpi-table tfoot td {
            position: sticky !important;
            bottom: 0;
            z-index: 20 !important;
            background-color: var(--fallback-b2,oklch(var(--b2))) !important;
        }
        .kpi-table tfoot td.sticky-col, .kpi-table tfoot td.bobot-col {
            z-index: 30 !important;
            background-color: var(--fallback-b2,oklch(var(--b2))) !important;
        }
        
    </style>
    @endpush

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full xl:w-auto">
                <h2 class="text-base md:text-lg font-bold">Individual Performance Management</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Dashboard KPI Supervisor</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedYear" class="select select-sm select-bordered rounded-xl bg-base-100 w-[120px]">
                        @for($i = date('Y'); $i >= 2023; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]" @if(!$selectedRegion) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                </div>

                <div class="flex items-center gap-2">
                    <input type="text" wire:model="search" wire:keydown.enter="applyFilter" placeholder="Cari Supervisor..." class="input input-sm input-bordered rounded-xl bg-base-100 w-[160px] xl:w-[200px]" />
                    <x-ui.button class="rounded-xl" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                    <x-ui.button class="rounded-xl" variant="outline" icon="arrows-up-down" size="sm" onclick="document.getElementById('export_import_modal').showModal()">Export/Import Data</x-ui.button>
                </div>
            </div>
        </div>

        {{-- Content / Matrix Data --}}
        <div class="flex-1 min-h-0 overflow-y-auto p-4 bg-base-200/20">
            @php
                $data = $this->reportData;
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                $kpis = [
                    'so' => ['label' => '1. Penjualan Bersih (SO)', 'bobot' => 35],
                    'ipt' => ['label' => '2. Item Per Transaksi (IPT)', 'bobot' => 15],
                    'jks' => ['label' => '3. Pemenuhan kunjungan JKS SE (AC/PC)', 'bobot' => 5],
                    'ec' => ['label' => '4. Pencapaian Efektif Call (EC/AC)', 'bobot' => 10],
                    'standpro' => ['label' => '5. Standpro (SE * 300)', 'bobot' => 10],
                    'ao' => ['label' => '6. Active Outlet (AO/RO)', 'bobot' => 10],
                    'rwo' => ['label' => '7. Reward Outlet Capai Target (RWO)', 'bobot' => 15],
                ];

                function getColorClass($ach) {
                    if ($ach < 80) return 'text-red-600 font-bold';
                    if ($ach < 100) return 'text-yellow-600 font-bold';
                    return 'text-green-600 font-bold';
                }
            @endphp

            @if(empty($data))
                <div class="flex flex-col items-center justify-center h-full text-base-content/50">
                    <x-heroicon-o-document-magnifying-glass class="w-16 h-16 mb-4" />
                    <p class="text-sm font-medium">Belum ada data. Silakan atur filter dan klik Terapkan.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($data as $spvCode => $spvData)
                        <div x-data="{ expanded: false }" class="bg-base-100 border border-base-300 rounded-xl shadow-sm overflow-hidden">
                            {{-- Accordion Header --}}
                            <div @click="expanded = !expanded" class="px-4 py-3 bg-base-200 cursor-pointer flex justify-between items-center hover:bg-base-300 transition-colors">
                                <div class="flex items-center gap-3">
                                    @php
                                        $colorMap = [
                                            ['bg' => 'bg-primary', 'text' => 'text-primary-content'],
                                            ['bg' => 'bg-secondary', 'text' => 'text-secondary-content'],
                                            ['bg' => 'bg-accent', 'text' => 'text-accent-content'],
                                            ['bg' => 'bg-info', 'text' => 'text-info-content'],
                                            ['bg' => 'bg-success', 'text' => 'text-success-content'],
                                            ['bg' => 'bg-warning', 'text' => 'text-warning-content'],
                                            ['bg' => 'bg-error', 'text' => 'text-error-content'],
                                            ['bg' => 'bg-neutral', 'text' => 'text-neutral-content'],
                                        ];
                                        $colorIndex = abs(crc32($spvData['region'])) % count($colorMap);
                                        $themeColor = $colorMap[$colorIndex];
                                    @endphp
                                    <div class="avatar placeholder">
                                        <div class="{{ $themeColor['bg'] }} {{ $themeColor['text'] }} rounded-full w-10">
                                            <span class="text-sm uppercase">{{ substr($spvData['name'], 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm uppercase">{{ $spvData['name'] }}</h3>
                                        <p class="text-xs text-base-content/70">
                                            {{ $spvCode }} 
                                            @if(!empty($spvData['branches_string']))
                                                &bull; <span class="font-medium">Cabang: {{ $spvData['branches_string'] }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('report.fundamental-sales.export', ['supervisorCode' => $spvCode, 'year' => $appliedYear]) }}" target="_blank" class="btn btn-sm btn-ghost btn-circle hover:bg-success/20 text-base-content/70 hover:text-success transition-colors" title="Export Excel" @click.stop>
                                        <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                                    </a>
                                    <x-heroicon-o-chevron-down class="w-5 h-5 transition-transform duration-300 text-base-content/50" x-bind:class="expanded ? 'rotate-180' : ''" />
                                </div>
                            </div>
                            
                            {{-- Accordion Body (Matrix Table) --}}
                            <div x-show="expanded" x-collapse x-cloak>
                                @include('livewire.report.fundamental-sales.matrix-table', ['nodeData' => $spvData])
                                
                                <div class="mt-4 space-y-2 pl-4 border-l-2 border-dashed border-base-200">
                                    @foreach($spvData['children'] as $cabangName => $cabangData)
                                        <div x-data="{ openCabang: false }" class="bg-white border border-gray-100 rounded-md overflow-hidden">
                                            <div @click="openCabang = !openCabang" class="px-3 py-1 cursor-pointer flex justify-between items-center hover:bg-gray-50 transition-colors">
                                                <div class="flex items-center gap-2">
                                                    <div class="badge badge-outline badge-sm">C</div>
                                                    <span class="text-sm font-medium italic">Cabang: {{ $cabangName }}</span>
                                                </div>
                                                <x-heroicon-o-chevron-down class="w-3 h-3 transition-transform duration-300 text-gray-400" x-bind:class="openCabang ? 'rotate-180' : ''" />
                                            </div>
                                            
                                            <div x-show="openCabang" x-collapse x-cloak class="p-2 border-t border-gray-100 bg-gray-50/30">
                                                @include('livewire.report.fundamental-sales.matrix-table', ['nodeData' => $cabangData])
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Export / Import Modal --}}
    <dialog id="export_import_modal" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
        <div class="modal-box bg-base-100">
            <h3 class="font-bold text-lg mb-4 text-base-content"><i class="fa-solid fa-arrows-up-down mr-2"></i> Export / Import Data Mentah</h3>
            
            <div class="space-y-6">
                <!-- Export Section -->
                <div class="p-4 border border-base-300 rounded-xl bg-base-200/50">
                    <h4 class="font-semibold text-sm mb-3">1. Export Data (Download)</h4>
                    <p class="text-xs text-base-content/70 mb-3">Pilih bulan data yang ingin di-download untuk diisi/diubah secara manual di Excel.</p>
                    <div class="flex items-start gap-2">
                        <div class="flex-1">
                            <input type="month" wire:model="exportImportMonth" class="input input-sm input-bordered w-full" />
                            @error('exportImportMonth') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </div>
                        <x-ui.button variant="primary" size="sm" icon="cloud-arrow-down" wire:click="exportRawData" spinner="exportRawData">
                            Download Excel
                        </x-ui.button>
                    </div>
                </div>

                <div class="divider text-xs text-base-content/50">KEMUDIAN</div>

                <!-- Import Section -->
                <div class="p-4 border border-base-300 rounded-xl bg-base-200/50">
                    <h4 class="font-semibold text-sm mb-3">2. Import Data (Upload)</h4>
                    <p class="text-xs text-base-content/70 mb-3">Unggah kembali file Excel yang telah diisi. Data yang sudah ada di bulan & cabang tersebut akan tertimpa otomatis.</p>
                    
                    <div class="flex items-start gap-2">
                        <div class="flex-1">
                            <input type="file" wire:model="importFile" class="file-input file-input-sm file-input-bordered w-full" accept=".xlsx,.xls" />
                            @error('importFile') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </div>
                        <x-ui.button variant="success" size="sm" icon="cloud-arrow-up" wire:click="importRawData" spinner="importRawData">
                            Upload Excel
                        </x-ui.button>
                    </div>
                </div>
            </div>

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-sm btn-ghost">Tutup</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</div>
