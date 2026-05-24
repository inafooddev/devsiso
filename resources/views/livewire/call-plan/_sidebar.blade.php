{{-- ============================================================
     SIDEBAR: Filter, List Toko, Kontrol Hari/Minggu
     ============================================================ --}}
<div class="w-[340px] flex flex-col bg-base-100 border-r border-base-200 shadow-xl z-20 h-full flex-shrink-0 min-h-0">

    {{-- Header Sidebar --}}
    <div class="p-4 border-b border-base-200">

        <div class="flex justify-between items-center mb-4">
            <h1 class="font-bold text-base-content text-lg">Data Kunjungan</h1>
            <x-ui.button variant="neutral" size="xs" outline x-on:click="$wire.set('showFilterModal', true)">
                <x-heroicon-s-adjustments-horizontal class="w-3.5 h-3.5 mr-1" />
                Filter Area
            </x-ui.button>
        </div>

        {{-- Pilih Salesman --}}
        <div class="form-control mb-3 {{ !$selectedDistributor ? 'opacity-50 pointer-events-none' : '' }}">
            <label class="label py-1">
                <span class="label-text text-[10px] font-bold uppercase tracking-wider">Pilih Salesman</span>
            </label>
            <select wire:model.live.debounce.250ms="selectedSalesman" class="select select-sm select-bordered w-full">
                <option value="">-- Pilih Salesman --</option>
                @if($selectedDistributor)
                    <option value="all" class="font-bold text-primary">-- SEMUA SALESMAN --</option>
                @endif
                @foreach($salesmen as $sls)
                    <option value="{{ $sls->slsno }}">{{ $sls->slsname }}</option>
                @endforeach
            </select>
        </div>

        {{-- Mode Pewarnaan --}}
        <div class="form-control mb-4">
            <label class="label py-1">
                <span class="label-text text-[10px] font-bold uppercase tracking-wider">Mode Pewarnaan Titik</span>
            </label>
            <div class="join w-full">
                <button @click="legendType = 'day'; renderMarkers()"
                        :class="legendType === 'day' ? 'btn-primary' : 'btn-ghost'"
                        class="btn btn-xs join-item flex-1">HARI</button>
                <button @click="legendType = 'week'; renderMarkers()"
                        :class="legendType === 'week' ? 'btn-primary' : 'btn-ghost'"
                        class="btn btn-xs join-item flex-1">WEEK</button>
                <button @click="legendType = 'salesman'; renderMarkers()"
                        :class="legendType === 'salesman' ? 'btn-primary' : 'btn-ghost'"
                        class="btn btn-xs join-item flex-1">SE</button>
            </div>
        </div>

        {{-- Toggle Non-Rute --}}
        <div class="mb-3">
            <button wire:click="toggleNonRute"
                    class="btn btn-sm w-full {{ $showNonRute ? 'btn-error' : 'btn-outline btn-neutral' }}">
                @if($showNonRute)
                    <x-heroicon-s-eye class="w-4 h-4" /> MENAMPILKAN NON-RUTE
                @else
                    <x-heroicon-s-eye-slash class="w-4 h-4" /> LIHAT DATA NON-RUTE
                @endif
            </button>
        </div>

        {{-- Filter Minggu & Hari --}}
        <div class="flex gap-2 {{ $showNonRute ? 'opacity-30 pointer-events-none' : '' }}">

            {{-- Minggu --}}
            <div class="flex-1">
                <div class="label-text text-[10px] font-bold uppercase tracking-wider text-base-content/50 mb-1">Minggu</div>
                <div class="flex flex-col gap-1">
                    @foreach($options['weeks'] as $week)
                        <button wire:click="toggleWeek('{{ $week }}')" wire:key="sidebar-week-{{ $week }}"
                                class="btn btn-xs w-full justify-between {{ in_array($week, $selectedWeeks) ? 'btn-primary' : 'btn-ghost btn-outline' }}">
                            <span>{{ $week }}</span>
                            @if(in_array($week, $selectedWeeks))
                                <x-heroicon-s-check class="w-3 h-3" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Hari --}}
            <div class="flex-1">
                <div class="label-text text-[10px] font-bold uppercase tracking-wider text-base-content/50 mb-1">Hari</div>
                <div class="flex flex-col gap-1">
                    @foreach($options['days'] as $day)
                        @php $isSelected = in_array($day, $selectedDays); @endphp
                        <button wire:click="toggleDay('{{ $day }}')" wire:key="sidebar-day-{{ $day }}"
                                class="btn btn-xs w-full justify-between overflow-hidden border
                                       {{ $isSelected ? 'border-transparent text-white shadow-sm' : 'btn-ghost btn-outline' }}"
                                style="{{ $isSelected ? "background: linear-gradient(to right, {$dayColors[$day]['ganjil']}, {$dayColors[$day]['genap']})" : "" }}">
                            <span class="relative z-10">{{ $day }}</span>
                            @if($isSelected)
                                <x-heroicon-s-check class="w-3 h-3 relative z-10" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer Info --}}
        <div class="mt-3 flex justify-between items-center text-[10px] text-base-content/50 pt-2 border-t border-base-200">
            <div class="flex flex-col gap-0.5">
                <span>Menampilkan: <b class="text-base-content">{{ $isFilterApplied ? count($this->filteredStores) : 0 }}</b> toko</span>
                @if($isFilterApplied && $this->untaggedCount > 0)
                    <span class="flex items-center gap-1 text-error font-semibold">
                        <x-heroicon-s-map-pin class="w-3 h-3" />
                        Untagged: {{ $this->untaggedCount }} toko
                    </span>
                @endif
            </div>
            <div class="flex gap-2">
                <button wire:click="selectAllFilters()" class="link link-primary text-[10px] font-semibold">Select All</button>
                <button wire:click="resetFilters()" class="link link-error text-[10px] font-semibold">Reset</button>
            </div>
        </div>
    </div>

    {{-- Daftar Toko --}}
    <div class="flex-1 overflow-y-auto custom-scroll bg-base-200/30 p-2">

        {{-- Search Bar + Tombol Aksi --}}
        <div class="px-1 py-2 flex items-center gap-2 sticky top-0 bg-base-100/80 backdrop-blur-sm z-10 border-b border-base-200 mb-2">
            <label class="input input-sm input-bordered flex items-center gap-2 flex-1">
                <x-heroicon-s-magnifying-glass class="w-3.5 h-3.5 text-base-content/40" />
                <input wire:model.live.debounce.300ms="searchStore" type="text"
                       placeholder="Cari toko atau alamat..."
                       class="grow text-xs outline-none bg-transparent">
            </label>
            @canExport('call-plan.index')
            <x-ui.button variant="success" size="sm" outline x-on:click="$wire.set('showExportModal', true)" title="Export Excel">
                <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
            </x-ui.button>
            @endcanExport
            @canEdit('call-plan.index')
            <x-ui.button variant="primary" size="sm" x-on:click="$wire.set('showAddModal', true)" title="Tambah Customer">
                <x-heroicon-s-user-plus class="w-4 h-4" />
            </x-ui.button>
            @endcanEdit
        </div>

        {{-- Empty State --}}
        @if(!$isFilterApplied)
            <div class="flex flex-col items-center justify-center h-48 text-center p-6 border-2 border-dashed border-base-300 m-2 rounded-xl bg-base-100/50">
                <x-heroicon-o-funnel class="w-10 h-10 text-primary/30 mb-3" />
                <p class="text-xs text-base-content/50">Terapkan filter area dan pilih salesman.</p>
            </div>
        @else
            <div wire:loading.class="opacity-50 pointer-events-none">
                @forelse($filteredStores as $store)
                    <div wire:key="store-row-{{ $store['frute_id'] }}"
                         class="bg-base-100 p-3 rounded-lg border border-base-200 mb-2 hover:shadow-md hover:border-primary/30 transition group relative">

                        {{-- Color bar kiri --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l"
                             style="background-color: {{ $dayColors[$store['day']]['ganjil'] ?? '#9CA3AF' }}">
                        </div>

                        <div class="pl-2 flex justify-between items-start gap-2">
                            {{-- Info Toko --}}
                            <div @click="flyToStore(@js($store))" class="cursor-pointer flex-1 min-w-0">
                                <h3 class="font-bold text-sm text-base-content truncate">
                                    {{ $store['code'] }} — {{ $store['name'] }}
                                </h3>
                                <p class="text-[10px] text-base-content/50 line-clamp-1 mb-1 flex items-center gap-1">
                                    <x-heroicon-s-map-pin class="w-3 h-3 flex-shrink-0" />
                                    {{ $store['address'] }}
                                </p>
                                @if(!$store['lat'] || $store['lat'] == 0)
                                    <x-ui.badge variant="error" size="xs">Belum Tagging Lokasi</x-ui.badge>
                                @endif
                                <div class="flex items-center gap-2 mt-1.5">
                                    <x-ui.badge variant="neutral" size="xs">
                                        <x-heroicon-s-calendar class="w-2.5 h-2.5" />
                                        {{ $store['day'] }}
                                    </x-ui.badge>
                                    <x-ui.badge variant="neutral" size="xs">
                                        <x-heroicon-s-user class="w-2.5 h-2.5" />
                                        {{ $store['salesman'] }}
                                    </x-ui.badge>
                                </div>
                            </div>

                            {{-- Tombol Aksi (muncul saat hover) --}}
                            <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                @canEdit('call-plan.index')
                                <x-ui.button size="xs" variant="info" outline x-on:click="handleEdit(@js($store))">
                                    <x-heroicon-s-pencil class="w-3 h-3" />
                                </x-ui.button>
                                <x-ui.button size="xs" variant="error" outline
                                             wire:click="deleteStore({{ $store['frute_id'] }})"
                                             wire:confirm="Hapus rute ini?">
                                    <x-heroicon-s-trash class="w-3 h-3" />
                                </x-ui.button>
                                @endcanEdit
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-base-content/40 text-xs">
                        <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2 opacity-30" />
                        Tidak ada data rute.
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
