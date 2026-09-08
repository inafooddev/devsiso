@props([
    'options' => [],
    'placeholder' => '-- Pilih --',
])

<div x-data="{
        open: false,
        search: '',
        value: @entangle($attributes->wire('model')),
        options: [],
        init() {
            // Initial load
            this.options = JSON.parse(this.$el.dataset.options || '[]');
            
            // Watch for Livewire morphdom changes to the data-options attribute
            let observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-options') {
                        this.options = JSON.parse(this.$el.dataset.options || '[]');
                        if (!this.options.find(o => o.value == this.value)) {
                            this.value = ''; // Reset if selected value is no longer in options
                        }
                    }
                });
            });
            observer.observe(this.$el, { attributes: true, attributeFilter: ['data-options'] });
        },
        get filteredOptions() {
            if (this.search === '') {
                return this.options;
            }
            return this.options.filter(option => 
                option.label.toLowerCase().includes(this.search.toLowerCase()) || 
                String(option.value).toLowerCase().includes(this.search.toLowerCase())
            );
        },
        get selectedLabel() {
            const selected = this.options.find(option => option.value == this.value);
            return selected ? selected.label : '{{ $placeholder }}';
        },
        selectOption(val) {
            this.open = false;
            this.search = '';
            
            // Set timeout ensures the modal closes immediately in UI
            // before Livewire initiates the wire:model.live network request.
            setTimeout(() => {
                this.value = val;
            }, 50);
        }
    }" 
    data-options="{{ json_encode($options) }}"
    @click.away="open = false" 
    class="relative w-full"
>
    
    <!-- Select Button (Fake Select) -->
    <button 
        type="button" 
        @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }" 
        class="select select-bordered select-sm w-full flex items-center justify-between bg-base-100"
        :class="{ 'text-base-content/50': !value }"
    >
        <span x-text="selectedLabel" class="truncate text-left flex-1"></span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Popover -->
    <div 
        x-show="open" 
        style="display: none;"
        class="absolute z-[100] mt-1 w-full bg-base-100 rounded-lg shadow-xl border border-base-300 max-h-60 overflow-hidden flex flex-col"
    >
        <!-- Search Input -->
        <div class="p-2 border-b border-base-200">
            <input 
                type="text" 
                x-model="search" 
                x-ref="searchInput"
                class="input input-sm input-bordered w-full" 
                placeholder="Cari..."
                @keydown.escape="open = false"
            >
        </div>

        <!-- Options List -->
        <ul class="overflow-y-auto flex-1 p-1">
            <template x-for="(option, index) in filteredOptions" :key="index">
                <li>
                    <button 
                        type="button" 
                        @click.prevent.stop="selectOption(option.value)"
                        class="w-full text-left px-3 py-2 text-sm rounded-md hover:bg-base-200 focus:bg-base-200 focus:outline-none transition-colors"
                        :class="{ 'bg-primary text-primary-content hover:bg-primary': value == option.value }"
                    >
                        <span x-text="option.label"></span>
                    </button>
                </li>
            </template>
            <template x-if="filteredOptions.length === 0">
                <li class="px-3 py-2 text-sm text-base-content/50 italic text-center">
                    Tidak ditemukan
                </li>
            </template>
        </ul>
    </div>
</div>
