<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
     @if($isImporting) wire:poll.500ms="checkImportProgress" @endif>
    
    <x-slot name="title">Target Per SE Untuk ESKA</x-slot>

    {{-- Alert Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success shrink-0 transition-all duration-300">
            <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                <div class="text-sm font-medium">{{ session('message') }}</div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show"
             class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error shrink-0 transition-all duration-300">
            <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider">Gagal</h3>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- Total Records --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Record Data</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-document-text class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-2xl font-extrabold leading-none mt-2 truncate relative z-10 text-primary">
                {{ number_format($totalRecords, 0, ',', '.') }}
            </div>
        </div>

        {{-- Total Target Value --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Nominal Target</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-banknotes class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-2xl font-extrabold leading-none mt-2 truncate relative z-10 text-success">
                Rp {{ number_format($totalTargetValue, 0, ',', '.') }}
            </div>
        </div>

        {{-- Total Salesman --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-info/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Salesman Unik</h3>
                <div class="w-8 h-8 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-user-group class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-2xl font-extrabold leading-none mt-2 truncate relative z-10 text-info">
                {{ number_format($totalSalesmanCount, 0, ',', '.') }}
            </div>
        </div>

        {{-- Total Outlet --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Outlet Unik</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-building-storefront class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-2xl font-extrabold leading-none mt-2 truncate relative z-10 text-warning">
                {{ number_format($totalOutletCount, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-base-100 rounded-2xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Toolbar / Header Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-3 bg-base-200/30">
            <div class="shrink-0">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <x-heroicon-s-chart-bar class="w-5 h-5 text-primary" />
                    Target Per SE Untuk ESKA
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">
                    Modul Pengelolaan, Import & Export Target Salesman ESKA
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
                {{-- Search Input --}}
                <div class="relative group grow sm:grow-0 min-w-[180px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari salesman/outlet..."
                           class="input input-sm input-bordered pl-9 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all">
                </div>

                {{-- Filter Tahun --}}
                <select wire:model.live="tahunFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $th)
                        <option value="{{ $th }}">{{ $th }}</option>
                    @endforeach
                </select>

                {{-- Filter Bulan --}}
                <select wire:model.live="bulanFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                    <option value="">Semua Bulan</option>
                    @foreach($bulanList as $bln)
                        <option value="{{ $bln }}">Bulan {{ $bln }}</option>
                    @endforeach
                </select>

                {{-- Filter Region --}}
                @if($regionList->count() > 0)
                <select wire:model.live="regionFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                    <option value="">Semua Region</option>
                    @foreach($regionList as $rg)
                        <option value="{{ $rg }}">{{ $rg }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Export Button --}}
                <button wire:click="openExportModal" class="btn btn-sm btn-success rounded-xl gap-1.5 shadow-sm text-xs font-bold text-white">
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                    <span>Export Excel</span>
                </button>

                {{-- Import Button --}}
                <button wire:click="openImportModal" class="btn btn-sm btn-primary rounded-xl gap-1.5 shadow-sm text-xs font-bold">
                    <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                    <span>Import Data</span>
                </button>
            </div>
        </div>

        {{-- Table Area --}}
        <div class="flex-1 overflow-auto min-h-0">
            <table class="table table-zebra table-pin-rows table-sm w-full">
                <thead>
                    <tr class="bg-base-200/80 text-base-content/70 uppercase text-[10px] tracking-wider">
                        <th class="w-12 text-center">No</th>
                        <th>Tahun</th>
                        <th>Bulan</th>
                        <th>Region</th>
                        <th>Branch</th>
                        <th>Selling Point</th>
                        <th>Salesman</th>
                        <th>Outlet</th>
                        <th class="text-right">Value Target (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr class="hover:bg-base-200/40 transition-colors">
                            <td class="text-center font-mono text-xs text-base-content/50">
                                {{ $data->firstItem() + $index }}
                            </td>
                            <td>
                                <span class="badge badge-sm badge-neutral font-semibold">{{ $item->tahun }}</span>
                            </td>
                            <td>
                                <span class="badge badge-sm badge-ghost font-mono font-semibold">{{ $item->bulan }}</span>
                            </td>
                            <td>
                                <span class="font-medium text-xs">{{ $item->region ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="font-medium text-xs text-primary">{{ $item->branch ?? '-' }}</span>
                            </td>
                            <td class="text-xs font-mono text-base-content/80">
                                {{ $item->sellingpoint ?? '-' }}
                            </td>
                            <td class="font-semibold text-xs text-base-content/90">
                                {{ $item->salesman ?? '-' }}
                            </td>
                            <td class="font-mono text-xs text-base-content/80">
                                {{ $item->outlet ?? '-' }}
                            </td>
                            <td class="text-right font-mono font-bold text-success text-xs">
                                Rp {{ number_format($item->value, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center gap-2 text-base-content/40">
                                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12" />
                                    <p class="font-semibold text-sm">Belum ada data target per SE untuk ESKA</p>
                                    <p class="text-xs">Klik tombol <strong>Import Data</strong> di atas untuk mengunggah berkas CSV/Excel.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer / Pagination --}}
        <div class="p-3 border-t border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-center gap-3 bg-base-200/30 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-base-content/60">Tampilkan per halaman:</span>
                <select wire:model.live="perPage" class="select select-xs select-bordered rounded-lg bg-base-100">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                    <option value="500">500</option>
                </select>
            </div>
            <div>
                {{ $data->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT DATA --}}
    @if($isImportModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in"
             x-data="{ isDragging: false }">
            <div class="bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden flex flex-col animate-scale-up relative">
                
                {{-- Header Modal --}}
                <div class="p-4 md:p-5 border-b border-base-300 flex justify-between items-center bg-base-200/40">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm md:text-base">Import Data Target Per SE ESKA</h3>
                            <p class="text-[10px] md:text-xs text-base-content/60">Format didukung: CSV (delimiter ;) atau Excel (.xlsx/.xls)</p>
                        </div>
                    </div>
                    @if(!$isImporting)
                        <button wire:click="closeImportModal" class="btn btn-sm btn-circle btn-ghost text-base-content/60 hover:text-base-content">
                            ✕
                        </button>
                    @endif
                </div>

                {{-- TAMPILAN PROSES IMPORT PROGRESS (AKTIF SECARA REAKSIF SAKETIKA DIKLIK VIA WIRE:LOADING) --}}
                <div wire:loading wire:target="processImport" class="w-full">
                    <div class="p-8 flex flex-col items-center justify-center text-center gap-4 bg-base-100">
                        <div class="relative flex items-center justify-center">
                            <div class="w-20 h-20 rounded-full bg-primary/20 animate-ping absolute"></div>
                            <div class="w-16 h-16 rounded-2xl bg-primary flex items-center justify-center text-primary-content shadow-lg">
                                <x-heroicon-s-arrow-path class="w-8 h-8 animate-spin" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-1 max-w-sm">
                            <span class="badge badge-primary badge-sm mx-auto uppercase font-bold tracking-wider">SEDANG MENGIMPOR DATA</span>
                            <div class="text-xl font-extrabold font-mono text-primary mt-1 flex items-center justify-center gap-2">
                                <span class="loading loading-dots loading-md text-primary"></span>
                                Memproses Berkas & Insert Data
                            </div>
                            <p class="text-xs text-base-content/60 leading-relaxed mt-1">
                                Data sedang dibaca, divalidasi, dan di-commit ke database secara otomatis per batch <strong>2.000 baris</strong>. Mohon jangan menutup halaman ini...
                            </p>
                        </div>

                        {{-- Progress Animation Bar --}}
                        <div class="w-full flex flex-col gap-1.5 mt-2">
                            <progress class="progress progress-primary w-full h-3.5 rounded-full"></progress>
                            <div class="flex justify-between items-center text-[10px] text-base-content/50 font-mono font-semibold">
                                <span>BATCH CHUNK: 2.000 BARIS / COMMIT</span>
                                <span class="animate-pulse text-primary font-bold">● SEDANG MEMPROSES...</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FORM INPUT IMPORT (SEMBUNYI KETIKA TOMBOL IMPORT DIKLIK / DIPROSES) --}}
                <div wire:loading.remove wire:target="processImport" class="w-full">
                    <form wire:submit.prevent="processImport" class="p-4 md:p-5 flex flex-col gap-4">
                        
                        {{-- Petunjuk Format CSV/Excel & Download Template --}}
                        <div class="p-3 bg-info/10 border border-info/20 rounded-2xl text-xs text-info flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-bold flex items-center gap-1">
                                    <x-heroicon-s-information-circle class="w-4 h-4 shrink-0" />
                                    <span>Struktur Header Excel / CSV:</span>
                                </div>
                                <button type="button" wire:click="downloadTemplate" class="btn btn-xs btn-info text-white rounded-lg gap-1 font-bold shadow-xs">
                                    <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" />
                                    <span>Template Excel (.xlsx)</span>
                                </button>
                            </div>
                            <code class="bg-base-100 p-2 rounded-xl text-[11px] font-mono text-base-content border border-base-300 overflow-x-auto block">
                                tahun;bulan;region;branch;sellingpoint;salesman;outlet;value
                            </code>
                        </div>

                        {{-- File Drop Area --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold text-base-content/70">Pilih File Import (.csv, .xlsx, .xls)</label>
                            
                            <div class="relative border-2 border-dashed border-base-300 rounded-2xl p-6 flex flex-col items-center justify-center gap-2 hover:border-primary/50 transition-colors bg-base-200/20"
                                 :class="{ 'border-primary bg-primary/5': isDragging }"
                                 @dragover.prevent="isDragging = true"
                                 @dragleave.prevent="isDragging = false"
                                 @drop.prevent="isDragging = false">

                                <x-heroicon-o-cloud-arrow-up class="w-10 h-10 text-primary/60" />
                                
                                <div class="text-center text-xs">
                                    @if($importFile)
                                        <span class="font-bold text-success flex items-center gap-1 justify-center">
                                            <x-heroicon-s-check-circle class="w-4 h-4" />
                                            {{ $importFile->getClientOriginalName() }}
                                        </span>
                                        <span class="text-[10px] text-base-content/50 block">({{ number_format($importFile->getSize() / 1024, 1) }} KB)</span>
                                    @else
                                        <span class="font-semibold text-base-content/70">Klik untuk memilih file</span> atau tarik file ke sini
                                    @endif
                                </div>

                                <input type="file" wire:model="importFile" accept=".csv, .txt, .xlsx, .xls"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            </div>

                            {{-- Indicator Uploading Temp File --}}
                            <div wire:loading wire:target="importFile" class="text-xs text-primary font-semibold flex items-center gap-1.5 mt-1">
                                <span class="loading loading-spinner loading-xs"></span>
                                Mengunggah berkas ke server...
                            </div>

                            @error('importFile')
                                <span class="text-xs text-error font-semibold mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Checkbox Truncate Periode --}}
                        <div class="form-control bg-base-200/30 p-3 rounded-2xl border border-base-300">
                            <label class="label cursor-pointer justify-start gap-3 p-0">
                                <input type="checkbox" wire:model="truncatePeriod" class="checkbox checkbox-sm checkbox-primary rounded-lg">
                                <div class="flex flex-col">
                                    <span class="label-text font-bold text-xs">Truncate Periode Import</span>
                                    <span class="label-text-alt text-[10px] text-base-content/60">
                                        Menghapus data lama pada periode (tahun + bulan) yang sama sebelum meng-insert data baru untuk mencegah duplikasi.
                                    </span>
                                </div>
                            </label>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-base-300">
                            <button type="button" wire:click="closeImportModal" class="btn btn-sm btn-ghost rounded-xl">
                                Batal
                            </button>
                            
                            <button type="submit" class="btn btn-sm btn-primary rounded-xl gap-2 font-bold px-5"
                                    wire:loading.attr="disabled" wire:target="processImport">
                                <span wire:loading.remove wire:target="processImport">Import Sekarang (Batch 2.000)</span>
                                <span wire:loading wire:target="processImport" class="flex items-center gap-1">
                                    <span class="loading loading-spinner loading-xs"></span>
                                    Mengimpor Data...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    @endif

    {{-- MODAL RINGKASAN HASIL IMPORT (SUMMARY REPORT MODAL) --}}
    @if($isImportResultModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
            <div class="bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-xl overflow-hidden flex flex-col animate-scale-up">
                
                {{-- Header Modal --}}
                <div class="p-4 md:p-5 border-b border-base-300 flex justify-between items-center bg-success/10 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-success/20 flex items-center justify-center text-success">
                            <x-heroicon-s-check-badge class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm md:text-base text-base-content">Ringkasan Laporan Hasil Import Data</h3>
                            <p class="text-[10px] md:text-xs text-base-content/60">Detail rincian data terproses, berhasil, dan dihapus</p>
                        </div>
                    </div>
                    <span class="badge badge-success text-white font-mono text-[10px] font-bold">⏱️ {{ $resultExecutionTime }} detik</span>
                </div>

                {{-- Body Modal Ringkasan Stats --}}
                <div class="p-4 md:p-6 flex flex-col gap-4 overflow-y-auto max-h-[75vh]">
                    
                    {{-- 4 Grid Summary Cards --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Success Count --}}
                        <div class="p-3.5 bg-success/10 border border-success/20 rounded-2xl flex flex-col gap-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-success">Berhasil Di-commit</span>
                            <span class="text-2xl font-extrabold text-success font-mono">
                                {{ number_format($resultImportedCount, 0, ',', '.') }}
                                <span class="text-xs font-normal">Baris</span>
                            </span>
                        </div>

                        {{-- Skipped / Failed Count --}}
                        <div class="p-3.5 bg-{{ $resultSkippedCount > 0 ? 'error' : 'base-200' }}/10 border border-{{ $resultSkippedCount > 0 ? 'error' : 'base-300' }}/20 rounded-2xl flex flex-col gap-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-{{ $resultSkippedCount > 0 ? 'error' : 'base-content/50' }}">Gagal / Dilewati</span>
                            <span class="text-2xl font-extrabold text-{{ $resultSkippedCount > 0 ? 'error' : 'base-content/50' }} font-mono">
                                {{ number_format($resultSkippedCount, 0, ',', '.') }}
                                <span class="text-xs font-normal">Baris</span>
                            </span>
                        </div>

                        {{-- Total Target Value --}}
                        <div class="p-3.5 bg-info/10 border border-info/20 rounded-2xl flex flex-col gap-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-info">Total Target Value</span>
                            <span class="text-lg font-extrabold text-info font-mono truncate">
                                Rp {{ number_format($resultTotalValue, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Truncated Count --}}
                        <div class="p-3.5 bg-warning/10 border border-warning/20 rounded-2xl flex flex-col gap-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-warning">Data Lama Dihapus</span>
                            <span class="text-2xl font-extrabold text-warning font-mono">
                                {{ number_format($resultTruncatedCount, 0, ',', '.') }}
                                <span class="text-xs font-normal">Baris</span>
                            </span>
                        </div>
                    </div>

                    {{-- Error Logs Box (Jika ada data yang dilewati) --}}
                    @if(count($resultErrorLogs) > 0)
                        <div class="flex flex-col gap-1.5 bg-base-200/50 p-3.5 rounded-2xl border border-base-300">
                            <span class="text-xs font-bold text-error flex items-center gap-1">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                Catatan Baris Yang Dilewati / Gagal (Maks 50 Baris):
                            </span>
                            <div class="bg-base-100 p-2.5 rounded-xl border border-base-300 text-mono text-[11px] max-h-36 overflow-y-auto flex flex-col gap-1 font-mono text-error/90">
                                @foreach($resultErrorLogs as $log)
                                    <div>{{ $log }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Footer Actions --}}
                <div class="p-4 border-t border-base-300 bg-base-200/30 flex justify-end shrink-0">
                    <button wire:click="closeImportResultModal" class="btn btn-sm btn-primary rounded-xl font-bold px-6">
                        Selesai & Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- MODAL FILTER EXPORT DATA --}}
    @if($isExportModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
            <div class="bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl min-h-[750px] max-h-[95vh] overflow-hidden flex flex-col animate-scale-up">
                
                {{-- Header Modal --}}
                <div class="p-4 md:p-5 border-b border-base-300 flex justify-between items-center bg-base-200/40 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success">
                            <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm md:text-base">Filter Export Excel (Target Per SE)</h3>
                            <p class="text-[10px] md:text-xs text-base-content/60">Pilih kriteria filter untuk menghasilkan file Excel per SE</p>
                        </div>
                    </div>
                    <button wire:click="closeExportModal" class="btn btn-sm btn-circle btn-ghost text-base-content/60 hover:text-base-content">
                        ✕
                    </button>
                </div>

                {{-- Body Modal Form --}}
                <form wire:submit.prevent="processExport" class="p-4 md:p-6 flex flex-col gap-4 overflow-y-auto flex-1 justify-between">
                    
                    <div class="flex flex-col gap-4">
                        {{-- Grid Filter Periode (Tahun & Bulan) --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-base-content/70">Tahun Target</label>
                                <select wire:model.live="exportTahun" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-success/50 w-full">
                                    <option value="">Semua Tahun</option>
                                    @foreach($tahunList as $th)
                                        <option value="{{ $th }}">{{ $th }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-base-content/70">Bulan Target</label>
                                <select wire:model.live="exportBulan" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-success/50 w-full">
                                    <option value="">Semua Bulan</option>
                                    @foreach($bulanList as $bln)
                                        <option value="{{ $bln }}">Bulan {{ $bln }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Single Select Region --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-base-content/70 flex items-center justify-between">
                                <span>Region (Single Select)</span>
                            </label>
                            <select wire:model.live="exportRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-success/50 w-full">
                                <option value="">-- Semua Region --</option>
                                @foreach($regionList as $rg)
                                    <option value="{{ $rg }}">{{ $rg }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Multi Select Branch --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-base-content/70">
                                    Branch / Cabang (Multi Select)
                                    @if(count($exportBranches) > 0)
                                        <span class="badge badge-xs badge-success text-white font-bold ml-1">{{ count($exportBranches) }} terpilih</span>
                                    @endif
                                </label>
                                <button type="button" wire:click="toggleAllExportBranches" class="btn btn-xs btn-ghost text-success font-bold text-[11px] p-0 hover:bg-transparent">
                                    {{ count($exportBranches) === count($exportBranchOptions) && count($exportBranchOptions) > 0 ? 'Reset' : 'Pilih Semua' }}
                                </button>
                            </div>
                            <select wire:model.live="exportBranches" multiple class="select select-bordered select-sm w-full h-52 rounded-xl bg-base-100 text-xs font-medium focus:ring-2 focus:ring-success/50 p-2">
                                @forelse($exportBranchOptions as $br)
                                    <option value="{{ $br->branch }}" class="p-2 rounded-lg hover:bg-success/10">{{ $br->branch }}</option>
                                @empty
                                    <option disabled class="italic text-base-content/40 p-2">Tidak ada cabang tersedia</option>
                                @endforelse
                            </select>
                            <span class="text-[10px] text-base-content/50">Tahan <kbd class="kbd kbd-xs">Ctrl</kbd> / <kbd class="kbd kbd-xs">Cmd</kbd> untuk memilih banyak cabang</span>
                        </div>

                        {{-- Multi Select Selling Point --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-base-content/70">
                                    Selling Point (Multi Select)
                                    @if(count($exportSellingPoints) > 0)
                                        <span class="badge badge-xs badge-success text-white font-bold ml-1">{{ count($exportSellingPoints) }} terpilih</span>
                                    @endif
                                </label>
                                <button type="button" wire:click="toggleAllExportSellingPoints" class="btn btn-xs btn-ghost text-success font-bold text-[11px] p-0 hover:bg-transparent">
                                    {{ count($exportSellingPoints) === count($exportSellingPointOptions) && count($exportSellingPointOptions) > 0 ? 'Reset' : 'Pilih Semua' }}
                                </button>
                            </div>
                            <select wire:model.live="exportSellingPoints" multiple class="select select-bordered select-sm w-full h-52 rounded-xl bg-base-100 text-xs font-medium focus:ring-2 focus:ring-success/50 p-2">
                                @forelse($exportSellingPointOptions as $sp)
                                    <option value="{{ $sp->sellingpoint }}" class="p-2 rounded-lg hover:bg-success/10">{{ $sp->sellingpoint }}</option>
                                @empty
                                    <option disabled class="italic text-base-content/40 p-2">Tidak ada selling point tersedia</option>
                                @endforelse
                            </select>
                            <span class="text-[10px] text-base-content/50">Tahan <kbd class="kbd kbd-xs">Ctrl</kbd> / <kbd class="kbd kbd-xs">Cmd</kbd> untuk memilih banyak selling point</span>
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-base-300 shrink-0 mt-1">
                        <button type="button" wire:click="closeExportModal" class="btn btn-sm btn-ghost rounded-xl">
                            Batal
                        </button>
                        
                        <button type="submit" class="btn btn-sm btn-success text-white rounded-xl gap-2 font-bold px-5 shadow-sm"
                                wire:loading.attr="disabled" wire:target="processExport">
                            <span wire:loading.remove wire:target="processExport" class="flex items-center gap-1.5">
                                <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                                Download Excel (.xlsx)
                            </span>
                            <span wire:loading wire:target="processExport" class="flex items-center gap-1">
                                <span class="loading loading-spinner loading-xs"></span>
                                Mengunduh Excel...
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif
</div>
