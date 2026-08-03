<div class="modal-box w-11/12 max-w-sm">
    <h3 class="font-bold text-lg mb-4">Filter Wilayah & Kuartal</h3>
    
    <div class="space-y-3">
        <div class="form-control">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Kuartal</span></label>
            <select wire:model="kuartal" class="select select-sm select-bordered w-full">
                @foreach($kuartals as $q)
                    <option value="{{ $q->quarter }}">Quarter {{ $q->quarter }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-control">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Region</span></label>
            <select wire:model.live="region" class="select select-sm select-bordered w-full">
                <option value="">Semua Region</option>
                @foreach($regions as $r)
                    <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-control">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Area</span></label>
            <select wire:model.live="area" class="select select-sm select-bordered w-full" {{ empty($areas) ? 'disabled' : '' }}>
                <option value="">Semua Area</option>
                @foreach($areas as $a)
                    <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-control">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Supervisor</span></label>
            <select wire:model.live="supervisor" class="select select-sm select-bordered w-full" {{ empty($supervisors) ? 'disabled' : '' }}>
                <option value="">Semua Supervisor</option>
                @foreach($supervisors as $s)
                    <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-control">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Distributor</span></label>
            <select wire:model="distributor" class="select select-sm select-bordered w-full" {{ empty($distributors) ? 'disabled' : '' }}>
                <option value="">Semua Distributor</option>
                @foreach($distributors as $d)
                    <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="modal-action">
        <button type="button" wire:click="applyFilter" class="btn btn-sm btn-primary w-full sm:w-auto">Terapkan Filter</button>
        <form method="dialog" class="w-full sm:w-auto">
            <button class="btn btn-sm w-full">Tutup</button>
        </form>
    </div>
</div>
