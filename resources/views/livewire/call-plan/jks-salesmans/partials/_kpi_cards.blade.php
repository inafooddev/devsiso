@if($kpiSummary)
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 shrink-0 mb-4">
    {{-- Card 1: Total Toko --}}
    <div class="bg-base-100 rounded-xl shadow-sm border border-primary p-3 flex flex-col justify-between relative overflow-hidden group">
        <div class="text-[10px] text-primary font-bold uppercase tracking-wider mb-1 z-10 flex items-center justify-between">
            <span>Total RO</span>
            <span class="text-lg font-black leading-none" title="Total Seluruh RO">{{ number_format($kpiSummary->all_ro ?? $kpiSummary->total_toko, 0, ',', '.') }}</span>
        </div>
        
        <div class="flex items-center gap-1 mt-1 z-10">
            <div class="flex-1 bg-success/10 rounded py-1 flex flex-col items-center border border-success/20" title="RO yang sudah masuk Plan (Jadwal)">
                <span class="text-[7px] font-bold text-success uppercase">Plan</span>
                <span class="text-xs font-black text-success">{{ number_format($kpiSummary->total_toko, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex-1 bg-warning/10 rounded py-1 flex flex-col items-center border border-warning/20" title="RO yang belum masuk Plan">
                <span class="text-[7px] font-bold text-warning-content uppercase tracking-tighter">Non Rute</span>
                <span class="text-xs font-black text-warning-content">{{ number_format(($kpiSummary->all_ro ?? $kpiSummary->total_toko) - $kpiSummary->total_toko, 0, ',', '.') }}</span>
            </div>

            <div class="flex-1 bg-error/10 rounded py-1 flex flex-col items-center border border-error/20" title="RO tanpa koordinat GPS">
                <span class="text-[7px] font-bold text-error uppercase">No GPS</span>
                <span class="text-xs font-black text-error">{{ number_format($kpiSummary->non_gps ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        <x-heroicon-o-building-storefront class="w-20 h-20 absolute -right-4 -bottom-4 text-primary/5 pointer-events-none transition-transform group-hover:scale-110" />
    </div>

    {{-- Cards 2-7: Senin to Sabtu --}}
    @php
        $days = [
            ['name' => 'Senin', 'ganjil' => $kpiSummary->h1_ganjil, 'genap' => $kpiSummary->h1_genap, 'nongps' => $kpiSummary->h1_non_gps ?? 0],
            ['name' => 'Selasa', 'ganjil' => $kpiSummary->h2_ganjil, 'genap' => $kpiSummary->h2_genap, 'nongps' => $kpiSummary->h2_non_gps ?? 0],
            ['name' => 'Rabu', 'ganjil' => $kpiSummary->h3_ganjil, 'genap' => $kpiSummary->h3_genap, 'nongps' => $kpiSummary->h3_non_gps ?? 0],
            ['name' => 'Kamis', 'ganjil' => $kpiSummary->h4_ganjil, 'genap' => $kpiSummary->h4_genap, 'nongps' => $kpiSummary->h4_non_gps ?? 0],
            ['name' => 'Jumat', 'ganjil' => $kpiSummary->h5_ganjil, 'genap' => $kpiSummary->h5_genap, 'nongps' => $kpiSummary->h5_non_gps ?? 0],
            ['name' => 'Sabtu', 'ganjil' => $kpiSummary->h6_ganjil, 'genap' => $kpiSummary->h6_genap, 'nongps' => $kpiSummary->h6_non_gps ?? 0],
        ];
    @endphp

    @foreach($days as $day)
    <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-3 flex flex-col justify-between relative overflow-hidden group">
        <div class="text-[10px] text-base-content/80 font-bold uppercase tracking-wider mb-1">{{ $day['name'] }}</div>
        
        <div class="flex items-center gap-1 mt-1 z-10">
            <div class="flex-1 bg-primary/10 rounded py-1 flex flex-col items-center border border-primary/20">
                <span class="text-[7px] font-bold text-primary uppercase">Ganjil</span>
                <span class="text-xs font-black text-primary">{{ number_format($day['ganjil'], 0, ',', '.') }}</span>
            </div>
            
            <div class="flex-1 bg-neutral/10 rounded py-1 flex flex-col items-center border border-neutral/20">
                <span class="text-[7px] font-bold text-neutral uppercase">Genap</span>
                <span class="text-xs font-black text-neutral">{{ number_format($day['genap'], 0, ',', '.') }}</span>
            </div>

            <div class="flex-1 bg-error/10 rounded py-1 flex flex-col items-center border border-error/20" title="RO tanpa koordinat GPS">
                <span class="text-[7px] font-bold text-error uppercase tracking-tighter">No GPS</span>
                <span class="text-xs font-black text-error">{{ number_format($day['nongps'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Background Total Number (Subtle) --}}
        <div class="absolute -right-2 -top-2 text-4xl font-black opacity-5 pointer-events-none transition-transform group-hover:scale-110">
            {{ $day['ganjil'] + $day['genap'] }}
        </div>
    </div>
    @endforeach
</div>
@endif
