<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Unit Mapping</x-slot>

    {{-- Notifikasi --}}
    @if (session()->has('message') || session()->has('error'))
    <div class="shrink-0 space-y-3">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0 flex items-start">
                <x-heroicon-s-check-circle class="w-5 h-5 mt-0.5 shrink-0" />
                <div class="flex-1">
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                    <div class="text-[10px]">{{ session('message') }}</div>
                </div>
                <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-success/20 transition-all">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0 flex items-start">
                <x-heroicon-s-x-circle class="w-5 h-5 mt-0.5 shrink-0" />
                <div class="flex-1">
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                    <div class="text-[10px]">{{ session('error') }}</div>
                </div>
                <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-error/20 transition-all">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif
    </div>
    @endif

    {{-- Alert Unmapped Units --}}
    @if (count($unmappedUnits) > 0)
        <div class="shrink-0 bg-error/10 border border-error/20 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-error/20 flex justify-between items-center">
                <h3 class="font-bold text-error flex items-center gap-2 text-sm">
                    <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                    Unit Perlu Mapping Segera (Hasil Scan Import)
                </h3>
                <div class="flex items-center gap-3">
                    <span class="badge badge-error text-white font-bold">{{ count($unmappedUnits) }} Unit</span>
                    @canEdit('product-unit-mappings.index')
                        <button wire:click="clearUnmapped" onclick="return confirm('Anda yakin ingin menghapus semua daftar unit yang belum di-mapping ini?')" class="btn btn-xs btn-error btn-outline shadow-sm font-bold uppercase text-[10px] rounded-lg border-error/30 hover:border-error bg-base-100/50">
                            <x-heroicon-s-trash class="w-3 h-3 mr-1" /> Clear All
                        </button>
                    @endcanEdit
                </div>
            </div>
            <div class="overflow-x-auto max-h-48">
                <table class="table table-xs table-pin-rows w-full bg-transparent">
                    <thead class="text-xs uppercase tracking-wider text-error/70 bg-error/5">
                        <tr>
                            <th>Distributor</th>
                            <th>Raw Unit (File)</th>
                            <th class="w-24 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach ($unmappedUnits as $unmapped)
                            <tr class="border-b border-error/5 hover:bg-error/10 transition-colors group">
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-error/90 group-hover:text-error transition-colors">
                                            {{ $unmapped->masterDistributor->distributor_name ?? 'N/A' }}
                                        </span>
                                        <span class="text-[10px] text-error/60 font-mono mt-0.5">{{ $unmapped->distributor_code }}</span>
                                    </div>
                                </td>
                                <td class="font-medium font-mono text-sm">{{ $unmapped->raw_unit }}</td>
                                <td class="text-center">
                                    @canEdit('product-unit-mappings.index')
                                    <button wire:click="mapUnmapped('{{ $unmapped->distributor_code }}', '{{ $unmapped->raw_unit }}')" class="btn btn-error btn-xs text-white uppercase font-bold text-[10px] rounded-lg">
                                        Map Sekarang
                                    </button>
                                    @else
                                    <span class="text-xs text-base-content/50 italic">View Only</span>
                                    @endcanEdit
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Mapping Unit</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola pemetaan satuan produk ke standar sistem</p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari data..." />

                {{-- Filter --}}
                <select wire:model.live="filterUnit" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Standar Unit</option>
                    <option value="CTN">Hanya CTN</option>
                    <option value="PCK">Hanya PCK</option>
                    <option value="PCS">Hanya PCS</option>
                </select>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            @if ($mappings->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40 h-full">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5 shadow-inner">
                        <x-heroicon-s-inbox class="w-10 h-10 text-base-content/30" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Data Kosong</h3>
                    <p class="text-[11px] text-center max-w-xs leading-relaxed">Tidak ada pemetaan unit yang cocok dengan filter atau pencarian Anda.</p>
                </div>
            @else
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th>Distributor</th>
                            <th>Raw Unit (File)</th>
                            <th>Mapped Unit (Sistem)</th>
                            <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach ($mappings as $mapping)
                            <tr wire:key="mapping-{{ $mapping->id }}" class="hover:bg-base-200/50 transition-colors group">
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">
                                            {{ $mapping->masterDistributor->distributor_name ?? 'N/A' }}
                                        </span>
                                        <span class="text-[10px] text-base-content/40 font-mono mt-0.5">{{ $mapping->distributor_code }}</span>
                                    </div>
                                </td>
                                <td class="font-medium font-mono text-sm">{{ $mapping->raw_unit }}</td>
                                <td>
                                    <span class="badge badge-sm rounded-lg {{ $mapping->mapped_unit === 'CTN' ? 'badge-primary' : ($mapping->mapped_unit === 'PCK' ? 'badge-secondary' : 'badge-accent') }}">
                                        {{ $mapping->mapped_unit }}
                                    </span>
                                </td>
                                <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                    <div class="flex items-center justify-center gap-1">
                                        @canEdit('product-unit-mappings.index')
                                            <x-ui.action-button type="edit" wire:click="edit({{ $mapping->id }})" />
                                            <x-ui.action-button type="delete" wire:click="delete({{ $mapping->id }})" onclick="return confirm('Yakin hapus mapping ini?')" />
                                        @else
                                            <span class="text-xs text-base-content/50 italic">View Only</span>
                                        @endcanEdit
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        
        {{-- Footer Card (Pagination) --}}
        @if($mappings->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $mappings->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
    <x-ui.modal id="modal-mapping" title="{{ $mappingId ? 'Edit Mapping' : 'Tambah Mapping Baru' }}" icon="{{ $mappingId ? 'pencil' : 'plus' }}" size="md" :dismissible="false" :open="$isModalOpen" wire:close="$set('isModalOpen', false)">
        <form wire:submit.prevent="store" id="form-mapping">
            <div class="space-y-4 mb-4">
                <div>
                    <label class="label"><span class="label-text font-medium text-xs">Distributor Code</span></label>
                    <input type="text" wire:model="distributor_code" class="input input-sm rounded-xl input-bordered w-full font-mono uppercase bg-base-200" readonly>
                </div>
                <div>
                    <label class="label"><span class="label-text font-medium text-xs">Raw Unit (Dari File Excel)</span></label>
                    <input type="text" wire:model="raw_unit" class="input input-sm rounded-xl input-bordered w-full font-mono uppercase bg-base-200" readonly>
                </div>
                
                <div>
                    <label class="label"><span class="label-text font-medium text-xs">Mapped Unit (Sistem)</span></label>
                    <select wire:model="mapped_unit" class="select select-sm rounded-xl select-bordered w-full">
                        <option value="">-- Pilih Satuan Standar --</option>
                        <option value="CTN">CTN (Carton)</option>
                        <option value="PCK">PCK (Pack)</option>
                        <option value="PCS">PCS (Pieces)</option>
                    </select>
                    @error('mapped_unit') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-mapping').requestSubmit()">
                Simpan
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
