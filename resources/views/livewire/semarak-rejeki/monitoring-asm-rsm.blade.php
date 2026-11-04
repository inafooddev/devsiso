<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-3.5 w-full h-full overflow-y-auto pr-0.5 pb-4">
    <x-slot name="title">Semarak Rejeki - Monitoring & Tactical Tool Reward ASM & RSM</x-slot>

    {{-- Loading Overlay --}}
    <div wire:loading.flex class="fixed inset-0 z-50 bg-base-100/60 backdrop-blur-md items-center justify-center">
        <div class="flex flex-col items-center gap-3 bg-base-100 p-8 rounded-3xl shadow-2xl border border-base-300 animate-in fade-in zoom-in duration-200">
            <span class="loading loading-ring loading-lg text-primary"></span>
            <div class="text-center">
                <span class="text-sm font-bold text-base-content block">Sinkronisasi Data Sell In</span>
                <span class="text-xs text-base-content/60">Mengkalkulasi realisasi ASM, RSM, Cabang, dan SPV...</span>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    @if ($alertSuccess)
        <div class="alert alert-success shadow-lg text-white text-xs py-2.5 px-4 rounded-2xl flex justify-between items-center bg-gradient-to-r from-success to-emerald-600 border-none" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-semibold text-xs">{{ $alertSuccess }}</span>
            </div>
            <button class="btn btn-ghost btn-xs btn-circle text-white hover:bg-white/20" @click="show = false; $wire.set('alertSuccess', null)">✕</button>
        </div>
    @endif
    @if ($alertError)
        <div class="alert alert-error shadow-lg text-white text-xs py-2.5 px-4 rounded-2xl flex justify-between items-center bg-gradient-to-r from-error to-rose-600 border-none" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <span class="font-semibold text-xs">{{ $alertError }}</span>
            </div>
            <button class="btn btn-ghost btn-xs btn-circle text-white hover:bg-white/20" @click="show = false; $wire.set('alertError', null)">✕</button>
        </div>
    @endif

    {{-- 1. Executive KPI Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        {{-- Card 1: Cakupan Wilayah & Peserta --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Cakupan Wilayah & Peserta</span>
                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-base-content font-mono">5</span>
                    <span class="text-xs text-base-content/60 font-semibold">Region (RSM)</span>
                    <span class="text-xs text-base-content/40">•</span>
                    <span class="text-2xl font-bold text-primary font-mono ml-1">{{ $compiled['total_asm_count'] }}</span>
                    <span class="text-xs text-base-content/60 font-semibold">Area (ASM)</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 pt-1 border-t border-base-200">
                <button type="button" wire:click="$set('filterRegion', '{{ $filterRegion === 'JAWA_1' ? '' : 'JAWA_1' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterRegion === 'JAWA_1' ? 'btn-primary' : 'btn-ghost bg-base-200/80 hover:bg-base-200' }}">Jawa 1</button>
                <button type="button" wire:click="$set('filterRegion', '{{ $filterRegion === 'JAWA_2' ? '' : 'JAWA_2' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterRegion === 'JAWA_2' ? 'btn-primary' : 'btn-ghost bg-base-200/80 hover:bg-base-200' }}">Jawa 2</button>
                <button type="button" wire:click="$set('filterRegion', '{{ $filterRegion === 'PULAU' ? '' : 'PULAU' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterRegion === 'PULAU' ? 'btn-primary' : 'btn-ghost bg-base-200/80 hover:bg-base-200' }}">Pulau</button>
                <button type="button" wire:click="$set('filterRegion', '{{ $filterRegion === 'SUMATERA_1' ? '' : 'SUMATERA_1' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterRegion === 'SUMATERA_1' ? 'btn-primary' : 'btn-ghost bg-base-200/80 hover:bg-base-200' }}">Sum 1</button>
                <button type="button" wire:click="$set('filterRegion', '{{ $filterRegion === 'SUMATERA_2' ? '' : 'SUMATERA_2' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterRegion === 'SUMATERA_2' ? 'btn-primary' : 'btn-ghost bg-base-200/80 hover:bg-base-200' }}">Sum 2</button>
            </div>
        </div>

        {{-- Card 2: Status Kualifikasi Pacing Q4 (Bukan Klaim Prematur) --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Kualifikasi Pacing Q4</span>
                <div class="w-8 h-8 rounded-lg {{ $showRewardClosing ? 'bg-warning/10 text-warning' : 'bg-success/10 text-success' }} flex items-center justify-center font-bold">
                    @if($showRewardClosing)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @endif
                </div>
            </div>
            <div>
                @if($showRewardClosing)
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold font-mono text-emerald-600">{{ $compiled['grand_estimated_pool_fmt'] }}</span>
                    </div>
                    <div class="text-xs text-amber-700 font-semibold mt-0.5">
                        Simulasi Pool Closing (Jika lolos 3 bln penuh)
                    </div>
                @else
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold font-mono text-primary">{{ $compiled['eligible_asm_count'] }}</span>
                        <span class="text-xs text-base-content/60 font-semibold">Area Lolos</span>
                        <span class="text-xs text-base-content/40">•</span>
                        <span class="text-xs text-amber-600 font-semibold">{{ $compiled['warning_asm_count'] }} Kejar</span>
                        <span class="text-xs text-base-content/40">•</span>
                        <span class="text-xs text-rose-600 font-semibold">{{ $compiled['hangus_asm_count'] }} Hangus</span>
                    </div>
                    <div class="text-xs text-base-content/50 font-medium mt-0.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <span>Nominal terkunci s/d Closing 31 Des</span>
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-1.5 pt-1 border-t border-base-200">
                <button type="button" wire:click="$set('filterStatus', '{{ $filterStatus === 'on_track_l2' ? 'semua' : 'on_track_l2' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterStatus === 'on_track_l2' ? 'btn-success text-white' : 'bg-emerald-500/15 text-emerald-700 hover:bg-emerald-500/25 border-emerald-500/20' }}">
                    {{ $compiled['l2_asm_count'] }} L2
                </button>
                <button type="button" wire:click="$set('filterStatus', '{{ $filterStatus === 'on_track_l1' ? 'semua' : 'on_track_l1' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterStatus === 'on_track_l1' ? 'btn-info text-white' : 'bg-cyan-500/15 text-cyan-700 hover:bg-cyan-500/25 border-cyan-500/20' }}">
                    {{ $compiled['l1_asm_count'] }} Aman L1
                </button>
                <button type="button" wire:click="$set('filterStatus', '{{ $filterStatus === 'warning' ? 'semua' : 'warning' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterStatus === 'warning' ? 'btn-warning text-white' : 'bg-amber-500/15 text-amber-700 hover:bg-amber-500/25 border-amber-500/20' }}">
                    {{ $compiled['warning_asm_count'] }} Kejar
                </button>
                <button type="button" wire:click="$set('filterStatus', '{{ $filterStatus === 'hangus' ? 'semua' : 'hangus' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterStatus === 'hangus' ? 'btn-error text-white' : 'bg-rose-500/15 text-rose-700 hover:bg-rose-500/25 border-rose-500/20' }}">
                    {{ $compiled['hangus_asm_count'] }} Hangus
                </button>
            </div>
        </div>

        {{-- Card 3: Volume & Omset Sell In --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Volume & Nilai Sell In</span>
                <span class="badge badge-primary badge-sm font-bold font-mono text-xs">{{ $compiled['overall_pct_l1'] }}% L1</span>
            </div>
            <div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-base-content font-mono">{{ number_format($compiled['grand_real_ktn'], 0, ',', '.') }}</span>
                    <span class="text-xs text-base-content/50 font-semibold font-mono">/ {{ number_format($compiled['grand_target_l1_ktn'], 0, ',', '.') }} Ktn</span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-primary h-1.5 rounded-full" style="width: {{ min(100, $compiled['overall_pct_l1']) }}%"></div>
                </div>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-base-200 text-xs">
                <span class="text-base-content/60 font-medium">Gap L1 Nasional:</span>
                <span class="font-mono font-bold {{ $compiled['grand_gap_l1'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ ($compiled['grand_gap_l1'] >= 0 ? '+' : '') . number_format($compiled['grand_gap_l1'], 0, ',', '.') }} Ktn
                </span>
            </div>
        </div>

        {{-- Card 4: Sisa Hari Kerja --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Sisa Hari Kerja Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-warning/10 text-warning flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-warning font-mono">{{ $daysInfo['remaining_days'] }}</span>
                    <span class="text-xs text-base-content/60 font-semibold">Hari Aktif</span>
                    <span class="text-xs text-base-content/40">•</span>
                    <span class="text-xs text-base-content/60 font-semibold">{{ $daysInfo['eval_month_name'] }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-base-200 text-xs">
                <span class="text-base-content/60">Hari ke-{{ $daysInfo['current_working_day'] }} dari {{ $daysInfo['total_working_days'] }} hari kerja</span>
                <span class="badge badge-ghost badge-sm text-xs font-semibold text-base-content/60 border border-base-300">Excl. Minggu</span>
            </div>
        </div>
    </div>

    {{-- Banner Alert Ketika Mode Simulasi Closing Aktif --}}
    @if($showRewardClosing)
        <div class="alert alert-warning shadow-sm text-amber-950 text-xs py-2.5 px-4 rounded-2xl border border-warning/40 bg-warning/15 flex flex-col sm:flex-row sm:items-center justify-between gap-2 animate-in fade-in duration-200">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-warning/30 flex items-center justify-center shrink-0 text-amber-900 font-bold">⚠️</div>
                <div class="leading-relaxed">
                    <strong>Mode Simulasi Closing Aktif:</strong> Angka rupiah di bawah adalah <em>estimasi matematis proyeksi reward</em> jika posisi realisasi saat ini dapat dipertahankan hingga penutupan resmi 31 Desember 2026. Angka ini <strong>belum bersifat final dan belum dapat dicairkan</strong> sebelum melewati seluruh milestone (Okt ≥70%, Nov Kumulatif ≥80%, Des Total 100%).
                </div>
            </div>
            <button type="button" wire:click="toggleRewardClosing" class="btn btn-xs btn-outline btn-neutral rounded-lg shrink-0">
                Kembali ke Mode Monitoring
            </button>
        </div>
    @endif

    {{-- 2. Tactical Toolbar --}}
    <div class="bg-base-100 p-3.5 md:p-4 rounded-2xl border border-base-300 shadow-sm flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-3">
        <div class="flex flex-wrap items-center gap-2">
            {{-- Filter Region --}}
            <select wire:model.live="filterRegion" class="select select-sm select-bordered rounded-xl bg-base-100 text-xs font-medium">
                <option value="">Semua Region (5 Region)</option>
                <option value="JAWA_1">JAWA 1</option>
                <option value="JAWA_2">JAWA 2</option>
                <option value="PULAU">PULAU</option>
                <option value="SUMATERA_1">SUMATERA 1</option>
                <option value="SUMATERA_2">SUMATERA 2</option>
            </select>

            {{-- Filter Status --}}
            <select wire:model.live="filterStatus" class="select select-sm select-bordered rounded-xl bg-base-100 text-xs font-medium">
                <option value="semua">Semua Status Pencapaian</option>
                <option value="on_track_l2">Peluang Layer 2</option>
                <option value="on_track_l1">Peluang Layer 1</option>
                <option value="warning">Zona Kejar Target</option>
                <option value="hangus">Milestone Gagal (Hangus)</option>
            </select>

            {{-- Filter Jalur --}}
            <select wire:model.live="filterJalur" class="select select-sm select-bordered rounded-xl bg-base-100 text-xs font-medium">
                <option value="semua">Semua Jalur Reward</option>
                <option value="kelompok">Jalur Kelompok</option>
                <option value="independen">Jalur Independen</option>
            </select>

            {{-- Search Box --}}
            <div class="relative min-w-48">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ASM / Region..." class="input input-sm input-bordered rounded-xl text-xs w-full pl-8" />
                <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            @if($filterRegion || $filterStatus !== 'semua' || $filterJalur !== 'semua' || $search)
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-ghost text-xs text-error font-semibold hover:bg-error/10 rounded-xl">
                    Reset Filter
                </button>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2 justify-end">
            {{-- Mode Toggle (Hierarki vs Flat) --}}
            <div class="join border border-base-300 rounded-xl p-0.5 bg-base-200/50">
                <button type="button" wire:click="$set('viewMode', 'accordion')" class="join-item btn btn-xs border-none rounded-lg text-xs font-bold {{ $viewMode === 'accordion' ? 'btn-primary shadow-sm' : 'btn-ghost' }}">
                    Hierarki Region
                </button>
                <button type="button" wire:click="$set('viewMode', 'table')" class="join-item btn btn-xs border-none rounded-lg text-xs font-bold {{ $viewMode === 'table' ? 'btn-primary shadow-sm' : 'btn-ghost' }}">
                    Tabel ASM Flat
                </button>
            </div>

            {{-- Toggle Mode Simulasi Closing (Solusi Kunci Kerancuan Angka) --}}
            <button type="button" wire:click="toggleRewardClosing" class="btn btn-sm rounded-xl text-xs font-bold gap-1.5 {{ $showRewardClosing ? 'btn-warning text-amber-950 border-warning shadow-sm' : 'btn-ghost border border-base-300 bg-base-100 hover:bg-base-200' }}" title="Beralih antara Mode Pacing Berjalan dan Simulasi Closing">
                @if($showRewardClosing)
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span>Mode: Simulasi Closing 💰</span>
                @else
                    <svg class="w-3.5 h-3.5 text-base-content/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>Fase Berjalan (Reward Terkunci 🔒)</span>
                @endif
            </button>

            {{-- Proposal Reference Modal Trigger --}}
            <button type="button" wire:click="openRulesModal" class="btn btn-sm btn-outline btn-neutral rounded-xl text-xs font-bold gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Aturan & Target
            </button>

            {{-- Refresh Data --}}
            <button type="button" wire:click="refreshData" class="btn btn-sm btn-ghost border border-base-300 rounded-xl text-xs font-semibold gap-1.5" title="Refresh kalkulasi realisasi">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Refresh
            </button>

            {{-- Export Excel --}}
            <button type="button" wire:click="exportExcel" class="btn btn-sm btn-success text-white rounded-xl text-xs font-bold gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </button>
        </div>
    </div>

    {{-- 3. Main Content View --}}
    @if ($viewMode === 'accordion')
        {{-- HIERARCHICAL REGION ACCORDION / CARDS --}}
        <div class="space-y-4">
            @forelse($regions as $reg)
                <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm overflow-hidden" x-data="{ expanded: true }">
                    {{-- Region Header Bar (RSM Level) --}}
                    <div class="p-4 bg-base-200/40 border-b border-base-300 flex flex-col md:flex-row md:items-center justify-between gap-3 cursor-pointer select-none" @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold shrink-0">
                                <span class="font-mono text-sm font-extrabold">{{ substr($reg['region_code'], 0, 3) }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-base text-base-content">{{ $reg['region_name'] }}</h3>
                                    <span class="badge badge-sm font-bold text-xs {{ $reg['status_badge'] }}">
                                        {{ $reg['status_label'] }}
                                    </span>
                                    <span class="badge badge-ghost border-base-300 badge-sm font-mono text-xs">
                                        {{ $reg['nomor_proposal'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-base-content/60 mt-0.5 flex flex-wrap items-center gap-2">
                                    <span>Target L1: <strong class="font-mono text-base-content">{{ number_format($reg['target_l1_bln'], 0, ',', '.') }}</strong> Ktn</span>
                                    <span>•</span>
                                    <span>Target L2: <strong class="font-mono text-base-content">{{ number_format($reg['target_l2_bln'], 0, ',', '.') }}</strong> Ktn</span>
                                    <span>•</span>
                                    <span>Realisasi: <strong class="font-mono text-primary font-bold">{{ number_format($reg['total_ktn'], 1, ',', '.') }}</strong> Ktn</span>
                                    <span>•</span>
                                    <span>Gap L1: <strong class="font-mono {{ $reg['gap_l1'] >= 0 ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold' }}">{{ ($reg['gap_l1'] >= 0 ? '+' : '') . number_format($reg['gap_l1'], 1, ',', '.') }}</strong> Ktn</span>
                                    <span>•</span>
                                    <span>Gap L2: <strong class="font-mono {{ $reg['gap_l2'] >= 0 ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold' }}">{{ ($reg['gap_l2'] >= 0 ? '+' : '') . number_format($reg['gap_l2'], 1, ',', '.') }}</strong> Ktn</span>
                                    <span>•</span>
                                    @if($showRewardClosing)
                                        <span class="text-emerald-600 font-bold">Estimasi Reward RSM: Rp {{ number_format($reg['reward_rsm_estimasi'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-base-content/50 font-medium">Estimasi Reward RSM: <strong class="font-normal text-base-content/70">Terkunci s/d Closing 31 Des 🔒</strong></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-xs font-bold font-mono {{ $reg['pct_l1'] >= 100 ? 'text-emerald-600' : 'text-primary' }}">
                                    {{ $reg['pct_l1'] }}% <span class="text-base-content/50 font-normal">vs L1</span>
                                </div>
                                <div class="text-xs font-mono text-base-content/60">
                                    {{ $reg['pct_l2'] }}% vs L2
                                </div>
                            </div>
                            <button type="button" class="btn btn-ghost btn-xs btn-circle text-base-content/60">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Region ASM Children Table --}}
                    <div x-show="expanded" x-collapse>
                        <div class="overflow-x-auto">
                            <table class="table table-sm w-full">
                                <thead>
                                    <tr class="bg-base-200/60 text-xs uppercase font-bold text-base-content/70 border-b border-base-300">
                                        <th class="w-12 text-center">No</th>
                                        <th>Divisi Area (ASM)</th>
                                        <th class="text-right">Target L1 / Bln</th>
                                        <th class="text-right">Target L2 / Bln</th>
                                        <th class="text-right">Okt (Ktn)</th>
                                        <th class="text-right">Nov (Ktn)</th>
                                        <th class="text-right">Total Realisasi</th>
                                        <th class="text-center">% Capai (L1 / L2)</th>
                                        <th class="text-right">Gap Ktn (L1 / L2)</th>
                                        <th class="text-center">Jalur Reward</th>
                                        @if($showRewardClosing)
                                            <th class="text-right">Estimasi Reward (Closing)</th>
                                        @else
                                            <th class="text-center">Status & Proyeksi Layer</th>
                                        @endif
                                        <th class="text-center w-28">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reg['asms'] as $idx => $asm)
                                        <tr class="hover:bg-base-200/40 border-b border-base-200 text-xs">
                                            <td class="text-center font-mono text-base-content/50">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="font-bold text-sm text-base-content">{{ $asm['area_name'] }}</div>
                                                <div class="text-xs text-base-content/50 font-mono">{{ $asm['asm_db_name'] }}</div>
                                            </td>
                                            <td class="text-right font-mono font-semibold">{{ number_format($asm['target_l1_bln'], 0, ',', '.') }}</td>
                                            <td class="text-right font-mono font-semibold text-primary">{{ number_format($asm['target_l2_bln'], 0, ',', '.') }}</td>
                                            <td class="text-right font-mono">
                                                <span>{{ number_format($asm['okt_ktn'], 1, ',', '.') }}</span>
                                                <span class="block text-xs {{ $asm['m1_passed'] ? 'text-emerald-600' : 'text-rose-600' }}">({{ $asm['m1_pct'] }}%)</span>
                                            </td>
                                            <td class="text-right font-mono font-bold text-primary">{{ number_format($asm['nov_ktn'], 1, ',', '.') }}</td>
                                            <td class="text-right font-mono font-extrabold text-base-content">
                                                {{ number_format($asm['total_ktn'], 1, ',', '.') }} Ktn
                                                <span class="block text-xs font-normal text-base-content/50">Rp {{ number_format($asm['total_val'] / 1000000, 1, ',', '.') }} Jt</span>
                                            </td>
                                            <td class="text-center font-mono">
                                                <div class="font-bold {{ $asm['pacing_pct_l1'] >= 100 ? 'text-emerald-600' : 'text-primary' }}">{{ $asm['pacing_pct_l1'] }}% <span class="text-xs text-base-content/50 font-normal">L1</span></div>
                                                <div class="text-xs text-base-content/60">{{ $asm['pacing_pct_l2'] }}% L2</div>
                                            </td>
                                            <td class="text-right font-mono">
                                                <div class="font-bold {{ $asm['gap_l1'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ ($asm['gap_l1'] >= 0 ? '+' : '') . number_format($asm['gap_l1'], 1, ',', '.') }} <span class="text-2xs font-normal text-base-content/50">L1</span>
                                                </div>
                                                <div class="text-xs {{ $asm['gap_l2'] >= 0 ? 'text-emerald-600' : 'text-rose-600/80' }}">
                                                    {{ ($asm['gap_l2'] >= 0 ? '+' : '') . number_format($asm['gap_l2'], 1, ',', '.') }} <span class="text-2xs font-normal text-base-content/50">L2</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-sm font-semibold text-xs {{ $asm['jalur'] === 'Kelompok' ? 'badge-primary badge-outline' : ($asm['jalur'] === 'Independen' ? 'badge-secondary badge-outline' : 'badge-ghost text-base-content/60 border-base-300') }}">
                                                    {{ $asm['jalur'] }}
                                                </span>
                                            </td>
                                            @if($showRewardClosing)
                                                <td class="text-right">
                                                    <div class="font-bold font-mono text-emerald-600 text-sm">
                                                        Rp {{ number_format($asm['estimasi_reward'], 0, ',', '.') }}
                                                    </div>
                                                    <span class="badge badge-xs font-bold {{ $asm['status_badge'] }}">
                                                        {{ $asm['status_label'] }}
                                                    </span>
                                                </td>
                                            @else
                                                <td class="text-center">
                                                    <span class="badge badge-sm font-bold {{ $asm['status_badge'] }}">
                                                        {{ $asm['status_label'] }}
                                                    </span>
                                                    <span class="block text-xs text-base-content/40 mt-0.5">Terkunci s/d Closing 🔒</span>
                                                </td>
                                            @endif
                                            <td class="text-center">
                                                <button type="button" wire:click="openDetailModal({{ $asm['id'] }})" class="btn btn-xs btn-primary rounded-lg text-xs font-bold gap-1 shadow-sm">
                                                    <span>Detail</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center py-4 text-xs text-base-content/60">Tidak ada data area ASM pada region ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                {{-- Grand Total Baris Per Region (RSM Summary Footer) --}}
                                <tfoot class="bg-base-200/80 font-bold border-t-2 border-base-300 text-xs">
                                    <tr class="hover:bg-base-200">
                                        <td class="text-center font-mono text-base-content/60">∑</td>
                                        <td>
                                            <div class="text-sm font-extrabold text-base-content">TOTAL {{ $reg['region_name'] }}</div>
                                            <div class="text-2xs font-normal text-base-content/50">Konsolidasi Seluruh Area RSM</div>
                                        </td>
                                        <td class="text-right font-mono font-bold">{{ number_format($reg['target_l1_bln'], 0, ',', '.') }}</td>
                                        <td class="text-right font-mono font-bold text-primary">{{ number_format($reg['target_l2_bln'], 0, ',', '.') }}</td>
                                        <td class="text-right font-mono font-bold">{{ number_format($reg['okt_ktn'], 1, ',', '.') }}</td>
                                        <td class="text-right font-mono font-bold text-primary">{{ number_format($reg['nov_ktn'], 1, ',', '.') }}</td>
                                        <td class="text-right font-mono font-extrabold text-base-content">
                                            {{ number_format($reg['total_ktn'], 1, ',', '.') }} Ktn
                                            <span class="block text-2xs font-normal text-base-content/50">Rp {{ number_format($reg['total_val'] / 1000000, 1, ',', '.') }} Jt</span>
                                        </td>
                                        <td class="text-center font-mono">
                                            <div class="font-bold {{ $reg['pct_l1'] >= 100 ? 'text-emerald-600' : 'text-primary' }}">{{ $reg['pct_l1'] }}% <span class="text-2xs font-normal text-base-content/50">L1</span></div>
                                            <div class="text-2xs text-base-content/60">{{ $reg['pct_l2'] }}% L2</div>
                                        </td>
                                        <td class="text-right font-mono">
                                            <div class="font-bold {{ $reg['gap_l1'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ ($reg['gap_l1'] >= 0 ? '+' : '') . number_format($reg['gap_l1'], 1, ',', '.') }} <span class="text-2xs font-normal text-base-content/50">L1</span>
                                            </div>
                                            <div class="text-xs {{ $reg['gap_l2'] >= 0 ? 'text-emerald-600' : 'text-rose-600/80' }}">
                                                {{ ($reg['gap_l2'] >= 0 ? '+' : '') . number_format($reg['gap_l2'], 1, ',', '.') }} <span class="text-2xs font-normal text-base-content/50">L2</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-sm font-bold text-xs {{ $reg['tier_status'] === 'kelompok_gagal' ? 'badge-ghost text-rose-700 bg-rose-500/10 border-rose-500/20' : ($reg['all_asms_passed_l1'] ? 'badge-primary' : 'badge-warning text-amber-950') }}">
                                                {{ $reg['tier_status'] === 'kelompok_gagal' ? 'Jalur Mandiri' : ($reg['all_asms_passed_l1'] ? 'Kelompok Kompak' : 'Kelompok (Fokus L1)') }}
                                            </span>
                                        </td>
                                        @if($showRewardClosing)
                                            <td class="text-right">
                                                <div class="font-extrabold font-mono {{ $reg['reward_rsm_estimasi'] > 0 ? 'text-emerald-600' : 'text-base-content/40' }} text-sm">
                                                    Rp {{ number_format($reg['reward_rsm_estimasi'], 0, ',', '.') }}
                                                </div>
                                                <span class="badge badge-2xs font-bold {{ $reg['status_badge'] }}">
                                                    {{ $reg['status_label'] }}
                                                </span>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <span class="badge badge-sm font-bold {{ $reg['status_badge'] }}">
                                                    {{ $reg['status_label'] }}
                                                </span>
                                                <span class="block text-2xs text-base-content/40 mt-0.5">Terkunci s/d Closing 🔒</span>
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            <span class="badge badge-outline badge-sm text-2xs font-mono text-base-content/70">
                                                {{ $reg['asms_passed_l1_count'] }}/{{ count($reg['asms']) }} Area Lolos
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-base-100 p-8 text-center rounded-2xl border border-base-300">
                    <p class="text-sm text-base-content/60 font-semibold">Tidak ada data region yang sesuai filter.</p>
                </div>
            @endforelse
        </div>
    @else
        {{-- FLAT TABLE MODE --}}
        <div class="bg-base-100 rounded-2xl border border-base-300 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr class="bg-base-200/70 text-xs uppercase font-bold text-base-content/70 border-b border-base-300">
                            <th class="w-12 text-center">No</th>
                            <th>Region</th>
                            <th>Divisi Area (ASM)</th>
                            <th class="text-right">Target L1/Bln</th>
                            <th class="text-right">Target L2/Bln</th>
                            <th class="text-right">Realisasi Total</th>
                            <th class="text-center">% vs L1</th>
                            <th class="text-center">% vs L2</th>
                            <th class="text-right">Gap Ktn (L1 / L2)</th>
                            <th class="text-center">Jalur Reward</th>
                            @if($showRewardClosing)
                                <th class="text-right">Estimasi Reward (Closing)</th>
                            @else
                                <th class="text-center">Status & Proyeksi Layer</th>
                            @endif
                            <th class="text-center w-28">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flatAsms as $idx => $asm)
                            <tr class="hover:bg-base-200/40 border-b border-base-200 text-xs">
                                <td class="text-center font-mono text-base-content/50">{{ $idx + 1 }}</td>
                                <td class="font-bold text-xs">{{ $asm['region_name'] }}</td>
                                <td>
                                    <div class="font-bold text-sm text-base-content">{{ $asm['area_name'] }}</div>
                                    <div class="text-xs text-base-content/50 font-mono">{{ $asm['asm_db_name'] }}</div>
                                </td>
                                <td class="text-right font-mono font-semibold">{{ number_format($asm['target_l1_bln'], 0, ',', '.') }}</td>
                                <td class="text-right font-mono font-semibold text-primary">{{ number_format($asm['target_l2_bln'], 0, ',', '.') }}</td>
                                <td class="text-right font-mono font-bold text-base-content">
                                    {{ number_format($asm['total_ktn'], 1, ',', '.') }} Ktn
                                </td>
                                <td class="text-center font-mono font-bold {{ $asm['pacing_pct_l1'] >= 100 ? 'text-emerald-600' : 'text-primary' }}">{{ $asm['pacing_pct_l1'] }}%</td>
                                <td class="text-center font-mono font-semibold text-base-content/60">{{ $asm['pacing_pct_l2'] }}%</td>
                                <td class="text-right font-mono">
                                    <div class="font-bold {{ $asm['gap_l1'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ ($asm['gap_l1'] >= 0 ? '+' : '') . number_format($asm['gap_l1'], 1, ',', '.') }} <span class="text-2xs font-normal text-base-content/50">L1</span>
                                    </div>
                                    <div class="text-xs {{ $asm['gap_l2'] >= 0 ? 'text-emerald-600' : 'text-rose-600/80' }}">
                                        {{ ($asm['gap_l2'] >= 0 ? '+' : '') . number_format($asm['gap_l2'], 1, ',', '.') }} <span class="text-2xs font-normal text-base-content/50">L2</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-sm font-semibold text-xs {{ $asm['jalur'] === 'Kelompok' ? 'badge-primary badge-outline' : ($asm['jalur'] === 'Independen' ? 'badge-secondary badge-outline' : 'badge-ghost text-base-content/60 border-base-300') }}">
                                        {{ $asm['jalur'] }}
                                    </span>
                                </td>
                                @if($showRewardClosing)
                                    <td class="text-right">
                                        <div class="font-bold font-mono text-emerald-600 text-sm">
                                            Rp {{ number_format($asm['estimasi_reward'], 0, ',', '.') }}
                                        </div>
                                        <span class="badge badge-xs font-bold {{ $asm['status_badge'] }}">
                                            {{ $asm['status_label'] }}
                                        </span>
                                    </td>
                                @else
                                    <td class="text-center">
                                        <span class="badge badge-sm font-bold {{ $asm['status_badge'] }}">
                                            {{ $asm['status_label'] }}
                                        </span>
                                        <span class="block text-xs text-base-content/40 mt-0.5">Terkunci s/d Closing 🔒</span>
                                    </td>
                                @endif
                                <td class="text-center">
                                    <button type="button" wire:click="openDetailModal({{ $asm['id'] }})" class="btn btn-xs btn-primary rounded-lg text-xs font-bold gap-1 shadow-sm">
                                        <span>Detail</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-6 text-xs text-base-content/60">Tidak ada data ASM yang sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 4. TACTICAL DRILLDOWN MODAL (The Core Chasing & Analysis Tool) --}}
    @if($isDetailModalOpen && $selectedAsmDetail)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-base-900/60 backdrop-blur-sm flex items-center justify-center p-3 md:p-5">
            <div class="bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-6xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-base-200/50 border-b border-base-300 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-base md:text-lg text-base-content">
                                    Pusat Kendali Taktis: {{ $selectedAsmDetail['area_name'] }}
                                </h3>
                                <span class="badge badge-sm font-bold {{ $selectedAsmDetail['status_badge'] }}">
                                    {{ $selectedAsmDetail['status_label'] }}
                                </span>
                            </div>
                            <p class="text-xs text-base-content/60 mt-0.5">
                                Region: <strong>{{ $selectedAsmDetail['region_name'] }}</strong> • Proposal: <strong>{{ $selectedAsmDetail['nomor_proposal'] }}</strong> • Database ASM: <code class="font-mono text-primary">{{ $selectedAsmDetail['asm_db_name'] }}</code>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="btn btn-sm btn-ghost btn-circle text-base-content/60 hover:bg-base-200">✕</button>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="p-6 overflow-y-auto space-y-5">
                    
                    {{-- 1. Tactical Pulse & Daily Run-rate Calculator --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Box 1: Target & Realisasi Saat Ini --}}
                        <div class="bg-base-200/40 rounded-2xl p-4 border border-base-300 flex flex-col justify-between gap-3">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Target & Realisasi Saat Ini</span>
                                <div class="mt-2 space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target Layer 1 / Bln:</span>
                                        <span class="font-mono font-bold">{{ number_format($selectedAsmDetail['target_l1_bln'], 0, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target Layer 2 / Bln:</span>
                                        <span class="font-mono font-bold text-primary">{{ number_format($selectedAsmDetail['target_l2_bln'], 0, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300">
                                        <span class="text-base-content/60">Realisasi Bersih:</span>
                                        <span class="font-mono font-extrabold text-base-content text-sm">{{ number_format($selectedAsmDetail['total_ktn'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Nilai Omset:</span>
                                        <span class="font-mono font-semibold text-primary">Rp {{ number_format($selectedAsmDetail['total_val'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-base-300 text-xs flex justify-between items-center">
                                <span class="text-base-content/60">Pacing vs L1:</span>
                                <span class="badge badge-sm font-bold font-mono {{ $selectedAsmDetail['pacing_pct_l1'] >= 100 ? 'badge-success text-white' : 'badge-primary' }}">
                                    {{ $selectedAsmDetail['pacing_pct_l1'] }}%
                                </span>
                            </div>
                        </div>

                        {{-- Box 2: Kalkulator Target Harian Sisa Hari Kerja (Run-Rate) --}}
                        <div class="bg-base-200/40 rounded-2xl p-4 border border-base-300 flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Kebutuhan Harian (Run-Rate)</span>
                                    <span class="badge badge-warning text-amber-950 font-bold badge-xs">{{ $selectedAsmDetail['daily_needed_l1'] > 0 ? 'Kejar' : 'Aman' }}</span>
                                </div>
                                <div class="mt-2 space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Sisa Hari Kerja Aktif:</span>
                                        <span class="font-mono font-bold text-warning">{{ $daysInfo['remaining_days'] }} Hari Kerja</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Kunci Layer 1:</span>
                                        <span class="font-mono font-bold {{ $selectedAsmDetail['gap_l1'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $selectedAsmDetail['gap_l1'] > 0 ? '~' . $selectedAsmDetail['daily_needed_l1'] . ' Ktn/hr' : '✓ SUDAH AMAN' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Tembus Layer 2:</span>
                                        <span class="font-mono font-bold text-primary">
                                            {{ $selectedAsmDetail['gap_l2'] > 0 ? '~' . $selectedAsmDetail['daily_needed_l2'] . ' Ktn/hr' : '✓ TERCAPAI' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300 text-xs">
                                        <span class="text-base-content/60">Rata-rata / Cabang:</span>
                                        <span class="font-mono font-bold text-base-content">~{{ $selectedAsmDetail['daily_per_cabang_l1'] }} Ktn/cabang</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-base-300 text-xs text-base-content/60">
                                Dihitung tanpa menyertakan hari Minggu.
                            </div>
                        </div>

                        {{-- Box 3: Status Proyeksi & Estimasi Reward --}}
                        <div class="bg-base-200/40 rounded-2xl p-4 border border-base-300 flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Status Proyeksi Reward</span>
                                    <span class="badge badge-sm font-bold text-xs {{ $selectedAsmDetail['jalur'] === 'Kelompok' ? 'badge-primary' : 'badge-secondary' }}">{{ $selectedAsmDetail['jalur'] }}</span>
                                </div>
                                <div class="mt-2.5">
                                    @if($showRewardClosing)
                                        <div class="text-2xl font-extrabold font-mono text-emerald-600">
                                            Rp {{ number_format($selectedAsmDetail['estimasi_reward'], 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-amber-700 font-semibold mt-0.5">
                                            Estimasi Potensi Closing (3 Bulan)
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-md font-bold {{ $selectedAsmDetail['status_badge'] }}">
                                                {{ $selectedAsmDetail['status_label'] }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-base-content/60 mt-2 flex items-center gap-1 font-medium">
                                            <svg class="w-3.5 h-3.5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <span>Nominal Terkunci (Potensi: <strong>Rp {{ number_format($selectedAsmDetail['estimasi_reward'], 0, ',', '.') }}</strong> saat Closing)</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="p-2.5 rounded-xl bg-base-100 border border-base-300 text-xs text-base-content/70 space-y-1">
                                <div>{{ $selectedAsmDetail['status_desc'] }}</div>
                                <div class="text-amber-800 font-medium pt-1 border-t border-base-200">
                                    📌 <em>Sesuai Klausul 5, reward baru sah cair saat closing final 31 Des setelah seluruh tahapan milestone terpenuhi.</em>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Evaluasi Klausul 5 Milestone (Okt min 70%, Okt-Nov kum min 80%, Total min 100%) --}}
                    <div class="bg-base-100 rounded-2xl border border-base-300 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/70">
                                Evaluasi Ketentuan Milestone Pencairan Reward (Klausul 5 Proposal)
                            </h4>
                            <span class="badge badge-ghost border-base-300 badge-sm text-xs font-mono">Ketentuan Wajib</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            {{-- Tahap 1: Okt --}}
                            <div class="p-3 rounded-xl border {{ $selectedAsmDetail['m1_passed'] ? 'bg-success/5 border-success/30' : 'bg-error/5 border-error/30' }}">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold">Tahap 1 • Oktober</span>
                                    <span class="badge badge-xs font-bold {{ $selectedAsmDetail['m1_passed'] ? 'badge-success text-white' : 'badge-error text-white' }}">
                                        {{ $selectedAsmDetail['m1_passed'] ? 'Lolos (≥70%)' : 'Gagal (<70%)' }}
                                    </span>
                                </div>
                                <div class="mt-2 text-xs space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Min Wajib (70%):</span>
                                        <span class="font-mono font-semibold">{{ number_format($selectedAsmDetail['m1_min_70'], 0, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Realisasi Okt:</span>
                                        <span class="font-mono font-bold {{ $selectedAsmDetail['m1_passed'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($selectedAsmDetail['okt_ktn'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300">
                                        <span class="text-base-content/60">Persentase:</span>
                                        <span class="font-mono font-bold">{{ $selectedAsmDetail['m1_pct'] }}%</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tahap 2: Okt-Nov Kumulatif --}}
                            <div class="p-3 rounded-xl border {{ $selectedAsmDetail['m2_passed'] ? 'bg-success/5 border-success/30' : 'bg-warning/5 border-warning/30' }}">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold">Tahap 2 • Okt-Nov Kumulatif</span>
                                    <span class="badge badge-xs font-bold {{ $selectedAsmDetail['m2_passed'] ? 'badge-success text-white' : 'badge-warning text-amber-950' }}">
                                        {{ $selectedAsmDetail['m2_passed'] ? 'Lolos (≥80%)' : 'Kejar Batas 80%' }}
                                    </span>
                                </div>
                                <div class="mt-2 text-xs space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Min Wajib (80%):</span>
                                        <span class="font-mono font-semibold">{{ number_format($selectedAsmDetail['m2_min_80'], 0, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Realisasi Kumulatif:</span>
                                        <span class="font-mono font-bold text-primary">{{ number_format($selectedAsmDetail['kum2_ktn'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300">
                                        <span class="text-base-content/60">Persentase Kumulatif:</span>
                                        <span class="font-mono font-bold">{{ $selectedAsmDetail['m2_pct'] }}%</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tahap 3: Total 3 Bulan --}}
                            <div class="p-3 rounded-xl border bg-base-200/40 border-base-300">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold">Tahap 3 • Total 3 Bulan</span>
                                    <span class="badge badge-xs font-bold badge-primary">Wajib 100%</span>
                                </div>
                                <div class="mt-2 text-xs space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target 3 Bulan (L1):</span>
                                        <span class="font-mono font-semibold">{{ number_format($selectedAsmDetail['target_l1_3bln'], 0, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Realisasi Berjalan:</span>
                                        <span class="font-mono font-bold text-base-content">{{ number_format($selectedAsmDetail['total_ktn'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300">
                                        <span class="text-base-content/60">Progres Total:</span>
                                        <span class="font-mono font-bold text-primary">{{ $selectedAsmDetail['m3_pct_l1'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. CABANG & SPV PERFORMANCE MATRIX (Kebutuhan Utama User) --}}
                    <div class="bg-base-100 rounded-2xl border border-base-300 p-4 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div>
                                <h4 class="font-bold text-sm text-base-content">
                                    🏢 Drilldown Performa Cabang & SPV Lapangan
                                </h4>
                                <p class="text-xs text-base-content/60 mt-0.5">
                                    Memetakan kontribusi masing-masing cabang dan SPV: siapa yang menopang target vs siapa yang tertinggal
                                </p>
                            </div>
                            <span class="badge badge-primary badge-outline badge-sm font-bold text-xs">
                                {{ count($selectedCabangList) }} Cabang • {{ count($selectedSpvList) }} SPV Lapangan
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table table-xs w-full">
                                <thead>
                                    <tr class="bg-base-200/70 text-xs uppercase font-bold text-base-content/70 border-b border-base-300">
                                        <th>Nama Cabang</th>
                                        <th>SPV Penanggung Jawab</th>
                                        <th class="text-right">Okt (Ktn)</th>
                                        <th class="text-right">Nov (Ktn)</th>
                                        <th class="text-right">Total Realisasi</th>
                                        <th class="text-center w-28">Porsi Kontribusi</th>
                                        <th class="text-center">Status Performa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedCabangList as $cb)
                                        <tr class="hover:bg-base-200/40 border-b border-base-200">
                                            <td>
                                                <div class="font-bold text-xs text-base-content">{{ $cb['cabang'] }}</div>
                                                <div class="text-xs text-base-content/50 font-mono">{{ count($cb['dist_names']) }} Distributor Terdaftar</div>
                                            </td>
                                            <td>
                                                <div class="text-xs font-semibold text-base-content">
                                                    {{ implode(', ', array_slice($cb['spv_names'], 0, 2)) ?: '-' }}
                                                </div>
                                            </td>
                                            <td class="text-right font-mono text-xs">{{ number_format($cb['okt_ktn'], 1, ',', '.') }}</td>
                                            <td class="text-right font-mono text-xs font-bold text-primary">{{ number_format($cb['nov_ktn'], 1, ',', '.') }}</td>
                                            <td class="text-right font-mono text-xs font-bold text-base-content">
                                                {{ number_format($cb['total_ktn'], 1, ',', '.') }} Ktn
                                                <span class="block text-xs font-normal text-base-content/50">Rp {{ number_format($cb['total_val'] / 1000000, 1, ',', '.') }} Jt</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="font-mono font-bold text-xs text-base-content">{{ $cb['pct_contribution'] }}%</div>
                                                <div class="w-full bg-base-200 rounded-full h-1 mt-1 overflow-hidden">
                                                    <div class="bg-primary h-1 rounded-full" style="width: {{ min(100, $cb['pct_contribution'] * 2.5) }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-xs font-bold {{ $cb['status_badge'] }}">
                                                    {{ $cb['status_label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-xs text-base-content/60">Tidak ada data transaksi cabang.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 4. Product Mix & Action Plan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Product Mix --}}
                        <div class="bg-base-100 rounded-2xl border border-base-300 p-4 shadow-sm">
                            <h4 class="font-bold text-sm text-base-content mb-3">
                                📦 Komposisi Produk Promo (Product Mix)
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="font-semibold text-base-content">Okebis Biskuit Kelapa 28</span>
                                        <span class="font-mono font-bold text-base-content">{{ number_format($selectedProductBreakdown['kelapa_ktn'], 1, ',', '.') }} Ktn ({{ $selectedProductBreakdown['kelapa_pct'] }}%)</span>
                                    </div>
                                    <div class="w-full bg-base-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-primary h-2 rounded-full" style="width: {{ $selectedProductBreakdown['kelapa_pct'] }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="font-semibold text-base-content">Okebis Chocolate Cookies 28</span>
                                        <span class="font-mono font-bold text-base-content">{{ number_format($selectedProductBreakdown['choco_ktn'], 1, ',', '.') }} Ktn ({{ $selectedProductBreakdown['choco_pct'] }}%)</span>
                                    </div>
                                    <div class="w-full bg-base-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-secondary h-2 rounded-full" style="width: {{ $selectedProductBreakdown['choco_pct'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-base-200/40 rounded-xl border border-base-300 mt-4 text-xs text-base-content/70">
                                💡 <em>Insight:</em> Kelapa 28 menyumbang porsi dominan. Dorong varian Chocolate Cookies 28 untuk mendongkrak omset grosir.
                            </div>
                        </div>

                        {{-- Action Plan Steps --}}
                        <div class="bg-base-100 rounded-2xl border border-base-300 p-4 shadow-sm">
                            <h4 class="font-bold text-sm text-base-content mb-3">
                                💡 Rekomendasi Taktis Lapangan (Action Plan)
                            </h4>
                            <div class="space-y-2.5">
                                @forelse($selectedTacticalActionPlan as $step)
                                    <div class="p-3 rounded-xl border border-base-300 bg-base-200/30 flex items-start gap-2.5">
                                        <span class="badge badge-sm font-bold text-xs shrink-0 mt-0.5 {{ $step['theme'] === 'success' ? 'badge-success text-white' : ($step['theme'] === 'warning' ? 'badge-warning text-amber-950' : 'badge-primary') }}">
                                            {{ $step['badge'] }}
                                        </span>
                                        <div>
                                            <div class="font-bold text-xs text-base-content">{{ $step['title'] }}</div>
                                            <div class="text-xs text-base-content/70 mt-0.5 leading-relaxed">{{ $step['desc'] }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-xs text-base-content/60">Tidak ada rekomendasi khusus saat ini.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-3.5 bg-base-200/50 border-t border-base-300 flex justify-end shrink-0">
                    <button type="button" wire:click="closeDetailModal" class="btn btn-sm btn-ghost rounded-xl text-xs font-bold">Tutup Pusat Kendali</button>
                </div>
            </div>
        </div>
    @endif

    {{-- 5. PROPOSAL & RULES MODAL --}}
    @if($isRulesModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-base-900/60 backdrop-blur-sm flex items-center justify-center p-3 md:p-5">
            <div class="bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-4 bg-base-200/50 border-b border-base-300 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-base-content">Mekanisme & Ketentuan Resmi Program Reward ASM & RSM</h3>
                            <p class="text-xs text-base-content/60">Proposal Semarak Rejeki Q4 2026 (01 Oktober 2026 – 31 Desember 2026)</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeRulesModal" class="btn btn-sm btn-ghost btn-circle text-base-content/60">✕</button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4 text-xs leading-relaxed text-base-content/80">
                    <div class="p-3.5 bg-primary/5 rounded-2xl border border-primary/20 space-y-2">
                        <h4 class="font-bold text-primary text-sm">Prinsip Pokok Program</h4>
                        <ol class="list-decimal pl-4 space-y-1">
                            <li><strong>Sumber Realisasi:</strong> Dihitung mutlak dari <strong>Sell In (selling_in)</strong> produk Okebis 28 (Kelapa & Chocolate Cookies).</li>
                            <li><strong>Target:</strong> Berdasarkan kemampuan salah 1 dari <strong>Layer I</strong> atau <strong>Layer II</strong>.</li>
                            <li><strong>Ketentuan Pencairan Reward (Klausul 5):</strong>
                                <ul class="list-disc pl-4 mt-1 space-y-0.5">
                                    <li>Bulan I (Oktober): Minimal 70% dari Target Per Bulan.</li>
                                    <li>Bulan II (November Kumulatif): Minimal 80% dari Target 2 Bulan.</li>
                                    <li>Bulan III (Desember Total): Wajib tercapai 100% dari Total Target 3 Bulan.</li>
                                    <li><em>Note: Jika tidak memenuhi ketentuan di atas pada saat closing, maka reward dinyatakan HANGUS.</em></li>
                                </ul>
                            </li>
                            <li><strong>Aturan Capping ("Kelompok Wajib Sama Kecuali Independen"):</strong>
                                <ul class="list-disc pl-4 mt-1 space-y-0.5">
                                    <li>Jika Region menembus Layer 2: RSM berhak atas Reward L2. Seluruh ASM yang memenuhi target berhak atas reward sesuai layernya.</li>
                                    <li>Jika Region hanya menembus Layer 1: RSM berhak atas Reward L1. Seluruh ASM dibatasi (capped) maksimal pada reward Layer 1.</li>
                                    <li>Jika Region Gagal: RSM mendapatkan Rp 0. Namun ASM yang secara mandiri mencapai target berhak mendapatkan reward penuhnya (Jalur Independen).</li>
                                </ul>
                            </li>
                        </ol>
                    </div>

                    <div class="border border-base-300 rounded-2xl overflow-hidden">
                        <div class="bg-base-200/70 p-2.5 font-bold text-xs uppercase text-base-content/70">
                            Daftar Nomor Proposal Resmi per Region
                        </div>
                        <table class="table table-xs w-full">
                            <thead>
                                <tr class="border-b border-base-300">
                                    <th>Region</th>
                                    <th>Nomor Proposal</th>
                                    <th class="text-right">Target L1 / Bln</th>
                                    <th class="text-right">Reward RSM L1</th>
                                    <th class="text-right">Target L2 / Bln</th>
                                    <th class="text-right">Reward RSM L2</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-bold">JAWA 1</td>
                                    <td class="font-mono">001/INA-RWDASMRSM/JAWA1/IX/2026</td>
                                    <td class="text-right font-mono">20.660 Ktn</td>
                                    <td class="text-right font-mono">Rp 6.000.000</td>
                                    <td class="text-right font-mono font-bold text-primary">22.380 Ktn</td>
                                    <td class="text-right font-mono font-bold text-primary">Rp 12.300.000</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">JAWA 2</td>
                                    <td class="font-mono">002/INA-RWDASMRSM/JAWA2/IX/2026</td>
                                    <td class="text-right font-mono">12.120 Ktn</td>
                                    <td class="text-right font-mono">Rp 3.700.000</td>
                                    <td class="text-right font-mono font-bold text-primary">13.130 Ktn</td>
                                    <td class="text-right font-mono font-bold text-primary">Rp 7.200.000</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">PULAU</td>
                                    <td class="font-mono">003/INA-RWDASMRSM/PULAU/V/2026</td>
                                    <td class="text-right font-mono">11.260 Ktn</td>
                                    <td class="text-right font-mono">Rp 6.600.000</td>
                                    <td class="text-right font-mono font-bold text-primary">12.200 Ktn</td>
                                    <td class="text-right font-mono font-bold text-primary">Rp 12.400.000</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">SUMATERA 1</td>
                                    <td class="font-mono">004/INA-RWDASMRSM/SUM1/V/2026</td>
                                    <td class="text-right font-mono">1.500 Ktn</td>
                                    <td class="text-right font-mono">Rp 1.000.000</td>
                                    <td class="text-right font-mono font-bold text-primary">1.620 Ktn</td>
                                    <td class="text-right font-mono font-bold text-primary">Rp 1.700.000</td>
                                </tr>
                                <tr>
                                    <td class="font-bold">SUMATERA 2</td>
                                    <td class="font-mono">005/INA-RWDASMRSM/SUM2/IX/2026</td>
                                    <td class="text-right font-mono">5.340 Ktn</td>
                                    <td class="text-right font-mono">Rp 3.300.000</td>
                                    <td class="text-right font-mono font-bold text-primary">5.780 Ktn</td>
                                    <td class="text-right font-mono font-bold text-primary">Rp 5.800.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="px-6 py-3.5 bg-base-200/50 border-t border-base-300 flex justify-end shrink-0">
                    <button type="button" wire:click="closeRulesModal" class="btn btn-sm btn-ghost rounded-xl text-xs font-bold">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
