<div class="bg-base-100 rounded-xl p-3 md:p-4 border border-base-300 shadow-sm shrink-0 flex flex-col gap-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
        
        {{-- Periode & Wilayah Filter --}}
        <div class="flex flex-wrap items-center gap-2">
            
            {{-- Tahun --}}
            <select wire:model.live="selectedYear" class="select select-sm select-bordered bg-base-100 text-xs font-semibold">
                @foreach($years as $y)
                    <option value="{{ $y }}">Tahun {{ $y }}</option>
                @endforeach
            </select>

            {{-- Bulan --}}
            <select wire:model.live="selectedMonth" class="select select-sm select-bordered bg-base-100 text-xs font-semibold">
                @foreach($months as $mNum => $mName)
                    <option value="{{ $mNum }}">Bulan {{ $mName }}</option>
                @endforeach
            </select>

            {{-- Region --}}
            <select wire:model.live="selectedRegion" class="select select-sm select-bordered bg-base-100 text-xs max-w-[150px]">
                <option value="">Semua Region</option>
                @foreach($regionOptions as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>

            {{-- Area --}}
            <select wire:model.live="selectedArea" class="select select-sm select-bordered bg-base-100 text-xs max-w-[150px]">
                <option value="">Semua Area</option>
                @foreach($areaOptions as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>

            {{-- Distributor --}}
            <select wire:model.live="selectedDistributor" class="select select-sm select-bordered bg-base-100 text-xs max-w-[200px]">
                <option value="">Semua Distributor</option>
                @foreach($distributorOptions as $d)
                    <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                @endforeach
            </select>

            {{-- Brand (Khusus Mode Produk) --}}
            @if($viewMode === 'product')
                <select wire:model.live="selectedBrand" class="select select-sm select-bordered bg-base-100 text-xs max-w-[150px]">
                    <option value="">Semua Brand</option>
                    @foreach($brandOptions as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            @endif

        </div>

        {{-- Info Filter Aktif --}}
        <div class="text-xs text-base-content/60 font-medium">
            Analisa data periode: <span class="font-bold text-primary">{{ $months[$selectedMonth] }} {{ $selectedYear }}</span>
        </div>

    </div>
</div>
