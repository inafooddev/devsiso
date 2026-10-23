@props([
    'start' => 'startDate',
    'end' => 'endDate',
    'maxDays' => 31,
    'presets' => true,
    'stepper' => true,
    'prevAction' => 'previousMonth',
    'nextAction' => 'nextMonth',
    'presetAction' => 'setDatePreset',
])

@php
    $startVal = isset($this) ? ($this->{$start} ?? null) : null;
    $endVal = isset($this) ? ($this->{$end} ?? null) : null;
    $diffDays = ($startVal && $endVal) ? (\Carbon\Carbon::parse($startVal)->diffInDays(\Carbon\Carbon::parse($endVal)) + 1) : 0;
    $minStart = ($endVal && $maxDays) ? \Carbon\Carbon::parse($endVal)->subDays($maxDays - 1)->format('Y-m-d') : '';
    $maxEnd = ($startVal && $maxDays) ? \Carbon\Carbon::parse($startVal)->addDays($maxDays - 1)->format('Y-m-d') : date('Y-m-d');
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 bg-base-100 px-1 py-0.5 rounded-xl border border-base-300 shadow-2xs text-xs shrink-0']) }}>
    @if($stepper)
        <button type="button" wire:click="{{ $prevAction }}" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-base-content cursor-pointer" title="Bulan Sebelumnya">
            <x-heroicon-m-chevron-left class="w-3.5 h-3.5" />
        </button>
    @endif

    <input type="date" 
        wire:model.live="{{ $start }}" 
        max="{{ $endVal ?: date('Y-m-d') }}"
        @if($minStart) min="{{ $minStart }}" @endif
        class="input input-xs bg-transparent border-none text-xs w-[105px] focus:outline-none p-0 text-center font-medium font-mono" 
        title="Tanggal Mulai" />

    <span class="text-xs font-semibold text-base-content/40">s/d</span>

    <input type="date" 
        wire:model.live="{{ $end }}" 
        @if($startVal) min="{{ $startVal }}" @endif
        max="{{ $maxEnd }}"
        class="input input-xs bg-transparent border-none text-xs w-[105px] focus:outline-none p-0 text-center font-medium font-mono" 
        title="Tanggal Akhir" />

    @if($diffDays > 0)
        <div class="tooltip tooltip-bottom" data-tip="{{ $diffDays }} hari dipilih @if($maxDays)(Maks. {{ $maxDays }} hari)@endif">
            <span class="badge badge-xs {{ ($maxDays && $diffDays >= $maxDays) ? 'badge-warning text-slate-950 font-bold' : 'badge-neutral text-neutral-content' }} font-mono font-bold px-1.5 cursor-default">
                {{ $diffDays }}h
            </span>
        </div>
    @endif

    @if($stepper)
        <button type="button" wire:click="{{ $nextAction }}" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-base-content cursor-pointer" title="Bulan Berikutnya">
            <x-heroicon-m-chevron-right class="w-3.5 h-3.5" />
        </button>
    @endif

    @if($presets)
        {{-- Dropdown Pintasan Periode Cepat --}}
        <div class="dropdown dropdown-end">
            <button type="button" tabindex="0" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-primary cursor-pointer" title="Pintasan Periode Cepat">
                <x-heroicon-m-calendar-days class="w-3.5 h-3.5" />
            </button>
            <ul tabindex="0" class="dropdown-content z-30 menu menu-xs p-1.5 shadow-xl bg-base-100 rounded-xl border border-base-300 w-36 text-xs">
                <li class="menu-title text-[0.625rem] uppercase tracking-wider text-base-content/50 px-2 py-0.5">Pintasan Periode</li>
                <li><a wire:click="{{ $presetAction }}('today')" class="py-1 cursor-pointer">Hari Ini</a></li>
                <li><a wire:click="{{ $presetAction }}('yesterday')" class="py-1 cursor-pointer">Kemarin</a></li>
                <li><a wire:click="{{ $presetAction }}('last_7_days')" class="py-1 cursor-pointer">7 Hari Terakhir</a></li>
                <li><a wire:click="{{ $presetAction }}('this_month')" class="py-1 font-semibold text-primary cursor-pointer">Bulan Ini (MTD)</a></li>
                <li><a wire:click="{{ $presetAction }}('last_30_days')" class="py-1 cursor-pointer">30 Hari Terakhir</a></li>
                <li><a wire:click="{{ $presetAction }}('last_month')" class="py-1 cursor-pointer">Bulan Lalu</a></li>
            </ul>
        </div>
    @endif
</div>
