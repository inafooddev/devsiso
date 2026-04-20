<div>
    <x-slot name="title">Edit Config Sales Invoice Distributor</x-slot>

    <div class="mx-auto px-6 py-8">
        <x-card>
            {{-- Form sekarang disubmit ke metode 'update' di Livewire --}}
            <form wire:submit.prevent="update">

                <!-- Action Buttons (Sticky) -->
                <div class="sticky top-0 z-10 bg-white -mx-6 -mt-6 mb-6">
                    <div class="flex items-center space-x-3 p-4 border-b border-gray-200">
                        <button type="submit"
                                class="px-6 py-2 bg-[#081c3a] text-white rounded-lg hover:bg-blue-900 transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Perbarui Konfigurasi</span>
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

                <!-- Ringkasan Error dari Livewire -->
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Branch Code -->
                    <div>
                        <label for="distributor_code" class="block text-sm font-semibold text-gray-700 mb-2">
                            Kode Cabang <span class="text-red-500">*</span>
                        </label>
                        {{-- wire:model.live untuk validasi real-time --}}
                        <input type="text"
                                readonly
                               wire:model.live="distributor_code"
                               id="distributor_code"
                               class="w-full bg-gray-100 readonly px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('distributor_code') border-red-500 @enderror">
                        @error('distributor_code')
                            <p class="mt-1 readonly text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Config Name -->
                    <div>
                        <label for="config_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Cabang <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                                readonly
                               wire:model.live="config_name"
                               id="config_name"
                               class="w-full px-4 py-2 border bg-gray-100 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('config_name') border-red-500 @enderror">
                        @error('config_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <h5 class="text-lg font-semibold text-gray-800 mb-2">Pemetaan Kolom Invoice</h5>
                    <p class="text-sm text-gray-600">Isi "Nama Kolom di File" dan "Nomor Urut Kolom" sesuai kebutuhan. Kolom yang ditandai (<span class="text-red-500">*</span>) wajib diisi lengkap.</p>
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
