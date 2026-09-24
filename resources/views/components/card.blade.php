@props([
    'variant' => 'default', // default, glass, flat, interactive
    'padding' => 'p-6',
    'title' => null,
    'subtitle' => null,
    'action' => null
])

@php
    $baseClasses = 'bg-white rounded-2xl border border-slate-200/80 transition-all duration-300 relative overflow-hidden';
    
    $variants = [
        'default' => 'shadow-sm hover:shadow-md hover:border-slate-300',
        'glass' => 'bg-white/80 backdrop-blur-xl border-white/40 shadow-lg',
        'flat' => 'bg-slate-50/70 border-slate-200 shadow-none',
        'interactive' => 'shadow-sm hover:-translate-y-1 hover:shadow-xl hover:border-emerald-500/40 cursor-pointer',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => "card $baseClasses $variantClass $padding"]) }}>
    @if($title || $action)
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
            <div>
                @if($title)
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs font-medium text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($action)
                <div class="flex items-center gap-2">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
