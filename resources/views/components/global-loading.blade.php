<div x-data="{
        show: false,
        timer: null,
        startLoading() {
            this.show = true;
            if (this.timer) clearTimeout(this.timer);
            // Safety timeout: Auto-dismiss spinner after 5 seconds max
            this.timer = setTimeout(() => {
                this.show = false;
            }, 5000);
        },
        stopLoading() {
            this.show = false;
            if (this.timer) clearTimeout(this.timer);
        },
        initHooks() {
            const register = () => {
                if (typeof window.Livewire !== 'undefined' && window.Livewire.hook) {
                    window.Livewire.hook('request', ({ succeed, fail }) => {
                        this.startLoading();
                        succeed(() => this.stopLoading());
                        fail(() => this.stopLoading());
                    });
                }
            };

            if (window.Livewire) {
                register();
            } else {
                document.addEventListener('livewire:init', register, { once: true });
            }

            // 1. Listen for Livewire SPA Navigation (wire:navigate)
            document.addEventListener('livewire:navigating', () => this.startLoading());
            document.addEventListener('livewire:navigated', () => this.stopLoading());

            // 2. Listen for traditional menu & page navigation clicks (a[href])
            document.addEventListener('click', (e) => {
                const a = e.target.closest('a[href]');
                if (a && a.href && !a.href.startsWith('#') && !a.href.startsWith('javascript:') && a.target !== '_blank') {
                    if (a.href !== window.location.href && !a.hasAttribute('download')) {
                        this.startLoading();
                    }
                }
            });

            // 3. Page unload safety trigger
            window.addEventListener('beforeunload', () => this.startLoading());
        }
     }"
     x-init="initHooks()"
     @set-loading-start.window="startLoading()"
     @set-loading-stop.window="stopLoading()"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-[999999] flex items-center justify-center p-4 overflow-hidden"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-250"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    {{-- Seamless Soft Glass Overlay (No hard edges) --}}
    <div class="absolute inset-0 bg-slate-950/35 backdrop-blur-md"></div>

    {{-- Modern Pure Kinetic Loader Pod (No Text) --}}
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-75 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-85 translate-y-1"
         class="relative z-10 bg-white/95 text-gray-800 rounded-3xl p-5 sm:p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.35)] border border-white/80 flex items-center justify-center select-none overflow-hidden"
         data-theme="light">
        
        {{-- Glowing Ambient Radial Aura --}}
        <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-blue-600 via-indigo-500 to-sky-400 blur-md opacity-35 animate-pulse absolute"></div>

        {{-- Multi-Ring Kinetic Orbital Spinner Container --}}
        <div class="relative w-14 h-14 flex items-center justify-center shrink-0">
            {{-- Outer Primary Gradient Arc (Clockwise Fast) --}}
            <div class="absolute inset-0 rounded-full border-[3.5px] border-indigo-100 border-t-indigo-600 border-r-sky-400 animate-spin"></div>

            {{-- Inner Counter-Rotating Gradient Arc (Counter-Clockwise) --}}
            <div class="absolute w-9 h-9 rounded-full border-[2.5px] border-transparent border-b-indigo-500 border-l-blue-400 animate-[spin_1.4s_linear_infinite_reverse]"></div>

            {{-- Pulsing Glowing Core Dot --}}
            <div class="w-3 h-3 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 shadow-[0_0_12px_rgba(79,70,229,0.9)] animate-pulse"></div>
        </div>
    </div>
</div>
