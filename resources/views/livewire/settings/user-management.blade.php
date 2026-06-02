<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-base-content">Manajemen Pengguna</h2>
                <p class="text-sm text-base-content/70 mt-1">Kelola data pengguna, role, dan cakupan wilayah.</p>
            </div>
            <div>
                <x-ui.button variant="primary" icon="plus" wire:click="create">
                    Tambah User
                </x-ui.button>
            </div>
        </div>

        {{-- Alert Sukses --}}
        @if (session()->has('message'))
            <div class="mb-6">
                <x-ui.notif type="success" dismissible="true">
                    {{ session('message') }}
                </x-ui.notif>
            </div>
        @endif

        {{-- Tabel User --}}
        <x-ui.table empty="Belum ada data user" emptyIcon="users">
            <x-slot:head>
                <tr>
                    <th>User ID / Nama</th>
                    <th>Role</th>
                    <th>Cakupan Wilayah</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </x-slot:head>

            @foreach($users as $user)
            <tr>
                <td>
                    <p class="font-bold text-base-content">{{ $user->userid }}</p>
                    <p class="text-sm text-base-content/70">{{ $user->name }}</p>
                </td>
                <td>
                    <x-ui.badge variant="primary" outline="true">
                        {{ $user->getRoleNames()->first() ?? 'Belum ada role' }}
                    </x-ui.badge>
                </td>
                <td class="text-base-content/70 text-sm">
                    {{-- Badge Level --}}
                    @php $lvl = $user->getAccessLevel(); @endphp

                    @if($lvl === 'supervisor')
                        <span class="badge badge-sm badge-error font-bold mr-1">👤 SUPERVISOR</span>
                        <span class="text-base-content">{{ $user->supervisor_code }}</span>
                    @elseif($lvl === 'area')
                        <span class="badge badge-sm badge-warning font-bold mr-1">📍 AREA</span>
                        <span class="text-base-content">
                            {{ is_array($user->area_code) ? implode(', ', $user->area_code) : $user->area_code }}
                        </span>
                    @elseif($lvl === 'region')
                        <span class="badge badge-sm badge-info font-bold mr-1">🗺️ REGION</span>
                        <span class="text-base-content">
                            {{ is_array($user->region_code) ? implode(', ', $user->region_code) : $user->region_code }}
                        </span>
                    @else
                        <span class="badge badge-sm badge-success font-bold">🌐 NASIONAL</span>
                    @endif

                    {{-- Grup Akses --}}
                    @if($user->access_group_id)
                        <br><span class="badge badge-sm badge-outline badge-success mt-1">Grup: {{ $user->accessGroup?->name ?? 'ID:'.$user->access_group_id }}</span>
                    @else
                        <br><span class="badge badge-sm badge-outline badge-warning mt-1">Belum ada grup</span>
                    @endif
                </td>
                <td class="text-right space-x-1">
                    <x-ui.button variant="primary" size="sm" outline="true" icon="pencil" wire:click="edit({{ $user->id }})">
                        Edit
                    </x-ui.button>
                    <x-ui.button variant="error" size="sm" outline="true" icon="trash" wire:click="delete({{ $user->id }})" onclick="return confirm('Yakin ingin menghapus user ini?')">
                        Hapus
                    </x-ui.button>
                </td>
            </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">
            @if(method_exists($users, 'links'))
                {{ $users->links() }}
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL TAMBAH / EDIT USER                                           --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <x-ui.modal id="modal-tambah" title="{{ $userId ? 'Edit User' : 'Tambah User Baru' }}" icon="{{ $userId ? 'pencil' : 'user-plus' }}" size="lg" :dismissible="false" :open="$isModalOpen" wire:close="$set('isModalOpen', false)">
        <form wire:submit.prevent="store" id="form-tambah-user">

            {{-- ── BAGIAN 1: Info Dasar ─────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="label"><span class="label-text font-medium">User ID</span></label>
                    <input type="text" wire:model="userid" placeholder="Misal: admin01" class="input input-bordered w-full">
                    @error('userid') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="label"><span class="label-text font-medium">Nama Lengkap</span></label>
                    <input type="text" wire:model="name" class="input input-bordered w-full">
                    @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="label"><span class="label-text font-medium">Email</span></label>
                    <input type="email" wire:model="email" class="input input-bordered w-full">
                    @error('email') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="label"><span class="label-text font-medium">Password</span></label>
                    <input type="password" wire:model="password" class="input input-bordered w-full">
                    @if($userId)
                        <span class="text-xs text-base-content/50 block mt-1">* Kosongkan jika tidak ingin mengubah password.</span>
                    @endif
                    @error('password') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- ── BAGIAN 2: Role & Grup ────────────────────────────────────── --}}
            <div class="border-t border-base-300 pt-4 mb-4">
                <h4 class="text-sm font-bold text-base-content mb-3">Role & Grup Akses</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="label p-0"><span class="label-text font-medium">Role Sistem</span></label>
                            <button type="button" wire:click="openRoleModal" class="text-xs text-primary hover:underline font-semibold flex items-center">
                                <x-heroicon-s-plus class="w-3 h-3 mr-1" /> Buat Role Baru
                            </button>
                        </div>
                        <select wire:model="role" class="select select-bordered w-full">
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ strtoupper($r->name) }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="label p-0 mb-1"><span class="label-text font-medium">Grup Akses (View Sidebar) <span class="text-error">*</span></span></label>
                        <select wire:model.live="access_group_id" class="select select-bordered w-full">
                            <option value="">-- Pilih Akses Group --</option>
                            @foreach($accessGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('access_group_id') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- ── BAGIAN 3: Level Akses Wilayah (BARU) ────────────────────── --}}
            <div class="border-t border-base-300 pt-4">
                <h4 class="text-sm font-bold text-base-content mb-1">Level Akses Wilayah</h4>
                <p class="text-xs text-base-content/50 mb-3">Tentukan cakupan data yang bisa dilihat oleh user ini.</p>

                {{-- Radio Pilih Level --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">

                    {{-- Nasional --}}
                    <label class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ $accessLevel === 'nasional' ? 'border-success bg-success/10' : 'border-base-300 hover:border-base-400' }}">
                        <input type="radio" wire:model.live="accessLevel" value="nasional" class="radio radio-success radio-sm">
                        <span class="text-lg">🌐</span>
                        <span class="text-xs font-bold text-center">Nasional</span>
                        <span class="text-[10px] text-base-content/50 text-center">Semua data</span>
                    </label>

                    {{-- Region --}}
                    <label class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ $accessLevel === 'region' ? 'border-info bg-info/10' : 'border-base-300 hover:border-base-400' }}">
                        <input type="radio" wire:model.live="accessLevel" value="region" class="radio radio-info radio-sm">
                        <span class="text-lg">🗺️</span>
                        <span class="text-xs font-bold text-center">Per-Region</span>
                        <span class="text-[10px] text-base-content/50 text-center">Bisa multi</span>
                    </label>

                    {{-- Area --}}
                    <label class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ $accessLevel === 'area' ? 'border-warning bg-warning/10' : 'border-base-300 hover:border-base-400' }}">
                        <input type="radio" wire:model.live="accessLevel" value="area" class="radio radio-warning radio-sm">
                        <span class="text-lg">📍</span>
                        <span class="text-xs font-bold text-center">Per-Area</span>
                        <span class="text-[10px] text-base-content/50 text-center">Bisa multi</span>
                    </label>

                    {{-- Supervisor --}}
                    <label class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all
                        {{ $accessLevel === 'supervisor' ? 'border-error bg-error/10' : 'border-base-300 hover:border-base-400' }}">
                        <input type="radio" wire:model.live="accessLevel" value="supervisor" class="radio radio-error radio-sm">
                        <span class="text-lg">👤</span>
                        <span class="text-xs font-bold text-center">Per-Supervisor</span>
                        <span class="text-[10px] text-base-content/50 text-center">1 akun = 1 SPV</span>
                    </label>

                </div>

                {{-- ── Section: Nasional ──────────────────────────────────── --}}
                @if($accessLevel === 'nasional')
                    <div class="alert alert-success border border-success/30 py-3">
                        <x-heroicon-s-check-circle class="w-5 h-5" />
                        <span class="text-sm">User ini dapat melihat <strong>semua data</strong> tanpa batasan wilayah.</span>
                    </div>
                @endif

                {{-- ── Section: Region ────────────────────────────────────── --}}
                @if($accessLevel === 'region')
                    <div>
                        <label class="label p-0 mb-2">
                            <span class="label-text font-medium">Pilih Region yang Diizinkan</span>
                            <span class="label-text-alt text-base-content/50">Bisa pilih lebih dari satu</span>
                        </label>
                        <div class="max-h-44 overflow-y-auto border border-base-300 rounded-xl p-3 space-y-1 bg-base-200/50">
                            @forelse($availableRegions as $region)
                                <label class="flex items-center w-full cursor-pointer hover:bg-base-300/60 p-2 rounded-lg transition-colors">
                                    <input type="checkbox" wire:model="region_code" value="{{ $region->region_code }}" class="checkbox checkbox-info checkbox-sm">
                                    <span class="ml-3 text-sm text-base-content">
                                        <span class="font-medium">{{ $region->region_code }}</span>
                                        <span class="text-base-content/60"> — {{ $region->region_name }}</span>
                                    </span>
                                </label>
                            @empty
                                <span class="text-xs text-base-content/50 p-2 block">Data region tidak ditemukan.</span>
                            @endforelse
                        </div>
                        @if(count($region_code) > 0)
                            <p class="text-xs text-info mt-2 font-medium">✓ {{ count($region_code) }} region dipilih: {{ implode(', ', $region_code) }}</p>
                        @else
                            <p class="text-xs text-warning mt-2">⚠ Belum ada region yang dipilih.</p>
                        @endif
                    </div>
                @endif

                {{-- ── Section: Area ──────────────────────────────────────── --}}
                @if($accessLevel === 'area')
                    <div>
                        {{-- Filter Region --}}
                        <label class="label p-0 mb-1"><span class="label-text font-medium">Filter berdasarkan Region <span class="text-base-content/40 font-normal">(opsional)</span></span></label>
                        <select wire:model.live="filterRegionForArea" class="select select-bordered select-sm w-full mb-3">
                            <option value="">-- Tampilkan semua area --</option>
                            @foreach($regionsForFilter as $region)
                                <option value="{{ $region->region_code }}">{{ $region->region_code }} — {{ $region->region_name }}</option>
                            @endforeach
                        </select>

                        {{-- Daftar Area --}}
                        <label class="label p-0 mb-2">
                            <span class="label-text font-medium">Pilih Area yang Diizinkan</span>
                            <span class="label-text-alt text-base-content/50">Bisa pilih lebih dari satu</span>
                        </label>
                        <div class="max-h-44 overflow-y-auto border border-base-300 rounded-xl p-3 space-y-1 bg-base-200/50">
                            @forelse($availableAreas as $area)
                                <label class="flex items-center w-full cursor-pointer hover:bg-base-300/60 p-2 rounded-lg transition-colors">
                                    <input type="checkbox" wire:model="area_code" value="{{ $area->area_code }}" class="checkbox checkbox-warning checkbox-sm">
                                    <span class="ml-3 text-sm text-base-content">
                                        <span class="font-medium">{{ $area->area_code }}</span>
                                        <span class="text-base-content/60"> — {{ $area->area_name }}</span>
                                        @if($area->region_code)
                                            <span class="badge badge-xs badge-outline ml-1">{{ $area->region_code }}</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <span class="text-xs text-base-content/50 p-2 block">Tidak ada area ditemukan.</span>
                            @endforelse
                        </div>
                        @if(count($area_code) > 0)
                            <p class="text-xs text-warning mt-2 font-medium">✓ {{ count($area_code) }} area dipilih: {{ implode(', ', $area_code) }}</p>
                        @else
                            <p class="text-xs text-warning mt-2">⚠ Belum ada area yang dipilih.</p>
                        @endif
                    </div>
                @endif

                {{-- ── Section: Supervisor ────────────────────────────────── --}}
                @if($accessLevel === 'supervisor')
                    <div>
                        {{-- Cascading: Region → Area → Supervisor --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="label p-0 mb-1"><span class="label-text font-medium">Filter Region</span></label>
                                <select wire:model.live="filterRegionForSpv" class="select select-bordered select-sm w-full">
                                    <option value="">-- Pilih Region --</option>
                                    @foreach($regionsForFilter as $region)
                                        <option value="{{ $region->region_code }}">{{ $region->region_code }} — {{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="label p-0 mb-1"><span class="label-text font-medium">Filter Area</span></label>
                                <select wire:model.live="filterAreaForSpv" class="select select-bordered select-sm w-full" @if(empty($filterRegionForSpv)) disabled @endif>
                                    <option value="">-- Pilih Area dulu --</option>
                                    @foreach($availableAreas as $area)
                                        <option value="{{ $area->area_code }}">{{ $area->area_code }} — {{ $area->area_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Daftar Supervisor --}}
                        <label class="label p-0 mb-2">
                            <span class="label-text font-medium">Pilih Supervisor <span class="text-error">*</span></span>
                            <span class="label-text-alt text-base-content/50">1 user = 1 supervisor</span>
                        </label>
                        <div class="max-h-44 overflow-y-auto border border-base-300 rounded-xl p-3 space-y-1 bg-base-200/50">
                            @forelse($availableSupervisors as $spv)
                                <label class="flex items-center w-full cursor-pointer hover:bg-base-300/60 p-2 rounded-lg transition-colors">
                                    <input type="radio" wire:model="supervisor_code" value="{{ $spv->supervisor_code }}" class="radio radio-error radio-sm">
                                    <span class="ml-3 text-sm text-base-content">
                                        <span class="font-medium">{{ $spv->supervisor_code }}</span>
                                        <span class="text-base-content/60"> — {{ $spv->supervisor_name }}</span>
                                        @if($spv->area_code)
                                            <span class="badge badge-xs badge-outline ml-1">{{ $spv->area_code }}</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <div class="text-center py-4">
                                    @if(empty($filterAreaForSpv))
                                        <p class="text-xs text-base-content/50">Pilih Region & Area di atas untuk menampilkan daftar supervisor.</p>
                                    @else
                                        <p class="text-xs text-base-content/50">Tidak ada supervisor di area ini.</p>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                        @error('supervisor_code')
                            <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                        @if(!empty($supervisor_code))
                            <p class="text-xs text-error mt-2 font-medium">✓ Supervisor dipilih: {{ $supervisor_code }}</p>
                        @endif
                    </div>
                @endif

            </div>
            {{-- /Bagian 3 --}}

        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-tambah-user').requestSubmit()">
                {{ $userId ? 'Update User' : 'Simpan User' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL TAMBAH ROLE                                                  --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <x-ui.modal id="modal-tambah-role" title="Buat Role Baru" icon="shield-check" size="md" :dismissible="false" :open="$isRoleModalOpen" wire:close="$set('isRoleModalOpen', false)">
        <p class="text-xs text-base-content/60 mb-4">Role ini akan otomatis tersedia di pilihan role sistem.</p>

        <form wire:submit.prevent="storeRole" id="form-tambah-role">
            <div class="mb-4">
                <label class="label"><span class="label-text font-medium">Nama Role</span></label>
                <input type="text" wire:model="newRoleName" placeholder="Misal: admin_area" class="input input-bordered w-full">
                @error('newRoleName') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="text-[10px] text-base-content/50 mt-1">* Gunakan huruf kecil tanpa spasi (gunakan underscore jika perlu).</p>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isRoleModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-tambah-role').requestSubmit()">
                Simpan Role
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

</div>