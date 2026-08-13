<div class="flex-1 flex flex-col w-full h-full min-h-0">
    
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
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Pilih Region</label>
            <select wire:model.live="filterRegion" class="select select-bordered select-sm rounded-lg min-w-[200px]">
                <option value="">-- Pilih Region --</option>
                @foreach($listRegions as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Filter Area (Opsional)</label>
            <select wire:model.live="filterArea" class="select select-bordered select-sm rounded-lg min-w-[200px]" {{ empty($filterRegion) ? 'disabled' : '' }}>
                <option value="">-- Semua Area --</option>
                @if(isset($listAreas))
                    @foreach($listAreas as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Cari Salesman / Distributor</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik nama atau kode..." class="input input-bordered input-sm rounded-lg min-w-[200px]">
        </div>

        <div class="ml-auto">
            <div wire:loading class="text-xs font-semibold text-primary animate-pulse flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> Menghitung Data...
            </div>
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

        <div class="flex-1 overflow-auto custom-scrollbar js-table-scroll">
            <table class="table table-xs w-full border-collapse">
                <thead class="sticky top-0 z-20" style="background-color: white;">
                    @php
                        $headerColors = [
                            ['main' => 'bg-orange-100 text-orange-900', 'sub' => 'bg-orange-50 text-orange-800'],
                            ['main' => 'bg-blue-100 text-blue-900', 'sub' => 'bg-blue-50 text-blue-800'],
                            ['main' => 'bg-green-100 text-green-900', 'sub' => 'bg-green-50 text-green-800'],
                            ['main' => 'bg-purple-100 text-purple-900', 'sub' => 'bg-purple-50 text-purple-800'],
                            ['main' => 'bg-pink-100 text-pink-900', 'sub' => 'bg-pink-50 text-pink-800'],
                            ['main' => 'bg-teal-100 text-teal-900', 'sub' => 'bg-teal-50 text-teal-800'],
                        ];
                    @endphp

                    <!-- Header Baris 1: Grup Header Produk -->
                    <tr class="text-xs">
                        <th rowspan="2" class="border border-base-300 text-center font-bold sticky left-0 z-30 bg-base-200 text-base-content w-[40px] min-w-[40px] max-w-[40px]">No</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold sticky left-[40px] z-30 bg-base-200 text-base-content w-[150px] min-w-[150px] max-w-[150px] truncate">Area</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold sticky left-[190px] z-30 bg-base-200 text-base-content w-[100px] min-w-[100px] max-w-[100px] truncate">KD DIST</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content min-w-[150px] max-w-[200px] truncate">Distributor</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content w-[100px] min-w-[100px] whitespace-nowrap">Kode SE</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content min-w-[150px] max-w-[200px] truncate">Nama SE</th>
                        
                        <!-- Header INSENTIF VALUE -->
                        <th colspan="4" class="border border-base-300 text-center font-bold bg-indigo-100 text-indigo-900">
                            INSENTIF VALUE
                        </th>

                        @foreach($headers as $index => $h)
                            @php $color = $headerColors[$index % count($headerColors)]['main']; @endphp
                            <th colspan="4" class="border border-base-300 text-center font-bold {{ $color }}">
                                {{ $h->nama_header }}
                            </th>
                        @endforeach

                        <!-- Header Total Insentif VTKP -->
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content min-w-[120px]">
                            Total Insentif<br>VTKP
                        </th>

                        <!-- Header INSENTIF EC -->
                        <th colspan="7" class="border border-base-300 text-center font-bold bg-yellow-100 text-yellow-900">
                            3. Insentif Effective Call (EC)
                        </th>

                        <!-- Header INSENTIF IPT -->
                        <th colspan="4" class="border border-base-300 text-center font-bold bg-sky-100 text-sky-900">
                            4. Insentif IPT
                        </th>
                    </tr>
                    <!-- Header Baris 2: Target | Real | Growth -->
                    <tr class="text-xs">
                        <!-- Sub-header INSENTIF VALUE -->
                        <th class="border border-base-300 text-center font-semibold bg-indigo-50 text-indigo-800 w-24">Tgt</th>
                        <th class="border border-base-300 text-center font-semibold bg-indigo-50 text-indigo-800 w-24">Real</th>
                        <th class="border border-base-300 text-center font-semibold bg-indigo-50 text-indigo-800 w-20">%Ach</th>
                        <th class="border border-base-300 text-center font-semibold bg-indigo-50 text-indigo-800 w-24">Insentif</th>

                        @foreach($headers as $index => $h)
                            @php $subColor = $headerColors[$index % count($headerColors)]['sub']; @endphp
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-16">Tgt</th>
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-16">Real</th>
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-20">%Growth</th>
                            <th class="border border-base-300 text-center font-semibold {{ $subColor }} w-24">Insentif</th>
                        @endforeach
                        
                        <!-- Sub-header INSENTIF EC -->
                        <th class="border border-base-300 text-center font-semibold bg-yellow-50 text-yellow-800 w-16">F</th>
                        <th class="border border-base-300 text-center font-semibold bg-yellow-50 text-yellow-800 w-16">RO</th>
                        <th class="border border-base-300 text-center font-semibold bg-yellow-50 text-yellow-800 w-16">AC</th>
                        <th class="border border-base-300 text-center font-semibold bg-yellow-50 text-yellow-800 w-16">EC</th>
                        <th class="border border-base-300 text-center font-semibold bg-yellow-50 text-yellow-800 w-20">%EC</th>
                        <th class="border border-base-300 text-center font-semibold bg-yellow-50 text-yellow-800 w-20">EC Harian</th>
                        <th class="border border-base-300 text-center font-semibold bg-yellow-50 text-yellow-800 w-24">Insentif</th>

                        <!-- Sub-header INSENTIF IPT -->
                        <th class="border border-base-300 text-center font-semibold bg-sky-50 text-sky-800 w-16">Total SKU</th>
                        <th class="border border-base-300 text-center font-semibold bg-sky-50 text-sky-800 w-16">Total EC</th>
                        <th class="border border-base-300 text-center font-semibold bg-sky-50 text-sky-800 w-16">IPT</th>
                        <th class="border border-base-300 text-center font-semibold bg-sky-50 text-sky-800 w-24">Insentif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesmenData as $index => $row)
                        <tr class="hover:bg-base-200/50 transition-colors group text-xs">
                            {{-- Sticky kolom saja = z-10 --}}
                            <td class="border border-base-300 text-center bg-base-100 group-hover:bg-base-200/50 sticky left-0 z-10 w-[40px] min-w-[40px] max-w-[40px]">{{ $index + 1 }}</td>
                            <td class="border border-base-300 text-center bg-base-100 group-hover:bg-base-200/50 sticky left-[40px] z-10 w-[150px] min-w-[150px] max-w-[150px] truncate" title="{{ $row['area'] }}">{{ $row['area'] }}</td>
                            <td class="border border-base-300 text-center bg-base-100 group-hover:bg-base-200/50 sticky left-[190px] z-10 w-[100px] min-w-[100px] max-w-[100px] truncate" title="{{ $row['kd_dist'] }}">{{ $row['kd_dist'] }}</td>
                            <td class="border border-base-300 truncate min-w-[150px] max-w-[200px]" title="{{ $row['distributor'] }}">{{ $row['distributor'] }}</td>
                            <td class="border border-base-300 text-center font-mono w-[100px] min-w-[100px] whitespace-nowrap">{{ $row['kode_se'] }}</td>
                            <td class="border border-base-300 font-semibold truncate min-w-[150px] max-w-[200px] {{ stripos($row['nama_se'], 'vacant') !== false ? 'text-error' : '' }}" title="{{ $row['nama_se'] }}">
                                {{ $row['nama_se'] }}
                            </td>

                            <!-- Data INSENTIF VALUE -->
                            @php
                                $valAchColor = 'text-base-content';
                                if ($row['value_target'] == 0 && $row['value_real'] == 0) {
                                    $valAchText = '0%';
                                    $valAchColor = 'text-base-content/40';
                                } else {
                                    $valAchText = $row['value_ach'] . '%';
                                    if ($row['value_ach'] >= 100) $valAchColor = 'text-success font-bold';
                                    elseif ($row['value_ach'] >= 60) $valAchColor = 'text-warning font-bold';
                                    else $valAchColor = 'text-error';
                                }
                            @endphp
                            <td class="border border-base-300 text-right">{{ $row['value_target'] > 0 ? number_format($row['value_target'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-base-300 text-right font-semibold">{{ $row['value_real'] > 0 ? number_format($row['value_real'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-base-300 text-right {{ $valAchColor }}">
                                {{ $valAchText }}
                            </td>
                            <td class="border border-base-300 text-right font-bold {{ $row['value_insentif'] > 0 ? 'text-indigo-600' : 'text-base-content/40' }}">
                                {{ $row['value_insentif'] > 0 ? number_format($row['value_insentif'], 0, ',', '.') : '-' }}
                            </td>

                            @foreach($headers as $h)
                                @php
                                    $ach = $row['achievements'][$h->nama_header] ?? ['target' => 0, 'real' => 0, 'growth' => 0, 'insentif' => 0];
                                    
                                    // Pewarnaan Growth (Excel style)
                                    $growthColor = 'text-base-content';
                                    if ($ach['target'] == 0 && $ach['real'] == 0) {
                                        $growthText = '-100%';
                                        $growthColor = 'text-base-content/40';
                                    } else {
                                        $growthText = $ach['growth'] . '%';
                                        if ($ach['growth'] > 0) $growthColor = 'text-success font-bold';
                                        elseif ($ach['growth'] < 0) $growthColor = 'text-error';
                                    }
                                @endphp
                                <td class="border border-base-300 text-right">{{ $ach['target'] ?: '-' }}</td>
                                <td class="border border-base-300 text-right font-semibold">{{ $ach['real'] ?: '-' }}</td>
                                <td class="border border-base-300 text-right {{ $growthColor }}">
                                    {{ $growthText }}
                                </td>
                                <td class="border border-base-300 text-right font-bold {{ $ach['insentif'] > 0 ? 'text-info' : 'text-base-content/40' }}">
                                    {{ $ach['insentif'] > 0 ? number_format($ach['insentif'], 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                            
                            <!-- Total Insentif VTKP -->
                            <td class="border border-base-300 text-right font-bold {{ $row['total_insentif_vtkp'] > 0 ? 'text-success bg-success/10' : 'text-base-content/40' }}">
                                {{ $row['total_insentif_vtkp'] > 0 ? number_format($row['total_insentif_vtkp'], 0, ',', '.') : '-' }}
                            </td>

                            <!-- Data INSENTIF EC -->
                            <td class="border border-base-300 text-center">{{ $row['frekuensi'] }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($row['ro'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($row['ac'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($row['ec'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ $row['persen_ec'] }}%</td>
                            <td class="border border-base-300 text-right">{{ number_format($row['ec_harian'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right font-bold {{ $row['insentif_ec'] > 0 ? 'text-yellow-600' : 'text-base-content/40' }}">
                                {{ $row['insentif_ec'] > 0 ? number_format($row['insentif_ec'], 0, ',', '.') : '-' }}
                            </td>

                            <!-- Data INSENTIF IPT -->
                            <td class="border border-base-300 text-right">{{ number_format($row['ipt_sku'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($row['ipt_ec'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right font-semibold">{{ number_format($row['ipt'], 2, ',', '.') }}</td>
                            <td class="border border-base-300 text-right font-bold {{ $row['insentif_ipt'] > 0 ? 'text-sky-600' : 'text-base-content/40' }}">
                                {{ $row['insentif_ipt'] > 0 ? number_format($row['insentif_ipt'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        @if($filterBulan && $filterRegion)
                            <tr>
                                <td colspan="{{ 6 + 4 + (count($headers) * 4) + 7 + 1 }}" class="text-center py-10 text-base-content/50">
                                    Tidak ada data Salesman (SE) untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
                @if(count($salesmenData) > 0)
                <tfoot>
                    <tr class="font-bold text-xs">
                        <td colspan="3" class="border border-base-300 text-center sticky left-0 z-30 sticky bottom-0" style="background-color: #e5e7eb;">
                            GRAND TOTAL
                        </td>
                        <td colspan="3" class="border border-base-300 text-center sticky bottom-0" style="background-color: #e5e7eb;">
                            
                        </td>
                        
                        {{-- Grand Total INSENTIF VALUE --}}
                        @php
                            $gtValAchColor = 'text-base-content';
                            if ($grandTotalValue['ach'] >= 100) $gtValAchColor = 'text-success';
                            elseif ($grandTotalValue['ach'] >= 60) $gtValAchColor = 'text-warning';
                            else $gtValAchColor = 'text-error';
                        @endphp
                        <td class="border border-base-300 text-right bg-indigo-100 sticky bottom-0" style="background-color: #e0e7ff;">{{ number_format($grandTotalValue['target'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right bg-indigo-100 sticky bottom-0" style="background-color: #e0e7ff;">{{ number_format($grandTotalValue['real'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right bg-indigo-100 sticky bottom-0 {{ $gtValAchColor }}" style="background-color: #e0e7ff;">{{ $grandTotalValue['ach'] }}%</td>
                        <td class="border border-base-300 text-right bg-indigo-100 sticky bottom-0 font-bold text-indigo-600" style="background-color: #e0e7ff;">{{ number_format($grandTotalValue['insentif'], 0, ',', '.') }}</td>

                        @foreach($headers as $index => $h)
                            @php
                                $gt = $grandTotals[$h->nama_header] ?? ['target' => 0, 'real' => 0, 'growth' => 0, 'insentif' => 0];
                                $gtGrowthColor = 'text-base-content';
                                if ($gt['growth'] > 0) $gtGrowthColor = 'text-success';
                                elseif ($gt['growth'] < 0) $gtGrowthColor = 'text-error';

                                $bgColors = ['#fed7aa', '#bfdbfe', '#bbf7d0', '#e9d5ff', '#fbcfe8', '#ccfbf1'];
                                $bgColor = $bgColors[$index % 6];
                            @endphp
                            <td class="border border-base-300 text-right sticky bottom-0" style="background-color: {{ $bgColor }};">{{ number_format($gt['target'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right sticky bottom-0" style="background-color: {{ $bgColor }};">{{ number_format($gt['real'], 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right sticky bottom-0 {{ $gtGrowthColor }}" style="background-color: {{ $bgColor }};">{{ $gt['growth'] }}%</td>
                            <td class="border border-base-300 text-right sticky bottom-0 font-bold text-info" style="background-color: {{ $bgColor }};">{{ number_format($gt['insentif'], 0, ',', '.') }}</td>
                        @endforeach
                        <!-- Grand Total: Total Insentif VTKP -->
                        <td class="border border-base-300 text-right sticky bottom-0 font-bold text-success" style="background-color: #d1fae5;">
                            {{ number_format($grandTotalVtkp, 0, ',', '.') }}
                        </td>

                        <!-- Grand Total: INSENTIF EC -->
                        <td class="border border-base-300 text-center sticky bottom-0" style="background-color: #fef08a;"></td>
                        <td class="border border-base-300 text-right sticky bottom-0" style="background-color: #fef08a;">{{ number_format($grandTotalEc['ro'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right sticky bottom-0" style="background-color: #fef08a;">{{ number_format($grandTotalEc['ac'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right sticky bottom-0" style="background-color: #fef08a;">{{ number_format($grandTotalEc['ec'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right sticky bottom-0" style="background-color: #fef08a;">{{ $grandTotalEc['persen_ec'] }}%</td>
                        <td class="border border-base-300 text-right sticky bottom-0" style="background-color: #fef08a;">{{ number_format($grandTotalEc['ec_harian'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right sticky bottom-0 font-bold text-yellow-700" style="background-color: #fef08a;">{{ number_format($grandTotalEc['insentif'], 0, ',', '.') }}</td>

                        <!-- Grand Total: INSENTIF IPT -->
                        <td class="border border-base-300 text-right sticky bottom-0" style="background-color: #bae6fd;">{{ number_format($grandTotalIpt['sku'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right sticky bottom-0" style="background-color: #bae6fd;">{{ number_format($grandTotalIpt['ec'], 0, ',', '.') }}</td>
                        <td class="border border-base-300 text-right sticky bottom-0 font-bold" style="background-color: #bae6fd;">{{ number_format($grandTotalIpt['ipt'], 2, ',', '.') }}</td>
                        <td class="border border-base-300 text-right sticky bottom-0 font-bold text-sky-700" style="background-color: #bae6fd;">{{ number_format($grandTotalIpt['insentif'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
