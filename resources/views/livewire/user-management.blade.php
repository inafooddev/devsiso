<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">
            + Tambah User
        </button>
    </div>

    <!-- Alert Sukses -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Tabel User -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User ID / Nama</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cakupan Wilayah</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-5 py-4 text-sm">
                        <p class="text-gray-900 font-bold">{{ $user->userid }}</p>
                        <p class="text-gray-600 font-medium">{{ $user->name }}</p>
                    </td>
                    <td class="px-5 py-4 text-sm">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            {{ $user->getRoleNames()->first() ?? 'Belum ada role' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">
                        @if(is_array($user->region_code) && count($user->region_code) > 0) 
                            Region: {{ implode(', ', $user->region_code) }}
                        @elseif(is_string($user->region_code) && !empty($user->region_code))
                            Region: {{ $user->region_code }}
                        @else 
                            Nasional (Semua) 
                        @endif
                    </td>
                    <td class="px-5 py-4 text-sm text-right">
                        <button wire:click="delete({{ $user->id }})" class="text-red-600 hover:text-red-900 font-semibold" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-4 bg-white border-t">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Tambah User -->
    <div x-data="{ open: @entangle('isModalOpen') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Background Overlay -->
            <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <!-- Modal Panel -->
            <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Tambah User Baru</h3>
                
                <form wire:submit="store">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">User ID</label>
                            <input type="text" wire:model="userid" placeholder="Misal: admin01" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                            @error('userid') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input type="text" wire:model="name" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model="email" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" wire:model="password" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <h4 class="text-sm font-bold text-gray-700 mb-2">Pengaturan Akses & Wilayah</h4>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700">Role Sistem</label>
                            <select wire:model="role" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ strtoupper($r->name) }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Checkbox Pilihan Region dari Database -->
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cakupan Region</label>
                            
                            <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2 bg-gray-50">
                                @forelse($availableRegions as $region)
                                    <label class="flex items-center w-full cursor-pointer hover:bg-gray-100 p-1 rounded">
                                        <input type="checkbox" wire:model="region_code" value="{{ $region->region_code }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">{{ $region->region_code }} - {{ $region->region_name }}</span>
                                    </label>
                                @empty
                                    <span class="text-xs text-gray-500">Data region tidak ditemukan di Master Distributor.</span>
                                @endforelse
                            </div>

                            <p class="text-xs text-gray-500 mt-2">* <b>Kosongkan</b> (jangan centang apapun) jika user ini adalah level Nasional / Pusat.</p>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 flex justify-end gap-2">
                        <button type="button" @click="open = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">
                            Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>