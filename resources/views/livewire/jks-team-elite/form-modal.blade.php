<div>
    {{-- Modal Form (Create/Edit) --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-init="$watch('open', value => { 
             if(value) { 
                 setTimeout(() => { initFormMap(); window.formMap && window.formMap.resize(); }, 500); 
             } else {
                 if (window.formMap) { window.formMap.remove(); window.formMap = null; }
                 window.formMarkers = [];
             }
         })"
         @update-form-map-markers.window="setTimeout(() => { updateFormMapMarkers($event.detail.selected, $event.detail.recommended) }, 100)"
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-[95vw] lg:w-[70vw] lg:max-w-[70vw] h-[95vh] md:h-[90vh] overflow-hidden ring-1 ring-base-content/5 flex flex-col">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Grup JKS' : 'Tambah JKS Multiple Customer' }}</h3>
                    </div>
                </div>
                <button type="button" @click="if(confirm('Apakah Anda yakin ingin membatalkan? Semua data yang belum disimpan akan hilang.')) open = false" class="btn btn-sm btn-circle btn-error text-white hover:opacity-80 transition-all duration-200 border-none">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col lg:flex-row bg-base-100 h-full">
                {{-- Kiri: Form Input, Search, & Rekomendasi --}}
                <div class="w-full lg:w-[25%] p-4 lg:p-6 border-r border-base-300 flex flex-col overflow-y-auto">
                    @if($formError)
                        <div class="alert alert-error shadow-sm rounded-xl border-none bg-error/10 text-error mb-4 flex items-start gap-3">
                            <x-heroicon-s-x-circle class="w-5 h-5 shrink-0 mt-0.5" />
                            <div class="text-sm font-medium">{{ $formError }}</div>
                        </div>
                    @endif
                    <form id="form-jks" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Tanggal --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal</label>
                                <input wire:model.blur="tanggal" type="date" class="input input-sm input-bordered w-full rounded-xl">
                                @error('tanggal') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Team (fsalesman) - Searchable --}}
                            <div class="space-y-1.5 relative z-[60]" x-data="{
                                    search: '',
                                    open: false,
                                    teams: @js($teams),
                                    selectedCode: @entangle('selectedTeamCode').live,
                                    get teamList() {
                                        return Object.values(this.teams || {});
                                    },
                                    get filteredTeams() {
                                        if (this.search === '') return this.teamList;
                                        let s = String(this.search).toLowerCase();
                                        return this.teamList.filter(t => String(t.nama_team || '').toLowerCase().includes(s) || String(t.kode_team || '').toLowerCase().includes(s));
                                    },
                                    init() {
                                        this.$watch('selectedCode', val => {
                                            let t = this.teamList.find(x => String(x.kode_team) === String(val));
                                            this.search = t ? t.nama_team : '';
                                        });
                                        let t = this.teamList.find(x => String(x.kode_team) === String(this.selectedCode));
                                        this.search = t ? t.nama_team : '';
                                    }
                                }">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Team</label>
                                <div class="relative w-full" @click.away="open = false; let t = teamList.find(x => String(x.kode_team) === String(selectedCode)); search = t ? t.nama_team : ''">
                                    <input type="text" x-model="search" @focus="open = true; search = ''" 
                                           placeholder="-- Ketik untuk mencari Team --" class="input input-sm input-bordered w-full rounded-xl">
                                           
                                    <div x-show="open" x-transition class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg max-h-60 overflow-y-auto" x-cloak>
                                        <div @click="selectedCode = ''; search = ''; open = false"
                                             class="px-3 py-2 text-sm cursor-pointer hover:bg-base-200 text-gray-500 italic">
                                            -- Kosongkan Pilihan --
                                        </div>
                                        <template x-for="(team, index) in filteredTeams" :key="String(team.kode_team) + '-' + index">
                                            <div @click="selectedCode = team.kode_team; search = team.nama_team; open = false"
                                                 class="px-3 py-2 text-sm cursor-pointer hover:bg-base-200"
                                                 :class="{'bg-primary/10 text-primary font-bold': String(selectedCode) === String(team.kode_team)}">
                                                <span x-text="team.nama_team"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredTeams.length === 0" class="px-3 py-2 text-sm text-gray-500">
                                            Tim tidak ditemukan
                                        </div>
                                    </div>
                                </div>
                                @error('selectedTeamCode') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="my-4 border-base-300">

                        {{-- Search Distributor --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Distributor (Opsional)</label>
                            @if($selectedDistributorCode)
                                <div class="flex items-center gap-2 p-2 border border-primary/30 bg-primary/5 rounded-xl text-sm">
                                    <div class="flex-1 font-semibold text-primary">{{ $selectedDistributorCode }} - {{ $searchDistributor }}</div>
                                    <button type="button" wire:click="clearDistributor" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error hover:text-white">
                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @else
                                <input wire:model.live.debounce.300ms="searchDistributor" type="text" placeholder="Ketik nama atau kode distributor..." class="input input-sm input-bordered w-full rounded-xl">
                                
                                @if(count($distributorOptions) > 0)
                                    <ul class="absolute z-[100] w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-1">
                                        @foreach($distributorOptions as $dist)
                                            <li>
                                                <button type="button" wire:click="selectDistributor('{{ $dist['distributor_code'] }}', '{{ addslashes($dist['distributor_name']) }}')" class="w-full text-left px-3 py-2 text-sm hover:bg-base-200 rounded-lg">
                                                    <div class="font-bold">{{ $dist['distributor_code'] }}</div>
                                                    <div class="text-xs opacity-70">{{ $dist['distributor_name'] }}</div>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>

                        {{-- Search Customer --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Customer</label>
                            <div class="relative">
                                <input wire:model.live.debounce.500ms="searchCustomer" type="text" placeholder="Ketik kode, nama, atau alamat..." class="input input-sm input-bordered w-full rounded-xl pr-10">
                                <div wire:loading wire:target="searchCustomer" class="absolute right-3 top-2">
                                    <span class="loading loading-spinner loading-xs text-primary"></span>
                                </div>
                            </div>
                            
                            @if(count($customerOptions) > 0)
                                <ul class="absolute z-[100] w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-1">
                                    @foreach($customerOptions as $cust)
                                        <li>
                                            <div class="w-full text-left px-3 py-2 hover:bg-base-200 rounded-lg flex justify-between items-center group cursor-default">
                                                <div class="flex-1">
                                                    <div class="font-bold text-sm">{{ $cust['custno'] }} - {{ $cust['custname'] }}</div>
                                                    <div class="text-xs opacity-70 truncate">{{ $cust['distributor_name'] }} ({{ $cust['distributor_code'] }})</div>
                                                    <div class="text-[0.625rem] opacity-50 truncate">{{ $cust['addres'] }}</div>
                                                </div>
                                                <button type="button" wire:click="addCustomerToCart('{{ $cust['custno'] }}', '{{ $cust['distributor_code'] }}')" class="btn btn-xs btn-primary btn-square opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <x-heroicon-s-plus class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif(strlen($searchCustomer) >= 3)
                                <div class="absolute z-[100] w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-3 text-center text-xs text-base-content/50">
                                    <span wire:loading.remove wire:target="searchCustomer">Tidak ditemukan customer yang sesuai.</span>
                                    <span wire:loading wire:target="searchCustomer">Mencari...</span>
                                </div>
                            @endif
                        </div>

                        @error('selectedCustomers') 
                            <div class="alert alert-error bg-error/10 text-error text-xs p-2 rounded-lg mt-4 border-none">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                {{ $message }}
                            </div>
                        @enderror
                    </form>
                    
                    {{-- Rekomendasi Toko --}}
                    <div class="mt-6 flex-1 flex flex-col">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary mb-2 flex items-center gap-1">
                            <x-heroicon-s-sparkles class="w-4 h-4" /> Rekomendasi Toko (< 1km)
                        </h4>
                        
                        <div class="flex-1 min-h-[200px] bg-base-200/50 rounded-xl border border-base-300 overflow-hidden flex flex-col">
                            <div class="overflow-y-auto flex-1 p-2">
                                @if(count($recommendedStores) > 0)
                                    <div class="space-y-2">
                                        @foreach($recommendedStores as $rec)
                                            <div class="bg-base-100 border border-base-300 rounded-lg p-2.5 shadow-sm hover:border-primary/50 transition-colors group flex items-start gap-2 cursor-pointer" onclick="focusFormMap({{ $rec['latitude'] ?? 0 }}, {{ $rec['longitude'] ?? 0 }}, '{{ $rec['custno'] }}')">
                                                <div class="flex-1 overflow-hidden">
                                                    <div class="font-bold text-xs">{{ $rec['custno'] }} - {{ $rec['custname'] }}</div>
                                                    <div class="text-[0.6rem] text-base-content/70 flex flex-wrap gap-x-2 mt-0.5 mb-1">
                                                        <span><x-heroicon-s-building-storefront class="w-2.5 h-2.5 inline"/> {{ $rec['distributor_code'] }}</span>
                                                        <span><x-heroicon-s-map-pin class="w-2.5 h-2.5 inline"/> {{ $rec['nama_area'] }}</span>
                                                    </div>
                                                    @if(!empty($rec['pilar']))
                                                        <span class="badge badge-outline badge-sm text-[0.6rem] border-gray-300 text-gray-600 font-bold py-0 h-4">Pilar: {{ $rec['pilar'] }}</span>
                                                    @endif
                                                </div>
                                                <button type="button" wire:click="addRecommendedStore('{{ $rec['custno'] }}', '{{ $rec['distributor_code'] }}')" class="btn btn-xs btn-primary btn-outline normal-case shrink-0 opacity-70 group-hover:opacity-100 transition-opacity">
                                                    Tambah
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="h-full flex flex-col items-center justify-center text-base-content/40 space-y-2 p-4 text-center">
                                        <x-heroicon-o-map class="w-8 h-8 opacity-50" />
                                        <p class="text-xs">Pilih toko di cart untuk melihat rekomendasi toko terdekat (< 1km).</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tengah: Daftar Customer (Cart) --}}
                <div class="w-full lg:w-[25%] border-r border-base-300 flex flex-col overflow-y-auto bg-base-50/50">
                    <div class="p-4 border-b border-base-300 flex justify-between items-center bg-base-100 sticky top-0 z-10">
                        <h4 class="font-bold text-sm uppercase tracking-wide">Daftar Customer Terpilih</h4>
                        <span class="badge badge-primary">{{ count($selectedCustomers) }} Toko</span>
                    </div>
                    
                    <div class="p-4 flex-1">
                        @if(count($selectedCustomers) == 0)
                            <div class="h-full flex flex-col items-center justify-center text-base-content/30 space-y-3">
                                <x-heroicon-o-shopping-bag class="w-16 h-16" />
                                <p class="text-sm">Belum ada customer yang dipilih.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($selectedCustomers as $idx => $cartItem)
                                    <div class="bg-base-100 border border-base-300 rounded-xl p-3 shadow-sm flex items-start gap-3 relative cursor-pointer hover:border-primary/50 transition-colors" onclick="focusFormMap({{ $cartItem['latitude'] ?? 0 }}, {{ $cartItem['longitude'] ?? 0 }}, '{{ $cartItem['custno'] }}')">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ $idx + 1 }}
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <div class="font-bold text-sm">{{ $cartItem['custno'] }} - {{ $cartItem['custname'] }}</div>
                                            <div class="text-xs text-base-content/70 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                                                <span class="flex items-center gap-1"><x-heroicon-s-building-storefront class="w-3 h-3"/> {{ $cartItem['distributor_code'] }}</span>
                                                <span class="flex items-center gap-1"><x-heroicon-s-map-pin class="w-3 h-3"/> {{ $cartItem['nama_area'] }}, {{ $cartItem['nama_region'] }}</span>
                                            </div>
                                            <div class="text-[0.625rem] text-base-content/50 mt-1 truncate">{{ $cartItem['addres'] }}</div>
                                        </div>
                                        <button type="button" wire:click="removeCustomerFromCart('{{ $cartItem['custno'] }}', '{{ $cartItem['distributor_code'] }}')" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error hover:text-white shrink-0">
                                            <x-heroicon-s-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Kanan: Map --}}
                <div class="w-full lg:w-[50%] min-h-[400px] lg:min-h-0 p-0 flex flex-col relative bg-base-200">
                    <div class="absolute inset-0 z-10" wire:ignore>
                        <div id="form-map" style="width: 100%; height: 100%; min-height: 400px;"></div>
                    </div>
                    <div class="absolute top-2 left-2 z-20 bg-base-100/90 backdrop-blur rounded-lg shadow-sm border border-base-300 p-2 text-[0.65rem] font-semibold space-y-1 w-max pointer-events-none">
                        <div class="font-bold mb-1 border-b border-base-300 pb-1">Terpilih di Cart</div>
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#22c55e]"></div> Customer Terpilih</div>
                        <div class="font-bold mt-2 mb-1 border-b border-base-300 pb-1">Rekomendasi (< 1km)</div>
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></div> Pilar 1 (RWO)</div>
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#8b5cf6]"></div> Pilar 2 (PNR)</div>
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#f97316]"></div> Pilar 3 (NGVO)</div>
                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#6b7280]"></div> Lainnya</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50 mt-auto">
                <button type="button" @click="if(confirm('Apakah Anda yakin ingin membatalkan? Semua data yang belum disimpan akan hilang.')) open = false" class="btn btn-error text-white rounded-xl normal-case hover:opacity-80 border-none">Batal</button>
                <button wire:click="save" type="button" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                    <span wire:loading.remove wire:target="save">Simpan Daftar ({{ count($selectedCustomers) }})</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>
</div>
