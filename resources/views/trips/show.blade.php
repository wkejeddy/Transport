@extends('layouts.app')

@section('title', __('Réservation Autocar 3D Réaliste') . ' ' . $trip->departure_city . ' - ' . $trip->arrival_city)

@section('styles')
<!-- Three.js WebGL 3D Core & Orbit Controls (Local Assets - Zero Tracking / Zero FOUC) -->
<script src="{{ asset('js/vendor/three.min.js') }}"></script>
<script src="{{ asset('js/vendor/OrbitControls.js') }}"></script>
<script src="{{ asset('js/three-bus-engine.js') }}"></script>
@endsection

@section('content')
<div class="container" style="padding-top: 25px; padding-bottom: 70px;">
    <!-- Top Navigation Breadcrumb -->
    <div style="margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <a href="{{ route('trips.index') }}" style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour aux résultats de recherche') }}
        </a>
        <div style="font-size: 0.82rem; color: var(--text-muted);">
            <i class="fa-solid fa-shield-halved text-success"></i> {{ __('Réservation Officielle Real Voyage Transport S.A.') }}
        </div>
    </div>

    <!-- Coach Header Banner -->
    <div class="card card-glass" style="padding: 22px; margin-bottom: 22px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                    <span class="badge badge-road">
                        <i class="fa-solid fa-bus"></i> {{ __('Autocar VIP Grand Tourisme') }}
                    </span>
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 800; font-family: monospace;">N° {{ $trip->trip_number }}</span>
                    <span class="badge badge-outline" style="font-size: 0.75rem;">{{ $trip->vehicle->type_label ?? __('Coach 75/80 Places') }}</span>
                </div>
                <h1 style="font-size: clamp(1.4rem, 2.4vw, 1.9rem); color: var(--text-heading); margin: 0; font-weight: 800;">
                    {{ $trip->departure_city }} &rarr; {{ $trip->arrival_city }}
                </h1>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                    <i class="fa-solid fa-location-dot text-primary"></i> <strong>{{ $trip->departure_station }}</strong> &bull; {{ $trip->departure_time->format('d/m/Y') }} &agrave; <strong style="color: var(--primary);">{{ $trip->departure_time->format('H:i') }}</strong>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 0.82rem; color: var(--text-muted);">{{ __('Tarif Voyageur :') }}</div>
                <div style="font-size: 1.75rem; font-weight: 900; color: var(--primary); font-family: var(--font-heading);">
                    {{ number_format($trip->base_price, 0, ',', ' ') }} <span style="font-size: 0.95rem; font-weight: 700;">FCFA</span>
                </div>
                <div class="badge badge-success" style="margin-top: 4px; font-size: 0.75rem;">
                    <i class="fa-solid fa-circle-check"></i> {{ __('Départ ponctuel garanti') }}
                </div>
            </div>
        </div>

        <!-- Boarding Milestones -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; background: var(--bg-surface); padding: 12px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(15, 41, 66, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Convocation') }}</div>
                    <div style="font-size: 0.9rem; font-weight: 800; color: var(--text-heading);">{{ $convocationTime->format('H:i') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                    <i class="fa-solid fa-play"></i>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Départ Fixe') }}</div>
                    <div style="font-size: 0.9rem; font-weight: 800; color: var(--primary);">{{ $trip->departure_time->format('H:i') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); color: #3B82F6; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Arrivée Prévue') }}</div>
                    <div style="font-size: 0.9rem; font-weight: 800; color: var(--text-heading);">{{ $trip->arrival_time_estimated->format('H:i') }} ({{ $tripDuration->format('%hh %Imin') }})</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); color: #F59E0B; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                    <i class="fa-solid fa-chair"></i>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Disponibilité') }}</div>
                    <div style="font-size: 0.9rem; font-weight: 800; color: #10B981;">{{ $availableSeatsCount ?? $trip->seats_available }} {{ __('Places Libres') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Layout: True 3D WebGL Virtual Bus as Primary Hero Stage -->
    <div class="booking-page-layout">
        <div class="booking-main-col">
            <!-- 3D WEBGL VIRTUAL BUS CANVAS STAGE -->
            <div style="margin-bottom: 22px;">
                <x-real-3d-bus-canvas :seatMap="$seatMap" :trip="$trip" />
            </div>

            <!-- STREAMLINED BOOKING DECK CONNECTED TO 3D BUS -->
            <form action="{{ route('passenger.bookings.store', $trip) }}" method="POST" id="bookingForm">
                @csrf

                <!-- Interactive Seat Selection Bar & Passenger Inputs -->
                <div class="card card-glass" style="padding: 22px; margin-bottom: 22px; box-shadow: var(--shadow-md);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="font-size: 1.15rem; color: var(--text-heading); margin: 0; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-chair text-primary"></i> {{ __('Fauteuils Réservés sur le Modèle 3D') }}
                        </h3>
                        <span class="badge badge-road" style="font-size: 0.8rem;">
                            <span id="selectedSeatsCounter">1</span> {{ __('place(s) sélectionnée(s)') }}
                        </span>
                    </div>

                    <!-- Selected Seats Chips Bar -->
                    <div class="selected-seats-tray" id="selectedSeatsTray" style="margin-bottom: 18px;">
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted); margin-bottom: 8px;">
                                <i class="fa-solid fa-check-double text-primary"></i> {{ __('Places choisies dans l\'autocar 3D :') }}
                            </div>
                            <div class="selected-seat-chips-wrap" id="selectedSeatChips">
                                <!-- Dynamically populated by JS -->
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="resetSeatsToDefault()" style="font-size: 0.75rem; padding: 5px 12px;">
                            <i class="fa-solid fa-rotate-left"></i> {{ __('Réinitialiser') }}
                        </button>
                    </div>

                    <!-- Passenger Details Inputs (Dynamically created for each selected seat) -->
                    <div style="margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-heading);">
                                <i class="fa-solid fa-user-group text-primary"></i> {{ __('Identité des Voyageurs par Fauteuil') }}
                            </div>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">{{ __('CNI officielle requise à l\'embarquement') }}</span>
                        </div>

                        <div id="passengersContainer">
                            <!-- Injected by renderPassengerInputs() -->
                        </div>
                    </div>
                </div>

                <!-- Booking Formula Choice (Immediate Payment vs Advance Hold) -->
                @php
                    $isEligibleForAdvance = $trip->departure_time > now()->addHours(8);
                @endphp
                <div class="card card-glass" style="padding: 22px; margin-bottom: 22px;">
                    <h3 style="font-size: 1.15rem; color: var(--text-heading); margin-bottom: 14px; font-weight: 800;">
                        <i class="fa-solid fa-receipt text-primary"></i> {{ __('Formule de Règlement') }}
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <!-- Option 1: Immediate Full Payment -->
                        <label style="border: 2px solid var(--primary); border-radius: var(--radius-md); padding: 16px; cursor: pointer; display: block; background: var(--primary-50);" class="booking-mode-card" id="modeImmediateCard">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                                <div style="display: flex; align-items: flex-start; gap: 12px;">
                                    <input type="radio" name="booking_option" value="immediate" checked onchange="toggleBookingMode('immediate')" style="margin-top: 4px; accent-color: var(--primary);">
                                    <div>
                                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                                            {{ __('Paiement Intégral Immédiat') }}
                                            <span class="badge badge-primary" style="font-size: 0.7rem; margin-left: 6px;">{{ __('Recommandé') }}</span>
                                        </div>
                                        <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px; line-height: 1.4;">
                                            {{ __('Réglez vos billets dès maintenant via Orange Money ou MTN MoMo sous') }} <strong>{{ __('2 minutes') }}</strong>. {{ __('Vos E-Billets avec QR Code officiel sont générés instantanément.') }}
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right; min-width: 110px;">
                                    <span class="badge badge-road" style="font-size: 0.75rem;"><i class="fa-solid fa-stopwatch"></i> {{ __('Délai 2 min') }}</span>
                                </div>
                            </div>
                        </label>

                        <!-- Option 2: Advance Reservation (500 FCFA hold fee) -->
                        <label style="border: 2px solid {{ $isEligibleForAdvance ? 'var(--border-color)' : 'var(--border-color); opacity: 0.6;' }}; border-radius: var(--radius-md); padding: 16px; cursor: {{ $isEligibleForAdvance ? 'pointer' : 'not-allowed' }}; display: block; background: var(--bg-card);" class="booking-mode-card" id="modeReserveCard">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                                <div style="display: flex; align-items: flex-start; gap: 12px;">
                                    <input type="radio" name="booking_option" value="reserve" {{ !$isEligibleForAdvance ? 'disabled' : '' }} onchange="toggleBookingMode('reserve')" style="margin-top: 4px; accent-color: var(--primary);">
                                    <div>
                                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                                            {{ __('Pré-Réserver la Place (Frais de Réservation : 500 FCFA)') }}
                                            <span class="badge badge-warning" style="font-size: 0.7rem; margin-left: 6px;">{{ __('Bloquer le fauteuil') }}</span>
                                        </div>
                                        <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 6px; line-height: 1.5;">
                                            {{ __('Bloquez vos places dès maintenant en réglant uniquement les') }} <strong>{{ __('frais de réservation de 500 FCFA') }}</strong> ({{ __('sous 2 minutes') }}).
                                            <ul style="margin: 6px 0 0 18px; padding: 0; font-size: 0.78rem; color: var(--text-main);">
                                                <li>{{ __('Rappel automatique par SMS à 8h du départ.') }}</li>
                                                <li>{{ __('À 6h du départ, le solde doit être payé sous peine de libération du fauteuil.') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right; min-width: 110px;">
                                    <div style="font-size: 1.15rem; font-weight: 900; color: var(--warning);">500 FCFA</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Frais de blocage') }}</div>
                                    @if(!$isEligibleForAdvance)
                                        <span class="badge badge-danger" style="font-size: 0.65rem; margin-top: 4px;">{{ __('Départ < 8h') }}</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Hidden inputs dynamically updated for submission -->
                <div id="hiddenSeatsInputs"></div>

                <div class="sidebar-mobile-toggle" style="margin-top: 18px;">
                    @auth
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800;">
                            {{ __('Continuer vers le Paiement Mobile Money') }} &rarr;
                        </button>
                    @else
                        <a href="{{ route('register.passenger') }}" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800; display: block; text-align: center;">
                            <i class="fa-solid fa-user-plus"></i> {{ __('Créer un Compte pour Réserver') }} &rarr;
                        </a>
                    @endauth
                </div>
            </form>
        </div>

        <!-- Right Col: Sticky Booking Summary Card -->
        <div class="booking-summary-col">
            <div class="card card-glass" style="padding: 24px; box-shadow: var(--shadow-lg); position: sticky; top: 90px;">
                <h3 style="font-size: 1.2rem; color: var(--text-heading); margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-weight: 800;">
                    {{ __('Récapitulatif de Réservation') }}
                </h3>

                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">{{ __('Trajet :') }}</span>
                    <strong style="color: var(--text-heading);">{{ $trip->departure_city }} &rarr; {{ $trip->arrival_city }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">{{ __('Convocation :') }}</span>
                    <strong style="color: var(--success);">{{ $convocationTime->format('H:i') }} ({{ $trip->departure_time->format('d/m/Y') }})</strong>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">{{ __('Départ Officiel :') }}</span>
                    <strong style="color: var(--text-heading);">{{ $trip->departure_time->format('H:i') }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">{{ __('Fauteuils Retenus :') }}</span>
                    <strong id="selectedSeatsList" style="color: var(--primary);">{{ $firstAvailableSeat ?? 'S-02' }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">{{ __('Tarif Unitaire :') }}</span>
                    <strong id="unitPriceDisplay" style="color: var(--text-heading);">{{ number_format($trip->base_price, 0, ',', ' ') }} FCFA</strong>
                </div>

                <div id="reservationFeeBreakdown" style="display: none; background: var(--bg-surface); border: 1px dashed var(--warning-border); border-radius: var(--radius-md); padding: 12px; margin-bottom: 12px; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: var(--text-muted);">{{ __('Prix Billet (solde à 6h du départ) :') }}</span>
                        <strong id="ticketBalanceDisplay" style="color: var(--text-heading);">{{ number_format($trip->base_price, 0, ',', ' ') }} FCFA</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--warning); font-weight: 700;">
                        <span>{{ __('Frais de Réservation Immédiats :') }}</span>
                        <span>500 FCFA</span>
                    </div>
                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px; line-height: 1.3;">
                        {{ __('Frais de garantie de fauteuil non déductibles du prix du billet.') }}
                    </div>
                </div>

                <div style="border-top: 2px solid var(--border-color); padding-top: 16px; margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-heading);" id="totalPayLabel">{{ __('Total à Régler Maintenant :') }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);" id="paymentDelayHint">{{ __('Délai : 2 minutes pour finaliser') }}</div>
                    </div>
                    <span id="totalPriceDisplay" style="font-size: 1.6rem; font-weight: 900; color: var(--primary); font-family: var(--font-heading);">
                        {{ number_format($trip->base_price, 0, ',', ' ') }} FCFA
                    </span>
                </div>

                <div style="margin-top: 24px;">
                    @auth
                        <button type="button" onclick="document.getElementById('bookingForm').submit()" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800; height: 50px;" id="mainSubmitBtn">
                            <i class="fa-solid fa-lock"></i> <span id="submitBtnText">{{ __('Valider et Payer sous 2 min') }}</span>
                        </button>
                    @else
                        <div style="background: var(--primary-50); border: 1.5px dashed var(--primary); border-radius: var(--radius-md); padding: 14px; text-align: center; margin-bottom: 12px;">
                            <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); margin-bottom: 4px;">
                                <i class="fa-solid fa-user-lock"></i> {{ __('Compte Requis') }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 10px;">
                                {{ __('Inscrivez-vous pour bloquer vos places et régler en Mobile Money.') }}
                            </div>
                            <a href="{{ route('auth.google', ['redirect' => url()->current()]) }}" class="btn btn-outline btn-sm" style="width: 100%; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; justify-content: center; gap: 8px; background: #ffffff;">
                                <svg width="15" height="15" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                {{ __('Continuer avec Google') }}
                            </a>
                            <a href="{{ route('register.passenger') }}" class="btn btn-primary btn-sm" style="width: 100%; font-weight: 800; margin-bottom: 6px;">
                                <i class="fa-solid fa-user-plus"></i> {{ __('Créer un Compte Gratuit') }}
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline btn-sm" style="width: 100%; font-weight: 700;">
                                <i class="fa-solid fa-right-to-bracket"></i> {{ __('Connexion E-mail') }}
                            </a>
                        </div>
                    @endauth
                </div>

                <div style="text-align: center; margin-top: 14px; font-size: 0.75rem; color: var(--text-muted);">
                    <i class="fa-solid fa-shield-halved text-primary"></i> {{ __('Paiement sécurisé certifié Orange Money & MTN MoMo') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Structured seats metadata from controller
const SEATS_DATA = @json($seats);
const SEATS_DICT = {};
SEATS_DATA.forEach(s => { SEATS_DICT[s.code] = s; });

const defaultFirstSeat = "{{ $firstAvailableSeat ?? 'S-02' }}";
let selectedSeats = defaultFirstSeat ? [defaultFirstSeat] : [];
let currentUnitPrice = {{ $trip->base_price }};
let currentBookingMode = 'immediate';

function getSeatDescription(seatCode) {
    const s = SEATS_DICT[seatCode];
    if (!s) return seatCode;
    const pos = s.is_window ? "{{ __('Fenêtre') }}" : (s.is_aisle ? "{{ __('Couloir') }}" : "{{ __('Milieu') }}");
    return `${seatCode} (R.${s.row} • ${pos})`;
}

/**
 * Two-way binding between Three.js WebGL 3D Bus Engine and Laravel Booking Form
 */
window.sync3DSeatSelection = function(seatCode, newSelectedList) {
    selectedSeats = newSelectedList;
    refreshSeatsVisualBadges();
    updateBookingSummary();
    renderSelectedChips();
    renderPassengerInputs();
};

function removeSeat(seatCode) {
    if (selectedSeats.length === 1) {
        alert("{{ __('Vous devez conserver au moins 1 fauteuil sélectionné.') }}");
        return;
    }
    
    // Toggle in Three.js Engine if loaded
    if (typeof bus3DEngine !== 'undefined' && bus3DEngine) {
        bus3DEngine.toggleSeatSelection(seatCode);
    } else {
        const idx = selectedSeats.indexOf(seatCode);
        if (idx > -1) selectedSeats.splice(idx, 1);
        refreshSeatsVisualBadges();
        updateBookingSummary();
        renderSelectedChips();
        renderPassengerInputs();
    }
}

function resetSeatsToDefault() {
    if (typeof bus3DEngine !== 'undefined' && bus3DEngine) {
        bus3DEngine.selectedSeats.clear();
        if (defaultFirstSeat) bus3DEngine.selectedSeats.add(defaultFirstSeat);
        bus3DEngine.refreshSeatVisuals();
        selectedSeats = defaultFirstSeat ? [defaultFirstSeat] : [];
    } else {
        selectedSeats = defaultFirstSeat ? [defaultFirstSeat] : [];
    }

    refreshSeatsVisualBadges();
    updateBookingSummary();
    renderSelectedChips();
    renderPassengerInputs();
}

function toggleBookingMode(mode) {
    currentBookingMode = mode;

    const immCard = document.getElementById('modeImmediateCard');
    const resCard = document.getElementById('modeReserveCard');

    if (mode === 'reserve') {
        if (immCard) {
            immCard.style.borderColor = 'var(--border-color)';
            immCard.style.background = 'var(--bg-card)';
        }
        if (resCard) {
            resCard.style.borderColor = '#D97706';
            resCard.style.background = 'var(--warning-50)';
        }
    } else {
        if (immCard) {
            immCard.style.borderColor = 'var(--primary)';
            immCard.style.background = 'var(--primary-50)';
        }
        if (resCard) {
            resCard.style.borderColor = 'var(--border-color)';
            resCard.style.background = 'var(--bg-card)';
        }
    }

    updateBookingSummary();
}

function refreshSeatsVisualBadges() {
    const counter = document.getElementById('selectedSeatsCounter');
    if (counter) counter.textContent = selectedSeats.length;
}

function renderSelectedChips() {
    const container = document.getElementById('selectedSeatChips');
    if (!container) return;

    let html = '';
    selectedSeats.forEach((seat, idx) => {
        const desc = getSeatDescription(seat);
        html += `
            <div class="selected-seat-chip" onclick="if(typeof bus3DEngine !== 'undefined' && bus3DEngine) bus3DEngine.flyToFirstPersonSeat('${seat}')" title="{{ __('Cliquer pour la vue 3D à bord depuis ce fauteuil') }}" style="cursor: pointer;">
                <span class="chip-passenger">P${idx + 1}</span>
                <span>${desc}</span>
                <i class="fa-solid fa-eye text-primary" style="font-size: 0.75rem; margin-left: 2px;" title="{{ __('Vue 3D') }}"></i>
                ${selectedSeats.length > 1 ? `<button type="button" class="chip-remove-btn" onclick="event.stopPropagation(); removeSeat('${seat}')" title="{{ __('Retirer ce fauteuil') }}">&times;</button>` : ''}
            </div>
        `;
    });
    container.innerHTML = html;
}

const activeLocale = '{{ app()->getLocale() }}';
const formatCurrency = (val) => new Intl.NumberFormat(activeLocale === 'en' ? 'en-US' : 'fr-FR').format(val) + ' FCFA';

function updateBookingSummary() {
    const count = selectedSeats.length;
    const ticketTotal = count * currentUnitPrice;
    
    const selectedSeatsList = document.getElementById('selectedSeatsList');
    if (selectedSeatsList) selectedSeatsList.textContent = selectedSeats.join(', ');

    const unitPriceDisplay = document.getElementById('unitPriceDisplay');
    if (unitPriceDisplay) unitPriceDisplay.textContent = formatCurrency(currentUnitPrice);
    
    const feeBreakdown = document.getElementById('reservationFeeBreakdown');
    const ticketBalanceDisplay = document.getElementById('ticketBalanceDisplay');
    const totalPayLabel = document.getElementById('totalPayLabel');
    const paymentDelayHint = document.getElementById('paymentDelayHint');
    const submitBtnText = document.getElementById('submitBtnText');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');

    if (currentBookingMode === 'reserve') {
        if (feeBreakdown) feeBreakdown.style.display = 'block';
        if (ticketBalanceDisplay) ticketBalanceDisplay.textContent = formatCurrency(ticketTotal);
        if (totalPayLabel) totalPayLabel.textContent = "{{ __('Frais de Réservation à Payer :') }}";
        if (paymentDelayHint) paymentDelayHint.textContent = "{{ __('Délai 2 min pour bloquer les fauteuils') }}";
        if (totalPriceDisplay) totalPriceDisplay.textContent = '500 FCFA';
        if (submitBtnText) submitBtnText.textContent = "{{ __('Réserver ma Place (500 FCFA)') }}";
    } else {
        if (feeBreakdown) feeBreakdown.style.display = 'none';
        if (totalPayLabel) totalPayLabel.textContent = "{{ __('Total à Régler Maintenant :') }}";
        if (paymentDelayHint) paymentDelayHint.textContent = "{{ __('Délai : 2 minutes pour finaliser') }}";
        if (totalPriceDisplay) totalPriceDisplay.textContent = formatCurrency(ticketTotal);
        if (submitBtnText) submitBtnText.textContent = "{{ __('Valider et Payer sous 2 min') }}";
    }
    
    // Update hidden inputs for submission
    const container = document.getElementById('hiddenSeatsInputs');
    if (container) {
        container.innerHTML = '';
        
        const countInput = document.createElement('input');
        countInput.type = 'hidden';
        countInput.name = 'seats_count';
        countInput.value = count;
        container.appendChild(countInput);

        const modeInput = document.createElement('input');
        modeInput.type = 'hidden';
        modeInput.name = 'booking_option';
        modeInput.value = currentBookingMode;
        container.appendChild(modeInput);
        
        selectedSeats.forEach(s => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'seat_numbers[]';
            inp.value = s;
            container.appendChild(inp);
        });
    }
}

function renderPassengerInputs() {
    const container = document.getElementById('passengersContainer');
    if (!container) return;

    let html = '';
    const txtPassenger = "{{ __('Voyageur') }}";
    const txtSeat = "{{ __('Fauteuil 3D Attribué :') }}";
    const txtFullName = "{{ __('Nom Complet (selon pièce d\'identité) *') }}";
    const txtFullNamePh = "{{ __('Nom & Prénom officiel') }}";
    const txtCni = "{{ __('N° CNI / Passeport / Titre *') }}";
    const txtCniPh = "{{ __('N° CNI requis') }}";
    const txtPhone = "{{ __('Téléphone *') }}";
    
    selectedSeats.forEach((seat, idx) => {
        const desc = getSeatDescription(seat);
        html += `
            <div class="passenger-item" style="background: var(--bg-surface); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                    <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                        <span class="badge badge-primary" style="font-size: 0.75rem;">P${idx + 1}</span>
                        ${txtPassenger} ${idx + 1}
                    </div>
                    <div style="font-size: 0.82rem; color: var(--primary); font-weight: 800;">
                        <i class="fa-solid fa-chair"></i> ${txtSeat} <span style="text-decoration: underline;">${desc}</span>
                    </div>
                </div>
                <div class="passenger-inputs-grid">
                    <div>
                        <label class="form-label" style="font-size: 0.8rem;">${txtFullName}</label>
                        <input type="text" name="passengers[${idx}][name]" class="form-control" required placeholder="${txtFullNamePh}" value="${idx === 0 ? '{{ Auth::user()->name ?? '' }}' : ''}">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem;">${txtCni}</label>
                        <input type="text" name="passengers[${idx}][cni]" class="form-control" required placeholder="${txtCniPh}">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem;">${txtPhone}</label>
                        <input type="text" name="passengers[${idx}][phone]" class="form-control" required placeholder="6XX XX XX XX" value="${idx === 0 ? '{{ Auth::user()->phone ?? '' }}' : ''}">
                    </div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    refreshSeatsVisualBadges();
    renderSelectedChips();
    renderPassengerInputs();
    updateBookingSummary();
});
</script>
@endsection
