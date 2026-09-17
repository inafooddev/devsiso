<div class="flex-1 flex flex-col min-h-0 w-full h-full">
    <x-slot name="title">Outlet Non JKS</x-slot>

    <x-ui.tab-menu>
        @php
            $pendingApprovalsCount = \Illuminate\Support\Facades\DB::table('jks_approvals')->where('status', 'PENDING')->count();
        @endphp
        <a href="#" class="tab">Dashboard</a>
        <a href="{{ route('call-plan.jks-summary') }}" class="tab">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab">Detail</a>
        <a href="#" class="tab">Maps</a>
        <a href="{{ route('call-plan.jks-non-route') }}" class="tab tab-active bg-primary text-primary-content">Outlet non JKS</a>
        <a href="{{ route('call-plan.jks-approvals') }}" class="tab">
            Persetujuan
            @if($pendingApprovalsCount > 0)
                <span class="badge badge-sm badge-error ml-2">{{ $pendingApprovalsCount }}</span>
            @endif
        </a>
    </x-ui.tab-menu>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full pt-4">
        
        {{-- Main Card --}}
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full xl:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Outlet Non JKS</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar toko aktif yang belum terdaftar di JKS Salesman</p>
                </div>
                
                {{-- Hierarchy Filters & Search --}}
                <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                    <select wire:model.live="appliedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                        <option value="">Semua Region</option>
                        @foreach($regionOptions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="appliedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0" @if(empty($appliedRegion)) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areaOptions as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="appliedSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0" @if(empty($appliedArea)) disabled @endif>
                        <option value="">Semua Supervisor</option>
                        @foreach($supervisorOptions as $spv)
                            <option value="{{ $spv->supervisor_code }}">{{ $spv->description ?? $spv->supervisor_code }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="appliedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0" @if(empty($appliedSupervisor)) disabled @endif>
                        <option value="">Semua Distributor</option>
                        @foreach($distributorOptions as $dist)
                            <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_name }}</option>
                        @endforeach
                    </select>

                    {{-- Bulk Actions --}}
                    @if(count($selected) > 0)
                        <div class="flex items-center gap-2 border-l border-base-300 pl-2">
                            <span class="badge badge-primary">{{ count($selected) }} terpilih</span>
                            <button class="btn btn-sm btn-primary text-white" wire:click="openBulkAddJksModal">
                                <x-heroicon-s-plus-circle class="w-4 h-4" /> Tambah Massal ke JKS
                            </button>
                        </div>
                    @endif

                    {{-- Search Component --}}
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari outlet..." />
                    
                    {{-- Actions (Export & Reset) --}}
                    <div class="border-l border-base-300 pl-2 hidden sm:flex items-center gap-2">
                        <button class="btn btn-sm btn-ghost text-base-content/70" wire:click="resetFilters" title="Reset Filters & Cache">
                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                        </button>
                        <button class="btn btn-sm btn-success text-white" wire:click="exportExcel">
                            <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1">
                                <x-heroicon-s-arrow-down-tray class="w-4 h-4" /> Export Excel
                            </span>
                            <span wire:loading wire:target="exportExcel" class="flex items-center gap-1">
                                <span class="loading loading-spinner loading-xs"></span> Loading...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Message if no filter applied --}}
            @if(empty($appliedRegion) && empty($appliedArea) && empty($appliedSupervisor) && empty($appliedDistributor) && empty($search))
                <div class="p-3 bg-info/10 text-info text-xs font-semibold flex items-center justify-center gap-2 border-b border-info/20">
                    <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                    Pilih minimal satu filter (Region/Area/Spv/Dist) atau gunakan pencarian untuk memuat data outlet.
                </div>
            @endif

            {{-- Body Card (Table Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th class="w-12 text-center">
                                <input type="checkbox" class="checkbox checkbox-sm rounded" wire:model.live="selectAll" />
                            </th>
                            <th class="w-16 text-center">No</th>
                            <th>Distributor Code</th>
                            <th>Distributor Name</th>
                            <th>Customer Kode</th>
                            <th>Customer Kode PRC</th>
                            <th>Nama Cust</th>
                            <th>Alamat</th>
                            <th class="text-right">Avg</th>
                            <th>Remark</th>
                            <th class="w-20 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($outlets as $index => $row)
                            <tr class="hover:bg-base-200/50 transition-colors">
                                <td class="text-center">
                                    <input type="checkbox" class="checkbox checkbox-sm rounded" wire:model.live="selected" value="{{ $row->id }}" @if($row->pending_approval_id) disabled @endif />
                                </td>
                                <th class="text-center">{{ $outlets->firstItem() + $index }}</th>
                                <td class="font-mono text-xs">{{ $row->distributor_code }}</td>
                                <td>{{ $row->distributor_name }}</td>
                                <td class="font-mono text-xs text-primary">{{ $row->customer_code }}</td>
                                <td class="font-mono text-xs">{{ $row->customer_eska ?? '-' }}</td>
                                <td class="font-bold">{{ $row->customer_name }}</td>
                                <td class="whitespace-normal min-w-[200px] text-xs text-base-content/80 line-clamp-2" title="{{ $row->alamat }}">{{ $row->alamat ?: '-' }}</td>
                                <td class="text-right font-mono font-bold text-success">
                                    Rp {{ number_format($row->avg_value_net, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-normal min-w-[150px] max-w-[250px]">
                                    @if($row->remark)
                                        <div class="text-xs italic text-error font-medium line-clamp-2" title="{{ $row->remark }}">{{ $row->remark }}</div>
                                    @else
                                        <span class="text-xs text-base-content/40 italic">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @if($row->pending_approval_id)
                                            <span class="badge badge-warning badge-sm text-[10px]" title="Sedang Diajukan ke JKS">Pending</span>
                                        @else
                                            <button 
                                                wire:click="openAddJksModal('{{ $row->id }}')"
                                                class="btn btn-xs btn-primary btn-square"
                                                title="Tambah ke JKS"
                                            >
                                                <x-heroicon-o-calendar-days class="w-4 h-4" />
                                            </button>
                                        @endif
                                        
                                        <button 
                                            wire:click="openRemarkModal('{{ $row->distributor_code }}', '{{ $row->customer_code }}', '{{ addslashes($row->customer_name) }}', '{{ addslashes($row->remark) }}')"
                                            class="btn btn-xs btn-ghost btn-square text-base-content/50 hover:text-primary transition-colors"
                                            title="{{ $row->remark ? 'Edit Remark' : 'Tambah Remark' }}"
                                        >
                                            <x-heroicon-o-chat-bubble-bottom-center-text class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-12">
                                    <div class="flex flex-col items-center justify-center text-base-content/40">
                                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mb-3 opacity-20" />
                                        @if(empty($appliedRegion) && empty($appliedArea) && empty($appliedSupervisor) && empty($appliedDistributor) && empty($search))
                                            <p class="font-medium text-sm">Pilih filter untuk memuat data</p>
                                        @else
                                            <p class="font-medium text-sm">Tidak ada Outlet Non JKS yang ditemukan.</p>
                                            <p class="text-xs mt-1">Semua outlet yang sesuai kriteria sudah memiliki rute JKS.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Remark Modal --}}
            <template x-teleport="body">
            <dialog class="modal items-start sm:pt-10 !z-[99999]" :class="$wire.remarkModalOpen ? 'modal-open' : ''">
                <div class="modal-box">
                    <h3 class="font-bold text-lg mb-4">Catatan Outlet Non JKS</h3>
                    
                    <div class="bg-base-200/50 p-3 rounded-lg mb-4">
                        <div class="text-xs text-base-content/60">Toko:</div>
                        <div class="font-bold">{{ $editingCustomerName }}</div>
                        <div class="font-mono text-xs text-primary mt-1">{{ $editingCustomerCode }} <span class="text-base-content/40">(Dist: {{ $editingDistributorCode }})</span></div>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Isi Remark / Catatan Khusus</span>
                        </label>
                        <textarea 
                            wire:model="editingRemark" 
                            class="textarea textarea-bordered h-24 w-full focus:textarea-primary" 
                            placeholder="Tuliskan catatan mengapa toko ini belum masuk JKS, atau info penting lainnya..."
                        ></textarea>
                    </div>

                    <div class="modal-action">
                        <button @click="$wire.remarkModalOpen = false" type="button" class="btn btn-ghost">Batal</button>
                        <button wire:click="saveRemark" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveRemark">
                            <span wire:loading.remove wire:target="saveRemark">Simpan Remark</span>
                            <span wire:loading wire:target="saveRemark" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </div>
                <div class="modal-backdrop bg-neutral/40" @click="$wire.remarkModalOpen = false"></div>
            </dialog>
            </template>

            {{-- Add JKS Modal --}}
            <template x-teleport="body">
            <dialog class="modal items-start sm:pt-10 !z-[99999]" :class="$wire.addJksModalOpen ? 'modal-open' : ''">
                <div class="modal-box max-w-2xl">
                    <h3 class="font-bold text-lg mb-4">Pengajuan Tambah ke JKS</h3>
                    
                    @if(!empty($formOutlets))
                        @if(count($formOutlets) === 1)
                            {{-- Single View --}}
                            @php $formOutlet = $formOutlets[0]; @endphp
                            <div class="bg-base-200/50 p-3 rounded-lg mb-4 grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-xs text-base-content/60">Toko:</div>
                                    <div class="font-bold text-sm">{{ $formOutlet['customer_name'] }}</div>
                                    <div class="font-mono text-xs text-primary mt-0.5">{{ $formOutlet['customer_code'] }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-base-content/60">Distributor:</div>
                                    <div class="font-bold text-sm">{{ $formOutlet['distributor_name'] }}</div>
                                    <div class="font-mono text-xs text-primary mt-0.5">{{ $formOutlet['distributor_code'] }}</div>
                                </div>
                                <div class="col-span-2 border-t border-base-300 pt-2 mt-1">
                                    <div class="text-xs text-base-content/60">Customer Kode PRC (eska):</div>
                                    <div class="font-mono text-sm {{ empty($formOutlet['customer_eska']) ? 'text-error italic' : 'text-success font-bold' }}">
                                        {{ $formOutlet['customer_eska'] ?: 'KOSONG - Harus diinput Admin saat Approval' }}
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Bulk View --}}
                            @php
                                $emptyPrcCount = count(array_filter($formOutlets, fn($o) => empty($o['customer_eska'])));
                            @endphp
                            <div class="bg-info/10 text-info p-3 rounded-lg text-sm font-medium flex gap-2 items-start mb-4">
                                <x-heroicon-o-information-circle class="w-5 h-5 shrink-0 mt-0.5" />
                                <div class="flex-1">
                                    <div>Anda akan menambahkan <strong>{{ count($formOutlets) }} toko</strong> dari distributor <strong>{{ $bulkDistributorCode }}</strong> ke JKS sekaligus.</div>
                                    @if($emptyPrcCount > 0)
                                        <div class="text-error font-bold mt-1 text-xs">
                                            (🚨 Terdapat {{ $emptyPrcCount }} toko tanpa kode PRC yang wajib dilengkapi oleh Admin nanti)
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="max-h-[30vh] overflow-y-auto mb-4 border border-base-300 rounded-lg bg-base-200/30">
                                <table class="table table-xs table-pin-rows">
                                    <thead class="bg-base-200">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Toko</th>
                                            <th>PRC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($formOutlets as $outlet)
                                            <tr>
                                                <td class="font-mono text-primary">{{ $outlet['customer_code'] }}</td>
                                                <td>{{ $outlet['customer_name'] }}</td>
                                                <td class="font-mono">
                                                    @if(empty($outlet['customer_eska']))
                                                        <span class="text-error italic" title="KOSONG - Harus diinput Admin saat Approval">Kosong</span>
                                                    @else
                                                        <span class="text-success">{{ $outlet['customer_eska'] }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Salesman <span class="text-error">*</span></span></label>
                            <select wire:model="formSalesman" class="select select-bordered w-full @error('formSalesman') select-error @enderror">
                                <option value="">Pilih Salesman</option>
                                @foreach($this->jksSalesmanOptions as $salesman)
                                    <option value="{{ $salesman->salesman_code }}">{{ $salesman->salesman_name }} ({{ $salesman->salesman_code }})</option>
                                @endforeach
                            </select>
                            @error('formSalesman') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Bulan Target <span class="text-error">*</span></span></label>
                            <input type="month" wire:model.live="formBulan" class="input input-bordered @error('formBulan') input-error @enderror" />
                            @error('formBulan') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div class="form-control bg-base-100 border @error('formH') border-error bg-error/5 @else border-base-300 @enderror rounded-lg p-3">
                            <label class="label pt-0 border-b border-base-200 mb-2"><span class="label-text font-bold">Hari Kunjungan <span class="text-error">*</span></span></label>
                            <div class="flex flex-wrap gap-2">
                                @foreach([1=>'Senin (H1)', 2=>'Selasa (H2)', 3=>'Rabu (H3)', 4=>'Kamis (H4)', 5=>'Jumat (H5)', 6=>'Sabtu (H6)', 7=>'Minggu (H7)'] as $val => $label)
                                    <label class="cursor-pointer flex items-center gap-1.5 bg-base-200 px-2 py-1 rounded border border-base-300 hover:border-primary transition-colors">
                                        <input type="checkbox" wire:model="formH" value="{{ $val }}" class="checkbox checkbox-xs checkbox-primary" />
                                        <span class="label-text text-xs font-medium">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('formH') <span class="text-error text-xs mt-2 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control bg-base-100 border @error('formW') border-error bg-error/5 @else border-base-300 @enderror rounded-lg p-3">
                            <label class="label pt-0 border-b border-base-200 mb-2"><span class="label-text font-bold">Minggu Kunjungan <span class="text-error">*</span></span></label>
                            <div class="flex flex-wrap gap-2">
                                @foreach([1,2,3,4] as $val)
                                    <label class="cursor-pointer flex items-center gap-1.5 bg-base-200 px-2 py-1 rounded border border-base-300 hover:border-primary transition-colors">
                                        <input type="checkbox" wire:model="formW" value="{{ $val }}" class="checkbox checkbox-xs checkbox-primary" />
                                        <span class="label-text text-xs font-medium">W{{ $val }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('formW') <span class="text-error text-xs mt-2 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Calendar Preview --}}
                    <div class="mb-4 bg-base-100 border border-base-300 rounded-lg overflow-hidden">
                        <div class="bg-base-200/50 p-2.5 border-b border-base-300 flex justify-between items-center">
                            <h4 class="font-bold text-sm text-base-content/80 flex items-center gap-1.5">
                                <x-heroicon-o-calendar class="w-4 h-4" /> Pratinjau Jadwal
                            </h4>
                        </div>
                        
                        <div class="p-3">
                            @php 
                                $calData = $this->calendarDays; 
                                $days = $calData['days'] ?? collect();
                                $offset = $calData['offset'] ?? 0;
                            @endphp

                            @if($days->isEmpty())
                                <div class="text-center py-6 text-base-content/50 text-sm italic">
                                    Pilih bulan terlebih dahulu atau kalender belum digenerate.
                                </div>
                            @else
                                <!-- Month View -->
                                <div>
                                    <div class="grid grid-cols-7 gap-1 text-center mb-1.5 border-b border-base-200 pb-1">
                                        <div class="text-[10px] font-bold text-error uppercase">Min</div>
                                        <div class="text-[10px] font-bold uppercase">Sen</div>
                                        <div class="text-[10px] font-bold uppercase">Sel</div>
                                        <div class="text-[10px] font-bold uppercase">Rab</div>
                                        <div class="text-[10px] font-bold uppercase">Kam</div>
                                        <div class="text-[10px] font-bold uppercase">Jum</div>
                                        <div class="text-[10px] font-bold uppercase">Sab</div>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1">
                                        @for($i=0; $i<$offset; $i++)
                                            <div class="aspect-square rounded"></div>
                                        @endfor
                                        
                                        @foreach($days as $day)
                                            <div class="aspect-square rounded border flex flex-col items-center justify-center relative transition-colors duration-200"
                                                 :class="{
                                                     'bg-primary/20 border-primary shadow-inner': $wire.formH.includes('{{ $day->day_number }}') && $wire.formW.includes('{{ $day->week_month }}'),
                                                     'border-base-200 bg-base-100/50': !($wire.formH.includes('{{ $day->day_number }}') && $wire.formW.includes('{{ $day->week_month }}'))
                                                 }">
                                                <span class="text-xs {{ ($day->libur_nasional || $day->day_number == 7) ? 'text-error font-semibold' : '' }}"
                                                      :class="{ 'text-primary font-bold': $wire.formH.includes('{{ $day->day_number }}') && $wire.formW.includes('{{ $day->week_month }}') && !{{ $day->libur_nasional || $day->day_number == 7 ? 'true' : 'false' }} }">
                                                    {{ (int)date('d', strtotime($day->date)) }}
                                                </span>
                                                <span class="text-[9px] absolute bottom-1 right-1 font-bold px-1 rounded-sm"
                                                      :class="($wire.formH.includes('{{ $day->day_number }}') && $wire.formW.includes('{{ $day->week_month }}')) ? 'bg-primary text-primary-content' : 'bg-primary/10 text-primary'">
                                                    W{{ $day->week_month }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-action">
                        <button @click="$wire.addJksModalOpen = false" type="button" class="btn btn-ghost">Batal</button>
                        <button wire:click="submitAddJks" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submitAddJks">
                            <span wire:loading.remove wire:target="submitAddJks">Ajukan Penambahan</span>
                            <span wire:loading wire:target="submitAddJks" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </div>
                <div class="modal-backdrop bg-neutral/40" @click="$wire.addJksModalOpen = false"></div>
            </dialog>
            </template>

            {{-- Footer Card (Pagination) --}}
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $outlets->links() }}
            </div>

        </div>
    </div>
</div>
