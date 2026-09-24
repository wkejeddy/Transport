@extends('layouts.app')

@section('title', __('Configuration Google OAuth - Real Voyage'))

@section('content')
<div class="container-sm" style="padding-top: 50px; padding-bottom: 70px;">
    <div class="card card-glass" style="padding: 40px; box-shadow: var(--shadow-xl); border-radius: var(--radius-xl); border: 1.5px solid var(--border-color);">
        
        <!-- Header with Google G Logo -->
        <div style="text-align: center; margin-bottom: 26px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #FFFFFF; box-shadow: var(--shadow-md); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; border: 1px solid var(--border-color);">
                <svg width="32" height="32" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
            </div>
            
            @if(!empty($hasCredentials))
                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #DCFCE7; color: #166534; border-radius: 999px; font-size: 0.8rem; font-weight: 800; margin-bottom: 12px;">
                    <i class="fa-solid fa-circle-check"></i> {{ __('Identifiants Google Configurés') }}
                </div>
            @else
                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #FEF3C7; color: #92400E; border-radius: 999px; font-size: 0.8rem; font-weight: 800; margin-bottom: 12px;">
                    <i class="fa-solid fa-clock"></i> {{ __('En attente de vos clés API Google') }}
                </div>
            @endif

            <h2 style="font-size: 1.7rem; color: var(--text-heading); font-weight: 800;">{{ __('Système Continuer avec Google') }}</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 580px; margin: 8px auto 0;">
                {{ __('Le système d\'authentification OAuth 2.0 est 100% prêt. Vous pouvez renseigner vos identifiants ci-dessous ou tester immédiatement la connexion.') }}
            </p>
        </div>

        @if(session('success'))
            <div style="background: #DCFCE7; color: #166534; padding: 14px 18px; border-radius: var(--radius-md); font-weight: 600; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check" style="font-size: 1.1rem;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div style="background: #FEE2E2; color: #991B1B; padding: 14px 18px; border-radius: var(--radius-md); font-weight: 600; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem;"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Step 1: Authorized Redirect URI Box -->
        <div style="background: var(--bg-surface); border: 1.5px solid var(--primary-100); border-radius: var(--radius-lg); padding: 22px; margin-bottom: 24px;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-link"></i> {{ __('1. Votre URI de Redirection Autorisée (À copier dans Google Console)') }}
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="redirectUriInput" class="form-control" value="{{ $redirectUri ?? url('/auth/google/callback') }}" readonly style="font-family: monospace; font-weight: 700; background: var(--bg-card); color: var(--text-heading); font-size: 0.95rem;">
                <button type="button" onclick="copyRedirectUri()" id="btnCopyUri" class="btn btn-primary" style="white-space: nowrap; gap: 6px;">
                    <i class="fa-solid fa-copy"></i> <span>{{ __('Copier') }}</span>
                </button>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;">
                {{ __('Dans Google Cloud Console > Identifiants > ID client OAuth 2.0 > URIs de redirection autorisés, collez cette URL exacte.') }}
            </div>
        </div>

        <!-- Step 2: Interactive Form to Paste API Keys Directly -->
        <div style="background: var(--bg-surface); border: 1.5px solid var(--border-color); border-radius: var(--radius-lg); padding: 22px; margin-bottom: 28px;">
            <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-heading); text-transform: uppercase; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-key" style="color: #0284C7;"></i> {{ __('2. Renseignez vos identifiants Google API') }}
            </div>

            <form action="{{ route('auth.google.setup.save') }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" for="google_client_id" style="font-weight: 700;">{{ __('Google Client ID') }}</label>
                    <input type="text" id="google_client_id" name="google_client_id" class="form-control" style="font-family: monospace; font-size: 0.9rem;" value="{{ old('google_client_id', config('services.google.client_id')) }}" required placeholder="Ex: 123456789-abc.apps.googleusercontent.com">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="google_client_secret" style="font-weight: 700;">{{ __('Google Client Secret') }}</label>
                    <input type="password" id="google_client_secret" name="google_client_secret" class="form-control" style="font-family: monospace; font-size: 0.9rem;" value="{{ old('google_client_secret', config('services.google.client_secret')) }}" required placeholder="Ex: GOCSPX-xxxxxxxxxxxxxxxxxxxx">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 800; gap: 8px; justify-content: center;">
                    <i class="fa-solid fa-floppy-disk"></i> {{ __('Enregistrer et Activer Google OAuth') }}
                </button>
            </form>
        </div>

        <!-- Action Buttons Deck -->
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <a href="{{ route('auth.google', ['force_real' => 1]) }}" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; font-weight: 700; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                {{ __('Tester la Vraie Connexion Google') }}
            </a>

            <div style="display: flex; gap: 12px; justify-content: space-between; flex-wrap: wrap;">
                <a href="{{ route('auth.google.demo') }}" class="btn btn-outline" style="flex: 1; min-width: 220px; justify-content: center; gap: 6px; font-weight: 700; background: var(--bg-surface);">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary);"></i> {{ __('Tester en Mode Simulation (1-Clic)') }}
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline" style="flex: 1; min-width: 220px; justify-content: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Retour à la Connexion') }}
                </a>
            </div>
        </div>

    </div>
</div>

<script>
function copyRedirectUri() {
    const input = document.getElementById('redirectUriInput');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
    
    const btn = document.getElementById('btnCopyUri');
    btn.innerHTML = '<i class="fa-solid fa-check"></i> ' + "{{ __('Copié !') }}";
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = '<i class="fa-solid fa-copy"></i> ' + "{{ __('Copier') }}";
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2500);
}
</script>
@endsection
