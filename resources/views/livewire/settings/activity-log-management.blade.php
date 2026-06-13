<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Log Aktivitas</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 w-full p-3 md:p-4">
        
        <!-- Filter Card -->
        <div class="bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm shrink-0">
            <h3 class="text-sm font-semibold text-base-content/80 mb-4 flex items-center gap-2">
                <x-heroicon-o-funnel class="w-4 h-4 text-primary" />
                Filter Data
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                <!-- Search -->
                <div class="form-control w-full sm:col-span-2 md:col-span-1">
                    <label class="label py-1"><span class="label-text text-xs font-semibold text-base-content/70">Keyword</span></label>
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari user, aksi, deskripsi..." />
                </div>

                <!-- Action Filter -->
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs font-semibold text-base-content/70">Aksi</span></label>
                    <select wire:model.live="actionFilter" class="select select-bordered select-sm w-full text-sm focus:select-primary">
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
                        class="input input-bordered input-sm w-full text-sm focus:input-primary" 
                    />
                </div>

                <!-- Date To -->
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs font-semibold text-base-content/70">Sampai Tanggal</span></label>
                    <input 
                        type="date" 
                        wire:model.live="dateTo" 
                        class="input input-bordered input-sm w-full text-sm focus:input-primary" 
                    />
                </div>

                <!-- Reset Button -->
                <div class="form-control w-full">
                    <x-ui.button 
                        variant="ghost" 
                        outline 
                        size="sm"
                        class="w-full text-sm border-base-300 hover:border-error hover:text-error" 
                        wire:click="resetFilters" 
                        icon="arrow-path"
                    >
                        Reset Filter
                    </x-ui.button>
                </div>

                <!-- Export Button -->
                <div class="form-control w-full">
                    <x-ui.button 
                        variant="success" 
                        size="sm"
                        class="w-full text-sm" 
                        wire:click="export" 
                        wire:loading.attr="disabled"
                        wire:target="export"
                        icon="arrow-down-tray"
                    >
                        Export Excel
                    </x-ui.button>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            <div class="flex-1 min-h-0 overflow-y-auto">
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
                        <td class="whitespace-nowrap">
                            @php
                                $actionLower = strtolower($log->action);
                                $badgeVariant = 'neutral';
                                if (str_contains($actionLower, 'create') || str_contains($actionLower, 'tambah') || str_contains($actionLower, 'store') || str_contains($actionLower, 'insert') || str_contains($actionLower, 'import')) {
                                    $badgeVariant = 'success';
                                } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit') || str_contains($actionLower, 'ubah') || str_contains($actionLower, 'mapping')) {
                                    $badgeVariant = 'info';
                                } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'hapus') || str_contains($actionLower, 'destroy') || str_contains($actionLower, 'remove')) {
                                    $badgeVariant = 'error';
                                } elseif (str_contains($actionLower, 'export') || str_contains($actionLower, 'download')) {
                                    $badgeVariant = 'secondary';
                                } elseif (str_contains($actionLower, 'login') || str_contains($actionLower, 'auth')) {
                                    $badgeVariant = 'primary';
                                } elseif (str_contains($actionLower, 'logout')) {
                                    $badgeVariant = 'warning';
                                }
                            @endphp
                            <x-ui.badge :variant="$badgeVariant" outline class="uppercase tracking-wider font-semibold text-[10px] whitespace-nowrap">
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
            </div>

            <div class="p-4 border-t border-base-200 bg-base-100">
                @if(method_exists($logs, 'links'))
                    {{ $logs->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
