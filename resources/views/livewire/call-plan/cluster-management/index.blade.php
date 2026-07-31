<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Management & Clustering</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 shadow-sm relative z-50">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <button wire:click="setActiveTab('management')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'management' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">Management Cluster</button>
            <button wire:click="setActiveTab('clustering')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'clustering' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">Clustering Toko</button>
        </div>

        @if($activeTab === 'management')
            {{-- Toolbar Actions (Aligned to Right on Tab Bar) --}}
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full sm:w-auto justify-end">
                {{-- Team Dropdown Input --}}
                <div class="w-full sm:w-52 relative">
                    <select wire:model.live="managementSelectedTeam" class="select select-xs select-bordered w-full text-xs font-semibold bg-base-100 h-7 min-h-0">
                        <option value="">-- Pilih Team Sales --</option>
                        @foreach($managementTeams as $team)
                            <option value="{{ $team->kode_team }}">{{ $team->kode_team }} - {{ $team->nama_team }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Distributor Dropdown Input --}}
                <div class="w-full sm:w-52 relative">
                    <select wire:model="managementSelectedDistributor" class="select select-xs select-bordered w-full text-xs font-semibold bg-base-100 h-7 min-h-0" {{ empty($managementSelectedTeam) ? 'disabled' : '' }}>
                        <option value="">-- Semua Distributor --</option>
                        @foreach($managementDistributors as $dist)
                            <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_code }} - {{ $dist->distributor_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Apply Filter Button --}}
                <button type="button" wire:click="applyManagementFilter" wire:loading.attr="disabled" class="btn btn-xs btn-primary text-white gap-1.5 font-bold shadow-xs h-7 min-h-0 px-3 shrink-0" {{ empty($managementSelectedTeam) ? 'disabled' : '' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <span>Terapkan</span>
                </button>
            </div>
        @elseif($activeTab === 'clustering')
            {{-- Teleport Target for Clustering Toolbar --}}
            <div id="clustering-toolbar-teleport" class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full sm:w-auto justify-end flex-1"></div>
        @endif
    </div>

    <div class="toast toast-top toast-end z-[9999] mt-12">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(function() { show = false; }, 4000)" x-show="show" x-transition.opacity.duration.500ms class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success flex justify-between items-start backdrop-blur-sm">
                <div class="flex items-start gap-3">
                    <x-heroicon-s-check-circle class="w-6 h-6 shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">{{ session('message') }}</div>
                    </div>
                </div>
                <button type="button" @click="show = false" class="btn btn-ghost btn-sm btn-circle shrink-0 hover:bg-success/20">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-init="setTimeout(function() { show = false; }, 4000)" x-show="show" x-transition.opacity.duration.500ms class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error flex justify-between items-start backdrop-blur-sm">
                <div class="flex items-start gap-3">
                    <x-heroicon-s-x-circle class="w-6 h-6 shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                </div>
                <button type="button" @click="show = false" class="btn btn-ghost btn-sm btn-circle shrink-0 hover:bg-error/20">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
        @endif
    </div>

    @if($activeTab === 'management')
        <livewire:call-plan.cluster-management.management-tab wire:key="tab-management" />
    @elseif($activeTab === 'clustering')
        <livewire:call-plan.cluster-management.clustering-tab wire:key="tab-clustering" />
    @endif
</div>

@once
@push('styles')
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <style>
        .cluster-marker {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.4);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .cluster-marker:hover {
            transform: scale(1.5);
            z-index: 9999 !important;
        }
        .maplibregl-popup {
            z-index: 99999 !important;
        }
        .maplibregl-popup-content {
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 200px;
        }
        .hover-popup .maplibregl-popup-content {
            padding: 6px 10px;
            min-width: auto;
            pointer-events: none;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
@endpush
@endonce
