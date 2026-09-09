<div class="contents">
    {{-- Trigger Button --}}
    <button type="button" onclick="document.getElementById('modal-create-jks').showModal()" class="btn btn-sm btn-primary text-white gap-1 shadow-sm">
        <x-heroicon-s-plus class="w-4 h-4" />
        Tambah
    </button>

    {{-- The Modal --}}
    <dialog id="modal-create-jks" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
        <div class="modal-box p-0 overflow-hidden relative" style="width: 90%; max-width: 900px;">
            
            {{-- Header --}}
            <div class="bg-base-200/50 p-4 border-b border-base-300 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg text-base-content/90 flex items-center gap-2">
                        <x-heroicon-o-plus-circle class="w-6 h-6 text-primary" />
                        Tambah Jadwal Manual
                    </h3>
                    <p class="text-xs text-base-content/60 mt-1">Penambahan jadwal membutuhkan Approval dari Atasan.</p>
                </div>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost" wire:click="$set('errorMessage', '')"><x-heroicon-s-x-mark class="w-5 h-5"/></button>
                </form>
            </div>

            {{-- Error Message Alert --}}
            @if($errorMessage)
                <div class="bg-error/10 border-l-4 border-error p-3 mx-6 mt-6 rounded-r-lg flex items-start gap-2 shadow-sm">
                    <x-heroicon-s-exclamation-circle class="w-5 h-5 text-error mt-0.5 shrink-0" />
                    <span class="text-sm text-error font-medium leading-tight">{!! nl2br(e($errorMessage)) !!}</span>
                </div>
            @endif

            {{-- Success Message Alert --}}
            @if($successMessage)
                <div class="p-10 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-success/20 rounded-full flex items-center justify-center mb-4">
                        <x-heroicon-o-check class="w-10 h-10 text-success" />
                    </div>
                    <h3 class="text-xl font-bold text-base-content/90 mb-2">Berhasil!</h3>
                    <p class="text-base-content/70 max-w-sm">{{ $successMessage }}</p>
                    
                    <button class="btn btn-primary mt-6 px-8" wire:click="resetModal" onclick="document.getElementById('modal-create-jks').close()">Selesai</button>
                </div>
            @else
                {{-- Body --}}
                <div class="p-6 overflow-y-auto max-h-[60vh] @if($errorMessage) pt-4 @endif">
                    
                    @if(empty($appliedBulan) || empty($appliedDistributor))
                    <div class="alert alert-warning shadow-sm text-sm">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                        <span>Silakan pilih <strong>Bulan</strong> dan <strong>Distributor</strong> pada filter utama terlebih dahulu sebelum menambah jadwal.</span>
                    </div>
                @else
                    {{-- Row 1: Basic Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="label text-xs font-bold uppercase text-base-content/70 pb-1">Bulan & Distributor</label>
                            <div class="text-sm font-semibold">{{ \Carbon\Carbon::parse($appliedBulan . '-01')->translatedFormat('F Y') }}</div>
                            <div class="text-xs text-base-content/50 mt-0.5">{{ $appliedDistributor }}</div>
                        </div>
                        <div>
                            <label class="label text-xs font-bold uppercase text-base-content/70 pb-1">Salesman <span class="text-error">*</span></label>
                            <select wire:model="salesmanCode" class="select select-bordered select-sm w-full">
                                <option value="">-- Pilih Salesman --</option>
                                @foreach($salesmans as $sales)
                                    <option value="{{ $sales->salesman_code }}">{{ $sales->salesman_name }} ({{ $sales->salesman_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: Customer Multi-Select --}}
                    <div class="mb-6">
                        <label class="label text-xs font-bold uppercase text-base-content/70 pb-1">Pilih Toko (Multi-Select) <span class="text-error">*</span></label>
                        
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="searchToko" placeholder="🔍 Ketik Nama atau Kode Toko Pareto..." class="input input-bordered input-sm w-full bg-base-200/50 focus:bg-base-100" />
                            
                            @if(!empty($searchResults))
                                <ul class="absolute z-50 mt-1 w-full bg-base-100 shadow-xl max-h-60 rounded-box overflow-auto border border-base-200 text-sm">
                                    @foreach($searchResults as $res)
                                        <li>
                                            <button type="button" wire:click="selectToko('{{ $res->customer_code }}', '{{ addslashes($res->customer_name) }}')" class="w-full text-left px-4 py-2 hover:bg-primary hover:text-primary-content transition-colors">
                                                <div class="flex justify-between items-center mb-0.5">
                                                    <span class="font-bold text-sm">{{ $res->customer_name }}</span>
                                                    <span class="text-[10px] font-mono opacity-80 bg-base-200/30 px-1.5 py-0.5 rounded">{{ $res->customer_code }}</span>
                                                </div>
                                                <div class="text-[10px] opacity-80 truncate">{{ $res->customer_address }}</div>
                                                <div class="text-[10px] opacity-70">
                                                    <x-heroicon-s-map-pin class="w-3 h-3 inline-block -mt-0.5" /> 
                                                    {{ $res->desa ?? '-' }}, {{ $res->kecamatan ?? '-' }}
                                                </div>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif(strlen($searchToko) >= 2)
                                <div class="absolute z-50 mt-1 w-full bg-base-100 shadow-xl rounded-box border border-base-200 text-sm p-3 text-center text-base-content/50">
                                    Toko tidak ditemukan di Master Pareto untuk Distributor ini.
                                </div>
                            @endif
                        </div>

                        {{-- Selected Badges --}}
                        <div class="mt-3 flex flex-wrap gap-2 min-h-[40px] p-2 border border-dashed border-base-300 rounded-lg bg-base-200/20">
                            @forelse($selectedTokos as $toko)
                                <div class="badge badge-primary gap-1 p-3 shadow-sm">
                                    <span class="font-semibold text-xs">{{ $toko['name'] }}</span>
                                    <span class="text-[9px] opacity-70">({{ $toko['code'] }})</span>
                                    <button type="button" wire:click="removeToko('{{ $toko['code'] }}')" class="hover:text-error transition-colors ml-1">
                                        <x-heroicon-s-x-mark class="w-3 h-3" />
                                    </button>
                                </div>
                            @empty
                                <div class="text-xs text-base-content/40 flex items-center gap-1 w-full justify-center my-1">
                                    <x-heroicon-o-information-circle class="w-4 h-4" /> Belum ada toko yang dipilih
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Row 3: Jadwal --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-base-200/30 p-4 rounded-lg border border-base-200">
                        <div>
                            <label class="text-xs font-bold uppercase text-base-content/70 block mb-2">Hari Kunjungan <span class="text-error">*</span></label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['h1'=>'Sen', 'h2'=>'Sel', 'h3'=>'Rab', 'h4'=>'Kam', 'h5'=>'Jum', 'h6'=>'Sab', 'h7'=>'Min'] as $key => $label)
                                    <label class="cursor-pointer flex items-center gap-1.5 px-2.5 py-1.5 border rounded-md transition-colors {{ $hari[$key] ? 'bg-success/10 border-success text-success shadow-sm' : 'border-base-300 text-base-content/70 hover:bg-base-200' }}">
                                        <input type="checkbox" wire:model="hari.{{ $key }}" class="checkbox checkbox-success checkbox-xs" />
                                        <span class="text-xs font-medium">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-base-content/70 block mb-2">Minggu Kunjungan <span class="text-error">*</span></label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['w1'=>'M1', 'w2'=>'M2', 'w3'=>'M3', 'w4'=>'M4'] as $key => $label)
                                    <label class="cursor-pointer flex items-center gap-1.5 px-2.5 py-1.5 border rounded-md transition-colors {{ $minggu[$key] ? 'bg-primary/10 border-primary text-primary shadow-sm' : 'border-base-300 text-base-content/70 hover:bg-base-200' }}">
                                        <input type="checkbox" wire:model="minggu.{{ $key }}" class="checkbox checkbox-primary checkbox-xs" />
                                        <span class="text-xs font-medium">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Row 4: Reason --}}
                    <div>
                        <label class="label text-xs font-bold uppercase text-base-content/70 pb-1">Alasan Pengajuan <span class="text-[10px] font-normal text-base-content/50 ml-1">(Opsional)</span></label>
                        <textarea wire:model="reason" class="textarea textarea-bordered w-full text-sm" rows="2" placeholder="Cth: Penambahan rute baru instruksi SPV..."></textarea>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="bg-base-200/50 p-4 border-t border-base-300 flex justify-end gap-2">
                <form method="dialog">
                    <button class="btn btn-sm btn-ghost">Batal</button>
                </form>
                <button type="button" wire:click="submit" class="btn btn-sm btn-primary" wire:loading.class="opacity-50" wire:target="submit" @if(empty($appliedBulan) || empty($appliedDistributor)) disabled @endif>
                    <span wire:loading.remove wire:target="submit">
                        <x-heroicon-s-paper-airplane class="w-4 h-4" />
                    </span>
                    <span wire:loading wire:target="submit" class="loading loading-spinner loading-xs"></span>
                    Ajukan Approval
                </button>
            </div>
            @endif
        </div>
    </dialog>

    {{-- Close modal script listener --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', (id) => {
                const modal = document.getElementById(id[0]);
                if (modal) {
                    modal.close();
                }
            });
        });
    </script>
</div>
