<div>
    <x-slot name="title">Tambah Config Sales Invoice Distributor</x-slot>

    <div class="mx-auto px-6 py-8">
        <x-card>
            {{-- 1. Skrip Alpine.js dan x-data telah dihapus --}}
            {{-- 2. Form sekarang disubmit langsung ke metode 'save' di Livewire --}}
            <form wire:submit.prevent="save">

                <!-- Action Buttons (Sticky) -->
                <div class="sticky top-0 z-10 bg-white -mx-6 -mt-6 mb-6">
                    <div class="flex items-center space-x-3 p-4 border-b border-gray-200">
                        <button type="submit" 
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 002-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            <span>Simpan</span>
                        </button>
                        <a href="{{ route('sales-configs.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <!-- 3. Ringkasan Error sekarang dikontrol oleh Livewire -->
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <strong class="font-semibold">Whoops!</strong> Ada beberapa masalah dengan input Anda.
                            </div>
                        </div>
                    </div>
                @endif

                    <div class="mb-6">
                        <label for="distributor_search" class="block text-sm font-medium text-gray-700 mb-1">
                            Pilih Distributor <span class="text-red-500">*</span>
                        </label>
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <!-- Input untuk menampilkan yang sudah dipilih atau untuk mencari -->
                            <div class="relative">
                                <input type="text"
                                       wire:model.live.debounce.300ms="distributorSearch"
                                       @focus="open = true"
                                       id="distributor_search"
                                       placeholder="Ketik untuk mencari distributor..."
                                       class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       x-show="!$wire.distributor_code">
                                
                                <div x-show="$wire.distributor_code" class="w-full flex items-center justify-between form-input bg-gray-100 border-blue-300 shadow-sm p-1">
                                    <span x-text="$wire.selectedDistributorName"></span>
                                    <button type="button" @click="$wire.set('distributor_code', ''); $wire.set('selectedDistributorName', '')" class="text-red-500 border hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                                </div>
                            </div>
                            
                            <!-- Dropdown hasil pencarian -->
                            <div x-show="open && $wire.distributorSearch.length >= 2 && !$wire.distributor_code"
                                 x-transition
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @if($distributors->isNotEmpty())
                                    @foreach($distributors as $distributor)
                                        <div wire:click="selectDistributor('{{ $distributor->distributor_code }}', '{{ addslashes($distributor->distributor_name) }}')" 
                                             @click="open = false"
                                             class="cursor-pointer p-2 hover:bg-gray-100">
                                            {{ $distributor->distributor_name }} ({{ $distributor->distributor_code }})
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-2 text-gray-500">Tidak ada distributor ditemukan.</div>
                                @endif
                            </div>
                        </div>
                        @error('distributor_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                <div class="mb-6">
                    <h5 class="text-lg font-semibold text-gray-800 mb-2">Pemetaan Kolom Invoice</h5>
                    <p class="text-sm text-gray-600">Nama Kolom di File (Kolom Dist) diisi dengan Nama header kolom file distributor dan Nomor Urut Kolom di isi kolom A=1 kolom B=2 dst. (<span class="text-red-500">*</span>) bersifat wajib diisi lengkap</p>
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Kolom Database</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Nama Kolom di File (Kolom Dist)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Nomor Urut Kolom</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $requiredFields = [
                                    'distributor_code','invoice_no', 'invoice_date', 'salesman_code', 'salesman_name',
                                    'customer_code', 'customer_name', 'product_code', 'product_name', 'net_amount'
                                ];
                            @endphp

                            @foreach ($staticHeaders as $field => $alias)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-800 align-top pt-5">
                                    {{ $alias }}
                                    @if(in_array($field, $requiredFields))
                                        <span class="text-red-500">*</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text"
                                           wire:model.live="config.{{ $field }}.header_inv_dist"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors text-sm @error('config.'.$field.'.header_inv_dist') border-red-500 @enderror"
                                           placeholder="Contoh: {{ $alias }}">
                                    @error('config.'.$field.'.header_inv_dist')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number"
                                           wire:model.live="config.{{ $field }}.index"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors text-sm @error('config.'.$field.'.index') border-red-500 @enderror"
                                           min="0">
                                    @error('config.'.$field.'.index')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </x-card>
    </div>
</div>

