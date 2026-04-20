<div>
    <x-slot name="title">Data Config Sales Invoice Distributor</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
            
            <!-- Grouping Tombol Aksi -->
            <div class="flex items-center w-full sm:w-auto gap-3">
                <!-- Tombol Primary: Tambah Data -->
                <a href="{{ route('sales-configs.create') }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl font-medium text-sm hover:bg-slate-800 hover:shadow-md hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Data</span>
                </a>

                <!-- Tombol Secondary: Import -->
                <!-- Icon diganti menggunakan icon import (panah ke bawah) yang lebih merepresentasikan tindakan -->
                <a href="{{ route('sales-invoices.import') }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-medium text-sm hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-1 transition-all duration-200">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Import</span>
                </a>
            </div>
            
            <!-- Kolom Pencarian -->
            <!-- Ditambahkan efek group-focus-within agar icon menyala saat input di-klik -->
            <div class="w-full sm:w-72 md:w-80 relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4.5 w-4.5 text-slate-400 group-focus-within:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <!-- Menggunakan bg yang sedikit redup dan menjadi putih bersih saat difokuskan -->
                <input wire:model.live.debounce.300ms="search" type="text" 
                       placeholder="Cari Kode atau Nama Cabang..." 
                       class="w-full bg-slate-50/50 border border-slate-200 text-slate-900 text-sm rounded-xl pl-10 pr-4 py-2.5 focus:bg-white focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 placeholder:text-slate-400">
            </div>
            
        </div>

        <!-- Notifikasi Sukses -->
        @if (session()->has('message'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 bg-emerald-100 rounded-full p-1">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition-colors p-1 hover:bg-emerald-100 rounded-lg">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Table Card -->
        <x-card class="shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-slate-100 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">Kode Cabang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">Nama Cabang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">Tanggal Buat</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">Tanggal Update</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse ($configs as $config)
                            <tr class="hover:bg-slate-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $loop->iteration + ($configs->currentPage() - 1) * $configs->perPage() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                                    {{ $config->distributor_code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 font-medium">
                                    {{ $config->config_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $config->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $config->updated_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Edit Button -->
                                        <a href="{{ route('sales-configs.edit', base64_encode($config->id)) }}"
                                           class="p-2 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-500 hover:text-white hover:shadow-sm focus:ring-2 focus:ring-offset-1 focus:ring-amber-500 transition-all duration-200 flex items-center justify-center"
                                           title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <!-- Delete Button -->
                                        <button wire:click.prevent="confirmDelete({{ $config->id }})"
                                                class="p-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-500 hover:text-white hover:shadow-sm focus:ring-2 focus:ring-offset-1 focus:ring-rose-500 transition-all duration-200 flex items-center justify-center"
                                                title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 bg-slate-50/30">
                                    <div class="h-16 w-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-800">Tidak ada data ditemukan</h3>
                                    <p class="mt-2 text-sm text-slate-500">Silakan klik "Tambah Data" untuk membuat konfigurasi baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($configs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-white">
                {{ $configs->links() }}
            </div>
            @endif
        </x-card>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    @if($isDeleteModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
        <!-- Modern Backdrop Blur -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" wire:click="closeDeleteModal"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 transform transition-all border border-slate-100">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100">
                    <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-lg font-semibold text-slate-900">Hapus Konfigurasi</h3>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">Apakah Anda yakin ingin menghapus konfigurasi ini? Data yang dihapus tidak dapat dikembalikan.</p>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button wire:click="closeDeleteModal" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-slate-200 transition-all shadow-sm">
                    Batal
                </button>
                <button wire:click="delete" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-rose-600 text-white rounded-xl text-sm font-semibold hover:bg-rose-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-rose-500 transition-all shadow-sm">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    @endif
</div>