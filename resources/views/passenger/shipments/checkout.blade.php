@extends('layouts.app')

@php
    $amountToPay = $shipment->total_amount;
    $walletBalance = Auth::user()->wallet_balance ?? 0;
    $hasEnoughWallet = ($walletBalance >= $amountToPay);
@endphp

@section('title', __('Règlement Fret Real Voyage - ') . $shipment->tracking_code)

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Summary Card -->
    <div class="card card-glass" style="padding: 28px; margin-bottom: 24px; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span class="badge" style="background: var(--primary); color: white; font-size: 0.75rem; margin-bottom: 4px;">{{ __('BORDEREAU FRET :') }} {{ $shipment->tracking_code }}</span>
                <h2 style="font-size: 1.4rem; color: var(--text-heading); font-weight: 800; margin: 4px 0;">
                    {{ $shipment->originTerminal->name ?? 'Gare Départ' }} &rarr; {{ $shipment->destinationTerminal->name ?? $shipment->destination_station }}
                </h2>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    {{ __('Destinataire :') }} <strong>{{ $shipment->recipient_name }}</strong> ({{ $shipment->recipient_phone }})
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Frais de Transport (10%) :') }}</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-heading);">
                    {{ number_format($shipment->total_amount, 0, ',', ' ') }} FCFA
                </div>
                <div style="font-size: 0.75rem; color: var(--success); font-weight: 700;">
                    {{ __('Valeur déclarée :') }} {{ number_format($shipment->declared_value, 0, ',', ' ') }} FCFA
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 20px; font-size: 0.85rem; color: var(--text-main); flex-wrap: wrap;">
            <div><i class="fa-solid fa-box text-primary"></i> {{ __('Contenu :') }} <strong>{{ $shipment->item_description }}</strong></div>
            <div><i class="fa-solid fa-weight-hanging text-primary"></i> {{ __('Poids :') }} <strong>{{ $shipment->weight_kg }} kg</strong></div>
            <div><i class="fa-solid fa-shield-halved text-primary"></i> {{ __('Garantie :') }} <strong>{{ __('Indemnisation 2x à 5x') }}</strong></div>
        </div>
    </div>

    <!-- Multi-Modal Payment Selection (Wallet + MoMo) -->
    <div class="card card-glass" style="padding: 32px; box-shadow: var(--shadow-lg); border-radius: 12px;">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; color: var(--text-heading); text-align: center;">
            <i class="fa-solid fa-credit-card text-primary"></i> {{ __('Sélectionnez le mode de règlement') }}
        </h3>

        <form action="{{ route('passenger.payments.shipment', $shipment) }}" method="POST" id="shipmentPaymentForm">
            @csrf

            <!-- Payment Providers Grid -->
            <div class="grid grid-cols-3" style="gap: 14px; margin-bottom: 24px;">
                <!-- E-Wallet -->
                <label style="border: 2px solid {{ $hasEnoughWallet ? 'var(--primary)' : 'var(--border-subtle)' }}; border-radius: 10px; padding: 16px 12px; cursor: pointer; display: block; background: {{ $hasEnoughWallet ? 'var(--bg-surface)' : 'var(--bg-card)' }}; text-align: center;" class="payment-card" id="cardWallet">
                    <input type="radio" name="method" value="wallet" {{ $hasEnoughWallet ? 'checked' : '' }} onchange="switchShipmentMethod('wallet')" style="margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                        <i class="fa-solid fa-wallet text-primary"></i> E-Wallet
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                        {{ __('Solde :') }} <strong>{{ number_format($walletBalance, 0, ',', ' ') }} F</strong>
                    </div>
                    @if($hasEnoughWallet)
                        <span class="badge badge-success" style="font-size: 0.65rem; margin-top: 6px; display: inline-block;">
                            {{ __('Immédiat (0 frais)') }}
                        </span>
                    @else
                        <span class="badge badge-danger" style="font-size: 0.65rem; margin-top: 6px; display: inline-block;">
                            {{ __('Solde insuffisant') }}
                        </span>
                    @endif
                </label>

                <!-- Orange Money -->
                <label style="border: 2px solid {{ !$hasEnoughWallet ? '#EA580C' : 'var(--border-subtle)' }}; border-radius: 10px; padding: 16px 12px; cursor: pointer; display: block; background: var(--bg-card); text-align: center;" class="payment-card" id="cardOrange">
                    <input type="radio" name="method" value="orange_money" {{ !$hasEnoughWallet ? 'checked' : '' }} onchange="switchShipmentMethod('orange_money')" style="margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 1rem; color: #EA580C;">
                        <i class="fa-solid fa-mobile-retro"></i> Orange Money
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">#150*50# Cameroun</div>
                </label>

                <!-- MTN MoMo -->
                <label style="border: 2px solid var(--border-subtle); border-radius: 10px; padding: 16px 12px; cursor: pointer; display: block; background: var(--bg-card); text-align: center;" class="payment-card" id="cardMtn">
                    <input type="radio" name="method" value="mtn_momo" onchange="switchShipmentMethod('mtn_momo')" style="margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 1rem; color: #CA8A04;">
                        <i class="fa-solid fa-phone"></i> MTN MoMo
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">*126# Cameroun</div>
                </label>
            </div>

            <!-- Phone Number Input -->
            <div class="form-group" id="phoneInputGroup" style="margin-bottom: 24px; display: {{ $hasEnoughWallet ? 'none' : 'block' }};">
                <label class="form-label" for="phone" style="font-weight: 700; color: var(--text-heading);">{{ __('Numéro du compte Mobile Money') }}</label>
                <div style="display: flex; gap: 8px;">
                    <span style="display: inline-flex; align-items: center; padding: 0 16px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; font-weight: 700; color: var(--text-heading);">+237</span>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ Auth::user()->phone ?? '699112233' }}" placeholder="6XX XX XX XX" style="font-size: 1.05rem; font-weight: 700;">
                </div>
            </div>

            <!-- Wallet Instant Notice -->
            <div id="walletNotice" style="background: var(--primary-50); border: 1px solid var(--primary-100); border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; display: {{ $hasEnoughWallet ? 'block' : 'none' }}; font-size: 0.85rem; color: var(--primary-text);">
                <i class="fa-solid fa-circle-check"></i>
                <strong>{{ __('Débit Direct E-Wallet :') }}</strong> {{ __('Le montant de :amount FCFA sera immédiatement réglé depuis votre solde. Votre bordereau de fret sera instantanément validé.', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}
            </div>

            <button type="submit" class="btn btn-lg btn-primary" style="width: 100%; font-weight: 800; height: 50px; border-radius: 8px;" id="payBtn">
                <i class="fa-solid fa-lock"></i>
                <span id="btnPayText">
                    {{ $hasEnoughWallet ? __('Confirmer et Débiter mon E-Wallet (:amount FCFA)', ['amount' => number_format($amountToPay, 0, ',', ' ')]) : __('Payer via Mobile Money (:amount FCFA)', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}
                </span>
            </button>
        </form>
    </div>
</div>

<script>
function switchShipmentMethod(method) {
    const phoneGroup = document.getElementById('phoneInputGroup');
    const walletNotice = document.getElementById('walletNotice');
    const btnText = document.getElementById('btnPayText');

    document.querySelectorAll('.payment-card').forEach(el => {
        el.style.borderColor = 'var(--border-subtle)';
    });

    if (method === 'wallet') {
        document.getElementById('cardWallet').style.borderColor = 'var(--primary)';
        if (phoneGroup) phoneGroup.style.display = 'none';
        if (walletNotice) walletNotice.style.display = 'block';
        if (btnText) btnText.textContent = "{{ __('Confirmer et Débiter mon E-Wallet (:amount FCFA)', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}";
    } else if (method === 'orange_money') {
        document.getElementById('cardOrange').style.borderColor = '#EA580C';
        if (phoneGroup) phoneGroup.style.display = 'block';
        if (walletNotice) walletNotice.style.display = 'none';
        if (btnText) btnText.textContent = "{{ __('Valider sur Orange Money (:amount FCFA)', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}";
    } else {
        document.getElementById('cardMtn').style.borderColor = '#CA8A04';
        if (phoneGroup) phoneGroup.style.display = 'block';
        if (walletNotice) walletNotice.style.display = 'none';
        if (btnText) btnText.textContent = "{{ __('Valider sur MTN MoMo (:amount FCFA)', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}";
    }
}
</script>
@endsection
