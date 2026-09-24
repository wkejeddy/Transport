@props([
    'variant' => 'default', // default, road, rail, success, warning, danger, score-a, score-b, score-c, score-d
    'size' => 'md', // sm, md, lg
    'icon' => null
])

@php
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-[10px] font-bold rounded-md gap-1',
        'md' => 'px-2.5 py-1 text-xs font-bold rounded-full gap-1.5',
        'lg' => 'px-3.5 py-1.5 text-sm font-extrabold rounded-full gap-2',
    ][$size] ?? 'px-2.5 py-1 text-xs font-bold rounded-full gap-1.5';

    $variants = [
        'default' => 'bg-slate-100 text-slate-700 border border-slate-200/80 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        'road' => 'bg-blue-50 text-blue-700 border border-blue-200/80 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800/80',
        'rail' => 'bg-purple-50 text-purple-700 border border-purple-200/80 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800/80',
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/80',
        'warning' => 'bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/80',
        'danger' => 'bg-rose-50 text-rose-700 border border-rose-200/80 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/80',
        'score-a' => 'bg-emerald-50 text-emerald-800 border-2 border-emerald-500 font-black dark:bg-emerald-950/70 dark:text-emerald-300 dark:border-emerald-500',
        'score-b' => 'bg-blue-50 text-blue-800 border-2 border-blue-500 font-black dark:bg-blue-950/70 dark:text-blue-300 dark:border-blue-500',
        'score-c' => 'bg-amber-50 text-amber-800 border-2 border-amber-500 font-black dark:bg-amber-950/70 dark:text-amber-300 dark:border-amber-500',
        'score-d' => 'bg-rose-50 text-rose-800 border-2 border-rose-500 font-black dark:bg-rose-950/70 dark:text-rose-300 dark:border-rose-500',
    ][$variant] ?? 'bg-slate-100 text-slate-700 border border-slate-200/80 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
@endphp

<span {{ $attributes->merge(['class' => "badge inline-flex items-center tracking-wide uppercase select-none $sizeClasses $variants"]) }}>
    @if($icon) <i class="{{ $icon }}"></i> @endif
    {{ $slot }}
</span>
