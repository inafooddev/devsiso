<div>
    <x-slot name="title">Mapping Supervisor/Team Elite</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
        {{-- Notifikasi --}}
        <div class="mb-6 space-y-3">
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
        </div>

        <x-card flush title="Mapping Supervisor/Team Elite" icon="user-group" subtitle="Daftar data mapping team elite dan supervisor code" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari data..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Region Filter --}}
                    <select wire:model.live="regionFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    {{-- Area Filter --}}
                    <select wire:model.live="areaFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300" @if(!$regionFilter) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    {{-- Level Filter --}}
                    <select wire:model.live="levelFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Level</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                        @endforeach
                    </select>

                    <button wire:click="export" class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs ml-1"></span>
                    </button>

                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah Data
                    </button>
                </div>
            </x-slot:actions>

            <x-ui.table empty="Tidak ada data mapping yang cocok dengan pencarian dan filter Anda.">
                <x-slot:head>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Kode Eska (Team Elite)</th>
                        <th>Nama Eska</th>
                        <th>Kode Siso (Supervisor)</th>
                        <th>Nama Siso</th>
                        <th>Level</th>
                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($data as $index => $item)
                    <tr wire:key="mapping-{{ $index }}" class="group text-sm">
                        <td><span class="text-xs font-semibold text-base-content/40">{{ $data->firstItem() + $index }}</span></td>
                        <td><span class="font-bold text-base-content/80">{{ $item->region_name ?? '-' }}</span></td>
                        <td><span class="font-bold text-base-content/80">{{ $item->area_name ?? '-' }}</span></td>
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
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="openEditModal({{ $item->id }})" class="btn btn-xs btn-circle btn-ghost text-primary hover:bg-primary/10" title="Edit">
                                    <x-heroicon-s-pencil class="w-4 h-4" />
                                </button>
                                <button wire:click="deleteMapping({{ $item->id }})" wire:confirm="Apakah Anda yakin ingin menghapus data mapping ini?" class="btn btn-xs btn-circle btn-ghost text-error hover:bg-error/10" title="Hapus">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if($data->hasPages())
                <div class="mt-4 px-6">{{ $data->links() }}</div>
            @endif
        </x-card>
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
