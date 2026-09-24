@props([
    'name' => '',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'hint' => null,
    'icon' => null
])

<div class="form-group mb-5">
    @if($label)
        <label for="{{ $name }}" class="form-label block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-xs">
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <i class="{{ $icon }}"></i>
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'form-control block w-full rounded-xl border-slate-300 bg-white py-3 ' . 
                           ($icon ? 'pl-10 ' : 'pl-4 ') . 'pr-4 text-sm text-slate-900 placeholder-slate-400 ' . 
                           'focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all duration-200 ' .
                           ($errors->has($name) ? 'border-rose-400 ring-rose-400/10' : '')
            ]) }}
        >
    </div>

    @if($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-xs font-semibold text-rose-600 flex items-center gap-1">
            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
        </p>
    @enderror
</div>
