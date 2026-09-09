<div class="flex-1 overflow-auto bg-base-100 w-full relative">
    <table class="table table-sm table-zebra w-full whitespace-nowrap relative">
        <thead class="sticky top-0 z-20 text-[11px] uppercase tracking-wider bg-base-300 text-base-content/80 shadow-sm border-b border-base-300 outline outline-1 outline-base-300">
            <tr class="[&>th]:bg-base-300">
                <th class="w-12 text-center" rowspan="2">No</th>
                <th rowspan="2">Sales</th>
                <th rowspan="2">Cust Kode</th>
                <th rowspan="2">Cust Name</th>
                <th rowspan="2">Wilayah</th>
                <th class="text-center border-b border-primary !bg-primary text-primary-content" colspan="7">Hari</th>
                <th class="text-center border-b border-neutral !bg-neutral text-neutral-content" colspan="4">Minggu</th>
                <th class="text-center w-20" rowspan="2">Aksi</th>
            </tr>
            <tr>
                <th class="text-center text-[10px] px-1 border-r border-primary !bg-primary text-primary-content">Sen</th>
                <th class="text-center text-[10px] px-1 border-r border-primary !bg-primary text-primary-content">Sel</th>
                <th class="text-center text-[10px] px-1 border-r border-primary !bg-primary text-primary-content">Rab</th>
                <th class="text-center text-[10px] px-1 border-r border-primary !bg-primary text-primary-content">Kam</th>
                <th class="text-center text-[10px] px-1 border-r border-primary !bg-primary text-primary-content">Jum</th>
                <th class="text-center text-[10px] px-1 border-r border-primary !bg-primary text-primary-content">Sab</th>
                <th class="text-center text-[10px] px-1 !bg-primary text-primary-content">Min</th>
                
                <th class="text-center text-[10px] px-1 border-r border-neutral border-l-2 !bg-neutral text-neutral-content">
                    <button wire:click="openCalendarModal(1)" class="flex items-center justify-center gap-1 w-full hover:text-primary-focus transition-colors cursor-pointer group">
                        <span>M1</span>
                        <x-heroicon-o-calendar-days class="w-3 h-3 text-neutral-content/70 group-hover:text-neutral-content transition-colors" />
                    </button>
                </th>
                <th class="text-center text-[10px] px-1 border-r border-neutral !bg-neutral text-neutral-content">
                    <button wire:click="openCalendarModal(2)" class="flex items-center justify-center gap-1 w-full hover:text-primary-focus transition-colors cursor-pointer group">
                        <span>M2</span>
                        <x-heroicon-o-calendar-days class="w-3 h-3 text-neutral-content/70 group-hover:text-neutral-content transition-colors" />
                    </button>
                </th>
                <th class="text-center text-[10px] px-1 border-r border-neutral !bg-neutral text-neutral-content">
                    <button wire:click="openCalendarModal(3)" class="flex items-center justify-center gap-1 w-full hover:text-primary-focus transition-colors cursor-pointer group">
                        <span>M3</span>
                        <x-heroicon-o-calendar-days class="w-3 h-3 text-neutral-content/70 group-hover:text-neutral-content transition-colors" />
                    </button>
                </th>
                <th class="text-center text-[10px] px-1 !bg-neutral text-neutral-content">
                    <button wire:click="openCalendarModal(4)" class="flex items-center justify-center gap-1 w-full hover:text-primary-focus transition-colors cursor-pointer group">
                        <span>M4</span>
                        <x-heroicon-o-calendar-days class="w-3 h-3 text-neutral-content/70 group-hover:text-neutral-content transition-colors" />
                    </button>
                </th>
            </tr>
        </thead>
        <tbody class="text-xs relative transition-opacity duration-300" wire:loading.class="opacity-30 pointer-events-none">
            @forelse ($jksData as $index => $row)
                <tr class="hover:bg-primary/5 transition-colors group">
                    <th class="text-center">{{ $jksData->firstItem() + $index }}</th>
                    
                    <td>
                        <div class="font-bold truncate max-w-[140px]" title="{{ $row->salesman_name }}">{{ $row->salesman_name }}</div>
                        <div class="text-[9px] text-base-content/50">{{ $row->salesman_code }}</div>
                    </td>
                    <td>
                        <div class="font-bold">{{ $row->uniq_kode }}</div>
                        <div class="text-[9px] text-base-content/50">{{ $row->customer_code }}</div>
                    </td>
                    <td>
                        <div class="flex items-center">
                            @if(in_array($row->customer_code, $collidingCustomerCodes))
                                <div class="font-bold truncate max-w-[170px] text-error" title="{{ $row->customer_name ?? '-' }}">{{ $row->customer_name ?? '-' }}</div>
                                <button type="button" wire:click="showCollisionDetails('{{ $row->customer_code }}', '{{ addslashes($row->customer_name ?? '') }}')" class="btn btn-xs btn-circle btn-error btn-outline border-none ml-1 shadow-sm" title="Toko ini bentrok dengan Salesman lain!">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 animate-pulse" />
                                </button>
                            @else
                                <div class="font-bold truncate max-w-[200px]" title="{{ $row->customer_name ?? '-' }}">{{ $row->customer_name ?? '-' }}</div>
                            @endif
                        </div>
                        <div class="text-[9px] text-base-content/50 truncate max-w-[200px]" title="{{ $row->customer_address }}">{{ $row->customer_address }}</div>
                    </td>
                    <td>
                        <div class="font-semibold truncate max-w-[120px]" title="{{ $row->kecamatan ?? '-' }}">{{ $row->kecamatan ?? '-' }}</div>
                        <div class="text-[9px] text-base-content/50 truncate max-w-[120px]" title="{{ $row->desa ?? '-' }}">{{ $row->desa ?? '-' }}</div>
                    </td>
                    {{-- Hari --}}
                    <td class="text-center px-1 border-l-2 border-base-300 bg-base-100/50">@if($row->h1 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-success mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-100/50">@if($row->h2 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-success mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-100/50">@if($row->h3 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-success mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-100/50">@if($row->h4 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-success mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-100/50">@if($row->h5 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-success mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-100/50">@if($row->h6 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-success mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-100/50">@if($row->h7 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-success mx-auto"/> @endif</td>
                    {{-- Minggu --}}
                    <td class="text-center px-1 bg-base-200/50 border-l-2 border-base-300">@if($row->w1 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-primary mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-200/50">@if($row->w2 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-primary mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-200/50">@if($row->w3 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-primary mx-auto"/> @endif</td>
                    <td class="text-center px-1 bg-base-200/50">@if($row->w4 === 'Y') <x-heroicon-s-check-circle class="w-4 h-4 text-primary mx-auto"/> @endif</td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click="editJadwal({{ $row->id }})" class="btn btn-xs btn-circle btn-ghost text-primary hover:bg-primary/10" title="Edit Jadwal">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </button>
                            <button wire:click="confirmDelete({{ $row->id }})" class="btn btn-xs btn-circle btn-ghost text-error hover:bg-error/10" title="Hapus Jadwal">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="text-center py-16">
                        <div class="flex flex-col items-center justify-center text-base-content/40">
                            <x-heroicon-o-document-magnifying-glass class="w-16 h-16 mb-3 opacity-50" />
                            <h3 class="text-lg font-bold text-base-content/70">Tidak ada jadwal ditemukan</h3>
                            <p class="text-sm mt-1">Coba sesuaikan filter pencarian, hari, atau minggu Anda.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination (Footer Card) --}}
@if($jksData->hasPages())
<div class="p-3 border-t border-base-300 bg-base-200/30 shrink-0">
    {{ $jksData->links(data: ['scrollTo' => false]) }}
</div>
@endif
