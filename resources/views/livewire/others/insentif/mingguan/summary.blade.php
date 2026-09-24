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
            <label class="block text-xs font-semibold text-base-content/70 mb-1">
                Region
                @if(in_array($accessLevel, ['region','area','supervisor']))
                    <span class="badge badge-warning badge-xs ml-1">Terbatas</span>
                @endif
            </label>
            <select wire:model.live="filterRegion"
                class="select select-bordered select-sm rounded-lg min-w-[160px] {{ in_array($accessLevel, ['region','area','supervisor']) && count($listRegion) === 1 ? 'opacity-70 cursor-not-allowed' : '' }}"
                @if(in_array($accessLevel, ['region','area','supervisor']) && count($listRegion) === 1) disabled @endif>
                @if(!in_array($accessLevel, ['region','area','supervisor']))
                    <option value="">Semua Region</option>
                @endif
                @foreach($listRegion as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">
                Area
                @if(in_array($accessLevel, ['area','supervisor']))
                    <span class="badge badge-warning badge-xs ml-1">Terbatas</span>
                @endif
            </label>
            <select wire:model.live="filterArea"
                class="select select-bordered select-sm rounded-lg min-w-[160px] {{ in_array($accessLevel, ['area','supervisor']) && count($listArea) === 1 ? 'opacity-70 cursor-not-allowed' : '' }}"
                @if($listArea->isEmpty() || (in_array($accessLevel, ['area','supervisor']) && count($listArea) === 1)) disabled @endif>
                @if(!in_array($accessLevel, ['area','supervisor']))
                    <option value="">Semua Area</option>
                @endif
                @foreach($listArea as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Pencarian</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, cabang..." class="input input-bordered input-sm rounded-lg min-w-[180px]">
        </div>

        <div>
            <label class="block text-xs font-semibold text-base-content/70 mb-1">Jabatan</label>
            <div class="join bg-base-100 shadow-sm border border-base-200 rounded-lg">
                <input class="join-item btn btn-sm min-w-[50px] {{ $filterLevel === 'SE' ? 'bg-primary text-primary-content hover:bg-primary' : 'hover:bg-base-200 border-transparent bg-transparent' }}" type="radio" name="jabatan" value="SE" wire:model.live="filterLevel" aria-label="SE" />
                <input class="join-item btn btn-sm min-w-[50px] {{ $filterLevel === 'SPV' ? 'bg-primary text-primary-content hover:bg-primary' : 'hover:bg-base-200 border-transparent bg-transparent' }}" type="radio" name="jabatan" value="SPV" wire:model.live="filterLevel" aria-label="SPV" />
                <input class="join-item btn btn-sm min-w-[50px] {{ $filterLevel === 'KACAB' ? 'bg-primary text-primary-content hover:bg-primary' : 'hover:bg-base-200 border-transparent bg-transparent' }}" type="radio" name="jabatan" value="KACAB" wire:model.live="filterLevel" aria-label="KACAB" />
            </div>
        </div>

        <div class="ml-auto flex items-center gap-4">
            <div wire:loading class="text-xs font-semibold text-primary animate-pulse flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> Mengkalkulasi...
            </div>
        </div>
    </div>

    <!-- KPI Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 shrink-0">
        <!-- SE Card -->
        <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <x-heroicon-o-users class="w-6 h-6" />
            </div>
            <div>
                <p class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">SE Dapat Insentif</p>
                <p class="text-xl font-black text-base-content">
                    {{ $seGetIncentive }} <span class="text-sm font-medium text-base-content/50">/ {{ $totalSe }} Orang</span>
                </p>
            </div>
        </div>

        <!-- SPV Card -->
        <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                <x-heroicon-o-user-group class="w-6 h-6" />
            </div>
            <div>
                <p class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">SPV Dapat Insentif</p>
                <p class="text-xl font-black text-base-content">
                    {{ $spvGetIncentive }} <span class="text-sm font-medium text-base-content/50">/ {{ $totalSpv }} Orang</span>
                </p>
            </div>
        </div>

        <!-- Kacab Card -->
        <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center text-accent shrink-0">
                <x-heroicon-o-briefcase class="w-6 h-6" />
            </div>
            <div>
                <p class="text-xs font-bold text-base-content/60 uppercase tracking-wider mb-1">KACAB Dapat Insentif</p>
                <p class="text-xl font-black text-base-content">
                    {{ $kacabGetIncentive }} <span class="text-sm font-medium text-base-content/50">/ {{ $totalKacab }} Orang</span>
                </p>
            </div>
        </div>

        <!-- Grand Total Card -->
        <div class="bg-success text-success-content rounded-xl shadow-sm p-4 flex flex-col justify-center">
            <p class="text-xs font-bold text-success-content/80 uppercase tracking-wider mb-1">Grand Total Insentif</p>
            <p class="text-2xl font-black">
                Rp {{ number_format($grandTotalInsentif, 0, ',', '.') }}
            </p>
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
                @if($filterLevel === 'SE')
                <!-- TAMPILAN SE -->
                <thead>
                    <tr class="bg-base-200/80 shadow-sm border-b border-base-300 text-xs font-bold">
                        <th class="bg-base-200/80 w-10 text-center">No</th>
                        <th class="bg-base-200/80">Area / Cabang</th>
                        <th class="bg-base-200/80">Distributor</th>
                        <th class="bg-base-200/80">Kode & Nama SE</th>
                        <th class="bg-base-200/80 text-right">Insentif Value (SO)</th>
                        <th class="bg-base-200/80 text-right">Insentif VTKP</th>
                        <th class="bg-base-200/80 text-right">Insentif EC</th>
                        <th class="bg-base-200/80 text-right">Insentif IPT</th>
                        <th class="bg-base-200/80 text-center">Penggunaan SFA</th>
                        <th class="bg-base-200/80 text-right">Total Insentif</th>
                        <th class="bg-base-200/80 text-right pr-6">Estimasi Insentif (Kotor)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryData as $index => $row)
                        <tr class="hover:bg-base-200/30 transition-colors border-b border-base-200/50">
                            <td class="text-center text-xs text-base-content/50">{{ $index + 1 }}</td>
                            <td class="text-xs font-semibold text-base-content/80">
                                {{ $row['area_name'] ?? '-' }} <br/>
                                <span class="font-normal opacity-70">{{ $row['cabang'] ?? '-' }}</span>
                            </td>
                            <td class="text-xs font-medium text-base-content/70">{{ $row['distributor'] ?? '-' }}</td>
                            <td>
                                <div class="font-mono text-xs text-base-content/50">{{ $row['kode'] }}</div>
                                <div class="font-semibold text-sm">{{ $row['nama'] }}</div>
                            </td>
                            @php
                                $soPct = round($row['ach_so'] ?? 0, 1);
                                if ($soPct >= 100) {
                                    $soBg = 'bg-success text-success-content';
                                } elseif ($soPct >= 60) {
                                    $soBg = 'bg-warning text-warning-content';
                                } else {
                                    $soBg = 'bg-error text-white';
                                }

                                $sfaPct = round($row['ach_sfa'] ?? 0, 1);
                                if ($sfaPct >= 95) {
                                    $sfaBg = 'bg-success text-success-content';
                                } else {
                                    $sfaBg = 'bg-error text-white';
                                }
                            @endphp
                            <td class="text-right text-xs">
                                <span class="badge {{ $soBg }} badge-sm font-bold border-0 mb-1">
                                    {{ number_format($soPct, 1, ',', '.') }}%
                                </span>
                                <br/>
                                <span class="opacity-80">Target: Rp {{ number_format($row['target'] ?? 0, 0, ',', '.') }}</span><br/>
                                <span class="opacity-80">Actual: Rp {{ number_format($row['actual'] ?? 0, 0, ',', '.') }}</span><br/>
                                <span class="font-semibold text-primary mt-1 block">Insentif: Rp {{ number_format($row['insentif_value'] ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right text-xs align-middle">Rp {{ number_format($row['insentif_vtkp'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right text-xs align-middle">Rp {{ number_format($row['insentif_ec'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right text-xs align-middle">Rp {{ number_format($row['insentif_ipt'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center text-xs align-middle">
                                <span class="badge {{ $sfaBg }} badge-sm font-bold border-0">
                                    {{ number_format($sfaPct, 1, ',', '.') }}%
                                </span>
                            </td>
                            <td class="text-right text-sm font-semibold text-base-content/60 align-middle">Rp {{ number_format($row['total_kotor'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right text-sm font-bold text-base-content/80 align-middle pr-6">Rp {{ number_format($row['estimasi_insentif'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-12 text-base-content/50">
                                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                <p>Tidak ada data penerima insentif SE yang ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @elseif($filterLevel === 'SPV')
                <!-- TAMPILAN SPV -->
                <thead>
                    <tr class="bg-base-200/80 shadow-sm border-b border-base-300 text-xs font-bold">
                        <th class="bg-base-200/80 w-10 text-center">No</th>
                        <th class="bg-base-200/80">Area / Cabang</th>
                        <th class="bg-base-200/80">Kode & Nama SPV</th>
                        <th class="bg-base-200/80 text-right">Insentif Value (SO)</th>
                        <th class="bg-base-200/80 text-right">Insentif VTKP</th>
                        <th class="bg-base-200/80 text-center">Insentif RWO</th>
                        <th class="bg-base-200/80 text-center">Insentif IPT</th>
                        <th class="bg-base-200/80 text-right">Tabungan (30%)</th>
                        <th class="bg-base-200/80 text-right pr-6">Transfer (70%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryData as $index => $row)
                        <tr class="hover:bg-base-200/30 transition-colors border-b border-base-200/50">
                            <td class="text-center text-xs text-base-content/50">{{ $index + 1 }}</td>
                            <td class="text-xs font-semibold text-base-content/80 align-middle">
                                {{ $row['area_name'] ?? '-' }} <br/>
                                <span class="font-normal opacity-70">{{ $row['cabang'] ?? '-' }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="font-mono text-xs text-base-content/50">{{ $row['kode'] }}</div>
                                <div class="font-semibold text-sm">{{ $row['nama'] }}</div>
                            </td>
                            @php
                                $spvSoPct = round($row['pencapaian_persen'] ?? 0, 1);
                                if ($spvSoPct >= 100) $spvSoBg = 'bg-success text-success-content';
                                elseif ($spvSoPct >= 90) $spvSoBg = 'bg-warning text-warning-content';
                                else $spvSoBg = 'bg-error text-white';

                                $rwoPct = round($row['rwo_achieve_pct'] ?? 0, 1);
                                if ($rwoPct >= 90) $rwoBg = 'bg-success text-success-content';
                                elseif ($rwoPct >= 70) $rwoBg = 'bg-warning text-warning-content';
                                else $rwoBg = 'bg-error text-white';

                                $iptVal = $row['ipt'] ?? 0;
                                if ($iptVal >= 12) $iptBg = 'bg-success text-success-content';
                                elseif ($iptVal >= 5) $iptBg = 'bg-warning text-warning-content';
                                else $iptBg = 'bg-error text-white';
                            @endphp
                            <td class="text-right text-xs">
                                <span class="badge {{ $spvSoBg }} badge-sm font-bold border-0 mb-1">
                                    {{ number_format($spvSoPct, 1, ',', '.') }}%
                                </span>
                                <br/>
                                <span class="opacity-80">Target: Rp {{ number_format($row['target'] ?? 0, 0, ',', '.') }}</span><br/>
                                <span class="opacity-80">Actual: Rp {{ number_format($row['actual'] ?? 0, 0, ',', '.') }}</span><br/>
                                <span class="font-semibold text-primary mt-1 block">Insentif: Rp {{ number_format($row['ins_so'] ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right text-xs align-middle">Rp {{ number_format($row['insentif_vtkp'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center text-xs align-middle">
                                <span class="badge {{ $rwoBg }} badge-sm font-bold border-0 mb-1">
                                    {{ number_format($rwoPct, 1, ',', '.') }}%
                                </span>
                                <br/>
                                <span class="opacity-80">Rp {{ number_format($row['insentif_rwo'] ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center text-xs align-middle">
                                <span class="badge {{ $iptBg }} badge-sm font-bold border-0 mb-1">
                                    {{ $iptVal }} SKU
                                </span>
                                <br/>
                                <span class="opacity-80">Rp {{ number_format($row['insentif_ipt'] ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right text-xs align-middle">Rp {{ number_format($row['tabungan_30'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right text-xs align-middle pr-6">Rp {{ number_format($row['transfer_70'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-base-content/50">
                                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                <p>Tidak ada data penerima insentif SPV yang ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @elseif($filterLevel === 'KACAB')
                <!-- TAMPILAN KACAB -->
                <thead>
                    <tr class="bg-base-200/80 shadow-sm border-b border-base-300 text-xs font-bold">
                        <th class="bg-base-200/80 w-10 text-center">No</th>
                        <th class="bg-base-200/80">Area / Cabang</th>
                        <th class="bg-base-200/80">Kode & Nama KACAB</th>
                        <th class="bg-base-200/80 text-right">Pencapaian SO</th>
                        <th class="bg-base-200/80 text-right pr-6">Total Insentif (Kotor)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summaryData as $index => $row)
                        <tr class="hover:bg-base-200/30 transition-colors border-b border-base-200/50">
                            <td class="text-center text-xs text-base-content/50">{{ $index + 1 }}</td>
                            <td class="text-xs font-semibold text-base-content/80 align-middle">
                                {{ $row['area_name'] ?? '-' }} <br/>
                                <span class="font-normal opacity-70">{{ $row['cabang'] ?? '-' }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="font-mono text-xs text-base-content/50">{{ $row['kode'] }}</div>
                                <div class="font-semibold text-sm">{{ $row['nama'] }}</div>
                            </td>
                            @php
                                $kacabPct = round($row['percentage'] ?? 0, 1);
                                if ($kacabPct >= 100) {
                                    $kacabBg = 'bg-success text-success-content';
                                } elseif ($kacabPct >= 60) {
                                    $kacabBg = 'bg-warning text-warning-content';
                                } else {
                                    $kacabBg = 'bg-error text-white';
                                }
                            @endphp
                            <td class="text-right text-xs align-middle">
                                <span class="badge {{ $kacabBg }} badge-sm font-bold border-0 mb-1">
                                    {{ number_format($kacabPct, 1, ',', '.') }}%
                                </span>
                                <br/>
                                <span class="opacity-80">Target: Rp {{ number_format($row['target'] ?? 0, 0, ',', '.') }}</span><br/>
                                <span class="opacity-80">Actual: Rp {{ number_format($row['sell_out'] ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right text-sm font-bold text-base-content/80 align-middle pr-6">Rp {{ number_format($row['nilai_insentif'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-base-content/50">
                                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                <p>Tidak ada data penerima insentif KACAB yang ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @else
                <!-- TAMPILAN SEMUA JABATAN (MIXED) -->
                <thead>
                    <tr class="bg-base-200/80 shadow-sm border-b border-base-300 text-xs font-bold">
                        <th class="bg-base-200/80 w-10 text-center">No</th>
                        <th class="bg-base-200/80">Area</th>
                        <th class="bg-base-200/80">Jabatan</th>
                        <th class="bg-base-200/80">Cabang</th>
                        <th class="bg-base-200/80">Kode</th>
                        <th class="bg-base-200/80">Nama</th>
                        <th class="bg-base-200/80 text-right pr-6">Estimasi Insentif (Kotor)</th>
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
                                <span class="font-bold text-base-content/80 text-sm">Rp {{ number_format($row['total_kotor'] ?? 0, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-base-content/50">
                                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                <p>Tidak ada data penerima insentif yang ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @endif
            </table>
        </div>
    </div>
</div>
