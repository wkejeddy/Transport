@extends('layouts.app')

@section('title', __('Session Expirée (419) - Real Express Voyages'))

@section('content')
<div class="container" style="max-width: 600px; margin: 40px auto; padding: 0 16px; text-align: center;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 48px 32px; box-shadow: var(--shadow-md);">
        
        <div style="width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; background: rgba(245, 158, 11, 0.12); color: var(--warning); display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>

        <div style="font-size: 0.9rem; font-weight: 700; color: var(--warning); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
            {{ __('Session Expirée • Erreur 419') }}
        </div>

        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-heading); margin-bottom: 16px; line-height: 1.25;">
            {{ __('Votre page ou session a expiré') }}
        </h1>

        <p style="font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; max-width: 480px; margin: 0 auto 28px;">
            {{ __('Pour des raisons de sécurité, les sessions inactives sont réinitialisées. Veuillez vous reconnecter pour poursuivre votre réservation.') }}
        </p>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 12px 28px; font-weight: 700;">
                <i class="fa-solid fa-right-to-bracket"></i> {{ __('Se Reconnecter') }}
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 12px 24px; font-weight: 700;">
                <i class="fa-solid fa-house"></i> {{ __('Accueil') }}
            </a>
        </div>
    </div>
</div>
@endsection
