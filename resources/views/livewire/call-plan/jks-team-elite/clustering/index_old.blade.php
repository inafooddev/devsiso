<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Customer Clustering</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">SISO vs ESKA</a>
            <a href="{{ route('call-plan.jks-team-elite.route-efficiency') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Route Efficiency</a>
            <a href="{{ route('call-plan.jks-team-elite.clustering') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100">Clustering</a>
        </div>
    </div>

    {{-- Header / Filter Bar --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success mb-4 flex justify-between items-start">
            <div class="flex items-start gap-3">
                <x-heroicon-s-check-circle class="w-6 h-6 shrink-0 mt-0.5" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                    <div class="text-sm">{{ session('message') }}</div>
                </div>
            </div>
            <button type="button" @click="show = false" class="btn btn-ghost btn-sm btn-circle shrink-0 hover:bg-success/20">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error mb-4 flex justify-between items-start">
            <div class="flex items-start gap-3">
                <x-heroicon-s-x-circle class="w-6 h-6 shrink-0 mt-0.5" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                    <div class="text-sm">{{ session('error') }}</div>
                </div>
            </div>
            <button type="button" @click="show = false" class="btn btn-ghost btn-sm btn-circle shrink-0 hover:bg-error/20">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>
    @endif

    <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 p-4 shrink-0 flex flex-col gap-4">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-lg font-bold">Pembuatan Cluster Toko</h2>
                <p class="text-xs text-base-content/60">Buat grup rute efisien berdasarkan titik tengah (Center Store)</p>
            </div>
            <button onclick="document.getElementById('modal_panduan').showModal()" class="btn btn-sm btn-ghost text-info hover:bg-info/10">
                <x-heroicon-o-information-circle class="w-5 h-5" />
                <span class="hidden sm:inline">Panduan & Info</span>
            </button>
        </div>
        
        <div class="flex flex-col sm:flex-row items-end gap-3 w-full">
            <div class="w-full sm:w-[35%] relative">
                <label class="label py-1"><span class="label-text text-xs">Filter Distributor (Opsional)</span></label>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="searchDistributor" type="text" class="input input-sm input-bordered w-full rounded-xl bg-base-100 pr-8" placeholder="Ketik Kode/Nama Distributor...">
                    @if(!empty($selectedDistributorCode))
                        <button wire:click="clearDistributor" class="absolute right-1 top-1 btn btn-xs btn-circle btn-ghost text-base-content/50 hover:bg-base-200">✕</button>
                    @endif
                </div>
                
                @if(count($distributorOptions) > 0)
                <ul class="menu bg-base-100 border border-base-200 rounded-box mt-1 max-h-60 overflow-y-auto absolute w-full z-50 shadow-lg top-full left-0">
                    @foreach($distributorOptions as $res)
                        <li><a wire:click="selectDistributor('{{ $res['distributor_code'] }}', '{{ addslashes($res['distributor_name']) }}')">{{ $res['distributor_code'] }} - {{ $res['distributor_name'] }}</a></li>
                    @endforeach
                </ul>
                @endif
            </div>

            <div class="w-full sm:w-[35%] relative">
                <label class="label py-1"><span class="label-text text-xs">Cari Center Store (Titik Pusat)</span></label>
                <input wire:model.live.debounce.300ms="searchCenterText" type="text" class="input input-sm input-bordered w-full rounded-xl bg-base-100" placeholder="Ketik Kode/Nama Toko...">
                
                @if(count($searchCenterResults) > 0)
                <ul class="menu bg-base-100 border border-base-200 rounded-box mt-1 max-h-60 overflow-y-auto absolute w-full z-50 shadow-lg top-full left-0">
                    @foreach($searchCenterResults as $res)
                        <li><a wire:click="selectCenterStore({{ $res->id }})">{{ $res->customer_code_prc }} - {{ $res->customer_name }}</a></li>
                    @endforeach
                </ul>
                @endif
            </div>

            <div class="w-full sm:w-24">
                <label class="label py-1"><span class="label-text text-xs">Jumlah Toko</span></label>
                <input wire:model="candidateCount" type="number" class="input input-sm input-bordered w-full rounded-xl bg-base-100" min="2" max="80">
            </div>

            <div class="w-full sm:w-auto">
                <button wire:click="generateCluster" wire:loading.attr="disabled" wire:target="generateCluster" class="btn btn-sm btn-primary rounded-xl w-full" @if(!$centerStore) disabled @endif>
                    <span wire:loading.remove wire:target="generateCluster">Generate Route</span>
                    <span wire:loading wire:target="generateCluster" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    @if(count($clusterStores) >= 2)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 shrink-0">
        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-map class="w-5 h-5 text-primary" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Jarak</p>
            </div>
            <div class="text-base font-bold text-primary">{{ $totalDistance }} <span class="text-xs font-normal">Km</span></div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-arrows-pointing-out class="w-5 h-5 text-info" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Rata-rata</p>
            </div>
            <div class="text-base font-bold text-info">{{ $averageDistance }} <span class="text-xs font-normal">Km</span></div>
        </div>

        @php
            $statusColor = 'success';
            if (str_contains($efficiencyStatus, 'Wajar')) $statusColor = 'warning';
            if (str_contains($efficiencyStatus, 'Tersebar')) $statusColor = 'error';
        @endphp
        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-check-badge class="w-5 h-5 text-{{ $statusColor }}" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Status</p>
            </div>
            <div class="text-sm font-bold text-{{ $statusColor }} truncate">{{ $efficiencyStatus }}</div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-truck class="w-5 h-5 text-secondary" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Di Jalan</p>
            </div>
            <div class="text-sm font-bold text-secondary truncate">{{ $drivingDurationFormatted }}</div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-building-storefront class="w-5 h-5 text-accent" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Visit Toko</p>
            </div>
            <div class="text-sm font-bold text-accent truncate">{{ $visitDurationFormatted }}</div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-base-content" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Total Kerja</p>
            </div>
            <div class="text-sm font-bold text-base-content truncate">{{ $totalDurationFormatted }}</div>
        </div>
    </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-4 flex-1 min-h-0">
        {{-- Map Container --}}
        <div class="w-full lg:w-2/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col relative z-0" wire:ignore>
            <div id="route-map" class="w-full h-[400px] lg:h-full z-0"></div>
            
            <div class="absolute bottom-4 right-4 bg-base-100/90 backdrop-blur p-2 rounded-lg border border-base-300 shadow-sm z-[400] text-xs">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-4 h-1 bg-blue-500 rounded-full"></div>
                    <span>Rute Jalan Raya (API)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-1 bg-green-500 border-dashed border border-transparent" style="border-top: 2px dashed #22c55e; background: none;"></div>
                    <span>Garis Lurus (Manual)</span>
                </div>
            </div>
        </div>

        {{-- Route Table --}}
        <div class="w-full lg:w-1/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col">
            <div class="p-3 border-b border-base-300 bg-base-200/50 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-sm">List Toko Cluster</h3>
                    <p class="text-[0.65rem] text-base-content/60">{{ count($clusterStores) }} Toko</p>
                </div>
                
                @if(count($clusterStores) > 0)
                <button wire:click="openSaveModal" class="btn btn-xs btn-success text-white">Simpan Cluster</button>
                @endif
            </div>

            {{-- Manual Add Store --}}
            <div class="p-2 border-b border-base-300 relative">
                <input wire:model.live.debounce.300ms="searchAddText" type="text" class="input input-xs input-bordered w-full" placeholder="Ketik untuk tambah toko...">
                @if(count($searchAddResults) > 0)
                <ul class="menu bg-base-100 border border-base-200 rounded-box mt-1 max-h-60 overflow-y-auto absolute w-full z-50 shadow-lg top-full left-0">
                    @foreach($searchAddResults as $res)
                        <li><a wire:click="selectAddStore({{ $res->id }})">{{ $res->customer_code_prc }} - {{ $res->customer_name }}</a></li>
                    @endforeach
                </ul>
                @endif
            </div>

            <div class="flex-1 overflow-auto p-0">
                <table class="table table-sm table-zebra w-full text-xs">
                    <thead class="bg-base-200/50 sticky top-0">
                        <tr>
                            <th class="w-10">No</th>
                            <th>Toko</th>
                            <th class="w-16">Pilar</th>
                            <th class="text-right w-16">Jarak (Km)</th>
                            <th class="w-10">Act</th>
                        </tr>
                    </thead>
                    <tbody x-data="{
                        draggingIndex: null,
                        dragstart(event, index) {
                            this.draggingIndex = index;
                            event.dataTransfer.effectAllowed = 'move';
                            event.target.classList.add('opacity-50');
                        },
                        dragenter(event) {
                            let tr = event.target.closest('tr');
                            if(tr) tr.classList.add('bg-base-300');
                        },
                        dragleave(event) {
                            let tr = event.target.closest('tr');
                            if(tr) tr.classList.remove('bg-base-300');
                        },
                        drop(event, dropIndex) {
                            let tr = event.target.closest('tr');
                            if(tr) tr.classList.remove('bg-base-300');
                            
                            if (this.draggingIndex !== null && this.draggingIndex !== dropIndex) {
                                @this.call('reorderStore', this.draggingIndex, dropIndex);
                            }
                            this.draggingIndex = null;
                            document.querySelectorAll('.route-row').forEach(el => el.classList.remove('opacity-50', 'bg-base-300'));
                        },
                        dragend(event) {
                            this.draggingIndex = null;
                            document.querySelectorAll('.route-row').forEach(el => el.classList.remove('opacity-50', 'bg-base-300'));
                        }
                    }">
                        @forelse($clusterStores as $index => $store)
                            <tr wire:key="cluster-store-{{ $index }}-{{ $store['id'] ?? $index }}"
                                class="route-row cursor-grab active:cursor-grabbing hover:bg-base-200 transition-colors duration-150"
                                draggable="true"
                                @dragstart="dragstart($event, {{ $index }})"
                                @dragover.prevent=""
                                @dragenter.prevent="dragenter($event)"
                                @dragleave.prevent="dragleave($event)"
                                @drop.prevent="drop($event, {{ $index }})"
                                @dragend="dragend($event)">
                                <td class="font-bold flex items-center gap-1">
                                    <x-heroicon-o-bars-3 class="w-4 h-4 text-base-content/30" />
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    <div class="font-semibold text-primary truncate max-w-[120px]" title="{{ $store['customer_name'] }}">
                                        {{ $store['customer_name'] }}
                                    </div>
                                    <div class="text-[0.6rem] text-base-content/60">{{ $store['customer_code_prc'] }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-sm badge-outline text-[0.6rem] whitespace-nowrap">{{ $store['pilar'] ?? '-' }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="font-bold">{{ $store['distance_to_next'] ?? 0 }}</div>
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <div class="flex flex-col gap-0.5">
                                            <button wire:click="moveStoreUp({{ $index }})" class="btn btn-xs btn-ghost btn-circle {{ $index == 0 ? 'opacity-30 cursor-not-allowed' : '' }}" {{ $index == 0 ? 'disabled' : '' }} title="Geser ke Atas (Lebih Awal)">
                                                <x-heroicon-o-arrow-up class="w-3 h-3" />
                                            </button>
                                            <button wire:click="moveStoreDown({{ $index }})" class="btn btn-xs btn-ghost btn-circle {{ $index == count($clusterStores) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}" {{ $index == count($clusterStores) - 1 ? 'disabled' : '' }} title="Geser ke Bawah (Lebih Akhir)">
                                                <x-heroicon-o-arrow-down class="w-3 h-3" />
                                            </button>
                                        </div>
                                        <button wire:click="removeStore({{ $index }})" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error/20" title="Hapus dari Rute">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-base-content/50">Belum ada rute di-generate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Save Modal --}}
    <div class="modal {{ $isSaveModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-backdrop bg-base-300/80 backdrop-blur-sm fixed inset-0" wire:click="closeSaveModal"></div>
        <div class="modal-box rounded-2xl relative z-10 shadow-2xl">
            <button wire:click="closeSaveModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-lg mb-4">Simpan Rute</h3>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text">Tipe Penyimpanan</span></label>
                <div class="flex gap-4">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" wire:model.live="saveType" value="clustering" class="radio radio-primary radio-sm" />
                        <span class="label-text">Master Clustering</span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" wire:model.live="saveType" value="jks" class="radio radio-primary radio-sm" />
                        <span class="label-text">Jadwalkan ke JKS</span>
                    </label>
                </div>
            </div>

            @if($saveType === 'clustering')
            <div class="form-control w-full mb-3">
                <label class="label"><span class="label-text">Nama Cluster</span></label>
                <input wire:model="clusterName" type="text" class="input input-bordered w-full" placeholder="Contoh: Cluster Pare 1" />
            </div>
            @else
            <div class="form-control w-full mb-3">
                <label class="label"><span class="label-text">Tanggal Kunjungan JKS</span></label>
                <input wire:model="jksDate" type="date" class="input input-bordered w-full" />
            </div>
            
            <div class="form-control w-full mb-3">
                <label class="label"><span class="label-text">Metode Sinkronisasi</span></label>
                <div class="flex flex-col gap-2 bg-base-200/50 p-3 rounded-lg border border-base-300">
                    <label class="cursor-pointer flex items-start gap-3">
                        <input type="radio" wire:model="jksSyncMethod" value="skip" class="radio radio-primary radio-sm mt-1" />
                        <div>
                            <span class="label-text font-bold">Skip if Exists (Aman)</span>
                            <div class="text-[0.65rem] text-base-content/60 leading-tight mt-0.5">Abaikan toko jika sudah terdaftar di jadwal tim ini pada tanggal tersebut.</div>
                        </div>
                    </label>
                    <label class="cursor-pointer flex items-start gap-3 mt-1">
                        <input type="radio" wire:model="jksSyncMethod" value="sync" class="radio radio-error radio-sm mt-1" />
                        <div>
                            <span class="label-text font-bold text-error">Full Sync (Hapus & Timpa Semua)</span>
                            <div class="text-[0.65rem] text-error/80 leading-tight mt-0.5">Semua rute jadwal tim ini pada tanggal tersebut akan <b>dihapus bersih</b> dan diganti penuh dengan daftar rute di atas.</div>
                        </div>
                    </label>
                </div>
            </div>
            @endif

            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text">Pilih Team Sales</span></label>
                <select wire:model="filterTeam" class="select select-bordered w-full">
                    @if(count($teams) == 0)
                        <option value="">-- Tidak ada team --</option>
                    @endif
                    @foreach($teams as $team)
                        <option value="{{ $team->kode_team }}">{{ $team->kode_team }} - {{ $team->nama_team }}</option>
                    @endforeach
                </select>
            </div>

            <div class="modal-action mt-6">
                <button wire:click="closeSaveModal" class="btn btn-ghost rounded-xl">Batal</button>
                <button wire:click="confirmSaveCluster" wire:loading.attr="disabled" wire:target="confirmSaveCluster" class="btn btn-success rounded-xl text-white">
                    <span wire:loading.remove wire:target="confirmSaveCluster">Konfirmasi Simpan</span>
                    <span wire:loading wire:target="confirmSaveCluster" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Panduan --}}
    <dialog id="modal_panduan" class="modal z-[999]">
        <div class="modal-box rounded-2xl relative w-11/12 max-w-2xl shadow-2xl">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                <x-heroicon-o-information-circle class="w-6 h-6 text-info" />
                Panduan & Metode Clustering
            </h3>
            
            <div class="space-y-4 text-sm max-h-[60vh] overflow-y-auto pr-2">
                <div class="bg-base-200/50 p-4 rounded-xl">
                    <h4 class="font-bold mb-2">Panduan Penggunaan:</h4>
                    <ol class="list-decimal list-inside space-y-2">
                        <li><strong>Filter Distributor (Opsional):</strong> Pilih distributor jika Anda ingin membatasi pencarian toko hanya pada satu distributor tertentu.</li>
                        <li><strong>Pilih Center Store:</strong> Cari dan pilih toko yang akan menjadi titik pusat <i>(center)</i> dari rute yang akan dibuat.</li>
                        <li><strong>Tentukan Jumlah Toko:</strong> Masukkan estimasi berapa banyak toko yang ingin dikunjungi (maksimal 80 toko).</li>
                        <li><strong>Generate Route:</strong> Klik tombol ini, sistem akan mencari titik-titik terdekat dan menyusun rute paling efisien.</li>
                        <li><strong>Simpan:</strong> Klik tombol "Simpan Cluster" dan pilih apakah akan disimpan sebagai Master Clustering atau dijadwalkan langsung ke JKS.</li>
                    </ol>
                </div>

                <div class="bg-base-200/50 p-4 rounded-xl">
                    <h4 class="font-bold mb-2">Metode Kalkulasi Rute (Algoritma yang Digunakan):</h4>
                    <p class="mb-2">Pembuatan rute dan penentuan titik pada halaman ini menggunakan pendekatan hibrida dalam tiga tahap:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li><strong>Tahap 1 - Pemfilteran Awal (Haversine Formula):</strong> <br/>
                            <span class="ml-5 text-base-content/80 text-xs block mt-1">Sistem mencari toko-toko kandidat dari database dengan menghitung jarak lurus (jarak udara) menggunakan rumus matematika Haversine dari <i>Center Store</i>. Ini untuk menyaring ribuan toko menjadi radius wajar.</span>
                        </li>
                        <li><strong>Tahap 2 - Seleksi Akurat (OSRM Distance Matrix):</strong> <br/>
                            <span class="ml-5 text-base-content/80 text-xs block mt-1">Kandidat dari Tahap 1 dikirim ke server pemetaan OSRM untuk mendapatkan jarak jalan raya nyata. Sistem lalu memilih jumlah toko sesuai yang Anda minta (misal: 10 toko) yang benar-benar memiliki <b>jarak tempuh jalan terdekat</b>, bukan sekadar jarak lurus.</span>
                        </li>
                        <li><strong>Tahap 3 - Pengurutan Rute (TSP & OSRM API):</strong> <br/>
                            <span class="ml-5 text-base-content/80 text-xs block mt-1">Sistem memecahkan masalah rute antar titik (Traveling Salesperson Problem / TSP) dengan mempertimbangkan arah jalan dan larangan putar balik untuk mendapatkan urutan kunjungan optimal.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-primary rounded-xl text-white">Mengerti</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-300/80 backdrop-blur-sm">
            <button>close</button>
        </form>
    </dialog>
