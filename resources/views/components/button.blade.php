@props([
    'variant' => 'primary', // primary, secondary, outline, danger, om, momo, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'href' => null,
    'type' => 'button'
])

@php
    $sizeClasses = [
        'sm' => 'px-3.5 py-1.5 text-xs rounded-lg gap-1.5 font-semibold min-h-[36px]',
        'md' => 'px-5 py-2.5 text-sm rounded-xl gap-2 font-bold min-h-[44px]',
        'lg' => 'px-7 py-3.5 text-base rounded-2xl gap-2.5 font-extrabold min-h-[52px]',
    ][$size] ?? 'px-5 py-2.5 text-sm rounded-xl gap-2 font-bold min-h-[44px]';

    $variants = [
        'primary' => 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm hover:shadow-emerald-600/25 active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2',
        'secondary' => 'bg-rose-600 hover:bg-rose-500 text-white shadow-sm hover:shadow-rose-600/25 active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2',
        'outline' => 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 hover:border-slate-300 shadow-xs active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2',
        'danger' => 'bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 active:scale-[0.98] focus-visible:ring-2 focus-visible:ring-red-500',
        'om' => 'bg-orange-500 hover:bg-orange-400 text-white shadow-sm hover:shadow-orange-500/30 active:scale-[0.98]',
        'momo' => 'bg-amber-400 hover:bg-amber-300 text-slate-950 font-black shadow-sm hover:shadow-amber-400/30 active:scale-[0.98]',
        'ghost' => 'bg-transparent hover:bg-slate-100 text-slate-600 hover:text-slate-900',
    ][$variant] ?? 'bg-emerald-600 text-white';

    $classes = "btn inline-flex items-center justify-center transition-all duration-200 select-none cursor-pointer focus:outline-none $sizeClasses $variants";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <i class="{{ $icon }}"></i> @endif
        {{ $slot }}
        @if($iconRight) <i class="{{ $iconRight }}"></i> @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <i class="{{ $icon }}"></i> @endif
        {{ $slot }}
        @if($iconRight) <i class="{{ $iconRight }}"></i> @endif
    </button>
@endif
