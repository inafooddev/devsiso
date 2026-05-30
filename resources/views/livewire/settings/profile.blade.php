<div>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-base-content">Profil Saya</h2>
            <p class="text-sm text-base-content/70 mt-1">Kelola informasi profil dan keamanan akun Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Info Profil -->
            <div class="md:col-span-1">
                <div class="bg-base-100 rounded-xl shadow-md border border-base-300 p-6 flex flex-col items-center text-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6272a4&color=fff&size=128" alt="Avatar" class="w-32 h-32 rounded-full mb-4 ring-4 ring-primary/20">
                    <h3 class="text-lg font-bold text-base-content">{{ $user->name }}</h3>
                    <p class="text-sm text-base-content/60">{{ $user->userid ?? $user->email }}</p>
                    <div class="mt-4">
                        <x-ui.badge variant="primary" outline="true">
                            {{ $user->getRoleNames()->first() ?? 'Belum ada role' }}
                        </x-ui.badge>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Form Edit -->
            <div class="md:col-span-2 space-y-6">
                <!-- Form Update Profil -->
                <div class="bg-base-100 rounded-xl shadow-md border border-base-300 p-6">
                    <h3 class="text-lg font-bold text-base-content mb-4 border-b border-base-300 pb-2">Informasi Profil</h3>
                    
                    @if (session()->has('message'))
                        <div class="mb-4">
                            <x-ui.notif type="success" dismissible="true">
                                {{ session('message') }}
                            </x-ui.notif>
                        </div>
                    @endif

                    <form wire:submit.prevent="updateProfile">
                        <div class="space-y-4">
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
                        </div>
                        <div class="mt-6 flex justify-end">
                            <x-ui.button variant="primary" type="submit" icon="check">
                                Simpan Perubahan
                            </x-ui.button>
                        </div>
                    </form>
                </div>

                <!-- Form Update Password -->
                <div class="bg-base-100 rounded-xl shadow-md border border-base-300 p-6">
                    <h3 class="text-lg font-bold text-base-content mb-4 border-b border-base-300 pb-2">Ubah Password</h3>
                    
                    @if (session()->has('message_password'))
                        <div class="mb-4">
                            <x-ui.notif type="success" dismissible="true">
                                {{ session('message_password') }}
                            </x-ui.notif>
                        </div>
                    @endif

                    <form wire:submit.prevent="updatePassword">
                        <div class="space-y-4">
                            <div>
                                <label class="label"><span class="label-text font-medium">Password Saat Ini</span></label>
                                <input type="password" wire:model="current_password" class="input input-bordered w-full">
                                @error('current_password') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label"><span class="label-text font-medium">Password Baru</span></label>
                                <input type="password" wire:model="new_password" class="input input-bordered w-full">
                                @error('new_password') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label"><span class="label-text font-medium">Konfirmasi Password Baru</span></label>
                                <input type="password" wire:model="new_password_confirmation" class="input input-bordered w-full">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <x-ui.button variant="primary" type="submit" outline="true" icon="key">
                                Perbarui Password
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
