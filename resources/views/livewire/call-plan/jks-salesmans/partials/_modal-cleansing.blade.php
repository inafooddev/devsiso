<div>
    <input type="checkbox" class="modal-toggle" wire:model.live="showCleansingModal" />
    <div class="modal" role="dialog">
        <div class="modal-box w-11/12 max-w-5xl">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                <x-heroicon-s-sparkles class="w-5 h-5 text-warning" />
                Cleansing Duplicate Route
            </h3>

            @if(empty($appliedDistributor))
                <div class="alert alert-warning">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                    <span>Silakan pilih <strong>Distributor</strong> terlebih dahulu pada filter utama untuk melakukan pembersihan data.</span>
                </div>
            @else
                @if($cleansingTotalGroups === 0)
                    <div class="alert alert-success">
                        <x-heroicon-o-check-circle class="w-6 h-6" />
                        <span><strong>Data Bersih!</strong> Tidak ditemukan duplicate route untuk Distributor dan Bulan yang dipilih.</span>
                    </div>
                @else
                    <div class="alert alert-warning mb-4 shadow-sm">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                        <div>
                            <h3 class="font-bold">Ditemukan {{ $cleansingTotalGroups }} toko dengan total {{ $cleansingTotalDuplicates }} data rute duplikat.</h3>
                            <div class="text-xs">Record <strong>Primary</strong> (berwarna hijau) akan dipertahankan dan menerima gabungan jadwal dari record lainnya. Record <strong>Duplicate</strong> (berwarna merah) akan dihapus secara permanen.</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-96 border rounded-box bg-base-100 mb-4">
                        <table class="table table-sm table-pin-rows">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>#</th>
                                    <th>Toko (Customer Code)</th>
                                    <th>Salesman</th>
                                    <th>ID Record</th>
                                    <th class="text-center">Jadwal (Hari)</th>
                                    <th class="text-center">Jadwal (Minggu)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cleansingResults as $index => $group)
                                    @foreach($group['records'] as $recIdx => $rec)
                                        <tr class="{{ $rec['isPrimary'] ? 'bg-success/5' : 'bg-error/5' }}">
                                            @if($recIdx === 0)
                                                <td rowspan="{{ count($group['records']) }}" class="align-top border-b border-r">{{ $index + 1 }}</td>
                                                <td rowspan="{{ count($group['records']) }}" class="align-top border-b border-r">
                                                    <div class="font-semibold">{{ $rec['customer_name'] }}</div>
                                                    <div class="text-xs opacity-70 mb-1">{{ $group['customer_code'] }}</div>
                                                    <div class="badge badge-outline badge-xs">{{ $group['dup_type'] ?? 'Unknown' }}</div>
                                                </td>
                                                <td rowspan="{{ count($group['records']) }}" class="align-top border-b border-r">
                                                    {{ $group['salesman_code'] }}
                                                </td>
                                            @endif
                                            <td class="font-mono text-xs {{ count($group['records']) - 1 === $recIdx ? 'border-b' : '' }}">
                                                {{ $rec['id'] }}
                                            </td>
                                            <td class="text-center {{ count($group['records']) - 1 === $recIdx ? 'border-b' : '' }}">
                                                <div class="flex gap-0.5 justify-center">
                                                    @foreach(['h1'=>'S','h2'=>'S','h3'=>'R','h4'=>'K','h5'=>'J','h6'=>'S','h7'=>'M'] as $col => $label)
                                                        <span class="badge badge-xs {{ ($rec['raw'][$col] ?? 'T') === 'Y' ? 'badge-primary' : 'badge-ghost opacity-30' }}" title="{{ $col }}">{{ $label }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="text-center {{ count($group['records']) - 1 === $recIdx ? 'border-b' : '' }}">
                                                <div class="flex gap-0.5 justify-center">
                                                    @foreach(['w1'=>'1','w2'=>'2','w3'=>'3','w4'=>'4'] as $col => $label)
                                                        <span class="badge badge-xs {{ ($rec['raw'][$col] ?? 'T') === 'Y' ? 'badge-secondary' : 'badge-ghost opacity-30' }}" title="{{ $col }}">{{ $label }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="{{ count($group['records']) - 1 === $recIdx ? 'border-b' : '' }}">
                                                @if($rec['isPrimary'])
                                                    <span class="badge badge-success badge-sm gap-1">
                                                        <x-heroicon-m-check class="w-3 h-3" /> Primary
                                                    </span>
                                                @else
                                                    <span class="badge badge-error badge-sm gap-1 text-white">
                                                        <x-heroicon-m-trash class="w-3 h-3" /> Duplicate
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

            <div class="modal-action">
                <button type="button" class="btn" wire:click="closeCleansingModal" wire:loading.attr="disabled" wire:target="executeCleansing">Batal</button>
                @if(!empty($appliedDistributor) && $cleansingTotalGroups > 0)
                    <button type="button" class="btn btn-error text-white" 
                        onclick="confirm('Peringatan!\nAnda akan menghapus {{ $cleansingTotalDuplicates }} data duplicate.\nTindakan ini tidak dapat dibatalkan.\n\nLanjutkan?') || event.stopImmediatePropagation()" 
                        wire:click="executeCleansing"
                        wire:loading.attr="disabled"
                        wire:target="executeCleansing">
                        <span wire:loading wire:target="executeCleansing" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-trash class="w-4 h-4" wire:loading.remove wire:target="executeCleansing" />
                        Delete Duplicate
                    </button>
                @endif
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeCleansingModal">close</button>
        </form>
    </div>
</div>
