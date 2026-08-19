<div x-data="{ showInfoModal: false }" class="flex-1 flex flex-col w-full h-full min-h-0">
    
    <!-- Filter Section -->
    <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-4 mb-4 shrink-0 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Pilih Bulan</label>
            <select wire:model.live="filterBulan" class="select select-bordered select-sm rounded-lg min-w-[150px]">
                <option value="">-- Pilih Bulan --</option>
                @foreach($listBulan as $b)
                    <option value="{{ $b }}">{{ $b }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">
                Pilih Region
                @if(in_array($accessLevel ?? '', ['region','area']))
                    <span class="badge badge-warning badge-xs ml-1">Terbatas</span>
                @endif
            </label>
            <select wire:model.live="filterRegion"
                class="select select-bordered select-sm rounded-lg min-w-[200px] {{ in_array($accessLevel ?? '', ['region','area']) && count($listRegions) === 1 ? 'opacity-70 cursor-not-allowed' : '' }}"
                @if(in_array($accessLevel ?? '', ['region','area']) && count($listRegions) === 1) disabled @endif>
                @if(!in_array($accessLevel ?? '', ['region','area']))
                    <option value="">-- Pilih Region --</option>
                @endif
                @foreach($listRegions as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">
                Filter Area (Opsional)
                @if(($accessLevel ?? '') === 'area')
                    <span class="badge badge-warning badge-xs ml-1">Terbatas</span>
                @endif
            </label>
            <select wire:model.live="filterArea"
                class="select select-bordered select-sm rounded-lg min-w-[200px] {{ ($accessLevel ?? '') === 'area' && count($listAreas) === 1 ? 'opacity-70 cursor-not-allowed' : '' }}"
                @if(empty($filterRegion) || (($accessLevel ?? '') === 'area' && count($listAreas) === 1)) disabled @endif>
                @if(($accessLevel ?? '') !== 'area')
                    <option value="">-- Semua Area --</option>
                @endif
                @if(isset($listAreas))
                    @foreach($listAreas as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Cari SPV / Distributor</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik nama atau kode..." class="input input-bordered input-sm rounded-lg min-w-[200px]">
        </div>

        <div class="ml-auto flex items-center gap-3">
            <div wire:loading class="text-xs font-semibold text-primary animate-pulse flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> Menghitung Data SPV...
            </div>
            
            <button @click="showInfoModal = true" class="btn btn-sm btn-ghost rounded-lg border border-base-300 text-base-content/70 hover:text-info hover:border-info/50 hover:bg-info/10">
                <x-heroicon-o-information-circle class="w-4 h-4" />
                Mekanisme Insentif
            </button>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="flex-1 min-h-0 bg-base-100 rounded-xl shadow-xl border border-base-300 overflow-hidden flex flex-col relative">
        @if(!$filterBulan || !$filterRegion)
            <div class="absolute inset-0 flex items-center justify-center bg-base-200/50 backdrop-blur-sm z-10">
                <div class="text-center">
                    <x-heroicon-o-funnel class="w-12 h-12 text-base-content/30 mx-auto mb-2" />
                    <p class="text-base-content/60 font-semibold">Silakan pilih Bulan dan Region terlebih dahulu</p>
                </div>
            </div>
        @endif

        <style>
            .table-border-fix {
                border-collapse: separate !important;
                border-spacing: 0 !important;
                border-top: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
                border-left: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
            }
            .table-border-fix th, .table-border-fix td {
                border-top: none !important;
                border-left: none !important;
                border-bottom: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
                border-right: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
                background-clip: padding-box !important;
            }
            .table-border-fix th.sticky-edge, .table-border-fix td.sticky-edge {
                border-right: 2px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
            }
        </style>
        <div class="flex-1 overflow-auto custom-scrollbar">
            <table class="table table-xs w-full table-border-fix">
                <thead class="sticky top-0 z-20" style="background-color: white;">
                    <tr>
                        <th rowspan="2" class="border border-base-300 text-center font-bold sticky left-0 z-30 bg-base-200 text-base-content w-[150px] min-w-[150px] max-w-[150px] truncate">Area</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold sticky left-[150px] z-30 bg-base-200 text-base-content w-[200px] min-w-[200px] max-w-[200px] truncate">Distributor</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold sticky left-[350px] z-30 bg-base-200 text-base-content w-[120px] min-w-[120px] max-w-[120px] truncate">Cabang</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold sticky left-[470px] z-30 bg-base-200 text-base-content w-[150px] min-w-[150px] max-w-[150px] truncate sticky-edge">Nama Supervisor</th>
                        
                        <!-- Header INSENTIF VALUE -->
                        <th colspan="5" class="border border-base-300 text-center font-bold bg-fuchsia-300 text-fuchsia-900">
                            1. Insentif Value Selling Out (Reguler)
                        </th>
                        
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-fuchsia-400 text-fuchsia-950 min-w-[120px]">
                            INS SO
                        </th>

                        <!-- Header VTKP -->
                        <th colspan="{{ count($headers) * 4 + 1 }}" class="border border-base-300 text-center font-bold bg-indigo-100 text-indigo-900">
                            2. Insentif Growth Qty Produk Fokus (VTKP)
                        </th>
                        
                        <!-- New Columns for RWO, IPT and Totals -->
                        <th colspan="2" class="border border-base-300 text-center font-bold bg-orange-100 text-orange-900">RWO</th>
                        <th colspan="4" class="border border-base-300 text-center font-bold bg-orange-200 text-orange-950">Total RWO</th>
                        <th colspan="2" class="border border-base-300 text-center font-bold bg-cyan-100 text-cyan-900">IPT</th>
                        <th colspan="4" class="border border-base-300 text-center font-bold bg-cyan-200 text-cyan-950">Total IPT</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-emerald-200 text-emerald-900 min-w-[120px]">Total Insentif<br>All Program</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-yellow-200 text-yellow-900 min-w-[120px]">30% Tabungan</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-blue-200 text-blue-900 min-w-[120px]">70% Transfer</th>
                    </tr>
                    
                    @php
                        $headerColors = [
                            ['main' => 'bg-emerald-100 text-emerald-900', 'sub' => 'bg-emerald-50 text-emerald-800'],
                            ['main' => 'bg-blue-100 text-blue-900', 'sub' => 'bg-blue-50 text-blue-800'],
                            ['main' => 'bg-orange-100 text-orange-900', 'sub' => 'bg-orange-50 text-orange-800'],
                            ['main' => 'bg-rose-100 text-rose-900', 'sub' => 'bg-rose-50 text-rose-800'],
                            ['main' => 'bg-purple-100 text-purple-900', 'sub' => 'bg-purple-50 text-purple-800'],
                            ['main' => 'bg-teal-100 text-teal-900', 'sub' => 'bg-teal-50 text-teal-800'],
                        ];
                    @endphp

                    <tr>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Target SO</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Target SO Reguler</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Aktual SO</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Pencapaian</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 w-[80px]">%</th>

                        @foreach($headers as $index => $h)
                            @php $subColor = $headerColors[$index % count($headerColors)]['sub']; @endphp
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-24">{{ $h->nama_header }}</th>
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-24">Real</th>
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-20">%Growth</th>
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-24">Insentif</th>
                        @endforeach
                        
                        <th class="border border-base-300 text-center font-bold bg-indigo-200 text-indigo-900 min-w-[120px]">
                            Total Insentif VTKP
                        </th>
                        
                        <!-- Subheaders for RWO and IPT -->
                        <th class="border border-base-300 text-center font-bold bg-orange-50 text-orange-800 min-w-[100px]">RWO (Peserta)</th>
                        <th class="border border-base-300 text-center font-bold bg-orange-50 text-orange-800 min-w-[100px]">RWO (Achieve)</th>
                        <th class="border border-base-300 text-center font-bold bg-orange-100 text-orange-900 min-w-[100px]">Peserta</th>
                        <th class="border border-base-300 text-center font-bold bg-orange-100 text-orange-900 min-w-[100px]">Achieve</th>
                        <th class="border border-base-300 text-center font-bold bg-orange-100 text-orange-900 min-w-[80px]">%</th>
                        <th class="border border-base-300 text-center font-bold bg-orange-100 text-orange-900 min-w-[100px]">Insentif</th>
                        
                        <th class="border border-base-300 text-center font-bold bg-cyan-50 text-cyan-800 min-w-[100px]">SKU</th>
                        <th class="border border-base-300 text-center font-bold bg-cyan-50 text-cyan-800 min-w-[100px]">EC</th>
                        <th class="border border-base-300 text-center font-bold bg-cyan-100 text-cyan-900 min-w-[100px]">Total SKU</th>
                        <th class="border border-base-300 text-center font-bold bg-cyan-100 text-cyan-900 min-w-[100px]">Total EC</th>
                        <th class="border border-base-300 text-center font-bold bg-cyan-100 text-cyan-900 min-w-[80px]">IPT</th>
                        <th class="border border-base-300 text-center font-bold bg-cyan-100 text-cyan-900 min-w-[100px]">Insentif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spvData as $spvCode => $spv)
                        @foreach($spv['cabangs'] as $cabang => $cabData)
                            @foreach($cabData['distributors'] as $idx => $dist)
                                <tr class="{{ $dist['distributor_name'] === 'VACANT' ? 'bg-red-50 text-red-600 font-bold' : 'hover:bg-base-200/50' }}" wire:key="spv-{{ md5($spv['supervisor_name'].$dist['distributor_code']) }}">
                                    <td class="border border-base-300 {{ $dist['distributor_name'] === 'VACANT' ? 'bg-red-50' : 'bg-base-100' }} text-xs truncate w-[150px] min-w-[150px] max-w-[150px] sticky left-0 z-10" title="{{ $dist['area_name'] }}">
                                        {{ $dist['area_name'] }}
                                    </td>
                                    
                                    <td class="border border-base-300 {{ $dist['distributor_name'] === 'VACANT' ? 'bg-red-50' : 'bg-base-100' }} text-xs truncate w-[200px] min-w-[200px] max-w-[200px] sticky left-[150px] z-10" title="{{ $dist['distributor_name'] }}">
                                        {{ $dist['distributor_name'] }}
                                    </td>
                                    
                                    <td class="border border-base-300 {{ $dist['distributor_name'] === 'VACANT' ? 'bg-red-50' : 'bg-base-100' }} text-xs truncate w-[120px] min-w-[120px] max-w-[120px] sticky left-[350px] z-10" title="{{ $dist['cabang'] }}">
                                        {{ $dist['cabang'] }}
                                    </td>

                                    @if($idx === 0 && $cabang === array_key_first($spv['cabangs']))
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 {{ $dist['distributor_name'] === 'VACANT' ? 'bg-red-50' : 'bg-base-100' }} font-bold text-xs truncate w-[150px] min-w-[150px] max-w-[150px] sticky left-[470px] z-10 uppercase sticky-edge" title="{{ $spv['supervisor_name'] }}">
                                            {{ $spv['supervisor_name'] }}
                                        </td>
                                    @endif
                                    
                                    <td class="border border-base-300 text-right font-medium">
                                        {{ number_format($dist['target_so'], 0, ',', '.') }}
                                    </td>

                                    @if($idx === 0 && $cabang === array_key_first($spv['cabangs']))
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $spv['total_target_reguler'] == 0 ? 'bg-red-50 text-red-400' : 'bg-base-100/50' }}">
                                            {{ number_format($spv['total_target_reguler'], 0, ',', '.') }}
                                        </td>
                                    @endif

                                    <td class="border border-base-300 text-right font-medium">
                                        {{ number_format($dist['aktual_so'], 0, ',', '.') }}
                                    </td>

                                    @if($idx === 0 && $cabang === array_key_first($spv['cabangs']))
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold bg-base-100/50">
                                            {{ number_format($spv['total_aktual_so'], 0, ',', '.') }}
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold {{ $spv['pencapaian_persen'] >= 100 ? 'text-success' : 'text-error' }} bg-base-100/50">
                                            {{ number_format($spv['pencapaian_persen'], 0) }}%
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold text-indigo-600 bg-base-100/50">
                                            {{ number_format($spv['ins_so'], 0, ',', '.') }}
                                        </td>
                                    @endif

                                    @foreach($headers as $h)
                                        @php
                                            $ach = $cabData['vtkp_achievements'][$h->nama_header] ?? ['target' => 0, 'real' => 0, 'growth' => 0, 'insentif' => 0];
                                            $achColor = 'text-base-content';
                                            if ($ach['target'] == 0 && $ach['real'] == 0) {
                                                $achText = '0%';
                                                $achColor = 'text-base-content/40';
                                            } else {
                                                $achText = round($ach['growth']) . '%';
                                                if ($ach['growth'] >= 30) $achColor = 'text-success font-bold';
                                                elseif ($ach['growth'] >= 10) $achColor = 'text-warning font-bold';
                                                else $achColor = 'text-error';
                                            }
                                        @endphp
                                        @if($idx === 0)
                                            <td rowspan="{{ $cabData['rowspan'] }}" class="border border-base-300 text-right {{ $ach['target'] == 0 ? 'bg-red-50 text-red-400' : 'bg-base-100/50' }}">
                                                {{ $ach['target'] > 0 ? number_format($ach['target'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td rowspan="{{ $cabData['rowspan'] }}" class="border border-base-300 text-right font-semibold bg-base-100/50">
                                                {{ $ach['real'] > 0 ? number_format($ach['real'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td rowspan="{{ $cabData['rowspan'] }}" class="border border-base-300 text-right {{ $achColor }} bg-base-100/50">
                                                {{ $achText }}
                                            </td>
                                            <td rowspan="{{ $cabData['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $ach['insentif'] > 0 ? 'text-indigo-600' : 'text-base-content/40' }} bg-base-100/50">
                                                {{ $ach['insentif'] > 0 ? number_format($ach['insentif'], 0, ',', '.') : '-' }}
                                            </td>
                                        @endif
                                    @endforeach
                                    
                                    @if($idx === 0 && $cabang === array_key_first($spv['cabangs']))
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $spv['total_insentif_vtkp'] > 0 ? 'text-success bg-success/10' : 'text-base-content/40 bg-base-100/50' }}">
                                            {{ $spv['total_insentif_vtkp'] > 0 ? number_format($spv['total_insentif_vtkp'], 0, ',', '.') : '-' }}
                                        </td>
                                    @endif
                                    
                                    <td class="border border-base-300 text-center font-medium">
                                        {{ $dist['rwo_peserta'] > 0 ? number_format($dist['rwo_peserta'], 0, ',', '.') : '-' }}
                                    </td>
                                    
                                    <td class="border border-base-300 text-center font-medium">
                                        {{ $dist['rwo_achieve'] > 0 ? number_format($dist['rwo_achieve'], 0, ',', '.') : '-' }}
                                    </td>
                                    
                                    @if($idx === 0 && $cabang === array_key_first($spv['cabangs']))
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold bg-base-100/50">
                                            {{ $spv['total_rwo_peserta'] > 0 ? number_format($spv['total_rwo_peserta'], 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold bg-base-100/50">
                                            {{ $spv['total_rwo_achieve'] > 0 ? number_format($spv['total_rwo_achieve'], 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold {{ $spv['rwo_achieve_pct'] >= 80 ? 'text-success' : 'text-error' }} bg-base-100/50">
                                            {{ number_format($spv['rwo_achieve_pct'], 1) }}%
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $spv['insentif_rwo'] > 0 ? 'text-orange-600 bg-orange-50' : 'text-base-content/40 bg-base-100/50' }}">
                                            {{ $spv['insentif_rwo'] > 0 ? number_format($spv['insentif_rwo'], 0, ',', '.') : '-' }}
                                        </td>
                                    @endif

                                    <td class="border border-base-300 text-center font-medium">
                                        {{ $dist['ipt_sku'] > 0 ? number_format($dist['ipt_sku'], 0, ',', '.') : '-' }}
                                    </td>
                                    
                                    <td class="border border-base-300 text-center font-medium">
                                        {{ $dist['ipt_ec'] > 0 ? number_format($dist['ipt_ec'], 0, ',', '.') : '-' }}
                                    </td>

                                    @if($idx === 0 && $cabang === array_key_first($spv['cabangs']))
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold bg-base-100/50">
                                            {{ $spv['total_ipt_sku'] > 0 ? number_format($spv['total_ipt_sku'], 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold bg-base-100/50">
                                            {{ $spv['total_ipt_ec'] > 0 ? number_format($spv['total_ipt_ec'], 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold {{ $spv['ipt'] >= 5 ? 'text-success' : 'text-error' }} bg-base-100/50">
                                            {{ number_format($spv['ipt'], 1) }}
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $spv['insentif_ipt'] > 0 ? 'text-cyan-600 bg-cyan-50' : 'text-base-content/40 bg-base-100/50' }}">
                                            {{ $spv['insentif_ipt'] > 0 ? number_format($spv['insentif_ipt'], 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $spv['total_all_insentif'] > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-base-content/40 bg-base-100/50' }}">
                                            {{ $spv['total_all_insentif'] > 0 ? number_format($spv['total_all_insentif'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $spv['tabungan_30'] > 0 ? 'text-yellow-700 bg-yellow-50' : 'text-base-content/40 bg-base-100/50' }}">
                                            {{ $spv['tabungan_30'] > 0 ? number_format($spv['tabungan_30'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $spv['transfer_70'] > 0 ? 'text-blue-700 bg-blue-50' : 'text-base-content/40 bg-base-100/50' }}">
                                            {{ $spv['transfer_70'] > 0 ? number_format($spv['transfer_70'], 0, ',', '.') : '-' }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-base-content/50">
                                <div class="flex flex-col items-center justify-center">
                                    <x-heroicon-o-inbox class="w-12 h-12 mb-2 opacity-30" />
                                    <p>Belum ada data untuk kriteria tersebut.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($spvData) > 0)
                <tfoot class="sticky bottom-0 z-40">
                    <tr>
                        <td colspan="4" class="border border-base-300 bg-base-300 text-right font-bold text-base-content px-4 py-2 sticky left-0 z-40 sticky-edge">
                            GRAND TOTAL
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-base-content py-2">
                            {{ number_format($grandTotal['target_so'], 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-base-content py-2">
                            {{ number_format($grandTotal['target_so'], 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-base-content py-2">
                            {{ number_format($grandTotal['aktual_so'], 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-base-content py-2">
                            {{ number_format($grandTotal['aktual_so'], 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold {{ $grandTotal['pencapaian_persen'] >= 100 ? 'text-success' : 'text-error' }} py-2">
                            {{ number_format($grandTotal['pencapaian_persen'], 0) }}%
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-success py-2">
                            {{ number_format($grandTotal['ins_so'], 0, ',', '.') }}
                        </td>

                        @foreach($headers as $h)
                            @php
                                $gtAch = $grandTotal['vtkp'][$h->nama_header] ?? ['target' => 0, 'real' => 0, 'growth' => 0, 'insentif' => 0];
                            @endphp
                            <td class="border border-base-300 bg-base-300 text-right font-bold text-base-content py-2">
                                {{ $gtAch['target'] > 0 ? number_format($gtAch['target'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-base-300 bg-base-300 text-right font-bold text-base-content py-2">
                                {{ $gtAch['real'] > 0 ? number_format($gtAch['real'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border border-base-300 bg-base-300 text-center font-bold {{ $gtAch['growth'] >= 0 ? 'text-success' : 'text-error' }} py-2">
                                {{ round($gtAch['growth']) }}%
                            </td>
                            <td class="border border-base-300 bg-base-300 text-right font-bold text-indigo-600 py-2">
                                {{ $gtAch['insentif'] > 0 ? number_format($gtAch['insentif'], 0, ',', '.') : '-' }}
                            </td>
                        @endforeach
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-success py-2">
                            {{ $grandTotal['total_insentif_vtkp'] > 0 ? number_format($grandTotal['total_insentif_vtkp'], 0, ',', '.') : '-' }}
                        </td>
                        
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['rwo_peserta'] > 0 ? number_format($grandTotal['rwo_peserta'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['rwo_achieve'] > 0 ? number_format($grandTotal['rwo_achieve'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['rwo_peserta'] > 0 ? number_format($grandTotal['rwo_peserta'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['rwo_achieve'] > 0 ? number_format($grandTotal['rwo_achieve'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold {{ $grandTotal['rwo_achieve_pct'] >= 80 ? 'text-success' : 'text-error' }} py-2">
                            {{ number_format($grandTotal['rwo_achieve_pct'], 1) }}%
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-orange-600 py-2">
                            {{ $grandTotal['insentif_rwo'] > 0 ? number_format($grandTotal['insentif_rwo'], 0, ',', '.') : '-' }}
                        </td>
                        
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['ipt_sku'] > 0 ? number_format($grandTotal['ipt_sku'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['ipt_ec'] > 0 ? number_format($grandTotal['ipt_ec'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['ipt_sku'] > 0 ? number_format($grandTotal['ipt_sku'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold text-base-content py-2">
                            {{ $grandTotal['ipt_ec'] > 0 ? number_format($grandTotal['ipt_ec'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-center font-bold {{ $grandTotal['ipt'] >= 5 ? 'text-success' : 'text-error' }} py-2">
                            {{ number_format($grandTotal['ipt'], 1) }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-cyan-600 py-2">
                            {{ $grandTotal['insentif_ipt'] > 0 ? number_format($grandTotal['insentif_ipt'], 0, ',', '.') : '-' }}
                        </td>
                        
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-emerald-700 py-2">
                            {{ $grandTotal['total_all_insentif'] > 0 ? number_format($grandTotal['total_all_insentif'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-yellow-700 py-2">
                            {{ $grandTotal['tabungan_30'] > 0 ? number_format($grandTotal['tabungan_30'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="border border-base-300 bg-base-300 text-right font-bold text-blue-700 py-2">
                            {{ $grandTotal['transfer_70'] > 0 ? number_format($grandTotal['transfer_70'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Modal Mekanisme Insentif -->
    <div x-show="showInfoModal" x-cloak class="fixed z-[100] inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showInfoModal" x-transition.opacity class="fixed inset-0 bg-base-100/80 backdrop-blur-sm transition-opacity" @click="showInfoModal = false" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showInfoModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-base-100 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-base-300">
                
                <div class="bg-base-200/50 px-6 py-4 border-b border-base-300 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-base-content flex items-center gap-2" id="modal-title">
                        <x-heroicon-o-information-circle class="w-6 h-6 text-info" />
                        Mekanisme Perhitungan Insentif SPV (Supervisor)
                    </h3>
                    <button @click="showInfoModal = false" class="btn btn-sm btn-circle btn-ghost">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-6 py-6 overflow-y-auto custom-scrollbar space-y-6" style="max-height: calc(100vh - 12rem);">
                    
                    <!-- 1. Insentif SO / Value -->
                    <div class="bg-base-200/30 rounded-xl p-4 border border-base-300">
                        <h4 class="font-bold text-md mb-2 flex items-center gap-2 text-primary">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-xs">1</span>
                            Insentif SO (Sell Out / Value Reguler)
                        </h4>
                        <p class="text-sm mb-3">Dihitung dari <span class="font-bold">Total Aktual SO</span> dibagi <span class="font-bold">Total Target Reguler</span> (gabungan seluruh cabang yang dinaungi SPV). <br/>Penentuan insentif bergantung pada skala Target Reguler yang ditetapkan.</p>
                        <div class="overflow-x-auto">
                            <table class="table table-sm table-zebra w-full text-xs">
                                <thead>
                                    <tr class="bg-base-300/50">
                                        <th>Target Reguler SPV</th>
                                        <th>Ach &ge; 120%</th>
                                        <th>Ach &ge; 110%</th>
                                        <th>Ach &ge; 100%</th>
                                        <th>Ach &ge; 90%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-semibold">&ge; Rp 2 Milyar</td>
                                        <td class="text-success font-bold">Rp 2.500.000</td>
                                        <td class="text-success">Rp 2.250.000</td>
                                        <td class="text-success">Rp 2.000.000</td>
                                        <td class="text-warning">Rp 500.000</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">&ge; Rp 1 Milyar</td>
                                        <td class="text-success font-bold">Rp 2.250.000</td>
                                        <td class="text-success">Rp 2.000.000</td>
                                        <td class="text-success">Rp 1.750.000</td>
                                        <td class="text-warning">Rp 400.000</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">&lt; Rp 1 Milyar</td>
                                        <td class="text-success font-bold">Rp 2.000.000</td>
                                        <td class="text-success">Rp 1.750.000</td>
                                        <td class="text-success">Rp 1.500.000</td>
                                        <td class="text-warning">Rp 300.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Insentif VTKP -->
                    <div class="bg-base-200/30 rounded-xl p-4 border border-base-300">
                        <h4 class="font-bold text-md mb-2 flex items-center gap-2 text-primary">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-xs">2</span>
                            Insentif VTKP (Volume Transaksi Kelompok Produk per Cabang)
                        </h4>
                        <p class="text-sm mb-3">Diukur dari selisih Aktual (Real) dikurangi Target. <br/><span class="badge badge-error badge-sm">Syarat Mutlak</span> Pencapaian Insentif SO (gabungan SPV) harus <span class="font-bold">&ge; 80%</span>. Jika &lt; 80%, VTKP SPV hangus.</p>
                        <div class="overflow-x-auto">
                            <table class="table table-sm table-zebra w-full text-xs">
                                <thead>
                                    <tr class="bg-base-300/50">
                                        <th>Growth VTKP per Cabang</th>
                                        <th>Nilai Insentif per Selisih Pcs (Real - Target)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-semibold">&ge; 30%</td>
                                        <td class="text-success font-bold">Rp 600 / Pcs</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">&ge; 20%</td>
                                        <td class="text-success font-bold">Rp 400 / Pcs</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">&ge; 10%</td>
                                        <td class="text-success font-bold">Rp 250 / Pcs</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Insentif RWO -->
                    <div class="bg-base-200/30 rounded-xl p-4 border border-base-300">
                        <h4 class="font-bold text-md mb-2 flex items-center gap-2 text-primary">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-xs">3</span>
                            Insentif RWO (Reward Outlet)
                        </h4>
                        <p class="text-sm mb-3">Dihitung berdasarkan persentase RWO Achieve (Total Capai / Total Potensi Peserta &times; 100) secara keseluruhan di wilayah SPV.</p>
                        <div class="overflow-x-auto">
                            <table class="table table-sm table-zebra w-full text-xs">
                                <thead>
                                    <tr class="bg-base-300/50">
                                        <th>Persentase RWO Achieve</th>
                                        <th>Insentif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-semibold">&ge; 90%</td>
                                        <td class="text-success font-bold">Rp 900.000</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">&ge; 80%</td>
                                        <td class="text-success font-bold">Rp 700.000</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">&ge; 70%</td>
                                        <td class="text-success font-bold">Rp 500.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. Insentif IPT -->
                    <div class="bg-base-200/30 rounded-xl p-4 border border-base-300">
                        <h4 class="font-bold text-md mb-2 flex items-center gap-2 text-primary">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/20 text-xs">4</span>
                            Insentif Item Per Transaction (IPT)
                        </h4>
                        <p class="text-sm mb-3">Dihitung dari rata-rata gabungan (Total SKU dibagi Total Kunjungan Efektif / EC) di wilayah SPV.</p>
                        <div class="overflow-x-auto">
                            <table class="table table-sm table-zebra w-full text-xs">
                                <thead>
                                    <tr class="bg-base-300/50">
                                        <th>Nilai IPT Rata-rata Gabungan</th>
                                        <th>Insentif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="font-semibold">&ge; 12</td><td class="text-success font-bold">Rp 600.000</td></tr>
                                    <tr><td class="font-semibold">&ge; 8</td><td class="text-success font-bold">Rp 500.000</td></tr>
                                    <tr><td class="font-semibold">&ge; 7</td><td class="text-success font-bold">Rp 250.000</td></tr>
                                    <tr><td class="font-semibold">&ge; 5</td><td class="text-success font-bold">Rp 150.000</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 5. Pembagian Insentif (Tabungan & Transfer) -->
                    <div class="bg-info/10 rounded-xl p-4 border border-info/20">
                        <h4 class="font-bold text-md mb-2 flex items-center gap-2 text-info">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-info/20 text-xs">5</span>
                            Sistem Pembayaran (30% Tabungan / 70% Transfer)
                        </h4>
                        <p class="text-sm">Dari <span class="font-bold">Total Seluruh Insentif</span> (SO + VTKP + RWO + IPT), sistem pencairannya adalah sebagai berikut: <br/><br/>
                        - <span class="font-bold text-warning">30% Tabungan:</span> Ditahan dan dicairkan pada periode tertentu sesuai kebijakan perusahaan.<br/>
                        - <span class="font-bold text-success">70% Transfer:</span> Take Home Pay (THP) yang langsung dicairkan pada periode berjalan.</p>
                    </div>

                </div>
                <div class="bg-base-200/50 px-6 py-4 border-t border-base-300 flex justify-end">
                    <button @click="showInfoModal = false" type="button" class="btn btn-primary rounded-xl px-8 shadow-sm">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
</div>
