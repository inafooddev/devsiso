<div class="w-full"> <!-- BUNGKUS UTAMA LIVEWIRE (Ditambah w-full agar tidak menyusut) -->
    <div class="w-full max-w-4xl mx-auto flex flex-col lg:flex-row bg-white shadow-2xl rounded-3xl overflow-hidden min-h-[550px]">
        
        <!-- Sisi Kiri: Form Register -->
        <div class="w-full lg:w-1/2 p-8 sm:p-10 lg:px-12 bg-white flex flex-col justify-center relative z-20">
            <div class="mb-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Sign up</h2>
            </div>

            <form wire:submit.prevent="register" class="mt-2">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <x-input-text wire:model="userid" label="User ID" />
                    <x-input-text wire:model="name" label="Name" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <x-input-text wire:model="email" type="email" label="Enter your email" />
                    
                    <!-- Pilihan Role Spatie (Modern Underlined Select) -->
                    <div class="mb-6 relative mt-4">
                        <select wire:model="role" id="role" class="peer block w-full py-2 px-1 bg-transparent border-0 border-b-2 border-gray-200 focus:ring-0 focus:border-purple-500 transition-colors outline-none text-gray-700 text-sm appearance-none cursor-pointer">
                            <option value="" disabled selected>Select Role</option>
                            @foreach($roles as $roleItem)
                                <option value="{{ $roleItem->name }}">{{ ucfirst($roleItem->name) }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute right-1 top-2 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <label for="role" class="absolute left-1 -top-3.5 text-gray-400 text-xs transition-all peer-focus:text-purple-500">
                            Role
                        </label>
                        @error('role') 
                            <span class="text-xs text-red-500 mt-1 flex items-center absolute -bottom-5">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <x-input-text wire:model="password" type="password" label="Password" />
                    <x-input-text wire:model="password_confirmation" type="password" label="Confirm Password" />
                </div>

                <!-- Pilihan Region Minimalis -->
                <div class="mt-4 mb-8 pt-2">
                    <div class="grid grid-cols-2 gap-3 max-h-24 overflow-y-auto custom-scrollbar pr-2">
                        @foreach($availableRegions as $region)
                            <label class="flex items-start space-x-2 cursor-pointer group">
                                <!-- shrink-0 mencegah kotak checkbox gepeng -->
                                <input type="checkbox" wire:model="region_code" value="{{ $region->region_code }}" class="form-checkbox h-4 w-4 mt-0.5 text-purple-500 border-gray-300 rounded focus:ring-purple-500 transition duration-150 ease-in-out shrink-0">
                                <!-- leading-tight agar teks panjang turun ke bawah dengan rapi -->
                                <span class="text-xs font-medium text-gray-500 group-hover:text-gray-800 transition-colors leading-tight">{{ $region->region_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('region_code') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6 flex flex-col items-center">
                    <button type="submit" class="w-full sm:w-auto min-w-[220px] flex justify-center items-center py-2.5 px-6 rounded-full shadow-lg shadow-purple-500/30 text-sm font-semibold text-white bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all transform hover:-translate-y-0.5">
                        <span wire:loading.remove wire:target="register">Register</span>
                        
                        <!-- Animasi Loading -->
                        <span wire:loading wire:target="register" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>

                    <p class="mt-5 text-xs text-gray-500">
                        Already a member? 
                        <a href="{{ route('login') }}" class="font-semibold text-purple-500 hover:text-purple-600 transition-colors">
                            Log in
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Sisi Kanan: Panel Gradien Biru-Ungu & Orbs 3D -->
        <div class="hidden lg:block lg:w-1/2 bg-gradient-to-br from-purple-500 via-indigo-500 to-blue-500 relative overflow-hidden">
            
            <!-- Gelombang Abstrak Latar Belakang -->
            <div class="absolute inset-0 opacity-20 mix-blend-overlay">
                <svg viewBox="0 0 400 400" xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" class="w-full h-full transform scale-150 -translate-y-10">
                    <path fill="#ffffff" d="M42.7,-53.4C55.4,-44.6,65.9,-30.9,70.5,-15.5C75.1,-0.1,73.8,17,65.1,30.3C56.4,43.6,40.3,53,24.1,58.3C7.9,63.6,-8.4,64.8,-23.4,59.8C-38.4,54.8,-52.1,43.6,-61.7,29.1C-71.3,14.6,-76.8,-3.2,-72.1,-18.2C-67.4,-33.2,-52.5,-45.4,-38.2,-53.8C-23.9,-62.2,-11.9,-66.8,1.3,-68.4C14.6,-70,29.2,-68.6,42.7,-53.4Z" transform="translate(200 200)" />
                </svg>
            </div>

            <!-- Orb 1: Biru/Cyan Kecil (Atas Tengah) -->
            <div class="absolute top-1/3 left-1/4 w-20 h-20 rounded-full bg-gradient-to-tr from-blue-400 to-cyan-300 shadow-[0_0_30px_rgba(56,189,248,0.5)] opacity-90"></div>
            
            <!-- Orb 2: Ungu Besar (Bawah Kanan) -->
            <div class="absolute bottom-1/4 right-1/4 w-32 h-32 rounded-full bg-gradient-to-tr from-purple-400 to-fuchsia-400 shadow-[0_0_40px_rgba(192,132,252,0.6)] opacity-90"></div>
            
            <!-- Shadow dekoratif di sudut -->
            <div class="absolute -bottom-20 -left-20 w-64 h-64 rounded-full bg-blue-600 opacity-40 blur-3xl"></div>
            <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full bg-purple-600 opacity-40 blur-3xl"></div>
        </div>
    </div>

    <!-- CSS Khusus Scrollbar -->
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</div> <!-- Akhir BUNGKUS UTAMA -->