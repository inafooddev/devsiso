<div class="max-w-md w-full animate-fade-in">
        <!-- Card Container -->
        <div class="glass-card p-10 rounded-3xl shadow-2xl overflow-hidden relative">
            
            <!-- Decorative Element -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl"></div>

            <div class="text-center mb-10 relative">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg shadow-blue-200 transform -rotate-6">
                    <span class="text-white text-2xl font-black italic">S</span>
                </div>
                <h2 class="text-4xl font-black text-gray-800 tracking-tight italic">
                    SISO<span class="text-blue-600">.</span>
                </h2>
                <p class="text-gray-500 mt-2 font-medium">Selamat datang kembali!</p>
            </div>

            <form wire:submit="authenticate" class="space-y-6 relative">
                <!-- User ID Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">User ID</label>
                    <div class="relative group">
                        <input type="text" wire:model="userid" 
                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all duration-300 input-focus-effect group-hover:border-blue-300"
                            placeholder="Masukkan User ID" required autofocus>
                    </div>
                    @error('userid') 
                        <span class="text-red-500 text-xs mt-2 ml-1 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">Password</label>
                    <div class="relative group">
                        <input type="password" wire:model="password" 
                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all duration-300 input-focus-effect group-hover:border-blue-300"
                            placeholder="••••••••" required>
                    </div>
                    @error('password') 
                        <span class="text-red-500 text-xs mt-2 ml-1 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <!-- Helpers -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" id="remember" wire:model="remember" class="sr-only">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-md transition-all group-hover:border-blue-500 flex items-center justify-center bg-white">
                                <svg class="w-3.5 h-3.5 text-blue-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="check-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                        <span class="ml-2 text-sm text-gray-600 font-medium select-none">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full relative group overflow-hidden bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-blue-200 hover:shadow-blue-400 transform transition-all active:scale-95 duration-200">
                    <span class="relative z-10 flex items-center justify-center">
                        <span wire:loading.remove wire:target="authenticate">LOG IN</span>
                        <span wire:loading wire:target="authenticate" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            MEMPROSES...
                        </span>
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-700 to-indigo-600 transition-transform duration-300 transform translate-x-full group-hover:translate-x-0"></div>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">
                    &copy; 2026 SISO Team.
                </p>
            </div>
        </div>
    </div>