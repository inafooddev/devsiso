<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
    <x-slot name="title">Data Master Kalender</x-slot>

    {{-- Notifikasi Sukses --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0 flex items-start">
            <x-heroicon-s-check-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                <div class="text-xs">{{ session('message') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-success/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    {{-- Notifikasi Error --}}
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0 flex items-start">
            <x-heroicon-s-x-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                <div class="text-xs">{{ session('error') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-error/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col gap-3 bg-base-200/30">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                            <x-heroicon-s-calendar-days class="w-5 h-5" />
                        </div>
                        <h2 class="text-base md:text-lg font-bold">Master Kalender</h2>
                    </div>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5 ml-8">
                        Kelola kalender operasional, nomor minggu (week year & week month), serta hari libur nasional
                    </p>
                </div>
                
                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-1.5 md:gap-2 w-full sm:w-auto justify-start sm:justify-end">
                    @canEdit('master-calenders.index')
                    <button wire:click="openGenerateModal" class="btn btn-sm btn-outline border-base-300 rounded-xl gap-1.5 text-xs hover:bg-base-200 hover:border-base-300">
                        <x-heroicon-s-sparkles class="w-4 h-4 text-warning" />
                        <span>Generate Tahunan</span>
                    </button>
                    <x-ui.action-button type="import" wire:click="$set('isImportModalOpen', true)" />
                    <x-ui.action-button type="add" wire:click="openCreateModal" />
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    @endcanEdit
                    <x-ui.action-button type="export" wire:click="export" />
                </div>
            </div>

            {{-- Filter Bar & Stats Chips --}}
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2.5 pt-2 border-t border-base-300/60">
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari Tanggal / Hari..." />

                    {{-- Filter Tahun --}}
                    <select wire:model.live="yearFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs">
                        <option value="">Semua Tahun</option>
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}">Tahun {{ $yr }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Bulan --}}
                    <select wire:model.live="monthFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs">
                        <option value="">Semua Bulan</option>
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Minggu Bulan --}}
                    <select wire:model.live="weekMonthFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs">
                        <option value="">Semua Minggu</option>
                        <option value="1">Minggu ke-1 (M1)</option>
                        <option value="2">Minggu ke-2 (M2)</option>
                        <option value="3">Minggu ke-3 (M3)</option>
                        <option value="4">Minggu ke-4 (M4)</option>
                        <option value="5">Minggu ke-5 (M5)</option>
                    </select>

                    {{-- Filter Libur --}}
                    <select wire:model.live="liburFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs">
                        <option value="">Semua Status</option>
                        <option value="0">Hari Kerja Saja</option>
                        <option value="1">Libur Nasional Saja</option>
                    </select>
                </div>

                {{-- Summary Stats Pills --}}
                <div class="flex items-center gap-2 text-xs shrink-0 self-end lg:self-center">
                    <span class="badge badge-sm badge-ghost border border-base-300 py-2.5 px-3 rounded-lg font-medium">
                        Total: <b class="ml-1 text-base-content">{{ number_format($totalDays) }}</b>&nbsp;hari
                    </span>
                    <span class="badge badge-sm badge-success/15 text-success border border-success/20 py-2.5 px-3 rounded-lg font-medium">
                        Kerja: <b class="ml-1">{{ number_format($totalKerja) }}</b>
                    </span>
                    <span class="badge badge-sm badge-error/15 text-error border border-error/20 py-2.5 px-3 rounded-lg font-medium">
                        Libur: <b class="ml-1">{{ number_format($totalLibur) }}</b>
                    </span>
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Data) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th class="text-center">Hari ke</th>
                        <th class="text-center">Minggu (Thn / Bln)</th>
                        <th class="text-center">Bulan</th>
                        <th class="text-center">Kuartal / Sem</th>
                        <th class="text-center">Status</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($calenders as $index => $row)
                        @php
                            $dateStr = $row->date instanceof \Carbon\Carbon ? $row->date->format('Y-m-d') : substr((string)$row->date, 0, 10);
                            $carbonDate = \Carbon\Carbon::parse($dateStr);
                            $isSunday = $row->day_number == 7;
                        @endphp
                        <tr wire:key="calender-{{ $dateStr }}" class="hover:bg-base-200/50 transition-colors group {{ $row->libur_nasional ? 'bg-error/5' : ($isSunday ? 'bg-warning/5' : '') }}">
                            <th class="text-center font-mono text-xs opacity-70">{{ $calenders->firstItem() + $index }}</th>
                            
                            {{-- Tanggal --}}
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-base-content/95 font-mono">{{ $carbonDate->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-base-content/50 font-mono">({{ $dateStr }})</span>
                                </div>
                            </td>

                            {{-- Hari --}}
                            <td>
                                <span class="badge badge-sm font-semibold {{ $isSunday ? 'badge-error badge-outline' : 'badge-ghost' }}">
                                    {{ $row->day }}
                                </span>
                            </td>

                            {{-- Nomor Hari (1..7) --}}
                            <td class="text-center font-mono text-xs">
                                <span class="px-2 py-0.5 rounded bg-base-200 text-base-content/80 font-bold">
                                    {{ $row->day_number }}
                                </span>
                            </td>

                            {{-- Minggu Tahun & Bulan --}}
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5 font-mono text-xs">
                                    <span class="badge badge-sm badge-info/15 text-info border border-info/30 font-semibold" title="Minggu ke-{{ $row->week_year }} dalam setahun">
                                        W{{ $row->week_year }}
                                    </span>
                                    <span class="text-base-content/30">/</span>
                                    <span class="badge badge-sm badge-primary/15 text-primary border border-primary/30 font-semibold" title="Minggu ke-{{ $row->week_month }} dalam bulan">
                                        M{{ $row->week_month }}
                                    </span>
                                </div>
                            </td>

                            {{-- Bulan --}}
                            <td class="text-center">
                                <span class="text-xs font-medium text-base-content/80">
                                    {{ $monthNames[$row->month] ?? $row->month }} ({{ $row->year }})
                                </span>
                            </td>

                            {{-- Kuartal / Semester --}}
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1 text-xs">
                                    <span class="badge badge-sm badge-outline border-base-300 font-bold text-base-content/80">
                                        Q{{ $row->quarter }}
                                    </span>
                                    <span class="text-base-content/40 text-[10px] font-medium">
                                        (S{{ $row->semester }})
                                    </span>
                                </div>
                            </td>

                            {{-- Status Libur Nasional --}}
                            <td class="text-center">
                                @canEdit('master-calenders.index')
                                <button wire:click="toggleLiburNasional('{{ $dateStr }}')" 
                                        type="button"
                                        class="badge badge-sm gap-1 cursor-pointer transition-all duration-200 select-none {{ $row->libur_nasional ? 'badge-error text-white shadow-sm shadow-error/20 hover:opacity-85' : 'badge-success/15 text-success hover:bg-success/25' }}"
                                        title="Klik untuk ubah status hari kerja / libur">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $row->libur_nasional ? 'bg-white' : 'bg-success' }}"></span>
                                    <span class="font-semibold text-[11px]">{{ $row->libur_nasional ? 'Libur Nasional' : 'Hari Kerja' }}</span>
                                </button>
                                @else
                                <span class="badge badge-sm {{ $row->libur_nasional ? 'badge-error text-white' : 'badge-ghost text-base-content/70' }}">
                                    {{ $row->libur_nasional ? 'Libur Nasional' : 'Hari Kerja' }}
                                </span>
                                @endcanEdit
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                @canEdit('master-calenders.index')
                                <div class="flex items-center justify-center gap-1">
                                    <x-ui.action-button type="edit" wire:click="openEditModal('{{ $dateStr }}')" class="btn-square btn-xs" title="Edit" />
                                    <x-ui.action-button type="delete" wire:click="confirmDelete('{{ $dateStr }}')" class="btn-square btn-xs" title="Hapus" />
                                </div>
                                @else
                                <span class="text-[10px] uppercase tracking-wider text-base-content/50 font-bold">View Only</span>
                                @endcanEdit
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="flex flex-col items-center justify-center py-12 text-base-content/40">
                                    <x-heroicon-o-inbox class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="text-sm font-medium">Tidak ada data kalender ditemukan.</p>
                                    <p class="text-xs text-base-content/40 mt-1">Gunakan tombol "Tambah Data" atau "Generate Tahunan" untuk menambahkan jadwal.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($calenders->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200/60 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-base-content/60">
                    Menampilkan <b>{{ $calenders->firstItem() ?? 0 }}</b> - <b>{{ $calenders->lastItem() ?? 0 }}</b> dari <b>{{ $calenders->total() }}</b> tanggal
                </div>
                <div class="overflow-x-auto max-w-full">
                    {{ $calenders->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL FORM (CREATE / EDIT) --}}
    {{-- ======================================================== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden ring-1 ring-base-content/5">
            
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
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Data Kalender' : 'Tambah Tanggal Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ $isEditing ? 'Perbarui nomor minggu, kuartal, atau status libur' : 'Atribut hari, minggu, dan bulan akan otomatis dikalkulasi' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-4 bg-base-100 max-h-[70vh] overflow-y-auto">
                    
                    {{-- Tanggal --}}
                    <div class="space-y-1.5">
                        <label for="date" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Tanggal</label>
                        <div class="relative group">
                            <input wire:model.live.debounce.400ms="date" type="date" id="date"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('date') input-error @enderror"
                                   {{ $isEditing ? 'disabled' : '' }}>
                            @if($isEditing)
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                    <x-heroicon-s-lock-closed class="w-4 h-4" />
                                </div>
                            @endif
                        </div>
                        @error('date') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    {{-- Hari & Nomor Hari --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="day" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Hari</label>
                            <input wire:model="day" type="text" id="day" placeholder="Contoh: Senin"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('day') input-error @enderror">
                            @error('day') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="day_number" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Nomor Hari (1-7)</label>
                            <input wire:model="day_number" type="number" min="1" max="7" id="day_number" placeholder="1-7"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('day_number') input-error @enderror">
                            @error('day_number') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Minggu Tahun & Minggu Bulan --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="week_year" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Minggu Tahun (Week Year)</label>
                            <input wire:model="week_year" type="number" min="1" max="54" id="week_year" placeholder="1 - 53"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('week_year') input-error @enderror">
                            @error('week_year') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="week_month" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Minggu Bulan (Week Month)</label>
                            <input wire:model="week_month" type="number" min="1" max="6" id="week_month" placeholder="1 - 5"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('week_month') input-error @enderror">
                            @error('week_month') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Bulan & Tahun --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="month" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Bulan (1-12)</label>
                            <select wire:model="month" id="month" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('month') select-error @enderror">
                                <option value="">-- Pilih Bulan --</option>
                                @foreach($monthNames as $num => $name)
                                    <option value="{{ $num }}">{{ $num }} - {{ $name }}</option>
                                @endforeach
                            </select>
                            @error('month') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="year" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Tahun</label>
                            <input wire:model="year" type="number" min="2000" max="2100" id="year" placeholder="Contoh: 2026"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('year') input-error @enderror">
                            @error('year') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Kuartal & Semester --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="quarter" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Kuartal (Q1 - Q4)</label>
                            <select wire:model="quarter" id="quarter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('quarter') select-error @enderror">
                                <option value="1">Kuartal 1 (Q1)</option>
                                <option value="2">Kuartal 2 (Q2)</option>
                                <option value="3">Kuartal 3 (Q3)</option>
                                <option value="4">Kuartal 4 (Q4)</option>
                            </select>
                            @error('quarter') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="semester" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Semester (S1 - S2)</label>
                            <select wire:model="semester" id="semester" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('semester') select-error @enderror">
                                <option value="1">Semester 1 (S1)</option>
                                <option value="2">Semester 2 (S2)</option>
                            </select>
                            @error('semester') <span class="text-error text-xs font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Status Libur Nasional --}}
                    <div class="pt-2">
                        <label class="label cursor-pointer p-3 rounded-2xl bg-base-200 border border-base-300 hover:border-primary/40 transition-all">
                            <div class="flex flex-col">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content">Tandai Libur Nasional</span>
                                <span class="text-[11px] text-base-content/60">Aktifkan jika tanggal ini merupakan hari libur nasional resmi</span>
                            </div>
                            <input wire:model="libur_nasional" type="checkbox" class="toggle toggle-error toggle-sm" />
                        </label>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Tambahkan Tanggal' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL GENERATE TAHUNAN --}}
    {{-- ======================================================== --}}
    <div x-data="{ open: @entangle('isGenerateModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden ring-1 ring-base-content/5">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-warning/10 text-warning">
                        <x-heroicon-s-sparkles class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">Generate Kalender Otomatis</h3>
                        <p class="text-xs text-base-content/50">Buat jadwal tanggal 1 tahun penuh beserta perhitungan nomor minggu secara berurutan</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="generateYearlyCalendar">
                <div class="p-6 space-y-4 bg-base-100">
                    
                    {{-- Pilihan Tahun Cepat --}}
                    <div class="space-y-1.5">
                        <label for="generateYear" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Tahun Target</label>
                        <input wire:model.live="generateYear" type="number" min="2020" max="2050" id="generateYear"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl font-bold text-base focus:ring-2 focus:ring-primary/50">
                        <span class="text-[11px] text-base-content/50 ml-1">Mengubah tahun akan menyesuaikan rentang tanggal mulai & akhir otomatis.</span>
                    </div>

                    {{-- Rentang Tanggal Mulai & Akhir --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="generateStartDate" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Tanggal Mulai</label>
                            <input wire:model="generateStartDate" type="date" id="generateStartDate"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('generateStartDate') input-error @enderror">
                            @error('generateStartDate') <span class="text-error text-xs ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="generateEndDate" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Tanggal Selesai</label>
                            <input wire:model="generateEndDate" type="date" id="generateEndDate"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('generateEndDate') input-error @enderror">
                            @error('generateEndDate') <span class="text-error text-xs ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Konfigurasi Awal Nomor Minggu --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="generateStartWeekYear" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Minggu Tahun Awal</label>
                            <input wire:model="generateStartWeekYear" type="number" min="1" max="53" id="generateStartWeekYear"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('generateStartWeekYear') input-error @enderror">
                        </div>

                        <div class="space-y-1.5">
                            <label for="generateStartWeekMonth" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Minggu Bulan Awal</label>
                            <input wire:model="generateStartWeekMonth" type="number" min="1" max="5" id="generateStartWeekMonth"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 @error('generateStartWeekMonth') input-error @enderror">
                        </div>
                    </div>

                    {{-- Overwrite Checkbox --}}
                    <div class="pt-2">
                        <label class="label cursor-pointer p-3 rounded-2xl bg-base-200 border border-base-300 hover:border-warning/40 transition-all">
                            <div class="flex flex-col">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content">Timpa Data Lama (Overwrite)</span>
                                <span class="text-[11px] text-base-content/60">Jika dicentang, tanggal yang sudah ada dalam rentang ini akan diperbarui nilainya</span>
                            </div>
                            <input wire:model="overwriteExisting" type="checkbox" class="checkbox checkbox-warning checkbox-sm rounded-lg" />
                        </label>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-xl px-8 normal-case shadow-sm shadow-warning/20 gap-2">
                        <span wire:loading.remove wire:target="generateYearlyCalendar">Mulai Generate</span>
                        <span wire:loading wire:target="generateYearlyCalendar" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-sparkles wire:loading.remove wire:target="generateYearlyCalendar" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL KONFIRMASI HAPUS --}}
    {{-- ======================================================== --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5">
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Tanggal?</h3>
                <p class="text-sm text-base-content/60 leading-relaxed px-4">
                    Apakah Anda yakin ingin menghapus tanggal <span class="font-bold font-mono text-base-content">{{ $dateToDelete }}</span> dari master kalender?
                </p>
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

    {{-- ======================================================== --}}
    {{-- MODAL IMPORT EXCEL --}}
    {{-- ======================================================== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">Import Data Kalender</h3>
                        <p class="text-xs text-base-content/50">Upload file Excel (.xlsx, .xls, .csv)</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 space-y-4 bg-base-100">
                    <div class="p-3 bg-base-200/60 rounded-2xl border border-base-300/80 flex items-center justify-between">
                        <div class="text-xs">
                            <p class="font-bold text-base-content">Belum punya formatnya?</p>
                            <p class="text-base-content/60 text-[11px]">Download template Excel resmi di sini</p>
                        </div>
                        <button type="button" wire:click="downloadTemplate" class="btn btn-xs btn-outline border-base-300 rounded-lg gap-1">
                            <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5 text-primary" />
                            <span>Template</span>
                        </button>
                    </div>

                    <div class="space-y-1.5">
                        <label for="importFile" class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-1">Pilih File</label>
                        <input wire:model="importFile" type="file" id="importFile" accept=".xlsx,.xls,.csv"
                               class="file-input file-input-bordered file-input-primary w-full bg-base-200 rounded-2xl @error('importFile') file-input-error @enderror">
                        @error('importFile') <span class="text-error text-xs ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div wire:loading wire:target="importFile" class="text-xs text-info flex items-center gap-2">
                        <span class="loading loading-spinner loading-xs"></span>
                        <span>Mengunggah file...</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-xl px-8 normal-case shadow-sm shadow-info/20 gap-2">
                        <span wire:loading.remove wire:target="import">Upload & Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-arrow-up-tray wire:loading.remove wire:target="import" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
