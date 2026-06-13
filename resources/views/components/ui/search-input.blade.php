@props([
    'placeholder' => 'Ketik pencarian...',
    'name' => 'search',
])

<div class="relative group grow sm:grow-0">
    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/40 group-focus-within:text-primary transition-colors" />
    <input 
        type="text" 
        name="{{ $name }}"
        class="input input-sm input-bordered pl-9 w-full sm:w-48 lg:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300" 
        placeholder="{{ $placeholder }}" 
        {{ $attributes }} 
    />
</div>
