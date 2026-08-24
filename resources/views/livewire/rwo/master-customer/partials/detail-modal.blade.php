{{-- ========== MODAL DETAIL (View) ========== --}}
    <div x-data="{ open: @entangle('isDetailModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-3xl overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-secondary/10 text-secondary">
                        <x-heroicon-s-eye class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">Detail Reward Outlet (RWO)</h3>
                        <p class="text-xs text-base-content/50">Tinjau informasi lengkap tentang RWO ini</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            @if($selectedOutlet)
            <div class="p-6 overflow-y-auto max-h-[calc(100vh-15rem)] bg-base-100 space-y-6">
                {{-- Data Outlet Utama --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Informasi Toko</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Customer:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->customer_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Kode Customer:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->customer_code }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Kode Eskalink:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->eskalink_code ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Pemilik Toko:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_pemilik_toko }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">No HP:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->no_hp ?? '-' }}</span>
                            </div>
                            <div class="flex flex-col pt-0.5">
                                <span class="text-xs font-semibold text-base-content/60 mb-0.5">Alamat Outlet:</span>
                                <span class="text-xs font-medium text-base-content leading-relaxed">{{ $selectedOutlet->alamat }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Hierarki Wilayah & Lokasi</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Region:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->region_code }} - {{ $selectedOutlet->region_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Area:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->area_code }} - {{ $selectedOutlet->area_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Cabang (Branch):</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->branch_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Latitude:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->latitude ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">Longitude:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->longitude ?? '-' }}</span>
                            </div>
                            @if($selectedOutlet->latitude && $selectedOutlet->longitude)
                            <div class="pt-2 flex justify-end">
                                <a href="https://www.google.com/maps?q={{ (float)$selectedOutlet->latitude }},{{ (float)$selectedOutlet->longitude }}" target="_blank"
                                   class="btn btn-xs btn-outline btn-accent rounded-lg normal-case gap-1.5">
                                    <x-heroicon-s-map-pin class="w-3.5 h-3.5" /> Buka Google Maps
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- KTP & Rekening --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Identitas KTP</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama di KTP:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_ktp ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">NIK KTP:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->nik_ktp ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Informasi Bank</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Bank:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_bank ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nomor Rekening:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->no_rekening ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Pemilik Rekening:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_pemilik_norek ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">Status Rekening:</span>
                                @if($selectedOutlet->validasi_rekening)
                                    <span class="text-xs font-bold text-success flex items-center gap-1"><x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Valid</span>
                                @else
                                    <span class="text-xs font-bold text-base-content/40 flex items-center gap-1"><x-heroicon-s-clock class="w-3.5 h-3.5" /> Belum Validasi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Validasi & Note SPM --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Check SPM -->
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300 flex flex-col justify-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 mb-2">Check SPM</span>
                        @if($selectedOutlet->is_valid)
                            <div class="flex items-start gap-2 text-success bg-success/10 p-2.5 rounded-xl border border-success/20">
                                <x-heroicon-s-check-circle class="w-5 h-5 shrink-0 mt-0.5" />
                                <div class="flex flex-col justify-center h-5">
                                    <span class="text-sm font-bold leading-tight">Sudah</span>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-2 text-error bg-error/10 p-2.5 rounded-xl border border-error/20">
                                <x-heroicon-s-x-circle class="w-5 h-5 shrink-0 mt-0.5" />
                                <div class="flex flex-col justify-center h-5">
                                    <span class="text-sm font-bold leading-tight">Belum</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Kelengkapan Data -->
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300 flex flex-col justify-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 mb-2">Kelengkapan Data</span>
                        @if($selectedOutlet->status === 'Complete')
                            <div class="flex items-center gap-2 text-success bg-success/10 p-2.5 rounded-xl border border-success/20">
                                <x-heroicon-s-check-badge class="w-5 h-5 shrink-0" />
                                <span class="text-sm font-bold">Complete</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-warning bg-warning/10 p-2.5 rounded-xl border border-warning/20">
                                <x-heroicon-s-exclamation-triangle class="w-5 h-5 shrink-0" />
                                <span class="text-sm font-bold">Not Complete</span>
                            </div>
                        @endif
                    </div>

                    <!-- Note SPM -->
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300 flex flex-col justify-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 mb-2">Note SPM</span>
                        <div class="bg-base-100/50 p-2.5 rounded-xl border border-base-200/50 h-full">
                            <p class="text-[11px] font-medium text-base-content/80 leading-relaxed">
                                {{ $selectedOutlet->keterangan ?: 'Tidak ada catatan SPM tambahan.' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Gambar-gambar --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- KTP --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto KTP</span>
                        @if ($selectedOutlet->foto_ktp)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_ktp) }}', title: 'Foto KTP' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_ktp) }}" alt="Foto KTP" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto KTP
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by GPS --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Toko by GPS</span>
                        @if ($selectedOutlet->foto_toko)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_toko) }}', title: 'Foto Toko GPS' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko) }}" alt="Foto Toko GPS" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto GPS
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by team Elite (Tampak Depan) --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Tampak Depan</span>
                        @if ($selectedOutlet->foto_toko2)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_toko2) }}', title: 'Foto Tampak Depan' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko2) }}" alt="Foto Tampak Depan" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto Depan
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by team Elite (Tampak Dalam) --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Tampak Dalam</span>
                        @if ($selectedOutlet->foto_toko3)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_toko3) }}', title: 'Foto Tampak Dalam' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko3) }}" alt="Foto Tampak Dalam" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto Dalam
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Finance Review Status --}}
            @if($selectedOutlet && ($selectedOutlet->isFinalized() || $selectedOutlet->finance_note))
            <div class="px-6 py-4 border-t border-base-200">
                <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="p-1.5 rounded-lg {{ $selectedOutlet->isFinalized() ? 'bg-success/20 text-success' : 'bg-warning/20 text-warning' }}">
                            @if($selectedOutlet->isFinalized())
                                <x-heroicon-s-lock-closed class="w-4 h-4" />
                            @else
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                            @endif
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">
                            {{ $selectedOutlet->isFinalized() ? 'Finalisasi Finance' : 'Catatan Revisi Finance' }}
                        </span>
                    </div>
                    
                    <div class="text-sm font-medium text-base-content bg-base-100 p-3 rounded-xl border border-base-200 italic">
                        "{{ $selectedOutlet->finance_note ?: 'Data telah diverifikasi dan dikunci.' }}"
                    </div>
                    
                    <div class="mt-3 flex items-center justify-between text-[10px] text-base-content/50">
                        <span>Oleh: <b>{{ $selectedOutlet->financeBy->name ?? 'Finance' }}</b></span>
                        <span>Pada: {{ $selectedOutlet->isFinalized() ? $selectedOutlet->finalized_at->format('d M Y, H:i') : ($selectedOutlet->finance_noted_at ? $selectedOutlet->finance_noted_at->format('d M Y, H:i') : '-') }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Finance Review Form --}}
            @canFinalize('rwo.index')
            @if($selectedOutlet && !$selectedOutlet->isFinalized())
            <div class="px-6 py-4 border-t border-base-200 bg-base-200/30">
                <div class="flex items-center gap-2 mb-3">
                    <div class="p-1.5 rounded-lg bg-warning/20 text-warning">
                        <x-heroicon-s-shield-check class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-base-content">Form Review Finance</h4>
                        <p class="text-[10px] text-base-content/60">Berikan catatan revisi atau finalisasi data jika sudah lengkap.</p>
                    </div>
                </div>
                <div class="form-control w-full space-y-2 mb-4">
                    <textarea wire:model="financeNote" class="textarea textarea-bordered h-24 w-full bg-base-100 focus:bg-base-100 rounded-xl transition-all" placeholder="Tuliskan catatan revisi (misal: KTP buram, dsb) atau catatan approval di sini..."></textarea>
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-2">
                    <button wire:click="submitFinanceRevisi" class="btn btn-sm btn-warning normal-case rounded-xl shadow-sm gap-2 w-full sm:w-auto">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                        Catatan Revisi
                    </button>
                    <button wire:click="submitFinanceFinalize" class="btn btn-sm btn-success normal-case text-white rounded-xl shadow-sm shadow-success/30 gap-2 w-full sm:w-auto">
                        <x-heroicon-s-lock-closed class="w-4 h-4" />
                        Finalisasi Kunci
                    </button>
                </div>
            </div>
            @endif
            @endcanFinalize

            <div class="flex items-center justify-between px-6 py-5 border-t border-base-300 bg-base-200/50">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Tutup</button>
                @canEdit('rwo.index')
                @if($selectedOutlet && !$selectedOutlet->isFinalized())
                <button type="button" 
                        @click="open = false; $wire.openEditModal({{ $selectedOutlet->id }})"
                        class="btn btn-sm btn-primary rounded-xl normalcase gap-2 shadow-sm shadow-primary/20">
                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                    Edit Data Ini
                </button>
                @endif
                @endcanEdit
            </div>
        </div>
    </div>