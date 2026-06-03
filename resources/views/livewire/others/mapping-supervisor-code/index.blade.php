<div>
    <x-slot name="title">Mapping Supervisor/Team Elite</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
        <x-card flush title="Mapping Supervisor/Team Elite" icon="user-group" subtitle="Daftar data mapping team elite dan supervisor code" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari data..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Region Filter --}}
                    <select wire:model.live="regionFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    {{-- Area Filter --}}
                    <select wire:model.live="areaFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300" @if(!$regionFilter) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    {{-- Level Filter --}}
                    <select wire:model.live="levelFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Level</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                        @endforeach
                    </select>
                </div>
            </x-slot:actions>

            <x-ui.table empty="Tidak ada data mapping yang cocok dengan pencarian dan filter Anda.">
                <x-slot:head>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Kode Eska (Team Elite)</th>
                        <th>Nama Eska</th>
                        <th>Kode Siso (Supervisor)</th>
                        <th>Nama Siso</th>
                        <th>Level</th>
                    </tr>
                </x-slot:head>

                @foreach ($data as $index => $item)
                    <tr wire:key="mapping-{{ $index }}" class="group text-sm">
                        <td><span class="text-xs font-semibold text-base-content/40">{{ $data->firstItem() + $index }}</span></td>
                        <td><span class="font-bold text-base-content/80">{{ $item->region_name ?? '-' }}</span></td>
                        <td><span class="font-bold text-base-content/80">{{ $item->area_name ?? '-' }}</span></td>
                        <td><span class="font-mono text-base-content/80">{{ $item->kode_eska ?? '-' }}</span></td>
                        <td><span class="font-bold text-base-content/80">{{ $item->nama_eska ?? '-' }}</span></td>
                        <td><span class="font-mono text-base-content/80">{{ $item->kode_siso ?? '-' }}</span></td>
                        <td><span class="font-bold text-base-content/80">{{ $item->nama_siso ?? '-' }}</span></td>
                        <td>
                            @if($item->level)
                                <span class="badge badge-sm badge-outline">{{ ucfirst($item->level) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if($data->hasPages())
                <div class="mt-4 px-6">{{ $data->links() }}</div>
            @endif
        </x-card>
    </div>
</div>
