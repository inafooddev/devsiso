<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('se_raport_show_kpi') !== null ? localStorage.getItem('se_raport_show_kpi') === 'true' : true,
         toggleKpi() {
             this.showKpi = !this.showKpi;
             localStorage.setItem('se_raport_show_kpi', this.showKpi);
         },
         showAuditModal: false,
         printRaport() {
             if (typeof window.printRaportDocument === 'function') {
                 window.printRaportDocument();
             } else {
                 window.print();
             }
         }
     }">

    <x-slot name="title">Raport Kunjungan SE</x-slot>

    {{-- Container Konten Layar (Disembunyikan saat Cetak Lembar Rapor) --}}
    <div id="raport-screen-content" class="flex flex-col flex-1 min-h-0 min-w-0 {{ $showModal ? 'print:hidden' : '' }}">

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
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-base-content/60 uppercase">
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
                <span class="text-xs font-semibold text-base-content/60">
                    {{ $metrics['total_se'] }} Sales Executive
                </span>
            </div>
            <div class="text-[0.625rem] text-base-content/60 mt-1">
                Kinerja gabungan 4 pilar operasional SE di area terpilih
            </div>
        </div>

        {{-- Card 2: Distribusi Grade Tim --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-blue-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-base-content/60 uppercase">
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
            <div class="text-[0.625rem] text-base-content/60 mt-1">
                Standar: A (&ge;85) &bull; B (70-84) &bull; C (55-69) &bull; D (&lt;55)
            </div>
        </div>

        {{-- Card 3: Top Performer SE --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-amber-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-amber-600 uppercase">
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
                    <div class="flex items-center justify-between text-xs mt-0.5">
                        <span class="text-base-content/60 font-mono text-[0.625rem]">{{ $metrics['top_performer']->salesman_code }}</span>
                        <span class="badge badge-sm bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-extrabold border-none">
                            Skor: {{ $metrics['top_performer']->skor_total }} ({{ $metrics['top_performer']->grade }})
                        </span>
                    </div>
                @else
                    <span class="text-sm text-base-content/50 italic">-</span>
                @endif
            </div>
            <div class="text-[0.625rem] text-base-content/60 mt-1">
                Skor tertinggi dari 4 pilar komprehensif
            </div>
        </div>

        {{-- Card 4: SE Perlu Pembinaan --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-rose-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-rose-600 uppercase">
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
                    <div class="flex items-center justify-between text-xs mt-0.5">
                        <span class="text-base-content/60 font-mono text-[0.625rem]">{{ $metrics['needs_coaching']->salesman_code }}</span>
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
            <div class="text-[0.625rem] text-base-content/60 mt-1">
                Prioritas coaching / audit lapangan bersama SPV
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar (Hanya Filter & Action Buttons agar hemat ruang layar) --}}
        <div class="p-2.5 sm:p-3 border-b border-base-300 shrink-0 bg-base-200/30">
            <div class="flex flex-wrap items-center justify-between gap-2 w-full">
                
                {{-- Filter Group: Modern Hierarchical Breadcrumb Capsule (Hybrid) --}}
                <div class="flex flex-wrap items-center gap-2">
                    
                    {{-- 1. Kapsul Hierarki Wilayah (Region > Area > Distributor > Salesman) --}}
                    <div class="inline-flex flex-wrap sm:flex-nowrap items-center bg-base-100 rounded-xl border border-base-300 shadow-2xs divide-y sm:divide-y-0 sm:divide-x divide-base-200/80 p-0.5 text-xs max-w-full">
                        
                        {{-- Region Segment (jika multi-region/nasional) --}}
                        @if(count($regions) > 1 || $accessLevel === 'nasional')
                            @php
                                $rTitle = collect($regions)->firstWhere('region_code', $selectedRegion)?->region_name ?? 'Pilih Region';
                                $rIsActive = !empty($selectedRegion);
                            @endphp
                            <div class="relative flex items-center pl-2 pr-1.5 w-32 sm:w-36 shrink-0 min-w-0" title="{{ $rTitle }}">
                                <x-heroicon-m-globe-asia-australia class="w-3.5 h-3.5 text-primary/70 shrink-0 mr-1 pointer-events-none" />
                                <select wire:model.live="selectedRegion" 
                                    class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs {{ $rIsActive ? 'text-primary font-bold' : 'font-medium text-base-content/75' }} w-full min-w-0 truncate pl-0 pr-4 py-1 cursor-pointer">
                                    <option value="" class="text-base-content">Pilih Region</option>
                                    @foreach($regions as $r)
                                        <option value="{{ $r->region_code }}" class="text-base-content">{{ $r->region_name }}</option>
                                    @endforeach
                                </select>
                                <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2" />
                            </div>
                        @endif

                        {{-- Area Segment --}}
                        @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                            @php
                                $aTitle = collect($areas)->firstWhere('area_code', $selectedArea)?->area_name ?? 'Pilih Area';
                                $aIsActive = !empty($selectedArea);
                            @endphp
                            <div class="relative flex items-center pl-2 pr-1.5 w-32 sm:w-36 shrink-0 min-w-0 {{ empty($selectedRegion) ? 'opacity-40' : '' }}" title="{{ $aTitle }}">
                                <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-rose-500/70 shrink-0 mr-1 pointer-events-none" />
                                <select wire:model.live="selectedArea" 
                                    class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs {{ $aIsActive ? 'text-primary font-bold' : 'font-medium text-base-content/75' }} w-full min-w-0 truncate pl-0 pr-4 py-1 cursor-pointer" 
                                    @disabled(empty($selectedRegion))>
                                    <option value="" class="text-base-content">Pilih Area</option>
                                    @foreach($areas as $a)
                                        <option value="{{ $a->area_code }}" class="text-base-content">{{ $a->area_name }}</option>
                                    @endforeach
                                </select>
                                <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2" />
                            </div>
                        @endif

                        {{-- Distributor Segment (Hybrid: Statis di Kapsul + Searchable Popover Modern) --}}
                        @php
                            $dObj = collect($distributors)->firstWhere('distributor_code', $selectedDistributor);
                            $dTitle = $dObj?->distributor_name ?? 'Semua Distributor';
                            $dIsActive = !empty($selectedDistributor);
                            $dDisabled = empty($selectedArea);
                            $distributorOptions = collect($distributors)->map(fn($d) => [
                                'code' => (string)$d->distributor_code,
                                'name' => (string)($d->distributor_name ?: $d->distributor_code)
                            ])->values()->all();
                        @endphp
                        <div wire:key="dist-capsule-{{ $selectedArea }}-{{ $selectedDistributor }}"
                            x-data="{
                                open: false,
                                search: '',
                                selectedCode: @js($selectedDistributor),
                                selectedLabel: @js($dTitle),
                                items: @js($distributorOptions),
                                get filtered() {
                                    if (!this.search.trim()) return this.items;
                                    const q = this.search.toLowerCase().trim();
                                    return this.items.filter(i => 
                                        i.name.toLowerCase().includes(q) || 
                                        i.code.toLowerCase().includes(q)
                                    );
                                },
                                select(code) {
                                    this.open = false;
                                    this.search = '';
                                    $wire.set('selectedDistributor', code);
                                }
                            }"
                            @click.outside="open = false"
                            @keydown.escape.window="open = false"
                            class="relative flex items-center w-36 sm:w-44 shrink-0 min-w-0 {{ $dDisabled ? 'opacity-40 pointer-events-none' : '' }}"
                            title="{{ $dTitle }}">

                            {{-- Trigger Button inside Capsule --}}
                            <button type="button"
                                @click="if (!{{ $dDisabled ? 'true' : 'false' }}) { open = !open; if(open) { $nextTick(() => $refs.distSearchInput?.focus()) } }"
                                class="w-full flex items-center justify-between text-left py-1 pl-2 pr-1.5 focus:outline-none cursor-pointer group"
                                {{ $dDisabled ? 'disabled' : '' }}>
                                <div class="flex items-center min-w-0 flex-1 mr-1">
                                    <x-heroicon-m-building-office-2 class="w-3.5 h-3.5 text-indigo-500/80 shrink-0 mr-1" />
                                    <span class="truncate text-xs font-medium {{ $dIsActive ? 'text-primary font-bold' : 'text-base-content/75' }}"
                                        x-text="selectedLabel"></span>
                                </div>
                                <div class="flex items-center gap-0.5 shrink-0">
                                    @if($dIsActive)
                                        <span @click.stop="select('')"
                                            class="p-0.5 rounded-full hover:bg-base-200 text-base-content/40 hover:text-error transition-colors cursor-pointer"
                                            title="Hapus pilihan distributor">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @endif
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-base-content/40 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            {{-- Dropdown Popover Menu --}}
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                style="display: none;"
                                class="absolute left-0 top-full mt-1.5 z-[70] w-72 sm:w-80 bg-base-100 rounded-xl shadow-2xl border border-base-300 p-2 flex flex-col text-xs focus:outline-none">
                                
                                {{-- Search Input Box --}}
                                <div class="relative mb-2 shrink-0">
                                    <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40 pointer-events-none" />
                                    <input type="text"
                                        x-model="search"
                                        x-ref="distSearchInput"
                                        placeholder="Cari nama distributor / kode..."
                                        class="input input-xs w-full pl-8 pr-6 rounded-lg bg-base-200/60 border border-transparent focus:border-primary focus:bg-base-100 text-xs font-normal" />
                                    <button x-show="search"
                                        @click="search = ''; $refs.distSearchInput?.focus()"
                                        type="button"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content cursor-pointer">
                                        <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                {{-- Options List --}}
                                <div class="overflow-y-auto max-h-60 flex flex-col gap-0.5 pr-0.5 divide-y divide-base-200/40">
                                    {{-- Default: Semua Distributor --}}
                                    <button type="button"
                                        @click="select('')"
                                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition-colors"
                                        :class="!selectedCode ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200 text-base-content/75'">
                                        <div class="flex items-center gap-1.5">
                                            <x-heroicon-m-building-office-2 class="w-3.5 h-3.5 text-indigo-500/80 shrink-0" />
                                            <span>Semua Distributor</span>
                                        </div>
                                        <template x-if="!selectedCode">
                                            <x-heroicon-m-check class="w-4 h-4 text-primary shrink-0" />
                                        </template>
                                    </button>

                                    {{-- List of Distributors --}}
                                    <template x-for="item in filtered" :key="item.code">
                                        <button type="button"
                                            @click="select(item.code)"
                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition-colors pt-1.5"
                                            :class="selectedCode === item.code ? 'bg-primary text-white font-semibold shadow-xs' : 'hover:bg-base-200 text-base-content'"
                                            :title="item.name + ' (' + item.code + ')'">
                                            <div class="flex flex-col min-w-0 pr-1">
                                                <span class="truncate font-medium text-xs" :class="selectedCode === item.code ? 'text-white' : 'text-base-content'" x-text="item.name"></span>
                                                <span class="text-[0.625rem] font-mono" :class="selectedCode === item.code ? 'text-white/80' : 'text-base-content/50'" x-text="item.code"></span>
                                            </div>
                                            <template x-if="selectedCode === item.code">
                                                <x-heroicon-m-check class="w-4 h-4 text-white shrink-0 ml-1.5" />
                                            </template>
                                        </button>
                                    </template>

                                    <template x-if="filtered.length === 0">
                                        <div class="px-3 py-4 text-xs text-base-content/50 italic text-center">
                                            Tidak ditemukan distributor yang cocok
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Salesman Segment (Hybrid: Statis di Kapsul + Searchable Popover Modern) --}}
                        @php
                            $sObj = collect($salesmen)->firstWhere('salesman_code', $selectedSalesman);
                            $sTitle = $sObj?->salesman_name ?? 'Semua Salesman';
                            $sIsActive = !empty($selectedSalesman);
                            $sDisabled = empty($selectedArea);
                            $salesmenOptions = collect($salesmen)->map(fn($s) => [
                                'code' => (string)$s->salesman_code,
                                'name' => (string)($s->salesman_name ?: $s->salesman_code)
                            ])->values()->all();
                        @endphp
                        <div wire:key="sales-capsule-{{ $selectedArea }}-{{ $selectedDistributor }}-{{ $selectedSalesman }}"
                            x-data="{
                                open: false,
                                search: '',
                                selectedCode: @js($selectedSalesman),
                                selectedLabel: @js($sTitle),
                                items: @js($salesmenOptions),
                                get filtered() {
                                    if (!this.search.trim()) return this.items;
                                    const q = this.search.toLowerCase().trim();
                                    return this.items.filter(i => 
                                        i.name.toLowerCase().includes(q) || 
                                        i.code.toLowerCase().includes(q)
                                    );
                                },
                                select(code) {
                                    this.open = false;
                                    this.search = '';
                                    $wire.set('selectedSalesman', code);
                                }
                            }"
                            @click.outside="open = false"
                            @keydown.escape.window="open = false"
                            class="relative flex items-center w-32 sm:w-40 shrink-0 min-w-0 {{ $sDisabled ? 'opacity-40 pointer-events-none' : '' }}"
                            title="{{ $sTitle }}">

                            {{-- Trigger Button inside Capsule --}}
                            <button type="button"
                                @click="if (!{{ $sDisabled ? 'true' : 'false' }}) { open = !open; if(open) { $nextTick(() => $refs.salesSearchInput?.focus()) } }"
                                class="w-full flex items-center justify-between text-left py-1 pl-2 pr-1.5 focus:outline-none cursor-pointer group"
                                {{ $sDisabled ? 'disabled' : '' }}>
                                <div class="flex items-center min-w-0 flex-1 mr-1">
                                    <x-heroicon-m-user class="w-3.5 h-3.5 text-emerald-600/80 shrink-0 mr-1" />
                                    <span class="truncate text-xs font-medium {{ $sIsActive ? 'text-primary font-bold' : 'text-base-content/75' }}"
                                        x-text="selectedLabel"></span>
                                </div>
                                <div class="flex items-center gap-0.5 shrink-0">
                                    @if($sIsActive)
                                        <span @click.stop="select('')"
                                            class="p-0.5 rounded-full hover:bg-base-200 text-base-content/40 hover:text-error transition-colors cursor-pointer"
                                            title="Hapus pilihan salesman">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @endif
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-base-content/40 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            {{-- Dropdown Popover Menu --}}
                            <div x-show="open"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                style="display: none;"
                                class="absolute left-0 top-full mt-1.5 z-[70] w-72 sm:w-80 bg-base-100 rounded-xl shadow-2xl border border-base-300 p-2 flex flex-col text-xs focus:outline-none">
                                
                                {{-- Search Input Box --}}
                                <div class="relative mb-2 shrink-0">
                                    <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40 pointer-events-none" />
                                    <input type="text"
                                        x-model="search"
                                        x-ref="salesSearchInput"
                                        placeholder="Cari nama salesman / kode SE..."
                                        class="input input-xs w-full pl-8 pr-6 rounded-lg bg-base-200/60 border border-transparent focus:border-primary focus:bg-base-100 text-xs font-normal" />
                                    <button x-show="search"
                                        @click="search = ''; $refs.salesSearchInput?.focus()"
                                        type="button"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content cursor-pointer">
                                        <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                {{-- Options List --}}
                                <div class="overflow-y-auto max-h-60 flex flex-col gap-0.5 pr-0.5 divide-y divide-base-200/40">
                                    {{-- Default: Semua Salesman --}}
                                    <button type="button"
                                        @click="select('')"
                                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition-colors"
                                        :class="!selectedCode ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-base-200 text-base-content/75'">
                                        <div class="flex items-center gap-1.5">
                                            <x-heroicon-m-user class="w-3.5 h-3.5 text-emerald-600/80 shrink-0" />
                                            <span>Semua Salesman</span>
                                        </div>
                                        <template x-if="!selectedCode">
                                            <x-heroicon-m-check class="w-4 h-4 text-primary shrink-0" />
                                        </template>
                                    </button>

                                    {{-- List of Salesmen --}}
                                    <template x-for="item in filtered" :key="item.code">
                                        <button type="button"
                                            @click="select(item.code)"
                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition-colors pt-1.5"
                                            :class="selectedCode === item.code ? 'bg-primary text-white font-semibold shadow-xs' : 'hover:bg-base-200 text-base-content'"
                                            :title="item.name + ' (' + item.code + ')'">
                                            <div class="flex flex-col min-w-0 pr-1">
                                                <span class="truncate font-medium text-xs" :class="selectedCode === item.code ? 'text-white' : 'text-base-content'" x-text="item.name"></span>
                                                <span class="text-[0.625rem] font-mono" :class="selectedCode === item.code ? 'text-white/80' : 'text-base-content/50'" x-text="item.code"></span>
                                            </div>
                                            <template x-if="selectedCode === item.code">
                                                <x-heroicon-m-check class="w-4 h-4 text-white shrink-0 ml-1.5" />
                                            </template>
                                        </button>
                                    </template>

                                    <template x-if="filtered.length === 0">
                                        <div class="px-3 py-4 text-xs text-base-content/50 italic text-center">
                                            Tidak ditemukan salesman yang cocok
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Kapsul Periode (Bulan & Tahun) --}}
                    <div class="inline-flex items-center bg-base-100 rounded-xl border border-base-300 shadow-2xs divide-x divide-base-200/80 p-0.5 text-xs shrink-0">
                        <div class="flex items-center pl-2 pr-1.5 gap-1.5">
                            <x-heroicon-m-calendar-days class="w-3.5 h-3.5 text-primary/70 shrink-0 mr-0.5" />
                            
                            <div class="relative flex items-center">
                                <select wire:model.live="selectedMonth" 
                                    class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs font-medium pl-0 pr-4 py-1 cursor-pointer" 
                                    title="Pilih Bulan Evaluasi">
                                    @foreach($monthOptions as $mVal => $mLabel)
                                        <option value="{{ $mVal }}" class="text-base-content">{{ $mLabel }}</option>
                                    @endforeach
                                </select>
                                <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-0.5 top-1/2 -translate-y-1/2" />
                            </div>

                            <span class="text-xs font-semibold text-base-content/30 select-none">/</span>

                            <div class="relative flex items-center">
                                <select wire:model.live="selectedYear" 
                                    class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs font-mono font-medium pl-0 pr-4 py-1 cursor-pointer" 
                                    title="Pilih Tahun Evaluasi">
                                    @foreach($yearOptions as $yVal)
                                        <option value="{{ $yVal }}" class="text-base-content">{{ $yVal }}</option>
                                    @endforeach
                                </select>
                                <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-0.5 top-1/2 -translate-y-1/2" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons: Terapkan, Reset, Excel --}}
                <div class="flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0">
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
                <span class="text-base-content/50 font-semibold uppercase text-[0.625rem] mr-1">Grade:</span>
                
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

                <div class="flex items-center gap-1 text-xs text-base-content/60 shrink-0">
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
                <table class="table table-xs table-pin-rows w-full text-xs">
                    <thead>
                        <tr class="bg-base-200/90 text-base-content/80 text-xs uppercase tracking-wider font-bold">
                            <th class="w-16 min-w-[64px] text-center">Rank</th>
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
                                    <span>Pilar 1 (20%)</span>
                                    <span class="text-[0.625rem] font-normal lowercase">Produktivitas</span>
                                </button>
                            </th>

                            {{-- Pilar 2 --}}
                            <th class="text-center min-w-[110px] bg-blue-500/5">
                                <button type="button" wire:click="sortBy('score_pilar_2')" class="hover:text-primary inline-flex flex-col items-center">
                                    <span>Pilar 2 (40%)</span>
                                    <span class="text-[0.625rem] font-normal lowercase">Penjualan</span>
                                </button>
                            </th>

                            {{-- Pilar 3 --}}
                            <th class="text-center min-w-[110px] bg-amber-500/5">
                                <button type="button" wire:click="sortBy('score_pilar_3')" class="hover:text-primary inline-flex flex-col items-center">
                                    <span>Pilar 3 (25%)</span>
                                    <span class="text-[0.625rem] font-normal lowercase">Kepatuhan SOP</span>
                                </button>
                            </th>

                            {{-- Pilar 4 --}}
                            <th class="text-center min-w-[110px] bg-purple-500/5">
                                <button type="button" wire:click="sortBy('score_pilar_4')" class="hover:text-primary inline-flex flex-col items-center">
                                    <span>Pilar 4 (15%)</span>
                                    <span class="text-[0.625rem] font-normal lowercase">Waktu Kerja</span>
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
                            <th class="text-center min-w-[150px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @foreach($raportList as $row)
                            <tr class="hover:bg-base-200/50 transition-colors group">
                                {{-- Rank --}}
                                <td class="text-center font-bold font-mono whitespace-nowrap">
                                    @if($row->rank === 1)
                                        <span class="badge badge-warning text-warning-content font-extrabold text-xs shadow-2xs">🥇 #1</span>
                                    @elseif($row->rank === 2)
                                        <span class="badge badge-neutral text-white font-extrabold text-xs shadow-2xs">🥈 #2</span>
                                    @elseif($row->rank === 3)
                                        <span class="badge badge-ghost font-extrabold text-xs border border-base-300">🥉 #3</span>
                                    @else
                                        <span class="text-base-content/50 font-semibold text-xs">#{{ $row->rank }}</span>
                                    @endif
                                </td>

                                {{-- Sales Executive Info --}}
                                <td>
                                    <div class="font-bold text-xs text-base-content group-hover:text-primary transition-colors">
                                        {{ $row->salesman_name }}
                                    </div>
                                    <div class="text-[0.625rem] font-mono text-base-content/50 flex items-center gap-1.5 mt-0.5">
                                        <span>{{ $row->salesman_code }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $row->hari_aktif }}/{{ $row->target_hari_kerja }} Hari Aktif</span>
                                    </div>
                                </td>

                                {{-- Distributor --}}
                                <td>
                                    <div class="font-medium text-xs text-base-content truncate max-w-[160px]" title="{{ $row->distributor_name }}">
                                        {{ $row->distributor_name }}
                                    </div>
                                    <div class="text-[0.625rem] font-mono text-base-content/40">
                                        {{ $row->distributor_code }}
                                    </div>
                                </td>

                                {{-- Pilar 1: Produktivitas Kunjungan (20%) --}}
                                <td class="text-center bg-emerald-500/5">
                                    <div class="font-black font-mono text-xs {{ $row->score_pilar_1 >= 85 ? 'text-emerald-600' : ($row->score_pilar_1 >= 70 ? 'text-blue-600' : 'text-amber-600') }}">
                                        {{ $row->score_pilar_1 }}
                                    </div>
                                    <div class="text-[0.625rem] text-base-content/60 font-medium">
                                        {{ $row->pct_ac_pc }}% ({{ $row->total_ac }}/{{ $row->total_pc }})
                                    </div>
                                </td>

                                {{-- Pilar 2: Efektivitas Penjualan (40%) --}}
                                <td class="text-center bg-blue-500/5">
                                    <div class="font-black font-mono text-xs {{ $row->score_pilar_2 >= 85 ? 'text-emerald-600' : ($row->score_pilar_2 >= 70 ? 'text-blue-600' : 'text-amber-600') }}">
                                        {{ $row->score_pilar_2 }}
                                    </div>
                                    <div class="text-[0.625rem] text-base-content/60 font-medium">
                                        SR: {{ $row->strike_rate }}% &bull; Rp {{ number_format($row->total_value / 1000000, 1) }}M
                                    </div>
                                </td>

                                {{-- Pilar 3: Kepatuhan SOP & Integritas (25%) --}}
                                <td class="text-center bg-amber-500/5">
                                    <div class="font-black font-mono text-xs {{ $row->score_pilar_3 >= 70 ? 'text-emerald-600' : ($row->score_pilar_3 >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ $row->score_pilar_3 }}
                                    </div>
                                    <div class="text-[0.625rem] text-base-content/60 font-medium flex items-center justify-center gap-1">
                                        @if($row->total_anomalies === 0)
                                            <span class="text-emerald-600 font-semibold">Clean (100%)</span>
                                        @else
                                            <span class="text-rose-600 font-semibold">{{ $row->total_anomalies }} Anomali</span>
                                            <span class="text-[0.625rem] text-base-content/40">({{ $row->clean_rate }}%)</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Pilar 4: Waktu Kerja (15%) --}}
                                <td class="text-center bg-purple-500/5">
                                    <div class="font-black font-mono text-xs {{ $row->score_pilar_4 >= 80 ? 'text-emerald-600' : ($row->score_pilar_4 >= 65 ? 'text-blue-600' : 'text-amber-600') }}">
                                        {{ $row->score_pilar_4 }}
                                    </div>
                                    <div class="text-[0.625rem] text-base-content/60 font-medium">
                                        {{ $row->avg_work_hours ?? 0 }} Jam/Hari &bull; {{ $row->avg_minutes_per_store }}m
                                    </div>
                                </td>

                                {{-- Skor Total --}}
                                <td class="text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-black font-mono {{ $row->skor_total >= 85 ? 'text-emerald-600' : ($row->skor_total >= 70 ? 'text-blue-600' : ($row->skor_total >= 55 ? 'text-amber-600' : 'text-rose-600')) }}">
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
                                        <span class="badge badge-sm bg-emerald-500 text-white font-extrabold text-xs shadow-xs" title="Grade A: Sangat Baik (Role Model)">
                                            🏆 A
                                        </span>
                                    @elseif($row->grade === 'B')
                                        <span class="badge badge-sm bg-blue-500 text-white font-extrabold text-xs shadow-xs" title="Grade B: Baik (Memenuhi Standar)">
                                            🟢 B
                                        </span>
                                    @elseif($row->grade === 'C')
                                        <span class="badge badge-sm bg-amber-500 text-white font-extrabold text-xs shadow-xs" title="Grade C: Cukup (Perlu Evaluasi)">
                                            🟡 C
                                        </span>
                                    @else
                                        <span class="badge badge-sm bg-rose-500 text-white font-extrabold text-xs shadow-xs" title="Grade D: Kurang (Pembinaan Khusus)">
                                            🔴 D
                                        </span>
                                    @endif
                                </td>

                                {{-- Action Buttons: Lihat Rapor & History --}}
                                <td class="text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" 
                                            wire:click="openRaport('{{ $row->salesman_code }}', '{{ $row->distributor_code }}')" 
                                            class="btn btn-xs btn-primary rounded-lg font-semibold gap-1 text-xs shadow-xs cursor-pointer"
                                            title="Lihat Lembar Rapor Bulan Ini">
                                            <x-heroicon-m-document-text class="w-3.5 h-3.5" />
                                            <span>Rapor</span>
                                        </button>

                                        <button type="button" 
                                            wire:click="openHistoryModal('{{ $row->salesman_code }}', '{{ $row->distributor_code }}')" 
                                            class="btn btn-xs btn-outline btn-info rounded-lg font-semibold gap-1 text-xs shadow-xs cursor-pointer hover:text-white"
                                            title="Histori Rapor 1 Tahun (12 Bulan)">
                                            <x-heroicon-m-calendar-days class="w-3.5 h-3.5" />
                                            <span>History</span>
                                        </button>
                                    </div>
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
                <div class="text-base-content/60 text-xs">
                    Menampilkan <strong>{{ $raportList->firstItem() }}</strong> &ndash; <strong>{{ $raportList->lastItem() }}</strong> dari <strong>{{ $raportList->total() }}</strong> Sales Executive
                </div>
                <div>
                    {{ $raportList->links() }}
                </div>
            </div>
        @endif
    </div>
    </div> {{-- /#raport-screen-content --}}

    {{-- MODAL LEMBAR RAPOR INDIVIDU SE --}}
    @if($showModal && $modalData)
        <div id="raport-modal-wrapper"
             class="fixed inset-0 z-[100000] overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 animate-fade-in print:hidden"
             @keydown.escape.window="$wire.closeModal()"
             @click.self="$wire.closeModal()">
            
            <div id="raport-modal-card" 
                 data-title="Rapor_SE_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $modalData->salesman_name) }}_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $modalData->period_label) }}"
                 class="bg-base-100 rounded-2xl shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden flex flex-col max-h-[92vh] print:hidden">
                
                {{-- Modal Header / Cover --}}
                <div id="raport-modal-header" class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 sm:p-6 shrink-0 relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:rounded-xl print-avoid-break">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white shrink-0 border border-white/20">
                            <x-heroicon-s-academic-cap class="w-7 h-7" />
                        </div>
                        <div>
                            <span class="badge badge-xs bg-emerald-500/30 text-emerald-300 border-none uppercase font-bold tracking-wider text-[0.625rem] px-2 py-1">
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
                            <div class="text-[0.625rem] text-white/60 font-semibold uppercase tracking-wider">Peringkat Tim</div>
                            <div class="text-sm font-black font-mono text-amber-300">#{{ $modalData->rank }} dari {{ $metrics['total_se'] }} SE</div>
                        </div>
                        <div class="w-16 h-16 rounded-2xl flex flex-col items-center justify-center border-2 shadow-lg shrink-0
                            {{ $modalData->grade === 'A' ? 'bg-emerald-600/30 border-emerald-400 text-emerald-300' : ($modalData->grade === 'B' ? 'bg-blue-600/30 border-blue-400 text-blue-300' : ($modalData->grade === 'C' ? 'bg-amber-600/30 border-amber-400 text-amber-300' : 'bg-rose-600/30 border-rose-400 text-rose-300')) }}">
                            <span class="text-2xl font-black leading-none">{{ $modalData->grade }}</span>
                            <span class="text-[0.625rem] font-bold uppercase tracking-wider mt-0.5">{{ $modalData->skor_total }}</span>
                        </div>
                    </div>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div id="raport-modal-body" class="p-5 sm:p-6 overflow-y-auto space-y-5 text-xs text-base-content print:p-0 print:overflow-visible print:block print:space-y-4">
                    
                    {{-- Identity Profile Bar --}}
                    <div id="raport-identity-bar" class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-base-200/50 p-3.5 rounded-xl border border-base-300 print:bg-slate-50 print:border-slate-300 print-avoid-break">
                        <div>
                            <span class="text-[0.625rem] text-base-content/50 uppercase font-semibold block">Nama Sales Executive</span>
                            <span class="font-bold text-sm text-base-content block mt-0.5">{{ $modalData->salesman_name }}</span>
                            <span class="text-[0.625rem] font-mono text-base-content/60">{{ $modalData->salesman_code }}</span>
                        </div>
                        <div>
                            <span class="text-[0.625rem] text-base-content/50 uppercase font-semibold block">Distributor</span>
                            <span class="font-bold text-base-content block mt-0.5 truncate" title="{{ $modalData->distributor_name }}">{{ $modalData->distributor_name }}</span>
                            <span class="text-[0.625rem] font-mono text-base-content/60">{{ $modalData->distributor_code }}</span>
                        </div>
                        <div>
                            <span class="text-[0.625rem] text-base-content/50 uppercase font-semibold block">Area & Region</span>
                            <span class="font-bold text-base-content block mt-0.5">{{ $modalData->area_name ?? $modalData->area_code }}</span>
                            <span class="text-[0.625rem] text-base-content/60">{{ $modalData->region_name ?? $modalData->region_code }}</span>
                        </div>
                        <div>
                            <span class="text-[0.625rem] text-base-content/50 uppercase font-semibold block">Predikat Kinerja</span>
                            <span class="font-black text-xs block mt-0.5
                                {{ $modalData->grade === 'A' ? 'text-emerald-600' : ($modalData->grade === 'B' ? 'text-blue-600' : ($modalData->grade === 'C' ? 'text-amber-600' : 'text-rose-600')) }}">
                                {{ $modalData->grade_label }}
                            </span>
                            <span class="text-[0.625rem] text-base-content/60">{{ $modalData->hari_aktif }} Hari Aktif Kerja</span>
                        </div>
                    </div>

                    {{-- 4 Pillars Deep-Dive Cards --}}
                    <div class="print-avoid-break">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70 mb-2 flex items-center gap-1.5 print:text-slate-800">
                            <x-heroicon-s-squares-2x2 class="w-4 h-4 text-primary print:text-indigo-600" />
                            <span>Rincian Penilaian 4 Pilar Kinerja</span>
                        </h4>

                        <div id="raport-pillars-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            
                            {{-- Pilar 1: Produktivitas Kunjungan --}}
                            <div class="bg-base-100 p-3.5 rounded-xl border border-emerald-500/30 bg-emerald-500/5 flex flex-col justify-between print-avoid-break raport-pillar-card print:border-emerald-300 print:bg-white">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-emerald-800 dark:text-emerald-300">Pilar 1: Produktivitas Kunjungan</span>
                                        <span class="badge badge-sm bg-emerald-600 text-white font-bold print:bg-emerald-700">Bobot 20%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-emerald-600">{{ $modalData->score_pilar_1 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-emerald-500/20 space-y-1 text-xs">
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
                            <div class="bg-base-100 p-3.5 rounded-xl border border-blue-500/30 bg-blue-500/5 flex flex-col justify-between print-avoid-break raport-pillar-card print:border-blue-300 print:bg-white">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-blue-800 dark:text-blue-300">Pilar 2: Efektivitas Penjualan</span>
                                        <span class="badge badge-sm bg-blue-600 text-white font-bold print:bg-blue-700">Bobot 40%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-blue-600">{{ $modalData->score_pilar_2 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-blue-500/20 space-y-1 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Toko Bertransaksi (EC):</span>
                                        <span class="font-bold">{{ number_format($modalData->total_ec) }} Toko</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Strike Rate (EC/AC):</span>
                                        <span class="font-bold text-blue-700 dark:text-blue-400">{{ $modalData->strike_rate }}% (Porsi 40%)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Total Omset Penjualan:</span>
                                        <span class="font-bold text-base-content">Rp {{ number_format($modalData->total_value) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target Omset (KPI):</span>
                                        <span class="font-bold text-blue-700 dark:text-blue-400">Rp {{ number_format($modalData->target_omset ?? 350000000) }} ({{ $modalData->pct_omset ?? round(($modalData->total_value / 350000000) * 100, 1) }}%)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Average Basket / Drop Size:</span>
                                        <span class="font-bold text-base-content/80">Rp {{ number_format($modalData->drop_size) }} / EC</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Pilar 3: Kepatuhan SOP & Integritas --}}
                            <div class="bg-base-100 p-3.5 rounded-xl border border-amber-500/30 bg-amber-500/5 flex flex-col justify-between print-avoid-break raport-pillar-card print:border-amber-300 print:bg-white">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-amber-800 dark:text-amber-300">Pilar 3: Kepatuhan SOP & Integritas</span>
                                        <span class="badge badge-sm bg-amber-600 text-white font-bold print:bg-amber-700">Bobot 25%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-amber-600">{{ $modalData->score_pilar_3 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-amber-500/20 space-y-1 text-xs">
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
                            <div class="bg-base-100 p-3.5 rounded-xl border border-purple-500/30 bg-purple-500/5 flex flex-col justify-between print-avoid-break raport-pillar-card print:border-purple-300 print:bg-white">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-purple-800 dark:text-purple-300">Pilar 4: Manajemen Waktu Kerja</span>
                                        <span class="badge badge-sm bg-purple-600 text-white font-bold print:bg-purple-700">Bobot 15%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <span class="text-2xl font-black font-mono text-purple-600">{{ $modalData->score_pilar_4 }}</span>
                                        <span class="text-xs text-base-content/50">/ 100</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-purple-500/20 space-y-1 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Durasi Kerja Lapangan:</span>
                                        <span class="font-bold text-purple-700 dark:text-purple-400">{{ $modalData->avg_work_hours ?? 0 }} Jam / Hari (Skor: {{ $modalData->score_work_duration ?? $modalData->score_store_ratio ?? 0 }})</span>
                                    </div>
                                    <div class="flex justify-between text-[0.625rem] text-base-content/60 pl-1">
                                        <span>Distribusi Hari Kerja:</span>
                                        <span>&ge;7j: {{ $modalData->days_ge_7h ?? 0 }}h &bull; 5-7j: {{ $modalData->days_5_to_7h ?? 0 }}h &bull; &lt;5j: {{ $modalData->days_lt_5h ?? 0 }}h</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Rata-rata Durasi di Toko:</span>
                                        <span class="font-bold">{{ $modalData->avg_minutes_per_store }} Menit / Toko</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Standar Alokasi:</span>
                                        <span class="font-medium text-base-content/60">&ge; 7 Jam/Hari &bull; 7-20 Menit/Toko</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Simulasi & Rincian Hitung Manual SE Ini --}}
                    <div x-data="{ openCalc: false }" 
                         :class="openCalc ? '' : 'print:hidden'"
                         class="bg-base-200/50 rounded-xl border border-base-300 p-3.5 space-y-3 print:bg-white print:border-slate-300 print-avoid-break">
                        <div @click="openCalc = !openCalc" class="flex items-center justify-between cursor-pointer select-none">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-600 flex items-center justify-center shrink-0">
                                    <x-heroicon-s-calculator class="w-4 h-4" />
                                </div>
                                <div>
                                    <span class="font-bold text-xs text-base-content block">Bongkar Rumus &amp; Hitung Manual Nilai {{ $modalData->salesman_name }}</span>
                                    <span class="text-[0.625rem] text-base-content/60">Lihat kalkulasi matematis langkah demi langkah bagaimana skor akhir <strong>{{ $modalData->skor_total }}</strong> didapatkan</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-primary font-bold print:hidden">
                                <span x-text="openCalc ? 'Tutup Kalkulasi' : 'Lihat Hitungan'"></span>
                                <x-heroicon-m-chevron-down class="w-4 h-4 transition-transform duration-200" ::class="openCalc ? 'rotate-180' : ''" />
                            </div>
                        </div>

                        <div x-show="openCalc" x-cloak class="pt-3 border-t border-base-300 space-y-2.5 text-xs font-mono leading-relaxed print:block">
                            {{-- Step 1: Pilar 1 --}}
                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 space-y-1">
                                <div class="flex justify-between items-center text-emerald-700 dark:text-emerald-400 font-bold">
                                    <span>Langkah 1: Pilar 1 (Produktivitas, Bobot 20%)</span>
                                    <span>Skor: {{ $modalData->score_pilar_1 }} / 100</span>
                                </div>
                                <div class="text-xs text-base-content/80 space-y-0.5">
                                    <div>&bull; Kepatuhan Rute PJP: {{ $modalData->total_ac }} / {{ $modalData->total_pc }} &times; 100% = {{ $modalData->pct_ac_pc }}% &rarr; <strong>Skor: {{ $modalData->score_ac_pc ?? $modalData->score_pilar_1 }}</strong> (Porsi 80%)</div>
                                    <div>&bull; Hari Aktif: {{ $modalData->hari_aktif }} / {{ $modalData->target_hari_kerja }} hari = {{ round(($modalData->hari_aktif / max(1, $modalData->target_hari_kerja)) * 100, 1) }}% &rarr; <strong>Skor: {{ $modalData->score_hari_aktif ?? 0 }}</strong> (Porsi 20%)</div>
                                    <div class="text-primary font-bold pt-0.5">&rArr; Pilar 1 = ({{ $modalData->score_ac_pc ?? $modalData->score_pilar_1 }} &times; 0.8) + ({{ $modalData->score_hari_aktif ?? 0 }} &times; 0.2) = <strong>{{ $modalData->score_pilar_1 }}</strong></div>
                                </div>
                            </div>

                            {{-- Step 2: Pilar 2 --}}
                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 space-y-1">
                                <div class="flex justify-between items-center text-blue-700 dark:text-blue-400 font-bold">
                                    <span>Langkah 2: Pilar 2 (Efektivitas, Bobot 40%)</span>
                                    <span>Skor: {{ $modalData->score_pilar_2 }} / 100</span>
                                </div>
                                <div class="text-xs text-base-content/80 space-y-0.5">
                                    <div>&bull; Strike Rate: {{ $modalData->total_ec }} / {{ $modalData->total_ac }} = {{ $modalData->strike_rate }}% &rarr; <strong>Skor: {{ $modalData->score_strike_rate ?? $modalData->score_pilar_2 }}</strong> (Porsi 40%)</div>
                                    <div>&bull; Omset vs Target: Rp {{ number_format($modalData->total_value) }} vs Rp {{ number_format($modalData->target_omset ?? 350000000) }} ({{ $modalData->pct_omset ?? round(($modalData->total_value / 350000000) * 100, 1) }}%) &rarr; <strong>Skor: {{ $modalData->score_omset ?? 0 }}</strong> (Porsi 60%)</div>
                                    <div class="text-primary font-bold pt-0.5">&rArr; Pilar 2 = ({{ $modalData->score_strike_rate ?? $modalData->score_pilar_2 }} &times; 0.4) + ({{ $modalData->score_omset ?? 0 }} &times; 0.6) = <strong>{{ $modalData->score_pilar_2 }}</strong></div>
                                </div>
                            </div>

                            {{-- Step 3: Pilar 3 --}}
                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 space-y-1">
                                <div class="flex justify-between items-center text-amber-700 dark:text-amber-400 font-bold">
                                    <span>Langkah 3: Pilar 3 (Integritas Lapangan, Bobot 25%)</span>
                                    <span>Skor: {{ $modalData->score_pilar_3 }} / 100</span>
                                </div>
                                <div class="text-xs text-base-content/80 space-y-0.5">
                                    <div>&bull; Modal Awal: 100 Poin</div>
                                    <div>&bull; Potongan Penalti Pelanggaran:</div>
                                    <div class="pl-2 space-y-0.5 text-[0.625rem] text-base-content/70">
                                        @php $acDiv = max(1, $modalData->total_ac ?? 1); @endphp
                                        @if(($modalData->count_depo ?? 0) > 0)
                                            <div class="text-rose-600">- Depo: ({{ $modalData->count_depo }} / {{ $modalData->total_ac }}) &times; 100 &times; 3.0 = -{{ round(($modalData->count_depo / $acDiv) * 100 * 3.0, 1) }}%</div>
                                        @endif
                                        @if(($modalData->count_kembar ?? 0) > 0)
                                            <div class="text-rose-600">- Kembar: ({{ $modalData->count_kembar }} / {{ $modalData->total_ac }}) &times; 100 &times; 2.5 = -{{ round(($modalData->count_kembar / $acDiv) * 100 * 2.5, 1) }}%</div>
                                        @endif
                                        @if(($modalData->count_kilat_ec ?? 0) > 0)
                                            <div class="text-rose-600">- Order Kilat: ({{ $modalData->count_kilat_ec }} / {{ $modalData->total_ac }}) &times; 100 &times; 2.0 = -{{ round(($modalData->count_kilat_ec / $acDiv) * 100 * 2.0, 1) }}%</div>
                                        @endif
                                        @if(($modalData->count_radius ?? 0) > 0)
                                            <div class="text-amber-600">- Luar Radius: ({{ $modalData->count_radius }} / {{ $modalData->total_ac }}) &times; 100 &times; 1.0 = -{{ round(($modalData->count_radius / $acDiv) * 100 * 1.0, 1) }}%</div>
                                        @endif
                                        @if(($modalData->count_kilat_non_ec ?? 0) > 0)
                                            <div class="text-amber-600">- Kilat Non-EC: ({{ $modalData->count_kilat_non_ec }} / {{ $modalData->total_ac }}) &times; 100 &times; 0.5 = -{{ round(($modalData->count_kilat_non_ec / $acDiv) * 100 * 0.5, 1) }}%</div>
                                        @endif
                                        @if(($modalData->count_no_gps ?? 0) > 0)
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
                                <div class="text-xs text-base-content/80 space-y-0.5">
                                    <div>&bull; Durasi Kerja Lapangan: Rata-rata {{ $modalData->avg_work_hours ?? 0 }} Jam/Hari ({{ $modalData->days_ge_7h ?? 0 }} hari &ge;7j [100pt], {{ $modalData->days_5_to_7h ?? 0 }} hari 5-7j [50pt], {{ $modalData->days_lt_5h ?? 0 }} hari &lt;5j [0pt]) &rarr; <strong>Skor: {{ $modalData->score_work_duration ?? $modalData->score_store_ratio ?? $modalData->score_pilar_4 }}</strong> (Porsi 60%)</div>
                                    <div>&bull; Rata-rata Durasi Layanan: {{ $modalData->avg_minutes_per_store }} menit/toko &rarr; <strong>Skor: {{ $modalData->score_duration ?? 0 }}</strong> (Porsi 40%)</div>
                                    <div class="text-primary font-bold pt-0.5">&rArr; Pilar 4 = ({{ $modalData->score_work_duration ?? $modalData->score_store_ratio ?? $modalData->score_pilar_4 }} &times; 0.6) + ({{ $modalData->score_duration ?? 0 }} &times; 0.4) = <strong>{{ $modalData->score_pilar_4 }}</strong></div>
                                </div>
                            </div>

                            {{-- Step 5: Skor Total Akhir --}}
                            <div class="bg-gradient-to-r from-primary/10 via-emerald-500/10 to-primary/10 p-3 rounded-lg border border-primary/20 space-y-1">
                                <div class="font-bold text-xs text-primary">Langkah 5: Perhitungan Skor Akhir &amp; Penentuan Grade</div>
                                <div class="text-xs text-base-content/80 font-mono">
                                    Total = ({{ $modalData->score_pilar_1 }} &times; 0.20) + ({{ $modalData->score_pilar_2 }} &times; 0.40) + ({{ $modalData->score_pilar_3 }} &times; 0.25) + ({{ $modalData->score_pilar_4 }} &times; 0.15)<br>
                                    Total = {{ round($modalData->score_pilar_1 * 0.20, 2) }} + {{ round($modalData->score_pilar_2 * 0.40, 2) }} + {{ round($modalData->score_pilar_3 * 0.25, 2) }} + {{ round($modalData->score_pilar_4 * 0.15, 2) }} = <span class="font-bold text-emerald-600 text-xs">{{ $modalData->skor_total }}</span>
                                </div>
                                <div class="text-xs font-bold mt-1">
                                    &rArr; Hasil Akhir: <span class="badge badge-sm font-extrabold {{ $modalData->grade === 'A' ? 'badge-success text-white' : ($modalData->grade === 'B' ? 'badge-info text-white' : ($modalData->grade === 'C' ? 'badge-warning text-warning-content' : 'badge-error text-white')) }}">Grade {{ $modalData->grade }}</span> ({{ $modalData->grade_label }})
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Analisa Kekuatan & Area Perbaikan Otomatis Sistem --}}
                    <div id="raport-strengths-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3 print-avoid-break">
                        {{-- Strengths --}}
                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-3.5 print-strength-box print:bg-emerald-50/50 print:border-emerald-300">
                            <h5 class="font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5 mb-2 text-xs uppercase tracking-wider">
                                <x-heroicon-s-sparkles class="w-4 h-4 text-emerald-600" />
                                <span>Kekuatan Utama (Strengths)</span>
                            </h5>
                            <ul class="space-y-1.5 text-xs text-base-content/80">
                                @foreach($modalData->strengths as $str)
                                    <li class="flex items-start gap-1.5">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" />
                                        <span>{{ $str }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Weaknesses / Improvement Areas --}}
                        <div class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3.5 print-weakness-box print:bg-rose-50/50 print:border-rose-300">
                            <h5 class="font-bold text-rose-800 dark:text-rose-300 flex items-center gap-1.5 mb-2 text-xs uppercase tracking-wider">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-rose-600" />
                                <span>Area Perbaikan (Improvement)</span>
                            </h5>
                            <ul class="space-y-1.5 text-xs text-base-content/80">
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
                    <div id="raport-recommendation-box" class="bg-base-200/70 border border-base-300 rounded-xl p-4 print-avoid-break print:bg-slate-50 print:border-slate-300">
                        <h5 class="font-bold text-base-content flex items-center gap-1.5 mb-1 text-xs uppercase tracking-wider">
                            <x-heroicon-s-clipboard-document-check class="w-4 h-4 text-primary" />
                            <span>Rekomendasi Tindak Lanjut Supervisor (Action Plan)</span>
                        </h5>
                        <p class="text-xs text-base-content/80 leading-relaxed">
                            {{ $modalData->spv_recommendation }}
                        </p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-3 sm:p-4 bg-base-200/50 border-t border-base-300 flex items-center justify-between gap-2 shrink-0 print:hidden">
                    <span class="text-[0.625rem] text-base-content/50">
                        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                            @click="printRaport()" 
                            class="btn btn-sm btn-outline btn-neutral rounded-xl gap-1.5 text-xs font-semibold cursor-pointer">
                            <x-heroicon-m-printer class="w-4 h-4" />
                            <span>Cetak Rapor</span>
                        </button>

                        <button type="button" 
                            wire:click="closeModal" 
                            class="btn btn-sm btn-primary rounded-xl px-5 text-xs font-semibold cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- DOKUMEN CETAK FORMAL KHUSUS (LEMBAR RAPOR RESMI A4) --}}
        <div id="raport-print-document" 
                 data-title="Rapor_SE_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $modalData->salesman_code ?? 'SE') }}_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $modalData->salesman_name ?? 'Sales') }}_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $modalData->period_label ?? '') }}"
                 class="hidden print:block raport-doc">
                
                {{-- KOP DOKUMEN RESMI --}}
                <div class="doc-header">
                    <table class="doc-header-table">
                        <tr>
                            <td style="width: 76%;">
                                <div class="company-title">PT. INTIM HARMONIS FOODS &bull; SISTEM INFORMASI SALES OPERATION</div>
                                <h1 class="doc-main-title">RAPOR EVALUASI KINERJA KUNJUNGAN SALES EXECUTIVE</h1>
                                <p class="doc-sub-title">Scorecard Evaluasi Kinerja, Efektivitas Penjualan &amp; Kepatuhan Integritas Lapangan (PJP)</p>
                            </td>
                            <td style="width: 24%; text-align: right;">
                                <div class="doc-badge-box">
                                    <div class="doc-badge-title">PERIODE EVALUASI</div>
                                    <div class="doc-badge-val">{{ $modalData->period_label }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- TABEL IDENTITAS & ADMINISTRASI SALES EXECUTIVE --}}
                <div class="section-title">I. DATA IDENTITAS &amp; PENEMPATAN SALES EXECUTIVE</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nama Sales Executive</td>
                        <td class="info-val">{{ $modalData->salesman_name }}</td>
                        <td class="info-label">Nama Distributor</td>
                        <td class="info-val">{{ $modalData->distributor_name }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Kode Salesman (NIK/ID)</td>
                        <td class="info-val font-mono">{{ $modalData->salesman_code }}</td>
                        <td class="info-label">Kode Distributor</td>
                        <td class="info-val font-mono">{{ $modalData->distributor_code }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Area Operasional</td>
                        <td class="info-val">{{ $modalData->area_name ?? $modalData->area_code }} ({{ $modalData->area_code }})</td>
                        <td class="info-label">Regional</td>
                        <td class="info-val">{{ $modalData->region_name ?? $modalData->region_code }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Peringkat Tim</td>
                        <td class="info-val font-bold" style="color: #0f172a;">
                            Peringkat #{{ $modalData->rank }} dari {{ $metrics['total_se'] ?? 0 }} SE di Area
                        </td>
                        <td class="info-label">Hari Kerja Aktif</td>
                        <td class="info-val">{{ $modalData->hari_aktif }} Hari (Target: {{ $modalData->target_hari_kerja }} Hari)</td>
                    </tr>
                </table>

                {{-- BANNER RINGKASAN HASIL AKHIR & GRADE MUTU --}}
                <div class="grade-banner avoid-break">
                    <table class="grade-banner-table">
                        <tr>
                            <td style="width: 18%; text-align: center; border-right: 1.5px solid #cbd5e1; padding-right: 8px;">
                                <span style="font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; display: block;">PREDIKAT MUTU</span>
                                <span style="font-size: 20pt; font-weight: 900; line-height: 1; color: #0f172a; display: block; margin-top: 1px;">
                                    {{ $modalData->grade }}
                                </span>
                            </td>
                            <td style="width: 54%; padding-left: 10px;">
                                <span style="font-size: 7pt; font-weight: 700; color: #64748b; text-transform: uppercase;">STATUS EVALUASI KINERJA:</span>
                                <div style="font-size: 9.5pt; font-weight: 800; color: #0f172a;">
                                    {{ $modalData->grade_label }}
                                </div>
                                <span style="font-size: 6.8pt; color: #64748b;">
                                    Verifikasi objektif 4 pilar audit: Produktivitas, Efektivitas Penjualan, SOP Integritas Lapangan, &amp; Waktu Kerja.
                                </span>
                            </td>
                            <td style="width: 28%; text-align: right;">
                                <span style="font-size: 7pt; font-weight: 700; color: #64748b; text-transform: uppercase; display: block;">TOTAL SKOR AKHIR</span>
                                <span class="font-mono" style="font-size: 17pt; font-weight: 900; color: #0f172a;">
                                    {{ number_format($modalData->skor_total, 1) }}
                                </span>
                                <span style="font-size: 7.5pt; font-weight: 600; color: #64748b;">/ 100.0</span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- TABEL EVALUASI 4 PILAR KINERJA RESMI --}}
                <div class="section-title">II. RINCIAN PENILAIAN 4 PILAR KINERJA UTAMA</div>
                <table class="table-eval avoid-break">
                    <thead>
                        <tr>
                            <th style="width: 4%;">NO</th>
                            <th style="width: 35%;">PILAR &amp; INDIKATOR PARAMETER EVALUASI</th>
                            <th style="width: 20%;">TARGET / STANDAR</th>
                            <th style="width: 20%;">REALISASI PENCAPAIAN</th>
                            <th style="width: 7%;">BOBOT</th>
                            <th style="width: 7%;">SKOR</th>
                            <th style="width: 7%;">KONTRIBUSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- PILAR 1 --}}
                        <tr class="pillar-header-row">
                            <td class="text-center font-bold">1</td>
                            <td colspan="3">PILAR 1: PRODUKTIVITAS KUNJUNGAN LAPANGAN</td>
                            <td class="text-center font-bold">20%</td>
                            <td class="text-center font-bold font-mono">{{ number_format($modalData->score_pilar_1, 1) }}</td>
                            <td class="text-right font-bold font-mono">{{ number_format($modalData->score_pilar_1 * 0.20, 2) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Kepatuhan Rencana Kunjungan (PC vs AC)</td>
                            <td>Target PJP: {{ number_format($modalData->total_pc) }} Toko</td>
                            <td>Realisasi: {{ number_format($modalData->total_ac) }} Toko ({{ $modalData->pct_ac_pc }}%)</td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">80%</td>
                            <td class="text-center font-mono">{{ number_format($modalData->score_ac_pc ?? $modalData->score_pilar_1, 1) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Kedisiplinan Kehadiran &amp; Hari Kerja Aktif</td>
                            <td>Target: {{ $modalData->target_hari_kerja }} Hari Kerja</td>
                            <td>Realisasi: {{ $modalData->hari_aktif }} Hari ({{ round(($modalData->hari_aktif / max(1, $modalData->target_hari_kerja)) * 100, 1) }}%)</td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">20%</td>
                            <td class="text-center font-mono">{{ number_format($modalData->score_hari_aktif ?? 0, 1) }}</td>
                            <td></td>
                        </tr>

                        {{-- PILAR 2 --}}
                        <tr class="pillar-header-row">
                            <td class="text-center font-bold">2</td>
                            <td colspan="3">PILAR 2: EFEKTIVITAS PENJUALAN &amp; DISTRIBUSI</td>
                            <td class="text-center font-bold">40%</td>
                            <td class="text-center font-bold font-mono">{{ number_format($modalData->score_pilar_2, 1) }}</td>
                            <td class="text-right font-bold font-mono">{{ number_format($modalData->score_pilar_2 * 0.40, 2) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Rasio Transaksi Sukses (Strike Rate EC/AC)</td>
                            <td>Standar: &ge; 50% Transaksi</td>
                            <td>EC: {{ number_format($modalData->total_ec) }} Toko ({{ $modalData->strike_rate }}%)</td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">40%</td>
                            <td class="text-center font-mono">{{ number_format($modalData->score_strike_rate ?? $modalData->score_pilar_2, 1) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Pencapaian Omset Penjualan vs Target</td>
                            <td>Target: Rp {{ number_format($modalData->target_omset ?? 350000000) }}</td>
                            <td>Rp {{ number_format($modalData->total_value) }} ({{ $modalData->pct_omset ?? round(($modalData->total_value / 350000000) * 100, 1) }}% Target)</td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">60%</td>
                            <td class="text-center font-mono">{{ number_format($modalData->score_omset ?? 0, 1) }}</td>
                            <td></td>
                        </tr>

                        {{-- PILAR 3 --}}
                        <tr class="pillar-header-row">
                            <td class="text-center font-bold">3</td>
                            <td colspan="3">PILAR 3: KEPATUHAN SOP &amp; INTEGRITAS LAPANGAN</td>
                            <td class="text-center font-bold">25%</td>
                            <td class="text-center font-bold font-mono">{{ number_format($modalData->score_pilar_3, 1) }}</td>
                            <td class="text-right font-bold font-mono">{{ number_format($modalData->score_pilar_3 * 0.25, 2) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Check-in Titik Depo / Distributor</td>
                            <td>0 Kasus Pelanggaran (Toleransi 0)</td>
                            <td class="{{ $modalData->count_depo > 0 ? 'font-bold' : '' }}" style="{{ $modalData->count_depo > 0 ? 'color: #991b1b;' : 'color: #065f46;' }}">
                                {{ $modalData->count_depo }} Kasus Terdeteksi
                            </td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">Bobot 3x</td>
                            <td class="text-center font-mono" colspan="2" rowspan="4" style="background: #f8fafc; vertical-align: middle;">
                                <span style="font-size: 6.8pt; color: #64748b; display: block;">Tingkat Integritas:</span>
                                <span class="font-bold" style="color: #065f46; font-size: 8.5pt;">{{ $modalData->clean_rate ?? 100 }}%</span>
                                <span style="font-size: 6.5pt; color: #64748b; display: block; margin-top: 1px;">(Penalti: -{{ $modalData->penalty_rate ?? 0 }}%)</span>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Check-in Titik Koordinat Kembar (Non-Pasar)</td>
                            <td>0 Kasus Pelanggaran (Toleransi 0)</td>
                            <td class="{{ $modalData->count_kembar > 0 ? 'font-bold' : '' }}" style="{{ $modalData->count_kembar > 0 ? 'color: #991b1b;' : 'color: #065f46;' }}">
                                {{ $modalData->count_kembar }} Kasus Terdeteksi
                            </td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">Bobot 2.5x</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Kunjungan di Luar Radius Master Toko (>50m)</td>
                            <td>0 Kasus (Maks toleransi radius GPS)</td>
                            <td style="{{ $modalData->count_radius > 0 ? 'color: #b45309;' : 'color: #065f46;' }}">
                                {{ $modalData->count_radius }} Toko di Luar Radius
                            </td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">Bobot 1x</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Kunjungan Kilat Anomali (< 2 Menit &amp; Toko Buka)</td>
                            <td>Kunjungan wajar &ge; 2 Menit (Toko buka)</td>
                            <td style="{{ ($modalData->count_kilat_ec ?? 0) > 0 ? 'color: #991b1b;' : 'color: #065f46;' }}">
                                {{ $modalData->count_kilat_ec ?? 0 }} Order Kilat &bull; {{ $modalData->count_kilat_non_ec ?? 0 }} Non-EC
                            </td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">Bobot 2x/0.5x</td>
                        </tr>

                        {{-- PILAR 4 --}}
                        <tr class="pillar-header-row">
                            <td class="text-center font-bold">4</td>
                            <td colspan="3">PILAR 4: MANAJEMEN WAKTU &amp; ALOKASI JAM KERJA</td>
                            <td class="text-center font-bold">15%</td>
                            <td class="text-center font-bold font-mono">{{ number_format($modalData->score_pilar_4, 1) }}</td>
                            <td class="text-right font-bold font-mono">{{ number_format($modalData->score_pilar_4 * 0.15, 2) }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Durasi Kerja Lapangan (Awal Cekin s/d Akhir Cekout)</td>
                            <td>Standar: &ge; 7 Jam / Hari Kerja</td>
                            <td>Rata-rata: {{ $modalData->avg_work_hours ?? 0 }} Jam/Hari (&ge;7j: {{ $modalData->days_ge_7h ?? 0 }}h, 5-7j: {{ $modalData->days_5_to_7h ?? 0 }}h, &lt;5j: {{ $modalData->days_lt_5h ?? 0 }}h)</td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">60%</td>
                            <td class="text-center font-mono">{{ number_format($modalData->score_work_duration ?? $modalData->score_store_ratio ?? $modalData->score_pilar_4, 1) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>&bull; Rata-rata Durasi Layanan per Kunjungan Toko</td>
                            <td>Standar Layanan: 7 &ndash; 20 Menit / Toko</td>
                            <td>{{ $modalData->avg_minutes_per_store }} Menit / Kunjungan Toko</td>
                            <td class="text-center" style="font-size: 6.8pt; color: #64748b;">40%</td>
                            <td class="text-center font-mono">{{ number_format($modalData->score_duration ?? 0, 1) }}</td>
                            <td></td>
                        </tr>

                        {{-- TOTAL REKAPITULASI --}}
                        <tr class="total-row">
                            <td colspan="4" class="text-right" style="padding-right: 10px; letter-spacing: 0.5px;">
                                TOTAL SKOR AKHIR RAPOR KINERJA SE (SKALA 0 - 100):
                            </td>
                            <td class="text-center font-bold">100%</td>
                            <td class="text-center font-mono" style="font-size: 9.5pt;">{{ number_format($modalData->skor_total, 1) }}</td>
                            <td class="text-right font-mono" style="font-size: 9.5pt;">{{ number_format($modalData->skor_total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- EVALUASI KUALITATIF: KEKUATAN & PERBAIKAN --}}
                <div class="section-title avoid-break">III. EVALUASI KUALITATIF &amp; AREA PENGEMBANGAN</div>
                <table class="eval-grid avoid-break">
                    <tr>
                        <td>
                            <div class="eval-box-header strength">&check; KEKUATAN UTAMA (STRENGTHS &amp; BEST PRACTICES)</div>
                            <ul class="eval-list">
                                @foreach($modalData->strengths as $str)
                                    <li>{{ $str }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <div class="eval-box-header weakness">&excl; AREA PERBAIKAN &amp; CATATAN PENGAWASAN (IMPROVEMENTS)</div>
                            <ul class="eval-list">
                                @foreach($modalData->weaknesses as $weak)
                                    <li>{{ $weak }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                </table>

                {{-- REKOMENDASI SUPERVISOR --}}
                <div class="action-plan-box avoid-break">
                    <div class="action-plan-title">IV. REKOMENDASI TINDAK LANJUT SUPERVISOR (ACTION PLAN)</div>
                    <div style="font-style: italic; color: #1f2937; line-height: 1.35;">
                        &ldquo;{{ $modalData->spv_recommendation }}&rdquo;
                    </div>
                </div>

                {{-- LEMBAR PENGESAHAN / TANDA TANGAN FORMAL --}}
                <div class="avoid-break">
                    <div style="font-size: 7.2pt; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 3px;">
                        V. LEMBAR PENGESAHAN DOKUMEN EVALUASI KINERJA RESMI
                    </div>
                    <table class="sign-table">
                        <tr>
                            <td>
                                <div class="sign-title">Dibuat &amp; Diterima Oleh (SE):</div>
                                <div class="sign-name">{{ $modalData->salesman_name }}</div>
                                <div class="sign-role">Sales Executive (NIK: {{ $modalData->salesman_code }})</div>
                                <div style="font-size: 6.8pt; color: #94a3b8; margin-top: 2px;">Tanggal: ........................................</div>
                            </td>
                            <td>
                                <div class="sign-title">Diperiksa &amp; Dievaluasi Oleh:</div>
                                <div class="sign-name">( .................................................... )</div>
                                <div class="sign-role">Supervisor / Field Team Leader</div>
                                <div style="font-size: 6.8pt; color: #94a3b8; margin-top: 2px;">Tanggal: ........................................</div>
                            </td>
                            <td>
                                <div class="sign-title">Mengetahui &amp; Menyetujui:</div>
                                <div class="sign-name">( .................................................... )</div>
                                <div class="sign-role">Kepala Cabang / ASM</div>
                                <div style="font-size: 6.8pt; color: #94a3b8; margin-top: 2px;">Tanggal: ........................................</div>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- LEGAL FOOTER --}}
                <div class="doc-footer avoid-break">
                    <span>Sistem Informasi Sales Operation (SISO) &bull; PT. Intim Harmonis Foods</span>
                    <span>Dokumen Resmi Terverifikasi &bull; Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
                    <span>Halaman 1 dari 1</span>
                </div>

            </div>
    @endif

    {{-- Print Media Styles khusus untuk Lembar Rapor Formal SE --}}
    <style id="raport-print-style">
        @media print {
            :root {
                color-scheme: light !important;
            }

            @page {
                size: A4 portrait;
                margin: 6mm 8mm 6mm 8mm;
            }

            /* 1. Global Reset & Print Color Adjust */
            html, body {
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #111827 !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            *, *::before, *::after {
                -webkit-backdrop-filter: none !important;
                backdrop-filter: none !important;
            }

            /* 2. Unconstrain App Layout & Scroll Ancestors */
            .drawer,
            .drawer-content,
            main,
            main > div,
            [x-data] {
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                position: static !important;
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
                background-color: transparent !important;
            }

            /* 3. Hide ALL screen elements cleanly with separate standard rules */
            #raport-modal-wrapper {
                display: none !important;
                visibility: hidden !important;
            }

            #raport-modal-card {
                display: none !important;
                visibility: hidden !important;
            }

            #raport-screen-content {
                display: none !important;
                visibility: hidden !important;
            }

            #audit-logic-modal,
            #raport-history-modal {
                display: none !important;
                visibility: hidden !important;
            }

            #sidebar-drawer,
            .drawer-side,
            .drawer-overlay,
            nav,
            header,
            aside,
            footer,
            #teleport-modals,
            .no-print {
                display: none !important;
                visibility: hidden !important;
            }

            /* 4. Show ONLY the dedicated formal report document */
            #raport-print-document {
                display: block !important;
                visibility: visible !important;
                width: 100% !important;
                max-width: 194mm !important;
                margin: 0 auto !important;
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #111827 !important;
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
                box-shadow: none !important;
                border: none !important;
                opacity: 1 !important;
            }
        }

        /* Formal Document Print Typography & Table Styles */
        .raport-doc {
            width: 100%;
            max-width: 194mm;
            margin: 0 auto;
            background: #ffffff;
            color: #111827;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 7.5pt;
            line-height: 1.2;
        }

        /* Kop Dokumen Formal */
        .doc-header {
            border-bottom: 2px solid #000000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .doc-header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .doc-header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .company-title {
            font-size: 8.5pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #111827;
            text-transform: uppercase;
            margin: 0;
        }
        .doc-main-title {
            font-size: 10.5pt;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 1px;
            margin-bottom: 0;
        }
        .doc-sub-title {
            font-size: 6.8pt;
            color: #4b5563;
            margin-top: 1px;
            margin-bottom: 0;
        }
        .doc-badge-box {
            border: 1.5px solid #000000;
            padding: 2px 6px;
            text-align: center;
            background: #f8fafc;
            border-radius: 2px;
        }
        .doc-badge-title {
            font-size: 6pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #4b5563;
        }
        .doc-badge-val {
            font-size: 9pt;
            font-weight: 900;
            color: #000000;
            font-family: Arial, sans-serif;
        }

        /* Section Title */
        .section-title {
            font-size: 7.2pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            background: #f1f5f9;
            padding: 2px 5px;
            border-left: 3px solid #0f172a;
            margin-top: 4px;
            margin-bottom: 2px;
            letter-spacing: 0.2px;
        }

        /* Tabel Identitas */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 7.2pt;
        }
        .info-table td {
            padding: 1.5px 4px;
            border: 1px solid #cbd5e1;
        }
        .info-label {
            width: 17%;
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }
        .info-val {
            width: 33%;
            font-weight: 700;
            color: #0f172a;
        }

        /* Grade Summary Banner */
        .grade-banner {
            border: 1.5px solid #0f172a;
            background: #f8fafc;
            padding: 3px 6px;
            margin-bottom: 4px;
            border-radius: 2px;
        }
        .grade-banner-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grade-banner-table td {
            vertical-align: middle;
            padding: 0;
        }

        /* Tabel Evaluasi 4 Pilar */
        .table-eval {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
            margin-bottom: 4px;
        }
        .table-eval th {
            background: #1e293b;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            padding: 2.5px 3.5px;
            border: 1px solid #1e293b;
            font-size: 6.6pt;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .table-eval td {
            padding: 1.5px 3.5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .pillar-header-row td {
            background: #f1f5f9;
            font-weight: 800;
            color: #0f172a;
            border-top: 1.5px solid #94a3b8;
            padding-top: 2px;
            padding-bottom: 2px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: Consolas, "Courier New", monospace; }
        .font-bold { font-weight: 700; }

        /* Total Row */
        .total-row td {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 7.5pt;
            border: 1px solid #0f172a;
            padding: 3px 5px;
        }

        /* SWOT / Evaluasi Kualitatif */
        .eval-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .eval-grid td {
            width: 50%;
            vertical-align: top;
            padding: 3px 5px;
            border: 1px solid #cbd5e1;
            font-size: 6.8pt;
        }
        .eval-box-header {
            font-weight: 800;
            font-size: 7pt;
            text-transform: uppercase;
            margin-bottom: 2px;
            padding-bottom: 1px;
            border-bottom: 1px dashed #cbd5e1;
        }
        .eval-box-header.strength { color: #065f46; }
        .eval-box-header.weakness { color: #991b1b; }
        .eval-list {
            margin: 0;
            padding-left: 10px;
            color: #1f2937;
        }
        .eval-list li {
            margin-bottom: 1.5px;
        }

        /* Action Plan Box */
        .action-plan-box {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 3px 6px;
            font-size: 6.8pt;
            margin-bottom: 4px;
        }
        .action-plan-title {
            font-weight: 800;
            font-size: 7pt;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        /* Lembar Pengesahan / Signatures */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .sign-table td {
            width: 33.33%;
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            text-align: center;
            font-size: 6.8pt;
            vertical-align: top;
        }
        .sign-title {
            font-weight: 700;
            color: #475569;
            margin-bottom: 22px;
            text-transform: uppercase;
            font-size: 6.5pt;
        }
        .sign-name {
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
            font-size: 7.2pt;
        }
        .sign-role {
            font-size: 6.5pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* Footer Nota */
        .doc-footer {
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px solid #cbd5e1;
            font-size: 6pt;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }

        .avoid-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>

    {{-- Modal Edukasi Panduan, Rumus Nilai & Logika Temuan --}}
    @include('livewire.report.analisa-kunjungan-se.audit-logic-modal')

    {{-- Modal Histori Rekam Jejak 1 Tahun SE --}}
    @include('livewire.report.analisa-kunjungan-se.history-modal')

    {{-- Script Cetak Lembar Rapor SE via Iframe Terisolasi --}}
    <script>
        window.printRaportDocument = function() {
            const printDoc = document.getElementById('raport-print-document');
            if (!printDoc) {
                window.print();
                return;
            }

            const docTitle = printDoc.dataset.title || 'Rapor_Kunjungan_SE';

            let oldFrame = document.getElementById('raport-isolated-print-frame');
            if (oldFrame) {
                oldFrame.remove();
            }

            const printFrame = document.createElement('iframe');
            printFrame.id = 'raport-isolated-print-frame';
            printFrame.style.position = 'fixed';
            printFrame.style.right = '0';
            printFrame.style.bottom = '0';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = '0';
            printFrame.style.visibility = 'hidden';
            document.body.appendChild(printFrame);

            const styleEl = document.getElementById('raport-print-style');
            const styleContent = styleEl ? styleEl.innerHTML : '';

            const frameDoc = printFrame.contentWindow.document;
            frameDoc.open();
            frameDoc.write(`<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="light">
    <title>${docTitle}</title>
    <style>
        :root { color-scheme: light !important; }
        @page {
            size: A4 portrait;
            margin: 6mm 8mm 6mm 8mm;
        }
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-backdrop-filter: none !important;
            backdrop-filter: none !important;
        }
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #111827 !important;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        #raport-print-document {
            display: block !important;
            visibility: visible !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            opacity: 1 !important;
        }
        ${styleContent}
    </style>
</head>
<body style="background: #ffffff !important; background-color: #ffffff !important;">
    ${printDoc.outerHTML}
</body>
</html>`);
            frameDoc.close();

            setTimeout(() => {
                const innerDocEl = frameDoc.getElementById('raport-print-document');
                if (innerDocEl) {
                    innerDocEl.classList.remove('hidden');
                    innerDocEl.style.display = 'block';
                }
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                setTimeout(() => {
                    printFrame.remove();
                }, 3000);
            }, 250);
        };
    </script>
</div>
