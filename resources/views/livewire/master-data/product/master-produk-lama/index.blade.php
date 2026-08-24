<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
    <x-slot name="title">Data Master Produk Lama</x-slot>

    {{-- Notifikasi --}}
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

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Master Produk Lama</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data master produk lama</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari produk..." />

                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>

                {{-- Kategori Filter --}}
                <select wire:model.live="kategoriFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Kategori</option>
                    @foreach($kategories as $kat)
                        <option value="{{ $kat }}">{{ $kat }}</option>
                    @endforeach
                </select>

                {{-- Top Item Filter --}}
                <select wire:model.live="topItemFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Top Item</option>
                    @foreach($topItems as $item)
                        <option value="{{ $item }}">{{ $item }}</option>
                    @endforeach
                </select>

                {{-- Subbrand Filter --}}
                <select wire:model.live="subbrandFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Subbrand</option>
                    @foreach($subbrands as $subbrand)
                        <option value="{{ $subbrand }}">{{ $subbrand }}</option>
                    @endforeach
                </select>

                {{-- Divisi Filter --}}
                <select wire:model.live="divisiFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Divisi</option>
                    @foreach($divisis as $divisi)
                        <option value="{{ $divisi }}">{{ $divisi }}</option>
                    @endforeach
                </select>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    @if($search || $statusFilter !== '' || $kategoriFilter !== '' || $topItemFilter !== '' || $subbrandFilter !== '' || $divisiFilter !== '')
                        <button wire:click="resetFilters" class="btn btn-sm btn-ghost text-error/80 hover:text-error hover:bg-error/10 transition-all duration-300 mr-2" title="Reset semua filter">
                            <x-heroicon-s-arrow-path class="w-4 h-4 mr-1" />
                            <span class="text-xs font-semibold hidden sm:inline">Reset</span>
                        </button>
                    @endif
                    <x-ui.action-button type="add" wire:click="openCreateModal" />
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12">No</th>
                        <th>Kode & Nama</th>
                        <th class="text-center">Status</th>
                        <th>Kategori / Brand</th>
                        <th>UOM (1/2/3)</th>
                        <th>Konversi (CRT/PAK/PCS)</th>
                        <th class="text-right">Harga (Price HRT)</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($products as $index => $product)
                        <tr wire:key="product-{{ $product->pcode_prc }}" class="hover:bg-base-200/50 transition-colors group">
                            <th>{{ $products->firstItem() + $index }}</th>
                            
                            {{-- Produk --}}
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-[11px] text-base-content/90">{{ $product->nama_produk }}</span>
                                    <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $product->pcode_prc }}</span>
                                </div>
                            </td>
                            
                            {{-- Status --}}
                            <td class="text-center">
                                @if ($product->status_product == '1')
                                    <span class="badge badge-sm badge-success/20 text-success border-success/30 px-3 rounded-full font-semibold">Aktif</span>
                                @else
                                    <span class="badge badge-sm badge-error/20 text-error border-error/30 px-3 rounded-full font-semibold">Nonaktif</span>
                                @endif
                            </td>

                            {{-- Kategori & Brand --}}
                            <td>
                                <div class="flex flex-col gap-0.5 text-[10px] text-base-content/60 font-medium">
                                    <span>{{ $product->kategory ?? '-' }}</span>
                                    <span>{{ $product->brand ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- UOM --}}
                            <td>
                                <div class="flex flex-col gap-0.5 text-[10px] text-base-content/60 font-medium font-mono">
                                    <span>{{ $product->uom1 ?? '-' }} / {{ $product->uom2 ?? '-' }} / {{ $product->uom3 ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Konversi --}}
                            <td>
                                <div class="flex flex-col gap-0.5 text-[10px] text-base-content/60 font-medium font-mono">
                                    <span>CRT->PCS: {{ $product->crttopcs ?? '-' }}</span>
                                    <span>CRT->PAK: {{ $product->crttopack ?? '-' }}</span>
                                    <span>PAK->PCS: {{ $product->packtopcs ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Harga --}}
                            <td class="text-right font-mono text-[11px] text-base-content/80">{{ $product->pricehrt ? number_format($product->pricehrt, 0, ',', '.') : '-' }}</td>

                            {{-- Aksi --}}
                            <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1 transition-opacity duration-200">
                                    <x-ui.action-button type="view" wire:click="openDetailModal('{{ $product->pcode_prc }}')" class="btn-square" title="Detail" />
                                    <x-ui.action-button type="edit" wire:click="openEditModal('{{ $product->pcode_prc }}')" class="btn-square" title="Edit" />
                                    <x-ui.action-button type="delete" wire:click="confirmDelete('{{ $product->pcode_prc }}')" class="btn-square" title="Hapus" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="flex flex-col items-center justify-center py-12 text-base-content/40">
                                    <x-heroicon-o-inbox class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="text-sm font-medium">Tidak ada data ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- ========== MODAL FORM (Create / Edit) ========== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-start justify-center p-4 pt-10 overflow-y-auto">

        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/70 backdrop-blur-sm" @click="open = false"></div>

        {{-- Modal Panel --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl ring-1 ring-base-content/5 text-base-content my-auto">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Produk Lama' : 'Tambah Produk Lama' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data produk' : 'Isi detail produk baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Body --}}
            <form wire:submit.prevent="save">
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto bg-base-100 custom-scrollbar">

                    {{-- === Section: Identitas Produk === --}}
                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-widest text-primary/70 mb-3 flex items-center gap-2">
                            <x-heroicon-s-identification class="w-3.5 h-3.5" /> Identitas Produk
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            {{-- Product ID --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Produk <span class="text-error">*</span></label>
                                <div class="relative group">
                                    @if($isEditing)
                                        <input wire:model.blur="pcode_prc" type="text" placeholder="Kode Produk"
                                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('pcode_prc') input-error @enderror"
                                               readonly>
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                            <x-heroicon-s-lock-closed class="w-4 h-4" />
                                        </div>
                                    @else
                                        <select wire:model.live="pcode_prc" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('pcode_prc') select-error @enderror">
                                            <option value="">-- Pilih Produk Master --</option>
                                            @if(isset($availableProducts) && $availableProducts)
                                                @foreach($availableProducts as $ap)
                                                    <option value="{{ $ap->product_id }}">{{ $ap->product_id }} - {{ $ap->product_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    @endif
                                </div>
                                @error('pcode_prc') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            
                            {{-- Product Name --}}
                            <div class="space-y-1.5 lg:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Produk <span class="text-error">*</span></label>
                                <input wire:model="nama_produk" type="text" placeholder="Nama lengkap produk"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('nama_produk') input-error @enderror">
                                @error('nama_produk') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>

                            {{-- Line --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Line Produk</label>
                                <input wire:model="produk_line" type="text" placeholder="Line Produk"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('produk_line') input-error @enderror">
                            </div>
                            
                            {{-- Brand --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Brand</label>
                                <input wire:model="brand" type="text" placeholder="Brand"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('brand') input-error @enderror">
                            </div>

                            {{-- Sub Brand --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Sub Brand</label>
                                <input wire:model="subbrand" type="text" placeholder="Sub Brand"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('subbrand') input-error @enderror">
                            </div>

                            {{-- Divisi --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Divisi</label>
                                <input wire:model="divisi" type="text" placeholder="Divisi"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('divisi') input-error @enderror">
                            </div>

                            {{-- Kategori --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kategori</label>
                                <select wire:model="kategory" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('kategory') select-error @enderror">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="OTHER">OTHER</option>
                                    <option value="NPD">NPD</option>
                                    <option value="TOPITEM">TOPITEM</option>
                                </select>
                            </div>

                            {{-- Promo Group --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Promo Group</label>
                                <input wire:model="promo_group" type="text" placeholder="Promo Group"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('promo_group') input-error @enderror">
                            </div>

                            {{-- Top Item --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Top Item</label>
                                <input wire:model="topitem" type="text" placeholder="Top Item"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('topitem') input-error @enderror">
                            </div>

                            {{-- Status --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status</label>
                                <select wire:model="status_product" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="divider divider-base-300 my-2 text-[10px] text-base-content/30 uppercase tracking-widest font-bold">Unit of Measure & Konversi</div>

                    {{-- === Section: UOM & Konversi === --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-base-content/50 ml-1">UOM 1</label>
                            <input wire:model="uom1" type="text" placeholder="Contoh: CRT"
                                   class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-base-content/50 ml-1">UOM 2</label>
                            <input wire:model="uom2" type="text" placeholder="Contoh: PAK"
                                   class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-base-content/50 ml-1">UOM 3</label>
                            <input wire:model="uom3" type="text" placeholder="Contoh: PCS"
                                   class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-base-content/50 ml-1">Konversi (CRT to PCS)</label>
                            <input wire:model="crttopcs" type="number" step="0.01" min="0"
                                   class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-base-content/50 ml-1">Konversi (CRT to PAK)</label>
                            <input wire:model="crttopack" type="number" step="0.01" min="0"
                                   class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-base-content/50 ml-1">Konversi (PAK to PCS)</label>
                            <input wire:model="packtopcs" type="number" step="0.01" min="0"
                                   class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        </div>
                    </div>

                    <div class="divider divider-base-300 my-2 text-[10px] text-base-content/30 uppercase tracking-widest font-bold">Harga</div>

                    {{-- === Section: Harga === --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Price HRT</label>
                            <input wire:model="pricehrt" type="number" step="1" min="0" placeholder="0"
                                   class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl shrink-0">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Produk' }}</span>
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
                <h3 class="text-xl font-bold mb-2 leading-none">Hapus Produk?</h3>
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Data produk lama ini akan dihapus secara <span class="text-error font-bold italic">permanen</span>.</p>
            </div>
            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case transition-all duration-200">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 text-white transition-all duration-200">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL DETAIL PRODUK ========== --}}
    <div x-data="{ open: @entangle('isDetailModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-start justify-center p-4 pt-10 overflow-y-auto">

        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/70 backdrop-blur-sm" @click="open = false"></div>

        {{-- Modal Panel --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl ring-1 ring-base-content/5 text-base-content my-auto">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                        <x-heroicon-s-information-circle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Detail Produk Lama</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Informasi lengkap data produk</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto bg-base-100 custom-scrollbar">
                @if($productDetail)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        {{-- Identitas --}}
                        <div class="space-y-4 lg:col-span-3 bg-base-200/30 p-4 rounded-2xl border border-base-300">
                            <h4 class="text-[11px] font-bold uppercase tracking-widest text-base-content/40 border-b border-base-300 pb-2">Identitas Produk</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Kode Produk</p>
                                    <p class="font-mono text-sm font-semibold">{{ $productDetail->pcode_prc }}</p>
                                </div>
                                <div class="lg:col-span-2">
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Nama Produk</p>
                                    <p class="text-sm font-bold">{{ $productDetail->nama_produk }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Status</p>
                                    <div>
                                        @if ($productDetail->status_product == '1')
                                            <span class="badge badge-sm badge-success/20 text-success border-success/30 font-semibold mt-1">Aktif</span>
                                        @else
                                            <span class="badge badge-sm badge-error/20 text-error border-error/30 font-semibold mt-1">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Klasifikasi --}}
                        <div class="space-y-4 lg:col-span-3 bg-base-200/30 p-4 rounded-2xl border border-base-300">
                            <h4 class="text-[11px] font-bold uppercase tracking-widest text-base-content/40 border-b border-base-300 pb-2">Klasifikasi</h4>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Kategori</p>
                                    <p class="text-sm font-medium">{{ $productDetail->kategory ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Brand</p>
                                    <p class="text-sm font-medium">{{ $productDetail->brand ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Sub Brand</p>
                                    <p class="text-sm font-medium">{{ $productDetail->subbrand ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Line Produk</p>
                                    <p class="text-sm font-medium">{{ $productDetail->produk_line ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Divisi</p>
                                    <p class="text-sm font-medium">{{ $productDetail->divisi ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Promo Group</p>
                                    <p class="text-sm font-medium">{{ $productDetail->promo_group ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50">Top Item</p>
                                    <p class="text-sm font-medium">{{ $productDetail->topitem ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Satuan & Harga --}}
                        <div class="space-y-4 lg:col-span-3 bg-base-200/30 p-4 rounded-2xl border border-base-300">
                            <h4 class="text-[11px] font-bold uppercase tracking-widest text-base-content/40 border-b border-base-300 pb-2">Satuan, Konversi, & Harga</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50 mb-2">Satuan (UOM)</p>
                                    <ul class="space-y-1 text-sm font-mono">
                                        <li>UOM 1: <span class="font-bold">{{ $productDetail->uom1 ?? '-' }}</span></li>
                                        <li>UOM 2: <span class="font-bold">{{ $productDetail->uom2 ?? '-' }}</span></li>
                                        <li>UOM 3: <span class="font-bold">{{ $productDetail->uom3 ?? '-' }}</span></li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50 mb-2">Konversi</p>
                                    <ul class="space-y-1 text-sm font-mono">
                                        <li>CRT -> PCS: <span class="font-bold">{{ $productDetail->crttopcs ?? '-' }}</span></li>
                                        <li>CRT -> PAK: <span class="font-bold">{{ $productDetail->crttopack ?? '-' }}</span></li>
                                        <li>PAK -> PCS: <span class="font-bold">{{ $productDetail->packtopcs ?? '-' }}</span></li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-base-content/50 mb-2">Harga</p>
                                    <div class="bg-base-100 p-3 rounded-xl border border-base-300">
                                        <p class="text-[10px] text-base-content/60">Price HRT</p>
                                        <p class="text-lg font-mono font-bold text-primary">
                                            Rp {{ $productDetail->pricehrt ? number_format($productDetail->pricehrt, 0, ',', '.') : '0' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl shrink-0">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Tutup</button>
            </div>
        </div>
    </div>

</div>
