@props(['label' => '', 'type' => 'text', 'placeholder' => ''])

@php
    $inputId = $attributes->get('wire:model')
        ?? $attributes->get('wire:model.live')
        ?? $attributes->get('wire:model.defer')
        ?? $attributes->get('name')
        ?? $label;

    $errorKey = $attributes->get('wire:model')
        ?? $attributes->get('wire:model.live')
        ?? $attributes->get('wire:model.defer')
        ?? $attributes->get('name')
        ?? '';
@endphp

<div class="form-control mb-4">
    @if($label)
    <label class="label pb-1" for="{{ $inputId }}">
        <span class="label-text text-xs font-medium text-base-content/70">{{ $label }}</span>
    </label>
    @endif

    <input
        {{ $attributes->merge(['class' => 'input input-bordered input-sm w-full focus:input-primary']) }}
        type="{{ $type }}"
        placeholder="{{ $placeholder ?: $label }}"
        id="{{ $inputId }}"
    >

    @if($errorKey)
        @error($errorKey)
            <label class="label pt-1">
                <span class="label-text-alt text-error flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </span>
            </label>
        @enderror
    @endif
</div>