</div>

@once
@push('styles')
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <style>
        .store-marker-label {
            background-color: #22c55e;
            color: white;
            font-weight: bold;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            font-size: 11px;
            cursor: pointer;
        }
        .maplibregl-popup-content {
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <script>
        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }

        document.addEventListener('livewire:init', () => {
            let map;
            let markers = [];
            let currentRouteSourceId = 'cluster-route-source';
            let currentRouteLayerId = 'cluster-route-layer';
            let resizeObserver = null;

            function initMap() {
                if (!document.getElementById('route-map')) return;
                
                if (map) {
                    map.remove();
                }

                map = new maplibregl.Map({
                    container: 'route-map',
                    style: {
                        'version': 8,
                        'sources': {
                            'raster-tiles': {
                                'type': 'raster',
                                'tiles': [
                                    'https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                                    'https://b.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                                    'https://c.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                                    'https://d.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png'
                                ],
                                'tileSize': 256,
                                'attribution': '© OpenStreetMap contributors, © CARTO'
                            }
                        },
                        'layers': [
                            {
                                'id': 'simple-tiles',
                                'type': 'raster',
                                'source': 'raster-tiles',
                                'minzoom': 0,
                                'maxzoom': 19
                            }
                        ]
                    },
                    center: [118.0149, -2.5489],
                    zoom: 5
                });

                map.addControl(new maplibregl.NavigationControl());

                if (resizeObserver) resizeObserver.disconnect();
                resizeObserver = new ResizeObserver(() => {
                    if (map) map.resize();
                });
                resizeObserver.observe(document.getElementById('route-map'));
            }

            function clearMap() {
                if (markers.length > 0) {
                    markers.forEach(m => m.remove());
                    markers = [];
                }
                if (map && map.getStyle()) {
                    if (map.getLayer(currentRouteLayerId)) {
                        map.removeLayer(currentRouteLayerId);
                    }
                    if (map.getSource(currentRouteSourceId)) {
                        map.removeSource(currentRouteSourceId);
                    }
                }
            }

            function drawRoute(routeData, geometry) {
                if (!map || !map.isStyleLoaded()) {
                    setTimeout(() => drawRoute(routeData, geometry), 200);
                    return;
                }
                
                clearMap();
                if (!routeData || routeData.length === 0) return;

                let bounds = new maplibregl.LngLatBounds();
                let coordinates = [];
                let hasPoints = false;

                routeData.forEach((store, index) => {
                    const lat = parseFloat(store.latitude);
                    const lng = parseFloat(store.longitude);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        let point = [lng, lat]; // MapLibre uses [lng, lat]
                        coordinates.push(point);
                        bounds.extend(point);
                        hasPoints = true;

                        const isFirst = index === 0;
                        const isLast = index === routeData.length - 1;
                        let markerColor = '#3b82f6'; // blue default
                        let zIndex = 1;
                        
                        if (isFirst) { markerColor = '#22c55e'; zIndex = 2; } // green first
                        else if (isLast) { markerColor = '#ef4444'; zIndex = 2; } // red last

                        const el = document.createElement('div');
                        el.className = 'store-marker-label';
                        el.style.backgroundColor = markerColor;
                        if (zIndex === 2) el.style.zIndex = '999';
                        el.textContent = index + 1;

                        const popupContent = `
                            <div class="text-xs min-w-[150px]">
                                <div class="font-bold text-sm mb-1 text-base-content">${index + 1}. ${escHtml(store.customer_name)}</div>
                                <div class="text-xs text-base-content/60 mb-1">${escHtml(store.customer_code_prc)}</div>
                                <div class="text-xs font-semibold text-blue-600 border-t border-base-200 pt-1 mt-1">
                                    Ke toko selanjutnya: ${escHtml(String(store.distance_to_next || 0))} Km
                                </div>
                            </div>
                        `;

                        let popup = new maplibregl.Popup({ offset: 15, closeButton: false }).setHTML(popupContent);

                        let marker = new maplibregl.Marker({ element: el })
                            .setLngLat(point)
                            .setPopup(popup)
                            .addTo(map);
                            
                        markers.push(marker);
                    }
                });

                // Draw line
                if (geometry) {
                    let geojsonData = geometry;
                    if (geometry.type !== 'Feature' && geometry.type !== 'FeatureCollection') {
                        geojsonData = {
                            "type": "Feature",
                            "properties": {},
                            "geometry": geometry
                        };
                    }
                    map.addSource(currentRouteSourceId, {
                        'type': 'geojson',
                        'data': geojsonData
                    });
                } else if (coordinates.length > 1) {
                    map.addSource(currentRouteSourceId, {
                        'type': 'geojson',
                        'data': {
                            'type': 'Feature',
                            'properties': {},
                            'geometry': {
                                'type': 'LineString',
                                'coordinates': coordinates
                            }
                        }
                    });
                }

                if (map.getSource(currentRouteSourceId)) {
                    map.addLayer({
                        'id': currentRouteLayerId,
                        'type': 'line',
                        'source': currentRouteSourceId,
                        'layout': {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        'paint': {
                            'line-color': geometry ? '#3b82f6' : '#22c55e',
                            'line-width': geometry ? 5 : 4,
                            'line-opacity': 0.7,
                            'line-dasharray': geometry ? [1] : [2, 2]
                        }
                    });
                }

                if (hasPoints) {
                    map.fitBounds(bounds, { padding: 50, duration: 1000 });
                }
            }

            // Initialization
            initMap();

            // Listen from Livewire Event
            Livewire.on('route-analyzed', (data) => {
                const routeData = data[0]?.route || data.route;
                const geometry = data[0]?.geometry || data.geometry;
                
                // Jika belum inisialisasi, paksa
                if (!map && document.getElementById('route-map')) {
                    initMap();
                }
                
                drawRoute(routeData, geometry);
            });
        });
    </script>
@endpush
@endonce
