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
        <a href="#" class="tab">Dashboard</a>
        <a href="#" class="tab">Summary</a>
        <a href="#" class="tab tab-active bg-primary text-primary-content">Detail</a>
        <a href="#" class="tab">Maps</a>
        <a href="#" class="tab">Outlet non JKS</a>
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
                </div>
            </div>
            
            <x-slot:footer>
                <button class="btn btn-ghost" wire:click="closeEditModal">Batal</button>
                <button class="btn btn-primary" wire:click="saveJadwal">Simpan Perubahan</button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <x-ui.modal id="modal-delete" title="Konfirmasi Hapus" icon="trash" size="sm" open="true" wire:close="closeDeleteModal">
            <div class="px-2 pb-2">
                <p class="text-sm text-base-content/80">Apakah Anda yakin ingin menghapus seluruh jadwal kunjungan untuk toko ini? Aksi ini tidak dapat dibatalkan.</p>
            </div>
            <x-slot:footer>
                <button class="btn btn-ghost" wire:click="closeDeleteModal">Batal</button>
                <button class="btn btn-error" wire:click="executeDelete">Ya, Hapus Jadwal</button>
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
                @else
                    <button class="btn btn-primary" disabled>Eksekusi Pertukaran</button>
                @endif
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
