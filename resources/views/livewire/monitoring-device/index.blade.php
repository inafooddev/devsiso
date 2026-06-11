<div>
    <x-slot name="title">Monitoring Device SE</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-6 text-base-content">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-black text-base-content uppercase tracking-wider">
                    Monitoring Device SE
                </h2>
                <p class="text-sm text-base-content/60 mt-1">Pantau kondisi device Sales Executive per bulan.</p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="exportExcel" class="btn btn-success text-white rounded-xl shadow-lg shadow-success/30 border-0" wire:loading.attr="disabled" wire:target="exportExcel">
                    <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-2">
                        <x-heroicon-o-document-arrow-down class="w-5 h-5" /> Export Excel
                    </span>
                    <span wire:loading wire:target="exportExcel" class="flex items-center gap-2">
                        <span class="loading loading-spinner loading-sm"></span> Mengekspor...
                    </span>
                </button>
                <button wire:click="openCreateModal" class="btn btn-primary text-white rounded-xl shadow-lg shadow-primary/30 border-0">
                    <x-heroicon-o-plus class="w-5 h-5" /> Tambah Data
                </button>
            </div>
        </div>

        {{-- Alert Messages --}}
        @if (session()->has('message'))
            <div class="alert alert-success shadow-lg mb-4 rounded-xl">
                <div>
                    <x-heroicon-o-check-circle class="stroke-current flex-shrink-0 h-6 w-6"/>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        @endif

        {{-- Modal Form --}}
        @if($isFormModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-base-100 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="p-6 border-b border-base-200 flex justify-between items-center sticky top-0 bg-base-100/90 backdrop-blur z-10">
                    <h3 class="text-lg font-black uppercase">Tambah Data Monitoring</h3>
                    <button wire:click="closeCreateModal" class="btn btn-ghost btn-sm btn-circle">✕</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="p-6 space-y-4">
                        <div class="form-control">
                            <label class="label font-bold text-xs"><span class="label-text">BULAN MONITORING</span></label>
                            <input type="month" wire:model="tanggal" class="input input-bordered rounded-xl w-full" required {{ $editId ? 'disabled' : '' }}>
                            @error('tanggal') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label font-bold text-xs"><span class="label-text">DISTRIBUTOR</span></label>
                                <input list="distributorList" wire:model.live="form_distributor_search" class="input input-bordered rounded-xl w-full" placeholder="Ketik untuk mencari..." required {{ $editId ? 'disabled' : '' }}>
                                <datalist id="distributorList">
                                    @foreach($this->getFormDistributors() as $dist)
                                        <option value="{{ $dist->distributor_code }} - {{ $dist->distributor_name }}"></option>
                                    @endforeach
                                </datalist>
                                @error('form_distributor_code') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label font-bold text-xs"><span class="label-text">SALES EXECUTIVE</span></label>
                                <select wire:model="form_sales_code" class="select select-bordered rounded-xl w-full" required {{ (empty($form_distributor_code) || $editId) ? 'disabled' : '' }}>
                                    <option value="">Pilih Sales</option>
                                    @foreach($this->getFormSales() as $sales)
                                        <option value="{{ $sales->sales_code }}">{{ $sales->sales_code }} - {{ $sales->sales_name }}</option>
                                    @endforeach
                                </select>
                                @error('form_sales_code') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label font-bold text-xs"><span class="label-text">FOTO DEPAN (MAX 2MB)</span></label>
                                <input type="file" wire:model="foto_tampak_depan" class="file-input file-input-bordered rounded-xl w-full">
                                <div wire:loading wire:target="foto_tampak_depan" class="text-xs text-primary mt-1 font-bold">Mengunggah...</div>
                                @if($existing_foto_tampak_depan)
                                    <div class="flex items-center gap-3 mt-2 text-[11px] font-semibold">
                                        <button type="button" wire:click="hapusFotoDepan" class="text-error hover:text-error/70">Hapus</button>
                                    </div>
                                @endif
                                @error('foto_tampak_depan') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label font-bold text-xs"><span class="label-text">FOTO BELAKANG (MAX 2MB)</span></label>
                                <input type="file" wire:model="foto_tampak_belakang" class="file-input file-input-bordered rounded-xl w-full">
                                <div wire:loading wire:target="foto_tampak_belakang" class="text-xs text-primary mt-1 font-bold">Mengunggah...</div>
                                @if($existing_foto_tampak_belakang)
                                    <div class="flex items-center gap-3 mt-2 text-[11px] font-semibold">
                                        <button type="button" wire:click="hapusFotoBelakang" class="text-error hover:text-error/70">Hapus</button>
                                    </div>
                                @endif
                                @error('foto_tampak_belakang') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label font-bold text-xs"><span class="label-text">KONDISI HP</span></label>
                                <input type="text" wire:model="kondisi_hp" class="input input-bordered rounded-xl w-full" placeholder="Contoh: Layar retak, tombol rusak, dsb.">
                            </div>
                            <div class="form-control">
                                <label class="label font-bold text-xs"><span class="label-text">KONDISI KARTU</span></label>
                                <select wire:model="kondisi_kartu" class="select select-bordered rounded-xl w-full">
                                    <option value="">Pilih Kondisi</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Mati">Mati</option>
                                    <option value="Hilang">Hilang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-base-200 bg-base-200/30 flex justify-end gap-3 sticky bottom-0">
                        <button type="button" wire:click="closeCreateModal" class="btn btn-ghost rounded-xl">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-xl" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">Simpan Data</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Card Container --}}
        <x-card flush class="pb-6">
            {{-- Filter Section --}}
            <div class="px-6 py-4 border-b border-base-200 flex flex-col sm:flex-row items-center gap-4 flex-wrap">
                <div class="relative group w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.500ms="search" type="text"
                           placeholder="Cari Sales / Distributor..."
                           class="input input-sm input-bordered pl-10 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select wire:model.live="filter_region" class="select select-sm select-bordered w-full sm:w-auto rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Region</option>
                        @foreach($this->getFilterRegions() as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filter_area" class="select select-sm select-bordered w-full sm:w-auto rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Area</option>
                        @foreach($this->getFilterAreas() as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filter_distributor" class="select select-sm select-bordered w-full sm:w-auto rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Distributor</option>
                        @foreach($this->getFilterDistributors() as $dist)
                            <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto ml-auto">
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold uppercase text-base-content/50 ml-1">Dari Bulan</label>
                        <input wire:model.live="start_month" type="month" 
                               class="input input-sm input-bordered w-full sm:w-auto rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                    <div class="flex flex-col mt-4">
                        <span class="text-base-content/40 font-bold">-</span>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-[10px] font-bold uppercase text-base-content/50 ml-1">Sampai Bulan</label>
                        <input wire:model.live="end_month" type="month" 
                               class="input input-sm input-bordered w-full sm:w-auto rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>
                    <div class="flex flex-col mt-4">
                        <button wire:click="resetFilters" class="btn btn-sm btn-outline btn-error rounded-xl">
                            <x-heroicon-o-arrow-path class="w-4 h-4 mr-1" /> Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table Wrapper --}}
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full border-collapse border border-base-200">
                    <thead class="bg-base-200/50 text-[11px] uppercase whitespace-nowrap">
                        <tr class="border-b border-base-200">
                            <th style="position: sticky; left: 0; min-width: 100px; max-width: 100px; z-index: 20;" class="border border-base-300 bg-base-200 text-center align-middle" rowspan="2">Region</th>
                            <th style="position: sticky; left: 100px; min-width: 100px; max-width: 100px; z-index: 20;" class="border border-base-300 bg-base-200 text-center align-middle" rowspan="2">Area</th>
                            <th style="position: sticky; left: 200px; min-width: 120px; max-width: 120px; z-index: 20;" class="border border-base-300 bg-base-200 text-center align-middle" rowspan="2">Depo/Cabang</th>
                            <th style="position: sticky; left: 320px; min-width: 160px; max-width: 160px; z-index: 20;" class="border border-base-300 bg-base-200 text-center align-middle" rowspan="2">Distributor</th>
                            <th style="position: sticky; left: 480px; min-width: 90px; max-width: 90px; z-index: 20;" class="border border-base-300 bg-base-200 text-center align-middle" rowspan="2">Sales Code</th>
                            <th style="position: sticky; left: 570px; min-width: 150px; max-width: 150px; z-index: 20; border-right-width: 2px; box-shadow: 4px 0 10px -4px rgba(0,0,0,0.1);" class="border border-base-300 bg-base-200 text-center align-middle" rowspan="2">Nama Sales</th>
                            
                            @foreach($months as $m)
                                <th class="border border-base-300 text-center bg-primary/10 text-primary font-bold" colspan="5">
                                    {{ $monthHeaders[$m] }}
                                </th>
                            @endforeach
                        </tr>
                        <tr class="border-b border-base-200 bg-base-200/50">
                            @foreach($months as $m)
                                <th class="border border-base-300 text-center min-w-[120px]">Foto Depan</th>
                                <th class="border border-base-300 text-center min-w-[120px]">Foto Belakang</th>
                                <th class="border border-base-300 text-center min-w-[100px]">Kondisi HP</th>
                                <th class="border border-base-300 text-center min-w-[100px]">Kondisi Kartu</th>
                                <th class="border border-base-300 text-center min-w-[80px]">Action</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesData as $row)
                            <tr class="hover:bg-base-200/50 text-[11px] group">
                                <td style="position: sticky; left: 0; min-width: 100px; max-width: 100px; z-index: 10;" class="border border-base-300 bg-base-100 group-hover:bg-base-200/50 align-middle truncate whitespace-normal">{{ $row->region_name }}</td>
                                <td style="position: sticky; left: 100px; min-width: 100px; max-width: 100px; z-index: 10;" class="border border-base-300 bg-base-100 group-hover:bg-base-200/50 align-middle truncate whitespace-normal">{{ $row->area_name }}</td>
                                <td style="position: sticky; left: 200px; min-width: 120px; max-width: 120px; z-index: 10;" class="border border-base-300 bg-base-100 group-hover:bg-base-200/50 align-middle truncate whitespace-normal">{{ $row->branch_name }}</td>
                                <td style="position: sticky; left: 320px; min-width: 160px; max-width: 160px; z-index: 10;" class="border border-base-300 bg-base-100 group-hover:bg-base-200/50 align-middle">
                                    <div class="w-full truncate" title="{{ $row->distributor_name }}">
                                        {{ $row->distributor_name }}
                                    </div>
                                </td>
                                <td style="position: sticky; left: 480px; min-width: 90px; max-width: 90px; z-index: 10;" class="border border-base-300 bg-base-100 group-hover:bg-base-200/50 text-center font-mono font-bold align-middle truncate whitespace-normal">{{ $row->sales_code }}</td>
                                <td style="position: sticky; left: 570px; min-width: 150px; max-width: 150px; z-index: 10; border-right-width: 2px; box-shadow: 4px 0 10px -4px rgba(0,0,0,0.1);" class="border border-base-300 bg-base-100 group-hover:bg-base-200/50 font-medium align-middle truncate whitespace-normal">
                                    <div class="w-full truncate" title="{{ $row->sales_name }}">
                                        {{ $row->sales_name }}
                                    </div>
                                </td>

                                @foreach($months as $m)
                                    @php
                                        $mData = $monitoringData[$row->distributor_code . '_' . $row->sales_code][$m] ?? null;
                                    @endphp
                                    <td class="border border-base-300 text-center">
                                        @if($mData && !empty($mData['foto_tampak_depan']))
                                            <button type="button" wire:click="openPreviewModal('{{ asset('storage/' . $mData['foto_tampak_depan']) }}')" class="btn btn-sm btn-ghost btn-circle text-primary hover:bg-primary/20 tooltip" data-tip="Lihat Foto Depan">
                                                <x-heroicon-o-photo class="w-6 h-6" />
                                            </button>
                                        @else
                                            <span class="text-base-content/30 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-base-300 text-center">
                                        @if($mData && !empty($mData['foto_tampak_belakang']))
                                            <button type="button" wire:click="openPreviewModal('{{ asset('storage/' . $mData['foto_tampak_belakang']) }}')" class="btn btn-sm btn-ghost btn-circle text-primary hover:bg-primary/20 tooltip" data-tip="Lihat Foto Belakang">
                                                <x-heroicon-o-photo class="w-6 h-6" />
                                            </button>
                                        @else
                                            <span class="text-base-content/30 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-base-300 text-center">
                                        @if($mData && !empty($mData['kondisi_hp']))
                                            <span class="font-bold text-success">{{ $mData['kondisi_hp'] }}</span>
                                        @else
                                            <span class="text-base-content/30 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-base-300 text-center">
                                        @if($mData && !empty($mData['kondisi_kartu']))
                                            <span class="font-bold text-success">{{ $mData['kondisi_kartu'] }}</span>
                                        @else
                                            <span class="text-base-content/30 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-base-300 text-center">
                                        @if($mData && !empty($mData['id']))
                                            <div class="flex justify-center gap-1">
                                                <button wire:click="edit({{ $mData['id'] }})" class="btn btn-xs btn-ghost btn-circle text-warning tooltip" data-tip="Edit">
                                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                </button>
                                                <button onclick="if(confirm('Apakah Anda yakin ingin menghapus data ini?')) { @this.delete({{ $mData['id'] }}) }" class="btn btn-xs btn-ghost btn-circle text-error tooltip" data-tip="Hapus">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-base-content/30 italic">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + (count($months) * 5) }}" class="text-center py-6 text-base-content/50 italic">
                                    Tidak ada data sales yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($salesData->hasPages())
                <div class="px-6 mt-4">
                    {{ $salesData->links() }}
                </div>
            @endif
        </x-card>
        
        {{-- Modal Preview Image --}}
        @if($isPreviewModalOpen)
        <div class="fixed inset-0 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="z-index: 99999;" wire:click="closePreviewModal">
            <div class="relative max-w-[95vw] sm:max-w-7xl max-h-[95vh] w-full flex flex-col items-center justify-center" wire:click.stop>
                <button wire:click="closePreviewModal" class="absolute -top-4 -right-4 btn btn-circle btn-error shadow-xl z-10 text-white">✕</button>
                <img src="{{ $previewImageUrl }}" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl bg-base-100" alt="Preview Foto">
            </div>
        </div>
        @endif
    </div>
</div>
