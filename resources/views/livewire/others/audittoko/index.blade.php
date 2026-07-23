<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
    x-data="{ 
        showApproveModal: false,
        showRejectModal: false, 
        showDetailModal: false, 
        showExportModal: false,
        showEditModal: false,
        detailData: null, 
        photoUrl: '', 
        showPhotoModal: false 
    }"
    x-on:open-approve-modal.window="showApproveModal = true"
    x-on:close-approve-modal.window="showApproveModal = false"
    x-on:open-reject-modal.window="showRejectModal = true"
    x-on:close-reject-modal.window="showRejectModal = false"
    x-on:open-export-modal.window="showExportModal = true"
    x-on:close-export-modal.window="showExportModal = false"
    x-on:open-edit-modal.window="showEditModal = true"
    x-on:close-edit-modal.window="showEditModal = false"
>
    <x-slot name="title">Approval Audit Toko</x-slot>

    {{-- 5 KPI Cards Section (Tetap Statis di Atas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- Total Audit --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Audit</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-document-text class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($this->kpiData['total']) }}</div>
        </div>

        {{-- Menunggu Approval (Pending) --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-warning uppercase tracking-wider truncate pr-2 mt-1">Menunggu Approval</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-clock class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">{{ number_format($this->kpiData['pending']) }}</div>
        </div>

        {{-- Disetujui (Approved) --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-success uppercase tracking-wider truncate pr-2 mt-1">Disetujui</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ number_format($this->kpiData['approved']) }}</div>
        </div>

        {{-- Ditolak (Rejected) --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-error uppercase tracking-wider truncate pr-2 mt-1">Ditolak</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ number_format($this->kpiData['rejected']) }}</div>
        </div>

        {{-- Rate Approval --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-info/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-info uppercase tracking-wider truncate pr-2 mt-1">Rate Disetujui</h3>
                <div class="w-8 h-8 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-chart-bar class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-info">{{ $this->kpiData['rate'] }}%</div>
        </div>
    </div>

    {{-- Main Card (Tabel Utama) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Approval Audit Toko</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar laporan penginputan audit oleh auditor</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative grow sm:grow-0">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari toko, kode, auditor..." 
                        class="input input-sm input-bordered rounded-xl w-full sm:w-64 bg-base-100 pl-9"
                    />
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/40" />
                </div>
                
                {{-- Filter Status Approval --}}
                <select wire:model.live="statusFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Status Approval</option>
                    <option value="Pending">🟡 Pending (Menunggu)</option>
                    <option value="Approved">🟢 Approved (Disetujui)</option>
                    <option value="Rejected">🔴 Rejected (Ditolak)</option>
                </select>

                {{-- Filter Region --}}
                @if(count($regions) > 1)
                <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Region</option>
                    @foreach($regions as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Export Button --}}
                <div class="flex items-center gap-1 md:gap-2">
                    <button wire:click="openExportModal" class="btn btn-sm btn-success text-white rounded-xl gap-2 font-bold shadow-sm">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Tanggal Audit</th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th>Distributor / Cabang</th>
                        <th>Auditor</th>
                        <th class="text-center">Checklist</th>
                        <th class="text-center">Status Approval</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($reports as $index => $row)
                        @php
                            $verifiedCount = collect([
                                $row->is_toko_fisik,
                                $row->is_nama_pemilik,
                                $row->is_nama_ktp,
                                $row->is_nik_ktp,
                                $row->is_no_hp,
                                $row->is_no_rekening,
                                $row->is_an_rekening,
                                $row->is_titik_koordinat,
                            ])->filter()->count();
                            
                            $status = $row->status_approval ?? 'Pending';
                        @endphp
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th class="text-center">{{ $reports->firstItem() + $index }}</th>
                            <td class="font-mono text-xs">{{ $row->created_at ? date('d M Y H:i', strtotime($row->created_at)) : '-' }}</td>
                            <td><span class="badge badge-sm badge-ghost font-mono font-bold">{{ $row->customer_code }}</span></td>
                            <td class="font-bold text-base-content">{{ $row->customer_name }}</td>
                            <td class="text-xs">{{ $row->distributor_name ?? '-' }} <span class="text-base-content/50">({{ $row->cabang ?? '-' }})</span></td>
                            <td class="font-semibold text-xs">{{ $row->auditor }}</td>
                            <td class="text-center">
                                <span class="badge badge-sm font-bold {{ $verifiedCount === 8 ? 'badge-success text-white' : 'badge-warning' }}">
                                    {{ $verifiedCount }}/8 Sesuai
                                </span>
                            </td>
                            <td class="text-center">
                                @if($status === 'Approved')
                                    <span class="badge badge-sm badge-success text-white font-extrabold gap-1">
                                        <x-heroicon-s-check-circle class="w-3 h-3" /> Disetujui
                                    </span>
                                @elseif($status === 'Rejected')
                                    <span class="badge badge-sm badge-error text-white font-extrabold gap-1" title="{{ $row->alasan_reject }}">
                                        <x-heroicon-s-x-circle class="w-3 h-3" /> Ditolak
                                    </span>
                                @else
                                    <span class="badge badge-sm badge-warning font-extrabold gap-1">
                                        <x-heroicon-s-clock class="w-3 h-3" /> Pending
                                    </span>
                                @endif
                            </td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Detail Button --}}
                                    <button 
                                        type="button"
                                        x-on:click="detailData = {{ json_encode($row) }}; showDetailModal = true;"
                                        class="btn btn-square btn-xs btn-ghost text-info hover:bg-info/10"
                                        title="Detail Audit Toko"
                                    >
                                        <x-heroicon-s-eye class="w-4 h-4" />
                                    </button>

                                    {{-- Approve & Reject & Edit Action --}}
                                    @canEdit('others.audit-toko')
                                        {{-- Edit Button --}}
                                        @if($status !== 'Approved')
                                        <button 
                                            type="button"
                                            wire:click="edit({{ $row->id }})"
                                            class="btn btn-square btn-xs btn-ghost text-blue-500 hover:bg-blue-500/10"
                                            title="Edit Data"
                                        >
                                            <x-heroicon-s-pencil-square class="w-4 h-4" />
                                        </button>
                                        @else
                                        <div class="tooltip tooltip-left" data-tip="Harus di-reject dulu untuk edit">
                                            <button 
                                                type="button"
                                                class="btn btn-square btn-xs btn-ghost text-slate-300 cursor-not-allowed"
                                            >
                                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                            </button>
                                        </div>
                                        @endif

                                        {{-- Approve Button --}}
                                        @if($status !== 'Approved')
                                        <button 
                                            type="button"
                                            wire:click="openApproveModal({{ $row->id }})"
                                            class="btn btn-square btn-xs btn-ghost text-success hover:bg-success/10"
                                            title="Approve (Setujui)"
                                        >
                                            <x-heroicon-s-check class="w-4 h-4" />
                                        </button>
                                        @endif

                                        {{-- Reject Button --}}
                                        @if($status !== 'Rejected')
                                        <button 
                                            type="button"
                                            wire:click="openRejectModal({{ $row->id }})"
                                            class="btn btn-square btn-xs btn-ghost text-error hover:bg-error/10"
                                            title="Reject (Tolak)"
                                        >
                                            <x-heroicon-s-x-mark class="w-4 h-4" />
                                        </button>
                                        @endif
                                    @endcanEdit

                                    {{-- Delete Button --}}
                                    @canDelete('others.audit-toko')
                                        <button 
                                            type="button"
                                            wire:click="delete({{ $row->id }})"
                                            wire:confirm="Apakah Anda yakin ingin MENGHAPUS data audit toko ini secara permanen?"
                                            class="btn btn-square btn-xs btn-ghost text-red-500 hover:bg-red-500/10"
                                            title="Hapus (Delete)"
                                        >
                                            <x-heroicon-s-trash class="w-4 h-4" />
                                        </button>
                                    @endcanDelete
                                </div>
                            </th>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-base-content/50">
                                <x-heroicon-s-document-text class="w-12 h-12 mx-auto mb-2 opacity-30" />
                                Tidak ada data hasil audit toko ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            <div class="text-base-content/60 text-center sm:text-left">
                Menampilkan <span class="font-bold text-base-content">{{ $reports->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-base-content">{{ $reports->lastItem() ?? 0 }}</span> dari <span class="font-bold text-base-content">{{ $reports->total() }}</span> entri
            </div>
            <div>
                {{ $reports->links() }}
            </div>
        </div>

    </div>

    {{-- MODAL EXPORT FILTER --}}
    <div 
        x-show="showExportModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200"
    >
        <div class="bg-base-100 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-base-300 flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-base-200 pb-3">
                <h3 class="font-extrabold text-base flex items-center gap-2">
                    <x-heroicon-s-funnel class="w-5 h-5 text-success" />
                    Filter Export Data
                </h3>
                <button type="button" @click="showExportModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>

            <div class="flex flex-col gap-3 max-h-[60vh] overflow-y-auto pr-2">
                <div class="form-control">
                    <label class="label text-xs font-bold">Rentang Tanggal</label>
                    <div class="flex items-center gap-2">
                        <input type="date" wire:model.defer="exportDateStart" class="input input-bordered input-sm w-full rounded-xl text-xs">
                        <span class="text-xs">s/d</span>
                        <input type="date" wire:model.defer="exportDateEnd" class="input input-bordered input-sm w-full rounded-xl text-xs">
                    </div>
                </div>

                <div class="form-control">
                    <label class="label text-xs font-bold">Status Approval</label>
                    <select wire:model.defer="exportStatusFilter" class="select select-bordered select-sm rounded-xl w-full text-xs">
                        <option value="">Semua Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <div class="form-control border rounded-xl p-3 bg-base-200/30">
                    <label class="label cursor-pointer justify-start gap-3 pb-2 border-b">
                        <input type="checkbox" wire:model.live="selectAllExportDistributors" class="checkbox checkbox-primary checkbox-sm rounded-md">
                        <span class="label-text font-bold text-xs">Pilih Semua Distributor</span>
                    </label>
                    
                    <div class="max-h-40 overflow-y-auto mt-2 space-y-1 pr-2">
                        @foreach($distributors as $dist)
                            <label class="label cursor-pointer justify-start gap-3 py-1">
                                <input type="checkbox" wire:model.defer="exportDistributors" value="{{ $dist }}" class="checkbox checkbox-sm rounded-md">
                                <span class="label-text text-xs">{{ $dist }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-2 pt-3 border-t border-base-200">
                <button type="button" @click="showExportModal = false" class="btn btn-sm rounded-xl">Batal</button>
                <button wire:click="exportExcel" @click="showExportModal = false" class="btn btn-sm btn-success text-white rounded-xl font-bold">
                    <x-heroicon-s-document-arrow-down class="w-4 h-4" />
                    Download Excel
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL APPROVE (Catatan Manager Optional) --}}
    <div 
        x-show="showApproveModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
    >
        <div class="bg-base-100 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-base-200 flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-base-200 pb-4">
                <h3 class="font-extrabold text-lg text-success flex items-center gap-2">
                    <div class="p-1.5 bg-success/10 rounded-lg"><x-heroicon-s-check class="w-6 h-6" /></div>
                    Persetujuan Audit Toko
                </h3>
                <button type="button" @click="showApproveModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>

            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menyetujui hasil audit ini? Anda juga dapat menambahkan catatan opsional.
            </p>

            <form wire:submit.prevent="approve" class="flex flex-col gap-4">
                <div class="form-control">
                    <label class="label text-xs font-bold text-base-content/70">Catatan Manager Audit (Opsional)</label>
                    <textarea 
                        wire:model="catatanManager" 
                        rows="3" 
                        placeholder="Tambahkan pesan/catatan (tidak wajib)..." 
                        class="textarea textarea-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white transition-colors"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="showApproveModal = false" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-6 font-bold">Batal</button>
                    <button type="submit" class="btn btn-success text-white rounded-xl px-6 font-bold shadow-sm shadow-success/30">
                        Setujui Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL REJECT ALASAN --}}
    <div 
        x-show="showRejectModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200"
    >
        <div class="bg-base-100 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-base-200 flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-base-200 pb-4">
                <h3 class="font-extrabold text-lg text-error flex items-center gap-2">
                    <div class="p-1.5 bg-error/10 rounded-lg"><x-heroicon-s-x-mark class="w-6 h-6" /></div>
                    Penolakan Audit Toko
                </h3>
                <button type="button" @click="showRejectModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>

            <p class="text-sm text-base-content/70">
                Silakan berikan catatan penolakan secara jelas agar Auditor dapat memperbaiki data/foto dan mengajukan ulang laporan audit ini.
            </p>

            <form wire:submit.prevent="reject" class="flex flex-col gap-4">
                <div class="form-control">
                    <label class="label text-xs font-bold text-base-content/70">Catatan Manager Audit <span class="text-error">*Wajib</span></label>
                    <textarea 
                        wire:model="catatanManager" 
                        rows="3" 
                        placeholder="Contoh: Foto tampak depan buram, NIK KTP tidak sesuai dengan foto KTP..." 
                        class="textarea textarea-bordered rounded-xl text-sm w-full bg-slate-50 focus:bg-white transition-colors"
                    ></textarea>
                    @error('catatanManager') <span class="text-error text-[11px] mt-1.5 font-bold flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" /> {{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="showRejectModal = false" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-6 font-bold">Batal</button>
                    <button type="submit" class="btn btn-error text-white rounded-xl px-6 font-bold shadow-sm shadow-error/30">
                        Simpan Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DETAIL AUDIT (PREMIUM REDESIGN) --}}
    <div 
        x-show="showDetailModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300"
    >
        <div class="bg-base-100 rounded-3xl max-w-5xl w-full max-h-[95vh] shadow-2xl flex flex-col overflow-hidden ring-1 ring-base-200" x-trap="showDetailModal" x-transition.scale.95>
            
            {{-- Header Premium --}}
            <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-base-200 bg-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <x-heroicon-s-building-storefront class="w-6 h-6 sm:w-7 sm:h-7" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="badge badge-sm badge-outline font-mono font-bold text-base-content/60" x-text="detailData?.customer_code"></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full"
                                  :class="{
                                      'bg-emerald-100 text-emerald-700': detailData?.status_approval === 'Approved',
                                      'bg-rose-100 text-rose-700': detailData?.status_approval === 'Rejected',
                                      'bg-amber-100 text-amber-700': !detailData?.status_approval || detailData?.status_approval === 'Pending'
                                  }"
                                  x-text="detailData?.status_approval || 'Pending'">
                            </span>
                        </div>
                        <h3 class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-tight" x-text="detailData?.customer_name"></h3>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:bg-base-200 hover:text-base-content">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Body Content --}}
            <div class="flex-1 overflow-y-auto bg-slate-50 p-5 sm:p-8" x-if="detailData">
                
                {{-- Body Content begins (Rejection alert removed) --}}

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
                    
                    {{-- Left Column: Info & Checklist --}}
                    <div class="lg:col-span-5 flex flex-col gap-6 sm:gap-8">
                        
                        {{-- General Info Widget --}}
                        <div class="bg-white rounded-2xl border border-base-200 p-5 sm:p-6 shadow-sm">
                            <div class="flex flex-col gap-4 sm:gap-5">
                                {{-- Distributor --}}
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <div class="bg-base-100 p-3 rounded-xl shrink-0 border border-base-200 shadow-sm">
                                        <x-heroicon-s-truck class="w-5 h-5 text-slate-500" />
                                    </div>
                                    <p class="font-bold text-slate-800 text-sm sm:text-base" x-text="(detailData?.distributor_name || '-') + ' (' + (detailData?.cabang || '-') + ')'"></p>
                                </div>
                                
                                {{-- Kode Toko - Nama Toko --}}
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <div class="bg-base-100 p-3 rounded-xl shrink-0 border border-base-200 shadow-sm">
                                        <x-heroicon-s-building-storefront class="w-5 h-5 text-slate-500" />
                                    </div>
                                    <p class="font-bold text-slate-800 text-sm sm:text-base" x-text="(detailData?.customer_code || '-') + ' - ' + (detailData?.customer_name || '-')"></p>
                                </div>

                                {{-- Waktu Audit --}}
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <div class="bg-base-100 p-3 rounded-xl shrink-0 border border-base-200 shadow-sm">
                                        <x-heroicon-s-calendar-days class="w-5 h-5 text-slate-500" />
                                    </div>
                                    <p class="font-bold text-slate-800 text-sm sm:text-base font-mono" x-text="detailData?.created_at ? new Date(detailData.created_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-'"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Checklist Widget --}}
                        <div class="bg-white rounded-2xl border border-base-200 p-5 sm:p-6 shadow-sm">
                            <h4 class="text-xs font-bold text-base-content/50 uppercase tracking-wider mb-5 flex items-center justify-between">
                                Hasil Verifikasi 
                                <span class="badge badge-sm badge-neutral" x-text="(
                                    [detailData?.is_toko_fisik, detailData?.is_nama_pemilik, detailData?.is_nama_ktp, detailData?.is_nik_ktp, detailData?.is_no_hp, detailData?.is_no_rekening, detailData?.is_an_rekening, detailData?.is_titik_koordinat].filter(Boolean).length
                                ) + '/8'"></span>
                            </h4>
                            
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                <template x-for="(item, idx) in [
                                    { label: 'Toko Fisik', key: 'is_toko_fisik' },
                                    { label: 'Nama Pemilik', key: 'is_nama_pemilik' },
                                    { label: 'Nama KTP', key: 'is_nama_ktp' },
                                    { label: 'NIK KTP', key: 'is_nik_ktp' },
                                    { label: 'No HP', key: 'is_no_hp' },
                                    { label: 'No Rekening', key: 'is_no_rekening' },
                                    { label: 'A/N Rekening', key: 'is_an_rekening' },
                                    { label: 'Titik Koordinat', key: 'is_titik_koordinat' }
                                ]" :key="idx">
                                    <div class="flex items-center gap-2.5 p-2.5 sm:p-3 rounded-xl border transition-colors shadow-sm"
                                         :class="detailData?.[item.key] ? 'bg-emerald-50/30 border-emerald-100/60' : 'bg-rose-50/30 border-rose-100/60'">
                                        <div class="shrink-0">
                                            <x-heroicon-s-check-circle x-show="detailData?.[item.key]" class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-500" />
                                            <x-heroicon-s-x-circle x-show="!detailData?.[item.key]" class="w-5 h-5 sm:w-6 sm:h-6 text-rose-400" />
                                        </div>
                                        <span class="text-xs sm:text-sm font-bold"
                                              :class="detailData?.[item.key] ? 'text-emerald-900' : 'text-rose-900'"
                                              x-text="item.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Lokasi Audit Widget --}}
                        <div class="bg-white rounded-2xl border border-base-200 p-5 sm:p-6 shadow-sm">
                            <h4 class="text-xs font-bold text-base-content/50 uppercase tracking-wider mb-4">Lokasi Audit</h4>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="bg-blue-100/50 p-2.5 rounded-xl text-blue-600 shrink-0 border border-blue-200/50">
                                        <x-heroicon-s-map-pin class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Titik Koordinat (GPS)</p>
                                        <p class="font-bold text-slate-800 text-sm font-mono truncate" x-text="detailData?.audit_latitude && detailData?.audit_longitude ? detailData.audit_latitude + ', ' + detailData.audit_longitude : 'Tidak direkam'"></p>
                                    </div>
                                </div>
                                <template x-if="detailData?.audit_latitude && detailData?.audit_longitude">
                                    <a :href="'https://www.google.com/maps/search/?api=1&query=' + detailData.audit_latitude + ',' + detailData.audit_longitude" 
                                       target="_blank" 
                                       class="btn btn-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold w-full shadow-sm shadow-blue-500/20 border-0 flex items-center justify-center gap-2 mt-1">
                                        <x-heroicon-s-map class="w-4 h-4" /> Buka di Google Maps
                                    </a>
                                </template>
                            </div>
                        </div>
                        
                    </div>

                    {{-- Right Column: Photos & Notes --}}
                    <div class="lg:col-span-7 flex flex-col gap-6 sm:gap-8">
                        
                        {{-- Photos Widget --}}
                        <div class="bg-white rounded-2xl border border-base-200 p-5 sm:p-6 shadow-sm flex-1">
                            <div class="flex items-center justify-between mb-5">
                                <h4 class="text-xs font-bold text-base-content/50 uppercase tracking-wider flex items-center gap-2">
                                    <x-heroicon-s-photo class="w-4 h-4" /> Dokumentasi Audit
                                </h4>
                                <span class="text-[10px] text-base-content/40 font-bold bg-base-100 px-2 py-1 rounded-md">Klik gambar untuk Zoom</span>
                            </div>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                                <template x-for="i in 8" :key="i">
                                    <div class="relative group aspect-square rounded-2xl overflow-hidden bg-slate-100/50 border border-slate-200 flex flex-col shadow-sm transition-all hover:shadow-md hover:border-primary/30">
                                        {{-- Image Label Overlay --}}
                                        <div class="absolute top-0 left-0 right-0 bg-gradient-to-b from-black/60 to-transparent p-2 sm:p-3 z-10 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                            <span class="text-[10px] sm:text-xs text-white font-bold drop-shadow-md" x-text="'FOTO ' + i"></span>
                                        </div>
                                        
                                        <template x-if="detailData && detailData['foto_audit' + i]">
                                            <div class="w-full h-full cursor-zoom-in relative" @click="photoUrl = '/storage/' + detailData['foto_audit' + i]; showPhotoModal = true;">
                                                <img :src="'/storage/' + detailData['foto_audit' + i]" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                                
                                                {{-- Magnify Icon Overlay --}}
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black/10 transition-opacity">
                                                    <div class="bg-white/90 p-2 rounded-full shadow-lg backdrop-blur-sm">
                                                        <x-heroicon-s-magnifying-glass-plus class="w-5 h-5 text-slate-800" />
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <template x-if="!detailData || !detailData['foto_audit' + i]">
                                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 gap-2">
                                                <div class="p-3 bg-slate-200/50 rounded-full">
                                                    <x-heroicon-s-photo class="w-6 h-6 opacity-40" />
                                                </div>
                                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Kosong</span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Notes Widget --}}
                        <div class="bg-white rounded-2xl border border-base-200 p-5 sm:p-6 shadow-sm">
                            <h4 class="text-xs font-bold text-base-content/50 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <x-heroicon-s-document-text class="w-4 h-4" /> Catatan Auditor
                            </h4>
                            <div class="bg-amber-50/50 border border-amber-100/50 rounded-xl p-4 sm:p-5 relative overflow-hidden">
                                <div class="absolute -right-2 -top-2 opacity-5 text-amber-900">
                                    <x-heroicon-s-chat-bubble-bottom-center-text class="w-24 h-24" />
                                </div>
                                <p class="text-sm font-semibold text-slate-700 italic relative z-10 leading-relaxed" 
                                   x-text="detailData?.keterangan_hasil_audit || 'Tidak ada catatan khusus yang dilampirkan oleh auditor.'"></p>
                            </div>
                        </div>

                        {{-- Manager Notes Widget --}}
                        <div class="bg-white rounded-2xl border border-base-200 p-5 sm:p-6 shadow-sm" x-show="detailData?.status_approval === 'Approved' || detailData?.status_approval === 'Rejected'">
                            <h4 class="text-xs font-bold text-base-content/50 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <x-heroicon-s-clipboard-document-check class="w-4 h-4" /> Catatan Manager Audit
                            </h4>
                            <div class="rounded-xl p-4 sm:p-5 relative overflow-hidden border"
                                 :class="detailData?.status_approval === 'Approved' ? 'bg-emerald-50/50 border-emerald-100/50' : 'bg-rose-50/50 border-rose-100/50'">
                                <div class="absolute -right-2 -top-2 opacity-5"
                                     :class="detailData?.status_approval === 'Approved' ? 'text-emerald-900' : 'text-rose-900'">
                                    <x-heroicon-s-chat-bubble-bottom-center-text class="w-24 h-24" />
                                </div>
                                <p class="text-sm font-semibold text-slate-700 italic relative z-10 leading-relaxed" 
                                   x-text="detailData?.alasan_reject || 'Tidak ada catatan yang dilampirkan oleh Manager.'"></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="px-5 sm:px-8 py-4 sm:py-5 bg-white border-t border-base-200 flex flex-col-reverse sm:flex-row items-center justify-between shrink-0 gap-3">
                <button type="button" @click="showDetailModal = false" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-8 font-bold w-full sm:w-auto">
                    Tutup Detail
                </button>
                
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    @canDelete('others.audit-toko')
                        <button 
                            type="button"
                            @click="if(confirm('Apakah Anda yakin ingin MENGHAPUS data audit toko ini secara permanen?')) { showDetailModal = false; $wire.delete(detailData.id); }"
                            class="btn btn-outline border-error text-error hover:bg-error hover:border-error hover:text-white rounded-xl px-4 gap-2 mr-auto sm:mr-4"
                        >
                            <x-heroicon-s-trash class="w-5 h-5" /> Hapus
                        </button>
                    @endcanDelete

                    @canEdit('others.audit-toko')
                        {{-- Edit Button --}}
                        <template x-if="detailData?.status_approval !== 'Approved'">
                            <button 
                                type="button"
                                @click="showDetailModal = false; $wire.edit(detailData.id);"
                                class="btn btn-info text-white rounded-xl px-6 shadow-sm shadow-info/20 gap-2 hover:scale-[1.03] transition-transform w-full sm:w-auto"
                            >
                                <x-heroicon-s-pencil-square class="w-5 h-5" /> Edit
                            </button>
                        </template>
                        <template x-if="detailData?.status_approval === 'Approved'">
                            <div class="tooltip tooltip-top w-full sm:w-auto" data-tip="Harus di-reject dulu untuk edit">
                                <button 
                                    type="button"
                                    class="btn bg-slate-100 text-slate-400 border-0 rounded-xl px-6 w-full sm:w-auto gap-2 cursor-not-allowed"
                                >
                                    <x-heroicon-s-pencil-square class="w-5 h-5" /> Edit
                                </button>
                            </div>
                        </template>

                        {{-- Reject Button --}}
                        <button 
                            type="button"
                            @click="showDetailModal = false; $wire.openRejectModal(detailData.id);"
                            class="btn btn-error text-white rounded-xl px-6 shadow-sm shadow-error/20 gap-2 hover:scale-[1.03] transition-transform w-full sm:w-auto"
                            x-show="detailData?.status_approval !== 'Rejected'"
                        >
                            <x-heroicon-s-x-mark class="w-5 h-5" /> Tolak
                        </button>
                        <button 
                            type="button"
                            @click="showDetailModal = false; $wire.openApproveModal(detailData.id);"
                            class="btn btn-success text-white rounded-xl px-6 shadow-sm shadow-success/20 gap-2 hover:scale-[1.03] transition-transform w-full sm:w-auto"
                            x-show="detailData?.status_approval !== 'Approved'"
                        >
                            <x-heroicon-s-check class="w-5 h-5" /> Setujui
                        </button>
                    @endcanEdit
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ZOOM FOTO --}}
    <div 
        x-show="showPhotoModal" 
        x-cloak 
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
        @click="showPhotoModal = false"
    >
        <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl bg-base-100 shadow-2xl">
            <img :src="photoUrl" class="max-w-full max-h-[85vh] object-contain" />
            <button type="button" @click="showPhotoModal = false" class="absolute top-3 right-3 btn btn-circle btn-sm bg-black/50 text-white border-0 hover:bg-black/70">✕</button>
        </div>
    </div>

    {{-- MODAL EDIT AUDIT TOKO --}}
    <div 
        x-show="showEditModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            x-show="showEditModal" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95"
            class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden ring-1 ring-slate-900/5"
            @click.outside="showEditModal = false"
        >
            <form wire:submit.prevent="update" class="flex flex-col h-full min-h-0 w-full">
                {{-- Header Edit Modal --}}
                <div class="px-5 sm:px-8 py-4 sm:py-5 bg-gradient-to-r from-info to-blue-500 text-white flex justify-between items-center shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0 backdrop-blur-sm">
                            <x-heroicon-s-pencil-square class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">Edit Data Audit Toko</h3>
                            <p class="text-white/80 text-xs font-medium">Perbarui checklist, koordinat, dan foto hasil audit</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="btn btn-ghost btn-sm btn-circle text-white hover:bg-white/20 transition-colors">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Body Edit Modal --}}
                <div class="p-5 sm:p-8 overflow-y-auto flex-1 bg-slate-50">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Kiri: Checklist Verifikasi --}}
                        <div class="space-y-6">
                            <h4 class="text-md font-bold text-slate-800 border-b pb-2 flex items-center gap-2">
                                <x-heroicon-s-clipboard-document-check class="w-5 h-5 text-primary" />
                                Poin Checklist Verifikasi
                            </h4>
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
                                @php
                                    $checklists = [
                                        'edit_is_toko_fisik' => 'Toko Fisik',
                                        'edit_is_nama_pemilik' => 'Nama Pemilik',
                                        'edit_is_nama_ktp' => 'Nama KTP',
                                        'edit_is_nik_ktp' => 'NIK KTP',
                                        'edit_is_no_hp' => 'Nomor HP',
                                        'edit_is_no_rekening' => 'Nomor Rekening',
                                        'edit_is_an_rekening' => 'A.N Rekening',
                                        'edit_is_titik_koordinat' => 'Titik Koordinat',
                                    ];
                                @endphp

                                @foreach($checklists as $field => $label)
                                    <div class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                                        <span class="font-medium text-slate-700">{{ $label }}</span>
                                        <input type="checkbox" wire:model="{{ $field }}" class="toggle toggle-success" />
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Kanan: Koordinat, Foto, dan Catatan --}}
                        <div class="space-y-6">
                            {{-- Koordinat GPS --}}
                            <h4 class="text-md font-bold text-slate-800 border-b pb-2 flex items-center gap-2">
                                <x-heroicon-s-map-pin class="w-5 h-5 text-error" />
                                Edit Titik Koordinat (Manual)
                            </h4>
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="form-control w-full">
                                    <label class="label"><span class="label-text font-semibold text-slate-700">Latitude</span></label>
                                    <input type="text" wire:model="edit_latitude" class="input input-bordered w-full bg-slate-50 focus:bg-white" placeholder="Contoh: -6.200000" />
                                    @error('edit_latitude') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-control w-full">
                                    <label class="label"><span class="label-text font-semibold text-slate-700">Longitude</span></label>
                                    <input type="text" wire:model="edit_longitude" class="input input-bordered w-full bg-slate-50 focus:bg-white" placeholder="Contoh: 106.816666" />
                                    @error('edit_longitude') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Catatan Auditor --}}
                            <h4 class="text-md font-bold text-slate-800 border-b pb-2 flex items-center gap-2 mt-6">
                                <x-heroicon-s-document-text class="w-5 h-5 text-warning" />
                                Catatan Hasil Audit
                            </h4>
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                                <textarea wire:model="edit_keterangan_hasil_audit" class="textarea textarea-bordered w-full h-24 bg-slate-50 focus:bg-white" placeholder="Ketik catatan hasil audit di sini..."></textarea>
                            </div>

                            {{-- Foto Audit --}}
                            <h4 class="text-md font-bold text-slate-800 border-b pb-2 flex items-center gap-2 mt-6">
                                <x-heroicon-s-camera class="w-5 h-5 text-success" />
                                Foto Hasil Audit (Opsional)
                            </h4>
                            <p class="text-xs text-slate-500">Pilih file baru jika ingin mengganti foto yang lama. Biarkan kosong jika tidak ingin diubah.</p>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                                @for($i = 1; $i <= 8; $i++)
                                    <div class="form-control w-full bg-white p-3 rounded-xl border border-slate-200 shadow-sm relative group">
                                        <label class="label p-0 mb-2 justify-center"><span class="label-text font-semibold text-xs text-slate-600">Foto {{ $i }}</span></label>
                                        
                                        {{-- Image Preview --}}
                                        <div class="w-full h-20 bg-slate-100 rounded-lg flex items-center justify-center mb-3 overflow-hidden border border-slate-200">
                                            @if(!empty($edit_foto_audit[$i]) && method_exists($edit_foto_audit[$i], 'temporaryUrl'))
                                                <img src="{{ $edit_foto_audit[$i]->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif(!empty($existing_foto_audit[$i]))
                                                <img src="{{ asset('storage/' . $existing_foto_audit[$i]) }}" class="w-full h-full object-cover">
                                            @else
                                                <x-heroicon-o-photo class="w-8 h-8 text-slate-300" />
                                            @endif
                                        </div>
                                        
                                        {{-- File Input --}}
                                        <div class="relative overflow-hidden w-full">
                                            <input type="file" wire:model="edit_foto_audit.{{ $i }}" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" />
                                            <button type="button" class="btn btn-xs btn-outline w-full text-[10px]">Pilih Foto</button>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Footer Edit Modal --}}
                <div class="px-5 sm:px-8 py-4 sm:py-5 bg-white border-t border-base-200 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="showEditModal = false" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-8 font-bold w-full sm:w-auto">
                        Batal
                    </button>
                    
                    <button type="submit" class="btn btn-info text-white rounded-xl px-8 shadow-sm shadow-info/20 gap-2 font-bold w-full sm:w-auto" wire:loading.attr="disabled" wire:target="update, edit_foto_audit">
                        <span wire:loading.remove wire:target="update, edit_foto_audit">
                            Simpan
                        </span>
                        <span wire:loading wire:target="update, edit_foto_audit">
                            <span class="loading loading-spinner loading-sm"></span> Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
