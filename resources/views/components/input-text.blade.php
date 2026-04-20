@props(['label', 'type' => 'text', 'placeholder' => ''])

<div class="mb-6 relative mt-4">
    <input 
        {{ $attributes->merge(['class' => 'peer block w-full py-2 px-1 bg-transparent border-0 border-b-2 border-gray-200 focus:ring-0 focus:border-purple-500 transition-colors outline-none text-gray-700 text-sm placeholder-transparent']) }}
        type="{{ $type }}" 
        placeholder="{{ $placeholder ?: $label }}"
        id="{{ $attributes->get('wire:model') }}"
    >
    <label for="{{ $attributes->get('wire:model') }}" class="absolute left-1 -top-3.5 text-gray-400 text-xs transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-300 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-purple-500 peer-focus:text-xs cursor-text">
        {{ $label }}
    </label>
    @error($attributes->get('wire:model')) 
        <span class="text-xs text-red-500 mt-1 flex items-center absolute -bottom-5">
            {{ $message }}
        </span>
    @enderror
</div>