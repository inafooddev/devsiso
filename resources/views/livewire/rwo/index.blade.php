<div>
    <x-slot name="title">RWO (Reward Outlet)</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
        {{-- Notifikasi --}}
        <div class="mb-6 space-y-3">
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                     class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">{{ session('message') }}</div>
                    </div>
                </div>
            @endif
            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- KPI Cards Summary --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
            {{-- Card 1: Total Toko --}}
            <div wire:click="setFilter('')" 
                 class="relative overflow-hidden cursor-pointer group p-5 bg-base-100 rounded-3xl border transition-all duration-300 hover:-translate-y-1 {{ empty($filter_type) ? 'border-primary shadow-lg shadow-primary/10 ring-1 ring-primary' : 'border-base-300 shadow-sm hover:shadow-md' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Total Toko</span>
                        <h3 class="text-2xl font-black mt-1 text-base-content">{{ number_format($kpis['total_toko']) }}</h3>
                    </div>
                    <div class="p-3 rounded-2xl transition-all duration-300 {{ empty($filter_type) ? 'bg-primary/20 text-primary' : 'bg-base-200 text-base-content/40 group-hover:bg-primary/10 group-hover:text-primary' }}">
                        <x-heroicon-s-building-storefront class="w-6 h-6 shrink-0" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="font-medium text-base-content/50">Semua Data</span>
                    <span class="font-bold text-primary opacity-0 group-hover:opacity-100 transition-opacity">Tampilkan &rarr;</span>
                </div>
            </div>

            {{-- Card 2: Belum Ada NIK KTP --}}
            <div wire:click="setFilter('tanpa_ktp')" 
                 class="relative overflow-hidden cursor-pointer group p-5 bg-base-100 rounded-3xl border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_ktp' ? 'border-warning shadow-lg shadow-warning/10 ring-1 ring-warning' : 'border-base-300 shadow-sm hover:shadow-md' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Belum Ada NIK KTP</span>
                        <h3 class="text-2xl font-black mt-1 text-base-content">{{ number_format($kpis['tanpa_ktp']) }}</h3>
                    </div>
                    <div class="p-3 rounded-2xl transition-all duration-300 {{ $filter_type === 'tanpa_ktp' ? 'bg-warning/20 text-warning' : 'bg-base-200 text-base-content/40 group-hover:bg-warning/10 group-hover:text-warning' }}">
                        <x-heroicon-s-identification class="w-6 h-6 shrink-0" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="font-medium text-base-content/50">Tanpa NIK</span>
                    <span class="font-bold text-warning opacity-0 group-hover:opacity-100 transition-opacity">Filter &rarr;</span>
                </div>
            </div>

            {{-- Card 3: Belum Ada Foto KTP --}}
            <div wire:click="setFilter('tanpa_foto_ktp')" 
                 class="relative overflow-hidden cursor-pointer group p-5 bg-base-100 rounded-3xl border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_foto_ktp' ? 'border-error shadow-lg shadow-error/10 ring-1 ring-error' : 'border-base-300 shadow-sm hover:shadow-md' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Belum Ada Foto KTP</span>
                        <h3 class="text-2xl font-black mt-1 text-base-content">{{ number_format($kpis['tanpa_foto_ktp']) }}</h3>
                    </div>
                    <div class="p-3 rounded-2xl transition-all duration-300 {{ $filter_type === 'tanpa_foto_ktp' ? 'bg-error/20 text-error' : 'bg-base-200 text-base-content/40 group-hover:bg-error/10 group-hover:text-error' }}">
                        <x-heroicon-s-camera class="w-6 h-6 shrink-0" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="font-medium text-base-content/50">Tanpa Foto KTP</span>
                    <span class="font-bold text-error opacity-0 group-hover:opacity-100 transition-opacity">Filter &rarr;</span>
                </div>
            </div>

            {{-- Card 4: Belum Ada Rekening --}}
            <div wire:click="setFilter('tanpa_rekening')" 
                 class="relative overflow-hidden cursor-pointer group p-5 bg-base-100 rounded-3xl border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_rekening' ? 'border-info shadow-lg shadow-info/10 ring-1 ring-info' : 'border-base-300 shadow-sm hover:shadow-md' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Belum Ada Rekening</span>
                        <h3 class="text-2xl font-black mt-1 text-base-content">{{ number_format($kpis['tanpa_rekening']) }}</h3>
                    </div>
                    <div class="p-3 rounded-2xl transition-all duration-300 {{ $filter_type === 'tanpa_rekening' ? 'bg-info/20 text-info' : 'bg-base-200 text-base-content/40 group-hover:bg-info/10 group-hover:text-info' }}">
                        <x-heroicon-s-credit-card class="w-6 h-6 shrink-0" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="font-medium text-base-content/50">Tanpa Rekening</span>
                    <span class="font-bold text-info opacity-0 group-hover:opacity-100 transition-opacity">Filter &rarr;</span>
                </div>
            </div>

            {{-- Card 5: Belum Ada Foto Toko --}}
            <div wire:click="setFilter('tanpa_foto_toko')" 
                 class="relative overflow-hidden cursor-pointer group p-5 bg-base-100 rounded-3xl border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_foto_toko' ? 'border-accent shadow-lg shadow-accent/10 ring-1 ring-accent' : 'border-base-300 shadow-sm hover:shadow-md' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Belum Ada Foto Toko</span>
                        <h3 class="text-2xl font-black mt-1 text-base-content">{{ number_format($kpis['tanpa_foto_toko']) }}</h3>
                    </div>
                    <div class="p-3 rounded-2xl transition-all duration-300 {{ $filter_type === 'tanpa_foto_toko' ? 'bg-accent/20 text-accent' : 'bg-base-200 text-base-content/40 group-hover:bg-accent/10 group-hover:text-accent' }}">
                        <x-heroicon-s-photo class="w-6 h-6 shrink-0" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="font-medium text-base-content/50">Tanpa Foto Toko</span>
                    <span class="font-bold text-accent opacity-0 group-hover:opacity-100 transition-opacity">Filter &rarr;</span>
                </div>
            </div>

            {{-- Card 6: Belum Ada Tikor --}}
            <div wire:click="setFilter('tanpa_tikor')" 
                 class="relative overflow-hidden cursor-pointer group p-5 bg-base-100 rounded-3xl border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_tikor' ? 'border-secondary shadow-lg shadow-secondary/10 ring-1 ring-secondary' : 'border-base-300 shadow-sm hover:shadow-md' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Belum Ada Tikor</span>
                        <h3 class="text-2xl font-black mt-1 text-base-content">{{ number_format($kpis['tanpa_tikor']) }}</h3>
                    </div>
                    <div class="p-3 rounded-2xl transition-all duration-300 {{ $filter_type === 'tanpa_tikor' ? 'bg-secondary/20 text-secondary' : 'bg-base-200 text-base-content/40 group-hover:bg-secondary/10 group-hover:text-secondary' }}">
                        <x-heroicon-s-map-pin class="w-6 h-6 shrink-0" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="font-medium text-base-content/50">Tanpa Lat/Long</span>
                    <span class="font-bold text-secondary opacity-0 group-hover:opacity-100 transition-opacity">Filter &rarr;</span>
                </div>
            </div>

            {{-- Card 7: Toko Tidak Valid --}}
            <div wire:click="setFilter('tidak_valid')" 
                 class="relative overflow-hidden cursor-pointer group p-5 bg-base-100 rounded-3xl border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tidak_valid' ? 'border-error shadow-lg shadow-error/10 ring-1 ring-error' : 'border-base-300 shadow-sm hover:shadow-md' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Toko Tidak Valid</span>
                        <h3 class="text-2xl font-black mt-1 text-base-content">{{ number_format($kpis['tidak_valid']) }}</h3>
                    </div>
                    <div class="p-3 rounded-2xl transition-all duration-300 {{ $filter_type === 'tidak_valid' ? 'bg-error/20 text-error' : 'bg-base-200 text-base-content/40 group-hover:bg-error/10 group-hover:text-error' }}">
                        <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="font-medium text-base-content/50">Tidak Valid</span>
                    <span class="font-bold text-error opacity-0 group-hover:opacity-100 transition-opacity">Filter &rarr;</span>
                </div>
            </div>
        </div>

        <x-card flush title="Reward Outlet (RWO)" icon="gift" subtitle="Kelola data program reward outlet (RWO)" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.500ms="search" type="text"
                               placeholder="Cari RWO..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Filter Dropdown --}}
                    <select wire:model.live="filter_type"
                            class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Data</option>
                        <option value="tanpa_ktp">Tanpa NIK KTP</option>
                        <option value="tanpa_foto_ktp">Tanpa Foto KTP</option>
                        <option value="tanpa_rekening">Tanpa Rekening</option>
                        <option value="tanpa_foto_toko">Tanpa Foto Toko</option>
                        <option value="tanpa_tikor">Tanpa Tikor (Lat/Long)</option>
                        <option value="tidak_valid">Outlet Tidak Valid</option>
                        <option value="valid">Outlet Valid</option>
                    </select>

                    {{-- Chained Wilayah Filter Button --}}
                    <button wire:click="openFilterModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200 relative {{ (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name)) ? 'border-primary text-primary hover:bg-primary/5' : '' }}">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        <span>Filter Wilayah</span>
                        @if (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name))
                            <span class="absolute -top-1.5 -right-1.5 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                            </span>
                        @endif
                    </button>

                    {{-- Export --}}
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export Excel
                    </button>

                    {{-- Import --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openImportModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import Excel
                    </button>

                    {{-- Tambah --}}
                    <button wire:click="openCreateModal"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah RWO
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            {{-- Table --}}
            <x-ui.table empty="Tidak ada data RWO ditemukan.">
                <x-slot:head>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Region / Area</th>
                        <th>Cabang</th>
                        <th>Kode Customer / Eskalink</th>
                        <th>Nama Customer</th>
                        <th>Pemilik Toko</th>
                        <th>No HP</th>
                        <th class="text-center">Foto KTP</th>
                        <th class="text-center">Foto Toko (GPS / Depan / Dalam)</th>
                        <th class="text-center">Validasi</th>
                        <th>Keterangan</th>
                <th class="text-center w-28">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($outlets as $index => $row)
                    <tr class="group text-sm hover:relative hover:z-40" wire:key="rwo-{{ $row->id }}">
                        <td class="text-center">
                            <span class="text-xs font-semibold text-base-content/40">{{ $outlets->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <div>
                                <span class="font-bold text-base-content/85 group-hover:text-primary transition-colors">
                                    {{ $row->region_name }}
                                </span>
                                <span class="text-xs text-base-content/50 font-mono">({{ $row->region_code }})</span>
                                <div class="text-[11px] text-base-content/40 font-semibold uppercase tracking-wider mt-0.5">
                                    {{ $row->area_name }} <span class="text-[10px] font-mono">({{ $row->area_code }})</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium text-base-content/70">{{ $row->branch_name }}</span>
                        </td>
                        <td>
                            <div class="flex flex-col gap-0.5">
                                <span class="badge badge-sm badge-outline border-base-300 text-secondary font-mono font-bold rounded-lg px-2">
                                    {{ $row->customer_code }}
                                </span>
                                @if($row->eskalink_code)
                                    <span class="text-[10px] text-base-content/50 font-mono mt-0.5">Eska: {{ $row->eskalink_code }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="max-w-xs overflow-hidden text-ellipsis whitespace-nowrap">
                                <span class="font-bold text-base-content/80">{{ $row->customer_name }}</span>
                                <p class="text-xs text-base-content/40 truncate">{{ $row->alamat }}</p>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium text-base-content/80">{{ $row->nama_pemilik_toko }}</span>
                        </td>
                        <td>
                            <span class="font-mono text-xs text-base-content/70">{{ $row->no_hp ?? '-' }}</span>
                        </td>
                          <td class="text-center">
                             @if($row->foto_ktp)
                                  <div class="flex justify-center">
                                      <div class="relative group/ktp">
                                          <div class="w-10 h-10 rounded-xl ring ring-base-300 ring-offset-base-100 overflow-hidden cursor-zoom-in" wire:click="openDetailModal({{ $row->id }})">
                                              <img src="{{ asset('storage/' . $row->foto_ktp) }}" alt="KTP" class="w-full h-full object-cover" />
                                          </div>
                                          <!-- Hover Preview Card (3x larger, top-most z-index, fixed viewport position to prevent clipping) -->
                                          <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-100 border border-base-300 rounded-3xl p-3 shadow-2xl pointer-events-none w-[90vw] sm:w-[33rem] max-w-[528px] transition-all duration-300 invisible opacity-0 scale-95 group-hover/ktp:visible group-hover/ktp:opacity-100 group-hover/ktp:scale-100">
                                              <img src="{{ asset('storage/' . $row->foto_ktp) }}" class="rounded-2xl w-full h-auto object-contain max-h-[30rem] bg-base-200/50" />
                                              <div class="text-xs text-center font-bold text-base-content/70 mt-2 uppercase tracking-wider">
                                                  Foto KTP
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                             @else
                                 <span class="text-xs text-base-content/30 italic">Tidak ada</span>
                             @endif
                          </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- GPS --}}
                                @if($row->foto_toko)
                                    <div class="relative group/gps">
                                        <div class="w-7 h-7 rounded-lg ring ring-base-300 ring-offset-base-100 overflow-hidden cursor-zoom-in" wire:click="openDetailModal({{ $row->id }})">
                                            <img src="{{ asset('storage/' . $row->foto_toko) }}" alt="GPS" class="w-full h-full object-cover" />
                                        </div>
                                        <!-- Hover Preview Card (3x larger, top-most z-index, fixed viewport position to prevent clipping) -->
                                        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-100 border border-base-300 rounded-3xl p-3 shadow-2xl pointer-events-none w-[90vw] sm:w-[33rem] max-w-[528px] transition-all duration-300 invisible opacity-0 scale-95 group-hover/gps:visible group-hover/gps:opacity-100 group-hover/gps:scale-100">
                                            <img src="{{ asset('storage/' . $row->foto_toko) }}" class="rounded-2xl w-full h-auto object-contain max-h-[30rem] bg-base-200/50" />
                                            <div class="text-xs text-center font-bold text-base-content/70 mt-2 uppercase tracking-wider">
                                                Foto GPS
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-zoom-in" data-tip="Foto Toko by GPS (Belum ada)" wire:click="openDetailModal({{ $row->id }})">G</div>
                                @endif

                                {{-- Depan --}}
                                @if($row->foto_toko2)
                                    <div class="relative group/depan">
                                        <div class="w-7 h-7 rounded-lg ring ring-base-300 ring-offset-base-100 overflow-hidden cursor-zoom-in" wire:click="openDetailModal({{ $row->id }})">
                                            <img src="{{ asset('storage/' . $row->foto_toko2) }}" alt="Depan" class="w-full h-full object-cover" />
                                        </div>
                                        <!-- Hover Preview Card (3x larger, top-most z-index, fixed viewport position to prevent clipping) -->
                                        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-100 border border-base-300 rounded-3xl p-3 shadow-2xl pointer-events-none w-[90vw] sm:w-[33rem] max-w-[528px] transition-all duration-300 invisible opacity-0 scale-95 group-hover/depan:visible group-hover/depan:opacity-100 group-hover/depan:scale-100">
                                            <img src="{{ asset('storage/' . $row->foto_toko2) }}" class="rounded-2xl w-full h-auto object-contain max-h-[30rem] bg-base-200/50" />
                                            <div class="text-xs text-center font-bold text-base-content/70 mt-2 uppercase tracking-wider">
                                                Tampak Depan
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-zoom-in" data-tip="Foto Tampak Depan (Belum ada)" wire:click="openDetailModal({{ $row->id }})">D</div>
                                @endif

                                {{-- Dalam --}}
                                @if($row->foto_toko3)
                                    <div class="relative group/dalam">
                                        <div class="w-7 h-7 rounded-lg ring ring-base-300 ring-offset-base-100 overflow-hidden cursor-zoom-in" wire:click="openDetailModal({{ $row->id }})">
                                            <img src="{{ asset('storage/' . $row->foto_toko3) }}" alt="Dalam" class="w-full h-full object-cover" />
                                        </div>
                                        <!-- Hover Preview Card (3x larger, top-most z-index, fixed viewport position to prevent clipping) -->
                                        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-100 border border-base-300 rounded-3xl p-3 shadow-2xl pointer-events-none w-[90vw] sm:w-[33rem] max-w-[528px] transition-all duration-300 invisible opacity-0 scale-95 group-hover/dalam:visible group-hover/dalam:opacity-100 group-hover/dalam:scale-100">
                                            <img src="{{ asset('storage/' . $row->foto_toko3) }}" class="rounded-2xl w-full h-auto object-contain max-h-[30rem] bg-base-200/50" />
                                            <div class="text-xs text-center font-bold text-base-content/70 mt-2 uppercase tracking-wider">
                                                Tampak Dalam
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-zoom-in" data-tip="Foto Tampak Dalam (Belum ada)" wire:click="openDetailModal({{ $row->id }})">L</div>
                                @endif
                            </div>
                         </td>
                         <td class="text-center">
                             @if($row->is_valid)
                                 <span class="inline-flex items-center gap-1 text-[10px] font-bold text-success bg-success/15 rounded-lg py-1 px-2">
                                     <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                     <span>Valid</span>
                                 </span>
                             @else
                                 <span class="inline-flex items-center gap-1 text-[10px] font-bold text-error bg-error/15 rounded-lg py-1 px-2">
                                     <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                     <span>Tidak Valid</span>
                                 </span>
                             @endif
                         </td>
                         <td>
                             @if($row->keterangan)
                                 <div class="max-w-[150px] truncate text-xs text-base-content/60" title="{{ $row->keterangan }}">
                                     {{ $row->keterangan }}
                                 </div>
                             @else
                                 <span class="text-xs text-base-content/30 italic">-</span>
                             @endif
                         </td>
                         <td>
                             <div class="flex items-center justify-center gap-1">
                                <button wire:click="openDetailModal({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-secondary hover:bg-secondary/10 transition-all duration-200" title="Detail">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </button>
                                @if($row->latitude && $row->longitude)
                                <a href="https://www.google.com/maps?q={{ $row->latitude }},{{ $row->longitude }}" target="_blank"
                                   class="btn btn-ghost btn-xs btn-square rounded-lg text-accent hover:bg-accent/10 transition-all duration-200" title="Buka Google Maps">
                                    <x-heroicon-s-map-pin class="w-4 h-4" />
                                </a>
                                @endif
                                @unless(auth()->user()->hasRole('guest'))
                                <button wire:click="openEditModal({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if($outlets->hasPages())
                <div class="mt-4 px-6">{{ $outlets->links() }}</div>
            @endif
        </x-card>
    </div>

    {{-- ========== MODAL FORM (Create/Edit) ========== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Reward Outlet (RWO)' : 'Tambah RWO Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ $isEditing ? 'Perbarui data outlet program RWO' : 'Daftarkan outlet program RWO baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 overflow-y-auto max-h-[calc(100vh-15rem)] bg-base-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        
                        {{-- HIERARKI WILAYAH --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Hierarki & Kode</h4>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                            <select wire:model.live="region_code"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('region_code') select-error @enderror">
                                <option value="">Pilih Region</option>
                                @foreach($this->getRegions() as $reg)
                                    <option value="{{ $reg->region_code }}">{{ $reg->region_code }} - {{ $reg->region_name }}</option>
                                @endforeach
                            </select>
                            @error('region_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                            <select wire:model.live="area_code"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('area_code') select-error @enderror"
                                    {{ empty($region_code) ? 'disabled' : '' }}>
                                <option value="">Pilih Area</option>
                                @foreach($this->getAreas() as $ar)
                                    <option value="{{ $ar->area_code }}">{{ $ar->area_code }} - {{ $ar->area_name }}</option>
                                @endforeach
                            </select>
                            @error('area_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang (Branch)</label>
                            <select wire:model="branch_name"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('branch_name') select-error @enderror">
                                <option value="">Pilih Cabang</option>
                                @foreach($this->getBranches() as $br)
                                    <option value="{{ $br->branch_name }}">{{ $br->branch_name }}</option>
                                @endforeach
                            </select>
                            @error('branch_name') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Customer Code</label>
                            <input wire:model="customer_code" type="text" placeholder="Contoh: CUST-01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('customer_code') input-error @enderror">
                            @error('customer_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Eskalink Code</label>
                            <input wire:model="eskalink_code" type="text" placeholder="Contoh: ESKA-01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('eskalink_code') input-error @enderror">
                            @error('eskalink_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">No HP</label>
                            <input wire:model="no_hp" type="text" placeholder="Contoh: 08123456789"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('no_hp') input-error @enderror">
                            @error('no_hp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- OUTLET DATA --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Informasi Toko / Outlet</h4>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Customer / Toko</label>
                            <input wire:model="customer_name" type="text" placeholder="Nama Toko"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('customer_name') input-error @enderror">
                            @error('customer_name') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Pemilik Toko</label>
                            <input wire:model="nama_pemilik_toko" type="text" placeholder="Nama Pemilik Toko"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nama_pemilik_toko') input-error @enderror">
                            @error('nama_pemilik_toko') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-3 space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Alamat Lengkap</label>
                            <textarea wire:model="alamat" placeholder="Tulis alamat toko secara detail..."
                                      class="textarea textarea-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('alamat') textarea-error @enderror" rows="3"></textarea>
                            @error('alamat') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Latitude</label>
                            <input wire:model="latitude" type="text" placeholder="Contoh: -6.12345"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('latitude') input-error @enderror">
                            @error('latitude') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Longitude</label>
                            <input wire:model="longitude" type="text" placeholder="Contoh: 106.12345"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('longitude') input-error @enderror">
                            @error('longitude') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- KTP & IDENTITY --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Identitas & KTP</h4>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama KTP</label>
                            <input wire:model="nama_ktp" type="text" placeholder="Nama Sesuai KTP"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nama_ktp') input-error @enderror">
                            @error('nama_ktp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">NIK KTP</label>
                            <input wire:model="nik_ktp" type="text" placeholder="Nomor NIK KTP"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nik_ktp') input-error @enderror">
                            @error('nik_ktp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Upload Foto KTP</label>
                            
                            <div x-data="{ 
                                isDragging: false, 
                                isFocused: false,
                                uploadProgress: 0,
                                isUploading: false,
                                uploadFile(file) {
                                    if (!file.type.startsWith('image/')) {
                                        alert('File harus berupa gambar!');
                                        return;
                                    }
                                    this.isUploading = true;
                                    this.uploadProgress = 0;
                                    @this.upload('foto_ktp', file, 
                                        (uploadedName) => {
                                            this.isUploading = false;
                                            this.uploadProgress = 0;
                                        }, 
                                        () => {
                                            this.isUploading = false;
                                            this.uploadProgress = 0;
                                            alert('Gagal mengunggah file.');
                                        }, 
                                        (event) => {
                                            this.uploadProgress = event.detail.progress;
                                        }
                                    );
                                }
                            }" 
                            class="relative">
                                <div 
                                    @dragover.prevent="isDragging = true" 
                                    @dragleave.prevent="isDragging = false" 
                                    @drop.prevent="isDragging = false; const files = $event.dataTransfer.files; if (files.length) uploadFile(files[0])"
                                    @paste.window="if (isFocused) { const items = ($event.clipboardData || $event.originalEvent.clipboardData).items; for (let i = 0; i < items.length; i++) { if (items[i].type.indexOf('image') !== -1) { const file = items[i].getAsFile(); uploadFile(file); } } }"
                                    @click="$refs.fileInputKtp.click()"
                                    tabindex="0"
                                    @focus="isFocused = true"
                                    @blur="isFocused = false"
                                    :class="{'border-primary bg-primary/5': isDragging || isFocused, 'border-base-300 bg-base-200/50': !isDragging && !isFocused}"
                                    class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-4 cursor-pointer hover:border-primary hover:bg-primary/5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50 group text-center min-h-[100px]"
                                >
                                    <input x-ref="fileInputKtp" type="file" accept="image/*" class="hidden" 
                                           @change="if ($event.target.files.length) uploadFile($event.target.files[0])">
                                           
                                    <x-heroicon-s-camera class="w-6 h-6 text-base-content/40 group-hover:text-primary group-focus:text-primary transition-colors mb-1" />
                                    <span class="text-xs font-bold text-base-content/70">Klik / Seret Foto KTP ke sini</span>
                                    <span class="text-[10px] text-base-content/40">Atau klik lalu paste (<kbd class="kbd kbd-xs">Ctrl</kbd> + <kbd class="kbd kbd-xs">V</kbd>)</span>

                                    {{-- Progress Bar --}}
                                    <div x-show="isUploading" class="absolute inset-0 bg-base-100/90 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center p-2 z-10" @click.stop>
                                        <div class="radial-progress text-primary" :style="'--value:' + uploadProgress + '; --size:2.5rem; --thickness: 3px;'" role="progressbar">
                                            <span class="text-[9px] font-bold" x-text="uploadProgress + '%'"></span>
                                        </div>
                                        <span class="text-[9px] font-bold mt-1 text-base-content/75">Mengunggah...</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 flex items-center gap-3">
                                @if ($this->getFotoKtpPreview())
                                    <div class="avatar">
                                        <div class="w-16 h-16 rounded-xl border border-base-300">
                                            <img src="{{ $this->getFotoKtpPreview() }}" alt="Preview KTP">
                                        </div>
                                    </div>
                                @elseif ($existing_foto_ktp)
                                    <div class="avatar">
                                        <div class="w-16 h-16 rounded-xl border border-base-300">
                                            <img src="{{ asset('storage/' . $existing_foto_ktp) }}" alt="Existing KTP">
                                        </div>
                                    </div>
                                @endif
                                <div class="text-[10px] text-base-content/40 leading-tight">Format: JPG/PNG. Max: 2MB</div>
                            </div>
                            @error('foto_ktp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- BANK & REKENING --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Informasi Bank & Transfer</h4>
                        </div>

                        <div class="space-y-1.5" x-data="{
                            open: false,
                            search: '',
                            selectedBank: @entangle('nama_bank'),
                            banks: @js($this->getBanksList())
                        }">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Bank</label>
                            <div @click.away="open = false" class="relative">
                                <button type="button" @click="open = !open" 
                                        class="input input-bordered w-full text-left flex justify-between items-center bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    <span x-text="selectedBank || 'Pilih Bank / Cari...'" class="truncate"></span>
                                    <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/40" />
                                </button>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-[60] mt-1 w-full bg-base-200 border border-base-300 rounded-2xl shadow-xl max-h-60 overflow-y-auto" 
                                     x-cloak>
                                    <div class="p-2 sticky top-0 bg-base-200 border-b border-base-300">
                                        <input type="text" x-model="search" placeholder="Cari nama bank..." 
                                               class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-1 focus:ring-primary" 
                                               @click.stop>
                                    </div>
                                    <ul class="py-1">
                                        <template x-for="bank in banks" :key="bank">
                                            <li x-show="bank.toLowerCase().includes(search.toLowerCase())"
                                                @click="selectedBank = bank; open = false; search = ''"
                                                class="px-4 py-2.5 hover:bg-primary hover:text-primary-content cursor-pointer text-sm transition-colors duration-150">
                                                <span x-text="bank"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            @error('nama_bank') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">No Rekening</label>
                            <input wire:model="no_rekening" type="text" placeholder="Nomor Rekening"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('no_rekening') input-error @enderror">
                            @error('no_rekening') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Pemilik Rekening</label>
                            <input wire:model="nama_pemilik_norek" type="text" placeholder="Nama Pemilik Rekening"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nama_pemilik_norek') input-error @enderror">
                            @error('nama_pemilik_norek') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- VALIDASI & KETERANGAN TOKO --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Validasi & Keterangan</h4>
                        </div>

                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            {{-- Validasi Checkbox --}}
                            <div class="space-y-1.5 md:col-span-1">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status Toko</label>
                                <div class="form-control bg-base-200 border border-base-300 rounded-2xl p-3 flex flex-row items-center justify-between gap-3 hover:bg-base-200/80 transition-all duration-200 cursor-pointer">
                                    <div class="flex flex-col select-none" @click="$refs.isValidCheckbox.click()">
                                        <span class="text-xs font-bold text-base-content/80">Toko Ada / Valid</span>
                                        <span class="text-[10px] text-base-content/40">Centang jika toko terverifikasi ada</span>
                                    </div>
                                    <input x-ref="isValidCheckbox" type="checkbox" wire:model="is_valid" class="checkbox checkbox-primary rounded-lg">
                                </div>
                                @error('is_valid') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Keterangan Text --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Keterangan</label>
                                <input wire:model="keterangan" type="text" placeholder="Masukkan keterangan tambahan tentang toko..."
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('keterangan') input-error @enderror">
                                @error('keterangan') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- FOTO TOKO --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Media / Foto Toko</h4>
                        </div>

                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Foto Toko by GPS --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto Toko by GPS</label>
                                <div x-data="{ 
                                    isDragging: false, 
                                    isFocused: false,
                                    uploadProgress: 0,
                                    isUploading: false,
                                    uploadFile(file) {
                                        if (!file.type.startsWith('image/')) {
                                            alert('File harus berupa gambar!');
                                            return;
                                        }
                                        this.isUploading = true;
                                        this.uploadProgress = 0;
                                        @this.upload('foto_toko', file, 
                                            (uploadedName) => {
                                                this.isUploading = false;
                                                this.uploadProgress = 0;
                                            }, 
                                            () => {
                                                this.isUploading = false;
                                                this.uploadProgress = 0;
                                                alert('Gagal mengunggah file.');
                                            }, 
                                            (event) => {
                                                this.uploadProgress = event.detail.progress;
                                            }
                                        );
                                    }
                                }" 
                                class="relative">
                                    <div 
                                        @dragover.prevent="isDragging = true" 
                                        @dragleave.prevent="isDragging = false" 
                                        @drop.prevent="isDragging = false; const files = $event.dataTransfer.files; if (files.length) uploadFile(files[0])"
                                        @paste.window="if (isFocused) { const items = ($event.clipboardData || $event.originalEvent.clipboardData).items; for (let i = 0; i < items.length; i++) { if (items[i].type.indexOf('image') !== -1) { const file = items[i].getAsFile(); uploadFile(file); } } }"
                                        @click="$refs.fileInputToko.click()"
                                        tabindex="0"
                                        @focus="isFocused = true"
                                        @blur="isFocused = false"
                                        :class="{'border-primary bg-primary/5': isDragging || isFocused, 'border-base-300 bg-base-200/50': !isDragging && !isFocused}"
                                        class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-4 cursor-pointer hover:border-primary hover:bg-primary/5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50 group text-center min-h-[110px]"
                                    >
                                        <input x-ref="fileInputToko" type="file" accept="image/*" class="hidden" 
                                               @change="if ($event.target.files.length) uploadFile($event.target.files[0])">
                                               
                                        <x-heroicon-s-camera class="w-6 h-6 text-base-content/40 group-hover:text-primary group-focus:text-primary transition-colors mb-1" />
                                        <span class="text-[11px] font-bold text-base-content/70">Seret Foto GPS</span>
                                        <span class="text-[9px] text-base-content/40">Klik lalu paste (<kbd class="kbd kbd-xs">Ctrl</kbd> + <kbd class="kbd kbd-xs">V</kbd>)</span>

                                        {{-- Progress Bar --}}
                                        <div x-show="isUploading" class="absolute inset-0 bg-base-100/90 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center p-2 z-10" @click.stop>
                                            <div class="radial-progress text-primary" :style="'--value:' + uploadProgress + '; --size:2.5rem; --thickness: 3px;'" role="progressbar">
                                                <span class="text-[9px] font-bold" x-text="uploadProgress + '%'"></span>
                                            </div>
                                            <span class="text-[9px] font-bold mt-1 text-base-content/75">Mengunggah...</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center gap-3">
                                    @if ($this->getFotoTokoPreview())
                                        <div class="avatar">
                                            <div class="w-14 h-14 rounded-xl border border-base-300">
                                                <img src="{{ $this->getFotoTokoPreview() }}" alt="Preview Toko">
                                            </div>
                                        </div>
                                    @elseif ($existing_foto_toko)
                                        <div class="avatar">
                                            <div class="w-14 h-14 rounded-xl border border-base-300">
                                                <img src="{{ asset('storage/' . $existing_foto_toko) }}" alt="Existing Toko">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="text-[9px] text-base-content/40 leading-tight">Format: JPG/PNG.<br>Max: 2MB</div>
                                </div>
                                @error('foto_toko') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Foto Toko Tampak Depan --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto Tampak Depan</label>
                                <div x-data="{ 
                                    isDragging: false, 
                                    isFocused: false,
                                    uploadProgress: 0,
                                    isUploading: false,
                                    uploadFile(file) {
                                        if (!file.type.startsWith('image/')) {
                                            alert('File harus berupa gambar!');
                                            return;
                                        }
                                        this.isUploading = true;
                                        this.uploadProgress = 0;
                                        @this.upload('foto_toko2', file, 
                                            (uploadedName) => {
                                                this.isUploading = false;
                                                this.uploadProgress = 0;
                                            }, 
                                            () => {
                                                this.isUploading = false;
                                                this.uploadProgress = 0;
                                                alert('Gagal mengunggah file.');
                                            }, 
                                            (event) => {
                                                this.uploadProgress = event.detail.progress;
                                            }
                                        );
                                    }
                                }" 
                                class="relative">
                                    <div 
                                        @dragover.prevent="isDragging = true" 
                                        @dragleave.prevent="isDragging = false" 
                                        @drop.prevent="isDragging = false; const files = $event.dataTransfer.files; if (files.length) uploadFile(files[0])"
                                        @paste.window="if (isFocused) { const items = ($event.clipboardData || $event.originalEvent.clipboardData).items; for (let i = 0; i < items.length; i++) { if (items[i].type.indexOf('image') !== -1) { const file = items[i].getAsFile(); uploadFile(file); } } }"
                                        @click="$refs.fileInputToko2.click()"
                                        tabindex="0"
                                        @focus="isFocused = true"
                                        @blur="isFocused = false"
                                        :class="{'border-primary bg-primary/5': isDragging || isFocused, 'border-base-300 bg-base-200/50': !isDragging && !isFocused}"
                                        class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-4 cursor-pointer hover:border-primary hover:bg-primary/5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50 group text-center min-h-[110px]"
                                    >
                                        <input x-ref="fileInputToko2" type="file" accept="image/*" class="hidden" 
                                               @change="if ($event.target.files.length) uploadFile($event.target.files[0])">
                                               
                                        <x-heroicon-s-camera class="w-6 h-6 text-base-content/40 group-hover:text-primary group-focus:text-primary transition-colors mb-1" />
                                        <span class="text-[11px] font-bold text-base-content/70">Seret Foto Depan</span>
                                        <span class="text-[9px] text-base-content/40">Klik lalu paste (<kbd class="kbd kbd-xs">Ctrl</kbd> + <kbd class="kbd kbd-xs">V</kbd>)</span>

                                        {{-- Progress Bar --}}
                                        <div x-show="isUploading" class="absolute inset-0 bg-base-100/90 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center p-2 z-10" @click.stop>
                                            <div class="radial-progress text-primary" :style="'--value:' + uploadProgress + '; --size:2.5rem; --thickness: 3px;'" role="progressbar">
                                                <span class="text-[9px] font-bold" x-text="uploadProgress + '%'"></span>
                                            </div>
                                            <span class="text-[9px] font-bold mt-1 text-base-content/75">Mengunggah...</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center gap-3">
                                    @if ($this->getFotoToko2Preview())
                                        <div class="avatar">
                                            <div class="w-14 h-14 rounded-xl border border-base-300">
                                                <img src="{{ $this->getFotoToko2Preview() }}" alt="Preview Toko 2">
                                            </div>
                                        </div>
                                    @elseif ($existing_foto_toko2)
                                        <div class="avatar">
                                            <div class="w-14 h-14 rounded-xl border border-base-300">
                                                <img src="{{ asset('storage/' . $existing_foto_toko2) }}" alt="Existing Toko 2">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="text-[9px] text-base-content/40 leading-tight">Format: JPG/PNG.<br>Max: 2MB</div>
                                </div>
                                @error('foto_toko2') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Foto Toko Tampak Dalam --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto Tampak Dalam</label>
                                <div x-data="{ 
                                    isDragging: false, 
                                    isFocused: false,
                                    uploadProgress: 0,
                                    isUploading: false,
                                    uploadFile(file) {
                                        if (!file.type.startsWith('image/')) {
                                            alert('File harus berupa gambar!');
                                            return;
                                        }
                                        this.isUploading = true;
                                        this.uploadProgress = 0;
                                        @this.upload('foto_toko3', file, 
                                            (uploadedName) => {
                                                this.isUploading = false;
                                                this.uploadProgress = 0;
                                            }, 
                                            () => {
                                                this.isUploading = false;
                                                this.uploadProgress = 0;
                                                alert('Gagal mengunggah file.');
                                            }, 
                                            (event) => {
                                                this.uploadProgress = event.detail.progress;
                                            }
                                        );
                                    }
                                }" 
                                class="relative">
                                    <div 
                                        @dragover.prevent="isDragging = true" 
                                        @dragleave.prevent="isDragging = false" 
                                        @drop.prevent="isDragging = false; const files = $event.dataTransfer.files; if (files.length) uploadFile(files[0])"
                                        @paste.window="if (isFocused) { const items = ($event.clipboardData || $event.originalEvent.clipboardData).items; for (let i = 0; i < items.length; i++) { if (items[i].type.indexOf('image') !== -1) { const file = items[i].getAsFile(); uploadFile(file); } } }"
                                        @click="$refs.fileInputToko3.click()"
                                        tabindex="0"
                                        @focus="isFocused = true"
                                        @blur="isFocused = false"
                                        :class="{'border-primary bg-primary/5': isDragging || isFocused, 'border-base-300 bg-base-200/50': !isDragging && !isFocused}"
                                        class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-4 cursor-pointer hover:border-primary hover:bg-primary/5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50 group text-center min-h-[110px]"
                                    >
                                        <input x-ref="fileInputToko3" type="file" accept="image/*" class="hidden" 
                                               @change="if ($event.target.files.length) uploadFile($event.target.files[0])">
                                               
                                        <x-heroicon-s-camera class="w-6 h-6 text-base-content/40 group-hover:text-primary group-focus:text-primary transition-colors mb-1" />
                                        <span class="text-[11px] font-bold text-base-content/70">Seret Foto Dalam</span>
                                        <span class="text-[9px] text-base-content/40">Klik lalu paste (<kbd class="kbd kbd-xs">Ctrl</kbd> + <kbd class="kbd kbd-xs">V</kbd>)</span>

                                        {{-- Progress Bar --}}
                                        <div x-show="isUploading" class="absolute inset-0 bg-base-100/90 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center p-2 z-10" @click.stop>
                                            <div class="radial-progress text-primary" :style="'--value:' + uploadProgress + '; --size:2.5rem; --thickness: 3px;'" role="progressbar">
                                                <span class="text-[9px] font-bold" x-text="uploadProgress + '%'"></span>
                                            </div>
                                            <span class="text-[9px] font-bold mt-1 text-base-content/75">Mengunggah...</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center gap-3">
                                    @if ($this->getFotoToko3Preview())
                                        <div class="avatar">
                                            <div class="w-14 h-14 rounded-xl border border-base-300">
                                                <img src="{{ $this->getFotoToko3Preview() }}" alt="Preview Toko 3">
                                            </div>
                                        </div>
                                    @elseif ($existing_foto_toko3)
                                        <div class="avatar">
                                            <div class="w-14 h-14 rounded-xl border border-base-300">
                                                <img src="{{ asset('storage/' . $existing_foto_toko3) }}" alt="Existing Toko 3">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="text-[9px] text-base-content/40 leading-tight">Format: JPG/PNG.<br>Max: 2MB</div>
                                </div>
                                @error('foto_toko3') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Tambahkan RWO' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL DETAIL (View) ========== --}}
    <div x-data="{ open: @entangle('isDetailModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-3xl overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-secondary/10 text-secondary">
                        <x-heroicon-s-eye class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">Detail Reward Outlet (RWO)</h3>
                        <p class="text-xs text-base-content/50">Tinjau informasi lengkap tentang RWO ini</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            @if($selectedOutlet)
            <div class="p-6 overflow-y-auto max-h-[calc(100vh-15rem)] bg-base-100 space-y-6">
                {{-- Data Outlet Utama --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Informasi Toko</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Customer:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->customer_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Kode Customer:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->customer_code }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Kode Eskalink:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->eskalink_code ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Pemilik Toko:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_pemilik_toko }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">No HP:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->no_hp ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Hierarki Wilayah & Lokasi</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Region:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->region_code }} - {{ $selectedOutlet->region_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Area:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->area_code }} - {{ $selectedOutlet->area_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Cabang (Branch):</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->branch_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Latitude:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->latitude ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">Longitude:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->longitude ?? '-' }}</span>
                            </div>
                            @if($selectedOutlet->latitude && $selectedOutlet->longitude)
                            <div class="pt-2 flex justify-end">
                                <a href="https://www.google.com/maps?q={{ $selectedOutlet->latitude }},{{ $selectedOutlet->longitude }}" target="_blank"
                                   class="btn btn-xs btn-outline btn-accent rounded-lg normal-case gap-1.5">
                                    <x-heroicon-s-map-pin class="w-3.5 h-3.5" /> Buka Google Maps
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Alamat --}}
                <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Alamat Outlet</span>
                    <p class="text-xs text-base-content mt-1.5 leading-relaxed">{{ $selectedOutlet->alamat }}</p>
                </div>

                {{-- Validasi & Keterangan --}}
                <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Status Validasi & Keterangan</span>
                    <div class="mt-2.5 flex flex-col md:flex-row md:items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-base-content/60">Status:</span>
                            @if($selectedOutlet->is_valid)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-success bg-success/15 rounded-lg py-1 px-2.5">
                                    <x-heroicon-s-check-circle class="w-4 h-4" />
                                    <span>Outlet Valid (Toko Ada)</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-error bg-error/15 rounded-lg py-1 px-2.5">
                                    <x-heroicon-s-x-circle class="w-4 h-4" />
                                    <span>Outlet Tidak Valid (Toko Tidak Ada)</span>
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 flex items-start md:items-center gap-2">
                            <span class="text-xs font-semibold text-base-content/60 whitespace-nowrap">Keterangan:</span>
                            <span class="text-xs font-semibold text-base-content/85">{{ $selectedOutlet->keterangan ?? 'Tidak ada keterangan tambahan' }}</span>
                        </div>
                    </div>
                </div>

                {{-- KTP & Rekening --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Identitas KTP</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama di KTP:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_ktp ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">NIK KTP:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->nik_ktp ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Informasi Bank</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Bank:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_bank ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nomor Rekening:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->no_rekening ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">Pemilik Rekening:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_pemilik_norek ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gambar-gambar --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- KTP --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto KTP</span>
                        @if ($selectedOutlet->foto_ktp)
                            <a href="{{ asset('storage/' . $selectedOutlet->foto_ktp) }}" target="_blank" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_ktp) }}" alt="Foto KTP" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </a>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto KTP
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by GPS --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Toko by GPS</span>
                        @if ($selectedOutlet->foto_toko)
                            <a href="{{ asset('storage/' . $selectedOutlet->foto_toko) }}" target="_blank" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko) }}" alt="Foto Toko GPS" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </a>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto GPS
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by team Elite (Tampak Depan) --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Tampak Depan</span>
                        @if ($selectedOutlet->foto_toko2)
                            <a href="{{ asset('storage/' . $selectedOutlet->foto_toko2) }}" target="_blank" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko2) }}" alt="Foto Tampak Depan" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </a>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto Depan
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by team Elite (Tampak Dalam) --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Tampak Dalam</span>
                        @if ($selectedOutlet->foto_toko3)
                            <a href="{{ asset('storage/' . $selectedOutlet->foto_toko3) }}" target="_blank" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko3) }}" alt="Foto Tampak Dalam" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </a>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto Dalam
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-end px-6 py-5 border-t border-base-300 bg-base-200/50">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Tutup</button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL IMPORT ========== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Import Data RWO</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Unggah File Excel (.xlsx / .csv)</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">Pilih File</label>
                            <button type="button" wire:click="downloadTemplate" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                                <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" /> Download Template
                            </button>
                        </div>
                        <input wire:model="importFile" type="file"
                               class="file-input file-input-bordered file-input-primary w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <div class="text-[10px] text-base-content/40 leading-tight mt-1">Ukuran maksimal file: 10MB</div>
                        @error('importFile') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-300 space-y-2">
                        <h5 class="text-xs font-bold text-base-content/70">Format Urutan Kolom Excel:</h5>
                        <ol class="list-decimal list-inside text-[11px] text-base-content/60 space-y-1 font-medium">
                            <li>Region Code</li>
                            <li>Region Name</li>
                            <li>Area Code</li>
                            <li>Area Name</li>
                            <li>Cabang (Branch Name)</li>
                            <li>Eskalink Code</li>
                            <li>Customer Code (Wajib & Unik)</li>
                            <li>Customer Name (Wajib)</li>
                            <li>Alamat</li>
                            <li>No HP</li>
                            <li>Latitude</li>
                            <li>Longitude</li>
                            <li>Nama Pemilik Toko</li>
                            <li>Nama KTP</li>
                            <li>NIK KTP</li>
                            <li>[Foto KTP - Dilewati saat import]</li>
                            <li>Nama Bank</li>
                            <li>No Rekening</li>
                            <li>Nama Pemilik Norek</li>
                        </ol>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="import">Unggah & Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-check-circle wire:loading.remove wire:target="import" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL KONFIRMASI HAPUS ========== --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Data RWO?</h3>
                <p class="text-sm text-base-content/60 leading-relaxed px-4">Apakah Anda yakin ingin menghapus data Reward Outlet ini? File foto yang tersimpan juga akan <span class="text-error font-bold italic">dihapus permanen</span>.</p>
            </div>

            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case transition-all duration-200">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 transition-all duration-200 text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL FILTER WILAYAH (CHAINED) ========== --}}
    <div x-data="{ open: @entangle('isFilterModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Wilayah</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Saring data secara bertingkat</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Region Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                    <select wire:model.live="filter_region_code"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Region</option>
                        @foreach($this->getFilterRegions() as $reg)
                            <option value="{{ $reg->region_code }}">{{ $reg->region_name }} ({{ $reg->region_code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Area Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                    <select wire:model.live="filter_area_code"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Area</option>
                        @foreach($this->getFilterAreas() as $ar)
                            <option value="{{ $ar->area_code }}">{{ $ar->area_name }} ({{ $ar->area_code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Cabang Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang</label>
                    <select wire:model.live="filter_branch_name"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($this->getFilterBranches() as $br)
                            <option value="{{ $br->branch_name }}">{{ $br->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                <button type="button" wire:click="resetFilters" class="btn btn-ghost text-error hover:bg-error/10 rounded-xl normal-case font-bold">Reset Filter</button>
                <button type="button" @click="open = false" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 font-bold">Terapkan</button>
            </div>
        </div>
    </div>
</div>
