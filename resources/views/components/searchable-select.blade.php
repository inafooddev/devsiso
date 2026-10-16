@props([
    'options' => [],
    'placeholder' => '-- Pilih --',
    'btnClass' => 'rounded-xl border-base-300 text-xs',
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
        class="select select-bordered select-sm w-full flex items-center justify-between bg-base-100 bg-none pr-3 {{ $btnClass }}"
        :class="{ 'text-base-content/50 font-normal': !value, 'font-semibold': value }"
    >
        <span x-text="selectedLabel" class="truncate text-left flex-1 mr-1"></span>
        <div class="flex items-center gap-1 shrink-0">
            <template x-if="value">
                <span 
                    @click.stop.prevent="selectOption('')" 
                    class="p-0.5 rounded-full hover:bg-base-300 text-base-content/40 hover:text-error transition-colors cursor-pointer"
                    title="Hapus pilihan"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </template>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <!-- Dropdown Popover -->
    <div 
        x-show="open" 
        style="display: none;"
        class="absolute z-[100] mt-1 w-full min-w-[260px] bg-base-100 rounded-xl shadow-2xl border border-base-300 max-h-60 overflow-hidden flex flex-col text-xs left-0"
    >
        <!-- Search Input -->
        <div class="p-2 border-b border-base-200 bg-base-200/50">
            <input 
                type="text" 
                x-model="search" 
                x-ref="searchInput"
                class="input input-sm input-bordered w-full rounded-lg border-base-300 text-xs bg-base-100" 
                placeholder="Ketik untuk mencari..."
                @keydown.escape="open = false"
            >
        </div>

        <!-- Options List -->
        <ul class="overflow-y-auto flex-1 p-1 divide-y divide-base-200/30">
            <template x-for="(option, index) in filteredOptions" :key="index">
                <li>
                    <button 
                        type="button" 
                        @click.prevent.stop="selectOption(option.value)"
                        class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-base-200 focus:bg-base-200 focus:outline-none transition-colors"
                        :class="{ 'bg-primary text-primary-content hover:bg-primary font-bold': value == option.value }"
                    >
                        <span x-text="option.label" class="line-clamp-2"></span>
                    </button>
                </li>
            </template>
            <template x-if="filteredOptions.length === 0">
                <li class="px-3 py-4 text-xs text-base-content/50 italic text-center">
                    Tidak ditemukan hasil yang cocok
                </li>
            </template>
        </ul>
    </div>
</div>
