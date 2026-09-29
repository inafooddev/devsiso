<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Monitoring Reward Distributor</x-slot>

    {{-- Loading Overlay for the whole page (optional) --}}
    <div wire:loading.flex class="fixed inset-0 z-50 bg-base-100/50 backdrop-blur-sm items-center justify-center">
        <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>



    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Monitoring Reward</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Tahun {{ $year }} - Semua Periode (Flat View)</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full sm:w-auto max-w-[150px] bg-base-100 text-xs">
                    <option value="">Semua Region</option>
                    @foreach($regions as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full sm:w-auto max-w-[150px] bg-base-100 text-xs">
                    <option value="">Semua Area</option>
                    @foreach($areas as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="select select-sm select-bordered w-full sm:w-auto max-w-[150px] bg-base-100 text-xs">
                    <option value="ikut">Ikut Program</option>
                    <option value="semua">Semua (Termasuk Tidak Ikut)</option>
                    <option value="tidak_ikut">Tidak Ikut Program</option>
                </select>
                <x-ui.search-input wire:model.live.debounce.300ms="search" />
                @canImport('monitoring-reward-distributor')
                <button wire:click="openImportModal" class="btn btn-sm btn-outline btn-primary">
                    <x-heroicon-s-arrow-up-tray class="w-4 h-4" /> Import AR & Stok
                </button>
                @endcanImport
                @canExport('monitoring-reward-distributor')
                <x-ui.action-button type="export" wire:click="export" />
                @endcanExport
            </div>
        </div>

        {{-- Body Card --}}
        <div class="flex-1 overflow-auto bg-base-200/50 w-full relative">
            <div class="p-4 md:p-6 flex flex-col gap-4 md:gap-6 w-full">
                @php
                    $borderColors = ['border-b-primary', 'border-b-secondary', 'border-b-accent', 'border-b-info', 'border-b-success', 'border-b-warning', 'border-b-error'];
                @endphp
                @forelse($data as $row)
                    @php
                        $colorClass = $borderColors[$loop->index % count($borderColors)];
                    @endphp
                    <div class="bg-base-100 rounded-xl border border-base-200 border-b-4 {{ $colorClass }} shadow hover:shadow-md hover:bg-base-200/30 transition-all flex flex-col xl:flex-row overflow-hidden relative group">
                        
                        {{-- Distributor Info --}}
                        <div class="p-4 xl:w-72 xl:shrink-0 flex flex-col justify-center border-b xl:border-b-0 xl:border-r border-base-200/50 relative bg-base-200/20">
                            <div class="absolute top-2 right-2 dropdown dropdown-end">
                                <button tabindex="0" class="btn btn-xs btn-ghost btn-circle">
                                    <x-heroicon-s-ellipsis-vertical class="w-4 h-4" />
                                </button>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48 border border-base-200">
                                    <li>
                                        <button wire:click="openDetailModal('{{ $row['distributor_code'] }}', '{{ addslashes($row['distributor']) }}')">
                                            <x-heroicon-s-chart-bar class="w-4 h-4" /> Detail Bulanan
                                        </button>
                                    </li>
                                    @canEdit('monitoring-reward-distributor')
                                    <li>
                                        <button wire:click="openSettingsModal('{{ $row['distributor_code'] }}', '{{ addslashes($row['distributor']) }}')">
                                            <x-heroicon-s-cog-8-tooth class="w-4 h-4" /> Pengaturan
                                        </button>
                                    </li>
                                    @endcanEdit
                                </ul>
                            </div>

                            <div class="pr-6">
                                @if($row['distributor'] !== '-')
                                    <h3 class="font-bold text-base-content leading-tight mb-1">{{ $row['distributor'] }}</h3>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span class="badge badge-sm badge-ghost text-[9px]">{{ $row['region'] }}</span>
                                        <span class="badge badge-sm badge-ghost text-[9px]">{{ $row['area'] }}</span>
                                    </div>
                                    <div class="text-[10px] text-base-content/50 mt-1.5">{{ $row['cabang'] }} &bull; {{ $row['distributor_code'] }}</div>
                                @else
                                    <h3 class="font-bold text-base-content leading-tight mb-1">{{ $row['cabang'] }}</h3>
                                    <div class="text-[10px] text-error/80 italic mt-1">Master data belum dilink</div>
                                @endif
                            </div>
                        </div>

                        {{-- Periode 1 --}}
                        @php
                            $p1PctIn = $row['p1']['target'] > 0 ? ($row['p1']['sell_in'] / $row['p1']['target']) * 100 : 0;
                            $p1PctOut = $row['p1']['target'] > 0 ? ($row['p1']['sell_out'] / $row['p1']['target']) * 100 : 0;
                        @endphp
                        <div class="p-3 md:p-4 flex-1 min-w-[200px] border-b xl:border-b-0 xl:border-r border-base-200/50 flex flex-col justify-center">
                            <div class="flex justify-between items-center mb-2">
                                <div class="text-[11px] font-bold text-base-content/80 flex items-center gap-1.5">
                                    <div class="w-1.5 h-3 bg-primary rounded-full"></div>
                                    PERIODE 1
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="badge {{ $row['p1']['is_ar_achieved'] ? 'badge-success' : 'badge-error' }} badge-sm text-[9px] font-bold text-white border-0 h-4 px-1" title="AR Late > 7d: {{ $row['p1']['ar_late_count'] }}x">AR</div>
                                    <div class="badge {{ $row['p1']['is_stock_achieved'] ? 'badge-success' : 'badge-error' }} badge-sm text-[9px] font-bold text-white border-0 h-4 px-1" title="Avg Stock: {{ number_format($row['p1']['stock_avg'], 1) }}%">STK</div>
                                </div>
                            </div>
                            
                            <div class="space-y-5">
                                <div>
                                    <div class="flex justify-between text-[10px] mb-0.5">
                                        <span class="text-base-content/70">Sell In vs Target</span>
                                        <span class="font-bold {{ $p1PctIn >= 100 ? 'text-success' : ($p1PctIn >= 80 ? 'text-warning' : 'text-error') }}">{{ number_format($p1PctIn, 1) }}%</span>
                                    </div>
                                    <progress class="progress w-full h-2 {{ $p1PctIn >= 100 ? 'progress-success' : ($p1PctIn >= 80 ? 'progress-warning' : 'progress-error') }} bg-base-200" value="{{ min(100, $p1PctIn) }}" max="100"></progress>
                                    <div class="flex justify-center gap-6 mt-1.5 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Target</span>
                                            <span class="font-mono text-[10px]">{{ number_format($row['p1']['target']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Actual</span>
                                            <span class="font-mono text-[10px] font-bold text-base-content/80">{{ number_format($row['p1']['sell_in']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Gap</span>
                                            <span class="font-mono text-[10px] {{ $row['p1']['sell_in'] >= $row['p1']['target'] ? 'text-success' : 'text-error font-bold' }}">{{ number_format($row['p1']['sell_in'] - $row['p1']['target']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between text-[10px] mb-0.5">
                                        <span class="text-base-content/70">Sell Out vs Target</span>
                                        <span class="font-bold {{ $p1PctOut >= 100 ? 'text-success' : ($p1PctOut >= 80 ? 'text-warning' : 'text-error') }}">{{ number_format($p1PctOut, 1) }}%</span>
                                    </div>
                                    <progress class="progress w-full h-2 {{ $p1PctOut >= 100 ? 'progress-success' : ($p1PctOut >= 80 ? 'progress-warning' : 'progress-error') }} bg-base-200" value="{{ min(100, $p1PctOut) }}" max="100"></progress>
                                    <div class="flex justify-center gap-6 mt-1.5 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Target</span>
                                            <span class="font-mono text-[10px]">{{ number_format($row['p1']['target']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Actual</span>
                                            <span class="font-mono text-[10px] font-bold text-base-content/80">{{ number_format($row['p1']['sell_out']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Gap</span>
                                            <span class="font-mono text-[10px] {{ $row['p1']['sell_out'] >= $row['p1']['target'] ? 'text-success' : 'text-error font-bold' }}">{{ number_format($row['p1']['sell_out'] - $row['p1']['target']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Reward P1 --}}
                        <div class="p-3 xl:w-28 xl:shrink-0 flex flex-col items-center justify-center border-b xl:border-b-0 xl:border-r-2 border-base-300 text-center {{ $row['p1']['is_target_achieved'] ? ($row['p1']['is_ar_achieved'] && $row['p1']['is_stock_achieved'] ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning-content') : 'bg-error/20 text-error font-bold' }}">
                            <div class="text-[9px] uppercase tracking-wider font-semibold opacity-70 mb-1">Status P1</div>
                            <div class="text-[11px] font-bold leading-tight">
                                @if($row['p1']['is_target_achieved'])
                                    @if(!$row['p1']['is_ar_achieved'] || !$row['p1']['is_stock_achieved'])
                                        Capai<br>(Penalti)
                                    @else
                                        Capai Penuh
                                    @endif
                                @else
                                    Tidak<br>Capai
                                @endif
                            </div>
                        </div>

                        {{-- Periode 2 --}}
                        @php
                            $p2PctIn = $row['p2']['target'] > 0 ? ($row['p2']['sell_in'] / $row['p2']['target']) * 100 : 0;
                            $p2PctOut = $row['p2']['target'] > 0 ? ($row['p2']['sell_out'] / $row['p2']['target']) * 100 : 0;
                        @endphp
                        <div class="p-3 md:p-4 flex-1 min-w-[200px] border-b xl:border-b-0 xl:border-r border-base-200/50 flex flex-col justify-center">
                            <div class="flex justify-between items-center mb-2">
                                <div class="text-[11px] font-bold text-base-content/80 flex items-center gap-1.5">
                                    <div class="w-1.5 h-3 bg-secondary rounded-full"></div>
                                    PERIODE 2
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="badge {{ $row['p2']['is_ar_achieved'] ? 'badge-success' : 'badge-error' }} badge-sm text-[9px] font-bold text-white border-0 h-4 px-1" title="AR Late > 7d: {{ $row['p2']['ar_late_count'] }}x">AR</div>
                                    <div class="badge {{ $row['p2']['is_stock_achieved'] ? 'badge-success' : 'badge-error' }} badge-sm text-[9px] font-bold text-white border-0 h-4 px-1" title="Avg Stock: {{ number_format($row['p2']['stock_avg'], 1) }}%">STK</div>
                                </div>
                            </div>
                            
                            <div class="space-y-5">
                                <div>
                                    <div class="flex justify-between text-[10px] mb-0.5">
                                        <span class="text-base-content/70">Sell In vs Target</span>
                                        <span class="font-bold {{ $p2PctIn >= 100 ? 'text-success' : ($p2PctIn >= 80 ? 'text-warning' : 'text-error') }}">{{ number_format($p2PctIn, 1) }}%</span>
                                    </div>
                                    <progress class="progress w-full h-2 {{ $p2PctIn >= 100 ? 'progress-success' : ($p2PctIn >= 80 ? 'progress-warning' : 'progress-error') }} bg-base-200" value="{{ min(100, $p2PctIn) }}" max="100"></progress>
                                    <div class="flex justify-center gap-6 mt-1.5 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Target</span>
                                            <span class="font-mono text-[10px]">{{ number_format($row['p2']['target']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Actual</span>
                                            <span class="font-mono text-[10px] font-bold text-base-content/80">{{ number_format($row['p2']['sell_in']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Gap</span>
                                            <span class="font-mono text-[10px] {{ $row['p2']['sell_in'] >= $row['p2']['target'] ? 'text-success' : 'text-error font-bold' }}">{{ number_format($row['p2']['sell_in'] - $row['p2']['target']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between text-[10px] mb-0.5">
                                        <span class="text-base-content/70">Sell Out vs Target</span>
                                        <span class="font-bold {{ $p2PctOut >= 100 ? 'text-success' : ($p2PctOut >= 80 ? 'text-warning' : 'text-error') }}">{{ number_format($p2PctOut, 1) }}%</span>
                                    </div>
                                    <progress class="progress w-full h-2 {{ $p2PctOut >= 100 ? 'progress-success' : ($p2PctOut >= 80 ? 'progress-warning' : 'progress-error') }} bg-base-200" value="{{ min(100, $p2PctOut) }}" max="100"></progress>
                                    <div class="flex justify-center gap-6 mt-1.5 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Target</span>
                                            <span class="font-mono text-[10px]">{{ number_format($row['p2']['target']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Actual</span>
                                            <span class="font-mono text-[10px] font-bold text-base-content/80">{{ number_format($row['p2']['sell_out']) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-base-content/50 uppercase tracking-wider">Gap</span>
                                            <span class="font-mono text-[10px] {{ $row['p2']['sell_out'] >= $row['p2']['target'] ? 'text-success' : 'text-error font-bold' }}">{{ number_format($row['p2']['sell_out'] - $row['p2']['target']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Reward P2 --}}
                        <div class="p-3 xl:w-28 xl:shrink-0 flex flex-col items-center justify-center text-center {{ $row['p2']['is_target_achieved'] ? ($row['p2']['is_ar_achieved'] && $row['p2']['is_stock_achieved'] ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning-content') : 'bg-error/20 text-error font-bold' }}">
                            <div class="text-[9px] uppercase tracking-wider font-semibold opacity-70 mb-1">Status P2</div>
                            <div class="text-[11px] font-bold leading-tight">
                                @if($row['p2']['is_target_achieved'])
                                    @if(!$row['p2']['is_ar_achieved'] || !$row['p2']['is_stock_achieved'])
                                        Capai<br>(Penalti)
                                    @else
                                        Capai Penuh
                                    @endif
                                @else
                                    Tidak<br>Capai
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center p-12 text-base-content/50 border-2 border-dashed border-base-300 rounded-xl min-h-[300px]">
                        <x-heroicon-o-inbox class="w-16 h-16 mb-4 text-base-300" />
                        <h3 class="text-lg font-bold">Belum Ada Data</h3>
                        <p class="text-sm mt-1">Tidak ada distributor yang sesuai dengan filter atau target belum diunggah.</p>
                    </div>
                @endforelse
            </div>
        </div>
        
    </div>
    {{-- Import Modal --}}
    @if($isImportModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-base-100 rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-4">Import Data AR & Stok</h3>
                
                <div class="alert bg-blue-50 text-blue-800 border border-blue-200 mb-4 p-3 rounded-md text-sm">
                    <div class="flex flex-col gap-2">
                        <span>Pastikan Anda mengisi data menggunakan template terbaru.</span>
                        <button wire:click="downloadTemplate" class="btn btn-xs btn-primary self-start">
                            <x-heroicon-s-arrow-down-tray class="w-3 h-3" /> Download Template
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="processImport" class="flex flex-col gap-4">
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold">Pilih Bulan & Tahun</span></label>
                        <input type="month" wire:model="importMonth" class="input input-bordered w-full" required />
                        @error('importMonth') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold">File Excel (.xlsx)</span></label>
                        <input type="file" wire:model="importFile" class="file-input file-input-bordered w-full" accept=".xlsx,.xls,.csv" required />
                        <div wire:loading wire:target="importFile" class="text-xs text-primary mt-1">Mengunggah...</div>
                        @error('importFile') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="modal-action mt-6">
                        <button type="button" wire:click="closeImportModal" class="btn btn-ghost">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="processImport">Proses Import</span>
                            <span wire:loading wire:target="processImport" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Settings Modal --}}
    @if($isSettingsModalOpen)
    <div class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="fixed inset-0 bg-base-100/50 backdrop-blur-sm" wire:click="closeSettingsModal"></div>
        <div class="bg-base-100 rounded-xl shadow-2xl border border-base-300 w-full max-w-2xl z-10 overflow-hidden flex flex-col mx-4">
            <div class="p-4 border-b border-base-200 bg-base-200/50 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-lg">Pengaturan Bulan Evaluasi: <span class="text-primary">{{ $settingDistributorName }}</span></h3>
                <button wire:click="closeSettingsModal" class="btn btn-sm btn-ghost btn-circle"><x-heroicon-s-x-mark class="w-5 h-5"/></button>
            </div>
            
            <div class="p-4 sm:p-6 overflow-y-auto max-h-[60vh] space-y-6">
                <div class="alert alert-info shadow-sm text-sm p-3">
                    <x-heroicon-s-information-circle class="w-5 h-5"/>
                    <span>Centang bulan yang ingin dihitung ke dalam masing-masing periode. Jika tidak ada yang dicentang, data tidak akan terhitung.</span>
                </div>
                
                <div class="form-control bg-base-200/50 p-3 rounded-lg border border-base-200">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" wire:model="isParticipating" class="toggle toggle-primary" />
                        <span class="label-text font-bold">Distributor ini mengikuti program Reward</span>
                    </label>
                </div>
                
                @php
                    $months = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                        7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                    ];
                @endphp

                <div class="space-y-4">
                    <div>
                        <h4 class="font-bold mb-2">Periode 1</h4>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @foreach($months as $num => $name)
                            <label class="flex items-center gap-2 cursor-pointer bg-base-200/50 p-2 rounded-lg hover:bg-base-200 transition-colors">
                                <input type="checkbox" wire:model="selectedMonthsP1" value="{{ $num }}" class="checkbox checkbox-sm checkbox-primary" />
                                <span class="text-sm font-semibold">{{ $name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="divider my-1"></div>

                    <div>
                        <h4 class="font-bold mb-2">Periode 2</h4>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @foreach($months as $num => $name)
                            <label class="flex items-center gap-2 cursor-pointer bg-base-200/50 p-2 rounded-lg hover:bg-base-200 transition-colors">
                                <input type="checkbox" wire:model="selectedMonthsP2" value="{{ $num }}" class="checkbox checkbox-sm checkbox-primary" />
                                <span class="text-sm font-semibold">{{ $name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-base-200 bg-base-200/50 flex justify-end gap-3 shrink-0">
                <button wire:click="closeSettingsModal" class="btn btn-outline">Batal</button>
                <button wire:click="saveSettings" class="btn btn-primary" wire:loading.class="loading">
                    <x-heroicon-s-check class="w-4 h-4 hidden sm:block" /> Simpan
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- Detail Modal --}}
    @if($isDetailModalOpen)
    <div class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="fixed inset-0 bg-base-100/50 backdrop-blur-sm" wire:click="closeDetailModal"></div>
        <div class="bg-base-100 rounded-xl shadow-2xl border border-base-300 w-full max-w-4xl z-10 overflow-hidden flex flex-col mx-4 h-[85vh]">
            <div class="p-4 md:p-6 border-b border-base-200 bg-base-200/50 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="font-bold text-lg md:text-xl">Detail Pencapaian Bulanan</h3>
                    <p class="text-xs text-base-content/60 mt-1">Distributor: <span class="font-bold text-primary">{{ $detailDistributorName }}</span></p>
                </div>
                <button wire:click="closeDetailModal" class="btn btn-sm btn-ghost btn-circle"><x-heroicon-s-x-mark class="w-5 h-5"/></button>
            </div>
            
            <div class="flex-1 overflow-auto bg-base-100 relative p-4">
                <table class="table table-sm w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider text-base-content/80 shadow-sm sticky top-0 bg-base-200 z-20">
                        <tr>
                            <th class="py-3 px-4 rounded-tl-lg">Bulan</th>
                            <th class="py-3 px-4 text-right">Target</th>
                            <th class="py-3 px-4 text-right">Sell In</th>
                            <th class="py-3 px-4 text-right">Sell Out</th>
                            <th class="py-3 px-4 text-center rounded-tr-lg">AR & STK</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($monthlyDetails as $mRow)
                            @php
                                $pctIn = $mRow['target'] > 0 ? ($mRow['sell_in'] / $mRow['target']) * 100 : 0;
                                $pctOut = $mRow['target'] > 0 ? ($mRow['sell_out'] / $mRow['target']) * 100 : 0;
                            @endphp
                            <tr class="hover:bg-base-200/50 transition-colors border-b border-base-200">
                                <td class="py-3 px-4 font-bold">{{ $mRow['month_name'] }}</td>
                                
                                <td class="py-3 px-4 text-right font-mono text-base-content/70">{{ number_format($mRow['target']) }}</td>
                                
                                <td class="py-3 px-4 text-right">
                                    <div class="font-mono font-bold">{{ number_format($mRow['sell_in']) }}</div>
                                    @if($mRow['target'] > 0)
                                        <div class="text-[10px] font-bold {{ $pctIn >= 100 ? 'text-success' : ($pctIn >= 80 ? 'text-warning' : 'text-error') }} mt-0.5">{{ number_format($pctIn, 1) }}%</div>
                                    @endif
                                </td>
                                
                                <td class="py-3 px-4 text-right">
                                    <div class="font-mono font-bold">{{ number_format($mRow['sell_out']) }}</div>
                                    @if($mRow['target'] > 0)
                                        <div class="text-[10px] font-bold {{ $pctOut >= 100 ? 'text-success' : ($pctOut >= 80 ? 'text-warning' : 'text-error') }} mt-0.5">{{ number_format($pctOut, 1) }}%</div>
                                    @endif
                                </td>
                                
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="badge badge-sm border-0 font-semibold {{ $mRow['ar_value'] <= 7 ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                                            AR: {{ $mRow['ar_value'] }}
                                        </div>
                                        <div class="badge badge-sm border-0 font-semibold {{ $mRow['is_stock_achieved'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                                            STK: {{ number_format($mRow['stock_avg'], 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-base-content/50">Belum ada data bulanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-base-200 bg-base-200/50 flex justify-end shrink-0">
                <button wire:click="closeDetailModal" class="btn btn-outline">Tutup</button>
            </div>
        </div>
    </div>
    @endif
    {{-- Script untuk Auto Reload Halaman --}}
    @script
    <script>
        Livewire.on('page-reload', () => {
            setTimeout(() => {
                window.location.reload();
            }, 500); // Jeda setengah detik agar notifikasi sukses terbaca
        });
    </script>
    @endscript
</div>
