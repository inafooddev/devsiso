<div>
    <x-slot name="title">Summary RWO</x-slot>

    <style>
        .table-summary-rwo th,
        .table-summary-rwo td {
            font-size: 8px !important;
            padding: 6px 8px !important;
        }
    </style>

    <div class="mx-auto px-4 sm:px-6 pt-4">
        <!-- TABS -->
        <div class="tabs tabs-boxed mb-4 w-fit bg-base-100 shadow-sm border border-base-200 p-1">
            <a href="{{ route('rwo.summary') }}" class="tab px-8 tab-active font-bold" wire:navigate>Summary</a>
            <a href="{{ route('rwo.index') }}" class="tab px-8 text-base-content/70 hover:text-base-content" wire:navigate>Detail</a>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 pb-8">
        <x-card title="Summary Kekurangan Kelengkapan Data RWO" icon="chart-bar" flush="true">
            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 w-full">
                    <select wire:model.live="filter_region_code" class="select select-sm select-bordered rounded-xl bg-base-200 border-base-300 w-full sm:w-40 text-xs">
                        <option value="">Semua Region</option>
                        @foreach($this->getFilterRegions() as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filter_area_code" class="select select-sm select-bordered rounded-xl bg-base-200 border-base-300 w-full sm:w-40 text-xs">
                        <option value="">Semua Area</option>
                        @foreach($this->getFilterAreas() as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    <div class="relative w-full sm:w-64">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Region, Area, atau Branch..." class="input input-sm input-bordered w-full rounded-xl pl-8 bg-base-200 border-base-300" />
                        <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/50" />
                    </div>
                </div>
            </x-slot:actions>

            <div class="overflow-x-auto border-t border-base-200 mb-6">
                <x-ui.table striped hover class="table-summary-rwo w-full whitespace-nowrap [&_thead]:sticky [&_thead]:top-0 [&_thead]:z-20 [&_thead]:shadow-sm border-x-0 border-b-0 rounded-none h-[500px] overflow-auto block">
                    <x-slot:head>
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
                    </x-slot:head>
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
                    <tfoot class="sticky bottom-0 z-20 bg-base-300 font-bold shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                        <tr class="text-base-content" style="background-color: #e5e7eb !important;">
                            <td colspan="5" class="text-right uppercase tracking-wider pr-4 py-3">GRAND TOTAL KESELURUHAN</td>
                            <td class="text-center py-3 text-primary">{{ number_format($grandTotals['total_customer']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_no_hp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_no_hp']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_nama_pemilik_toko'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_pemilik_toko']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_nama_ktp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_ktp']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_nik_ktp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nik_ktp']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_foto_ktp'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_foto_ktp']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_no_rekening'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_no_rekening']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_nama_bank'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_bank']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_nama_pemilik_norek'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_nama_pemilik_norek']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_foto_toko'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_foto_toko']) }}</td>
                            <td class="text-center py-3 {{ $grandTotals['missing_is_valid'] > 0 ? 'text-error' : 'text-success' }}">{{ number_format($grandTotals['missing_is_valid']) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </x-ui.table>
            </div>
        </x-card>
    </div>
</div>
