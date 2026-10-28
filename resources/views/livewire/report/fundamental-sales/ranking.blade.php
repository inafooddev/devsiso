<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Individual Performance Management - Ranking SPV</x-slot>

    {{-- TAB MENU --}}
    @include('livewire.report.fundamental-sales._tabs')

    @php
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if (!function_exists('getRankingAchColor')) {
            function getRankingAchColor($ach) {
                if ($ach >= 150) return 'text-blue-600 dark:text-blue-400';
                if ($ach >= 100) return 'text-emerald-600 dark:text-emerald-400';
                if ($ach >= 80) return 'text-amber-500 dark:text-amber-400';
                return 'text-rose-600 dark:text-rose-400';
            }
        }

        if (!function_exists('formatPctClean')) {
            function formatPctClean($val, $decimals = 1) {
                $formatted = number_format((float) $val, $decimals, '.', '');
                if ($decimals > 0 && str_contains($formatted, '.')) {
                    $formatted = rtrim(rtrim($formatted, '0'), '.');
                }
                return $formatted . '%';
            }
        }

        $avatarPalettes = [
            'background-color: #0d9488; color: #ffffff;', // Teal (DK)
            'background-color: #ea580c; color: #ffffff;', // Orange (RY)
            'background-color: #2563eb; color: #ffffff;', // Blue (MA)
            'background-color: #d97706; color: #ffffff;', // Amber (DS)
            'background-color: #7c3aed; color: #ffffff;', // Violet
            'background-color: #059669; color: #ffffff;', // Emerald
            'background-color: #e11d48; color: #ffffff;', // Rose
            'background-color: #0284c7; color: #ffffff;', // Sky
        ];
    @endphp

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Top Toolbar: Title, Summary Pills & Filters --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-col gap-3 bg-base-100">
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-3">
                {{-- Title & Active Period --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center shrink-0 text-amber-500">
                        <x-heroicon-s-trophy class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base md:text-lg font-extrabold tracking-tight text-base-content">Leaderboard KPI Supervisor</h2>
                            <span class="badge badge-sm badge-primary font-bold">
                                {{ $monthNames[$appliedMonth] ?? '' }} {{ $appliedYear }}
                            </span>
                        </div>
                        <p class="text-[11px] text-base-content/60 font-medium mt-0.5">
                            Evaluasi & Peringkat Performa Bulanan Supervisor (7 Indikator Fundamental Sales)
                        </p>
                    </div>
                </div>

                {{-- Filter Controls --}}
                <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto justify-start xl:justify-end">
                    {{-- Tahun --}}
                    <select wire:model.live="selectedYear" class="select select-sm select-bordered rounded-lg bg-base-100 font-semibold text-xs w-[95px]">
                        @for($i = date('Y'); $i >= 2023; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>

                    {{-- Bulan --}}
                    <select wire:model.live="selectedMonth" class="select select-sm select-bordered rounded-lg bg-base-100 font-semibold text-xs w-[125px]">
                        @foreach($monthNames as $mNum => $mLabel)
                            <option value="{{ $mNum }}">{{ $mLabel }}</option>
                        @endforeach
                    </select>

                    {{-- Region --}}
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-lg bg-base-100 font-semibold text-xs w-[140px]">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    {{-- Area --}}
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-lg bg-base-100 font-semibold text-xs w-[135px]" @if(!$selectedRegion) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    {{-- Urutan --}}
                    <select wire:model.live="sortBy" class="select select-sm select-bordered rounded-lg bg-base-100 font-semibold text-xs w-[150px]">
                        <option value="score_desc">Urut: Juara 1 - Akhir</option>
                        <option value="score_asc">Urut: Skor Terendah</option>
                        <option value="name_asc">Urut: Nama (A - Z)</option>
                    </select>

                    {{-- Search Input --}}
                    <div class="relative w-full sm:w-[190px]">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               wire:keydown.enter="applyFilter"
                               placeholder="Cari nama / cabang..."
                               class="input input-sm input-bordered rounded-lg bg-base-100 w-full pr-7 text-xs" />
                        @if(!empty($search))
                            <button type="button" wire:click="$set('search', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content">
                                <x-heroicon-s-x-mark class="w-4 h-4" />
                            </button>
                        @endif
                    </div>

                    @if(!empty($selectedRegion) || !empty($selectedArea) || !empty($search) || $sortBy !== 'score_desc')
                        <button type="button" wire:click="resetFilter" class="btn btn-sm btn-ghost text-xs text-error gap-1 px-2" title="Reset Filter">
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                            Reset
                        </button>
                    @endif
                </div>
            </div>

            {{-- Compact Summary Strip --}}
            @if($stats['total_spv'] > 0)
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 pt-2 border-t border-base-200">
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-base-200/40 border border-base-200">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 block">Partisipan SPV</span>
                        <span class="text-sm font-black text-base-content">
                            {{ number_format($stats['filtered_spv']) }}
                            @if($stats['filtered_spv'] !== $stats['total_spv'])
                                <span class="text-xs font-normal text-base-content/50">/ {{ number_format($stats['total_spv']) }}</span>
                            @endif
                            Supervisor
                        </span>
                    </div>
                    <x-heroicon-o-user-group class="w-5 h-5 text-primary/70 shrink-0" />
                </div>

                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-base-200/40 border border-base-200">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 block">Rata-Rata Skor</span>
                        <span class="text-sm font-black text-info">{{ number_format($stats['avg_score'], 1, '.', '') }}%</span>
                    </div>
                    <x-heroicon-o-chart-bar-square class="w-5 h-5 text-info/70 shrink-0" />
                </div>

                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-base-200/40 border border-base-200">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 block">Skor &ge; 80% (Target)</span>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                            {{ number_format($stats['pass_count']) }} SPV
                            <span class="text-[11px] font-semibold text-base-content/50">
                                ({{ $stats['total_spv'] > 0 ? round(($stats['pass_count'] / $stats['total_spv']) * 100) : 0 }}%)
                            </span>
                        </span>
                    </div>
                    <x-heroicon-o-check-badge class="w-5 h-5 text-emerald-500/70 shrink-0" />
                </div>

                @if($stats['top_spv'])
                <div class="flex items-center justify-between px-3 py-2 rounded-lg border" style="background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(251,191,36,0.15)); border-color: rgba(245,158,11,0.35);">
                    <div class="min-w-0 pr-2">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-400 block">Juara 1 Periode Ini</span>
                        <span class="text-sm font-black text-base-content truncate block" title="{{ $stats['top_spv']['display_name'] }}">
                            {{ $stats['top_spv']['display_name'] }}
                            <span class="text-amber-600 dark:text-amber-400">({{ number_format($stats['top_spv']['total_score'], 1, '.', '') }}%)</span>
                        </span>
                    </div>
                    <x-heroicon-s-trophy class="w-5 h-5 text-amber-500 shrink-0" />
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Loading Indicator Overlay --}}
        <div wire:loading.flex class="items-center justify-center gap-2 py-2 bg-primary/10 text-primary text-xs font-bold border-b border-primary/20 shrink-0">
            <span class="loading loading-spinner loading-xs"></span>
            <span>Memperbarui data ranking supervisor...</span>
        </div>

        {{-- Main Scrollable List of SPV Cards --}}
        <div class="flex-1 min-h-0 overflow-y-auto p-3 md:p-4 space-y-3.5 bg-base-200/30">
            @if(empty($data))
                <div class="flex flex-col items-center justify-center h-64 text-base-content/50">
                    <x-heroicon-o-document-magnifying-glass class="w-14 h-14 mb-3 stroke-1" />
                    <p class="text-sm font-semibold">Tidak ada data supervisor untuk periode {{ $monthNames[$appliedMonth] ?? '' }} {{ $appliedYear }}.</p>
                    <p class="text-xs text-base-content/40 mt-1">Coba pilih bulan/region lain atau hapus kata kunci pencarian.</p>
                </div>
            @else
                @foreach($data as $idx => $item)
                    @php
                        $rank = $item['rank'];
                        $totalScore = $item['total_score'];
                        $avatarStyle = $avatarPalettes[$idx % count($avatarPalettes)];

                        // Card Outer Border & Left Accent Bar (Matching Reference Screenshot)
                        if ($rank === 1) {
                            $cardBorderStyle = 'border: 1.5px solid #fbbf24; border-left: 6px solid #f59e0b;';
                        } elseif ($totalScore >= 80) {
                            $cardBorderStyle = 'border: 1.5px solid rgba(16, 185, 129, 0.45); border-left: 6px solid #10b981;';
                        } else {
                            $cardBorderStyle = 'border: 1.5px solid rgba(244, 63, 94, 0.35); border-left: 6px solid #f43f5e;';
                        }

                        // Skor Akhir Right Box Styling (Matching Reference Screenshot)
                        if ($totalScore >= 80) {
                            $scoreBoxStyle = 'background-color: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.3);';
                            $scoreLabelColor = 'text-emerald-600/80 dark:text-emerald-400/80';
                            $scoreValColor = 'text-emerald-700 dark:text-emerald-300';
                        } else {
                            $scoreBoxStyle = 'background-color: rgba(244, 63, 94, 0.06); border: 1px solid rgba(244, 63, 94, 0.25);';
                            $scoreLabelColor = 'text-rose-400 dark:text-rose-300/80';
                            $scoreValColor = 'text-rose-700 dark:text-rose-400';
                        }
                    @endphp

                    <div x-data="{ openCabang: false }"
                         style="{{ $cardBorderStyle }}"
                         class="relative bg-base-100 rounded-xl shadow-xs hover:shadow-md transition-all duration-200 px-3.5 py-3.5 md:px-4 md:py-3.5">

                        {{-- Top-Right Corner Medal Ribbon for Top 3 (Matching Reference Screenshot) --}}
                        @if($rank === 1)
                            <div class="absolute top-0 right-3 px-2.5 py-0.5 rounded-b-lg shadow-xs flex items-center gap-1 text-[10px] font-extrabold z-10"
                                 style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #451a03;">
                                <span>🏆</span>
                                <span>Juara 1</span>
                            </div>
                        @elseif($rank === 2)
                            <div class="absolute top-0 right-3 px-2.5 py-0.5 rounded-b-lg shadow-xs flex items-center gap-1 text-[10px] font-extrabold z-10"
                                 style="background: linear-gradient(135deg, #e2e8f0, #94a3b8); color: #1e293b;">
                                <span>🥈</span>
                                <span>Juara 2</span>
                            </div>
                        @elseif($rank === 3)
                            <div class="absolute top-0 right-3 px-2.5 py-0.5 rounded-b-lg shadow-xs flex items-center gap-1 text-[10px] font-extrabold z-10"
                                 style="background: linear-gradient(135deg, #f59e0b, #b45309); color: #ffffff;">
                                <span>🥉</span>
                                <span>Juara 3</span>
                            </div>
                        @endif

                        {{-- Horizontal 3-Column Card Body: [Profile] - [4x2 KPI Grid] - [Skor Akhir] --}}
                        <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3.5 lg:gap-4">

                            {{-- 1. LEFT: Supervisor Identity --}}
                            <div class="flex items-center gap-3 w-full lg:w-[215px] xl:w-[235px] shrink-0">
                                <div class="w-10 h-10 rounded-lg font-extrabold text-xs flex items-center justify-center shrink-0 shadow-xs tracking-wider"
                                     style="{{ $avatarStyle }}">
                                    {{ $item['initials'] }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-[13px] text-base-content leading-snug truncate" title="{{ $item['display_name'] }} ({{ $item['code'] }})">
                                        {{ $item['display_name'] }}
                                    </div>
                                    <div class="text-[11px] text-base-content/60 font-medium truncate mt-0.5"
                                         title="{{ $item['region'] }} • {{ $item['se_count'] }} SE • Rank #{{ $rank }} dari {{ $item['total_spv_in_scope'] }}">
                                        {{ $item['region'] }} &bull; {{ $item['se_count'] }} SE &bull; Rank #{{ $rank }} dari {{ $item['total_spv_in_scope'] }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-[10px] text-base-content/50 truncate" title="Cabang: {{ $item['branches_string'] }}">
                                            {{ $item['branches_string'] }}
                                        </span>
                                        @canExport('report.fundamental-sales.index')
                                            <a href="{{ route('report.fundamental-sales.export', ['supervisorCode' => $item['code'], 'year' => $appliedYear]) }}"
                                               target="_blank"
                                               class="text-base-content/40 hover:text-success transition-colors shrink-0"
                                               title="Download Excel Tahunan {{ $item['display_name'] }}">
                                                <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                            </a>
                                        @endcanExport
                                    </div>
                                </div>
                            </div>

                            {{-- 2. CENTER: Symmetrical 4x2 KPI Grid --}}
                            <div class="flex-1 min-w-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                @foreach(['so', 'ipt', 'jks', 'ec', 'standpro', 'ao', 'rwo'] as $kpiKey)
                                    @php $kpi = $item['kpis'][$kpiKey]; @endphp
                                    <div class="rounded-lg px-3 py-2 border border-base-200 dark:border-base-300 bg-base-200/35 dark:bg-base-200/20 flex flex-col justify-between hover:border-base-300 transition-colors">
                                        {{-- KPI Title + Info Tooltip + Score --}}
                                        <div class="flex items-center justify-between gap-1">
                                            <div class="flex items-center gap-1 min-w-0">
                                                <span class="text-[10.5px] font-bold text-base-content/80 uppercase tracking-tight truncate">
                                                    {{ $kpi['label'] }}
                                                </span>
                                                <span class="text-base-content/35 hover:text-base-content/70 cursor-help shrink-0" title="{{ $kpi['full_label'] }}">
                                                    <x-heroicon-o-information-circle class="w-3.5 h-3.5" />
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 shrink-0">
                                                {{ formatPctClean($kpi['score'], 1) }}
                                            </span>
                                        </div>

                                        {{-- Achievement % --}}
                                        <div class="text-[13px] font-extrabold leading-tight my-0.5 {{ getRankingAchColor($kpi['ach']) }}">
                                            {{ formatPctClean($kpi['ach'], 1) }}
                                        </div>

                                        {{-- Actual / Target --}}
                                        <div class="text-[10.5px] text-base-content/60 font-medium truncate" title="{{ $kpi['formatted_actual'] }} / {{ $kpi['formatted_target'] }}">
                                            {{ $kpi['formatted_actual'] }} / {{ $kpi['formatted_target'] }}
                                        </div>
                                    </div>
                                @endforeach

                                {{-- 8th Slot in 4x2 Grid: Interactive Branch Breakdown Card --}}
                                <div @click="openCabang = !openCabang"
                                     class="rounded-lg px-3 py-2 border border-dashed border-base-300 bg-base-200/20 hover:bg-base-200/50 cursor-pointer flex flex-col justify-between transition-colors select-none group">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-[10.5px] font-bold text-base-content/70 uppercase tracking-tight">
                                            CABANG BINAAN
                                        </span>
                                        <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-base-content/50 group-hover:text-primary transition-transform duration-200"
                                                                   x-bind:class="openCabang ? 'rotate-180 text-primary' : ''" />
                                    </div>
                                    <div class="text-[13px] font-extrabold text-primary leading-tight my-0.5">
                                        {{ count($item['children']) }} Cabang &bull; {{ $item['se_count'] }} SE
                                    </div>
                                    <div class="text-[10.5px] text-base-content/60 group-hover:text-primary font-medium truncate">
                                        <span x-text="openCabang ? 'Tutup rincian cabang' : 'Klik lihat rincian cabang'">Klik lihat rincian cabang</span>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. RIGHT: Skor Akhir Box --}}
                            <div class="w-full lg:w-[96px] shrink-0 flex flex-col justify-center"
                                 @if($rank <= 3) style="margin-top: 6px;" @endif>
                                <div style="{{ $scoreBoxStyle }}"
                                     class="rounded-xl px-3 py-2.5 flex flex-row lg:flex-col items-center lg:items-start justify-between lg:justify-center w-full min-h-[64px]">
                                    <div class="text-[9.5px] font-extrabold uppercase tracking-wider leading-tight {{ $scoreLabelColor }}">
                                        SKOR<br class="hidden lg:inline"> AKHIR
                                    </div>
                                    <div class="text-lg xl:text-xl font-black tracking-tight leading-none lg:mt-1 {{ $scoreValColor }}">
                                        {{ number_format($totalScore, 1, '.', '') }}%
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Expandable Breakdown Per Cabang --}}
                        @if(!empty($item['children']))
                            <div x-show="openCabang" x-collapse x-cloak class="mt-3 pt-3 border-t border-base-200 space-y-2">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-base-content/50 px-1">
                                    Rincian Performa Per Cabang — {{ $item['display_name'] }}
                                </div>
                                @foreach($item['children'] as $cabangName => $cData)
                                    <div class="rounded-lg p-2.5 bg-base-200/30 border border-base-200 flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                                        <div class="w-full lg:w-[190px] shrink-0 flex items-center gap-2">
                                            <span class="badge badge-xs badge-neutral font-bold py-2">CABANG</span>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-base-content truncate">{{ $cabangName }}</div>
                                                <div class="text-[10px] text-base-content/60">{{ $cData['se_count'] }} SE</div>
                                            </div>
                                        </div>

                                        <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-1.5">
                                            @foreach($cData['kpis'] as $kKey => $cKpi)
                                                <div class="bg-base-100 rounded-md px-2 py-1.5 border border-base-200/80">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <span class="text-[9.5px] font-bold text-base-content/60 truncate">{{ str_replace(['KPI ', 'PI '], '', explode(' (', $cKpi['label'])[0]) }}</span>
                                                        <span class="text-[9.5px] font-extrabold text-indigo-600 dark:text-indigo-400">{{ formatPctClean($cKpi['score'], 1) }}</span>
                                                    </div>
                                                    <div class="text-xs font-extrabold {{ getRankingAchColor($cKpi['ach']) }}">
                                                        {{ formatPctClean($cKpi['ach'], 1) }}
                                                    </div>
                                                    <div class="text-[9.5px] text-base-content/50 truncate">
                                                        {{ $cKpi['formatted_actual'] }} / {{ $cKpi['formatted_target'] }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="w-full lg:w-[88px] shrink-0 text-right lg:text-center px-2 py-1 rounded-lg bg-base-100 border border-base-200">
                                            <div class="text-[8.5px] font-bold uppercase text-base-content/50">Skor Cabang</div>
                                            <div class="text-sm font-black {{ $cData['total_score'] >= 80 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ number_format($cData['total_score'], 1, '.', '') }}%
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
