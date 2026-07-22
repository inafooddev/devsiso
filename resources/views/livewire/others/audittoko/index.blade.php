<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
    x-data="{ 
        showRejectModal: false, 
        showDetailModal: false, 
        detailData: null, 
        photoUrl: '', 
        showPhotoModal: false 
    }"
    x-on:open-reject-modal.window="showRejectModal = true"
    x-on:close-reject-modal.window="showRejectModal = false"
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
                    <button wire:click="exportExcel" class="btn btn-sm btn-success text-white rounded-xl gap-2 font-bold shadow-sm">
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

                                    {{-- Approve Button --}}
                                    @if($status !== 'Approved')
                                    <button 
                                        type="button"
                                        wire:click="approve({{ $row->id }})"
                                        wire:confirm="Apakah Anda yakin ingin MENSETUJUI hasil audit toko ini?"
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

    {{-- MODAL REJECT ALASAN --}}
    <div 
        x-show="showRejectModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200"
    >
        <div class="bg-base-100 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-base-300 flex flex-col gap-4">
            <div class="flex items-center justify-between border-b border-base-200 pb-3">
                <h3 class="font-extrabold text-base text-error flex items-center gap-2">
                    <x-heroicon-s-x-circle class="w-5 h-5" />
                    Penolakan Hasil Audit Toko
                </h3>
                <button type="button" @click="showRejectModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>

            <p class="text-xs text-base-content/70">
                Silakan berikan alasan penolakan secara jelas agar Auditor dapat memperbaiki data/foto dan mengajukan ulang laporan audit ini.
            </p>

            <form wire:submit.prevent="reject" class="flex flex-col gap-3">
                <div class="form-control">
                    <label class="label text-xs font-bold">Alasan Penolakan (Wajib Diisi)</label>
                    <textarea 
                        wire:model="alasanReject" 
                        rows="3" 
                        placeholder="Contoh: Foto tampak depan buram, NIK KTP tidak sesuai dengan foto KTP..." 
                        class="textarea textarea-bordered rounded-xl text-xs"
                    ></textarea>
                    @error('alasanReject') <span class="text-error text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2 mt-2">
                    <button type="button" @click="showRejectModal = false" class="btn btn-sm rounded-xl">Batal</button>
                    <button type="submit" class="btn btn-sm btn-error text-white rounded-xl font-bold">
                        Simpan Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DETAIL AUDIT --}}
    <div 
        x-show="showDetailModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200"
    >
        <div class="bg-base-100 rounded-2xl max-w-4xl w-full max-h-[90vh] shadow-2xl border border-base-300 flex flex-col overflow-hidden" x-trap="showDetailModal">
            {{-- Modal Header --}}
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <div>
                    <span class="badge badge-sm badge-neutral font-mono font-bold" x-text="detailData?.customer_code"></span>
                    <h3 class="font-extrabold text-base text-base-content mt-0.5" x-text="detailData?.customer_name"></h3>
                </div>
                <button type="button" @click="showDetailModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto flex flex-col gap-5 text-xs" x-if="detailData">
                {{-- If Rejected, show banner --}}
                <template x-if="detailData?.status_approval === 'Rejected'">
                    <div class="bg-error/10 border border-error/30 text-error p-3 rounded-xl flex flex-col gap-1">
                        <span class="font-bold flex items-center gap-1 text-xs">
                            <x-heroicon-s-x-circle class="w-4 h-4" /> Catatan Penolakan Manager:
                        </span>
                        <p class="text-xs pl-5 font-semibold" x-text="detailData?.alasan_reject || 'Belum ada catatan'"></p>
                    </div>
                </template>

                {{-- General Info Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-base-200/40 p-3 rounded-xl border border-base-200">
                    <div>
                        <span class="text-base-content/50 block font-semibold text-[10px] uppercase">Auditor</span>
                        <span class="font-bold" x-text="detailData?.auditor || '-'"></span>
                    </div>
                    <div>
                        <span class="text-base-content/50 block font-semibold text-[10px] uppercase">Distributor / Cabang</span>
                        <span class="font-bold" x-text="(detailData?.distributor_name || '-') + ' (' + (detailData?.cabang || '-') + ')'"></span>
                    </div>
                    <div>
                        <span class="text-base-content/50 block font-semibold text-[10px] uppercase">Tanggal Audit</span>
                        <span class="font-bold" x-text="detailData?.created_at || '-'"></span>
                    </div>
                    <div>
                        <span class="text-base-content/50 block font-semibold text-[10px] uppercase">Status Approval</span>
                        <span class="font-bold uppercase" x-text="detailData?.status_approval || 'Pending'"></span>
                    </div>
                </div>

                {{-- Checklist 8 Items Table --}}
                <div>
                    <h4 class="font-bold text-sm mb-2 text-base-content">Hasil Checklist 8 Verifikasi Audit</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_toko_fisik ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">Toko Fisik</span>
                            <span class="font-bold" x-text="detailData?.is_toko_fisik ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_nama_pemilik ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">Nama Pemilik</span>
                            <span class="font-bold" x-text="detailData?.is_nama_pemilik ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_nama_ktp ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">Nama KTP</span>
                            <span class="font-bold" x-text="detailData?.is_nama_ktp ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_nik_ktp ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">NIK KTP</span>
                            <span class="font-bold" x-text="detailData?.is_nik_ktp ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_no_hp ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">No HP</span>
                            <span class="font-bold" x-text="detailData?.is_no_hp ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_no_rekening ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">No Rekening</span>
                            <span class="font-bold" x-text="detailData?.is_no_rekening ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_an_rekening ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">A/N Rekening</span>
                            <span class="font-bold" x-text="detailData?.is_an_rekening ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                        <div class="p-2.5 rounded-xl border flex items-center justify-between" :class="detailData?.is_titik_koordinat ? 'bg-success/10 border-success/30 text-success-content' : 'bg-error/10 border-error/30 text-error-content'">
                            <span class="font-semibold">Titik Koordinat</span>
                            <span class="font-bold" x-text="detailData?.is_titik_koordinat ? '✓ Sesuai' : '✗ Tidak'"></span>
                        </div>
                    </div>
                </div>

                {{-- Keterangan Hasil Audit --}}
                <div class="bg-base-200/50 p-3 rounded-xl border border-base-200">
                    <span class="text-base-content/60 block font-bold mb-1">Keterangan Catatan Audit:</span>
                    <p class="font-medium" x-text="detailData?.keterangan_hasil_audit || 'Tidak ada catatan khusus.'"></p>
                </div>

                {{-- Foto Grid --}}
                <div>
                    <h4 class="font-bold text-sm mb-2 text-base-content">Foto Dokumentasi Hasil Audit</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <template x-for="i in 8" :key="i">
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-bold text-base-content/60" x-text="'Foto ' + i"></span>
                                <div class="aspect-square bg-base-200 rounded-xl overflow-hidden border border-base-300 relative group">
                                    <template x-if="detailData && detailData['foto_audit' + i]">
                                        <img 
                                            :src="'/storage/' + detailData['foto_audit' + i]" 
                                            class="w-full h-full object-cover cursor-pointer group-hover:scale-105 transition-transform"
                                            @click="photoUrl = '/storage/' + detailData['foto_audit' + i]; showPhotoModal = true;"
                                        />
                                    </template>
                                    <template x-if="!detailData || !detailData['foto_audit' + i]">
                                        <div class="w-full h-full flex flex-col items-center justify-center text-base-content/30">
                                            <x-heroicon-s-photo class="w-6 h-6 mb-1" />
                                            <span class="text-[9px]">Kosong</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="p-4 border-t border-base-300 bg-base-200/50 flex justify-between items-center">
                <button type="button" @click="showDetailModal = false" class="btn btn-sm rounded-xl">Tutup</button>
                
                <div class="flex items-center gap-2" x-if="detailData">
                    <button 
                        type="button"
                        @click="showDetailModal = false; $wire.approve(detailData.id);"
                        class="btn btn-sm btn-success text-white rounded-xl font-bold gap-1"
                        x-show="detailData?.status_approval !== 'Approved'"
                    >
                        <x-heroicon-s-check class="w-4 h-4" /> Setujui (Approve)
                    </button>
                    <button 
                        type="button"
                        @click="showDetailModal = false; $wire.openRejectModal(detailData.id);"
                        class="btn btn-sm btn-error text-white rounded-xl font-bold gap-1"
                        x-show="detailData?.status_approval !== 'Rejected'"
                    >
                        <x-heroicon-s-x-mark class="w-4 h-4" /> Tolak (Reject)
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ZOOM FOTO --}}
    <div 
        x-show="showPhotoModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
        @click="showPhotoModal = false"
    >
        <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl bg-base-100 shadow-2xl">
            <img :src="photoUrl" class="max-w-full max-h-[85vh] object-contain" />
            <button type="button" @click="showPhotoModal = false" class="absolute top-3 right-3 btn btn-circle btn-sm bg-black/50 text-white border-0 hover:bg-black/70">✕</button>
        </div>
    </div>
</div>
