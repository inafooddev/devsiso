<div class="flex-1 flex flex-col w-full h-full min-h-0">
    
    <!-- Filter Section -->
    <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-4 mb-4 shrink-0 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Bulan Proses</label>
            <select wire:model.live="filterBulan" class="select select-bordered select-sm rounded-lg min-w-[140px]">
                <option value="">-- Pilih Bulan --</option>
                @foreach($listBulan as $b)
                    <option value="{{ $b }}">{{ $b }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Region</label>
            <select wire:model.live="filterRegion" class="select select-bordered select-sm rounded-lg min-w-[160px]">
                <option value="">Semua Region</option>
                @foreach($listRegion as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Area</label>
            <select wire:model.live="filterArea" class="select select-bordered select-sm rounded-lg min-w-[160px]" @if($listArea->isEmpty()) disabled @endif>
                <option value="">Semua Area</option>
                @foreach($listArea as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Jabatan</label>
            <select wire:model.live="filterLevel" class="select select-bordered select-sm rounded-lg min-w-[150px]">
                <option value="">Semua Jabatan</option>
                <option value="KACAB">Kepala Cabang</option>
                <option value="SPV">Supervisor (SPV)</option>
                <option value="SE">Sales Executive (SE)</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Pencarian</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, cabang..." class="input input-bordered input-sm rounded-lg min-w-[180px]">
        </div>

        <div class="ml-auto flex items-center gap-4">
            <div wire:loading class="text-xs font-semibold text-primary animate-pulse flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> Mengkalkulasi...
            </div>
            
            <div class="bg-success/10 border border-success/30 rounded-xl px-4 py-2 text-right">
                <p class="text-xs font-bold text-success-content/70">Grand Total THP</p>
                <p class="text-xl font-black text-success">Rp {{ number_format($grandTotalInsentif, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="flex-1 min-h-0 bg-base-100 rounded-xl shadow-xl border border-base-300 overflow-hidden flex flex-col relative">
        @if(!$filterBulan)
            <div class="absolute inset-0 flex items-center justify-center bg-base-200/50 backdrop-blur-sm z-10">
                <div class="text-center">
                    <x-heroicon-o-funnel class="w-12 h-12 text-base-content/30 mx-auto mb-2" />
                    <p class="text-base-content/60 font-semibold">Silakan pilih Bulan Proses terlebih dahulu</p>
                </div>
            </div>
        @endif

        <div class="overflow-y-auto custom-scrollbar flex-1 relative">
            <table class="table table-sm table-pin-rows w-full">
                <thead>
                    <tr class="bg-base-200/80 shadow-sm border-b border-base-300 text-xs font-bold">
                        <th class="bg-base-200/80 w-10 text-center">No</th>
                        <th class="bg-base-200/80">Area</th>
                        <th class="bg-base-200/80">Jabatan</th>
                        <th class="bg-base-200/80">Cabang</th>
                        <th class="bg-base-200/80">Kode</th>
                        <th class="bg-base-200/80">Nama</th>
                        <th class="bg-base-200/80 text-right pr-6">THP</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lastArea = null;
                        $lastCabang = null;
                    @endphp
                    @forelse($summaryData as $index => $row)
                        @php
                            $areaChanged = $row['area_name'] !== $lastArea;
                            $cabangChanged = $row['cabang'] !== $lastCabang || $areaChanged;
                            $lastArea = $row['area_name'];
                            $lastCabang = $row['cabang'];
                        @endphp
                        <tr class="hover:bg-base-200/30 transition-colors border-b border-base-200/50 {{ $areaChanged && $index > 0 ? 'border-t-2 border-t-base-300' : '' }}">
                            <td class="text-center text-xs text-base-content/50">{{ $index + 1 }}</td>
                            <td class="text-xs font-semibold text-base-content/80">
                                {{ $areaChanged ? $row['area_name'] : '' }}
                            </td>
                            <td>
                                @if($row['level'] == 'KACAB')
                                    <span class="badge badge-accent badge-sm font-bold">KACAB</span>
                                @elseif($row['level'] == 'SPV')
                                    <span class="badge badge-secondary badge-sm font-bold">SPV</span>
                                @else
                                    <span class="badge badge-primary badge-sm font-bold">SE</span>
                                @endif
                            </td>
                            <td class="text-xs font-medium text-base-content/70">
                                {{ $cabangChanged ? $row['cabang'] : '' }}
                            </td>
                            <td class="text-xs text-base-content/50 font-mono">{{ $row['kode'] }}</td>
                            <td class="font-semibold text-sm">{{ $row['nama'] }}</td>
                            <td class="text-right pr-6">
                                <span class="font-bold text-success text-sm">Rp {{ number_format($row['thp'], 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-base-content/50">
                                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                <p>Tidak ada data penerima insentif yang ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
