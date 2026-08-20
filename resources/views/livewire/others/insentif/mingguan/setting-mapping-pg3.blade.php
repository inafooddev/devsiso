<div class="flex-1 flex flex-col w-full h-full min-h-0 gap-4 lg:gap-6">
    <!-- Header Page & Alerts -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0">
        <h1 class="text-xl md:text-2xl font-bold text-base-content">Mapping Grup Produk Mingguan</h1>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success py-2.5 px-4 rounded-xl shadow-sm border border-success/20 text-sm">
            <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-error py-2.5 px-4 rounded-xl shadow-sm border border-error/20 text-sm">
            <x-heroicon-o-x-circle class="w-5 h-5 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Content Panel -->
    <div class="flex-1 flex flex-col min-h-0 bg-base-100 rounded-xl shadow-xl border border-base-300 overflow-hidden">
        <!-- Panel Header -->
        <div class="p-4 border-b border-base-300 bg-base-200/30 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 shrink-0">
            <div>
                <h2 class="font-bold text-lg">Daftar Mapping Grup 3</h2>
                <p class="text-[10px] uppercase font-semibold text-base-content/60 tracking-wider">Jodohkan Data Mingguan ke Bulanan</p>
            </div>
            
            <div class="flex gap-2">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" class="input input-bordered input-sm w-full pl-9 rounded-xl" placeholder="Cari Grup Mingguan...">
                </div>

                <button type="button" class="btn btn-primary btn-sm rounded-xl normal-case" wire:click="saveMappings" wire:loading.attr="disabled">
                    <x-heroicon-o-arrow-down-on-square class="w-4 h-4 mr-1 hidden sm:inline-block" />
                    <span wire:loading.remove wire:target="saveMappings">Simpan Perubahan</span>
                    <span wire:loading wire:target="saveMappings">Menyimpan...</span>
                </button>
            </div>
        </div>
        
        <!-- Panel Info Alert -->
        <div class="p-4 pb-0 shrink-0">
            <div class="alert alert-info py-2 px-3 rounded-lg text-xs shadow-sm flex items-start gap-2 bg-info/10 text-info border border-info/20">
                <x-heroicon-s-information-circle class="w-4 h-4 shrink-0 mt-0.5" />
                <span class="leading-relaxed">Halaman ini digunakan untuk menjodohkan (mapping) nama Grup Produk versi Mingguan ke versi Bulanan. Pilih nama Grup Bulanan pada dropdown di sebelah kanan agar pencapaian Mingguan bisa digabungkan ke Target Bulanan yang sesuai. Kosongkan pilihan jika produk tersebut tidak memiliki target atau tidak perlu digabungkan.</span>
            </div>
        </div>

        <!-- Panel Table -->
        <div class="flex-1 overflow-x-auto custom-scrollbar p-4">
            <table class="table table-sm table-pin-rows table-pin-cols w-full">
                <thead>
                    <tr class="bg-base-200 text-base-content/70">
                        <th class="w-12 text-center">#</th>
                        <th class="w-1/2">Grup Produk (Mingguan dari t_sellingout)</th>
                        <th class="w-1/2">Mapping ke Target VTKP (Bulanan)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @forelse($filteredMingguan as $mingguan => $mapped)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="text-center text-xs text-base-content/50">{{ $no++ }}</td>
                            <td class="font-bold text-sm">{{ $mingguan ?: '(Tanpa Nama)' }}</td>
                            <td class="w-1/2">
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    value: @entangle('mappings.' . $mingguan),
                                    options: {{ json_encode($bulananPg3s) }},
                                    get filteredOptions() {
                                        if (this.search === '') return this.options;
                                        return this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase()));
                                    }
                                }" class="relative w-full max-w-md">
                                    <!-- Trigger -->
                                    <div @click.away="open = false" class="select select-bordered select-sm w-full rounded-lg flex items-center justify-between bg-base-100 hover:border-primary/50 transition-colors px-2 relative">
                                        <div class="flex-1 truncate cursor-pointer text-xs sm:text-sm h-full flex items-center" @click="open = !open" x-text="value ? value : '-- Tidak Dimapping / Kosong --'"></div>
                                        <div class="flex items-center gap-1 shrink-0 bg-base-100 pl-1">
                                            <button type="button" x-show="value" @click.stop="value = ''; search = ''" class="p-0.5 hover:bg-error/20 hover:text-error rounded-md text-base-content/40 transition-colors" title="Hapus Mapping">
                                                <x-heroicon-s-x-mark class="w-4 h-4" />
                                            </button>
                                            <button type="button" @click="open = !open" class="p-0.5 text-base-content/40">
                                                <x-heroicon-s-chevron-down class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Dropdown -->
                                    <div x-show="open" x-cloak x-transition
                                         class="absolute z-50 w-full mt-1 bg-base-100 rounded-lg shadow-xl border border-base-300 max-h-60 flex flex-col origin-top">
                                        <div class="p-2 border-b border-base-300 shrink-0 bg-base-200/50 rounded-t-lg">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-base-content/50" />
                                                </div>
                                                <input x-ref="searchInput" type="text" x-model="search" class="input input-sm input-bordered w-full rounded-md pl-8" placeholder="Cari..." @click.stop x-init="$watch('open', val => { if(val) setTimeout(() => $refs.searchInput.focus(), 100) })">
                                            </div>
                                        </div>
                                        <ul class="flex-1 overflow-y-auto custom-scrollbar py-1 text-sm">
                                            <li class="px-3 py-2 hover:bg-base-200 cursor-pointer text-xs" @click="value = ''; open = false; search = ''">
                                                -- Tidak Dimapping / Kosong --
                                            </li>
                                            <template x-for="opt in filteredOptions" :key="opt">
                                                <li class="px-3 py-1.5 hover:bg-base-200 cursor-pointer transition-colors"
                                                    :class="{'bg-primary/10 text-primary font-bold': value === opt}"
                                                    @click="value = opt; open = false; search = ''">
                                                    <div class="flex items-center justify-between">
                                                        <span x-text="opt"></span>
                                                        <x-heroicon-s-check class="w-4 h-4" x-show="value === opt" />
                                                    </div>
                                                </li>
                                            </template>
                                            <li x-show="filteredOptions.length === 0" class="px-3 py-3 text-xs text-base-content/50 italic text-center">
                                                Tidak ditemukan
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-base-content/50 text-sm italic">
                                Tidak ada data Grup Produk Mingguan yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
