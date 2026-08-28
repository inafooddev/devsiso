<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    
    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 md:gap-4 shrink-0">
        
        {{-- Card 1: Total Toko Transaksi --}}
        <div class="bg-base-100 p-3 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider mt-1">Total Toko Transaksi</h3>
            </div>
            <div class="text-lg md:text-xl font-black leading-none mt-2 relative z-10 text-primary">
                {{ number_format($kpi->total_toko, 0, ',', '.') }}
            </div>
            <div class="text-[9px] mt-1 text-base-content/50 relative z-10">Total toko aktif pada periode ini</div>
        </div>

        {{-- Card 2-7: Per Product Bucket Stats --}}
        @for($i = 1; $i <= 6; $i++)
            @php 
                $col = 'beli_' . $i; 
                $val = $kpi->$col ?? 0;
                $pct = $kpi->total_toko > 0 ? ($val / $kpi->total_toko) * 100 : 0;
            @endphp
            <div class="bg-base-100 p-3 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-{{ $i == 6 ? 'success' : 'primary' }}/10 transition-transform group-hover:scale-150"></div>
                
                <div class="flex flex-col relative z-10 h-full justify-between">
                    <div>
                        <div class="text-[9px] font-black {{ $i == 6 ? 'text-success' : 'text-primary' }} uppercase">Beli {{ $i }} Produk</div>
                        <h3 class="text-[9px] md:text-[10px] font-bold text-base-content/70 leading-tight mt-0.5">
                            Toko Beli {{ $i }} Produk
                        </h3>
                    </div>
                    
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-lg font-black {{ $val > 0 ? 'text-base-content' : 'text-base-content/30' }}">
                            {{ number_format($val, 0, ',', '.') }}
                        </div>
                        <div class="text-[10px] font-bold {{ $pct >= 50 ? 'text-success' : ($pct > 0 ? 'text-warning' : 'text-base-content/30') }}">
                            {{ number_format($pct, 1, ',', '.') }}%
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Monitoring Top Item & NPD</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar pencapaian transaksi per toko</p>
            </div>
            
            {{-- Menggunakan flex-wrap agar barisan aksi jatuh secara responsif --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <div wire:loading class="text-xs text-primary font-bold mr-2">
                    <span class="loading loading-spinner loading-xs align-middle"></span> Memuat...
                </div>

                {{-- Filter Region --}}
                <select wire:model.live="filterRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>

                {{-- Filter Area --}}
                <select wire:model.live="filterArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 max-w-[150px]">
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                    @endforeach
                </select>
                
                {{-- Filter Supervisor --}}
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 max-w-[150px]">
                    <option value="">Semua Supervisor</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor }}">{{ $supervisor }}</option>
                    @endforeach
                </select>

                {{-- Filter Distributor --}}
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 max-w-[180px]">
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $distributor)
                        <option value="{{ $distributor }}">{{ $distributor }}</option>
                    @endforeach
                </select>

                {{-- Filter Bulan --}}
                <select wire:model.live="month" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0,0,0,$i,1)) }}</option>
                    @endfor
                </select>

                {{-- Filter Tahun --}}
                <select wire:model.live="year" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    @for($i=date('Y')-2; $i<=date('Y'); $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
                
                {{-- Filter Produk --}}
                <select wire:model.live="filterBucket" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Jumlah Produk</option>
                    @for($i=1; $i<=6; $i++)
                        <option value="{{ $i }}">Hanya Beli {{ $i }} Produk</option>
                    @endfor
                </select>

                {{-- Button Reset Filter --}}
                <button wire:click="resetFilters" class="btn btn-sm btn-outline btn-error rounded-xl ml-auto sm:ml-0" title="Reset Semua Filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </button>
                
                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <button wire:click="export" wire:loading.attr="disabled" class="btn btn-sm bg-base-100 hover:bg-base-200 border-base-300">
                        <span wire:loading.remove wire:target="export">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        </span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs text-success"></span>
                        <span class="hidden sm:inline">Export</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative custom-scrollbar">
            {{-- whitespace-nowrap agar kolom tidak hancur menjadi baris baru saat dizoom --}}
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="bg-base-300 shadow-sm z-20">Region</th>
                        <th class="bg-base-300 shadow-sm z-20">Area</th>
                        <th class="bg-base-300 shadow-sm">Distributor</th>
                        <th class="bg-base-300 shadow-sm">Kode Toko</th>
                        <th class="bg-base-300 shadow-sm">Nama Toko</th>
                        <th class="bg-base-300 shadow-sm">Alamat</th>
                        
                        <!-- Dynamic Product Columns -->
                        @foreach($topItems as $index => $item)
                            <th class="bg-base-300 shadow-sm text-center border-l border-base-200">
                                <div class="text-[10px] {{ $item->kategory === 'NPD' ? 'text-primary' : 'text-secondary' }} font-black uppercase">{{ $item->kategory }}</div>
                                <div class="text-[10px]">{{ $item->topitem }}</div>
                            </th>
                        @endforeach
                        
                        <th class="bg-primary text-primary-content text-center shadow-sm text-[10px]">
                            Total Prd<br>Transaksi
                        </th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $row)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td>{{ $row->region_name ?? '-' }}</td>
                            <td>{{ $row->area_name ?? '-' }}</td>
                            <td>{{ $row->distributor_name ?? $row->distributor_code }}</td>
                            <td class="font-mono text-xs">{{ $row->uniq_code }}</td>
                            <td class="font-bold">{{ $row->customer_name ?? '-' }}</td>
                            <td class="truncate max-w-[200px]" title="{{ $row->address }}">{{ $row->address ?? '-' }}</td>
                            
                            @foreach($topItems as $index => $item)
                                @php 
                                    $col = 'prd' . ($index + 1) . '_qty'; 
                                    $qty = $row->$col;
                                    // Use number_format to get thousand separators and 2 decimals, then trim trailing zeroes and comma
                                    $formattedQty = rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',');
                                @endphp
                                <td class="text-center border-l border-base-200/50 {{ $qty > 0 ? 'font-bold text-success' : 'bg-error/15 text-error/60' }}">
                                    {{ $qty > 0 ? $formattedQty : '-' }}
                                </td>
                            @endforeach
                            
                            <td class="text-center font-black text-primary bg-primary/5">
                                {{ $row->total_prd_transaksi }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + count($topItems) }}" class="text-center py-10 text-base-content/50">
                                Tidak ada data transaksi top item pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        @if($data->hasPages())
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            <div class="w-full">
                {{ $data->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
