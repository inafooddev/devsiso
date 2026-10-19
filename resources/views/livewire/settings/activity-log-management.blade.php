<div class="flex-1 flex flex-col w-full h-full min-h-0 overflow-hidden" wire:keydown.escape.window="closeLogDetail">
    <x-slot name="title">Log Aktivitas Pengguna</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 w-full p-3 md:p-4 overflow-hidden">
        
        <!-- KPI Summary Cards (Interactive Click-to-Filter Shortcuts) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 shrink-0">
            <!-- Active Users -->
            <div 
                wire:click="applyKpiFilter('users')"
                title="Klik untuk melihat seluruh user aktif"
                class="bg-base-100 rounded-xl border border-base-200 p-3 shadow-sm flex items-center gap-3 cursor-pointer hover:border-primary/60 hover:shadow-md transition-all select-none {{ $activityCategory === 'all' && $sortBy === 'last_access' ? 'ring-2 ring-primary/30' : '' }}"
            >
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <x-heroicon-o-users class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">User Aktif</div>
                    <div class="text-xl font-bold text-base-content leading-tight mt-0.5">{{ number_format($kpiStats['total_users']) }}</div>
                    <div class="text-[10px] text-primary font-medium hover:underline">Semua pengguna &rarr;</div>
                </div>
            </div>

            <!-- Total Activities -->
            <div 
                wire:click="applyKpiFilter('activities')"
                title="Klik untuk mengurutkan user paling aktif"
                class="bg-base-100 rounded-xl border border-base-200 p-3 shadow-sm flex items-center gap-3 cursor-pointer hover:border-info/60 hover:shadow-md transition-all select-none {{ $sortBy === 'total_activities' ? 'ring-2 ring-info/30 bg-info/5' : '' }}"
            >
                <div class="w-10 h-10 rounded-lg bg-info/10 text-info flex items-center justify-center shrink-0">
                    <x-heroicon-o-bolt class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">Total Aktivitas</div>
                    <div class="text-xl font-bold text-base-content leading-tight mt-0.5">{{ number_format($kpiStats['total_activities']) }}</div>
                    <div class="text-[10px] text-info font-medium hover:underline">Urutkan terbanyak &rarr;</div>
                </div>
            </div>

            <!-- Data Mutations -->
            <div 
                wire:click="applyKpiFilter('mutations')"
                title="Klik untuk memfilter hanya aksi mutasi data (CRUD)"
                class="bg-base-100 rounded-xl border border-base-200 p-3 shadow-sm flex items-center gap-3 cursor-pointer hover:border-success/60 hover:shadow-md transition-all select-none {{ $activityCategory === 'mutation' ? 'ring-2 ring-success/30 bg-success/5' : '' }}"
            >
                <div class="w-10 h-10 rounded-lg bg-success/10 text-success flex items-center justify-center shrink-0">
                    <x-heroicon-o-arrow-path-rounded-square class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">Mutasi Data</div>
                    <div class="text-xl font-bold text-base-content leading-tight mt-0.5">{{ number_format($kpiStats['total_mutations']) }}</div>
                    <div class="text-[10px] text-success font-medium hover:underline">Fokus CRUD &rarr;</div>
                </div>
            </div>

            <!-- Failed / Anomalies -->
            <div 
                wire:click="applyKpiFilter('failed')"
                title="Klik untuk memprioritaskan user dengan anomali / login gagal"
                class="bg-base-100 rounded-xl border border-base-200 p-3 shadow-sm flex items-center gap-3 cursor-pointer hover:border-error/60 hover:shadow-md transition-all select-none {{ $sortBy === 'anomalies' ? 'ring-2 ring-error/30 bg-error/5' : '' }}"
            >
                <div class="w-10 h-10 rounded-lg {{ $kpiStats['total_failed'] > 0 ? 'bg-error/10 text-error' : 'bg-base-200 text-base-content/40' }} flex items-center justify-center shrink-0">
                    <x-heroicon-o-shield-exclamation class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">Percobaan Gagal</div>
                    <div class="text-xl font-bold {{ $kpiStats['total_failed'] > 0 ? 'text-error' : 'text-base-content' }} leading-tight mt-0.5">
                        {{ number_format($kpiStats['total_failed']) }}
                    </div>
                    <div class="text-[10px] text-error font-medium hover:underline">Investigasi risiko &rarr;</div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-base-100 p-3.5 rounded-xl border border-base-200 shadow-sm shrink-0 space-y-3">
            
            <!-- Filter Header, Presets & Sorting -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-2.5 border-b border-base-200">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-funnel class="w-4 h-4 text-primary" />
                    <span class="text-xs font-bold text-base-content/90 uppercase tracking-wider">Filter & Urutan Audit</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Audit Priority Sort Selector -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-base-content/50 font-medium text-xs">Urutan:</span>
                        <select wire:model.live="sortBy" class="select select-bordered select-xs text-xs font-semibold focus:select-primary">
                            <option value="last_access">🕒 Terakhir Akses (Default)</option>
                            <option value="anomalies">🚨 Prioritas Anomali & Risiko Dulu</option>
                            <option value="total_activities">📊 Total Aktivitas Terbanyak</option>
                            <option value="first_access">🌅 Jam Mulai Paling Pagi</option>
                        </select>
                    </div>

                    <!-- Quick Date Presets -->
                    <div class="flex flex-wrap items-center gap-1 text-xs">
                        <button 
                            type="button" 
                            wire:click="setDatePreset('today')"
                            class="btn btn-xs {{ $dateFrom === now()->toDateString() && $dateTo === now()->toDateString() ? 'btn-primary text-primary-content' : 'btn-ghost border border-base-300' }}"
                        >
                            Hari Ini
                        </button>
                        <button 
                            type="button" 
                            wire:click="setDatePreset('yesterday')"
                            class="btn btn-xs {{ $dateFrom === now()->subDay()->toDateString() && $dateTo === now()->subDay()->toDateString() ? 'btn-primary text-primary-content' : 'btn-ghost border border-base-300' }}"
                        >
                            Kemarin
                        </button>
                        <button 
                            type="button" 
                            wire:click="setDatePreset('this_week')"
                            class="btn btn-xs {{ $dateFrom === now()->startOfWeek()->toDateString() && $dateTo === now()->endOfWeek()->toDateString() ? 'btn-primary text-primary-content' : 'btn-ghost border border-base-300' }}"
                        >
                            Minggu Ini
                        </button>
                        <button 
                            type="button" 
                            wire:click="setDatePreset('this_month')"
                            class="btn btn-xs {{ $dateFrom === now()->startOfMonth()->toDateString() && $dateTo === now()->endOfMonth()->toDateString() ? 'btn-primary text-primary-content' : 'btn-ghost border border-base-300' }}"
                        >
                            Bulan Ini
                        </button>
                        <button 
                            type="button" 
                            wire:click="setDatePreset('all')"
                            class="btn btn-xs {{ empty($dateFrom) && empty($dateTo) ? 'btn-primary text-primary-content' : 'btn-ghost border border-base-300' }}"
                        >
                            Semua
                        </button>
                    </div>
                </div>
            </div>

            <!-- Smart Activity Category Filter (Noise Reduction) -->
            <div class="flex flex-wrap items-center justify-between gap-2.5">
                <div class="flex flex-wrap items-center gap-1.5 bg-base-200/70 p-1 rounded-lg">
                    <span class="text-[11px] font-semibold text-base-content/60 px-2">Fokus:</span>
                    <button 
                        type="button" 
                        wire:click="setActivityCategory('all')"
                        class="btn btn-xs {{ $activityCategory === 'all' ? 'btn-neutral' : 'btn-ghost' }} text-xs"
                    >
                        Semua Aktivitas
                    </button>
                    <button 
                        type="button" 
                        wire:click="setActivityCategory('mutation')"
                        class="btn btn-xs {{ $activityCategory === 'mutation' ? 'btn-success text-success-content' : 'btn-ghost' }} text-xs flex items-center gap-1"
                    >
                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                        Mutasi Data (CRUD)
                    </button>
                    <button 
                        type="button" 
                        wire:click="setActivityCategory('auth')"
                        class="btn btn-xs {{ $activityCategory === 'auth' ? 'btn-primary text-primary-content' : 'btn-ghost' }} text-xs flex items-center gap-1"
                    >
                        <x-heroicon-o-lock-closed class="w-3.5 h-3.5" />
                        Keamanan & Login
                    </button>
                    <button 
                        type="button" 
                        wire:click="setActivityCategory('menu')"
                        class="btn btn-xs {{ $activityCategory === 'menu' ? 'btn-info text-info-content' : 'btn-ghost' }} text-xs flex items-center gap-1"
                    >
                        <x-heroicon-o-cursor-arrow-rays class="w-3.5 h-3.5" />
                        Akses Menu Saja
                    </button>
                </div>

                <div class="text-xs text-base-content/50 hidden md:block">
                    Menampilkan pengguna yang aktif pada periode & kategori terpilih.
                </div>
            </div>
            
            <!-- Standard Input Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end pt-0.5">
                <!-- Search -->
                <div class="form-control w-full">
                    <label class="label py-0.5"><span class="label-text text-xs font-semibold text-base-content/70">Keyword</span></label>
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari user, aksi, deskripsi..." />
                </div>

                <!-- Action Filter -->
                <div class="form-control w-full">
                    <label class="label py-0.5"><span class="label-text text-xs font-semibold text-base-content/70">Aksi Spesifik</span></label>
                    <select wire:model.live="actionFilter" class="select select-bordered select-sm w-full text-sm focus:select-primary">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}">{{ strtoupper($act) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div class="form-control w-full">
                    <label class="label py-0.5"><span class="label-text text-xs font-semibold text-base-content/70">Mulai Tanggal</span></label>
                    <input 
                        type="date" 
                        wire:model.live="dateFrom" 
                        class="input input-bordered input-sm w-full text-sm focus:input-primary" 
                    />
                </div>

                <!-- Date To -->
                <div class="form-control w-full">
                    <label class="label py-0.5"><span class="label-text text-xs font-semibold text-base-content/70">Sampai Tanggal</span></label>
                    <input 
                        type="date" 
                        wire:model.live="dateTo" 
                        class="input input-bordered input-sm w-full text-sm focus:input-primary" 
                    />
                </div>

                <!-- Reset Button -->
                <div class="form-control w-full">
                    <x-ui.button 
                        variant="ghost" 
                        outline 
                        size="sm"
                        class="w-full text-sm border-base-300 hover:border-error hover:text-error" 
                        wire:click="resetFilters" 
                        icon="arrow-path"
                    >
                        Reset Filter
                    </x-ui.button>
                </div>

                <!-- Export Button -->
                <div class="form-control w-full">
                    <x-ui.button 
                        variant="success" 
                        size="sm"
                        class="w-full text-sm" 
                        wire:click="export" 
                        wire:loading.attr="disabled"
                        wire:target="export"
                        icon="arrow-down-tray"
                    >
                        Export Excel
                    </x-ui.button>
                </div>
            </div>

            <!-- Active Filter Chips (Breadcrumb Tags) -->
            @php
                $hasFilters = $search !== '' 
                    || $actionFilter !== '' 
                    || $activityCategory !== 'all' 
                    || $sortBy !== 'last_access' 
                    || $dateFrom !== now()->toDateString() 
                    || $dateTo !== now()->toDateString();
            @endphp
            @if($hasFilters)
                <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-base-200/80 text-xs text-base-content/70">
                    <span class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider mr-1">Filter Aktif:</span>
                    
                    @if($activityCategory !== 'all')
                        <span class="badge badge-sm badge-neutral gap-1 text-[11px]">
                            Fokus: {{ $activityCategory === 'mutation' ? 'Mutasi Data' : ($activityCategory === 'menu' ? 'Akses Menu' : 'Keamanan & Auth') }}
                            <button type="button" wire:click="setActivityCategory('all')" class="hover:text-error ml-0.5 font-bold">✕</button>
                        </span>
                    @endif

                    @if($sortBy !== 'last_access')
                        <span class="badge badge-sm badge-warning text-warning-content gap-1 text-[11px]">
                            Urutan: {{ $sortBy === 'anomalies' ? 'Prioritas Anomali' : ($sortBy === 'total_activities' ? 'Aktivitas Terbanyak' : 'Jam Masuk Awal') }}
                            <button type="button" wire:click="$set('sortBy', 'last_access')" class="hover:text-error ml-0.5 font-bold">✕</button>
                        </span>
                    @endif

                    @if($search !== '')
                        <span class="badge badge-sm badge-info text-info-content gap-1 text-[11px]">
                            Keyword: "{{ $search }}"
                            <button type="button" wire:click="$set('search', '')" class="hover:text-error ml-0.5 font-bold">✕</button>
                        </span>
                    @endif

                    @if($actionFilter !== '')
                        <span class="badge badge-sm badge-primary text-primary-content gap-1 text-[11px]">
                            Aksi: {{ $actionFilter }}
                            <button type="button" wire:click="$set('actionFilter', '')" class="hover:text-error ml-0.5 font-bold">✕</button>
                        </span>
                    @endif

                    @if($dateFrom !== now()->toDateString() || $dateTo !== now()->toDateString())
                        <span class="badge badge-sm badge-ghost border-base-300 gap-1 text-[11px]">
                            Periode: {{ $dateFrom ?: 'Awal' }} s/d {{ $dateTo ?: 'Sekarang' }}
                            <button type="button" wire:click="setDatePreset('today')" class="hover:text-error ml-0.5 font-bold">✕</button>
                        </span>
                    @endif

                    <button type="button" wire:click="resetFilters" class="btn btn-ghost btn-xs text-error font-semibold hover:bg-error/10 ml-auto text-[11px]">
                        Hapus Semua Filter
                    </button>
                </div>
            @endif
        </div>

        <!-- Main User Table Container (Single Internal Scrollbar) -->
        <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            <div class="flex-1 min-h-0 overflow-y-auto">
                <x-ui.table empty="Belum ada aktivitas user yang ditemukan pada kriteria filter." emptyIcon="users" :loading="false">
                    <x-slot:head>
                        <tr>
                            <th class="w-12 text-center"></th>
                            <th class="w-80">PENGGUNA & JABATAN</th>
                            <th class="w-64">JAM OPERASIONAL & LAST ACCESS</th>
                            <th class="w-56 text-center">TOTAL AKTIVITAS & DIAGNOSTIK</th>
                            <th class="w-36 text-center">AKSI</th>
                        </tr>
                    </x-slot:head>

                    @foreach($users as $user)
                        @php
                            $userKey = $user->user_id ?? '__SYSTEM__';
                            $isExpanded = $expandedUserId === $userKey;

                            $userName = $user->user_name ?: ($user->user_id ? $user->user_id : 'System / Guest');
                            $words = preg_split('/\s+/', trim($userName));
                            $initials = count($words) >= 2 
                                ? mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1))
                                : mb_strtoupper(mb_substr($userName, 0, 2));

                            $firstAccess = $user->first_access ? \Carbon\Carbon::parse($user->first_access) : null;
                            $lastAccess = $user->last_access ? \Carbon\Carbon::parse($user->last_access) : null;
                            $isActiveRecently = $lastAccess ? $lastAccess->greaterThanOrEqualTo(now()->subMinutes(15)) : false;

                            // Calculate operating duration safely
                            $durationText = '-';
                            if ($firstAccess && $lastAccess) {
                                $diffHours = (int) $firstAccess->diffInHours($lastAccess);
                                $diffMinutes = ((int) $firstAccess->diffInMinutes($lastAccess)) % 60;
                                $durationText = $diffHours > 0 ? "{$diffHours}j {$diffMinutes}m" : "{$diffMinutes}m";
                            }
                        @endphp

                        <!-- Parent Row (User Summary) -->
                        <tr 
                            wire:key="user-row-{{ $userKey }}"
                            wire:click="toggleUser('{{ $userKey }}')"
                            class="cursor-pointer transition-colors duration-150 {{ $isExpanded ? 'bg-primary/5 hover:bg-primary/10 font-medium' : 'hover:bg-base-200/60' }}"
                        >
                            <!-- Chevron Icon -->
                            <td class="text-center">
                                <button 
                                    type="button" 
                                    class="btn btn-ghost btn-xs btn-circle text-base-content/60"
                                    aria-label="Toggle Detail"
                                >
                                    @if($isExpanded)
                                        <x-heroicon-o-chevron-down class="w-4 h-4 text-primary font-bold" />
                                    @else
                                        <x-heroicon-o-chevron-right class="w-4 h-4 text-base-content/40 hover:text-base-content" />
                                    @endif
                                </button>
                            </td>

                            <!-- User Identity & Role -->
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="{{ $isExpanded ? 'bg-primary text-primary-content ring-2 ring-primary/30' : 'bg-primary/10 text-primary' }} rounded-full w-10 h-10 flex items-center justify-center font-bold text-xs uppercase shadow-sm transition-all">
                                            {{ $initials ?: 'S' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-base-content flex items-center gap-1.5 flex-wrap">
                                            <span>{{ $user->user_name ?: 'System / Guest' }}</span>
                                            
                                            @if($user->role_name)
                                                <span class="badge badge-xs badge-neutral uppercase font-mono text-[9px] px-1.5">
                                                    {{ $user->role_name }}
                                                </span>
                                            @endif

                                            @if($isActiveRecently)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[10px] font-semibold bg-success/15 text-success">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>
                                                    Aktif
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="text-xs text-base-content/50 font-mono select-all flex items-center gap-2 mt-0.5">
                                            <span>ID: {{ $user->user_id ?: 'SYSTEM' }}</span>
                                            @if(!empty($user->region_code))
                                                <span class="text-[10px] text-base-content/40 font-sans">
                                                    📍 {{ is_array($user->region_code) ? implode(', ', array_slice($user->region_code, 0, 2)) : $user->region_code }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Operating Hours & Last Access -->
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <!-- Last Access -->
                                    <div class="flex items-center gap-1.5 font-medium text-base-content text-sm">
                                        <x-heroicon-o-clock class="w-4 h-4 text-base-content/40 shrink-0" />
                                        <span>{{ $lastAccess ? $lastAccess->format('d M Y H:i:s') : '-' }}</span>
                                        <span class="text-xs text-base-content/50">
                                            ({{ $lastAccess ? $lastAccess->diffForHumans() : '-' }})
                                        </span>
                                    </div>
                                    
                                    <!-- Work Duration Range -->
                                    @if($firstAccess && $lastAccess)
                                        <div class="text-[11px] text-base-content/55 flex items-center gap-1 ml-5.5">
                                            <span class="font-mono">{{ $firstAccess->format('H:i') }}</span>
                                            <span>s/d</span>
                                            <span class="font-mono">{{ $lastAccess->format('H:i') }}</span>
                                            <span class="text-base-content/40 font-semibold">• Rentang: {{ $durationText }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Total Activities & Diagnostic Badges -->
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="badge {{ $isExpanded ? 'badge-primary text-primary-content' : 'badge-neutral' }} font-bold text-xs px-2.5 py-2.5">
                                        {{ number_format($user->total_activities) }} Aktivitas
                                    </span>

                                    <!-- Security & Anomaly Diagnostic Flags -->
                                    <div class="flex items-center gap-1 flex-wrap justify-center">
                                        @if(($user->failed_count ?? 0) > 0)
                                            <span class="badge badge-error badge-xs font-semibold gap-1 text-[9px] px-1.5 py-0.5" title="{{ $user->failed_count }} kali percobaan gagal login">
                                                <x-heroicon-o-shield-exclamation class="w-2.5 h-2.5" />
                                                {{ $user->failed_count }}x Gagal
                                            </span>
                                        @endif

                                        @if(($user->ip_count ?? 0) > 1)
                                            <span class="badge badge-warning badge-xs font-semibold gap-1 text-[9px] px-1.5 py-0.5" title="Mengakses dari {{ $user->ip_count }} alamat IP berbeda">
                                                <x-heroicon-o-globe-alt class="w-2.5 h-2.5" />
                                                {{ $user->ip_count }} IP
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Toggle Button Action -->
                            <td class="text-center" onclick="event.stopPropagation()">
                                <x-ui.button 
                                    variant="{{ $isExpanded ? 'primary' : 'ghost' }}" 
                                    size="xs"
                                    class="{{ $isExpanded ? '' : 'border-base-300' }}"
                                    wire:click="toggleUser('{{ $userKey }}')"
                                >
                                    {{ $isExpanded ? 'Tutup' : 'Lihat Log' }}
                                </x-ui.button>
                            </td>
                        </tr>

                        <!-- Child Row: Nested Mini-Table (Accordion Tunggal) -->
                        @if($isExpanded)
                            <tr wire:key="user-detail-{{ $userKey }}" class="bg-base-200/50 hover:bg-base-200/50 border-b border-base-300">
                                <td colspan="5" class="p-3 md:p-4">
                                    <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm space-y-3">
                                        
                                        <!-- Header Detail with Local Search and 1-Click Export -->
                                        <div class="flex flex-wrap items-center justify-between gap-3 pb-2.5 border-b border-base-200">
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-o-bars-3-bottom-left class="w-4 h-4 text-primary" />
                                                <h4 class="text-sm font-bold text-base-content">
                                                    Rincian Aktivitas: <span class="text-primary">{{ $user->user_name ?: 'System / Guest' }}</span>
                                                </h4>
                                                <span class="badge badge-sm badge-neutral font-mono">
                                                    {{ count($childLogs) }} dari {{ number_format($user->total_activities) }} log
                                                </span>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2">
                                                <!-- In-Row Child Search Input -->
                                                <div class="relative w-48 sm:w-60">
                                                    <input 
                                                        type="text" 
                                                        wire:model.live.debounce.250ms="childSearch"
                                                        placeholder="Cari di log user ini..." 
                                                        class="input input-bordered input-xs w-full pl-7 pr-6 text-xs focus:input-primary"
                                                    />
                                                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-base-content/40 absolute left-2 top-1.5 pointer-events-none" />
                                                    @if($childSearch)
                                                        <button 
                                                            type="button" 
                                                            wire:click="$set('childSearch', '')" 
                                                            class="absolute right-2 top-1 text-base-content/40 hover:text-base-content font-bold text-xs"
                                                            title="Hapus pencarian lokal"
                                                        >
                                                            ✕
                                                        </button>
                                                    @endif
                                                </div>

                                                <!-- Single User Excel Export -->
                                                <button 
                                                    type="button" 
                                                    wire:click="exportUserLogs('{{ $userKey }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="exportUserLogs('{{ $userKey }}')"
                                                    class="btn btn-xs btn-outline btn-success gap-1 text-xs"
                                                    title="Unduh log khusus user ini ke file Excel"
                                                >
                                                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                                    <span>Unduh Excel</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Loading Spinner saat membuka -->
                                        <div wire:loading wire:target="toggleUser('{{ $userKey }}')" class="py-8 text-center text-sm text-base-content/60 w-full">
                                            <span class="loading loading-spinner loading-md text-primary align-middle mr-2"></span>
                                            Memuat rincian aktivitas pengguna...
                                        </div>

                                        <!-- Nested Mini-Table Content -->
                                        <div wire:loading.remove wire:target="toggleUser('{{ $userKey }}')" class="overflow-x-auto max-h-96 overflow-y-auto rounded-lg border border-base-200">
                                            <table class="table table-xs md:table-sm table-zebra w-full">
                                                <thead class="bg-base-200 text-[11px] font-semibold uppercase tracking-wider text-base-content/70 sticky top-0 z-10 shadow-sm">
                                                    <tr>
                                                        <th class="w-44">Waktu</th>
                                                        <th class="w-36">Aksi</th>
                                                        <th>Deskripsi / Halaman yang Diakses</th>
                                                        <th class="w-60">Perangkat & IP</th>
                                                        <th class="w-16 text-center">Detail</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-base-200 text-xs">
                                                    @forelse($childLogs as $log)
                                                        <tr class="hover:bg-base-200/50">
                                                            <!-- Waktu -->
                                                            <td class="whitespace-nowrap font-medium text-base-content/80">
                                                                <div class="flex items-center gap-1.5">
                                                                    <x-heroicon-o-clock class="w-3.5 h-3.5 text-base-content/40" />
                                                                    <span>{{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}</span>
                                                                </div>
                                                            </td>

                                                            <!-- Aksi -->
                                                            <td class="whitespace-nowrap">
                                                                <x-ui.badge :variant="$log->badge_variant" outline class="uppercase tracking-wider font-semibold text-[9px] px-2 py-0.5">
                                                                    {{ $log->action }}
                                                                </x-ui.badge>
                                                            </td>

                                                            <!-- Deskripsi -->
                                                            <td class="max-w-md break-words text-base-content/80 font-medium">
                                                                <span class="line-clamp-2" title="{{ $log->description }}">
                                                                    {{ $log->description ?: '-' }}
                                                                </span>
                                                            </td>

                                                            <!-- Perangkat & IP (Humanized) -->
                                                            <td>
                                                                <div class="flex items-center gap-1.5">
                                                                    @if($log->is_mobile)
                                                                        <x-heroicon-o-device-phone-mobile class="w-3.5 h-3.5 text-info shrink-0" title="Akses dari Perangkat Mobile" />
                                                                    @else
                                                                        <x-heroicon-o-computer-desktop class="w-3.5 h-3.5 text-base-content/50 shrink-0" title="Akses dari Komputer / Desktop" />
                                                                    @endif
                                                                    <span class="font-medium text-base-content/80 text-[11px] truncate max-w-[130px]" title="{{ $log->user_agent }}">
                                                                        {{ $log->device_label }}
                                                                    </span>
                                                                </div>
                                                                <div class="font-mono text-[10px] text-base-content/50 flex items-center gap-1 mt-0.5">
                                                                    <x-heroicon-o-globe-alt class="w-3 h-3 text-base-content/30" />
                                                                    <span>{{ $log->ip_address ?: '-' }}</span>
                                                                </div>
                                                            </td>

                                                            <!-- Detail Inspect Button -->
                                                            <td class="text-center">
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="showLogDetail({{ $log->id }})"
                                                                    class="btn btn-ghost btn-xs btn-square text-base-content/50 hover:text-primary"
                                                                    title="Inspeksi Detail Lengkap"
                                                                >
                                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center py-6 text-base-content/50">
                                                                Tidak ada rincian log untuk user ini pada filter yang dipilih.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        @if(count($childLogs) >= 100)
                                            <p class="text-[11px] text-base-content/40 italic text-right">
                                                * Menampilkan 100 aktivitas terbaru. Gunakan tombol "Unduh Excel" di atas untuk melihat seluruh arsip log user ini.
                                            </p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </x-ui.table>
            </div>

            <!-- Pagination Footer -->
            <div class="p-3.5 border-t border-base-200 bg-base-100 shrink-0">
                @if(method_exists($users, 'links'))
                    {{ $users->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- Detail Modal Dialog for Log Inspection (Backdrop Click & ESC Enabled) -->
    @if($selectedLog)
        <div 
            wire:click.self="closeLogDetail"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 animate-fade-in"
        >
            <div class="bg-base-100 rounded-2xl border border-base-300 shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden">
                
                <!-- Modal Header -->
                <div class="p-4 border-b border-base-200 flex items-center justify-between bg-base-200/50">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-base-content">Detail Inspeksi Log Aktivitas</h3>
                    </div>
                    <button 
                        type="button" 
                        wire:click="closeLogDetail" 
                        class="btn btn-ghost btn-sm btn-circle text-base-content/60 hover:text-error"
                        title="Tutup (ESC)"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-5 overflow-y-auto space-y-4 text-sm flex-1">
                    <div class="grid grid-cols-2 gap-3 bg-base-200/40 p-3 rounded-xl border border-base-300/60">
                        <div>
                            <span class="text-xs text-base-content/50 font-semibold block">WAKTU EKSEKUSI</span>
                            <span class="font-medium text-base-content">{{ $selectedLog->created_at ? $selectedLog->created_at->format('d M Y H:i:s') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 font-semibold block">TIPE AKSI</span>
                            <x-ui.badge :variant="$selectedLog->badge_variant" outline class="uppercase tracking-wider font-semibold text-[10px] mt-0.5">
                                {{ $selectedLog->action }}
                            </x-ui.badge>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 font-semibold block">NAMA USER</span>
                            <span class="font-semibold text-base-content">{{ $selectedLog->user_name ?: 'System / Guest' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 font-semibold block">USER ID</span>
                            <span class="font-mono text-base-content/80">{{ $selectedLog->user_id ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 font-semibold block">IP ADDRESS</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-base-content/80">{{ $selectedLog->ip_address ?: '-' }}</span>
                                @if($selectedLog->ip_address)
                                    <button 
                                        type="button" 
                                        onclick="navigator.clipboard.writeText('{{ $selectedLog->ip_address }}');"
                                        class="text-xs text-primary hover:underline font-sans"
                                        title="Salin IP ke clipboard"
                                    >
                                        [Salin]
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-base-content/50 font-semibold block">PERANGKAT / OS</span>
                            <span class="text-base-content font-medium">{{ $selectedLog->device_label }}</span>
                        </div>
                    </div>

                    <!-- Full Description / Payload -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-base-content/70 uppercase tracking-wider block">
                                Deskripsi / Rincian Muatan Log:
                            </label>
                            @if($selectedLog->description)
                                <button 
                                    type="button" 
                                    onclick="navigator.clipboard.writeText(`{{ addslashes($selectedLog->description) }}`);"
                                    class="text-xs text-primary hover:underline font-sans font-medium"
                                >
                                    Salin Deskripsi
                                </button>
                            @endif
                        </div>
                        <div class="bg-base-200/80 p-3.5 rounded-xl border border-base-300 font-mono text-xs text-base-content/90 whitespace-pre-wrap break-words max-h-48 overflow-y-auto select-all">
                            {{ $selectedLog->description ?: 'Tidak ada deskripsi.' }}
                        </div>
                    </div>

                    <!-- Full User Agent String -->
                    <div>
                        <label class="text-xs font-bold text-base-content/70 uppercase tracking-wider block mb-1.5">
                            Raw User Agent String:
                        </label>
                        <div class="bg-base-200/40 p-2.5 rounded-lg border border-base-300/60 font-mono text-[11px] text-base-content/60 break-words select-all">
                            {{ $selectedLog->user_agent ?: '-' }}
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-3.5 border-t border-base-200 flex justify-between items-center bg-base-200/30">
                    <span class="text-[11px] text-base-content/40">Tekan <kbd class="kbd kbd-xs">ESC</kbd> atau klik di luar untuk menutup</span>
                    <button type="button" wire:click="closeLogDetail" class="btn btn-sm btn-ghost border border-base-300">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
