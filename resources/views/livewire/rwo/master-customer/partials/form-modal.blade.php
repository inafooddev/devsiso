{{-- ========== MODAL FORM (Create/Edit) ========== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if(($form->outletId ?? false))
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ ($form->outletId ?? false) ? 'Edit Reward Outlet (RWO)' : 'Tambah RWO Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ ($form->outletId ?? false) ? 'Perbarui data outlet program RWO' : 'Daftarkan outlet program RWO baru' }}</p>
                    </div>
                </div>
                <button @click="$wire.closeFormModal()" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 overflow-y-auto max-h-[calc(100vh-15rem)] bg-base-100">
                    @if(($form->outletId ?? false))
                        @php $existingOutlet = \App\Models\RewardOutlet::find($form->outletId); @endphp
                        @if($existingOutlet && $existingOutlet->finance_note)
                            <div class="alert alert-warning mb-6 shadow-sm rounded-2xl text-warning-content bg-warning/10 border border-warning/20">
                                <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0 text-warning" />
                                <div>
                                    <h3 class="font-bold text-sm text-warning">Catatan Revisi dari Finance:</h3>
                                    <p class="text-xs mt-1 text-warning/90 italic">"{{ $existingOutlet->finance_note }}"</p>
                                    @if($existingOutlet->finance_noted_at)
                                        <p class="text-[10px] mt-1 text-warning/70">Diberikan pada: {{ $existingOutlet->finance_noted_at->format('d M Y, H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-error mb-6 shadow-sm rounded-2xl text-error-content bg-error/10 border border-error/20">
                            <x-heroicon-o-x-circle class="w-6 h-6 shrink-0 text-error" />
                            <div>
                                <h3 class="font-bold text-sm text-error">Validasi Gagal! Terdapat {{ $errors->count() }} kesalahan:</h3>
                                <ul class="text-xs mt-1 list-disc list-inside text-error/80">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        
                        {{-- HIERARKI WILAYAH --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Hierarki & Kode</h4>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region <span class="text-error">*</span></label>
                            <select wire:model.live="form.region_code"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.region_code') select-error @enderror">
                                <option value="">Pilih Region</option>
                                @foreach($this->getRegions() as $reg)
                                    <option value="{{ $reg->region_code }}">{{ $reg->region_code }} - {{ $reg->region_name }}</option>
                                @endforeach
                            </select>
                            @error('form.region_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area <span class="text-error">*</span></label>
                            <select wire:model.live="form.area_code"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.area_code') select-error @enderror"
                                    {{ empty($form->region_code) ? 'disabled' : '' }}>
                                <option value="">Pilih Area</option>
                                @foreach($this->getAreas() as $ar)
                                    <option value="{{ $ar->area_code }}">{{ $ar->area_code }} - {{ $ar->area_name }}</option>
                                @endforeach
                            </select>
                            @error('form.area_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang (Branch) <span class="text-error">*</span></label>
                            <select wire:model.live="form.branch_name"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.branch_name') select-error @enderror">
                                <option value="">Pilih Cabang</option>
                                @foreach($this->getBranches() as $br)
                                    <option value="{{ $br->branch_name }}">{{ $br->branch_name }}</option>
                                @endforeach
                            </select>
                            @error('form.branch_name') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Customer Code <span class="text-error">*</span></label>
                            <input wire:model="form.customer_code" type="text" placeholder="Contoh: CUST-01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.customer_code') input-error @enderror">
                            @error('form.customer_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Eskalink Code</label>
                            <input wire:model="form.eskalink_code" type="text" placeholder="Contoh: ESKA-01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.eskalink_code') input-error @enderror">
                            @error('form.eskalink_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- OUTLET DATA --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Informasi Toko / Outlet</h4>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Customer / Toko <span class="text-error">*</span></label>
                            <input wire:model="form.customer_name" type="text" placeholder="Nama Toko"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.customer_name') input-error @enderror">
                            @error('form.customer_name') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Pemilik Toko</label>
                            <input wire:model="form.nama_pemilik_toko" type="text" placeholder="Nama Pemilik Toko"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.nama_pemilik_toko') input-error @enderror">
                            @error('form.nama_pemilik_toko') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-3 space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Alamat Lengkap <span class="text-error">*</span></label>
                            <textarea wire:model="form.alamat" placeholder="Tulis alamat toko secara detail..."
                                      class="textarea textarea-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.alamat') textarea-error @enderror" rows="3"></textarea>
                            @error('form.alamat') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">No HP</label>
                            <input wire:model="form.no_hp" type="text" placeholder="Contoh: 08123456789"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.no_hp') input-error @enderror">
                            @error('form.no_hp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Latitude</label>
                            <input wire:model="form.latitude" type="number" step="any" min="-90" max="90" placeholder="Contoh: -6.12345"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.latitude') input-error @enderror">
                            @error('form.latitude') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Longitude</label>
                            <input wire:model="form.longitude" type="number" step="any" min="-180" max="180" placeholder="Contoh: 106.12345"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.longitude') input-error @enderror">
                            @error('form.longitude') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- KTP & IDENTITY --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Identitas & KTP</h4>
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Sesuai KTP / NPWP</label>
                            <input wire:model="form.nama_ktp" type="text" placeholder="Nama Sesuai KTP / NPWP"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.nama_ktp') input-error @enderror">
                            @error('form.nama_ktp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">NIK / NPWP</label>
                            <input wire:model="form.nik_ktp" type="text" minlength="15" maxlength="25" placeholder="Contoh: 1234567890123456 atau 12.345.678.9-012.000"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.nik_ktp') input-error @enderror">
                            @error('form.nik_ktp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
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
                                        :class="!selectedBank ? 'border-error' : 'border-base-300'"
                                        class="input input-bordered w-full text-left flex justify-between items-center bg-base-200 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 pr-2">
                                    <span x-text="selectedBank || 'Pilih Bank / Cari...'" class="truncate" :class="!selectedBank ? 'text-base-content/50' : ''"></span>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <div x-show="selectedBank" 
                                             @click.stop="selectedBank = ''; open = false" 
                                             class="p-1 hover:bg-error/10 rounded-lg transition-colors cursor-pointer text-base-content/40 hover:text-error"
                                             title="Kosongkan pilihan">
                                            <x-heroicon-s-x-mark class="w-4 h-4" />
                                        </div>
                                        <div class="p-1">
                                            <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/40" />
                                        </div>
                                    </div>
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
                            @error('form.nama_bank') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">No Rekening</label>
                            <input wire:model="form.no_rekening" type="text" placeholder="Nomor Rekening"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.no_rekening') input-error @enderror">
                            @error('form.no_rekening') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Pemilik Rekening</label>
                            <input wire:model="form.nama_pemilik_norek" type="text" placeholder="Nama Pemilik Rekening"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('form.nama_pemilik_norek') input-error @enderror">
                            @error('form.nama_pemilik_norek') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- VALIDASI & NOTE SPM TOKO --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Validasi & Note SPM</h4>
                        </div>

                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            {{-- Validasi Checkbox --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status Check SPM</label>
                                <div class="form-control bg-base-200 border border-base-300 rounded-2xl p-3 flex flex-row items-center justify-between gap-3 hover:bg-base-200/80 transition-all duration-200 cursor-pointer">
                                    <div class="flex flex-col select-none" @click="$refs.isValidCheckbox.click()">
                                        <span class="text-xs font-bold text-base-content/80">Sudah Check SPM</span>
                                        <span class="text-[10px] text-base-content/40">Centang jika sudah check SPM</span>
                                    </div>
                                    <input x-ref="isValidCheckbox" type="checkbox" wire:model="form.is_valid" class="checkbox checkbox-primary rounded-lg">
                                </div>
                                @error('form.is_valid') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Validasi Rekening Checkbox --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Validasi Rekening</label>
                                <div class="form-control bg-base-200 border border-base-300 rounded-2xl p-3 flex flex-row items-center justify-between gap-3 hover:bg-base-200/80 transition-all duration-200 cursor-pointer">
                                    <div class="flex flex-col select-none" @click="$refs.isValidRekeningCheckbox.click()">
                                        <span class="text-xs font-bold text-base-content/80">Rekening Valid</span>
                                        <span class="text-[10px] text-base-content/40">Centang jika rekening diverifikasi</span>
                                    </div>
                                    <input x-ref="isValidRekeningCheckbox" type="checkbox" wire:model="form.validasi_rekening" class="checkbox checkbox-primary rounded-lg">
                                </div>
                                @error('form.validasi_rekening') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Note SPM Text --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Note SPM</label>
                                <input wire:model="form.keterangan" type="text" placeholder="Masukkan catatan SPM..."
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('form.keterangan') input-error @enderror">
                                @error('form.keterangan') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- FOTO TOKO & KTP --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Media / Foto</h4>
                        </div>

                        <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            {{-- Foto KTP --}}
                            <x-ui.upload-image 
                                wireModel="form.foto_ktp" 
                                label="Upload Foto KTP" 
                                :previewUrl="$this->getFotoKtpPreview()" 
                                :existingUrl="$form->existing_foto_ktp ? asset('storage/' . $form->existing_foto_ktp) : null" 
                                minHeight="110px"
                            />

                            {{-- Foto Toko by GPS --}}
                            <x-ui.upload-image 
                                wireModel="form.foto_toko" 
                                label="Foto Toko by GPS" 
                                :previewUrl="$this->getFotoTokoPreview()" 
                                :existingUrl="$form->existing_foto_toko ? asset('storage/' . $form->existing_foto_toko) : null" 
                                minHeight="110px"
                            />

                            {{-- Foto Toko Tampak Depan --}}
                            <x-ui.upload-image 
                                wireModel="form.foto_toko2" 
                                label="Foto Tampak Depan" 
                                :previewUrl="$this->getFotoToko2Preview()" 
                                :existingUrl="$form->existing_foto_toko2 ? asset('storage/' . $form->existing_foto_toko2) : null" 
                                minHeight="110px"
                            />

                            {{-- Foto Toko Tampak Dalam --}}
                            <x-ui.upload-image 
                                wireModel="form.foto_toko3" 
                                label="Foto Tampak Dalam" 
                                :previewUrl="$this->getFotoToko3Preview()" 
                                :existingUrl="$form->existing_foto_toko3 ? asset('storage/' . $form->existing_foto_toko3) : null" 
                                minHeight="110px"
                            />
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ ($form->outletId ?? false) ? 'Simpan Perubahan' : 'Tambahkan RWO' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>