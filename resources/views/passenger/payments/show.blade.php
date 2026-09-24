@extends('layouts.app')

@section('title', __('Simulateur Paiement Mobile Money - TransportCM'))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Top Back Link -->
    <div style="margin-bottom: 20px; text-align: center;">
        <span class="badge badge-road" style="font-size: 0.85rem; padding: 6px 16px;">
            <i class="fa-solid fa-flask"></i> {{ __('Environnement Sandbox / Passerelle Mobile Money Cameroun') }}
        </span>
    </div>

    <!-- Phone Push USSD Simulator Frame -->
    <div style="max-width: 420px; margin: 0 auto; background: #1F2937; border-radius: 44px; padding: 18px; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6); border: 4px solid var(--border-color);">
        <!-- Smartphone Speaker & Notch -->
        <div style="width: 100px; height: 16px; background: #374151; border-radius: 20px; margin: 0 auto 16px;"></div>

        <!-- Phone Screen Content -->
        <div style="background: var(--bg-card); color: var(--text-main); border-radius: 30px; padding: 24px; min-height: 520px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <!-- Top Status Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 0.8rem; color: var(--text-muted);">
                    <span>+237 {{ $payment->payer_phone }}</span>
                    <span><i class="fa-solid fa-signal"></i> 4G LTE</span>
                </div>

                <!-- Carrier Badge -->
                <div style="text-align: center; margin-bottom: 20px;">
                    @if($payment->isOrangeMoney())
                        <div style="display: inline-block; background: var(--orange-money-light); border: 2px solid var(--orange-money); border-radius: var(--radius-lg); padding: 12px 24px;">
                            <div style="font-weight: 900; font-size: 1.3rem; color: #EA580C;">
                                <i class="fa-solid fa-mobile-retro"></i> Orange Money
                            </div>
                            <div style="font-size: 0.75rem; color: #9A3412;">{{ __('#150*50# Paiement Marchand') }}</div>
                        </div>
                    @else
                        <div style="display: inline-block; background: var(--mtn-momo-light); border: 2px solid var(--mtn-momo); border-radius: var(--radius-lg); padding: 12px 24px;">
                            <div style="font-weight: 900; font-size: 1.3rem; color: #854D0E;">
                                <i class="fa-solid fa-wallet"></i> MTN MoMo
                            </div>
                            <div style="font-size: 0.75rem; color: #713F12;">{{ __('*126# Mobile Money') }}</div>
                        </div>
                    @endif
                </div>

                <!-- USSD Push Modal Simulation Box -->
                <div style="background: var(--bg-surface); border: 2px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px; text-align: center; box-shadow: var(--shadow-sm);">
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                        {{ __('Notification Push USSD') }}
                    </div>
                    <div style="font-size: 1rem; font-weight: 800; color: var(--text-heading); margin: 8px 0;">
                        {{ __('Autoriser le débit de') }} <span style="color: var(--primary);">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</span> ?
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        {{ __('Bénéficiaire :') }} <strong>TransportCM Billetterie</strong><br>
                        {{ __('Réf :') }} {{ $payment->external_reference }}
                    </div>
                </div>

                <!-- PIN Code simulation notice -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px;">{{ __('Entrez votre code secret PIN :') }}</div>
                    <div style="font-size: 1.8rem; letter-spacing: 8px; color: var(--text-heading); font-weight: 900;">
                        ••••
                    </div>
                </div>
            </div>

            <!-- Validation Trigger Form Actions (Sandbox) -->
            <div>
                <form action="{{ route('passenger.payments.simulate', $payment) }}" method="POST" style="margin-bottom: 10px;">
                    @csrf
                    <input type="hidden" name="status" value="success">
                    <button type="submit" class="btn btn-lg btn-primary" style="width: 100%; font-weight: 900; font-size: 1.05rem;">
                        <i class="fa-solid fa-circle-check"></i> {{ __('Valider le Paiement (PIN OK)') }}
                    </button>
                </form>

                <form action="{{ route('passenger.payments.simulate', $payment) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="fail">
                    <button type="submit" class="btn btn-sm btn-outline" style="width: 100%; color: var(--danger); border-color: var(--danger-border);">
                        <i class="fa-solid fa-circle-xmark"></i> {{ __('Simuler Échec / Solde Insuffisant') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
