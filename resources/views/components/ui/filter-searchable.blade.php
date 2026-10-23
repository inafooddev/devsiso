@props([
    'options' => [],
    'placeholder' => 'Pilih...',
    'icon' => null,
    'width' => 'w-36 sm:w-44',
    'popoverWidth' => 'w-72 sm:w-80',
    'valueField' => null,
    'labelField' => null,
    'disabled' => false,
    'title' => null,
    'searchPlaceholder' => 'Ketik untuk mencari...',
    'emptyText' => 'Tidak ditemukan data yang cocok',
    'allowAll' => false,
    'allLabel' => null,
])

@php
    $wireModel = $attributes->wire('model')->value();
    $isLive = $attributes->wire('model')->hasModifier('live');

    // Normalisasi opsi ke struktur seragam [{code: '...', name: '...'}] untuk Alpine JS
    $normalizedOptions = collect($options)->map(function($item, $key) use ($valueField, $labelField) {
        if (is_object($item)) {
            $code = $valueField ? ($item->{$valueField} ?? '') : ($item->code ?? $item->distributor_code ?? $item->salesman_code ?? $item->id ?? $item->value ?? $key);
            $name = $labelField ? ($item->{$labelField} ?? '') : ($item->name ?? $item->distributor_name ?? $item->salesman_name ?? $item->label ?? $code);
        } elseif (is_array($item)) {
            $code = $valueField ? ($item[$valueField] ?? '') : ($item['code'] ?? $item['distributor_code'] ?? $item['salesman_code'] ?? $item['id'] ?? $item['value'] ?? $key);
            $name = $labelField ? ($item[$labelField] ?? '') : ($item['name'] ?? $item['distributor_name'] ?? $item['salesman_name'] ?? $item['label'] ?? $code);
        } else {
            $code = is_numeric($key) ? $item : $key;
            $name = $item;
        }
        return [
            'code' => (string)$code,
            'name' => (string)($name ?: $code)
        ];
    })->values()->all();

    $currentVal = $wireModel && isset($this) ? ($this->{$wireModel} ?? null) : null;
    $isActive = !empty($currentVal);
@endphp

