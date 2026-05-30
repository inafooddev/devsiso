<div>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-100 p-6 rounded-xl border border-base-200 shadow-sm">
            <div>
                <h2 class="text-2xl font-bold text-base-content flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-7 h-7 text-primary" />
                    Log Aktivitas
                </h2>
                <p class="text-sm text-base-content/60 mt-1">Pantau dan analisis riwayat aktivitas pengguna di sistem secara real-time.</p>
            </div>
            <div>
                <x-ui.button 
                    variant="success" 
                    icon="arrow-down-tray" 
                    wire:click="export" 
                    wire:loading.attr="disabled"
                    wire:target="export"
                >
                    Export Excel
                </x-ui.button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-base-100 p-5 rounded-xl border border-base-200 shadow-sm">
            <h3 class="text-sm font-semibold text-base-content/80 mb-4 flex items-center gap-2">
                <x-heroicon-o-funnel class="w-4 h-4 text-primary" />
                Filter Data
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                <!-- Search -->
                <div class="form-control w-full sm:col-span-2 md:col-span-1">
                    <label class="label py-1"><span class="label-text text-xs font-semibold text-base-content/70">Keyword</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-base-content/40">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </span>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Cari user, aksi, deskripsi..." 
                            class="input input-bordered w-full pl-10 text-sm focus:input-primary" 
                        />
                    </div>
                </div>

                <!-- Action Filter -->
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs font-semibold text-base-content/70">Aksi</span></label>
                    <select wire:model.live="actionFilter" class="select select-bordered w-full text-sm focus:select-primary">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}">{{ strtoupper($act) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs font-semibold text-base-content/70">Mulai Tanggal</span></label>
                    <input 
                        type="date" 
                        wire:model.live="dateFrom" 
                        class="input input-bordered w-full text-sm focus:input-primary" 
                    />
                </div>

                <!-- Date To -->
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs font-semibold text-base-content/70">Sampai Tanggal</span></label>
                    <input 
                        type="date" 
                        wire:model.live="dateTo" 
                        class="input input-bordered w-full text-sm focus:input-primary" 
                    />
                </div>

                <!-- Reset Button -->
                <div class="form-control w-full">
                    <x-ui.button 
                        variant="ghost" 
                        outline 
                        class="w-full text-sm border-base-300 hover:border-error hover:text-error" 
                        wire:click="resetFilters" 
                        icon="arrow-path"
                    >
                        Reset Filter
                    </x-ui.button>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 overflow-hidden">
            <x-ui.table empty="Belum ada log aktivitas." emptyIcon="clock" :loading="false">
                <x-slot:head>
                    <tr>
                        <th class="w-48">WAKTU</th>
                        <th class="w-64">USER</th>
                        <th class="w-40">AKSI</th>
                        <th>DESKRIPSI</th>
                        <th class="w-56">IP & PERANGKAT</th>
                    </tr>
                </x-slot:head>

                @foreach($logs as $log)
                    <tr>
                        <!-- Waktu -->
                        <td class="whitespace-nowrap font-medium text-base-content/80">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-clock class="w-4 h-4 text-base-content/40" />
                                <span>{{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}</span>
                            </div>
                        </td>

                        <!-- User -->
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="avatar placeholder">
                                    <div class="bg-primary/10 text-primary rounded-full w-8 h-8 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                        {{ substr($log->user_name ?: 'S', 0, 2) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold text-base-content">{{ $log->user_name ?: 'System / Guest' }}</div>
                                    <div class="text-xs text-base-content/50 font-mono select-all">{{ $log->user_id ?: '-' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Aksi -->
                        <td>
                            @php
                                $actionLower = strtolower($log->action);
                                $badgeVariant = 'neutral';
                                if (str_contains($actionLower, 'create') || str_contains($actionLower, 'tambah') || str_contains($actionLower, 'store') || str_contains($actionLower, 'insert') || str_contains($actionLower, 'import')) {
                                    $badgeVariant = 'success';
                                } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit') || str_contains($actionLower, 'ubah') || str_contains($actionLower, 'mapping')) {
                                    $badgeVariant = 'info';
                                } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'hapus') || str_contains($actionLower, 'destroy') || str_contains($actionLower, 'remove')) {
                                    $badgeVariant = 'error';
                                } elseif (str_contains($actionLower, 'login') || str_contains($actionLower, 'auth')) {
                                    $badgeVariant = 'primary';
                                } elseif (str_contains($actionLower, 'logout')) {
                                    $badgeVariant = 'warning';
                                }
                            @endphp
                            <x-ui.badge :variant="$badgeVariant" outline class="uppercase tracking-wider font-semibold text-[10px]">
                                {{ $log->action }}
                            </x-ui.badge>
                        </td>

                        <!-- Deskripsi -->
                        <td class="max-w-md break-words text-base-content/80 font-medium">
                            {{ $log->description }}
                        </td>

                        <!-- IP Address & User Agent -->
                        <td>
                            <div class="font-mono text-xs text-base-content/70 flex items-center gap-1.5">
                                <x-heroicon-o-globe-alt class="w-3.5 h-3.5 text-base-content/30" />
                                <span>{{ $log->ip_address ?: '-' }}</span>
                            </div>
                            @if($log->user_agent)
                                <div class="text-[10px] text-base-content/40 max-w-[200px] truncate mt-0.5" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            <div class="p-4 border-t border-base-200 bg-base-100">
                @if(method_exists($logs, 'links'))
                    {{ $logs->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
