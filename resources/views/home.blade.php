@extends('layouts.app')

@section('title', 'Real Voyage - ' . __('Transport Interurbain & Fret Routier Sécurisé'))

@section('content')
<!-- Ambient Multimodal Transport Hero Section -->
<section class="hero-transport-bg">
    <div class="container" style="position: relative; z-index: 10;">
        <!-- Pre-header: Accreditation & Reliability -->
        <div style="text-align: center; max-width: 820px; margin: 0 auto 32px;">
            <div style="display: inline-flex; align-items: center; gap: 9px; background: var(--bg-card); border: 1px solid var(--border-color); padding: 6px 16px; border-radius: var(--radius-full); font-size: 0.78rem; font-weight: 700; color: var(--text-heading); margin-bottom: 20px; box-shadow: var(--shadow-xs); letter-spacing: 0.04em; text-transform: uppercase;">
                <span class="live-beacon-dot"></span>
                <span>{{ __('Réseau Interurbain Real Voyage') }}</span>
                <span style="color: var(--border-color);">•</span>
                <span style="color: var(--text-muted); font-weight: 600;">{{ __('Homologation MINT & Sécurité Routière') }}</span>
            </div>

            <h1 style="font-size: clamp(2.2rem, 4.2vw, 3.4rem); font-weight: 900; line-height: 1.15; color: var(--text-heading); margin-bottom: 16px; letter-spacing: -0.03em;">
                {{ __('Voyagez l\'esprit tranquille avec Real Voyage') }}
            </h1>

            <p style="font-size: clamp(0.98rem, 1.6vw, 1.15rem); color: var(--text-muted); font-weight: 500; line-height: 1.6; max-width: 660px; margin: 0 auto;">
                {{ __('Réservez vos places assises dans notre flotte d\'autocars VIP (75 & 80 places) sur les liaisons Douala, Yaoundé et Ouest. Règlement instantané par Orange Money & MTN MoMo.') }}
            </p>
        </div>

        <!-- Multimodal Search Console (Bespoke Travel Command Deck) -->
        <div class="transit-console-card" style="max-width: 1020px; margin: 0 auto;">
            <!-- Mode Selector Tabs -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
                <div class="console-mode-tabs" id="consoleModeTabs">
                    <button type="button" class="console-mode-tab active" onclick="setSearchMode('', this)">
                        <i class="fa-solid fa-bus"></i> {{ __('Tous les Départs (10h00 / 21h30)') }}
                    </button>
                    <button type="button" class="console-mode-tab" onclick="setSearchMode('road', this)">
                        <i class="fa-solid fa-couch"></i> {{ __('Autocars VIP (75/80 pl.)') }}
                    </button>
                    <a href="{{ route('passenger.shipments.create') }}" class="console-mode-tab">
                        <i class="fa-solid fa-box-open"></i> {{ __('Fret & Colis') }}
                    </a>
                </div>

                <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-bolt" style="color: #F59E0B;"></i> {{ __('Réservation avec choix de place 3D') }}
                </div>
            </div>

            <!-- Form Grid -->
            <form action="{{ route('trips.index') }}" method="GET" id="searchForm">
                <input type="hidden" name="mode" id="modeInput" value="{{ request('mode', '') }}">
                <div class="transit-console-row">
                    <!-- Origin Field -->
                    <div class="console-field">
                        <label class="console-field-label">{{ __('Ville de Départ') }}</label>
                        <select name="departure" id="departureSelect" class="console-select">
                            <option value="">{{ __('Toutes les villes') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ $city === 'Douala' ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                        <div class="console-subtext" id="departureStationTag">{{ __('Gares & Agences de départ') }}</div>
                    </div>

                    <!-- Interchange Swap Button -->
                    <div style="padding: 0 4px; display: flex; align-items: center; justify-content: center;">
                        <button type="button" class="console-swap-btn" onclick="swapCities()" title="{{ __('Permuter les villes') }}" aria-label="{{ __('Permuter les villes') }}">
                            <i class="fa-solid fa-arrow-right-arrow-left" style="font-size: 0.82rem;"></i>
                        </button>
                    </div>

                    <!-- Destination Field -->
                    <div class="console-field">
                        <label class="console-field-label">{{ __('Destination') }}</label>
                        <select name="arrival" id="arrivalSelect" class="console-select">
                            <option value="">{{ __('Toutes les destinations') }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ $city === 'Yaoundé' ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                        <div class="console-subtext" id="arrivalStationTag">{{ __('Gares & Agences d\'arrivée') }}</div>
                    </div>

                    <!-- Travel Date Field -->
                    <div class="console-field">
                        <label class="console-field-label">{{ __('Date de Voyage') }}</label>
                        <input type="date" name="date" id="travelDateInput" class="console-input" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                        <div style="display: flex; gap: 6px; margin-top: 2px;">
                            <span onclick="setDateQuick('today')" style="cursor: pointer; font-size: 0.68rem; font-weight: 700; color: var(--primary); text-transform: uppercase;">{{ __('Aujourd\'hui') }}</span>
                            <span style="font-size: 0.68rem; color: var(--border-color);">•</span>
                            <span onclick="setDateQuick('tomorrow')" style="cursor: pointer; font-size: 0.68rem; font-weight: 700; color: var(--primary); text-transform: uppercase;">{{ __('Demain') }}</span>
                        </div>
                    </div>

                    <!-- Submit CTA -->
                    <button type="submit" class="console-submit-btn">
                        <span>{{ __('Consulter les Départs') }}</span>
                        <i class="fa-solid fa-arrow-right" style="font-size: 0.85rem;"></i>
                    </button>
                </div>
            </form>

            <!-- Route Quick Chips -->
            <div class="route-quick-chips">
                <span style="font-weight: 700; font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">{{ __('Lignes Régulières :') }}</span>
                <a href="{{ route('trips.index', ['departure' => 'Douala', 'arrival' => 'Yaoundé']) }}" class="route-chip">
                    Douala ⇄ Yaoundé <span style="color: var(--text-light); font-size: 0.7rem;">(N3)</span>
                </a>
                <a href="{{ route('trips.index', ['departure' => 'Yaoundé', 'arrival' => 'Bafoussam']) }}" class="route-chip">
                    <i class="fa-solid fa-bus" style="font-size: 0.7rem;"></i> Yaoundé ⇄ Bafoussam <span style="color: var(--text-light); font-size: 0.7rem;">(Centre-Ouest)</span>
                </a>
                <a href="{{ route('trips.index', ['departure' => 'Douala', 'arrival' => 'Bafoussam']) }}" class="route-chip">
                    Douala ⇄ Bafoussam <span style="color: var(--text-light); font-size: 0.7rem;">(Ouest)</span>
                </a>
                <a href="{{ route('trips.index', ['departure' => 'Yaoundé', 'arrival' => 'Kribi']) }}" class="route-chip">
                    Yaoundé ⇄ Kribi <span style="color: var(--text-light); font-size: 0.7rem;">(Autoroute)</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Operational Network Precision Bar -->
<section style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 22px 0;">
    <div class="container">
        <div class="grid grid-cols-4" style="gap: 24px; align-items: center;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: var(--text-heading); line-height: 1;">
                    {{ $stats['total_trips'] ?? 24 }}+
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-heading);">{{ __('Rotations Quotidiennes') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Liaisons routières & ferroviaires') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; border-left: 1px solid var(--border-color); padding-left: 20px;">
                <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: var(--text-heading); line-height: 1;">
                    11
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-heading);">{{ __('Gares & Terminaux') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Littoral • Centre • Grand Nord') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; border-left: 1px solid var(--border-color); padding-left: 20px;">
                <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: #10B981; line-height: 1;">
                    {{ $stats['avg_punctuality'] ?? 98 }}%
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-heading);">{{ __('Taux de Ponctualité') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Contrôle télématique en direct') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; border-left: 1px solid var(--border-color); padding-left: 20px;">
                <div style="font-size: 1.8rem; font-weight: 800; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: #D97706; line-height: 1;">
                    {{ $stats['rating_avg'] ?? 4.8 }}/5
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-heading);">{{ __('Satisfaction Voyageurs') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Avis certifiés sur billets scannés') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Station Departure Board ("Tableau des Départs en Temps Réel") -->
<section style="padding: 60px 0;">
    <div class="container">
        <!-- Section Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span class="live-beacon-dot"></span>
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                        {{ __('Tableau des Départs en Temps Réel') }}
                    </span>
                </div>
                <h2 style="font-size: clamp(1.6rem, 2.5vw, 2rem); color: var(--text-heading); font-weight: 800; margin: 0;">
                    {{ __('Prochains Départs Prévus') }}
                </h2>
            </div>

            <a href="{{ route('trips.index') }}" class="btn btn-outline" style="font-weight: 700; font-size: 0.88rem;">
                {{ __('Consulter tous les horaires') }} <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
            </a>
        </div>

        <!-- Departure Terminal Cards Grid -->
        <div class="grid grid-cols-3" style="gap: 20px;">
            @forelse($featuredTrips as $trip)
                <div class="departure-terminal-card">
                    <div>
                        <!-- Top Metadata: Service Type & Trip Code -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="badge badge-road"><i class="fa-solid fa-bus"></i> {{ $trip->vehicle->name ?? __('Autocar VIP 75/80 pl.') }}</span>
                            </div>

                            <span style="font-family: monospace; font-size: 0.74rem; font-weight: 700; color: var(--text-muted); background: var(--bg-surface); padding: 2px 8px; border-radius: var(--radius-xs); border: 1px solid var(--border-color);">
                                {{ $trip->trip_code }}
                            </span>
                        </div>

                        <!-- Departure Track Line -->
                        <div class="transit-track-line">
                            <div>
                                <div class="tabular-time">{{ $trip->departure_time->format('H:i') }}</div>
                                <div style="font-weight: 800; font-size: 0.88rem; color: var(--text-heading); margin-top: 2px;">{{ $trip->departure_city }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px;">
                                    {{ $trip->departure_station }}
                                </div>
                            </div>

                            <div class="track-divider">
                                <span class="track-duration">{{ __('Liaison Directe') }}</span>
                                <div class="track-line"></div>
                                <span style="font-size: 0.65rem; color: var(--text-light); font-weight: 600;">
                                    {{ $trip->departure_time->format('d M') }}
                                </span>
                            </div>

                            <div style="text-align: right;">
                                <div class="tabular-time" style="color: var(--text-muted);">
                                    {{ $trip->arrival_time_estimated->format('H:i') }}
                                </div>
                                <div style="font-weight: 800; font-size: 0.88rem; color: var(--text-heading); margin-top: 2px;">{{ $trip->arrival_city }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px;">
                                    {{ $trip->arrival_station }}
                                </div>
                            </div>
                        </div>

                        <!-- Amenities & Live Availability -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; gap: 6px;">
                                <span class="amenity-pill"><i class="fa-solid fa-snowflake"></i> Climatiseur</span>
                                <span class="amenity-pill"><i class="fa-solid fa-bolt"></i> Prises</span>
                            </div>

                            <div style="font-size: 0.78rem; font-weight: 700; color: {{ $trip->seats_available > 5 ? '#10B981' : '#D97706' }};">
                                <i class="fa-solid fa-chair"></i> {{ $trip->seats_available }} {{ __('Places libres') }}
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Price & Booking Action -->
                    <div style="border-top: 1px solid var(--border-color); padding-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ __('Tarif Billet') }}</div>
                            <div style="font-size: 1.35rem; font-weight: 900; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: var(--text-heading);">
                                {{ number_format($trip->base_price, 0, ',', ' ') }} <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted);">FCFA</span>
                            </div>
                        </div>

                        <a href="{{ route('trips.show', $trip) }}" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 8px 16px;">
                            {{ __('Sélectionner') }} <i class="fa-solid fa-chevron-right" style="font-size: 0.68rem;"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 48px;">
                    <p style="color: var(--text-muted);">{{ __('Aucun départ programmé actuellement.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Asymmetric Quality & Freight Operations Showcase -->
<section style="background: var(--bg-surface); padding: 64px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="grid grid-cols-2" style="gap: 36px; align-items: stretch;">
            <!-- Left: Operational Integrity & Regulatory Standards -->
            <div class="card" style="padding: 32px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span class="badge badge-primary">{{ __('Normes Institutionnelles') }}</span>
                    </div>
                    <h3 style="font-size: 1.45rem; font-weight: 800; color: var(--text-heading); line-height: 1.25; margin-bottom: 12px;">
                        {{ __('Sécurité Passagers & Cadre Réglementaire Homologué') }}
                    </h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6; margin-bottom: 24px;">
                        {{ __('Chaque rotation d\'autocar ou de train est auditée et surveillée conformément aux directives du Ministère des Transports du Cameroun (MINT).') }}
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <strong style="font-size: 0.9rem; color: var(--text-heading); display: block;">{{ __('Contrôle Technique des Véhicules & Équipages') }}</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Inspections pré-départ et repos obligatoire des conducteurs sur l\'axe Douala-Yaoundé.') }}</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <strong style="font-size: 0.9rem; color: var(--text-heading); display: block;">{{ __('Garantie d\'Annulation Flexible jusqu\'à 6h') }}</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Remboursement direct et instantané sur votre portefeuille électronique en cas d\'imprévu.') }}</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <strong style="font-size: 0.9rem; color: var(--text-heading); display: block;">{{ __('Paiements Certifiés Orange Money & MTN MoMo') }}</strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Cryptage TLS bancaire et génération d\'e-billet infalsifiable avec QR code sécurisé.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 18px; margin-top: 24px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted);">{{ __('Partenaire Agréé MTN MoMo & Orange Money') }}</span>
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--primary);">{{ __('Règlement 100% Sans Frais Cachés') }}</span>
                </div>
            </div>

            <!-- Right: High-Security Freight & Logistics Terminal -->
            <div class="card" style="padding: 32px; background: var(--bg-dark); color: #FFFFFF; border: none; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span class="badge" style="background: rgba(255,255,255,0.15); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.2);">
                            <i class="fa-solid fa-box"></i> {{ __('Fret & Messagerie Express') }}
                        </span>
                    </div>

                    <h3 style="font-size: 1.45rem; font-weight: 800; color: #FFFFFF; line-height: 1.25; margin-bottom: 12px;">
                        {{ __('Expédiez vos colis et plis avec traçabilité par code OTP') }}
                    </h3>

                    <p style="color: #94A3B8; font-size: 0.92rem; line-height: 1.6; margin-bottom: 24px;">
                        {{ __('Vos colis voyagent dans les soutes sécurisées de nos véhicules ou par wagon fret Camrail avec remise contre code secret unique.') }}
                    </p>

                    <!-- 3-Step Process -->
                    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-family: monospace; font-size: 0.8rem; font-weight: 800; background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2);">01</span>
                            <span style="font-size: 0.85rem; color: #E2E8F0;"><strong style="color: white;">{{ __('1. Récépissé :') }}</strong> {{ __('L\'expéditeur règle les frais par Orange Money ou MTN MoMo.') }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-family: monospace; font-size: 0.8rem; font-weight: 800; background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2);">02</span>
                            <span style="font-size: 0.85rem; color: #E2E8F0;"><strong style="color: white;">{{ __('2. Code OTP SMS :') }}</strong> {{ __('Le destinataire reçoit un code secret à 4 chiffres.') }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-family: monospace; font-size: 0.8rem; font-weight: 800; background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2);">03</span>
                            <span style="font-size: 0.85rem; color: #E2E8F0;"><strong style="color: white;">{{ __('3. Retrait en Gare :') }}</strong> {{ __('Présentation du code OTP et de la CNI au guichet.') }}</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 20px;">
                    <a href="{{ route('passenger.shipments.create') }}" class="btn" style="background: #FFFFFF; color: #0F172A; font-weight: 700;">
                        <i class="fa-solid fa-paper-plane"></i> {{ __('Déclarer une Expédition') }}
                    </a>
                    <a href="{{ route('shipments.track') }}" class="btn btn-outline" style="border-color: rgba(255,255,255,0.3); color: #FFFFFF; background: transparent;">
                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('Suivre un Colis') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function swapCities() {
    const dep = document.getElementById('departureSelect');
    const arr = document.getElementById('arrivalSelect');
    const temp = dep.value;
    dep.value = arr.value;
    arr.value = temp;
}

function setSearchMode(mode, btn) {
    document.getElementById('modeInput').value = mode;
    const tabs = document.querySelectorAll('#consoleModeTabs .console-mode-tab');
    tabs.forEach(t => t.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

function setDateQuick(type) {
    const dateInput = document.getElementById('travelDateInput');
    const now = new Date();
    if (type === 'tomorrow') {
        now.setDate(now.getDate() + 1);
    }
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    dateInput.value = `${yyyy}-${mm}-${dd}`;
}
</script>
@endsection
