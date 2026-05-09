<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-base-content">Unit Mapping</h2>
                <p class="text-sm text-base-content/70 mt-1">Kelola pemetaan satuan produk dari file Excel ke standar sistem (CTN/PCK/PCS).</p>
            </div>

        </div>

        @if (count($unmappedUnits) > 0)
            <div class="mb-8 bg-error/10 border border-error/20 rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-error/20 flex justify-between items-center">
                    <h3 class="font-bold text-error flex items-center gap-2">
                        <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                        Unit Perlu Mapping Segera (Hasil Scan Import)
                    </h3>
                    <span class="badge badge-error text-white font-bold">{{ count($unmappedUnits) }} Unit</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-compact w-full bg-transparent">
                        <thead>
                            <tr class="text-error/70 border-b border-error/10">
                                <th>Distributor</th>
                                <th>Raw Unit (File)</th>
                                <th class="w-24 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unmappedUnits as $unmapped)
                                <tr class="border-b border-error/5 hover:bg-error/5 transition-colors">
                                    <td class="font-bold text-xs">{{ $unmapped->distributor_code }}</td>
                                    <td class="font-medium font-mono text-sm">{{ $unmapped->raw_unit }}</td>
                                    <td class="text-center">
                                        <button wire:click="mapUnmapped('{{ $unmapped->distributor_code }}', '{{ $unmapped->raw_unit }}')" class="btn btn-error btn-xs text-white uppercase font-bold text-[10px]">
                                            Map Sekarang
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if (session()->has('message'))
            <div class="mb-6">
                <x-ui.notif type="success" dismissible="true">
                    {{ session('message') }}
                </x-ui.notif>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6">
                <x-ui.notif type="error" dismissible="true">
                    {{ session('error') }}
                </x-ui.notif>
            </div>
        @endif

        <div class="bg-base-100 shadow-sm border border-base-200 rounded-xl overflow-hidden">
            <div class="p-4 border-b border-base-200 flex flex-wrap gap-4 justify-between items-center bg-base-200/50">
                <div class="w-full md:w-1/3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-base-content/50" />
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" class="input input-sm input-bordered w-full pl-10" placeholder="Cari Source Unit...">
                    </div>
                </div>
                <div class="w-full md:w-auto">
                    <select wire:model.live="filterUnit" class="select select-sm select-bordered w-full md:w-auto">
                        <option value="">Semua Standar Unit</option>
                        <option value="CTN">Hanya CTN</option>
                        <option value="PCK">Hanya PCK</option>
                        <option value="PCS">Hanya PCS</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th>Distributor</th>
                            <th>Raw Unit (File)</th>
                            <th>Mapped Unit (Sistem)</th>
                            <th class="w-32 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mappings as $mapping)
                            <tr class="hover:bg-base-200/30 transition-colors">
                                <td class="font-bold text-xs">{{ $mapping->distributor_code }}</td>
                                <td class="font-medium font-mono text-sm">{{ $mapping->raw_unit }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $mapping->mapped_unit === 'CTN' ? 'badge-primary' : ($mapping->mapped_unit === 'PCK' ? 'badge-secondary' : 'badge-accent') }}">
                                        {{ $mapping->mapped_unit }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="edit({{ $mapping->id }})" class="btn btn-ghost btn-xs text-primary" title="Edit">
                                            <x-heroicon-s-pencil class="w-4 h-4" />
                                        </button>
                                        <button wire:click="delete({{ $mapping->id }})" class="btn btn-ghost btn-xs text-error" onclick="return confirm('Yakin hapus mapping ini?')" title="Hapus">
                                            <x-heroicon-s-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-8 text-base-content/50">
                                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                    Tidak ada data unit mapping ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($mappings->hasPages())
                <div class="p-4 border-t border-base-200 bg-base-100">
                    {{ $mappings->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form -->
    <x-ui.modal id="modal-mapping" title="{{ $mappingId ? 'Edit Mapping' : 'Tambah Mapping Baru' }}" icon="{{ $mappingId ? 'pencil' : 'plus' }}" size="md" :dismissible="false" :open="$isModalOpen" wire:close="$set('isModalOpen', false)">
        <form wire:submit.prevent="store" id="form-mapping">
            <div class="space-y-4 mb-4">
                <div>
                    <label class="label"><span class="label-text font-medium">Distributor Code</span></label>
                    <input type="text" wire:model="distributor_code" class="input input-bordered w-full font-mono uppercase bg-base-200" readonly>
                </div>
                <div>
                    <label class="label"><span class="label-text font-medium">Raw Unit (Dari File Excel)</span></label>
                    <input type="text" wire:model="raw_unit" class="input input-bordered w-full font-mono uppercase bg-base-200" readonly>
                </div>
                
                <div>
                    <label class="label"><span class="label-text font-medium">Mapped Unit (Sistem)</span></label>
                    <select wire:model="mapped_unit" class="select select-bordered w-full">
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
