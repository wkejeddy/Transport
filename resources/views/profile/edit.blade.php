@extends('layouts.app')

@section('title', __('Mon Profil & Sécurité du Compte'))

@section('content')
<div class="container" style="padding-top: 32px; padding-bottom: 60px; max-width: 1080px;">
    <!-- Breadcrumb & Back Navigation -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: var(--text-muted);">
            <a href="{{ route('home') }}" style="color: var(--text-muted);"><i class="fa-solid fa-house"></i> {{ __('Accueil') }}</a>
            <span>&rsaquo;</span>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" style="color: var(--text-muted);">{{ __('Supervision Admin') }}</a>
            @elseif(Auth::user()->isManager())
                <a href="{{ route('manager.dashboard') }}" style="color: var(--text-muted);">{{ __('Espace Agence') }}</a>
            @else
                <a href="{{ route('passenger.dashboard') }}" style="color: var(--text-muted);">{{ __('Mon Espace Voyageur') }}</a>
            @endif
            <span>&rsaquo;</span>
            <span style="color: var(--text-heading); font-weight: 700;">{{ __('Profil & Sécurité') }}</span>
        </div>

        <div>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Retour Tableau de Bord') }}
                </a>
            @elseif(Auth::user()->isManager())
                <a href="{{ route('manager.dashboard') }}" class="btn btn-sm btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Retour Tableau de Bord') }}
                </a>
            @else
                <a href="{{ route('passenger.dashboard') }}" class="btn btn-sm btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Retour Espace Voyageur') }}
                </a>
            @endif
        </div>
    </div>

    <!-- User Header Profile Card -->
    <div class="card card-glass" style="padding: 28px; margin-bottom: 32px; box-shadow: var(--shadow-md); border-radius: var(--radius-lg);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <!-- Current Profile Image or Initials -->
                <div id="headerAvatarContainer" style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; box-shadow: var(--shadow-md); border: 3px solid var(--bg-surface); overflow: hidden; flex-shrink: 0; position: relative;">
                    @if($user->avatar)
                        <img id="headerAvatarImg" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span id="headerAvatarInitials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    @endif
                </div>

                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-heading); margin: 0;">
                            {{ $user->name }}
                        </h1>
                        @if($user->isAdmin())
                            <span class="badge badge-danger"><i class="fa-solid fa-shield-halved"></i> {{ __('Administrateur') }}</span>
                        @elseif($user->isManager())
                            <span class="badge badge-road"><i class="fa-solid fa-building"></i> {{ __('Manager Agence') }}</span>
                        @else
                            <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> {{ __('Passager Certifié') }}</span>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; font-size: 0.85rem; color: var(--text-muted); flex-wrap: wrap;">
                        <span><i class="fa-solid fa-envelope"></i> {{ $user->email }}</span>
                        @if($user->phone)
                            <span><i class="fa-solid fa-phone"></i> {{ $user->phone }}</span>
                        @endif
                        <span><i class="fa-solid fa-calendar-check"></i> {{ __('Membre depuis') }} {{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 8px;">
                <span class="badge badge-success" style="font-weight: 700; padding: 6px 12px;">
                    <i class="fa-solid fa-signal"></i> {{ __('Compte Actif & Homologué') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Profile Settings Grid (2 Columns: Personal Info & Avatar | Password & Security) -->
    <div class="grid grid-cols-2" style="gap: 28px; align-items: start;">
        <!-- Left Box: Personal Info & Avatar Upload -->
        <div class="card" style="padding: 28px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-heading); margin: 0;">
                        {{ __('Informations Personnelles') }}
                    </h2>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                        {{ __('Modifiez votre nom, votre photo de profil et vos coordonnées.') }}
                    </p>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileUpdateForm">
                @csrf
                @method('PUT')

                <!-- Avatar Upload Section -->
                <div style="margin-bottom: 24px; padding: 18px; background: var(--bg-surface); border: 1px dashed var(--border-color); border-radius: var(--radius-md);">
                    <label class="form-label" style="font-size: 0.88rem; font-weight: 700; color: var(--text-heading); margin-bottom: 12px; display: block;">
                        <i class="fa-solid fa-image" style="color: var(--primary);"></i> {{ __('Photo de Profil (Avatar)') }}
                    </label>

                    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <!-- Live Avatar Preview Container -->
                        <div style="position: relative; width: 90px; height: 90px; border-radius: 50%; overflow: hidden; box-shadow: var(--shadow-md); border: 3px solid var(--border-color); background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; flex-shrink: 0;">
                            @if($user->avatar)
                                <img id="avatarPreview" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <img id="avatarPreview" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <span id="avatarPlaceholderInitials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            @endif
                        </div>

                        <!-- Upload Trigger & Actions -->
                        <div style="flex: 1; min-width: 180px;">
                            <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/webp" style="display: none;">
                            
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 8px;">
                                <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatarInput').click();">
                                    <i class="fa-solid fa-camera"></i> {{ __('Choisir une photo') }}
                                </button>

                                @if($user->avatar)
                                    <button type="button" class="btn btn-sm btn-outline" style="color: var(--danger); border-color: var(--danger-border);" onclick="confirmRemoveAvatar();">
                                        <i class="fa-solid fa-trash-can"></i> {{ __('Supprimer') }}
                                    </button>
                                @endif
                            </div>

                            <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
                                {{ __('Formats acceptés : JPG, PNG ou WebP. Taille maximale recommandée : 2 Mo.') }}
                            </div>
                            <div id="selectedFileName" style="font-size: 0.78rem; font-weight: 700; color: var(--primary); margin-top: 4px; display: none;"></div>
                        </div>
                    </div>
                </div>

                <!-- Full Name Field -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" for="name" style="font-weight: 700;">
                        <i class="fa-solid fa-user" style="color: var(--primary);"></i> {{ __('Nom & Prénom *') }}
                    </label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required placeholder="{{ __('Ex: Jean Ekwalla') }}" style="font-size: 0.95rem;">
                    @error('name')
                        <span style="color: var(--danger); font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Address Field -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" for="email" style="font-weight: 700;">
                        <i class="fa-solid fa-envelope" style="color: var(--primary);"></i> {{ __('Adresse E-mail *') }}
                    </label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required placeholder="{{ __('Ex: utilisateur@domaine.cm') }}" style="font-size: 0.95rem;">
                    @error('email')
                        <span style="color: var(--danger); font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Phone Number Field -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" for="phone" style="font-weight: 700;">
                        <i class="fa-solid fa-phone" style="color: var(--primary);"></i> {{ __('Numéro de Téléphone (Mobile Money / Alertes SMS)') }}
                    </label>
                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="{{ __('Ex: +237 699 00 11 22') }}" style="font-size: 0.95rem;">
                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; display: block;">
                        {{ __('Recommandé pour recevoir les codes OTP de retrait colis et les confirmations de billets.') }}
                    </span>
                    @error('phone')
                        <span style="color: var(--danger); font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; font-weight: 800;">
                    <i class="fa-solid fa-floppy-disk"></i> {{ __('Enregistrer les Modifications') }}
                </button>
            </form>

            <!-- Separate Hidden Form for Avatar Removal -->
            @if($user->avatar)
                <form id="removeAvatarForm" action="{{ route('profile.avatar.destroy') }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>

        <!-- Right Box: Security & Password Change -->
        <div class="card" style="padding: 28px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: rgba(239, 68, 68, 0.1); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--text-heading); margin: 0;">
                        {{ __('Sécurité & Mot de Passe') }}
                    </h2>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                        {{ __('Changez régulièrement votre mot de passe pour garantir la sécurité de votre compte.') }}
                    </p>
                </div>
            </div>

            <form action="{{ route('profile.password.update') }}" method="POST" id="passwordChangeForm">
                @csrf
                @method('PUT')

                <!-- Current Password Field -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" for="current_password" style="font-weight: 700;">
                        <i class="fa-solid fa-lock-open" style="color: var(--danger);"></i> {{ __('Mot de Passe Actuel *') }}
                    </label>
                    <div style="position: relative;">
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required placeholder="{{ __('Votre mot de passe actuel') }}" style="padding-right: 42px;">
                        <button type="button" onclick="togglePasswordVisibility('current_password', 'toggleCurrentIcon')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                            <i class="fa-solid fa-eye" id="toggleCurrentIcon"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <span style="color: var(--danger); font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password Field -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" for="password" style="font-weight: 700;">
                        <i class="fa-solid fa-key" style="color: var(--primary);"></i> {{ __('Nouveau Mot de Passe *') }}
                    </label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="{{ __('Au moins 8 caractères avec lettres et chiffres') }}" style="padding-right: 42px;">
                        <button type="button" onclick="togglePasswordVisibility('password', 'toggleNewIcon')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                            <i class="fa-solid fa-eye" id="toggleNewIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span style="color: var(--danger); font-size: 0.8rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror

                    <!-- Password Requirement Checklist -->
                    <div style="margin-top: 10px; background: var(--bg-surface); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 0.78rem;">
                        <div style="font-weight: 700; color: var(--text-heading); margin-bottom: 6px;">{{ __('Exigences de sécurité :') }}</div>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px;">
                            <li id="rule-min-length" style="color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                                <i class="fa-regular fa-circle" style="font-size: 0.7rem;"></i> {{ __('8 caractères minimum') }}
                            </li>
                            <li id="rule-letter" style="color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                                <i class="fa-regular fa-circle" style="font-size: 0.7rem;"></i> {{ __('Au moins une lettre alphabétique') }}
                            </li>
                            <li id="rule-number" style="color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                                <i class="fa-regular fa-circle" style="font-size: 0.7rem;"></i> {{ __('Au moins un chiffre (0-9)') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Confirm New Password Field -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" for="password_confirmation" style="font-weight: 700;">
                        <i class="fa-solid fa-circle-check" style="color: var(--primary);"></i> {{ __('Confirmer le Nouveau Mot de Passe *') }}
                    </label>
                    <div style="position: relative;">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="{{ __('Répétez le nouveau mot de passe') }}" style="padding-right: 42px;">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'toggleConfIcon')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                            <i class="fa-solid fa-eye" id="toggleConfIcon"></i>
                        </button>
                    </div>
                    <div id="matchFeedback" style="font-size: 0.8rem; margin-top: 4px; display: none;"></div>
                </div>

                <button type="submit" id="submitPasswordBtn" class="btn btn-danger btn-lg" style="width: 100%; justify-content: center; font-weight: 800;">
                    <i class="fa-solid fa-lock"></i> {{ __('Mettre à Jour le Mot de Passe') }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Toggle Password Visibility (Eye Icon)
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    }

    // Live Avatar Image Preview & Validation
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarInitials = document.getElementById('avatarPlaceholderInitials');
    const fileNameDisplay = document.getElementById('selectedFileName');
    const headerAvatarContainer = document.getElementById('headerAvatarContainer');

    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Check size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert("{{ __('Le fichier sélectionné dépasse la limite autorisée de 2 Mo. Veuillez en choisir un autre.') }}");
                avatarInput.value = '';
                return;
            }

            // Show selected filename
            if (fileNameDisplay) {
                fileNameDisplay.textContent = "{{ __('Sélectionné :') }} " + file.name;
                fileNameDisplay.style.display = 'block';
            }

            // Preview via FileReader
            const reader = new FileReader();
            reader.onload = function(event) {
                if (avatarPreview) {
                    avatarPreview.src = event.target.result;
                    avatarPreview.style.display = 'block';
                }
                if (avatarInitials) {
                    avatarInitials.style.display = 'none';
                }
                // Also update header preview immediately
                if (headerAvatarContainer) {
                    let headerImg = document.getElementById('headerAvatarImg');
                    if (!headerImg) {
                        const headerInitials = document.getElementById('headerAvatarInitials');
                        if (headerInitials) headerInitials.style.display = 'none';
                        headerImg = document.createElement('img');
                        headerImg.id = 'headerAvatarImg';
                        headerImg.style.width = '100%';
                        headerImg.style.height = '100%';
                        headerImg.style.objectFit = 'cover';
                        headerAvatarContainer.appendChild(headerImg);
                    }
                    headerImg.src = event.target.result;
                    headerImg.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    // Confirm Avatar Removal
    function confirmRemoveAvatar() {
        if (confirm("{{ __('Êtes-vous certain de vouloir supprimer votre photo de profil ?') }}")) {
            document.getElementById('removeAvatarForm').submit();
        }
    }

    // Real-time Password Rules & Match Validation
    const pwdInput = document.getElementById('password');
    const pwdConf = document.getElementById('password_confirmation');
    const ruleMinLength = document.getElementById('rule-min-length');
    const ruleLetter = document.getElementById('rule-letter');
    const ruleNumber = document.getElementById('rule-number');
    const matchFeedback = document.getElementById('matchFeedback');

    function updateRule(element, isValid) {
        if (!element) return;
        const icon = element.querySelector('i');
        if (isValid) {
            element.style.color = 'var(--primary)';
            icon.className = 'fa-solid fa-circle-check';
            icon.style.color = '#10B981';
        } else {
            element.style.color = 'var(--text-muted)';
            icon.className = 'fa-regular fa-circle';
            icon.style.color = '';
        }
    }

    function validatePasswordStrength() {
        if (!pwdInput) return;
        const val = pwdInput.value;
        const hasMinLength = val.length >= 8;
        const hasLetter = /[a-zA-Z]/.test(val);
        const hasNumber = /[0-9]/.test(val);

        updateRule(ruleMinLength, hasMinLength);
        updateRule(ruleLetter, hasLetter);
        updateRule(ruleNumber, hasNumber);

        validatePasswordMatch();
    }

    function validatePasswordMatch() {
        if (!pwdConf || !pwdInput) return;
        const pwd = pwdInput.value;
        const conf = pwdConf.value;

        if (conf.length === 0) {
            matchFeedback.style.display = 'none';
            return;
        }

        matchFeedback.style.display = 'block';
        if (pwd === conf) {
            matchFeedback.style.color = '#10B981';
            matchFeedback.innerHTML = '<i class="fa-solid fa-circle-check"></i> {{ __('Les mots de passe correspondent parfaitement.') }}';
        } else {
            matchFeedback.style.color = '#EF4444';
            matchFeedback.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> {{ __('Les mots de passe ne correspondent pas encore.') }}';
        }
    }

    if (pwdInput) {
        pwdInput.addEventListener('input', validatePasswordStrength);
    }
    if (pwdConf) {
        pwdConf.addEventListener('input', validatePasswordMatch);
    }
</script>
@endsection
