<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Mapping Distributor (Selling In)</h1>
        <div class="flex space-x-2">
            <button wire:click="export" class="btn btn-outline btn-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export
            </button>
            <button wire:click="openImportModal" class="btn btn-outline btn-info">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Import
            </button>
            <button wire:click="openModal" class="btn btn-primary">
                + Tambah Mapping
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success shadow-lg mb-4">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error shadow-lg mb-4">
            <div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Card Container -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0">
            <!-- Search & Filters -->
            <div class="p-4 border-b flex justify-between items-center">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode/nama/wilayah/divisi..." class="input input-bordered w-full max-w-md" />
            </div>

            <!-- Table -->
            <div class="overflow-x-auto overflow-y-auto" style="max-height: calc(100vh - 280px);">
                <table class="table table-pin-rows w-full">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Divisi</th>
                            <th>Wilayah</th>
                            <th>Kode (Raw)</th>
                            <th>Distributor (Raw)</th>
                            <th>Master Distributor</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mappings as $map)
                            <tr class="hover">
                                <td>{{ $map->divisi }}</td>
                                <td>{{ $map->wilayah }}</td>
                                <td>{{ $map->kode_distributor }}</td>
                                <td>{{ $map->distributor }}</td>
                                <td>
                                    @if($map->masterDistributor)
                                        <div class="badge badge-success gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            {{ $map->distributor_code }} - {{ $map->masterDistributor->short_name }}
                                        </div>
                                    @else
                                        <div class="badge badge-error gap-1">Belum Ter-map</div>
                                    @endif
                                </td>
                                <td class="text-right space-x-1">
                                    <button wire:click="openModal({{ $map->id }})" class="btn btn-sm btn-circle btn-ghost text-blue-500" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button wire:click="delete({{ $map->id }})" class="btn btn-sm btn-circle btn-ghost text-red-500" title="Hapus" onclick="confirm('Yakin ingin menghapus mapping ini?') || event.stopImmediatePropagation()">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t">
                {{ $mappings->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form CRUD -->
    <div class="modal {{ $isModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-3xl">
            <h3 class="font-bold text-lg mb-4">{{ $mapping_id ? 'Edit' : 'Tambah' }} Mapping Distributor</h3>
            
            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Divisi (Raw)</span></label>
                        <input type="text" wire:model="divisi" class="input input-bordered w-full" required />
                        @error('divisi') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Wilayah (Raw)</span></label>
                        <input type="text" wire:model="wilayah" class="input input-bordered w-full" required />
                        @error('wilayah') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Kode Distributor (Raw)</span></label>
                        <input type="text" wire:model="kode_distributor" class="input input-bordered w-full" required />
                        @error('kode_distributor') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Nama Distributor (Raw)</span></label>
                        <input type="text" wire:model="distributor" class="input input-bordered w-full" required />
                        @error('distributor') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-bold">Pilih Master Distributor</span></label>
                        
                        <!-- Searchable Input + Datalist (Native HTML5 Searchable Select) -->
                        <input type="text" wire:model="distributor_code" list="master-distributors-list" class="input input-bordered input-primary w-full" placeholder="Ketik kode atau nama distributor master..." required />
                        <datalist id="master-distributors-list">
                            @foreach($masterDistributors as $master)
                                <option value="{{ $master->distributor_code }}">{{ $master->distributor_name }}</option>
                            @endforeach
                        </datalist>

                        @error('distributor_code') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="closeModal" class="btn">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Import -->
    <div class="modal {{ $isImportModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Import Data Mapping</h3>
            
            <form wire:submit.prevent="import">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Upload File Excel (xlsx, xls)</span></label>
                    <input type="file" wire:model="importFile" class="file-input file-input-bordered file-input-primary w-full" accept=".xlsx,.xls" required />
                    @error('importFile') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="alert alert-info shadow-lg text-sm">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current flex-shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Format harus sesuai dengan template Export. Sistem akan meng-update mapping yang sudah ada (Upsert) berdasarkan kombinasi Divisi, Wilayah, dan Distributor (Raw).</span>
                    </div>
                </div>

                <div class="modal-action flex justify-between">
                    <div>
                        <button type="button" wire:click="export" class="btn btn-outline btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download Template
                        </button>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" wire:click="closeModal" class="btn">Batal</button>
                        <!-- Tampilkan loading saat upload/import berjalan -->
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="import, importFile">
                            <span wire:loading wire:target="import">Proses...</span>
                            <span wire:loading.remove wire:target="import">Upload & Import</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
