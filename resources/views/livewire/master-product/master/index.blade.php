<div>
    <x-slot name="title">Data Master Products</x-slot>

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
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                     class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                    <div><h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                         <div class="text-sm">{{ session('error') }}</div></div>
                </div>
            @endif
        </div>

        <x-card flush title="Master Product" icon="cube" subtitle="Kelola data produk beserta atribut UOM dan harga zona" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kode atau nama produk..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-72 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Status Filter --}}
                    <select wire:model.live="statusFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>

                    {{-- Export --}}
                    <button wire:click="export" wire:loading.attr="disabled" class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export
                    </button>

                    {{-- Add Button --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah Produk
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            {{-- Tabel --}}
            <x-ui.table empty="Tidak ada data produk ditemukan.">
                <x-slot:head>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Product ID</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Status</th>
                        <th>Group</th>
                        <th>UOM</th>
                        <th>Base Unit</th>
                        <th>CTN->PCS</th>
                        <th>ctn->pak</th>
                        <th>pak/pcs</th>
                        <th class="text-right">Zone 1</th>
                        <th class="text-right">Zone 2</th>
                        <th class="text-right">Zone 3</th>
                        <th class="text-center w-24">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($products as $index => $product)
                    <tr wire:key="product-{{ $product->product_id }}" class="group text-sm">
                        <td><span class="text-xs font-semibold text-base-content/40">{{ $products->firstItem() + $index }}</span></td>
                        <td>
                            <span class="badge badge-sm badge-outline border-base-300 text-primary font-mono px-2 py-3 rounded-lg">{{ $product->product_id }}</span>
                        </td>
                        <td>
                            <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">{{ $product->product_name }}</span>
                        </td>
                        <td class="text-center">
                            @if ($product->is_active)
                                <span class="badge badge-sm badge-success/20 text-success border-success/30 px-3 rounded-full">Aktif</span>
                            @else
                                <span class="badge badge-sm badge-error/20 text-error border-error/30 px-3 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-xs text-base-content/50 space-y-0.5">
                                <div>{{ $product->line_name ?? '-' }}</div>
                                <div>{{ $product->brand_name ?? '-' }}</div>
                                <div>{{ $product->brand_unit_name ?? '-' }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-xs text-base-content/50 space-y-0.5">
                                <div>{{ $product->uom1 ?? '-' }}</div>
                                <div>{{ $product->uom2 ?? '-' }}</div>
                                <div>{{ $product->uom3 ?? '-' }}</div>
                            </div>
                        </td>
                        <td><span class="text-base-content/60">{{ $product->base_unit ?? '-' }}</span></td>
                        <td>{{ $product->conv_unit1 ?? '-' }}</td>
                        <td>{{ $product->conv_unit2 ?? '-' }}</td>
                        <td>{{ $product->conv_unit3 ?? '-' }}</td>
                        <td class="text-right font-mono text-xs">{{ $product->price_zone1 ? number_format($product->price_zone1, 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono text-xs">{{ $product->price_zone2 ? number_format($product->price_zone2, 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono text-xs">{{ $product->price_zone3 ? number_format($product->price_zone3, 0, ',', '.') : '-' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                @unless(auth()->user()->hasRole('guest'))
                                <button wire:click="openEditModal('{{ $product->product_id }}')"
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete('{{ $product->product_id }}')"
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if($products->hasPages())
                <div class="mt-4 px-6">{{ $products->links() }}</div>
            @endif
        </x-card>
    </div>

    {{-- ========== MODAL FORM (Create / Edit) ========== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-10 overflow-y-auto">

        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/70 backdrop-blur-sm" @click="open = false"></div>

        {{-- Modal Panel --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-3xl ring-1 ring-base-content/5 text-base-content my-auto">

            {{-- Header --}}
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
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Master Product' : 'Tambah Master Product' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data produk' : 'Isi detail produk baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Body --}}
            <form wire:submit.prevent="save">
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

                    {{-- === Section: Identitas Produk === --}}
                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-widest text-primary/70 mb-3 flex items-center gap-2">
                            <x-heroicon-s-identification class="w-3.5 h-3.5" /> Identitas Produk
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Product ID --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Produk <span class="text-error">*</span></label>
                                <input wire:model.blur="product_id" type="text" placeholder="Contoh: PRD001"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('product_id') input-error @enderror"
                                       {{ $isEditing ? 'readonly' : '' }}>
                                @error('product_id') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            {{-- Product Name --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Produk <span class="text-error">*</span></label>
                                <input wire:model="product_name" type="text" placeholder="Nama lengkap produk"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('product_name') input-error @enderror">
                                @error('product_name') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            {{-- Line --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Line Produk <span class="text-error">*</span></label>
                                <select wire:model="line_id" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('line_id') select-error @enderror">
                                    <option value="">-- Pilih Line --</option>
                                    @foreach($productLines as $line)
                                        <option value="{{ $line->line_id }}">{{ $line->line_name }}</option>
                                    @endforeach
                                </select>
                                @error('line_id') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            {{-- Brand --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Brand <span class="text-error">*</span></label>
                                <select wire:model="brand_id" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('brand_id') select-error @enderror">
                                    <option value="">-- Pilih Brand --</option>
                                    @foreach($productBrands as $brand)
                                        <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            {{-- Group --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Group Produk <span class="text-error">*</span></label>
                                <select wire:model="product_group_id" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('product_group_id') select-error @enderror">
                                    <option value="">-- Pilih Group --</option>
                                    @foreach($productGroups as $group)
                                        <option value="{{ $group->product_group_id }}">{{ $group->brand_unit_name }}</option>
                                    @endforeach
                                </select>
                                @error('product_group_id') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            {{-- Sub Brand --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Sub Brand</label>
                                <select wire:model="sub_brand_id" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    <option value="">-- Pilih Sub Brand (Opsional) --</option>
                                    @foreach($productSubBrands as $sub)
                                        <option value="{{ $sub->sub_brand_id }}">{{ $sub->sub_brand_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Status --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status <span class="text-error">*</span></label>
                                <select wire:model="is_active" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                            {{-- Base Unit --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Unit Dasar <span class="text-error">*</span></label>
                                <input wire:model="base_unit" type="text" placeholder="Contoh: 90"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('base_unit') input-error @enderror">
                                @error('base_unit') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="divider divider-base-300 my-2 text-[10px] text-base-content/30 uppercase tracking-widest font-bold">Unit of Measure & Konversi</div>

                    {{-- === Section: UOM === --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach([1,2,3] as $i)
                        <div class="bg-base-200/50 rounded-2xl p-4 space-y-3 border border-base-300">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40">UOM {{ $i }}</p>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-base-content/50 ml-1">Nama Unit</label>
                                <input wire:model="uom{{ $i }}" type="text" placeholder="Contoh: PCS"
                                       class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                @error("uom{$i}") <span class="text-error text-[10px] ml-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-base-content/50 ml-1">Konversi</label>
                                <input wire:model="conv_unit{{ $i }}" type="number" step="0.01" min="0" placeholder="0.00"
                                       class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                @error("conv_unit{$i}") <span class="text-error text-[10px] ml-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="divider divider-base-300 my-2 text-[10px] text-base-content/30 uppercase tracking-widest font-bold">Harga Zona</div>

                    {{-- === Section: Harga Zona === --}}
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                        @foreach([1,2,3,4,5] as $z)
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Zone {{ $z }}</label>
                            <input wire:model="price_zone{{ $z }}" type="number" step="1" min="0" placeholder="0"
                                   class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            @error("price_zone{$z}") <span class="text-error text-[10px] ml-1">{{ $message }}</span> @enderror
                        </div>
                        @endforeach
                    </div>

                    <div class="divider divider-base-300 my-2 text-[10px] text-base-content/30 uppercase tracking-widest font-bold">Kategori Produk</div>

                    {{-- === Section: Kategori === --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Kategori (Bisa lebih dari satu)</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto p-3 bg-base-200/50 rounded-2xl border border-base-300">
                            @foreach($allCategories as $cat)
                            <label class="flex items-center gap-2 cursor-pointer group p-1.5 rounded-xl hover:bg-base-300 transition-colors">
                                <input wire:model="selectedCategories" type="checkbox" value="{{ $cat->category_id }}"
                                       class="checkbox checkbox-primary checkbox-sm rounded-md">
                                <span class="text-xs text-base-content/70 group-hover:text-base-content transition-colors">{{ $cat->category_name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
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
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Data master produk ini akan dihapus secara <span class="text-error font-bold italic">permanen</span>.</p>
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
