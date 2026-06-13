<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Tambah Config Sales Invoice Distributor</x-slot>

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        <form wire:submit.prevent="save" class="flex-1 flex flex-col min-h-0">

            <!-- Action Buttons (Header Card) -->
            <div class="bg-base-200/50 p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-wrap items-center gap-3 shadow-sm z-10">
                <x-ui.action-button
                    type="save"
                />
                <x-ui.action-button
                    type="default"
                    label="Kembali"
                    icon="arrow-left"
                    href="{{ route('sales-configs.index') }}"
                    class="btn-ghost border border-base-300 hover:bg-base-300/50"
                />
            </div>

            <!-- Scrollable Form Area -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-5 md:p-6 lg:p-8">
                <div class="max-w-4xl mx-auto">
                    @if ($errors->any())
                        <div class="mb-8 p-4 bg-error/10 border border-error/20 text-error rounded-xl flex items-start space-x-3">
                            <x-heroicon-s-exclamation-circle class="w-5 h-5 mt-0.5 flex-shrink-0" />
                            <div class="flex-1 text-sm">
                                <strong class="font-bold block mb-1">Whoops!</strong> Ada beberapa masalah dengan input Anda.
                            </div>
                        </div>
                    @endif

                    <div class="mb-8 max-w-xl">
                        <label for="distributor_search" class="block text-sm font-semibold text-base-content mb-2">
                            Pilih Distributor <span class="text-error">*</span>
                        </label>
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <!-- Input untuk menampilkan yang sudah dipilih atau untuk mencari -->
                            <div class="relative group">
                                <input type="text"
                                       wire:model.live.debounce.300ms="distributorSearch"
                                       @focus="open = true"
                                       id="distributor_search"
                                       placeholder="Ketik untuk mencari distributor..."
                                       class="input w-full bg-base-200/50 border border-white/5 text-base-content text-sm rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-transparent transition-all placeholder:text-base-content/30"
                                       x-show="!$wire.distributor_code">
                                
                                <div x-show="$wire.distributor_code" class="w-full flex items-center justify-between px-4 py-3 bg-base-200/50 border border-white/5 rounded-xl text-sm font-medium text-base-content shadow-sm">
                                    <span x-text="$wire.selectedDistributorName"></span>
                                    <button type="button" @click="$wire.set('distributor_code', ''); $wire.set('selectedDistributorName', '')" class="text-error/70 hover:text-error transition-colors p-1">
                                        <x-heroicon-s-x-mark class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Dropdown hasil pencarian -->
                            <div x-show="open && $wire.distributorSearch.length >= 2 && !$wire.distributor_code"
                                 x-transition
                                 class="absolute z-30 w-full mt-2 bg-base-200 border border-white/10 rounded-xl shadow-xl shadow-black/40 max-h-60 overflow-y-auto custom-scrollbar">
                                @if($distributors->isNotEmpty())
                                    <div class="py-1">
                                        @foreach($distributors as $distributor)
                                            <div wire:click="selectDistributor('{{ $distributor->distributor_code }}', '{{ addslashes($distributor->distributor_name) }}')" 
                                                 @click="open = false"
                                                 class="cursor-pointer px-4 py-2 text-sm text-base-content/80 hover:text-base-content hover:bg-primary/20 transition-colors">
                                                <span class="font-medium">{{ $distributor->distributor_name }}</span> 
                                                <span class="opacity-50 ml-1">({{ $distributor->distributor_code }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 text-sm text-base-content/50 text-center">Tidak ada distributor ditemukan.</div>
                                @endif
                            </div>
                        </div>
                        @error('distributor_code') <p class="mt-2 text-xs text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <h5 class="text-lg font-bold text-base-content mb-2 flex items-center gap-2">
                            <x-heroicon-o-table-cells class="w-5 h-5 text-primary" />
                            Pemetaan Kolom Invoice
                        </h5>
                        <p class="text-sm text-base-content/60 leading-relaxed">Nama Kolom di File (Kolom Dist) diisi dengan Nama header kolom file excel distributor. Nomor Urut Kolom diisi dengan posisi kolom (A=1, B=2, dst). Kolom dengan tanda <span class="text-error font-bold">*</span> bersifat wajib.</p>
                    </div>

                    <div class="overflow-x-auto rounded-xl ring-1 ring-white/5">
                        <table class="min-w-full text-left text-sm whitespace-nowrap">
                            <thead class="uppercase tracking-wider text-xs font-bold text-base-content/50 bg-base-200/50">
                                <tr>
                                    <th class="px-6 py-4 w-1/3">Kolom Database</th>
                                    <th class="px-6 py-4 w-2/5">Nama Kolom di File (Kolom Dist)</th>
                                    <th class="px-6 py-4 w-1/5">Nomor Urut Kolom</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @php
                                    $requiredFields = [
                                        'distributor_code','invoice_no', 'invoice_date', 'salesman_code', 'salesman_name',
                                        'customer_code', 'customer_name', 'product_code', 'product_name', 'net_amount'
                                    ];
                                @endphp

                                @foreach ($staticHeaders as $field => $alias)
                                <tr class="hover:bg-white/5 transition-colors duration-200">
                                    <td class="px-6 py-4 font-semibold text-base-content/90 flex items-center gap-1">
                                        {{ $alias }}
                                        @if(in_array($field, $requiredFields))
                                            <span class="text-error font-bold">*</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <input type="text"
                                               wire:model.live="config.{{ $field }}.header_inv_dist"
                                               class="input input-sm w-full bg-base-200/50 border border-white/5 rounded-lg focus:ring-2 focus:ring-primary/50 text-base-content @error('config.'.$field.'.header_inv_dist') border-error focus:ring-error/50 @enderror"
                                               placeholder="Contoh: {{ $alias }}">
                                        @error('config.'.$field.'.header_inv_dist')
                                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-3">
                                        <input type="number"
                                               wire:model.live="config.{{ $field }}.index"
                                               class="input input-sm w-full bg-base-200/50 border border-white/5 rounded-lg focus:ring-2 focus:ring-primary/50 text-base-content @error('config.'.$field.'.index') border-error focus:ring-error/50 @enderror"
                                               min="0">
                                        @error('config.'.$field.'.index')
                                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
