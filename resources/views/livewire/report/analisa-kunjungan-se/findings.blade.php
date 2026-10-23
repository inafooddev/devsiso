<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('se_findings_show_kpi') !== null ? localStorage.getItem('se_findings_show_kpi') === 'true' : true,
         toggleKpi() {
             this.showKpi = !this.showKpi;
             localStorage.setItem('se_findings_show_kpi', this.showKpi);
         },
         showAuditModal: false
     }">
    <x-slot name="title">Temuan Anomali Kunjungan SE</x-slot>

    {{-- Standar Tab Menu --}}
    @php
        $tabParams = array_filter([
            'region' => $appliedRegion ?: $selectedRegion,
            'area' => $appliedArea ?: $selectedArea,
            'distributor' => $appliedDistributor ?: $selectedDistributor,
            'salesman' => $appliedSalesman ?: $selectedSalesman,
            'startDate' => $appliedStartDate ?: $startDate,
            'endDate' => $appliedEndDate ?: $endDate,
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
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.findings', $tabParams)" :navigate="true">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block mr-1 text-warning" /> Temuan
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.tracking', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-check class="w-4 h-4 inline-block mr-1 text-primary" /> Tracking Toko
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.raport', $tabParams)" :navigate="true">
            <x-heroicon-o-academic-cap class="w-4 h-4 inline-block mr-1 text-emerald-500" /> Raport SE
        </x-ui.tab-item>

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                @if($appliedRegion && $appliedArea)
                    <span class="badge badge-warning text-warning-content font-bold text-xs gap-1 py-2.5 px-3 shadow-2xs">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                        {{ number_format($metrics->total_findings) }} Temuan
                    </span>
                @endif

                <button type="button" 
                    @click="$dispatch('open-audit-modal', {tab: 'anomalies'}); showAuditModal = true" 
                    class="btn btn-xs sm:btn-sm btn-ghost border border-base-300 rounded-xl gap-1.5 text-base-content/75 hover:text-primary hover:border-primary font-semibold shadow-2xs cursor-pointer select-none"
                    title="Pelajari Aturan & Logika Temuan Kunjungan (Titik Kembar, Depo, Geofence, Durasi)">
                    <x-heroicon-m-information-circle class="w-4 h-4 text-info" />
                    <span class="text-xs hidden md:inline">Logika Temuan</span>
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
        {{-- Card 1: Total Temuan --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-amber-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Total Temuan Anomali</span>
                <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                    <x-heroicon-s-shield-exclamation class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-base-content">
                    {{ number_format($metrics->total_findings) }}
                </span>
                <span class="text-xs font-semibold text-base-content/50">Insiden Kunjungan</span>
            </div>
            <div class="text-[0.625rem] text-base-content/60 mt-1">
                Data terdeteksi dari hasil audit rute & GPS
            </div>
        </div>

        {{-- Card 2: Kritis / High Severity --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-rose-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-rose-600 uppercase">
                <span>Kritis (High Severity)</span>
                <div class="w-7 h-7 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-600 shrink-0">
                    <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-rose-600">
                    {{ number_format($metrics->high_count) }}
                </span>
                <span class="badge badge-error badge-xs text-white font-bold">Prioritas Audit</span>
            </div>
            <div class="text-[0.625rem] text-base-content/60 mt-1">
                Titik Depo ({{ $metrics->type_counts['at_distributor'] }}), Kembar ({{ $metrics->type_counts['duplicate_coord'] }})
            </div>
        </div>

        {{-- Card 3: Sedang / Medium Severity --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-amber-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-amber-600 uppercase">
                <span>Sedang (Medium Severity)</span>
                <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                    <x-heroicon-s-clock class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-amber-600">
                    {{ number_format($metrics->medium_count) }}
                </span>
                <span class="badge badge-warning badge-xs text-warning-content font-bold">Kepatuhan</span>
            </div>
            <div class="text-[0.625rem] text-base-content/60 mt-1">
                Luar Radius ({{ $metrics->type_counts['out_of_radius'] }}), Kunjungan Kilat ({{ $metrics->type_counts['quick_visit'] }})
            </div>
        </div>

        {{-- Card 4: Top Salesman Pelanggar --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-sky-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-sky-600 uppercase">
                <span>SE Temuan Terbanyak</span>
                <div class="w-7 h-7 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-600 shrink-0">
                    <x-heroicon-s-user-group class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-1">
                @if($metrics->top_salesmen->isNotEmpty())
                    @php $topOne = $metrics->top_salesmen->first(); @endphp
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-bold text-base-content truncate max-w-[140px]" title="{{ $topOne->salesman_name }}">
                            {{ $topOne->salesman_name }}
                        </span>
                        <span class="text-xs font-mono font-bold text-rose-600">
                            {{ $topOne->total }} Kasus
                        </span>
                    </div>
                    <div class="text-[0.625rem] text-base-content/60 mt-0.5">
                        {{ $topOne->high }} Kritis &bull; {{ $topOne->medium }} Sedang
                    </div>
                @else
                    <div class="text-xs text-base-content/40 italic py-1">Tidak ada data temuan</div>
                @endif
            </div>
            <div class="text-[0.625rem] text-base-content/50 border-t border-base-200 pt-1 mt-1 flex justify-between">
                <span>Total SE Terlibat:</span>
                <span class="font-bold">{{ $metrics->top_salesmen->count() }} Orang</span>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar (Hanya Filter & Action Buttons agar hemat ruang layar) --}}
        <div class="p-2.5 sm:p-3 border-b border-base-300 shrink-0 bg-base-200/30">
            <div class="flex flex-wrap items-center justify-between gap-2 w-full">
                
                {{-- Filter Group: Modern Hierarchical Breadcrumb Capsule (Static Fixed Width) --}}
                <div class="flex flex-wrap items-center gap-2">
                    
                    {{-- 1. Kapsul Hierarki Wilayah (Hybrid: Region & Area Native + Distributor & SE Micro-Searchable Popover) --}}
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

                    {{-- 2. Kapsul Rentang Periode Tanggal (Lebar Statis) --}}
                    <div class="inline-flex items-center bg-base-100 rounded-xl border border-base-300 shadow-2xs divide-x divide-base-200/80 p-0.5 text-xs shrink-0">
                        <div class="flex items-center pl-2 pr-1.5 gap-1.5">
                            <x-heroicon-m-calendar-days class="w-3.5 h-3.5 text-primary/70 shrink-0" />
                            <input type="date" 
                                wire:model.live="startDate" 
                                max="{{ $endDate ?: date('Y-m-d') }}"
                                class="input input-xs bg-transparent border-none text-xs w-[95px] focus:outline-none p-0 text-center font-mono font-medium" 
                                title="Tanggal Mulai" />

                            <span class="text-xs font-semibold text-base-content/30 select-none">s/d</span>

                            <input type="date" 
                                wire:model.live="endDate" 
                                min="{{ $startDate ?: '' }}"
                                max="{{ date('Y-m-d') }}"
                                class="input input-xs bg-transparent border-none text-xs w-[95px] focus:outline-none p-0 text-center font-mono font-medium" 
                                title="Tanggal Selesai" />
                        </div>

                        {{-- Preset Date Quick Dropdown --}}
                        <div class="dropdown dropdown-end">
                            <button type="button" tabindex="0" class="btn btn-xs btn-ghost rounded-lg px-1.5 text-base-content/60 hover:text-base-content" title="Pilihan Cepat Periode Tanggal">
                                <x-heroicon-m-chevron-down class="w-3.5 h-3.5" />
                            </button>
                            <ul tabindex="0" class="dropdown-content menu z-50 p-2 shadow-2xl bg-base-100 rounded-xl w-44 border border-base-200 text-xs">
                                <li><button type="button" wire:click="setDatePreset('today')" class="py-1.5 font-medium">Hari Ini</button></li>
                                <li><button type="button" wire:click="setDatePreset('last_7_days')" class="py-1.5 font-medium">7 Hari Terakhir</button></li>
                                <li><button type="button" wire:click="setDatePreset('this_month')" class="py-1.5 font-medium">Bulan Ini</button></li>
                                <li><button type="button" wire:click="setDatePreset('last_30_days')" class="py-1.5 font-medium">30 Hari Terakhir</button></li>
                                <li><button type="button" wire:click="setDatePreset('last_month')" class="py-1.5 font-medium">Bulan Lalu</button></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Action Group: Search & Buttons --}}
                <div class="flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0">
                    {{-- Search Box --}}
                    <div class="relative w-full sm:w-auto">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Cari Toko / SE / Alamat..."
                               class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 text-xs pl-8 pr-2 w-full sm:w-44 focus:outline-none focus:border-primary" />
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40" />
                    </div>

                    {{-- Button Terapkan Filter --}}
                    <button type="button" 
                        wire:click="applyFilter" 
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-primary rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer">
                        <span wire:loading.remove wire:target="applyFilter" class="flex items-center gap-1">
                            <x-heroicon-m-funnel class="w-4 h-4" />
                            <span>Terapkan</span>
                        </span>
                        <span wire:loading wire:target="applyFilter" class="loading loading-spinner loading-xs"></span>
                    </button>

                    {{-- Button Reset Filter --}}
                    <button type="button" 
                        wire:click="resetFilters" 
                        class="btn btn-sm btn-ghost btn-square rounded-xl border border-base-300 hover:bg-base-200 text-base-content/70 hover:text-base-content" 
                        title="Reset Filter ke Default">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                    </button>

                    {{-- Button Export Excel --}}
                    @if($canExport)
                        <button type="button" 
                            wire:click="exportExcel" 
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1.5 text-xs font-semibold shadow-xs cursor-pointer"
                            title="Download Laporan Temuan Anomali SE ke format Excel (.xlsx)">
                            <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1.5">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span>Excel</span>
                            </span>
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Filter Chips & Severity Toggle --}}
        <div class="px-3 md:px-4 py-2 border-b border-base-300 bg-base-100 flex flex-wrap items-center justify-between gap-2 text-xs">
            {{-- Anomaly Type Chips --}}
            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto py-0.5">
                <span class="text-base-content/50 font-bold uppercase text-[0.625rem] mr-1">Tipe Anomali:</span>

                <button type="button" 
                    wire:click="setTypeFilter('all')"
                    class="btn btn-xs rounded-lg transition-all {{ $typeFilter === 'all' ? 'btn-neutral text-white font-bold shadow-2xs' : 'btn-ghost text-base-content/70 border border-base-300 hover:bg-base-200' }}">
                    Semua ({{ $metrics->total_findings }})
                </button>

                <button type="button" 
                    wire:click="setTypeFilter('at_distributor')"
                    class="btn btn-xs rounded-lg transition-all {{ $typeFilter === 'at_distributor' ? 'btn-error text-white font-bold shadow-2xs' : 'btn-ghost text-rose-600 border border-rose-300 hover:bg-rose-50' }}"
                    title="Kunjungan check-in di dalam radius 100m kantor distributor">
                    🏢 Titik Depo ({{ $metrics->type_counts['at_distributor'] }})
                </button>

                <button type="button" 
                    wire:click="setTypeFilter('duplicate_coord')"
                    class="btn btn-xs rounded-lg transition-all {{ $typeFilter === 'duplicate_coord' ? 'btn-error text-white font-bold shadow-2xs' : 'btn-ghost text-rose-600 border border-rose-300 hover:bg-rose-50' }}"
                    title="Titik GPS identik/kembar (toleransi ≤ 3m) untuk toko yang berbeda di hari yang sama">
                    📑 Koordinat Kembar ({{ $metrics->type_counts['duplicate_coord'] }})
                </button>

                <button type="button" 
                    wire:click="setTypeFilter('quick_visit')"
                    class="btn btn-xs rounded-lg transition-all {{ $typeFilter === 'quick_visit' ? 'btn-warning text-warning-content font-bold shadow-2xs' : 'btn-ghost text-amber-600 border border-amber-300 hover:bg-amber-50' }}"
                    title="Durasi kunjungan sangat singkat (< 2 menit)">
                    ⏱️ Durasi Kilat ({{ $metrics->type_counts['quick_visit'] }})
                </button>

                <button type="button" 
                    wire:click="setTypeFilter('out_of_radius')"
                    class="btn btn-xs rounded-lg transition-all {{ $typeFilter === 'out_of_radius' ? 'btn-warning text-warning-content font-bold shadow-2xs' : 'btn-ghost text-amber-600 border border-amber-300 hover:bg-amber-50' }}"
                    title="Jarak check-in lebih dari 50m dari titik master toko">
                    📍 Luar Radius ({{ $metrics->type_counts['out_of_radius'] }})
                </button>
            </div>

            {{-- Severity Toggle Buttons --}}
            <div class="flex items-center gap-1 shrink-0">
                <span class="text-base-content/50 font-bold uppercase text-[0.625rem] mr-1">Tingkat:</span>
                <div class="join">
                    <button type="button" wire:click="setSeverityFilter('all')" class="join-item btn btn-xs {{ $severityFilter === 'all' ? 'btn-active font-bold' : 'btn-ghost' }}">Semua</button>
                    <button type="button" wire:click="setSeverityFilter('high')" class="join-item btn btn-xs {{ $severityFilter === 'high' ? 'btn-error text-white font-bold' : 'btn-ghost text-rose-600' }}">🔴 Kritis</button>
                    <button type="button" wire:click="setSeverityFilter('medium')" class="join-item btn btn-xs {{ $severityFilter === 'medium' ? 'btn-warning text-warning-content font-bold' : 'btn-ghost text-amber-600' }}">🟡 Sedang</button>
                </div>
            </div>
        </div>

        {{-- Tree Grid Container (Level 1: SE -> Level 2: Jenis Temuan -> Level 3: List Toko) --}}
        <div class="flex-1 min-h-0 overflow-auto bg-base-100 flex flex-col">
            @if(empty($appliedRegion) || empty($appliedArea))
                {{-- State: Filter Belum Diterapkan --}}
                <div class="h-full flex flex-col items-center justify-center p-8 text-center my-auto">
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 mb-3 shadow-inner">
                        <x-heroicon-o-funnel class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-base-content">Tentukan Filter Terlebih Dahulu</h3>
                    <p class="text-xs text-base-content/60 max-w-md mt-1">
                        Pilih Region dan Area pada filter di atas (Distributor opsional), lalu klik tombol <strong class="text-primary font-bold">"Filter"</strong> untuk memuat data temuan anomali kunjungan.
                    </p>
                </div>
            @elseif($seHierarchies->isEmpty())
                {{-- State: Data Kosong / Tidak Ada Temuan --}}
                <div class="h-full flex flex-col items-center justify-center p-8 text-center my-auto">
                    <div class="w-16 h-16 rounded-2xl bg-success/10 flex items-center justify-center text-success mb-3 shadow-inner">
                        <x-heroicon-o-check-badge class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-base-content">Tidak Ada Temuan Anomali</h3>
                    <p class="text-xs text-base-content/60 max-w-md mt-1">
                        Tidak ditemukan anomali kunjungan pada kriteria filter yang Anda pilih. Kunjungan salesman berjalan sesuai SOP & rute.
                    </p>
                </div>
            @else
                <div class="flex flex-col flex-1 min-h-0">
                    {{-- Toolbar Drill-down Header --}}
                    <div class="px-3.5 py-2.5 border-b border-base-300 bg-base-200/50 flex flex-wrap items-center justify-between gap-2 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-base-content/70">
                                Ditemukan <strong class="text-base-content font-bold">{{ $seHierarchies->total() }} Salesman (SE)</strong> dengan total <strong class="text-rose-600 font-bold font-mono">{{ number_format($totalFilteredFindings) }} kasus temuan</strong>
                            </span>
                        </div>

                        {{-- Action Buttons: Buka / Tutup Semua --}}
                        <div class="flex items-center gap-1.5">
                            <button type="button" 
                                    @click="$dispatch('findings-expand-all')" 
                                    class="btn btn-xs btn-ghost border border-base-300 hover:bg-base-200 rounded-lg text-xs gap-1 font-semibold text-base-content/80 cursor-pointer"
                                    title="Buka seluruh accordion SE dan Jenis Temuan">
                                <span>➕ Buka Semua</span>
                            </button>
                            <button type="button" 
                                    @click="$dispatch('findings-collapse-all')" 
                                    class="btn btn-xs btn-ghost border border-base-300 hover:bg-base-200 rounded-lg text-xs gap-1 font-semibold text-base-content/80 cursor-pointer"
                                    title="Tutup seluruh accordion kembali ke summary SE">
                                <span>➖ Tutup Semua</span>
                            </button>

                            @if($canExport)
                                <button type="button" 
                                        wire:click="exportExcel" 
                                        wire:loading.attr="disabled"
                                        class="btn btn-xs btn-success text-white rounded-lg text-xs gap-1 font-semibold cursor-pointer shadow-2xs ml-1"
                                        title="Download Laporan Temuan Anomali ke Excel (.xlsx)">
                                    <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1">
                                        <x-heroicon-m-arrow-down-tray class="w-3.5 h-3.5" />
                                        <span>Excel</span>
                                    </span>
                                    <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Tree List Content --}}
                    <div class="flex-1 overflow-y-auto divide-y divide-base-300">
                        @php $isSearching = !empty(trim($search)); @endphp
                        @foreach($seHierarchies as $se)
                            <div wire:key="se-card-{{ $se->hierarchy_key ?? $se->salesman_code }}-{{ $loop->index }}"
                                 x-data="{ openSe: {{ $isSearching ? 'true' : 'false' }} }"
                                 @findings-expand-all.window="openSe = true"
                                 @findings-collapse-all.window="openSe = false"
                                 class="group/se">
                                {{-- ======================================================== --}}
                                {{-- LEVEL 1: Baris Salesman (Summary per SE) --}}
                                {{-- ======================================================== --}}
                                <div @click="openSe = !openSe"
                                     class="p-3 md:p-3.5 bg-base-100 hover:bg-base-200/60 transition-colors cursor-pointer flex items-center justify-between gap-3 select-none">
                                    <div class="flex items-center gap-3 min-w-0">
                                        {{-- Chevron Icon Indicator --}}
                                        <div class="w-6 h-6 rounded-lg bg-base-200 flex items-center justify-center text-base-content/60 transition-transform duration-200 shrink-0"
                                             :class="{ 'rotate-90 text-primary bg-primary/10 shadow-2xs': openSe }">
                                            <x-heroicon-m-chevron-right class="w-4 h-4" />
                                        </div>

                                        {{-- Inisial Avatar --}}
                                        <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary font-bold flex items-center justify-center text-xs shrink-0 shadow-inner">
                                            {{ strtoupper(substr($se->salesman_name, 0, 2)) }}
                                        </div>

                                        {{-- Identitas SE & Info Agregat --}}
                                        <div class="min-w-0">
                                            <div class="font-bold text-sm text-base-content flex items-center gap-1.5 truncate">
                                                <span>{{ $se->salesman_name }}</span>
                                                <span class="text-[0.625rem] text-base-content/50 font-mono font-normal">({{ $se->salesman_code }})</span>
                                                @if(empty($appliedDistributor) && !empty($se->distributor_name))
                                                    <span class="badge badge-sm badge-outline text-[0.625rem] text-base-content/70 ml-1">
                                                        🏢 {{ $se->distributor_name }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-base-content/60 flex items-center flex-wrap gap-1 mt-0.5">
                                                <span class="font-medium text-base-content/80">{{ $se->unique_stores }} Toko Terdampak</span>
                                                <span class="text-base-content/30">&bull;</span>
                                                <span>{{ $se->categories->count() }} Kategori Anomali</span>
                                                @if($se->total_ec > 0)
                                                    <span class="text-base-content/30">&bull;</span>
                                                    <span class="text-emerald-600 font-semibold font-mono">{{ $se->total_ec }} EC (Rp {{ number_format($se->total_val_order, 0, ',', '.') }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Badges Temuan di Sisi Kanan --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($se->high_count > 0)
                                            <span class="badge badge-error badge-sm text-white font-bold gap-1 shadow-2xs">
                                                🔴 {{ $se->high_count }} Kritis
                                            </span>
                                        @endif
                                        @if($se->medium_count > 0)
                                            <span class="badge badge-warning badge-sm text-warning-content font-bold gap-1 shadow-2xs">
                                                🟡 {{ $se->medium_count }} Sedang
                                            </span>
                                        @endif
                                        <span class="badge badge-neutral badge-sm font-bold font-mono">
                                            {{ $se->total_findings }} Kasus
                                        </span>
                                    </div>
                                </div>

                                {{-- ======================================================== --}}
                                {{-- LEVEL 2: Container Jenis Temuan (Accordion Child) --}}
                                {{-- ======================================================== --}}
                                <div x-show="openSe" 
                                     x-cloak 
                                     class="bg-base-200/40 border-t border-base-200 pl-4 sm:pl-10 pr-2 sm:pr-4 py-2.5 space-y-2 border-l-4 border-l-primary/40">
                                    @foreach($se->categories as $cat)
                                        <div wire:key="cat-card-{{ $se->hierarchy_key ?? $se->salesman_code }}-{{ $cat->type }}"
                                             x-data="{ openCat: {{ $isSearching ? 'true' : 'false' }} }"
                                             @findings-expand-all.window="openCat = true"
                                             @findings-collapse-all.window="openCat = false"
                                             class="bg-base-100 rounded-xl border border-base-300 shadow-xs overflow-hidden">
                                            {{-- Baris Header Jenis Temuan --}}
                                            <div @click="openCat = !openCat"
                                                 class="p-2.5 px-3 hover:bg-base-200/50 transition-colors cursor-pointer flex items-center justify-between gap-2 select-none">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    {{-- Chevron Sub --}}
                                                    <div class="w-5 h-5 rounded flex items-center justify-center text-base-content/50 transition-transform duration-200 shrink-0"
                                                         :class="{ 'rotate-90 text-primary': openCat }">
                                                        <x-heroicon-m-chevron-right class="w-3.5 h-3.5" />
                                                    </div>

                                                    {{-- Indicator Dot --}}
                                                    @if($cat->severity === 'high')
                                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0 shadow-2xs"></span>
                                                    @elseif($cat->severity === 'medium')
                                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0 shadow-2xs"></span>
                                                    @else
                                                        <span class="w-2.5 h-2.5 rounded-full bg-slate-400 shrink-0 shadow-2xs"></span>
                                                    @endif

                                                    <div class="min-w-0">
                                                        <div class="font-bold text-xs text-base-content flex items-center gap-1.5">
                                                            <span>{{ $cat->label }}</span>
                                                            @if($cat->severity === 'high')
                                                                <span class="badge badge-error badge-xs text-white font-bold">Kritis</span>
                                                            @elseif($cat->severity === 'medium')
                                                                <span class="badge badge-warning badge-xs text-warning-content font-bold">Sedang</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-[0.625rem] text-base-content/50 truncate">
                                                            {{ $cat->description }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="badge badge-sm font-bold font-mono {{ $cat->severity === 'high' ? 'bg-rose-50 text-rose-700 border-rose-300' : 'bg-amber-50 text-amber-700 border-amber-300' }}">
                                                        {{ $cat->count }} Toko / Kunjungan
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- ======================================================== --}}
                                            {{-- LEVEL 3: Sub-Table List Toko Kunjungan --}}
                                            {{-- ======================================================== --}}
                                            <div x-show="openCat" 
                                                 x-cloak 
                                                 class="border-t border-base-200 bg-base-100">
                                                <div class="overflow-x-auto">
                                                    <table class="table table-xs table-zebra w-full text-left">
                                                        <thead>
                                                            <tr class="bg-base-200/80 text-[0.625rem] font-bold uppercase tracking-wider text-base-content/70">
                                                                <th class="w-8 text-center">No</th>
                                                                <th>Tanggal & Jam</th>
                                                                <th>Toko / Outlet</th>
                                                                <th>Bukti Temuan & Anomali</th>
                                                                <th>Jarak</th>
                                                                <th class="text-right">Order & Transaksi</th>
                                                                <th class="text-center w-28">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-xs divide-y divide-base-200/60">
                                                            @foreach($cat->items as $idx => $item)
                                                                <tr wire:key="finding-item-{{ $item->id }}-{{ $cat->type }}" class="hover:bg-primary/5 transition-colors">
                                                                    {{-- No --}}
                                                                    <td class="text-center font-mono text-base-content/40">
                                                                        {{ $idx + 1 }}
                                                                    </td>

                                                                    {{-- Tanggal & Jam --}}
                                                                    <td class="whitespace-nowrap">
                                                                        <div class="font-bold text-base-content">{{ $item->formatted_date }}</div>
                                                                        <div class="text-[0.625rem] text-base-content/60 font-mono mt-0.5 flex items-center gap-1">
                                                                            <x-heroicon-m-clock class="w-3 h-3 text-base-content/40" />
                                                                            <span>{{ $item->time_in }} - {{ $item->time_out }}</span>
                                                                            <span class="text-base-content/40">({{ $item->time_consume }})</span>
                                                                        </div>
                                                                    </td>

                                                                    {{-- Toko / Outlet --}}
                                                                    <td class="max-w-[220px]">
                                                                        <div class="font-bold text-base-content truncate" title="{{ $item->custname }}">
                                                                            {{ $item->custname }}
                                                                        </div>
                                                                        <div class="text-[0.625rem] text-base-content/50 truncate font-mono">
                                                                            #{{ $item->custno }} &bull; {{ $item->address ?: 'Alamat tidak terdata' }}
                                                                        </div>
                                                                    </td>

                                                                    {{-- Bukti / Detail Temuan --}}
                                                                    <td class="max-w-[280px]">
                                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium leading-relaxed {{ $cat->severity === 'high' ? 'text-rose-700 bg-rose-50 border border-rose-200' : 'text-amber-700 bg-amber-50 border border-amber-200' }}">
                                                                            {{ $item->current_anomaly_detail }}
                                                                        </span>
                                                                    </td>

                                                                    {{-- Jarak Lokasi --}}
                                                                    <td class="whitespace-nowrap text-xs">
                                                                        @if($item->dist_to_distributor !== null)
                                                                            <div class="text-rose-600 font-mono font-semibold" title="Jarak Kunjungan ke Kantor Depo">
                                                                                Depo: {{ number_format($item->dist_to_distributor, 0, ',', '.') }}m
                                                                            </div>
                                                                        @endif
                                                                        @if($item->dist_to_master !== null)
                                                                            <div class="text-amber-600 font-mono font-semibold" title="Jarak Kunjungan ke Titik Master Toko">
                                                                                Master: {{ number_format($item->dist_to_master, 0, ',', '.') }}m
                                                                            </div>
                                                                        @elseif(!$item->clean_master)
                                                                            <div class="text-rose-500 font-mono font-semibold text-[0.625rem]" title="Toko Tidak Memiliki Koordinat Master (0, 0)">
                                                                                Master: Kosong (0, 0)
                                                                            </div>
                                                                        @endif
                                                                    </td>

                                                                    {{-- Order & Transaksi --}}
                                                                    <td class="text-right whitespace-nowrap">
                                                                        @if($item->flag_ec === 'Y' || $item->val_order > 0)
                                                                            <div class="font-mono font-bold text-emerald-600">
                                                                                Rp {{ number_format($item->val_order, 0, ',', '.') }}
                                                                            </div>
                                                                            <div class="text-[0.625rem] text-base-content/60">
                                                                                <span class="badge badge-success badge-xs text-white font-bold">EC</span>
                                                                                <span class="font-mono ml-1">{{ $item->qty_order }} Qty</span>
                                                                            </div>
                                                                        @else
                                                                            <span class="text-base-content/40 text-xs">Non-EC (Rp 0)</span>
                                                                        @endif
                                                                    </td>

                                                                    {{-- Aksi --}}
                                                                    <td class="text-center whitespace-nowrap">
                                                                        <div class="flex items-center justify-center gap-1">
                                                                            @php
                                                                                $mapItem = [
                                                                                    'id' => $item->id,
                                                                                    'custname' => $item->custname,
                                                                                    'custno' => $item->custno,
                                                                                    'address' => $item->address,
                                                                                    'salesman_name' => $item->salesman_name,
                                                                                    'salesman_code' => $item->salesman_code,
                                                                                    'distributor_name' => $item->distributor_name,
                                                                                    'formatted_date' => $item->formatted_date,
                                                                                    'time_in' => $item->time_in,
                                                                                    'time_out' => $item->time_out,
                                                                                    'primary_severity' => $item->primary_severity,
                                                                                    'anomaly_types' => $item->anomaly_types,
                                                                                    'visit_lat' => $item->visit_lat,
                                                                                    'visit_lon' => $item->visit_lon,
                                                                                    'master_lat' => $item->master_lat,
                                                                                    'master_lon' => $item->master_lon,
                                                                                    'distributor_point' => $item->distributor_point,
                                                                                    'dist_to_distributor' => $item->dist_to_distributor,
                                                                                    'dist_to_master' => $item->dist_to_master,
                                                                                    'dist_master_to_distributor' => $item->dist_master_to_distributor ?? null,
                                                                                ];
                                                                            @endphp

                                                                            {{-- Tombol Buka Peta --}}
                                                                            <button type="button" 
                                                                                data-finding="{{ rawurlencode(json_encode($mapItem)) }}"
                                                                                @click="$dispatch('open-finding-map-modal', JSON.parse(decodeURIComponent($el.dataset.finding)))"
                                                                                class="btn btn-xs btn-ghost border border-base-300 hover:border-primary hover:bg-primary/10 rounded-lg text-primary text-xs font-semibold gap-1 cursor-pointer"
                                                                                title="Buka Peta Bukti Lokasi (Check-in vs Master Toko / Depo)">
                                                                                <x-heroicon-s-map-pin class="w-3.5 h-3.5" />
                                                                                <span>Peta</span>
                                                                            </button>

                                                                            {{-- Link Direct Detail Kunjungan --}}
                                                                            <a href="{{ route('report.analisa-kunjungan-se.index', [
                                                                                    'region' => $item->region_code ?? $appliedRegion,
                                                                                    'area' => $item->area_code ?? $appliedArea,
                                                                                    'distributor' => $item->distributor_code ?? $appliedDistributor,
                                                                                    'salesman' => $item->salesman_code,
                                                                                    'startDate' => $item->tanggal,
                                                                                    'endDate' => $item->tanggal,
                                                                                ]) }}" 
                                                                                class="btn btn-xs btn-ghost border border-base-300 hover:bg-base-200 rounded-lg text-base-content/70 text-xs p-1"
                                                                                title="Buka Halaman Detail Kunjungan SE untuk Tanggal Ini"
                                                                                wire:navigate>
                                                                                <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                                                            </a>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Tree Footer & Pagination per SE --}}
        @if(!$seHierarchies->isEmpty())
            <div class="p-3 border-t border-base-300 shrink-0 bg-base-100 flex flex-col sm:flex-row justify-between items-center gap-2">
                <div class="flex items-center gap-2 text-xs text-base-content/70">
                    <span>Menampilkan {{ $seHierarchies->firstItem() }} - {{ $seHierarchies->lastItem() }} dari {{ number_format($seHierarchies->total()) }} Salesman</span>
                    <span class="text-base-content/30">&bull;</span>
                    <span class="font-bold text-base-content">{{ number_format($totalFilteredFindings) }} Total Kasus Temuan</span>
                    <span class="text-base-content/30">&bull;</span>
                    <div class="flex items-center gap-1">
                        <span>Per halaman:</span>
                        <select wire:model.live="perPage" class="select select-xs select-bordered rounded-lg bg-base-100 border-base-300">
                            <option value="10">10 SE</option>
                            <option value="15">15 SE</option>
                            <option value="25">25 SE</option>
                            <option value="50">50 SE</option>
                        </select>
                    </div>
                </div>

                <div>
                    {{ $seHierarchies->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Map Modal Component (Decoupled Alpine Component) --}}
    <div x-data="findingMapModal()" 
         @open-finding-map-modal.window="openModal($event.detail)"
         @keydown.escape.window="closeMap()">
        <div class="modal" :class="{'modal-open': mapModalOpen}">
            <div class="modal-box w-11/12 max-w-5xl rounded-2xl p-0 overflow-hidden border border-base-300 shadow-2xl flex flex-col max-h-[92vh]">
                {{-- Modal Header --}}
                <div class="p-3.5 md:p-4 border-b border-base-300 bg-base-200/50 flex items-center justify-between shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base text-base-content">Peta Bukti Temuan Anomali</h3>
                            <template x-if="activeFinding && activeFinding.primary_severity === 'high'">
                                <span class="badge badge-error badge-xs text-white font-bold">🔴 Kritis</span>
                            </template>
                            <template x-if="activeFinding && activeFinding.primary_severity === 'medium'">
                                <span class="badge badge-warning badge-xs text-warning-content font-bold">🟡 Sedang</span>
                            </template>
                        </div>
                        <p class="text-xs text-base-content/70 mt-0.5 flex items-center flex-wrap gap-1">
                            <span x-text="activeFinding ? activeFinding.custname : ''" class="font-bold text-base-content"></span>
                            <span class="text-base-content/30">&bull;</span>
                            <span x-text="activeFinding ? activeFinding.salesman_name : ''"></span>
                            <template x-if="activeFinding && activeFinding.distributor_name">
                                <span class="badge badge-xs badge-outline text-[0.625rem] text-base-content/70" x-text="activeFinding.distributor_name"></span>
                            </template>
                            <span class="text-base-content/30">&bull;</span>
                            <span x-text="activeFinding ? activeFinding.formatted_date + ' (' + activeFinding.time_in + ')' : ''" class="font-mono text-base-content/60"></span>
                        </p>
                    </div>

                    <button type="button" @click="closeMap()" class="btn btn-sm btn-ghost btn-circle" title="Tutup Peta (Esc)">✕</button>
                </div>

                {{-- Map Container & Overlays --}}
                <div class="relative bg-slate-100 flex-1 min-h-[500px]">
                    {{-- Container murni untuk canvas MapLibre --}}
                    <div id="finding-map-container" wire:ignore class="w-full h-full" style="min-height: 500px; height: 500px;"></div>

                    {{-- Overlay Loading --}}
                    <div x-show="mapLoading" 
                         class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-base-100/75 backdrop-blur-xs text-slate-600 gap-2">
                        <span class="loading loading-spinner loading-md text-primary"></span>
                        <span class="text-xs font-semibold">Memuat peta & koordinat lokasi...</span>
                    </div>

                    {{-- Banner Peringatan jika Koordinat Kosong (No GPS) --}}
                    <div x-show="!mapLoading && noGpsDetected" 
                         class="absolute top-4 left-1/2 -translate-x-1/2 z-20 bg-amber-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-2 border border-amber-600 max-w-md text-center">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 shrink-0" />
                        <span>Kunjungan ini tidak memiliki data koordinat GPS yang valid (0, 0 atau kosong).</span>
                    </div>

                    {{-- Quick Action Floating Buttons (Fokus Toko / Rute Depo) --}}
                    <div x-show="!mapLoading && !noGpsDetected" class="absolute top-3 left-3 z-10 flex items-center gap-1.5 bg-base-100/90 backdrop-blur-md p-1.5 rounded-xl border border-base-300 shadow-md">
                        <button type="button" 
                                @click="focusStore()" 
                                class="btn btn-xs btn-primary rounded-lg gap-1 font-semibold"
                                title="Fokuskan kamera langsung ke titik toko/kunjungan">
                            <x-heroicon-s-map-pin class="w-3.5 h-3.5" />
                            <span>Fokus Toko</span>
                        </button>
                        <template x-if="hasDistributor">
                            <button type="button" 
                                    @click="focusDepoRoute()" 
                                    class="btn btn-xs btn-ghost border border-base-300 hover:bg-base-200 rounded-lg gap-1 font-semibold text-xs"
                                    title="Tampilkan rute dan jarak antara toko ke kantor depo">
                                <x-heroicon-s-building-office-2 class="w-3.5 h-3.5 text-indigo-600" />
                                <span>Lihat Depo</span>
                            </button>
                        </template>
                    </div>

                    {{-- Map Legend Overlay --}}
                    <div x-show="!mapLoading && !noGpsDetected" class="absolute bottom-4 left-4 bg-base-100/95 backdrop-blur-md p-2.5 rounded-xl border border-base-300 shadow-xl text-xs flex flex-col gap-1.5 z-10 max-w-xs pointer-events-auto">
                        <div class="font-bold text-[0.625rem] text-base-content/70 uppercase tracking-wider mb-0.5">Legenda Peta</div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500 border border-white shadow-xs shrink-0"></span>
                            <span class="font-medium text-slate-700 text-xs">Titik Kunjungan SE (Visit)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 border border-white shadow-xs shrink-0"></span>
                            <span class="font-medium text-slate-700 text-xs">Titik Master Outlet (Toko)</span>
                        </div>
                        <template x-if="hasDistributor">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 border border-white shadow-xs shrink-0"></span>
                                <span class="font-medium text-slate-700 text-xs">Kantor/Gudang Distributor</span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-3 bg-base-200/40 border-t border-base-300 flex items-center justify-between shrink-0">
                    <div class="text-xs text-base-content/80 flex items-center gap-3">
                        <template x-if="activeFinding && activeFinding.dist_to_distributor !== null">
                            <div class="flex items-center gap-1">
                                <span class="text-base-content/60">Jarak ke Depo:</span>
                                <strong class="font-mono text-rose-600 font-bold" x-text="Number(activeFinding.dist_to_distributor).toLocaleString('id-ID') + ' m'"></strong>
                            </div>
                        </template>
                        <template x-if="activeFinding && activeFinding.dist_to_master !== null">
                            <div class="flex items-center gap-1">
                                <span class="text-base-content/60">Jarak ke Master:</span>
                                <strong class="font-mono text-amber-600 font-bold" x-text="Number(activeFinding.dist_to_master).toLocaleString('id-ID') + ' m'"></strong>
                            </div>
                        </template>
                        <template x-if="activeFinding && (!activeFinding.master_lat || activeFinding.master_lat == 0)">
                            <div class="flex items-center gap-1">
                                <span class="badge badge-error badge-xs text-white font-bold py-1.5 px-2">Master GPS: Kosong (0, 0)</span>
                            </div>
                        </template>
                    </div>

                    <button type="button" class="btn btn-sm btn-neutral rounded-xl px-5" @click="closeMap()">
                        Tutup
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="closeMap()"></div>
        </div>
    </div>

    {{-- Map Modal Script --}}
    <script>
    function findingMapModal() {
        return {
            mapModalOpen: false,
            activeFinding: null,
            mapInstance: null,
            mapLoading: false,
            noGpsDetected: false,
            hasDistributor: false,
            visitMarker: null,
            storePoint: null,
            allBounds: null,

            openModal(item) {
                this.activeFinding = item;
                this.mapModalOpen = true;
                this.mapLoading = true;
                this.noGpsDetected = false;
                this.hasDistributor = false;

                this.$nextTick(() => {
                    this.initMap(item);
                });
            },

            closeMap() {
                this.mapModalOpen = false;
                this.mapLoading = false;
                this.activeFinding = null;
                this.destroyMap();
            },

            destroyMap() {
                if (this.mapInstance) {
                    try {
                        this.mapInstance.remove();
                    } catch (e) {
                        console.warn('Map remove warning:', e);
                    }
                    this.mapInstance = null;
                }
                this.visitMarker = null;
                this.storePoint = null;
                this.allBounds = null;
                const container = document.getElementById('finding-map-container');
                if (container) {
                    container.innerHTML = '';
                }
            },

            focusStore() {
                if (this.mapInstance && this.storePoint) {
                    this.mapInstance.flyTo({
                        center: this.storePoint,
                        zoom: 17.5,
                        essential: true
                    });
                    if (this.visitMarker) {
                        const popup = this.visitMarker.getPopup();
                        if (popup && !popup.isOpen()) {
                            this.visitMarker.togglePopup();
                        }
                    }
                }
            },

            focusDepoRoute() {
                if (this.mapInstance && this.allBounds && !this.allBounds.isEmpty()) {
                    this.mapInstance.fitBounds(this.allBounds, {
                        padding: 80,
                        maxZoom: 18,
                        essential: true
                    });
                }
            },

            initMap(item) {
                // Parsing koordinat aman (support string koma/titik, null, undefined)
                const parseCoord = (val) => {
                    if (val === null || val === undefined || val === '') return NaN;
                    const s = String(val).trim().replace(',', '.');
                    const n = parseFloat(s);
                    return isNaN(n) ? NaN : n;
                };

                // Memastikan library MapLibre GL termuat
                if (typeof maplibregl === 'undefined') {
                    if (!document.getElementById('maplibre-gl-script')) {
                        const script = document.createElement('script');
                        script.id = 'maplibre-gl-script';
                        script.src = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js';
                        script.onload = () => {
                            setTimeout(() => this.initMap(item), 100);
                        };
                        document.head.appendChild(script);

                        const css = document.createElement('link');
                        css.id = 'maplibre-gl-css';
                        css.rel = 'stylesheet';
                        css.href = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css';
                        document.head.appendChild(css);
                    } else {
                        setTimeout(() => this.initMap(item), 150);
                    }
                    return;
                }

                // Bersihkan peta sebelumnya secara total
                this.destroyMap();

                const container = document.getElementById('finding-map-container');
                if (!container) return;

                let vLat = parseCoord(item.visit_lat);
                let vLon = parseCoord(item.visit_lon);
                let mLat = parseCoord(item.master_lat);
                let mLon = parseCoord(item.master_lon);
                let dLat = (item.distributor_point && item.distributor_point.lat) ? parseCoord(item.distributor_point.lat) : NaN;
                let dLon = (item.distributor_point && item.distributor_point.lon) ? parseCoord(item.distributor_point.lon) : NaN;

                // Tangani kemungkinan lat/lon tertukar (lat > 90)
                if (Math.abs(vLat) > 90 && Math.abs(vLon) <= 90) { const t = vLat; vLat = vLon; vLon = t; }
                if (Math.abs(mLat) > 90 && Math.abs(mLon) <= 90) { const t = mLat; mLat = mLon; mLon = t; }
                if (Math.abs(dLat) > 90 && Math.abs(dLon) <= 90) { const t = dLat; dLat = dLon; dLon = t; }

                const hasVisit = !isNaN(vLat) && !isNaN(vLon) && Math.abs(vLat) > 0.000001 && Math.abs(vLon) > 0.000001 && vLat >= -90 && vLat <= 90 && vLon >= -180 && vLon <= 180;
                const hasMaster = !isNaN(mLat) && !isNaN(mLon) && Math.abs(mLat) > 0.000001 && Math.abs(mLon) > 0.000001 && mLat >= -90 && mLat <= 90 && mLon >= -180 && mLon <= 180;
                const hasDist = !isNaN(dLat) && !isNaN(dLon) && Math.abs(dLat) > 0.000001 && Math.abs(dLon) > 0.000001 && dLat >= -90 && dLat <= 90 && dLon >= -180 && dLon <= 180;

                this.hasDistributor = hasDist;

                if (!hasVisit && !hasMaster && !hasDist) {
                    this.noGpsDetected = true;
                    this.mapLoading = false;
                    return;
                }

                // Titik fokus kamera: prioritaskan visit, master, lalu depo
                const focusLon = hasVisit ? vLon : (hasMaster ? mLon : (hasDist ? dLon : 112.0));
                const focusLat = hasVisit ? vLat : (hasMaster ? mLat : (hasDist ? dLat : -7.8));
                this.storePoint = [focusLon, focusLat];

                try {
                    this.mapInstance = new maplibregl.Map({
                        container: 'finding-map-container',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: [focusLon, focusLat],
                        zoom: 17
                    });
                } catch (err) {
                    console.error('Failed to create MapLibre instance:', err);
                    this.mapLoading = false;
                    return;
                }

                this.mapInstance.addControl(new maplibregl.NavigationControl(), 'top-right');

                const bounds = new maplibregl.LngLatBounds();
                if (hasVisit) bounds.extend([vLon, vLat]);
                if (hasMaster) bounds.extend([mLon, mLat]);

                // Simpan allBounds (termasuk distributor) untuk navigasi rute depo
                this.allBounds = new maplibregl.LngLatBounds();
                if (hasVisit) this.allBounds.extend([vLon, vLat]);
                if (hasMaster) this.allBounds.extend([mLon, mLat]);
                if (hasDist) this.allBounds.extend([dLon, dLat]);

                // Marker 1: Titik Kunjungan SE (Merah)
                if (hasVisit) {
                    const custNameSafe = item.custname ? item.custname.replace(/</g, '&lt;').replace(/>/g, '&gt;') : 'Toko';
                    const seNameSafe = item.salesman_name ? item.salesman_name.replace(/</g, '&lt;').replace(/>/g, '&gt;') : '-';
                    const popupVisit = new maplibregl.Popup({ offset: 25, closeButton: false }).setHTML(
                        '<div style="font-family: sans-serif; font-size: 12px; color: #1e293b; padding: 4px;">' +
                        '<div style="font-weight: bold; color: #dc2626; margin-bottom: 2px;">📍 Titik Kunjungan SE<\/div>' +
                        '<div style="font-weight: bold; font-size: 13px;">' + custNameSafe + '<\/div>' +
                        '<div style="color: #64748b; font-family: monospace; font-size: 11px; margin-top: 2px;">' + vLat.toFixed(6) + ', ' + vLon.toFixed(6) + '<\/div>' +
                        '<div style="color: #475569; margin-top: 4px;">Jam: <strong>' + (item.time_in || '-') + '<\/strong> | SE: <strong>' + seNameSafe + '<\/strong><\/div>' +
                        '<\/div>'
                    );

                    this.visitMarker = new maplibregl.Marker({ color: '#ef4444' })
                        .setLngLat([vLon, vLat])
                        .setPopup(popupVisit)
                        .addTo(this.mapInstance);

                    // Auto-open popup kunjungan agar user langsung melihat toko yang sedang dibuka
                    this.visitMarker.togglePopup();
                }

                // Marker 2: Titik Master Outlet (Hijau)
                if (hasMaster) {
                    const custNameSafe = item.custname ? item.custname.replace(/</g, '&lt;').replace(/>/g, '&gt;') : 'Toko';
                    const distMasterStr = item.dist_to_master !== null 
                        ? '<div style="color: #475569; margin-top: 4px;">Jarak ke Visit: <strong style="color: #d97706;">' + Number(item.dist_to_master).toLocaleString('id-ID') + ' m<\/strong><\/div>' 
                        : '';
                    new maplibregl.Marker({ color: '#22c55e' })
                        .setLngLat([mLon, mLat])
                        .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(
                            '<div style="font-family: sans-serif; font-size: 12px; color: #1e293b; padding: 4px;">' +
                            '<div style="font-weight: bold; color: #16a34a; margin-bottom: 2px;">🏪 Titik Master Outlet<\/div>' +
                            '<div style="font-weight: bold; font-size: 13px;">' + custNameSafe + '<\/div>' +
                            '<div style="color: #64748b; font-family: monospace; font-size: 11px; margin-top: 2px;">' + mLat.toFixed(6) + ', ' + mLon.toFixed(6) + '<\/div>' +
                            distMasterStr +
                            '<\/div>'
                        ))
                        .addTo(this.mapInstance);
                }

                // Marker 3: Titik Kantor/Gudang Distributor (Ungu)
                if (hasDist) {
                    const distNameSafe = (item.distributor_point && item.distributor_point.name) 
                        ? String(item.distributor_point.name).replace(/</g, '&lt;').replace(/>/g, '&gt;') 
                        : 'Distributor';
                    const distDepoStr = item.dist_to_distributor !== null 
                        ? '<div style="color: #475569; margin-top: 4px;">Jarak ke Visit: <strong style="color: #dc2626;">' + Number(item.dist_to_distributor).toLocaleString('id-ID') + ' m<\/strong><\/div>' 
                        : '';
                    new maplibregl.Marker({ color: '#6366f1' })
                        .setLngLat([dLon, dLat])
                        .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(
                            '<div style="font-family: sans-serif; font-size: 12px; color: #1e293b; padding: 4px;">' +
                            '<div style="font-weight: bold; color: #4f46e5; margin-bottom: 2px;">🏢 Kantor/Gudang Distributor<\/div>' +
                            '<div style="font-weight: bold; font-size: 13px;">' + distNameSafe + '<\/div>' +
                            '<div style="color: #64748b; font-family: monospace; font-size: 11px; margin-top: 2px;">' + dLat.toFixed(6) + ', ' + dLon.toFixed(6) + '<\/div>' +
                            distDepoStr +
                            '<\/div>'
                        ))
                        .addTo(this.mapInstance);
                }

                // Sertakan titik distributor ke bounds default HANYA jika kasus anomali di depo atau jarak <= 300m
                const isAtDepo = item.anomaly_types && item.anomaly_types.includes('at_distributor');
                if (hasDist && (isAtDepo || (item.dist_to_distributor !== null && item.dist_to_distributor <= 300))) {
                    bounds.extend([dLon, dLat]);
                }

                const applyCameraFit = () => {
                    if (!this.mapInstance) return;
                    if (!bounds.isEmpty()) {
                        this.mapInstance.fitBounds(bounds, { padding: 80, maxZoom: 18, animate: false });
                    } else {
                        this.mapInstance.setCenter([focusLon, focusLat]);
                        this.mapInstance.setZoom(17);
                    }
                };

                applyCameraFit();

                const setupLayers = () => {
                    if (!this.mapInstance) return;
                    this.mapLoading = false;
                    applyCameraFit();

                    // Garis hubung Visit ke Master (Putus-putus Oranye)
                    if (hasMaster && hasVisit) {
                        try {
                            this.mapInstance.addSource('route-master', {
                                'type': 'geojson',
                                'data': {
                                    'type': 'Feature',
                                    'geometry': {
                                        'type': 'LineString',
                                        'coordinates': [
                                            [mLon, mLat],
                                            [vLon, vLat]
                                        ]
                                    }
                                }
                            });
                            this.mapInstance.addLayer({
                                'id': 'route-master',
                                'type': 'line',
                                'source': 'route-master',
                                'layout': { 'line-join': 'round', 'line-cap': 'round' },
                                'paint': {
                                    'line-color': '#f59e0b',
                                    'line-width': 2.5,
                                    'line-dasharray': [2, 2]
                                }
                            });
                        } catch (e) {}
                    }

                    // Garis hubung Visit ke Distributor jika anomali di depo atau jarak <= 1000m (Putus-putus Merah)
                    if (hasDist && hasVisit && (isAtDepo || (item.dist_to_distributor !== null && item.dist_to_distributor <= 1000))) {
                        try {
                            this.mapInstance.addSource('route-distributor', {
                                'type': 'geojson',
                                'data': {
                                    'type': 'Feature',
                                    'geometry': {
                                        'type': 'LineString',
                                        'coordinates': [
                                            [dLon, dLat],
                                            [vLon, vLat]
                                        ]
                                    }
                                }
                            });
                            this.mapInstance.addLayer({
                                'id': 'route-distributor',
                                'type': 'line',
                                'source': 'route-distributor',
                                'layout': { 'line-join': 'round', 'line-cap': 'round' },
                                'paint': {
                                    'line-color': '#ef4444',
                                    'line-width': 3,
                                    'line-dasharray': [2, 2]
                                }
                            });
                        } catch (e) {}
                    }
                };

                if (this.mapInstance.loaded()) {
                    setupLayers();
                } else {
                    this.mapInstance.on('load', setupLayers);
                }

                // Fallback mematikan spinner jika koneksi/tiles lambat
                setTimeout(() => {
                    this.mapLoading = false;
                }, 1500);

                // Resize setelah transisi modal selesai
                setTimeout(() => { 
                    if (this.mapInstance) {
                        this.mapInstance.resize();
                        applyCameraFit();
                    }
                }, 200);
                setTimeout(() => { 
                    if (this.mapInstance) {
                        this.mapInstance.resize();
                        applyCameraFit();
                    }
                }, 500);
            }
        };
    }
    </script>

    {{-- Modal Edukasi Panduan & Logika Audit Lapangan --}}
    @include('livewire.report.analisa-kunjungan-se.audit-logic-modal')
</div>
