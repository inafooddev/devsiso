<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-base-content">Log Aktivitas</h2>
                <p class="text-sm text-base-content/70 mt-1">Pantau riwayat aktivitas user di sistem.</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari log..." class="input input-bordered w-full max-w-xs" />
            </div>
        </div>

        <!-- Tabel Log -->
        <div class="overflow-x-auto bg-base-100 rounded-lg shadow border border-base-200">
            <table class="table w-full">
                <thead class="bg-base-200 text-base-content/70">
                    <tr>
                        <th class="w-48">Waktu</th>
                        <th class="w-48">User</th>
                        <th class="w-32">Aksi</th>
                        <th>Deskripsi</th>
                        <th class="w-32">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="hover:bg-base-200/50">
                        <td class="text-sm whitespace-nowrap">
                            {{ $log->created_at->format('d M Y H:i:s') }}
                        </td>
                        <td>
                            <div class="font-medium">{{ $log->user_name ?: 'System/Guest' }}</div>
                            <div class="text-xs text-base-content/60">{{ $log->user_id }}</div>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-outline badge-primary">{{ $log->action }}</span>
                        </td>
                        <td class="text-sm">
                            {{ $log->description }}
                        </td>
                        <td class="text-xs text-base-content/60 font-mono">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-base-content/50">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="activity" class="w-12 h-12 mb-2 opacity-20"></i>
                                Belum ada log aktivitas.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            @if(method_exists($logs, 'links'))
                {{ $logs->links() }}
            @endif
        </div>
    </div>
</div>
