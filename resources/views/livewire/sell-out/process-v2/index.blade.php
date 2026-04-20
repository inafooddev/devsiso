<div>
    <x-slot name="title">Generate Format Data Lama</x-slot>

   <div class="mx-auto px-6 py-8">
        <!-- Notifikasi -->
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <!-- Header Card & Actions -->
            <div class="px-6 py-4 border-b bg-gray-50 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                <div class="flex items-center space-x-2 flex-wrap">
                    <button wire:click="$set('isFilterModalOpen', true)" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus:border-blue-300 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filter Periode
                    </button>
                    <button wire:click="startProcess" wire:loading.attr="disabled" wire:target="startProcess"
                        @if(!$hasAppliedFilters) disabled @endif
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-50 transition ease-in-out duration-150 shadow-sm">
                        <svg wire:loading wire:target="startProcess" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="startProcess" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Mulai Proses
                    </button>

                </div>
            </div>

            <!-- Log Proses -->
            <div class="p-6" wire:poll.1500ms="syncLog">
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Log Proses</h4>
                <div class="w-full bg-gray-900 text-white rounded-md shadow-inner p-4 font-mono text-sm overflow-y-auto" style="height: 60vh;">
                    @if(empty($logLines))
                        <div class="text-gray-400">[INFO] - {{ now()->toDateTimeString() }}</div>
                        <div class="text-gray-400">Menunggu proses dimulai. Silakan terapkan filter dan klik "Mulai Proses".</div>
                    @else
                        @foreach($logLines as $log)
                            <div class="{{ $log['type'] == 'error' ? 'text-red-400' : ($log['type'] == 'success' ? 'text-green-400' : ($log['type'] == 'warning' ? 'text-yellow-400' : 'text-blue-300')) }}">
                                [{{ strtoupper($log['type'] ?? 'INFO') }}] - {{ $log['message'] }}
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Filter -->
    <div x-data="{ open: @entangle('isFilterModalOpen') }" x-show="open" x-cloak class="fixed z-20 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="open" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="applyFilters">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filter Pemrosesan Data</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="regionFilter" class="block text-sm font-medium text-gray-700">Region</label>
                                <select wire:model.live="regionFilter" id="regionFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        
                                        {{-- Opsi ini hanya muncul untuk Admin --}}
                                        @if(auth()->user()->hasRole('admin'))
                                            <option value="">Semua Region</option>
                                        @else
                                            <option value="">-- Pilih Region --</option>
                                        @endif

                                        @foreach($regions as $region)
                                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                        @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="areaFilter" class="block text-sm font-medium text-gray-700">Area</label>
                                <select wire:model.live="areaFilter" id="areaFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" @if(!$regionFilter) disabled @endif>
                                    <option value="">Semua Area</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="distributorFilter" class="block text-sm font-medium text-gray-700">Distributor</label>
                                <select wire:model.defer="distributorFilter" id="distributorFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" @if(!$areaFilter) disabled @endif>
                                    <option value="">Semua Distributor</option>
                                    @foreach($distributors as $distributor)
                                        <option value="{{ $distributor->distributor_code }}">{{ $distributor->distributor_name }} ({{ $distributor->distributor_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="monthFilter" class="block text-sm font-medium text-gray-700">Bulan</label>
                                    <select wire:model.defer="monthFilter" id="monthFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="yearFilter" class="block text-sm font-medium text-gray-700">Tahun</label>
                                    <select wire:model.defer="yearFilter" id="yearFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Terapkan</button>
                        <button wire:click="resetFilters" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Reset</button>
                        <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

