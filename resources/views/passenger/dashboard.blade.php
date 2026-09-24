@extends('layouts.dashboard')

@section('title', __('Espace Voyageur - Tableau de Bord'))

@section('dashboard_content')
<div>
    <!-- Welcome Header with E-Wallet Balance Card -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--text-heading); font-weight: 800; margin-bottom: 4px;">
                {{ __('Bonjour') }}, {{ Auth::user()->name }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">
                {{ __('Gérez vos voyages Real Voyage, votre E-Wallet et vos expéditions fret sécurisées.') }}
            </p>
        </div>

        <!-- Real Voyage Executive E-Wallet Card -->
        <div style="background: var(--primary-gradient); color: white; border-radius: 12px; padding: 16px 22px; min-width: 260px; box-shadow: var(--shadow-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">
                <span><i class="fa-solid fa-wallet"></i> {{ __('Portefeuille E-Wallet') }}</span>
                <span class="badge badge-success" style="font-size: 0.65rem;">{{ __('Actif') }}</span>
            </div>
            <div style="font-size: 1.7rem; font-weight: 900; color: #FFFFFF; font-family: var(--font-heading);">
                {{ number_format(Auth::user()->wallet_balance, 0, ',', ' ') }} <span style="font-size: 0.9rem; font-weight: 600; color: var(--primary-text);">FCFA</span>
            </div>
            <div style="font-size: 0.72rem; color: rgba(255, 255, 255, 0.75); margin-top: 4px;">
                {{ __('Solde disponible pour billets & fret Real Voyage') }}
            </div>
        </div>
    </div>

    <!-- Quick Action Navigation -->
    <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
        <a href="{{ route('trips.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-bus"></i> {{ __('Réserver un Trajet (10h00 ou 21h30)') }}
        </a>
        <a href="{{ route('passenger.shipments.create') }}" class="btn btn-outline">
            <i class="fa-solid fa-truck-ramp-box"></i> {{ __('Expédier un Colis Fret (10%)') }}
        </a>
    </div>

    <!-- Active Alerts Banner (if any) -->
    @if($alerts->isNotEmpty())
        <div style="margin-bottom: 24px;">
            @foreach($alerts as $alert)
                <div class="alert alert-{{ $alert->type === 'delay' ? 'warning' : ($alert->type === 'cancellation' ? 'error' : 'info') }}">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
                    <div style="flex: 1;">
                        <div style="font-weight: 700;">{{ $alert->title }} ({{ __('Voyage N°') }} {{ $alert->trip->trip_number ?? '' }})</div>
                        <div style="font-size: 0.85rem;">{{ $alert->message }}</div>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $alert->created_at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Statistics Overview Grid -->
    <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Trajets Confirmés') }}</span>
                <div style="width: 34px; height: 34px; border-radius: 8px; background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-ticket"></i>
                </div>
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: var(--text-heading);">{{ $stats['confirmed_trips'] }}</div>
        </div>

        <div class="card" style="padding: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Colis Fret') }}</span>
                <div style="width: 34px; height: 34px; border-radius: 8px; background: var(--success-50); color: var(--success); display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: var(--text-heading);">{{ $stats['total_shipments'] }}</div>
        </div>

        <div class="card" style="padding: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Terminaux Desservis') }}</span>
                <div style="width: 34px; height: 34px; border-radius: 8px; background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-building"></i>
                </div>
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: var(--text-heading);">11</div>
        </div>

        <div class="card" style="padding: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Assistance & Suivi') }}</span>
                <div style="width: 34px; height: 34px; border-radius: 8px; background: var(--warning-50); color: var(--warning); display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </div>
            <div style="font-size: 1.6rem; font-weight: 900; color: var(--text-heading);">{{ $stats['total_disputes'] }}</div>
        </div>
    </div>

    <!-- Active Bookings & Active Shipments Grid -->
    <div class="grid grid-cols-2" style="gap: 24px;">
        <!-- Left: Active Bookings & Cancellation Management -->
        <div class="card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h3 style="font-size: 1.15rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-ticket text-primary"></i> {{ __('Mes Billets & Voyages Actifs') }}
                </h3>
                <a href="{{ route('passenger.bookings.history') }}" style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">
                    {{ __('Tout voir') }} &rarr;
                </a>
            </div>

            @if($activeBookings->isEmpty())
                <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 24px 0;">
                    {{ __('Aucune réservation active actuellement.') }} <br>
                    <a href="{{ route('trips.index') }}" style="color: var(--primary); font-weight: 700; margin-top: 6px; display: inline-block;">
                        {{ __('Réserver un départ à 10h00 ou 21h30') }}
                    </a>
                </p>
            @else
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach($activeBookings as $booking)
                        @php
                            $trip = $booking->trip;
                            $hoursRemaining = $trip ? now()->diffInHours($trip->departure_time, false) : 0;
                            $canCancel = ($hoursRemaining > 6) && ($booking->status === 'confirmed');
                        @endphp
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <div>
                                    <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted);">{{ __('RÉF :') }} {{ $booking->booking_reference }}</span>
                                    <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                                        {{ $trip->departure_city }} &rarr; {{ $trip->arrival_city }}
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                        <i class="fa-regular fa-calendar"></i> {{ $trip->departure_time->format('d/m/Y à H:i') }} &bull; {{ $trip->departureTerminal->name ?? $trip->departure_station }}
                                    </div>
                                </div>

                                <div style="text-align: right;">
                                    @if($booking->status === 'confirmed')
                                        <span class="badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 0.72rem;"><i class="fa-solid fa-circle-check"></i> {{ __('Confirmé') }}</span>
                                    @elseif($booking->status === 'reserved')
                                        <span class="badge" style="background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; font-size: 0.72rem;"><i class="fa-solid fa-shield-check"></i> {{ __('Réservé (500 F)') }}</span>
                                    @else
                                        <span class="badge" style="background: var(--bg-surface); color: var(--text-muted); font-size: 0.72rem;"><i class="fa-solid fa-clock"></i> {{ $booking->status }}</span>
                                    @endif
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding-top: 10px; border-top: 1px dashed var(--border-subtle); flex-wrap: wrap; gap: 8px;">
                                <div style="font-size: 0.8rem; color: var(--text-main);">
                                    <strong>{{ $booking->seats_count }}</strong> {{ __('place(s) :') }} <strong>{{ implode(', ', $booking->seat_numbers ?? []) }}</strong>
                                </div>

                                <div style="display: flex; gap: 8px; align-items: center;">
                                    @if($booking->status === 'confirmed')
                                        <a href="{{ route('passenger.bookings.ticket', $booking) }}" class="btn btn-sm btn-primary" style="font-size: 0.75rem; padding: 4px 10px;">
                                            <i class="fa-solid fa-qrcode"></i> {{ __('E-Billet') }}
                                        </a>

                                        <!-- Strict Cancellation Trigger -->
                                        @if($canCancel)
                                            <form action="{{ route('passenger.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('{{ __('Attention : Annulation d\'un billet Real Voyage. Aucun remboursement en espèces n\'est effectué. Le montant intégral (:amount FCFA) sera crédité directement sur votre E-Wallet Real Voyage. Confirmer ?', ['amount' => number_format($booking->total_amount, 0, ',', ' ')]) }}');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline" style="border-color: #DC2626; color: #DC2626; font-size: 0.75rem; padding: 4px 10px;">
                                                    <i class="fa-solid fa-ban"></i> {{ __('Annuler (> 6h)') }}
                                                </button>
                                            </form>
                                        @else
                                            <span style="font-size: 0.7rem; color: var(--text-muted);" title="{{ __('Annulation interdite à moins de 6h du départ') }}">
                                                <i class="fa-solid fa-lock"></i> {{ __('Départ proche (≤ 6h)') }}
                                            </span>
                                        @endif
                                    @elseif($booking->status === 'reserved' || $booking->status === 'pending')
                                        <a href="{{ route('passenger.bookings.checkout', $booking) }}" class="btn btn-sm btn-primary" style="font-size: 0.75rem; padding: 4px 10px;">
                                            <i class="fa-solid fa-wallet"></i> {{ __('Régler Billet') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Active Cargo Shipments -->
        <div class="card" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h3 style="font-size: 1.15rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-box text-primary"></i> {{ __('Mes Expéditions de Fret') }}
                </h3>
                <a href="{{ route('passenger.shipments.index') }}" style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">
                    {{ __('Historique') }} &rarr;
                </a>
            </div>

            @if($activeShipments->isEmpty())
                <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 24px 0;">
                    {{ __('Aucun colis en cours d\'expédition.') }}<br>
                    <a href="{{ route('passenger.shipments.create') }}" style="color: var(--primary); font-weight: 700; margin-top: 6px; display: inline-block;">
                        {{ __('Enregistrer un colis fret (10% valeur déclarée)') }}
                    </a>
                </p>
            @else
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach($activeShipments as $shipment)
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                <div>
                                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--primary);">{{ $shipment->tracking_code }}</span>
                                    <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading);">{{ __('Destinataire :') }} {{ $shipment->recipient_name }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        {{ $shipment->originTerminal->name ?? __('Gare') }} &rarr; {{ $shipment->destinationTerminal->name ?? $shipment->destination_station }}
                                    </div>
                                </div>
                                <div>
                                    @if($shipment->status === 'in_transit')
                                        <span class="badge" style="background: #EFF6FF; color: #1D4ED8; font-size: 0.7rem;">{{ __('En Transit') }}</span>
                                    @elseif($shipment->status === 'arrived')
                                        <span class="badge" style="background: #ECFDF5; color: #059669; font-size: 0.7rem;">{{ __('Prêt au Retrait') }}</span>
                                    @else
                                        <span class="badge" style="background: var(--bg-surface); color: var(--text-muted); font-size: 0.7rem;">{{ $shipment->status }}</span>
                                    @endif
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; font-size: 0.8rem; border-top: 1px dashed var(--border-subtle); padding-top: 8px;">
                                <span>{{ __('Code Retrait :') }} <strong>{{ $shipment->proof_of_delivery_code }}</strong></span>
                                <a href="{{ route('passenger.shipments.show', $shipment) }}" class="btn btn-sm btn-outline" style="font-size: 0.75rem; padding: 3px 8px;">
                                    {{ __('Suivre Envoi') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
