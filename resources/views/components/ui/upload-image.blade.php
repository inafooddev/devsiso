@props([
    'wireModel',
    'label',
    'previewUrl' => null,
    'existingUrl' => null,
    'minHeight' => '100px'
])

<div class="space-y-1.5">
    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">{{ $label }}</label>
    
    <div x-data="{ 
        isDragging: false,
        uploadProgress: 0,
        isUploading: false,
        uploadFile(file) {
            if (!file.type.startsWith('image/')) {
                alert('File harus berupa gambar!');
                return;
            }
            this.isUploading = true;
            this.uploadProgress = 0;
            @this.upload('{{ $wireModel }}', file, 
                (uploadedName) => {
                    this.isUploading = false;
                    this.uploadProgress = 0;
                }, 
                () => {
                    this.isUploading = false;
                    this.uploadProgress = 0;
                    alert('Gagal mengunggah file.');
                }, 
                (event) => {
                    this.uploadProgress = event.detail.progress;
                }
            );
        }
    }" 
    class="relative">
        <div 
            @dragover.prevent="isDragging = true" 
            @dragleave.prevent="isDragging = false" 
            @drop.prevent="isDragging = false; const files = $event.dataTransfer.files; if (files.length) uploadFile(files[0])"
            @paste="const items = ($event.clipboardData || $event.originalEvent.clipboardData).items; for (let i = 0; i < items.length; i++) { if (items[i].type.indexOf('image') !== -1) { const file = items[i].getAsFile(); uploadFile(file); break; } }"
            @click="$refs.fileInput.click()"
            tabindex="0"
            :class="{'border-primary bg-primary/5': isDragging, 'border-base-300 bg-base-200/50': !isDragging}"
            class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-4 cursor-pointer hover:border-primary hover:bg-primary/5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50 group text-center"
            style="min-height: {{ $minHeight }}"
        >
            <input x-ref="fileInput" type="file" accept="image/*" class="hidden" 
                   @change="if ($event.target.files.length) uploadFile($event.target.files[0])">
                   
            <x-heroicon-s-camera class="w-6 h-6 text-base-content/40 group-hover:text-primary group-focus:text-primary transition-colors mb-1" />
            <span class="text-xs font-bold text-base-content/70">Klik / Seret Foto ke sini</span>
            <span class="text-[10px] text-base-content/40">Atau klik lalu paste (<kbd class="kbd kbd-xs">Ctrl</kbd> + <kbd class="kbd kbd-xs">V</kbd>)</span>

            {{-- Progress Bar --}}
            <div x-show="isUploading" class="absolute inset-0 bg-base-100/90 backdrop-blur-sm rounded-2xl flex flex-col items-center justify-center p-2 z-10" @click.stop>
                <div class="radial-progress text-primary" :style="'--value:' + uploadProgress + '; --size:2.5rem; --thickness: 3px;'" role="progressbar">
                    <span class="text-[9px] font-bold" x-text="uploadProgress + '%'"></span>
                </div>
                <span class="text-[9px] font-bold mt-1 text-base-content/75">Mengunggah...</span>
            </div>
        </div>
    </div>

    <div class="mt-2 flex items-center gap-3">
        @if ($previewUrl || $existingUrl)
            <div class="avatar relative group">
                <div class="w-16 h-16 rounded-xl border border-base-300 relative overflow-hidden">
                    <img src="{{ $previewUrl ?? $existingUrl }}" alt="Preview" class="object-cover w-full h-full">
                </div>
                <button type="button" wire:click="removePhoto('{{ $wireModel }}')" wire:confirm="Apakah Anda yakin ingin menghapus foto ini?" class="absolute -top-2 -right-2 bg-error text-error-content rounded-full p-1 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity hover:scale-110 focus:opacity-100 z-10" title="Hapus Foto">
                    <x-heroicon-s-x-mark class="w-3 h-3" />
                </button>
            </div>
        @endif
        <div class="text-[10px] text-base-content/40 leading-tight flex-1">Format: JPG/PNG. Max: 2MB</div>
    </div>
    @error($wireModel) <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
</div>
