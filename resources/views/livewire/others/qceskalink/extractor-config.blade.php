<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Konfigurasi Ekstraktor</x-slot>

    {{-- Tabs Navigation --}}
    @include('livewire.others.qceskalink._tabs')

    {{-- Notifikasi Toast --}}
    <div class="toast toast-top toast-center z-[100] mt-16">
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
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <x-heroicon-s-cog-6-tooth class="w-5 h-5" />
                    </div>
                    Konfigurasi Ekstraktor (Master Data)
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola pengaturan kolom dan kriteria file yang akan di ekstrak</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari konfigurasi..." />
                
                <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                    <x-heroicon-s-plus class="w-4 h-4" />
                    Tambah Konfigurasi
                </button>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-10 text-center">No</th>
                        <th>Nama Konfigurasi</th>
                        <th class="text-center">Baris Header</th>
                        <th>Keywords</th>
                        <th class="text-center">QTY</th>
                        <th class="text-center whitespace-nowrap">DISC 4</th>
                        <th class="text-center whitespace-nowrap">DISC 8</th>
                        <th class="text-center">NETT</th>
                        <th class="text-center w-24 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] bg-base-200/40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($configs as $idx => $config)
                        @php
                            $cols = collect($config->columns ?? []);
                            $qty = $cols->firstWhere('label', 'QTY');
                            $disc4 = $cols->firstWhere('label', 'DISC 4');
                            $disc8 = $cols->firstWhere('label', 'DISC 8');
                            $nett = $cols->first(fn($c) => stripos($c['label'] ?? '', 'NETT') !== false);
                        @endphp
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="text-center font-semibold">{{ $idx + 1 }}</td>
                            <td class="font-bold">{{ $config->name }}</td>
                            <td class="text-center">{{ $config->header_row }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($config->keywords ?? [] as $kw)
                                        <span class="badge badge-sm badge-ghost border-base-300 font-semibold">{{ $kw }}</span>
                                    @empty
                                        <span class="text-xs text-base-content/40 italic">-</span>
                                    @endforelse
                                </div>
                            </td>
                            
                            {{-- Specific Columns --}}
                            <td class="text-center">
                                @if($qty)
                                    <span class="font-mono bg-base-200 px-1.5 py-0.5 rounded border border-base-300 shadow-sm text-xs font-bold">{{ $qty['source'] ?? '' }}</span>
                                @else
                                    <span class="text-base-content/30 text-xs">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($disc4)
                                    <span class="font-mono bg-base-200 px-1.5 py-0.5 rounded border border-base-300 shadow-sm text-xs font-bold">{{ $disc4['source'] ?? '' }}</span>
                                @else
                                    <span class="text-base-content/30 text-xs">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($disc8)
                                    <span class="font-mono bg-base-200 px-1.5 py-0.5 rounded border border-base-300 shadow-sm text-xs font-bold">{{ $disc8['source'] ?? '' }}</span>
                                @else
                                    <span class="text-base-content/30 text-xs">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($nett)
                                    <div class="flex justify-center" title="{{ $nett['label'] }}">
                                        <span class="font-mono bg-success/10 text-success border border-success/20 px-1.5 py-0.5 rounded shadow-sm text-xs font-bold">{{ $nett['source'] ?? '' }}</span>
                                    </div>
                                @else
                                    <span class="text-base-content/30 text-xs">-</span>
                                @endif
                            </td>

                            <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $config->id }})" class="btn btn-xs btn-ghost btn-circle text-info hover:bg-info/10" title="Edit">
                                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button type="button" 
                                            @click="
                                                if (typeof Swal !== 'undefined') {
                                                    Swal.fire({
                                                        title: 'Hapus Konfigurasi?',
                                                        text: 'Konfigurasi yang dihapus tidak dapat dikembalikan!',
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#d33',
                                                        cancelButtonColor: '#3085d6',
                                                        confirmButtonText: 'Ya, Hapus!',
                                                        cancelButtonText: 'Batal'
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            $wire.delete({{ $config->id }});
                                                        }
                                                    });
                                                } else {
                                                    if(confirm('Yakin ingin menghapus konfigurasi ini?')) {
                                                        $wire.delete({{ $config->id }});
                                                    }
                                                }
                                            " 
                                            class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error/10" title="Hapus">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-base-content/40">
                                <div class="flex flex-col items-center justify-center">
                                    <x-heroicon-o-folder-open class="w-12 h-12 mb-3 opacity-50" />
                                    <p class="font-semibold text-base">Belum ada konfigurasi</p>
                                    <p class="text-xs">Silakan tambah konfigurasi baru untuk mulai menggunakan ekstraktor.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form --}}
    <div x-data="{ open: @entangle('isModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" class="fixed inset-0 bg-base-100/80 backdrop-blur-sm"></div>
        
        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 shrink-0">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <x-heroicon-s-cog-6-tooth class="w-5 h-5 text-primary" />
                    {{ $isEditing ? 'Edit Konfigurasi' : 'Tambah Konfigurasi' }}
                </h3>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="p-6 overflow-auto flex-1 bg-base-50/50 space-y-6">
                {{-- Group Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label text-xs font-bold uppercase tracking-wider text-base-content/60">Nama Konfigurasi / Group</label>
                        <input type="text" wire:model="name" class="input input-bordered w-full bg-base-100" placeholder="Misal: Indomarco HO">
                        @error('name') <span class="text-error text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="label text-xs font-bold uppercase tracking-wider text-base-content/60">Baris Header Excel</label>
                        <input type="number" wire:model="header_row" class="input input-bordered w-full bg-base-100" min="1">
                        @error('header_row') <span class="text-error text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Keywords --}}
                <div class="bg-base-100 p-4 rounded-xl border border-base-300">
                    <label class="label text-xs font-bold uppercase tracking-wider text-base-content/60 pb-1">Keywords Identifikasi File</label>
                    <p class="text-xs text-base-content/50 mb-3">Masukkan kata kunci (nama file) agar sistem tahu file ini cocok dengan grup konfigurasi ini.</p>
                    
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($keywords as $idx => $kw)
                            <div class="badge badge-primary gap-1 p-3">
                                {{ $kw }}
                                <button type="button" wire:click="removeKeyword({{ $idx }})" class="hover:text-error ml-1"><x-heroicon-s-x-mark class="w-3 h-3" /></button>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex gap-2">
                        <input type="text" id="newKeyword" wire:model="newKeyword" wire:keydown.enter.prevent="addKeyword" class="input input-sm input-bordered w-full max-w-xs" placeholder="Ketik lalu Enter">
                        <button type="button" wire:click="addKeyword" class="btn btn-sm btn-primary">
                            <span wire:loading wire:target="addKeyword" class="loading loading-spinner loading-xs"></span>
                            Tambah
                        </button>
                    </div>
                </div>

                {{-- Columns Config --}}
                <div class="bg-base-100 p-4 rounded-xl border border-base-300">
                    <div class="flex justify-between items-center mb-4">
                        <label class="label text-xs font-bold uppercase tracking-wider text-base-content/60 p-0">Pemetaan Kolom Excel</label>
                    </div>
                    
                    <div class="overflow-x-auto border border-base-200 rounded-lg mb-4">
                        <table class="table table-sm w-full">
                            <thead class="bg-base-200 text-xs">
                                <tr>
                                    <th>Kolom (A,B..)</th>
                                    <th>Label / Nama</th>
                                    <th>Tipe</th>
                                    <th>Pengaturan Filter</th>
                                    <th class="text-center w-10">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($columns as $idx => $col)
                                    <tr>
                                        <td class="font-mono font-bold">{{ $col['source'] ?? '' }}</td>
                                        <td>{{ $col['label'] ?? '' }}</td>
                                        <td>
                                            @if(($col['type'] ?? '') === 'text')
                                                <span class="badge badge-ghost badge-sm text-xs">Text (Biasa)</span>
                                            @elseif(($col['type'] ?? '') === 'sum')
                                                <span class="badge badge-success badge-sm text-xs">Jumlah (Sum)</span>
                                            @else
                                                <span class="badge badge-warning badge-sm text-xs">Jumlah Terfilter</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(($col['type'] ?? '') === 'filtered_sum')
                                                <div class="text-xs">
                                                    Kolom <span class="font-mono font-bold">{{ $col['filterCol'] ?? '' }}</span> 
                                                    <span class="font-bold text-primary px-1">{{ $col['filterOp'] ?? '' }}</span> 
                                                    "{{ $col['filterVal'] ?? '' }}"
                                                </div>
                                            @else
                                                <span class="text-base-content/30">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" wire:click="removeColumn({{ $idx }})" class="btn btn-xs btn-ghost text-error"><x-heroicon-s-trash class="w-4 h-4" /></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-base-content/40 py-4 text-xs">Belum ada kolom yang ditambahkan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Form Tambah Kolom Baru --}}
                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-200">
                        <h4 class="text-xs font-bold mb-3">Tambah Kolom Baru</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                            <div>
                                <label class="text-[10px] font-bold uppercase text-base-content/50">Nama Header di Excel</label>
                                <input type="text" id="newColSource" wire:model="newColSource" class="input input-sm input-bordered w-full font-mono uppercase" placeholder="Misal: NAMA BARANG">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-base-content/50">Label Output</label>
                                @php
                                    $usedLabels = collect($columns ?? [])->pluck('label')->toArray();
                                @endphp
                                <select id="newColLabel" wire:model="newColLabel" class="select select-sm select-bordered w-full">
                                    <option value="">-- Pilih Label --</option>
                                    @if(!in_array('QTY', $usedLabels)) <option value="QTY">QTY</option> @endif
                                    @if(!in_array('DISC 4', $usedLabels)) <option value="DISC 4">DISC 4</option> @endif
                                    @if(!in_array('DISC 8', $usedLabels)) <option value="DISC 8">DISC 8</option> @endif
                                    @if(!in_array('NETT', $usedLabels)) <option value="NETT">NETT</option> @endif
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-base-content/50">Tipe Kolom</label>
                                <select wire:model.live="newColType" class="select select-sm select-bordered w-full">
                                    <option value="text">Teks (tidak dijumlah)</option>
                                    <option value="sum">Jumlah (Sum)</option>
                                    <option value="filtered_sum">Jumlah Terfilter</option>
                                </select>
                            </div>
                        </div>

                        @if($newColType === 'filtered_sum')
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3 items-end border-t border-base-300 pt-3">
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-warning">Filter di Kolom (A, B)</label>
                                    <input type="text" wire:model="newColFilterCol" class="input input-sm input-bordered w-full font-mono uppercase" placeholder="A">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-warning">Operator</label>
                                    <select wire:model="newColFilterOp" class="select select-sm select-bordered w-full">
                                        <option value="=">=</option>
                                        <option value="!=">!=</option>
                                        <option value=">">&gt;</option>
                                        <option value="<">&lt;</option>
                                        <option value=">=">&gt;=</option>
                                        <option value="<=">&lt;=</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-warning">Nilai Pembanding</label>
                                    <input type="text" wire:model="newColFilterVal" class="input input-sm input-bordered w-full" placeholder="Nilai">
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="addColumn" class="btn btn-sm btn-outline btn-primary">
                                <span wire:loading wire:target="addColumn" class="loading loading-spinner loading-xs"></span>
                                Tambah Kolom
                            </button>
                        </div>
                        @if($errors->has('newColSource') || $errors->has('newColLabel') || $errors->has('newColType') || $errors->has('newColFilterCol') || $errors->has('newColFilterOp') || $errors->has('newColFilterVal'))
                            <div class="text-error text-[10px] mt-2">Pastikan semua kolom isian dilengkapi dengan benar.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-base-300 bg-base-200/30 shrink-0 rounded-b-3xl">
                <button type="button" @click="open = false" class="btn btn-ghost normal-case">Batal</button>
                <button type="button" 
                        @click="
                            let hasUnsavedCol = document.getElementById('newColSource').value.trim() !== '' || document.getElementById('newColLabel').value.trim() !== '';
                            let hasUnsavedKw = document.getElementById('newKeyword').value.trim() !== '';
                            if (hasUnsavedCol || hasUnsavedKw) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: 'Perhatian!',
                                        text: 'Ada form Kolom atau Keyword yang sudah diisi tapi belum ditambahkan. Yakin ingin menyimpan pengaturan sekarang?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3085d6',
                                        cancelButtonColor: '#d33',
                                        confirmButtonText: 'Ya, Simpan!',
                                        cancelButtonText: 'Batal'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $wire.save();
                                        }
                                    });
                                } else {
                                    if(confirm('Ada form Kolom atau Keyword yang sudah diisi tapi belum ditambahkan. Yakin ingin menyimpan pengaturan sekarang?')) {
                                        $wire.save();
                                    }
                                }
                            } else {
                                $wire.save();
                            }
                        " 
                        class="btn btn-primary normal-case px-8" wire:loading.class="btn-disabled">
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
