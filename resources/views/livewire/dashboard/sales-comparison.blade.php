<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Komparasi Sales (Eska vs SID)</x-slot>

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
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <x-heroicon-s-chart-bar class="w-5 h-5" />
                    </div>
                    Sales Comparison Dashboard
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Komparasi data Selling Out sistem ESKA dengan data Sales Invoice sistem SID</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <button wire:click="$set('isFilterModalOpen', true)" class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        Filter Laporan
                        @if($isFiltered)
                            <span class="badge badge-xs badge-primary rounded-full">ON</span>
                        @endif
                    </button>

                    @hasanyrole('admin|user')
                    <button wire:click="$set('isImportModalOpen', true)" class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import SO Eska
                    </button>
                    @endhasanyrole
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative flex flex-col">
            @if (!$isFiltered)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40 absolute inset-0">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-chart-bar class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Data Belum Ditampilkan</h3>
                    <p class="text-sm text-center max-w-xs">Silakan tentukan periode bulan dan wilayah untuk memuat komparasi data penjualan.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter Dashboard
                    </button>
                </div>
            @else
                {{-- Summary Section --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-4 lg:p-6 shrink-0">
                    <div class="bg-base-200/50 rounded-2xl p-5 border border-base-300 transition-all hover:shadow-md group">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-primary/10 text-primary group-hover:scale-110 transition-transform">
                                <x-heroicon-s-home-modern class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/40">Total Depo</p>
                                <p class="text-2xl font-black text-base-content/80">{{ number_format($summary->total_branch ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-success/5 rounded-2xl p-5 border border-success/20 transition-all hover:shadow-md group">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-success/10 text-success group-hover:scale-110 transition-transform">
                                <x-heroicon-s-check-circle class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-success/60">Sudah Tarik Data</p>
                                <p class="text-2xl font-black text-success">{{ number_format($summary->net_siso_non_zero ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-warning/5 rounded-2xl p-5 border border-warning/20 transition-all hover:shadow-md group">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-warning/10 text-warning group-hover:scale-110 transition-transform">
                                <x-heroicon-s-x-circle class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-warning/60">Belum Tarik Data</p>
                                <p class="text-2xl font-black text-warning">{{ number_format($summary->net_siso_zero ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-error/5 rounded-2xl p-5 border border-error/20 transition-all hover:shadow-md group">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-error/10 text-error group-hover:scale-110 transition-transform">
                                <x-heroicon-s-exclamation-triangle class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-error/60">Total NOT OK</p>
                                <p class="text-2xl font-black text-error">{{ number_format($summary->total_not_ok ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Container --}}
                <div class="flex-1 overflow-auto border-t border-base-300">
                    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                        <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                            <tr>
                                <th>Wilayah</th>
                                <th>Distributor / Cabang</th>
                                <th class="text-right">Sales ESKA (Net)</th>
                                <th class="text-right">Sales SID (Net)</th>
                                <th class="text-right bg-base-200/30">Selisih</th>
                                <th class="text-center w-32 whitespace-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse ($comparisons as $row)
                                <tr class="hover:bg-base-200/50 transition-colors group">
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-base-content/80">{{ $row->region_name }}</span>
                                            <span class="text-[10px] font-bold text-base-content/30 uppercase tracking-widest">{{ $row->entity_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">{{ $row->branch_name }}</span>
                                            <span class="badge badge-xs badge-outline border-base-content/20 font-mono text-[9px] uppercase tracking-tighter">{{ $row->branch_code }}</span>
                                        </div>
                                    </td>
                                    <td class="text-right font-mono font-medium text-base-content/70">
                                        {{ number_format($row->net_eska) }}
                                    </td>
                                    <td class="text-right font-mono font-medium text-base-content/70">
                                        {{ number_format($row->net_siso) }}
                                    </td>
                                    <td class="text-right font-mono font-bold bg-base-200/10 border-x border-base-300 {{ $row->selisih != 0 ? 'text-error' : 'text-success' }}">
                                        {{ number_format($row->selisih) }}
                                    </td>
                                    <td class="text-center">
                                        @if (abs($row->selisih) >= 1000)
                                            <span class="badge badge-error badge-sm text-white rounded-lg font-bold px-3">NOT OK</span>
                                        @else
                                            <span class="badge badge-success badge-sm text-white rounded-lg font-bold px-3">OK</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-base-content/40">Tidak ada data komparasi yang ditemukan untuk kriteria filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Pagination Footer --}}
        @if ($isFiltered && $comparisons->hasPages())
        <div class="p-3 border-t border-base-300 bg-base-50 shrink-0">
            {{ $comparisons->links() }}
        </div>
        @endif
    </div>

    {{-- ========== MODAL FILTER ========== --}}
    <div x-data="{ open: @entangle('isFilterModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Komparasi Sales</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Gunakan multi-select untuk wilayah</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters">
                <div class="p-6 space-y-6">
                    {{-- Row 1: Periode, Implementasi & Status --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Periode Bulan <span class="text-error">*</span></label>
                            <input type="month" wire:model="monthFilter"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            @error('monthFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status Penarikan Data <span class="text-error">*</span></label>
                            <select wire:model="implementasiFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                <option value="ALL">Semua Distributor</option>
                                <option value="Y">Sudah Tarik Data SO</option>
                                <option value="N">Belum Tarik Data SO</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status Selisih Data <span class="text-error">*</span></label>
                            <select wire:model="statusFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                <option value="ALL">Semua Status</option>
                                <option value="OK">Hanya OK (Balance)</option>
                                <option value="NOT_OK">Hanya NOT OK (Selisih)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Multi-Select Region --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between px-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">Pilih Region (Wilayah)</label>
                            <button type="button" wire:click="selectAllRegions" class="text-[10px] font-bold text-primary hover:underline italic">Pilih Semua Region</button>
                        </div>
                        <div class="bg-base-200 rounded-2xl border border-base-300 h-48 overflow-y-auto p-3 scrollbar-thin scrollbar-thumb-base-300">
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($regionsOption as $r)
                                    <label class="flex items-center gap-3 p-2.5 hover:bg-base-300 rounded-xl cursor-pointer transition-colors group">
                                        <input type="checkbox" wire:model="regionsFilter" value="{{ $r->region_code }}" 
                                               class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-all duration-300 group-hover:scale-110">
                                        <span class="text-xs font-semibold text-base-content/70 group-hover:text-primary transition-colors">{{ $r->region_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('regionsFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" wire:click="resetFilters" @click="open = false"
                            class="btn btn-ghost rounded-xl normal-case text-error hover:bg-error/10 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" /> Reset
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Tampilkan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL IMPORT ========== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-success/10 text-success">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Import Selling Out ESKA</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Unggah file excel untuk memuat data SO</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-8">
                    <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-300 rounded-3xl p-10 hover:border-success/50 transition-all group bg-base-200/50">
                        <x-heroicon-o-cloud-arrow-up class="w-16 h-16 text-base-content/20 group-hover:text-success/50 transition-colors mb-4" />
                        <label class="btn btn-sm btn-success text-white rounded-xl cursor-pointer">
                            Pilih File Excel
                            <input type="file" wire:model="importFile" class="hidden" accept=".xlsx,.xls">
                        </label>
                        <p class="text-[11px] text-base-content/40 mt-3 font-medium">Format: .xlsx atau .xls (Maks 100MB)</p>
                        
                        @if ($importFile)
                            <div class="mt-6 p-3 bg-success/10 rounded-2xl border border-success/20 flex items-center gap-3 w-full">
                                <x-heroicon-s-document-text class="w-8 h-8 text-success" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-success truncate">{{ $importFile->getClientOriginalName() }}</p>
                                    <p class="text-[10px] text-success/60">{{ number_format($importFile->getSize() / 1024, 2) }} KB</p>
                                </div>
                                <button type="button" wire:click="$set('importFile', null)" class="text-success/40 hover:text-error transition-colors">
                                    <x-heroicon-s-x-circle class="w-5 h-5" />
                                </button>
                            </div>
                        @endif

                        @error('importFile') 
                            <div class="mt-4 p-3 bg-error/10 rounded-2xl border border-error/20 flex items-center gap-3 w-full">
                                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-error" />
                                <p class="text-[10px] font-bold text-error leading-tight">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mt-6 bg-info/5 rounded-2xl p-4 border border-info/20 flex gap-3">
                        <x-heroicon-s-information-circle class="w-5 h-5 text-info shrink-0" />
                        <div class="text-[10px] text-info/70 leading-relaxed">
                            Pastikan format kolom file excel sesuai dengan standar template yang telah ditentukan untuk menghindari kegagalan saat proses import data.
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-success rounded-xl px-10 normal-case text-white shadow-sm shadow-success/20 gap-2"
                            wire:loading.attr="disabled" wire:target="import">
                        <span wire:loading.remove wire:target="import">Konfirmasi Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading wire:target="import">Proses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
