<div>
    <x-slot name="title">Export Detail Sell Out</x-slot>

    <div class="mx-auto px-6 py-8">
        <!-- Notifikasi -->
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                 class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <p>{{ session('message') }}</p>
            </div>
        @endif
         @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="export">
                <!-- Header Card -->
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-700">Filter Ekspor Sell Out</h3>
                </div>

                <!-- Body -->
                <div class="p-6">
                     <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                            <p class="font-bold">Oops! Ada beberapa kesalahan:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <!-- Periode -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="monthFilter" class="block text-sm font-medium text-gray-700">Bulan <span class="text-red-500">*</span></label>
                                <select wire:model.defer="monthFilter" id="monthFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="yearFilter" class="block text-sm font-medium text-gray-700">Tahun <span class="text-red-500">*</span></label>
                                <select wire:model.defer="yearFilter" id="yearFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Region (Radio) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Region <span class="text-red-500">*</span></label>
                            <div class="mt-2 p-2 border border-gray-300 rounded-md max-h-40 overflow-y-auto space-y-2">
                                @foreach($regions as $region)
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="regionFilter" name="regionFilter" value="{{ $region->region_code }}" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <span class="ml-2 text-sm text-gray-600">{{ $region->region_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('regionFilter') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Area (Radio) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Area <span class="text-red-500">*</span></label>
                            <div class="mt-2 p-2 border border-gray-300 rounded-md max-h-40 overflow-y-auto space-y-2 @if(empty($areas)) bg-gray-50 @endif">
                                @forelse($areas as $area)
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="areaFilter" name="areaFilter" value="{{ $area->area_code }}" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <span class="ml-2 text-sm text-gray-600">{{ $area->area_name }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500">Pilih region terlebih dahulu</p>
                                @endforelse
                            </div>
                            @error('areaFilter') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Distributor (Checkbox Listbox) -->
                        <div x-data="{ open: false }" class="relative">
                            <label class="block text-sm font-medium text-gray-700">Distributor <span class="text-red-500">*</span></label>
                            <button type="button" @click="open = !open" class="mt-1 relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @if(empty($distributors)) bg-gray-50 cursor-not-allowed @endif" @if(empty($distributors)) disabled @endif>
                                 <span class="block truncate">
                                    @if(count($distributorFilter) == count($distributors) && count($distributors) > 0)
                                        Semua Distributor
                                    @elseif(count($distributorFilter) > 0)
                                        {{ count($distributorFilter) }} Distributor terpilih
                                    @else
                                        Pilih Distributor
                                    @endif
                                </span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></span>
                            </button>
                            <div x-show="open" @click.away="open = false" class="relative z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-300">
                                <div class="p-2 flex justify-between">
                                    <button type="button" wire:click="selectAllDistributors" class="text-xs text-indigo-600 hover:text-indigo-900">Pilih Semua</button>
                                    <button type="button" wire:click="clearDistributors" class="text-xs text-gray-500 hover:text-gray-700">Hapus Pilihan</button>
                                </div>
                                <div class="max-h-40 overflow-y-auto p-2 space-y-2 border-t">
                                    @forelse($distributors as $distributor)
                                        <label class="flex items-center"><input type="checkbox" wire:model.live="distributorFilter" value="{{ $distributor->distributor_code }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"><span class="ml-2 text-sm text-gray-600">{{ $distributor->distributor_name }}</span></label>
                                    @empty
                                        <p class="text-sm text-gray-500 p-2">Pilih area terlebih dahulu</p>
                                    @endforelse
                                </div>
                            </div>
                            @error('distributorFilter') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-50 transition ease-in-out duration-150 shadow-sm"
                        wire:loading.attr="disabled">
                        <svg wire:loading wire:target="export" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="export" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
