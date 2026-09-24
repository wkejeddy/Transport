@props([
    'title' => '',
    'value' => '',
    'subtitle' => null,
    'icon' => 'fa-solid fa-chart-simple',
    'trend' => null, // e.g. '+12%'
    'trendType' => 'up', // up, down, neutral
    'accent' => 'emerald' // emerald, blue, purple, amber, rose
])

@php
    $accentMap = [
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200/60'],
        'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200/60'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-200/60'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200/60'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200/60'],
    ][$accent] ?? ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200/60'];
@endphp

<div class="card p-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden group">
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">{{ $title }}</span>
            <div class="text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight flex items-baseline gap-2">
                {{ $value }}
            </div>
            @if($subtitle)
                <p class="text-xs text-slate-500 font-medium pt-1">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="w-12 h-12 rounded-2xl {{ $accentMap['bg'] }} {{ $accentMap['text'] }} border {{ $accentMap['border'] }} flex items-center justify-center text-lg shadow-xs group-hover:scale-110 transition-transform duration-300">
            <i class="{{ $icon }}"></i>
        </div>
    </div>

    @if($trend)
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-1.5 text-xs font-semibold">
            @if($trendType === 'up')
                <span class="text-emerald-600 flex items-center gap-1"><i class="fa-solid fa-arrow-trend-up"></i> {{ $trend }}</span>
            @elseif($trendType === 'down')
                <span class="text-rose-600 flex items-center gap-1"><i class="fa-solid fa-arrow-trend-down"></i> {{ $trend }}</span>
            @else
                <span class="text-slate-500">{{ $trend }}</span>
            @endif
            <span class="text-slate-400 font-normal">{{ __('vs mois précédent') }}</span>
        </div>
    @endif
</div>
