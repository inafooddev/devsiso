<div>
    <x-slot name="title">Data Master Distributor</x-slot>

    <!-- Notifikasi Toast (Floating di Kanan Atas) -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="bg-white/90 backdrop-blur-sm border border-emerald-200 p-4 rounded-xl shadow-lg flex items-start gap-3 pointer-events-auto max-w-sm w-full">
                <svg class="h-6 w-6 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <h3 class="text-sm font-semibold text-emerald-800">Berhasil</h3>
                    <p class="text-sm text-emerald-600 mt-0.5">{{ session('message') }}</p>
                </div>
                <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="bg-white/90 backdrop-blur-sm border border-red-200 p-4 rounded-xl shadow-lg flex items-start gap-3 pointer-events-auto max-w-sm w-full">
                <svg class="h-6 w-6 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800">Terjadi Kesalahan</h3>
                    <p class="text-sm text-red-600 mt-0.5">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="ml-auto text-red-400 hover:text-red-600">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div class="mt-4 sm:mt-0 flex flex-wrap gap-3">
                <a href="{{ route('master-distributors.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Distributor Baru
                </a>
                
                <button wire:click="export" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <svg wire:loading.remove wire:target="export" class="mr-2 h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <svg wire:loading wire:target="export" class="animate-spin mr-2 h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Export Excel
                </button>

                <button wire:click="synchronize" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <svg wire:loading.remove wire:target="synchronize" class="mr-2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <svg wire:loading wire:target="synchronize" class="animate-spin mr-2 h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="synchronize">Sinkronisasi</span>
                    <span wire:loading wire:target="synchronize">Menyinkronkan...</span>
                </button>
            </div>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-2xl overflow-hidden">
            <!-- Filter Bar -->
            <div class="px-6 py-4 border-b border-slate-100 bg-white">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, kode..." class="block w-full rounded-lg border-0 py-2 pl-9 pr-3 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-shadow">
                    </div>

                    <!-- Status -->
                    <select wire:model.live="statusFilter" class="block w-full rounded-lg border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6 cursor-pointer transition-shadow">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>

                    <!-- Region -->
                    <select wire:model.live="regionFilter" class="block w-full rounded-lg border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6 cursor-pointer transition-shadow">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <!-- Area -->
                    <select wire:model.live="areaFilter" 
                            @disabled(!$regionFilter) 
                            class="block w-full rounded-lg border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-shadow disabled:bg-slate-50 disabled:text-slate-500 disabled:ring-slate-200 disabled:cursor-not-allowed">
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Table Container (Relative & Overflow for Sticky Header) -->
            <div class="relative overflow-x-auto min-h-[400px]">
                
                <!-- Loading Overlay -->
                <div wire:loading class="absolute inset-0 z-20 bg-white/60 backdrop-blur-sm flex items-center justify-center">
                    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-md ring-1 ring-slate-200">
                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-sm font-medium text-slate-700">Memuat data...</span>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600 w-16">No</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Region</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Area</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Kode</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Distributor</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Cabang</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Supervisor</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Status</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Created</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600">Updated</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-slate-600 text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($distributors as $index => $distributor)
                            <tr wire:key="distributor-{{ $distributor->distributor_code }}" class="hover:bg-slate-50 transition-colors duration-150 group">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                    {{ $distributors->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $distributor->region_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $distributor->area_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600 font-mono">{{ $distributor->distributor_code }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $distributor->distributor_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $distributor->branch_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $distributor->supervisor?->description ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($distributor->is_active)
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $distributor->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $distributor->updated_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <!-- Btn Map -->
                                        <button wire:click="showMap('{{ $distributor->distributor_code }}')" class="p-1.5 rounded-md text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-all" title="Lihat Peta">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </button>
                                        
                                        <!-- Btn Edit -->
                                        <a href="{{ route('master-distributors.edit', $distributor->distributor_code) }}" class="p-1.5 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" title="Edit Data">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>

                                        <!-- Btn Delete -->
                                        <button wire:click="confirmDelete('{{ $distributor->distributor_code }}')" class="p-1.5 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all" title="Hapus Data">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                    <!-- Fallback for touch devices -->
                                    <div class="flex sm:hidden items-center justify-center gap-3">
                                        <button wire:click="showMap('{{ $distributor->distributor_code }}')" class="text-sky-600"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg></button>
                                        <a href="{{ route('master-distributors.edit', $distributor->distributor_code) }}" class="text-indigo-600"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></a>
                                        <button wire:click="confirmDelete('{{ $distributor->distributor_code }}')" class="text-rose-600"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="h-12 w-12 mb-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <h3 class="text-base font-semibold text-slate-900">Tidak ada data ditemukan</h3>
                                        <p class="mt-1 text-sm text-slate-500">Coba ubah filter pencarian atau tambahkan distributor baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($distributors->hasPages())
                <div class="px-6 py-4 bg-white border-t border-slate-200">
                    {{ $distributors->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Hapus (Glassmorphism + Modern Backdrop) -->
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0">
        
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="open = false"></div>

        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all sm:max-w-lg w-full p-6 text-center sm:text-left">
            
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg font-semibold leading-6 text-slate-900">Hapus Data Distributor</h3>
                    <div class="mt-2">
                        <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus data ini? Semua data yang terkait dengan distributor ini mungkin akan hilang secara permanen. Aksi ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
            </div>
            <div class="mt-6 sm:mt-5 sm:flex sm:flex-row-reverse gap-3">
                <button wire:click="delete" wire:loading.attr="disabled" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent bg-rose-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 sm:w-auto sm:text-sm transition-all">
                    <svg wire:loading wire:target="delete" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Hapus Data
                </button>
                <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg bg-white px-4 py-2 text-base font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-all">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Map -->
    <div x-data="{ 
        open: @entangle('isMapModalOpen'),
        latitude: @entangle('mapLatitude'),
        longitude: @entangle('mapLongitude'),
        distributorName: @entangle('mapDistributorName'),
        initMap() {
            if (!this.open) return;
            const waitForLeaflet = setInterval(() => {
                if (typeof L !== 'undefined') {
                    clearInterval(waitForLeaflet);
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const mapElement = document.getElementById('distributorMap');
                            if (!mapElement) return;
                            
                            const lat = parseFloat(this.latitude);
                            const lng = parseFloat(this.longitude);
                            
                            if (isNaN(lat) || isNaN(lng)) return;
                            
                            if (window.distributorMapInstance) {
                                window.distributorMapInstance.remove();
                                window.distributorMapInstance = null;
                            }
                            
                            try {
                                const map = L.map('distributorMap').setView([lat, lng], 16);
                                window.distributorMapInstance = map;
                                
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors &copy; <a href=\'https://carto.com/attributions\'>CARTO</a>'
                                }).addTo(map);
                                
                                const customIcon = L.divIcon({
                                    className: 'custom-pin',
                                    html: `<svg class='w-8 h-8 text-indigo-600 drop-shadow-md' fill='currentColor' viewBox='0 0 24 24'><path d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'/></svg>`,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 32],
                                    popupAnchor: [0, -32]
                                });

                                L.marker([lat, lng], {icon: customIcon}).addTo(map)
                                 .bindPopup(`<div class='font-sans px-1 py-0.5'><strong class='text-slate-800'>${this.distributorName}</strong><br><span class='text-xs text-slate-500'>${lat}, ${lng}</span></div>`)
                                 .openPopup();
                                
                                setTimeout(() => { map.invalidateSize(); }, 200);
                            } catch (error) { console.error('Map error:', error); }
                        }, 300);
                    });
                }
            }, 100);
            setTimeout(() => { clearInterval(waitForLeaflet); }, 5000);
        }
    }" 
    x-show="open" 
    @open-map.window="initMap()"
    x-cloak 
    class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0">
        
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="open = false"></div>

        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all sm:max-w-3xl w-full flex flex-col">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 leading-tight" x-text="distributorName || 'Lokasi Distributor'"></h3>
                        <p class="text-xs text-slate-500 mt-0.5">Koordinat: <span x-text="latitude"></span>, <span x-text="longitude"></span></p>
                    </div>
                </div>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <!-- Map Container -->
            <div class="p-4 bg-slate-50">
                <div id="distributorMap" style="height: 400px; width: 100%;" class="rounded-xl shadow-inner border border-slate-200 bg-slate-200 relative overflow-hidden z-0">
                    <!-- Skeleton / Placeholder -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                        <svg class="animate-spin h-8 w-8 mb-3 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-sm font-medium">Memuat Peta Interaktif...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @script
    <script>
        Livewire.on('map-opened', () => {
            setTimeout(() => { window.dispatchEvent(new CustomEvent('open-map')); }, 100);
        });
    </script>
    @endscript
</div>