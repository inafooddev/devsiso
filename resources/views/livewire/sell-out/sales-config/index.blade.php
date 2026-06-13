<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Data Config Sales Invoice Distributor</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
        @include('livewire.sell-out._tabs')

        <!-- Success Notification -->
        @if (session()->has('message'))
            <x-ui.notif type="success" dismissible class="mb-2 shrink-0">
                {{ session('message') }}
            </x-ui.notif>
        @endif

        {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Data Config Sales Invoice</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola mapping kode cabang distributor</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <x-ui.search-input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari Kode atau Nama Cabang..." 
                    />
                    
                    <div class="flex flex-wrap items-center gap-1 md:gap-2">
                        <x-ui.action-button
                            type="add"
                            href="{{ route('sales-configs.create') }}"
                        />
                    </div>
                </div>
            </div>

            {{-- Body Card (Tabel Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th class="w-16 text-center">No</th>
                            <th>Kode Cabang</th>
                            <th>Nama Cabang</th>
                            <th>Tanggal Buat</th>
                            <th>Tanggal Update</th>
                            <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($configs as $config)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="text-center font-mono text-xs text-base-content/50">
                                {{ $loop->iteration + ($configs->currentPage() - 1) * $configs->perPage() }}
                            </td>
                            <td class="font-mono text-base-content/70 text-xs">
                                {{ $config->distributor_code }}
                            </td>
                            <td class="font-bold">
                                {{ $config->config_name }}
                            </td>
                            <td class="text-xs text-base-content/60 font-mono">
                                {{ $config->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="text-xs text-base-content/60 font-mono">
                                {{ $config->updated_at->format('d M Y H:i') }}
                            </td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    <x-ui.action-button
                                        type="edit"
                                        href="{{ route('sales-configs.edit', base64_encode($config->id)) }}"
                                        class="btn-square"
                                        title="Edit"
                                    />
                                    <x-ui.action-button
                                        type="delete"
                                        wire:click.prevent="confirmDelete({{ $config->id }})"
                                        class="btn-square"
                                        title="Hapus"
                                    />
                                </div>
                            </th>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center py-12 gap-3 text-base-content/40">
                                    <x-heroicon-o-inbox class="w-10 h-10" />
                                    <p class="text-sm">Tidak ada data ditemukan. Silakan klik 'Tambah Data' untuk membuat konfigurasi baru.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Card (Pagination) --}}
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
                @if($configs->hasPages())
                    <div class="w-full">
                        {{ $configs->links() }}
                    </div>
                @else
                    <div class="text-base-content/60 text-center sm:text-left w-full">
                        Menampilkan <span class="font-bold text-base-content">{{ $configs->count() }}</span> data
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Delete Confirmation Modal (Admin/Non-Guest only) -->
    @if($isDeleteModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" wire:click="closeDeleteModal"></div>

        <div class="relative bg-base-200 rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 ring-1 ring-base-300">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error/10 ring-1 ring-error/20">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-error" />
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-lg font-bold text-base-content">Hapus Konfigurasi</h3>
                    <p class="mt-2 text-sm text-base-content/60 leading-relaxed">
                        Apakah Anda yakin ingin menghapus konfigurasi ini? Data yang dihapus tidak dapat dikembalikan.
                    </p>
                </div>
            </div>
            <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button wire:click="closeDeleteModal"
                        class="btn btn-ghost normal-case rounded-xl border border-base-300 hover:bg-base-300">
                    Batal
                </button>
                <button wire:click="delete"
                        class="btn btn-error normal-case rounded-xl shadow-lg shadow-error/20 text-white">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    @endif
</div>