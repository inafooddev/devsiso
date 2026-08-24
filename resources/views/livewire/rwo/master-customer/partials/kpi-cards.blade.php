{{-- KPI Cards Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4 shrink-0">
        {{-- Card 1: Sudah Finalisasi Finance --}}
        <div class="relative overflow-hidden p-2.5 bg-base-100 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <div>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-base-content/50 leading-tight block">FINALISASI FINANCE</span>
                    <h3 class="text-lg font-black mt-0.5 text-success leading-none">{{ number_format($kpis['sudah_finalisasi']) }}</h3>
                </div>
                <div class="p-1.5 rounded-lg bg-success/20 text-success shrink-0">
                    <x-heroicon-s-lock-closed class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-0.5 text-[9px] pt-1.5 border-t border-base-200">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Total Toko</span>
                    <span class="font-bold text-base-content">{{ number_format($kpis['total_toko']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Sisa Belum</span>
                    <span class="font-bold text-error">{{ number_format($kpis['total_toko'] - $kpis['sudah_finalisasi']) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Sudah Check SPM --}}
        <div class="relative overflow-hidden p-2.5 bg-base-100 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <div>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-base-content/50 leading-tight block">SUDAH CHECK SPM</span>
                    <h3 class="text-lg font-black mt-0.5 text-primary leading-none">{{ number_format($kpis['total_toko'] - $kpis['tidak_valid']) }}</h3>
                </div>
                <div class="p-1.5 rounded-lg bg-primary/20 text-primary shrink-0">
                    <x-heroicon-s-shield-check class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-0.5 text-[9px] pt-1.5 border-t border-base-200">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Total Toko</span>
                    <span class="font-bold text-base-content">{{ number_format($kpis['total_toko']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Sisa Belum</span>
                    <span class="font-bold text-error">{{ number_format($kpis['tidak_valid']) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Status Complete --}}
        <div class="relative overflow-hidden p-2.5 bg-base-100 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <div>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-base-content/50 leading-tight block">DATA COMPLETE</span>
                    <h3 class="text-lg font-black mt-0.5 text-info leading-none">{{ number_format($kpis['total_lengkap']) }}</h3>
                </div>
                <div class="p-1.5 rounded-lg bg-info/20 text-info shrink-0">
                    <x-heroicon-s-clipboard-document-check class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-0.5 text-[9px] pt-1.5 border-t border-base-200">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Total Toko</span>
                    <span class="font-bold text-base-content">{{ number_format($kpis['total_toko']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Not Complete</span>
                    <span class="font-bold text-error">{{ number_format($kpis['total_belum_lengkap']) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 4: KTP Lengkap --}}
        <div class="relative overflow-hidden p-2.5 bg-base-100 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <div>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-base-content/50 leading-tight block">KTP (Foto+NIK+Nama)</span>
                    <h3 class="text-lg font-black mt-0.5 text-accent leading-none">{{ number_format($kpis['total_toko'] - $kpis['tanpa_data_ktp']) }}</h3>
                </div>
                <div class="p-1.5 rounded-lg bg-accent/20 text-accent shrink-0">
                    <x-heroicon-s-identification class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-0.5 text-[9px] pt-1.5 border-t border-base-200">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Total Toko</span>
                    <span class="font-bold text-base-content">{{ number_format($kpis['total_toko']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Belum Lengkap</span>
                    <span class="font-bold text-error">{{ number_format($kpis['tanpa_data_ktp']) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 5: Rekening Lengkap --}}
        <div class="relative overflow-hidden p-2.5 bg-base-100 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <div>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-base-content/50 leading-tight block">REKENING (NAMA+NOMOR)</span>
                    <h3 class="text-lg font-black mt-0.5 text-info leading-none">{{ number_format($kpis['total_toko'] - $kpis['tanpa_data_rekening']) }}</h3>
                </div>
                <div class="p-1.5 rounded-lg bg-info/20 text-info shrink-0">
                    <x-heroicon-s-credit-card class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-0.5 text-[9px] pt-1.5 border-t border-base-200">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Total Toko</span>
                    <span class="font-bold text-base-content">{{ number_format($kpis['total_toko']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Belum Lengkap</span>
                    <span class="font-bold text-error">{{ number_format($kpis['tanpa_data_rekening']) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 6: Geotag Lengkap --}}
        <div class="relative overflow-hidden p-2.5 bg-base-100 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-1.5">
                <div>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-base-content/50 leading-tight block">GEOTAG (LAT+LONG)</span>
                    <h3 class="text-lg font-black mt-0.5 text-secondary leading-none">{{ number_format($kpis['total_toko'] - $kpis['tanpa_tikor']) }}</h3>
                </div>
                <div class="p-1.5 rounded-lg bg-secondary/20 text-secondary shrink-0">
                    <x-heroicon-s-map-pin class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-0.5 text-[9px] pt-1.5 border-t border-base-200">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Total Toko</span>
                    <span class="font-bold text-base-content">{{ number_format($kpis['total_toko']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-base-content/60">Belum Lengkap</span>
                    <span class="font-bold text-error">{{ number_format($kpis['tanpa_tikor']) }}</span>
                </div>
            </div>
        </div>
    </div>