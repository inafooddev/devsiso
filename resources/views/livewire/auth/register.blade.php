<div class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-base-200">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 bg-base-100 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-black/40 ring-1 ring-white/5">
        
        <!-- Left Side: Informational / Branding -->
        <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-indigo-600 to-blue-700 relative overflow-hidden">
            <!-- Subtle Mesh Gradient / Glow -->
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-96 h-96 bg-sky-400/20 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-96 h-96 bg-indigo-400/20 blur-[120px] rounded-full"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center ring-1 ring-white/20">
                        <x-heroicon-s-shield-check class="w-6 h-6 text-white" />
                    </div>
                    <span class="text-xl font-bold text-white tracking-tight">SISO Admin</span>
                </div>

                <h1 class="text-4xl font-extrabold text-white leading-tight mb-6">
                    Initial System <br/> 
                    <span class="text-sky-200">Setup & Configuration</span>
                </h1>
                <p class="text-indigo-100 text-lg leading-relaxed max-w-md">
                    Welcome to SISO. Please create your primary administrator account to begin managing your distribution network.
                </p>
            </div>

            <div class="relative z-10 flex items-center gap-4 text-indigo-100/60 text-sm">
                <div class="flex -space-x-2">
                    @for($i=0; $i<4; $i++)
                        <div class="w-8 h-8 rounded-full border-2 border-indigo-600 bg-indigo-400/30"></div>
                    @endfor
                </div>
                <span>Trusted by enterprise distributors worldwide.</span>
            </div>
        </div>

        <!-- Right Side: The Form -->
        <div class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center bg-base-100">
            <div class="mb-10">
                <h2 class="text-2xl font-bold text-base-content mb-2">Create Admin Account</h2>
                <p class="text-base-content/50 text-sm">Complete the form below to initialize the system.</p>
            </div>

            <form wire:submit.prevent="register" class="space-y-6">
                
                <!-- Forced Identity Section (Visual Only) -->
                <div class="grid grid-cols-2 gap-4 p-4 rounded-2xl bg-base-200/50 border border-white/5 mb-8">
                    <div>
                        <span class="block text-[10px] uppercase tracking-widest text-base-content/40 font-bold mb-1">Default ID</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-mono font-medium text-primary">admin</span>
                            <x-heroicon-s-lock-closed class="w-3 h-3 text-base-content/20" />                             
                        </div>
                        @error('userid') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase tracking-widest text-base-content/40 font-bold mb-1">Assigned Role</span>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-primary badge-sm font-bold uppercase tracking-tighter">Administrator</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text text-base-content/60 font-medium">Full Name</span></label>
                            <input wire:model="name" type="text" class="input input-bordered bg-base-200/50 focus:ring-2 focus:ring-primary/20 border-white/5 rounded-xl transition-all" placeholder="Enter your name">
                            @error('name') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text text-base-content/60 font-medium">Email Address</span></label>
                            <input wire:model="email" type="email" class="input input-bordered bg-base-200/50 focus:ring-2 focus:ring-primary/20 border-white/5 rounded-xl transition-all" placeholder="admin@example.com">
                            @error('email') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text text-base-content/60 font-medium">Password</span></label>
                            <input wire:model="password" type="password" class="input input-bordered bg-base-200/50 focus:ring-2 focus:ring-primary/20 border-white/5 rounded-xl transition-all" placeholder="••••••••">
                            @error('password') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text text-base-content/60 font-medium">Confirm Password</span></label>
                            <input wire:model="password_confirmation" type="password" class="input input-bordered bg-base-200/50 focus:ring-2 focus:ring-primary/20 border-white/5 rounded-xl transition-all" placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <!-- Region Selection -->
                <div class="pt-4">
                    <label class="label mb-2"><span class="label-text text-base-content/60 font-medium">Regional Responsibility</span></label>
                    <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto custom-scrollbar pr-2 p-2 bg-base-200/30 rounded-xl border border-white/5">
                        @foreach($availableRegions as $region)
                            <label class="flex items-center p-2 rounded-lg hover:bg-white/5 cursor-pointer group transition-colors">
                                <input type="checkbox" wire:model="region_code" value="{{ $region->region_code }}" class="checkbox checkbox-primary checkbox-xs rounded">
                                <span class="ml-3 text-xs text-base-content/60 group-hover:text-base-content transition-colors">{{ $region->region_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('region_code') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-6">
                    <button type="submit" class="btn btn-primary w-full rounded-xl shadow-lg shadow-primary/20 normal-case h-12">
                        <span wire:loading.remove wire:target="register">Complete Registration & Start</span>
                        <span wire:loading wire:target="register" class="loading loading-spinner"></span>
                    </button>

                    <p class="mt-6 text-center text-xs text-base-content/40">
                        Already set up? 
                        <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline transition-all">
                            Sign In
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.1); }
    </style>
</div> <!-- Akhir BUNGKUS UTAMA -->