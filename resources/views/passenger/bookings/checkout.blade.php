@extends('layouts.app')

@php
    $isPayingReservationFee = ($booking->isAdvanceReservation() && !$booking->reservation_fee_paid);
    $isPayingTicketBalance = $booking->isReserved();
    $amountToPay = $isPayingReservationFee ? $booking->reservation_fee : $booking->total_amount;
    $walletBalance = Auth::user()->wallet_balance ?? 0;
    $hasEnoughWallet = ($walletBalance >= $amountToPay);
@endphp

@section('title', __('Règlement Billet Real Voyage - ') . $booking->booking_reference)

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Countdown Timer Banner -->
    <div class="card" style="background: var(--bg-surface); border: 1.5px solid var(--border-color); margin-bottom: 24px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; border-radius: 12px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div>
                @if($isPayingTicketBalance)
                    <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">{{ __('Délai limite pour solder le billet (6h avant départ) :') }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Passé ce délai, vos places seront automatiquement libérées.') }}</div>
                @else
                    <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">{{ __('Temps restant pour finaliser votre règlement :') }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Vos sièges sont maintenus en exclusivité durant cette période.') }}</div>
                @endif
            </div>
        </div>

        <div style="font-size: 1.8rem; font-weight: 900; font-family: monospace; color: var(--text-heading);" id="countdownDisplay">
            --:--
        </div>
    </div>

    <!-- Booking Summary Card -->
    <div class="card card-glass" style="padding: 28px; margin-bottom: 24px; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted);">{{ __('RÉSERVATION :') }} {{ $booking->booking_reference }}</span>
                    <span class="badge" style="background: var(--primary); color: white; font-size: 0.7rem;">Real Voyage S.A.</span>
                </div>
                <h2 style="font-size: 1.4rem; color: var(--text-heading); font-weight: 800; margin: 2px 0;">
                    {{ $booking->trip->departure_city }} &rarr; {{ $booking->trip->arrival_city }}
                </h2>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fa-regular fa-clock"></i> {{ __('Départ') }} {{ $booking->trip->departure_time->format('H:i') }} &bull; {{ $booking->trip->departure_time->format('d/m/Y') }}
                    &bull; {{ $booking->trip->departureTerminal->name ?? $booking->trip->departure_station }}
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Total à Régler :') }}</div>
                <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-heading);">
                    {{ number_format($amountToPay, 0, ',', ' ') }} FCFA
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 20px; font-size: 0.85rem; color: var(--text-main); flex-wrap: wrap;">
            <div><i class="fa-solid fa-chair text-primary"></i> {{ __('Sièges attribués :') }} <strong>{{ implode(', ', $booking->seat_numbers ?? []) }}</strong></div>
            <div><i class="fa-solid fa-user-group text-primary"></i> {{ __('Voyageurs :') }} <strong>{{ $booking->seats_count }} {{ __('personne(s)') }}</strong></div>
            <div><i class="fa-solid fa-bus text-primary"></i> {{ __('Autocar :') }} <strong>{{ $booking->trip->vehicle->name ?? __('Grand Confort') }}</strong></div>
        </div>
    </div>

    <!-- Multi-Modal Payment Form (E-Wallet + Mobile Money) -->
    <div class="card card-glass" style="padding: 32px; box-shadow: var(--shadow-lg); border-radius: 12px;">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; color: var(--text-heading); text-align: center;">
            <i class="fa-solid fa-credit-card text-primary"></i> {{ __('Sélectionnez le mode de règlement') }}
        </h3>

        <form action="{{ route('passenger.payments.booking', $booking) }}" method="POST" id="paymentForm">
            @csrf

            <!-- Payment Methods Grid -->
            <div class="grid grid-cols-3" style="gap: 14px; margin-bottom: 24px;">
                <!-- 1. Real Voyage E-Wallet -->
                <label style="border: 2px solid {{ $hasEnoughWallet ? 'var(--primary)' : 'var(--border-subtle)' }}; border-radius: 10px; padding: 16px 12px; cursor: pointer; display: block; background: {{ $hasEnoughWallet ? 'var(--bg-surface)' : 'var(--bg-card)' }}; text-align: center; position: relative;" class="payment-method-card" id="cardWallet">
                    <input type="radio" name="method" value="wallet" {{ $hasEnoughWallet ? 'checked' : '' }} onchange="switchPaymentMethod('wallet')" style="margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                        <i class="fa-solid fa-wallet text-primary"></i> E-Wallet
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                        {{ __('Solde :') }} <strong>{{ number_format($walletBalance, 0, ',', ' ') }} F</strong>
                    </div>
                    @if($hasEnoughWallet)
                        <span class="badge" style="background: #ECFDF5; color: #059669; font-size: 0.65rem; margin-top: 6px; display: inline-block;">
                            {{ __('Instantané (0 frais)') }}
                        </span>
                    @else
                        <span class="badge" style="background: #FEF2F2; color: #DC2626; font-size: 0.65rem; margin-top: 6px; display: inline-block;">
                            {{ __('Solde insuffisant') }}
                        </span>
                    @endif
                </label>

                <!-- 2. Orange Money -->
                <label style="border: 2px solid {{ !$hasEnoughWallet ? '#EA580C' : 'var(--border-subtle)' }}; border-radius: 10px; padding: 16px 12px; cursor: pointer; display: block; background: var(--bg-card); text-align: center;" class="payment-method-card" id="cardOrange">
                    <input type="radio" name="method" value="orange_money" {{ !$hasEnoughWallet ? 'checked' : '' }} onchange="switchPaymentMethod('orange_money')" style="margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 1rem; color: #EA580C;">
                        <i class="fa-solid fa-mobile-retro"></i> Orange Money
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                        #150*50# Cameroun
                    </div>
                </label>

                <!-- 3. MTN MoMo -->
                <label style="border: 2px solid var(--border-subtle); border-radius: 10px; padding: 16px 12px; cursor: pointer; display: block; background: var(--bg-card); text-align: center;" class="payment-method-card" id="cardMtn">
                    <input type="radio" name="method" value="mtn_momo" onchange="switchPaymentMethod('mtn_momo')" style="margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 1rem; color: #CA8A04;">
                        <i class="fa-solid fa-phone"></i> MTN MoMo
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                        *126# Cameroun
                    </div>
                </label>
            </div>

            <!-- Phone Number Input (Required for Mobile Money, Hidden for E-Wallet) -->
            <div class="form-group" id="phoneInputGroup" style="margin-bottom: 24px; display: {{ $hasEnoughWallet ? 'none' : 'block' }};">
                <label class="form-label" for="phone" style="font-weight: 700; color: var(--text-heading);">
                    <i class="fa-solid fa-phone"></i> {{ __('Numéro de Téléphone du Compte Mobile Money') }}
                </label>
                <div style="display: flex; gap: 8px;">
                    <span style="display: inline-flex; align-items: center; padding: 0 16px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; font-weight: 700; font-size: 0.95rem; color: var(--text-heading);">
                        +237
                    </span>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ Auth::user()->phone ?? '699112233' }}" placeholder="6XX XX XX XX" style="font-size: 1.05rem; font-weight: 700;">
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 6px;">
                    {{ __('Une invite USSD sécurisée sera déclenchée sur ce numéro pour valider :amount FCFA.', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}
                </div>
            </div>

            <!-- Wallet Instant Pay Notice -->
            <div id="walletNotice" style="background: var(--primary-50); border: 1px solid var(--primary-100); border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; display: {{ $hasEnoughWallet ? 'block' : 'none' }}; font-size: 0.85rem; color: var(--primary-text);">
                <i class="fa-solid fa-circle-check"></i>
                <strong>{{ __('Débit Direct E-Wallet :') }}</strong> {{ __('Le montant de :amount FCFA sera instantanément déduit de votre solde. Votre E-Billet sera généré immédiatement sans attente d\'opérateur.', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}
            </div>

            <!-- Action Buttons -->
            <button type="submit" class="btn btn-lg btn-primary" style="width: 100%; font-weight: 800; height: 50px; border-radius: 8px;" id="payBtn">
                <i class="fa-solid fa-lock"></i>
                <span id="btnPayText">
                    {{ $hasEnoughWallet ? __('Confirmer et Débiter mon E-Wallet (:amount FCFA)', ['amount' => number_format($amountToPay, 0, ',', ' ')]) : __('Payer via Mobile Money (:amount FCFA)', ['amount' => number_format($amountToPay, 0, ',', ' ')]) }}
                </span>
            </button>
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-subtle); flex-wrap: wrap; gap: 10px;">
            <a href="{{ route('passenger.bookings.history') }}" style="font-size: 0.82rem; color: var(--text-muted);">
                <i class="fa-solid fa-arrow-left"></i> {{ __('Retour à mes réservations') }}
            </a>
            <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fa-solid fa-shield-halved text-primary"></i> {{ __('Plateforme Sécurisée Real Voyage S.A.') }}</span>
        </div>
    </div>
</div>

<script>
const expiresAt = new Date("{{ $booking->expires_at->toIso8601String() }}").getTime();
const isReservedState = {{ $isPayingTicketBalance ? 'true' : 'false' }};

function updateTimer() {
    const now = new Date().getTime();
    const distance = expiresAt - now;

    if (distance <= 0) {
        document.getElementById('countdownDisplay').textContent = "{{ __('EXPIRÉ') }}";
        window.location.href = "{{ route('passenger.bookings.history') }}";
        return;
    }

    const hours = Math.floor(distance / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    if (hours > 0) {
        document.getElementById('countdownDisplay').textContent = 
            hours + "h " + (minutes < 10 ? '0' : '') + minutes + "m " + (seconds < 10 ? '0' : '') + seconds + "s";
    } else {
        document.getElementById('countdownDisplay').textContent = 
            (minutes < 10 ? '0' : '') + minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
    }
}

setInterval(updateTimer, 1000);
updateTimer();

function switchPaymentMethod(method) {
    const phoneGroup = document.getElementById('phoneInputGroup');
    const walletNotice = document.getElementById('walletNotice');
    const btnText = document.getElementById('btnPayText');

    document.querySelectorAll('.payment-method-card').forEach(el => {
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
