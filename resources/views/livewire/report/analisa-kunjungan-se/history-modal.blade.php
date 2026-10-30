{{-- MODAL HISTORY & REKAM JEJAK RAPOR TAHUNAN SE (12 BULAN) --}}
@if($showHistoryModal && $historyData)
    <div id="raport-history-modal"
         class="fixed inset-0 z-[100000] overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 animate-fade-in print:hidden"
         @keydown.escape.window="$wire.closeHistoryModal()"
         @click.self="$wire.closeHistoryModal()">
        
        <div class="bg-base-100 rounded-2xl shadow-2xl border border-base-300 w-full max-w-[96vw] 2xl:max-w-[1500px] overflow-hidden flex flex-col max-h-[92vh]"
             style="max-width: min(96vw, 1500px);">
            
            {{-- Header Modal --}}
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-4 sm:p-5 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0 border border-sky-400/30">
                        <x-heroicon-s-calendar-days class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-xs bg-sky-500/20 text-sky-300 border border-sky-400/30 uppercase font-bold tracking-wider text-[0.625rem] px-2 py-0.5">
                                Rekam Jejak Kinerja 1 Tahun
                            </span>
                            <span class="text-xs text-white/50">&bull;</span>
                            <span class="text-xs font-semibold text-white/80">Tahun {{ $historyData->year }}</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black tracking-tight mt-0.5 flex items-center gap-2">
                            <span>{{ $historyData->salesman_name }}</span>
                            <span class="text-xs font-mono font-normal text-white/60">({{ $historyData->salesman_code }})</span>
                        </h3>
                        <p class="text-xs text-white/70">
                            {{ $historyData->distributor_name }} ({{ $historyData->distributor_code }}) &bull; {{ $historyData->area_name }}
                        </p>
                    </div>
                </div>

                {{-- Kontrol Tahun & Tombol Tutup --}}
                <div class="flex items-center gap-2.5 self-end sm:self-center">
                    <div class="flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-xl border border-white/20">
                        <label class="text-[0.6875rem] text-white/70 font-semibold uppercase tracking-wider">Tahun:</label>
                        <select wire:change="changeHistoryYear($event.target.value)" 
                                class="select select-xs select-ghost text-white font-bold focus:bg-slate-900 focus:text-white cursor-pointer py-0 h-6 min-h-0 text-xs">
                            @foreach($yearOptions as $yr)
                                <option value="{{ $yr }}" class="bg-slate-900 text-white" {{ $historyData->year == $yr ? 'selected' : '' }}>
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" 
                        wire:click="closeHistoryModal" 
                        class="btn btn-sm btn-circle btn-ghost text-white/70 hover:text-white hover:bg-white/10"
                        title="Tutup (Esc)">
                        <x-heroicon-m-x-mark class="w-5 h-5" />
                    </button>
                </div>
            </div>

            {{-- Body Modal --}}
            <div class="p-4 sm:p-5 overflow-y-auto space-y-4 text-xs text-base-content">
                
                {{-- 4 Executive Summary KPI Cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    
                    {{-- 1. Rata-rata Skor Tahunan --}}
                    <div class="bg-base-200/60 border border-base-300 rounded-xl p-3 flex flex-col justify-between">
                        <span class="text-[0.6875rem] font-bold uppercase tracking-wider text-base-content/60">
                            Rata-Rata Skor Tahunan
                        </span>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-black font-mono text-{{ $historyData->avg_grade_color }}-600">
                                {{ number_format($historyData->avg_score, 1) }}
                            </span>
                            <span class="text-xs text-base-content/50">/ 100</span>
                        </div>
                        <div class="mt-2 pt-2 border-t border-base-300/60 flex items-center justify-between">
                            <span class="badge badge-sm font-bold bg-{{ $historyData->avg_grade_color }}-500/10 text-{{ $historyData->avg_grade_color }}-700 border border-{{ $historyData->avg_grade_color }}-500/30">
                                Grade {{ $historyData->avg_grade }}
                            </span>
                            <span class="text-[0.625rem] text-base-content/60">
                                {{ $historyData->active_months_count }} bln aktif
                            </span>
                        </div>
                    </div>

                    {{-- 2. Bulan Performa Terbaik --}}
                    <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-xl p-3 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[0.6875rem] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                                Performa Terbaik
                            </span>
                            <x-heroicon-s-arrow-trending-up class="w-4 h-4 text-emerald-600" />
                        </div>
                        <div class="mt-1">
                            <div class="text-base font-black text-emerald-700 dark:text-emerald-400">
                                {{ $historyData->peak_month ? $historyData->peak_month->month_name : '-' }}
                            </div>
                            <div class="text-xs font-mono font-bold text-emerald-600 mt-0.5">
                                {{ $historyData->peak_month ? 'Skor ' . number_format($historyData->peak_month->skor_total, 1) : '-' }}
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-emerald-500/20 flex items-center justify-between text-[0.625rem] text-emerald-800/80 dark:text-emerald-300/80">
                            <span>Status Mutu</span>
                            <span class="font-bold">
                                {{ $historyData->peak_month ? 'Grade ' . $historyData->peak_month->grade : '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- 3. Bulan Perlu Perhatian (Terendah) --}}
                    <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-3 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[0.6875rem] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                Perlu Perhatian
                            </span>
                            <x-heroicon-s-arrow-trending-down class="w-4 h-4 text-amber-600" />
                        </div>
                        <div class="mt-1">
                            <div class="text-base font-black text-amber-700 dark:text-amber-400">
                                {{ $historyData->lowest_month ? $historyData->lowest_month->month_name : '-' }}
                            </div>
                            <div class="text-xs font-mono font-bold text-amber-600 mt-0.5">
                                {{ $historyData->lowest_month ? 'Skor ' . number_format($historyData->lowest_month->skor_total, 1) : '-' }}
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-amber-500/20 flex items-center justify-between text-[0.625rem] text-amber-800/80 dark:text-amber-300/80">
                            <span>Status Mutu</span>
                            <span class="font-bold">
                                {{ $historyData->lowest_month ? 'Grade ' . $historyData->lowest_month->grade : '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- 4. Akumulasi Omset Tahunan --}}
                    <div class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-3 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[0.6875rem] font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300">
                                Akumulasi Omset
                            </span>
                            <x-heroicon-s-currency-dollar class="w-4 h-4 text-blue-600" />
                        </div>
                        <div class="mt-1">
                            <div class="text-base font-black text-blue-700 dark:text-blue-400 font-mono">
                                Rp {{ number_format($historyData->total_omset) }}
                            </div>
                            <div class="text-[0.6875rem] text-base-content/70 mt-0.5">
                                Rata-rata: Rp {{ number_format($historyData->avg_monthly_omset) }}/bln
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-blue-500/20 flex items-center justify-between text-[0.625rem] text-blue-800/80 dark:text-blue-300/80">
                            <span>Pencapaian Target</span>
                            <span class="font-bold font-mono">
                                {{ $historyData->avg_pct_omset }}% / bln
                            </span>
                        </div>
                    </div>

                </div>

                {{-- Tabel Rekapitulasi Rapor 12 Bulan --}}
                <div class="border border-base-300 rounded-xl overflow-hidden bg-base-100 shadow-xs">
                    <div class="bg-base-200/50 px-3.5 py-2.5 border-b border-base-300 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-heroicon-s-table-cells class="w-4 h-4 text-primary" />
                            <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80">
                                Rekapitulasi Nilai Rapor Per Bulan &bull; Tahun {{ $historyData->year }}
                            </h4>
                        </div>
                        <span class="text-[0.6875rem] text-base-content/50">
                            Bobot: P1 (20%) &bull; P2 (40%) &bull; P3 (25%) &bull; P4 (15%)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-xs w-full">
                            <thead>
                                <tr class="bg-base-200/80 text-[0.6875rem] uppercase text-base-content/70 border-b border-base-300">
                                    <th class="text-center w-10">No</th>
                                    <th>Bulan</th>
                                    <th class="text-center min-w-[100px]">Call (PC / AC)</th>
                                    <th class="text-center min-w-[70px]">Hari Aktif</th>
                                    <th class="text-center min-w-[90px]">Strike Rate</th>
                                    <th class="text-right min-w-[120px]">Omset (Rp)</th>
                                    <th class="text-center min-w-[75px] bg-emerald-500/5">Pilar 1<br><span class="text-[0.6rem] font-normal normal-case">Produktivitas (20%)</span></th>
                                    <th class="text-center min-w-[75px] bg-blue-500/5">Pilar 2<br><span class="text-[0.6rem] font-normal normal-case">Penjualan (40%)</span></th>
                                    <th class="text-center min-w-[75px] bg-amber-500/5">Pilar 3<br><span class="text-[0.6rem] font-normal normal-case">Integritas (25%)</span></th>
                                    <th class="text-center min-w-[75px] bg-purple-500/5">Pilar 4<br><span class="text-[0.6rem] font-normal normal-case">Waktu Kerja (15%)</span></th>
                                    <th class="text-center min-w-[75px] bg-indigo-500/10">Skor Akhir</th>
                                    <th class="text-center min-w-[65px]">Grade</th>
                                    <th class="text-center min-w-[60px]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200 font-sans text-xs">
                                @foreach($historyData->months as $m => $d)
                                    <tr class="hover:bg-base-200/40 transition-colors {{ !$d->has_activity ? 'opacity-60 bg-base-200/20' : '' }}">
                                        {{-- No --}}
                                        <td class="text-center font-mono text-base-content/60">
                                            {{ $m }}
                                        </td>

                                        {{-- Bulan --}}
                                        <td class="font-bold">
                                            <div class="flex items-center gap-1.5">
                                                @if($d->has_activity)
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                    <span class="text-base-content">{{ $d->month_name }}</span>
                                                @elseif($d->is_future)
                                                    <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                                    <span class="text-base-content/50 italic">{{ $d->month_name }}</span>
                                                @else
                                                    <span class="w-2 h-2 rounded-full bg-rose-400 shrink-0"></span>
                                                    <span class="text-base-content/70">{{ $d->month_name }}</span>
                                                @endif
                                            </div>
                                        </td>

                                        @if($d->has_activity)
                                            {{-- Call PC vs AC --}}
                                            <td class="text-center font-mono">
                                                <div class="font-semibold">{{ $d->total_ac }} / {{ $d->total_pc }}</div>
                                                <div class="text-[0.625rem] {{ $d->pct_ac_pc >= 100 ? 'text-emerald-600' : ($d->pct_ac_pc >= 90 ? 'text-blue-600' : 'text-amber-600') }} font-bold">
                                                    {{ $d->pct_ac_pc }}%
                                                </div>
                                            </td>

                                            {{-- Hari Aktif --}}
                                            <td class="text-center font-mono">
                                                <div>{{ $d->hari_aktif }} Hari</div>
                                                <div class="text-[0.625rem] text-base-content/50">Tgt: {{ $d->target_hari_kerja }}</div>
                                            </td>

                                            {{-- Strike Rate --}}
                                            <td class="text-center font-mono">
                                                <div>{{ $d->total_ec }} Toko</div>
                                                <div class="text-[0.625rem] text-base-content/60 font-semibold">{{ $d->strike_rate }}%</div>
                                            </td>

                                            {{-- Omset Penjualan --}}
                                            <td class="text-right font-mono">
                                                <div class="font-semibold">Rp {{ number_format($d->total_value) }}</div>
                                                <div class="text-[0.625rem] text-base-content/60">{{ $d->pct_omset }}% Target</div>
                                            </td>

                                            {{-- Pilar 1 --}}
                                            <td class="text-center font-mono font-bold bg-emerald-500/5 text-emerald-700 dark:text-emerald-400">
                                                {{ number_format($d->score_pilar_1, 1) }}
                                            </td>

                                            {{-- Pilar 2 --}}
                                            <td class="text-center font-mono font-bold bg-blue-500/5 text-blue-700 dark:text-blue-400">
                                                {{ number_format($d->score_pilar_2, 1) }}
                                            </td>

                                            {{-- Pilar 3 --}}
                                            <td class="text-center font-mono font-bold bg-amber-500/5 text-amber-700 dark:text-amber-400">
                                                {{ number_format($d->score_pilar_3, 1) }}
                                            </td>

                                            {{-- Pilar 4 --}}
                                            <td class="text-center font-mono font-bold bg-purple-500/5 text-purple-700 dark:text-purple-400">
                                                {{ number_format($d->score_pilar_4, 1) }}
                                            </td>

                                            {{-- Skor Total --}}
                                            <td class="text-center font-mono font-black text-xs bg-indigo-500/10 text-{{ $d->grade_color }}-600">
                                                {{ number_format($d->skor_total, 1) }}
                                            </td>

                                            {{-- Grade --}}
                                            <td class="text-center">
                                                <span class="badge badge-sm font-black bg-{{ $d->grade_color }}-500/15 text-{{ $d->grade_color }}-700 dark:text-{{ $d->grade_color }}-300 border border-{{ $d->grade_color }}-500/30">
                                                    {{ $d->grade }}
                                                </span>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="text-center">
                                                <button type="button" 
                                                    wire:click="openRaportFromHistory('{{ $historyData->salesman_code }}', '{{ $historyData->distributor_code }}', {{ $d->month }}, {{ $historyData->year }})" 
                                                    class="btn btn-ghost btn-xs btn-circle text-primary hover:bg-primary/10"
                                                    title="Lihat Lembar Rapor {{ $d->month_name }} {{ $historyData->year }}">
                                                    <x-heroicon-m-document-text class="w-4 h-4" />
                                                </button>
                                            </td>
                                        @else
                                            {{-- Status Kosong / Belum Berjalan --}}
                                            <td colspan="11" class="text-center text-xs py-2">
                                                @if($d->is_future)
                                                    <span class="badge badge-xs bg-base-300 text-base-content/50 border-none font-medium">
                                                        Belum Berjalan
                                                    </span>
                                                @else
                                                    <span class="badge badge-xs bg-rose-500/10 text-rose-600 border border-rose-500/20 font-medium">
                                                        Tidak Ada Aktivitas Kunjungan (Skor: 0)
                                                    </span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>

                            {{-- Summary Footer --}}
                            <tfoot class="bg-base-200 font-sans border-t-2 border-base-300 font-bold text-xs text-base-content">
                                <tr>
                                    <td colspan="2" class="uppercase text-left font-black tracking-wider py-2.5">
                                        Rata-rata / Total {{ $historyData->year }}
                                    </td>
                                    <td class="text-center font-mono">
                                        <div>{{ number_format($historyData->total_ac) }} / {{ number_format($historyData->total_pc) }}</div>
                                        <div class="text-[0.625rem] text-base-content/60">{{ $historyData->overall_pct_ac_pc }}%</div>
                                    </td>
                                    <td class="text-center font-mono">
                                        <span class="text-[0.6875rem] text-base-content/60">{{ $historyData->active_months_count }} bln aktif</span>
                                    </td>
                                    <td class="text-center font-mono">
                                        <div>{{ number_format($historyData->total_ec) }} Toko</div>
                                        <div class="text-[0.625rem] text-base-content/60">{{ $historyData->overall_strike_rate }}%</div>
                                    </td>
                                    <td class="text-right font-mono">
                                        <div>Rp {{ number_format($historyData->total_omset) }}</div>
                                        <div class="text-[0.625rem] text-base-content/60">{{ $historyData->avg_pct_omset }}% Tgt/bln</div>
                                    </td>
                                    <td colspan="4" class="text-center text-[0.6875rem] text-base-content/60 uppercase tracking-wider">
                                        Rata-Rata Skor Tahunan
                                    </td>
                                    <td class="text-center font-mono font-black text-sm bg-indigo-500/20 text-{{ $historyData->avg_grade_color }}-600">
                                        {{ number_format($historyData->avg_score, 1) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-md font-black bg-{{ $historyData->avg_grade_color }}-600 text-white border-none shadow-xs">
                                            {{ $historyData->avg_grade }}
                                        </span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Footer Modal --}}
            <div class="p-3 sm:p-4 bg-base-200/50 border-t border-base-300 flex items-center justify-between gap-2 shrink-0">
                <span class="text-[0.6875rem] text-base-content/60 flex items-center gap-1">
                    <x-heroicon-m-information-circle class="w-3.5 h-3.5 text-info" />
                    <span>Perhitungan 4 Pilar resmi: Produktivitas (20%), Penjualan (40%), SOP & Integritas (25%), Durasi Lapangan (15%).</span>
                </span>
                
                <button type="button" 
                    wire:click="closeHistoryModal" 
                    class="btn btn-sm btn-primary rounded-xl px-5 text-xs font-semibold cursor-pointer">
                    Tutup
                </button>
            </div>

        </div>
    </div>
@endif
