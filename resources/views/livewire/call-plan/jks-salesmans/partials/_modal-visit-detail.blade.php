{{-- Modal Detail Riwayat Kunjungan & Catatan Alasan (RPT Visit) --}}
@if($showVisitDetailModal)
    <x-ui.modal 
        id="modal-visit-detail" 
        title="Riwayat Kunjungan & Alasan: {{ $visitModalHeader['customer_name'] ?? 'Toko' }}" 
        icon="clock" 
        size="xl" 
        open="true" 
        wire:close="closeVisitDetailModal" 
        position="top"
    >
        <div class="p-1 sm:p-2 flex flex-col gap-4 text-xs">
            
            {{-- Header Profil Toko & Salesman --}}
            <div class="bg-base-200/50 p-3 rounded-xl border border-base-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-col gap-1 min-w-0 max-w-md">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-sm badge-primary font-bold">{{ $visitModalHeader['pilar'] ?? '-' }}</span>
                        <h4 class="text-sm font-bold text-base-content truncate" title="{{ $visitModalHeader['customer_name'] }}">{{ $visitModalHeader['customer_name'] }}</h4>
                    </div>
                    <p class="text-xs text-base-content/70 leading-relaxed truncate" title="{{ $visitModalHeader['alamat'] }}">{{ $visitModalHeader['alamat'] }}</p>
                    <div class="flex items-center gap-2 text-xs text-base-content/50 font-mono">
                        <span>Kode JKS: <strong class="text-base-content/80">{{ $visitModalHeader['uniq_kode'] }}</strong></span>
                        <span>•</span>
                        <span>Kode PRC: <strong class="text-primary">{{ $visitModalHeader['customer_code'] }}</strong></span>
                    </div>
                </div>

                <div class="flex flex-col items-start sm:items-end gap-1 text-xs">
                    <span class="text-base-content/60">Salesman Terjadwal:</span>
                    <span class="font-bold text-primary text-xs sm:text-sm">{{ $visitModalHeader['salesman_name'] }}</span>
                    <span class="text-base-content/50 font-mono text-xs">Target: Rp {{ number_format((float)($visitModalHeader['target'] ?? 0), 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- 4 Stat Cards Ringkasan Akumulasi Bulanan --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <div class="bg-base-100 p-2.5 rounded-xl border border-base-300 shadow-2xs flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-info/10 text-info flex items-center justify-center shrink-0">
                        <x-heroicon-m-map-pin class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-base-content/60 font-medium">Kunjungan Rute (PJP)</div>
                        <div class="text-sm font-black text-base-content">{{ $visitModalSummary['total_visit_route'] ?? 0 }}x Visit</div>
                        @if(($visitModalSummary['total_visit_non_route'] ?? 0) > 0)
                            <div class="text-xs text-warning font-semibold">+{{ $visitModalSummary['total_visit_non_route'] }}x Non-Rute</div>
                        @endif
                    </div>
                </div>

                <div class="bg-base-100 p-2.5 rounded-xl border border-base-300 shadow-2xs flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-success/10 text-success flex items-center justify-center shrink-0">
                        <x-heroicon-m-shopping-cart class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-base-content/60 font-medium">Effective Call (Rute)</div>
                        <div class="text-sm font-black text-success">{{ $visitModalSummary['total_ec'] ?? 0 }}x Order</div>
                        @if((($visitModalSummary['total_ec_all'] ?? 0) - ($visitModalSummary['total_ec'] ?? 0)) > 0)
                            <div class="text-xs text-warning font-semibold">+{{ ($visitModalSummary['total_ec_all'] - $visitModalSummary['total_ec']) }}x Non-Rute</div>
                        @endif
                    </div>
                </div>

                <div class="bg-base-100 p-2.5 rounded-xl border border-base-300 shadow-2xs flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <x-heroicon-m-chart-pie class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-base-content/60 font-medium">Strike Rate (Rute)</div>
                        <div class="text-sm font-black text-primary">{{ $visitModalSummary['strike_rate'] ?? 0 }}%</div>
                        <div class="text-xs text-base-content/40">Evaluasi Call Plan</div>
                    </div>
                </div>

                <div class="bg-base-100 p-2.5 rounded-xl border border-base-300 shadow-2xs flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                        <x-heroicon-m-banknotes class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-base-content/60 font-medium">Total Order (Rute)</div>
                        <div class="text-sm font-black text-emerald-600 truncate" title="Rp {{ number_format((float)($visitModalSummary['total_order_val'] ?? 0), 0, ',', '.') }}">
                            Rp {{ number_format((float)($visitModalSummary['total_order_val'] ?? 0), 0, ',', '.') }}
                        </div>
                        @if(($visitModalSummary['total_order_val_all'] ?? 0) > ($visitModalSummary['total_order_val'] ?? 0))
                            <div class="text-xs text-base-content/50 truncate" title="Total Termasuk Non-Rute: Rp {{ number_format($visitModalSummary['total_order_val_all'], 0, ',', '.') }}">
                                Total All: Rp {{ number_format($visitModalSummary['total_order_val_all'], 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Riwayat Kunjungan Tabel --}}
            <div class="flex flex-col gap-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-base-content/70 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-m-clipboard-document-list class="w-4 h-4 text-primary" />
                        Log Kunjungan Salesman (Periode {{ \Carbon\Carbon::parse(($visitModalHeader['bulan'] ?? now()->format('Y-m')) . '-01')->translatedFormat('F Y') }}):
                    </span>
                    <span class="badge badge-sm badge-ghost font-semibold">{{ count($visitModalHistory) }} Log Kunjungan</span>
                </div>

                <div class="border border-base-300 rounded-xl overflow-hidden bg-base-100 shadow-2xs max-h-80 overflow-y-auto">
                    <table class="table table-xs w-full whitespace-nowrap">
                        <thead class="bg-base-200/80 text-base-content sticky top-0 z-10 text-xs">
                            <tr>
                                <th class="w-8 text-center">No</th>
                                <th>Tanggal & Jam</th>
                                <th>Salesman</th>
                                <th class="text-center">Tipe Visit</th>
                                <th class="text-center">Status Visit</th>
                                <th class="text-center">Status Order (EC)</th>
                                <th>Nilai Pesanan</th>
                                <th>Alasan & Catatan Lapangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            @forelse($visitModalHistory as $idx => $v)
                                <tr class="hover:bg-base-200/40 transition-colors">
                                    <td class="text-center font-bold text-base-content/50">{{ $idx + 1 }}</td>
                                    
                                    {{-- Tanggal & Durasi --}}
                                    <td>
                                        <div class="font-bold text-base-content font-mono">{{ \Carbon\Carbon::parse($v->TANGGAL)->translatedFormat('d M Y') }}</div>
                                        <div class="text-xs text-base-content/60 font-mono">
                                            @if(!empty($v->TIME_IN))
                                                {{ substr($v->TIME_IN, 11, 5) }} - {{ substr($v->TIME_OUT, 11, 5) }} ({{ $v->TIME_CONSUME ?: '-' }})
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Salesman Visit --}}
                                    <td>
                                        <div class="font-medium text-base-content truncate max-w-32">{{ $v->MUNAME ?: $v->MUID }}</div>
                                        <div class="text-xs font-mono text-base-content/50">{{ $v->MUID }}</div>
                                    </td>

                                    {{-- Tipe Kunjungan (Route / Non-Route) --}}
                                    <td class="text-center">
                                        @if($v->FLAG_PJP === 'R')
                                            <span class="badge badge-xs badge-info font-bold text-white shadow-2xs">Route (PJP)</span>
                                        @elseif($v->FLAG_PJP === 'N')
                                            <span class="badge badge-xs badge-warning font-bold shadow-2xs">Non-Rute</span>
                                        @else
                                            <span class="badge badge-xs badge-ghost font-mono">{{ $v->FLAG_PJP ?: '-' }}</span>
                                        @endif
                                    </td>

                                    {{-- Status Visit --}}
                                    <td class="text-center">
                                        @if($v->FLAG_VISIT === 'Y')
                                            <span class="badge badge-xs badge-success font-bold text-white">Visited</span>
                                        @else
                                            <span class="badge badge-xs badge-error font-bold text-white">Unvisited</span>
                                        @endif
                                    </td>

                                    {{-- Status Order (EC) --}}
                                    <td class="text-center">
                                        @if($v->FLAG_EC === 'Y')
                                            <span class="badge badge-xs badge-success font-bold text-white">Order (EC)</span>
                                        @else
                                            <span class="badge badge-xs badge-ghost text-base-content/60 font-semibold">No Order</span>
                                        @endif
                                    </td>

                                    {{-- Nilai Pesanan --}}
                                    <td>
                                        @if((float)($v->ORDER_VAL ?? 0) > 0)
                                            <div class="font-bold text-emerald-600 font-mono">Rp {{ number_format((float)$v->ORDER_VAL, 0, ',', '.') }}</div>
                                            <div class="text-xs text-base-content/60">{{ $v->ORDER_QTY ?? 0 }} Pcs • {{ $v->TOTAL_ITEM ?? 0 }} SKU</div>
                                        @else
                                            <span class="text-base-content/40 font-mono">-</span>
                                        @endif
                                    </td>

                                    {{-- Alasan & Catatan --}}
                                    <td class="whitespace-normal min-w-56 max-w-xs">
                                        @php
                                            // Jangan tampilkan alasan No-Order jika kunjungan ini berhasil Order (EC = Y)
                                            // Jangan tampilkan alasan Unvisited jika kunjungan ini tervisit (VISIT = Y)
                                            $showReason = !empty($v->REASON_DESC) 
                                                && !($v->FLAG_EC === 'Y' && $v->REASON_TYPE === 'BUY')
                                                && !($v->FLAG_VISIT === 'Y' && $v->REASON_TYPE === 'VISIT');
                                        @endphp
                                        @if($showReason)
                                            <div class="flex items-start gap-1">
                                                <span class="badge badge-xs {{ $v->REASON_TYPE === 'VISIT' ? 'badge-error text-white' : 'badge-warning font-bold' }} shrink-0 mt-0.5">
                                                    {{ $v->REASON_TYPE === 'VISIT' ? 'Tidak Kunjung' : 'Tidak Order' }}
                                                </span>
                                                <span class="font-medium text-base-content leading-tight">{{ $v->REASON_DESC }}</span>
                                            </div>
                                            @if(!empty($v->REASON_NOTE))
                                                <div class="text-xs text-base-content/60 italic mt-0.5 ml-1">"{{ $v->REASON_NOTE }}"</div>
                                            @endif
                                        @elseif(!empty($v->NOTES))
                                            <div class="text-xs text-base-content/70 italic">Catatan: {{ $v->NOTES }}</div>
                                        @else
                                            <span class="text-base-content/30 italic text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-base-content/50">
                                        <x-heroicon-o-calendar class="w-8 h-8 mx-auto mb-1 opacity-30 text-primary" />
                                        <p class="font-semibold text-xs">Belum Ada Riwayat Kunjungan Bulan Ini</p>
                                        <p class="text-xs text-base-content/60">Salesman belum mencatatkan riwayat kunjungan di aplikasi Eskalink untuk toko ini pada periode terpilih.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end w-full">
                <button type="button" wire:click="closeVisitDetailModal" class="btn btn-sm btn-ghost rounded-xl text-xs">
                    Tutup
                </button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
@endif
