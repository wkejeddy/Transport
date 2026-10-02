@php
    $currentLocale = app()->getLocale();
@endphp

<div class="bilingual-switch-container {{ $class ?? '' }}" data-current-locale="{{ $currentLocale }}">
    <div class="bilingual-pill-wrapper" role="group" aria-label="{{ __('Changer de langue') }}">
        <button type="button" 
                class="bilingual-pill-btn {{ $currentLocale === 'fr' ? 'active' : '' }}" 
                onclick="window.switchAppLanguage('fr')" 
                title="Passer en Français (French)"
                aria-pressed="{{ $currentLocale === 'fr' ? 'true' : 'false' }}">
            <span class="flag-icon">🇫🇷</span>
            <span class="lang-text">FR</span>
        </button>
        <button type="button" 
                class="bilingual-pill-btn {{ $currentLocale === 'en' ? 'active' : '' }}" 
                onclick="window.switchAppLanguage('en')" 
                title="Switch to English"
                aria-pressed="{{ $currentLocale === 'en' ? 'true' : 'false' }}">
            <span class="flag-icon">🇬🇧</span>
            <span class="lang-text">EN</span>
        </button>
    </div>
</div>

<style>
/* Modern Bilingual Switcher Styles */
.bilingual-switch-container {
    display: inline-flex;
    align-items: center;
    position: relative;
    user-select: none;
    z-index: 50;
}

.bilingual-pill-wrapper {
    display: inline-flex;
    align-items: center;
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 9999px;
    padding: 3px;
    gap: 3px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

[data-theme="dark"] .bilingual-pill-wrapper,
.dark .bilingual-pill-wrapper {
    background: var(--bg-surface, #1e293b);
    border-color: rgba(255, 255, 255, 0.12);
}

.bilingual-pill-btn {
    border: none;
    background: transparent;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted, #64748b);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
    line-height: 1;
}

.bilingual-pill-btn:hover {
    color: var(--text-heading, #0f172a);
}

.bilingual-pill-btn.active {
    background: var(--primary, #0028fc);
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(0, 40, 252, 0.35);
}

.bilingual-pill-btn .flag-icon {
    font-size: 0.9rem;
    line-height: 1;
}

.bilingual-pill-btn .lang-text {
    letter-spacing: 0.5px;
}
</style>
