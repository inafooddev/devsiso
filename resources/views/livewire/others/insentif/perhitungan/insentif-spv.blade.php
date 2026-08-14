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
            <table class="table table-xs table-pin-rows table-pin-cols w-full">
                <thead>
                    <tr>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content min-w-[150px] whitespace-nowrap">Area</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content min-w-[150px] max-w-[200px] truncate">Distributor</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content min-w-[120px] max-w-[200px] truncate">Cabang</th>
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-base-200 text-base-content min-w-[150px] max-w-[200px] truncate">Nama Supervisor</th>
                        
                        <!-- Header INSENTIF VALUE -->
                        <th colspan="5" class="border border-base-300 text-center font-bold bg-fuchsia-300 text-fuchsia-900">
                            1. Insentif Value Selling Out (Reguler)
                        </th>
                        
                        <th rowspan="2" class="border border-base-300 text-center font-bold bg-fuchsia-400 text-fuchsia-950 min-w-[120px]">
                            INS SO
                        </th>
                    </tr>
                    <tr>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Target SO</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Target SO Reguler</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Aktual SO</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 min-w-[120px]">Pencapaian</th>
                        <th class="border border-base-300 text-center font-bold bg-fuchsia-200 text-fuchsia-900 w-[80px]">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spvData as $spv)
                        @foreach($spv['distributors'] as $idx => $dist)
                            <tr class="hover:bg-base-200/50" wire:key="spv-{{ md5($spv['supervisor_code'].$dist['distributor_code']) }}">
                                <td class="border border-base-300 bg-base-100 text-xs truncate max-w-[150px]" title="{{ $dist['area_name'] }}">
                                    {{ $dist['area_name'] }}
                                </td>
                                
                                <td class="border border-base-300 bg-base-100 text-xs truncate max-w-[200px]" title="{{ $dist['distributor_name'] }}">
                                    {{ $dist['distributor_name'] }}
                                </td>
                                
                                <td class="border border-base-300 bg-base-100 text-xs truncate max-w-[150px]" title="{{ $dist['cabang'] }}">
                                    {{ $dist['cabang'] }}
                                </td>

                                @if($idx === 0)
                                    <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 bg-base-100 font-bold text-xs truncate max-w-[200px] uppercase" title="{{ $spv['supervisor_name'] }}">
                                        {{ $spv['supervisor_name'] }}
                                    </td>
                                @endif
                                
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
                                    
                                    <td rowspan="{{ $spv['rowspan'] }}" class="border border-base-300 text-right font-bold text-success bg-base-100/50">
                                        {{ number_format($spv['ins_so'], 0, ',', '.') }}
                                    </td>
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
                <tfoot class="sticky bottom-0 z-10">
                    <tr>
                        <td colspan="4" class="border border-base-300 bg-base-300 text-right font-bold text-base-content px-4 py-2">
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
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
