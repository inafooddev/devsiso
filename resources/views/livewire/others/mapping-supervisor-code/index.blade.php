<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Mapping Supervisor/Team Elite</x-slot>

    {{-- Notifikasi Toast --}}
    <div class="toast toast-top toast-center z-[100] mt-16">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                    <div class="text-sm">{{ session('message') }}</div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                    <div class="text-sm">{{ session('error') }}</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full md:w-auto">
                <h2 class="text-base md:text-lg font-bold">Mapping Supervisor/Team Elite</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar data mapping team elite dan supervisor code</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start md:justify-end gap-2 md:gap-3 w-full md:w-auto">
                {{-- Search --}}
                <div class="relative group grow sm:grow-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari data..."
                           class="input input-sm input-bordered pl-10 w-full sm:w-48 lg:w-56 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>

                {{-- Region Filter --}}
                <select wire:model.live="regionFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>

                {{-- Area Filter --}}
                <select wire:model.live="areaFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0" @if(!$regionFilter) disabled @endif>
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                    @endforeach
                </select>

                {{-- Level Filter --}}
                <select wire:model.live="levelFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Level</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                    @endforeach
                </select>

                <div class="flex flex-wrap items-center gap-1 md:gap-2 mt-2 sm:mt-0 w-full sm:w-auto justify-end">
                    <button wire:click="export" wire:loading.attr="disabled" wire:target="export" class="btn btn-sm btn-ghost bg-base-100 border border-base-300 rounded-xl" title="Export">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" wire:loading.remove wire:target="export" />
                        <span class="loading loading-spinner loading-xs" wire:loading wire:target="export"></span>
                        <span class="hidden sm:inline">Export</span>
                    </button>

                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah Data
                    </button>
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-16">No</th>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Kode Eska (Team Elite)</th>
                        <th>Nama Eska</th>
                        <th>Kode Siso (Supervisor)</th>
                        <th>Nama Siso</th>
                        <th>Level</th>
                        <th class="text-center w-24 bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs md:text-sm">
                    @forelse ($data as $index => $item)
                        <tr wire:key="mapping-{{ $item->id }}" class="hover:bg-base-200/50 transition-colors group">
                            <th><span class="font-semibold text-base-content/50">{{ $data->firstItem() + $index }}</span></th>
                            <td><span class="font-bold text-base-content">{{ $item->region_name ?? '-' }}</span></td>
                            <td><span class="font-bold text-base-content">{{ $item->area_name ?? '-' }}</span></td>
                            <td><span class="font-mono text-base-content/80">{{ $item->kode_eska ?? '-' }}</span></td>
                            <td><span class="font-bold text-base-content/80">{{ $item->nama_eska ?? '-' }}</span></td>
                            <td><span class="font-mono text-base-content/80">{{ $item->kode_siso ?? '-' }}</span></td>
                            <td><span class="font-bold text-base-content/80">{{ $item->nama_siso ?? '-' }}</span></td>
                            <td>
                                @if($item->level)
                                    <span class="badge badge-sm badge-outline">{{ ucfirst($item->level) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openEditModal({{ $item->id }})" class="btn btn-xs btn-square btn-ghost text-primary hover:bg-primary/10 transition-colors" title="Edit">
                                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="deleteMapping({{ $item->id }})" wire:confirm="Apakah Anda yakin ingin menghapus data mapping ini?" class="btn btn-xs btn-square btn-ghost text-error hover:bg-error/10 transition-colors" title="Hapus">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </th>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-base-content/40 bg-base-100">
                                <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                                <p>Tidak ada data mapping yang cocok dengan pencarian dan filter Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        @if($data->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    {{-- ========== MODAL FORM CREATE ========== --}}
    <div x-data="{ open: @entangle('isCreateModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto">
         
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative text-left bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl ring-1 ring-base-content/5 text-base-content my-8">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-plus-circle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditMode ? 'Edit Pemetaan' : 'Tambah Pemetaan' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditMode ? 'Ubah data mapping supervisor' : 'Buat data mapping supervisor baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-5">
                    {{-- Region & Area --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region <span class="text-error">*</span></label>
                            <select wire:model.live="formRegionCode" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('formRegionCode') select-error @enderror">
                                <option value="">-- Pilih Region --</option>
                                @foreach($formRegions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                            @error('formRegionCode') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area <span class="text-error">*</span></label>
                            <select wire:model.live="formAreaCode" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40 @error('formAreaCode') select-error @enderror" @if(!$formRegionCode) disabled @endif>
                                <option value="">-- Pilih Area --</option>
                                @foreach($formAreas as $area)
                                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                @endforeach
                            </select>
                            @error('formAreaCode') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Supervisor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Siso (Supervisor) <span class="text-error">*</span></label>
                        <select wire:model.live="formSisoCode" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40 @error('formSisoCode') select-error @enderror" @if(!$formAreaCode) disabled @endif>
                            <option value="">-- Pilih Supervisor --</option>
                            @foreach($formSupervisors as $supervisor)
                                <option value="{{ $supervisor->supervisor_code }}">
                                    {{ $supervisor->supervisor_code }} - {{ $supervisor->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('formSisoCode') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Team Elite --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Eska (Team Elite) <span class="text-error">*</span></label>
                        
                        @if($formTeamEliteCode)
                            <div class="flex items-center justify-between p-3 border border-primary bg-primary/5 rounded-2xl">
                                <div class="flex flex-col">
                                    <span class="font-bold text-sm">{{ $selectedTeamEliteName }}</span>
                                    <span class="text-xs text-base-content/60 font-mono">{{ $formTeamEliteCode }}</span>
                                </div>
                                <button type="button" wire:click="clearTeamElite" class="btn btn-ghost btn-sm btn-circle text-error">
                                    <x-heroicon-s-x-mark class="w-5 h-5" />
                                </button>
                            </div>
                        @else
                            <div class="relative" x-data="{ open: false }">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30">
                                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="searchTeamElite" 
                                           placeholder="Ketik nama atau kode..." 
                                           @focus="open = true" 
                                           @click.away="open = false" 
                                           class="input input-bordered w-full pl-10 bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('formTeamEliteCode') input-error @enderror">
                                </div>
                                
                                <div x-show="open" 
                                     x-transition
                                     class="absolute z-[100] w-full mt-2 bg-base-100 border border-base-300 rounded-2xl shadow-xl max-h-60 overflow-y-auto">
                                    @if(count($formTeamElites) > 0)
                                        <ul class="p-2 space-y-1">
                                            @foreach($formTeamElites as $te)
                                                <li>
                                                    <button type="button" wire:click="selectTeamElite('{{ $te->team_elite_code }}', '{{ addslashes($te->team_elite_name) }}')" @click="open = false" 
                                                            class="w-full text-left px-4 py-2 hover:bg-base-200 rounded-xl transition-colors flex flex-col">
                                                        <span class="font-bold text-sm">{{ $te->team_elite_name }}</span>
                                                        <span class="text-xs text-base-content/50 font-mono">{{ $te->team_elite_code }}</span>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="p-4 text-center text-sm text-base-content/50">
                                            Data tidak ditemukan atau semua sudah termapping.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @error('formTeamEliteCode') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Level --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Level <span class="text-error">*</span></label>
                        <select wire:model.live="formLevel" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('formLevel') select-error @enderror">
                            <option value="">-- Pilih Level --</option>
                            <option value="region">Region</option>
                            <option value="area">Area</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                        @error('formLevel') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Pemetaan' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
