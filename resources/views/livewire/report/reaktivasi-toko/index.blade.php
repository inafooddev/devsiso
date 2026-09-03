<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Report Reaktivasi Toko</x-slot>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Report Reaktivasi Toko</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Memantau keaktifan toko bulan ini</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.500ms="search" placeholder="Cari nama/kode toko..." />
                
                {{-- Filter Region --}}
                <select wire:model.live="filterRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Region</option>
                    @foreach($this->filterOptions['regions'] as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>

                {{-- Filter Area --}}
                <select wire:model.live="filterArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Area</option>
                    @foreach($this->filterOptions['areas'] as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                    @endforeach
                </select>

                {{-- Filter Supervisor --}}
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 max-w-[120px]">
                    <option value="">Semua SPV</option>
                    @foreach($this->filterOptions['supervisors'] as $spv)
                        <option value="{{ $spv }}">{{ Str::limit($spv, 15) }}</option>
                    @endforeach
                </select>

                {{-- Filter Distributor --}}
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 max-w-[150px]">
                    <option value="">Semua Distributor</option>
                    @foreach($this->filterOptions['distributors'] as $dist)
                        <option value="{{ $dist }}">{{ Str::limit($dist, 15) }}</option>
                    @endforeach
                </select>
                
                {{-- Filter Bulan --}}
                <select wire:model.live="filterBulan" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>

                {{-- Filter Tahun --}}
                <select wire:model.live="filterTahun" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                </select>
                
                {{-- Filter Status --}}
                <select wire:model.live="filterStatus" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="tidak_aktif">Tidak Aktif</option>
                </select>
                
                {{-- Filter Type --}}
                <select wire:model.live="filterType" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Type</option>
                    <option value="SO">SO</option>
                    <option value="G">G</option>
                    <option value="SG">SG</option>
                    <option value="R">R</option>
                </select>
                
                {{-- Reset Filter Button --}}
                <button wire:click="resetFilters" class="btn btn-sm btn-ghost btn-circle text-base-content/60 hover:text-primary" title="Reset Filter" wire:loading.attr="disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
                
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <x-ui.action-button type="export" />
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-16">No</th>
                        <th>Region & Area</th>
                        <th>Distributor & SPV</th>
                        <th>Kode</th>
                        <th>Toko</th>
                        <th>Type</th>
                        <th>Class</th>
                        <th>Status Aktif</th>
                        <th>Last Transaksi</th>
                        <th class="text-right">Total Transaksi</th>
                        <th class="text-right">Avg 6 Bulan</th>
                        <th class="text-right">Pencapaian Bln Ini</th>
                        @for($m = 1; $m <= 12; $m++)
                            <th class="text-right">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($this->stores as $index => $row)
                        @php 
                            $avg = $row->avg_6_months;
                            $type = 'R';
                            $typeColor = 'badge-ghost';
                            
                            if ($avg > 10000000) {
                                $type = 'SO';
                                $typeColor = 'badge-primary';
                            } elseif ($avg >= 5000000) {
                                $type = 'G';
                                $typeColor = 'badge-info';
                            } elseif ($avg >= 3000000) {
                                $type = 'SG';
                                $typeColor = 'badge-warning';
                            }

                            $class = ($type === 'R') ? 'Non Pareto' : 'Pareto';
                            $isAktif = $row->pencapaian_bulan_ini > 0;
                        @endphp
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th>{{ $this->stores->firstItem() + $index }}</th>
                            <td>
                                <div class="font-bold truncate max-w-[120px] md:max-w-[150px]" title="{{ $row->region }}">{{ $row->region }}</div>
                                <div class="text-xs text-base-content/60 truncate max-w-[120px] md:max-w-[150px]" title="{{ $row->area }}">{{ $row->area }}</div>
                            </td>
                            <td>
                                <div class="font-bold truncate max-w-[150px] md:max-w-[180px]" title="{{ $row->distributor }}">{{ $row->distributor }}</div>
                                <div class="text-xs text-base-content/60 truncate max-w-[150px] md:max-w-[180px]" title="{{ $row->supervisor }}">{{ $row->supervisor }}</div>
                            </td>
                            <td>
                                <div class="font-bold font-mono">{{ $row->uniq_kd }}</div>
                                <div class="text-xs text-base-content/60 font-mono">{{ $row->custno }}</div>
                            </td>
                            <td>
                                <div class="font-bold truncate max-w-[180px] md:max-w-[220px]" title="{{ $row->custname }}">{{ $row->custname }}</div>
                                <div class="text-xs text-base-content/60 truncate max-w-[180px] md:max-w-[220px]" title="{{ $row->alamat }}">{{ $row->alamat }}</div>
                            </td>
                            <td>
                                <span class="badge badge-sm text-xs {{ $typeColor }} border-none">{{ $type }}</span>
                            </td>
                            <td>
                                <span class="badge badge-sm badge-outline {{ $class == 'Pareto' ? 'badge-primary' : '' }}">{{ $class }}</span>
                            </td>
                            <td>
                                @if($isAktif)
                                    <span class="badge badge-sm badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-sm badge-error">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>{{ $row->last_transaksi ? \Carbon\Carbon::parse($row->last_transaksi)->format('d M Y') : '-' }}</td>
                            <td class="text-right font-mono">{{ number_format((float)$row->total_transaksi, 0, ',', '.') }}</td>
                            <td class="text-right font-mono">{{ number_format((float)$row->avg_6_months, 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold {{ $isAktif ? 'text-success' : '' }}">{{ number_format((float)$row->pencapaian_bulan_ini, 0, ',', '.') }}</td>
                            @for($m = 1; $m <= 12; $m++)
                                @php $col = 'bln_' . str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                                <td class="text-right font-mono text-xs {{ $row->$col > 0 ? 'text-success font-semibold' : 'bg-error/20 text-error font-semibold' }}">
                                    {{ $row->$col > 0 ? number_format((float)$row->$col, 0, ',', '.') : '-' }}
                                </td>
                            @endfor
                        </tr>
                    @empty
                        <tr>
                            <td colspan="23" class="text-center py-6 text-base-content/50">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
            {{ $this->stores->links() }}
        </div>
    </div>
</div>
