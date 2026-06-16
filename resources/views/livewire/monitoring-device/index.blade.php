<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Monitoring Device SE</x-slot>

    {{-- Notifikasi Toast --}}
    <div class="toast toast-top toast-center z-[100] mt-16">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                    <div class="text-sm">{{ session('message') }}</div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                    <div class="text-sm">{{ session('error') }}</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full md:w-auto">
                <h2 class="text-base md:text-lg font-bold uppercase tracking-wider">Monitoring Device SE</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Pantau kondisi device Sales Executive per bulan</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start md:justify-end gap-2 md:gap-3 w-full md:w-auto">
                <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="btn btn-sm btn-success text-white rounded-xl shadow-sm normal-case gap-2 border-0">
                    <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                        <span class="hidden sm:inline">Export</span>
                    </span>
                    <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                </button>

                <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm border-0">
                    <x-heroicon-s-plus class="w-4 h-4" />
                    Tambah Data
                </button>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="p-3 md:p-4 bg-base-100 border-b border-base-300 flex flex-wrap items-center gap-2 md:gap-3 shrink-0">
            {{-- Search --}}
            <div class="relative group grow sm:grow-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                </div>
                <input wire:model.live.debounce.500ms="search" type="text"
                       placeholder="Cari Sales / Distributor..."
                       class="input input-sm input-bordered pl-10 w-full sm:w-48 lg:w-56 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
            </div>

            {{-- Filters --}}
            <select wire:model.live="filter_region" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                <option value="">Semua Region</option>
                @foreach($this->getFilterRegions() as $region)
                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filter_area" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                <option value="">Semua Area</option>
                @foreach($this->getFilterAreas() as $area)
                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filter_distributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 w-full sm:w-auto">
                <option value="">Semua Distributor</option>
                @foreach($this->getFilterDistributors() as $dist)
                    <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_name }}</option>
                @endforeach
            </select>
            
            {{-- Date range filter --}}
            <div class="flex items-center gap-2 grow sm:grow-0 w-full sm:w-auto ml-auto">
                <div class="flex items-center gap-1 w-full sm:w-auto">
                    <input wire:model.live="start_month" type="month" title="Dari Bulan"
                           class="input input-sm input-bordered w-full sm:w-auto rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <span class="text-base-content/40 font-bold">-</span>
                    <input wire:model.live="end_month" type="month" title="Sampai Bulan"
                           class="input input-sm input-bordered w-full sm:w-auto rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end w-full sm:w-auto">
                <button wire:click="resetFilters" class="btn btn-sm btn-ghost bg-base-100 border border-base-300 rounded-xl" title="Reset Filters">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                    <span class="hidden sm:inline">Reset</span>
                </button>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra w-full whitespace-nowrap border-collapse">
                <thead class="text-[11px] uppercase tracking-wider bg-base-300 text-base-content/80 shadow-sm sticky top-0 z-30">
                    <tr>
                        <th style="position: sticky; left: 0; min-width: 100px; max-width: 100px; z-index: 40;" class="border-b border-r border-base-300 bg-base-300 text-center align-middle" rowspan="2">Region</th>
                        <th style="position: sticky; left: 100px; min-width: 100px; max-width: 100px; z-index: 40;" class="border-b border-r border-base-300 bg-base-300 text-center align-middle" rowspan="2">Area</th>
                        <th style="position: sticky; left: 200px; min-width: 120px; max-width: 120px; z-index: 40;" class="border-b border-r border-base-300 bg-base-300 text-center align-middle" rowspan="2">Depo/Cabang</th>
                        <th style="position: sticky; left: 320px; min-width: 160px; max-width: 160px; z-index: 40;" class="border-b border-r border-base-300 bg-base-300 text-center align-middle" rowspan="2">Distributor</th>
                        <th style="position: sticky; left: 480px; min-width: 90px; max-width: 90px; z-index: 40;" class="border-b border-r border-base-300 bg-base-300 text-center align-middle" rowspan="2">Sales Code</th>
                        <th style="position: sticky; left: 570px; min-width: 150px; max-width: 150px; z-index: 40; border-right-width: 2px; box-shadow: 4px 0 10px -4px rgba(0,0,0,0.1);" class="border-b border-r border-base-300 bg-base-300 text-center align-middle" rowspan="2">Nama Sales</th>
                        
                        @foreach($months as $m)
                            <th class="border-b border-r border-base-300 text-center bg-primary/10 text-primary font-bold shadow-[inset_0_-1px_0_rgba(0,0,0,0.05)]" colspan="5">
                                {{ $monthHeaders[$m] }}
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($months as $m)
                            <th class="border-b border-r border-base-300 text-center min-w-[120px] bg-base-200">Foto Depan</th>
                            <th class="border-b border-r border-base-300 text-center min-w-[120px] bg-base-200">Foto Belakang</th>
                            <th class="border-b border-r border-base-300 text-center min-w-[100px] bg-base-200">Kondisi HP</th>
                            <th class="border-b border-r border-base-300 text-center min-w-[100px] bg-base-200">Kondisi Kartu</th>
                            <th class="border-b border-r border-base-300 text-center min-w-[80px] bg-base-200">Action</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-xs md:text-sm">
                    @forelse($salesData as $row)
                        <tr class="hover:bg-base-200 transition-colors group">
                            <td style="position: sticky; left: 0; min-width: 100px; max-width: 100px; z-index: 10;" class="border-r border-b border-base-200 bg-base-100 group-hover:bg-base-200 align-middle truncate"><span class="font-bold text-base-content/80">{{ $row->region_name }}</span></td>
                            <td style="position: sticky; left: 100px; min-width: 100px; max-width: 100px; z-index: 10;" class="border-r border-b border-base-200 bg-base-100 group-hover:bg-base-200 align-middle truncate"><span class="font-bold text-base-content/80">{{ $row->area_name }}</span></td>
                            <td style="position: sticky; left: 200px; min-width: 120px; max-width: 120px; z-index: 10;" class="border-r border-b border-base-200 bg-base-100 group-hover:bg-base-200 align-middle truncate"><span class="font-bold text-base-content">{{ $row->branch_name }}</span></td>
                            <td style="position: sticky; left: 320px; min-width: 160px; max-width: 160px; z-index: 10;" class="border-r border-b border-base-200 bg-base-100 group-hover:bg-base-200 align-middle">
                                <div class="w-full truncate font-bold text-base-content" title="{{ $row->distributor_name }}">
                                    {{ $row->distributor_name }}
                                </div>
                            </td>
                            <td style="position: sticky; left: 480px; min-width: 90px; max-width: 90px; z-index: 10;" class="border-r border-b border-base-200 bg-base-100 group-hover:bg-base-200 text-center font-mono font-bold align-middle truncate text-base-content/80">{{ $row->sales_code }}</td>
                            <td style="position: sticky; left: 570px; min-width: 150px; max-width: 150px; z-index: 10; border-right-width: 2px; box-shadow: 4px 0 10px -4px rgba(0,0,0,0.1);" class="border-r border-b border-base-200 bg-base-100 group-hover:bg-base-200 font-bold text-base-content align-middle truncate">
                                <div class="w-full truncate" title="{{ $row->sales_name }}">
                                    {{ $row->sales_name }}
                                </div>
                            </td>

                            @foreach($months as $m)
                                @php
                                    $mData = $monitoringData[$row->distributor_code . '_' . $row->sales_code][$m] ?? null;
                                @endphp
                                <td class="border-r border-b border-base-200 text-center">
                                    @if($mData && !empty($mData['foto_tampak_depan']))
                                        <button type="button" wire:click="openPreviewModal('{{ asset('storage/' . $mData['foto_tampak_depan']) }}')" class="btn btn-xs btn-ghost btn-square text-primary hover:bg-primary/20 tooltip" data-tip="Lihat Foto Depan">
                                            <x-heroicon-o-photo class="w-4 h-4" />
                                        </button>
                                    @else
                                        <span class="text-base-content/30 italic text-[10px]">-</span>
                                    @endif
                                </td>
                                <td class="border-r border-b border-base-200 text-center">
                                    @if($mData && !empty($mData['foto_tampak_belakang']))
                                        <button type="button" wire:click="openPreviewModal('{{ asset('storage/' . $mData['foto_tampak_belakang']) }}')" class="btn btn-xs btn-ghost btn-square text-primary hover:bg-primary/20 tooltip" data-tip="Lihat Foto Belakang">
                                            <x-heroicon-o-photo class="w-4 h-4" />
                                        </button>
                                    @else
                                        <span class="text-base-content/30 italic text-[10px]">-</span>
                                    @endif
                                </td>
                                <td class="border-r border-b border-base-200 text-center">
                                    @if($mData && !empty($mData['kondisi_hp']))
                                        <span class="font-bold text-success">{{ $mData['kondisi_hp'] }}</span>
                                    @else
                                        <span class="text-base-content/30 italic text-[10px]">-</span>
                                    @endif
                                </td>
                                <td class="border-r border-b border-base-200 text-center">
                                    @if($mData && !empty($mData['kondisi_kartu']))
                                        <span class="font-bold text-success">{{ $mData['kondisi_kartu'] }}</span>
                                    @else
                                        <span class="text-base-content/30 italic text-[10px]">-</span>
                                    @endif
                                </td>
                                <td class="border-r border-b border-base-200 text-center bg-base-100/50 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                    @if($mData && !empty($mData['id']))
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="edit({{ $mData['id'] }})" class="btn btn-xs btn-square btn-ghost text-warning hover:bg-warning/10 tooltip transition-colors" data-tip="Edit">
                                                <x-heroicon-s-pencil-square class="w-4 h-4" />
                                            </button>
                                            <button onclick="if(confirm('Apakah Anda yakin ingin menghapus data ini?')) { @this.delete({{ $mData['id'] }}) }" class="btn btn-xs btn-square btn-ghost text-error hover:bg-error/10 tooltip transition-colors" data-tip="Hapus">
                                                <x-heroicon-s-trash class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-base-content/30 italic text-[10px]">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + (count($months) * 5) }}" class="text-center py-12 text-base-content/40 bg-base-100">
                                <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                                <p>Tidak ada data monitoring yang cocok dengan pencarian dan filter Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Card (Pagination) --}}
        @if($salesData->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $salesData->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form --}}
    @if($isFormModalOpen)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-base-100 rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl ring-1 ring-base-content/5">
            <div class="p-6 border-b border-base-300 flex justify-between items-center sticky top-0 bg-base-200/30 backdrop-blur z-10 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-clipboard-document-list class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $editId ? 'Edit Data Monitoring' : 'Tambah Data Monitoring' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Formulir kondisi device Sales Executive</p>
                    </div>
                </div>
                <button wire:click="closeCreateModal" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            <form wire:submit.prevent="save">
                <div class="p-6 space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Bulan Monitoring <span class="text-error">*</span></label>
                        <input type="month" wire:model="tanggal" class="input input-bordered bg-base-200 border-base-300 rounded-2xl w-full focus:ring-2 focus:ring-primary/50 transition-all duration-300" required {{ $editId ? 'disabled' : '' }}>
                        @error('tanggal') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor <span class="text-error">*</span></label>
                            <input list="distributorList" wire:model.live="form_distributor_search" class="input input-bordered bg-base-200 border-base-300 rounded-2xl w-full focus:ring-2 focus:ring-primary/50 transition-all duration-300" placeholder="Ketik untuk mencari..." required {{ $editId ? 'disabled' : '' }}>
                            <datalist id="distributorList">
                                @foreach($this->getFormDistributors() as $dist)
                                    <option value="{{ $dist->distributor_code }} - {{ $dist->distributor_name }}"></option>
                                @endforeach
                            </datalist>
                            @error('form_distributor_code') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Sales Executive <span class="text-error">*</span></label>
                            <select wire:model="form_sales_code" class="select select-bordered bg-base-200 border-base-300 rounded-2xl w-full focus:ring-2 focus:ring-primary/50 transition-all duration-300" required {{ (empty($form_distributor_code) || $editId) ? 'disabled' : '' }}>
                                <option value="">Pilih Sales</option>
                                @foreach($this->getFormSales() as $sales)
                                    <option value="{{ $sales->sales_code }}">{{ $sales->sales_code }} - {{ $sales->sales_name }}</option>
                                @endforeach
                            </select>
                            @error('form_sales_code') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto Depan (Max 2MB)</label>
                            <input type="file" wire:model="foto_tampak_depan" class="file-input file-input-bordered bg-base-200 border-base-300 rounded-2xl w-full focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <div wire:loading wire:target="foto_tampak_depan" class="text-[10px] text-primary mt-1 font-bold ml-1">Mengunggah...</div>
                            @if($existing_foto_tampak_depan)
                                <div class="flex items-center justify-between mt-2 p-2 bg-base-200/50 rounded-xl border border-base-300">
                                    <span class="text-[10px] text-base-content/60 font-semibold truncate max-w-[150px]">Foto Tersimpan</span>
                                    <button type="button" wire:click="hapusFotoDepan" class="btn btn-xs btn-ghost text-error hover:bg-error/10">Hapus</button>
                                </div>
                            @endif
                            @error('foto_tampak_depan') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto Belakang (Max 2MB)</label>
                            <input type="file" wire:model="foto_tampak_belakang" class="file-input file-input-bordered bg-base-200 border-base-300 rounded-2xl w-full focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <div wire:loading wire:target="foto_tampak_belakang" class="text-[10px] text-primary mt-1 font-bold ml-1">Mengunggah...</div>
                            @if($existing_foto_tampak_belakang)
                                <div class="flex items-center justify-between mt-2 p-2 bg-base-200/50 rounded-xl border border-base-300">
                                    <span class="text-[10px] text-base-content/60 font-semibold truncate max-w-[150px]">Foto Tersimpan</span>
                                    <button type="button" wire:click="hapusFotoBelakang" class="btn btn-xs btn-ghost text-error hover:bg-error/10">Hapus</button>
                                </div>
                            @endif
                            @error('foto_tampak_belakang') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kondisi HP</label>
                            <input type="text" wire:model="kondisi_hp" class="input input-bordered bg-base-200 border-base-300 rounded-2xl w-full focus:ring-2 focus:ring-primary/50 transition-all duration-300" placeholder="Contoh: Layar retak, dsb.">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kondisi Kartu</label>
                            <select wire:model="kondisi_kartu" class="select select-bordered bg-base-200 border-base-300 rounded-2xl w-full focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                <option value="">Pilih Kondisi</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Mati">Mati</option>
                                <option value="Hilang">Hilang</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-base-300 bg-base-200/30 flex justify-end gap-3 sticky bottom-0 rounded-b-3xl">
                    <button type="button" wire:click="closeCreateModal" class="btn btn-ghost rounded-xl hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 shadow-sm shadow-primary/20" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Simpan Data</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <span class="loading loading-spinner loading-xs"></span>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Preview Image --}}
    @if($isPreviewModalOpen)
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm z-[200]" wire:click="closePreviewModal">
        <div class="relative max-w-[95vw] sm:max-w-7xl max-h-[95vh] w-full flex flex-col items-center justify-center" wire:click.stop>
            <button wire:click="closePreviewModal" class="absolute -top-4 -right-4 lg:-top-6 lg:-right-6 btn btn-circle btn-error shadow-xl z-10 text-white hover:scale-105 transition-transform">✕</button>
            <img src="{{ $previewImageUrl }}" class="max-w-full max-h-[90vh] object-contain rounded-2xl shadow-2xl bg-base-100 ring-4 ring-base-100/50" alt="Preview Foto">
        </div>
    </div>
    @endif
</div>
