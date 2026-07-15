<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Bug Report & Request</x-slot>

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- Total --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Tiket</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-ticket class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ $kpis['total'] }}</div>
        </div>

        {{-- Open --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Status Open</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-envelope-open class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">{{ $kpis['open'] }}</div>
        </div>

        {{-- In Progress --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-info/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">In Progress</h3>
                <div class="w-8 h-8 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-arrow-path class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-info">{{ $kpis['in_progress'] }}</div>
        </div>

        {{-- Resolved --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Resolved</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ $kpis['resolved'] }}</div>
        </div>

        {{-- Closed --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-neutral/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Closed</h3>
                <div class="w-8 h-8 rounded-xl bg-neutral/10 flex items-center justify-center text-neutral shrink-0">
                    <x-heroicon-s-archive-box class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-neutral-content">{{ $kpis['closed'] }}</div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <h2 class="text-base md:text-lg font-bold">Monitoring Tiket</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar Bug Report & Request Kebutuhan Sistem</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 md:gap-3 w-full lg:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari judul/deskripsi..." />
                
                {{-- Filter Tipe --}}
                <select wire:model.live="typeFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Tipe</option>
                    <option value="bug">Bug</option>
                    <option value="request">Request</option>
                </select>

                {{-- Filter Status --}}
                <select wire:model.live="statusFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>

                {{-- Filter Priority --}}
                <select wire:model.live="priorityFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Prioritas</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>

                <div class="flex items-center gap-1 md:gap-2">
                    @canAdd('others.bug-report-request')
                        <x-ui.action-button type="add" onclick="document.getElementById('addTicketModal').showModal()" label="Buat Tiket Baru" />
                    @endcanAdd
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-16">No</th>
                        <th>Tipe</th>
                        <th>Judul</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Pelapor</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Tanggal Selesai</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($tickets as $index => $ticket)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th>{{ $tickets->firstItem() + $index }}</th>
                            <td>
                                @if($ticket->type === 'bug')
                                    <span class="badge badge-sm badge-error badge-outline">Bug</span>
                                @else
                                    <span class="badge badge-sm badge-info badge-outline">Request</span>
                                @endif
                            </td>
                            <td class="font-bold truncate max-w-xs" title="{{ $ticket->title }}">
                                {{ $ticket->title }}
                            </td>
                            <td>
                                @php
                                    $prioBadge = match($ticket->priority) {
                                        'low' => 'badge-neutral',
                                        'medium' => 'badge-info',
                                        'high' => 'badge-warning',
                                        'critical' => 'badge-error',
                                        default => 'badge-neutral'
                                    };
                                @endphp
                                <span class="badge badge-sm {{ $prioBadge }} text-white font-semibold capitalize">{{ $ticket->priority }}</span>
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($ticket->status) {
                                        'open' => 'badge-warning',
                                        'in_progress' => 'badge-info',
                                        'resolved' => 'badge-success',
                                        'closed' => 'badge-neutral',
                                        default => 'badge-neutral'
                                    };
                                @endphp
                                <span class="badge badge-sm badge-outline {{ $statusBadge }} font-bold capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </td>
                            <td>{{ $ticket->user->name ?? 'System' }}</td>
                            <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($ticket->completed_at)
                                    <span class="text-success font-semibold">{{ $ticket->completed_at->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-base-content/40 italic">-</span>
                                @endif
                            </td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Detail view button --}}
                                    <button 
                                        type="button"
                                        onclick="document.getElementById('viewTicketModal-{{ $ticket->id }}').showModal()"
                                        class="btn btn-sm btn-square btn-ghost text-info hover:bg-info/10"
                                        title="Detail Tiket"
                                    >
                                        <x-heroicon-s-eye class="w-4 h-4" />
                                    </button>

                                    {{-- Admin response button --}}
                                    @if($isAdmin)
                                        @canEdit('others.bug-report-request')
                                            <button 
                                                type="button"
                                                wire:click="selectTicket({{ $ticket->id }})"
                                                class="btn btn-sm btn-square btn-ghost text-warning hover:bg-warning/10"
                                                title="Update Status / Respon"
                                            >
                                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                            </button>
                                        @endcanEdit
                                    @endif

                                    {{-- Owner / Admin delete button --}}
                                    @if(($ticket->user_id === auth()->id() && $ticket->status === 'open') || $isAdmin)
                                        @canDelete('others.bug-report-request')
                                            <button 
                                                type="button"
                                                wire:click="deleteTicket({{ $ticket->id }})"
                                                wire:confirm="Yakin ingin menghapus tiket ini?"
                                                class="btn btn-sm btn-square btn-ghost text-error hover:bg-error/10"
                                                title="Hapus Tiket"
                                            >
                                                <x-heroicon-s-trash class="w-4 h-4" />
                                            </button>
                                        @endcanDelete
                                    @endif
                                </div>
                            </th>
                        </tr>

                        {{-- Modal View Detail per Ticket (static markup to keep it fast and functional) --}}
                        <x-ui.modal id="viewTicketModal-{{ $ticket->id }}" title="Detail Tiket #{{ $ticket->id }}" icon="ticket" size="lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-[10px] uppercase font-bold text-base-content/50">Judul Laporan</label>
                                        <p class="font-bold text-base text-base-content">{{ $ticket->title }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[10px] uppercase font-bold text-base-content/50">Tipe</label>
                                            <p class="capitalize font-semibold text-sm">{{ $ticket->type }}</p>
                                        </div>
                                        <div>
                                            <label class="text-[10px] uppercase font-bold text-base-content/50">Prioritas</label>
                                            <p class="capitalize font-semibold text-sm">{{ $ticket->priority }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[10px] uppercase font-bold text-base-content/50">Status</label>
                                            <p class="capitalize font-semibold text-sm">{{ str_replace('_', ' ', $ticket->status) }}</p>
                                        </div>
                                        <div>
                                            <label class="text-[10px] uppercase font-bold text-base-content/50">Tgl Selesai</label>
                                            <p class="font-semibold text-sm {{ $ticket->completed_at ? 'text-success' : 'text-base-content/40 italic' }}">
                                                {{ $ticket->completed_at ? $ticket->completed_at->format('d M Y H:i') : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase font-bold text-base-content/50">Deskripsi / Detail Masalah</label>
                                        <p class="bg-base-200/50 p-3 rounded-lg text-sm whitespace-pre-wrap border border-base-300 text-base-content">{{ $ticket->description }}</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-[10px] uppercase font-bold text-base-content/50">Screenshot Pendukung</label>
                                        @if($ticket->screenshot)
                                            <div class="mt-1 border border-base-300 rounded-lg overflow-hidden max-h-48 flex justify-center bg-black/5">
                                                <a href="{{ asset('storage/' . $ticket->screenshot) }}" target="_blank" title="Klik untuk memperbesar">
                                                    <img src="{{ asset('storage/' . $ticket->screenshot) }}" alt="Screenshot" class="object-contain max-h-48 hover:scale-105 transition-transform">
                                                </a>
                                            </div>
                                        @else
                                            <p class="text-xs text-base-content/40 italic mt-1">Tidak ada screenshot yang diunggah.</p>
                                        @endif
                                    </div>
                                    <div class="border-t border-base-300 pt-3">
                                        <label class="text-[10px] uppercase font-bold text-base-content/50 text-primary">Tanggapan Developer / Admin</label>
                                        @if($ticket->developer_response)
                                            <p class="bg-primary/5 text-primary border border-primary/20 p-3 rounded-lg text-sm whitespace-pre-wrap mt-1">
                                                {{ $ticket->developer_response }}
                                            </p>
                                        @else
                                            <p class="text-xs text-base-content/40 italic mt-1">Belum ada tanggapan dari developer.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <x-slot:footer>
                                <button onclick="document.getElementById('viewTicketModal-{{ $ticket->id }}').close()" class="btn btn-sm btn-ghost rounded-xl">Tutup</button>
                            </x-slot:footer>
                        </x-ui.modal>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-base-content/50">Tidak ada data tiket ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer & Pagination --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            <div class="text-base-content/60 text-center sm:text-left">
                Menampilkan <span class="font-bold text-base-content">{{ $tickets->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-base-content">{{ $tickets->lastItem() ?? 0 }}</span> dari <span class="font-bold text-base-content">{{ $tickets->total() }}</span> entri
            </div>
            <div>
                {{ $tickets->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Create Ticket --}}
    <x-ui.modal id="addTicketModal" title="Buat Laporan Bug / Request Baru" icon="plus" size="md" wire:close="resetForm" wire:ignore.self>
        <form wire:submit.prevent="store" class="space-y-4">
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-bold uppercase tracking-wider text-base-content/60 text-xs">Judul Laporan</span></label>
                <input type="text" wire:model="title" class="input input-bordered w-full rounded-xl @error('title') input-error @enderror" placeholder="Contoh: Error halaman sell-out saat ekspor">
                @error('title') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-bold uppercase tracking-wider text-base-content/60 text-xs">Tipe Laporan</span></label>
                    <select wire:model="type" class="select select-bordered w-full rounded-xl">
                        <option value="bug">Bug (Kesalahan/Error)</option>
                        <option value="request">Request (Fitur Baru/Kebutuhan)</option>
                    </select>
                </div>
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-bold uppercase tracking-wider text-base-content/60 text-xs">Prioritas</span></label>
                    <select wire:model="priority" class="select select-bordered w-full rounded-xl">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical (Mendesak)</option>
                    </select>
                </div>
            </div>

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-bold uppercase tracking-wider text-base-content/60 text-xs">Deskripsi Laporan / Penjelasan Detail</span></label>
                <textarea wire:model="description" class="textarea textarea-bordered w-full rounded-xl h-28 @error('description') textarea-error @enderror" placeholder="Jelaskan langkah-langkah terjadinya error atau kebutuhan request detail Anda..."></textarea>
                @error('description') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-bold uppercase tracking-wider text-base-content/60 text-xs">Screenshot (Opsional)</span></label>
                <input type="file" wire:model="screenshot" wire:key="screenshot-{{ $iteration }}" class="file-input file-input-bordered w-full rounded-xl" accept="image/*">
                <div wire:loading wire:target="screenshot" class="text-xs text-info mt-1">Mengunggah gambar...</div>
                @error('screenshot') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror

                @if ($screenshot && !is_string($screenshot) && method_exists($screenshot, 'temporaryUrl'))
                    <div class="mt-2 text-xs">
                        <p class="font-bold text-success">Preview Gambar:</p>
                        <img src="{{ $screenshot->temporaryUrl() }}" class="mt-1 max-h-32 rounded-lg object-cover border border-base-300">
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="document.getElementById('addTicketModal').close()" class="btn btn-ghost rounded-xl">Batal</button>
                <button type="submit" class="btn btn-primary rounded-xl text-primary-content">
                    <span wire:loading wire:target="store" class="loading loading-spinner loading-xs"></span>
                    Kirim Tiket
                </button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal Respond Ticket (Admin Only) --}}
    @if($isAdmin)
        <x-ui.modal id="respondTicketModal" title="Tanggapan Developer & Status Tiket" icon="pencil-square" size="md" wire:ignore.self>
            <form wire:submit.prevent="updateResponse" class="space-y-4">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-bold uppercase tracking-wider text-base-content/60 text-xs">Ubah Status Tiket</span></label>
                    <select wire:model="editStatus" class="select select-bordered w-full rounded-xl">
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-bold uppercase tracking-wider text-base-content/60 text-xs">Tanggapan / Catatan Developer</span></label>
                    <textarea wire:model="developerResponse" class="textarea textarea-bordered w-full rounded-xl h-28" placeholder="Tuliskan respon, solusi, atau perkembangan pengerjaan di sini..."></textarea>
                    @error('developerResponse') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="document.getElementById('respondTicketModal').close()" class="btn btn-ghost rounded-xl">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl text-primary-content">Simpan Perubahan</button>
                </div>
            </form>
        </x-ui.modal>
    @endif
</div>

@push('scripts')
<style>
    .v-toast-fade-out {
        animation: toast-fade-out 0.3s forwards;
    }
    @keyframes toast-fade-out {
        from { transform: scale(1); opacity: 1; }
        to   { transform: scale(0.9); opacity: 0; }
    }
</style>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('close-modal', (event) => {
            const modalId = Array.isArray(event) ? event[0] : event;
            const modal = document.getElementById(modalId);
            if (modal) modal.close();
        });

        Livewire.on('open-modal', (event) => {
            const modalId = Array.isArray(event) ? event[0] : event;
            const modal = document.getElementById(modalId);
            if (modal) modal.showModal();
        });

        // Vanilla JS Toast Handler
        window.addEventListener('show-toast', (e) => {
            let detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
            let type = detail?.type || e.detail?.type || 'success';
            let message = detail?.message || e.detail?.message || 'Sukses';
            
            let container = document.getElementById('vanilla-toast-container');
            if(!container) {
                container = document.createElement('div');
                container.id = 'vanilla-toast-container';
                container.className = 'toast toast-top toast-center z-[9999] pt-4';
                document.body.appendChild(container);
            }
            
            let toast = document.createElement('div');
            toast.className = 'flex items-start gap-3 px-5 py-4 mb-2 min-w-[280px] w-auto max-w-[90vw] sm:max-w-md whitespace-normal break-words rounded-2xl shadow-2xl backdrop-blur-md text-sm font-medium ' + 
                (type === 'error' 
                    ? 'bg-error/95 text-error-content shadow-error/20 border border-error/50' 
                    : 'bg-success/95 text-success-content shadow-success/20 border border-success/50');
            
            // Ikon
            let iconSvg = type === 'success' 
                ? '<svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" /></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" /></svg>';
            
            toast.innerHTML = iconSvg + '<div class="flex-1 pt-0.5 leading-snug tracking-wide">' + message + '</div>';
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('v-toast-fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        });
    });
</script>
@endpush
