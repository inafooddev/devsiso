<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Pencapaian RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary List Potensi</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi RWO</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Surat Kesepakatan Bersama</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Pencapaian RWO</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Cek Plan Kunjungan</a>
        </div>
    </div>

    @if(isset($activeQuarterLabel))
        <div class="flex justify-between items-end mt-2 mb-1 shrink-0">
            <h2 class="text-lg font-bold text-base-content/80">Ringkasan KPI</h2>
            <div class="badge badge-info badge-outline font-bold">{{ $activeQuarterLabel }}</div>
        </div>
    @endif

    {{-- KPI SUMMARY --}}
    @php
        $totalToko = $kpi->total_toko ?? 0;
        $tokoTransaksi = $kpi->toko_transaksi ?? 0;
        $tokoTransaksiPct = $totalToko > 0 ? ($tokoTransaksi / $totalToko) * 100 : 0;
        
        $overallPct = $kpi->total_target > 0 ? ($kpi->total_achievement / $kpi->total_target) * 100 : 0;
        
        $tokoHijau = $kpi->toko_hijau ?? 0;
        $tokoHijauPct = $totalToko > 0 ? ($tokoHijau / $totalToko) * 100 : 0;
        
        $tokoKuning = $kpi->toko_kuning ?? 0;
        $tokoKuningPct = $totalToko > 0 ? ($tokoKuning / $totalToko) * 100 : 0;
        
        $tokoMerah = $kpi->toko_merah ?? 0;
        $tokoMerahPct = $totalToko > 0 ? ($tokoMerah / $totalToko) * 100 : 0;
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 shrink-0">
        {{-- Card 1: Toko & Transaksi --}}
        <div class="stats shadow bg-base-100 border border-base-300">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Total Toko / Transaksi</div>
                <div class="stat-value text-2xl text-primary">{{ number_format($totalToko, 0, ',', '.') }}</div>
                <div class="stat-desc text-[11px] font-semibold text-primary/80 mt-1">
                    {{ number_format($tokoTransaksi, 0, ',', '.') }} Toko Transaksi ({{ number_format($tokoTransaksiPct, 1, ',', '.') }}%)
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-primary w-full h-1.5" value="{{ min($tokoTransaksiPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>

        {{-- Card 2: Target & Achievement --}}
        <div class="stats shadow bg-base-100 border border-base-300">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Target & Pencapaian</div>
                <div class="stat-value text-xl text-success font-black mt-1">Rp {{ number_format($kpi->total_achievement, 0, ',', '.') }}</div>
                <div class="stat-desc text-[11px] font-semibold text-base-content/70 mt-1">
                    Target Prorata: Rp {{ number_format($kpi->total_target, 0, ',', '.') }}
                </div>
                <div class="w-full mt-2 flex items-center gap-2">
                    <progress class="progress progress-success w-full h-1.5" value="{{ min($overallPct, 100) }}" max="100"></progress>
                    <span class="text-xs font-bold text-success shrink-0">{{ number_format($overallPct, 1, ',', '.') }}%</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Kolom Hijau --}}
        <div class="stats shadow bg-base-100 border border-base-300">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Kolom Hijau (>= 100%)</div>
                <div class="stat-value text-2xl text-success">{{ number_format($tokoHijau, 0, ',', '.') }} <span class="text-xs font-bold opacity-75">Toko</span></div>
                <div class="stat-desc text-[11px] font-semibold text-success/80 mt-1">
                    {{ number_format($tokoHijauPct, 1, ',', '.') }}% dari Total Toko
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-success w-full h-1.5" value="{{ min($tokoHijauPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>

        {{-- Card 4: Kolom Kuning --}}
        <div class="stats shadow bg-base-100 border border-base-300">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Kolom Kuning (80-99%)</div>
                <div class="stat-value text-2xl text-warning">{{ number_format($tokoKuning, 0, ',', '.') }} <span class="text-xs font-bold opacity-75">Toko</span></div>
                <div class="stat-desc text-[11px] font-semibold text-warning/80 mt-1">
                    {{ number_format($tokoKuningPct, 1, ',', '.') }}% dari Total Toko
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-warning w-full h-1.5" value="{{ min($tokoKuningPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>

        {{-- Card 5: Kolom Merah --}}
        <div class="stats shadow bg-base-100 border border-base-300">
            <div class="stat">
                <div class="stat-title text-xs font-bold uppercase tracking-wider text-base-content/60">Kolom Merah (< 80%)</div>
                <div class="stat-value text-2xl text-error">{{ number_format($tokoMerah, 0, ',', '.') }} <span class="text-xs font-bold opacity-75">Toko</span></div>
                <div class="stat-desc text-[11px] font-semibold text-error/80 mt-1">
                    {{ number_format($tokoMerahPct, 1, ',', '.') }}% dari Total Toko
                </div>
                <div class="w-full mt-2">
                    <progress class="progress progress-error w-full h-1.5" value="{{ min($tokoMerahPct, 100) }}" max="100"></progress>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Toolbar / Filters --}}
        <div class="p-4 border-b border-base-300 shrink-0 bg-base-200/30 flex flex-wrap items-end gap-3">
            <div class="form-control min-w-[200px] flex-1">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Cari Toko</span></label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau kode toko..." class="input input-sm input-bordered w-full pl-8" />
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-2.5 text-base-content/50" />
                </div>
            </div>

            <div class="form-control min-w-[120px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Kuartal</span></label>
                <select wire:model="kuartal" class="select select-sm select-bordered">
                    @foreach($kuartals as $q)
                        <option value="{{ $q->quarter }}">Quarter {{ $q->quarter }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control min-w-[130px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Region</span></label>
                <select wire:model.live="region" class="select select-sm select-bordered">
                    <option value="">Semua Region</option>
                    @foreach($regions as $r)
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control min-w-[130px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Area</span></label>
                <select wire:model.live="area" class="select select-sm select-bordered" {{ empty($areas) ? 'disabled' : '' }}>
                    <option value="">Semua Area</option>
                    @foreach($areas as $a)
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control min-w-[130px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Supervisor</span></label>
                <select wire:model.live="supervisor" class="select select-sm select-bordered" {{ empty($supervisors) ? 'disabled' : '' }}>
                    <option value="">Semua Supervisor</option>
                    @foreach($supervisors as $s)
                        <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control min-w-[130px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Distributor</span></label>
                <select wire:model="distributor" class="select select-sm select-bordered" {{ empty($distributors) ? 'disabled' : '' }}>
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $d)
                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-control min-w-[120px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Pengkoloman</span></label>
                <select wire:model="statusProgress" class="select select-sm select-bordered">
                    <option value="Semua">Semua Pengkoloman</option>
                    <option value="1. HIJAU">1. HIJAU (>= 100%)</option>
                    <option value="2. KUNING">2. KUNING (80% - 99%)</option>
                    <option value="3. MERAH">3. MERAH (< 80%)</option>
                </select>
            </div>

            <div class="form-control min-w-[110px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Status SKB</span></label>
                <select wire:model="statusSkb" class="select select-sm select-bordered">
                    <option value="Semua">Semua</option>
                    <option value="Sudah">Sudah SKB</option>
                    <option value="Belum">Belum SKB</option>
                </select>
            </div>

            <div class="form-control min-w-[110px]">
                <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Status Data</span></label>
                <select wire:model="statusData" class="select select-sm select-bordered">
                    <option value="Semua">Semua</option>
                    <option value="Lengkap">Lengkap</option>
                    <option value="Belum">Belum Lengkap</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button wire:click="applyFilter" class="btn btn-sm btn-primary">Filter</button>
                <button wire:click="resetFilter" class="btn btn-sm btn-outline btn-neutral">Reset</button>
            </div>

            <div class="ml-auto shrink-0 self-center">
                <div wire:loading>
                    <span class="loading loading-spinner loading-sm text-primary"></span>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 overflow-auto bg-base-100 relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 shadow-sm">
                    <tr>
                        <th>Distributor</th>
                        <th>Kode Toko</th>
                        <th>Kode PRC</th>
                        <th>Nama Toko</th>
                        <th class="text-center">Status SKB</th>
                        <th class="text-center">Status Data</th>
                        <th class="text-right">Target Total</th>
                        <th class="text-right text-primary">Target Prorata</th>
                        <th class="text-right">Actual Total</th>
                        <th class="text-center">%</th>
                        <th class="text-right">{{ $monthLabels[0] ?? 'Month 1' }}</th>
                        <th class="text-right">{{ $monthLabels[1] ?? 'Month 2' }}</th>
                        <th class="text-right">{{ $monthLabels[2] ?? 'Month 3' }}</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        @php
                            $target = $row->total_target ?? 0;
                            $proratedData = $this->getProratedData($target, $row, $this->appliedKuartal);
                            $percent = $proratedData['percent'];
                            $activeAchievement = $proratedData['active_achievement'];
                            $colorLabel = $proratedData['color_label'];
                            $proratedTarget = $proratedData['prorated_target'];
                            
                            $progressClass = 'progress-error';
                            $textClass = 'text-error font-bold';
                            if ($colorLabel === '1. HIJAU') {
                                $progressClass = 'progress-success';
                                $textClass = 'text-success font-bold';
                            } elseif ($colorLabel === '2. KUNING') {
                                $progressClass = 'progress-warning';
                                $textClass = 'text-warning font-bold';
                            }
                        @endphp
                        <tr class="hover">
                            <td class="max-w-[180px] truncate" title="{{ $row->distributor_name }}">{{ $row->distributor_name ?? '-' }}</td>
                            <td class="font-mono font-bold text-primary">{{ $row->customer_code }}</td>
                            <td class="font-mono text-xs opacity-75">{{ $row->customer_prc ?? '-' }}</td>
                            <td class="font-semibold text-base-content/95">{{ $row->customer_name }}</td>
                            <td class="text-center">
                                @if($row->status_skb === 'Sudah')
                                    @if($row->is_approved === 1 || $row->is_approved === true)
                                        <span class="badge badge-success text-[10px] font-bold text-white py-2">Approved</span>
                                    @elseif($row->is_approved === 0 || $row->is_approved === false)
                                        <span class="badge badge-error text-[10px] font-bold text-white py-2">Rejected</span>
                                    @else
                                        <span class="badge badge-info text-[10px] font-bold text-white py-2">Submitted</span>
                                    @endif
                                @else
                                    <span class="badge badge-ghost text-[10px] font-bold py-2 opacity-60">Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row->status_data_lengkap === 'Lengkap')
                                    <span class="badge badge-success text-[10px] font-bold text-white py-2">Lengkap</span>
                                @else
                                    <span class="badge badge-warning text-[10px] font-bold text-white py-2">Belum</span>
                                @endif
                            </td>
                            <td class="text-right font-mono font-semibold">Rp {{ number_format($target, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs text-base-content/75 font-semibold">Rp {{ number_format($proratedTarget, 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-bold text-success/90">Rp {{ number_format($activeAchievement, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-0.5 min-w-[70px]">
                                    <span class="{{ $textClass }} text-[11px]">{{ number_format($percent, 1, ',', '.') }}%</span>
                                    <progress class="progress {{ $progressClass }} w-14 h-1" value="{{ min($percent, 100) }}" max="100"></progress>
                                </div>
                            </td>
                            <td class="text-right font-mono text-xs text-base-content/75">Rp {{ number_format($row->month_1_value ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs text-base-content/75">Rp {{ number_format($row->month_2_value ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-xs text-base-content/75">Rp {{ number_format($row->month_3_value ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <button wire:click="showStoreDetail('{{ $row->customer_code }}', '{{ $row->distributor_code }}')" class="btn btn-xs btn-primary font-bold">
                                    <x-heroicon-s-information-circle class="w-3.5 h-3.5 mr-0.5" /> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-8">
                                <div class="flex flex-col items-center gap-2 opacity-60">
                                    <x-heroicon-o-building-storefront class="w-12 h-12" />
                                    <span class="font-semibold text-sm">Tidak ada data pencapaian RWO</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($records->hasPages())
            <div class="p-4 border-t border-base-300 shrink-0 bg-base-200/20">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    {{-- DETAIL MODAL --}}
    @if($isDetailModalOpen && $selectedStore)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-4xl bg-base-100 rounded-2xl relative p-6 flex flex-col max-h-[90vh]">
                <button wire:click="closeDetailModal" class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4">✕</button>
                
                <h3 class="font-extrabold text-xl text-base-content border-b pb-3 mb-4">
                    Detail Toko: <span class="text-primary">{{ $selectedStore->customer_name }}</span>
                </h3>

                <div class="flex-1 overflow-y-auto pr-2" x-data="{ tab: 'info' }">
                    {{-- Modal Tabs --}}
                    <div class="tabs tabs-boxed mb-4 bg-base-200 p-1 w-fit">
                        <button type="button" @click="tab = 'info'" class="tab tab-sm" :class="tab === 'info' ? 'tab-active font-bold bg-base-100 shadow-sm' : ''">Informasi & Kelengkapan</button>
                        <button type="button" @click="tab = 'so'" class="tab tab-sm" :class="tab === 'so' ? 'tab-active font-bold bg-base-100 shadow-sm' : ''">Kinerja Sales & Transaksi</button>
                    </div>

                    {{-- TAB 1: Informasi --}}
                    <div x-show="tab === 'info'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-4">
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Profil Toko</h4>
                                <div class="grid grid-cols-3 gap-y-2 text-xs">
                                    <div class="font-semibold text-base-content/60">Kode Toko:</div>
                                    <div class="col-span-2 font-bold">{{ $selectedStore->customer_code }}</div>

                                    <div class="font-semibold text-base-content/60">Alamat:</div>
                                    <div class="col-span-2">{{ $selectedStore->address ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">No HP:</div>
                                    <div class="col-span-2 font-mono font-semibold">{{ $selectedStore->no_hp ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Pemilik Toko:</div>
                                    <div class="col-span-2">{{ $selectedStore->nama_pemilik_toko ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">NIK KTP:</div>
                                    <div class="col-span-2 font-mono">{{ $selectedStore->nik_ktp ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Nama KTP:</div>
                                    <div class="col-span-2">{{ $selectedStore->nama_ktp ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Rekening Bank</h4>
                                <div class="grid grid-cols-3 gap-y-2 text-xs">
                                    <div class="font-semibold text-base-content/60">Nama Bank:</div>
                                    <div class="col-span-2 font-bold">{{ $selectedStore->nama_bank ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">No Rekening:</div>
                                    <div class="col-span-2 font-mono font-bold text-info">{{ $selectedStore->no_rekening ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Pemilik Rekening:</div>
                                    <div class="col-span-2">{{ $selectedStore->nama_pemilik_norek ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                                <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Dokumen & Koordinat GPS</h4>
                                <div class="grid grid-cols-3 gap-y-3 text-xs items-center">
                                    <div class="font-semibold text-base-content/60">Latitude:</div>
                                    <div class="col-span-2 font-mono">{{ $selectedStore->latitude ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Longitude:</div>
                                    <div class="col-span-2 font-mono">{{ $selectedStore->longitude ?? '-' }}</div>

                                    <div class="font-semibold text-base-content/60">Foto KTP:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->foto_ktp)
                                            <a href="/storage/{{ $selectedStore->foto_ktp }}" target="_blank" class="btn btn-xs btn-info font-bold text-white">Lihat KTP</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diunggah</span>
                                        @endif
                                    </div>

                                    <div class="font-semibold text-base-content/60">Foto Toko 2:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->foto_toko2)
                                            <a href="/storage/{{ $selectedStore->foto_toko2 }}" target="_blank" class="btn btn-xs btn-info font-bold text-white">Lihat Foto 2</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diunggah</span>
                                        @endif
                                    </div>

                                    <div class="font-semibold text-base-content/60">Foto Toko 3:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->foto_toko3)
                                            <a href="/storage/{{ $selectedStore->foto_toko3 }}" target="_blank" class="btn btn-xs btn-info font-bold text-white">Lihat Foto 3</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diunggah</span>
                                        @endif
                                    </div>

                                    <div class="font-semibold text-base-content/60">Dokumen SKB:</div>
                                    <div class="col-span-2">
                                        @if($selectedStore->skb_foto)
                                            <a href="/storage/{{ $selectedStore->skb_foto }}" target="_blank" class="btn btn-xs btn-success font-bold text-white">Unduh SKB</a>
                                        @else
                                            <span class="text-rose-500 font-bold">Belum Diajukan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: Transaksi --}}
                    <div x-show="tab === 'so'" class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="card bg-success/10 border border-success/20 p-4 rounded-xl">
                                <span class="text-xs font-bold uppercase tracking-wider text-success">Transaksi Maksimal</span>
                                <span class="text-xl font-bold font-mono mt-1">Rp {{ number_format($selectedStore->max_transaction ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="card bg-info/10 border border-info/20 p-4 rounded-xl">
                                <span class="text-xs font-bold uppercase tracking-wider text-info">Rata-rata Transaksi</span>
                                <span class="text-xl font-bold font-mono mt-1">Rp {{ number_format($selectedStore->avg_transaction ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="card bg-warning/10 border border-warning/20 p-4 rounded-xl">
                                <span class="text-xs font-bold uppercase tracking-wider text-warning">Transaksi Terakhir</span>
                                <div class="flex flex-col mt-1">
                                    <span class="text-sm font-bold font-mono">Rp {{ number_format($selectedStore->last_transaction_value ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-[10px] opacity-70">{{ $selectedStore->last_transaction_date ? date('d-m-Y', strtotime($selectedStore->last_transaction_date)) : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-base-200/50 p-4 rounded-xl border border-base-300">
                            <h4 class="font-bold text-sm text-primary mb-3 uppercase tracking-wide">Grafik / Detail Target Bulanan</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-3 border rounded-lg bg-base-100 flex flex-col">
                                    <span class="text-[10px] uppercase font-bold text-base-content/60">{{ $monthLabels[0] ?? 'Bulan 1' }}</span>
                                    <span class="text-lg font-bold font-mono mt-1 text-base-content/90">Rp {{ number_format($selectedStore->month_1_value ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3 border rounded-lg bg-base-100 flex flex-col">
                                    <span class="text-[10px] uppercase font-bold text-base-content/60">{{ $monthLabels[1] ?? 'Bulan 2' }}</span>
                                    <span class="text-lg font-bold font-mono mt-1 text-base-content/90">Rp {{ number_format($selectedStore->month_2_value ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3 border rounded-lg bg-base-100 flex flex-col">
                                    <span class="text-[10px] uppercase font-bold text-base-content/60">{{ $monthLabels[2] ?? 'Bulan 3' }}</span>
                                    <span class="text-lg font-bold font-mono mt-1 text-base-content/90">Rp {{ number_format($selectedStore->month_3_value ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-action border-t pt-3">
                    <button wire:click="closeDetailModal" class="btn btn-neutral btn-sm">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
