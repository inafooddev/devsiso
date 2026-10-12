<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">List Potensi RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.dashboard') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Dashboard</a>
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Pencapaian</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>List Potensi</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SKB</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Plan Kunjungan</a>
            <a href="{{ route('rwo.monitoring-pareto') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Monitoring Visit</a>
        </div>
    </div>

    {{-- 4 KPI Cards Section (Clickable Quick Filters) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- KPI 1: Total Toko --}}
        <button type="button"
                wire:click="filterByCard('all')"
                title="Klik untuk tampilkan semua toko"
                class="text-left bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border transition-all flex flex-col relative overflow-hidden group cursor-pointer hover:shadow-md {{ $statusSkb === '' && $jenisReward === '' && $statusData === '' ? 'border-primary/40 ring-1 ring-primary/20' : 'border-base-300' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Toko</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-users class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($kpi['total_toko'], 0, ',', '.') }}</div>
        </button>

        {{-- KPI 2: Total Target --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-secondary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Target</h3>
                <div class="w-8 h-8 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                    <x-heroicon-s-currency-dollar class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-secondary">Rp {{ number_format($kpi['total_target'], 0, ',', '.') }}</div>
        </div>

        {{-- KPI 3: Sudah SKB --}}
        <button type="button"
                wire:click="filterByCard('sudah_skb')"
                title="Klik untuk filter toko Sudah SKB"
                class="text-left bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border transition-all flex flex-col relative overflow-hidden group cursor-pointer hover:shadow-md {{ $statusSkb === 'Sudah' ? 'border-success ring-2 ring-success/30' : 'border-base-300' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Sudah SKB</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ number_format($kpi['sudah_skb'], 0, ',', '.') }}</div>
        </button>

        {{-- KPI 4: Belum SKB --}}
        <button type="button"
                wire:click="filterByCard('belum_skb')"
                title="Klik untuk filter toko CashBack yang Belum SKB"
                class="text-left bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border transition-all flex flex-col relative overflow-hidden group cursor-pointer hover:shadow-md {{ $statusSkb === 'Belum' && $jenisReward === 'CB' ? 'border-error ring-2 ring-error/30' : 'border-base-300' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10 w-full">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Belum SKB (CB)</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ number_format($kpi['belum_skb'], 0, ',', '.') }}</div>
        </button>
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Alerts (Global via Events) --}}
        <div x-data="{ show: false, type: '', message: '' }" 
             @notify.window="show = true; type = $event.detail.type; message = $event.detail.message; setTimeout(() => show = false, 5000)"
             x-show="show" 
             x-transition.duration.500ms 
             class="alert rounded-none border-0 border-b shadow-sm"
             :class="type === 'success' ? 'alert-success' : 'alert-error'"
             style="display: none;">
            <span x-html="message"></span>
        </div>

        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">List Potensi RWO</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar potensi dan status reward outlet</p>
            </div>
            
            {{-- Menggunakan flex-wrap agar barisan aksi jatuh secara responsif jika window menyempit / dizoom --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live="search" />

                {{-- Filter Jenis Reward --}}
                <select wire:model.live="jenisReward" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Jenis Reward</option>
                    <option value="CB">CashBack</option>
                    <option value="BB">Bonus Barang</option>
                </select>

                {{-- Filter Status SKB --}}
                <select wire:model.live="statusSkb" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Status SKB</option>
                    <option value="Sudah">Sudah SKB</option>
                    <option value="Belum">Belum SKB</option>
                </select>

                {{-- Filter Status Data --}}
                <select wire:model.live="statusData" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Status Data</option>
                    <option value="Lengkap">Data Lengkap</option>
                    <option value="Belum">Data Belum Lengkap</option>
                </select>

                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <span class="relative inline-flex" onclick="filter_modal.showModal()">
                        <x-ui.action-button type="filter" />
                        @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor || $appliedPic)
                            <span class="badge badge-primary badge-xs absolute -top-1 -right-1"></span>
                        @endif
                    </span>
                    @if($canImport)
                    <span onclick="import_modal.showModal()">
                        <x-ui.action-button type="import" />
                    </span>
                    @endif
                    @if($canExport)
                    <span wire:click="exportData">
                        <x-ui.action-button type="export" />
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Active Filter Chips Bar --}}
        @if($appliedKuartal || $appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor || $appliedPic || $jenisReward || $statusSkb || $statusData)
        <div class="px-4 py-2 bg-base-200/50 border-b border-base-300 flex flex-wrap items-center gap-1.5 text-xs shrink-0">
            <span class="text-base-content/60 font-semibold mr-1">Filter Aktif:</span>
            @if($appliedKuartal)
                <span class="badge badge-sm badge-outline gap-1">Kuartal: <strong>Q{{ $appliedKuartal }}</strong></span>
            @endif
            @if($jenisReward)
                <button type="button" wire:click="$set('jenisReward', '')" class="badge badge-sm badge-primary gap-1 cursor-pointer">
                    Reward: {{ $jenisReward === 'CB' ? 'CashBack' : 'Bonus Barang' }} ✕
                </button>
            @endif
            @if($statusSkb)
                <button type="button" wire:click="$set('statusSkb', '')" class="badge badge-sm badge-primary gap-1 cursor-pointer">
                    SKB: {{ $statusSkb }} ✕
                </button>
            @endif
            @if($statusData)
                <button type="button" wire:click="$set('statusData', '')" class="badge badge-sm badge-primary gap-1 cursor-pointer">
                    Data: {{ $statusData }} ✕
                </button>
            @endif
            @if($appliedPic)
                <button type="button" wire:click="$set('pic', ''); $set('appliedPic', '')" class="badge badge-sm badge-secondary gap-1 cursor-pointer">
                    PIC: {{ $appliedPic }} ✕
                </button>
            @endif
            @if($appliedRegion)
                <span class="badge badge-sm badge-ghost border-base-300">Region: {{ $appliedRegion }}</span>
            @endif
            @if($appliedArea)
                <span class="badge badge-sm badge-ghost border-base-300">Area: {{ $appliedArea }}</span>
            @endif
            @if($appliedSupervisor)
                <span class="badge badge-sm badge-ghost border-base-300">SPV: {{ $appliedSupervisor }}</span>
            @endif
            @if($appliedDistributor)
                <span class="badge badge-sm badge-ghost border-base-300">Dist: {{ $appliedDistributor }}</span>
            @endif
            @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor || $appliedPic || $jenisReward || $statusSkb || $statusData)
                <button type="button" wire:click="resetFilter(); filterByCard('all')" class="text-error hover:underline font-semibold ml-auto text-[11px]">
                    Reset Semua
                </button>
            @endif
        </div>
        @endif

        {{-- Filter Modal --}}
        <dialog id="filter_modal" class="modal modal-bottom sm:modal-middle z-[99999]" wire:ignore.self>
            <div class="modal-box">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Filter Pencarian</h3>
                    <form method="dialog">
                        <button class="btn btn-sm btn-circle btn-ghost">✕</button>
                    </form>
                </div>
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text">Kuartal</span></label>
                            <select wire:model.live="kuartal" class="select select-bordered w-full">
                                <option value="">-- Semua Kuartal --</option>
                                @foreach($kuartals as $q)
                                    <option value="{{ $q->quarter }}">Kuartal {{ $q->quarter }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text">PIC Target</span></label>
                            <select wire:model.live="pic" class="select select-bordered w-full">
                                <option value="">-- Semua PIC --</option>
                                <option value="SPV">SPV (&lt; 30 Jt)</option>
                                <option value="ASM">ASM (30 Jt - &lt; 90 Jt)</option>
                                <option value="RSM">RSM (&ge; 90 Jt)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text">Region</span></label>
                        <select wire:model.live="region" class="select select-bordered w-full">
                            <option value="">-- Semua Region --</option>
                            @foreach($regions as $r)
                                <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text">Area</span></label>
                        <select wire:model.live="area" class="select select-bordered w-full" {{ empty($areas) ? 'disabled' : '' }}>
                            <option value="">-- Semua Area --</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text">Supervisor</span></label>
                        <select wire:model.live="supervisor" class="select select-bordered w-full" {{ empty($supervisors) ? 'disabled' : '' }}>
                            <option value="">-- Semua Supervisor --</option>
                            @foreach($supervisors as $s)
                                <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text">Distributor</span></label>
                        <select wire:model.live="distributor" class="select select-bordered w-full" {{ empty($distributors) ? 'disabled' : '' }}>
                            <option value="">-- Semua Distributor --</option>
                            @foreach($distributors as $d)
                                <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-action mt-6">
                    <form method="dialog" class="w-full flex justify-between">
                        <button class="btn btn-ghost text-error" wire:click="resetFilter">Reset Filter</button>
                        <button class="btn btn-primary" wire:click="applyFilter">Tutup & Terapkan</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>Tutup</button>
            </form>
        </dialog>

        {{-- Import Modal --}}
        <dialog id="import_modal" class="modal z-[99999]" wire:ignore.self
                x-data
                @close-import-modal.window="
                    $el.close();
                    if ($refs.fileInput) $refs.fileInput.value = '';
                "
                @download-log.window="
                    let content = $event.detail.content;
                    let filename = $event.detail.filename;
                    let blob = new Blob([content], { type: 'text/plain' });
                    let url = window.URL.createObjectURL(blob);
                    let a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                ">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Import Data List Potensi RWO</h3>
                
                <div class="mb-4 bg-info/10 text-info text-sm p-3 rounded-xl border border-info/20 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div>
                        <span class="font-bold block mb-1">Format Upload:</span>
                        Pastikan file Excel Anda memiliki kolom sesuai template yang disediakan (termasuk <code>jenis_reward</code>: CB / BB).
                    </div>
                    <button type="button" class="btn btn-sm btn-info btn-outline whitespace-nowrap" wire:click="downloadTemplate" wire:loading.attr="disabled" wire:target="downloadTemplate">
                        <span wire:loading.remove wire:target="downloadTemplate">Download Template</span>
                        <span wire:loading wire:target="downloadTemplate" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>

                <form wire:submit.prevent="importData">
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">File Excel (.xlsx, .csv)</span>
                        </label>
                        <input type="file" x-ref="fileInput" wire:model="importFile" class="file-input file-input-bordered file-input-primary w-full" accept=".xlsx, .xls, .csv" />
                        @error('importFile') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="importFile" class="text-sm text-info mt-1">Mengunggah file...</div>
                    </div>
                    
                    <div class="modal-action w-full flex justify-between">
                        <button type="button" class="btn btn-ghost" @click="$dispatch('close-import-modal')">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="importData,importFile">
                            <span wire:loading.remove wire:target="importData">Mulai Import</span>
                            <span wire:loading wire:target="importData" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
        </dialog>

        {{-- Delete Modal --}}
        <dialog id="delete_modal" class="modal z-[99999] {{ $isDeleteModalOpen ? 'modal-open' : '' }}">
            <div class="modal-box">
                <h3 class="font-bold text-lg text-error mb-4">Konfirmasi Hapus</h3>
                <p>Apakah Anda yakin ingin menghapus potensi toko <strong>{{ $deleteCustomerName }}</strong> ({{ $deleteCustomerCode }}) untuk Kuartal {{ $deleteKuartal }}?</p>
                <p class="text-sm text-base-content/60 mt-2">Tindakan ini akan menghapus data tersebut secara permanen.</p>
                
                <div class="modal-action mt-6">
                    <button class="btn btn-ghost" wire:click="$set('isDeleteModalOpen', false)">Batal</button>
                    <button class="btn btn-error" wire:click="destroyData" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="destroyData">Ya, Hapus Data</span>
                        <span wire:loading wire:target="destroyData" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop bg-black/60">
                <button wire:click="$set('isDeleteModalOpen', false)">Tutup</button>
            </form>
        </dialog>

        {{-- Edit Modal --}}
        <dialog id="edit_modal" class="modal z-[99999] {{ $isEditModalOpen ? 'modal-open' : '' }}">
            <div class="modal-box w-11/12 max-w-2xl">
                <div class="flex items-center justify-between mb-4 border-b border-base-200 pb-2">
                    <h3 class="font-bold text-lg">Edit Data Toko</h3>
                    <button type="button" class="btn btn-sm btn-circle btn-ghost" wire:click="$set('isEditModalOpen', false)">✕</button>
                </div>
                <form wire:submit.prevent="updateData">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text">Kode Toko</span></label>
                            <input type="text" class="input input-bordered w-full bg-base-200" wire:model="editCustomerCode" readonly />
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text">Distributor</span></label>
                            <input type="text" class="input input-bordered w-full bg-base-200" wire:model="editDistributorName" readonly />
                        </div>
                    </div>
                    
                    <div class="form-control w-full mb-4">
                        <label class="label"><span class="label-text">Nama Toko</span></label>
                        <input type="text" class="input input-bordered w-full" wire:model="editCustomerName" />
                        @error('editCustomerName') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="form-control w-full" x-data="{ amount: @entangle('editTotalTarget') }">
                            <label class="label"><span class="label-text">Total Target (Rp)</span></label>
                            <input type="number" class="input input-bordered w-full" wire:model="editTotalTarget" x-model="amount" />
                            <span class="text-sm text-success mt-1 font-mono" x-text="amount ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount) : 'Rp 0'"></span>
                            @error('editTotalTarget') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control w-full">
                            <label class="label"><span class="label-text">Jenis Reward</span></label>
                            <select class="select select-bordered w-full" wire:model="editJenisReward">
                                <option value="CB">CashBack (CB)</option>
                                <option value="BB">Bonus Barang (BB - 1 KTN)</option>
                            </select>
                            @error('editJenisReward') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="form-control w-full mb-4">
                        <label class="label"><span class="label-text">Alamat</span></label>
                        <textarea class="textarea textarea-bordered w-full h-24" wire:model="editAlamat"></textarea>
                        @error('editAlamat') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="modal-action mt-6 flex justify-between">
                        <button type="button" class="btn btn-ghost" wire:click="$set('isEditModalOpen', false)">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updateData">Simpan Perubahan</span>
                            <span wire:loading wire:target="updateData" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop bg-black/60">
                <button wire:click="$set('isEditModalOpen', false)">Tutup</button>
            </form>
        </dialog>

        {{-- Detail Modal --}}
        <dialog id="detail_modal" class="modal z-[99999] {{ $isDetailModalOpen ? 'modal-open' : '' }}">
            <div class="modal-box w-11/12 max-w-4xl p-0 overflow-hidden flex flex-col max-h-[90vh] bg-base-100 rounded-2xl shadow-2xl border border-base-300">
                @if($detailData)
                    @php
                        $isBB = ($detailData['jenis_reward'] ?? '') === 'BB';
                        $isCB = ($detailData['jenis_reward'] ?? '') === 'CB';
                        $kuartalClean = current(explode('_', $detailData['kuartal'] ?? '')) ?: '-';
                        $pct = 0.015;
                        if (($detailData['total_target'] ?? 0) >= 90000000) $pct = 0.025;
                        elseif (($detailData['total_target'] ?? 0) >= 30000000) $pct = 0.020;

                        $fields = [
                            'no_hp' => 'No HP',
                            'nama_pemilik_toko' => 'Nama Pemilik Toko',
                            // 'nik_ktp' => 'NIK KTP',
                            // 'nama_ktp' => 'Nama KTP',
                            // 'nama_bank' => 'Nama Bank',
                            // 'no_rekening' => 'No Rekening',
                            // 'nama_pemilik_norek' => 'Nama Pemilik Norek',
                            'latitude' => 'Latitude',
                            'longitude' => 'Longitude',
                        ];

                        $photos = [
                            // ['key' => 'foto_ktp', 'label' => 'Foto KTP'],
                            ['key' => 'foto_toko2', 'label' => 'Foto Tampak Depan'],
                            ['key' => 'foto_toko3', 'label' => 'Foto Tampak Dalam'],
                        ];

                        $totalCheckFields = count($fields) + count($photos);
                        $filledCount = 0;
                        foreach ($fields as $k => $lbl) {
                            if (isset($detailData[$k]) && trim((string) $detailData[$k]) !== '') $filledCount++;
                        }
                        foreach ($photos as $p) {
                            if (isset($detailData[$p['key']]) && trim((string) $detailData[$p['key']]) !== '') $filledCount++;
                        }
                        $hasCoords = !empty(trim((string) ($detailData['latitude'] ?? ''))) && !empty(trim((string) ($detailData['longitude'] ?? '')));
                    @endphp

                    {{-- Sticky Header Banner --}}
                    <div class="px-5 py-4 bg-gradient-to-r from-primary/10 via-base-200/60 to-base-100 border-b border-base-300 flex items-start justify-between gap-4 shrink-0">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-primary text-primary-content flex items-center justify-center shadow-sm shrink-0 mt-0.5">
                                <x-heroicon-s-building-storefront class="w-6 h-6" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-extrabold text-base md:text-lg text-base-content truncate" title="{{ $detailData['customer_name'] }}">
                                        {{ $detailData['customer_name'] }}
                                    </h3>
                                    <span class="badge badge-sm badge-primary font-bold">Kuartal {{ $kuartalClean }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-base-content/70 mt-1">
                                    <span class="font-mono font-semibold bg-base-200 px-2 py-0.5 rounded border border-base-300">
                                        Kode: {{ $detailData['customer_code'] }}
                                    </span>
                                    <span class="font-mono bg-base-200/70 px-2 py-0.5 rounded border border-base-300">
                                        PRC: {{ $detailData['customer_prc'] ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-circle btn-ghost text-base-content/60 hover:text-base-content shrink-0" wire:click="$set('isDetailModalOpen', false)" title="Tutup">
                            ✕
                        </button>
                    </div>

                    {{-- Scrollable Body --}}
                    <div class="p-5 overflow-y-auto space-y-5 flex-1">
                        {{-- 1. Highlight Summary Bar (3 Cards) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Card Target --}}
                            <div class="bg-base-200/40 rounded-xl p-3.5 border border-base-300 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-base-content/50 block">Total Target</span>
                                    <span class="text-base md:text-lg font-extrabold font-mono text-primary mt-0.5 block">
                                        Rp {{ number_format($detailData['total_target'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <x-heroicon-s-currency-dollar class="w-5 h-5" />
                                </div>
                            </div>

                            {{-- Card Reward & PIC --}}
                            <div class="bg-base-200/40 rounded-xl p-3.5 border border-base-300 flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-base-content/50">Reward</span>
                                        <span class="badge badge-xs {{ $isBB ? 'badge-secondary' : 'badge-info' }} font-semibold">
                                            {{ $isBB ? 'Bonus Barang' : ($isCB ? 'CashBack' : '-') }}
                                        </span>
                                    </div>
                                    <div class="flex items-baseline gap-2 mt-0.5">
                                        <span class="text-base md:text-lg font-extrabold text-base-content">
                                            @if($isBB)
                                                1 KTN
                                            @elseif($isCB)
                                                {{ rtrim(rtrim(number_format($pct * 100, 2, ',', '.'), '0'), ',') }}%
                                            @else
                                                -
                                            @endif
                                        </span>
                                        <span class="text-xs font-bold text-base-content/60">• PIC: <strong class="text-base-content">{{ $detailData['pic'] ?? '-' }}</strong></span>
                                    </div>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center shrink-0">
                                    <x-heroicon-s-check-badge class="w-5 h-5" />
                                </div>
                            </div>

                            {{-- Card Status SKB --}}
                            <div class="bg-base-200/40 rounded-xl p-3.5 border border-base-300 flex flex-col justify-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-base-content/50 block mb-1">Status SKB</span>
                                @if($isBB)
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-base-content/60">
                                        <span class="badge badge-sm badge-ghost">Tidak Wajib SKB</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        @if(($detailData['status_skb'] ?? '') === 'Sudah')
                                            @if($detailData['is_approved'] === true)
                                                <span class="badge badge-sm badge-success font-bold gap-1">
                                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Approved
                                                </span>
                                            @elseif($detailData['is_approved'] === false)
                                                <span class="badge badge-sm badge-error font-bold gap-1">
                                                    <x-heroicon-s-x-circle class="w-3.5 h-3.5" /> Rejected
                                                </span>
                                            @else
                                                <span class="badge badge-sm badge-warning font-bold gap-1">
                                                    <x-heroicon-s-clock class="w-3.5 h-3.5" /> Pending
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge badge-sm badge-outline badge-error font-bold">Belum SKB</span>
                                        @endif
                                    </div>
                                    @if(($detailData['status_skb'] ?? '') === 'Sudah' && $detailData['is_approved'] === false && !empty($detailData['skb_reason']))
                                        <p class="text-[11px] text-error mt-1.5 leading-tight bg-error/10 px-2 py-1 rounded border border-error/20">
                                            <strong>Alasan:</strong> {{ $detailData['skb_reason'] }}
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- 2. Grid Informasi Profil & Hirarki Distribusi --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Kolom Kiri: Profil & Lokasi Toko --}}
                            <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-2 pb-2.5 mb-3 border-b border-base-200">
                                        <x-heroicon-s-information-circle class="w-4 h-4 text-primary" />
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70">Profil & Lokasi Toko</h4>
                                    </div>
                                    <dl class="space-y-2.5 text-xs">
                                        <div class="flex justify-between gap-4">
                                            <dt class="text-base-content/60 shrink-0">Nama Pemilik</dt>
                                            <dd class="font-semibold text-right text-base-content">{{ !empty(trim((string) ($detailData['nama_pemilik_toko'] ?? ''))) ? $detailData['nama_pemilik_toko'] : '-' }}</dd>
                                        </div>
                                        <div class="flex justify-between gap-4">
                                            <dt class="text-base-content/60 shrink-0">No. Handphone</dt>
                                            <dd class="font-mono font-semibold text-right text-base-content">{{ !empty(trim((string) ($detailData['no_hp'] ?? ''))) ? $detailData['no_hp'] : '-' }}</dd>
                                        </div>
                                        <div class="flex flex-col gap-1 pt-1 border-t border-base-200/60">
                                            <dt class="text-base-content/60">Alamat Toko</dt>
                                            <dd class="font-medium text-base-content leading-relaxed bg-base-200/40 p-2 rounded-lg border border-base-200">
                                                {{ !empty(trim((string) ($detailData['alamat'] ?? ''))) ? $detailData['alamat'] : '-' }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="mt-3 pt-2.5 border-t border-base-200 flex items-center justify-between gap-2">
                                    <div class="text-xs">
                                        <span class="text-base-content/60 block text-[11px]">Koordinat GPS</span>
                                        @if($hasCoords)
                                            <span class="font-mono font-semibold text-base-content">{{ $detailData['latitude'] }}, {{ $detailData['longitude'] }}</span>
                                        @else
                                            <span class="text-error/80 italic">Belum ada titik koordinat</span>
                                        @endif
                                    </div>
                                    @if($hasCoords)
                                        <a href="https://www.google.com/maps?q={{ urlencode(trim($detailData['latitude']) . ',' . trim($detailData['longitude'])) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="btn btn-xs btn-outline btn-primary gap-1 shrink-0">
                                            <x-heroicon-s-eye class="w-3.5 h-3.5" /> Buka Maps
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Kolom Kanan: Hirarki Area & Distribusi --}}
                            <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm">
                                <div class="flex items-center gap-2 pb-2.5 mb-3 border-b border-base-200">
                                    <x-heroicon-s-user-group class="w-4 h-4 text-secondary" />
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70">Hirarki Wilayah & Distribusi</h4>
                                </div>
                                <dl class="space-y-3 text-xs">
                                    <div class="flex items-center justify-between gap-4 pb-2 border-b border-base-200/60">
                                        <dt class="text-base-content/60">Region</dt>
                                        <dd class="font-bold text-right text-base-content">{{ $detailData['region_name'] ?? '-' }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 pb-2 border-b border-base-200/60">
                                        <dt class="text-base-content/60">Area</dt>
                                        <dd class="font-semibold text-right text-base-content">{{ $detailData['area_name'] ?? '-' }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 pb-2 border-b border-base-200/60">
                                        <dt class="text-base-content/60 shrink-0">Supervisor</dt>
                                        <dd class="font-semibold text-right text-base-content truncate" title="{{ $detailData['supervisor_name'] ?? '-' }}">
                                            {{ $detailData['supervisor_name'] ?? '-' }}
                                        </dd>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <dt class="text-base-content/60">Distributor</dt>
                                        <dd class="font-semibold text-base-content bg-base-200/40 p-2 rounded-lg border border-base-200 flex items-center justify-between gap-2">
                                            <span class="truncate">{{ $detailData['distributor_name'] ?? '-' }}</span>
                                            <span class="badge badge-xs badge-neutral font-mono shrink-0">{{ $detailData['distributor_code'] ?? '-' }}</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        {{-- 3. Checklist Kelengkapan Data Toko (Hanya untuk CB) --}}
                        @if(!$isBB)
                        <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-2 pb-2.5 mb-3 border-b border-base-200">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-s-clipboard-document-check class="w-4 h-4 text-primary" />
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70">Indikator Kelengkapan Data Toko</h4>
                                </div>
                                <span class="badge badge-sm font-bold {{ $filledCount === $totalCheckFields ? 'badge-success' : 'badge-warning' }}">
                                    {{ $filledCount }} / {{ $totalCheckFields }} Terisi
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                                @foreach($fields as $key => $label)
                                    @php $isFilled = isset($detailData[$key]) && trim((string) $detailData[$key]) !== ''; @endphp
                                    <div class="flex items-center justify-between py-1.5 px-2.5 rounded-lg {{ $isFilled ? 'bg-success/5 border border-success/20' : 'bg-error/5 border border-error/20' }}">
                                        <span class="text-xs font-medium text-base-content/80">{{ $label }}</span>
                                        @if($isFilled)
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-xs font-semibold text-base-content truncate max-w-[160px]" title="{{ $detailData[$key] }}">{{ $detailData[$key] }}</span>
                                                <x-heroicon-s-check-circle class="w-4 h-4 text-success shrink-0" />
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1 text-error">
                                                <span class="text-[11px] font-medium">Kosong</span>
                                                <x-heroicon-s-x-circle class="w-4 h-4 shrink-0" />
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- 4. Galeri Dokumentasi Foto + In-Modal Lightbox --}}
                        @php
                            $galleryItems = [];
                            foreach ($photos as $p) {
                                $hasP = isset($detailData[$p['key']]) && trim((string) $detailData[$p['key']]) !== '';
                                $galleryItems[] = [
                                    'key' => $p['key'],
                                    'label' => $p['label'],
                                    'hasPhoto' => $hasP,
                                    'url' => $hasP ? Storage::url($detailData[$p['key']]) : '',
                                    'icon' => 'photo',
                                ];
                            }
                            if (!$isBB) {
                                $hasSkbP = ($detailData['status_skb'] ?? '') === 'Sudah' && !empty($detailData['foto_skb']);
                                $galleryItems[] = [
                                    'key' => 'foto_skb',
                                    'label' => 'Foto Dokumen SKB',
                                    'hasPhoto' => $hasSkbP,
                                    'url' => $hasSkbP ? Storage::url($detailData['foto_skb']) : '',
                                    'icon' => 'document',
                                ];
                            }
                        @endphp
                        <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm"
                             x-data="{
                                 lightboxOpen: false,
                                 activeUrl: '',
                                 activeLabel: '',
                                 scale: 1,
                                 rotation: 0,
                                 isDragging: false,
                                 startX: 0,
                                 startY: 0,
                                 x: 0,
                                 y: 0,
                                 openViewer(url, label) {
                                     this.activeUrl = url;
                                     this.activeLabel = label;
                                     this.resetTransform();
                                     this.lightboxOpen = true;
                                 },
                                 resetTransform() {
                                     this.scale = 1;
                                     this.rotation = 0;
                                     this.x = 0;
                                     this.y = 0;
                                 }
                             }"
                             @keydown.escape.window="if (lightboxOpen) lightboxOpen = false">
                            <div class="flex items-center justify-between gap-2 pb-2.5 mb-3 border-b border-base-200">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-photo class="w-4 h-4 text-primary" />
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70">Dokumentasi Foto</h4>
                                </div>
                                <span class="text-[11px] text-base-content/50">Klik foto untuk pratinjau layar penuh (Zoom & Putar)</span>
                            </div>

                            <div class="grid grid-cols-1 {{ $isBB ? 'sm:grid-cols-2' : 'sm:grid-cols-3' }} gap-4">
                                @foreach($galleryItems as $gItem)
                                    <div class="flex flex-col rounded-xl border border-base-300 overflow-hidden bg-base-200/30">
                                        <div class="px-3 py-2 bg-base-200/60 border-b border-base-300 flex items-center justify-between">
                                            <span class="text-xs font-bold text-base-content/80">{{ $gItem['label'] }}</span>
                                            @if($gItem['hasPhoto'])
                                                <x-heroicon-s-check-circle class="w-4 h-4 text-success" />
                                            @else
                                                <x-heroicon-s-x-circle class="w-4 h-4 text-error/70" />
                                            @endif
                                        </div>
                                        @if($gItem['hasPhoto'])
                                            <button type="button"
                                                    @click="openViewer('{{ $gItem['url'] }}', '{{ $gItem['label'] }}')"
                                                    class="group relative block h-40 w-full bg-base-300 overflow-hidden focus:outline-none cursor-zoom-in">
                                                <img src="{{ $gItem['url'] }}" alt="{{ $gItem['label'] }}" loading="lazy" class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <span class="badge badge-sm bg-base-100/95 text-base-content border-0 font-semibold gap-1.5 py-2.5 px-3 shadow-md">
                                                        <x-heroicon-s-eye class="w-3.5 h-3.5 text-primary" /> Lihat Foto
                                                    </span>
                                                </div>
                                            </button>
                                        @else
                                            <div class="h-40 flex flex-col items-center justify-center text-base-content/40 p-4 text-center">
                                                @if($gItem['icon'] === 'document')
                                                    <x-heroicon-o-document class="w-8 h-8 mb-1.5 opacity-60" />
                                                @else
                                                    <x-heroicon-o-photo class="w-8 h-8 mb-1.5 opacity-60" />
                                                @endif
                                                <span class="text-xs font-medium">Belum Ada {{ $gItem['label'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Fullscreen Interactive Lightbox Overlay --}}
                            <div x-show="lightboxOpen"
                                 x-transition.opacity.duration.200ms
                                 style="display: none;"
                                 class="fixed inset-0 z-[100000] bg-black/90 backdrop-blur-sm flex flex-col select-none">
                                {{-- Top Control Bar --}}
                                <div class="px-4 py-3 bg-base-100/10 border-b border-white/10 flex flex-wrap items-center justify-between gap-3 text-white shrink-0">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="font-bold text-sm md:text-base truncate" x-text="activeLabel"></span>
                                        <span class="text-xs text-white/60 hidden sm:inline">• {{ $detailData['customer_name'] }}</span>
                                    </div>

                                    {{-- Quick Switcher Antar Foto --}}
                                    <div class="flex items-center gap-1.5 bg-white/10 p-1 rounded-xl">
                                        @foreach($galleryItems as $gSwitch)
                                            @if($gSwitch['hasPhoto'])
                                                <button type="button"
                                                        @click="openViewer('{{ $gSwitch['url'] }}', '{{ $gSwitch['label'] }}')"
                                                        class="btn btn-xs rounded-lg border-0 transition-all"
                                                        :class="activeLabel === '{{ $gSwitch['label'] }}' ? 'btn-primary text-primary-content shadow' : 'btn-ghost text-white/80 hover:text-white hover:bg-white/10'">
                                                    {{ $gSwitch['label'] }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>

                                    {{-- Zoom, Rotate & Close Controls --}}
                                    <div class="flex items-center gap-1">
                                        <button type="button" class="btn btn-xs btn-ghost text-white hover:bg-white/20 btn-square" @click="scale = Math.max(0.25, scale - 0.25)" title="Perkecil">
                                            <x-heroicon-s-minus class="w-4 h-4" />
                                        </button>
                                        <span class="text-xs font-mono w-12 text-center" x-text="Math.round(scale * 100) + '%'"></span>
                                        <button type="button" class="btn btn-xs btn-ghost text-white hover:bg-white/20 btn-square" @click="scale = Math.min(5, scale + 0.25)" title="Perbesar">
                                            <x-heroicon-s-plus class="w-4 h-4" />
                                        </button>
                                        <div class="w-px h-4 bg-white/20 mx-1"></div>
                                        <button type="button" class="btn btn-xs btn-ghost text-white hover:bg-white/20 btn-square" @click="rotation -= 90" title="Putar Kiri">
                                            <x-heroicon-s-arrow-uturn-left class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="btn btn-xs btn-ghost text-white hover:bg-white/20 btn-square" @click="rotation += 90" title="Putar Kanan">
                                            <x-heroicon-s-arrow-uturn-right class="w-4 h-4" />
                                        </button>
                                        <button type="button" class="btn btn-xs btn-ghost text-warning hover:bg-white/20 px-2" @click="resetTransform()" title="Reset Posisi">
                                            Reset
                                        </button>
                                        <div class="w-px h-4 bg-white/20 mx-1"></div>
                                        <button type="button" class="btn btn-sm btn-circle btn-error text-white ml-1" @click="lightboxOpen = false" title="Tutup Pratinjau (ESC)">
                                            ✕
                                        </button>
                                    </div>
                                </div>

                                {{-- Interactive Image Viewport --}}
                                <div class="flex-1 overflow-hidden relative flex items-center justify-center p-4"
                                     @mousedown="isDragging = true; startX = $event.clientX - x; startY = $event.clientY - y"
                                     @mousemove="if (isDragging) { x = $event.clientX - startX; y = $event.clientY - startY }"
                                     @mouseup="isDragging = false"
                                     @mouseleave="isDragging = false"
                                     @wheel.prevent="scale = Math.min(Math.max(0.25, scale + ($event.deltaY < 0 ? 0.2 : -0.2)), 5)"
                                     @click.self="lightboxOpen = false">
                                    <img :src="activeUrl"
                                         :alt="activeLabel"
                                         draggable="false"
                                         class="max-w-full max-h-full object-contain rounded-lg shadow-2xl cursor-grab active:cursor-grabbing transition-transform duration-75"
                                         :style="`transform: translate(${x}px, ${y}px) scale(${scale}) rotate(${rotation}deg);`" />
                                </div>

                                {{-- Bottom Helper Hint --}}
                                <div class="py-2 px-4 text-center text-[11px] text-white/60 bg-black/40">
                                    Gunakan <strong class="text-white/90">Scroll Mouse</strong> untuk Zoom, <strong class="text-white/90">Klik & Geser</strong> untuk menggerakkan foto, atau klik area gelap untuk menutup.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Footer Modal --}}
                    <div class="px-5 py-3.5 bg-base-200/60 border-t border-base-300 flex items-center justify-between gap-2 shrink-0">
                        <div>
                            @if($canEdit && !$isBB)
                                <button type="button"
                                        class="btn btn-sm btn-success btn-outline gap-1.5"
                                        wire:click="$set('isDetailModalOpen', false); openApprovalModal('{{ $detailData['customer_code'] }}', '{{ $detailData['kuartal'] }}', '{{ $detailData['distributor_code'] }}', '{{ addslashes($detailData['customer_name']) }}')">
                                    <x-heroicon-s-check-circle class="w-4 h-4" /> Approval SKB
                                </button>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-primary px-6" wire:click="$set('isDetailModalOpen', false)">Tutup</button>
                    </div>
                @endif
            </div>
            <form method="dialog" class="modal-backdrop bg-black/60">
                <button wire:click="$set('isDetailModalOpen', false)">Tutup</button>
            </form>
        </dialog>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-16">No</th>
                        <th class="w-16 text-center" title="Kuartal">Q</th>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Supervisor</th>
                        <th>Kode Dist</th>
                        <th>Distributor</th>
                        <th>Customer PRC</th>
                        <th>Kode Cust</th>
                        <th>Customer</th>
                        <th>Alamat</th>
                        <th>Total Target</th>
                        <th>Reward</th>
                        <th>PIC</th>
                        <th>Status SKB</th>
                        <th>Status Data</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $key => $item)
                    <tr wire:key="row-{{ $item->customer_code }}-{{ $item->kuartal }}" class="hover:bg-base-200/50 transition-colors">
                        <th>{{ $data->firstItem() + $key }}</th>
                        <td class="text-center">{{ $item->kuartal }}</td>
                        <td>{{ $item->region_name }}</td>
                        <td>{{ $item->area_name }}</td>
                        <td class="max-w-[100px] truncate" title="{{ $item->supervisor_name }}">{{ $item->supervisor_name }}</td>
                        <td>{{ $item->distributor_code }}</td>
                        <td class="max-w-[100px] truncate text-xs" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</td>
                        <td>{{ $item->customer_prc ?? '-' }}</td>
                        <td>{{ $item->customer_code }}</td>
                        <td class="max-w-[150px] truncate" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                        <td class="max-w-[130px] truncate text-xs" title="{{ $item->alamat }}">{{ $item->alamat }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($item->total_target, 0, ',', '.') }}</td>
                        <td class="text-center font-bold">
                            @if(($item->jenis_reward ?? '') === 'BB')
                                1 KTN
                            @elseif(($item->jenis_reward ?? '') === 'CB')
                                {{ rtrim(rtrim(number_format($item->reward_percent * 100, 2, ',', '.'), '0'), ',') }}%
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center font-bold">{{ $item->pic }}</td>
                        <td class="text-center">
                            @if(($item->jenis_reward ?? '') !== 'BB' && !empty($item->status_skb))
                            <div class="flex flex-col items-center justify-center gap-1">
                                @if($item->status_skb == 'Sudah')
                                    @if($item->is_approved === true)
                                        <span class="badge badge-sm badge-success">Approved</span>
                                    @elseif($item->is_approved === false)
                                        <span class="badge badge-sm badge-error">Rejected</span>
                                    @else
                                        <span class="badge badge-sm badge-warning">Pending</span>
                                    @endif
                                @else
                                    <span class="badge badge-sm badge-outline badge-error">{{ $item->status_skb }}</span>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(($item->jenis_reward ?? '') !== 'BB' && !empty($item->status_data_lengkap))
                            <button type="button" wire:click="showStatusData('{{ $item->customer_code }}')" class="badge badge-sm font-bold border cursor-pointer hover:scale-105 transition-transform {{ $item->status_data_lengkap === 'Lengkap' ? 'badge-success badge-outline' : 'badge-error badge-outline' }}">
                                {{ $item->status_data_lengkap }}
                            </button>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <div class="tooltip tooltip-left" data-tip="Detail Toko" wire:loading.class="opacity-50 pointer-events-none" wire:target="showDetail('{{ $item->customer_code }}', '{{ $item->kuartal }}')">
                                    <x-ui.action-button 
                                        type="default" 
                                        icon="eye" 
                                        label="" 
                                        class="btn-ghost text-info hover:bg-info/10 btn-square" 
                                        title="Detail" 
                                        wire:click="showDetail('{{ $item->customer_code }}', '{{ $item->kuartal }}')"
                                    />
                                </div>
                                @if($canEdit)
                                    @if(($item->jenis_reward ?? '') !== 'BB')
                                    <div class="tooltip tooltip-left" data-tip="Approval SKB" wire:loading.class="opacity-50 pointer-events-none" wire:target="openApprovalModal('{{ $item->customer_code }}', '{{ $item->kuartal }}', '{{ $item->distributor_code }}', '{{ addslashes($item->customer_name) }}')">
                                        <x-ui.action-button 
                                            type="default" 
                                            icon="check" 
                                            label="" 
                                            class="btn-ghost text-success hover:bg-success/10 btn-square" 
                                            title="Approval SKB" 
                                            wire:click="openApprovalModal('{{ $item->customer_code }}', '{{ $item->kuartal }}', '{{ $item->distributor_code }}', '{{ addslashes($item->customer_name) }}')"
                                        />
                                    </div>
                                    @endif
                                    <div class="tooltip tooltip-left" data-tip="Edit Data" wire:loading.class="opacity-50 pointer-events-none" wire:target="editData('{{ $item->customer_code }}', '{{ $item->kuartal }}')">
                                        <x-ui.action-button 
                                            type="edit" 
                                            class="btn-square" 
                                            title="Edit" 
                                            wire:click="editData('{{ $item->customer_code }}', '{{ $item->kuartal }}')"
                                        />
                                    </div>
                                @endif
                                @if($canDelete)
                                <div class="tooltip tooltip-left" data-tip="Hapus Data" wire:loading.class="opacity-50 pointer-events-none" wire:target="deleteData('{{ $item->customer_code }}', '{{ $item->kuartal }}')">
                                    <x-ui.action-button 
                                        type="delete" 
                                        class="btn-square" 
                                        title="Hapus" 
                                        wire:click="deleteData('{{ $item->customer_code }}', '{{ $item->kuartal }}')"
                                    />
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="17" class="text-center py-6 text-base-content/50">Tidak ada data ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
            {{ $data->links() }}
        </div>

        {{-- Approval Modal --}}
        <dialog id="approval_modal" class="modal z-[99999] {{ $isApprovalModalOpen ? 'modal-open' : '' }}">
            <div class="modal-box w-11/12 max-w-2xl relative">
                <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4" wire:click="$set('isApprovalModalOpen', false)">✕</button>
                <h3 class="font-bold text-lg mb-4 pr-8">Input Data & Approval SKB</h3>
                <p class="text-sm text-base-content/70 mb-4 border-b pb-2">Toko: <span class="font-bold text-base-content">{{ $approvalCustomerName }}</span> ({{ $approvalCustomerCode }}) - Kuartal {{ $approvalKuartal }}</p>
                
                @if($approvalError)
                    <div class="alert alert-error mb-4 shadow-sm text-sm p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ $approvalError }}</span>
                    </div>
                @endif
                
                <form wire:submit.prevent="submitApproval">
                    <div class="form-control w-full mb-4">
                        <label class="label"><span class="label-text font-semibold">Foto SKB</span></label>
                        <input type="file" class="file-input file-input-bordered w-full" wire:model="fotoSkb" accept="image/*" />
                        @error('fotoSkb') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="fotoSkb" class="text-sm text-info mt-1">Mengunggah foto...</div>
                        
                        @if($fotoSkb)
                            <div class="mt-3 p-3 rounded-xl border border-success/30 bg-success/5">
                                <div class="text-xs font-semibold text-success mb-2 flex items-center gap-1.5">
                                    <x-heroicon-s-check-circle class="w-4 h-4" />
                                    Pratinjau Foto SKB Baru (Siap Diunggah)
                                </div>
                                <img src="{{ $fotoSkb->temporaryUrl() }}" alt="Preview Foto SKB Baru" class="max-h-56 w-auto mx-auto rounded-lg object-contain border border-base-300 shadow-sm bg-base-200" />
                            </div>
                        @elseif($existingFotoSkb)
                            <div class="mt-3 p-3 rounded-xl border border-base-300 bg-base-200/40">
                                <div class="text-xs font-semibold text-base-content/70 mb-2">Foto SKB Saat Ini:</div>
                                <img src="{{ Storage::url($existingFotoSkb) }}" alt="Foto SKB Saat Ini" class="max-h-56 w-auto mx-auto rounded-lg object-contain border border-base-300 shadow-sm bg-base-200" />
                            </div>
                        @endif
                    </div>
                    
                    <div class="form-control w-full mb-4">
                        <label class="label"><span class="label-text font-semibold">Status SKB</span></label>
                        <div class="flex gap-4 items-center">
                            <label class="cursor-pointer label justify-start gap-2">
                                <input type="radio" wire:model.live="approvalStatus" value="approve" class="radio radio-success" />
                                <span class="label-text">Approve</span>
                            </label>
                            <label class="cursor-pointer label justify-start gap-2">
                                <input type="radio" wire:model.live="approvalStatus" value="reject" class="radio radio-error" />
                                <span class="label-text">Reject</span>
                            </label>
                        </div>
                        @error('approvalStatus') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    @if($approvalStatus === 'reject')
                    <div class="form-control w-full mb-4">
                        <label class="label"><span class="label-text">Alasan Penolakan <span class="text-error">*</span></span></label>
                        <textarea class="textarea textarea-bordered w-full h-24" wire:model="rejectReason" placeholder="Tulis alasan mengapa ditolak..."></textarea>
                        @error('rejectReason') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    @endif
                    
                    <div class="modal-action mt-6 flex justify-between">
                        <button type="button" class="btn btn-ghost" wire:click="$set('isApprovalModalOpen', false)">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submitApproval, fotoSkb">
                            <span wire:loading.remove wire:target="submitApproval">Simpan Data</span>
                            <span wire:loading wire:target="submitApproval" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop bg-black/60">
                <button wire:click="$set('isApprovalModalOpen', false)">Tutup</button>
            </form>
        </dialog>

    {{-- Status Data Modal --}}
    <dialog id="status_data_modal" class="modal z-[99999] {{ $isStatusModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-2xl bg-base-100 rounded-xl relative">
            <h3 class="font-bold text-lg mb-4 text-primary flex items-center gap-2">
                <x-heroicon-s-clipboard-document-check class="w-6 h-6" />
                Status Kelengkapan Data Toko
            </h3>
            <button class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4" wire:click="$set('isStatusModalOpen', false)">✕</button>
            
            @if($activeStatusData)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 mt-4">
                    @php
                        $fields = [
                            'no_hp' => 'No HP',
                            'nama_pemilik_toko' => 'Nama Pemilik Toko',
                            // 'nik_ktp' => 'NIK KTP',
                            // 'nama_ktp' => 'Nama KTP',
                            // 'foto_ktp' => 'Foto KTP',
                            // 'nama_bank' => 'Nama Bank',
                            // 'no_rekening' => 'No Rekening',
                            // 'nama_pemilik_norek' => 'Nama Pemilik Norek',
                            'latitude' => 'Latitude',
                            'longitude' => 'Longitude',
                            'foto_toko2' => 'Foto Depan',
                            'foto_toko3' => 'Foto Dalam',
                        ];
                    @endphp

                    @foreach($fields as $key => $label)
                        <div class="flex items-center justify-between border-b border-base-200 pb-2">
                            <span class="text-sm font-semibold">{{ $label }}</span>
                            @if(isset($activeStatusData[$key]) && trim($activeStatusData[$key]) !== '')
                                <x-heroicon-s-check-circle class="w-5 h-5 text-success" title="Lengkap" />
                            @else
                                <x-heroicon-s-x-circle class="w-5 h-5 text-error" title="Belum Lengkap" />
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <div class="modal-action mt-6">
                    <button type="button" class="btn btn-primary" wire:click="$set('isStatusModalOpen', false)">Tutup</button>
                </div>
            @else
                <div class="py-10 text-center text-base-content/50">
                    <p>Memuat data...</p>
                </div>
            @endif
        </div>
        <form method="dialog" class="modal-backdrop bg-black/60">
            <button wire:click="$set('isStatusModalOpen', false)">Tutup</button>
        </form>
    </dialog>
</div>
