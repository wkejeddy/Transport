@extends('layouts.app')

@section('title', __('Accès Refusé (403) - Real Express Voyages'))

@section('content')
<div class="container" style="max-width: 680px; margin: 40px auto; padding: 0 16px; text-align: center;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 48px 32px; box-shadow: var(--shadow-md);">
        
        <div style="width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; background: rgba(239, 68, 68, 0.12); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div style="font-size: 0.9rem; font-weight: 700; color: var(--danger); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
            {{ __('Erreur 403 • Accès Non Autorisé') }}
        </div>

        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-heading); margin-bottom: 16px; line-height: 1.25;">
            {{ __('Vous n\'avez pas accès à cette ressource') }}
        </h1>

        <p style="font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; max-width: 500px; margin: 0 auto 28px;">
            {{ $exception->getMessage() ?: __('Cette page ou cette opération est protégée ou appartient à un autre compte. Veuillez vérifier votre session ou vous connecter avec le compte approprié.') }}
        </p>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            @auth
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="padding: 12px 24px; font-weight: 700;">
                        <i class="fa-solid fa-chart-line"></i> {{ __('Tableau de bord Admin') }}
                    </a>
                @elseif(Auth::user()->isManager())
                    <a href="{{ route('manager.dashboard') }}" class="btn btn-primary" style="padding: 12px 24px; font-weight: 700;">
                        <i class="fa-solid fa-gauge-high"></i> {{ __('Espace Direction') }}
                    </a>
                @else
                    <a href="{{ route('passenger.dashboard') }}" class="btn btn-primary" style="padding: 12px 24px; font-weight: 700;">
                        <i class="fa-solid fa-house-user"></i> {{ __('Mon Espace Voyageur') }}
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 12px 24px; font-weight: 700;">
                    <i class="fa-solid fa-right-to-bracket"></i> {{ __('Se Connecter') }}
                </a>
            @endauth

            <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 12px 24px; font-weight: 700;">
                <i class="fa-solid fa-house"></i> {{ __('Retour à l\'Accueil') }}
            </a>
        </div>

        <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 0.78rem; color: var(--text-light);">
            <i class="fa-solid fa-headset" style="color: var(--primary);"></i> {{ __('Besoin d\'aide ? Contactez le support au') }} <strong>+237 233 42 00 00</strong>
        </div>
    </div>
</div>
@endsection
