<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
     x-data="{ 
        toasts: [], 
        addToast(toast) {
            const id = Date.now();
            this.toasts.push({ id, ...toast });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3000);
        }
     }"
     @toast.window="addToast(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)"
>
    <x-slot name="title">JKS Salesman</x-slot>

    <x-ui.tab-menu>
        @php
            $pendingApprovalsCount = \Illuminate\Support\Facades\DB::table('jks_approvals')->where('status', 'PENDING')->count();
        @endphp
        <a href="#" class="tab">Dashboard</a>
        <a href="#" class="tab">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab tab-active bg-primary text-primary-content">Detail</a>
        <a href="#" class="tab">Maps</a>
        <a href="#" class="tab">Outlet non JKS</a>
        <a href="{{ route('call-plan.jks-approvals') }}" class="tab">
            Persetujuan
            @if($pendingApprovalsCount > 0)
                <span class="badge badge-sm badge-error ml-2">{{ $pendingApprovalsCount }}</span>
            @endif
        </a>
    </x-ui.tab-menu>

    @include('livewire.call-plan.jks-salesmans.partials._kpi_cards')

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        @include('livewire.call-plan.jks-salesmans.partials._header')
        
        @include('livewire.call-plan.jks-salesmans.partials._table')
        
    </div>

    @include('livewire.call-plan.jks-salesmans.partials._filter')

    {{-- Calendar Modal --}}
    @if($showCalendarModal)
        <x-ui.modal id="modal-calendar" title="Kalender Minggu M{{ $selectedWeekForCalendar }}" icon="calendar-days" size="sm" open="true" wire:close="closeCalendarModal">
            <div class="px-2 pb-2">
                <div class="grid grid-cols-7 gap-1 text-center mb-2">
                    <div class="text-[10px] font-bold text-base-content/50 uppercase text-error">Min</div>
                    <div class="text-[10px] font-bold text-base-content/50 uppercase">Sen</div>
                    <div class="text-[10px] font-bold text-base-content/50 uppercase">Sel</div>
                    <div class="text-[10px] font-bold text-base-content/50 uppercase">Rab</div>
                    <div class="text-[10px] font-bold text-base-content/50 uppercase">Kam</div>
                    <div class="text-[10px] font-bold text-base-content/50 uppercase">Jum</div>
                    <div class="text-[10px] font-bold text-base-content/50 uppercase">Sab</div>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-sm">
                    @if($this->calendarDays->isNotEmpty())
                        @php
                            $firstDay = $this->calendarDays->first();
                            $emptyCells = $firstDay->day_number == 7 ? 0 : $firstDay->day_number;
                        @endphp
                        
                        @for($i = 0; $i < $emptyCells; $i++)
                            <div class="p-2 border border-transparent rounded-lg"></div>
                        @endfor
                        
                        @foreach($this->calendarDays as $day)
                            @php
                                $isHighlighted = $day->week_month == $selectedWeekForCalendar;
                                $isHoliday = $day->libur_nasional || $day->day_number == 7;
                            @endphp
                            <div class="p-2 rounded-lg border {{ $isHighlighted ? 'bg-primary text-primary-content border-primary shadow-md shadow-primary/20 scale-105 z-10' : 'bg-base-100 border-base-200 text-base-content' }} {{ !$isHighlighted && $isHoliday ? '!text-error font-semibold bg-error/5' : '' }} flex flex-col items-center justify-center relative transition-all duration-300 min-h-[44px]">
                                <span class="absolute top-0.5 left-1 text-[7px] font-bold {{ $isHighlighted ? 'text-primary-content/80' : 'text-base-content/40' }} tracking-tighter">M{{ $day->week_month }}</span>
                                <span class="{{ $isHighlighted ? 'font-black' : 'font-medium' }} mt-1">{{ \Carbon\Carbon::parse($day->date)->format('j') }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <x-slot:footer>
                <button class="btn btn-primary w-full" wire:click="closeCalendarModal">Tutup Kalender</button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Edit Modal --}}
    @if($showEditModal)
        <x-ui.modal id="modal-edit" title="Modifikasi Jadwal" icon="pencil-square" size="md" open="true" wire:close="closeEditModal">
            <div class="px-2 pb-2">
                <div class="mb-4">
                    <label class="text-xs text-base-content/50 font-bold uppercase">Nama Toko</label>
                    <div class="font-semibold text-base-content text-lg">{{ $editCustomerName }}</div>
                </div>

                <div class="mb-4">
                    <label class="text-xs text-base-content/50 font-bold uppercase block mb-2">Pindah ke Salesman</label>
                    <select wire:model="editSalesmanCode" class="select select-bordered select-sm w-full">
                        <option value="">-- Pilih Salesman --</option>
                        @foreach($this->headerSalesmans as $salesman)
                            <option value="{{ $salesman->salesman_code }}">{{ $salesman->salesman_name }} ({{ $salesman->salesman_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="text-xs text-base-content/50 font-bold uppercase block mb-2">Hari Kunjungan</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['h1' => 'Senin', 'h2' => 'Selasa', 'h3' => 'Rabu', 'h4' => 'Kamis', 'h5' => 'Jumat', 'h6' => 'Sabtu', 'h7' => 'Minggu'] as $key => $label)
                            <div x-data="{ checked: @entangle('editHari.'.$key) }" 
                                 :class="checked ? 'bg-primary/10 border-primary text-primary shadow-sm' : 'border-base-300 text-base-content/70 hover:bg-base-200'"
                                 class="cursor-pointer flex items-center gap-2 px-3 py-2 border rounded-lg transition-colors"
                                 @click="checked = !checked">
                                <input type="checkbox" x-model="checked" class="checkbox checkbox-primary checkbox-sm" @click.stop />
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-xs text-base-content/50 font-bold uppercase block mb-2">Minggu Kunjungan</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['w1' => 'M1', 'w2' => 'M2', 'w3' => 'M3', 'w4' => 'M4'] as $key => $label)
                            <div x-data="{ checked: @entangle('editMinggu.'.$key) }" 
                                 :class="checked ? 'bg-primary/10 border-primary text-primary shadow-sm' : 'border-base-300 text-base-content/70 hover:bg-base-200'"
                                 class="cursor-pointer flex items-center gap-2 px-3 py-2 border rounded-lg transition-colors"
                                 @click="checked = !checked">
                                <input type="checkbox" x-model="checked" class="checkbox checkbox-primary checkbox-sm" @click.stop />
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-bold">Alasan Perubahan <span class="text-error">*</span></span>
                    </label>
                    <textarea wire:model.live="editReason" class="textarea textarea-bordered w-full h-20" placeholder="Tuliskan alasan minimal 5 karakter..."></textarea>
                </div>
            </div>
            
            <x-slot:footer>
                <button class="btn btn-ghost" wire:click="closeEditModal">Batal</button>
                <button class="btn btn-primary" wire:click="saveJadwal" @if(strlen(trim($editReason)) < 5) disabled @endif>
                    <span wire:loading wire:target="saveJadwal" class="loading loading-spinner loading-xs mr-1"></span>
                    Simpan Perubahan
                </button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <x-ui.modal id="modal-delete" title="Konfirmasi Reset" icon="trash" size="sm" open="true" wire:close="closeDeleteModal">
            <div class="px-2 pb-2">
                <p class="text-sm text-base-content/80 mb-4">Apakah Anda yakin ingin mereset seluruh jadwal kunjungan untuk toko ini menjadi kosong (T)?</p>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-bold text-error">Alasan Reset <span class="text-error">*</span></span>
                    </label>
                    <textarea wire:model.live="deleteReason" class="textarea textarea-bordered textarea-error w-full h-24" placeholder="Tuliskan alasan minimal 5 karakter..."></textarea>
                </div>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" wire:click="closeDeleteModal">Batal</button>
                <button class="btn btn-error" wire:click="executeDelete" @if(strlen(trim($deleteReason)) < 5) disabled @endif>
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs mr-1"></span>
                    Ya, Reset Jadwal
                </button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Bulk Swap Modal --}}
    @if($showSwapModal)
        <x-ui.modal id="modal-swap" title="Tukar Jadwal Massal" icon="arrows-right-left" size="lg" open="true" wire:close="closeSwapModal" class="!items-start" boxClass="!mt-8">
            <div class="px-2 pb-2">
                {{-- Tabs --}}
                <div class="tabs tabs-boxed mb-6 bg-base-200/50">
                    <button wire:click="$set('swapTab', 'hari')" class="tab {{ $swapTab === 'hari' ? 'tab-active bg-primary text-primary-content' : '' }}">Tukar Hari</button>
                    <button wire:click="$set('swapTab', 'minggu')" class="tab {{ $swapTab === 'minggu' ? 'tab-active bg-primary text-primary-content' : '' }}">Tukar Minggu</button>
                    <button wire:click="$set('swapTab', 'salesman')" class="tab {{ $swapTab === 'salesman' ? 'tab-active bg-primary text-primary-content' : '' }}">Tukar Salesman</button>
                </div>

                @if($swapTab === 'hari')
                    <div class="space-y-4">
                        {{-- Form Row 1 --}}
                        <div>
                            <label class="text-xs text-base-content/50 font-bold uppercase block mb-1">Pilih Salesman</label>
                            <select wire:model.live="swapSalesmanCode" class="select select-bordered select-sm w-full">
                                <option value="">-- Pilih Salesman --</option>
                                @foreach($this->headerSalesmans as $salesman)
                                    <option value="{{ $salesman->salesman_code }}">{{ $salesman->salesman_name }} ({{ $salesman->salesman_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Form Row 2 --}}
                        <div class="flex items-center gap-4 bg-base-200/30 p-3 rounded-lg border border-base-200">
                            <div class="flex-1">
                                <label class="text-[10px] text-base-content/50 font-bold uppercase block mb-1">Hari Asal</label>
                                <select wire:model.live="swapHariAsal" class="select select-bordered select-sm w-full bg-base-100">
                                    <option value="">- Pilih Hari -</option>
                                    @foreach(['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pt-4 text-base-content/40">
                                <x-heroicon-o-arrow-right class="w-5 h-5" />
                            </div>
                            <div class="flex-1">
                                <label class="text-[10px] text-base-content/50 font-bold uppercase block mb-1">Hari Tujuan</label>
                                <select wire:model.live="swapHariTujuan" class="select select-bordered select-sm w-full bg-base-100">
                                    <option value="">- Pilih Hari -</option>
                                    @foreach(['h1'=>'Senin', 'h2'=>'Selasa', 'h3'=>'Rabu', 'h4'=>'Kamis', 'h5'=>'Jumat', 'h6'=>'Sabtu', 'h7'=>'Minggu'] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Form Row 3 --}}
                        <div>
                            <label class="text-xs text-base-content/50 font-bold uppercase block mb-2">Terapkan Pada Minggu</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['w1' => 'M1', 'w2' => 'M2', 'w3' => 'M3', 'w4' => 'M4'] as $key => $label)
                                    <div x-data="{ checked: @entangle('swapMinggu.'.$key).live }" 
                                         :class="checked ? 'bg-primary/10 border-primary text-primary shadow-sm' : 'border-base-300 text-base-content/70 hover:bg-base-200'"
                                         class="cursor-pointer flex items-center gap-2 px-3 py-1.5 border rounded-lg transition-colors"
                                         @click="checked = !checked">
                                        <input type="checkbox" x-model="checked" class="checkbox checkbox-primary checkbox-xs" @click.stop />
                                        <span class="text-xs font-medium">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-[10px] text-base-content/50 mt-1">*Jika mencentang sebagian, sistem akan otomatis melakukan Row Splitting secara aman.</div>
                        </div>

                        {{-- Live Preview Section --}}
                        @if($swapSalesmanCode && $swapHariAsal && $swapHariTujuan && $swapHariAsal !== $swapHariTujuan)
                            <div class="mt-6 pt-4 border-t border-base-200">
                                <div class="flex justify-between items-end mb-3">
                                    <h4 class="text-sm font-bold text-base-content/80">Pratinjau Toko Terdampak</h4>
                                    <div class="badge badge-primary">{{ $this->affectedStores->count() }} Toko</div>
                                </div>
                                
                                @if($this->affectedStores->count() > 0)
                                    <div class="overflow-x-auto max-h-[250px] border border-base-200 rounded-lg shadow-inner bg-base-100">
                                        <table class="table table-xs table-pin-rows table-pin-cols">
                                            <thead class="bg-base-200/80 text-base-content/70">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode Toko</th>
                                                    <th>Nama Toko</th>
                                                    <th class="text-center">Perubahan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($this->affectedStores as $idx => $s)
                                                    <tr class="hover:bg-base-200/50">
                                                        <td class="text-base-content/50">{{ $idx + 1 }}</td>
                                                        <td class="font-medium text-xs">{{ $s->customer_code }}</td>
                                                        <td class="truncate max-w-[150px]" title="{{ $s->customer_name }}">{{ $s->customer_name ?? '-' }}</td>
                                                        <td class="text-center">
                                                            @if($s->asal === 'Y' && $s->tujuan !== 'Y')
                                                                <span class="badge badge-outline badge-info text-[9px] gap-1 px-1.5 py-2">
                                                                    {{ str_replace('h', 'Hari ', $swapHariAsal) }} <x-heroicon-s-arrow-right class="w-3 h-3" /> {{ str_replace('h', 'Hari ', $swapHariTujuan) }}
                                                                </span>
                                                            @elseif($s->tujuan === 'Y' && $s->asal !== 'Y')
                                                                <span class="badge badge-outline badge-info text-[9px] gap-1 px-1.5 py-2">
                                                                    {{ str_replace('h', 'Hari ', $swapHariTujuan) }} <x-heroicon-s-arrow-right class="w-3 h-3" /> {{ str_replace('h', 'Hari ', $swapHariAsal) }}
                                                                </span>
                                                            @else
                                                                <span class="badge badge-outline badge-ghost text-[9px]">Bertukar Penuh</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4 flex items-start gap-2 text-warning bg-warning/10 p-2 rounded border border-warning/20">
                                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 shrink-0" />
                                        <p class="text-xs font-medium">Mohon cek kembali sebelum eksekusi. Data yang tertukar akan memengaruhi target kunjungan harian.</p>
                                    </div>
                                @else
                                    <div class="py-8 text-center text-base-content/50 border border-dashed border-base-300 rounded-lg">
                                        <x-heroicon-o-face-smile class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                        <p class="text-xs font-medium">Tidak ada toko yang menggunakan hari asal/tujuan tersebut pada minggu yang dipilih.</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($swapTab === 'minggu')
                    <div class="space-y-4">
                        {{-- Form Row 1 --}}
                        <div>
                            <label class="text-xs text-base-content/50 font-bold uppercase block mb-1">Pilih Salesman</label>
                            <select wire:model.live="swapSalesmanCode" class="select select-bordered select-sm w-full">
                                <option value="">-- Pilih Salesman --</option>
                                @foreach($this->headerSalesmans as $salesman)
                                    <option value="{{ $salesman->salesman_code }}">{{ $salesman->salesman_name }} ({{ $salesman->salesman_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Form Row 2 --}}
                        <div class="flex items-center gap-4 bg-base-200/30 p-3 rounded-lg border border-base-200">
                            <div class="flex-1">
                                <label class="text-[10px] text-base-content/50 font-bold uppercase block mb-1">Minggu Asal</label>
                                <select wire:model.live="swapMingguAsal" class="select select-bordered select-sm w-full bg-base-100">
                                    <option value="">- Pilih Minggu -</option>
                                    @foreach(['w1'=>'Minggu 1 (M1)', 'w2'=>'Minggu 2 (M2)', 'w3'=>'Minggu 3 (M3)', 'w4'=>'Minggu 4 (M4)'] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pt-4 text-base-content/40">
                                <x-heroicon-o-arrow-right class="w-5 h-5" />
                            </div>
                            <div class="flex-1">
                                <label class="text-[10px] text-base-content/50 font-bold uppercase block mb-1">Minggu Tujuan</label>
                                <select wire:model.live="swapMingguTujuan" class="select select-bordered select-sm w-full bg-base-100">
                                    <option value="">- Pilih Minggu -</option>
                                    @foreach(['w1'=>'Minggu 1 (M1)', 'w2'=>'Minggu 2 (M2)', 'w3'=>'Minggu 3 (M3)', 'w4'=>'Minggu 4 (M4)'] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Live Preview Section --}}
                        @if($swapSalesmanCode && $swapMingguAsal && $swapMingguTujuan && $swapMingguAsal !== $swapMingguTujuan)
                            <div class="mt-6 pt-4 border-t border-base-200">
                                <div class="flex justify-between items-end mb-3">
                                    <h4 class="text-sm font-bold text-base-content/80">Pratinjau Toko Terdampak</h4>
                                </div>
                                
                                @if($this->affectedMingguSummary->count() > 0)
                                    <div class="overflow-x-auto max-h-[250px] border border-base-200 rounded-lg shadow-inner bg-base-100">
                                        <table class="table table-xs table-pin-rows table-pin-cols">
                                            <thead class="bg-base-200/80 text-base-content/70">
                                                <tr>
                                                    <th>Hari</th>
                                                    <th class="text-center">Total Toko ({{ strtoupper($swapMingguAsal) }})</th>
                                                    <th class="text-center">Total Toko ({{ strtoupper($swapMingguTujuan) }})</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($this->affectedMingguSummary as $sum)
                                                    <tr class="hover:bg-base-200/50">
                                                        <td class="font-medium text-xs">{{ $sum->hari }}</td>
                                                        <td class="text-center">
                                                            <div class="badge {{ $sum->asal > 0 ? 'badge-primary badge-outline' : 'badge-ghost text-base-content/30 border-dashed' }} text-xs font-bold w-12">{{ $sum->asal }}</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="badge {{ $sum->tujuan > 0 ? 'badge-primary badge-outline' : 'badge-ghost text-base-content/30 border-dashed' }} text-xs font-bold w-12">{{ $sum->tujuan }}</div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4 flex items-start gap-2 text-warning bg-warning/10 p-2 rounded border border-warning/20">
                                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 shrink-0" />
                                        <p class="text-xs font-medium">Mohon cek kembali sebelum eksekusi. Sistem akan otomatis saling bertukar nilai antara {{ strtoupper($swapMingguAsal) }} dan {{ strtoupper($swapMingguTujuan) }}.</p>
                                    </div>
                                @else
                                    <div class="py-8 text-center text-base-content/50 border border-dashed border-base-300 rounded-lg">
                                        <x-heroicon-o-face-smile class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                        <p class="text-xs font-medium">Tidak ada toko yang menggunakan minggu asal/tujuan tersebut.</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($swapTab === 'salesman')
                    <div class="space-y-4">
                        {{-- Form Row --}}
                        <div class="flex items-center gap-4 bg-base-200/30 p-3 rounded-lg border border-base-200">
                            <div class="flex-1">
                                <label class="text-[10px] text-base-content/50 font-bold uppercase block mb-1">Salesman Asal</label>
                                <select wire:model.live="swapSalesmanAsal" class="select select-bordered select-sm w-full bg-base-100">
                                    <option value="">- Pilih Salesman Asal -</option>
                                    @foreach($this->headerSalesmans as $salesman)
                                        <option value="{{ $salesman->salesman_code }}">{{ $salesman->salesman_name }} ({{ $salesman->salesman_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pt-4 text-base-content/40">
                                <x-heroicon-s-arrows-right-left class="w-5 h-5" />
                            </div>
                            <div class="flex-1">
                                <label class="text-[10px] text-base-content/50 font-bold uppercase block mb-1">Salesman Tujuan</label>
                                <select wire:model.live="swapSalesmanTujuan" class="select select-bordered select-sm w-full bg-base-100">
                                    <option value="">- Pilih Salesman Tujuan -</option>
                                    @foreach($this->headerSalesmans as $salesman)
                                        <option value="{{ $salesman->salesman_code }}">{{ $salesman->salesman_name }} ({{ $salesman->salesman_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Live Preview Section --}}
                        @if($swapSalesmanAsal && $swapSalesmanTujuan && $swapSalesmanAsal !== $swapSalesmanTujuan)
                            <div class="mt-6 pt-4 border-t border-base-200">
                                <h4 class="text-sm font-bold text-base-content/80 mb-3">Pratinjau Pertukaran</h4>
                                
                                @if($this->affectedSalesmanSummary['asal']['count'] > 0 || $this->affectedSalesmanSummary['tujuan']['count'] > 0)
                                    <div class="flex gap-4">
                                        <div class="flex-1 bg-base-100 border border-base-200 rounded-lg p-4 shadow-sm text-center">
                                            <p class="text-xs font-bold text-base-content/50 uppercase mb-1">Toko Milik</p>
                                            <h5 class="font-extrabold text-primary mb-2 line-clamp-1" title="{{ $this->affectedSalesmanSummary['asal']['name'] }}">
                                                {{ $this->affectedSalesmanSummary['asal']['name'] }}
                                            </h5>
                                            <div class="text-3xl font-black">{{ $this->affectedSalesmanSummary['asal']['count'] }}</div>
                                            <p class="text-[10px] mt-1 text-base-content/40">Toko</p>
                                        </div>
                                        <div class="flex-1 bg-base-100 border border-base-200 rounded-lg p-4 shadow-sm text-center">
                                            <p class="text-xs font-bold text-base-content/50 uppercase mb-1">Toko Milik</p>
                                            <h5 class="font-extrabold text-primary mb-2 line-clamp-1" title="{{ $this->affectedSalesmanSummary['tujuan']['name'] }}">
                                                {{ $this->affectedSalesmanSummary['tujuan']['name'] }}
                                            </h5>
                                            <div class="text-3xl font-black">{{ $this->affectedSalesmanSummary['tujuan']['count'] }}</div>
                                            <p class="text-[10px] mt-1 text-base-content/40">Toko</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-start gap-2 text-info bg-info/10 p-2 rounded border border-info/20">
                                        <x-heroicon-s-information-circle class="w-5 h-5 shrink-0" />
                                        <p class="text-xs font-medium">Sistem akan menukar <strong>seluruh jadwal kunjungan dan toko</strong> antara kedua salesman ini.</p>
                                    </div>
                                @else
                                    <div class="py-8 text-center text-base-content/50 border border-dashed border-base-300 rounded-lg">
                                        <x-heroicon-o-face-smile class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                        <p class="text-xs font-medium">Kedua salesman tidak memiliki toko untuk ditukar pada distributor ini.</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-16 text-center text-base-content/50 border border-dashed border-base-300 rounded-lg">
                        <x-heroicon-o-wrench-screwdriver class="w-12 h-12 mx-auto mb-3 opacity-30" />
                        <h4 class="font-bold mb-1">Segera Hadir</h4>
                        <p class="text-xs">Fitur Tukar {{ ucfirst($swapTab) }} sedang dalam tahap pengembangan.</p>
                    </div>
                @endif
            </div>

            <x-slot:footer>
                <button class="btn btn-ghost" wire:click="closeSwapModal">Batal</button>
                @if($swapTab === 'hari' && $swapSalesmanCode && $swapHariAsal && $swapHariTujuan && $swapHariAsal !== $swapHariTujuan && $this->affectedStores->count() > 0)
                    <button class="btn btn-primary" wire:click="executeSwapDay">
                        <x-heroicon-s-arrows-right-left class="w-4 h-4 mr-1" wire:loading.remove wire:target="executeSwapDay" />
                        <span wire:loading wire:target="executeSwapDay" class="loading loading-spinner loading-xs mr-1"></span>
                        Eksekusi Pertukaran
                    </button>
                @elseif($swapTab === 'minggu' && $swapSalesmanCode && $swapMingguAsal && $swapMingguTujuan && $swapMingguAsal !== $swapMingguTujuan && $this->affectedMingguSummary->count() > 0)
                    <button class="btn btn-primary" wire:click="executeSwapMinggu">
                        <x-heroicon-s-arrows-right-left class="w-4 h-4 mr-1" wire:loading.remove wire:target="executeSwapMinggu" />
                        <span wire:loading wire:target="executeSwapMinggu" class="loading loading-spinner loading-xs mr-1"></span>
                        Eksekusi Pertukaran
                    </button>
                @elseif($swapTab === 'salesman' && $swapSalesmanAsal && $swapSalesmanTujuan && $swapSalesmanAsal !== $swapSalesmanTujuan && ($this->affectedSalesmanSummary['asal']['count'] > 0 || $this->affectedSalesmanSummary['tujuan']['count'] > 0))
                    <button class="btn btn-primary" wire:click="executeSwapSalesman">
                        <x-heroicon-s-arrows-right-left class="w-4 h-4 mr-1" wire:loading.remove wire:target="executeSwapSalesman" />
                        <span wire:loading wire:target="executeSwapSalesman" class="loading loading-spinner loading-xs mr-1"></span>
                        Eksekusi Pertukaran
                    </button>
                @else
                    <button class="btn btn-primary" disabled>Eksekusi Pertukaran</button>
                @endif
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Bulk Delete Modal --}}
    @if($showBulkDeleteModal)
        <x-ui.modal id="modal-bulk-delete" title="Hapus Jadwal Massal" icon="trash" size="xl" open="true" wire:close="closeBulkDeleteModal" class="!items-start" boxClass="!mt-8 border-t-4 border-error">
            <div class="px-2 pb-2">
                {{-- Form Filters --}}
                <div class="flex flex-wrap gap-4 bg-error/5 p-4 rounded-lg border border-error/20 mb-6">
                    <div class="flex-1 min-w-[200px]">
                        <label class="text-xs text-error font-bold block mb-1">Salesman</label>
                        <select wire:model.live="bdSalesman" class="select select-bordered select-sm w-full bg-base-100 border-error/30 focus:border-error">
                            <option value="">- Pilih Salesman -</option>
                            @foreach($this->headerSalesmans as $salesman)
                                <option value="{{ $salesman->salesman_code }}">{{ $salesman->salesman_name }} ({{ $salesman->salesman_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="text-xs text-base-content/70 font-bold block mb-1">Hari (Opsional)</label>
                        <select wire:model.live="bdHari" class="select select-bordered select-sm w-full bg-base-100">
                            <option value="">-- Semua Hari --</option>
                            <option value="h1">Senin</option>
                            <option value="h2">Selasa</option>
                            <option value="h3">Rabu</option>
                            <option value="h4">Kamis</option>
                            <option value="h5">Jumat</option>
                            <option value="h6">Sabtu</option>
                            <option value="h7">Minggu</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="text-xs text-base-content/70 font-bold block mb-1">Minggu (Opsional)</label>
                        <select wire:model.live="bdMinggu" class="select select-bordered select-sm w-full bg-base-100">
                            <option value="">-- Semua Minggu --</option>
                            <option value="w1">Minggu 1</option>
                            <option value="w2">Minggu 2</option>
                            <option value="w3">Minggu 3</option>
                            <option value="w4">Minggu 4</option>
                        </select>
                    </div>
                </div>

                {{-- Preview Table --}}
                @if($bdSalesman)
                    <div class="flex justify-between items-end mb-2">
                        <h4 class="text-sm font-bold text-base-content/80">Pilih Toko untuk Direset (Jadwal Dikosongkan)</h4>
                        <div class="text-xs font-medium text-base-content/60">
                            <span class="text-error font-bold">{{ count($bdSelectedIds) }}</span> dari {{ $this->bdPreviewStores->count() }} dipilih
                        </div>
                    </div>
                    
                    @if($this->bdPreviewStores->count() > 0)
                        <div class="overflow-x-auto max-h-[300px] border border-base-200 rounded-lg shadow-inner bg-base-100 relative">
                            <table class="table table-xs table-pin-rows">
                                <thead class="bg-base-200/80">
                                    <tr>
                                        <th class="w-10 text-center">
                                            <input type="checkbox" wire:model.live="bdSelectAll" class="checkbox checkbox-error checkbox-sm" />
                                        </th>
                                        <th>Kode</th>
                                        <th>Nama Toko</th>
                                        <th>Hari</th>
                                        <th>Minggu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->bdPreviewStores as $store)
                                        <tr class="hover:bg-error/5 cursor-pointer transition-colors" wire:key="bd-store-{{ $store->id }}">
                                            <td class="text-center">
                                                <input type="checkbox" wire:model.live="bdSelectedIds" value="{{ $store->id }}" class="checkbox checkbox-error checkbox-sm" />
                                            </td>
                                            <td class="font-medium text-xs">{{ $store->customer_code }}</td>
                                            <td class="truncate max-w-[150px]">{{ $store->customer_name ?? '-' }}</td>
                                            <td>
                                                <div class="flex gap-0.5">
                                                    @foreach(['h1'=>'Sen','h2'=>'Sel','h3'=>'Rab','h4'=>'Kam','h5'=>'Jum','h6'=>'Sab','h7'=>'Min'] as $col => $lbl)
                                                        @if($store->$col === 'Y') <span class="badge badge-outline text-[9px]">{{ $lbl }}</span> @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex gap-0.5">
                                                    @foreach(['w1'=>'M1','w2'=>'M2','w3'=>'M3','w4'=>'M4'] as $col => $lbl)
                                                        @if($store->$col === 'Y') <span class="badge badge-outline text-[9px]">{{ $lbl }}</span> @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-8 text-center text-base-content/50 border border-dashed border-base-300 rounded-lg">
                            <x-heroicon-o-face-smile class="w-8 h-8 mx-auto mb-2 opacity-30" />
                            <p class="text-xs font-medium">Tidak ada toko yang sesuai dengan kriteria filter.</p>
                        </div>
                    @endif
                @else
                    <div class="py-12 text-center text-base-content/40 border border-dashed border-base-300 rounded-lg">
                        <x-heroicon-o-funnel class="w-10 h-10 mx-auto mb-2 opacity-20" />
                        <p class="text-xs">Silakan pilih <strong>Salesman</strong> terlebih dahulu untuk memunculkan daftar toko.</p>
                    </div>
                @endif
            </div>

            <x-slot:footer>
                <div class="flex-1 text-left mr-4">
                    <input type="text" wire:model.live="bdReason" class="input input-bordered input-error input-sm w-full" placeholder="Alasan reset (wajib diisi)..." />
                </div>
                <button class="btn btn-ghost" wire:click="closeBulkDeleteModal">Batal</button>
                @if(count($bdSelectedIds) > 0)
                    <button class="btn btn-error" wire:click="executeBulkDelete" @if(strlen(trim($bdReason)) < 5) disabled @endif>
                        <x-heroicon-s-trash class="w-4 h-4 mr-1" wire:loading.remove wire:target="executeBulkDelete" />
                        <span wire:loading wire:target="executeBulkDelete" class="loading loading-spinner loading-xs mr-1"></span>
                        Reset {{ count($bdSelectedIds) }} Jadwal
                    </button>
                @else
                    <button class="btn btn-error opacity-50" disabled>Reset Jadwal</button>
                @endif
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Collision Detection Modal --}}
    @if($showCollisionModal)
        <x-ui.modal id="collision_modal" title="Detail Bentrok Jadwal" width="max-w-3xl" icon="heroicon-s-exclamation-triangle" iconClass="text-error" onClose="closeCollisionModal">
            <div class="p-6">
                <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between p-4 bg-error/10 border border-error/20 rounded-lg shadow-sm gap-4">
                    <div>
                        <h4 class="font-bold text-error text-lg mb-1">Toko: {{ $collisionCustomerName }}</h4>
                        <p class="text-sm text-base-content/70">Terdeteksi kunjungan tumpang tindih oleh beberapa Salesman di bulan yang sama.</p>
                    </div>
                    <div class="bg-base-100 p-2 rounded-md border border-base-200 hidden md:block">
                        <x-heroicon-o-users class="w-8 h-8 text-error" />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-base-200 shadow-sm bg-base-100">
                    <table class="table table-sm w-full">
                        <thead class="bg-base-200/50 text-base-content/70 uppercase text-[10px] tracking-wider">
                            <tr>
                                <th>Salesman</th>
                                <th class="text-center">Hari Kunjungan</th>
                                <th class="text-center">Minggu Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collisionDetails as $detail)
                                <tr class="hover:bg-base-200/30">
                                    <td class="font-medium whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="avatar placeholder">
                                              <div class="bg-neutral text-neutral-content w-8 rounded-full">
                                                <span class="text-xs">{{ substr($detail['salesman_name'] ?? 'X', 0, 1) }}</span>
                                              </div>
                                            </div>
                                            <div>
                                                <div>{{ $detail['salesman_name'] ?? '-' }}</div>
                                                <div class="text-[10px] text-base-content/50">{{ $detail['salesman_code'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-1 justify-center">
                                            @foreach(['h1'=>'Sen','h2'=>'Sel','h3'=>'Rab','h4'=>'Kam','h5'=>'Jum','h6'=>'Sab','h7'=>'Min'] as $col => $lbl)
                                                @if($detail[$col] === 'Y') 
                                                    <span class="badge badge-sm badge-success badge-outline font-semibold">{{ $lbl }}</span> 
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-1 justify-center">
                                            @foreach(['w1'=>'M1','w2'=>'M2','w3'=>'M3','w4'=>'M4'] as $col => $lbl)
                                                @if($detail[$col] === 'Y') 
                                                    <span class="badge badge-sm badge-primary badge-outline font-semibold">{{ $lbl }}</span> 
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-base-content/50 text-sm">Data tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-xs text-base-content/50 bg-base-200 p-3 rounded-md flex items-start gap-2">
                    <x-heroicon-o-information-circle class="w-4 h-4 shrink-0 mt-0.5" />
                    <p>Satu toko idealnya hanya dipegang oleh satu salesman dalam bulan yang sama. Silakan gunakan fitur <strong>Hapus Massal</strong> atau <strong>Tukar Jadwal</strong> pada halaman utama untuk menyelesaikan bentrok ini.</p>
                </div>
            </div>

            <x-slot:footer>
                <button class="btn btn-outline btn-error btn-sm w-full" wire:click="closeCollisionModal">Tutup</button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Toasts Container --}}
    <div class="toast toast-top toast-right z-[9999]">
        <template x-for="toast in toasts" :key="toast.id">
            <div :class="{
                'alert flex items-center gap-2 border-none shadow-xl text-white font-medium': true,
                'bg-success': toast.type === 'success',
                'bg-error': toast.type === 'error',
                'bg-info': toast.type === 'info',
            }">
                <x-heroicon-s-check-circle x-show="toast.type === 'success'" class="w-5 h-5 text-white/80" />
                <x-heroicon-s-x-circle x-show="toast.type === 'error'" class="w-5 h-5 text-white/80" />
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>
</div>
