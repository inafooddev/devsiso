<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Summary List Potensi RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Summary List Potensi</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi RWO</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Surat Kesepakatan Bersama</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Pencapaian RWO</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Cek Plan Kunjungan</a>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Toolbar / Filters --}}
        <div class="p-4 border-b border-base-300 shrink-0 bg-base-200/30 flex flex-wrap items-end gap-3">
            <div class="form-control">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Tgl Mulai JKS</span></label>
                <input type="date" wire:model.live="jksDateStart" class="input input-sm input-bordered" />
            </div>
            
            <div class="form-control">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Tgl Akhir JKS</span></label>
                <input type="date" wire:model.live="jksDateEnd" class="input input-sm input-bordered" />
            </div>

            <div class="form-control min-w-[120px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Kuartal</span></label>
                <select wire:model.live="kuartal" class="select select-sm select-bordered">
                    <option value="">Semua Kuartal</option>
                    @foreach($kuartals as $q)
                        <option value="{{ $q->quarter }}">{{ $q->quarter }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control min-w-[150px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Region</span></label>
                <select wire:model.live="region" class="select select-sm select-bordered">
                    <option value="">Semua Region</option>
                    @foreach($regions as $r)
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control min-w-[150px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Area</span></label>
                <select wire:model.live="area" class="select select-sm select-bordered" {{ empty($areas) ? 'disabled' : '' }}>
                    <option value="">Semua Area</option>
                    @foreach($areas as $a)
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="ml-auto flex items-center gap-4">
                <div wire:loading>
                    <span class="loading loading-spinner loading-sm text-primary"></span>
                </div>
                <div class="mt-6">
                    <button wire:click="exportExcel" class="btn btn-sm btn-success font-bold text-white shadow-sm" wire:loading.attr="disabled">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-1" />
                        <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                        <span wire:loading wire:target="exportExcel">Exporting...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 overflow-auto bg-base-100 relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 shadow-sm">
                    <tr>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Supervisor</th>
                        <th class="text-right text-primary">Total Toko</th>
                        <th class="text-right text-info">Masuk JKS</th>
                        <th class="text-right">Sudah SKB</th>
                        <th class="text-right text-success">Approve</th>
                        <th class="text-right text-error">Reject</th>
                        <th class="text-right text-success">Lengkap</th>
                        <th class="text-right text-error">Belum</th>
                        <th class="text-right text-primary">Total Target</th>
                        <th class="text-right text-primary">Target Prorata</th>
                        <th class="text-right text-primary">Pencapaian</th>
                        <th class="text-right text-primary">%</th>
                        <th class="text-right text-primary">Toko Trx</th>
                        <th class="text-right text-success">Hijau</th>
                        <th class="text-right text-warning">Kuning</th>
                        <th class="text-right text-error">Merah</th>
                    </tr>
                </thead>
                @forelse($records as $regionIndex => $region)
                    @php $isFirstRegionRow = true; @endphp
                    @foreach($region['areas'] as $areaIndex => $area)
                        @php $isFirstAreaRow = true; @endphp
                        @foreach($area['supervisors'] as $supervisorIndex => $supervisor)
                            <tbody wire:key="spv-{{ $regionIndex }}-{{ $areaIndex }}-{{ $supervisorIndex }}" x-data="{ expanded: false }" class="text-sm border-b border-base-200">
                                <tr class="hover:bg-base-200 transition-colors cursor-pointer group" @click="expanded = !expanded" :class="expanded ? 'bg-base-200/40' : ''">
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded hover:bg-base-300 flex items-center justify-center transition-colors">
                                                <x-heroicon-s-chevron-right class="w-4 h-4 transition-transform duration-200 text-base-content/50 group-hover:text-base-content" x-bind:class="expanded ? 'rotate-90' : ''" />
                                            </div>
                                            @if($isFirstRegionRow)
                                                <span class="font-bold">{{ $region['name'] }}</span>
                                            @else
                                                <span class="text-base-content/30 italic hidden md:inline">{{ $region['name'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($isFirstAreaRow)
                                            <span class="font-semibold">{{ $area['name'] }}</span>
                                        @else
                                            <span class="text-base-content/30 italic hidden md:inline">{{ $area['name'] }}</span>
                                        @endif
                                    </td>
                                    <td class="font-bold text-base-content/80">{{ $supervisor['name'] }}</td>
                                    <td class="text-right font-bold">{{ number_format($supervisor['total_toko'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-info">{{ number_format($supervisor['total_jks'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold">{{ number_format($supervisor['sudah_skb'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-success">{{ number_format($supervisor['skb_approve'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-error">{{ number_format($supervisor['skb_reject'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-success">{{ number_format($supervisor['data_lengkap'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-error">{{ number_format($supervisor['data_belum'], 0, ',', '.') }}</td>
                                    <td class="text-right font-mono text-[11px]">{{ number_format($supervisor['total_target'], 0, ',', '.') }}</td>
                                    <td class="text-right font-mono text-[11px] text-base-content/60">{{ number_format($supervisor['target_prorata'], 0, ',', '.') }}</td>
                                    <td class="text-right font-mono font-bold text-[11px] text-success">{{ number_format($supervisor['total_achievement'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-[11px]">{{ $supervisor['target_prorata'] > 0 ? number_format(($supervisor['total_achievement'] / $supervisor['target_prorata']) * 100, 1, ',', '.') : 0 }}%</td>
                                    <td class="text-right font-bold">{{ number_format($supervisor['toko_transaksi'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-success">{{ number_format($supervisor['toko_hijau'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-warning">{{ number_format($supervisor['toko_kuning'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-error">{{ number_format($supervisor['toko_merah'], 0, ',', '.') }}</td>
                                </tr>
                                
                                @foreach($supervisor['cabang'] as $cabangIndex => $cabang)
                                    <tr wire:key="cabang-{{ $regionIndex }}-{{ $areaIndex }}-{{ $supervisorIndex }}-{{ $cabangIndex }}" x-show="expanded" x-transition.opacity x-cloak class="bg-base-200/50 text-[12px] hover:bg-base-200/80 transition-colors">
                                        <td colspan="3" class="pl-12 py-2 border-l-4 border-l-primary border-y-none border-r-none">
                                            <div class="font-bold text-primary whitespace-normal" title="{{ $cabang['distributor_name'] }}">{{ $cabang['distributor_name'] }}</div>
                                        </td>
                                        <td class="text-right font-semibold opacity-80 border-none py-2">{{ number_format($cabang['total_toko'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-info opacity-80 border-none py-2">{{ number_format($cabang['total_jks'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold opacity-80 border-none py-2">{{ number_format($cabang['sudah_skb'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-success opacity-80 border-none py-2">{{ number_format($cabang['skb_approve'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-error opacity-80 border-none py-2">{{ number_format($cabang['skb_reject'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-success opacity-80 border-none py-2">{{ number_format($cabang['data_lengkap'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-error opacity-80 border-none py-2">{{ number_format($cabang['data_belum'], 0, ',', '.') }}</td>
                                        <td class="text-right font-mono text-[10px] opacity-80 border-none py-2">{{ number_format($cabang['total_target'], 0, ',', '.') }}</td>
                                        <td class="text-right font-mono text-[10px] opacity-60 border-none py-2">{{ number_format($cabang['target_prorata'], 0, ',', '.') }}</td>
                                        <td class="text-right font-mono font-bold text-[10px] text-success opacity-80 border-none py-2">{{ number_format($cabang['total_achievement'], 0, ',', '.') }}</td>
                                        <td class="text-right font-bold text-[10px] opacity-80 border-none py-2">{{ $cabang['target_prorata'] > 0 ? number_format(($cabang['total_achievement'] / $cabang['target_prorata']) * 100, 1, ',', '.') : 0 }}%</td>
                                        <td class="text-right font-semibold opacity-80 border-none py-2">{{ number_format($cabang['toko_transaksi'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-success opacity-80 border-none py-2">{{ number_format($cabang['toko_hijau'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-warning opacity-80 border-none py-2">{{ number_format($cabang['toko_kuning'], 0, ',', '.') }}</td>
                                        <td class="text-right font-semibold text-error opacity-80 border-none py-2">{{ number_format($cabang['toko_merah'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @php 
                                $isFirstRegionRow = false; 
                                $isFirstAreaRow = false;
                            @endphp
                        @endforeach

                        {{-- Area Subtotal --}}
                        <tbody wire:key="sub-area-{{ $regionIndex }}-{{ $areaIndex }}" class="text-sm">
                            <tr class="bg-info/10 text-info font-bold border-b-2 border-info/20">
                                <td colspan="3" class="text-right uppercase py-2">Subtotal Area {{ $area['name'] }}</td>
                                <td class="text-right">{{ number_format($area['total_toko'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($area['total_jks'], 0, ',', '.') }}</td>
                                <td class="text-right text-base-content/80">{{ number_format($area['sudah_skb'], 0, ',', '.') }}</td>
                                <td class="text-right text-success">{{ number_format($area['skb_approve'], 0, ',', '.') }}</td>
                                <td class="text-right text-error">{{ number_format($area['skb_reject'], 0, ',', '.') }}</td>
                                <td class="text-right text-success">{{ number_format($area['data_lengkap'], 0, ',', '.') }}</td>
                                <td class="text-right text-error">{{ number_format($area['data_belum'], 0, ',', '.') }}</td>
                                <td class="text-right font-mono text-[11px]">{{ number_format($area['total_target'], 0, ',', '.') }}</td>
                                <td class="text-right font-mono text-[11px] opacity-80">{{ number_format($area['target_prorata'], 0, ',', '.') }}</td>
                                <td class="text-right font-mono text-[11px] text-success">{{ number_format($area['total_achievement'], 0, ',', '.') }}</td>
                                <td class="text-right font-bold text-[11px]">{{ $area['target_prorata'] > 0 ? number_format(($area['total_achievement'] / $area['target_prorata']) * 100, 1, ',', '.') : 0 }}%</td>
                                <td class="text-right">{{ number_format($area['toko_transaksi'], 0, ',', '.') }}</td>
                                <td class="text-right text-success">{{ number_format($area['toko_hijau'], 0, ',', '.') }}</td>
                                <td class="text-right text-warning">{{ number_format($area['toko_kuning'], 0, ',', '.') }}</td>
                                <td class="text-right text-error">{{ number_format($area['toko_merah'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    @endforeach

                    {{-- Region Subtotal --}}
                    <tbody wire:key="sub-region-{{ $regionIndex }}" class="text-sm">
                        <tr class="bg-primary/10 text-primary font-bold border-b-[3px] border-primary/30">
                            <td colspan="3" class="text-right uppercase py-3">Subtotal Region {{ $region['name'] }}</td>
                            <td class="text-right">{{ number_format($region['total_toko'], 0, ',', '.') }}</td>
                            <td class="text-right text-info">{{ number_format($region['total_jks'], 0, ',', '.') }}</td>
                            <td class="text-right text-base-content/80">{{ number_format($region['sudah_skb'], 0, ',', '.') }}</td>
                            <td class="text-right text-success">{{ number_format($region['skb_approve'], 0, ',', '.') }}</td>
                            <td class="text-right text-error">{{ number_format($region['skb_reject'], 0, ',', '.') }}</td>
                            <td class="text-right text-success">{{ number_format($region['data_lengkap'], 0, ',', '.') }}</td>
                            <td class="text-right text-error">{{ number_format($region['data_belum'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-[11px]">{{ number_format($region['total_target'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-[11px] opacity-80">{{ number_format($region['target_prorata'], 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-[11px] text-success">{{ number_format($region['total_achievement'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-[11px]">{{ $region['target_prorata'] > 0 ? number_format(($region['total_achievement'] / $region['target_prorata']) * 100, 1, ',', '.') : 0 }}%</td>
                            <td class="text-right">{{ number_format($region['toko_transaksi'], 0, ',', '.') }}</td>
                            <td class="text-right text-success">{{ number_format($region['toko_hijau'], 0, ',', '.') }}</td>
                            <td class="text-right text-warning">{{ number_format($region['toko_kuning'], 0, ',', '.') }}</td>
                            <td class="text-right text-error">{{ number_format($region['toko_merah'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>

                @empty
                    <tbody class="text-sm">
                        <tr>
                            <td colspan="16" class="text-center py-8 text-base-content/50">
                                <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-3 opacity-30" />
                                <p>Tidak ada data summary ditemukan dengan filter tersebut.</p>
                            </td>
                        </tr>
                    </tbody>
                @endforelse
                @if(count($records) > 0)
                @php
                    $recordsColl = collect($records);
                @endphp
                <tfoot class="bg-base-200/80 sticky bottom-0 z-10 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] border-t border-base-300">
                    <tr class="font-bold text-[13px] text-base-content/90">
                        <td colspan="3" class="text-right uppercase py-4">Grand Total Nasional</td>
                        <td class="text-right">{{ number_format($recordsColl->sum('total_toko'), 0, ',', '.') }}</td>
                        <td class="text-right text-info">{{ number_format($recordsColl->sum('total_jks'), 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($recordsColl->sum('sudah_skb'), 0, ',', '.') }}</td>
                        <td class="text-right text-success">{{ number_format($recordsColl->sum('skb_approve'), 0, ',', '.') }}</td>
                        <td class="text-right text-error">{{ number_format($recordsColl->sum('skb_reject'), 0, ',', '.') }}</td>
                        <td class="text-right text-success">{{ number_format($recordsColl->sum('data_lengkap'), 0, ',', '.') }}</td>
                        <td class="text-right text-error">{{ number_format($recordsColl->sum('data_belum'), 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-[11px]">{{ number_format($recordsColl->sum('total_target'), 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-[11px] opacity-80">{{ number_format($recordsColl->sum('target_prorata'), 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-[11px] text-success">{{ number_format($recordsColl->sum('total_achievement'), 0, ',', '.') }}</td>
                        <td class="text-right font-bold text-[11px]">{{ $recordsColl->sum('target_prorata') > 0 ? number_format(($recordsColl->sum('total_achievement') / $recordsColl->sum('target_prorata')) * 100, 1, ',', '.') : 0 }}%</td>
                        <td class="text-right">{{ number_format($recordsColl->sum('toko_transaksi'), 0, ',', '.') }}</td>
                        <td class="text-right text-success">{{ number_format($recordsColl->sum('toko_hijau'), 0, ',', '.') }}</td>
                        <td class="text-right text-warning">{{ number_format($recordsColl->sum('toko_kuning'), 0, ',', '.') }}</td>
                        <td class="text-right text-error">{{ number_format($recordsColl->sum('toko_merah'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
