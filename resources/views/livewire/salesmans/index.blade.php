<div>
    <x-slot name="title">Data Salesman</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
        {{-- Notifikasi --}}
        <div class="mb-6 space-y-3">
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                     class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                    <div><h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                         <div class="text-sm">{{ session('message') }}</div></div>
                </div>
            @endif
            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                    <div><h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                         <div class="text-sm">{{ session('error') }}</div></div>
                </div>
            @endif
        </div>

        <x-card flush title="Data Salesman" icon="user-group" subtitle="Kelola data salesman per distributor dengan filter wilayah berjenjang" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari kode/nama salesman..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Filter Button --}}
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        Filter
                        @if($hasAppliedFilters)
                            <span class="badge badge-xs badge-primary rounded-full">ON</span>
                        @endif
                    </button>

                    {{-- Export --}}
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export
                    </button>

                    {{-- Add Button --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openCreateModal"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            {{-- State: Filter Belum Diterapkan --}}
            @if (!$hasAppliedFilters)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-funnel class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Filter Belum Diterapkan</h3>
                    <p class="text-sm text-center max-w-xs">Klik tombol <strong>Filter</strong> untuk memilih region, area, atau distributor dan menampilkan data salesman.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter
                    </button>
                </div>
            @else
                {{-- Tabel --}}
                <x-ui.table empty="Tidak ada salesman yang cocok dengan kriteria filter.">
                    <x-slot:head>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Distributor</th>
                            <th>Salesman Code</th>
                            <th>Nama Salesman</th>
                            <th class="text-center">Status</th>
                            <th>Tgl. Dibuat</th>
                            <th class="text-center w-24">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($salesmans as $index => $salesman)
                        <tr wire:key="salesman-{{ $salesman->salesman_code }}-{{ $salesman->distributor_code }}" class="group text-sm">
                            <td><span class="text-xs font-semibold text-base-content/40">{{ $salesmans->firstItem() + $index }}</span></td>
                            <td>
                                <div>
                                    <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">
                                        {{ $salesman->masterDistributor->distributor_name ?? 'N/A' }}
                                    </span>
                                    <div class="text-xs text-base-content/40 font-mono mt-0.5">{{ $salesman->distributor_code }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-sm badge-outline border-base-300 text-primary font-mono px-2 py-3 rounded-lg">{{ $salesman->salesman_code }}</span>
                            </td>
                            <td>
                                <span class="text-base-content/70">{{ $salesman->salesman_name }}</span>
                            </td>
                            <td class="text-center">
                                @if ($salesman->is_active)
                                    <span class="badge badge-sm badge-success/20 text-success border-success/30 px-3 rounded-full">Aktif</span>
                                @else
                                    <span class="badge badge-sm badge-error/20 text-error border-error/30 px-3 rounded-full">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2 text-base-content/50 text-xs">
                                    <x-heroicon-s-calendar class="w-3.5 h-3.5 shrink-0" />
                                    <span>{{ $salesman->created_at->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    @unless(auth()->user()->hasRole('guest'))
                                    <button wire:click="edit('{{ $salesman->distributor_code }}', '{{ $salesman->salesman_code }}')"
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="confirmDelete('{{ $salesman->salesman_code }}', '{{ $salesman->distributor_code }}')"
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if($salesmans->hasPages())
                    <div class="mt-4 px-6">{{ $salesmans->links() }}</div>
                @endif
            @endif
        </x-card>
    </div>

    {{-- ========== MODAL FILTER ========== --}}
    <div x-data="{ open: @entangle('isFilterModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Salesman</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Pilih wilayah untuk menampilkan data</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters">
                <div class="p-6 space-y-4">
                    {{-- Region --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                        <select wire:model.live="regionFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <option value="">Semua Region</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Area --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                        <select wire:model.live="areaFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$regionFilter) disabled @endif>
                            <option value="">Semua Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Distributor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
                        <select wire:model="distributorFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$areaFilter) disabled @endif>
                            <option value="">Semua Distributor</option>
                            @foreach($distributors as $distributor)
                                <option value="{{ $distributor->distributor_code }}"
                                        class="{{ $distributor->is_active ? '' : 'opacity-50' }}">
                                    {{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" wire:click="resetFilters" @click="open = false"
                            class="btn btn-ghost rounded-xl normal-case text-error hover:bg-error/10 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" /> Reset
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                            <x-heroicon-s-funnel class="w-4 h-4" /> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL FORM SALESMAN (CREATE & EDIT) ========== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Salesman' : 'Tambah Salesman' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data salesman' : 'Isi detail salesman baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    @if(!$isEditing)
                        {{-- Region & Area (Hanya saat Create) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region <span class="text-error">*</span></label>
                                <select wire:model.live="formRegionFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    <option value="">-- Pilih Region --</option>
                                    @foreach($formRegions as $region)
                                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area <span class="text-error">*</span></label>
                                <select wire:model.live="formAreaFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40" @if(!$formRegionFilter) disabled @endif>
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($formAreas as $area)
                                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Distributor --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor <span class="text-error">*</span></label>
                            <select wire:model.live="distributor_code" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40 @error('distributor_code') select-error @enderror" @if(!$formAreaFilter) disabled @endif>
                                <option value="">-- Pilih Distributor --</option>
                                @foreach($formDistributors as $distributor)
                                    <option value="{{ $distributor->distributor_code }}" class="{{ $distributor->is_active ? '' : 'opacity-50 text-error' }}">
                                        {{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('distributor_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>

                        {{-- Manual Number --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nomor Urut Kode <span class="text-error">*</span></label>
                            <input type="text" wire:model.live="manual_number" placeholder="Contoh: 01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('manual_number') input-error @enderror">
                            @error('manual_number') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            <p class="mt-1 text-[10px] text-base-content/40 italic ml-1">Masukkan angka atau akhiran unik untuk salesman.</p>
                        </div>
                    @else
                        {{-- Distributor Code (read-only saat Edit) --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Distributor</label>
                            <input type="text" wire:model="distributor_code" readonly
                                   class="input input-bordered w-full bg-base-300/50 border-base-300 rounded-2xl font-mono text-base-content/60 cursor-not-allowed focus:ring-0">
                        </div>
                    @endif

                    {{-- Salesman Code --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Salesman <span class="text-error">*</span></label>
                        <input type="text" wire:model="salesman_code" {{ !$isEditing ? 'readonly' : '' }}
                               class="input input-bordered w-full {{ !$isEditing ? 'bg-base-300/50 cursor-not-allowed text-primary font-bold' : 'bg-base-200' }} border-base-300 rounded-2xl font-mono focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('salesman_code') input-error @enderror">
                        @error('salesman_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                    </div>

                    {{-- Salesman Name --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Salesman <span class="text-error">*</span></label>
                        <input type="text" wire:model="salesman_name"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('salesman_name') input-error @enderror">
                        @error('salesman_name') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status</label>
                        <select wire:model="is_active"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Salesman' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL KONFIRMASI HAPUS ========== --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm ring-1 ring-base-content/5 text-base-content">
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold mb-2 leading-none">Hapus Salesman?</h3>
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Data salesman ini akan dihapus secara <span class="text-error font-bold italic">permanen</span> dan tidak dapat dipulihkan.</p>
            </div>
            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>
</div>
