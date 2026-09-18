<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
     x-data="{ 
        toasts: [], 
        addToast(toast) {
            const id = Date.now();
            this.toasts.push({ id, ...toast });
            setTimeout(() => {
                this.removeToast(id);
            }, 3000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
        showRejectModal: false,
        showPrcModal: false,
        showDrawer: false,
        drawerData: null,
        openDrawer(payload) {
            this.drawerData = payload;
            this.showDrawer = true;
        }
    }"
    @toast.window="addToast(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)"
    @open-reject-modal.window="showRejectModal = true"
    @close-reject-modal.window="showRejectModal = false"
    @open-prc-modal.window="showPrcModal = true"
    @close-prc-modal.window="showPrcModal = false"
    @open-dynamic-drawer.window="openDrawer(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)">
    
    <x-slot name="title">Pusat Persetujuan JKS</x-slot>

    <x-ui.tab-menu>
        <a href="#" class="tab">Dashboard</a>
        <a href="{{ route('call-plan.jks-summary') }}" class="tab">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab">Detail</a>
        <a href="#" class="tab">Maps</a>
        <a href="{{ route('call-plan.jks-non-route') }}" class="tab">Outlet non JKS</a>
        <a href="{{ route('call-plan.jks-approvals') }}" class="tab tab-active bg-primary text-primary-content">
            Persetujuan
            @if(($kpi['PENDING'] ?? 0) > 0)
                <span class="badge badge-sm badge-error ml-2">{{ $kpi['PENDING'] }}</span>
            @endif
        </a>
    </x-ui.tab-menu>

    {{-- KPI Cards Section (Tetap statis di atas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- KPI Menunggu --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border {{ $filterStatus === 'PENDING' ? 'border-warning ring-1 ring-warning' : 'border-base-300 hover:border-warning/50' }} flex flex-col relative overflow-hidden group cursor-pointer transition-all" wire:click="$set('filterStatus', 'PENDING')">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Menunggu</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-clock class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">{{ $kpi['PENDING'] ?? 0 }}</div>
        </div>

        {{-- KPI Disetujui --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border {{ $filterStatus === 'APPROVED' ? 'border-success ring-1 ring-success' : 'border-base-300 hover:border-success/50' }} flex flex-col relative overflow-hidden group cursor-pointer transition-all" wire:click="$set('filterStatus', 'APPROVED')">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Disetujui</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ $kpi['APPROVED'] ?? 0 }}</div>
        </div>

        {{-- KPI Ditolak --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border {{ $filterStatus === 'REJECTED' ? 'border-error ring-1 ring-error' : 'border-base-300 hover:border-error/50' }} flex flex-col relative overflow-hidden group cursor-pointer transition-all" wire:click="$set('filterStatus', 'REJECTED')">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Ditolak</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ $kpi['REJECTED'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Antrean Persetujuan</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola perubahan jadwal kunjungan salesman</p>
            </div>
            
            {{-- Search & Filters & Bulk Actions --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 w-full sm:w-auto">
                
                {{-- Bulk Actions (Hanya muncul jika ada yang dipilih dan user memiliki role yang diizinkan) --}}
                @if($filterStatus === 'PENDING' && count($selected) > 0)
                    @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                        <div class="flex items-center gap-2 mr-2">
                            <span class="badge badge-primary">{{ count($selected) }} terpilih</span>
                            <button class="btn btn-sm btn-success text-white" wire:click="bulkApprove" wire:confirm="Setujui {{ count($selected) }} pengajuan?">
                                <x-heroicon-s-check class="w-4 h-4" /> Setujui
                            </button>
                            <button class="btn btn-sm btn-error text-white" wire:click="confirmBulkReject">
                                <x-heroicon-s-x-mark class="w-4 h-4" /> Tolak
                            </button>
                        </div>
                    @else
                        <div class="flex items-center gap-2 mr-2">
                            <span class="badge badge-primary">{{ count($selected) }} terpilih</span>
                            <span class="text-xs text-base-content/50 italic">Tidak ada akses persetujuan</span>
                        </div>
                    @endif
                @endif

                {{-- Search --}}
                <div class="relative min-w-[200px] grow sm:grow-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari data..." class="input input-sm input-bordered w-full pl-9 rounded-xl focus:outline-none focus:border-primary">
                </div>
                
                {{-- Filter Distributor --}}
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 focus:outline-none focus:border-primary">
                    <option value="">Semua Distributor</option>
                    @foreach($distributorOptions as $dist)
                        <option value="{{ $dist }}">{{ $dist }}</option>
                    @endforeach
                </select>
                
                {{-- Filter Type --}}
                <select wire:model.live="filterType" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 focus:outline-none focus:border-primary">
                    <option value="">Semua Tipe Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        @if($filterStatus === 'PENDING')
                            <th class="w-12 text-center">
                                <input type="checkbox" class="checkbox checkbox-sm rounded" wire:model.live="selectAll" />
                            </th>
                        @endif
                        <th class="w-32 font-semibold">Tipe Aksi</th>
                        <th class="w-32 font-semibold">Distributor</th>
                        <th class="w-40 font-semibold">Diajukan Oleh</th>
                        <th class="w-auto min-w-[300px] font-semibold">Alasan & Rincian</th>
                        <th class="w-40 font-semibold">Waktu Pengajuan</th>
                        @if($filterStatus !== 'PENDING')
                            <th class="w-40 font-semibold">Diperiksa Oleh</th>
                            @if($filterStatus === 'REJECTED')
                                <th class="w-auto min-w-[200px] font-semibold">Alasan Ditolak</th>
                            @endif
                        @endif
                        <th class="w-24 text-center font-semibold bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] sticky right-0">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm transition-opacity duration-200 relative" wire:loading.class="opacity-50 grayscale pointer-events-none">
                    @forelse($approvals as $approval)
                        <tr class="hover:bg-base-200/50 transition-colors group">
                            @if($filterStatus === 'PENDING')
                                <td class="text-center relative">
                                    @if(isset($prcIssues[$approval->id]))
                                        <div class="tooltip tooltip-right" data-tip="PRC Kurang">
                                            <input type="checkbox" class="checkbox checkbox-sm rounded checkbox-error opacity-50 cursor-not-allowed" disabled />
                                            <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-warning absolute -top-1 -right-1 bg-base-100 rounded-full" />
                                        </div>
                                    @else
                                        <input type="checkbox" class="checkbox checkbox-sm rounded" wire:model.live="selected" value="{{ $approval->id }}" />
                                    @endif
                                </td>
                            @endif
                            <td>
                                @php
                                    $actionType = str_replace('_', ' ', $approval->action_type);
                                    $badgeClass = match($approval->action_type) {
                                        'DELETE_MASSAL' => 'badge-error shadow-sm font-bold',
                                        'DELETE_INDIVIDU' => 'badge-error badge-outline',
                                        'TUKAR_JADWAL' => 'badge-secondary shadow-sm font-bold',
                                        'TAMBAH_JADWAL' => 'badge-success text-white shadow-sm',
                                        'EDIT_INDIVIDU' => 'badge-info badge-outline',
                                        default => 'badge-ghost border-base-300'
                                    };
                                @endphp
                                <span class="badge badge-sm font-medium {{ $badgeClass }} uppercase text-[10px] tracking-wider py-2">
                                    {{ $actionType }}
                                </span>
                                @if(in_array($approval->action_type, ['DELETE_INDIVIDU', 'DELETE_MASSAL']))
                                    <span class="badge badge-sm font-medium {{ ($approval->delete_type ?? 'SOFT') === 'HARD' ? 'badge-error' : 'badge-warning' }} uppercase text-[10px] tracking-wider py-2 ml-1">
                                        {{ $approval->delete_type ?? 'SOFT' }}
                                    </span>
                                @endif
                            </td>
                            <td class="font-mono text-base-content/80">{{ $approval->distributor_code }}</td>
                            <td class="font-medium">{{ $approval->maker_name }}</td>
                            <td class="whitespace-normal break-words text-base-content/90 min-w-[300px] max-w-sm">
                                <div class="font-medium">{{ $approval->reason }}</div>
                                @php $payload = json_decode($approval->payload, true); @endphp
                                @if($approval->action_type === 'TAMBAH_JADWAL' && $payload)
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="font-semibold text-xs text-base-content/80 bg-base-200 px-2 py-1 rounded">
                                            Penambahan {{ count($payload['tokos'] ?? []) }} Toko
                                        </span>
                                        <button wire:click="loadDrawerDetail({{ $approval->id }})" wire:loading.class="opacity-50 loading" wire:target="loadDrawerDetail({{ $approval->id }})" class="btn btn-xs btn-outline btn-primary">Lihat Detail</button>
                                    </div>
                                @elseif(in_array($approval->action_type, ['TUKAR_SALESMAN', 'TUKAR_MINGGU', 'TUKAR_HARI', 'EDIT_INDIVIDU', 'DELETE_INDIVIDU', 'DELETE_MASSAL']) && $payload)
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="font-semibold text-xs text-base-content/80 bg-base-200 px-2 py-1 rounded">
                                            @if($approval->action_type === 'DELETE_MASSAL')
                                                Penghapusan {{ count($payload['ids'] ?? []) }} Jadwal
                                            @elseif(in_array($approval->action_type, ['EDIT_INDIVIDU', 'DELETE_INDIVIDU']))
                                                Modifikasi 1 Jadwal
                                            @elseif($approval->action_type === 'TUKAR_HARI')
                                                Pertukaran Hari
                                            @elseif($approval->action_type === 'TUKAR_MINGGU')
                                                Pertukaran Minggu
                                            @elseif($approval->action_type === 'TUKAR_SALESMAN')
                                                Pertukaran Salesman
                                            @endif
                                        </span>
                                        <button wire:click="loadDrawerDetail({{ $approval->id }})" wire:loading.class="opacity-50 loading" wire:target="loadDrawerDetail({{ $approval->id }})" class="btn btn-xs btn-outline btn-primary">Lihat Detail</button>
                                    </div>
                                @endif
                            </td>
                            <td class="text-xs text-base-content/70">
                                <div class="font-medium text-base-content">{{ \Carbon\Carbon::parse($approval->created_at)->format('d M Y') }}</div>
                                <div>{{ \Carbon\Carbon::parse($approval->created_at)->format('H:i') }}</div>
                            </td>
                            
                            @if($filterStatus !== 'PENDING')
                                <td class="text-xs text-base-content/70">
                                    <div class="font-medium text-base-content">{{ $approval->checker_name }}</div>
                                    <div>{{ \Carbon\Carbon::parse($approval->updated_at)->format('d M Y H:i') }}</div>
                                </td>
                                @if($filterStatus === 'REJECTED')
                                    <td class="whitespace-normal break-words text-error font-medium">{{ $approval->reject_reason }}</td>
                                @endif
                            @endif
                            
                            <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)] sticky right-0">
                                @if($filterStatus === 'PENDING')
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('user'))
                                        <div class="flex items-center justify-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                            @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                                            <button class="btn btn-sm btn-square btn-ghost text-success hover:bg-success/20" 
                                                    title="Setujui"
                                                    wire:click="approve({{ $approval->id }})" 
                                                    wire:confirm="Apakah Anda yakin ingin menyetujui pengajuan ini?">
                                                <x-heroicon-s-check class="w-5 h-5" />
                                            </button>
                                            @endif
                                            @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                                            <button class="btn btn-sm btn-square btn-ghost text-error hover:bg-error/20" 
                                                    title="Tolak"
                                                    wire:click="confirmReject({{ $approval->id }})">
                                                <x-heroicon-s-x-mark class="w-5 h-5" />
                                            </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge badge-sm badge-warning">Menunggu</span>
                                    @endif
                                @else
                                    <span class="badge badge-sm {{ $filterStatus === 'APPROVED' ? 'badge-success' : 'badge-error' }}">
                                        {{ $filterStatus === 'APPROVED' ? 'Disetujui' : 'Ditolak' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $filterStatus === 'PENDING' ? 8 : ($filterStatus === 'REJECTED' ? 8 : 7) }}" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center text-base-content/40">
                                    <x-heroicon-o-inbox class="w-12 h-12 mb-3" />
                                    <p class="font-medium">Tidak ada data pengajuan {{ strtolower($filterStatus) }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Card (Pagination) --}}
        @if($approvals->hasPages())
            <div class="p-3 border-t border-base-300 bg-base-200 shrink-0">
                {{ $approvals->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    {{-- Modal Penolakan --}}
    <div class="modal modal-bottom sm:modal-middle !items-start z-[10000]" :class="showRejectModal ? 'modal-open' : ''">
        <div class="modal-box relative mt-6 sm:mt-16 mb-auto">
            <button @click="showRejectModal = false" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="text-lg font-bold text-error flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                Alasan Penolakan
            </h3>
            <p class="py-4 text-sm text-base-content/80">Silakan masukkan alasan mengapa pengajuan ini ditolak. Alasan ini akan diteruskan ke pembuat pengajuan.</p>
            
            <textarea wire:model="rejectReason" class="textarea textarea-bordered w-full h-32 focus:border-error focus:ring-1 focus:ring-error" placeholder="Contoh: Jadwal tidak sesuai dengan rute..."></textarea>
            @error('rejectReason') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror

            <div class="modal-action">
                <button class="btn btn-ghost" @click="showRejectModal = false">Batal</button>
                <button class="btn btn-error text-white" wire:click="executeReject" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="executeReject">Tolak Pengajuan</span>
                    <span wire:loading wire:target="executeReject" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop" @click="showRejectModal = false"></div>
    </div>

    {{-- PRC Input Modal --}}
    <div class="modal modal-bottom sm:modal-middle z-[10000]" :class="showPrcModal ? 'modal-open' : ''">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4 text-warning flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                Customer Kode PRC Bermasalah (Kosong / Duplikat)
            </h3>
            
            <p class="text-sm text-base-content/80 mb-4">
                Terdapat <span class="font-bold text-error">{{ count($missingPrcTokos) }}</span> toko yang belum memiliki Customer Kode PRC <b>atau kodenya sudah terpakai oleh toko lain</b> di distributor ini. Sesuai aturan, Anda wajib mengisi kode yang unik sebelum pengajuan dapat disetujui untuk diinjeksi ke Master Pareto.
            </p>

            <div class="space-y-3 max-h-[50vh] overflow-y-auto p-1 mb-4">
                @foreach($missingPrcTokos as $index => $toko)
                    <div class="bg-base-200/50 p-3 rounded-lg border border-base-300">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div>
                                <div class="text-xs text-base-content/60">Toko:</div>
                                <div class="font-bold text-sm leading-tight">{{ $toko['name'] }}</div>
                                <div class="font-mono text-xs text-primary mt-0.5">{{ $toko['code'] }}</div>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="text" wire:model="missingPrcTokos.{{ $index }}.eska" class="input input-sm input-bordered w-full font-mono @error('missingPrcTokos.'.$index.'.eska') input-error @enderror" placeholder="Input PRC (eska)..." />
                            @error('missingPrcTokos.'.$index.'.eska') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="modal-action flex justify-between items-center">
                @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                                            <button wire:click="confirmReject('{{ $prcApprovalId }}')" class="btn btn-error btn-sm btn-outline">Tolak Pengajuan</button>
                                            @endif
                <div class="flex gap-2">
                    <button wire:click="closePrcModal" class="btn btn-ghost btn-sm">Batal</button>
                    <button wire:click="submitPrcAndApprove" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="submitPrcAndApprove">
                        <span wire:loading.remove wire:target="submitPrcAndApprove">Simpan & Setujui</span>
                        <span wire:loading wire:target="submitPrcAndApprove" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-backdrop bg-neutral/40" wire:click="closePrcModal"></div>
    </div>

    {{-- Side Drawer (Alpine JS) --}}
    <div class="fixed inset-0 z-[11000] flex justify-end" x-show="showDrawer" style="display: none;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-neutral/40 transition-opacity" 
             x-show="showDrawer" 
             x-transition:enter="ease-in-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in-out duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             @click="showDrawer = false"></div>
             
        {{-- Panel --}}
        <div class="relative w-full max-w-md bg-base-100 shadow-2xl h-full flex flex-col transform transition-transform"
             x-show="showDrawer" 
             x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" 
             x-transition:enter-start="translate-x-full" 
             x-transition:enter-end="translate-x-0" 
             x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" 
             x-transition:leave-start="translate-x-0" 
             x-transition:leave-end="translate-x-full">
            
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <h3 class="font-bold text-lg">Rincian Pengajuan</h3>
                <button @click="showDrawer = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4">
                <template x-if="drawerData">
                    <div class="space-y-4 text-sm">
                        {{-- Generic rendering of payload based on keys --}}
                        <div class="bg-base-200/30 rounded-lg p-3 border border-base-300 space-y-2">
                            <template x-if="drawerData.salesman_code">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Salesman</span>
                                    <span class="font-mono" x-text="drawerData.salesman_code"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.hari">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Hari</span>
                                    <span class="font-mono uppercase" x-text="(drawerData.hari || []).join(', ')"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.minggu">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Minggu</span>
                                    <span class="font-mono uppercase" x-text="(drawerData.minggu || []).join(', ')"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.salesman_asal">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Salesman Asal</span>
                                    <span class="font-mono" x-text="drawerData.salesman_asal"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.salesman_tujuan">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Salesman Tujuan</span>
                                    <span class="font-mono" x-text="drawerData.salesman_tujuan"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.minggu_asal">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Minggu Asal</span>
                                    <span class="font-mono uppercase" x-text="drawerData.minggu_asal"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.minggu_tujuan">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Minggu Tujuan</span>
                                    <span class="font-mono uppercase" x-text="drawerData.minggu_tujuan"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.hari_asal">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Hari Asal</span>
                                    <span class="font-mono uppercase" x-text="drawerData.hari_asal"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.hari_tujuan">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Hari Tujuan</span>
                                    <span class="font-mono uppercase" x-text="drawerData.hari_tujuan"></span>
                                </div>
                            </template>
                        </div>
                        
                        <template x-if="drawerData.tokos && drawerData.tokos.length > 0">
                            <div>
                                <h4 class="font-semibold text-base-content/80 mb-2">Daftar Toko (<span x-text="drawerData.tokos.length"></span>)</h4>
                                <ul class="space-y-2">
                                    <template x-for="toko in drawerData.tokos">
                                        <li class="bg-base-100 p-3 rounded border border-base-300 flex flex-col gap-1">
                                            <div class="flex justify-between items-start">
                                                <span class="font-bold text-sm" x-text="toko.name || toko.code || 'Nama Toko Tidak Diketahui'"></span>
                                                <span class="font-mono text-xs text-primary bg-primary/10 px-1.5 py-0.5 rounded" x-text="toko.code"></span>
                                            </div>
                                            <template x-if="toko.salesman_code && !(drawerData.action_type === 'DELETE_MASSAL' && drawerData.salesman_code)">
                                                <div class="mt-1 text-xs text-base-content/70 flex flex-col gap-1 border-t border-base-200 pt-2 mt-2">
                                                    <div><span class="font-semibold text-base-content/80">Salesman:</span> <span class="font-mono" x-text="toko.salesman_code"></span></div>
                                                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                                                        <div>
                                                            <span class="font-semibold text-base-content/80">Jadwal Hari:</span> 
                                                            <span x-text="['h1','h2','h3','h4','h5','h6','h7'].filter(h => toko[h] === 'Y').map(h => h.toUpperCase()).join(', ') || '-'"></span>
                                                        </div>
                                                        <div>
                                                            <span class="font-semibold text-base-content/80">Jadwal Minggu:</span> 
                                                            <span x-text="['w1','w2','w3','w4'].filter(w => toko[w] === 'Y').map(w => w.toUpperCase()).join(', ') || '-'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        {{-- Fallback UI for hard-deleted records where 'tokos' cannot be fetched from DB anymore, relying purely on the snapshot payload --}}
                        <template x-if="(!drawerData.tokos || drawerData.tokos.length === 0) && drawerData.records && drawerData.records.length > 0">
                            <div>
                                <h4 class="font-semibold text-base-content/80 mb-2">Daftar Jadwal Terdampak (<span x-text="drawerData.records.length"></span>)</h4>
                                <ul class="space-y-2">
                                    <template x-for="record in drawerData.records">
                                        <li class="bg-base-100 p-3 rounded border border-base-300 flex flex-col gap-1">
                                            <div class="flex justify-between items-start">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-sm" x-text="record.customer_name || 'Nama Toko Tidak Diketahui'"></span>
                                                    <span class="text-[10px] text-base-content/60">ID Record: <span class="font-mono text-primary" x-text="record.id"></span></span>
                                                </div>
                                                <div class="flex flex-col items-end gap-1">
                                                    <template x-if="record.customer_code">
                                                        <span class="font-mono text-xs text-primary bg-primary/10 px-1.5 py-0.5 rounded" x-text="record.customer_code"></span>
                                                    </template>
                                                    <span class="font-mono text-[10px] bg-base-200 px-1.5 py-0.5 rounded" x-text="record.bulan"></span>
                                                </div>
                                            </div>
                                            <template x-if="!(drawerData.action_type === 'DELETE_MASSAL' && drawerData.salesman_code)">
                                                <div class="mt-1 text-xs text-base-content/70 flex flex-col gap-1 border-t border-base-200 pt-2 mt-2">
                                                    <div><span class="font-semibold text-base-content/80">Salesman:</span> <span class="font-mono" x-text="record.salesman_code"></span></div>
                                                    <div><span class="font-semibold text-base-content/80">Distributor:</span> <span class="font-mono" x-text="record.distributor_code"></span></div>
                                                </div>
                                            </template>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <template x-if="drawerData.ids && drawerData.ids.length > 0">
                            <div class="mb-4">
                                <h4 class="font-semibold text-base-content/80 mb-2">Peringatan Penghapusan</h4>
                                <div class="bg-error/10 text-error p-3 rounded border border-error/20 flex items-center gap-3">
                                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 flex-shrink-0" />
                                    <div class="text-sm">
                                        <span class="font-bold block">Penghapusan Massal (<span x-text="drawerData.ids.length"></span> Jadwal)</span>
                                        <span>Seluruh jadwal harian dan mingguan untuk toko-toko di atas akan dikosongkan secara permanen.</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="drawerData.action_type === 'DELETE_INDIVIDU'">
                            <div class="mb-4">
                                <h4 class="font-semibold text-base-content/80 mb-2">Peringatan Penghapusan</h4>
                                <div class="bg-error/10 text-error p-3 rounded border border-error/20 flex items-center gap-3">
                                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 flex-shrink-0" />
                                    <div class="text-sm">
                                        <span class="font-bold block">Penghapusan Jadwal</span>
                                        <span>Seluruh jadwal harian dan mingguan untuk toko di atas akan dikosongkan secara permanen.</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="drawerData.update && !drawerData.action_type.includes('DELETE')">
                            <div class="mt-4">
                                <h4 class="font-semibold text-base-content/80 mb-2">Detail Modifikasi Jadwal</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Hari -->
                                    <div class="bg-base-100 p-3 rounded border border-base-300">
                                        <h5 class="font-semibold text-xs text-base-content/60 mb-2">Hari Kunjungan</h5>
                                        <div class="grid grid-cols-7 gap-1">
                                            <template x-for="h in ['h1','h2','h3','h4','h5','h6','h7']">
                                                <div class="text-center p-1 rounded text-xs font-mono"
                                                     :class="drawerData.update[h] === 'Y' ? 'bg-success/20 text-success-content font-bold' : 'bg-base-200 text-base-content/40'">
                                                    <span x-text="h.toUpperCase()"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Minggu -->
                                    <div class="bg-base-100 p-3 rounded border border-base-300">
                                        <h5 class="font-semibold text-xs text-base-content/60 mb-2">Minggu Kunjungan</h5>
                                        <div class="grid grid-cols-4 gap-1">
                                            <template x-for="w in ['w1','w2','w3','w4']">
                                                <div class="text-center p-1 rounded text-xs font-mono"
                                                     :class="drawerData.update[w] === 'Y' ? 'bg-success/20 text-success-content font-bold' : 'bg-base-200 text-base-content/40'">
                                                    <span x-text="w.toUpperCase()"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <template x-if="drawerData.update.reason">
                                    <div class="mt-3 bg-base-100 p-3 rounded border border-base-300">
                                        <h5 class="font-semibold text-xs text-base-content/60 mb-1">Alasan Detail</h5>
                                        <p class="text-sm text-base-content/80 italic" x-text="drawerData.update.reason"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        
                    </div>
                </template>
            </div>
            <div class="p-4 border-t border-base-300 bg-base-200/50 flex justify-end">
                <button @click="showDrawer = false" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast toast-top toast-right z-[9999] mt-16" style="position: fixed;">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-full"
                 class="alert shadow-lg flex items-center justify-between min-w-[300px]"
                 :class="{
                     'alert-success bg-success text-success-content': toast.type === 'success',
                     'alert-error bg-error text-error-content': toast.type === 'error',
                     'alert-warning bg-warning text-warning-content': toast.type === 'warning',
                     'alert-info bg-info text-info-content': toast.type === 'info'
                 }">
                <div class="flex items-center gap-3">
                    <template x-if="toast.type === 'success'">
                        <x-heroicon-o-check-circle class="w-6 h-6 shrink-0" />
                    </template>
                    <template x-if="toast.type === 'error'">
                        <x-heroicon-o-x-circle class="w-6 h-6 shrink-0" />
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0" />
                    </template>
                    <template x-if="toast.type === 'info'">
                        <x-heroicon-o-information-circle class="w-6 h-6 shrink-0" />
                    </template>
                    <span x-text="toast.message" class="text-sm font-medium"></span>
                </div>
                <button @click="removeToast(toast.id)" class="btn btn-ghost btn-xs btn-square">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
        </template>
    </div>
</div>
