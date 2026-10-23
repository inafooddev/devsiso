{{-- MODAL PANDUAN, RUMUS NILAI & LOGIKA TEMUAN KUNJUNGAN SE --}}
<div id="audit-logic-modal"
     x-show="showAuditModal" 
     x-cloak
     x-data="{ activeAuditTab: 'formula' }"
     @open-audit-modal.window="showAuditModal = true; if ($event.detail && $event.detail.tab) activeAuditTab = $event.detail.tab"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100000] overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 print:hidden no-print"
     @keydown.escape.window="showAuditModal = false"
     @click.self="showAuditModal = false">

    <div class="bg-base-100 rounded-2xl shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden flex flex-col max-h-[92vh] animate-scale-up"
         @click.stop>
        
        {{-- Header Modal --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-4 sm:p-5 shrink-0 flex items-center justify-between gap-3 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info/20 text-info flex items-center justify-center shrink-0 border border-info/30">
                    <x-heroicon-s-academic-cap class="w-6 h-6" />
                </div>
                <div>
                    <span class="badge badge-xs bg-info/30 text-info uppercase font-bold tracking-wider text-[0.625rem] px-2 py-0.5">
                        Standar Operasional Prosedur (SOP) & Evaluasi Kinerja
                    </span>
                    <h3 class="text-base sm:text-lg font-black tracking-tight mt-0.5">
                        PANDUAN LENGKAP RUMUS RAPORT & LOGIKA TEMUAN
                    </h3>
                    <p class="text-xs text-white/70">
                        Formula Matematis 4 Pilar Penilaian Kinerja, Cara Hitung Manual, dan Kriteria Deteksi Anomali Lapangan
                    </p>
                </div>
            </div>

            <button type="button" 
                @click="showAuditModal = false" 
                class="btn btn-sm btn-circle btn-ghost text-white/70 hover:text-white hover:bg-white/10"
                title="Tutup (Esc)">
                <x-heroicon-m-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Tab Switcher Navigation --}}
        <div class="flex items-center gap-2 p-2.5 bg-base-200/80 border-b border-base-300 px-4 sm:px-6 shrink-0 overflow-x-auto select-none">
            <button type="button" 
                @click="activeAuditTab = 'formula'"
                class="btn btn-xs sm:btn-sm rounded-xl font-bold transition-all gap-1.5 shrink-0"
                :class="activeAuditTab === 'formula' ? 'btn-primary text-white shadow-xs' : 'btn-ghost text-base-content/70 hover:text-base-content hover:bg-base-300/60'">
                <x-heroicon-s-calculator class="w-4 h-4" />
                <span>1. Rumus & Mekanisme Nilai Raport (4 Pilar)</span>
            </button>

            <button type="button" 
                @click="activeAuditTab = 'anomalies'"
                class="btn btn-xs sm:btn-sm rounded-xl font-bold transition-all gap-1.5 shrink-0"
                :class="activeAuditTab === 'anomalies' ? 'btn-primary text-white shadow-xs' : 'btn-ghost text-base-content/70 hover:text-base-content hover:bg-base-300/60'">
                <x-heroicon-s-shield-exclamation class="w-4 h-4" />
                <span>2. Logika Temuan & Anomali Kunjungan</span>
            </button>
        </div>

        {{-- Body Modal (Scrollable) --}}
        <div class="p-4 sm:p-6 overflow-y-auto space-y-5 text-xs text-base-content">
            
            {{-- ======================================================== --}}
            {{-- TAB 1: RUMUS & MEKANISME NILAI RAPORT SE (4 PILAR) --}}
            {{-- ======================================================== --}}
            <div x-show="activeAuditTab === 'formula'" class="space-y-5">
                
                {{-- Banner Rumus Utama --}}
                <div class="bg-gradient-to-r from-primary/10 via-emerald-500/10 to-primary/10 border border-primary/20 rounded-2xl p-4 sm:p-5">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
                            <x-heroicon-s-calculator class="w-4 h-4" />
                            <span>Rumus Induk Nilai Akhir Raport SE</span>
                        </span>
                        <span class="badge badge-primary text-white font-mono font-bold text-xs">Skala 0 - 100</span>
                    </div>

                    <div class="bg-base-100 p-3 sm:p-4 rounded-xl border border-base-300 text-center font-mono font-bold text-xs sm:text-sm text-base-content shadow-inner overflow-x-auto">
                        Total Skor = (Pilar 1 &times; 30%) + (Pilar 2 &times; 30%) + (Pilar 3 &times; 25%) + (Pilar 4 &times; 15%)
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-3 text-center text-xs">
                        <div class="bg-base-100/80 p-2 rounded-lg border border-base-300">
                            <span class="text-emerald-700 dark:text-emerald-400 font-bold block">Pilar 1 (30%)</span>
                            <span class="text-base-content/70 text-[0.625rem]">Produktivitas Kunjungan</span>
                        </div>
                        <div class="bg-base-100/80 p-2 rounded-lg border border-base-300">
                            <span class="text-blue-700 dark:text-blue-400 font-bold block">Pilar 2 (30%)</span>
                            <span class="text-base-content/70 text-[0.625rem]">Efektivitas Penjualan</span>
                        </div>
                        <div class="bg-base-100/80 p-2 rounded-lg border border-base-300">
                            <span class="text-amber-700 dark:text-amber-400 font-bold block">Pilar 3 (25%)</span>
                            <span class="text-base-content/70 text-[0.625rem]">Kepatuhan SOP & Integritas</span>
                        </div>
                        <div class="bg-base-100/80 p-2 rounded-lg border border-base-300">
                            <span class="text-purple-700 dark:text-purple-400 font-bold block">Pilar 4 (15%)</span>
                            <span class="text-base-content/70 text-[0.625rem]">Manajemen Waktu Kerja</span>
                        </div>
                    </div>
                </div>

                {{-- Penjelasan Detail 4 Pilar --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70 flex items-center gap-1.5">
                        <x-heroicon-s-squares-2x2 class="w-4 h-4 text-primary" />
                        <span>Rincian Komponen & Aturan Skoring Tiap Pilar</span>
                    </h4>

                    {{-- PILAR 1 --}}
                    <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="font-bold text-sm text-base-content">Pilar 1: Produktivitas Kunjungan</span>
                            </div>
                            <span class="badge badge-sm bg-emerald-600 text-white font-bold">Bobot: 30%</span>
                        </div>

                        <p class="text-xs text-base-content/80 leading-relaxed">
                            Mengukur seberapa patuh SE menjalankan jadwal rute rencana kunjungan (PJP) dan konsistensi hari aktif kerja selama satu bulan.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 text-xs">
                            {{-- Sub 1.1 --}}
                            <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">1. Kepatuhan Rute PJP (%AC / PC)</span>
                                    <span class="badge badge-xs badge-neutral font-bold">Porsi 80%</span>
                                </div>
                                <div class="text-[0.625rem] text-base-content/70 font-mono bg-base-200/60 p-1.5 rounded">
                                    Rasio = (Total AC / Total PC) &times; 100%
                                </div>
                                <ul class="text-xs space-y-0.5 text-base-content/80 list-disc list-inside">
                                    <li>&ge; 100% &rarr; <strong>100 Poin</strong></li>
                                    <li>90% &ndash; 99.9% &rarr; <strong>85 + ((Rasio - 90) &times; 1.5)</strong></li>
                                    <li>80% &ndash; 89.9% &rarr; <strong>70 + ((Rasio - 80) &times; 1.5)</strong></li>
                                    <li>&lt; 80% &rarr; <strong>(Rasio / 80) &times; 70</strong></li>
                                </ul>
                            </div>

                            {{-- Sub 1.2 --}}
                            <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">2. Kedisiplinan Hari Aktif</span>
                                    <span class="badge badge-xs badge-neutral font-bold">Porsi 20%</span>
                                </div>
                                <div class="text-[0.625rem] text-base-content/70 font-mono bg-base-200/60 p-1.5 rounded">
                                    Skor = (Hari Aktif / Target Hari Kerja) &times; 100%
                                </div>
                                <p class="text-xs text-base-content/70 leading-relaxed">
                                    Target hari kerja diambil dari hari kalender resmi (non-Minggu &amp; non-libur nasional). Maksimal 100 poin.
                                </p>
                            </div>
                        </div>

                        <div class="bg-emerald-500/10 p-2.5 rounded-lg border border-emerald-500/30 text-xs font-mono font-bold text-emerald-900 dark:text-emerald-300 text-center">
                            Skor Pilar 1 = (Skor AC/PC &times; 0.8) + (Skor Hari Aktif &times; 0.2)
                        </div>
                    </div>

                    {{-- PILAR 2 --}}
                    <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                <span class="font-bold text-sm text-base-content">Pilar 2: Efektivitas Penjualan</span>
                            </div>
                            <span class="badge badge-sm bg-blue-600 text-white font-bold">Bobot: 30%</span>
                        </div>

                        <p class="text-xs text-base-content/80 leading-relaxed">
                            Mengukur kemampuan sales mengubah kunjungan menjadi transaksi riil (*Effective Call*) dan kontribusi nilai penjualan (*value order*) dibandingkan rata-rata sales di area yang sama.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 text-xs">
                            {{-- Sub 2.1 --}}
                            <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-blue-700 dark:text-blue-400">1. Strike Rate (EC / AC)</span>
                                    <span class="badge badge-xs badge-neutral font-bold">Porsi 60%</span>
                                </div>
                                <div class="text-[0.625rem] text-base-content/70 font-mono bg-base-200/60 p-1.5 rounded">
                                    SR = (Total EC / Total AC) &times; 100%
                                </div>
                                <ul class="text-xs space-y-0.5 text-base-content/80 list-disc list-inside">
                                    <li>&ge; 50% &rarr; <strong>100 Poin</strong></li>
                                    <li>40% &ndash; 49.9% &rarr; <strong>85 + ((SR - 40) &times; 1.5)</strong></li>
                                    <li>30% &ndash; 39.9% &rarr; <strong>70 + ((SR - 30) &times; 1.5)</strong></li>
                                    <li>20% &ndash; 29.9% &rarr; <strong>55 + ((SR - 20) &times; 1.5)</strong></li>
                                    <li>&lt; 20% &rarr; <strong>SR &times; 2.75</strong> (Min: 20 Poin)</li>
                                </ul>
                            </div>

                            {{-- Sub 2.2 --}}
                            <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-blue-700 dark:text-blue-400">2. Omset vs Benchmark Area</span>
                                    <span class="badge badge-xs badge-neutral font-bold">Porsi 40%</span>
                                </div>
                                <div class="text-[0.625rem] text-base-content/70 font-mono bg-base-200/60 p-1.5 rounded">
                                    Rasio = Omset SE / Rata-rata Omset Area
                                </div>
                                <ul class="text-xs space-y-0.5 text-base-content/80 list-disc list-inside">
                                    <li>&ge; 1.3x (&ge;130% Rata-rata) &rarr; <strong>100 Poin</strong></li>
                                    <li>1.0x &ndash; 1.29x &rarr; <strong>85 + ((Rasio - 1.0) / 0.3 &times; 15)</strong></li>
                                    <li>0.7x &ndash; 0.99x &rarr; <strong>70 + ((Rasio - 0.7) / 0.3 &times; 15)</strong></li>
                                    <li>0.4x &ndash; 0.69x &rarr; <strong>55 + ((Rasio - 0.4) / 0.3 &times; 15)</strong></li>
                                    <li>&lt; 0.4x &rarr; <strong>Rasio &times; 100</strong> (Min: 30 Poin)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-blue-500/10 p-2.5 rounded-lg border border-blue-500/30 text-xs font-mono font-bold text-blue-900 dark:text-blue-300 text-center">
                            Skor Pilar 2 = (Skor Strike Rate &times; 0.6) + (Skor Omset &times; 0.4)
                        </div>
                    </div>

                    {{-- PILAR 3 --}}
                    <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <span class="font-bold text-sm text-base-content">Pilar 3: Kepatuhan SOP &amp; Integritas Lapangan</span>
                            </div>
                            <span class="badge badge-sm bg-amber-600 text-white font-bold">Bobot: 25%</span>
                        </div>

                        <p class="text-xs text-base-content/80 leading-relaxed">
                            Dimulai dari <strong>Modal Awal 100 Poin</strong>. Setiap pelanggaran/anomali kunjungan yang terdeteksi oleh sistem akan memotong poin secara proporsional terhadap frekuensi kunjungan SE tersebut.
                        </p>

                        <div class="overflow-x-auto border border-base-300 rounded-xl bg-base-100">
                            <table class="table table-xs w-full text-xs">
                                <thead class="bg-base-200/70 text-base-content/70">
                                    <tr>
                                        <th>Jenis Pelanggaran / Anomali</th>
                                        <th class="text-center">Tingkat Bahaya</th>
                                        <th class="text-center">Koefisien Bobot</th>
                                        <th>Rumus Potongan Penalti</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-base-200 font-mono">
                                    <tr>
                                        <td class="font-semibold text-rose-600">Check-in di Titik Depo / Distributor</td>
                                        <td class="text-center"><span class="badge badge-xs badge-error text-white font-bold">High</span></td>
                                        <td class="text-center font-bold text-rose-600">3.0x</td>
                                        <td>(Kasus / AC) &times; 100% &times; 3.0</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-rose-600">Titik Koordinat Kembar (Non-Pasar)</td>
                                        <td class="text-center"><span class="badge badge-xs badge-error text-white font-bold">High</span></td>
                                        <td class="text-center font-bold text-rose-600">2.5x</td>
                                        <td>(Kasus / AC) &times; 100% &times; 2.5</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-rose-600">Durasi Kilat Bertransaksi (&lt; 2m &amp; EC = Y)</td>
                                        <td class="text-center"><span class="badge badge-xs badge-error text-white font-bold">High</span></td>
                                        <td class="text-center font-bold text-rose-600">2.0x</td>
                                        <td>(Kasus / AC) &times; 100% &times; 2.0</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-amber-600">Di Luar Radius Master (&gt; 50 Meter)</td>
                                        <td class="text-center"><span class="badge badge-xs badge-warning text-warning-content font-bold">Medium</span></td>
                                        <td class="text-center font-bold text-amber-600">1.0x</td>
                                        <td>(Kasus / AC) &times; 100% &times; 1.0</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-amber-600">Durasi Kunjungan Kilat (&lt; 2m &amp; Non-EC Toko Buka)</td>
                                        <td class="text-center"><span class="badge badge-xs badge-warning text-warning-content font-bold">Medium</span></td>
                                        <td class="text-center font-bold text-amber-600">0.5x</td>
                                        <td>(Kasus / AC) &times; 100% &times; 0.5</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold text-base-content/60">Koordinat GPS Kosong / Blank (0, 0)</td>
                                        <td class="text-center"><span class="badge badge-xs badge-ghost font-bold">Low</span></td>
                                        <td class="text-center font-bold text-base-content/70">0.2x</td>
                                        <td>(Kasus / AC) &times; 100% &times; 0.2</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-base-100 p-2.5 rounded-lg border border-emerald-500/30 text-xs text-emerald-800 dark:text-emerald-300">
                            <strong>Pengecualian Khusus (Bebas Penalti):</strong> Kunjungan toko tutup sah (alasan resmi Toko Tutup / Belum Buka / Tutup Sementara / Permanen / Akses Tertutup &amp; EC = N) <u>100% bebas dari penalti durasi kilat</u>.
                        </div>

                        <div class="bg-amber-500/10 p-2.5 rounded-lg border border-amber-500/30 text-xs font-mono font-bold text-amber-900 dark:text-amber-300 text-center">
                            Skor Pilar 3 = Max(0, 100 - Total Penalti Rate)
                        </div>
                    </div>

                    {{-- PILAR 4 --}}
                    <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                                <span class="font-bold text-sm text-base-content">Pilar 4: Manajemen Waktu Kerja</span>
                            </div>
                            <span class="badge badge-sm bg-purple-600 text-white font-bold">Bobot: 15%</span>
                        </div>

                        <p class="text-xs text-base-content/80 leading-relaxed">
                            Mengukur efisiensi kerja lapangan: proporsi waktu produktif yang dihabiskan di dalam toko pelanggan dibandingkan perjalanan (*traveling*), serta kesesuaian rata-rata durasi singgah per toko.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 text-xs">
                            {{-- Sub 4.1 --}}
                            <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-purple-700 dark:text-purple-400">1. Rasio Waktu di Toko (Store Time)</span>
                                    <span class="badge badge-xs badge-neutral font-bold">Porsi 60%</span>
                                </div>
                                <div class="text-[0.625rem] text-base-content/70 font-mono bg-base-200/60 p-1.5 rounded">
                                    Porsi = (Waktu Toko / Total Jam Kerja) &times; 100%
                                </div>
                                <ul class="text-xs space-y-0.5 text-base-content/80 list-disc list-inside">
                                    <li>&ge; 50% di toko &rarr; <strong>100 Poin</strong></li>
                                    <li>40% &ndash; 49.9% &rarr; <strong>80 + ((Porsi - 40) &times; 2.0)</strong></li>
                                    <li>30% &ndash; 39.9% &rarr; <strong>60 + ((Porsi - 30) &times; 2.0)</strong></li>
                                    <li>&lt; 30% &rarr; <strong>Porsi &times; 2.0</strong> (Min: 25 Poin)</li>
                                </ul>
                            </div>

                            {{-- Sub 4.2 --}}
                            <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-purple-700 dark:text-purple-400">2. Rata-rata Durasi Layanan per Toko</span>
                                    <span class="badge badge-xs badge-neutral font-bold">Porsi 40%</span>
                                </div>
                                <div class="text-[0.625rem] text-base-content/70 font-mono bg-base-200/60 p-1.5 rounded">
                                    Durasi = Total Menit di Toko / Total AC
                                </div>
                                <ul class="text-xs space-y-0.5 text-base-content/80 list-disc list-inside">
                                    <li>7 &ndash; 20 Menit (Ideal SOP) &rarr; <strong>100 Poin</strong></li>
                                    <li>5 &ndash; 6.9 Mnt atau 20.1 &ndash; 30 Mnt &rarr; <strong>85 Poin</strong></li>
                                    <li>3 &ndash; 4.9 Menit &rarr; <strong>70 Poin</strong></li>
                                    <li>&lt; 3 Menit atau &gt; 30 Menit &rarr; <strong>50 Poin</strong></li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-purple-500/10 p-2.5 rounded-lg border border-purple-500/30 text-xs font-mono font-bold text-purple-900 dark:text-purple-300 text-center">
                            Skor Pilar 4 = (Skor Porsi Toko &times; 0.6) + (Skor Durasi &times; 0.4)
                        </div>
                    </div>
                </div>

                {{-- Tabel Predikat Grade --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70 flex items-center gap-1.5 mb-2">
                        <x-heroicon-s-trophy class="w-4 h-4 text-primary" />
                        <span>Standar Penentuan Predikat Grade Akhir</span>
                    </h4>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                        <div class="bg-emerald-500/15 border border-emerald-500/40 p-3 rounded-xl text-emerald-800 dark:text-emerald-300">
                            <span class="text-xl font-black block">Grade A</span>
                            <span class="font-mono font-bold text-xs mt-0.5 block">&ge; 85.0 Poin</span>
                            <span class="text-[0.625rem] text-base-content/70 block mt-1">Sangat Baik (Role Model)</span>
                        </div>
                        <div class="bg-blue-500/15 border border-blue-500/40 p-3 rounded-xl text-blue-800 dark:text-blue-300">
                            <span class="text-xl font-black block">Grade B</span>
                            <span class="font-mono font-bold text-xs mt-0.5 block">70.0 &ndash; 84.9 Poin</span>
                            <span class="text-[0.625rem] text-base-content/70 block mt-1">Baik (Memenuhi SOP)</span>
                        </div>
                        <div class="bg-amber-500/15 border border-amber-500/40 p-3 rounded-xl text-amber-800 dark:text-amber-300">
                            <span class="text-xl font-black block">Grade C</span>
                            <span class="font-mono font-bold text-xs mt-0.5 block">55.0 &ndash; 69.9 Poin</span>
                            <span class="text-[0.625rem] text-base-content/70 block mt-1">Cukup (Perlu Evaluasi)</span>
                        </div>
                        <div class="bg-rose-500/15 border border-rose-500/40 p-3 rounded-xl text-rose-800 dark:text-rose-300">
                            <span class="text-xl font-black block">Grade D</span>
                            <span class="font-mono font-bold text-xs mt-0.5 block">&lt; 55.0 Poin</span>
                            <span class="text-[0.625rem] text-base-content/70 block mt-1">Kurang (Pembinaan Khusus)</span>
                        </div>
                    </div>
                </div>

                {{-- Simulasi Kasus Nyata Hitung Manual --}}
                <div class="bg-base-200/60 border border-base-300 rounded-xl p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-primary/20 text-primary flex items-center justify-center font-bold">
                            &Sigma;
                        </div>
                        <div>
                            <h5 class="font-bold text-sm text-base-content">Contoh Kasus Nyata Hitung Manual (Step-by-Step)</h5>
                            <p class="text-xs text-base-content/60">Simulasi perhitungan manual kinerja Sales Executive "Ahmad" dalam 1 bulan:</p>
                        </div>
                    </div>

                    <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-2 text-xs font-mono leading-relaxed">
                        <div class="text-base-content/70 border-b border-base-200 pb-2">
                            <strong>Data Lapangan Ahmad:</strong><br>
                            &bull; PC = 500 toko | AC = 480 toko | EC = 192 toko<br>
                            &bull; Hari Aktif = 24 hari (Target Kalender = 25 hari)<br>
                            &bull; Omset = Rp 145 Jt (Rata-rata Area = Rp 140 Jt &rarr; Rasio: 1.036x)<br>
                            &bull; Anomali: 0 Depo, 0 Kembar, 5 Luar Radius, 2 Order Kilat, 3 Kilat Non-EC, 0 GPS Blank<br>
                            &bull; Waktu: 54.2% Jam Kerja di Toko, Rata-rata 8.1 Menit / Toko
                        </div>

                        <div class="space-y-1.5 text-xs">
                            <div>
                                <span class="text-emerald-700 dark:text-emerald-400 font-bold">1. PILAR 1:</span>
                                AC/PC = 96.0% (&rarr; 85 + (6 &times; 1.5) = 94.0). Hari Aktif = 24/25 = 96.0.<br>
                                &rArr; Skor P1 = (94.0 &times; 0.8) + (96.0 &times; 0.2) = 75.2 + 19.2 = <strong>94.4</strong>
                            </div>
                            <div>
                                <span class="text-blue-700 dark:text-blue-400 font-bold">2. PILAR 2:</span>
                                Strike Rate = 192/480 = 40.0% (&rarr; 85.0). Omset = 1.036x (&rarr; 85 + (0.036/0.3 &times; 15) = 86.8).<br>
                                &rArr; Skor P2 = (85.0 &times; 0.6) + (86.8 &times; 0.4) = 51.0 + 34.7 = <strong>85.7</strong>
                            </div>
                            <div>
                                <span class="text-amber-700 dark:text-amber-400 font-bold">3. PILAR 3:</span>
                                Penalti: Radius (5/480 &times; 100 &times; 1.0 = 1.04%) + Order Kilat (2/480 &times; 100 &times; 2.0 = 0.83%) + Kilat Non-EC (3/480 &times; 100 &times; 0.5 = 0.31%) = 2.18%.<br>
                                &rArr; Skor P3 = 100 - 2.18 = <strong>97.8</strong>
                            </div>
                            <div>
                                <span class="text-purple-700 dark:text-purple-400 font-bold">4. PILAR 4:</span>
                                Porsi Toko = 54.2% (&ge; 50% &rarr; 100.0). Durasi = 8.1 mnt (&rarr; 100.0).<br>
                                &rArr; Skor P4 = (100 &times; 0.6) + (100 &times; 0.4) = <strong>100.0</strong>
                            </div>
                            <div class="pt-2 border-t border-base-200 text-primary font-bold text-xs">
                                &rArr; TOTAL SKOR = (94.4 &times; 0.30) + (85.7 &times; 0.30) + (97.8 &times; 0.25) + (100.0 &times; 0.15)<br>
                                &rArr; TOTAL SKOR = 28.32 + 25.71 + 24.45 + 15.00 = <span class="text-emerald-600 text-sm">93.5 &rarr; Grade A (Sangat Baik)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: LOGIKA TEMUAN & ANOMALI KUNJUNGAN --}}
            {{-- ======================================================== --}}
            <div x-show="activeAuditTab === 'anomalies'" class="space-y-4">
                
                {{-- Intro Notice Box --}}
                <div class="bg-info/10 border border-info/30 rounded-xl p-3 sm:p-4 flex items-start gap-3 text-info-content dark:text-info">
                    <x-heroicon-s-shield-check class="w-5 h-5 text-info shrink-0 mt-0.5" />
                    <div class="text-xs leading-relaxed">
                        <strong class="font-bold text-xs block text-base-content">Prinsip Logika Temuan yang Adil &amp; Terukur</strong>
                        Sistem deteksi temuan kunjungan dirancang untuk memverifikasi <strong>integritas rute fisik</strong> dan <strong>kejujuran data lapangan</strong>. Sistem dilengkapi <strong>Smart Market Filter</strong> dan <strong>Smart Closed-Store Filter</strong> agar SE yang jujur <u>tidak terkena sanksi fiktif</u>.
                    </div>
                </div>

                {{-- Indikator 1: Koordinat Kembar & Smart Market Filter --}}
                <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                            <span class="font-black text-sm text-base-content">1. Titik Koordinat Kembar (Clone GPS / Fake Visit)</span>
                        </div>
                        <span class="badge badge-sm badge-error text-white font-bold">Severity: High (Penalti 2.5x)</span>
                    </div>

                    <div class="text-xs text-base-content/80 leading-relaxed">
                        Terdeteksi ketika seorang Sales Executive melakukan <em>check-in</em> di <strong>dua atau lebih toko berbeda pada hari yang sama</strong>, namun titik koordinat GPS fisik ponselnya <strong>hampir menempel atau berjarak &le; 3 meter</strong>.
                    </div>

                    {{-- Kotak Smart Market Filter --}}
                    <div class="bg-base-100 rounded-xl p-3 border border-emerald-500/30 bg-emerald-500/5 space-y-2">
                        <div class="flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300 font-bold text-xs">
                            <x-heroicon-s-sparkles class="w-4 h-4 text-emerald-600" />
                            <span>Pengecualian Sah: Smart Market Filter (Area Pasar &amp; Kluster Ruko)</span>
                        </div>
                        <p class="text-xs text-base-content/75 leading-relaxed">
                            Kunjungan dengan titik koordinat kembar (&le; 3m) <strong>OTOMATIS DINYATAKAN SAH / BUKAN TEMUAN ANOMALI</strong> jika memenuhi 2 syarat:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[0.625rem]">
                            <div class="bg-base-200/80 p-2.5 rounded-lg border border-base-300">
                                <span class="font-bold text-emerald-700 dark:text-emerald-400 block mb-0.5">Syarat 1: Master Toko Berdekatan</span>
                                Titik master resmi kedua toko memang terdaftar berdekatan di area pasar yang sama (radius antar master &le; 100 meter).
                            </div>
                            <div class="bg-base-200/80 p-2.5 rounded-lg border border-base-300">
                                <span class="font-bold text-emerald-700 dark:text-emerald-400 block mb-0.5">Syarat 2: Jeda Waktu Check-in Wajar</span>
                                Terdapat jeda waktu singgah wajar antara check-in toko 1 dan toko 2 (&ge; 2 menit / 120 detik) untuk melayani pembeli.
                            </div>
                        </div>
                    </div>

                    {{-- Kriteria Kapan Jadi Temuan --}}
                    <div class="bg-rose-500/10 rounded-xl p-3 border border-rose-500/30 text-xs space-y-1 text-rose-900 dark:text-rose-200">
                        <span class="font-bold block text-rose-700 dark:text-rose-400 text-xs">Kapan Dinyatakan Temuan Koordinat Kembar?</span>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            <li><strong>Jarak master toko aslinya berjauhan (&gt; 100 meter)</strong> &ndash; misal toko beda kelurahan/jalan tapi di-check-in di titik sama persis.</li>
                            <li><strong>Check-in instan / borongan (&lt; 2 menit)</strong> &ndash; check-in toko berikutnya hanya selisih beberapa detik tanpa interaksi riil.</li>
                            <li><strong>Master GPS toko kosong / belum dititikkan (0, 0)</strong> &ndash; rawan disalahgunakan untuk tembak kunjungan.</li>
                        </ul>
                    </div>
                </div>

                {{-- Indikator 2: Kunjungan di Titik Depo --}}
                <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span class="font-black text-sm text-base-content">2. Kunjungan di Titik Depo / Distributor</span>
                        </div>
                        <span class="badge badge-sm badge-error text-white font-bold">Severity: High (Penalti 3.0x)</span>
                    </div>

                    <div class="text-xs text-base-content/80 leading-relaxed">
                        Terdeteksi ketika SE melakukan <em>check-in</em> toko pelanggan dalam <strong>radius &le; 100 meter dari titik koordinat kantor cabang/depo distributor</strong>.
                    </div>
                    <div class="text-[0.625rem] text-base-content/60 bg-base-100 p-2.5 rounded-lg border border-base-300">
                        <strong>Aturan Lapangan:</strong> Sales Executive wajib melakukan kunjungan langsung ke toko fisik pelanggan di rute penugasan, bukan menembak kunjungan atau memesan dari dalam kantor gudang/depo distributor.
                    </div>
                </div>

                {{-- Indikator 3: Di Luar Radius Master (> 50m) --}}
                <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-black text-sm text-base-content">3. Di Luar Radius Master Toko (&gt; 50 Meter)</span>
                        </div>
                        <span class="badge badge-sm badge-warning text-warning-content font-bold">Severity: Medium (Penalti 1.0x)</span>
                    </div>

                    <div class="text-xs text-base-content/80 leading-relaxed">
                        Terdeteksi ketika jarak antara titik <em>check-in</em> fisik SE berjarak <strong>lebih dari 50 meter</strong> dari titik koordinat master toko yang terdaftar resmi di database sistem.
                    </div>
                    <div class="text-[0.625rem] text-base-content/60 bg-base-100 p-2.5 rounded-lg border border-base-300">
                        <strong>Tindakan Perbaikan:</strong> Jika toko telah pindah alamat atau titik master GPS sebelumnya meleset, SE segera melapor ke Supervisor/Admin untuk dilakukan peremajaan (*updating*) koordinat master toko.
                    </div>
                </div>

                {{-- Indikator 4: Kunjungan Kilat (< 2 Menit) & Smart Closed-Store Filter --}}
                <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-black text-sm text-base-content">4. Durasi Kunjungan Kilat (&lt; 2 Menit)</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="badge badge-sm badge-error text-white font-bold">EC = Y: High (Penalti 2.0x)</span>
                            <span class="badge badge-sm badge-warning text-warning-content font-bold">Non-EC: Medium (Penalti 0.5x)</span>
                        </div>
                    </div>

                    <div class="text-xs text-base-content/80 leading-relaxed">
                        Terdeteksi ketika waktu singgah di toko (selisih antara `TIME_IN` dan `TIME_OUT`) <strong>kurang dari 2 menit (120 detik)</strong> untuk toko berstatus berhasil dikunjungi (`FLAG_VISIT = Y`).
                    </div>

                    {{-- Kotak Pengecualian Sah: Smart Closed-Store Filter --}}
                    <div class="bg-base-100 rounded-xl p-3 border border-emerald-500/30 bg-emerald-500/5 space-y-2">
                        <div class="flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300 font-bold text-xs">
                            <x-heroicon-s-sparkles class="w-4 h-4 text-emerald-600" />
                            <span>Pengecualian Sah: Smart Closed-Store Filter (Toko Fisik Tutup)</span>
                        </div>
                        <p class="text-xs text-base-content/75 leading-relaxed">
                            Kunjungan kilat (&lt; 2 menit) <strong>OTOMATIS DINYATAKAN SAH (BUKAN ANOMALI)</strong> jika memenuhi 2 syarat:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[0.625rem]">
                            <div class="bg-base-200/80 p-2.5 rounded-lg border border-base-300">
                                <span class="font-bold text-emerald-700 dark:text-emerald-400 block mb-0.5">Syarat 1: Alasan Resmi Toko Tutup</span>
                                Tercatat alasan sah di aplikasi (Toko Tutup, Toko Belum Buka, Tutup Sementara, Tutup Permanen, atau Akses Tertutup).
                            </div>
                            <div class="bg-base-200/80 p-2.5 rounded-lg border border-base-300">
                                <span class="font-bold text-emerald-700 dark:text-emerald-400 block mb-0.5">Syarat 2: Tanpa Transaksi (EC = N)</span>
                                Tidak ada transaksi penjualan atau cetak order fiktif (`FLAG_EC = N`).
                            </div>
                        </div>
                    </div>

                    {{-- Kriteria Kapan Jadi Temuan --}}
                    <div class="bg-rose-500/10 rounded-xl p-3 border border-rose-500/30 text-xs space-y-1 text-rose-900 dark:text-rose-200">
                        <span class="font-bold block text-rose-700 dark:text-rose-400 text-xs">Kapan Dinyatakan Temuan Durasi Kilat?</span>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            <li><strong>Order Kilat / Cetak Nota Instan (EC = Y)</strong> &ndash; Toko bertransaksi tapi durasi &lt; 2 menit (Sangat Kritis: indikasi transaksi tanpa cek fisik).</li>
                            <li><strong>Toko Buka Tanpa Alasan Tutup Sah (EC = N)</strong> &ndash; Toko buka namun kunjungan hanya beberapa detik tanpa cek stok rak display.</li>
                            <li><strong>Lapor Toko Tutup dari Luar Radius (&gt; 50m)</strong> &ndash; Check-in toko tutup dari jarak jauh tetap terkena anomali "Di Luar Radius".</li>
                        </ul>
                    </div>
                </div>

                {{-- Indikator 5: GPS Kosong / Blank --}}
                <div class="bg-base-200/50 rounded-xl p-4 border border-base-300 space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                            <span class="font-black text-sm text-base-content">5. Koordinat GPS Kosong / Blank (0, 0)</span>
                        </div>
                        <span class="badge badge-sm badge-ghost font-bold">Severity: Low (Penalti 0.2x)</span>
                    </div>

                    <div class="text-xs text-base-content/80 leading-relaxed">
                        Terjadi saat aplikasi kunjungan mencatat koordinat kosong (0, 0) yang umumnya disebabkan oleh izin lokasi smartphone dimatikan, GPS belum terkunci (*lock*), atau kendala teknis perangkat.
                    </div>
                </div>

                {{-- Tips Terbaik untuk Tim Lapangan --}}
                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4 space-y-2">
                    <h5 class="font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5 text-xs uppercase tracking-wider">
                        <x-heroicon-s-light-bulb class="w-4 h-4 text-emerald-600" />
                        <span>Tips Sukses Mencapai Grade A untuk Tim Sales</span>
                    </h5>
                    <ul class="space-y-1.5 text-xs text-base-content/80 list-disc list-inside">
                        <li>Pastikan fitur <strong>GPS / Lokasi Berakurasi Tinggi</strong> selalu aktif sebelum melakukan *check-in*.</li>
                        <li>Lakukan *check-in* saat benar-benar berada di depan/dalam toko pelanggan (maksimal toleransi radius 50m).</li>
                        <li>Di area pasar, layani toko satu per satu secara berurutan dan luangkan waktu interaksi minimal &ge; 2 menit.</li>
                        <li>Jika ada toko tutup atau pindah rute, laporkan alasan resmi melalui fitur *Reason PJP* tanpa membuat kunjungan palsu.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="p-3 sm:p-4 bg-base-200/60 border-t border-base-300 flex items-center justify-between gap-2 shrink-0">
            <span class="text-xs text-base-content/60">
                Sistem Terpadu Validasi &amp; Penilaian Raport Lapangan PT. Fastrata Buana
            </span>
            <button type="button" 
                @click="showAuditModal = false" 
                class="btn btn-sm btn-primary rounded-xl px-6 text-xs font-semibold">
                Saya Mengerti
            </button>
        </div>
    </div>
</div>
