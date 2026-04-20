<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Role Sistem</h2>
        <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">
            + Tambah Role
        </button>
    </div>

    <!-- Alert Sukses -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Alert Error (Jika mencoba menghapus role core) -->
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabel Role -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden w-full lg:w-2/3">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Role (Kode)</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total User</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-5 py-4 text-sm text-gray-900">{{ $role->id }}</td>
                    <td class="px-5 py-4 text-sm font-bold text-blue-600">
                        {{ strtoupper($role->name) }}
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">
                        {{-- Mengambil jumlah user yang memiliki role ini --}}
                        {{ $role->users()->count() }} Akun
                    </td>
                    <td class="px-5 py-4 text-sm text-right">
                        @if($role->name !== 'national')
                            <button wire:click="delete({{ $role->id }})" class="text-red-600 hover:text-red-900 font-semibold" onclick="return confirm('Peringatan: Menghapus role ini mungkin akan berdampak pada hak akses user yang memilikinya. Lanjutkan?')">Hapus</button>
                        @else
                            <span class="text-gray-400 italic text-xs">Core Role</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-4 text-center text-gray-500 text-sm">Belum ada role yang ditambahkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 bg-white border-t">
            {{ $roles->links() }}
        </div>
    </div>

    <!-- Modal Tambah Role -->
    <div x-data="{ open: @entangle('isModalOpen') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Overlay -->
            <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <!-- Modal Body -->
            <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md w-full p-6">
                <h3 class="text-lg leading-6 font-bold text-gray-900 mb-2">Buat Role Baru</h3>
                <p class="text-xs text-gray-500 mb-4">Role ini nantinya bisa dipilih saat Anda membuat user baru.</p>
                
                <form wire:submit="store">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Role</label>
                        <input type="text" wire:model="name" placeholder="Misal: admin_area" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-[10px] text-gray-400 mt-1">* Sebaiknya gunakan huruf kecil tanpa spasi (gunakan underscore jika perlu).</p>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="open = false" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700">
                            Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>