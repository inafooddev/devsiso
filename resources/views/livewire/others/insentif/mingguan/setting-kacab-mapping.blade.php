<div>
    <dialog id="setting_kacab_modal" class="modal modal-bottom sm:modal-middle" {{ $isOpen ? 'open' : '' }}>
        <div class="modal-box w-11/12 max-w-3xl">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-primary">
                <x-heroicon-s-cog-6-tooth class="w-5 h-5" />
                Setting Mapping Cabang
            </h3>
            
            <p class="py-2 text-sm text-base-content/70 mb-4">
                Fitur ini digunakan untuk meleburkan (merge) pencapaian Actual Sell Out dari Cabang Anak ke Cabang Induk. Gunakan jika target operasional dipusatkan di 1 cabang, namun penjualan terjadi di beberapa depo.
            </p>

            @if (session()->has('success'))
                <div class="alert alert-success shadow-sm mb-4 p-3 rounded-lg text-sm text-white">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col md:flex-row gap-4 mb-6 items-end bg-base-200 p-4 rounded-xl border border-base-300">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-medium text-xs">Pilih Cabang Induk <span class="text-error">*</span></span></label>
                    <select wire:model="parent_cabang" class="select select-bordered select-sm w-full">
                        <option value="">-- Pilih Induk --</option>
                        @foreach($cabangList as $cabang)
                            <option value="{{ $cabang }}">{{ $cabang }}</option>
                        @endforeach
                    </select>
                    @error('parent_cabang') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-center shrink-0 w-8 h-8 rounded-full bg-base-300 self-center md:self-end md:mb-1">
                    <x-heroicon-s-arrow-left class="w-4 h-4 text-base-content/50" />
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-medium text-xs">Pilih Cabang Anak (Dilebur) <span class="text-error">*</span></span></label>
                    <select wire:model="child_cabang" class="select select-bordered select-sm w-full">
                        <option value="">-- Pilih Anak --</option>
                        @foreach($cabangList as $cabang)
                            <option value="{{ $cabang }}">{{ $cabang }}</option>
                        @endforeach
                    </select>
                    @error('child_cabang') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="shrink-0 mt-4 md:mt-0">
                    <button wire:click="save" class="btn btn-sm btn-primary w-full md:w-auto">
                        <x-heroicon-o-plus class="w-4 h-4" /> Tambah
                    </button>
                </div>
            </div>

            <h4 class="font-bold text-sm text-base-content mb-3 border-b border-base-300 pb-2">Daftar Mapping Aktif</h4>
            <div class="overflow-x-auto border border-base-300 rounded-lg max-h-64 custom-scrollbar">
                <table class="table table-xs table-pin-rows w-full">
                    <thead class="bg-base-200 text-base-content/70">
                        <tr>
                            <th class="w-12 text-center">No</th>
                            <th>Cabang Induk (Menerima)</th>
                            <th>Cabang Anak (Dilebur)</th>
                            <th class="w-20 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mappings as $index => $map)
                            <tr class="hover:bg-base-200/50">
                                <td class="text-center text-base-content/50">{{ $index + 1 }}</td>
                                <td class="font-bold text-indigo-700">{{ $map->parent_cabang }}</td>
                                <td class="font-semibold text-rose-600">{{ $map->child_cabang }}</td>
                                <td class="text-center">
                                    <button wire:click="delete({{ $map->id }})" class="btn btn-ghost btn-xs text-error hover:bg-error/20" title="Hapus Mapping">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-base-content/50 italic">
                                    Belum ada data mapping pengecualian cabang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="modal-action mt-6">
                <button wire:click="closeModal" class="btn btn-sm btn-ghost">Tutup</button>
            </div>
        </div>
        
        <div class="modal-backdrop bg-neutral/40" wire:click="closeModal"></div>
    </dialog>
</div>
