@extends('layouts.app')

@section('title', __('Sign Up - Real Express Voyages'))

@section('content')
<div class="login-scenic-wrapper">
    <div class="login-scenic-card login-scenic-card-register">
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

                    @include('components.bilingual-switcher')
                </div>

                <div></div>
            </div>
        </div>

        <!-- Right Side: Clean White Signup Panel Matching the Reference Format Exactly -->
        <div class="login-scenic-panel">
            <!-- Golden "Sign Up" Title & Warm "Welcome" Subtitle -->
            <h1 class="login-title-gold">{{ __('Sign Up') }}</h1>
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

            <!-- Main Sign-Up Form -->
            <form action="{{ route('register.passenger.submit') }}" method="POST">
                @csrf

                <!-- 1. Full Name Input (Pill Shaped with User Icon) -->
                <div class="login-pill-field">
                    <i class="fa-regular fa-user login-field-icon"></i>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="{{ __('Full Name') }}">
                </div>

                <!-- 2. Phone Number Input (Pill Shaped with Phone Icon) -->
                <div class="login-pill-field">
                    <i class="fa-solid fa-phone login-field-icon"></i>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="{{ __('Phone Number (+237...)') }}">
                </div>

                <!-- 3. Email Address Input (Pill Shaped with Envelope Icon) -->
                <div class="login-pill-field">
                    <i class="fa-regular fa-envelope login-field-icon"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="{{ __('Email Address') }}">
                </div>

                <!-- 4. Password Input (Pill Shaped with Lock Icon & Eye Toggle) -->
                <div class="login-pill-field">
                    <i class="fa-solid fa-lock login-field-icon"></i>
                    <input type="password" id="password" name="password" required placeholder="{{ __('Password (min 8 chars, 1 number)') }}">
                    <button type="button" class="login-pwd-toggle" onclick="togglePasswordVisibility('password', this)" aria-label="{{ __('Afficher / Masquer le mot de passe') }}" title="{{ __('Afficher / Masquer le mot de passe') }}">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>

                <!-- 5. Password Confirmation Input (Pill Shaped with Shield Icon & Eye Toggle) -->
                <div class="login-pill-field">
                    <i class="fa-solid fa-shield-halved login-field-icon"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="{{ __('Confirm Password') }}">
                    <button type="button" class="login-pwd-toggle" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="{{ __('Afficher / Masquer la confirmation') }}" title="{{ __('Afficher / Masquer la confirmation') }}">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>

                <!-- 6. Options Row: Terms Checkbox & Already Registered Link -->
                <div class="login-options-row">
                    <label class="login-remember-label">
                        <input type="checkbox" name="terms" id="terms" checked style="accent-color: #111D17; width: 16px; height: 16px; cursor: pointer; border-radius: 4px;">
                        <span>{{ __('I accept the Terms') }}</span>
                    </label>

                    <a href="{{ route('login') }}" class="login-forgot-link">
                        {{ __('Already registered? Sign In') }}
                    </a>
                </div>

                <!-- 7. Dark Pill Sign Up Action Button (Right Aligned Exactly As in Reference Image) -->
                <div style="display: flex; justify-content: flex-end; margin-top: 14px;">
                    <button type="submit" class="login-btn-action">
                        <span>{{ __('Sign Up') }}</span>
                    </button>
                </div>
            </form>

            <!-- Bottom Prompt to Switch to Sign In -->
            <div class="login-switch-prompt">
                {{ __('Already have an account?') }} 
                <a href="{{ route('login') }}">{{ __('Sign In') }}</a>
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
