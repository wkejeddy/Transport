@extends('layouts.app')

@section('title', __('Connexion - Real Voyage Transport S.A.'))

@section('content')
<div style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; padding: 40px 16px;">
    <div class="card card-glass" style="max-width: 1000px; width: 100%; padding: 0; overflow: hidden; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); border: 1.5px solid var(--border-color);">
        <div class="grid grid-cols-2" style="gap: 0;">
            <!-- Left: Hero Illustration & Highlights -->
            <div style="background: var(--primary); color: white; padding: 48px 40px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                <div style="position: relative; z-index: 2;">
                    <div class="brand-logo" style="color: white; margin-bottom: 24px;">
                        <div class="brand-icon" style="background: rgba(255,255,255,0.15); box-shadow: none;">
                            <i class="fa-solid fa-bus"></i>
                        </div>
                        Real Voyage
                    </div>

                    <h2 style="font-size: 1.8rem; font-weight: 800; line-height: 1.25; color: white; margin-bottom: 16px;">
                        {{ __('Compagnie Nationale de Transport Interurbain & Fret') }}
                    </h2>

                    <p style="color: #E2E8F0; font-size: 0.92rem; line-height: 1.6; margin-bottom: 30px;">
                        {{ __('Accédez à votre espace voyageur ou d\'exploitation pour vos billets d\'autocars VIP (75/80 places), gestion du fret et suivi des rotations.') }}
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.88rem; color: #F1F5F9;">
                            <i class="fa-solid fa-circle-check" style="color: #6EE7B7; font-size: 1rem;"></i>
                            <span>{{ __('Paiement sécurisé par') }} <strong>Orange Money</strong> & <strong>MTN MoMo</strong></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.88rem; color: #F1F5F9;">
                            <i class="fa-solid fa-circle-check" style="color: #6EE7B7; font-size: 1rem;"></i>
                            <span>{{ __('Blocage exclusif des places assises') }} <strong>{{ __('2 minutes') }}</strong></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.88rem; color: #F1F5F9;">
                            <i class="fa-solid fa-circle-check" style="color: #6EE7B7; font-size: 1rem;"></i>
                            <span>{{ __('Service client & assistance réactive sous') }} <strong>{{ __('48 heures') }}</strong></span>
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 20px; font-size: 0.8rem; color: #94A3B8; position: relative; z-index: 2;">
                    {{ __('Real Voyage S.A. • Homologation Ministérielle') }}
                </div>
            </div>

            <!-- Right: Login Form & 1-Click Role Switcher -->
            <div style="padding: 44px 40px; background: var(--bg-card);">
                <div style="margin-bottom: 24px;">
                    <h2 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">{{ __('Connexion') }}</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">
                        {{ __('Connectez-vous pour accéder à vos billets ou à votre terminal.') }}
                    </p>
                </div>

                <!-- 1-Click Quick Demo Profiles Pills -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 24px;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary);"></i> {{ __('Profils Accès Rapide (1-Clic) :') }}
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <button type="button" onclick="fillCredentials('passenger@realvoyage.cm', 'password123')" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 6px 10px;">
                            <i class="fa-solid fa-user" style="color: var(--primary);"></i> {{ __('Passager') }}
                        </button>
                        <button type="button" onclick="fillCredentials('manager.douala@realvoyage.cm', 'password123')" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 6px 10px;">
                            <i class="fa-solid fa-building" style="color: var(--road-color);"></i> {{ __('Chef Gare DLA') }}
                        </button>
                        <button type="button" onclick="fillCredentials('manager.yaounde@realvoyage.cm', 'password123')" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 6px 10px;">
                            <i class="fa-solid fa-building" style="color: var(--road-color);"></i> {{ __('Chef Gare YDE') }}
                        </button>
                        <button type="button" onclick="fillCredentials('wkejeddy@gmail.com', 'Ab12345678.')" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 6px 10px;">
                            <i class="fa-solid fa-shield" style="color: var(--secondary);"></i> {{ __('Admin (Wkej Eddy)') }}
                        </button>
                    </div>
                </div>

                <!-- Google OAuth Sign-In Button -->
                <div style="margin-bottom: 20px;">
                    <a href="{{ route('auth.google', array_filter(['redirect' => request('redirect')])) }}" class="btn btn-outline btn-lg" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 700; border-color: var(--border-color); background: var(--bg-card); color: var(--text-main); transition: all 0.2s ease;">
                        <svg width="18" height="18" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        {{ __('Continuer avec Google') }}
                    </a>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding: 0 4px;">
                        <a href="{{ route('auth.google.setup') }}" style="font-size: 0.74rem; color: var(--text-muted); text-decoration: none;">
                            <i class="fa-solid fa-gear"></i> {{ __('Configuration API') }}
                        </a>
                        <a href="{{ route('auth.google.demo') }}" style="font-size: 0.74rem; color: var(--primary); text-decoration: none; font-weight: 600;">
                            <i class="fa-solid fa-bolt"></i> {{ __('Test 1-Clic (Simulateur Google)') }}
                        </a>
                    </div>
                </div>

                <!-- Divider -->
                <div style="display: flex; align-items: center; margin: 18px 0; color: var(--text-muted); font-size: 0.8rem;">
                    <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
                    <span style="padding: 0 12px; font-weight: 600;">{{ __('ou par e-mail') }}</span>
                    <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
                </div>

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="email">{{ __('Adresse E-mail') }}</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="votre.email@exemple.cm">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password"><i class="fa-solid fa-lock" style="color: var(--primary);"></i> {{ __('Mot de passe') }}</label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="{{ __('Afficher / Masquer le mot de passe') }}" title="{{ __('Afficher / Masquer le mot de passe') }}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800; margin-top: 8px;">
                        <i class="fa-solid fa-right-to-bracket"></i> {{ __('Se Connecter') }}
                    </button>

                    <div style="margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.75rem; color: var(--text-muted); background: var(--bg-surface); padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <i class="fa-solid fa-shield-halved" style="color: var(--primary); font-size: 0.9rem;"></i>
                        <span>{{ __('Chiffrement sécurisé des mots de passe • Protection anti-bruteforce active') }}</span>
                    </div>
                </form>

                <div style="text-align: center; margin-top: 24px; font-size: 0.85rem; color: var(--text-muted);">
                    {{ __('Pas encore de compte ?') }} 
                    <a href="{{ route('register.passenger') }}" style="color: var(--primary); font-weight: 700;">{{ __('Créer un compte Passager') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillCredentials(email, pwd) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pwd;
}
</script>
@endsection
