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
        showRejectModal: false
    }"
    @toast.window="addToast(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)"
    @open-reject-modal.window="showRejectModal = true"
    @close-reject-modal.window="showRejectModal = false">
    
    <x-slot name="title">Pusat Persetujuan JKS</x-slot>

    <x-ui.tab-menu>
        @php
            $pendingApprovalsCount = \Illuminate\Support\Facades\DB::table('jks_approvals')->where('status', 'PENDING')->count();
        @endphp
        <a href="#" class="tab">Dashboard</a>
        <a href="#" class="tab">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab">Detail</a>
        <a href="#" class="tab">Maps</a>
        <a href="#" class="tab">Outlet non JKS</a>
        <a href="{{ route('call-plan.jks-approvals') }}" class="tab tab-active bg-primary text-primary-content">
            Persetujuan
            @if($pendingApprovalsCount > 0)
                <span class="badge badge-sm badge-error ml-2">{{ $pendingApprovalsCount }}</span>
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
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('user'))
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
                            <span class="text-xs text-base-content/50 italic">Hanya Admin/User yang bisa proses</span>
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
                <tbody class="text-sm">
                    @forelse($approvals as $approval)
                        <tr class="hover:bg-base-200/50 transition-colors group">
                            @if($filterStatus === 'PENDING')
                                <td class="text-center">
                                    <input type="checkbox" class="checkbox checkbox-sm rounded" wire:model.live="selected" value="{{ $approval->id }}" />
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
                            </td>
                            <td class="font-mono text-base-content/80">{{ $approval->distributor_code }}</td>
                            <td class="font-medium">{{ $approval->maker_name }}</td>
                            <td class="whitespace-normal break-words text-base-content/90 min-w-[300px] max-w-sm">
                                <div class="font-medium">{{ $approval->reason }}</div>
                                @php $payload = json_decode($approval->payload, true); @endphp
                                @if($approval->action_type === 'TAMBAH_JADWAL' && $payload)
                                    <div class="text-xs text-base-content/70 mt-2 bg-base-200/50 p-2 rounded border border-base-300 space-y-1">
                                        <div><span class="font-semibold">Salesman:</span> {{ $payload['salesman_code'] }}</div>
                                        <div><span class="font-semibold">Hari:</span> {{ strtoupper(implode(', ', $payload['hari'] ?? [])) }}</div>
                                        <div><span class="font-semibold">Minggu:</span> {{ strtoupper(implode(', ', $payload['minggu'] ?? [])) }}</div>
                                        <div class="line-clamp-2"><span class="font-semibold">Toko ({{ count($payload['tokos'] ?? []) }}):</span> 
                                            {{ collect($payload['tokos'] ?? [])->pluck('name')->join(', ') }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="text-xs text-base-content/70">
                                <div class="font-medium text-base-content">{{ \Carbon\Carbon::parse($approval->created_at)->format('d M Y') }}</div>
                                <div>{{ \Carbon\Carbon::parse($approval->created_at)->format('H:i') }}</div>
                            </td>
                            
                            @if($filterStatus !== 'PENDING')
                                <td class="text-base-content/80">{{ $approval->checker_name }}</td>
                                @if($filterStatus === 'REJECTED')
                                    <td class="whitespace-normal break-words text-error font-medium">{{ $approval->reject_reason }}</td>
                                @endif
                            @endif
                            
                            <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)] sticky right-0">
                                @if($filterStatus === 'PENDING')
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('user'))
                                        <div class="flex items-center justify-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                            <button class="btn btn-sm btn-square btn-ghost text-success hover:bg-success/10" 
                                                    title="Setujui"
                                                    wire:click="approve({{ $approval->id }})" 
                                                    wire:confirm="Apakah Anda yakin ingin menyetujui pengajuan ini?">
                                                <x-heroicon-s-check class="w-4 h-4" />
                                            </button>
                                            <button class="btn btn-sm btn-square btn-ghost text-error hover:bg-error/10" 
                                                    title="Tolak"
                                                    wire:click="confirmReject({{ $approval->id }})">
                                                <x-heroicon-s-x-mark class="w-4 h-4" />
                                            </button>
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