<div {{ $attributes->only('wire:key') }}
    x-data="{
        open: false,
        search: '',
        selectedCode: @if($wireModel) @if($isLive) @entangle($wireModel).live @else @entangle($wireModel) @endif @else '' @endif,
        options: @js($normalizedOptions),
        placeholder: '{{ $placeholder }}',
        get selectedLabel() {
            if (!this.selectedCode) return this.placeholder;
            const found = this.options.find(o => o.code === this.selectedCode);
            return found ? found.name : this.selectedCode;
        },
        get filtered() {
            if (!this.search.trim()) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o => 
                o.name.toLowerCase().includes(q) || 
                o.code.toLowerCase().includes(q)
            );
        },
        select(code) {
            this.open = false;
            this.search = '';
            @if($wireModel)
                $wire.set('{{ $wireModel }}', code);
            @else
                this.selectedCode = code;
            @endif
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative flex items-center {{ $width }} shrink-0 min-w-0 {{ $disabled ? 'opacity-40 pointer-events-none' : '' }}"
    title="{{ $title ?? $placeholder }}">

    {{-- Trigger Button inside Capsule --}}
    <button type="button"
        @click="if (!{{ $disabled ? 'true' : 'false' }}) { open = !open; if(open) { $nextTick(() => $refs.searchInput?.focus()) } }"
        class="w-full flex items-center justify-between text-left py-1 pl-2 pr-1.5 focus:outline-none cursor-pointer group"
        {{ $disabled ? 'disabled' : '' }}>
        <div class="flex items-center min-w-0 flex-1 mr-1">
            @if($icon)
                @if($icon === 'building' || $icon === 'distributor')
                    <x-heroicon-m-building-office-2 class="w-3.5 h-3.5 text-indigo-500/80 shrink-0 mr-1" />
                @elseif($icon === 'user' || $icon === 'salesman')
                    <x-heroicon-m-user class="w-3.5 h-3.5 text-emerald-600/80 shrink-0 mr-1" />
                @elseif($icon === 'shopping-bag' || $icon === 'store' || $icon === 'toko')
                    <x-heroicon-m-building-storefront class="w-3.5 h-3.5 text-amber-500/80 shrink-0 mr-1" />
                @else
                    <x-dynamic-component :component="'heroicon-m-'.$icon" class="w-3.5 h-3.5 text-base-content/60 shrink-0 mr-1" />
                @endif
            @endif
            <span class="truncate text-xs font-medium"
                :class="selectedCode ? 'text-primary font-bold' : 'text-base-content/75'"
                x-text="selectedLabel"></span>
        </div>
        <div class="flex items-center gap-0.5 shrink-0">
            <template x-if="selectedCode">
                <span @click.stop="select('')"
                    class="p-0.5 rounded-full hover:bg-base-200 text-base-content/40 hover:text-error transition-colors cursor-pointer"
                    title="Hapus pilihan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </template>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-base-content/40 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    {{-- Dropdown Popover Menu --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        style="display: none;"
        class="absolute left-0 top-full mt-1.5 z-[70] {{ $popoverWidth }} bg-base-100 rounded-xl shadow-2xl border border-base-300 p-2 flex flex-col text-xs focus:outline-none">
        
        {{-- Search Input Box --}}
        <div class="relative mb-2 shrink-0">
            <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40 pointer-events-none" />
            <input type="text"
                x-model="search"
                x-ref="searchInput"
                placeholder="{{ $searchPlaceholder }}"
                class="input input-xs w-full pl-8 pr-6 rounded-lg bg-base-200/60 border border-transparent focus:border-primary focus:bg-base-100 text-xs font-normal" />
            <button x-show="search"
                @click="search = ''; $refs.searchInput?.focus()"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content cursor-pointer">
                <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
            </button>
        </div>

        {{-- Options List --}}
        <div class="overflow-y-auto max-h-60 flex flex-col gap-0.5 pr-0.5 divide-y divide-base-200/40">
            @if($allowAll)
                <button type="button"
                    @click="select('')"
                    class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition-colors pt-1.5 cursor-pointer"
                    :class="!selectedCode ? 'bg-primary text-white font-semibold shadow-xs' : 'hover:bg-base-200 text-base-content'">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-xs">{{ $allLabel ?? $placeholder }}</span>
                    </div>
                    <template x-if="!selectedCode">
                        <x-heroicon-m-check class="w-4 h-4 text-white shrink-0 ml-1.5" />
                    </template>
                </button>
            @endif

            <template x-for="item in filtered" :key="item.code">
                <button type="button"
                    @click="select(item.code)"
                    class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition-colors pt-1.5 cursor-pointer"
                    :class="selectedCode === item.code ? 'bg-primary text-white font-semibold shadow-xs' : 'hover:bg-base-200 text-base-content'"
                    :title="item.name + ' (' + item.code + ')'">
                    <div class="flex flex-col min-w-0 pr-1">
                        <span class="truncate font-medium text-xs" :class="selectedCode === item.code ? 'text-white' : 'text-base-content'" x-text="item.name"></span>
                        <span class="text-[0.625rem] font-mono" :class="selectedCode === item.code ? 'text-white/80' : 'text-base-content/50'" x-text="item.code"></span>
                    </div>
                    <template x-if="selectedCode === item.code">
                        <x-heroicon-m-check class="w-4 h-4 text-white shrink-0 ml-1.5" />
                    </template>
                </button>
            </template>

            <template x-if="filtered.length === 0">
                <div class="px-3 py-4 text-xs text-base-content/50 italic text-center">
                    {{ $emptyText }}
                </div>
            </template>
        </div>
    </div>
</div>
