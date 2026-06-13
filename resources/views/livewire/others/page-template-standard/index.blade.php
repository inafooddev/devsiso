<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Standar Halaman Master</x-slot>

    {{-- 5 KPI Cards Section (Tetap statis di atas) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0">
            @for ($i = 1; $i <= 5; $i++)
            <div class="bg-base-100 p-3 md:p-4 lg:p-5 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
                {{-- Dekorasi KPI --}}
                <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        @if($i == 1) <x-heroicon-s-users class="w-5 h-5" />
                        @elseif($i == 2) <x-heroicon-s-chart-bar class="w-5 h-5" />
                        @elseif($i == 3) <x-heroicon-s-currency-dollar class="w-5 h-5" />
                        @elseif($i == 4) <x-heroicon-s-document-text class="w-5 h-5" />
                        @else <x-heroicon-s-check-circle class="w-5 h-5" />
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate">KPI Target {{ $i }}</h3>
                        <div class="text-lg md:text-xl font-bold leading-none mt-1 truncate">{{ number_format(rand(1000, 9999)) }}</div>
                    </div>
                </div>
            </div>
            @endfor
        </div>

        {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Data Master Transaksi</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar transaksi berjalan hari ini</p>
                </div>
                
                {{-- Menggunakan flex-wrap agar barisan aksi jatuh secara responsif jika window menyempit / dizoom --}}
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    {{-- Search --}}
                    <x-ui.search-input />
                    
                    {{-- Filter Status --}}
                    <select class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                        <option>Semua Kategori</option>
                        <option>Selesai</option>
                        <option>Tertunda</option>
                    </select>

                    {{-- Actions Button --}}
                    <div class="flex flex-wrap items-center gap-1 md:gap-2">
                        <x-ui.action-button type="filter" />
                        <x-ui.action-button type="import" />
                        <x-ui.action-button type="export" />
                        <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                        <x-ui.action-button type="add" />
                    </div>
                </div>
            </div>

            {{-- Body Card (Tabel Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                {{-- Dihilangkan table-pin-cols untuk mencegah glitch scrollbar saat zoom 150%+ --}}
                {{-- Ditambahkan whitespace-nowrap agar kolom tidak hancur menjadi baris baru saat dizoom --}}
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th class="w-16">No</th>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Nama Klien</th>
                            <th>Cabang</th>
                            <th>Regional</th>
                            <th>Tipe Layanan</th>
                            <th class="text-right">Nominal</th>
                            <th>Status</th>
                            <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @for($i = 1; $i <= 30; $i++)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th>{{ $i }}</th>
                            <td class="font-mono">TRX-{{ date('Ym') }}-{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ date('d M Y', strtotime("-$i days")) }}</td>
                            <td class="font-bold">PT. Klien Maju {{ $i }}</td>
                            <td>Cabang {{ ['Utara', 'Selatan', 'Barat', 'Timur'][rand(0,3)] }}</td>
                            <td>Jawa {{ ['Barat', 'Tengah', 'Timur'][rand(0,2)] }}</td>
                            <td>Layanan {{ ['Reguler', 'Premium', 'VIP'][rand(0,2)] }}</td>
                            <td class="text-right font-mono">Rp {{ number_format(rand(10, 999) * 10000, 0, ',', '.') }}</td>
                            <td>
                                @php $status = ['Sukses' => 'success', 'Pending' => 'warning', 'Batal' => 'error'][['Sukses', 'Pending', 'Batal'][rand(0,2)]]; @endphp
                                <span class="badge badge-sm badge-outline badge-{{ $status }}">{{ ['Sukses', 'Pending', 'Batal'][rand(0,2)] }}</span>
                            </td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    <x-ui.action-button 
                                        type="default" 
                                        icon="eye" 
                                        label="" 
                                        class="btn-ghost text-info hover:bg-info/10 btn-square" 
                                        title="Detail" 
                                    />
                                    <x-ui.action-button 
                                        type="edit" 
                                        class="btn-square" 
                                        title="Edit" 
                                    />
                                    <x-ui.action-button 
                                        type="delete" 
                                        class="btn-square" 
                                        title="Hapus" 
                                    />
                                </div>
                            </th>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            
            {{-- Footer Card (Pagination dummy) --}}
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
                <div class="text-base-content/60 text-center sm:text-left">
                    Menampilkan <span class="font-bold text-base-content">1</span> sampai <span class="font-bold text-base-content">30</span> dari <span class="font-bold text-base-content">156</span> entri
                </div>
                <div class="join">
                    <button class="join-item btn btn-sm btn-disabled">«</button>
                    <button class="join-item btn btn-sm btn-active">1</button>
                    <button class="join-item btn btn-sm">2</button>
                    <button class="join-item btn btn-sm">3</button>
                    <button class="join-item btn btn-sm">4</button>
                    <button class="join-item btn btn-sm">»</button>
                </div>
            </div>

        </div>
    </div>
