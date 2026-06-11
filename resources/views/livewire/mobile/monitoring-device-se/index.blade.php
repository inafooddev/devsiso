<div class="w-full max-w-md mx-auto min-h-screen bg-slate-50 text-slate-800 flex flex-col shadow-sm border-x border-slate-100 relative" 
     x-data="{ showFiltersSheet: false, showFormSheet: false, showDetailSheet: false }"
     @open-form-sheet.window="showFormSheet = true"
     @open-detail-sheet.window="showDetailSheet = true"
     @close-form-sheet.window="showFormSheet = false">
    
    {{-- Toast Notification (from Session) --}}
    @if (session()->has('message'))
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-[320px] px-4" 
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2">
            <div class="bg-emerald-600 shadow-xl rounded-2xl p-3.5 text-[11px] font-extrabold flex items-center gap-2 text-white border border-black/10">
                <x-heroicon-s-check-circle class="w-5 h-5 text-white flex-shrink-0" />
                <span class="flex-1 text-white leading-snug">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    {{-- Sticky Top Header & Search Wrapper --}}
    <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
        {{-- Header --}}
        <header class="px-4 py-3 flex items-center justify-between" style="padding-top: calc(0.75rem + env(safe-area-inset-top, 0px));">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-sm shadow-primary/10">
                    <x-heroicon-s-device-phone-mobile class="w-5 h-5" />
                </div>
                <div>
                    <h1 class="text-xs font-black uppercase tracking-wider text-slate-900 leading-tight">Monitoring Device</h1>
                    <p class="text-[8px] font-bold text-primary tracking-widest uppercase leading-none">Sales Executive</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- Reset Filters Button -->
                @if($search || $filter_region || $filter_area || $filter_distributor || $filter_year != date('Y'))
                    <button wire:click="resetFilters" class="btn btn-ghost btn-circle btn-xs text-rose-500" title="Reset Filter">
                        <x-heroicon-s-arrow-path class="w-4 h-4" />
                    </button>
                @endif
            </div>
        </header>

        {{-- Search & Filter Bar --}}
        <div class="px-4 pb-3 flex items-center gap-2">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-s-magnifying-glass class="w-5 h-5" />
                </span>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari nama / kode..." class="input input-bordered input-sm h-10 w-full pl-9 pr-8 rounded-xl text-base bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <span class="loading loading-spinner loading-xs text-primary"></span>
                </div>
            </div>
            
            <!-- Filter Button -->
            <button @click="showFiltersSheet = true" class="btn btn-sm btn-square h-10 w-10 rounded-xl border flex items-center justify-center transition-all duration-200 relative {{ ($filter_region || $filter_area || $filter_distributor || $filter_year != date('Y')) ? 'btn-primary text-white shadow-md shadow-primary/20 border-primary' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                <x-heroicon-s-adjustments-horizontal class="w-5 h-5" />
                @if($filter_region || $filter_area || $filter_distributor || $filter_year != date('Y'))
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full"></span>
                @endif
            </button>
        </div>
    </div>

    {{-- Main Content Area --}}
    <main class="flex-1 px-4 py-4 space-y-4 flex flex-col bg-slate-50/50 pb-24">
        
        {{-- Selected Month Indicator --}}
        <div class="flex items-center justify-between">
            <h2 class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">
                Data Tahun: <span class="text-primary">{{ $filter_year }}</span>
            </h2>
            <div wire:loading class="text-xs font-bold text-primary animate-pulse">Memuat...</div>
        </div>

        {{-- Outlet Cards List --}}
        <div class="flex-1 flex flex-col gap-3">
            @forelse($salesData as $item)
                @php
                    $mKey = $item->distributor_code . '_' . $item->sales_code . '_' . $item->month_value;
                    $mData = $monitoringData[$mKey] ?? null;
                @endphp
                <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] flex flex-col gap-3.5">
                    
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-[9px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider w-fit">{{ $item->sales_code }}</span>
                                
                                @if($mData)
                                    <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border bg-emerald-50 text-emerald-600 border-emerald-100/80">Sudah Update</span>
                                @else
                                    <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border bg-rose-50 text-rose-600 border-rose-100/80">Belum Update</span>
                                @endif
                            </div>
                            <h4 class="text-xs font-black text-slate-800 mt-2 tracking-tight truncate">{{ $item->sales_name }} - {{ $item->month_name }}</h4>
                            <p class="text-[10px] text-slate-400 font-semibold leading-normal mt-0.5 line-clamp-2">{{ $item->distributor_name }}</p>
                        </div>
                    </div>
                    
                    <!-- Kondisi Devices (Jika Ada) -->
                    @if($mData)
                    <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100/50">
                        <div>
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi HP</span>
                            <span class="text-[10px] font-black text-slate-700 truncate block max-w-[120px]" title="{{ $mData['kondisi_hp'] }}">{{ $mData['kondisi_hp'] ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi Kartu</span>
                            <span class="text-[10px] font-black {{ $mData['kondisi_kartu'] == 'Aktif' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $mData['kondisi_kartu'] ?: '-' }}</span>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Status and Action Row -->
                    <div class="flex flex-wrap items-center justify-between gap-2.5 border-t border-slate-100 pt-3">
                        <div class="flex items-center gap-1.5">
                            <!-- Indikator Foto -->
                            @if($mData && $mData['foto_tampak_depan'])
                                <div class="flex items-center gap-1 px-2 py-1 rounded-full text-[8px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100/50">
                                    <x-heroicon-s-check-circle class="w-3 h-3" /> Depan
                                </div>
                            @else
                                <div class="flex items-center gap-1 px-2 py-1 rounded-full text-[8px] font-bold bg-slate-50 text-slate-400 border border-slate-100/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Depan
                                </div>
                            @endif

                            @if($mData && $mData['foto_tampak_belakang'])
                                <div class="flex items-center gap-1 px-2 py-1 rounded-full text-[8px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100/50">
                                    <x-heroicon-s-check-circle class="w-3 h-3" /> Belakang
                                </div>
                            @else
                                <div class="flex items-center gap-1 px-2 py-1 rounded-full text-[8px] font-bold bg-slate-50 text-slate-400 border border-slate-100/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Belakang
                                </div>
                            @endif
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-1.5">
                            @if($mData)
                                <button wire:click="showDetail({{ $mData['id'] }})" class="btn btn-xs btn-outline border-slate-200 hover:bg-slate-100 h-8 rounded-lg text-[9px] uppercase font-black text-slate-700 tracking-wider flex items-center gap-1 shadow-xs">
                                    <x-heroicon-s-information-circle class="w-3.5 h-3.5 text-slate-400" /> Detail
                                </button>
                            @else
                                <button wire:click="prefillAdd('{{ $item->distributor_code }}', '{{ $item->sales_code }}', '{{ $item->month_value }}')" class="btn btn-xs btn-primary h-8 rounded-lg text-[9px] uppercase font-black text-white tracking-wider flex items-center gap-1 shadow-xs">
                                    <x-heroicon-s-plus class="w-3.5 h-3.5" /> Tambah
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-xs flex-1 flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                        <x-heroicon-o-magnifying-glass class="w-8 h-8 stroke-[1.5]" />
                    </div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-700">Toko Tidak Ditemukan</h4>
                    <p class="text-[10px] text-slate-400 max-w-[200px] mx-auto leading-normal font-semibold">
                        Tidak ada data sales yang cocok dengan filter atau pencarian Anda.
                    </p>
                </div>
            @endforelse
        </div>

        @if($salesData->hasPages())
            <div class="mt-6 mb-2 flex items-center justify-between gap-3 px-1">
                @if ($salesData->onFirstPage())
                    <button class="btn btn-sm flex-1 bg-white border-slate-200 text-slate-400" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="btn btn-sm flex-1 bg-white border-slate-200 text-slate-700 shadow-sm hover:bg-slate-50">Sebelumnya</button>
                @endif

                <span class="text-[10px] font-bold text-slate-500">Hal {{ $salesData->currentPage() }} / {{ $salesData->lastPage() }}</span>

                @if ($salesData->hasMorePages())
                    <button wire:click="nextPage" class="btn btn-sm flex-1 bg-white border-slate-200 text-slate-700 shadow-sm hover:bg-slate-50">Selanjutnya</button>
                @else
                    <button class="btn btn-sm flex-1 bg-white border-slate-200 text-slate-400" disabled>Selanjutnya</button>
                @endif
            </div>
        @endif
    </main>

    {{-- Bottom Sheet: Filters --}}
    <div x-show="showFiltersSheet" class="fixed inset-0 z-40" x-cloak>
        <div x-show="showFiltersSheet" x-transition.opacity class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs" @click="showFiltersSheet = false"></div>
        <div x-show="showFiltersSheet" x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50">
             
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <div class="px-5 pb-3 flex items-center justify-between border-b border-slate-100 shrink-0">
                 <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                     <x-heroicon-s-adjustments-horizontal class="w-4 h-4 text-primary" /> Filter Pencarian
                 </h3>
                 <button @click="showFiltersSheet = false" class="btn btn-ghost btn-circle btn-xs text-slate-400">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <div class="flex-1 overflow-y-auto p-5 space-y-4">
                 <div class="form-control">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Tahun</span></label>
                     <select wire:model.live="filter_year" class="select select-bordered h-11 w-full rounded-xl text-sm bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                         @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                             <option value="{{ $y }}">{{ $y }}</option>
                         @endfor
                     </select>
                 </div>
                 <div class="form-control">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Region</span></label>
                     <select wire:model.live="filter_region" class="select select-bordered h-11 w-full rounded-xl text-sm bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                         <option value="">Semua Region</option>
                         @foreach($this->getFilterRegions() as $r)
                             <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                         @endforeach
                     </select>
                 </div>
                 <div class="form-control">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Area</span></label>
                     <select wire:model.live="filter_area" class="select select-bordered h-11 w-full rounded-xl text-sm bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                         <option value="">Semua Area</option>
                         @foreach($this->getFilterAreas() as $a)
                             <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                         @endforeach
                     </select>
                 </div>
                 <div class="form-control">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Distributor</span></label>
                     <select wire:model.live="filter_distributor" class="select select-bordered h-11 w-full rounded-xl text-sm bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                         <option value="">Semua Distributor</option>
                         @foreach($this->getFilterDistributors() as $d)
                             <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                         @endforeach
                     </select>
                 </div>
             </div>
             
             <div class="p-5 border-t border-slate-100 shrink-0 bg-slate-50 flex items-center gap-3">
                 <button wire:click="resetFilters" class="btn btn-outline border-slate-200 flex-1 h-11 rounded-xl text-xs font-bold">Reset</button>
                 <button @click="showFiltersSheet = false" class="btn btn-primary flex-1 h-11 rounded-xl text-xs font-bold text-white shadow-md shadow-primary/20">Terapkan</button>
             </div>
        </div>
    </div>

    {{-- Bottom Sheet: Form Upload --}}
    <div x-show="showFormSheet" class="fixed inset-0 z-40" x-cloak>
        <div x-show="showFormSheet" x-transition.opacity class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs" @click="showFormSheet = false"></div>
        <div x-show="showFormSheet" x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[90%] flex flex-col z-50 overflow-hidden">
             
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <div class="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                 <div class="min-w-0 pr-4">
                     <span class="badge badge-primary badge-xs font-mono font-bold rounded-lg px-2 text-[9px]">{{ $form_sales_code }}</span>
                     <h4 class="text-xs font-black text-slate-900 mt-1">{{ $editId ? 'Edit Monitoring' : 'Tambah Monitoring' }}</h4>
                 </div>
                 <button @click="showFormSheet = false" class="btn btn-ghost btn-circle btn-xs text-slate-400">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <form wire:submit.prevent="save" class="flex-1 overflow-y-auto p-5 space-y-4">
                 
                 <!-- Bulan -->
                 <div class="form-control">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Bulan Monitoring</span></label>
                     <input type="month" wire:model="tanggal" class="input input-bordered h-11 rounded-xl text-sm bg-slate-50" {{ $editId ? 'disabled' : '' }} required />
                     @error('tanggal') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                 </div>



                 <div class="divider my-2"></div>

                 <!-- Foto Depan -->
                 <div class="form-control">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Foto Depan (Toko)</span></label>
                     <input type="file" wire:model="foto_tampak_depan" accept="image/*" capture="environment" class="file-input file-input-bordered h-11 rounded-xl text-sm bg-slate-50 w-full" />
                     <div wire:loading wire:target="foto_tampak_depan" class="text-[10px] text-primary font-bold mt-1 animate-pulse">Sedang memproses gambar...</div>
                     @error('foto_tampak_depan') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                     
                     @if($foto_tampak_depan)
                         <img src="{{ $foto_tampak_depan->temporaryUrl() }}" class="mt-2 rounded-xl border max-h-32 object-contain" />
                     @elseif($existing_foto_tampak_depan)
                         <div class="mt-2 relative inline-block">
                             <img src="{{ Storage::url($existing_foto_tampak_depan) }}" class="rounded-xl border max-h-32 object-contain" />
                             <button type="button" wire:click="hapusFotoDepan" class="absolute -top-2 -right-2 btn btn-xs btn-circle btn-error text-white"><x-heroicon-s-x-mark class="w-3 h-3"/></button>
                         </div>
                     @endif
                 </div>

                 <!-- Foto Belakang -->
                 <div class="form-control">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Foto Belakang (Gudang)</span></label>
                     <input type="file" wire:model="foto_tampak_belakang" accept="image/*" capture="environment" class="file-input file-input-bordered h-11 rounded-xl text-sm bg-slate-50 w-full" />
                     <div wire:loading wire:target="foto_tampak_belakang" class="text-[10px] text-primary font-bold mt-1 animate-pulse">Sedang memproses gambar...</div>
                     @error('foto_tampak_belakang') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                     
                     @if($foto_tampak_belakang)
                         <img src="{{ $foto_tampak_belakang->temporaryUrl() }}" class="mt-2 rounded-xl border max-h-32 object-contain" />
                     @elseif($existing_foto_tampak_belakang)
                         <div class="mt-2 relative inline-block">
                             <img src="{{ Storage::url($existing_foto_tampak_belakang) }}" class="rounded-xl border max-h-32 object-contain" />
                             <button type="button" wire:click="hapusFotoBelakang" class="absolute -top-2 -right-2 btn btn-xs btn-circle btn-error text-white"><x-heroicon-s-x-mark class="w-3 h-3"/></button>
                         </div>
                     @endif
                 </div>

                 <div class="grid grid-cols-2 gap-4 mt-2">
                     <div class="form-control">
                         <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Kondisi HP</span></label>
                         <input type="text" wire:model="kondisi_hp" class="input input-bordered h-11 rounded-xl text-sm bg-slate-50" placeholder="Ketik kondisi HP..." required />
                         @error('kondisi_hp') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                     </div>
                     <div class="form-control">
                         <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Kondisi Kartu</span></label>
                         <select wire:model="kondisi_kartu" class="select select-bordered h-11 rounded-xl text-sm bg-slate-50" required>
                             <option value="">Pilih</option>
                             <option value="Aktif">Aktif</option>
                             <option value="Mati">Mati</option>
                             <option value="Hilang">Hilang</option>
                         </select>
                         @error('kondisi_kartu') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                     </div>
                 </div>

                 <div class="pb-16 pt-2"></div> <!-- Spacer for scrolling -->
                 
                 <div class="absolute bottom-0 left-0 right-0 p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3">
                     <button type="button" @click="showFormSheet = false" class="btn btn-outline border-slate-200 flex-1 h-11 rounded-xl text-xs font-bold">Batal</button>
                     <button type="submit" class="btn btn-primary flex-1 h-11 rounded-xl text-xs font-bold text-white shadow-md shadow-primary/20" wire:loading.attr="disabled">
                         <span wire:loading.remove wire:target="save">Simpan</span>
                         <span wire:loading wire:target="save" class="flex items-center gap-2"><span class="loading loading-spinner loading-xs"></span> Menyimpan...</span>
                     </button>
                 </div>
             </form>
        </div>
    </div>

    {{-- Bottom Sheet: Detail Outlet --}}
    <div x-show="showDetailSheet" class="fixed inset-0 z-40" x-cloak>
        <div x-show="showDetailSheet" x-transition.opacity class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs" @click="showDetailSheet = false"></div>
        <div x-show="showDetailSheet" x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50 overflow-hidden">
             
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <div class="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                 <div class="min-w-0 pr-4">
                     <span class="badge badge-secondary badge-xs font-mono font-bold rounded-lg px-2 text-[9px]">Detail Monitoring</span>
                     <h4 class="text-xs font-black text-slate-900 mt-1 truncate">{{ $detailData['sales'] ?? '' }}</h4>
                 </div>
                 <button @click="showDetailSheet = false" class="btn btn-ghost btn-circle btn-xs text-slate-400">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <div class="flex-1 overflow-y-auto p-5 space-y-5 text-xs font-medium text-slate-600">
                 @if($detailData)
                 <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                     <div class="col-span-2">
                         <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Bulan</span>
                         <span class="font-black text-slate-800">{{ $detailData['tanggal'] }}</span>
                     </div>
                     <div class="col-span-2">
                         <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Distributor</span>
                         <span class="font-bold text-slate-800">{{ $detailData['distributor'] }}</span>
                     </div>
                     <div>
                         <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi HP</span>
                         <span class="font-black text-slate-800">{{ $detailData['kondisi_hp'] ?: '-' }}</span>
                     </div>
                     <div>
                         <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kondisi Kartu</span>
                         <span class="font-black {{ $detailData['kondisi_kartu'] == 'Aktif' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $detailData['kondisi_kartu'] ?: '-' }}</span>
                     </div>
                 </div>

                 <!-- Foto Section -->
                 <div class="space-y-3">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-camera class="w-3.5 h-3.5" />
                         Foto Tersimpan
                     </h5>
                     <div class="grid grid-cols-2 gap-3">
                         <!-- Foto Depan -->
                         <div class="border border-slate-200 rounded-xl p-2 bg-white flex flex-col items-center">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 mb-2">Tampak Depan</span>
                             @if($detailData['foto_tampak_depan'])
                                 <img src="{{ Storage::url($detailData['foto_tampak_depan']) }}" class="w-full h-32 object-contain rounded-lg bg-slate-50" />
                             @else
                                 <div class="w-full h-32 bg-slate-50 rounded-lg flex flex-col items-center justify-center text-slate-400">
                                     <x-heroicon-o-photo class="w-6 h-6 mb-1 opacity-50" />
                                     <span class="text-[8px] font-bold">Tidak ada foto</span>
                                 </div>
                             @endif
                         </div>

                         <!-- Foto Belakang -->
                         <div class="border border-slate-200 rounded-xl p-2 bg-white flex flex-col items-center">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 mb-2">Tampak Belakang</span>
                             @if($detailData['foto_tampak_belakang'])
                                 <img src="{{ Storage::url($detailData['foto_tampak_belakang']) }}" class="w-full h-32 object-contain rounded-lg bg-slate-50" />
                             @else
                                 <div class="w-full h-32 bg-slate-50 rounded-lg flex flex-col items-center justify-center text-slate-400">
                                     <x-heroicon-o-photo class="w-6 h-6 mb-1 opacity-50" />
                                     <span class="text-[8px] font-bold">Tidak ada foto</span>
                                 </div>
                             @endif
                         </div>
                     </div>
                 </div>
                 @endif
             </div>
        </div>
    </div>
</div>
