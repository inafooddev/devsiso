<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">List Potensi RWO</x-slot>

    {{-- 4 KPI Cards Section (Tetap statis di atas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- KPI 1: Total Toko --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Toko</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-users class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($kpi['total_toko'], 0, ',', '.') }}</div>
        </div>

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
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Sudah SKB</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ number_format($kpi['sudah_skb'], 0, ',', '.') }}</div>
        </div>

        {{-- KPI 4: Belum SKB --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Belum SKB</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ number_format($kpi['belum_skb'], 0, ',', '.') }}</div>
        </div>
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

                {{-- Filter Status SKB --}}
                <select wire:model.live="statusSkb" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Status SKB</option>
                    <option value="Sudah">Sudah SKB</option>
                    <option value="Belum">Belum SKB</option>
                </select>

                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <span onclick="filter_modal.showModal()">
                        <x-ui.action-button type="filter" />
                    </span>
                    <span onclick="import_modal.showModal()">
                        <x-ui.action-button type="import" />
                    </span>
                    <span wire:click="exportData">
                        <x-ui.action-button type="export" />
                    </span>
                </div>
            </div>
        </div>

        {{-- Filter Modal --}}
        <dialog id="filter_modal" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Filter Pencarian</h3>
                <div class="flex flex-col gap-4">
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
        <dialog id="import_modal" class="modal" wire:ignore.self
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
                        Pastikan file Excel Anda memiliki kolom sesuai template yang disediakan.
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
        <dialog id="delete_modal" class="modal {{ $isDeleteModalOpen ? 'modal-open' : '' }}">
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
        </dialog>

        {{-- Edit Modal --}}
        <dialog id="edit_modal" class="modal {{ $isEditModalOpen ? 'modal-open' : '' }}">
            <div class="modal-box w-11/12 max-w-2xl">
                <h3 class="font-bold text-lg mb-4">Edit Data Toko</h3>
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
                    
                    <div class="form-control w-full mb-4" x-data="{ amount: @entangle('editTotalTarget') }">
                        <label class="label"><span class="label-text">Total Target (Rp)</span></label>
                        <input type="number" class="input input-bordered w-full" wire:model="editTotalTarget" x-model="amount" />
                        <span class="text-sm text-success mt-1 font-mono" x-text="amount ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount) : 'Rp 0'"></span>
                        @error('editTotalTarget') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
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
        </dialog>

        {{-- Detail Modal --}}
        <dialog id="detail_modal" class="modal {{ $isDetailModalOpen ? 'modal-open' : '' }}">
            <div class="modal-box w-11/12 max-w-3xl">
                <h3 class="font-bold text-lg mb-4 border-b pb-2">Detail Potensi Toko</h3>
                
                @if($detailData)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div>
                        <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mb-1">Informasi Utama</p>
                        <table class="table table-sm w-full">
                            <tbody>
                                <tr><td class="w-1/3 text-base-content/70">Kuartal</td><td class="font-medium">{{ current(explode('_', $detailData['kuartal'])) ?? '-' }}</td></tr>
                                <tr><td class="text-base-content/70">Kode Toko</td><td class="font-medium">{{ $detailData['customer_code'] }}</td></tr>
                                <tr><td class="text-base-content/70">Nama Toko</td><td class="font-bold text-primary">{{ $detailData['customer_name'] }}</td></tr>
                                <tr><td class="text-base-content/70">PRC Code</td><td class="font-medium">{{ $detailData['customer_prc'] ?? '-' }}</td></tr>
                                <tr><td class="text-base-content/70 align-top">Alamat</td><td class="whitespace-normal leading-tight text-xs">{{ $detailData['alamat'] ?: '-' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div>
                        <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mb-1">Hirarki Area</p>
                        <table class="table table-sm w-full">
                            <tbody>
                                <tr><td class="w-1/3 text-base-content/70">Region</td><td class="font-medium">{{ $detailData['region_name'] }}</td></tr>
                                <tr><td class="text-base-content/70">Area</td><td class="font-medium">{{ $detailData['area_name'] }}</td></tr>
                                <tr><td class="text-base-content/70 flex whitespace-nowrap">Supervisor</td><td class="font-medium text-xs leading-tight">{{ $detailData['supervisor_name'] }}</td></tr>
                                <tr><td class="text-base-content/70">Distributor</td><td class="font-medium text-xs leading-tight">{{ $detailData['distributor_name'] }} ({{ $detailData['distributor_code'] }})</td></tr>
                            </tbody>
                        </table>
                        
                        <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-4 mb-1">Reward & Status</p>
                        <table class="table table-sm w-full">
                            <tbody>
                                <tr><td class="w-1/3 text-base-content/70">Total Target</td><td class="font-mono text-primary font-bold">Rp {{ number_format($detailData['total_target'], 0, ',', '.') }}</td></tr>
                                <tr>
                                    <td class="text-base-content/70">Reward</td>
                                    <td>
                                        @php
                                            $pct = 0.015;
                                            if ($detailData['total_target'] >= 90000000) $pct = 0.025;
                                            elseif ($detailData['total_target'] >= 30000000) $pct = 0.020;
                                        @endphp
                                        <span class="font-bold">{{ rtrim(rtrim(number_format($pct * 100, 2, ',', '.'), '0'), ',') }}%</span>
                                    </td>
                                </tr>
                                <tr><td class="text-base-content/70">Status SKB</td>
                                    <td>
                                        @php $skbColor = $detailData['status_skb'] == 'Sudah' ? 'success' : 'error'; @endphp
                                        <span class="badge badge-sm badge-outline badge-{{ $skbColor }}">{{ $detailData['status_skb'] }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <div class="modal-action mt-6">
                    <button class="btn btn-primary" wire:click="$set('isDetailModalOpen', false)">Tutup</button>
                </div>
            </div>
        </dialog>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-16">No</th>
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
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $key => $item)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <th>{{ $data->firstItem() + $key }}</th>
                        <td>{{ $item->region_name }}</td>
                        <td>{{ $item->area_name }}</td>
                        <td class="max-w-[150px] truncate" title="{{ $item->supervisor_name }}">{{ $item->supervisor_name }}</td>
                        <td>{{ $item->distributor_code }}</td>
                        <td class="max-w-[150px] truncate text-xs" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</td>
                        <td>{{ $item->customer_prc ?? '-' }}</td>
                        <td>{{ $item->customer_code }}</td>
                        <td class="max-w-[150px] truncate" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                        <td class="max-w-[200px] truncate text-xs" title="{{ $item->alamat }}">{{ $item->alamat }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($item->total_target, 0, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ rtrim(rtrim(number_format($item->reward_percent * 100, 2, ',', '.'), '0'), ',') }}%</td>
                        <td class="text-center font-bold">{{ $item->pic }}</td>
                        <td class="text-center">
                            @php 
                                $statusClass = $item->status_skb == 'Sudah' ? 'success' : 'error';
                            @endphp
                            <span class="badge badge-sm badge-outline badge-{{ $statusClass }}">{{ $item->status_skb }}</span>
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <div wire:loading.class="opacity-50 pointer-events-none" wire:target="showDetail('{{ $item->customer_code }}', '{{ $item->kuartal }}')">
                                    <x-ui.action-button 
                                        type="default" 
                                        icon="eye" 
                                        label="" 
                                        class="btn-ghost text-info hover:bg-info/10 btn-square" 
                                        title="Detail" 
                                        wire:click="showDetail('{{ $item->customer_code }}', '{{ $item->kuartal }}')"
                                    />
                                </div>
                                <div wire:loading.class="opacity-50 pointer-events-none" wire:target="editData('{{ $item->customer_code }}', '{{ $item->kuartal }}')">
                                    <x-ui.action-button 
                                        type="edit" 
                                        class="btn-square" 
                                        title="Edit" 
                                        wire:click="editData('{{ $item->customer_code }}', '{{ $item->kuartal }}')"
                                    />
                                </div>
                                <div wire:loading.class="opacity-50 pointer-events-none" wire:target="deleteData('{{ $item->customer_code }}', '{{ $item->kuartal }}')">
                                    <x-ui.action-button 
                                        type="delete" 
                                        class="btn-square" 
                                        title="Hapus" 
                                        wire:click="deleteData('{{ $item->customer_code }}', '{{ $item->kuartal }}')"
                                    />
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center py-4 text-base-content/50">Tidak ada data ditemukan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
            {{ $data->links() }}
        </div>
    </div>
</div>
