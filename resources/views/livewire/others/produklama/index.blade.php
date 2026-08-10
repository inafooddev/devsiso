<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Master Produk Lama</x-slot>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Master Produk Lama</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar semua produk lama beserta informasinya</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative grow sm:grow-0">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari nama produk, brand..." 
                        class="input input-sm input-bordered rounded-xl w-full sm:w-64 bg-base-100 pl-9"
                    />
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/40" />
                </div>

                {{-- Add Button --}}
                <button wire:click="openCreateModal" class="btn btn-sm btn-primary text-white rounded-xl gap-2 font-bold shadow-sm">
                    <x-heroicon-s-plus class="w-4 h-4" />
                    Tambah Data
                </button>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Nama Produk</th>
                        <th>Brand / Subbrand</th>
                        <th>Kategori</th>
                        <th>Top 6</th>
                        <th>Grup Top 6</th>
                        <th class="text-right">Pak</th>
                        <th class="text-right">Pcs</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($produks as $index => $row)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th class="text-center">{{ $produks->firstItem() + $index }}</th>
                            <td class="font-bold text-base-content">{{ $row->nama_produk }}</td>
                            <td class="text-xs">
                                <span class="font-bold text-primary">{{ $row->brand ?? '-' }}</span> 
                                @if($row->subbrand)
                                    <span class="text-base-content/50 mx-1">/</span>
                                    <span class="text-base-content/70">{{ $row->subbrand }}</span>
                                @endif
                            </td>
                            <td class="text-xs">{{ $row->kategory ?? '-' }}</td>
                            <td class="text-xs">{{ $row->top_6 ?? '-' }}</td>
                            <td class="text-xs">{{ $row->grup_top_6 ?? '-' }}</td>
                            <td class="text-right font-mono">{{ $row->pak ? number_format($row->pak) : '-' }}</td>
                            <td class="text-right font-mono">{{ $row->pcs ? number_format($row->pcs) : '-' }}</td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Edit Button --}}
                                    <button 
                                        type="button"
                                        wire:click="edit('{{ addslashes($row->nama_produk) }}')"
                                        class="btn btn-square btn-xs btn-ghost text-blue-500 hover:bg-blue-500/10"
                                        title="Edit Data"
                                    >
                                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                                    </button>

                                    {{-- Delete Button --}}
                                    <button 
                                        type="button"
                                        wire:click="confirmDelete('{{ addslashes($row->nama_produk) }}')"
                                        class="btn btn-square btn-xs btn-ghost text-red-500 hover:bg-red-500/10"
                                        title="Hapus Data"
                                    >
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </th>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-base-content/50">
                                <x-heroicon-s-document-text class="w-12 h-12 mx-auto mb-2 opacity-30" />
                                Tidak ada data produk lama ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            <div class="text-base-content/60 text-center sm:text-left">
                Menampilkan <span class="font-bold text-base-content">{{ $produks->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-base-content">{{ $produks->lastItem() ?? 0 }}</span> dari <span class="font-bold text-base-content">{{ $produks->total() }}</span> entri
            </div>
            <div>
                {{ $produks->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL CREATE / EDIT --}}
    @if($showCreateModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
        <div class="bg-base-100 rounded-3xl max-w-4xl w-full max-h-[90vh] shadow-2xl flex flex-col border border-base-200">
            <div class="px-6 py-5 border-b border-base-200 flex items-center justify-between shrink-0 bg-base-100/50 rounded-t-3xl">
                <h3 class="font-extrabold text-lg flex items-center gap-2">
                    @if($showEditModal)
                        <div class="p-2 bg-blue-100 rounded-xl text-blue-600"><x-heroicon-s-pencil-square class="w-5 h-5" /></div>
                        Edit Produk Lama
                    @else
                        <div class="p-2 bg-primary/10 rounded-xl text-primary"><x-heroicon-s-plus class="w-5 h-5" /></div>
                        Tambah Produk Lama
                    @endif
                </h3>
                <button type="button" wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-slate-50">
                <form wire:submit.prevent="{{ $showEditModal ? 'update' : 'store' }}" id="formProdukLama">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        
                        {{-- Nama Produk (Primary Identifier) --}}
                        <div class="form-control lg:col-span-3">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Nama Produk <span class="text-error">*</span></label>
                            <input type="text" wire:model="nama_produk" class="input input-sm input-bordered rounded-xl bg-white w-full font-bold" placeholder="Masukkan Nama Produk (Unik)" required>
                            @error('nama_produk') <span class="text-error text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Brand & Subbrand --}}
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Brand</label>
                            <input type="text" wire:model="brand" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Subbrand</label>
                            <input type="text" wire:model="subbrand" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>

                        {{-- Kategori --}}
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Kategori</label>
                            <input type="text" wire:model="kategory" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>

                        {{-- Top 6 Grouping --}}
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Top 6</label>
                            <input type="text" wire:model="top_6" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Grup Top 6</label>
                            <input type="text" wire:model="grup_top_6" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Top 6 Item</label>
                            <input type="text" wire:model="top_6_item" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>

                        {{-- Other details --}}
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Reg Fest</label>
                            <input type="text" wire:model="reg_fest" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Top Item</label>
                            <input type="text" wire:model="top_item" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="-">
                        </div>

                        {{-- Packaging --}}
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Isi per Pak</label>
                            <input type="number" wire:model="pak" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="0">
                        </div>
                        <div class="form-control">
                            <label class="label text-xs font-bold text-base-content/70 pb-1">Isi per Pcs</label>
                            <input type="number" wire:model="pcs" class="input input-sm input-bordered rounded-xl bg-white w-full" placeholder="0">
                        </div>

                    </div>
                </form>
            </div>

            <div class="px-6 py-4 bg-white border-t border-base-200 flex justify-end gap-3 rounded-b-3xl shrink-0">
                <button type="button" wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-6 font-bold">Batal</button>
                <button type="submit" form="formProdukLama" class="btn btn-primary text-white rounded-xl px-8 font-bold shadow-sm shadow-primary/30">
                    Simpan Data
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL DELETE --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200">
        <div class="bg-base-100 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-base-200 flex flex-col gap-4 text-center">
            <div class="w-16 h-16 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-2">
                <x-heroicon-s-exclamation-triangle class="w-8 h-8" />
            </div>
            
            <h3 class="font-extrabold text-xl text-slate-800">Hapus Data Produk?</h3>
            <p class="text-sm text-slate-500">
                Apakah Anda yakin ingin menghapus produk <span class="font-bold text-slate-700">"{{ $original_nama_produk }}"</span> secara permanen? Data yang sudah dihapus tidak dapat dikembalikan.
            </p>

            <div class="flex justify-center gap-3 mt-4">
                <button type="button" wire:click="closeDeleteModal" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-6 font-bold w-full sm:w-auto">Batal</button>
                <button type="button" wire:click="delete" class="btn btn-error text-white rounded-xl px-6 font-bold shadow-sm shadow-error/30 w-full sm:w-auto">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
