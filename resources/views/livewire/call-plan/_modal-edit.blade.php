{{-- ============================================================
     MODAL: Edit Jadwal Toko — x-show
     ============================================================ --}}
<div
    x-show="$wire.showEditScheduleModal"
    x-transition.opacity.duration.200ms
    x-cloak
    class="fixed inset-0 z-[2000] flex items-center justify-center px-4"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
         @click="$wire.set('showEditScheduleModal', false)"></div>

    {{-- Modal Box --}}
    <div class="relative bg-base-100 rounded-2xl shadow-2xl w-full max-w-sm border border-base-300 overflow-hidden z-10">

        @if($editingStore)
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-primary/10">
                        <x-heroicon-s-pencil-square class="w-5 h-5 text-primary" />
                    </div>
                    <div>
                        <h3 class="font-bold text-base-content">{{ $editingStore['name'] }}</h3>
                        <x-ui.badge variant="neutral" size="xs">
                            <x-heroicon-s-identification class="w-3 h-3" />
                            {{ $editingStore['code'] }}
                        </x-ui.badge>
                    </div>
                </div>
                <button wire:click="$set('showEditScheduleModal', false)"
                        class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:text-base-content">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4">

                {{-- Salesman --}}
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs font-bold">Pindahkan ke Salesman (SE)</span>
                    </label>
                    <select wire:model="selectedSalesmanInModal" class="select select-sm select-bordered w-full">
                        <option value="">-- Pilih Salesman --</option>
                        @foreach($salesmen as $sls)
                            <option value="{{ $sls->slsno }}">{{ $sls->slsname }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Minggu --}}
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs font-bold">Pilih Minggu</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($options['weeks'] as $w)
                            <label class="flex items-center gap-2 p-2 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200 transition">
                                <input type="checkbox" value="{{ $w }}"
                                       {{ in_array($w, $editingStore['weeks']) ? 'checked' : '' }}
                                       class="week-edit-check checkbox checkbox-xs checkbox-primary">
                                <span class="text-xs font-medium">{{ $w }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Hari --}}
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs font-bold">Pilih Hari</span>
                    </label>
                    <select id="edit-day-select" class="select select-sm select-bordered w-full">
                        @foreach($options['days'] as $d)
                            <option value="{{ $d }}" {{ $d == $editingStore['day'] ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-base-300 bg-base-200/50">
                <x-ui.button variant="neutral" outline wire:click="$set('showEditScheduleModal', false)">Batal</x-ui.button>
                <x-ui.button variant="primary" icon="check" x-on:click="saveManualEdit()">Simpan Jadwal</x-ui.button>
            </div>
        @endif
    </div>
</div>
