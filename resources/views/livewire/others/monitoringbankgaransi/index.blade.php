<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Monitoring Bank Garansi</x-slot>

    <!-- KPI Cards -->
    @php
        $stats = $this->kpiStats;
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 lg:gap-4 animate-in fade-in slide-in-from-bottom-4 duration-500 mb-2 md:mb-3">
        @php
            $totalBg = $stats['aktif'] + $stats['expired'];
            $pctAktif = $totalBg > 0 ? round(($stats['aktif'] / $totalBg) * 100, 1) : 0;
            $pctExpired = $totalBg > 0 ? round(($stats['expired'] / $totalBg) * 100, 1) : 0;
            $pct3Bulan = $totalBg > 0 ? round(($stats['kurang_3_bulan'] / $totalBg) * 100, 1) : 0;
            $pct2Bulan = $totalBg > 0 ? round(($stats['kurang_2_bulan'] / $totalBg) * 100, 1) : 0;
            $pct1Bulan = $totalBg > 0 ? round(($stats['kurang_1_bulan'] / $totalBg) * 100, 1) : 0;
        @endphp

        <!-- Aktif -->
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-white/10 group-hover:scale-125 transition-transform duration-500"></div>
            
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/90">Aktif</span>
                    <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <x-heroicon-s-check-circle class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-black text-white drop-shadow-sm mb-2">{{ $stats['aktif'] }}</div>
                    <div class="flex items-center justify-between text-[10px] text-white/80 font-bold mb-1.5">
                        <span>Dari Total BG</span>
                        <span>{{ $pctAktif }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-1">
                        <div class="bg-white h-1 rounded-full" style="width: {{ $pctAktif }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- < 3 Bulan -->
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-white/10 group-hover:scale-125 transition-transform duration-500"></div>
            
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/90">&lt; 3 Bulan</span>
                    <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <x-heroicon-s-clock class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-black text-white drop-shadow-sm mb-2">{{ $stats['kurang_3_bulan'] }}</div>
                    <div class="flex items-center justify-between text-[10px] text-white/80 font-bold mb-1.5">
                        <span>Dari Total BG</span>
                        <span>{{ $pct3Bulan }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-1">
                        <div class="bg-white h-1 rounded-full" style="width: {{ $pct3Bulan }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- < 2 Bulan -->
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-white/10 group-hover:scale-125 transition-transform duration-500"></div>
            
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/90">&lt; 2 Bulan</span>
                    <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-black text-white drop-shadow-sm mb-2">{{ $stats['kurang_2_bulan'] }}</div>
                    <div class="flex items-center justify-between text-[10px] text-white/80 font-bold mb-1.5">
                        <span>Dari Total BG</span>
                        <span>{{ $pct2Bulan }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-1">
                        <div class="bg-white h-1 rounded-full" style="width: {{ $pct2Bulan }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- < 1 Bulan -->
        <div class="relative overflow-hidden bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-white/10 group-hover:scale-125 transition-transform duration-500"></div>
            
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/90">&lt; 1 Bulan</span>
                    <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <x-heroicon-s-bell-alert class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-black text-white drop-shadow-sm mb-2">{{ $stats['kurang_1_bulan'] }}</div>
                    <div class="flex items-center justify-between text-[10px] text-white/80 font-bold mb-1.5">
                        <span>Dari Total BG</span>
                        <span>{{ $pct1Bulan }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-1">
                        <div class="bg-white h-1 rounded-full" style="width: {{ $pct1Bulan }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expired -->
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-700 to-slate-900 rounded-2xl p-4 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group text-white">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-white/10 group-hover:scale-125 transition-transform duration-500"></div>
            
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/90">Expired</span>
                    <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                        <x-heroicon-s-x-circle class="w-4 h-4 text-white" />
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-black text-white drop-shadow-sm mb-2">{{ $stats['expired'] }}</div>
                    <div class="flex items-center justify-between text-[10px] text-white/80 font-bold mb-1.5">
                        <span>Dari Total BG</span>
                        <span>{{ $pctExpired }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-1">
                        <div class="bg-white h-1 rounded-full" style="width: {{ $pctExpired }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-2 md:gap-3 w-full h-full">
        {{-- Notifikasi --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0">
                <x-heroicon-s-check-circle class="w-5 h-5" />
                <div>
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                    <div class="text-xs">{{ session('message') }}</div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0">
                <x-heroicon-s-x-circle class="w-5 h-5" />
                <div>
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                    <div class="text-xs">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">

            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Bank Garansi</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Monitoring masa berlaku Bank Garansi</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <!-- Search Component -->
                    <x-ui.search-input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari BG/Distributor..." 
                    />

                    <!-- Filter Status -->
                    <select wire:model.live="statusFilter" class="select select-sm select-bordered w-32 sm:w-36 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Status BG</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Expired">Expired</option>
                    </select>


                    <!-- Filter Jatuh Tempo -->
                    <select wire:model.live="masaBerlakuFilter" class="select select-sm select-bordered w-36 sm:w-40 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Waktu</option>
                        <option value="3_months">Mendekati < 3 Bulan</option>
                        <option value="2_months">Mendekati < 2 Bulan</option>
                        <option value="1_month">Mendekati < 1 Bulan</option>
                        <option value="2_weeks">Mendekati < 2 Minggu</option>
                    </select>

                    <!-- Filter Status Dist -->
                    <select wire:model.live="statusDistributorFilter" class="select select-sm select-bordered w-32 sm:w-36 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Status Dist.</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Inaktif">Inaktif</option>
                    </select>

                    <!-- Filter Progres -->
                    <select wire:model.live="progressFilter" class="select select-sm select-bordered w-32 sm:w-36 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Progres</option>
                        <option value="Belum">Belum</option>
                        <option value="Sudah di-Follow Up">Sdh Follow Up</option>
                        <option value="Close">Close</option>
                    </select>

                    <!-- Filter Region -->
                    <select wire:model.live="regionFilter" class="select select-sm select-bordered w-32 sm:w-36 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Region</option>
                        @foreach($this->availableRegions as $reg)
                            <option value="{{ $reg->region_code }}">{{ $reg->region_name }}</option>
                        @endforeach
                    </select>

                    <div class="flex flex-wrap items-center gap-1 md:gap-2">
                        {{-- @canEdit('monitoringbankgaransi.index') --}}
                        <button wire:click="exportExcel" class="btn btn-sm btn-success text-success-content rounded-xl shadow-sm hover:shadow-md transition-all">
                            <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                            <span class="hidden md:inline">Export</span>
                        </button>
                        
                        <button @click="$wire.set('isImportModalOpen', true)" class="btn btn-sm btn-info text-info-content rounded-xl shadow-sm hover:shadow-md transition-all">
                            <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                            <span class="hidden md:inline">Import</span>
                        </button>

                        <x-ui.action-button
                            type="add"
                            wire:click="openCreateModal"
                        />
                        {{-- @endcanEdit --}}
                    </div>
                </div>
            </div>

            {{-- Body Card (Tabel Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                @if($garansis->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-base-content/50 p-6">
                        <x-heroicon-o-document-text class="w-12 h-12 mb-2 opacity-50" />
                        <p class="text-sm font-medium">Tidak ada data Bank Garansi ditemukan.</p>
                    </div>
                @else
                    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                        <thead class="text-[11px] uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th class="min-w-[200px]">Distributor</th>
                                <th class="text-center">Status Dist.</th>
                                <th>Info Bank</th>
                                <th>Nilai Jaminan</th>
                                <th class="text-center">Tgl Terbit</th>
                                <th class="bg-base-200/50 text-[10px] uppercase tracking-wider text-base-content/70 font-extrabold w-32 cursor-pointer hover:bg-base-200 transition-colors">Tgl Jatuh Tempo</th>
                                <th class="bg-base-200/50 text-[10px] uppercase tracking-wider text-base-content/70 font-extrabold w-32 cursor-pointer hover:bg-base-200 transition-colors">Masa Berlaku</th>
                                <th class="bg-base-200/50 text-[10px] uppercase tracking-wider text-base-content/70 font-extrabold text-center w-24">Status BG</th>
                                <th class="bg-base-200/50 text-[10px] uppercase tracking-wider text-base-content/70 font-extrabold text-center w-28">Perpanjangan</th>
                                <th class="bg-base-200/50 text-[10px] uppercase tracking-wider text-base-content/70 font-extrabold text-center w-24">Progres</th>
                                <th class="bg-base-200/50 text-[10px] uppercase tracking-wider text-base-content/70 font-extrabold text-center w-32 rounded-tr-xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-[11px]">
                            @foreach ($garansis as $index => $garansi)
                                @php
                                    $daysLeft = \Carbon\Carbon::now()->startOfDay()->diffInDays($garansi->tanggal_jatuh_tempo->startOfDay(), false);
                                    $badgeClass = '';
                                    
                                    if ($garansi->status == 'Expired' || $daysLeft < 0) {
                                        $garansi->status = 'Expired'; // Auto visual update
                                    }

                                    if ($garansi->status == 'Expired' || $daysLeft <= 30) {
                                        $badgeClass = 'bg-error/20 text-error';
                                        $textColorClass = 'text-error';
                                    } elseif ($daysLeft <= 60) {
                                        $badgeClass = 'bg-warning/20 text-warning';
                                        $textColorClass = 'text-warning';
                                    } else {
                                        $badgeClass = 'bg-success/20 text-success';
                                        $textColorClass = 'text-success';
                                    }
                                @endphp
                                <tr wire:key="bg-{{ $garansi->id }}" class="hover:bg-base-200/50 transition-colors group">
                                    <th class="text-center">{{ $garansis->firstItem() + $index }}</th>
                                    
                                    {{-- Distributor --}}
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-bold text-[11px] {{ $textColorClass }} transition-colors truncate" title="{{ $garansi->distributor->distributor_name ?? '-' }}">{{ $garansi->distributor->short_name ?? '-' }}</span>
                                        </div>
                                    </td>

                                    {{-- Status Distributor --}}
                                    <td class="text-center">
                                        @if($garansi->distributor && $garansi->distributor->is_active)
                                            <div class="badge badge-success/10 text-success text-[10px] font-bold uppercase tracking-wider border-none">Aktif</div>
                                        @else
                                            <div class="badge badge-error/10 text-error text-[10px] font-bold uppercase tracking-wider border-none">Inaktif</div>
                                        @endif
                                    </td>

                                    {{-- Bank --}}
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[11px] font-semibold truncate">{{ $garansi->nama_bank }}</span>
                                            <div class="text-[10px] text-base-content/60 font-mono flex items-center gap-1">
                                                <span title="Nomor BG">{{ $garansi->nomor_jaminan }}</span>
                                                @if($garansi->nomor_seri)
                                                    <span class="text-base-content/30">•</span>
                                                    <span title="Nomor Seri" class="text-primary/70">{{ $garansi->nomor_seri }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Nilai --}}
                                    <td class="font-bold">
                                        Rp {{ number_format($garansi->nilai_jaminan, 0, ',', '.') }}
                                    </td>

                                    {{-- Terbit --}}
                                    <td class="text-center text-base-content/70">
                                        {{ $garansi->tanggal_terbit->translatedFormat('d M Y') }}
                                    </td>

                                    {{-- Jatuh Tempo --}}
                                    <td class="text-center">
                                        <span class="font-semibold">{{ $garansi->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</span>
                                    </td>

                                    {{-- Masa Berlaku (Monitoring) --}}
                                    <td class="text-center">
                                        <span class="badge badge-sm border-none {{ $badgeClass }} font-bold px-2 py-1 rounded-md text-[10px] uppercase tracking-wider">
                                            @if($daysLeft < 0)
                                                Lewat {{ abs($daysLeft) }} Hari
                                            @else
                                                {{ $daysLeft }} Hari
                                            @endif
                                        </span>
                                    </td>

                                    {{-- Status BG --}}
                                    <td class="text-center font-medium">
                                        @if($garansi->status === 'Aktif')
                                            <span class="badge badge-success badge-sm text-xs px-2.5 py-3 rounded-md font-bold border-0">{{ $garansi->status }}</span>
                                        @else
                                            <span class="badge badge-error badge-sm text-xs px-2.5 py-3 rounded-md font-bold border-0">{{ $garansi->status }}</span>
                                        @endif
                                    </td>

                                    {{-- Status Perpanjangan --}}
                                    <td class="text-center">
                                        @if($garansi->status_perpanjangan === 'Ya')
                                            <span class="badge badge-info badge-sm text-xs px-2 py-3 rounded-md font-bold bg-info/20 text-info border-0">Ya</span>
                                        @else
                                            <span class="badge badge-ghost badge-sm text-xs px-2 py-3 rounded-md font-medium text-base-content/50 border-0">Tidak</span>
                                        @endif
                                    </td>

                                    {{-- Status Progres --}}
                                    <td class="text-center">
                                        @if($garansi->progress_status === 'Sudah di-Follow Up')
                                            <span class="badge badge-primary badge-sm text-xs px-2 py-3 rounded-md font-bold border-0">{{ $garansi->progress_status }}</span>
                                        @elseif($garansi->progress_status === 'Close')
                                            <span class="badge badge-neutral badge-sm text-xs px-2 py-3 rounded-md font-bold border-0">{{ $garansi->progress_status }}</span>
                                        @else
                                            <span class="badge badge-error badge-sm text-xs px-2 py-3 rounded-md font-bold border-0 text-error bg-error/20">{{ $garansi->progress_status ?? 'Belum' }}</span>
                                        @endif
                                    </td>

                                    <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                        <div class="flex items-center justify-center gap-1">
                                            @if($daysLeft <= 90)
                                                <button wire:click="openFollowUpModal({{ $garansi->id }})" 
                                                        class="btn btn-ghost btn-sm btn-square rounded-xl text-primary hover:bg-primary/10 transition-all duration-200" title="Follow Up">
                                                    <x-heroicon-s-chat-bubble-left-ellipsis class="w-4 h-4" />
                                                </button>
                                            @endif
                                            {{-- @canEdit('monitoringbankgaransi.index') --}}
                                            <button wire:click="openEditModal({{ $garansi->id }})" 
                                                    class="btn btn-ghost btn-sm btn-square rounded-xl text-warning hover:bg-warning/10 transition-all duration-200" title="Edit">
                                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                            </button>
                                            <button wire:click="confirmDelete({{ $garansi->id }})" 
                                                    class="btn btn-ghost btn-sm btn-square rounded-xl text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                                <x-heroicon-s-trash class="w-4 h-4" />
                                            </button>
                                            {{-- @endcanEdit --}}
                                        </div>
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if($garansis->hasPages())
                <div class="p-3 md:p-4 border-t border-base-300 bg-base-50 shrink-0">
                    {{ $garansis->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Form (Create/Edit) --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl overflow-hidden ring-1 ring-base-content/5 max-h-[90vh] flex flex-col text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Bank Garansi' : 'Tambah Bank Garansi' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Kelola data bank garansi distributor</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save" class="overflow-y-auto">
                <div class="p-6 space-y-6 bg-base-100">
                    
                    {{-- Distributor Selection --}}
                    <div class="space-y-1.5 relative">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Distributor</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base-content/30">
                                <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                            </div>
                            <input wire:model.live.debounce.300ms="distributorSearch" type="text" placeholder="Ketik nama atau kode distributor..."
                                   class="input input-bordered w-full pl-11 bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            
                            @if(!$selectedDistributorName && count($this->activeDistributors) > 0)
                                <div class="absolute z-50 w-full mt-2 bg-base-100 border border-base-300 rounded-2xl shadow-xl overflow-y-auto max-h-60 animate-in fade-in zoom-in-95 duration-200">
                                    @foreach($this->activeDistributors as $distributor)
                                        <button type="button" wire:click="selectDistributor('{{ $distributor->distributor_code }}', '{{ $distributor->short_name }}')"
                                                class="w-full px-4 py-3 text-left hover:bg-base-200 flex items-center justify-between border-b border-base-200 last:border-0 transition-colors">
                                            <div class="flex flex-col">
                                                <div class="font-bold text-[11px] uppercase tracking-wider text-base-content">{{ $distributor->short_name ?? '-' }}</div>
                                            </div>
                                            <x-heroicon-s-chevron-right class="w-4 h-4 text-base-content/20" />
                                        </button>
                                    @endforeach
                                </div>
                            @elseif(!$selectedDistributorName && strlen($this->distributorSearch) >= 2)
                                <div class="absolute z-50 w-full mt-2 bg-base-100 border border-base-300 rounded-2xl shadow-xl p-4 text-center text-xs text-base-content/50">
                                    Distributor aktif tidak ditemukan.
                                </div>
                            @endif
                        </div>
                        
                        @if($selectedDistributorName)
                            <div class="mt-3 p-4 rounded-2xl bg-primary/5 border border-primary/10 flex items-center justify-between group/sel animate-in slide-in-from-top-2 duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                        <x-heroicon-s-user-circle class="w-4 h-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-primary">{{ $selectedDistributorName }}</span>
                                        <span class="text-[10px] text-base-content/40 font-mono tracking-tighter">{{ $distributor_code }}</span>
                                    </div>
                                </div>
                                <button type="button" wire:click="$set('distributor_code', ''); $set('selectedDistributorName', '');" class="btn btn-ghost btn-xs btn-circle text-base-content/20 hover:text-error hover:bg-error/10">
                                    <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        @endif
                        @error('distributor_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label for="nama_bank" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Bank</label>
                            <select wire:model.blur="nama_bank" id="nama_bank" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nama_bank') input-error @enderror">
                                <option value="">-- Pilih Bank --</option>
                                <option value="Bank Mandiri">Bank Mandiri</option>
                                <option value="Bank Rakyat Indonesia (BRI)">Bank Rakyat Indonesia (BRI)</option>
                                <option value="Bank Central Asia (BCA)">Bank Central Asia (BCA)</option>
                                <option value="Bank Negara Indonesia (BNI)">Bank Negara Indonesia (BNI)</option>
                                <option value="Bank Syariah Indonesia (BSI)">Bank Syariah Indonesia (BSI)</option>
                                <option value="Bank Tabungan Negara (BTN)">Bank Tabungan Negara (BTN)</option>
                                <option value="Bank CIMB Niaga">Bank CIMB Niaga</option>
                                <option value="Bank Permata">Bank Permata</option>
                                <option value="Bank Danamon">Bank Danamon</option>
                                <option value="Bank Panin">Bank Panin</option>
                                <option value="Bank Mega">Bank Mega</option>
                                <option value="Bank OCBC NISP">Bank OCBC NISP</option>
                                <option value="Bank Maybank Indonesia">Bank Maybank Indonesia</option>
                                <option value="Bank BJB">Bank BJB</option>
                                <option value="Bank DKI">Bank DKI</option>
                                <option value="Bank Muamalat">Bank Muamalat</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            @error('nama_bank') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="nomor_jaminan" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nomor Jaminan (BG)</label>
                            <input wire:model.blur="nomor_jaminan" type="text" id="nomor_jaminan" placeholder="Cth: BG-12345678"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nomor_jaminan') input-error @enderror">
                            @error('nomor_jaminan') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label for="nomor_seri" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nomor Seri (Opsional)</label>
                            <input wire:model.blur="nomor_seri" type="text" id="nomor_seri" placeholder="Cth: SR-98765432"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nomor_seri') input-error @enderror">
                            @error('nomor_seri') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label for="nilai_jaminan" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nilai Jaminan (Rp)</label>
                            <input wire:model.blur="nilai_jaminan" type="number" id="nilai_jaminan" placeholder="Cth: 100000000"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nilai_jaminan') input-error @enderror">
                            @error('nilai_jaminan') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="tanggal_terbit" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal Terbit</label>
                            <input wire:model.blur="tanggal_terbit" type="date" id="tanggal_terbit"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('tanggal_terbit') input-error @enderror">
                            @error('tanggal_terbit') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="tanggal_jatuh_tempo" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Jatuh Tempo (Masa Berlaku)</label>
                            <input wire:model.blur="tanggal_jatuh_tempo" type="date" id="tanggal_jatuh_tempo"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('tanggal_jatuh_tempo') input-error @enderror">
                            @error('tanggal_jatuh_tempo') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label pb-1">
                                <span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">Status Perpanjangan</span>
                            </label>
                            <select wire:model="status_perpanjangan" class="select select-bordered select-md w-full bg-base-100/50 focus:bg-base-100 transition-colors rounded-xl font-medium">
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                            </select>
                            @error('status_perpanjangan') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label for="keterangan" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Keterangan / Catatan Tambahan</label>
                            <textarea wire:model.blur="keterangan" id="keterangan" rows="3" placeholder="Opsional..."
                                      class="textarea textarea-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300"></textarea>
                            @error('keterangan') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label for="dokumen_lampiran" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto/Dokumen BG (Opsional)</label>
                            
                            <input wire:model="dokumen_lampiran" type="file" id="dokumen_lampiran" accept="image/*"
                                   class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('dokumen_lampiran') input-error @enderror" />
                            
                            <div wire:loading wire:target="dokumen_lampiran" class="text-xs text-info mt-1 ml-1 flex items-center gap-1">
                                <span class="loading loading-spinner loading-xs"></span> Mengunggah file...
                            </div>
                            
                            @error('dokumen_lampiran') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                            
                            {{-- Preview Area --}}
                            @if ($dokumen_lampiran)
                                <div class="mt-3 relative rounded-xl overflow-hidden border border-base-300 shadow-sm w-32 h-32 group">
                                    <img src="{{ $dokumen_lampiran->temporaryUrl() }}" class="w-full h-full object-cover">
                                    <button type="button" wire:click="$set('dokumen_lampiran', null)" class="absolute top-1 right-1 btn btn-xs btn-circle btn-error opacity-0 group-hover:opacity-100 transition-opacity">
                                        <x-heroicon-s-x-mark class="w-3 h-3" />
                                    </button>
                                </div>
                            @elseif ($dokumen_lampiran_lama)
                                <div class="mt-3 relative rounded-xl overflow-hidden border border-base-300 shadow-sm w-32 h-32 group">
                                    <img src="{{ Storage::disk('public')->url($dokumen_lampiran_lama) }}" class="w-full h-full object-cover">
                                </div>
                                <span class="text-[10px] text-base-content/50 mt-1 block">Foto BG Tersimpan</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 shrink-0">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan BG Baru' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div class="modal {{ $isDeleteModalOpen ? 'modal-open' : '' }} backdrop-blur-sm bg-base-300/40 modal-bottom sm:modal-middle">
        <div class="modal-box rounded-3xl shadow-2xl p-0 overflow-hidden max-w-sm w-full mx-4">
            <div class="bg-error/10 p-6 flex flex-col items-center justify-center border-b border-error/20">
                <div class="w-16 h-16 rounded-full bg-error/20 flex items-center justify-center mb-4">
                    <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-error" />
                </div>
                <h3 class="font-bold text-lg text-error text-center">Konfirmasi Hapus</h3>
            </div>
            <div class="p-6 text-center">
                <p class="text-base-content/70">Apakah Anda yakin ingin menghapus data Bank Garansi ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="flex items-center justify-center gap-3 p-6 pt-0">
                <button type="button" wire:click="$set('isDeleteModalOpen', false)" class="btn btn-ghost rounded-xl px-6">Batal</button>
                <button type="button" wire:click="delete" class="btn btn-error rounded-xl px-8 shadow-sm shadow-error/20 gap-2">
                    <span wire:loading.remove wire:target="delete">Hapus Data</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Import Excel --}}
    <div class="modal {{ $isImportModalOpen ? 'modal-open' : '' }} backdrop-blur-sm bg-base-300/40 modal-bottom sm:modal-middle">
        <div class="modal-box rounded-3xl shadow-2xl p-0 overflow-hidden max-w-md w-full mx-4">
            <div class="bg-gradient-to-br from-info to-info/80 p-6 flex flex-col items-center justify-center relative">
                <button wire:click="$set('isImportModalOpen', false)" class="btn btn-circle btn-sm btn-ghost absolute top-3 right-3 text-info-content/70 hover:bg-black/10">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mb-3">
                    <x-heroicon-o-arrow-up-tray class="w-8 h-8 text-white" />
                </div>
                <h3 class="font-bold text-xl text-white">Import Data BG</h3>
                <p class="text-info-content/80 text-sm mt-1 text-center">Upload file Excel (.xlsx) untuk menambahkan data secara massal.</p>
            </div>
            <form wire:submit.prevent="importExcel" class="p-6">
                <div class="space-y-4">
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <h4 class="font-bold text-sm mb-2">Langkah Import:</h4>
                        <ol class="list-decimal list-inside text-xs text-base-content/70 space-y-1">
                            <li>Download template Excel kosong.</li>
                            <li>Isi data Bank Garansi (Kode Distributor wajib sama dengan master).</li>
                            <li><span class="text-info/80 italic">Catatan: Kolom 'Nama Distributor' hanya untuk alat bantu baca (sistem hanya memproses Kode).</span></li>
                            <li>Upload file Excel yang sudah diisi.</li>
                        </ol>
                        <button type="button" wire:click="downloadTemplate" class="btn btn-sm btn-outline btn-info mt-3 w-full rounded-xl">
                            <x-heroicon-s-document-arrow-down class="w-4 h-4" /> Download Template
                        </button>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih File Excel</label>
                        <input type="file" wire:model="importFile" accept=".xlsx, .xls" class="file-input file-input-bordered file-input-info w-full rounded-2xl" />
                        <div wire:loading wire:target="importFile" class="text-xs text-info mt-1 ml-1 flex items-center gap-1">
                            <span class="loading loading-spinner loading-xs"></span> Mengupload file ke sistem...
                        </div>
                        @error('importFile') <span class="text-error text-xs ml-1 flex mt-1 items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3"/> {{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-base-300 flex justify-end gap-3">
                    <button type="button" wire:click="$set('isImportModalOpen', false)" class="btn btn-ghost rounded-xl normal-case">Batal</button>
                    <button type="submit" class="btn btn-info text-info-content rounded-xl px-8 normal-case shadow-sm gap-2" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="importExcel">Mulai Import</span>
                        <span wire:loading wire:target="importExcel" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reminder Modal -->
    <div class="modal {{ $showReminderModal ? 'modal-open' : '' }} backdrop-blur-sm bg-base-900/40 transition-all duration-300 z-50">
        <div class="modal-box rounded-3xl shadow-2xl p-0 overflow-hidden max-w-5xl w-full mx-4">
            <div class="bg-gradient-to-br from-warning to-warning/80 p-6 flex flex-col items-center justify-center relative">
                <button wire:click="$set('showReminderModal', false)" class="btn btn-circle btn-sm btn-ghost absolute top-3 right-3 text-warning-content/70 hover:bg-black/10">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mb-3">
                    <x-heroicon-o-bell-alert class="w-8 h-8 text-warning-content" />
                </div>
                <h3 class="font-bold text-xl text-warning-content">Perhatian! Bank Garansi Perlu Follow-up</h3>
                <p class="text-warning-content/80 text-sm mt-1 text-center">Terdapat {{ count($this->expiringBgs) }} Bank Garansi yang sudah Expired atau Mendekati Jatuh Tempo (< 3 Bulan).</p>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto overflow-y-auto max-h-[50vh] rounded-xl border border-base-200">
                    <table class="table table-sm table-pin-rows w-full text-xs">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th>Distributor</th>
                                <th>Bank</th>
                                <th>Nilai</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Progres</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->expiringBgs as $bg)
                                @php
                                    $diffDays = \Carbon\Carbon::now()->startOfDay()->diffInDays($bg->tanggal_jatuh_tempo->startOfDay(), false);
                                    $isExpired = $diffDays < 0;
                                    
                                    if ($isExpired || $diffDays <= 30) {
                                        $statusClass = 'badge-error';
                                    } else {
                                        $statusClass = 'badge-warning';
                                    }
                                    
                                    if ($isExpired) {
                                        $statusText = 'Expired (Lewat ' . abs($diffDays) . ' Hari)';
                                    } else {
                                        $statusText = 'Sisa ' . $diffDays . ' Hari';
                                    }
                                @endphp
                                <tr class="hover">
                                    <td class="font-semibold">{{ $bg->distributor ? $bg->distributor->short_name : '-' }}</td>
                                    <td>{{ $bg->nama_bank }}</td>
                                    <td>Rp {{ number_format($bg->nilai_jaminan, 0, ',', '.') }}</td>
                                    <td>{{ $bg->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                                    <td><div class="badge badge-sm {{ $statusClass }} font-bold">{{ $statusText }}</div></td>
                                    <td>
                                        @if($bg->progress_status === 'Sudah di-Follow Up')
                                            <span class="badge badge-primary badge-sm text-[10px] font-bold border-0">{{ $bg->progress_status }}</span>
                                        @else
                                            <span class="badge badge-error badge-sm text-[10px] font-bold border-0 text-error bg-error/20">{{ $bg->progress_status ?? 'Belum' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-base-content/50">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 pt-5 border-t border-base-300 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showReminderModal', false)" class="btn btn-outline rounded-xl normal-case">Tutup Peringatan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow Up Modal -->
    <div class="modal {{ $isFollowUpModalOpen ? 'modal-open' : '' }} backdrop-blur-sm bg-base-900/40 transition-all duration-300 z-50">
        <div class="modal-box rounded-3xl shadow-2xl p-0 overflow-hidden max-w-4xl w-full mx-4 flex flex-col h-[80vh]">
            <div class="bg-base-200/50 p-4 border-b border-base-300 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <x-heroicon-s-chat-bubble-left-ellipsis class="w-5 h-5 text-primary" />
                    Progres Follow Up: {{ $selectedBgForFollowUp ? ($selectedBgForFollowUp->distributor ? $selectedBgForFollowUp->distributor->short_name : '-') : '' }}
                </h3>
                <button wire:click="$set('isFollowUpModalOpen', false)" class="btn btn-circle btn-sm btn-ghost hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 bg-base-100 flex flex-col">
                <!-- Timeline History -->
                <div class="flex-1 space-y-4 mb-6">
                    @if($selectedBgForFollowUp && $selectedBgForFollowUp->followUps->count() > 0)
                        @foreach($selectedBgForFollowUp->followUps as $fu)
                            <div class="chat {{ $fu->user_id === auth()->id() ? 'chat-end' : 'chat-start' }}">
                                <div class="chat-header text-xs opacity-70 mb-1">
                                    {{ $fu->user->name ?? 'User' }}
                                    <time class="ml-1 text-[10px]">{{ $fu->created_at->format('d M Y H:i') }}</time>
                                </div>
                                <div class="chat-bubble {{ $fu->user_id === auth()->id() ? 'chat-bubble-primary text-primary-content' : 'chat-bubble-base-200' }} text-sm flex flex-col gap-2 overflow-hidden">
                                    @if($fu->attachment)
                                        <button type="button" onclick="document.getElementById('image_viewer_img').src = '{{ Storage::url($fu->attachment) }}'; document.getElementById('image_viewer_modal').showModal();" class="border-none bg-transparent p-0 m-0 text-left">
                                            <img src="{{ Storage::url($fu->attachment) }}" class="max-w-[200px] md:max-w-xs rounded-lg max-h-48 object-cover cursor-pointer hover:opacity-90 transition-opacity border border-base-100/20">
                                        </button>
                                    @endif
                                    <span>{{ $fu->catatan }}</span>
                                </div>
                                <div class="chat-footer opacity-50 text-[10px] mt-1 font-semibold">
                                    Status di-set: <span class="uppercase">{{ $fu->status_progress }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-base-content/40 space-y-3">
                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-12 h-12 opacity-20" />
                            <p>Belum ada riwayat follow up.</p>
                        </div>
                    @endif
                </div>

                <!-- Input Form -->
                <div class="mt-auto bg-base-200/30 p-4 rounded-2xl border border-base-300">
                    <form wire:submit.prevent="saveFollowUp">
                        <div class="flex flex-col gap-3">
                            <div class="form-control w-full max-w-xs">
                                <label class="label py-1"><span class="label-text text-xs font-semibold">Ubah Status Progres</span></label>
                                <select wire:model.defer="followUpStatus" class="select select-bordered select-sm w-full rounded-lg bg-base-100">
                                    <option value="Belum">Belum</option>
                                    <option value="Sudah di-Follow Up">Sudah di-Follow Up</option>
                                    <option value="Close">Close</option>
                                </select>
                            </div>
                            
                            <div class="form-control w-full" x-data="{
                                handlePaste(e) {
                                    if (e.clipboardData.files.length > 0) {
                                        const file = e.clipboardData.files[0];
                                        if (file.type.startsWith('image/')) {
                                            $wire.upload('followUpAttachment', file, (uploadedFilename) => {
                                                // Sukses
                                            }, () => {
                                                alert('Gagal mengunggah gambar');
                                            });
                                        }
                                    }
                                }
                            }" @reset-file-input.window="$refs.fileInput.value = ''">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-semibold">Catatan (Paste gambar di sini)</span>
                                    <span class="label-text-alt flex items-center gap-2">
                                        <span wire:loading wire:target="followUpAttachment" class="text-primary loading loading-spinner loading-xs"></span>
                                        <input type="file" x-ref="fileInput" wire:model="followUpAttachment" accept="image/*" class="file-input file-input-bordered file-input-xs w-full max-w-[150px]" />
                                    </span>
                                </label>
                                <textarea wire:model.defer="followUpCatatan" x-on:paste="handlePaste" class="textarea textarea-bordered h-20 rounded-xl bg-base-100 placeholder-base-content/30" placeholder="Tulis catatan atau hasil komunikasi... bisa paste (Ctrl+V) gambar bukti chat di sini."></textarea>
                                @error('followUpCatatan') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                                @error('followUpAttachment') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror

                                @if($followUpAttachment)
                                    <div class="mt-3 relative inline-block self-start">
                                        <img src="{{ $followUpAttachment->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-base-300 shadow-sm">
                                        <button type="button" wire:click="$set('followUpAttachment', null)" class="btn btn-circle btn-xs btn-error absolute -top-2 -right-2 text-white">
                                            <x-heroicon-s-x-mark class="w-3 h-3"/>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex justify-end mt-2">
                                <button type="submit" class="btn btn-primary btn-sm rounded-xl px-6">
                                    <x-heroicon-s-paper-airplane class="w-4 h-4 mr-1" />
                                    Kirim & Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <dialog id="image_viewer_modal" class="modal modal-bottom sm:modal-middle z-[60]">
        <div class="modal-box p-1 max-w-5xl bg-transparent shadow-none overflow-hidden flex flex-col items-center">
            <form method="dialog" class="w-full flex justify-end mb-2">
                <button class="btn btn-circle btn-sm btn-neutral text-white"><x-heroicon-s-x-mark class="w-4 h-4" /></button>
            </form>
            <img id="image_viewer_img" src="" class="max-w-full max-h-[85vh] rounded-xl object-contain shadow-2xl bg-base-100">
        </div>
        <form method="dialog" class="modal-backdrop bg-base-900/80">
            <button>close</button>
        </form>
    </dialog>
</div>
