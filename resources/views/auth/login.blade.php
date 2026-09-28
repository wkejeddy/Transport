@extends('layouts.app')

@section('title', __('Sign In - Real Express Voyages'))

@section('content')
<div class="login-scenic-wrapper">
    <div class="login-scenic-card">
        <!-- Left Side: Scenic Highway Landscape with White Coach on Sunlit Road -->
        <div class="login-scenic-visual">
            <div class="login-scenic-visual-overlay">
                <!-- Topbar with Brand Logo Link & Language Switcher -->
                <div class="login-scenic-topbar">
                    <a href="{{ route('home') }}" class="login-scenic-badge" title="{{ __('Retour à l\'accueil') }}">
                        <i class="fa-solid fa-arrow-left" style="font-size: 0.75rem;"></i>
                        <img src="{{ asset('images/logo.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="Logo" style="width: 18px; height: 18px; object-fit: contain;">
                        <span>{{ config('app.name', 'Real Express Voyages') }}</span>
                    </a>

                    <a href="{{ route('lang.swap', app()->getLocale() === 'fr' ? 'en' : 'fr') }}" class="login-scenic-lang" title="{{ app()->getLocale() === 'fr' ? __('Passer en Anglais') : __('Switch to French') }}">
                        <i class="fa-solid fa-globe"></i>
                        <span>{{ app()->getLocale() === 'fr' ? 'EN' : 'FR' }}</span>
                    </a>
                </div>

                <div></div>
            </div>
        </div>

        <!-- Right Side: Clean White Login Panel Matching the Reference Format Exactly -->
        <div class="login-scenic-panel">
            <!-- Golden "Sign In" Title & Warm "Welcome" Subtitle -->
            <h1 class="login-title-gold">{{ __('Sign In') }}</h1>
            <div class="login-subtitle-welcome">{{ __('Welcome') }}</div>

            <!-- Validation Errors & Flash Messages -->
            @if ($errors->any())
                <div class="alert alert-error" style="border-radius: 14px; padding: 10px 14px; font-size: 0.82rem; margin-bottom: 18px;">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-top: 2px;"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success" style="border-radius: 14px; padding: 10px 14px; font-size: 0.82rem; margin-bottom: 18px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <!-- Main Sign-In Form -->
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                <!-- 1. Email Address Input (Pill Shaped with Envelope Icon) -->
                <div class="login-pill-field">
                    <i class="fa-regular fa-envelope login-field-icon"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="{{ __('Email Address') }}">
                </div>

                <!-- 2. Password Input (Pill Shaped with Lock Icon & Eye Toggle) -->
                <div class="login-pill-field">
                    <i class="fa-solid fa-lock login-field-icon"></i>
                    <input type="password" id="password" name="password" required placeholder="{{ __('Password') }}">
                    <button type="button" class="login-pwd-toggle" onclick="togglePasswordVisibility('password', this)" aria-label="{{ __('Afficher / Masquer le mot de passe') }}" title="{{ __('Afficher / Masquer le mot de passe') }}">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>

                <!-- 3. Remember Me & Forgot Password Row -->
                <div class="login-options-row">
                    <label class="login-remember-label">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="accent-color: #111D17; width: 16px; height: 16px; cursor: pointer; border-radius: 4px;">
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    <a href="javascript:void(0)" onclick="alert('{{ __('Pour réinitialiser votre mot de passe, contactez l\'assistance Real Express Voyages au +237 699 001 002 ou rapprochez-vous de votre gare de départ.') }}')" class="login-forgot-link">
                        {{ __('Forgot Password?') }}
                    </a>
                </div>

                <!-- 4. Dark Pill Login Action Button (Right Aligned Exactly As in Reference Image) -->
                <div style="display: flex; justify-content: flex-end; margin-top: 14px;">
                    <button type="submit" class="login-btn-action">
                        <span>{{ __('Login') }}</span>
                    </button>
                </div>
            </form>

            <!-- Bottom Prompt to Switch to Sign Up -->
            <div class="login-switch-prompt">
                {{ __('Don\'t have an account?') }} 
                <a href="{{ route('register.passenger') }}">{{ __('Sign Up') }}</a>
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
</script>
@endsection
