{{-- Modal Cleansing Duplicate Route --}}
@if($showCleansingModal)
    <x-ui.modal 
        id="modal_cleansing" 
        wire:key="modal-cleansing-{{ $appliedDistributor ?: 'all' }}"
        title="Cleansing Duplicate Route" 
        icon="sparkles" 
        size="5xl" 
        open="true" 
        wire:close="closeCleansingModal" 
        position="top"
    >
        <div class="flex flex-col gap-4 text-xs">
            @if(empty($appliedDistributor))
                <div class="alert alert-warning shadow-xs py-3 px-4 border border-warning/30 rounded-xl">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-warning shrink-0" />
                    <span>Silakan pilih <strong>Distributor</strong> terlebih dahulu pada filter utama untuk melakukan pembersihan data.</span>
                </div>
            @else
                @if($cleansingTotalGroups === 0)
                    <div class="alert alert-success shadow-xs py-3 px-4 border border-success/30 rounded-xl">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-success shrink-0" />
                        <span><strong>Data Bersih!</strong> Tidak ditemukan duplicate route untuk Distributor dan Bulan yang dipilih.</span>
                    </div>
                @else
                    <div class="alert alert-warning shadow-xs py-3 px-4 border border-warning/30 rounded-xl flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-warning shrink-0 mt-0.5" />
                        <div>
                            <h4 class="font-bold text-sm text-base-content">Ditemukan {{ $cleansingTotalGroups }} toko dengan total {{ $cleansingTotalDuplicates }} data rute duplikat.</h4>
                            <div class="text-xs text-base-content/70 mt-1">Record <strong class="text-success font-semibold">Primary</strong> (berwarna hijau) akan dipertahankan dan menerima gabungan jadwal dari record lainnya. Record <strong class="text-error font-semibold">Duplicate</strong> (berwarna merah) akan dihapus secara permanen.</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-96 border border-base-300 rounded-xl bg-base-100">
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
                                        <tr wire:key="cleansing-rec-{{ $rec['id'] }}" class="{{ $rec['isPrimary'] ? 'bg-success/5' : 'bg-error/5' }}">
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
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-sm btn-ghost font-bold px-4" wire:click="closeCleansingModal" wire:loading.attr="disabled" wire:target="executeCleansing">Batal</button>
            @if(!empty($appliedDistributor) && $cleansingTotalGroups > 0)
                <button type="button" class="btn btn-sm btn-error text-white font-bold px-5 shadow-sm shadow-error/20" 
                    onclick="confirm('Peringatan!\nAnda akan menghapus {{ $cleansingTotalDuplicates }} data duplicate.\nTindakan ini tidak dapat dibatalkan.\n\nLanjutkan?') || event.stopImmediatePropagation()" 
                    wire:click="executeCleansing"
                    wire:loading.attr="disabled"
                    wire:target="executeCleansing">
                    <span wire:loading wire:target="executeCleansing" class="loading loading-spinner loading-xs mr-1"></span>
                    <x-heroicon-s-trash class="w-4 h-4 mr-1" wire:loading.remove wire:target="executeCleansing" />
                    Delete Duplicate
                </button>
            @endif
        </x-slot:footer>
    </x-ui.modal>
@endif
