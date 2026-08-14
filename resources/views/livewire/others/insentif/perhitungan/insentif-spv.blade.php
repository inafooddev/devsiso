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
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Cari SPV / Distributor</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik nama atau kode..." class="input input-bordered input-sm rounded-lg min-w-[200px]">
        </div>

        <div class="ml-auto">
            <div wire:loading class="text-xs font-semibold text-primary animate-pulse flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> Menghitung Data SPV...
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

        <div class="flex-1 overflow-auto custom-scrollbar">
            <table class="table table-xs w-full border-collapse">
                <thead class="sticky top-0 z-20" style="background-color: white;">
                    <tr>
                        <th class="border-x border-t border-base-300 border-b-transparent text-center font-bold sticky left-0 z-30 bg-base-200 text-base-content w-[150px] min-w-[150px] max-w-[150px] truncate">Area</th>
                        <th class="border-x border-t border-base-300 border-b-transparent text-center font-bold sticky left-[150px] z-30 bg-base-200 text-base-content w-[200px] min-w-[200px] max-w-[200px] truncate">Distributor</th>
                        <th class="border-x border-t border-base-300 border-b-transparent text-center font-bold sticky left-[350px] z-30 bg-base-200 text-base-content w-[120px] min-w-[120px] max-w-[120px] truncate">Cabang</th>
                        <th class="border-l border-t border-r-2 !border-r-gray-300 border-base-300 border-b-transparent text-center font-bold sticky left-[470px] z-30 bg-base-200 text-base-content w-[150px] min-w-[150px] max-w-[150px] truncate">Nama Supervisor</th>
                        
                        <!-- Header INSENTIF VALUE -->
                        <th colspan="5" class="border border-base-300 text-center font-bold bg-fuchsia-300 text-fuchsia-900">
                            1. Insentif Value Selling Out (Reguler)
                        </th>
                        
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-fuchsia-400 text-fuchsia-950 min-w-[120px]">
                            INS SO
                        </th>

                        <!-- Header VTKP -->
                        <th colspan="{{ count($headers) * 4 }}" class="border border-base-300 text-center font-bold bg-indigo-100 text-indigo-900">
                            2. Insentif Growth Qty Produk Fokus (VTKP)
                        </th>
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
                        <th class="border-x border-b border-base-300 border-t-transparent sticky left-0 z-30 bg-base-200 w-[150px] min-w-[150px] max-w-[150px]"></th>
                        <th class="border-x border-b border-base-300 border-t-transparent sticky left-[150px] z-30 bg-base-200 w-[200px] min-w-[200px] max-w-[200px]"></th>
                        <th class="border-x border-b border-base-300 border-t-transparent sticky left-[350px] z-30 bg-base-200 w-[120px] min-w-[120px] max-w-[120px]"></th>
                        <th class="border-l border-b border-r-2 !border-r-gray-300 border-base-300 border-t-transparent sticky left-[470px] z-30 bg-base-200 w-[150px] min-w-[150px] max-w-[150px]"></th>

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
                    </tr>
                </thead>
                <tbody>
                    @forelse($spvData as $spv)
                        @foreach($spv['distributors'] as $idx => $dist)
                            <tr class="hover:bg-base-200/50" wire:key="spv-{{ md5($spv['supervisor_code'].$dist['distributor_code']) }}">
                                <td class="border border-base-300 bg-base-100 text-xs truncate w-[150px] min-w-[150px] max-w-[150px] sticky left-0 z-10" title="{{ $dist['area_name'] }}">
                                    {{ $dist['area_name'] }}
                                </td>
                                
                                <td class="border border-base-300 bg-base-100 text-xs truncate w-[200px] min-w-[200px] max-w-[200px] sticky left-[150px] z-10" title="{{ $dist['distributor_name'] }}">
                                    {{ $dist['distributor_name'] }}
                                </td>
                                
                                <td class="border border-base-300 bg-base-100 text-xs truncate w-[120px] min-w-[120px] max-w-[120px] sticky left-[350px] z-10" title="{{ $dist['cabang'] }}">
                                    {{ $dist['cabang'] }}
                                </td>

                                <td class="border-l border-r-2 !border-r-gray-300 border-base-300 bg-base-100 font-bold text-xs truncate w-[150px] min-w-[150px] max-w-[150px] sticky left-[470px] z-10 uppercase {{ $idx === 0 ? 'border-t-base-300' : '!border-t-transparent' }} {{ $idx === count($spv['distributors']) - 1 ? 'border-b-base-300' : '!border-b-transparent' }}" title="{{ $spv['supervisor_name'] }}">
                                    @if($idx === 0)
                                        {{ $spv['supervisor_name'] }}
                                    @endif
                                </td>
                                
                                <td class="border border-base-300 text-right font-medium">
                                    {{ number_format($dist['target_so'], 0, ',', '.') }}
                                </td>

                                @if($idx === 0)
                                    <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold bg-base-100/50">
                                        {{ number_format($spv['total_target_reguler'], 0, ',', '.') }}
                                    </td>
                                @endif

                                <td class="border border-base-300 text-right font-medium">
                                    {{ number_format($dist['aktual_so'], 0, ',', '.') }}
                                </td>

                                @if($idx === 0)
                                    <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold bg-base-100/50">
                                        {{ number_format($spv['total_aktual_so'], 0, ',', '.') }}
                                    </td>
                                    
                                    <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-center font-bold {{ $spv['pencapaian_persen'] >= 100 ? 'text-success' : 'text-error' }} bg-base-100/50">
                                        {{ number_format($spv['pencapaian_persen'], 0) }}%
                                    </td>
                                    
                                    <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold text-indigo-600 bg-base-100/50">
                                        {{ number_format($spv['ins_so'], 0, ',', '.') }}
                                    </td>

                                    @foreach($headers as $h)
                                        @php
                                            $ach = $spv['vtkp_achievements'][$h->nama_header] ?? ['target' => 0, 'real' => 0, 'growth' => 0, 'insentif' => 0];
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
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right bg-base-100/50">
                                            {{ $ach['target'] > 0 ? number_format($ach['target'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-semibold bg-base-100/50">
                                            {{ $ach['real'] > 0 ? number_format($ach['real'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right {{ $achColor }} bg-base-100/50">
                                            {{ $achText }}
                                        </td>
                                        <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold {{ $ach['insentif'] > 0 ? 'text-indigo-600' : 'text-base-content/40' }} bg-base-100/50">
                                            {{ $ach['insentif'] > 0 ? number_format($ach['insentif'], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
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
                        <td colspan="4" class="border-l border-r-2 !border-r-gray-300 border-base-300 bg-base-300 text-right font-bold text-base-content px-4 py-2 sticky left-0 z-40">
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
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
