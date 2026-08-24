{{-- ========== MODAL PHOTO VIEWER ========== --}}
    <div x-data="{ 
            open: false, 
            imageUrl: '', 
            title: '', 
            scale: 1, 
            rotation: 0,
            panX: 0,
            panY: 0,
            isDragging: false,
            startX: 0,
            startY: 0,
            reset() {
                this.scale = 1;
                this.rotation = 0;
                this.panX = 0;
                this.panY = 0;
            },
            handleWheel(e) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -0.15 : 0.15;
                this.scale = Math.min(Math.max(0.25, this.scale + delta), 4);
            },
            startDrag(e) {
                if (this.scale <= 1) return; // Allow dragging only when zoomed in
                this.isDragging = true;
                this.startX = e.clientX - this.panX;
                this.startY = e.clientY - this.panY;
            },
            doDrag(e) {
                if (!this.isDragging) return;
                this.panX = e.clientX - this.startX;
                this.panY = e.clientY - this.startY;
            },
            endDrag() {
                this.isDragging = false;
            }
         }" 
         @open-photo-modal.window="open = true; imageUrl = $event.detail.url; title = $event.detail.title; reset();"
         @mousemove.window="doDrag($event)"
         @mouseup.window="endDrag()"
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6">
        
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/90 backdrop-blur-sm cursor-zoom-out" @click="open = false"></div>

        <!-- Modal Panel -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full max-w-4xl flex flex-col items-center justify-center pointer-events-none">
            
            <div class="w-full flex justify-between items-center mb-4 pointer-events-auto">
                <div class="flex items-center gap-1 bg-black/40 rounded-xl p-1 backdrop-blur-sm border border-white/10">
                    <button @click="scale = Math.max(0.25, scale - 0.25)" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Zoom Out">
                        <x-heroicon-s-minus class="w-4 h-4" />
                    </button>
                    <button @click="scale = Math.min(4, scale + 0.25)" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Zoom In">
                        <x-heroicon-s-plus class="w-4 h-4" />
                    </button>
                    <button @click="reset()" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Reset">
                        <x-heroicon-s-arrow-path class="w-4 h-4" />
                    </button>
                    <div class="w-px h-5 bg-white/20 mx-1"></div>
                    <button @click="rotation -= 90" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Putar Kiri">
                        <x-heroicon-s-arrow-uturn-left class="w-4 h-4" />
                    </button>
                    <button @click="rotation += 90" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Putar Kanan">
                        <x-heroicon-s-arrow-uturn-right class="w-4 h-4" />
                    </button>
                </div>
                <button @click="open = false" class="btn btn-circle btn-ghost text-white/70 hover:text-white hover:bg-white/20">
                    <x-heroicon-s-x-mark class="w-6 h-6" />
                </button>
            </div>
            
            <div class="overflow-hidden flex items-center justify-center pointer-events-auto max-h-[75vh] w-full rounded-xl ring-1 ring-white/10 bg-base-200/20 relative"
                 @wheel="handleWheel($event)"
                 @mousedown="startDrag($event)">
                <img :src="imageUrl" :alt="title" 
                     :style="`transform: translate(${panX}px, ${panY}px) scale(${scale}) rotate(${rotation}deg); transition: ${isDragging ? 'none' : 'transform 0.2s ease-in-out'}; cursor: ${scale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'default'};`"
                     class="max-h-[75vh] w-auto object-contain select-none" draggable="false" />
            </div>
            <div class="mt-4 text-white font-semibold tracking-wider uppercase text-sm pointer-events-auto" x-text="title"></div>
        </div>
    </div>