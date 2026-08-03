<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Kelola Surat Kesepakatan Bersama RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.dashboard') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Dashboard</a>
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Pencapaian</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>SKB</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Plan Kunjungan</a>
            <a href="{{ route('rwo.monitoring-pareto') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Monitoring Visit</a>
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
                        <th class="text-center">Status Lapangan</th>
                        <th>Alasan Penolakan</th>
                        <th class="text-center">Status HO</th>
                        <th>Catatan HO</th>
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
                        <td class="max-w-[150px] truncate text-xs text-error" title="{{ $item->reason }}">{{ $item->reason ?? '-' }}</td>
                        <td class="text-center">
                            @if($item->ho_is_valid === true)
                                <span class="badge badge-sm badge-outline badge-success">Diterima</span>
                            @elseif($item->ho_is_valid === false)
                                <span class="badge badge-sm badge-outline badge-error">Ditolak</span>
                            @else
                                <span class="badge badge-sm badge-outline badge-warning">Belum Dicek</span>
                            @endif
                        </td>
                        <td class="max-w-[150px] truncate text-xs" title="{{ $item->ho_notes }}">{{ $item->ho_notes ?? '-' }}</td>
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
        <div class="modal-box w-11/12 max-w-6xl p-0 overflow-hidden flex flex-col h-[90vh]">
            {{-- Header Modal --}}
            <div class="p-4 border-b border-base-200 flex justify-between items-center bg-base-100 shrink-0">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="font-bold text-lg">Validasi SKB HO</h3>
                        <div class="hidden sm:flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold border transition-colors duration-300" 
                             :class="{
                                 'bg-success/10 text-success border-success/30': $wire.hoIsValid === 'valid',
                                 'bg-error/10 text-error border-error/30': $wire.hoIsValid === 'invalid',
                                 'bg-warning/10 text-warning-content border-warning/30': $wire.hoIsValid === ''
                             }"
                             x-data>
                            <template x-if="$wire.hoIsValid === 'valid'">
                                <div class="flex items-center gap-1"><x-heroicon-s-check-circle class="w-3 h-3"/> DITERIMA</div>
                            </template>
                            <template x-if="$wire.hoIsValid === 'invalid'">
                                <div class="flex items-center gap-1"><x-heroicon-s-x-circle class="w-3 h-3"/> DITOLAK</div>
                            </template>
                            <template x-if="$wire.hoIsValid === ''">
                                <div class="flex items-center gap-1"><x-heroicon-s-clock class="w-3 h-3"/> BELUM DICEK</div>
                            </template>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/70 mt-0.5">Toko: <span class="font-bold text-base-content">{{ $approvalCustomerName }}</span> ({{ $approvalCustomerCode }}) - Kuartal {{ $approvalKuartal }}</p>
                </div>
                <button type="button" class="btn btn-sm btn-circle btn-ghost" wire:click="$set('isEditModalOpen', false)">✕</button>
            </div>

            <div class="flex flex-col md:flex-row flex-1 min-h-0 overflow-hidden">
                {{-- Kiri: Image Viewer --}}
                <div class="w-full md:w-1/2 lg:w-7/12 bg-base-200 border-r border-base-300 relative flex flex-col"
                     x-data="{ scale: 1, rotation: 0, isDragging: false, startX: 0, startY: 0, x: 0, y: 0 }">
                    
                    {{-- Toolbar Image --}}
                    <div class="absolute top-2 left-2 right-2 z-10 flex justify-between items-center gap-2 bg-base-100/90 backdrop-blur rounded-lg shadow-sm p-1">
                        <div class="flex items-center">
                            <button type="button" class="btn btn-sm btn-ghost btn-square" @click="scale = Math.max(0.2, scale - 0.2)" title="Zoom Out">
                                <x-heroicon-s-minus class="w-4 h-4" />
                            </button>
                            <span class="text-xs font-mono w-12 text-center" x-text="Math.round(scale * 100) + '%'"></span>
                            <button type="button" class="btn btn-sm btn-ghost btn-square" @click="scale = Math.min(5, scale + 0.2)" title="Zoom In">
                                <x-heroicon-s-plus class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" class="btn btn-sm btn-ghost btn-square" @click="rotation -= 90" title="Putar Kiri">
                                <x-heroicon-s-arrow-uturn-left class="w-4 h-4" />
                            </button>
                            <button type="button" class="btn btn-sm btn-ghost btn-square" @click="rotation += 90" title="Putar Kanan">
                                <x-heroicon-s-arrow-uturn-right class="w-4 h-4" />
                            </button>
                            <div class="w-px h-4 bg-base-300 mx-1"></div>
                            <button type="button" class="btn btn-sm btn-ghost btn-square text-error" @click="scale = 1; rotation = 0; x = 0; y = 0" title="Reset">
                                <x-heroicon-s-arrow-path class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    {{-- Image Area --}}
                    <div class="flex-1 overflow-hidden flex items-center justify-center cursor-move"
                         @mousedown="isDragging = true; startX = $event.clientX - x; startY = $event.clientY - y"
                         @mousemove="if(isDragging) { x = $event.clientX - startX; y = $event.clientY - startY }"
                         @mouseup="isDragging = false"
                         @mouseleave="isDragging = false"
                         @wheel.prevent="scale = Math.min(Math.max(0.2, scale + $event.deltaY * -0.002), 5)">
                        
                        @if($existingFotoSkb || $fotoSkb)
                            <img src="{{ $fotoSkb ? $fotoSkb->temporaryUrl() : Storage::url($existingFotoSkb) }}" 
                                 class="max-w-full max-h-full object-contain transition-transform duration-75 select-none"
                                 :style="`transform: translate(${x}px, ${y}px) scale(${scale}) rotate(${rotation}deg);`"
                                 draggable="false"
                                 alt="Foto SKB" />
                        @else
                            <div class="text-center text-base-content/40 flex flex-col items-center">
                                <x-heroicon-o-photo class="w-16 h-16 mb-2 opacity-50" />
                                <p>Foto SKB belum diunggah</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Kanan: Form --}}
                <div class="w-full md:w-1/2 lg:w-5/12 bg-base-100 flex flex-col" x-data="{ unlockShopApproval: false }">
                    <form wire:submit.prevent="submitApproval" class="flex flex-col h-full">
                        <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                            
                            @if($approvalError)
                                <div class="alert alert-error shadow-sm text-sm p-3">
                                    <x-heroicon-s-x-circle class="w-5 h-5 shrink-0" />
                                    <span>{{ $approvalError }}</span>
                                </div>
                            @endif
                            
                            {{-- Section: Referensi Master Data --}}
                            <div class="rounded-xl p-4 relative transition-all duration-300" 
                                 :class="editMaster ? 'bg-base-100 border-2 border-primary shadow-lg shadow-primary/10' : 'bg-base-200/40 border border-base-200'"
                                 x-data="{ editMaster: false }">
                                <div class="flex justify-between items-start mb-4 border-b border-base-200/60 pb-2 transition-colors" :class="editMaster ? 'border-primary/20' : ''">
                                    <h4 class="font-bold text-base-content/80 flex items-center gap-2 text-sm transition-colors" :class="editMaster ? 'text-primary' : ''">
                                        <x-heroicon-s-information-circle class="w-4 h-4 transition-colors" x-bind:class="editMaster ? 'text-primary' : 'text-info'" />
                                        Referensi Master Data Toko
                                    </h4>
                                    @if($masterData)
                                        <label class="swap swap-rotate btn btn-xs btn-ghost hover:bg-base-200/50" :class="editMaster ? 'text-primary' : 'text-base-content/50'">
                                            <input type="checkbox" x-model="editMaster" />
                                            <div class="swap-off flex items-center gap-1 font-medium"><x-heroicon-s-pencil-square class="w-3 h-3" /> Edit</div>
                                            <div class="swap-on flex items-center gap-1 font-medium"><x-heroicon-s-x-mark class="w-3 h-3" /> Batal</div>
                                        </label>
                                    @endif
                                </div>
                                
                                @if($masterData)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                                        
                                        <!-- Kode & Pemilik Toko -->
                                        <div>
                                            <label class="block text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-1">Toko & Pemilik</label>
                                            <div x-show="!editMaster" x-transition.opacity.duration.300ms class="font-medium text-base-content">
                                                <span class="text-base-content/50 mr-1 text-xs">{{ $masterData['customer_code'] }}</span><br/>
                                                @if($masterData['nama_pemilik_toko'] || $masterData['customer_name'])
                                                    {{ $masterData['nama_pemilik_toko'] ?: $masterData['customer_name'] }}
                                                @else
                                                    <span class="text-base-content/30 italic font-normal">Belum ada data</span>
                                                @endif
                                            </div>
                                            <div x-show="editMaster" x-cloak x-transition.opacity.duration.300ms class="mt-1">
                                                <input type="text" wire:model="masterNamaPemilik" class="input input-sm input-bordered w-full focus:input-primary transition-colors" placeholder="Nama Pemilik Toko" />
                                                <div class="text-[10px] text-base-content/50 mt-1">Kode Toko: {{ $masterData['customer_code'] }}</div>
                                            </div>
                                        </div>

                                        <!-- No HP -->
                                        <div>
                                            <label class="block text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-1">Nomor HP</label>
                                            <div x-show="!editMaster" x-transition.opacity.duration.300ms class="font-medium text-base-content flex items-center gap-1 h-full">
                                                @if($masterData['no_hp'])
                                                    {{ $masterData['no_hp'] }}
                                                @else
                                                    <span class="text-base-content/30 italic font-normal">Belum ada data</span>
                                                @endif
                                            </div>
                                            <div x-show="editMaster" x-cloak x-transition.opacity.duration.300ms class="mt-1">
                                                <input type="text" wire:model="masterNoHp" class="input input-sm input-bordered w-full focus:input-primary transition-colors" placeholder="Nomor HP" />
                                            </div>
                                        </div>

                                        <!-- Identitas (KTP) -->
                                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 p-3 rounded-lg border shadow-sm transition-all" :class="editMaster ? 'border-primary/30 bg-primary/5' : 'bg-base-100/50 border-base-200/60'">
                                            <div>
                                                <label class="block text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-1 flex items-center gap-1">NIK KTP <x-heroicon-s-question-mark-circle class="w-3 h-3 text-base-content/30" title="16 Digit Nomor KTP"/></label>
                                                <div x-show="!editMaster" x-transition.opacity.duration.300ms class="font-mono font-medium text-base-content">
                                                    @if($masterData['nik_ktp'])
                                                        {{ $masterData['nik_ktp'] }}
                                                    @else
                                                        <span class="text-base-content/30 italic font-normal font-sans">Belum ada data</span>
                                                    @endif
                                                </div>
                                                <div x-show="editMaster" x-cloak x-transition.opacity.duration.300ms class="mt-1">
                                                    <input type="text" wire:model="masterNikKtp" class="input input-sm input-bordered w-full font-mono focus:input-primary transition-colors" placeholder="Nomor Induk Kependudukan" />
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-1">Nama di KTP</label>
                                                <div x-show="!editMaster" x-transition.opacity.duration.300ms class="font-medium text-base-content">
                                                    @if($masterData['nama_ktp'])
                                                        {{ $masterData['nama_ktp'] }}
                                                    @else
                                                        <span class="text-base-content/30 italic font-normal">Belum ada data</span>
                                                    @endif
                                                </div>
                                                <div x-show="editMaster" x-cloak x-transition.opacity.duration.300ms class="mt-1">
                                                    <input type="text" wire:model="masterNamaKtp" class="input input-sm input-bordered w-full focus:input-primary transition-colors" placeholder="Nama Sesuai KTP" />
                                                </div>
                                            </div>

                                            <!-- Foto KTP -->
                                            <div class="md:col-span-2">
                                                <label class="block text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-1">Foto KTP</label>
                                                <div x-show="!editMaster" x-transition.opacity.duration.300ms class="font-medium text-base-content">
                                                    @if($existingMasterFotoKtp)
                                                        <div class="cursor-pointer border border-base-300 rounded-lg p-1 inline-block hover:border-primary hover:shadow-md transition-all mt-1" @click="if(typeof $dispatch !== 'undefined') $dispatch('open-preview', '{{ Storage::url($existingMasterFotoKtp) }}')">
                                                            <img src="{{ Storage::url($existingMasterFotoKtp) }}" class="h-16 w-24 object-cover rounded" alt="Foto KTP" />
                                                        </div>
                                                    @else
                                                        <span class="text-base-content/30 italic font-normal">Belum ada foto</span>
                                                    @endif
                                                </div>
                                                <div x-show="editMaster" x-cloak x-transition.opacity.duration.300ms class="mt-1 flex items-center gap-3">
                                                    @if($masterFotoKtp)
                                                        <img src="{{ $masterFotoKtp->temporaryUrl() }}" class="h-16 w-24 object-cover rounded border" />
                                                    @elseif($existingMasterFotoKtp)
                                                        <img src="{{ Storage::url($existingMasterFotoKtp) }}" class="h-16 w-24 object-cover rounded border opacity-50" />
                                                    @endif
                                                    <div class="flex-1">
                                                        <input type="file" wire:model="masterFotoKtp" class="file-input file-input-sm file-input-bordered w-full focus:file-input-primary transition-colors" accept="image/jpeg,image/png,image/jpg" />
                                                        <div wire:loading wire:target="masterFotoKtp" class="text-xs text-info mt-1 font-medium"><span class="loading loading-spinner loading-xs align-middle"></span> Mengunggah foto...</div>
                                                        @error('masterFotoKtp') <span class="text-error text-xs block mt-1">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Rekening -->
                                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 p-3 rounded-lg border shadow-sm transition-all" :class="editMaster ? 'border-primary/30 bg-primary/5' : 'bg-base-100/50 border-base-200/60'">
                                            <div class="md:col-span-2">
                                                <label class="block text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-1">Informasi Bank</label>
                                                <div x-show="!editMaster" x-transition.opacity.duration.300ms class="font-medium text-base-content flex items-center gap-2">
                                                    @if($masterData['nama_bank'] || $masterData['no_rekening'])
                                                        @if($masterData['nama_bank'])
                                                            <span class="badge badge-sm badge-neutral">{{ $masterData['nama_bank'] }}</span>
                                                        @endif
                                                        <span class="font-mono font-bold">{{ $masterData['no_rekening'] ?: '-' }}</span>
                                                    @else
                                                        <span class="text-base-content/30 italic font-normal">Belum ada data</span>
                                                    @endif
                                                </div>
                                                <div x-show="editMaster" x-cloak x-transition.opacity.duration.300ms class="flex gap-2 mt-1">
                                                    <input list="list_bank_indo" type="text" wire:model="masterNamaBank" class="input input-sm input-bordered w-1/3 focus:input-primary transition-colors" placeholder="Bank (e.g. BRI)" />
                                                    <datalist id="list_bank_indo">
                                                        <option value="BCA">
                                                        <option value="BRI">
                                                        <option value="Mandiri">
                                                        <option value="BNI">
                                                        <option value="BSI">
                                                        <option value="BJB">
                                                        <option value="CIMB Niaga">
                                                        <option value="Permata">
                                                        <option value="Danamon">
                                                        <option value="BTPN">
                                                        <option value="BTN">
                                                        <option value="Maybank">
                                                        <option value="Mega">
                                                        <option value="Panin">
                                                    </datalist>
                                                    <input type="text" wire:model="masterNoRekening" class="input input-sm input-bordered w-2/3 font-mono focus:input-primary transition-colors" placeholder="No Rekening" />
                                                </div>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-[10px] font-bold text-base-content/50 uppercase tracking-wider mb-1">Atas Nama Rekening</label>
                                                <div x-show="!editMaster" x-transition.opacity.duration.300ms class="font-medium text-base-content">
                                                    @if($masterData['nama_pemilik_norek'])
                                                        {{ $masterData['nama_pemilik_norek'] }}
                                                    @else
                                                        <span class="text-base-content/30 italic font-normal">Belum ada data</span>
                                                    @endif
                                                </div>
                                                <div x-show="editMaster" x-cloak x-transition.opacity.duration.300ms class="mt-1">
                                                    <input type="text" wire:model="masterPemilikRekening" class="input input-sm input-bordered w-full focus:input-primary transition-colors" placeholder="Atas Nama Pemilik Rekening" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="md:col-span-2 mt-0 text-right" x-show="editMaster" x-cloak x-transition.opacity.duration.300ms>
                                            <span class="text-[10px] text-primary italic font-medium flex items-center justify-end gap-1"><x-heroicon-m-check-circle class="w-3 h-3"/> Perubahan akan disimpan saat menekan "Simpan Validasi"</span>
                                        </div>

                                    </div>
                                @else
                                    <div class="alert alert-warning shadow-sm text-sm py-2">
                                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 shrink-0" />
                                        <span>Data master belum dilengkapi untuk toko ini.</span>
                                    </div>
                                @endif
                            </div>

                            <div class="divider text-xs text-base-content/30 my-0"></div>

                            {{-- Section: Validasi HO (Primary) --}}
                            <div class="bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-primary flex items-center gap-2 mb-4 border-b border-primary/10 pb-2">
                                    <x-heroicon-s-check-badge class="w-5 h-5" />
                                    Validasi Head Office
                                </h4>

                                <div class="form-control w-full mb-4">
                                    <label class="label pt-0"><span class="label-text font-semibold">Status Validasi</span></label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        {{-- Valid / Diterima --}}
                                        <label class="relative cursor-pointer transition-all duration-200" 
                                               :class="$wire.hoIsValid === 'valid' ? 'scale-[1.02]' : 'hover:scale-[1.01] opacity-70 hover:opacity-100'"
                                               x-data>
                                            <input type="radio" wire:model.live="hoIsValid" value="valid" class="peer sr-only" />
                                            <div class="flex items-center gap-2 px-3 py-3 rounded-xl border-2 transition-all duration-200"
                                                 :class="$wire.hoIsValid === 'valid' ? 'bg-success/10 border-success text-success shadow-sm shadow-success/20' : 'bg-base-100 border-base-200 text-base-content/70'">
                                                <x-heroicon-s-check-circle class="w-5 h-5 shrink-0 transition-transform duration-200" x-bind:class="$wire.hoIsValid === 'valid' ? 'scale-110' : ''" />
                                                <span class="text-sm font-bold">Diterima & Valid</span>
                                            </div>
                                        </label>

                                        {{-- Invalid / Ditolak --}}
                                        <label class="relative cursor-pointer transition-all duration-200" 
                                               :class="$wire.hoIsValid === 'invalid' ? 'scale-[1.02]' : 'hover:scale-[1.01] opacity-70 hover:opacity-100'"
                                               x-data>
                                            <input type="radio" wire:model.live="hoIsValid" value="invalid" class="peer sr-only" />
                                            <div class="flex items-center gap-2 px-3 py-3 rounded-xl border-2 transition-all duration-200"
                                                 :class="$wire.hoIsValid === 'invalid' ? 'bg-error/10 border-error text-error shadow-sm shadow-error/20' : 'bg-base-100 border-base-200 text-base-content/70'">
                                                <x-heroicon-s-x-circle class="w-5 h-5 shrink-0 transition-transform duration-200" x-bind:class="$wire.hoIsValid === 'invalid' ? 'scale-110' : ''" />
                                                <span class="text-sm font-bold">Ditolak / Invalid</span>
                                            </div>
                                        </label>

                                        {{-- Belum Dicek --}}
                                        <label class="relative cursor-pointer transition-all duration-200" 
                                               :class="$wire.hoIsValid === '' ? 'scale-[1.02]' : 'hover:scale-[1.01] opacity-70 hover:opacity-100'"
                                               x-data>
                                            <input type="radio" wire:model.live="hoIsValid" value="" class="peer sr-only" />
                                            <div class="flex items-center gap-2 px-3 py-3 rounded-xl border-2 transition-all duration-200"
                                                 :class="$wire.hoIsValid === '' ? 'bg-warning/10 border-warning text-warning-content shadow-sm shadow-warning/20' : 'bg-base-100 border-base-200 text-base-content/70'">
                                                <x-heroicon-s-clock class="w-5 h-5 shrink-0 transition-transform duration-200" x-bind:class="$wire.hoIsValid === '' ? 'scale-110' : ''" />
                                                <span class="text-sm font-bold">Belum Dicek</span>
                                            </div>
                                        </label>
                                    </div>
                                    @error('hoIsValid') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-control w-full">
                                    <label class="label"><span class="label-text font-semibold">Catatan HO</span></label>
                                    <textarea class="textarea textarea-bordered w-full h-24 focus:textarea-primary" wire:model="hoNotes" placeholder="Tuliskan catatan atau instruksi jika ada..."></textarea>
                                    @error('hoNotes') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="divider text-xs text-base-content/40">Data dari Lapangan</div>

                            {{-- Section: Data Lapangan (Locked by default) --}}
                            <div class="relative rounded-xl border transition-all duration-300" :class="unlockShopApproval ? 'border-base-300 p-4 bg-base-100' : 'border-dashed border-base-300 p-4 bg-base-200/50 opacity-80 grayscale-[20%]'">
                                
                                {{-- Lock Overlay / Toggle --}}
                                <div class="absolute -top-3.5 right-4 z-10">
                                    <label class="swap swap-rotate btn btn-xs btn-outline bg-base-100 shadow-sm transition-colors duration-300" :class="unlockShopApproval ? 'btn-error' : 'btn-ghost'">
                                        <input type="checkbox" x-model="unlockShopApproval" />
                                        <div class="swap-on flex items-center gap-1"><x-heroicon-s-lock-open class="w-3 h-3" /> Kunci</div>
                                        <div class="swap-off flex items-center gap-1"><x-heroicon-s-lock-closed class="w-3 h-3" /> Edit Lapangan</div>
                                    </label>
                                </div>

                                <fieldset :disabled="!unlockShopApproval">
                                    <div class="form-control w-full mb-3">
                                        <label class="label"><span class="label-text font-semibold">Status Persetujuan Toko</span></label>
                                        <div class="flex gap-4 items-center">
                                            <label class="cursor-pointer label justify-start gap-2">
                                                <input type="radio" wire:model.live="approvalStatus" value="approve" class="radio radio-sm radio-success" />
                                                <span class="label-text">Approve</span>
                                            </label>
                                            <label class="cursor-pointer label justify-start gap-2">
                                                <input type="radio" wire:model.live="approvalStatus" value="reject" class="radio radio-sm radio-error" />
                                                <span class="label-text">Reject</span>
                                            </label>
                                        </div>
                                        @error('approvalStatus') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    @if($approvalStatus === 'reject')
                                    <div class="form-control w-full mb-3">
                                        <label class="label"><span class="label-text">Alasan Penolakan Toko</span></label>
                                        <textarea class="textarea textarea-bordered textarea-sm w-full h-16" wire:model="rejectReason" placeholder="Alasan ditolak..."></textarea>
                                        @error('rejectReason') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    @endif

                                    <div class="form-control w-full">
                                        <label class="label"><span class="label-text">Ganti Foto SKB</span></label>
                                        <input type="file" class="file-input file-input-bordered file-input-sm w-full" wire:model="fotoSkb" accept="image/*" />
                                        @error('fotoSkb') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                                        <div wire:loading wire:target="fotoSkb" class="text-sm text-info mt-1">Mengunggah foto...</div>
                                    </div>
                                </fieldset>
                            </div>

                        </div>
                        
                        {{-- Footer Form --}}
                        <div class="p-4 border-t border-base-200 bg-base-100/90 backdrop-blur-md sticky bottom-0 z-20 shrink-0 flex justify-end gap-2">
                            <button type="button" class="btn btn-ghost" wire:click="$set('isEditModalOpen', false)">Batal</button>
                            <button type="submit" class="btn btn-primary shadow-lg shadow-primary/20" wire:loading.attr="disabled" wire:target="submitApproval, fotoSkb">
                                <span wire:loading.remove wire:target="submitApproval">Simpan Validasi</span>
                                <span wire:loading wire:target="submitApproval" class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </dialog>

    {{-- Preview Photo Modal --}}
    <dialog id="preview_modal" class="modal {{ $isPreviewModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box max-w-4xl p-0 overflow-hidden bg-base-100 rounded-xl relative" 
             x-data="{ scale: 1, isDragging: false, startX: 0, startY: 0, x: 0, y: 0 }">
            <div class="p-3 border-b border-base-200 flex justify-between items-center bg-base-100/90 backdrop-blur sticky top-0 z-50">
                <h3 class="font-bold text-lg px-2">Preview Foto SKB</h3>
                <div class="flex items-center gap-1">
                    <button class="btn btn-sm btn-ghost btn-circle" @click="scale = Math.max(0.5, scale - 0.25)" title="Zoom Out">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button class="btn btn-sm btn-ghost text-xs font-mono" @click="scale = 1; x = 0; y = 0" title="Reset Zoom" style="width: 60px;">
                        <span x-text="Math.round(scale * 100) + '%'"></span>
                    </button>
                    <button class="btn btn-sm btn-ghost btn-circle" @click="scale = Math.min(5, scale + 0.25)" title="Zoom In">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div class="w-px h-5 bg-base-300 mx-1"></div>
                    <button class="btn btn-sm btn-circle btn-ghost" wire:click="closePreview" @click="scale = 1; x = 0; y = 0">✕</button>
                </div>
            </div>
            <div class="p-0 m-0 flex justify-center bg-base-200/80 min-h-[50vh] items-center relative overflow-hidden" 
                 @mousedown="isDragging = true; startX = $event.clientX - x; startY = $event.clientY - y"
                 @mousemove="if(isDragging) { x = $event.clientX - startX; y = $event.clientY - startY }"
                 @mouseup="isDragging = false"
                 @mouseleave="isDragging = false"
                 @wheel.prevent="scale = Math.min(Math.max(0.5, scale + $event.deltaY * -0.002), 5)">
                @if($previewPhotoUrl)
                    <img src="{{ $previewPhotoUrl }}" alt="Foto SKB" 
                         class="max-w-full max-h-[75vh] object-contain shadow-sm transition-transform duration-75 select-none" 
                         :class="isDragging ? 'cursor-grabbing' : (scale > 1 ? 'cursor-grab' : 'cursor-zoom-in')"
                         :style="`transform: translate(${x}px, ${y}px) scale(${scale});`"
                         @dblclick="scale = scale > 1 ? 1 : 2; x = 0; y = 0"
                         draggable="false" />
                @else
                    <div class="flex flex-col items-center justify-center text-base-content/50 py-10">
                        <span class="loading loading-spinner loading-md mb-2"></span>
                        <p>Memuat foto...</p>
                    </div>
                @endif
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-black/60">
            <button wire:click="closePreview" @click="scale = 1; x = 0; y = 0">Tutup</button>
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
