<dialog id="filter_modal" class="modal items-start" wire:ignore.self>
    <div class="modal-box relative overflow-visible mt-20">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="document.getElementById('filter_modal').close()">✕</button>
        <h3 class="font-bold text-lg mb-4">Filter JKS Salesman</h3>
        
        <form wire:submit="applyFilters" x-on:submit="document.getElementById('filter_modal').close()">
            @php $accessLevel = auth()->user() ? auth()->user()->getAccessLevel() : 'nasional'; @endphp
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Bulan</span></label>
                    <input type="month" wire:model="selectedBulan" class="input input-bordered input-sm w-full">
                </div>

                @if(in_array($accessLevel, ['nasional', 'region']))
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Region</span></label>
                    <select wire:model.live="selectedRegion" class="select select-bordered select-sm w-full" @if(count($this->filterRegions) <= 1 && $accessLevel != 'nasional') disabled @endif>
                        @if(count($this->filterRegions) != 1) <option value="">-- Pilih Region --</option> @endif
                        @foreach($this->filterRegions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(in_array($accessLevel, ['nasional', 'region', 'area']))
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Area</span></label>
                    <select wire:model.live="selectedArea" class="select select-bordered select-sm w-full" @if(count($this->filterAreas) <= 1 && $accessLevel != 'nasional') disabled @endif>
                        @if(count($this->filterAreas) != 1) <option value="">-- Pilih Area --</option> @endif
                        @foreach($this->filterAreas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(in_array($accessLevel, ['nasional', 'region', 'area', 'supervisor']))
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Spv (Team Elite)</span></label>
                    <select wire:model.live="selectedSupervisor" class="select select-bordered select-sm w-full" @if(count($this->filterSupervisors) <= 1 && $accessLevel != 'nasional') disabled @endif>
                        @if(count($this->filterSupervisors) != 1) <option value="">-- Pilih Supervisor --</option> @endif
                        @foreach($this->filterSupervisors as $spv)
                            <option value="{{ $spv->supervisor_code }}">{{ $spv->description }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Distributor</span></label>
                    <x-searchable-select 
                        wire:model.live="selectedDistributor"
                        wire:key="select-dist-{{ $selectedSupervisor ?? 'all' }}"
                        :options="$this->filterDistributors->map(fn($d) => ['value' => $d->distributor_code, 'label' => $d->distributor_code . ' - ' . $d->distributor_name])->toArray()"
                        placeholder="-- Pilih Distributor --"
                    />
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" wire:click="resetFilters" @click="document.getElementById('filter_modal').close()" class="btn btn-sm btn-ghost text-error">Reset</button>
                <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
