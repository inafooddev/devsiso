<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Cek Plan Kunjungan RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary List Potensi</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi RWO</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Surat Kesepakatan Bersama</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Pencapaian RWO</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Cek Plan Kunjungan</a>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-200/30">
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                {{-- Filter Tanggal Mulai --}}
                <div class="relative group grow md:grow-0">
                    <input wire:model.live="dateStart" type="date"
                           class="input input-sm input-bordered w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300" title="Tanggal Mulai">
                </div>
                
                {{-- Filter Tanggal Selesai --}}
                <div class="relative group grow md:grow-0">
                    <input wire:model.live="dateEnd" type="date"
                           class="input input-sm input-bordered w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300" title="Tanggal Akhir">
                </div>

                {{-- Filter Region (Multi Select) --}}
                <div class="relative group grow md:grow-0 min-w-[150px]" x-data="{ open: false, selected: @entangle('selectedRegions'), searchRegion: '' }" x-init="$watch('selected', value => { if (!value || value.length === 0) searchRegion = ''; })">
                    <button @click="open = !open" type="button" class="btn btn-sm btn-outline btn-block justify-between border-base-300 bg-base-100 rounded-xl font-normal normal-case">
                        <span x-text="selected.length ? selected.length + ' Region' : 'Pilih Region'"></span>
                        <x-heroicon-s-chevron-down class="w-4 h-4" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-72 flex flex-col p-2">
                        <div class="p-1 mb-2 sticky top-0 bg-base-100 z-10">
                            <input x-model="searchRegion" type="text" placeholder="Cari region..." class="input input-sm input-bordered w-full rounded-lg text-xs" />
                        </div>
                        <div class="overflow-auto flex-1">
                            @foreach($regionOptions as $region)
                                <label x-show="searchRegion === '' || '{{ addslashes(strtolower($region)) }}'.includes(searchRegion.toLowerCase())" class="label cursor-pointer justify-start gap-2 hover:bg-base-200 rounded-lg px-2 py-1">
                                    <input type="checkbox" wire:model.live="selectedRegions" value="{{ $region }}" class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="label-text">{{ $region }}</span>
                                </label>
                            @endforeach
                            @if($regionOptions->isEmpty())
                                <div class="p-2 text-xs text-base-content/50 text-center">Tidak ada region</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Filter Area (Multi Select) --}}
                <div class="relative group grow md:grow-0 min-w-[150px]" x-data="{ open: false, selected: @entangle('selectedAreas'), searchArea: '' }" x-init="$watch('selected', value => { if (!value || value.length === 0) searchArea = ''; })">
                    <button @click="open = !open" type="button" class="btn btn-sm btn-outline btn-block justify-between border-base-300 bg-base-100 rounded-xl font-normal normal-case">
                        <span x-text="selected.length ? selected.length + ' Area' : 'Pilih Area'"></span>
                        <x-heroicon-s-chevron-down class="w-4 h-4" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-72 flex flex-col p-2">
                        <div class="p-1 mb-2 sticky top-0 bg-base-100 z-10">
                            <input x-model="searchArea" type="text" placeholder="Cari area..." class="input input-sm input-bordered w-full rounded-lg text-xs" />
                        </div>
                        <div class="overflow-auto flex-1">
                            @foreach($areaOptions as $area)
                                <label x-show="searchArea === '' || '{{ addslashes(strtolower($area)) }}'.includes(searchArea.toLowerCase())" class="label cursor-pointer justify-start gap-2 hover:bg-base-200 rounded-lg px-2 py-1">
                                    <input type="checkbox" wire:model.live="selectedAreas" value="{{ $area }}" class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="label-text">{{ $area }}</span>
                                </label>
                            @endforeach
                            @if($areaOptions->isEmpty())
                                <div class="p-2 text-xs text-base-content/50 text-center">Tidak ada area</div>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Filter Tim (Multi Select) --}}
                <div class="relative group grow md:grow-0 min-w-[150px]" x-data="{ open: false, selected: @entangle('selectedTeams'), searchTeam: '' }" x-init="$watch('selected', value => { if (!value || value.length === 0) searchTeam = ''; })">
                    <button @click="open = !open" type="button" class="btn btn-sm btn-outline btn-block justify-between border-base-300 bg-base-100 rounded-xl font-normal normal-case">
                        <span x-text="selected.length ? selected.length + ' Tim' : 'Pilih Tim'"></span>
                        <x-heroicon-s-chevron-down class="w-4 h-4" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-72 flex flex-col p-2">
                        <div class="p-1 mb-2 sticky top-0 bg-base-100 z-10">
                            <input x-model="searchTeam" type="text" placeholder="Cari tim..." class="input input-sm input-bordered w-full rounded-lg text-xs" />
                        </div>
                        <div class="overflow-auto flex-1">
                            @foreach($teamOptions as $team)
                                <label x-show="searchTeam === '' || '{{ addslashes(strtolower($team)) }}'.includes(searchTeam.toLowerCase())" class="label cursor-pointer justify-start gap-2 hover:bg-base-200 rounded-lg px-2 py-1">
                                    <input type="checkbox" wire:model.live="selectedTeams" value="{{ $team }}" class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="label-text">{{ $team }}</span>
                                </label>
                            @endforeach
                            @if($teamOptions->isEmpty())
                                <div class="p-2 text-xs text-base-content/50 text-center">Tidak ada tim</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-2 mt-2 md:mt-0">
                <button wire:click="resetFilters" class="btn btn-sm btn-outline btn-error rounded-xl shadow-sm hover:shadow-md transition-all whitespace-nowrap" title="Reset Filter">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </button>
                <button wire:click="export" wire:loading.attr="disabled" class="btn btn-sm btn-success text-white rounded-xl shadow-sm hover:shadow-md transition-all whitespace-nowrap">
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" wire:loading.remove wire:target="export" />
                    <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                    Export Excel
                </button>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto w-full relative" style="isolation: auto;" wire:loading.class="opacity-60 pointer-events-none">
            <div wire:loading wire:target="dateStart, dateEnd, selectedTeams" 
                 class="absolute inset-0 flex items-center justify-center bg-base-100/70 z-30 backdrop-blur-[1px]">
                <div class="flex flex-col items-center gap-2">
                    <span class="loading loading-dots loading-lg text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/50">Memuat data...</span>
                </div>
            </div>
            <table class="table table-sm table-zebra table-pin-rows table-pin-cols w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center sticky left-0 z-20 bg-base-300">No</th>
                        <th>Nama Team</th>
                        <th>Cust No</th>
                        <th>Cust Name</th>
                        <th>Alamat</th>
                        <th class="text-center bg-base-200 text-primary">Status</th>
                        <th class="text-center bg-base-200">No HP</th>
                        <th class="text-center bg-base-200">Pemilik Toko</th>
                        <th class="text-center bg-base-200">NIK KTP</th>
                        <th class="text-center bg-base-200">Nama KTP</th>
                        <th class="text-center bg-base-200">Foto KTP</th>
                        <th class="text-center bg-base-200">No Rekening</th>
                        <th class="text-center bg-base-200">Pemilik Rekening</th>
                        <th class="text-center bg-base-200">Latitude</th>
                        <th class="text-center bg-base-200">Longitude</th>
                        <th class="text-center bg-base-200">Tampak Depan</th>
                        <th class="text-center bg-base-200">Tampak Dalam</th>
                    </tr>
                </thead>
                <tbody class="text-sm">

                @foreach ($records as $index => $row)
                    <tr class="group text-[11px] hover:relative hover:z-40" wire:key="plan-{{ $index }}">
                        <td class="text-center sticky left-0 bg-base-100 group-hover:bg-base-200 transition-colors z-10">
                            <span class="font-semibold text-base-content/40">{{ $records->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <div class="max-w-[120px] truncate" title="{{ $row->nama_team }}">
                                {{ $row->nama_team }}
                            </div>
                        </td>
                        <td>
                            <span class="font-bold text-base-content/85">{{ $row->custno }}</span>
                        </td>
                        <td>
                            <div class="max-w-[200px] truncate" title="{{ $row->custname }}">
                                {{ $row->custname }}
                            </div>
                        </td>
                        <td>
                            <div class="max-w-[150px] truncate" title="{{ $row->addres }}">
                                {{ $row->addres ?: '-' }}
                            </div>
                        </td>
                        
                        {{-- Data Cek Kelengkapan --}}
                        @php
                            $isLengkap = !empty($row->no_hp) && 
                                         !empty($row->nama_pemilik_toko) && 
                                         !empty($row->nik_ktp) && 
                                         !empty($row->nama_ktp) && 
                                         !empty($row->foto_ktp) && 
                                         !empty($row->no_rekening) && 
                                         !empty($row->nama_pemilik_norek) && 
                                         !empty($row->latitude) && 
                                         !empty($row->longitude) && 
                                         !empty($row->tampak_depan) && 
                                         !empty($row->tampak_dalam);
                        @endphp
                        <td class="text-center font-bold">
                            @if($isLengkap)
                                <span class="badge badge-sm badge-success text-white">Lengkap</span>
                            @else
                                <span class="badge badge-sm badge-error text-white">Belum</span>
                            @endif
                        </td>
                        
                        <td class="text-center">
                            @if(!empty($row->no_hp))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->nama_pemilik_toko))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->nik_ktp))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->nama_ktp))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->foto_ktp))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->no_rekening))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->nama_pemilik_norek))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->latitude))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->longitude))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->tampak_depan))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($row->tampak_dalam))
                                <x-heroicon-s-check class="w-5 h-5 text-success inline-block" />
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if(count($records) === 0)
                    <tr>
                        <td colspan="18" class="text-center py-8 text-base-content/40">Tidak ada data plan kunjungan ditemukan.</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="p-3 border-t border-base-300 bg-base-100 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="text-xs text-base-content/50 font-medium">
                @if($records->total() > 0)
                    Menampilkan <span class="font-bold text-base-content/70">{{ $records->firstItem() }}</span> –
                    <span class="font-bold text-base-content/70">{{ $records->lastItem() }}</span>
                    dari <span class="font-bold text-primary">{{ number_format($records->total()) }}</span> data
                @else
                    Tidak ada data ditemukan
                @endif
            </div>
            @if($records->hasPages())
                {{ $records->links() }}
            @endif
        </div>
    </div>
</div>
