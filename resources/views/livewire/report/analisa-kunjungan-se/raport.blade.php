<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('se_raport_show_kpi') !== null ? localStorage.getItem('se_raport_show_kpi') === 'true' : true,
         toggleKpi() {
             this.showKpi = !this.showKpi;
             localStorage.setItem('se_raport_show_kpi', this.showKpi);
         },
         showAuditModal: false,
         printRaport() {
             window.print();
         }
     }">

    <x-slot name="title">Raport Kunjungan SE</x-slot>

    {{-- Standar Tab Menu (7 Tab Terintegrasi) --}}
    @php
        $shared = session('analisa_kunjungan_se_shared_filter', []);
        $tabParams = array_filter([
            'region' => $appliedRegion ?: $selectedRegion,
            'area' => $appliedArea ?: $selectedArea,
            'distributor' => $appliedDistributor ?: $selectedDistributor,
            'salesman' => $appliedSalesman ?: $selectedSalesman,
            'startDate' => $shared['startDate'] ?? (isset($appliedYear, $appliedMonth) ? sprintf('%04d-%02d-01', $appliedYear, $appliedMonth) : null),
            'endDate' => $shared['endDate'] ?? (isset($appliedYear, $appliedMonth) ? \Carbon\Carbon::create($appliedYear, $appliedMonth, 1)->endOfMonth()->format('Y-m-d') : null),
        ], fn($v) => !empty($v));
    @endphp
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.index', $tabParams)" :navigate="true">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.summary', $tabParams)" :navigate="true">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.reason', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-list class="w-4 h-4 inline-block mr-1" /> Summary Reason
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.trend', $tabParams)" :navigate="true">
            <x-heroicon-o-arrow-trending-up class="w-4 h-4 inline-block mr-1" /> Trend Bulanan
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.findings', $tabParams)" :navigate="true">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block mr-1 text-warning" /> Temuan
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.tracking', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-check class="w-4 h-4 inline-block mr-1 text-primary" /> Tracking Toko
        </x-ui.tab-item>
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.raport', $tabParams)" :navigate="true">
            <x-heroicon-o-academic-cap class="w-4 h-4 inline-block mr-1 text-emerald-500" /> Raport SE
        </x-ui.tab-item>

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                @if($appliedRegion && $appliedArea && $metrics['total_se'] > 0)
                    <span class="badge badge-success text-white font-bold text-xs gap-1 py-2.5 px-3 shadow-2xs">
                        <x-heroicon-s-trophy class="w-4 h-4" />
                        Rata-rata: {{ $metrics['avg_score'] }} / 100
                    </span>
                @endif

                <button type="button" 
                    @click="$dispatch('open-audit-modal', {tab: 'formula'}); showAuditModal = true" 
                    class="btn btn-xs sm:btn-sm btn-ghost border border-base-300 rounded-xl gap-1.5 text-base-content/75 hover:text-emerald-600 hover:border-emerald-500 font-semibold shadow-2xs cursor-pointer select-none"
                    title="Pelajari Mekanisme & Rumus Lengkap Penilaian Raport SE (4 Pilar & Cara Hitung Manual)">
                    <x-heroicon-m-calculator class="w-4 h-4 text-emerald-600" />
                    <span class="text-xs hidden md:inline">Rumus & Mekanisme Nilai</span>
                </button>

                <button type="button" 
                    @click="toggleKpi()" 
                    class="btn btn-xs sm:btn-sm rounded-xl gap-1.5 transition-all font-semibold border shadow-2xs cursor-pointer select-none"
                    :class="showKpi ? 'btn-ghost text-base-content/70 hover:text-base-content border-base-300' : 'btn-primary text-white shadow-sm border-primary'"
                    :title="showKpi ? 'Sembunyikan KPI Card agar ruang tabel lebih lega' : 'Tampilkan kembali KPI Card'">
                    <template x-if="showKpi">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-m-eye-slash class="w-4 h-4 text-base-content/70" />
                            <span class="text-xs hidden sm:inline">Sembunyikan KPI</span>
                        </span>
                    </template>
                    <template x-if="!showKpi">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-m-chart-bar class="w-4 h-4 text-white" />
                            <span class="text-xs">Tampilkan KPI</span>
                        </span>
                    </template>
                </button>
            </div>
        </x-slot>
    </x-ui.tab-menu>

    {{-- 4 Executive Summary KPI Cards --}}
    <div x-show="showKpi"
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 -translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 -translate-y-2" 
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 shrink-0 mb-3 md:mb-4">
        
        {{-- Card 1: Rata-rata Skor Tim --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-emerald-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Rata-rata Skor Tim Area</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                    <x-heroicon-s-chart-bar class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-base-content">
                    {{ $metrics['avg_score'] > 0 ? $metrics['avg_score'] : '-' }}
                    <span class="text-xs font-normal text-base-content/50">/ 100</span>
                </span>
                <span class="text-[11px] font-semibold text-base-content/60">
                    {{ $metrics['total_se'] }} Sales Executive
                </span>
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Kinerja gabungan 4 pilar operasional SE di area terpilih
            </div>
        </div>

        {{-- Card 2: Distribusi Grade Tim --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-blue-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Distribusi Grade Tim</span>
                <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-600 shrink-0">
                    <x-heroicon-s-academic-cap class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="badge badge-sm bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-bold border-none" title="Grade A (Sangat Baik: >= 85)">
                    A: {{ $metrics['grade_a'] }}
                </span>
                <span class="badge badge-sm bg-blue-500/15 text-blue-700 dark:text-blue-400 font-bold border-none" title="Grade B (Baik: 70 - 84.9)">
                    B: {{ $metrics['grade_b'] }}
                </span>
                <span class="badge badge-sm bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-none" title="Grade C (Cukup: 55 - 69.9)">
                    C: {{ $metrics['grade_c'] }}
                </span>
                <span class="badge badge-sm bg-rose-500/15 text-rose-700 dark:text-rose-400 font-bold border-none" title="Grade D (Kurang: < 55)">
                    D: {{ $metrics['grade_d'] }}
                </span>
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Standar: A (&ge;85) &bull; B (70-84) &bull; C (55-69) &bull; D (&lt;55)
            </div>
        </div>

        {{-- Card 3: Top Performer SE --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-amber-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-amber-600 uppercase">
                <span>🏆 Top Performer SE</span>
                <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                    <x-heroicon-s-trophy class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-1">
                @if($metrics['top_performer'])
                    <div class="font-bold text-sm text-base-content truncate" title="{{ $metrics['top_performer']->salesman_name }}">
                        {{ $metrics['top_performer']->salesman_name }}
                    </div>
                    <div class="flex items-center justify-between text-[11px] mt-0.5">
                        <span class="text-base-content/60 font-mono text-[10px]">{{ $metrics['top_performer']->salesman_code }}</span>
                        <span class="badge badge-sm bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-extrabold border-none">
                            Skor: {{ $metrics['top_performer']->skor_total }} ({{ $metrics['top_performer']->grade }})
                        </span>
                    </div>
                @else
                    <span class="text-sm text-base-content/50 italic">-</span>
                @endif
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Skor tertinggi dari 4 pilar komprehensif
            </div>
        </div>

        {{-- Card 4: SE Perlu Pembinaan --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-rose-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-rose-600 uppercase">
                <span>⚠️ Butuh Pembinaan SPV</span>
                <div class="w-7 h-7 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-600 shrink-0">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-1">
                @if($metrics['needs_coaching'] && $metrics['needs_coaching']->skor_total < 70)
                    <div class="font-bold text-sm text-base-content truncate" title="{{ $metrics['needs_coaching']->salesman_name }}">
                        {{ $metrics['needs_coaching']->salesman_name }}
                    </div>
                    <div class="flex items-center justify-between text-[11px] mt-0.5">
                        <span class="text-base-content/60 font-mono text-[10px]">{{ $metrics['needs_coaching']->salesman_code }}</span>
                        <span class="badge badge-sm bg-rose-500/20 text-rose-700 dark:text-rose-400 font-extrabold border-none">
                            Skor: {{ $metrics['needs_coaching']->skor_total }} ({{ $metrics['needs_coaching']->grade }})
                        </span>
                    </div>
                @else
                    <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1 mt-1">
                        <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Seluruh SE memenuhi standar
                    </span>
                @endif
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Prioritas coaching / audit lapangan bersama SPV
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base md:text-lg font-bold flex items-center gap-1.5">
                        <x-heroicon-s-academic-cap class="w-5 h-5 text-emerald-600" />
                        <span>Scorecard & Raport Kunjungan SE Bulanan</span>
                    </h2>

                    @if($appliedYear && $appliedMonth)
                        <span class="badge badge-neutral text-neutral-content font-mono text-xs font-semibold">
                            📅 {{ $monthOptions[$appliedMonth] ?? $appliedMonth }} {{ $appliedYear }}
                        </span>
                    @endif

                    @if($appliedRegion && $appliedArea)
                        <span class="badge badge-outline text-xs font-medium text-base-content/70">
                            📍 {{ $appliedArea }}
                        </span>
                        @if(!empty($appliedDistributor))
                            @php
                                $dName = collect($distributors)->firstWhere('distributor_code', $appliedDistributor)?->distributor_name ?? $appliedDistributor;
                            @endphp
                            <span class="badge badge-outline text-xs font-medium text-base-content/70" title="{{ $dName }}">
                                🏢 {{ \Illuminate\Support\Str::limit($dName, 22) }}
                            </span>
                        @else
                            <span class="badge badge-outline text-xs font-medium text-primary">
                                🏢 Semua Distributor
                            </span>
                        @endif
                    @endif
                </div>
                <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">
                    Evaluasi Komprehensif 4 Pilar: Produktivitas (30%) &bull; Efektivitas (30%) &bull; Kepatuhan SOP (25%) &bull; Waktu Kerja (15%)
                </p>
            </div>
            
            {{-- Filter Bar --}}
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 w-full lg:w-auto">
                
                {{-- Filter Region --}}
                @if(count($regions) > 1 || $accessLevel === 'nasional')
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[125px] text-xs">
                        <option value="">Pilih Region</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Area --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[125px] text-xs" @disabled(empty($selectedRegion))>
                        <option value="">Pilih Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Distributor --}}
                <select wire:model.live="selectedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[150px] text-xs" @disabled(empty($selectedArea))>
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $d)
                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Salesman --}}
                <select wire:model.live="selectedSalesman" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[140px] text-xs" @disabled(empty($selectedArea))>
                    <option value="">Semua Salesman</option>
                    @foreach($salesmen as $s)
                        <option value="{{ $s->salesman_code }}">{{ $s->salesman_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Periode (Bulan & Tahun) --}}
                <div class="flex items-center gap-1 bg-base-100 px-1.5 py-0.5 rounded-xl border border-base-300 shadow-2xs w-full sm:w-auto">
                    <select wire:model.live="selectedMonth" class="select select-xs bg-transparent border-none text-xs focus:outline-none min-w-[95px] font-medium" title="Pilih Bulan Evaluasi">
                        @foreach($monthOptions as $mVal => $mLabel)
                            <option value="{{ $mVal }}">{{ $mLabel }}</option>
                        @endforeach
                    </select>

                    <span class="text-xs font-semibold text-base-content/30">/</span>

                    <select wire:model.live="selectedYear" class="select select-xs bg-transparent border-none text-xs font-mono focus:outline-none min-w-[70px] font-medium" title="Pilih Tahun Evaluasi">
                        @foreach($yearOptions as $yVal)
                            <option value="{{ $yVal }}">{{ $yVal }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons: Terapkan, Reset, Excel --}}
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" 
                        wire:click="applyFilter" 
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-primary rounded-xl gap-1 text-xs font-semibold shadow-xs">
                        <span wire:loading.remove wire:target="applyFilter" class="flex items-center gap-1">
                            <x-heroicon-m-funnel class="w-4 h-4" />
                            <span>Terapkan</span>
                        </span>
                        <span wire:loading wire:target="applyFilter" class="loading loading-spinner loading-xs"></span>
                    </button>

                    <button type="button" 
                        wire:click="resetFilter" 
                        class="btn btn-sm btn-ghost border border-base-300 rounded-xl text-base-content/70 hover:text-base-content text-xs"
                        title="Reset semua filter">
                        <x-heroicon-m-arrow-path class="w-4 h-4" />
                    </button>

                    @if($canExport)
                        <button type="button" 
                            wire:click="exportExcel"
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1 text-xs font-semibold"
                            title="Export Rapor Kunjungan SE ke Excel">
                            <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span class="hidden sm:inline">Excel</span>
                            </span>
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sub-Toolbar: Grade Quick Filter Pills, Search Bar & Pagination Selector --}}
        <div class="px-3 md:px-4 py-2 border-b border-base-300/70 bg-base-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 text-xs">
            {{-- Quick Pills Grade --}}
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 scrollbar-none">
                <span class="text-base-content/50 font-semibold uppercase text-[10px] mr-1">Grade:</span>
                
                <button type="button" 
                    wire:click="setGradeFilter('all')" 
                    class="btn btn-xs rounded-lg font-semibold cursor-pointer select-none transition-all {{ $gradeFilter === 'all' ? 'btn-neutral text-white shadow-xs' : 'btn-ghost text-base-content/70 border border-base-300' }}">
                    Semua ({{ $metrics['total_se'] }})
                </button>

                <button type="button" 
                    wire:click="setGradeFilter('A')" 
                    class="btn btn-xs rounded-lg font-semibold cursor-pointer select-none transition-all {{ $gradeFilter === 'A' ? 'bg-emerald-600 text-white shadow-xs' : 'btn-ghost text-emerald-700 dark:text-emerald-400 border border-emerald-500/30' }}">
                    🏆 Grade A ({{ $metrics['grade_a'] }})
                </button>

                <button type="button" 
                    wire:click="setGradeFilter('B')" 
                    class="btn btn-xs rounded-lg font-semibold cursor-pointer select-none transition-all {{ $gradeFilter === 'B' ? 'bg-blue-600 text-white shadow-xs' : 'btn-ghost text-blue-700 dark:text-blue-400 border border-blue-500/30' }}">
                    🟢 Grade B ({{ $metrics['grade_b'] }})
                </button>

                <button type="button" 
                    wire:click="setGradeFilter('C')" 
                    class="btn btn-xs rounded-lg font-semibold cursor-pointer select-none transition-all {{ $gradeFilter === 'C' ? 'bg-amber-600 text-white shadow-xs' : 'btn-ghost text-amber-700 dark:text-amber-400 border border-amber-500/30' }}">
                    🟡 Grade C ({{ $metrics['grade_c'] }})
                </button>

                <button type="button" 
                    wire:click="setGradeFilter('D')" 
                    class="btn btn-xs rounded-lg font-semibold cursor-pointer select-none transition-all {{ $gradeFilter === 'D' ? 'bg-rose-600 text-white shadow-xs' : 'btn-ghost text-rose-700 dark:text-rose-400 border border-rose-500/30' }}">
                    🔴 Grade D ({{ $metrics['grade_d'] }})
                </button>
            </div>

            {{-- Search & PerPage --}}
            <div class="flex items-center gap-2 w-full md:w-auto justify-between md:justify-end">
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari SE / Distributor..." 
                        class="input input-xs input-bordered rounded-lg w-full pl-7 pr-7 text-xs bg-base-100" />
                    <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 absolute left-2 top-1/2 -translate-y-1/2 text-base-content/40" />
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content">
                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-1 text-[11px] text-base-content/60 shrink-0">
                    <span>Tampil:</span>
                    <select wire:model.live="perPage" class="select select-xs select-bordered rounded-lg bg-base-100 font-medium">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="flex-1 min-h-0 min-w-0 overflow-auto relative">
            
            {{-- Loading Overlay --}}
            <div wire:loading wire:target="applyFilter, resetFilter, setGradeFilter, search, sortBy, perPage" 
                 class="absolute inset-0 bg-base-100/60 backdrop-blur-xs z-20 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 bg-base-100 p-4 rounded-xl shadow-lg border border-base-300">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/70">Memuat Scorecard Raport SE...</span>
                </div>
            </div>

            @if(empty($appliedRegion) || empty($appliedArea))
                <div class="h-full flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                        <x-heroicon-o-academic-cap class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-base-content mb-1">Pilih Region & Area Terlebih Dahulu</h3>
                    <p class="text-xs text-base-content/60 max-w-md">
                        Silakan tentukan Region, Area, dan Periode Bulan pada filter di atas lalu klik tombol <strong>Terapkan</strong> untuk mengkalkulasi Scorecard Raport SE.
                    </p>
                </div>
            @elseif($raportList->isEmpty())
                <div class="h-full flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-base-200 text-base-content/40 flex items-center justify-center mb-4">
                        <x-heroicon-o-folder-open class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-base-content mb-1">Tidak Ada Data Raport Kunjungan</h3>
                    <p class="text-xs text-base-content/60 max-w-md">
                        Tidak ditemukan data kunjungan SE pada filter yang dipilih. Coba ganti filter Grade atau kata kunci pencarian.
                    </p>
                </div>
            @else
                <table class="table table-xs table-pin-rows table-pin-cols w-full text-xs">
                    <thead>
                        <tr class="bg-base-200/90 text-base-content/80 text-[11px] uppercase tracking-wider font-bold">
                            <th class="w-12 text-center">Rank</th>
                            <th class="min-w-[190px]">
                                <button type="button" wire:click="sortBy('salesman_name')" class="flex items-center gap-1 hover:text-primary">
                                    <span>Sales Executive</span>
                                    @if($sortField === 'salesman_name')
                                        <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="min-w-[150px]">Distributor</th>
                            
                            {{-- Pilar 1 --}}
                            <th class="text-center min-w-[110px] bg-emerald-500/5">
                                <button type="button" wire:click="sortBy('score_pilar_1')" class="hover:text-primary inline-flex flex-col items-center">
                                    <span>Pilar 1 (30%)</span>
                                    <span class="text-[9px] font-normal lowercase">Produktivitas</span>
                                </button>
                            </th>

                            {{-- Pilar 2 --}}
                            <th class="text-center min-w-[110px] bg-blue-500/5">
                                <button type="button" wire:click="sortBy('score_pilar_2')" class="hover:text-primary inline-flex flex-col items-center">
                                    <span>Pilar 2 (30%)</span>
                                    <span class="text-[9px] font-normal lowercase">Penjualan</span>
                                </button>
                            </th>

                            {{-- Pilar 3 --}}
                            <th class="text-center min-w-[110px] bg-amber-500/5">
                                <button type="button" wire:click="sortBy('score_pilar_3')" class="hover:text-primary inline-flex flex-col items-center">
                                    <span>Pilar 3 (25%)</span>
                                    <span class="text-[9px] font-normal lowercase">Kepatuhan SOP</span>
                                </button>
                            </th>

                            {{-- Pilar 4 --}}
                            <th class="text-center min-w-[110px] bg-purple-500/5">
                                <button type="button" wire:click="sortBy('score_pilar_4')" class="hover:text-primary inline-flex flex-col items-center">
                                    <span>Pilar 4 (15%)</span>
                                    <span class="text-[9px] font-normal lowercase">Waktu Kerja</span>
                                </button>
                            </th>

                            {{-- Skor Total & Grade --}}
                            <th class="text-center min-w-[120px]">
                                <button type="button" wire:click="sortBy('skor_total')" class="hover:text-primary inline-flex items-center gap-1">
                                    <span>Skor Total</span>
                                    @if($sortField === 'skor_total')
                                        <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="text-center w-20">Grade</th>
                            <th class="text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @foreach($raportList as $row)
                            <tr class="hover:bg-base-200/50 transition-colors group">
                                {{-- Rank --}}
                                <td class="text-center font-bold font-mono">
                                    @if($row->rank === 1)
                                        <span class="badge badge-warning text-warning-content font-extrabold text-xs shadow-2xs">🥇 #1</span>
                                    @elseif($row->rank === 2)
                                        <span class="badge badge-neutral text-white font-extrabold text-xs shadow-2xs">🥈 #2</span>
                                    @elseif($row->rank === 3)
                                        <span class="badge badge-ghost font-extrabold text-xs border border-base-300">🥉 #3</span>
                                    @else
                                        <span class="text-base-content/50">#{{ $row->rank }}</span>
                                    @endif
                                </td>

                                {{-- Sales Executive Info --}}
                                <td>
                                    <div class="font-bold text-base-content group-hover:text-primary transition-colors">
                                        {{ $row->salesman_name }}
                                    </div>
                                    <div class="text-[10px] font-mono text-base-content/50 flex items-center gap-1.5 mt-0.5">
                                        <span>{{ $row->salesman_code }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $row->hari_aktif }}/{{ $row->target_hari_kerja }} Hari Aktif</span>
                                    </div>
                                </td>

                                {{-- Distributor --}}
                                <td>
                                    <div class="font-medium text-base-content truncate max-w-[160px]" title="{{ $row->distributor_name }}">
                                        {{ $row->distributor_name }}
                                    </div>
                                    <div class="text-[10px] font-mono text-base-content/40">
                                        {{ $row->distributor_code }}
                                    </div>
                                </td>

                                {{-- Pilar 1: Produktivitas Kunjungan (30%) --}}
                                <td class="text-center bg-emerald-500/5">
                                    <div class="font-black font-mono text-sm {{ $row->score_pilar_1 >= 85 ? 'text-emerald-600' : ($row->score_pilar_1 >= 70 ? 'text-blue-600' : 'text-amber-600') }}">
                                        {{ $row->score_pilar_1 }}
                                    </div>
                                    <div class="text-[10px] text-base-content/60 font-medium">
                                        {{ $row->pct_ac_pc }}% ({{ $row->total_ac }}/{{ $row->total_pc }})
                                    </div>
                                </td>

                                {{-- Pilar 2: Efektivitas Penjualan (30%) --}}
                                <td class="text-center bg-blue-500/5">
                                    <div class="font-black font-mono text-sm {{ $row->score_pilar_2 >= 85 ? 'text-emerald-600' : ($row->score_pilar_2 >= 70 ? 'text-blue-600' : 'text-amber-600') }}">
                                        {{ $row->score_pilar_2 }}
                                    </div>
                                    <div class="text-[10px] text-base-content/60 font-medium">
                                        SR: {{ $row->strike_rate }}% &bull; Rp {{ number_format($row->total_value / 1000000, 1) }}M
                                    </div>
                                </td>

                                {{-- Pilar 3: Kepatuhan SOP & Integritas (25%) --}}
                                <td class="text-center bg-amber-500/5">
                                    <div class="font-black font-mono text-sm {{ $row->score_pilar_3 >= 70 ? 'text-emerald-600' : ($row->score_pilar_3 >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ $row->score_pilar_3 }}
                                    </div>
                                    <div class="text-[10px] text-base-content/60 font-medium flex items-center justify-center gap-1">
                                        @if($row->total_anomalies === 0)
                                            <span class="text-emerald-600 font-semibold">Clean (100%)</span>
                                        @else
                                            <span class="text-rose-600 font-semibold">{{ $row->total_anomalies }} Anomali</span>
                                            <span class="text-[9px] text-base-content/40">({{ $row->clean_rate }}%)</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Pilar 4: Waktu Kerja (15%) --}}
                                <td class="text-center bg-purple-500/5">
                                    <div class="font-black font-mono text-sm {{ $row->score_pilar_4 >= 80 ? 'text-emerald-600' : ($row->score_pilar_4 >= 65 ? 'text-blue-600' : 'text-amber-600') }}">
                                        {{ $row->score_pilar_4 }}
                                    </div>
                                    <div class="text-[10px] text-base-content/60 font-medium">
                                        {{ $row->pct_store_time }}% Toko &bull; {{ $row->avg_minutes_per_store }}m
                                    </div>
                                </td>

                                {{-- Skor Total --}}
                                <td class="text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-base font-black font-mono {{ $row->skor_total >= 85 ? 'text-emerald-600' : ($row->skor_total >= 70 ? 'text-blue-600' : ($row->skor_total >= 55 ? 'text-amber-600' : 'text-rose-600')) }}">
                                            {{ $row->skor_total }}
                                        </span>
                                        <div class="w-16 bg-base-300 rounded-full h-1.5 mt-1 overflow-hidden">
                                            <div class="h-full rounded-full {{ $row->skor_total >= 85 ? 'bg-emerald-500' : ($row->skor_total >= 70 ? 'bg-blue-500' : ($row->skor_total >= 55 ? 'bg-amber-500' : 'bg-rose-500')) }}" 
                                                 style="width: {{ min(100, $row->skor_total) }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Grade Badge --}}
                                <td class="text-center">
                                    @if($row->grade === 'A')
                                        <span class="badge badge-md bg-emerald-500 text-white font-extrabold shadow-xs" title="Grade A: Sangat Baik (Role Model)">
                                            🏆 A
                                        </span>
                                    @elseif($row->grade === 'B')
                                        <span class="badge badge-md bg-blue-500 text-white font-extrabold shadow-xs" title="Grade B: Baik (Memenuhi Standar)">
                                            🟢 B
                                        </span>
                                    @elseif($row->grade === 'C')
                                        <span class="badge badge-md bg-amber-500 text-white font-extrabold shadow-xs" title="Grade C: Cukup (Perlu Evaluasi)">
                                            🟡 C
                                        </span>
                                    @else
                                        <span class="badge badge-md bg-rose-500 text-white font-extrabold shadow-xs" title="Grade D: Kurang (Pembinaan Khusus)">
                                            🔴 D
                                        </span>
                                    @endif
                                </td>

                                {{-- Action Button: Lihat Rapor --}}
                                <td class="text-center">
                                    <button type="button" 
                                        wire:click="openRaport('{{ $row->salesman_code }}', '{{ $row->distributor_code }}')" 
                                        class="btn btn-xs btn-primary rounded-lg font-semibold gap-1 text-[11px] shadow-xs cursor-pointer">
                                        <x-heroicon-m-document-text class="w-3.5 h-3.5" />
                                        <span>Rapor</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Footer Pagination --}}
        @if(!empty($appliedRegion) && !empty($appliedArea) && $raportList->hasPages())
            <div class="p-3 border-t border-base-300 bg-base-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
                <div class="text-base-content/60 text-[11px]">
                    Menampilkan <strong>{{ $raportList->firstItem() }}</strong> &ndash; <strong>{{ $raportList->lastItem() }}</strong> dari <strong>{{ $raportList->total() }}</strong> Sales Executive
                </div>
                <div>
                    {{ $raportList->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL LEMBAR RAPOR INDIVIDU SE --}}
    @if($showModal && $modalData)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 animate-fade-in print:p-0 print:bg-transparent print:static"
             @keydown.escape.window="$wire.closeModal()"
             @click.self="$wire.closeModal()">
            
            <div id="raport-modal-card" class="bg-base-100 rounded-2xl shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden flex flex-col max-h-[92vh] print:max-h-none print:shadow-none print:border-none print:w-full print:rounded-none">
                
                {{-- Modal Header / Cover --}}
                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 sm:p-6 shrink-0 relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white shrink-0 border border-white/20">
                            <x-heroicon-s-academic-cap class="w-7 h-7" />
                        </div>
                        <div>
                            <span class="badge badge-xs bg-emerald-500/30 text-emerald-300 border-none uppercase font-bold tracking-wider text-[10px] px-2 py-1">
                                Scorecard Kinerja Lapangan Resmi
                            </span>
                            <h3 class="text-lg sm:text-xl font-black tracking-tight mt-0.5">
                                RAPOR KUNJUNGAN SALES EXECUTIVE
                            </h3>
                            <p class="text-xs text-white/70">
                                Evaluasi Objektif & Multidimensi Periode: <strong>{{ $modalData->period_label }}</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Stempel Grade Besar --}}
                    <div class="flex items-center gap-3 self-end sm:self-auto">
                        <div class="text-right">
                            <div class="text-[10px] text-white/60 font-semibold uppercase tracking-wider">Peringkat Tim</div>
                            <div class="text-sm font-black font-mono text-amber-300">#{{ $modalData->rank }} dari {{ $metrics['total_se'] }} SE</div>
                        </div>
                        <div class="w-16 h-16 rounded-2xl flex flex-col items-center justify-center border-2 shadow-lg shrink-0
                            {{ $modalData->grade === 'A' ? 'bg-emerald-600/30 border-emerald-400 text-emerald-300' : ($modalData->grade === 'B' ? 'bg-blue-600/30 border-blue-400 text-blue-300' : ($modalData->grade === 'C' ? 'bg-amber-600/30 border-amber-400 text-amber-300' : 'bg-rose-600/30 border-rose-400 text-rose-300')) }}">
                            <span class="text-2xl font-black leading-none">{{ $modalData->grade }}</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5">{{ $modalData->skor_total }}</span>
                        </div>
                    </div>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-5 text-xs text-base-content print:p-0">
                    
                    {{-- Identity Profile Bar --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-base-200/50 p-3.5 rounded-xl border border-base-300">
                        <div>
                            <span class="text-[10px] text-base-content/50 uppercase font-semibold block">Nama Sales Executive</span>
                            <span class="font-bold text-sm text-base-content block mt-0.5">{{ $modalData->salesman_name }}</span>
                            <span class="text-[10px] font-mono text-base-content/60">{{ $modalData->salesman_code }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-base-content/50 uppercase font-semibold block">Distributor</span>
                            <span class="font-bold text-base-content block mt-0.5 truncate" title="{{ $modalData->distributor_name }}">{{ $modalData->distributor_name }}</span>
                            <span class="text-[10px] font-mono text-base-content/60">{{ $modalData->distributor_code }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-base-content/50 uppercase font-semibold block">Area & Region</span>
                            <span class="font-bold text-base-content block mt-0.5">{{ $modalData->area_name ?? $modalData->area_code }}</span>
                            <span class="text-[10px] text-base-content/60">{{ $modalData->region_name ?? $modalData->region_code }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-base-content/50 uppercase font-semibold block">Predikat Kinerja</span>
                            <span class="font-black text-xs block mt-0.5
                                {{ $modalData->grade === 'A' ? 'text-emerald-600' : ($modalData->grade === 'B' ? 'text-blue-600' : ($modalData->grade === 'C' ? 'text-amber-600' : 'text-rose-600')) }}">
                                {{ $modalData->grade_label }}
                            </span>
                            <span class="text-[10px] text-base-content/60">{{ $modalData->hari_aktif }} Hari Aktif Kerja</span>
                        </div>
                    </div>

                    {{-- 4 Pillars Deep-Dive Cards --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70 mb-2 flex items-center gap-1.5">
                            <x-heroicon-s-squares-2x2 class="w-4 h-4 text-primary" />
                            <span>Rincian Penilaian 4 Pilar Kinerja</span>
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            
                            {{-- Pilar 1: Produktivitas Kunjungan --}}
                            <div class="bg-base-100 p-3.5 rounded-xl border border-emerald-500/30 bg-emerald-500/5 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-emerald-800 dark:text-emerald-300">Pilar 1: Produktivitas Kunjungan</span>
                                        <span class="badge badge-sm bg-emerald-600 text-white font-bold">Bobot 30%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-emerald-600">{{ $modalData->score_pilar_1 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-emerald-500/20 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target Jadwal (PC):</span>
                                        <span class="font-bold">{{ number_format($modalData->total_pc) }} Toko</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Realisasi Kunjungan (AC):</span>
                                        <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($modalData->total_ac) }} Toko ({{ $modalData->pct_ac_pc }}%)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Kehadiran Hari Kerja:</span>
                                        <span class="font-bold">{{ $modalData->hari_aktif }} dari {{ $modalData->target_hari_kerja }} Hari</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Pilar 2: Efektivitas Penjualan --}}
                            <div class="bg-base-100 p-3.5 rounded-xl border border-blue-500/30 bg-blue-500/5 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-blue-800 dark:text-blue-300">Pilar 2: Efektivitas Penjualan</span>
                                        <span class="badge badge-sm bg-blue-600 text-white font-bold">Bobot 30%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-blue-600">{{ $modalData->score_pilar_2 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-blue-500/20 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Toko Bertransaksi (EC):</span>
                                        <span class="font-bold">{{ number_format($modalData->total_ec) }} Toko</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Strike Rate (EC/AC):</span>
                                        <span class="font-bold text-blue-700 dark:text-blue-400">{{ $modalData->strike_rate }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Total Omset Penjualan:</span>
                                        <span class="font-bold text-base-content">Rp {{ number_format($modalData->total_value) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Average Basket / Drop Size:</span>
                                        <span class="font-bold text-base-content/80">Rp {{ number_format($modalData->drop_size) }} / EC</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Pilar 3: Kepatuhan SOP & Integritas --}}
                            <div class="bg-base-100 p-3.5 rounded-xl border border-amber-500/30 bg-amber-500/5 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-amber-800 dark:text-amber-300">Pilar 3: Kepatuhan SOP & Integritas</span>
                                        <span class="badge badge-sm bg-amber-600 text-white font-bold">Bobot 25%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-amber-600">{{ $modalData->score_pilar_3 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-amber-500/20 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Check-in di Titik Depo:</span>
                                        <span class="font-bold {{ $modalData->count_depo > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $modalData->count_depo }} Kasus
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Titik Koordinat Kembar:</span>
                                        <span class="font-bold {{ $modalData->count_kembar > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $modalData->count_kembar }} Kasus
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Di Luar Radius Master (>50m):</span>
                                        <span class="font-bold {{ $modalData->count_radius > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                            {{ $modalData->count_radius }} Toko
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Kunjungan Kilat (< 2 Menit):</span>
                                        <span class="font-bold {{ $modalData->count_kilat > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                            {{ $modalData->count_kilat }} Kunjungan
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Pilar 4: Manajemen Waktu Kerja --}}
                            <div class="bg-base-100 p-3.5 rounded-xl border border-purple-500/30 bg-purple-500/5 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-purple-800 dark:text-purple-300">Pilar 4: Manajemen Waktu Kerja</span>
                                        <span class="badge badge-sm bg-purple-600 text-white font-bold">Bobot 15%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-purple-600">{{ $modalData->score_pilar_4 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-purple-500/20 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Rasio Waktu di Toko:</span>
                                        <span class="font-bold text-purple-700 dark:text-purple-400">{{ $modalData->pct_store_time }}% dari Total Jam Kerja</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Rata-rata Durasi di Toko:</span>
                                        <span class="font-bold">{{ $modalData->avg_minutes_per_store }} Menit / Toko</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Standar Alokasi:</span>
                                        <span class="font-medium text-base-content/60">&ge; 50% di Toko &bull; 7-20 Menit/Toko</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Simulasi & Rincian Hitung Manual SE Ini --}}
                    <div x-data="{ openCalc: false }" class="bg-base-200/50 rounded-xl border border-base-300 p-3.5 space-y-3 print:bg-white print:border-gray-300">
                        <div @click="openCalc = !openCalc" class="flex items-center justify-between cursor-pointer select-none">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-600 flex items-center justify-center shrink-0">
                                    <x-heroicon-s-calculator class="w-4 h-4" />
                                </div>
                                <div>
                                    <span class="font-bold text-xs text-base-content block">Bongkar Rumus &amp; Hitung Manual Nilai {{ $modalData->salesman_name }}</span>
                                    <span class="text-[10px] text-base-content/60">Lihat kalkulasi matematis langkah demi langkah bagaimana skor akhir <strong>{{ $modalData->skor_total }}</strong> didapatkan</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-primary font-bold print:hidden">
                                <span x-text="openCalc ? 'Tutup Kalkulasi' : 'Lihat Hitungan'"></span>
                                <x-heroicon-m-chevron-down class="w-4 h-4 transition-transform duration-200" ::class="openCalc ? 'rotate-180' : ''" />
                            </div>
                        </div>

                        <div x-show="openCalc" x-cloak class="pt-3 border-t border-base-300 space-y-2.5 text-[11px] font-mono leading-relaxed print:block">
                            {{-- Step 1: Pilar 1 --}}
                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 space-y-1">
                                <div class="flex justify-between items-center text-emerald-700 dark:text-emerald-400 font-bold">
                                    <span>Langkah 1: Pilar 1 (Produktivitas, Bobot 30%)</span>
                                    <span>Skor: {{ $modalData->score_pilar_1 }} / 100</span>
                                </div>
                                <div class="text-[10.5px] text-base-content/80 space-y-0.5">
                                    <div>&bull; Kepatuhan Rute PJP: {{ $modalData->total_ac }} / {{ $modalData->total_pc }} &times; 100% = {{ $modalData->pct_ac_pc }}% &rarr; <strong>Skor: {{ $modalData->score_ac_pc ?? $modalData->score_pilar_1 }}</strong> (Porsi 80%)</div>
                                    <div>&bull; Hari Aktif: {{ $modalData->hari_aktif }} / {{ $modalData->target_hari_kerja }} hari = {{ round(($modalData->hari_aktif / max(1, $modalData->target_hari_kerja)) * 100, 1) }}% &rarr; <strong>Skor: {{ $modalData->score_hari_aktif ?? 0 }}</strong> (Porsi 20%)</div>
                                    <div class="text-primary font-bold pt-0.5">&rArr; Pilar 1 = ({{ $modalData->score_ac_pc ?? $modalData->score_pilar_1 }} &times; 0.8) + ({{ $modalData->score_hari_aktif ?? 0 }} &times; 0.2) = <strong>{{ $modalData->score_pilar_1 }}</strong></div>
                                </div>
                            </div>

                            {{-- Step 2: Pilar 2 --}}
                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 space-y-1">
                                <div class="flex justify-between items-center text-blue-700 dark:text-blue-400 font-bold">
                                    <span>Langkah 2: Pilar 2 (Efektivitas, Bobot 30%)</span>
                                    <span>Skor: {{ $modalData->score_pilar_2 }} / 100</span>
                                </div>
                                <div class="text-[10.5px] text-base-content/80 space-y-0.5">
                                    <div>&bull; Strike Rate: {{ $modalData->total_ec }} / {{ $modalData->total_ac }} = {{ $modalData->strike_rate }}% &rarr; <strong>Skor: {{ $modalData->score_strike_rate ?? $modalData->score_pilar_2 }}</strong> (Porsi 60%)</div>
                                    <div>&bull; Omset: Rp {{ number_format($modalData->total_value) }} vs Rata-rata Area Rp {{ number_format($modalData->avg_area_value ?? 0) }} ({{ $modalData->val_ratio ?? 0 }}x) &rarr; <strong>Skor: {{ $modalData->score_omset ?? 0 }}</strong> (Porsi 40%)</div>
                                    <div class="text-primary font-bold pt-0.5">&rArr; Pilar 2 = ({{ $modalData->score_strike_rate ?? $modalData->score_pilar_2 }} &times; 0.6) + ({{ $modalData->score_omset ?? 0 }} &times; 0.4) = <strong>{{ $modalData->score_pilar_2 }}</strong></div>
                                </div>
                            </div>

                            {{-- Step 3: Pilar 3 --}}
                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 space-y-1">
                                <div class="flex justify-between items-center text-amber-700 dark:text-amber-400 font-bold">
                                    <span>Langkah 3: Pilar 3 (Integritas Lapangan, Bobot 25%)</span>
                                    <span>Skor: {{ $modalData->score_pilar_3 }} / 100</span>
                                </div>
                                <div class="text-[10.5px] text-base-content/80 space-y-0.5">
                                    <div>&bull; Modal Awal: 100 Poin</div>
                                    <div>&bull; Potongan Penalti Pelanggaran:</div>
                                    <div class="pl-2 space-y-0.5 text-[10px] text-base-content/70">
                                        @php $acDiv = max(1, $modalData->total_ac); @endphp
                                        @if($modalData->count_depo > 0)
                                            <div class="text-rose-600">- Depo: ({{ $modalData->count_depo }} / {{ $modalData->total_ac }}) &times; 100 &times; 3.0 = -{{ round(($modalData->count_depo / $acDiv) * 100 * 3.0, 1) }}%</div>
                                        @endif
                                        @if($modalData->count_kembar > 0)
                                            <div class="text-rose-600">- Kembar: ({{ $modalData->count_kembar }} / {{ $modalData->total_ac }}) &times; 100 &times; 2.5 = -{{ round(($modalData->count_kembar / $acDiv) * 100 * 2.5, 1) }}%</div>
                                        @endif
                                        @if(($modalData->count_kilat_ec ?? 0) > 0)
                                            <div class="text-rose-600">- Order Kilat: ({{ $modalData->count_kilat_ec }} / {{ $modalData->total_ac }}) &times; 100 &times; 2.0 = -{{ round(($modalData->count_kilat_ec / $acDiv) * 100 * 2.0, 1) }}%</div>
                                        @endif
                                        @if($modalData->count_radius > 0)
                                            <div class="text-amber-600">- Luar Radius: ({{ $modalData->count_radius }} / {{ $modalData->total_ac }}) &times; 100 &times; 1.0 = -{{ round(($modalData->count_radius / $acDiv) * 100 * 1.0, 1) }}%</div>
                                        @endif
                                        @if(($modalData->count_kilat_non_ec ?? 0) > 0)
                                            <div class="text-amber-600">- Kilat Non-EC: ({{ $modalData->count_kilat_non_ec }} / {{ $modalData->total_ac }}) &times; 100 &times; 0.5 = -{{ round(($modalData->count_kilat_non_ec / $acDiv) * 100 * 0.5, 1) }}%</div>
                                        @endif
                                        @if($modalData->count_no_gps > 0)
                                            <div class="text-base-content/60">- GPS Blank: ({{ $modalData->count_no_gps }} / {{ $modalData->total_ac }}) &times; 100 &times; 0.2 = -{{ round(($modalData->count_no_gps / $acDiv) * 100 * 0.2, 1) }}%</div>
                                        @endif
                                        @if(($modalData->penalty_rate ?? 0) <= 0)
                                            <div class="text-emerald-600 font-semibold">&bull; Tidak ada penalti pelanggaran (Nol Pelanggaran)</div>
                                        @endif
                                    </div>
                                    <div class="text-primary font-bold pt-0.5">&rArr; Pilar 3 = 100 - {{ $modalData->penalty_rate ?? (100 - $modalData->score_pilar_3) }}% = <strong>{{ $modalData->score_pilar_3 }}</strong></div>
                                </div>
                            </div>

                            {{-- Step 4: Pilar 4 --}}
                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 space-y-1">
                                <div class="flex justify-between items-center text-purple-700 dark:text-purple-400 font-bold">
                                    <span>Langkah 4: Pilar 4 (Waktu Kerja, Bobot 15%)</span>
                                    <span>Skor: {{ $modalData->score_pilar_4 }} / 100</span>
                                </div>
                                <div class="text-[10.5px] text-base-content/80 space-y-0.5">
                                    <div>&bull; Porsi Jam Kerja di Toko: {{ $modalData->pct_store_time }}% &rarr; <strong>Skor: {{ $modalData->score_store_ratio ?? $modalData->score_pilar_4 }}</strong> (Porsi 60%)</div>
                                    <div>&bull; Rata-rata Durasi Layanan: {{ $modalData->avg_minutes_per_store }} menit/toko &rarr; <strong>Skor: {{ $modalData->score_duration ?? 0 }}</strong> (Porsi 40%)</div>
                                    <div class="text-primary font-bold pt-0.5">&rArr; Pilar 4 = ({{ $modalData->score_store_ratio ?? $modalData->score_pilar_4 }} &times; 0.6) + ({{ $modalData->score_duration ?? 0 }} &times; 0.4) = <strong>{{ $modalData->score_pilar_4 }}</strong></div>
                                </div>
                            </div>

                            {{-- Step 5: Skor Total Akhir --}}
                            <div class="bg-gradient-to-r from-primary/10 via-emerald-500/10 to-primary/10 p-3 rounded-lg border border-primary/20 space-y-1">
                                <div class="font-bold text-xs text-primary">Langkah 5: Perhitungan Skor Akhir &amp; Penentuan Grade</div>
                                <div class="text-[10.5px] text-base-content/80 font-mono">
                                    Total = ({{ $modalData->score_pilar_1 }} &times; 0.30) + ({{ $modalData->score_pilar_2 }} &times; 0.30) + ({{ $modalData->score_pilar_3 }} &times; 0.25) + ({{ $modalData->score_pilar_4 }} &times; 0.15)<br>
                                    Total = {{ round($modalData->score_pilar_1 * 0.30, 2) }} + {{ round($modalData->score_pilar_2 * 0.30, 2) }} + {{ round($modalData->score_pilar_3 * 0.25, 2) }} + {{ round($modalData->score_pilar_4 * 0.15, 2) }} = <span class="font-bold text-emerald-600 text-xs">{{ $modalData->skor_total }}</span>
                                </div>
                                <div class="text-xs font-bold mt-1">
                                    &rArr; Hasil Akhir: <span class="badge badge-sm font-extrabold {{ $modalData->grade === 'A' ? 'badge-success text-white' : ($modalData->grade === 'B' ? 'badge-info text-white' : ($modalData->grade === 'C' ? 'badge-warning text-warning-content' : 'badge-error text-white')) }}">Grade {{ $modalData->grade }}</span> ({{ $modalData->grade_label }})
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Analisa Kekuatan & Area Perbaikan Otomatis Sistem --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Strengths --}}
                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-3.5">
                            <h5 class="font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5 mb-2 text-xs uppercase tracking-wider">
                                <x-heroicon-s-sparkles class="w-4 h-4 text-emerald-600" />
                                <span>Kekuatan Utama (Strengths)</span>
                            </h5>
                            <ul class="space-y-1.5 text-[11px] text-base-content/80">
                                @foreach($modalData->strengths as $str)
                                    <li class="flex items-start gap-1.5">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                                        <span>{{ $str }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Weaknesses / Improvement Areas --}}
                        <div class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3.5">
                            <h5 class="font-bold text-rose-800 dark:text-rose-300 flex items-center gap-1.5 mb-2 text-xs uppercase tracking-wider">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-rose-600" />
                                <span>Area Perbaikan (Improvement)</span>
                            </h5>
                            <ul class="space-y-1.5 text-[11px] text-base-content/80">
                                @foreach($modalData->weaknesses as $weak)
                                    <li class="flex items-start gap-1.5">
                                        <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-rose-600 shrink-0 mt-0.5" />
                                        <span>{{ $weak }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Rekomendasi Tindak Lanjut Supervisor --}}
                    <div class="bg-base-200/70 border border-base-300 rounded-xl p-4">
                        <h5 class="font-bold text-base-content flex items-center gap-1.5 mb-1 text-xs uppercase tracking-wider">
                            <x-heroicon-s-clipboard-document-check class="w-4 h-4 text-primary" />
                            <span>Rekomendasi Tindak Lanjut Supervisor (Action Plan)</span>
                        </h5>
                        <p class="text-xs text-base-content/80 leading-relaxed">
                            {{ $modalData->spv_recommendation }}
                        </p>
                    </div>

                    {{-- Kolom Tanda Tangan Cetak (Hanya tampil saat print) --}}
                    <div class="hidden print:grid grid-cols-3 gap-8 pt-8 text-center text-xs">
                        <div>
                            <p class="text-base-content/60">Sales Executive</p>
                            <div class="h-16"></div>
                            <p class="font-bold underline">{{ $modalData->salesman_name }}</p>
                            <p class="text-[10px] text-base-content/60 font-mono">{{ $modalData->salesman_code }}</p>
                        </div>
                        <div>
                            <p class="text-base-content/60">Supervisor / Team Leader</p>
                            <div class="h-16"></div>
                            <p class="font-bold underline">( ........................................ )</p>
                            <p class="text-[10px] text-base-content/60">Tanda Tangan & Tanggal</p>
                        </div>
                        <div>
                            <p class="text-base-content/60">Kepala Cabang / ASM</p>
                            <div class="h-16"></div>
                            <p class="font-bold underline">( ........................................ )</p>
                            <p class="text-[10px] text-base-content/60">Mengetahui & Menyetujui</p>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-3 sm:p-4 bg-base-200/50 border-t border-base-300 flex items-center justify-between gap-2 shrink-0 print:hidden">
                    <span class="text-[11px] text-base-content/50">
                        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                            @click="printRaport()" 
                            class="btn btn-sm btn-outline btn-neutral rounded-xl gap-1.5 text-xs font-semibold">
                            <x-heroicon-m-printer class="w-4 h-4" />
                            <span>Cetak Rapor</span>
                        </button>

                        <button type="button" 
                            wire:click="closeModal" 
                            class="btn btn-sm btn-primary rounded-xl px-5 text-xs font-semibold">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Print Media Styles khusus untuk Lembar Rapor SE --}}
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body > * {
                visibility: hidden !important;
            }
            #raport-modal-card, #raport-modal-card * {
                visibility: visible !important;
            }
            #raport-modal-card {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                background-color: #ffffff !important;
            }
        }
    </style>

    {{-- Modal Edukasi Panduan, Rumus Nilai & Logika Temuan --}}
    @include('livewire.report.analisa-kunjungan-se.audit-logic-modal')
</div>
