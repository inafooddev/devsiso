<div x-data="{
        show: false,
        delayTimer: null,
        safetyTimer: null,
        startLoading() {
            if (this.delayTimer) clearTimeout(this.delayTimer);
            if (this.safetyTimer) clearTimeout(this.safetyTimer);
            
            // Wait 1000ms before showing the spinner to prevent flashing on fast requests
            this.delayTimer = setTimeout(() => {
                this.show = true;
                // Safety timeout: Auto-dismiss spinner after 5 seconds max
                this.safetyTimer = setTimeout(() => {
                    this.show = false;
                }, 5000);
            }, 1000);
        },
        stopLoading() {
            if (this.delayTimer) clearTimeout(this.delayTimer);
            if (this.safetyTimer) clearTimeout(this.safetyTimer);
            this.show = false;
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
        
        {{-- 360 Logo Spinner Container --}}
        <div class="relative w-14 h-14 flex items-center justify-center shrink-0">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-md">
                <!-- Outer 360 Arc (Clockwise Spin) -->
                <g class="origin-center animate-[spin_1s_linear_infinite]">
                    <path d="M 12 3 A 9 9 0 1 1 3 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="text-sky-500" />
                </g>
                
                <!-- Inner Accent Arc (Counter-Clockwise Spin) -->
                <g class="origin-center animate-[spin_1.5s_linear_infinite_reverse]">
                    <path d="M 12 7 A 5 5 0 0 0 7 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="text-indigo-400" />
                </g>
                
                <!-- Core Node (Glowing Pulse) -->
                <circle cx="12" cy="12" r="2.5" fill="currentColor" class="text-indigo-600 animate-pulse" />
            </svg>
        </div>
    </div>
</div>
