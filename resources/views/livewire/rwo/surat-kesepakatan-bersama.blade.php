<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Kelola Surat Kesepakatan Bersama RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi RWO</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Surat Kesepakatan Bersama</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Cek Plan Kunjungan</a>
        </div>
    </div>

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- Total Toko --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Toko SKB</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-users class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($kpi['total_toko'], 0, ',', '.') }}</div>
        </div>

        {{-- Total Approve --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Approve</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ number_format($kpi['total_approve'], 0, ',', '.') }}</div>
        </div>

        {{-- Total Reject --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Reject</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ number_format($kpi['total_reject'], 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Toolbar Card --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data SKB</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar persetujuan SKB RWO</p>
            </div>

            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/50" />
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari kode/nama..." class="input input-sm input-bordered w-full pl-9 focus:input-primary" />
                </div>
                <div class="join bg-base-100 rounded-xl">
                    <select wire:model.live="statusApproval" class="select select-sm select-bordered join-item font-semibold focus:outline-none w-auto max-w-[130px] lg:max-w-none text-xs lg:text-sm">
                        <option value="">Semua Status</option>
                        <option value="approve">Approved</option>
                        <option value="reject">Rejected</option>
                    </select>
                </div>
                
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <x-ui.action-button type="filter" wire:click="$dispatch('open-modal', 'filter_modal')" onclick="filter_modal.showModal()" class="relative shrink-0">
                        @if($appliedKuartal || $appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor)
                            <div class="badge badge-primary badge-xs absolute -top-1 -right-1"></div>
                        @endif
                    </x-ui.action-button>
                    @if($canImport)
                        <x-ui.action-button type="import" wire:click="$set('isImportModalOpen', true)" onclick="import_modal.showModal()" />
                    @endif
                    @if($canExport)
                        <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                        <x-ui.action-button type="export" wire:click="exportData" />
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12">No</th>
                        <th>Kuartal</th>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Kode Dist</th>
                        <th>Nama Distributor</th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th class="text-center">Foto SKB</th>
                        <th class="text-center">Status</th>
                        <th>Alasan Penolakan</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $key => $item)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <th>{{ $data->firstItem() + $key }}</th>
                        <td class="font-medium">{{ $item->kuartal }}</td>
                        <td>{{ $item->region_name }}</td>
                        <td>{{ $item->area_name }}</td>
                        <td class="font-mono">{{ $item->distributor_code }}</td>
                        <td class="max-w-[150px] truncate" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</td>
                        <td class="font-mono">{{ $item->customer_code }}</td>
                        <td class="font-bold max-w-[200px] truncate" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                        <td class="text-center">
                            @if($item->foto_skb)
                                <button type="button" wire:click="previewPhoto('{{ Storage::url($item->foto_skb) }}')" class="btn btn-xs btn-ghost text-info" title="Lihat Foto">
                                    <x-heroicon-s-photo class="w-4 h-4 mr-1" />
                                    Lihat
                                </button>
                            @else
                                <span class="text-base-content/50 text-xs italic">Kosong</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->is_approved === true)
                                <span class="badge badge-sm badge-outline badge-success">Approved</span>
                            @elseif($item->is_approved === false)
                                <span class="badge badge-sm badge-outline badge-error">Rejected</span>
                            @else
                                <span class="badge badge-sm badge-outline badge-warning">Pending</span>
                            @endif
                        </td>
                        <td class="max-w-[200px] truncate text-xs text-error" title="{{ $item->reason }}">{{ $item->reason ?? '-' }}</td>
                        <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                            <div class="flex items-center justify-center gap-1">
                                @if($canEdit)
                                <div wire:loading.class="opacity-50 pointer-events-none" wire:target="editData('{{ $item->id }}')">
                                    <x-ui.action-button 
                                        type="edit" 
                                        class="btn-square" 
                                        title="Edit/Approval" 
                                        wire:click="editData('{{ $item->id }}')"
                                    />
                                </div>
                                @endif
                                @if($canDelete)
                                <div wire:loading.class="opacity-50 pointer-events-none" wire:target="confirmDelete('{{ $item->id }}')">
                                    <x-ui.action-button 
                                        type="delete" 
                                        class="btn-square" 
                                        title="Hapus" 
                                        wire:click="confirmDelete('{{ $item->id }}', '{{ addslashes($item->customer_name) }}', '{{ $item->customer_code }}')"
                                    />
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-8 text-base-content/50">
                            <div class="flex flex-col items-center justify-center">
                                <x-heroicon-s-inbox class="w-12 h-12 mb-2 text-base-300" />
                                <p>Tidak ada data surat kesepakatan bersama ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($data->hasPages())
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
            {{ $data->links() }}
        </div>
        @endif
    </div>

    {{-- Filter Modal --}}
    <dialog id="filter_modal" class="modal" wire:ignore.self>
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Filter Pencarian</h3>
            <div class="grid grid-cols-1 gap-4">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text">Kuartal</span></label>
                    <select wire:model.live="kuartal" class="select select-bordered w-full">
                        <option value="">-- Semua Kuartal --</option>
                        @foreach($kuartals as $q)
                            <option value="{{ $q->quarter }}">{{ current(explode('_', $q->quarter)) }}</option>
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

    {{-- Delete Modal --}}
    <dialog id="delete_modal" class="modal {{ $isDeleteModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-error mb-4">Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus Surat Kesepakatan Bersama untuk toko <strong>{{ $deleteCustomerName }}</strong> ({{ $deleteCustomerCode }})?</p>
            <p class="text-sm text-base-content/60 mt-2">Tindakan ini akan menghapus data tersebut secara permanen dari database.</p>
            
            <div class="modal-action mt-6">
                <button class="btn btn-ghost" wire:click="$set('isDeleteModalOpen', false)">Batal</button>
                <button class="btn btn-error" wire:click="destroyData" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="destroyData">Ya, Hapus Data</span>
                    <span wire:loading wire:target="destroyData" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </dialog>

    {{-- Edit / Approval Modal --}}
    <dialog id="edit_modal" class="modal {{ $isEditModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Edit Data & Approval SKB</h3>
            <p class="text-sm text-base-content/70 mb-4 border-b pb-2">Toko: <span class="font-bold text-base-content">{{ $approvalCustomerName }}</span> ({{ $approvalCustomerCode }}) - Kuartal {{ $approvalKuartal }}</p>
            
            @if($approvalError)
                <div class="alert alert-error mb-4 shadow-sm text-sm p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ $approvalError }}</span>
                </div>
            @endif
            
            <form wire:submit.prevent="submitApproval">
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text">Ubah Foto SKB (Opsional)</span></label>
                    <input type="file" class="file-input file-input-bordered w-full" wire:model="fotoSkb" accept="image/*" />
                    @error('fotoSkb') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="fotoSkb" class="text-sm text-info mt-1">Mengunggah foto...</div>
                    
                    @if($fotoSkb)
                        <div class="mt-2 text-sm text-success">Foto baru siap disimpan.</div>
                    @elseif($existingFotoSkb)
                        <div class="mt-2">
                            <a href="{{ Storage::url($existingFotoSkb) }}" target="_blank" class="text-info text-sm underline hover:text-info-focus">Lihat Foto SKB Saat Ini</a>
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
                    <button type="button" class="btn btn-ghost" wire:click="$set('isEditModalOpen', false)">Batal</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submitApproval, fotoSkb">
                        <span wire:loading.remove wire:target="submitApproval">Simpan Perubahan</span>
                        <span wire:loading wire:target="submitApproval" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Preview Photo Modal --}}
    <dialog id="preview_modal" class="modal {{ $isPreviewModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box max-w-3xl p-0 overflow-hidden bg-base-100 rounded-xl relative">
            <div class="p-4 border-b border-base-200 flex justify-between items-center bg-base-100/90 backdrop-blur sticky top-0 z-10">
                <h3 class="font-bold text-lg">Preview Foto SKB</h3>
                <button class="btn btn-sm btn-circle btn-ghost" wire:click="closePreview">✕</button>
            </div>
            <div class="p-4 flex justify-center bg-base-200/50 min-h-[40vh] items-center">
                @if($previewPhotoUrl)
                    <img src="{{ $previewPhotoUrl }}" alt="Foto SKB" class="max-w-full max-h-[70vh] object-contain rounded shadow-sm" />
                @else
                    <div class="flex flex-col items-center justify-center text-base-content/50">
                        <span class="loading loading-spinner loading-md mb-2"></span>
                        <p>Memuat foto...</p>
                    </div>
                @endif
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-black/60">
            <button wire:click="closePreview">Tutup</button>
        </form>
    </dialog>

    {{-- Import Modal --}}
    <dialog id="import_modal" class="modal {{ $isImportModalOpen ? 'modal-open' : '' }}" wire:ignore.self>
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Import Data SKB</h3>
            
            @if(!$importResult)
            <div class="alert alert-info shadow-sm text-sm mb-4">
                <x-heroicon-s-information-circle class="w-5 h-5" />
                <span>Format file harus sama persis dengan template export. Status Approval dapat diisi <strong>Approve</strong> atau <strong>Reject</strong>.</span>
            </div>
            
            <form wire:submit.prevent="importData">
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text">File Excel (.xlsx / .xls)</span></label>
                    <input type="file" wire:model="fileImport" class="file-input file-input-bordered file-input-primary w-full" accept=".xlsx,.xls" />
                    @error('fileImport') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-between items-center mt-6">
                    <button type="button" class="btn btn-sm btn-ghost text-primary" wire:click="downloadTemplate" wire:loading.attr="disabled">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-1" /> Download Template
                    </button>
                    
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-ghost" wire:click="$set('isImportModalOpen', false)" onclick="import_modal.close()">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="importData, fileImport">
                            <span wire:loading.remove wire:target="importData">Proses Import</span>
                            <span wire:loading wire:target="importData" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </div>
            </form>
            @else
            <div>
                @if(isset($importResult['success']) && $importResult['success'] === false)
                    <div class="alert alert-error shadow-sm mb-4">
                        <x-heroicon-s-x-circle class="w-5 h-5" />
                        <span>{{ $importResult['message'] }}</span>
                    </div>
                @else
                    <div class="alert alert-success shadow-sm mb-4">
                        <x-heroicon-s-check-circle class="w-5 h-5" />
                        <span>Berhasil memproses file Excel.</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm">
                        <div class="bg-base-200 p-3 rounded-lg border border-base-300">
                            <div class="text-base-content/70">Data Dibuat (Baru)</div>
                            <div class="font-bold text-lg">{{ $importResult['count'] ?? 0 }}</div>
                        </div>
                        <div class="bg-base-200 p-3 rounded-lg border border-base-300">
                            <div class="text-base-content/70">Data Diupdate</div>
                            <div class="font-bold text-lg">{{ count($importResult['updated'] ?? []) }}</div>
                        </div>
                    </div>

                    @if(count($importResult['errors'] ?? []) > 0)
                        <div class="mt-4">
                            <h4 class="font-semibold text-error mb-2">Terdapat {{ count($importResult['errors']) }} Error:</h4>
                            <div class="bg-base-200 p-3 rounded-lg max-h-40 overflow-y-auto text-xs text-error font-mono space-y-1">
                                @foreach($importResult['errors'] as $err)
                                    <div>- {{ $err }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if(count($importResult['updated'] ?? []) > 0)
                        <div class="mt-4">
                            <h4 class="font-semibold text-success mb-2">Riwayat Update:</h4>
                            <div class="bg-base-200 p-3 rounded-lg max-h-40 overflow-y-auto text-xs text-success font-mono space-y-1">
                                @foreach($importResult['updated'] as $upd)
                                    <div>- {{ $upd }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
                
                <div class="modal-action mt-6">
                    <button class="btn btn-primary w-full" wire:click="resetImport; $set('isImportModalOpen', false)" onclick="import_modal.close()">Tutup & Refresh Tabel</button>
                </div>
            </div>
            @endif
        </div>
        <form method="dialog" class="modal-backdrop">
            <button wire:click="resetImport; $set('isImportModalOpen', false)">Tutup</button>
        </form>
    </dialog>
</div>
