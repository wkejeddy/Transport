@extends('layouts.app')

@section('title', __('Inscription Passager'))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <div class="card card-glass" style="padding: 36px; box-shadow: var(--shadow-lg); border-radius: var(--radius-xl);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--primary-50); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 12px;">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2 style="font-size: 1.6rem; color: var(--text-heading);">{{ __('Créer un Compte Passager') }}</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
                {{ __('Réservez vos voyages en bus ou train, suivez vos colis et payez par Orange Money / MTN MoMo.') }}
            </p>
        </div>

        <!-- Google OAuth Sign-Up Button -->
        <div style="margin-bottom: 20px;">
            <a href="{{ route('auth.google', array_filter(['redirect' => request('redirect')])) }}" class="btn btn-outline btn-lg" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 700; border-color: var(--border-color); background: var(--bg-card); color: var(--text-main); transition: all 0.2s ease;">
                <svg width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                {{ __('S\'inscrire avec Google') }}
            </a>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding: 0 4px;">
                <a href="{{ route('auth.google.setup') }}" style="font-size: 0.74rem; color: var(--text-muted); text-decoration: none;">
                    <i class="fa-solid fa-gear"></i> {{ __('Configuration API') }}
                </a>
                <a href="{{ route('auth.google.demo') }}" style="font-size: 0.74rem; color: var(--primary); text-decoration: none; font-weight: 600;">
                    <i class="fa-solid fa-bolt"></i> {{ __('Inscription Démo (1-Clic)') }}
                </a>
            </div>
        </div>

        <!-- Divider -->
        <div style="display: flex; align-items: center; margin: 18px 0; color: var(--text-muted); font-size: 0.8rem;">
            <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
            <span style="padding: 0 12px; font-weight: 600;">{{ __('ou formulaire standard') }}</span>
            <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
        </div>

        <form action="{{ route('register.passenger.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="name"><i class="fa-solid fa-user" style="color: var(--primary);"></i> {{ __('Nom complet (tel que sur CNI/Passeport)') }}</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="{{ __('Ex: Jean Paul Kamga') }}">
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="email"><i class="fa-solid fa-envelope" style="color: var(--primary);"></i> {{ __('Adresse E-mail') }}</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="jean.kamga@gmail.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone"><i class="fa-solid fa-phone" style="color: var(--primary);"></i> {{ __('Numéro de Téléphone (Orange / MTN)') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" required placeholder="+237 699 123 456">
                </div>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="password"><i class="fa-solid fa-lock" style="color: var(--primary);"></i> {{ __('Mot de passe') }}</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required placeholder="{{ __('Minimum 8 caractères') }}">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="{{ __('Afficher / Masquer le mot de passe') }}" title="{{ __('Afficher / Masquer le mot de passe') }}">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation"><i class="fa-solid fa-check-double" style="color: var(--primary);"></i> {{ __('Confirmer le mot de passe') }}</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="{{ __('Confirmez le mot de passe') }}">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="{{ __('Afficher / Masquer la confirmation') }}" title="{{ __('Afficher / Masquer la confirmation') }}">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatchFeedback" class="password-match-feedback"></div>
                </div>
            </div>

            <!-- Password Security Checklist & Strength Meter -->
            <div class="password-security-box" id="passengerSecurityBox">
                <div class="password-strength-meta">
                    <span style="color: var(--text-muted);"><i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> {{ __('Sécurité du mot de passe :') }}</span>
                    <span id="strengthLabel" style="color: var(--text-muted); font-weight: 800;">{{ __('À renseigner') }}</span>
                </div>
                <div class="password-meter-track">
                    <div id="strengthFill" class="password-meter-fill strength-0"></div>
                </div>
                <ul class="password-checklist">
                    <li class="password-check-item" id="ruleLength">
                        <i class="fa-solid fa-circle-dot"></i> <span>{{ __('8 caractères minimum') }}</span>
                    </li>
                    <li class="password-check-item" id="ruleLetters">
                        <i class="fa-solid fa-circle-dot"></i> <span>{{ __('Lettre majuscule & minuscule') }}</span>
                    </li>
                    <li class="password-check-item" id="ruleNumbers">
                        <i class="fa-solid fa-circle-dot"></i> <span>{{ __('Au moins un chiffre (0-9)') }}</span>
                    </li>
                    <li class="password-check-item" id="ruleSpecial">
                        <i class="fa-solid fa-circle-dot"></i> <span>{{ __('Caractère spécial recommandé (@$!%*?)') }}</span>
                    </li>
                </ul>
            </div>

            <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 16px; margin-top: 18px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-muted);">
                <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i>
                {{ __('En créant un compte, vous bénéficierez de l\'émission automatique d\'E-Billets avec QR code et de la garantie de remboursement en cas de litige arbitré.') }}
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                <i class="fa-solid fa-check"></i> {{ __('Créer mon compte Passager') }}
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted);">
            {{ __('Vous avez déjà un compte ?') }} 
            <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 700;">{{ __('Connectez-vous ici') }}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pwd = document.getElementById('password');
    const pwdConf = document.getElementById('password_confirmation');
    const feedback = document.getElementById('passwordMatchFeedback');

    const strengthFill = document.getElementById('strengthFill');
    const strengthLabel = document.getElementById('strengthLabel');
    const ruleLength = document.getElementById('ruleLength');
    const ruleLetters = document.getElementById('ruleLetters');
    const ruleNumbers = document.getElementById('ruleNumbers');
    const ruleSpecial = document.getElementById('ruleSpecial');

    function updateRule(el, valid) {
        if (!el) return;
        const icon = el.querySelector('i');
        if (valid) {
            el.classList.add('valid');
            if (icon) icon.className = 'fa-solid fa-circle-check';
        } else {
            el.classList.remove('valid');
            if (icon) icon.className = 'fa-solid fa-circle-dot';
        }
    }

    function evaluatePassword() {
        if (!pwd) return;
        const val = pwd.value;

        if (val.length === 0) {
            if (strengthFill) {
                strengthFill.className = 'password-meter-fill strength-0';
            }
            if (strengthLabel) {
                strengthLabel.textContent = "{{ __('À renseigner') }}";
                strengthLabel.style.color = 'var(--text-muted)';
            }
            updateRule(ruleLength, false);
            updateRule(ruleLetters, false);
            updateRule(ruleNumbers, false);
            updateRule(ruleSpecial, false);
            return;
        }

        const hasLen = val.length >= 8;
        const hasLetters = (/[a-z]/.test(val) && /[A-Z]/.test(val)) || (/[a-zA-Z]/.test(val) && val.length >= 10);
        const hasNum = /\d/.test(val);
        const hasSpec = /[^A-Za-z0-9]/.test(val);

        updateRule(ruleLength, hasLen);
        updateRule(ruleLetters, hasLetters);
        updateRule(ruleNumbers, hasNum);
        updateRule(ruleSpecial, hasSpec);

        let score = 0;
        if (hasLen) score++;
        if (hasLetters) score++;
        if (hasNum) score++;
        if (hasSpec) score++;

        if (strengthFill) {
            strengthFill.className = 'password-meter-fill strength-' + score;
        }

        if (strengthLabel) {
            if (score === 1) {
                strengthLabel.textContent = "{{ __('Faible') }}";
                strengthLabel.style.color = 'var(--danger)';
            } else if (score === 2) {
                strengthLabel.textContent = "{{ __('Moyen') }}";
                strengthLabel.style.color = 'var(--warning)';
            } else if (score === 3) {
                strengthLabel.textContent = "{{ __('Robuste') }}";
                strengthLabel.style.color = '#0284C7';
            } else if (score === 4) {
                strengthLabel.textContent = "{{ __('Très Sécurisé') }}";
                strengthLabel.style.color = 'var(--success)';
            } else {
                strengthLabel.textContent = "{{ __('Très Faible') }}";
                strengthLabel.style.color = 'var(--danger)';
            }
        }
    }

    function checkPasswordMatch() {
        if (!pwd || !pwdConf || !feedback) return;
        const val1 = pwd.value;
        const val2 = pwdConf.value;

        if (val2.length === 0) {
            feedback.className = 'password-match-feedback';
            feedback.innerHTML = '';
            feedback.style.display = 'none';
            return;
        }

        feedback.style.display = 'flex';
        if (val1 === val2) {
            feedback.className = 'password-match-feedback matched';
            feedback.innerHTML = '<i class="fa-solid fa-circle-check"></i> {{ __("Les mots de passe correspondent parfaitement") }}';
        } else {
            feedback.className = 'password-match-feedback mismatched';
            feedback.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> {{ __("Les mots de passe ne correspondent pas encore") }}';
        }
    }

    if (pwd) {
        pwd.addEventListener('input', function() {
            evaluatePassword();
            checkPasswordMatch();
        });
    }

    if (pwdConf) {
        pwdConf.addEventListener('input', checkPasswordMatch);
    }
});
</script>
@endsection
