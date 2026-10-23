@props([
    'options' => [],
    'placeholder' => 'Pilih...',
    'valueField' => null,
    'labelField' => null,
    'icon' => null,
    'width' => 'w-32 sm:w-36',
    'title' => null,
    'disabled' => false,
    'selected' => null,
])

@php
    $wireModel = $attributes->wire('model')->value();
    $currentVal = $selected ?? ($wireModel && isset($this) ? ($this->{$wireModel} ?? null) : null);
    $isActive = !empty($currentVal);
@endphp

<div class="relative flex items-center pl-2 pr-1.5 {{ $width }} shrink-0 min-w-0 {{ $disabled ? 'opacity-40 pointer-events-none' : '' }}" 
    title="{{ $title ?? $placeholder }}">
    
    @if($icon)
        @if($icon === 'globe' || $icon === 'region')
            <x-heroicon-m-globe-asia-australia class="w-3.5 h-3.5 text-primary/70 shrink-0 mr-1 pointer-events-none" />
        @elseif($icon === 'map-pin' || $icon === 'area')
            <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-rose-500/70 shrink-0 mr-1 pointer-events-none" />
        @elseif($icon === 'building' || $icon === 'distributor')
            <x-heroicon-m-building-office-2 class="w-3.5 h-3.5 text-indigo-500/80 shrink-0 mr-1 pointer-events-none" />
        @elseif($icon === 'user' || $icon === 'salesman')
            <x-heroicon-m-user class="w-3.5 h-3.5 text-emerald-600/80 shrink-0 mr-1 pointer-events-none" />
        @elseif($icon === 'tag')
            <x-heroicon-m-tag class="w-3.5 h-3.5 text-amber-500/80 shrink-0 mr-1 pointer-events-none" />
        @else
            <x-dynamic-component :component="'heroicon-m-'.$icon" class="w-3.5 h-3.5 text-base-content/60 shrink-0 mr-1 pointer-events-none" />
        @endif
    @elseif(isset($customIcon))
        {{ $customIcon }}
    @endif

    <select {{ $attributes->merge([
        'class' => 'appearance-none bg-none bg-transparent border-none focus:outline-none text-xs ' . 
                   ($isActive ? 'text-primary font-bold' : 'font-medium text-base-content/75') . 
                   ' w-full min-w-0 truncate pl-0 pr-4 py-1 cursor-pointer'
    ]) }} @disabled($disabled)>
        @if($placeholder)
            <option value="" class="text-base-content">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $key => $opt)
            @php
                if (is_object($opt)) {
                    $val = $valueField ? ($opt->{$valueField} ?? '') : ($opt->value ?? $opt->code ?? $opt->region_code ?? $opt->area_code ?? $opt->id ?? $key);
                    $lbl = $labelField ? ($opt->{$labelField} ?? '') : ($opt->label ?? $opt->name ?? $opt->region_name ?? $opt->area_name ?? $val);
                } elseif (is_array($opt)) {
                    $val = $valueField ? ($opt[$valueField] ?? '') : ($opt['value'] ?? $opt['code'] ?? $opt['id'] ?? $key);
                    $lbl = $labelField ? ($opt[$labelField] ?? '') : ($opt['label'] ?? $opt['name'] ?? $val);
                } else {
                    $val = is_numeric($key) ? $opt : $key;
                    $lbl = $opt;
                }
            @endphp
            <option value="{{ $val }}" class="text-base-content">{{ $lbl }}</option>
        @endforeach
    </select>

    <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2" />
</div>
