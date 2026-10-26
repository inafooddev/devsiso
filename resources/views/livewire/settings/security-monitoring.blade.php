<div class="flex-1 flex flex-col w-full h-full min-h-0 overflow-y-auto p-4 md:p-6 bg-base-200/40" @if($autoRefresh) wire:poll.10s @endif>
    <x-slot name="title">Pusat Keamanan & Monitoring Serangan</x-slot>

    <!-- Header & Alert Notifikasi -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-error/10 text-error flex items-center justify-center">
                    <x-heroicon-o-shield-check class="w-5 h-5" />
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-base-content tracking-tight">Security & Threat Control</h1>
            </div>
            <p class="text-xs md:text-sm text-base-content/60 mt-1">
                Deteksi serangan siber real-time, audit probing, dan kontrol pemblokiran IP otomatis (WAF Internal).
            </p>
        </div>

        <!-- Quick Toggles -->
        <div class="flex items-center gap-3 bg-base-100 p-2 rounded-xl border border-base-300 shadow-sm self-start md:self-auto">
            <!-- Toggle Auto-Ban -->
            <label class="flex items-center gap-2 cursor-pointer px-2 py-1 rounded-lg hover:bg-base-200/60 transition-colors" title="Blokir otomatis IP selama 24 jam jika mencoba exploitasi kritis (seperti file .env / CGI)">
                <span class="text-xs font-semibold {{ $autoBanEnabled ? 'text-error' : 'text-base-content/50' }}">
                    Auto-Ban: {{ $autoBanEnabled ? 'ON' : 'OFF' }}
                </span>
                <input type="checkbox" wire:click="toggleAutoBan" class="toggle toggle-error toggle-sm" {{ $autoBanEnabled ? 'checked' : '' }} />
            </label>

            <div class="divider divider-horizontal my-0 mx-1"></div>

            <!-- Toggle Auto Refresh -->
            <button wire:click="toggleAutoRefresh" class="btn btn-ghost btn-xs gap-1.5 {{ $autoRefresh ? 'text-success' : 'text-base-content/40' }}" title="Toggle auto refresh 10 detik">
                <span class="relative flex h-2 w-2">
                    @if($autoRefresh)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                    @endif
                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $autoRefresh ? 'bg-success' : 'bg-base-content/40' }}"></span>
                </span>
                <span class="text-[11px] font-medium">{{ $autoRefresh ? 'Live Poll (10s)' : 'Paused' }}</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash Message -->
    @if (session()->has('message'))
        <div class="alert alert-success shadow-sm mb-4 py-2 px-3 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-heroicon-o-check-circle class="w-5 h-5 text-success-content" />
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Total Ancaman Hari Ini -->
        <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-bolt class="w-6 h-6" />
            </div>
            <div>
                <div class="text-[11px] font-semibold text-base-content/60 uppercase tracking-wider">Serangan Hari Ini</div>
                <div class="text-2xl font-black text-base-content leading-tight mt-0.5">{{ number_format($kpiStats['today_total']) }}</div>
                <div class="text-[10px] text-base-content/50">Permintaan mencurigakan</div>
            </div>
        </div>

        <!-- Serangan Kritis -->
        <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-error/10 text-error flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
            </div>
            <div>
                <div class="text-[11px] font-semibold text-base-content/60 uppercase tracking-wider">Insiden Kritis</div>
                <div class="text-2xl font-black {{ $kpiStats['today_critical'] > 0 ? 'text-error animate-pulse' : 'text-base-content' }} leading-tight mt-0.5">
                    {{ number_format($kpiStats['today_critical']) }}
                </div>
                <div class="text-[10px] text-base-content/50">Probe .env / CGI / SQLi</div>
            </div>
        </div>

        <!-- Brute-Force Logins -->
        <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-warning/10 text-warning flex items-center justify-center shrink-0">
                <x-heroicon-o-key class="w-6 h-6" />
            </div>
            <div>
                <div class="text-[11px] font-semibold text-base-content/60 uppercase tracking-wider">Brute-Force Login</div>
                <div class="text-2xl font-black text-base-content leading-tight mt-0.5">{{ number_format($kpiStats['today_brute_force']) }}</div>
                <div class="text-[10px] text-base-content/50">Gagal login berturut-turut</div>
            </div>
        </div>

        <!-- IP Terblokir Aktif -->
        <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-neutral/10 text-neutral-content flex items-center justify-center shrink-0">
                <x-heroicon-o-no-symbol class="w-6 h-6 text-error" />
            </div>
            <div>
                <div class="text-[11px] font-semibold text-base-content/60 uppercase tracking-wider">IP Dicekal (Banned)</div>
                <div class="text-2xl font-black text-base-content leading-tight mt-0.5">{{ number_format($kpiStats['active_blocked']) }}</div>
                <div class="text-[10px] text-base-content/50">Ditolak otomatis (403)</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs tabs-boxed bg-base-100 p-1 rounded-xl border border-base-300 w-fit mb-4">
        <button wire:click="switchTab('incidents')" class="tab tab-sm md:tab-md gap-2 font-medium {{ $activeTab === 'incidents' ? 'tab-active !bg-primary !text-primary-content font-bold' : '' }}">
            <x-heroicon-o-view-columns class="w-4 h-4" />
            Live Threat Feed
        </button>
        <button wire:click="switchTab('blocked')" class="tab tab-sm md:tab-md gap-2 font-medium {{ $activeTab === 'blocked' ? 'tab-active !bg-primary !text-primary-content font-bold' : '' }}">
            <x-heroicon-o-no-symbol class="w-4 h-4" />
            IP Terblokir ({{ $blockedIps->count() }})
        </button>
        <button wire:click="switchTab('whitelist')" class="tab tab-sm md:tab-md gap-2 font-medium {{ $activeTab === 'whitelist' ? 'tab-active !bg-primary !text-primary-content font-bold' : '' }}">
            <x-heroicon-o-check-badge class="w-4 h-4" />
            IP Whitelist Kantor ({{ $whitelists->count() }})
        </button>
    </div>

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- TAB 1: LIVE THREAT FEED (LOG SERANGAN)                        -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if ($activeTab === 'incidents')
        <div class="bg-base-100 rounded-xl border border-base-300 shadow-sm flex flex-col">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-base-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 flex-1 min-w-[280px]">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Cari IP, endpoint, atau payload..." 
                            class="input input-sm input-bordered w-full pl-8 text-xs"
                        />
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/40 absolute left-2.5 top-2" />
                    </div>

                    <!-- Severity Filter -->
                    <select wire:model.live="severityFilter" class="select select-sm select-bordered text-xs">
                        <option value="all">Semua Severity</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>

                    <!-- Threat Type Filter -->
                    <select wire:model.live="threatFilter" class="select select-sm select-bordered text-xs">
                        <option value="all">Semua Jenis Serangan</option>
                        <option value="sensitive_probe">File Probing (.env/.git)</option>
                        <option value="brute_force">Brute Force Login</option>
                        <option value="malicious_bot">Scanner Bot / Tool</option>
                        <option value="sql_injection_attempt">Injeksi SQL (SQLi)</option>
                        <option value="xss_attempt">Script Injeksi (XSS)</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="flex items-center gap-1.5">
                    <input type="date" wire:model.live="dateFrom" class="input input-sm input-bordered text-xs w-32" />
                    <span class="text-xs text-base-content/40">-</span>
                    <input type="date" wire:model.live="dateTo" class="input input-sm input-bordered text-xs w-32" />
                </div>
            </div>

            <!-- Table Feed -->
            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra w-full text-xs">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70">
                            <th>Waktu</th>
                            <th>Severity</th>
                            <th>Jenis Ancaman</th>
                            <th>Target URL & Method</th>
                            <th>IP Asal</th>
                            <th>Status Aksi</th>
                            <th class="text-center">Aksi Kontrol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $inc)
                            <tr class="hover">
                                <td class="whitespace-nowrap font-mono text-[11px] text-base-content/70" title="{{ $inc->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $inc->created_at->diffForHumans() }}
                                </td>
                                <td>
                                    <span class="badge badge-xs font-semibold uppercase badge-{{ $inc->severity_badge_variant }} py-2 px-2">
                                        {{ $inc->severity }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-medium text-base-content">{{ $inc->threat_type_label }}</div>
                                    <div class="text-[10px] text-base-content/50 font-mono truncate max-w-xs" title="{{ $inc->payload }}">
                                        {{ $inc->payload ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <span class="badge badge-ghost badge-xs font-mono font-bold">{{ $inc->method }}</span>
                                        <span class="font-mono text-base-content/80 truncate max-w-xs" title="{{ $inc->url }}">
                                            {{ Str::limit($inc->url, 40) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="font-mono font-medium text-base-content">
                                    {{ $inc->ip_address }}
                                </td>
                                <td>
                                    @if($inc->action_taken === 'auto_banned')
                                        <span class="badge badge-error badge-outline badge-xs font-medium">Auto-Banned</span>
                                    @elseif($inc->action_taken === 'blocked')
                                        <span class="badge badge-warning badge-outline badge-xs font-medium">Blocked (403)</span>
                                    @else
                                        <span class="badge badge-ghost badge-xs font-medium">Logged</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Detail Button -->
                                        <button wire:click="showIncidentDetail({{ $inc->id }})" class="btn btn-ghost btn-xs text-info" title="Lihat detail payload & header">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                        </button>
                                        <!-- Ban IP Button -->
                                        <button wire:click="openBanModal('{{ $inc->ip_address }}', '{{ $inc->threat_type }}')" class="btn btn-ghost btn-xs text-error hover:bg-error/10" title="Blokir IP ini sekarang">
                                            <x-heroicon-o-no-symbol class="w-4 h-4" />
                                            Ban
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-base-content/40">
                                    <x-heroicon-o-shield-check class="w-10 h-10 mx-auto text-success/40 mb-2" />
                                    <p class="font-medium text-sm">Tidak ada rekaman ancaman pada filter ini.</p>
                                    <p class="text-xs text-base-content/50">Sistem dalam status aman atau belum ada bot yang terjaring.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($incidents->hasPages())
                <div class="p-3 border-t border-base-200">
                    {{ $incidents->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- TAB 2: DAFTAR IP TERBLOKIR (BLACKLIST)                        -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if ($activeTab === 'blocked')
        <div class="bg-base-100 rounded-xl border border-base-300 shadow-sm flex flex-col">
            <div class="p-4 border-b border-base-200 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-base text-base-content">Daftar IP Terblokir (Firewall Blacklist)</h2>
                    <p class="text-xs text-base-content/60">Semua request dari IP ini langsung ditolak dengan HTTP 403 Forbidden secara instan.</p>
                </div>
                <button wire:click="openBanModal('')" class="btn btn-error btn-sm gap-1 text-white shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Blokir IP Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra w-full text-xs">
                    <thead>
                        <tr class="bg-base-200/50 text-base-content/70">
                            <th>Alamat IP</th>
                            <th>Alasan Pemblokiran</th>
                            <th>Tipe Serangan</th>
                            <th>Oleh</th>
                            <th>Waktu Blokir</th>
                            <th>Kedaluwarsa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blockedIps as $b)
                            <tr class="hover">
                                <td class="font-mono font-bold text-error">
                                    {{ $b->ip_address }}
                                </td>
                                <td>
                                    {{ $b->reason ?? '-' }}
                                    @if($b->is_auto_ban)
                                        <span class="badge badge-xs badge-outline badge-error ml-1">Auto</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-ghost badge-xs">{{ $b->threat_type ?? 'Manual' }}</span>
                                </td>
                                <td>
                                    {{ $b->user ? $b->user->name : 'Sistem (Auto)' }}
                                </td>
                                <td class="whitespace-nowrap text-base-content/70 font-mono text-[11px]">
                                    {{ $b->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    @if($b->expires_at)
                                        <span class="font-mono text-[11px] {{ $b->expires_at->isPast() ? 'text-error' : 'text-base-content/80' }}">
                                            {{ $b->expires_at->format('Y-m-d H:i') }}
                                            ({{ $b->expires_at->diffForHumans() }})
                                        </span>
                                    @else
                                        <span class="badge badge-neutral badge-xs">Permanen</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button 
                                        wire:click="unbanIp('{{ $b->ip_address }}')" 
                                        wire:confirm="Yakin ingin mencabut blokir untuk IP {{ $b->ip_address }}?"
                                        class="btn btn-xs btn-outline btn-success"
                                    >
                                        Buka Blokir (Unban)
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/40">
                                    <p class="font-medium text-sm">Tidak ada IP yang sedang diblokir saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- TAB 3: IP WHITELIST & PENGATURAN                              -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if ($activeTab === 'whitelist')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Whitelist -->
            <div class="bg-base-100 rounded-xl border border-base-300 p-5 shadow-sm">
                <h2 class="font-bold text-base text-base-content mb-1">Tambah IP Whitelist</h2>
                <p class="text-xs text-base-content/60 mb-4">
                    IP dalam whitelist akan selalu diizinkan dan kebal terhadap deteksi ancaman otomatis.
                </p>

                <form wire:submit="addWhitelist" class="space-y-3">
                    <div>
                        <label class="label label-text text-xs font-semibold">Alamat IP (IPv4 / IPv6)</label>
                        <input 
                            type="text" 
                            wire:model="newWhitelistIp" 
                            placeholder="Contoh: 103.111.4.15" 
                            class="input input-sm input-bordered w-full font-mono text-xs @error('newWhitelistIp') input-error @enderror"
                        />
                        @error('newWhitelistIp') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Label / Nama Lokasi</label>
                        <input 
                            type="text" 
                            wire:model="newWhitelistLabel" 
                            placeholder="Contoh: Kantor Pusat, VPN IT, Server Bot" 
                            class="input input-sm input-bordered w-full text-xs @error('newWhitelistLabel') input-error @enderror"
                        />
                        @error('newWhitelistLabel') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full mt-2">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Simpan ke Whitelist
                    </button>
                </form>

                <div class="alert alert-info shadow-none bg-info/10 text-info text-xs mt-4 p-3 rounded-lg">
                    <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                    <span>IP Localhost (`127.0.0.1`, `::1`) sudah otomatis masuk whitelist bawaan sistem.</span>
                </div>
            </div>

            <!-- Daftar Whitelist -->
            <div class="lg:col-span-2 bg-base-100 rounded-xl border border-base-300 shadow-sm flex flex-col">
                <div class="p-4 border-b border-base-200">
                    <h2 class="font-bold text-base text-base-content">Daftar IP Whitelist Aktif</h2>
                    <p class="text-xs text-base-content/60">IP terdaftar di bawah ini tidak akan pernah diblokir.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-sm table-zebra w-full text-xs">
                        <thead>
                            <tr class="bg-base-200/50 text-base-content/70">
                                <th>Alamat IP</th>
                                <th>Label / Keterangan</th>
                                <th>Ditambahkan Oleh</th>
                                <th>Waktu</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($whitelists as $w)
                                <tr class="hover">
                                    <td class="font-mono font-bold text-success">
                                        {{ $w->ip_address }}
                                    </td>
                                    <td class="font-medium text-base-content">
                                        {{ $w->label }}
                                    </td>
                                    <td>
                                        {{ $w->user ? $w->user->name : 'Sistem' }}
                                    </td>
                                    <td class="text-base-content/70 font-mono text-[11px]">
                                        {{ $w->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <button 
                                            wire:click="removeWhitelist('{{ $w->ip_address }}')" 
                                            wire:confirm="Hapus {{ $w->ip_address }} dari whitelist?"
                                            class="btn btn-ghost btn-xs text-error"
                                        >
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-base-content/40">
                                        Belum ada IP kustom yang di-whitelist.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- MODAL 1: DETAIL INSIDEN KEAMANAN                              -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if ($selectedIncident)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl bg-base-100 border border-base-300">
                <div class="flex items-center justify-between pb-3 border-b border-base-200">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-{{ $selectedIncident->severity_badge_variant }} font-bold uppercase">
                            {{ $selectedIncident->severity }}
                        </span>
                        <h3 class="font-bold text-base text-base-content">Detail Rekaman Ancaman #{{ $selectedIncident->id }}</h3>
                    </div>
                    <button wire:click="closeIncidentDetail" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>

                <div class="py-4 space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-base-content/60 font-semibold block">Waktu Kejadian:</span>
                            <span class="font-mono text-base-content">{{ $selectedIncident->created_at->format('Y-m-d H:i:s') }} ({{ $selectedIncident->created_at->diffForHumans() }})</span>
                        </div>
                        <div>
                            <span class="text-base-content/60 font-semibold block">Alamat IP:</span>
                            <span class="font-mono font-bold text-error text-sm">{{ $selectedIncident->ip_address }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-base-content/60 font-semibold block">Jenis Ancaman:</span>
                        <span class="font-medium text-base-content">{{ $selectedIncident->threat_type_label }}</span>
                    </div>

                    <div>
                        <span class="text-base-content/60 font-semibold block">Target URL & Method:</span>
                        <div class="p-2 bg-base-200/80 rounded font-mono text-[11px] text-base-content break-all mt-1">
                            <span class="font-bold text-primary">{{ $selectedIncident->method }}</span> {{ $selectedIncident->url }}
                        </div>
                    </div>

                    <div>
                        <span class="text-base-content/60 font-semibold block">Payload / Indikator Deteksi:</span>
                        <div class="p-2 bg-base-200/80 rounded font-mono text-[11px] text-error break-all mt-1">
                            {{ $selectedIncident->payload ?? 'Tidak ada payload tambahan' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-base-content/60 font-semibold block">User-Agent:</span>
                        <div class="p-2 bg-base-200/80 rounded font-mono text-[11px] text-base-content/80 break-all mt-1">
                            {{ $selectedIncident->user_agent ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="modal-action border-t border-base-200 pt-3">
                    <button wire:click="openBanModal('{{ $selectedIncident->ip_address }}', '{{ $selectedIncident->threat_type }}')" class="btn btn-error btn-sm text-white">
                        <x-heroicon-o-no-symbol class="w-4 h-4" />
                        Blokir IP Ini
                    </button>
                    <button wire:click="closeIncidentDetail" class="btn btn-ghost btn-sm">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- MODAL 2: KONFIRMASI BAN IP                                    -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if ($showBanModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-md bg-base-100 border border-base-300">
                <h3 class="font-bold text-lg text-base-content flex items-center gap-2 text-error">
                    <x-heroicon-o-no-symbol class="w-5 h-5" />
                    Blokir Akses Alamat IP
                </h3>
                <p class="text-xs text-base-content/60 mt-1">
                    IP yang diblokir akan langsung menerima respon 403 Forbidden pada semua permintaan berikutnya.
                </p>

                <div class="py-4 space-y-3 text-xs">
                    <div>
                        <label class="label label-text text-xs font-semibold">Alamat IP Penyerang</label>
                        <input 
                            type="text" 
                            wire:model="banModalIp" 
                            class="input input-sm input-bordered w-full font-mono text-xs font-bold text-error @error('banModalIp') input-error @enderror"
                        />
                        @error('banModalIp') <span class="text-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Alasan Pemblokiran</label>
                        <input 
                            type="text" 
                            wire:model="banModalReason" 
                            placeholder="Contoh: Probe .env, Bot scanner, Gagal login 10x" 
                            class="input input-sm input-bordered w-full text-xs @error('banModalReason') input-error @enderror"
                        />
                        @error('banModalReason') <span class="text-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label label-text text-xs font-semibold">Durasi Pemblokiran</label>
                        <select wire:model="banModalDuration" class="select select-sm select-bordered w-full text-xs">
                            <option value="1_hour">1 Jam (Percobaan Awal)</option>
                            <option value="24_hours">24 Jam (Standar Serangan)</option>
                            <option value="7_days">7 Hari (Spammer Agresif)</option>
                            <option value="permanent">Permanen (Blacklist Selamanya)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-action border-t border-base-200 pt-3">
                    <button wire:click="confirmBan" class="btn btn-error btn-sm text-white">
                        Ya, Blokir Sekarang
                    </button>
                    <button wire:click="closeBanModal" class="btn btn-ghost btn-sm">Batal</button>
                </div>
            </div>
        </div>
    @endif
</div>
