<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Management Cluster</x-slot>

    {{-- Header / Filter Bar --}}
    @if (session()->has('message'))
        <div class="alert alert-success shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 p-4 shrink-0 flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold">Management Cluster Toko</h2>
        </div>
    </div>

    {{-- Table Cluster --}}
    <div class="bg-base-100 flex-1 min-h-0 rounded-xl shadow-sm border border-base-200 overflow-hidden flex flex-col">
        <div class="overflow-x-auto flex-1 sidebar-scroll">
            <table class="table table-sm table-pin-rows">
                <thead class="bg-base-200 text-base-content font-bold shadow-sm">
                    <tr>
                        <th class="w-10">No</th>
                        <th>Nama Cluster</th>
                        <th>Team Sales</th>
                        <th>Center Store</th>
                        <th>Jumlah Toko</th>
                        <th>Tanggal Dibuat</th>
                        <th class="text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clusters as $index => $cluster)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="font-bold text-center text-base-content/50">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $cluster->name }}</td>
                            <td><span class="badge badge-primary badge-outline badge-sm">{{ $cluster->team_sales }}</span></td>
                            <td class="text-xs">{{ $cluster->center_store_id }}</td>
                            <td>
                                <span class="badge badge-neutral badge-sm">{{ $cluster->items_count }} Toko</span>
                            </td>
                            <td class="text-xs">{{ $cluster->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <div class="flex justify-center items-center gap-2">
                                    <button wire:click="viewCluster({{ $cluster->id }})" class="btn btn-xs btn-info text-white" title="Lihat Toko">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    </button>
                                    <button wire:confirm="Anda yakin ingin menghapus cluster ini? Semua daftar toko di dalamnya akan ikut terhapus." wire:click="deleteCluster({{ $cluster->id }})" class="btn btn-xs btn-error text-white" title="Hapus Cluster">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-base-content/50">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    <p>Belum ada cluster yang dibuat.</p>
                                    <a href="{{ route('call-plan.jks-team-elite.clustering') }}" class="btn btn-sm btn-primary mt-2">Buat Cluster Baru</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal View Cluster Detail --}}
    <div class="modal {{ $isViewModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-box w-11/12 max-w-5xl rounded-2xl relative">
            <button wire:click="closeViewModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-lg mb-2">Detail Cluster: <span class="text-primary">{{ $selectedCluster?->name }}</span></h3>
            <p class="text-xs text-base-content/70 mb-4">Team: {{ $selectedCluster?->team_sales }} | Center: {{ $selectedCluster?->center_store_id }}</p>
            
            <div class="overflow-x-auto bg-base-200 rounded-xl max-h-[60vh] sidebar-scroll border border-base-content/10">
                <table class="table table-sm table-pin-rows">
                    <thead class="bg-base-300 text-base-content shadow-sm">
                        <tr>
                            <th class="w-12 text-center">Rute Ke-</th>
                            <th class="w-24">Kode Toko</th>
                            <th>Nama Toko</th>
                            <th>Jarak (Km)</th>
                            <th>Durasi (Menit)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($selectedClusterItems as $item)
                            <tr class="hover:bg-base-100 transition-colors">
                                <td class="font-bold text-center text-primary">
                                    <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center mx-auto text-xs">
                                        {{ $item['route_order'] }}
                                    </div>
                                </td>
                                <td>{{ $item['toko_id'] }}</td>
                                <td class="font-semibold">{{ $item['toko_name'] }}</td>
                                <td>{{ number_format($item['distance_from_prev_km'], 2) }}</td>
                                <td>{{ number_format($item['duration_from_prev_min'], 1) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Data toko tidak ditemukan di dalam cluster ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeViewModal" class="btn btn-neutral">Tutup</button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeViewModal"></div>
    </div>
</div>
