<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Summary RWO</x-slot>

    <style>
        .table-summary-rwo th,
        .table-summary-rwo td {
            font-size: 8px !important;
            padding: 6px 8px !important;
        }
    </style>

    <!-- TABS -->
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.summary') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Summary</a>
            <a href="{{ route('rwo.index') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Detail</a>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full md:w-auto">
                <h2 class="text-base md:text-lg font-bold">Summary Kekurangan Kelengkapan Data RWO</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Ringkasan status data</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start md:justify-end gap-2 md:gap-3 w-full md:w-auto">
                <select wire:model.live="filter_region_code" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow md:grow-0 text-xs">
                    <option value="">Semua Region</option>
                    @foreach($this->getFilterRegions() as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filter_area_code" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow md:grow-0 text-xs">
                    <option value="">Semua Area</option>
                    @foreach($this->getFilterAreas() as $area)
                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                    @endforeach
                </select>

                <div class="relative grow md:grow-0 group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Region, Area, atau Branch..." 
                           class="input input-sm input-bordered pl-10 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300" />
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto w-full relative">
            <table class="table table-sm table-zebra table-summary-rwo w-full whitespace-nowrap">
                <thead class="sticky top-0 z-20 text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                            <th rowspan="2" class="align-middle border-b border-r border-base-200">No</th>
                            <th rowspan="2" class="align-middle border-b border-r border-base-200">Region</th>
                            <th rowspan="2" class="align-middle border-b border-r border-base-200">Area</th>
                            <th rowspan="2" class="align-middle border-b border-r border-base-200">Supervisor</th>
                            <th rowspan="2" class="align-middle border-b border-r border-base-200">Branch</th>
                            <th rowspan="2" class="align-middle border-b border-r border-base-200 text-center font-bold text-primary">Total<br>Toko (RWO)</th>
                            <th colspan="10" class="text-center border-b border-base-200 bg-error/10 text-error">Data Yang Belum Ada / Kosong</th>
                        </tr>
                        <tr>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">No HP</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">Nm. Pemilik</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">Nm. KTP</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">NIK KTP</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">Foto KTP</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">No. Rek</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">Nm. Bank</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">Nm. Pemilik Rek</th>
                            <th class="border-b border-r border-base-200 text-center bg-error/5">Foto Toko</th>
                            <th class="border-b border-base-200 text-center bg-error/5">Not Valid</th>
                        </tr>
                </thead>
                <tbody>
                        @php
                            $groupedByRegion = collect($records)->groupBy('region_name');
                            $index = 0;

                            $grandTotals = [
                                'total_customer' => collect($records)->sum('total_customer'),
                                'missing_no_hp' => collect($records)->sum('missing_no_hp'),
                                'missing_nama_pemilik_toko' => collect($records)->sum('missing_nama_pemilik_toko'),
                                'missing_nama_ktp' => collect($records)->sum('missing_nama_ktp'),
                                'missing_nik_ktp' => collect($records)->sum('missing_nik_ktp'),
                                'missing_foto_ktp' => collect($records)->sum('missing_foto_ktp'),
                                'missing_no_rekening' => collect($records)->sum('missing_no_rekening'),
                                'missing_nama_bank' => collect($records)->sum('missing_nama_bank'),
                                'missing_nama_pemilik_norek' => collect($records)->sum('missing_nama_pemilik_norek'),
                                'missing_foto_toko' => collect($records)->sum('missing_foto_toko'),
                                'missing_is_valid' => collect($records)->sum('missing_is_valid'),
                            ];
                        @endphp
                        
                        @forelse($groupedByRegion as $regionName => $areas)
                            @php
                                $regionSubtotals = [
                                    'total_customer' => $areas->sum('total_customer'),
                                    'missing_no_hp' => $areas->sum('missing_no_hp'),
                                    'missing_nama_pemilik_toko' => $areas->sum('missing_nama_pemilik_toko'),
                                    'missing_nama_ktp' => $areas->sum('missing_nama_ktp'),
                                    'missing_nik_ktp' => $areas->sum('missing_nik_ktp'),
                                    'missing_foto_ktp' => $areas->sum('missing_foto_ktp'),
                                    'missing_no_rekening' => $areas->sum('missing_no_rekening'),
                                    'missing_nama_bank' => $areas->sum('missing_nama_bank'),
                                    'missing_nama_pemilik_norek' => $areas->sum('missing_nama_pemilik_norek'),
                                    'missing_foto_toko' => $areas->sum('missing_foto_toko'),
                                    'missing_is_valid' => $areas->sum('missing_is_valid'),
                                ];
                                $groupedByArea = $areas->groupBy('area_name');
                            @endphp
                            
                            @foreach($groupedByArea as $areaName => $supervisors)
                                @php
                                    $areaSubtotals = [
                                        'total_customer' => $supervisors->sum('total_customer'),
                                        'missing_no_hp' => $supervisors->sum('missing_no_hp'),
                                        'missing_nama_pemilik_toko' => $supervisors->sum('missing_nama_pemilik_toko'),
                                        'missing_nama_ktp' => $supervisors->sum('missing_nama_ktp'),
                                        'missing_nik_ktp' => $supervisors->sum('missing_nik_ktp'),
                                        'missing_foto_ktp' => $supervisors->sum('missing_foto_ktp'),
                                        'missing_no_rekening' => $supervisors->sum('missing_no_rekening'),
                                        'missing_nama_bank' => $supervisors->sum('missing_nama_bank'),
                                        'missing_nama_pemilik_norek' => $supervisors->sum('missing_nama_pemilik_norek'),
                                        'missing_foto_toko' => $supervisors->sum('missing_foto_toko'),
                                        'missing_is_valid' => $supervisors->sum('missing_is_valid'),
                                    ];
                                    $groupedBySupervisor = $supervisors->groupBy('supervisor_code');
                                @endphp

                                @foreach($groupedBySupervisor as $spvCode => $branches)
                                    @php
                                        $supervisorName = $branches->first()->supervisor_name;
                                        $spvSubtotals = [
                                            'total_customer' => $branches->sum('total_customer'),
                                            'missing_no_hp' => $branches->sum('missing_no_hp'),
                                            'missing_nama_pemilik_toko' => $branches->sum('missing_nama_pemilik_toko'),
                                            'missing_nama_ktp' => $branches->sum('missing_nama_ktp'),
                                            'missing_nik_ktp' => $branches->sum('missing_nik_ktp'),
                                            'missing_foto_ktp' => $branches->sum('missing_foto_ktp'),
                                            'missing_no_rekening' => $branches->sum('missing_no_rekening'),
                                            'missing_nama_bank' => $branches->sum('missing_nama_bank'),
                                            'missing_nama_pemilik_norek' => $branches->sum('missing_nama_pemilik_norek'),
                                            'missing_foto_toko' => $branches->sum('missing_foto_toko'),
                                            'missing_is_valid' => $branches->sum('missing_is_valid'),
                                        ];
                                    @endphp

                                    @foreach($branches as $row)
                                        @php $index++; @endphp
                                        <tr class="hover:bg-base-200/50 transition-colors">
                                            <td class="text-center">{{ $index }}</td>
                                            <td>{{ $regionName ?? '-' }}</td>
                                            <td class="max-w-[120px] truncate" title="{{ $areaName ?? '-' }}">{{ $areaName ?? '-' }}</td>
                                            <td class="max-w-[120px] truncate" title="{{ $supervisorName ?? '-' }}">{{ $supervisorName ?? '-' }}</td>
                                            <td class="font-bold max-w-[120px] truncate" title="{{ $row->branch_name ?? '-' }}">{{ $row->branch_name ?? '-' }}</td>
                                            <td class="text-center font-bold text-primary bg-primary/5">{{ number_format($row->total_customer) }}</td>
                                            
                                            <td class="text-center {{ $row->missing_no_hp > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_no_hp) }}</td>
                                            <td class="text-center {{ $row->missing_nama_pemilik_toko > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_nama_pemilik_toko) }}</td>
                                            <td class="text-center {{ $row->missing_nama_ktp > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_nama_ktp) }}</td>
                                            <td class="text-center {{ $row->missing_nik_ktp > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_nik_ktp) }}</td>
                                            <td class="text-center {{ $row->missing_foto_ktp > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_foto_ktp) }}</td>
                                            <td class="text-center {{ $row->missing_no_rekening > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_no_rekening) }}</td>
                                            <td class="text-center {{ $row->missing_nama_bank > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_nama_bank) }}</td>
                                            <td class="text-center {{ $row->missing_nama_pemilik_norek > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_nama_pemilik_norek) }}</td>
                                            <td class="text-center {{ $row->missing_foto_toko > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_foto_toko) }}</td>
                                            <td class="text-center {{ $row->missing_is_valid > 0 ? 'text-error font-bold' : 'text-success' }}">{{ number_format($row->missing_is_valid) }}</td>
                                        </tr>
                                    @endforeach

                                    {{-- Subtotal Supervisor --}}
                                    <tr style="background-color: #dbeafe !important; color: #1e3a8a !important; font-weight: bold;">
                                        <td colspan="5" class="text-right uppercase tracking-wider pr-4">Subtotal SPV: {{ $supervisorName ?? '-' }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['total_customer']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_no_hp']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_nama_pemilik_toko']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_nama_ktp']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_nik_ktp']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_foto_ktp']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_no_rekening']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_nama_bank']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_nama_pemilik_norek']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_foto_toko']) }}</td>
                                        <td class="text-center">{{ number_format($spvSubtotals['missing_is_valid']) }}</td>
                                    </tr>
                                @endforeach

                                {{-- Subtotal Area --}}
                                <tr style="background-color: #fef3c7 !important; color: #92400e !important; font-weight: bold;">
                                    <td colspan="5" class="text-right uppercase tracking-wider pr-4">Subtotal Area: {{ $areaName ?? '-' }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['total_customer']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_no_hp']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_nama_pemilik_toko']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_nama_ktp']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_nik_ktp']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_foto_ktp']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_no_rekening']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_nama_bank']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_nama_pemilik_norek']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_foto_toko']) }}</td>
                                    <td class="text-center">{{ number_format($areaSubtotals['missing_is_valid']) }}</td>
                                </tr>
                            @endforeach

                            {{-- Subtotal Region --}}
                            <tr style="background-color: #d1fae5 !important; color: #065f46 !important; font-weight: bold;">
                                <td colspan="5" class="text-right uppercase tracking-wider pr-4">Subtotal Region: {{ $regionName ?? '-' }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['total_customer']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_no_hp']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_nama_pemilik_toko']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_nama_ktp']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_nik_ktp']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_foto_ktp']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_no_rekening']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_nama_bank']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_nama_pemilik_norek']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_foto_toko']) }}</td>
                                <td class="text-center">{{ number_format($regionSubtotals['missing_is_valid']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="text-center py-8 text-base-content/50">
                                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-30" />
                                    Tidak ada data summary yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($records) > 0)
                    <tfoot class="sticky bottom-0 z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                        <tr class="text-base-content/80 text-[10px]" style="background-color: #e5e7eb !important;">
                            <td colspan="5" class="text-right font-black uppercase tracking-wider pr-4 py-3">GRAND TOTAL KESELURUHAN</td>
                            <td class="text-center font-black py-3 text-primary">{{ number_format($grandTotals['total_customer']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_no_hp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_no_hp']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_nama_pemilik_toko'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_pemilik_toko']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_nama_ktp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_ktp']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_nik_ktp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nik_ktp']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_foto_ktp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_foto_ktp']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_no_rekening'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_no_rekening']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_nama_bank'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_bank']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_nama_pemilik_norek'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_pemilik_norek']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_foto_toko'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_foto_toko']) }}</td>
                            <td class="text-center font-black py-3 {{ $grandTotals['missing_is_valid'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_is_valid']) }}</td>
                        </tr>
                    </tfoot>
                    @endif
            </table>
        </div>
    </div>
</div>
