<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
    x-data="{ 
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false
    }"
    x-on:open-create-modal.window="showCreateModal = true"
    x-on:close-create-modal.window="showCreateModal = false"
    x-on:open-edit-modal.window="showEditModal = true"
    x-on:close-edit-modal.window="showEditModal = false"
    x-on:open-delete-modal.window="showDeleteModal = true"
    x-on:close-delete-modal.window="showDeleteModal = false"
>
    <x-slot name="title">Master Customer Audit</x-slot>

    {{-- Tabs Navigation --}}
    <x-ui.tab-menu>
        <x-ui.tab-item href="{{ route('others.audit-toko') }}" :active="false" class="gap-1.5">
            <x-heroicon-s-clipboard-document-check class="w-3.5 h-3.5" /> Approval Audit
        </x-ui.tab-item>
        <x-ui.tab-item href="{{ route('others.audit-toko.master') }}" :active="true" class="gap-1.5 text-primary">
            <x-heroicon-s-building-storefront class="w-3.5 h-3.5" /> Master Customer
        </x-ui.tab-item>
    </x-ui.tab-menu>

    {{-- Main Card (Tabel Utama) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden mt-2">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Master Customer</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola daftar master toko untuk kebutuhan audit</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative grow sm:grow-0">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari toko atau kode..." 
                        class="input input-sm input-bordered rounded-xl w-full sm:w-64 bg-base-100 pl-9"
                    />
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/40" />
                </div>
                
                {{-- Export Button --}}
                <div class="flex items-center gap-1 md:gap-2">
                    <button wire:click="exportExcel" class="btn btn-sm btn-success text-white rounded-xl gap-2 font-bold shadow-sm">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export
                    </button>
                    @canAdd('others.audit-toko')
                    <button wire:click="create" class="btn btn-sm btn-primary text-white rounded-xl gap-2 font-bold shadow-sm">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah Data
                    </button>
                    @endcanAdd
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Kode Distributor</th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th>Alamat</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($customers as $index => $row)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th class="text-center">{{ $customers->firstItem() + $index }}</th>
                            <td><span class="badge badge-sm badge-ghost font-mono font-bold">{{ $row->distributor_code }}</span></td>
                            <td><span class="badge badge-sm badge-ghost font-mono font-bold">{{ $row->customer_code }}</span></td>
                            <td class="font-bold text-base-content">{{ $row->customer_name }}</td>
                            <td class="max-w-[200px] truncate" title="{{ $row->customer_address }}">{{ $row->customer_address ?? '-' }}</td>
                            <td class="font-mono text-xs">{{ $row->latitude ?? '-' }}</td>
                            <td class="font-mono text-xs">{{ $row->longitude ?? '-' }}</td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    @canEdit('others.audit-toko')
                                        <button 
                                            type="button"
                                            wire:click="edit({{ $row->id }})"
                                            class="btn btn-square btn-xs btn-ghost text-blue-500 hover:bg-blue-500/10"
                                            title="Edit Data"
                                        >
                                            <x-heroicon-s-pencil-square class="w-4 h-4" />
                                        </button>
                                    @endcanEdit

                                    @canDelete('others.audit-toko')
                                        <button 
                                            type="button"
                                            wire:click="deleteConfirm({{ $row->id }})"
                                            class="btn btn-square btn-xs btn-ghost text-red-500 hover:bg-red-500/10"
                                            title="Hapus Data"
                                        >
                                            <x-heroicon-s-trash class="w-4 h-4" />
                                        </button>
                                    @endcanDelete
                                </div>
                            </th>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-base-content/50">
                                <x-heroicon-s-users class="w-12 h-12 mx-auto mb-2 opacity-30" />
                                Tidak ada data master customer audit ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            <div class="text-base-content/60 text-center sm:text-left">
                Menampilkan <span class="font-bold text-base-content">{{ $customers->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-base-content">{{ $customers->lastItem() ?? 0 }}</span> dari <span class="font-bold text-base-content">{{ $customers->total() }}</span> entri
            </div>
            <div>
                {{ $customers->links() }}
            </div>
        </div>

    </div>

    {{-- MODAL CREATE / EDIT --}}
    <div 
        x-show="showCreateModal || showEditModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
    >
        <div class="bg-base-100 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-base-200 flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-base-200 pb-4">
                <h3 class="font-extrabold text-lg flex items-center gap-2">
                    <div class="p-1.5 bg-primary/10 rounded-lg text-primary">
                        <x-heroicon-s-building-storefront class="w-6 h-6" x-show="showCreateModal" />
                        <x-heroicon-s-pencil-square class="w-6 h-6" x-show="showEditModal" />
                    </div>
                    <span x-text="showEditModal ? 'Edit Data Customer' : 'Tambah Data Customer'"></span>
                </h3>
                <button type="button" @click="showCreateModal = false; showEditModal = false;" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label text-xs font-bold text-base-content/70">Kode Distributor <span class="text-error">*</span></label>
                        <input type="text" wire:model="distributor_code" class="input input-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white" placeholder="Contoh: D-12345">
                        @error('distributor_code') <span class="text-error text-[11px] mt-1.5 font-bold flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" /> {{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label text-xs font-bold text-base-content/70">Kode Toko (Customer) <span class="text-error">*</span></label>
                        <input type="text" wire:model="customer_code" class="input input-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white" placeholder="Contoh: C-998877">
                        @error('customer_code') <span class="text-error text-[11px] mt-1.5 font-bold flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" /> {{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-control">
                    <label class="label text-xs font-bold text-base-content/70">Nama Toko <span class="text-error">*</span></label>
                    <input type="text" wire:model="customer_name" class="input input-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white" placeholder="Contoh: TOKO MAJU JAYA">
                    @error('customer_name') <span class="text-error text-[11px] mt-1.5 font-bold flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" /> {{ $message }}</span> @enderror
                </div>

                <div class="form-control">
                    <label class="label text-xs font-bold text-base-content/70">Alamat Toko</label>
                    <textarea wire:model="customer_address" rows="2" class="textarea textarea-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white" placeholder="Alamat lengkap..."></textarea>
                    @error('customer_address') <span class="text-error text-[11px] mt-1.5 font-bold flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" /> {{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label text-xs font-bold text-base-content/70">Latitude (Garis Lintang)</label>
                        <input type="text" wire:model="latitude" class="input input-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white font-mono" placeholder="Contoh: -6.123456">
                        @error('latitude') <span class="text-error text-[11px] mt-1.5 font-bold flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" /> {{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label text-xs font-bold text-base-content/70">Longitude (Garis Bujur)</label>
                        <input type="text" wire:model="longitude" class="input input-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white font-mono" placeholder="Contoh: 106.123456">
                        @error('longitude') <span class="text-error text-[11px] mt-1.5 font-bold flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" /> {{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-base-200">
                    <button type="button" @click="showCreateModal = false; showEditModal = false;" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-6 font-bold">Batal</button>
                    <button type="submit" class="btn btn-primary text-white rounded-xl px-6 font-bold shadow-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DELETE --}}
    <div 
        x-show="showDeleteModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
    >
        <div class="bg-base-100 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-base-200 flex flex-col gap-4 text-center">
            <div class="w-16 h-16 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-2">
                <x-heroicon-s-trash class="w-8 h-8" />
            </div>
            <h3 class="font-extrabold text-xl text-slate-800">Hapus Data?</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus data customer ini? Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="flex justify-center gap-3 mt-4">
                <button type="button" @click="showDeleteModal = false" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-6 font-bold">Batal</button>
                <button type="button" wire:click="destroy" class="btn btn-error text-white rounded-xl px-6 font-bold shadow-sm shadow-error/30">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>
