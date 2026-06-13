<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Edit Config Sales Invoice Distributor</x-slot>

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Form sekarang disubmit ke metode 'update' di Livewire --}}
        <form wire:submit.prevent="update" class="flex-1 flex flex-col min-h-0">

            <!-- Action Buttons (Header Card) -->
            <div class="bg-base-200/50 p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-wrap items-center gap-3 shadow-sm z-10">
                <x-ui.action-button
                    type="save"
                    label="Perbarui Konfigurasi"
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
                    <!-- Ringkasan Error dari Livewire -->
                    @if ($errors->any())
                        <div class="mb-8 p-4 bg-error/10 border border-error/20 text-error rounded-xl flex items-start space-x-3">
                            <x-heroicon-s-exclamation-circle class="w-5 h-5 mt-0.5 flex-shrink-0" />
                            <div class="flex-1 text-sm">
                                <strong class="font-bold block mb-1">Whoops!</strong> Ada beberapa masalah dengan input Anda.
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Branch Code -->
                        <div>
                            <label for="distributor_code" class="block text-sm font-semibold text-base-content mb-2">
                                Kode Cabang <span class="text-error">*</span>
                            </label>
                            {{-- wire:model.live untuk validasi real-time --}}
                            <input type="text"
                                    readonly
                                   wire:model.live="distributor_code"
                                   id="distributor_code"
                                   class="input w-full bg-base-300/50 border border-white/5 text-base-content/70 rounded-xl cursor-not-allowed @error('distributor_code') border-error @enderror">
                            @error('distributor_code')
                                <p class="mt-2 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Config Name -->
                        <div>
                            <label for="config_name" class="block text-sm font-semibold text-base-content mb-2">
                                Nama Cabang <span class="text-error">*</span>
                            </label>
                            <input type="text"
                                    readonly
                                   wire:model.live="config_name"
                                   id="config_name"
                                   class="input w-full bg-base-300/50 border border-white/5 text-base-content/70 rounded-xl cursor-not-allowed @error('config_name') border-error @enderror">
                            @error('config_name')
                                <p class="mt-2 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <h5 class="text-lg font-bold text-base-content mb-2 flex items-center gap-2">
                            <x-heroicon-o-table-cells class="w-5 h-5 text-primary" />
                            Pemetaan Kolom Invoice
                        </h5>
                        <p class="text-sm text-base-content/60 leading-relaxed">Isi "Nama Kolom di File" dan "Nomor Urut Kolom" sesuai kebutuhan. Kolom yang ditandai (<span class="text-error font-bold">*</span>) wajib diisi lengkap.</p>
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
                                               class="input input-sm w-full bg-base-200/50 border rounded-lg focus:ring-2 focus:ring-primary/50 text-base-content transition-colors duration-200
                                                      {{ (!empty($config[$field]['header_inv_dist'])) ? 'border-primary/60' : 'border-white/5' }}
                                                      @error('config.'.$field.'.header_inv_dist') border-error focus:ring-error/50 @enderror"
                                               placeholder="Contoh: {{ $alias }}">
                                        @error('config.'.$field.'.header_inv_dist')
                                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-6 py-3">
                                        <input type="number"
                                               wire:model.live="config.{{ $field }}.index"
                                               class="input input-sm w-full bg-base-200/50 border rounded-lg focus:ring-2 focus:ring-primary/50 text-base-content transition-colors duration-200
                                                      {{ (isset($config[$field]['index']) && strval($config[$field]['index']) !== '') ? 'border-primary/60' : 'border-white/5' }}
                                                      @error('config.'.$field.'.index') border-error focus:ring-error/50 @enderror"
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