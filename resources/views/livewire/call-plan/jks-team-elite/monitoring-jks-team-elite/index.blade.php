<div>
    <x-card title="Monitoring JKS Team Elite" icon="clipboard-document-check" class="mb-4" flush="true">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Tim (Kode/Nama)..." class="input input-bordered input-sm w-48 lg:w-64" />
                <label class="text-sm font-medium ml-2">Bulan:</label>
                <input type="month" wire:model.live="filterMonth" class="input input-bordered input-sm" />
            </div>
        </x-slot:actions>

        <style>
            /* Matrix Table UI/UX Revamp */
            .matrix-table { border-collapse: separate; border-spacing: 0; width: 100%; }
            
            /* 2-Axis Sticky Base */
            .matrix-th-top-1 { position: sticky; top: 0; z-index: 40; }
            .matrix-th-top-2 { position: sticky; top: 34px; z-index: 40; }
            .matrix-col-1 { position: sticky; left: 0; z-index: 30; min-width: 84px; max-width: 84px; }
            .matrix-col-2 { position: sticky; left: 84px; z-index: 30; min-width: 132px; max-width: 132px; }
            
            /* Top-Left Intersections */
            .matrix-th-top-1.matrix-col-1, .matrix-th-top-1.matrix-col-2,
            .matrix-th-top-2.matrix-col-1, .matrix-th-top-2.matrix-col-2 {
                z-index: 50; /* Above both sticky rows and columns */
            }
            
            /* Week Boundaries */
            .matrix-week-border { border-right: 2px solid var(--fallback-bc, oklch(var(--bc)/0.15)) !important; }
            .dark .matrix-week-border { border-right: 2px solid var(--fallback-bc, oklch(var(--bc)/0.25)) !important; }
            
            /* Row Hover for Sticky Columns */
            .matrix-row:hover td { background-color: var(--fallback-b2, oklch(var(--b2)/1)) !important; }
        </style>

        @if(empty($filterMonth))
        <div class="flex flex-col items-center justify-center py-24 text-base-content/50 border border-base-200 border-dashed rounded-xl mt-4 bg-base-100/50">
            <x-heroicon-o-calendar-days class="w-16 h-16 mb-4 opacity-30" />
            <h3 class="text-lg font-semibold text-base-content/70">Pilih Bulan</h3>
            <p class="text-sm mt-1">Silakan pilih bulan pada kalender di atas untuk menampilkan data matriks.</p>
        </div>
        @else
        <div class="overflow-auto border border-base-300 bg-base-100 rounded-xl mt-4 mb-4 relative shadow-sm" style="height: 550px;">
            <table class="matrix-table text-xs">
                <thead>
                    <tr>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-1 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Kode Team</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-2 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Nama Team</th>
                        @foreach($weekSpans as $span)
                        <th colspan="{{ $span['colspan'] }}" class="matrix-th-top-1 text-center bg-base-300 border-b border-base-200 {{ $loop->last ? '' : 'matrix-week-border' }} p-0">
                            <div class="flex items-center justify-center h-[34px] w-full uppercase tracking-widest text-[10px] font-bold text-base-content/60">
                                W{{ $span['week_month'] }}
                            </div>
                        </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($monthDates as $date => $dayData)
                        <th class="matrix-th-top-2 text-center p-0 bg-base-300 border-b border-r border-base-200 last:border-r-0 {{ $dayData['is_sunday'] ? 'text-error font-bold bg-error/10' : 'text-base-content/70' }} {{ $dayData['is_end_of_week'] && !$loop->last ? 'matrix-week-border' : '' }}">
                            <div class="flex items-center justify-center h-full w-full whitespace-nowrap">{{ $dayData['label'] }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-base-content/90">
                    @forelse($teams as $index => $team)
                    <tr class="matrix-row transition-colors">
                        <td class="matrix-col-1 font-medium border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}" title="{{ $team->kode_team }}">{{ $team->kode_team }}</td>
                        <td class="matrix-col-2 text-base-content/80 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}" title="{{ $team->nama_team }}">{{ $team->nama_team }}</td>
                        @foreach($monthDates as $date => $dayData)
                        <td class="text-center p-0 border-b border-r border-base-200 last:border-r-0 {{ $dayData['is_sunday'] ? 'bg-error/5' : '' }} {{ $dayData['is_end_of_week'] && !$loop->last ? 'matrix-week-border' : '' }}">
                            <div class="flex items-center justify-center h-full w-full min-h-[32px]">
                                @if(isset($jksData[$team->kode_team][$date]))
                                    @php
                                        $tokoCount = $jksData[$team->kode_team][$date];
                                        $isRed = $dayData['is_weekday'] && $tokoCount < 10;
                                    @endphp
                                    <span class="text-[11px] font-bold {{ $isRed ? 'text-error' : 'text-success' }}">{{ $tokoCount }}</span>
                                @else
                                    <span class="text-base-content/20 text-[10px] font-bold">&middot;</span>
                                @endif
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="99" class="text-center py-12 text-base-content/40 bg-base-100">
                            <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                            <p>Tidak ada data ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </x-card>
</div>
