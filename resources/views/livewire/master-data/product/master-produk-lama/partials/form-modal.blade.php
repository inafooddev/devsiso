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
                    @if($form->isEditing)
                        <x-heroicon-s-pencil-square class="w-6 h-6" />
                    @else
                        <x-heroicon-s-plus-circle class="w-6 h-6" />
                    @endif
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-none">{{ $form->isEditing ? 'Edit Produk Lama' : 'Tambah Produk Lama' }}</h3>
                    <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $form->isEditing ? 'Perbarui data produk' : 'Isi detail produk baru' }}</p>
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
                                @if($form->isEditing)
                                    <input wire:model="form.pcode_prc" type="text" placeholder="Kode Produk"
                                            class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.pcode_prc') input-error @enderror"
                                            readonly>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                        <x-heroicon-s-lock-closed class="w-4 h-4" />
                                    </div>
                                @else
                                    <select wire:model.live="form.pcode_prc" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.pcode_prc') select-error @enderror">
                                        <option value="">-- Pilih Produk Master --</option>
                                        {{-- We use dynamic query here or rely on the frontend fetching. Since we refactored, let's just make it a text input or fetch all available in the form --}}
                                        {{-- For simplicity in livewire 3, since we can just use an input and validate on blur, but let's stick to the previous dropdown. We need to pass $availableProducts or just fetch them if needed. --}}
                                        {{-- Wait, $availableProducts is not passed from the component anymore because it was not in the refactored code. Let's make it a text input that auto fetches or re-add the availableProducts to the form component --}}
                                        <input wire:model.live.debounce.300ms="form.pcode_prc" type="text" placeholder="Masukkan Kode Produk"
                                            class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.pcode_prc') input-error @enderror">
                                    @endif
                            </div>
                            @error('form.pcode_prc') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                        
                        {{-- Product Name --}}
                        <div class="space-y-1.5 lg:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Produk <span class="text-error">*</span></label>
                            <input wire:model="form.nama_produk" type="text" placeholder="Nama lengkap produk"
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.nama_produk') input-error @enderror">
                            @error('form.nama_produk') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>

                        {{-- Line --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Line Produk</label>
                            <input wire:model="form.produk_line" type="text" placeholder="Line Produk"
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.produk_line') input-error @enderror">
                        </div>
                        
                        {{-- Brand --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Brand</label>
                            <input wire:model="form.brand" type="text" placeholder="Brand"
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.brand') input-error @enderror">
                        </div>

                        {{-- Sub Brand --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Sub Brand</label>
                            <input wire:model="form.subbrand" type="text" placeholder="Sub Brand"
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.subbrand') input-error @enderror">
                        </div>

                        {{-- Divisi --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Divisi</label>
                            <input wire:model="form.divisi" type="text" placeholder="Divisi"
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.divisi') input-error @enderror">
                        </div>

                        {{-- Kategori --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kategori</label>
                            <select wire:model="form.kategory" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.kategory') select-error @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="OTHER">OTHER</option>
                                <option value="NPD">NPD</option>
                                <option value="TOPITEM">TOPITEM</option>
                            </select>
                        </div>

                        {{-- Promo Group --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Promo Group</label>
                            <input wire:model="form.promo_group" type="text" placeholder="Promo Group"
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.promo_group') input-error @enderror">
                        </div>

                        {{-- Top Item --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Top Item</label>
                            <input wire:model="form.topitem" type="text" placeholder="Top Item"
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.topitem') input-error @enderror">
                        </div>

                        {{-- Status --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status</label>
                            <select wire:model="form.status_product" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
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
                        <input wire:model="form.uom1" type="text" placeholder="Contoh: CRT"
                                class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-base-content/50 ml-1">UOM 2</label>
                        <input wire:model="form.uom2" type="text" placeholder="Contoh: PAK"
                                class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-base-content/50 ml-1">UOM 3</label>
                        <input wire:model="form.uom3" type="text" placeholder="Contoh: PCS"
                                class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-base-content/50 ml-1">Konversi (CRT to PCS)</label>
                        <input wire:model="form.crttopcs" type="number" step="0.01" min="0"
                                class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-base-content/50 ml-1">Konversi (CRT to PAK)</label>
                        <input wire:model="form.crttopack" type="number" step="0.01" min="0"
                                class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-base-content/50 ml-1">Konversi (PAK to PCS)</label>
                        <input wire:model="form.packtopcs" type="number" step="0.01" min="0"
                                class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                </div>

                <div class="divider divider-base-300 my-2 text-[10px] text-base-content/30 uppercase tracking-widest font-bold">Harga</div>

                {{-- === Section: Harga === --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Price HRT</label>
                        <input wire:model="form.pricehrt" type="number" step="1" min="0" placeholder="0"
                                class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl shrink-0">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                    <span wire:loading.remove wire:target="save">{{ $form->isEditing ? 'Simpan Perubahan' : 'Simpan Produk' }}</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                    <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                </button>
            </div>
        </form>
    </div>
</div>
