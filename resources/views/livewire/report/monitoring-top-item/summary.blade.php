<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Summary Monitoring</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Rekapitulasi Outlet Top Item & NPD</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Filter Region --}}
                <select wire:model.live="region" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Region</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg }}">{{ $reg }}</option>
                    @endforeach
                </select>

                {{-- Filter Month --}}
                <select wire:model.live="month" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
                </select>

                {{-- Filter Year --}}
                <select wire:model.live="year" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
                
                <div class="flex items-center gap-2 ml-auto sm:ml-0">
                    <span class="loading loading-spinner loading-sm text-primary" wire:loading></span>
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Supervisor</th>
                        <th>Distributor</th>
                        <th class="text-center font-bold text-primary">Total Toko Transaksi</th>
                        @for($i=1; $i<=6; $i++)
                        <th class="text-center">Beli {{ $i }} Produk</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($groupedData as $regionName => $regionData)
                        
                        @foreach($regionData['areas'] as $areaName => $areaData)
                            
                            @foreach($areaData['spvs'] as $spvName => $spvData)
                                
                                {{-- Rows for each Distributor --}}
                                @foreach($spvData['distributors'] as $row)
                                    <tr class="hover:bg-base-200/50 transition-colors">
                                        <td class="font-medium">{{ $regionName }}</td>
                                        <td>{{ $areaName }}</td>
                                        <td>{{ $spvName }}</td>
                                        <td class="font-medium text-base-content/80">{{ $row->distributor_name ?? '-' }}</td>
                                        <td class="text-center font-bold text-primary bg-primary/5 text-sm">
                                            {{ number_format($row->total_toko_aktif, 0, ',', '.') }}
                                        </td>
                                        
                                        @for($i=1; $i<=6; $i++)
                                            @php $col = 'beli_' . $i; @endphp
                                            <td class="text-center {{ $row->$col > 0 ? 'font-bold' : 'text-base-content/30' }}">
                                                {{ $row->$col > 0 ? number_format($row->$col, 0, ',', '.') : '-' }}
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                                
                                {{-- Subtotal Supervisor --}}
                                <tr style="background-color: #e0f2fe; color: #0369a1; border-top: 1px solid #bae6fd;">
                                    <td colspan="4" class="text-right font-medium italic">Subtotal Supervisor ({{ $spvName }})</td>
                                    <td class="text-center font-bold text-sm" style="background-color: #bae6fd;">{{ number_format($spvData['totals']['total_toko_aktif'], 0, ',', '.') }}</td>
                                    @for($i=1; $i<=6; $i++)
                                        @php $col = 'beli_' . $i; @endphp
                                        <td class="text-center font-medium">{{ $spvData['totals'][$col] > 0 ? number_format($spvData['totals'][$col], 0, ',', '.') : '-' }}</td>
                                    @endfor
                                </tr>

                            @endforeach
                            
                            {{-- Subtotal Area --}}
                            <tr class="font-semibold" style="background-color: #dcfce7; color: #15803d; border-top: 1px solid #bbf7d0;">
                                <td colspan="4" class="text-right">Subtotal Area ({{ $areaName }})</td>
                                <td class="text-center font-bold text-sm" style="background-color: #bbf7d0;">{{ number_format($areaData['totals']['total_toko_aktif'], 0, ',', '.') }}</td>
                                @for($i=1; $i<=6; $i++)
                                    @php $col = 'beli_' . $i; @endphp
                                    <td class="text-center">{{ $areaData['totals'][$col] > 0 ? number_format($areaData['totals'][$col], 0, ',', '.') : '-' }}</td>
                                @endfor
                            </tr>

                        @endforeach
                        
                        {{-- Subtotal Region --}}
                        <tr class="font-bold uppercase tracking-wide" style="background-color: #f3e8ff; color: #7e22ce; border-top: 2px solid #e9d5ff;">
                            <td colspan="4" class="text-right">Subtotal Region {{ $regionName }}</td>
                            <td class="text-center font-black text-sm" style="background-color: #e9d5ff;">{{ number_format($regionData['totals']['total_toko_aktif'], 0, ',', '.') }}</td>
                            @for($i=1; $i<=6; $i++)
                                @php $col = 'beli_' . $i; @endphp
                                <td class="text-center">{{ $regionData['totals'][$col] > 0 ? number_format($regionData['totals'][$col], 0, ',', '.') : '-' }}</td>
                            @endfor
                        </tr>

                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-10 text-base-content/50 italic">
                                Tidak ada data rekapitulasi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                    
                    @if(count($groupedData) > 0)
                    {{-- Grand Total --}}
                    <tr class="bg-primary text-primary-content font-black shadow-md border-t-4 border-base-100">
                        <td colspan="4" class="text-right uppercase tracking-widest text-base">GRAND TOTAL</td>
                        <td class="text-center text-lg bg-black/10">{{ number_format($grandTotal['total_toko_aktif'], 0, ',', '.') }}</td>
                        @for($i=1; $i<=6; $i++)
                            @php $col = 'beli_' . $i; @endphp
                            <td class="text-center text-sm">{{ $grandTotal[$col] > 0 ? number_format($grandTotal[$col], 0, ',', '.') : '-' }}</td>
                        @endfor
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
    </div>

</div>
