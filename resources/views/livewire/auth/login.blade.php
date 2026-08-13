<div class="max-w-md w-full animate-fade-in z-10 relative">
    <!-- Main Card Container -->
    <div class="bg-base-100 p-10 rounded-[2.5rem] shadow-2xl shadow-black/40 ring-1 ring-white/5 overflow-hidden relative">
        
        <!-- Decorative Glow Effects -->
        <div class="absolute -top-20 -right-20 w-48 h-48 bg-primary/20 rounded-full blur-[80px]"></div>
        <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-blue-500/20 rounded-full blur-[80px]"></div>

        <div class="text-center mb-10 relative z-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/5 ring-1 ring-white/10 backdrop-blur-md rounded-2xl mb-5 shadow-lg shadow-black/20">
                <x-heroicon-s-shield-check class="w-8 h-8 text-white" />
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">
                SISO<span class="text-primary">.</span>
            </h2>
            <p class="text-base-content/60 mt-2 text-sm">Welcome back! Please enter your details.</p>
        </div>

        <form wire:submit.prevent="authenticate"
            x-data="{
                secs: 0,
                timer: null,
                init() {

                    this.secs = $wire.lockoutSeconds;
                    if (this.secs > 0) this.startCountdown();
                    $wire.$watch('lockoutSeconds', (val) => {
                        if (val > 0) {
                            this.secs = val;
                            this.startCountdown();
                        }
                    });
                },
                startCountdown() {
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (this.secs > 0) {
                            this.secs--;
                        } else {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                    }, 1000);
                }
            }"
            class="space-y-6 relative z-10">

            <!-- User ID Field -->
            <div class="form-control">
                <label class="label pb-1"><span class="label-text text-base-content/60 font-medium">User ID</span></label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <x-heroicon-o-user class="w-5 h-5 text-base-content/40 group-focus-within:text-primary transition-colors" />
                    </div>
                    <input type="text" wire:model="userid" 
                        class="input w-full pl-11 bg-base-200/50 border border-white/5 rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-transparent outline-none transition-all duration-300 text-base-content placeholder-base-content/30"
                        placeholder="Enter your User ID" required autofocus>
                </div>
                @error('userid') 
                    <span class="text-error text-xs mt-2 flex items-center">
                        <x-heroicon-s-exclamation-circle class="w-3 h-3 mr-1" />
                        {{ $message }}
                    </span> 
                @enderror

                {{-- Countdown Rate Limit --}}
                <div x-show="secs > 0" x-cloak class="mt-2 flex items-center gap-1 text-warning text-xs font-medium">
                    <x-heroicon-s-clock class="w-3 h-3 shrink-0" />
                    <span>Terlalu banyak percobaan. Coba lagi dalam</span>
                    <span class="font-bold tabular-nums" x-text="secs"></span>
                    <span>detik.</span>
                </div>
            </div>

            <!-- Password Field -->
            <div class="form-control">
                <label class="label pb-1"><span class="label-text text-base-content/60 font-medium">Password</span></label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <x-heroicon-o-lock-closed class="w-5 h-5 text-base-content/40 group-focus-within:text-primary transition-colors" />
                    </div>
                    <input type="password" wire:model="password" 
                        class="input w-full pl-11 bg-base-200/50 border border-white/5 rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-transparent outline-none transition-all duration-300 text-base-content placeholder-base-content/30"
                        placeholder="••••••••" required>
                </div>
                @error('password') 
                    <span class="text-error text-xs mt-2 flex items-center">
                        <x-heroicon-s-exclamation-circle class="w-3 h-3 mr-1" />
                        {{ $message }}
                    </span> 
                @enderror
            </div>

            <!-- Helpers -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center cursor-pointer group">
                    <input type="checkbox" wire:model="remember" class="checkbox checkbox-sm checkbox-primary rounded bg-base-200 border-white/10">
                    <span class="ml-2 text-sm text-base-content/60 font-medium group-hover:text-base-content transition-colors select-none">Remember Me</span>
                </label>
                
                <a href="#" class="text-sm font-medium text-primary hover:text-primary-content transition-colors">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                :disabled="secs > 0"
                :class="secs > 0 ? 'opacity-50 cursor-not-allowed' : 'group'"
                class="btn btn-primary w-full h-12 rounded-xl shadow-lg shadow-primary/20 normal-case text-base border-none relative overflow-hidden">
                <span class="relative z-10 flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="authenticate">
                        <span x-show="secs <= 0">Sign In</span>
                        <span x-show="secs > 0" x-cloak>
                            Tunggu <span class="tabular-nums font-bold" x-text="secs"></span>s...
                        </span>
                    </span>
                    <span wire:loading wire:target="authenticate" class="flex items-center">
                        <span class="loading loading-spinner loading-sm mr-2"></span>
                        Authenticating...
                    </span>
                </span>
                <div class="absolute inset-0 bg-white/20 transition-transform duration-300 transform -translate-x-full group-hover:translate-x-0"></div>
            </button>
        </form>

        <div class="mt-8 text-center relative z-10">
            <p class="text-xs text-base-content/40 font-medium">
                &copy; {{ date('Y') }} SISO Team. All rights reserved.
            </p>
        </div>
    </div>
</div>