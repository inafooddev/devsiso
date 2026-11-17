<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Pencapaian RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.dashboard') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Dashboard</a>
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Pencapaian</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SKB</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Plan Kunjungan</a>
            <a href="{{ route('rwo.monitoring-pareto') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Monitoring Visit</a>
        </div>
    </div>

    @if(isset($activeQuarterLabel))
        <div class="flex justify-between items-end mt-2 mb-1 shrink-0">
            <h2 class="text-lg font-bold text-base-content/80">Ringkasan KPI</h2>
            <div class="badge badge-info badge-outline font-bold">{{ $activeQuarterLabel }}</div>
        </div>
    @endif

    {{-- KPI SUMMARY --}}
    @php
        $totalToko = $kpi->total_toko ?? 0;
        $tokoTransaksi = $kpi->toko_transaksi ?? 0;
        $tokoTransaksiPct = $totalToko > 0 ? ($tokoTransaksi / $totalToko) * 100 : 0;
        $tokoBelumTransaksi = $totalToko - $tokoTransaksi;
        
        $overallPct = $kpi->total_target > 0 ? ($kpi->total_achievement / $kpi->total_target) * 100 : 0;
        
        $tokoHijau = $kpi->toko_hijau ?? 0;
        $tokoHijauPct = $totalToko > 0 ? ($tokoHijau / $totalToko) * 100 : 0;
        
        $tokoKuning = $kpi->toko_kuning ?? 0;
        $tokoKuningPct = $totalToko > 0 ? ($tokoKuning / $totalToko) * 100 : 0;
        
        $tokoMerah = $kpi->toko_merah ?? 0;
        $tokoMerahPct = $totalToko > 0 ? ($tokoMerah / $totalToko) * 100 : 0;
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 shrink-0">
        {{-- Card 1: Toko & Transaksi --}}
        <div wire:click="filterByKpiCard('transaksi')" title="Klik untuk filter toko yang sudah transaksi" class="stats shadow bg-base-100 border cursor-pointer transition-all hover:shadow-md {{ $appliedStatusTransaksi === 'Sudah' ? 'border-primary ring-2 ring-primary/30' : 'border-base-300' }}">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Total Transaksi / Toko</div>
                <div class="stat-value text-2xl text-primary">{{ number_format($tokoTransaksi, 0, ',', '.') }}</div>
                <div class="stat-desc text-xs font-semibold text-primary/80 mt-1 flex justify-between items-center">
                    <span>dari Total {{ number_format($totalToko, 0, ',', '.') }} Toko ({{ number_format($tokoTransaksiPct, 1, ',', '.') }}%)</span>
                    <span class="text-xs font-bold text-error">Gap: {{ number_format($tokoBelumTransaksi, 0, ',', '.') }}</span>
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-primary w-full h-1.5" value="{{ min($tokoTransaksiPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>

        {{-- Card 2: Target & Achievement --}}
        <div class="stats shadow bg-base-100 border border-base-300">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Target & Pencapaian</div>
                <div class="stat-value text-xl text-success font-black mt-1">Rp {{ number_format($kpi->total_achievement, 0, ',', '.') }}</div>
                <div class="stat-desc text-xs font-semibold text-base-content/70 mt-1">
                    Target Prorata: Rp {{ number_format($kpi->total_target, 0, ',', '.') }}
                </div>
                <div class="w-full mt-2 flex items-center gap-2">
                    <progress class="progress progress-success w-full h-1.5" value="{{ min($overallPct, 100) }}" max="100"></progress>
                    <span class="text-xs font-bold text-success shrink-0">{{ number_format($overallPct, 1, ',', '.') }}%</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Kolom Hijau --}}
        <div wire:click="filterByKpiCard('hijau')" title="Klik untuk filter Kolom Hijau" class="stats shadow bg-base-100 border cursor-pointer transition-all hover:shadow-md {{ $appliedStatusProgress === '1. HIJAU' ? 'border-success ring-2 ring-success/30' : 'border-base-300' }}">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Kolom Hijau (>= 100%)</div>
                <div class="stat-value text-2xl text-success">{{ number_format($tokoHijau, 0, ',', '.') }} <span class="text-xs font-bold opacity-75">Toko</span></div>
                <div class="stat-desc text-xs font-semibold text-success/80 mt-1">
                    {{ number_format($tokoHijauPct, 1, ',', '.') }}% dari Total Toko
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-success w-full h-1.5" value="{{ min($tokoHijauPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>

        {{-- Card 4: Kolom Kuning --}}
        <div wire:click="filterByKpiCard('kuning')" title="Klik untuk filter Kolom Kuning" class="stats shadow bg-base-100 border cursor-pointer transition-all hover:shadow-md {{ $appliedStatusProgress === '2. KUNING' ? 'border-warning ring-2 ring-warning/30' : 'border-base-300' }}">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Kolom Kuning (80-99%)</div>
                <div class="stat-value text-2xl text-warning">{{ number_format($tokoKuning, 0, ',', '.') }} <span class="text-xs font-bold opacity-75">Toko</span></div>
                <div class="stat-desc text-xs font-semibold text-warning/80 mt-1">
                    {{ number_format($tokoKuningPct, 1, ',', '.') }}% dari Total Toko
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-warning w-full h-1.5" value="{{ min($tokoKuningPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>

        {{-- Card 5: Kolom Merah --}}
        <div wire:click="filterByKpiCard('merah')" title="Klik untuk filter Kolom Merah" class="stats shadow bg-base-100 border cursor-pointer transition-all hover:shadow-md {{ $appliedStatusProgress === '3. MERAH' ? 'border-error ring-2 ring-error/30' : 'border-base-300' }}">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Kolom Merah (< 80%)</div>
                <div class="stat-value text-2xl text-error">{{ number_format($tokoMerah, 0, ',', '.') }} <span class="text-xs font-bold opacity-75">Toko</span></div>
                <div class="stat-desc text-xs font-semibold text-error/80 mt-1">
                    {{ number_format($tokoMerahPct, 1, ',', '.') }}% dari Total Toko
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-error w-full h-1.5" value="{{ min($tokoMerahPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Toolbar / Filters --}}
        <div class="p-4 border-b border-base-300 shrink-0 bg-base-200/30 flex flex-wrap items-end gap-3">
            <div class="form-control min-w-[250px] max-w-xs">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Cari Toko</span></label>
                <div class="relative">
                    <input type="text" wire:model="search" wire:keydown.enter="$refresh" placeholder="Cari nama atau kode toko (tekan Enter)..." class="input input-sm input-bordered w-full pl-8" />
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-2.5 text-base-content/50" />
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-3 ml-auto">
                <button type="button" class="btn btn-sm btn-outline btn-primary" onclick="filter_modal.showModal()">
                    <x-heroicon-o-funnel class="w-4 h-4 mr-1" />
                    Filter Wilayah & Kuartal
                </button>

                <div class="form-control min-w-[120px]">
                    <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Pengkoloman</span></label>
                    <select wire:model.live="statusProgress" class="select select-sm select-bordered">
                        <option value="Semua">Semua Pengkoloman</option>
                        <option value="1. HIJAU">1. HIJAU (>= 100%)</option>
                        <option value="2. KUNING">2. KUNING (80% - 99%)</option>
                        <option value="3. MERAH">3. MERAH (< 80%)</option>
                    </select>
                </div>

                <div class="form-control min-w-[110px]">
                    <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Status SKB</span></label>
                    <select wire:model.live="statusSkb" class="select select-sm select-bordered">
                        <option value="Semua">Semua</option>
                        <option value="Sudah">Sudah SKB</option>
                        <option value="Belum">Belum SKB</option>
                        <option value="Approve">Approve</option>
                        <option value="Reject">Reject</option>
                    </select>
                </div>

                <div class="form-control min-w-[110px]">
                    <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Status Data</span></label>
                    <select wire:model.live="statusData" class="select select-sm select-bordered">
                        <option value="Semua">Semua</option>
                        <option value="Lengkap">Lengkap</option>
                        <option value="Belum">Belum Lengkap</option>
                    </select>
                </div>

                <div class="form-control min-w-[110px]">
                    <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Reward</span></label>
                    <select wire:model.live="statusReward" class="select select-sm select-bordered">
                        <option value="Semua">Semua</option>
                        <option value="2.5%">2,5%</option>
                        <option value="2%">2%</option>
                        <option value="1.5%">1,5%</option>
                        <option value="1KTN">1KTN</option>
                    </select>
                </div>

                <div class="form-control min-w-[110px]">
                    <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Transaksi</span></label>
                    <select wire:model.live="statusTransaksi" class="select select-sm select-bordered">
                        <option value="Semua">Semua</option>
                        <option value="Sudah">Sudah</option>
                        <option value="Belum">Belum</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button wire:click="resetFilter" class="btn btn-sm btn-outline btn-neutral">Reset</button>
                    <button type="button" wire:click="export" wire:loading.attr="disabled" wire:target="export" class="btn btn-sm btn-success text-white">
                        <span wire:loading.remove wire:target="export" class="flex items-center">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1" />
                            Export Excel
                        </span>
                        <span wire:loading wire:target="export" class="flex items-center">
                            <span class="loading loading-spinner loading-xs mr-1"></span>
                            Exporting...
                        </span>
                    </button>
                    <button type="button" wire:click="loadMapData" wire:loading.attr="disabled" wire:target="loadMapData" class="btn btn-sm btn-info text-white">
                        <span wire:loading.remove wire:target="loadMapData" class="flex items-center">
                            <x-heroicon-o-map class="w-4 h-4 mr-1" />
                            Maps
                        </span>
                        <span wire:loading wire:target="loadMapData" class="flex items-center">
                            <span class="loading loading-spinner loading-xs mr-1"></span>
                            Memuat Peta...
                        </span>
                    </button>
                </div>

                <div class="shrink-0 self-center flex items-center h-[32px]">
                    <div wire:loading>
                        <span class="loading loading-spinner loading-sm text-primary"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Wilayah Filter Badges --}}
        @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor)
            <div class="px-4 py-2 bg-base-200/50 border-b border-base-300 flex flex-wrap items-center gap-2 text-xs shrink-0">
                <span class="font-semibold text-base-content/70">Filter Wilayah Aktif:</span>
                @if($appliedRegion)
                    <span class="badge badge-primary badge-outline gap-1 font-medium">
                        Region: {{ $appliedRegion }}
                        <button type="button" wire:click="clearWilayahFilter('region')" class="hover:text-error font-bold ml-1" title="Hapus filter Region">✕</button>
                    </span>
                @endif
                @if($appliedArea)
                    <span class="badge badge-primary badge-outline gap-1 font-medium">
                        Area: {{ $appliedArea }}
                        <button type="button" wire:click="clearWilayahFilter('area')" class="hover:text-error font-bold ml-1" title="Hapus filter Area">✕</button>
                    </span>
                @endif
                @if($appliedSupervisor)
                    <span class="badge badge-primary badge-outline gap-1 font-medium">
                        SPV: {{ $appliedSupervisor }}
                        <button type="button" wire:click="clearWilayahFilter('supervisor')" class="hover:text-error font-bold ml-1" title="Hapus filter Supervisor">✕</button>
                    </span>
                @endif
                @if($appliedDistributor)
                    <span class="badge badge-primary badge-outline gap-1 font-medium">
                        Distributor: {{ $appliedDistributor }}
                        <button type="button" wire:click="clearWilayahFilter('distributor')" class="hover:text-error font-bold ml-1" title="Hapus filter Distributor">✕</button>
                    </span>
                @endif
            </div>
        @endif

        {{-- Table --}}
        <div class="flex-1 overflow-auto bg-base-100 relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 shadow-sm">
                    <tr>
                        <th>Distributor</th>
                        <th>Kode Toko</th>
                        <th>Kode PRC</th>
                        <th>Nama Toko</th>
                        <th class="text-center">Status SKB</th>
                        <th class="text-center">Status Data</th>
                        <th class="text-center">Reward</th>
                        <th class="text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('total_target')">
                            Target Total
                            @if($sortField === 'total_target')
                                <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 inline-block ml-1" />
                            @else
                                <x-heroicon-s-chevron-up-down class="w-3 h-3 inline-block ml-1 opacity-30" />
                            @endif
                        </th>
                        <th class="text-right text-primary cursor-pointer hover:bg-base-200" wire:click="sortBy('prorated_target')">
                            Target Prorata
                            @if($sortField === 'prorated_target')
                                <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 inline-block ml-1" />
                            @else
                                <x-heroicon-s-chevron-up-down class="w-3 h-3 inline-block ml-1 opacity-30" />
                            @endif
                        </th>
                        <th class="text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('actual')">
                            Actual Total
                            @if($sortField === 'actual')
                                <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 inline-block ml-1" />
                            @else
                                <x-heroicon-s-chevron-up-down class="w-3 h-3 inline-block ml-1 opacity-30" />
                            @endif
                        </th>
                        <th class="text-center cursor-pointer hover:bg-base-200" wire:click="sortBy('percent')">
                            %
                            @if($sortField === 'percent')
                                <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 inline-block ml-1" />
                            @else
                                <x-heroicon-s-chevron-up-down class="w-3 h-3 inline-block ml-1 opacity-30" />
                            @endif
                        </th>
                        <th class="text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('gap')">
                            Gap 
                            @if($sortField === 'gap')
                                <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 inline-block ml-1" />
                            @else
                                <x-heroicon-s-chevron-up-down class="w-3 h-3 inline-block ml-1 opacity-30" />
                            @endif
                        </th>
                        <th class="text-right">{{ $monthLabels[0] ?? 'Month 1' }}</th>
                        <th class="text-right">{{ $monthLabels[1] ?? 'Month 2' }}</th>
                        <th class="text-right">{{ $monthLabels[2] ?? 'Month 3' }}</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        @php
                            $target = $row->total_target ?? 0;
                            $jenisReward = $row->jenis_reward ?? '';
                            
                            if ($jenisReward === 'BB') {
                                $rewardPercent = '1KTN';
                            } elseif ($jenisReward === 'CB') {
                                $rewardPercent = '1,5%';
                                if ($target >= 90000000) {
                                    $rewardPercent = '2,5%';
                                } elseif ($target >= 30000000) {
                                    $rewardPercent = '2%';
                                }
                            } else {
                                $rewardPercent = '-';
                            }

                            $proratedData = $this->getProratedData($target, $row, $this->appliedKuartal);
                            $percent = $proratedData['percent'];
                            $activeAchievement = $proratedData['active_achievement'];
                            $colorLabel = $proratedData['color_label'];
                            $proratedTarget = $proratedData['prorated_target'];
                            
                            $gap = $proratedTarget - $activeAchievement;
                            
                            $progressClass = 'progress-error';
                            $textClass = 'text-error font-bold';
                            $bgClass = 'bg-error/10';
                            if ($colorLabel === '1. HIJAU') {
                                $progressClass = 'progress-success';
                                $textClass = 'text-success font-bold';
                                $bgClass = 'bg-success/10';
                            } elseif ($colorLabel === '2. KUNING') {
                                $progressClass = 'progress-warning';
                                $textClass = 'text-warning font-bold';
                                $bgClass = 'bg-warning/10';
                            }
                        @endphp
                        <tr class="hover">
                            <td class="max-w-[180px] truncate" title="{{ $row->distributor_name }}">{{ $row->distributor_name ?? '-' }}</td>
                            <td class="font-mono font-bold text-primary max-w-[120px] truncate" title="{{ $row->customer_code }}">{{ $row->customer_code }}</td>
                            <td class="font-mono text-xs opacity-75 max-w-[120px] truncate" title="{{ $row->customer_prc }}">{{ $row->customer_prc ?? '-' }}</td>
                            <td class="font-semibold text-base-content/95 {{ $bgClass }}">{{ $row->customer_name }}</td>
                            <td class="text-center">
                                @if(($row->jenis_reward ?? '') === 'CB')
                                    @if($row->status_skb === 'Sudah')
                                        @if($row->is_approved === 1 || $row->is_approved === true)
                                            <span class="badge badge-success badge-sm text-xs font-bold text-white py-1">Approved</span>
                                        @elseif($row->is_approved === 0 || $row->is_approved === false)
                                            <span class="badge badge-error badge-sm text-xs font-bold text-white py-1">Rejected</span>
                                        @else
                                            <span class="badge badge-info badge-sm text-xs font-bold text-white py-1">Submitted</span>
                                        @endif
                                    @else
                                        <span class="badge badge-ghost badge-sm text-xs font-bold py-1 opacity-60">Belum</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($row->jenis_reward ?? '') !== 'BB')
                                    @if($row->status_data_lengkap === 'Lengkap')
                                        <span class="badge badge-success badge-sm text-xs font-bold text-white py-1">Lengkap</span>
                                    @else
                                        <span class="badge badge-warning badge-sm text-xs font-bold text-white py-1">Belum</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center font-bold text-info">{{ $rewardPercent }}</td>
                            <td class="text-right font-mono font-semibold">{{ number_format($target, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs text-base-content/75 font-semibold">{{ number_format($proratedTarget, 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold text-success/90">{{ number_format($activeAchievement, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-0.5 min-w-[70px]">
                                    <span class="{{ $textClass }} text-xs font-bold">{{ number_format($percent, 1, ',', '.') }}%</span>
                                    <progress class="progress {{ $progressClass }} w-14 h-1" value="{{ min($percent, 100) }}" max="100"></progress>
                                </div>
                            </td>
                            <td class="text-right font-mono font-semibold {{ $gap > 0 ? 'text-error' : 'text-success' }}">
                                {{ $gap > 0 ? '-' : '+' }}{{ number_format(abs($gap), 0, ',', '.') }}
                            </td>
                            <td class="text-right font-mono text-xs text-base-content/75">Rp {{ number_format($row->month_1_value ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs text-base-content/75">Rp {{ number_format($row->month_2_value ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs text-base-content/75">Rp {{ number_format($row->month_3_value ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <button wire:click="showStoreDetail('{{ $row->customer_code }}', '{{ $row->distributor_code }}')" class="btn btn-xs btn-primary font-bold">
                                    <x-heroicon-s-information-circle class="w-3.5 h-3.5 mr-0.5" /> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center py-8">
                                <div class="flex flex-col items-center gap-2 opacity-60">
                                    <x-heroicon-o-building-storefront class="w-12 h-12" />
                                    <span class="font-semibold text-sm">Tidak ada data pencapaian RWO</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($records->hasPages())
            <div class="p-4 border-t border-base-300 shrink-0 bg-base-200/20">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    {{-- DETAIL MODAL --}}
    @if($isDetailModalOpen && $this->selectedStore)
        @php $selectedStore = $this->selectedStore; @endphp
        <div class="modal modal-open" wire:key="detail-modal-{{ $selectedStore->customer_code ?? '1' }}">
            <div class="modal-box w-11/12 max-w-6xl bg-base-100 rounded-2xl relative p-6 flex flex-col max-h-[90vh]">
                <button type="button" wire:click="closeDetailModal" class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4">✕</button>
                
                @php
                    $modalTarget = $selectedStore->total_target ?? 0;
                    $modalJenisReward = $selectedStore->jenis_reward ?? '';
                    $modalProratedData = $this->getProratedData($modalTarget, $selectedStore, $this->appliedKuartal);
                    if ($modalJenisReward === 'BB') {
                        $modalRewardPercent = '1KTN';
                    } elseif ($modalJenisReward === 'CB') {
                        $modalRewardPercent = '1,5%';
                        if ($modalTarget >= 90000000) {
                            $modalRewardPercent = '2,5%';
                        } elseif ($modalTarget >= 30000000) {
                            $modalRewardPercent = '2%';
                        }
                    } else {
                        $modalRewardPercent = '-';
                    }
                @endphp
                <div class="border-b pb-3 mb-4">
                    <h3 class="font-extrabold text-xl text-base-content">
                        Detail Toko: <span class="text-primary">{{ $selectedStore->customer_name }}</span>
                    </h3>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @if($modalJenisReward === 'CB')
                            @if($selectedStore->status_skb === 'Sudah')
                                @if($selectedStore->is_approved === 1 || $selectedStore->is_approved === true)
                                    <span class="badge badge-success badge-sm font-bold text-white shadow-sm">SKB: Approved</span>
                                @elseif($selectedStore->is_approved === 0 || $selectedStore->is_approved === false)
                                    <span class="badge badge-error badge-sm font-bold text-white shadow-sm">SKB: Rejected</span>
                                @else
                                    <span class="badge badge-info badge-sm font-bold text-white shadow-sm">SKB: Submitted</span>
                                @endif
                            @else
                                <span class="badge badge-ghost badge-sm font-bold shadow-sm">SKB: Belum</span>
                            @endif
                        @endif

                        @if($modalJenisReward !== 'BB')
                            @if($selectedStore->status_data_lengkap === 'Lengkap')
                                <span class="badge badge-success badge-sm font-bold text-white shadow-sm">Data: Lengkap</span>
                            @else
                                <span class="badge badge-warning badge-sm font-bold text-white shadow-sm">Data: Belum</span>
                            @endif
                        @endif

                        <span class="badge badge-primary badge-sm font-bold text-white shadow-sm">Reward: {{ $modalRewardPercent }}</span>

                        @php
                            $kpiColorClass = 'badge-error';
                            if ($modalProratedData['color_label'] === '1. HIJAU') {
                                $kpiColorClass = 'badge-success';
                            } elseif ($modalProratedData['color_label'] === '2. KUNING') {
                                $kpiColorClass = 'badge-warning';
                            }
                        @endphp
                        <span class="badge {{ $kpiColorClass }} badge-sm font-bold text-white shadow-sm">Kolom: {{ $modalProratedData['color_label'] }}</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto pr-2" x-data="{ tab: 'info' }">
                    {{-- Modal Tabs --}}
                    <div class="tabs tabs-boxed mb-4 bg-base-200 p-1 w-fit">
                        <button type="button" @click="tab = 'info'" class="tab tab-sm" :class="tab === 'info' ? 'tab-active font-bold bg-base-100 shadow-sm' : ''">Informasi & Kelengkapan</button>
                        <button type="button" @click="tab = 'so'" class="tab tab-sm" :class="tab === 'so' ? 'tab-active font-bold bg-base-100 shadow-sm' : ''">Kinerja Sales & Transaksi</button>
                        <button type="button" @click="tab = 'qty'" class="tab tab-sm" :class="tab === 'qty' ? 'tab-active font-bold bg-base-100 shadow-sm' : ''">History Penjualan by Qty</button>
                        <button type="button" @click="tab = 'visit'" class="tab tab-sm" :class="tab === 'visit' ? 'tab-active font-bold bg-base-100 shadow-sm' : ''">Analisa Kunjungan</button>
                        <button type="button" @click="tab = 'fulfillment'" class="tab tab-sm" :class="tab === 'fulfillment' ? 'tab-active font-bold bg-base-100 shadow-sm' : ''">Komparasi Order vs Realisasi</button>
                    </div>

                    {{-- TAB 1: Informasi --}}
                    <div x-show="tab === 'info'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="flex flex-col gap-4">
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Profil Toko</h4>
                                <div class="grid grid-cols-3 gap-y-2 text-xs">
                                    <div class="font-semibold text-base-content/60">Kode Toko:</div>
                                    <div class="col-span-2 font-bold">{{ $selectedStore->customer_code }}</div>

                                    <div class="font-semibold text-base-content/60">Alamat:</div>
                                    <div class="col-span-2">{{ $selectedStore->address ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">No HP:</div>
                                    <div class="col-span-2 font-mono font-semibold">{{ $selectedStore->no_hp ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Pemilik Toko:</div>
                                    <div class="col-span-2">{{ $selectedStore->nama_pemilik_toko ?? '-' }}</div>

                                    {{--
                                    <div class="font-semibold text-base-content/60">NIK KTP:</div>
                                    <div class="col-span-2 font-mono">{{ $selectedStore->nik_ktp ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Nama KTP:</div>
                                    <div class="col-span-2">{{ $selectedStore->nama_ktp ?? '-' }}</div>
                                    --}}
                                </div>
                            </div>

                            {{--
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Rekening Bank</h4>
                                <div class="grid grid-cols-3 gap-y-2 text-xs">
                                    <div class="font-semibold text-base-content/60">Nama Bank:</div>
                                    <div class="col-span-2 font-bold">{{ $selectedStore->nama_bank ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">No Rekening:</div>
                                    <div class="col-span-2 font-mono font-bold text-info">{{ $selectedStore->no_rekening ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Pemilik Rekening:</div>
                                    <div class="col-span-2">{{ $selectedStore->nama_pemilik_norek ?? '-' }}</div>
                                </div>
                            </div>
                            --}}
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Pencapaian Toko</h4>
                                <div class="grid grid-cols-2 gap-y-3 text-xs">
                                    <div class="font-semibold text-base-content/60">Target Total:</div>
                                    <div class="font-mono font-semibold">Rp {{ number_format($modalTarget, 0, ',', '.') }}</div>

                                    <div class="font-semibold text-base-content/60">Target Prorata:</div>
                                    <div class="font-mono font-semibold text-primary">Rp {{ number_format($modalProratedData['prorated_target'], 0, ',', '.') }}</div>

                                    <div class="font-semibold text-base-content/60">Actual Total:</div>
                                    <div class="font-mono font-bold text-success/90">Rp {{ number_format($modalProratedData['active_achievement'], 0, ',', '.') }}</div>

                                    <div class="font-semibold text-base-content/60">Persentase:</div>
                                    <div class="font-bold">{{ number_format($modalProratedData['percent'], 1, ',', '.') }}% ({{ $modalProratedData['color_label'] }})</div>

                                    <div class="col-span-2 border-t mt-1 pt-3">
                                        <div class="font-bold text-xs mb-2 text-base-content/80">Detail Pencapaian Per Bulan:</div>
                                        <div class="grid grid-cols-3 gap-2 text-xs">
                                            <div class="bg-base-100 p-2 rounded border border-base-300 text-center">
                                                <div class="uppercase text-base-content/60 font-bold mb-1">{{ $monthLabels[0] ?? 'Bln 1' }}</div>
                                                <div class="font-mono font-bold text-success/90">Rp {{ number_format($selectedStore->month_1_value ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="bg-base-100 p-2 rounded border border-base-300 text-center">
                                                <div class="uppercase text-base-content/60 font-bold mb-1">{{ $monthLabels[1] ?? 'Bln 2' }}</div>
                                                <div class="font-mono font-bold text-success/90">Rp {{ number_format($selectedStore->month_2_value ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="bg-base-100 p-2 rounded border border-base-300 text-center">
                                                <div class="uppercase text-base-content/60 font-bold mb-1">{{ $monthLabels[2] ?? 'Bln 3' }}</div>
                                                <div class="font-mono font-bold text-success/90">Rp {{ number_format($selectedStore->month_3_value ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Dokumen & Koordinat GPS</h4>
                                <div class="grid grid-cols-3 gap-y-3 text-xs items-center">
                                    <div class="font-semibold text-base-content/60">Latitude:</div>
                                    <div class="col-span-2 font-mono">{{ $selectedStore->latitude ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Longitude:</div>
                                    <div class="col-span-2 font-mono">{{ $selectedStore->longitude ?? '-' }}</div>

                                    {{--
                                    <div class="font-semibold text-base-content/60">Foto KTP:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->foto_ktp)
                                            <a href="/storage/{{ $selectedStore->foto_ktp }}" target="_blank" class="btn btn-xs btn-info font-bold text-white">Lihat KTP</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diunggah</span>
                                        @endif
                                    </div>
                                    --}}

                                    <div class="font-semibold text-base-content/60">Foto Toko 2:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->foto_toko2)
                                            <a href="/storage/{{ $selectedStore->foto_toko2 }}" target="_blank" class="btn btn-xs btn-info font-bold text-white">Lihat Foto 2</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diunggah</span>
                                        @endif
                                    </div>

                                    <div class="font-semibold text-base-content/60">Foto Toko 3:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->foto_toko3)
                                            <a href="/storage/{{ $selectedStore->foto_toko3 }}" target="_blank" class="btn btn-xs btn-info font-bold text-white">Lihat Foto 3</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diunggah</span>
                                        @endif
                                    </div>

                                    <div class="font-semibold text-base-content/60">Dokumen SKB:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->skb_foto)
                                            <a href="/storage/{{ $selectedStore->skb_foto }}" target="_blank" class="btn btn-xs btn-success font-bold text-white">Unduh SKB</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diajukan</span>
                                        @endif
                                    </div>

                                    <div class="font-semibold text-base-content/60">Alasan/Reason SKB:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->skb_reason)
                                            <div class="bg-error/10 text-error font-semibold p-2.5 rounded-md italic text-sm">{{ $selectedStore->skb_reason }}</div>
                                        @else
                                            <span class="whitespace-normal italic">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300 flex-1 flex flex-col">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Remark / Catatan Khusus Toko</h4>
                                
                                @if(session()->has('success'))
                                    <div class="alert alert-success shadow-sm mb-3 text-xs py-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-4 w-4" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>{{ session('success') }}</span>
                                    </div>
                                @endif
                                
                                <div class="form-control w-full flex-1 flex flex-col">
                                    <textarea wire:model="remarkKhusus" class="textarea textarea-bordered flex-1 w-full resize-none text-sm" placeholder="Tulis remark khusus untuk toko ini jika ada (misal: case khusus)..."></textarea>
                                    <div class="flex justify-end mt-3">
                                        <button type="button" wire:click="saveRemarkKhusus" class="btn btn-primary btn-sm">
                                            <span wire:loading wire:target="saveRemarkKhusus" class="loading loading-spinner loading-xs"></span>
                                            Simpan Remark
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Chat Column (Bagian Kanan) --}}
                        <div class="flex flex-col gap-4">
                            <div class="card bg-base-200/50 p-0 rounded-xl border border-base-300 flex flex-col h-full overflow-hidden">
                                <div class="bg-base-300/50 p-3 border-b border-base-300 flex items-center justify-between shrink-0">
                                    <h4 class="font-bold text-sm text-primary uppercase tracking-wide">Diskusi Tim</h4>
                                </div>
                                
                                <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-2" id="chat-container">
                                    @forelse($chatHistories as $chat)
                                        @php
                                            $isMe = $chat->user_id === auth()->id();
                                        @endphp
                                        <div class="chat {{ $isMe ? 'chat-end' : 'chat-start' }}">
                                            <div class="chat-header text-xs opacity-70 mb-1">
                                                {{ $isMe ? 'Anda' : ($chat->user->name ?? 'User') }}
                                            </div>
                                            <div class="chat-bubble {{ $isMe ? 'chat-bubble-primary' : '' }} text-sm shadow-sm whitespace-pre-wrap">{{ $chat->message }}</div>
                                            <div class="chat-footer opacity-50 text-xs mt-1">
                                                {{ $chat->created_at->format('H:i, d M') }}
                                            </div>
                                        </div>
                                    @empty
                                        <div class="flex flex-col items-center justify-center h-full opacity-50">
                                            <x-heroicon-o-chat-bubble-left-right class="w-8 h-8 mb-2" />
                                            <span class="text-xs">Belum ada diskusi</span>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="p-3 bg-base-200 border-t border-base-300 shrink-0">
                                    <div class="flex gap-2">
                                        <textarea wire:model="newChatMessage" wire:keydown.enter="sendChat" class="textarea textarea-bordered textarea-sm w-full h-10 min-h-10 resize-none" placeholder="Ketik pesan (Enter untuk kirim)..."></textarea>
                                        <button type="button" wire:click="sendChat" class="btn btn-primary btn-sm h-10" title="Kirim">
                                            <x-heroicon-s-paper-airplane class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: Transaksi --}}
                    <div x-show="tab === 'so'" class="flex flex-col gap-4">
                        {{-- 1. KPI CARDS --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="card bg-success/10 border border-success/20 p-4 rounded-xl">
                                <span class="text-xs font-bold uppercase tracking-wider text-success">Omset Bulanan Tertinggi (2026)</span>
                                <span class="text-xl font-bold font-mono mt-1">Rp {{ number_format($selectedStore->max_transaction ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="card bg-info/10 border border-info/20 p-4 rounded-xl">
                                <span class="text-xs font-bold uppercase tracking-wider text-info">Rata-rata per Bulan (2026)</span>
                                <span class="text-xl font-bold font-mono mt-1">Rp {{ number_format($selectedStore->avg_transaction ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="card bg-warning/10 border border-warning/20 p-4 rounded-xl">
                                <span class="text-xs font-bold uppercase tracking-wider text-warning">Bulan Aktif Terakhir</span>
                                <div class="flex flex-col mt-1">
                                    @if($selectedStore->last_transaction_date)
                                        <span class="text-sm font-bold font-mono">Rp {{ number_format($selectedStore->last_transaction_value ?? 0, 0, ',', '.') }}</span>
                                        <span class="text-xs opacity-80 mt-0.5">{{ \Carbon\Carbon::parse($selectedStore->last_transaction_date)->translatedFormat('F Y') }}</span>
                                    @else
                                        <span class="text-sm font-bold font-mono text-base-content/50">Rp 0</span>
                                        <span class="text-xs opacity-60 mt-0.5">Tidak ada transaksi aktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- 2. TREN TAHUN BERJALAN & RINCIAN BULANAN (DATA HISTORIS ANGKA) --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- CHART -->
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Tren Tahun Berjalan</h4>
                                <div class="flex items-end justify-between h-40 gap-1 mt-4">
                                    @foreach($yearlyHistory as $data)
                                        <div class="flex flex-col items-center justify-end w-full group relative h-full">
                                            @if($data['value'] > 0)
                                                @php $heightPercent = ($data['value'] / ($maxYearlyValue ?: 1)) * 100; @endphp
                                                <div class="w-full bg-primary/70 hover:bg-primary transition-all duration-300 rounded-t-md" style="height: {{ $heightPercent }}%;"></div>
                                                
                                                <!-- Tooltip -->
                                                <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-neutral text-neutral-content text-xs px-2 py-1 rounded whitespace-nowrap z-10 transition-opacity pointer-events-none">
                                                    Rp {{ number_format($data['value'], 0, ',', '.') }}
                                                </div>
                                            @else
                                                <div class="w-full border-b-2 border-dashed border-base-content/30 mb-1" style="height: 0%;"></div>
                                                <!-- Tooltip -->
                                                <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-neutral text-neutral-content text-xs px-2 py-1 rounded whitespace-nowrap z-10 transition-opacity pointer-events-none">
                                                    Rp 0
                                                </div>
                                            @endif
                                            <span class="text-xs mt-2 font-bold uppercase text-base-content/70 tracking-tighter">{{ $data['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- LEDGER -->
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300 flex flex-col max-h-72">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide shrink-0">Rincian Bulanan</h4>
                                <div class="overflow-y-auto flex-1 pr-2">
                                    <table class="table table-xs table-pin-rows table-zebra w-full">
                                        <thead>
                                            <tr>
                                                <th>Periode</th>
                                                <th class="text-right">Total Omset</th>
                                                <th class="text-center">Tren</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(array_reverse($yearlyHistory) as $data)
                                            <tr>
                                                <td class="font-medium whitespace-nowrap">{{ $data['full_label'] }}</td>
                                                <td class="text-right font-mono text-xs whitespace-nowrap">Rp {{ number_format($data['value'], 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if($data['trend'] === 'up')
                                                        <span class="text-success text-xs" title="Naik dari bulan sebelumnya">▲</span>
                                                    @elseif($data['trend'] === 'down')
                                                        <span class="text-error text-xs" title="Turun dari bulan sebelumnya">▼</span>
                                                    @else
                                                        <span class="text-base-content/50 text-xs" title="Stabil">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- 3. WIDGET 1: ANALISIS MOMENTUM BELANJA (KATEGORI RANGE) --}}
                        @php
                            $momentum = $this->salesMomentumAnalysis;
                        @endphp
                        @if($momentum)
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300 shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-base-300 pb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                                            <x-heroicon-o-chart-bar class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80">Analisis Momentum Belanja Toko (2026)</h4>
                                            <span class="text-xs text-base-content/60">
                                                Evaluasi: <strong class="text-base-content">{{ $momentum['evalPeriod'] }}</strong>
                                                ({{ $momentum['isCurrentMonth'] ? 'Bulan Berjalan' : 'Bulan Terakhir Aktif' }}) vs Rata-rata 2026
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Range Badges Selector Display --}}
                                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                        <span class="px-2 py-0.5 rounded font-bold border transition-all {{ $momentum['status'] === 'UNDER' ? 'bg-error text-white border-error shadow-sm scale-105' : 'bg-base-100 text-base-content/50 border-base-300 opacity-60' }}">
                                            &lt; 100% Under
                                        </span>
                                        <span class="px-2 py-0.5 rounded font-bold border transition-all {{ $momentum['status'] === 'NORMAL' ? 'bg-success text-white border-success shadow-sm scale-105' : 'bg-base-100 text-base-content/50 border-base-300 opacity-60' }}">
                                            100% - 120% Normal
                                        </span>
                                        <span class="px-2 py-0.5 rounded font-bold border transition-all {{ $momentum['status'] === 'HIGH' ? 'bg-info text-white border-info shadow-sm scale-105' : 'bg-base-100 text-base-content/50 border-base-300 opacity-60' }}">
                                            120% - 170% High
                                        </span>
                                        <span class="px-2 py-0.5 rounded font-bold border transition-all {{ $momentum['status'] === 'OVER' ? 'bg-warning text-white border-warning shadow-sm scale-105' : 'bg-base-100 text-base-content/50 border-base-300 opacity-60' }}">
                                            &gt; 170% Over
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 items-center">
                                    {{-- Kolom Metrik Angka --}}
                                    <div class="space-y-1.5 text-xs">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-base-content/70">Transaksi Evaluasi:</span>
                                            <span class="font-mono font-bold">Rp {{ number_format($momentum['evalValue'], 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-base-content/70">Rata-rata 2026 (Baseline):</span>
                                            <span class="font-mono font-semibold text-primary">Rp {{ number_format($momentum['avgValue'], 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center pt-1 border-t border-base-300 text-xs">
                                            <span class="font-semibold text-base-content/80">Rasio Performa:</span>
                                            <span class="font-mono font-extrabold text-sm {{ $momentum['status'] === 'UNDER' ? 'text-error' : ($momentum['status'] === 'NORMAL' ? 'text-success' : ($momentum['status'] === 'HIGH' ? 'text-info' : 'text-warning')) }}">
                                                {{ number_format($momentum['ratio'], 1, ',', '.') }}%
                                                <span class="text-xs font-bold uppercase">({{ $momentum['status'] }})</span>
                                            </span>
                                        </div>
                                        <div class="w-full pt-0.5">
                                            <progress class="progress {{ $momentum['status'] === 'UNDER' ? 'progress-error' : ($momentum['status'] === 'NORMAL' ? 'progress-success' : ($momentum['status'] === 'HIGH' ? 'progress-info' : 'progress-warning')) }} w-full h-1.5" value="{{ min($momentum['ratio'], 200) }}" max="200"></progress>
                                        </div>
                                    </div>

                                    {{-- Kolom Diagnosa & Saran Aksi --}}
                                    <div class="col-span-1 md:col-span-2 card bg-base-100 p-3 rounded-lg border border-base-300 text-xs space-y-1.5">
                                        <div class="flex items-start gap-2 text-xs leading-tight">
                                            <strong class="text-base-content/70 shrink-0">Diagnosa:</strong>
                                            <span class="text-base-content/90 font-medium">{{ $momentum['diagnosis'] }}</span>
                                        </div>
                                        <div class="flex items-start gap-2 pt-1 border-t border-base-200 text-xs leading-tight">
                                            <strong class="text-primary shrink-0">Rekomendasi Aksi:</strong>
                                            <span class="text-base-content/80">{{ $momentum['action'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- 4. DUAL INTELLIGENCE & DECISION WIDGET --}}
                        @php
                            $stock = $this->stockVerification;
                            $target = $this->targetFeasibility;
                        @endphp

                        @if($stock || $target)
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                {{-- CARD 1: ANALISIS KETAHANAN STOK & KONSISTENSI --}}
                                @if($stock)
                                    <div class="card bg-base-200/50 p-4 rounded-xl border {{ $stock['borderClass'] }} shadow-sm flex flex-col justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-base-300 pb-2.5">
                                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                                    <div class="p-1.5 rounded-lg bg-primary/10 text-primary shrink-0">
                                                        <x-heroicon-o-archive-box class="w-4 h-4" />
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 truncate">Analisis Ketahanan & Penyerapan Stok</h4>
                                                        <span class="text-xs text-base-content/60 block truncate">
                                                            Estimasi ketersediaan stok berdasarkan daya serap normal (Burn-Rate)
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="badge {{ $stock['badgeClass'] }} text-xs font-semibold shrink-0 whitespace-nowrap shadow-sm h-auto py-1 px-2.5">
                                                    {{ $stock['label'] }}
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2 my-3 text-xs">
                                                <div class="bg-base-100 p-2 rounded-lg border border-base-300">
                                                    <span class="text-xs text-base-content/60 font-semibold block uppercase">Pembelian Terakhir</span>
                                                    <span class="font-mono font-bold text-sm">Rp {{ number_format($stock['evalValue'], 0, ',', '.') }}</span>
                                                    <span class="text-xs opacity-75 block mt-0.5">{{ $stock['evalPeriod'] }}</span>
                                                </div>
                                                <div class="bg-base-100 p-2 rounded-lg border border-base-300">
                                                    <span class="text-xs text-base-content/60 font-semibold block uppercase">Rata-rata 2026 (Burn-Rate)</span>
                                                    <span class="font-mono font-bold text-sm text-primary">Rp {{ number_format($stock['avgValue'], 0, ',', '.') }}</span>
                                                    <span class="text-xs opacity-75 block mt-0.5">Daya serap / bulan</span>
                                                </div>
                                            </div>

                                            {{-- Matriks Estimasi Ketahanan Stok --}}
                                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs space-y-1 mb-3">
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-base-content/70">Waktu Sejak Order Terakhir:</span>
                                                    <span class="font-semibold {{ $stock['monthsElapsed'] > 1 ? 'text-error font-bold' : '' }}">
                                                        {{ $stock['monthsElapsed'] > 0 ? $stock['monthsElapsed'] . ' Bulan' : 'Bulan Berjalan (0 Bulan)' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="text-base-content/70">Ketahanan Stok Pembelian:</span>
                                                    <span class="font-semibold">{{ $stock['monthsCoverage'] }} Bulan Konsumsi Normal</span>
                                                </div>
                                                <div class="flex justify-between items-center text-xs pt-1 border-t border-base-200">
                                                    <span class="text-base-content/70 font-semibold">Estimasi Cadangan Stok Toko:</span>
                                                    <span class="font-mono font-bold {{ $stock['theoreticalBalance'] > 0 ? 'text-success' : 'text-error' }}">
                                                        {{ $stock['theoreticalBalance'] > 0 ? '~Rp ' . number_format($stock['theoreticalBalance'], 0, ',', '.') : 'Terserap Habis (Rp 0)' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2 pt-2 border-t border-base-300">
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-base-content/70">Konsistensi Belanja:</span>
                                                    <span class="font-bold text-primary">{{ $stock['consistencyRate'] }}%</span>
                                                    <span class="text-xs text-base-content/50">({{ $stock['activeMonths'] }}/{{ $stock['totalMonths'] }} bln)</span>
                                                </div>
                                                <span class="badge {{ $stock['dormancyBadge'] }} badge-xs py-1 text-xs font-bold shrink-0">
                                                    {{ $stock['dormancyText'] }}
                                                </span>
                                            </div>

                                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs space-y-1">
                                                <div class="text-xs leading-tight">
                                                    <strong class="text-base-content/80">Analisa Kondisi:</strong>
                                                    <span class="text-base-content/90">{{ $stock['diagnosis'] }}</span>
                                                </div>
                                                <div class="text-xs leading-tight pt-1 border-t border-base-200">
                                                    <strong class="text-primary">Rekomendasi Tim Sales:</strong>
                                                    <span class="text-base-content/80">{{ $stock['action'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- CARD 2: KELAYAKAN TARGET & PANDUAN ORDER PO --}}
                                @if($target)
                                    <div class="card bg-base-200/50 p-4 rounded-xl border {{ $target['borderClass'] }} shadow-sm flex flex-col justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-base-300 pb-2.5">
                                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                                    <div class="p-1.5 rounded-lg bg-success/10 text-success shrink-0">
                                                        <x-heroicon-o-chart-bar class="w-4 h-4" />
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 truncate">Kelayakan Target & Panduan Order</h4>
                                                        <span class="text-xs text-base-content/60 block truncate">
                                                            Target Kuartal: <strong class="text-base-content">{{ $target['rewardLabel'] }}</strong>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="badge {{ $target['badgeClass'] }} text-xs font-semibold shrink-0 whitespace-nowrap shadow-sm h-auto py-1 px-2.5">
                                                    {{ $target['label'] }}
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2 my-3 text-xs">
                                                <div class="bg-base-100 p-2 rounded-lg border border-base-300">
                                                    <span class="text-xs text-base-content/60 font-semibold block uppercase">Target Total Kuartal</span>
                                                    <span class="font-mono font-bold text-sm">Rp {{ number_format($target['totalTarget'], 0, ',', '.') }}</span>
                                                    <span class="text-xs opacity-75 block mt-0.5">Realisasi: Rp {{ number_format($target['activeAchievement'], 0, ',', '.') }}</span>
                                                </div>
                                                <div class="bg-base-100 p-2 rounded-lg border border-base-300">
                                                    <span class="text-xs text-base-content/60 font-semibold block uppercase">Kekurangan (Gap Target)</span>
                                                    <span class="font-mono font-bold text-sm {{ $target['gap'] > 0 ? 'text-error' : 'text-success' }}">
                                                        Rp {{ number_format($target['gap'], 0, ',', '.') }}
                                                    </span>
                                                    <span class="text-xs opacity-75 block mt-0.5">Pencapaian: {{ number_format($target['percent'], 1, ',', '.') }}%</span>
                                                </div>
                                            </div>

                                            {{-- Box Rekomendasi PO Minimal --}}
                                            <div class="bg-base-100 p-3 rounded-lg border border-base-300 text-xs mb-3">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="text-xs uppercase font-bold text-base-content/60 block">Rekomendasi PO Kunjungan</span>
                                                        <span class="text-xs text-base-content/75">
                                                            {{ $target['remainingMonths'] > 0 ? 'Sisa Waktu: ' . $target['remainingMonths'] . ' Bulan' : 'Kuartal Ditutup' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="font-mono font-extrabold text-base text-primary block">
                                                            Rp {{ number_format($target['recommendedPO'], 0, ',', '.') }}
                                                        </span>
                                                        <span class="text-xs font-semibold text-base-content/60">
                                                            per bulan di sisa kuartal
                                                        </span>
                                                    </div>
                                                </div>
                                                @if($target['gap'] > 0)
                                                    <div class="w-full mt-2">
                                                        <progress class="progress {{ $target['status'] === 'ON_TRACK' ? 'progress-success' : ($target['status'] === 'NEED_PUSH' ? 'progress-warning' : 'progress-error') }} w-full h-1.5" value="{{ min($target['percent'], 100) }}" max="100"></progress>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-base-300">
                                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs">
                                                <div class="flex items-start gap-1.5 text-xs leading-tight">
                                                    <strong class="text-primary shrink-0">💡 Panduan Sales Pitch:</strong>
                                                    <span class="text-base-content/85">{{ $target['pitch'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- TAB 3: History Penjualan by Qty --}}
                    @php
                        $qtyData = $this->qtyHistory;
                    @endphp
                    <div x-show="tab === 'qty'" x-data="{ openSubbrands: {} }" class="flex flex-col gap-4">
                        @if(!$qtyData || empty($qtyData['subbrands']))
                            <div class="card bg-base-200/50 p-8 rounded-xl border border-base-300 text-center flex flex-col items-center justify-center">
                                <x-heroicon-o-archive-box class="w-12 h-12 text-base-content/30 mb-2" />
                                <h4 class="font-bold text-sm text-base-content/70">Belum Ada Histori Kuantitas Penjualan (6 Bulan Terakhir)</h4>
                                <p class="text-xs text-base-content/50 mt-1 max-w-md">Tidak ditemukan data faktur pada t_sellingout untuk toko ini dalam rentang 6 bulan terakhir.</p>
                            </div>
                        @else
                            {{-- 1. 4 KPI MINI CARDS --}}
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="card bg-primary/10 border border-primary/20 p-3 rounded-xl">
                                    <span class="text-xs font-bold uppercase tracking-wider text-primary">Total 6 Bulan (KTN)</span>
                                    <span class="text-lg font-bold font-mono mt-0.5">{{ number_format($qtyData['kpi']['total_qty'], 1, ',', '.') }} <span class="text-xs font-normal opacity-70">KTN</span></span>
                                </div>
                                <div class="card bg-info/10 border border-info/20 p-3 rounded-xl">
                                    <span class="text-xs font-bold uppercase tracking-wider text-info">Rata-rata per Bulan</span>
                                    <span class="text-lg font-bold font-mono mt-0.5">{{ number_format($qtyData['kpi']['avg_qty'], 1, ',', '.') }} <span class="text-xs font-normal opacity-70">KTN/bln</span></span>
                                </div>
                                <div class="card bg-success/10 border border-success/20 p-3 rounded-xl">
                                    <span class="text-xs font-bold uppercase tracking-wider text-success">Lini Subbrand Aktif</span>
                                    <span class="text-lg font-bold font-mono mt-0.5">{{ $qtyData['kpi']['active_subbrands'] }} <span class="text-xs font-normal opacity-70">Subbrand</span></span>
                                </div>
                                <div class="card bg-warning/10 border border-warning/20 p-3 rounded-xl">
                                    <span class="text-xs font-bold uppercase tracking-wider text-warning">Subbrand Terlaris (Hero)</span>
                                    <div class="flex items-baseline gap-1 mt-0.5 truncate">
                                        <span class="text-xs font-bold truncate" title="{{ $qtyData['kpi']['hero_subbrand'] }}">{{ $qtyData['kpi']['hero_subbrand'] }}</span>
                                        <span class="text-xs font-mono font-bold text-warning shrink-0">({{ $qtyData['kpi']['hero_percent'] }}%)</span>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. EXPANDABLE PIVOT TABLE --}}
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300 shadow-sm flex flex-col">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                                            <x-heroicon-o-table-cells class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs uppercase tracking-wide text-base-content/90">Histori Penjualan 6 Bulan Terakhir (TTL_QTY_KTN)</h4>
                                            <span class="text-xs text-base-content/60">Klik baris subbrand untuk melihat rincian per varian rasa / SKU</span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-base-content/60 flex items-center gap-2">
                                        <button type="button" @click="openSubbrands = Object.keys(openSubbrands).length > 0 ? {} : { @foreach($qtyData['subbrands'] as $sb) '{{ $sb['key'] }}': true, @endforeach }" class="btn btn-ghost btn-xs text-xs font-bold">
                                            Toggle Buka/Tutup Semua
                                        </button>
                                    </div>
                                </div>

                                <div class="overflow-x-auto max-h-[380px] border border-base-300 rounded-lg">
                                    <table class="table table-xs table-pin-rows table-zebra w-full text-xs">
                                        <thead class="sticky top-0 z-20 bg-base-300 text-base-content font-bold shadow-sm">
                                            <tr class="bg-base-300 text-base-content font-bold">
                                                <th class="min-w-[220px]">Subbrand / Varian Item</th>
                                                @foreach($qtyData['months'] as $m)
                                                    <th class="text-right whitespace-nowrap px-2">{{ $m['label'] }}</th>
                                                @endforeach
                                                <th class="text-right whitespace-nowrap bg-base-300 px-3">Total (KTN)</th>
                                                <th class="text-right whitespace-nowrap px-3">Avg/Bln</th>
                                                <th class="text-center w-12">Tren</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($qtyData['subbrands'] as $sbKey => $sb)
                                                {{-- Parent Row (Subbrand) --}}
                                                <tr @click="openSubbrands['{{ $sb['key'] }}'] = !openSubbrands['{{ $sb['key'] }}']" 
                                                    class="cursor-pointer font-bold hover:bg-base-200 transition-colors select-none group border-t border-base-300"
                                                    wire:key="sb-row-{{ $sb['key'] }}">
                                                    <td class="font-semibold whitespace-nowrap flex items-center gap-1.5 py-2">
                                                        <span class="text-primary transition-transform duration-200 inline-block text-xs" :class="openSubbrands['{{ $sb['key'] }}'] ? 'rotate-90' : ''">▶</span>
                                                        <span class="text-base-content/90">{{ $sb['name'] }}</span>
                                                        <span class="badge badge-ghost badge-sm text-xs font-normal opacity-70">({{ count($sb['items']) }} SKU)</span>
                                                    </td>
                                                    @foreach($qtyData['months'] as $m)
                                                        @php $mVal = $sb['monthly'][$m['key']] ?? 0; @endphp
                                                        <td class="text-right font-mono text-xs whitespace-nowrap px-2">
                                                            @if($mVal > 0)
                                                                <span class="text-base-content font-semibold">{{ number_format($mVal, $mVal == (int)$mVal ? 0 : 1, ',', '.') }}</span>
                                                            @elseif($mVal < 0)
                                                                <span class="text-error font-semibold">({{ number_format(abs($mVal), 1, ',', '.') }})</span>
                                                            @else
                                                                <span class="text-base-content/25">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="text-right font-mono font-bold whitespace-nowrap bg-base-200/50 px-3 text-primary">
                                                        {{ number_format($sb['total'], 1, ',', '.') }}
                                                    </td>
                                                    <td class="text-right font-mono font-semibold whitespace-nowrap px-3 text-base-content/80">
                                                        {{ number_format($sb['avg'], 1, ',', '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if($sb['trend'] === 'up')
                                                            <span class="text-success text-xs font-bold" title="Naik dari bulan sebelumnya">▲</span>
                                                        @elseif($sb['trend'] === 'down')
                                                            <span class="text-error text-xs font-bold" title="Turun dari bulan sebelumnya">▼</span>
                                                        @else
                                                            <span class="text-base-content/30 text-xs font-bold">-</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                {{-- Child Rows (SKU Items) --}}
                                                @foreach($sb['items'] as $itKey => $it)
                                                    <tr x-show="openSubbrands['{{ $sb['key'] }}']" 
                                                        x-cloak 
                                                        class="bg-base-100/70 hover:bg-base-200/40 text-xs border-b border-base-200/50"
                                                        wire:key="it-row-{{ $sb['key'] }}-{{ md5($itKey) }}">
                                                        <td class="pl-6 font-normal whitespace-nowrap text-base-content/80 flex items-center gap-1.5 py-1.5">
                                                            <span class="text-base-content/40 text-xs">└─</span>
                                                            <span>{{ $it['name'] }}</span>
                                                        </td>
                                                        @foreach($qtyData['months'] as $m)
                                                            @php $itMVal = $it['monthly'][$m['key']] ?? 0; @endphp
                                                            <td class="text-right font-mono whitespace-nowrap px-2">
                                                                @if($itMVal > 0)
                                                                    <span class="text-base-content/80">{{ number_format($itMVal, $itMVal == (int)$itMVal ? 0 : 1, ',', '.') }}</span>
                                                                @elseif($itMVal < 0)
                                                                    <span class="text-error/80">({{ number_format(abs($itMVal), 1, ',', '.') }})</span>
                                                                @else
                                                                    <span class="text-base-content/20">-</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                        <td class="text-right font-mono font-semibold whitespace-nowrap bg-base-200/30 px-3 text-base-content/80">
                                                            {{ number_format($it['total'], 1, ',', '.') }}
                                                        </td>
                                                        <td class="text-right font-mono whitespace-nowrap px-3 text-base-content/60">
                                                            {{ number_format($it['avg'], 1, ',', '.') }}
                                                        </td>
                                                        <td class="text-center text-base-content/20">-</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-base-300 font-bold text-xs text-base-content border-t-2 border-base-content/20">
                                                <th class="uppercase">TOTAL KARTON (KTN)</th>
                                                @foreach($qtyData['months'] as $m)
                                                    @php $totMVal = $qtyData['grandTotalPerMonth'][$m['key']] ?? 0; @endphp
                                                    <th class="text-right font-mono px-2">
                                                        {{ number_format($totMVal, $totMVal == (int)$totMVal ? 0 : 1, ',', '.') }}
                                                    </th>
                                                @endforeach
                                                <th class="text-right font-mono bg-primary/20 text-primary px-3 text-sm">
                                                    {{ number_format($qtyData['grandTotal6Bln'], 1, ',', '.') }}
                                                </th>
                                                <th class="text-right font-mono px-3">
                                                    {{ number_format($qtyData['grandTotalAvg'], 1, ',', '.') }}
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- 3. DUAL INTELLIGENCE GRID: 4 ANALISA PENDUKUNG QTY --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                {{-- CARD 1: ALERT PRODUK PUTUS / DROP SKU --}}
                                <div class="card bg-base-200/50 p-4 rounded-xl border {{ !empty($qtyData['dropSkus']) ? 'border-warning/40' : 'border-base-300' }} shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-base-300 pb-2.5">
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                <div class="p-1.5 rounded-lg {{ !empty($qtyData['dropSkus']) ? 'bg-warning/10 text-warning' : 'bg-base-300 text-base-content/60' }} shrink-0">
                                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 truncate">Alert Produk Putus / Drop SKU</h4>
                                                    <span class="text-xs text-base-content/60 block truncate">Subbrand yang sebelumnya aktif namun terhenti di 1–2 bulan terakhir</span>
                                                </div>
                                            </div>
                                            @if(!empty($qtyData['dropSkus']))
                                                <span class="badge badge-warning text-white text-xs font-semibold shrink-0 whitespace-nowrap shadow-sm h-auto py-1 px-2.5">
                                                    {{ count($qtyData['dropSkus']) }} Produk Terindikasi
                                                </span>
                                            @else
                                                <span class="badge badge-success text-white text-xs font-semibold shrink-0 whitespace-nowrap shadow-sm h-auto py-1 px-2.5">
                                                    Semua SKU Terjaga
                                                </span>
                                            @endif
                                        </div>

                                        <div class="space-y-2 my-3">
                                            @forelse($qtyData['dropSkus'] as $drop)
                                                <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs">
                                                    <div class="flex items-center justify-between font-semibold">
                                                        <span class="text-base-content font-bold">{{ $drop['subbrand'] }}</span>
                                                        <span class="badge {{ $drop['badge'] }} badge-sm text-xs shrink-0 whitespace-nowrap">{{ $drop['duration'] }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between text-xs text-base-content/70 mt-1">
                                                        <span>Historis Belanja Sebelumnya:</span>
                                                        <span class="font-mono font-semibold">~{{ $drop['historical_avg'] }} KTN/bln</span>
                                                    </div>
                                                    <div class="text-xs text-base-content/80 pt-1.5 mt-1 border-t border-base-200">
                                                        <strong class="text-warning-content">Aksi:</strong> {{ $drop['action'] }}
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="bg-base-100 p-4 rounded-lg border border-base-300 text-center text-xs text-base-content/60">
                                                    Tidak ditemukan produk yang mengalami putus order mendadak. Seluruh subbrand yang aktif tetap melakukan order rutin.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                {{-- CARD 2: KONSENTRASI PORTOFOLIO PRODUK (PARETO) --}}
                                @php $pareto = $qtyData['pareto']; @endphp
                                <div class="card bg-base-200/50 p-4 rounded-xl border {{ $pareto['border'] }} shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-base-300 pb-2.5">
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                <div class="p-1.5 rounded-lg bg-info/10 text-info shrink-0">
                                                    <x-heroicon-o-chart-pie class="w-4 h-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 truncate">Ketergantungan Portofolio (Pareto)</h4>
                                                    <span class="text-xs text-base-content/60 block truncate">Tingkat diversifikasi dan konsentrasi volume toko</span>
                                                </div>
                                            </div>
                                            <span class="badge {{ $pareto['badge'] }} text-xs font-semibold shrink-0 whitespace-nowrap shadow-sm h-auto py-1 px-2.5">
                                                {{ $pareto['label'] }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 my-3 text-xs">
                                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300">
                                                <span class="text-xs text-base-content/60 font-semibold uppercase block">Hero Subbrand (#1)</span>
                                                <span class="font-bold text-sm text-primary block truncate" title="{{ $pareto['hero'] }}">{{ $pareto['hero'] }}</span>
                                                <span class="text-xs opacity-75 font-mono">{{ $pareto['hero_percent'] }}% dari total karton</span>
                                            </div>
                                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300">
                                                <span class="text-xs text-base-content/60 font-semibold uppercase block">Top 2 Subbrand</span>
                                                <span class="font-bold text-sm block font-mono">{{ $pareto['top2_percent'] }}%</span>
                                                <span class="text-xs opacity-75">Kontribusi 2 produk teratas</span>
                                            </div>
                                        </div>

                                        <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs space-y-1">
                                            <p class="text-xs text-base-content/90">{{ $pareto['desc'] }}</p>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-base-300 mt-2">
                                        <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs">
                                            <div class="flex items-start gap-1.5 text-xs leading-tight">
                                                <strong class="text-primary shrink-0">💡 Rekomendasi Sales:</strong>
                                                <span class="text-base-content/85">{{ $pareto['action'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- CARD 3: PELUANG PENETRASI PRODUK (CROSS-SELLING) --}}
                                <div class="card bg-base-200/50 p-4 rounded-xl border border-success/30 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-base-300 pb-2.5">
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                <div class="p-1.5 rounded-lg bg-success/10 text-success shrink-0">
                                                    <x-heroicon-o-sparkles class="w-4 h-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 truncate">Peluang Penetrasi Produk Baru (Top Item)</h4>
                                                    <span class="text-xs text-base-content/60 block truncate">Subbrand prioritas principal yang belum pernah dibeli toko ini</span>
                                                </div>
                                            </div>
                                            <span class="badge badge-success text-white text-xs font-semibold shrink-0 whitespace-nowrap shadow-sm h-auto py-1 px-2.5">
                                                Peluang Tambah Qty
                                            </span>
                                        </div>

                                        <div class="space-y-2 my-3">
                                            @forelse($qtyData['crossSelling'] as $cs)
                                                <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs">
                                                    <div class="flex items-center justify-between font-semibold">
                                                        <span class="text-base-content font-bold">{{ $cs['subbrand'] }}</span>
                                                        <span class="badge badge-outline badge-primary badge-xs py-1 text-xs shrink-0 whitespace-nowrap">{{ $cs['kategori'] }}</span>
                                                    </div>
                                                    <div class="text-xs text-base-content/80 mt-1">
                                                        {{ $cs['action'] }}
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="bg-base-100 p-4 rounded-lg border border-base-300 text-center text-xs text-base-content/60">
                                                    Toko ini telah mengambil seluruh subbrand prioritas TOP ITEM utama distributor.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-base-300 mt-2">
                                        <span class="text-xs text-base-content/60 italic block">
                                            *Memperkenalkan SKU baru membantu meningkatkan total karton toko tanpa kanibalisasi SKU lama.
                                        </span>
                                    </div>
                                </div>

                                {{-- CARD 4: BEDAH KOMPOSISI RASA SUBBRAND UNGGULAN --}}
                                @php $flavor = $qtyData['flavorAnalysis']; @endphp
                                <div class="card bg-base-200/50 p-4 rounded-xl border border-secondary/30 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-base-300 pb-2.5">
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                <div class="p-1.5 rounded-lg bg-secondary/10 text-secondary shrink-0">
                                                    <x-heroicon-o-fire class="w-4 h-4" />
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 truncate">Bedah Rasa Subbrand Unggulan</h4>
                                                    <span class="text-xs text-base-content/60 block truncate">Komposisi rasa cepat laku vs seret pada {{ $flavor['hero_subbrand'] ?? 'Subbrand Terlaris' }}</span>
                                                </div>
                                            </div>
                                            <span class="badge badge-secondary text-white text-xs font-semibold shrink-0 whitespace-nowrap shadow-sm h-auto py-1 px-2.5">
                                                Flavor Run-Rate
                                            </span>
                                        </div>

                                        @if($flavor)
                                            <div class="grid grid-cols-2 gap-2 my-3 text-xs">
                                                <div class="bg-base-100 p-2.5 rounded-lg border border-base-300">
                                                    <span class="text-xs text-success font-bold uppercase block">⭐ Rasa Terlaris (Favorit)</span>
                                                    <span class="font-bold text-xs text-base-content block mt-0.5 truncate" title="{{ $flavor['top_flavor'] }}">{{ $flavor['top_flavor'] }}</span>
                                                    <span class="text-xs opacity-75 font-mono">{{ number_format($flavor['top_total'], 1, ',', '.') }} KTN ({{ $flavor['top_percent'] }}%)</span>
                                                </div>
                                                <div class="bg-base-100 p-2.5 rounded-lg border border-base-300">
                                                    <span class="text-xs text-warning font-bold uppercase block">🐢 Rasa Paling Seret (Slow)</span>
                                                    <span class="font-bold text-xs text-base-content block mt-0.5 truncate" title="{{ $flavor['bottom_flavor'] }}">{{ $flavor['bottom_flavor'] }}</span>
                                                    <span class="text-xs opacity-75 font-mono">{{ number_format($flavor['bottom_total'], 1, ',', '.') }} KTN ({{ $flavor['bottom_percent'] }}%)</span>
                                                </div>
                                            </div>

                                            <div class="bg-base-100 p-2.5 rounded-lg border border-base-300 text-xs">
                                                <div class="flex items-start gap-1.5 text-xs leading-tight">
                                                    <strong class="text-secondary shrink-0">🎯 Trik Negosiasi:</strong>
                                                    <span class="text-base-content/85">{{ $flavor['tip'] }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="bg-base-100 p-4 rounded-lg border border-base-300 text-center text-xs text-base-content/60 my-3">
                                                Belum ada cukup data varian rasa untuk dianalisa.
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-2 border-t border-base-300 mt-2">
                                        <span class="text-xs text-base-content/60 italic block">
                                            *Mencegah alasan toko "stok banyak" yang sebenarnya hanya menumpuk pada varian lambat.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- TAB 4: ANALISA KUNJUNGAN --}}
                    <div x-show="tab === 'visit'" class="space-y-4">
                        @php
                            $visitData = $this->visitHistory;
                        @endphp

                        @if(!$visitData)
                            <div class="bg-base-200/50 p-6 rounded-xl border border-base-300 text-center">
                                <span class="loading loading-spinner loading-md text-primary"></span>
                                <p class="text-xs text-base-content/60 mt-2 font-medium">Memuat data histori kunjungan...</p>
                            </div>
                        @elseif(!$visitData['hasPrc'])
                            <div class="alert alert-warning shadow-sm border border-warning/30 rounded-xl">
                                <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0 text-warning" />
                                <div>
                                    <h4 class="font-bold text-xs uppercase tracking-wider">Kode PRC Toko Belum Terhubung</h4>
                                    <p class="text-xs opacity-90 mt-0.5">
                                        Toko ini (<strong>{{ $visitData['customer_code'] }}</strong> - {{ $visitData['customer_name'] }}) belum memiliki mapping kode <em>Principal Customer (PRC)</em> di sistem. 
                                        Monitoring histori kunjungan dari tim Head Office / Principal hanya dapat disinkronkan untuk outlet yang memiliki kode PRC.
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- Header Top Info --}}
                            <div class="flex flex-wrap items-center justify-between gap-2 bg-base-200/50 p-3 rounded-xl border border-base-300">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="p-1.5 rounded-lg bg-primary/10 text-primary shrink-0">
                                        <x-heroicon-o-map-pin class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs text-base-content/60 font-medium block">Monitoring Kunjungan Principal (HOINA)</span>
                                        <span class="text-xs font-bold text-base-content truncate block">
                                            Kode PRC: <strong class="text-primary font-mono">{{ $visitData['prc'] }}</strong> &bull; Periode: {{ $visitData['kuartalLabel'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge badge-ghost badge-sm text-xs font-mono font-semibold">
                                        {{ count($visitData['timeline']) }} Total Riwayat
                                    </span>
                                </div>
                            </div>

                            {{-- SEKSI 1: 4 KPI SUMMARY CARDS --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                {{-- Card 1: Total Kunjungan Kuartal --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Total Kunjungan</span>
                                            <div class="p-1 rounded-md bg-primary/10 text-primary shrink-0">
                                                <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-baseline gap-1.5">
                                            <span class="text-2xl font-black text-primary">{{ $visitData['kpi']['total_visits'] }}x</span>
                                            <span class="text-xs text-base-content/60 font-semibold">Fisik ({{ $visitData['kpi']['total_schedules'] }}x Rute)</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs">
                                        @if($visitData['kpi']['last_visit'])
                                            <div class="flex items-start gap-1">
                                                <span class="text-base-content/60 shrink-0">Terakhir:</span>
                                                <div class="min-w-0 truncate">
                                                    <strong class="text-base-content font-bold">{{ $visitData['kpi']['last_visit']['formatted_date'] }}</strong>
                                                    <span class="text-xs text-base-content/60 block truncate">
                                                        ({{ $visitData['kpi']['last_visit']['days_ago'] }}) &bull; {{ $visitData['kpi']['last_visit']['visitor_role'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-error font-semibold flex items-center gap-1">
                                                <x-heroicon-o-x-circle class="w-3.5 h-3.5 shrink-0" />
                                                Belum dikunjungi di kuartal ini
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Card 2: Pembagian Tingkatan Kunjungan (SPV, ASM, RSM) --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Pemisahan Role</span>
                                            <div class="p-1 rounded-md bg-secondary/10 text-secondary shrink-0">
                                                <x-heroicon-o-user-group class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-3 gap-1.5 mt-2 text-center">
                                            {{-- SPV --}}
                                            <div class="bg-base-100 p-1.5 rounded-lg border border-base-300">
                                                <span class="badge badge-primary badge-xs text-xs font-bold text-white px-1.5">SPV</span>
                                                <span class="font-black text-sm text-base-content block mt-1">{{ $visitData['roles']['SPV']['visits'] }}x</span>
                                                <span class="text-xs text-base-content/50 block truncate">{{ $visitData['roles']['SPV']['last_date'] ? \Carbon\Carbon::parse($visitData['roles']['SPV']['last_date'])->format('d/m') : '-' }}</span>
                                            </div>
                                            {{-- ASM --}}
                                            <div class="bg-base-100 p-1.5 rounded-lg border border-base-300">
                                                <span class="badge badge-secondary badge-xs text-xs font-bold text-white px-1.5">ASM</span>
                                                <span class="font-black text-sm text-base-content block mt-1">{{ $visitData['roles']['ASM']['visits'] }}x</span>
                                                <span class="text-xs text-base-content/50 block truncate">{{ $visitData['roles']['ASM']['last_date'] ? \Carbon\Carbon::parse($visitData['roles']['ASM']['last_date'])->format('d/m') : '-' }}</span>
                                            </div>
                                            {{-- RSM --}}
                                            <div class="bg-base-100 p-1.5 rounded-lg border border-base-300">
                                                <span class="badge badge-accent badge-xs text-xs font-bold text-white px-1.5">RSM</span>
                                                <span class="font-black text-sm text-base-content block mt-1">{{ $visitData['roles']['RSM']['visits'] }}x</span>
                                                <span class="text-xs text-base-content/50 block truncate">{{ $visitData['roles']['RSM']['last_date'] ? \Carbon\Carbon::parse($visitData['roles']['RSM']['last_date'])->format('d/m') : '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs flex justify-between items-center text-base-content/60 font-semibold">
                                        <span>Total Tim: 3 Level</span>
                                        <span>Jadwal: {{ $visitData['kpi']['total_schedules'] }} Rute</span>
                                    </div>
                                </div>

                                {{-- Card 3: Efektivitas Order (Strike Rate & Value) --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Efektivitas Order (EC)</span>
                                            <div class="p-1 rounded-md bg-success/10 text-success shrink-0">
                                                <x-heroicon-o-shopping-bag class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-baseline gap-1.5">
                                            <span class="text-2xl font-black {{ $visitData['kpi']['strike_rate'] >= 70 ? 'text-success' : ($visitData['kpi']['strike_rate'] > 0 ? 'text-warning' : 'text-error') }}">
                                                {{ $visitData['kpi']['strike_rate'] }}%
                                            </span>
                                            <span class="text-xs text-base-content/60 font-semibold">Strike Rate</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base-content/60">PO Visit:</span>
                                            <span class="font-mono font-bold text-success">Rp {{ number_format($visitData['kpi']['total_order_val'], 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs opacity-75 mt-0.5">
                                            <span>Kuantiti PO:</span>
                                            <span class="font-mono font-semibold">{{ number_format($visitData['kpi']['total_order_qty'], 0, ',', '.') }} unit</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card 4: Rata-Rata Durasi di Toko --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Durasi di Toko</span>
                                            <div class="p-1 rounded-md bg-warning/10 text-warning shrink-0">
                                                <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-baseline gap-1.5">
                                            <span class="text-2xl font-black text-primary">⏱ ~{{ $visitData['kpi']['avg_duration'] }}</span>
                                            <span class="text-xs text-base-content/60 font-semibold">Menit / Visit</span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs">
                                        @if($visitData['kpi']['avg_duration'] >= 15)
                                            <span class="badge badge-success text-white badge-xs py-1 text-xs font-semibold shrink-0 whitespace-nowrap gap-1">
                                                <x-heroicon-m-check class="w-3 h-3" /> Durasi Kunjungan Efektif
                                            </span>
                                        @elseif($visitData['kpi']['avg_duration'] > 0)
                                            <span class="badge badge-warning text-white badge-xs py-1 text-xs font-semibold shrink-0 whitespace-nowrap gap-1">
                                                <x-heroicon-m-bolt class="w-3 h-3" /> Kunjungan Kilat / Cepat
                                            </span>
                                        @else
                                            <span class="text-xs text-base-content/50">Tidak ada durasi tercatat</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- SEKSI 2: REKAPITULASI ALASAN KENDALA (NOT BUY / NOT VISIT) --}}
                            @if(!empty($visitData['reasons']['not_buy']) || !empty($visitData['reasons']['not_visit']))
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    {{-- Kolom Alasan Tidak Beli (Not Buy) --}}
                                    @if(!empty($visitData['reasons']['not_buy']))
                                        <div class="card bg-warning/5 p-3.5 rounded-xl border border-warning/20 shadow-sm">
                                            <div class="flex items-center justify-between pb-2 border-b border-warning/20">
                                                <div class="flex items-center gap-1.5 min-w-0">
                                                    <span class="badge badge-warning text-white badge-xs py-1 text-xs font-bold shrink-0 whitespace-nowrap">NOT BUY</span>
                                                    <h5 class="text-xs font-bold text-base-content/90 truncate">Alasan Dikunjungi Tapi Tidak Order</h5>
                                                </div>
                                                <span class="text-xs font-bold text-warning shrink-0">{{ count($visitData['reasons']['not_buy']) }} Kategori</span>
                                            </div>
                                            <div class="space-y-1.5 mt-2.5">
                                                @foreach($visitData['reasons']['not_buy'] as $rb)
                                                    <div class="bg-base-100 p-2 rounded-lg border border-base-300 text-xs">
                                                        <div class="flex items-center justify-between">
                                                            <span class="font-bold text-base-content">{{ $rb['desc'] }}</span>
                                                            <span class="badge badge-warning badge-outline badge-xs text-xs font-bold shrink-0 whitespace-nowrap">{{ $rb['count'] }}x Kejadian</span>
                                                        </div>
                                                        @if(!empty($rb['notes']))
                                                            <div class="text-xs text-base-content/70 italic mt-1 pt-1 border-t border-base-200">
                                                                Catatan: {{ implode(' | ', $rb['notes']) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Kolom Alasan Tidak Kunjung (Not Visit) --}}
                                    @if(!empty($visitData['reasons']['not_visit']))
                                        <div class="card bg-error/5 p-3.5 rounded-xl border border-error/20 shadow-sm">
                                            <div class="flex items-center justify-between pb-2 border-b border-error/20">
                                                <div class="flex items-center gap-1.5 min-w-0">
                                                    <span class="badge badge-error text-white badge-xs py-1 text-xs font-bold shrink-0 whitespace-nowrap">NOT VISIT</span>
                                                    <h5 class="text-xs font-bold text-base-content/90 truncate">Alasan Terjadwal Tapi Batal Kunjung</h5>
                                                </div>
                                                <span class="text-xs font-bold text-error shrink-0">{{ count($visitData['reasons']['not_visit']) }} Kategori</span>
                                            </div>
                                            <div class="space-y-1.5 mt-2.5">
                                                @foreach($visitData['reasons']['not_visit'] as $rv)
                                                    <div class="bg-base-100 p-2 rounded-lg border border-base-300 text-xs">
                                                        <div class="flex items-center justify-between">
                                                            <span class="font-bold text-base-content">{{ $rv['desc'] }}</span>
                                                            <span class="badge badge-error badge-outline badge-xs text-xs font-bold shrink-0 whitespace-nowrap">{{ $rv['count'] }}x Kejadian</span>
                                                        </div>
                                                        @if(!empty($rv['notes']))
                                                            <div class="text-xs text-base-content/70 italic mt-1 pt-1 border-t border-base-200">
                                                                Catatan: {{ implode(' | ', $rv['notes']) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- SEKSI 3: TABEL DETAIL TIMELINE KUNJUNGAN --}}
                            <div class="card bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden" 
                                 x-data="{ filterRole: 'all', filterStatus: 'all' }">
                                
                                {{-- Table Header & Filters --}}
                                <div class="p-3 bg-base-200/50 border-b border-base-300 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-lg bg-base-300 text-base-content/70 shrink-0">
                                            <x-heroicon-o-queue-list class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/90">Riwayat & Log Kunjungan Toko</h4>
                                            <span class="text-xs text-base-content/60 block">Rincian aktivitas kunjungan lapangan tim principal per tanggal</span>
                                        </div>
                                    </div>

                                    {{-- Quick Filters --}}
                                    <div class="flex flex-wrap items-center gap-2">
                                        {{-- Filter Role --}}
                                        <div class="join border border-base-300 rounded-lg overflow-hidden bg-base-100">
                                            <button type="button" @click="filterRole = 'all'" :class="filterRole === 'all' ? 'bg-primary text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">Semua Role</button>
                                            <button type="button" @click="filterRole = 'SPV'" :class="filterRole === 'SPV' ? 'bg-primary text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">SPV</button>
                                            <button type="button" @click="filterRole = 'ASM'" :class="filterRole === 'ASM' ? 'bg-secondary text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">ASM</button>
                                            <button type="button" @click="filterRole = 'RSM'" :class="filterRole === 'RSM' ? 'bg-accent text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">RSM</button>
                                        </div>

                                        {{-- Filter Status --}}
                                        <div class="join border border-base-300 rounded-lg overflow-hidden bg-base-100">
                                            <button type="button" @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-neutral text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">Semua Status</button>
                                            <button type="button" @click="filterStatus = 'buy'" :class="filterStatus === 'buy' ? 'bg-success text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">Beli (EC)</button>
                                            <button type="button" @click="filterStatus = 'not_buy'" :class="filterStatus === 'not_buy' ? 'bg-warning text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">Not Buy</button>
                                            <button type="button" @click="filterStatus = 'unvisited'" :class="filterStatus === 'unvisited' ? 'bg-error text-white font-bold' : 'text-base-content/70 hover:bg-base-200'" class="btn btn-xs join-item border-0">Batal/Not Visit</button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Responsive Table Container --}}
                                <div class="overflow-x-auto max-h-[420px]">
                                    <table class="table table-xs w-full">
                                        <thead class="sticky top-0 z-20 bg-base-200 text-base-content/80 uppercase text-xs tracking-wider border-b border-base-300 shadow-sm">
                                            <tr>
                                                <th class="w-10 text-center whitespace-nowrap">No</th>
                                                <th class="whitespace-nowrap">Tanggal & Jam</th>
                                                <th class="whitespace-nowrap">Tingkatan & Petugas</th>
                                                <th class="text-center whitespace-nowrap">Durasi</th>
                                                <th class="text-center whitespace-nowrap">Status Kunjungan</th>
                                                <th class="text-right whitespace-nowrap">Order PO</th>
                                                <th class="whitespace-nowrap min-w-[240px]">Alasan & Catatan Lapangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-base-200 text-xs">
                                            @forelse($visitData['timeline'] as $idx => $item)
                                                <tr wire:key="visit-row-{{ $idx }}-{{ $item['date'] }}-{{ $item['muid'] }}"
                                                    x-show="(filterRole === 'all' || '{{ $item['role'] }}' === filterRole) && 
                                                            (filterStatus === 'all' || 
                                                             (filterStatus === 'buy' && {{ $item['is_buy'] ? 'true' : 'false' }}) ||
                                                             (filterStatus === 'not_buy' && {{ ($item['is_visit'] && !$item['is_buy']) ? 'true' : 'false' }}) ||
                                                             (filterStatus === 'unvisited' && {{ (!$item['is_visit']) ? 'true' : 'false' }}))"
                                                    class="hover:bg-base-200/40 transition-colors {{ $item['is_joint'] ? 'bg-warning/5' : '' }}">
                                                    
                                                    {{-- No --}}
                                                    <td class="text-center font-mono opacity-60 whitespace-nowrap">{{ $loop->iteration }}</td>

                                                    {{-- Tanggal & Jam --}}
                                                    <td class="whitespace-nowrap">
                                                        <span class="font-bold text-base-content block">{{ $item['formatted_date'] }}</span>
                                                        <span class="text-xs text-base-content/60 font-mono block mt-0.5">
                                                            {{ $item['time_range'] }}
                                                        </span>
                                                        @if($item['is_pjp'])
                                                            <span class="text-xs text-primary font-semibold block mt-0.5">&bull; Rute Terjadwal (PJP)</span>
                                                        @else
                                                            <span class="text-xs text-warning-content font-semibold block mt-0.5">&bull; Kunjungan Non-PJP</span>
                                                        @endif
                                                    </td>

                                                    {{-- Tingkatan & Petugas --}}
                                                    <td class="whitespace-nowrap">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="badge {{ $item['role_badge'] }} badge-sm text-xs font-bold text-white shrink-0 whitespace-nowrap h-auto py-0.5 px-2">
                                                                {{ $item['role'] }}
                                                            </span>
                                                            <div class="min-w-0">
                                                                <span class="font-bold text-xs text-base-content block truncate max-w-[160px]" title="{{ $item['muname'] }}">
                                                                    {{ $item['muname'] }}
                                                                </span>
                                                                <span class="font-mono text-xs opacity-60 block truncate">{{ $item['muid'] }}</span>
                                                            </div>
                                                        </div>
                                                        @if($item['is_joint'])
                                                            <div class="mt-1">
                                                                <span class="badge badge-warning text-white badge-xs text-xs font-semibold shrink-0 whitespace-nowrap h-auto py-0.5 px-1.5 gap-1">
                                                                    🤝 Joint Visit
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </td>

                                                    {{-- Durasi --}}
                                                    <td class="text-center whitespace-nowrap">
                                                        @if($item['duration_minutes'] >= 15)
                                                            <span class="badge badge-success text-white badge-sm text-xs font-bold font-mono shrink-0 whitespace-nowrap h-auto py-1 px-2.5 shadow-sm">
                                                                ⏱ {{ $item['duration_minutes'] }} mnt
                                                            </span>
                                                        @elseif($item['duration_minutes'] > 0)
                                                            <span class="badge badge-warning text-white badge-sm text-xs font-bold font-mono shrink-0 whitespace-nowrap h-auto py-1 px-2.5 shadow-sm">
                                                                ⏱ {{ $item['duration_minutes'] }} mnt
                                                            </span>
                                                        @else
                                                            <span class="badge badge-ghost badge-sm text-xs font-mono opacity-50 shrink-0 whitespace-nowrap h-auto py-1 px-2.5">
                                                                0 mnt
                                                            </span>
                                                        @endif
                                                    </td>

                                                    {{-- Status Kunjungan --}}
                                                    <td class="text-center whitespace-nowrap">
                                                        @if($item['is_visit'])
                                                            <span class="badge badge-success badge-sm text-xs font-bold text-white shadow-sm shrink-0 whitespace-nowrap h-auto py-1 px-2.5">
                                                                Dikunjungi
                                                            </span>
                                                        @else
                                                            <span class="badge badge-error badge-sm text-xs font-bold text-white shadow-sm shrink-0 whitespace-nowrap h-auto py-1 px-2.5">
                                                                Tidak Kunjung
                                                            </span>
                                                        @endif
                                                    </td>

                                                    {{-- Order PO (Value & Qty) --}}
                                                    <td class="text-right whitespace-nowrap">
                                                        @if($item['is_buy'])
                                                            <div class="font-mono whitespace-nowrap">
                                                                <span class="font-bold text-xs text-success block">Rp {{ number_format($item['order_val'], 0, ',', '.') }}</span>
                                                                <span class="text-xs text-base-content/75 font-semibold block">{{ number_format($item['order_qty'], 0, ',', '.') }} unit</span>
                                                            </div>
                                                        @elseif($item['is_visit'])
                                                            <span class="badge badge-warning badge-sm text-xs font-semibold text-white shrink-0 whitespace-nowrap h-auto py-1 px-2.5">
                                                                Tidak Order
                                                            </span>
                                                        @else
                                                            <span class="text-xs opacity-50 block">-</span>
                                                        @endif
                                                    </td>

                                                    {{-- Alasan & Catatan Lapangan --}}
                                                    <td class="min-w-[240px]">
                                                        @if(!empty($item['reason_desc']))
                                                            <div class="space-y-1">
                                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                                    <span class="badge {{ $item['reason_type'] === 'BUY' ? 'badge-warning text-white' : 'badge-error text-white' }} badge-xs text-xs font-semibold shrink-0 whitespace-nowrap h-auto py-0.5 px-2">
                                                                        [{{ $item['reason_type'] ?? 'INFO' }}] {{ $item['reason_id'] }}
                                                                    </span>
                                                                    <span class="font-bold text-xs text-base-content">{{ $item['reason_desc'] }}</span>
                                                                </div>
                                                                @if(!empty($item['reason_note']))
                                                                    <div class="text-xs text-base-content/80 italic bg-base-200/60 p-1.5 rounded border border-base-300">
                                                                        "{{ $item['reason_note'] }}"
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @elseif($item['is_buy'])
                                                            <span class="text-xs text-success font-semibold flex items-center gap-1 whitespace-nowrap">
                                                                <x-heroicon-m-check-circle class="w-3.5 h-3.5 shrink-0" />
                                                                Kunjungan Efektif (PO Berhasil)
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-base-content/40 italic">Tidak ada catatan kendala</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-8">
                                                        <div class="flex flex-col items-center justify-center text-base-content/50">
                                                            <x-heroicon-o-calendar class="w-10 h-10 mb-2 opacity-40" />
                                                            <span class="font-bold text-xs">Belum Ada Riwayat Kunjungan pada Kuartal Ini</span>
                                                            <span class="text-xs opacity-75 mt-0.5">Toko ini belum memiliki catatan rute kunjungan dari tim SPI/HOINA di periode terpilih.</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- TAB 5: KOMPARASI ORDER VS REALISASI --}}
                    <div x-show="tab === 'fulfillment'" class="space-y-4">
                        @php
                            $fulData = $this->orderFulfillment;
                        @endphp

                        @if(!$fulData)
                            <div class="bg-base-200/50 p-6 rounded-xl border border-base-300 text-center">
                                <span class="loading loading-spinner loading-md text-primary"></span>
                                <p class="text-xs text-base-content/60 mt-2 font-medium">Memuat data komparasi order vs realisasi...</p>
                            </div>
                        @else
                            {{-- Header Top Info --}}
                            <div class="flex flex-wrap items-center justify-between gap-2 bg-base-200/50 p-3 rounded-xl border border-base-300">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="p-1.5 rounded-lg bg-info/10 text-info shrink-0">
                                        <x-heroicon-o-scale class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs text-base-content/60 font-medium block">Evaluasi Eksekusi & Pemenuhan Pesanan (Fulfillment)</span>
                                        <span class="text-xs font-bold text-base-content truncate block">
                                            Komparasi Order PO Kunjungan vs Faktur Selling Out Distributor &bull; {{ $fulData['kuartalLabel'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge {{ $fulData['kpi']['overall_badge'] }} text-xs font-bold shrink-0 whitespace-nowrap h-auto py-1 px-2.5 shadow-sm">
                                        {{ $fulData['kpi']['overall_status'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Alert Info Jika Ada Joint Visit Auto-Deduplication --}}
                            @if($fulData['kpi']['has_jv'])
                                <div class="alert alert-info bg-info/10 border border-info/30 text-xs py-2.5 px-3.5 rounded-xl flex flex-wrap items-center justify-between gap-2 shadow-sm">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="badge badge-info badge-sm font-bold text-white shrink-0 whitespace-nowrap h-auto py-0.5 px-2">
                                            🤝 JV Auto-Deduplication
                                        </span>
                                        <span class="text-base-content/85 text-xs">
                                            Sistem mendeteksi transaksi <strong>Joint Visit (kunjungan bersama)</strong> dan otomatis mendeduplikasi pesanan ganda sebesar 
                                            <strong class="text-primary font-mono">Rp {{ number_format($fulData['kpi']['jv_savings'], 0, ',', '.') }}</strong> agar komparasi 100% objektif.
                                        </span>
                                    </div>
                                    <span class="text-xs opacity-75 font-mono shrink-0 whitespace-nowrap">
                                        Laporan Kasar: Rp {{ number_format($fulData['kpi']['total_po_raw'], 0, ',', '.') }} &rarr; <strong>Bersih: Rp {{ number_format($fulData['kpi']['total_po_val'], 0, ',', '.') }}</strong>
                                    </span>
                                </div>
                            @endif

                            {{-- SEKSI 1: 4 KPI SUMMARY CARDS --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                {{-- Card 1: Total Order PO (Kunjungan) --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Total Order PO</span>
                                            <div class="p-1 rounded-md bg-primary/10 text-primary shrink-0">
                                                <x-heroicon-o-shopping-bag class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xl font-black text-primary font-mono block">
                                                Rp {{ number_format($fulData['kpi']['total_po_val'], 0, ',', '.') }}
                                            </span>
                                            <span class="text-xs text-base-content/60 font-semibold block mt-0.5">
                                                {{ $fulData['kpi']['total_po_sessions'] }} Sesi Order ({{ number_format($fulData['kpi']['total_po_qty'], 0, ',', '.') }} unit)
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs text-base-content/60">
                                        <span>Sumber: Mobile Visit HOINA</span>
                                    </div>
                                </div>

                                {{-- Card 2: Total Realisasi Faktur (Selling Out) --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Realisasi Faktur SO</span>
                                            <div class="p-1 rounded-md bg-success/10 text-success shrink-0">
                                                <x-heroicon-o-document-check class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xl font-black text-success font-mono block">
                                                Rp {{ number_format($fulData['kpi']['total_so_val'], 0, ',', '.') }}
                                            </span>
                                            <span class="text-xs text-base-content/60 font-semibold block mt-0.5">
                                                {{ $fulData['kpi']['total_so_invoices'] }} Faktur ({{ number_format($fulData['kpi']['total_so_ktn'], 1, ',', '.') }} KTN)
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs text-base-content/60">
                                        <span>Sumber: Faktur Distributor</span>
                                    </div>
                                </div>

                                {{-- Card 3: Tingkat Pemenuhan (Service Level) --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Tingkat Pemenuhan</span>
                                            <div class="p-1 rounded-md bg-info/10 text-info shrink-0">
                                                <x-heroicon-o-chart-pie class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-baseline gap-1.5">
                                            <span class="text-2xl font-black {{ $fulData['kpi']['fulfillment_rate'] >= 80 ? 'text-success' : ($fulData['kpi']['fulfillment_rate'] > 0 ? 'text-warning' : 'text-error') }}">
                                                {{ $fulData['kpi']['fulfillment_rate'] }}%
                                            </span>
                                            <span class="text-xs text-base-content/60 font-semibold">Service Level</span>
                                        </div>
                                        <div class="w-full mt-2">
                                            <progress class="progress {{ $fulData['kpi']['fulfillment_rate'] >= 80 ? 'progress-success' : ($fulData['kpi']['fulfillment_rate'] > 0 ? 'progress-warning' : 'progress-error') }} w-full h-1.5" 
                                                      value="{{ min($fulData['kpi']['fulfillment_rate'], 100) }}" max="100"></progress>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs font-semibold">
                                        <span class="text-base-content/80">{{ $fulData['kpi']['overall_status'] }}</span>
                                    </div>
                                </div>

                                {{-- Card 4: Selisih / Gap Pemenuhan --}}
                                <div class="card bg-base-200/50 p-3.5 rounded-xl border border-base-300 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Selisih / Gap</span>
                                            <div class="p-1 rounded-md bg-warning/10 text-warning shrink-0">
                                                <x-heroicon-o-arrows-right-left class="w-3.5 h-3.5" />
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xl font-black font-mono block {{ $fulData['kpi']['gap'] >= 0 ? 'text-success' : 'text-error' }}">
                                                {{ $fulData['kpi']['gap'] >= 0 ? '+' : '' }}Rp {{ number_format($fulData['kpi']['gap'], 0, ',', '.') }}
                                            </span>
                                            <span class="text-xs text-base-content/60 font-semibold block mt-0.5">
                                                Realisasi vs Pesanan PO
                                            </span>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-base-300 mt-2 text-xs">
                                        @if($fulData['kpi']['gap'] < 0)
                                            <span class="text-error font-semibold flex items-center gap-1">
                                                <x-heroicon-m-arrow-trending-down class="w-3.5 h-3.5 shrink-0" />
                                                Drop PO / Tertahan Kirim
                                            </span>
                                        @else
                                            <span class="text-success font-semibold flex items-center gap-1">
                                                <x-heroicon-m-arrow-trending-up class="w-3.5 h-3.5 shrink-0" />
                                                Surplus / Repeat Order
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- SEKSI 2: TABEL KOMPARASI BULANAN --}}
                            <div class="card bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden">
                                <div class="p-3 bg-base-200/50 border-b border-base-300 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-lg bg-base-300 text-base-content/70 shrink-0">
                                            <x-heroicon-o-calendar class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/90">Komparasi Performa Bulanan</h4>
                                            <span class="text-xs text-base-content/60 block">Rincian perbandingan nilai order PO vs faktur realisasi per bulan</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="table table-xs w-full">
                                        <thead class="bg-base-200 text-base-content/80 uppercase text-xs tracking-wider border-b border-base-300">
                                            <tr>
                                                <th>Bulan</th>
                                                <th class="text-right whitespace-nowrap">Order PO Kunjungan</th>
                                                <th class="text-right whitespace-nowrap">Realisasi Faktur SO</th>
                                                <th class="text-right whitespace-nowrap">Selisih (Gap)</th>
                                                <th class="text-center whitespace-nowrap">% Pemenuhan</th>
                                                <th class="text-center whitespace-nowrap">Status Eksekusi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-base-200 text-xs">
                                            @foreach($fulData['monthly'] as $mRow)
                                                <tr class="hover:bg-base-200/40 transition-colors">
                                                    <td class="font-bold text-base-content whitespace-nowrap">
                                                        {{ $mRow['label'] }}
                                                    </td>
                                                    <td class="text-right font-mono whitespace-nowrap">
                                                        <span class="font-bold block {{ $mRow['po_val'] > 0 ? 'text-primary' : 'text-base-content/40' }}">
                                                            Rp {{ number_format($mRow['po_val'], 0, ',', '.') }}
                                                        </span>
                                                        <span class="text-xs text-base-content/60 block">
                                                            {{ $mRow['po_count'] }} Sesi ({{ number_format($mRow['po_qty'], 0, ',', '.') }} unit)
                                                        </span>
                                                    </td>
                                                    <td class="text-right font-mono whitespace-nowrap">
                                                        <span class="font-bold block {{ $mRow['so_val'] > 0 ? 'text-success' : 'text-base-content/40' }}">
                                                            Rp {{ number_format($mRow['so_val'], 0, ',', '.') }}
                                                        </span>
                                                        <span class="text-xs text-base-content/60 block">
                                                            {{ $mRow['so_count'] }} Faktur ({{ number_format($mRow['so_ktn'], 1, ',', '.') }} KTN)
                                                        </span>
                                                    </td>
                                                    <td class="text-right font-mono whitespace-nowrap font-bold {{ $mRow['gap'] >= 0 ? 'text-success' : 'text-error' }}">
                                                        {{ $mRow['gap'] >= 0 ? '+' : '' }}Rp {{ number_format($mRow['gap'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-center whitespace-nowrap">
                                                        <span class="font-mono font-bold text-xs {{ $mRow['rate'] >= 80 ? 'text-success' : ($mRow['rate'] > 0 ? 'text-warning' : 'text-base-content/40') }}">
                                                            {{ $mRow['rate'] }}%
                                                        </span>
                                                    </td>
                                                    <td class="text-center whitespace-nowrap">
                                                        <span class="badge {{ $mRow['status_badge'] }} badge-sm text-xs font-semibold shrink-0 whitespace-nowrap h-auto py-1 px-2.5">
                                                            {{ $mRow['status'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-base-200/70 border-t-2 border-base-300 font-bold text-xs">
                                            <tr>
                                                <td class="whitespace-nowrap">TOTAL KUARTAL</td>
                                                <td class="text-right font-mono whitespace-nowrap text-primary">
                                                    Rp {{ number_format($fulData['kpi']['total_po_val'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-right font-mono whitespace-nowrap text-success">
                                                    Rp {{ number_format($fulData['kpi']['total_so_val'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-right font-mono whitespace-nowrap {{ $fulData['kpi']['gap'] >= 0 ? 'text-success' : 'text-error' }}">
                                                    {{ $fulData['kpi']['gap'] >= 0 ? '+' : '' }}Rp {{ number_format($fulData['kpi']['gap'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-center font-mono whitespace-nowrap {{ $fulData['kpi']['fulfillment_rate'] >= 80 ? 'text-success' : 'text-warning' }}">
                                                    {{ $fulData['kpi']['fulfillment_rate'] }}%
                                                </td>
                                                <td class="text-center whitespace-nowrap">
                                                    <span class="badge {{ $fulData['kpi']['overall_badge'] }} badge-sm text-xs font-bold shrink-0 whitespace-nowrap h-auto py-1 px-2.5">
                                                        {{ $fulData['kpi']['overall_status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- SEKSI 3: DUAL DETAIL LIST (BERDAMPINGAN: PO KUNJUNGAN VS FAKTUR REALISASI) --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                {{-- Kolom Kiri: Riwayat Order PO Kunjungan --}}
                                <div class="card bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col justify-between">
                                    <div>
                                        <div class="p-3 bg-base-200/50 border-b border-base-300 flex items-center justify-between">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="p-1 rounded-md bg-primary/10 text-primary shrink-0">
                                                    <x-heroicon-o-clipboard-document-list class="w-3.5 h-3.5" />
                                                </div>
                                                <h5 class="font-bold text-xs uppercase tracking-wider text-base-content/90 truncate">
                                                    Daftar Order PO Kunjungan (Mobile App)
                                                </h5>
                                            </div>
                                            <span class="badge badge-primary badge-outline badge-sm text-xs font-mono font-bold shrink-0 whitespace-nowrap">
                                                {{ count($fulData['po_sessions']) }} Sesi PO
                                            </span>
                                        </div>

                                        <div class="overflow-x-auto max-h-[350px]">
                                            <table class="table table-xs w-full">
                                                <thead class="sticky top-0 z-20 bg-base-200 text-base-content/80 uppercase text-xs border-b border-base-300 shadow-sm">
                                                    <tr>
                                                        <th class="whitespace-nowrap">Tgl & Jam</th>
                                                        <th class="whitespace-nowrap">Petugas Sales</th>
                                                        <th class="text-right whitespace-nowrap">Nilai PO (Bersih)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-base-200 text-xs">
                                                    @forelse($fulData['po_sessions'] as $pos)
                                                        <tr class="hover:bg-base-200/40 transition-colors {{ $pos['is_jv'] ? 'bg-info/5' : '' }}">
                                                            <td class="whitespace-nowrap">
                                                                <span class="font-bold text-base-content block">{{ $pos['formatted_date'] }}</span>
                                                                <span class="text-xs text-base-content/60 font-mono block">{{ $pos['time'] }} WIB</span>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                                    <span class="badge badge-primary badge-xs text-xs font-bold text-white shrink-0 whitespace-nowrap">
                                                                        {{ $pos['lead_role'] }}
                                                                    </span>
                                                                    <span class="font-bold text-xs text-base-content truncate">{{ $pos['lead_person'] }}</span>
                                                                </div>
                                                                @if($pos['is_jv'])
                                                                    <div class="mt-1">
                                                                        <span class="badge badge-info text-white badge-xs text-xs font-semibold shrink-0 whitespace-nowrap h-auto py-0.5 px-1.5 gap-1"
                                                                              title="Joint visit bersama {{ $pos['member_count'] }} petugas, nilai order diambil 1x transaksi tertinggi">
                                                                            🤝 Joint Visit ({{ $pos['member_count'] }} Petugas)
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="text-right font-mono whitespace-nowrap">
                                                                <span class="font-bold text-xs text-primary block">
                                                                    Rp {{ number_format($pos['val'], 0, ',', '.') }}
                                                                </span>
                                                                <span class="text-xs text-base-content/60 block">
                                                                    {{ number_format($pos['qty'], 0, ',', '.') }} unit
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center py-6 text-xs text-base-content/50">
                                                                Tidak ada catatan pesanan PO pada kuartal ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="p-2.5 bg-base-200/30 border-t border-base-300 text-xs flex justify-between font-bold">
                                        <span>Total PO Bersih:</span>
                                        <span class="font-mono text-primary">Rp {{ number_format($fulData['kpi']['total_po_val'], 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Daftar Faktur Penjualan Distributor (Selling Out) --}}
                                <div class="card bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col justify-between">
                                    <div>
                                        <div class="p-3 bg-base-200/50 border-b border-base-300 flex items-center justify-between">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="p-1 rounded-md bg-success/10 text-success shrink-0">
                                                    <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                                                </div>
                                                <h5 class="font-bold text-xs uppercase tracking-wider text-base-content/90 truncate">
                                                    Daftar Faktur Resmi Distributor (SO)
                                                </h5>
                                            </div>
                                            <span class="badge badge-success badge-outline badge-sm text-xs font-mono font-bold shrink-0 whitespace-nowrap">
                                                {{ count($fulData['invoices']) }} Faktur
                                            </span>
                                        </div>

                                        <div class="overflow-x-auto max-h-[350px]">
                                            <table class="table table-xs w-full">
                                                <thead class="sticky top-0 z-20 bg-base-200 text-base-content/80 uppercase text-xs border-b border-base-300 shadow-sm">
                                                    <tr>
                                                        <th class="whitespace-nowrap">No Faktur & Tgl</th>
                                                        <th class="whitespace-nowrap">Salesman / Depo</th>
                                                        <th class="text-right whitespace-nowrap">Netto Faktur</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-base-200 text-xs">
                                                    @forelse($fulData['invoices'] as $inv)
                                                        <tr class="hover:bg-base-200/40 transition-colors">
                                                            <td class="whitespace-nowrap">
                                                                <span class="font-mono font-bold text-xs text-base-content block">{{ $inv['invoice_no'] }}</span>
                                                                <span class="text-xs text-base-content/60 block">{{ $inv['formatted_date'] }}</span>
                                                            </td>
                                                            <td class="whitespace-nowrap">
                                                                <span class="font-bold text-xs text-base-content block">{{ $inv['slsname'] }}</span>
                                                                <span class="text-xs text-base-content/60 block">{{ $inv['total_sku'] }} SKU Varian</span>
                                                            </td>
                                                            <td class="text-right font-mono whitespace-nowrap">
                                                                <span class="font-bold text-xs text-success block">
                                                                    Rp {{ number_format($inv['netto'], 0, ',', '.') }}
                                                                </span>
                                                                <span class="text-xs text-base-content/60 block">
                                                                    {{ number_format($inv['total_ktn'], 1, ',', '.') }} KTN
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center py-6 text-xs text-base-content/50">
                                                                Belum ada faktur penjualan yang terbit di kuartal ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="p-2.5 bg-base-200/30 border-t border-base-300 text-xs flex justify-between font-bold">
                                        <span>Total Realisasi SO:</span>
                                        <span class="font-mono text-success">Rp {{ number_format($fulData['kpi']['total_so_val'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div> <!-- Close x-data container -->

                <div class="modal-action border-t pt-3">
                    <button type="button" wire:click="closeDetailModal" class="btn btn-neutral btn-sm">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Filter Wilayah --}}
    <dialog id="filter_modal" class="modal" wire:ignore.self>
        <livewire:rwo.filter-pencapaianrwo 
            :key="'filter-wilayah-' . $appliedKuartal . '-' . $appliedRegion . '-' . $appliedArea . '-' . $appliedSupervisor . '-' . $appliedDistributor"
            :appliedKuartal="$appliedKuartal" 
            :appliedRegion="$appliedRegion" 
            :appliedArea="$appliedArea" 
            :appliedSupervisor="$appliedSupervisor" 
            :appliedDistributor="$appliedDistributor" 
            :appliedStatusProgress="$appliedStatusProgress" 
            :appliedStatusSkb="$appliedStatusSkb" 
            :appliedStatusData="$appliedStatusData" 
            :appliedStatusReward="$appliedStatusReward" 
        />
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    {{-- Map Component --}}
    <livewire:rwo.pencapaianrwo-map />
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            window.addEventListener('close-filter-modal', () => {
                const filterModal = document.getElementById('filter_modal');
                if (filterModal && filterModal.open) {
                    filterModal.close();
                }
            });
        });
    </script>
@endpush
