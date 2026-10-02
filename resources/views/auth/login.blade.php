@extends('layouts.app')

@section('title', __('Welcome Back - Real Express Voyages'))

@section('content')
<div class="login-wakatobi-wrapper">
    <div class="login-wakatobi-card">
        
        <!-- Left Side: Aerial Ocean Beach & Coral Reef Visual with Typography -->
        <div class="login-wakatobi-visual">
            <div class="login-wakatobi-visual-content">
                <!-- Top Brand Return Link -->
                <div class="login-wakatobi-brand-bar">
                    <a href="{{ route('home') }}" class="login-wakatobi-brand-link" title="{{ __('Retour à l\'accueil') }}">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>{{ config('app.name', 'Real Express Voyages') }}</span>
                    </a>
                </div>


                <!-- Bottom-Left Concentric Arc Lines Graphic -->
                <div class="login-wakatobi-arcs-left" aria-hidden="true">
                    <svg viewBox="0 0 220 220" fill="none">
                        <circle cx="0" cy="220" r="60" stroke="rgba(255,255,255,0.45)" stroke-width="1.2" />
                        <circle cx="0" cy="220" r="100" stroke="rgba(255,255,255,0.35)" stroke-width="1.2" />
                        <circle cx="0" cy="220" r="145" stroke="rgba(255,255,255,0.25)" stroke-width="1.2" />
                        <circle cx="0" cy="220" r="190" stroke="rgba(255,255,255,0.18)" stroke-width="1.2" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Right Side: Clean White Login Panel with Organic Wave Divider -->
        <div class="login-wakatobi-panel">
            
            <!-- Seamless Organic S-Wave Boundary extending into the visual -->
            <div class="login-wakatobi-wave-divider" aria-hidden="true">
                <svg viewBox="0 0 135 800" preserveAspectRatio="none">
                    <path d="M 135,0 
                             C 80,60 20,180 16,310 
                             C 12,440 95,500 105,580 
                             C 115,660 25,730 0,800 
                             L 135,800 Z" class="login-wave-fill" />
                </svg>
            </div>

            <!-- Top Header with Minimalist Menu Icon & Quick Dropdown -->
            <div class="login-wakatobi-header">
                <div></div>
                <div class="login-wakatobi-menu-container">
                    <button type="button" class="login-wakatobi-menu-btn" id="loginMenuBtn" aria-label="Menu" onclick="toggleLoginMenu(event)">
                        <span class="menu-dash"></span>
                        <span class="menu-dash"></span>
                    </button>
                    <div class="login-wakatobi-dropdown" id="loginMenuDropdown">
                        <a href="{{ route('home') }}">
                            <i class="fa-solid fa-house"></i>
                            <span>{{ __('Accueil') }}</span>
                        </a>
                        <a href="{{ route('lang.swap', app()->getLocale() === 'fr' ? 'en' : 'fr') }}">
                            <i class="fa-solid fa-globe"></i>
                            <span>{{ app()->getLocale() === 'fr' ? 'English (EN)' : 'Français (FR)' }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form Content Body -->
            <div class="login-wakatobi-form-body">
                <!-- Large Bold Title "Welcome Back" on 2 lines -->
                <h1 class="login-wakatobi-title">
                    Welcome<br>Back
                </h1>

                <!-- Validation Alerts -->
                @if ($errors->any())
                    <div class="alert alert-error" style="border-radius: 8px; padding: 10px 14px; font-size: 0.82rem; margin-bottom: 16px;">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-top: 2px;"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success" style="border-radius: 8px; padding: 10px 14px; font-size: 0.82rem; margin-bottom: 16px;">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf

                    <!-- Email Field -->
                    <div class="login-wakatobi-field-group">
                        <label for="email" class="login-wakatobi-label">{{ __('Email') }}</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="{{ __('Enter your email') }}" class="login-wakatobi-input">
                    </div>

                    <!-- Password Field -->
                    <div class="login-wakatobi-field-group">
                        <label for="password" class="login-wakatobi-label">{{ __('Password') }}</label>
                        <div class="login-wakatobi-input-wrapper">
                            <input type="password" id="password" name="password" required placeholder="{{ __('Enter your password') }}" class="login-wakatobi-input">
                            <button type="button" class="login-wakatobi-pwd-toggle" onclick="togglePasswordVisibility('password', this)" aria-label="{{ __('Afficher / Masquer le mot de passe') }}" title="{{ __('Afficher / Masquer le mot de passe') }}">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password Row -->
                    <div class="login-wakatobi-row">
                        <label class="login-wakatobi-checkbox-label">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>{{ __('Remember me') }}</span>
                        </label>

                        <a href="javascript:void(0)" onclick="alert('{{ __('Pour réinitialiser votre mot de passe, contactez l\'assistance Real Express Voyages au +237 699 001 002 ou rapprochez-vous de votre gare de départ.') }}')" class="login-wakatobi-forgot">
                            {{ __('Forgot Password') }}
                        </a>
                    </div>

                    <!-- Sign In Primary Button -->
                    <button type="submit" class="login-wakatobi-btn-primary">
                        {{ __('Sign in') }}
                    </button>
                </form>

                <!-- Divider "Or" -->
                <div class="login-wakatobi-divider">
                    <span>{{ __('Or') }}</span>
                </div>

                <!-- Sign in with Google Button -->
                <a href="javascript:void(0)" onclick="alert('{{ __('La connexion avec Google sera bientôt disponible.') }}')" class="login-wakatobi-btn-google">
                    <svg class="google-icon" width="18" height="18" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span>{{ __('Sign in with Google') }}</span>
                </a>

                <!-- Register switch prompt -->
                <div class="login-wakatobi-switch-link">
                    {{ __('Don\'t have an account?') }}
                    <a href="{{ route('register.passenger') }}">{{ __('Sign Up') }}</a>
                </div>
            </div>

            <!-- Bottom-Right Decorative Radial Lines & Dark Quarter Segment -->
            <div class="login-wakatobi-corner-graphic" aria-hidden="true">
                <svg viewBox="0 0 180 180" fill="none">
                    <path d="M 0 180 A 180 180 0 0 1 180 0" stroke="currentColor" stroke-width="1.2" />
                    <path d="M 22 180 A 158 158 0 0 1 180 22" stroke="currentColor" stroke-width="1.2" />
                    <path d="M 44 180 A 136 136 0 0 1 180 44" stroke="currentColor" stroke-width="1.2" />
                    <path d="M 66 180 A 114 114 0 0 1 180 66" stroke="currentColor" stroke-width="1.2" />
                    <path d="M 88 180 A 92 92 0 0 1 180 88 L 180 180 Z" fill="currentColor" />
                </svg>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.className = 'fa-solid fa-eye';
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.className = 'fa-solid fa-eye-slash';
        }
    }
}

function toggleLoginMenu(event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('loginMenuDropdown');
    if (dropdown) {
        dropdown.classList.toggle('is-open');
    }
}

document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('loginMenuDropdown');
    const btn = document.getElementById('loginMenuBtn');
    if (dropdown && dropdown.classList.contains('is-open')) {
        if (!dropdown.contains(e.target) && (!btn || !btn.contains(e.target))) {
            dropdown.classList.remove('is-open');
        }
    }
});
</script>
@endsection
