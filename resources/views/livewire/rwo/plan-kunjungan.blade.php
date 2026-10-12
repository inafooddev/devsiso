<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-5 w-full h-full"
     x-data="{
         photoModalOpen: false,
         photoUrl: '',
         photoTitle: '',
         openPhoto(url, title) {
             this.photoUrl = url;
             this.photoTitle = title;
             this.photoModalOpen = true;
         }
     }"
     @keydown.escape.window="if (photoModalOpen) photoModalOpen = false">
    <x-slot name="title">Cek Plan Kunjungan RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.dashboard') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Dashboard</a>
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Pencapaian</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SKB</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Plan Kunjungan</a>
            <a href="{{ route('rwo.monitoring-pareto') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Monitoring Visit</a>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Filters --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-col gap-3 bg-base-200/30">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                    {{-- Filter Tanggal Mulai & Akhir (Maks 7 Hari) --}}
                    <div class="flex items-center gap-1.5 bg-base-100 px-2 py-1 rounded-xl border border-base-300">
                        <span class="text-[11px] font-semibold text-base-content/60 pl-1">Tanggal:</span>
                        <input wire:model.live="dateStart" type="date"
                               class="input input-xs border-0 bg-transparent focus:outline-none font-medium" title="Tanggal Mulai (Maks rentang 7 hari)">
                        <span class="text-xs text-base-content/40">s/d</span>
                        <input wire:model.live="dateEnd" type="date"
                               class="input input-xs border-0 bg-transparent focus:outline-none font-medium" title="Tanggal Akhir (Maks rentang 7 hari)">
                    </div>

                    {{-- Filter Region (Multi Select) --}}
                    <div class="relative group grow sm:grow-0 min-w-[140px]" x-data="{ open: false, selected: @entangle('selectedRegions'), searchRegion: '' }" x-init="$watch('selected', value => { if (!value || value.length === 0) searchRegion = ''; })">
                        <button @click="open = !open" type="button" class="btn btn-sm btn-outline btn-block justify-between border-base-300 bg-base-100 rounded-xl font-normal normal-case">
                            <span x-text="selected.length ? selected.length + ' Region' : 'Semua Region'"></span>
                            <x-heroicon-s-chevron-down class="w-4 h-4" />
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute z-50 w-56 mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-72 flex flex-col p-2">
                            <div class="p-1 mb-1 sticky top-0 bg-base-100 z-10">
                                <input x-model="searchRegion" type="text" placeholder="Cari region..." class="input input-xs input-bordered w-full rounded-lg text-xs" />
                            </div>
                            <div class="overflow-auto flex-1">
                                @foreach($regionOptions as $region)
                                    <label x-show="searchRegion === '' || '{{ addslashes(strtolower($region)) }}'.includes(searchRegion.toLowerCase())" class="label cursor-pointer justify-start gap-2 hover:bg-base-200 rounded-lg px-2 py-1">
                                        <input type="checkbox" wire:model.live="selectedRegions" value="{{ $region }}" class="checkbox checkbox-xs checkbox-primary" />
                                        <span class="label-text text-xs">{{ $region }}</span>
                                    </label>
                                @endforeach
                                @if($regionOptions->isEmpty())
                                    <div class="p-2 text-xs text-base-content/50 text-center">Tidak ada region</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Filter Area (Multi Select) --}}
                    <div class="relative group grow sm:grow-0 min-w-[140px]" x-data="{ open: false, selected: @entangle('selectedAreas'), searchArea: '' }" x-init="$watch('selected', value => { if (!value || value.length === 0) searchArea = ''; })">
                        <button @click="open = !open" type="button" class="btn btn-sm btn-outline btn-block justify-between border-base-300 bg-base-100 rounded-xl font-normal normal-case">
                            <span x-text="selected.length ? selected.length + ' Area' : 'Semua Area'"></span>
                            <x-heroicon-s-chevron-down class="w-4 h-4" />
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute z-50 w-56 mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-72 flex flex-col p-2">
                            <div class="p-1 mb-1 sticky top-0 bg-base-100 z-10">
                                <input x-model="searchArea" type="text" placeholder="Cari area..." class="input input-xs input-bordered w-full rounded-lg text-xs" />
                            </div>
                            <div class="overflow-auto flex-1">
                                @foreach($areaOptions as $area)
                                    <label x-show="searchArea === '' || '{{ addslashes(strtolower($area)) }}'.includes(searchArea.toLowerCase())" class="label cursor-pointer justify-start gap-2 hover:bg-base-200 rounded-lg px-2 py-1">
                                        <input type="checkbox" wire:model.live="selectedAreas" value="{{ $area }}" class="checkbox checkbox-xs checkbox-primary" />
                                        <span class="label-text text-xs">{{ $area }}</span>
                                    </label>
                                @endforeach
                                @if($areaOptions->isEmpty())
                                    <div class="p-2 text-xs text-base-content/50 text-center">Tidak ada area</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    {{-- Filter Tim (Multi Select) --}}
                    <div class="relative group grow sm:grow-0 min-w-[140px]" x-data="{ open: false, selected: @entangle('selectedTeams'), searchTeam: '' }" x-init="$watch('selected', value => { if (!value || value.length === 0) searchTeam = ''; })">
                        <button @click="open = !open" type="button" class="btn btn-sm btn-outline btn-block justify-between border-base-300 bg-base-100 rounded-xl font-normal normal-case">
                            <span x-text="selected.length ? selected.length + ' Tim' : 'Semua Tim'"></span>
                            <x-heroicon-s-chevron-down class="w-4 h-4" />
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute z-50 w-60 mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-72 flex flex-col p-2">
                            <div class="p-1 mb-1 sticky top-0 bg-base-100 z-10">
                                <input x-model="searchTeam" type="text" placeholder="Cari tim..." class="input input-xs input-bordered w-full rounded-lg text-xs" />
                            </div>
                            <div class="overflow-auto flex-1">
                                @foreach($teamOptions as $team)
                                    <label x-show="searchTeam === '' || '{{ addslashes(strtolower($team)) }}'.includes(searchTeam.toLowerCase())" class="label cursor-pointer justify-start gap-2 hover:bg-base-200 rounded-lg px-2 py-1">
                                        <input type="checkbox" wire:model.live="selectedTeams" value="{{ $team }}" class="checkbox checkbox-xs checkbox-primary" />
                                        <span class="label-text text-xs">{{ $team }}</span>
                                    </label>
                                @endforeach
                                @if($teamOptions->isEmpty())
                                    <div class="p-2 text-xs text-base-content/50 text-center">Tidak ada tim</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Filter Status Kelengkapan --}}
                    <div class="grow sm:grow-0 min-w-[135px]">
                        <select wire:model.live="statusFilter" class="select select-bordered select-sm w-full rounded-xl bg-base-100 border-base-300 text-xs">
                            <option value="">Semua Status Data</option>
                            <option value="lengkap">Data Lengkap</option>
                            <option value="belum">Belum Lengkap</option>
                        </select>
                    </div>

                    {{-- Filter Status SKB --}}
                    <div class="grow sm:grow-0 min-w-[140px]">
                        <select wire:model.live="skbFilter" class="select select-bordered select-sm w-full rounded-xl bg-base-100 border-base-300 text-xs">
                            <option value="">Semua Status SKB</option>
                            <option value="sudah">Sudah SKB (Semua)</option>
                            <option value="approve">SKB Approved</option>
                            <option value="pending">SKB Pending</option>
                            <option value="reject">SKB Rejected</option>
                            <option value="belum">Belum SKB</option>
                            <option value="non_skb">Non-SKB (BB)</option>
                        </select>
                    </div>

                    {{-- Search Input --}}
                    <div class="relative grow sm:grow-0 min-w-[190px]">
                        <input wire:model.live.debounce.400ms="search"
                               type="text"
                               placeholder="Cari kode/nama toko, tim..."
                               class="input input-sm input-bordered w-full rounded-xl bg-base-100 border-base-300 pr-7 text-xs" />
                        @if(!empty($search))
                            <button type="button" wire:click="$set('search', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content text-xs">
                                ✕
                            </button>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center gap-2 ml-auto">
                    <button wire:click="resetFilters" class="btn btn-sm btn-outline btn-error rounded-xl shadow-sm hover:shadow-md transition-all whitespace-nowrap gap-1 text-xs" title="Reset Filter">
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Reset</span>
                    </button>
                    <button wire:click="export" wire:loading.attr="disabled" class="btn btn-sm btn-success text-white rounded-xl shadow-sm hover:shadow-md transition-all whitespace-nowrap text-xs">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" wire:loading.remove wire:target="export" />
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export Excel
                    </button>
                </div>
            </div>

            {{-- Summary KPI Bar (Clickable Filter Pills) --}}
            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-base-300/60 text-xs">
                <button type="button"
                        wire:click="clearQuickFilters"
                        class="flex items-center gap-2 px-3 py-1 rounded-lg border transition-all {{ ($statusFilter === '' && $skbFilter === '') ? 'bg-primary/10 border-primary text-primary font-bold shadow-xs' : 'bg-base-100 border-base-300 text-base-content/70 hover:bg-base-200' }}">
                    <span>Total Plan:</span>
                    <span class="badge badge-xs badge-primary font-bold">{{ number_format($summary['total'] ?? 0, 0, ',', '.') }}</span>
                </button>

                <button type="button"
                        wire:click="toggleStatusFilter('lengkap')"
                        class="flex items-center gap-2 px-3 py-1 rounded-lg border transition-all {{ $statusFilter === 'lengkap' ? 'bg-success/15 border-success text-success font-bold shadow-xs' : 'bg-base-100 border-base-300 text-base-content/70 hover:bg-base-200' }}"
                        title="Klik untuk toggle filter Data Lengkap">
                    <span>Data Lengkap:</span>
                    <span class="badge badge-xs badge-success text-white font-bold">{{ number_format($summary['lengkap'] ?? 0, 0, ',', '.') }} ({{ number_format($summary['pct_lengkap'] ?? 0, 1, ',', '.') }}%)</span>
                </button>

                <button type="button"
                        wire:click="toggleStatusFilter('belum')"
                        class="flex items-center gap-2 px-3 py-1 rounded-lg border transition-all {{ $statusFilter === 'belum' ? 'bg-error/15 border-error text-error font-bold shadow-xs' : 'bg-base-100 border-base-300 text-base-content/70 hover:bg-base-200' }}"
                        title="Klik untuk toggle filter Data Belum Lengkap">
                    <span>Data Belum:</span>
                    <span class="badge badge-xs badge-error text-white font-bold">{{ number_format($summary['belum'] ?? 0, 0, ',', '.') }}</span>
                </button>

                <span class="text-base-content/20 hidden sm:inline">|</span>

                <button type="button"
                        wire:click="toggleSkbFilter('sudah')"
                        class="flex items-center gap-2 px-3 py-1 rounded-lg border transition-all {{ in_array($skbFilter, ['sudah', 'approve', 'pending', 'reject']) ? 'bg-info/15 border-info text-info font-bold shadow-xs' : 'bg-base-100 border-base-300 text-base-content/70 hover:bg-base-200' }}"
                        title="Approved: {{ number_format($summary['skb_approve'] ?? 0, 0, ',', '.') }} | Pending: {{ number_format($summary['skb_pending'] ?? 0, 0, ',', '.') }} | Rejected: {{ number_format($summary['skb_reject'] ?? 0, 0, ',', '.') }}">
                    <span>Sudah SKB:</span>
                    <span class="badge badge-xs badge-info text-white font-bold">{{ number_format($summary['sudah_skb'] ?? 0, 0, ',', '.') }}</span>
                </button>

                <button type="button"
                        wire:click="toggleSkbFilter('belum')"
                        class="flex items-center gap-2 px-3 py-1 rounded-lg border transition-all {{ $skbFilter === 'belum' ? 'bg-warning/15 border-warning text-warning font-bold shadow-xs' : 'bg-base-100 border-base-300 text-base-content/70 hover:bg-base-200' }}"
                        title="Klik untuk toggle filter Belum SKB">
                    <span>Belum SKB:</span>
                    <span class="badge badge-xs badge-warning font-bold">{{ number_format($summary['belum_skb'] ?? 0, 0, ',', '.') }}</span>
                </button>

                <span class="ml-auto text-[11px] text-base-content/50 hidden xl:inline">
                    *Klik "Lihat" pada SKB, Maps, atau Foto untuk pratinjau langsung
                </span>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto w-full relative" style="isolation: auto;">
            <div wire:loading.flex
                 wire:target="dateStart, dateEnd, search, statusFilter, skbFilter, selectedRegions, selectedAreas, selectedTeams, resetFilters, toggleStatusFilter, toggleSkbFilter, clearQuickFilters, gotoPage, nextPage, previousPage"
                 class="absolute inset-0 items-center justify-center bg-base-100/60 z-30 backdrop-blur-[1px]">
                <div class="bg-base-100 px-4 py-2.5 rounded-xl shadow-lg border border-base-300 flex items-center gap-3">
                    <span class="loading loading-spinner loading-sm text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/80">Memuat data plan kunjungan...</span>
                </div>
            </div>

            <table class="table table-sm table-zebra table-pin-rows table-pin-cols w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm sticky top-0 z-20">
                    <tr>
                        <th class="w-12 text-center sticky left-0 z-20 bg-base-300">No</th>
                        <th class="text-center">Tanggal</th>
                        <th>Region / Area</th>
                        <th>Nama Team</th>
                        <th>Cust No</th>
                        <th>Cust Name</th>
                        <th>Alamat</th>
                        <th class="text-center bg-base-200 text-primary">Status SKB</th>
                        <th class="text-center bg-base-200 text-primary">Status Data</th>
                        <th class="text-center bg-base-200">No HP</th>
                        <th class="text-center bg-base-200">Pemilik Toko</th>
                        <th class="text-center bg-base-200">Maps</th>
                        <th class="text-center bg-base-200">Tampak Depan</th>
                        <th class="text-center bg-base-200">Tampak Dalam</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                @forelse ($records as $index => $row)
                    @php
                        $hasNoHp = !empty(trim((string) ($row->no_hp ?? '')));
                        $hasPemilik = !empty(trim((string) ($row->nama_pemilik_toko ?? '')));
                        $hasLat = !empty(trim((string) ($row->latitude ?? '')));
                        $hasLng = !empty(trim((string) ($row->longitude ?? '')));
                        $hasMaps = $hasLat && $hasLng;
                        $hasDepan = !empty(trim((string) ($row->tampak_depan ?? '')));
                        $hasDalam = !empty(trim((string) ($row->tampak_dalam ?? '')));

                        $filledCount = ($hasNoHp ? 1 : 0)
                                     + ($hasPemilik ? 1 : 0)
                                     + ($hasMaps ? 1 : 0)
                                     + ($hasDepan ? 1 : 0)
                                     + ($hasDalam ? 1 : 0);
                        $isLengkap = ($filledCount === 5);
                        $mapsUrl = $hasMaps
                            ? 'https://www.google.com/maps?q=' . urlencode(trim($row->latitude) . ',' . trim($row->longitude))
                            : null;

                        $isBB = ($row->jenis_reward ?? 'CB') === 'BB';
                        $hasSkb = !empty($row->skb_customer_code);
                        $hasFotoSkb = !empty(trim((string) ($row->foto_skb ?? '')));
                    @endphp
                    <tr class="group text-[11px] hover:bg-base-200/60 transition-colors" wire:key="plan-{{ $row->tanggal }}-{{ $row->custno }}-{{ $index }}">
                        <td class="text-center sticky left-0 bg-base-100 group-hover:bg-base-200 transition-colors z-10">
                            <span class="font-semibold text-base-content/50">{{ $records->firstItem() + $index }}</span>
                        </td>
                        <td class="text-center font-mono text-base-content/80">
                            {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <div class="flex flex-col leading-tight max-w-[140px]">
                                <span class="font-bold text-base-content/80 truncate" title="{{ $row->nama_region }}">{{ $row->nama_region ?: '-' }}</span>
                                <span class="text-[10px] text-base-content/55 truncate" title="{{ $row->nama_area }}">{{ $row->nama_area ?: '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col leading-tight max-w-[150px]">
                                <span class="font-semibold text-base-content truncate" title="{{ $row->nama_team }}">{{ $row->nama_team ?: '-' }}</span>
                                <span class="text-[10px] text-base-content/50 truncate" title="{{ $row->distributor_name }}">{{ $row->distributor_name ?: '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col leading-tight">
                                <span class="font-mono font-bold text-base-content/85" title="Kode JKS (Eskalink)">{{ $row->custno }}</span>
                                @if(!empty($row->uniq_kd) && $row->uniq_kd !== $row->custno)
                                    <span class="font-mono text-[9px] text-base-content/45" title="Uniq KD (Master Reward Outlet)">{{ $row->uniq_kd }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="max-w-[200px] truncate font-medium" title="{{ $row->custname }}">
                                {{ $row->custname }}
                            </div>
                        </td>
                        <td>
                            <div class="max-w-[150px] truncate text-base-content/70" title="{{ $row->addres }}">
                                {{ $row->addres ?: '-' }}
                            </div>
                        </td>

                        {{-- Status SKB (Bridged via reward_outlet.customer_code) --}}
                        <td class="text-center font-bold">
                            @if($isBB)
                                <span class="badge badge-xs badge-ghost text-base-content/50 px-2 py-2" title="Bonus Barang (Tidak Wajib SKB)">Non-SKB (BB)</span>
                            @elseif($hasSkb)
                                <div class="inline-flex items-center gap-1">
                                    @if($row->skb_is_approved === true)
                                        <span class="badge badge-xs badge-success text-white font-bold px-2 py-2">Approved</span>
                                    @elseif($row->skb_is_approved === false)
                                        <span class="badge badge-xs badge-error text-white font-bold px-2 py-2" title="{{ $row->skb_reason ? 'Alasan: ' . $row->skb_reason : 'Rejected' }}">Rejected</span>
                                    @else
                                        <span class="badge badge-xs badge-warning font-bold px-2 py-2">Pending</span>
                                    @endif

                                    @if($hasFotoSkb)
                                        <button type="button"
                                                @click="openPhoto('{{ \Illuminate\Support\Facades\Storage::url($row->foto_skb) }}', 'Foto Dokumen SKB — {{ addslashes($row->custname) }}')"
                                                class="btn btn-ghost btn-xs text-info px-1 h-5 min-h-0"
                                                title="Klik untuk lihat Foto SKB">
                                            <span class="text-[10px] underline">Lihat</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="badge badge-xs badge-outline badge-error font-bold px-2 py-2">Belum SKB</span>
                            @endif
                        </td>

                        {{-- Status Kelengkapan Data --}}
                        <td class="text-center font-bold">
                            @if($isLengkap)
                                <span class="badge badge-xs badge-success text-white font-bold px-2 py-2">Lengkap (5/5)</span>
                            @else
                                <span class="badge badge-xs badge-error text-white font-bold px-2 py-2">Belum ({{ $filledCount }}/5)</span>
                            @endif
                        </td>
                        
                        <td class="text-center" title="{{ $hasNoHp ? $row->no_hp : 'Belum diisi' }}">
                            @if($hasNoHp)
                                <div class="inline-flex items-center gap-1 text-success font-mono text-[10px]">
                                    <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
                                    <span class="max-w-[85px] truncate">{{ $row->no_hp }}</span>
                                </div>
                            @else
                                <span class="text-error/50 font-bold">✕</span>
                            @endif
                        </td>

                        <td class="text-center" title="{{ $hasPemilik ? $row->nama_pemilik_toko : 'Belum diisi' }}">
                            @if($hasPemilik)
                                <div class="inline-flex items-center gap-1 text-success text-[10px]">
                                    <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
                                    <span class="max-w-[100px] truncate text-base-content/80">{{ $row->nama_pemilik_toko }}</span>
                                </div>
                            @else
                                <span class="text-error/50 font-bold">✕</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($hasMaps)
                                <a href="{{ $mapsUrl }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-ghost btn-xs text-success gap-1 px-1.5 h-6 min-h-0"
                                   title="Koordinat: {{ $row->latitude }}, {{ $row->longitude }} — Klik untuk buka Google Maps">
                                    <x-heroicon-s-check-circle class="w-4 h-4" />
                                    <span class="text-[10px] underline">Lihat</span>
                                </a>
                            @else
                                <span class="text-error/50 font-bold" title="Titik koordinat belum lengkap">✕</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($hasDepan)
                                <button type="button"
                                        @click="openPhoto('{{ \Illuminate\Support\Facades\Storage::url($row->tampak_depan) }}', 'Foto Tampak Depan — {{ addslashes($row->custname) }}')"
                                        class="btn btn-ghost btn-xs text-success gap-1 px-1.5 h-6 min-h-0"
                                        title="Klik untuk lihat Foto Tampak Depan">
                                    <x-heroicon-s-check-circle class="w-4 h-4" />
                                    <span class="text-[10px] underline">Lihat</span>
                                </button>
                            @else
                                <span class="text-error/50 font-bold">✕</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($hasDalam)
                                <button type="button"
                                        @click="openPhoto('{{ \Illuminate\Support\Facades\Storage::url($row->tampak_dalam) }}', 'Foto Tampak Dalam — {{ addslashes($row->custname) }}')"
                                        class="btn btn-ghost btn-xs text-success gap-1 px-1.5 h-6 min-h-0"
                                        title="Klik untuk lihat Foto Tampak Dalam">
                                    <x-heroicon-s-check-circle class="w-4 h-4" />
                                    <span class="text-[10px] underline">Lihat</span>
                                </button>
                            @else
                                <span class="text-error/50 font-bold">✕</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center py-10 text-base-content/50">
                            Tidak ada data plan kunjungan ditemukan untuk filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="p-3 border-t border-base-300 bg-base-100 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="text-xs text-base-content/50 font-medium">
                @if($records->total() > 0)
                    Menampilkan <span class="font-bold text-base-content/70">{{ $records->firstItem() }}</span> –
                    <span class="font-bold text-base-content/70">{{ $records->lastItem() }}</span>
                    dari <span class="font-bold text-primary">{{ number_format($records->total(), 0, ',', '.') }}</span> data
                @else
                    Tidak ada data ditemukan
                @endif
            </div>
            @if($records->hasPages())
                {{ $records->links() }}
            @endif
        </div>
    </div>

    {{-- Photo Lightbox Modal --}}
    <div x-show="photoModalOpen"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-[99999] bg-black/80 backdrop-blur-xs flex items-center justify-center p-4"
         @click.self="photoModalOpen = false">
        <div class="bg-base-100 rounded-2xl max-w-3xl w-full overflow-hidden shadow-2xl border border-base-300 flex flex-col max-h-[90vh]">
            <div class="px-4 py-3 border-b border-base-300 flex items-center justify-between gap-3">
                <h4 class="font-bold text-sm text-base-content truncate" x-text="photoTitle"></h4>
                <button type="button" class="btn btn-xs btn-circle btn-ghost" @click="photoModalOpen = false">✕</button>
            </div>
            <div class="p-4 bg-base-200/50 flex-1 overflow-auto flex items-center justify-center min-h-[300px]">
                <img :src="photoUrl" :alt="photoTitle" class="max-h-[75vh] w-auto object-contain rounded-lg shadow-md" />
            </div>
        </div>
    </div>
</div>
