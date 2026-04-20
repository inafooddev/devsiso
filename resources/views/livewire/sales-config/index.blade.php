<div>
    <x-slot name="title">Data Config Sales Invoice Distributor</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">

        <!-- Header: Actions (left) + Search (right) -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">

            @unless(auth()->user()->hasRole('guest'))
            <div class="flex items-center w-full sm:w-auto gap-3">
                <a href="{{ route('sales-configs.create') }}" class="btn btn-primary rounded-xl shadow-lg shadow-primary/20 normal-case">
                    <x-heroicon-o-plus class="w-5 h-5" />
                    Tambah Data
                </a>

                <a href="{{ route('sales-invoices.import') }}" class="btn btn-ghost border border-base-300 text-base-content hover:bg-base-200 hover:text-base-content hover:border-base-300 rounded-xl normal-case transition-colors">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-base-content/60" />
                    Import
                </a>
            </div>
            @endunless

            <!-- Global Search -->
            <div class="w-full sm:w-80 relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-base-content/40 group-focus-within:text-primary transition-colors duration-200" />
                </div>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari Kode atau Nama Cabang..."
                       class="input w-full bg-base-100 border border-base-300 text-base-content text-sm rounded-xl pl-11 pr-4 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all placeholder:text-base-content/40">
            </div>
        </div>

        <!-- Success Notification -->
        @if (session()->has('message'))
            <x-ui.notif type="success" dismissible class="mb-6">
                {{ session('message') }}
            </x-ui.notif>
        @endif

        <!-- Table Card -->
        <x-card>
            <x-ui.table hover empty="Tidak ada data ditemukan. Silakan klik 'Tambah Data' untuk membuat konfigurasi baru.">
                <x-slot:head>
                    <tr>
                        <th class="w-16 text-center">No</th>
                        <th>Kode Cabang</th>
                        <th>Nama Cabang</th>
                        <th>Tanggal Buat</th>
                        <th>Tanggal Update</th>
                        @unless(auth()->user()->hasRole('guest'))
                        <th class="text-center w-24">Aksi</th>
                        @endunless
                    </tr>
                </x-slot:head>

                @foreach ($configs as $config)
                    <tr class="hover:bg-base-300/40 transition duration-200">
                        <td class="text-center font-mono text-xs text-base-content/50">
                            {{ $loop->iteration + ($configs->currentPage() - 1) * $configs->perPage() }}
                        </td>
                        <td class="font-mono text-base-content/70 text-xs">
                            {{ $config->distributor_code }}
                        </td>
                        <td class="font-medium text-base-content/90">
                            {{ $config->config_name }}
                        </td>
                        <td class="text-xs text-base-content/60 font-mono">
                            {{ $config->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="text-xs text-base-content/60 font-mono">
                            {{ $config->updated_at->format('d M Y H:i') }}
                        </td>
                        @unless(auth()->user()->hasRole('guest'))
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('sales-configs.edit', base64_encode($config->id)) }}"
                                   class="btn btn-sm btn-ghost btn-square text-info hover:bg-info/10"
                                   title="Edit">
                                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                                </a>

                                <button wire:click.prevent="confirmDelete({{ $config->id }})"
                                        class="btn btn-sm btn-ghost btn-square text-error hover:bg-error/10"
                                        title="Hapus">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                        @endunless
                    </tr>
                @endforeach
            </x-ui.table>

            @if($configs->hasPages())
                <div class="mt-4 pt-4 border-t border-base-300">
                    {{ $configs->links() }}
                </div>
            @endif
        </x-card>
    </div>

    <!-- Delete Confirmation Modal (Admin/Non-Guest only) -->
    @unless(auth()->user()->hasRole('guest'))
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
    @endunless
</div>