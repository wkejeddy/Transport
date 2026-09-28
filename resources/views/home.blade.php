@extends('layouts.app')

@section('title', 'Real Express Voyages - ' . __('Transport Interurbain VIP & Fret Routier'))

@section('content')
<!-- Estate Haven Styled Luxury Architectural Hero Section -->
<section class="haven-hero-wrapper">
    <div class="container">
        <div class="haven-hero-card">
            <div class="haven-hero-overlay"></div>
            
            <div class="haven-hero-content">
                <!-- Accreditation & Official Slots Badge -->
                <div class="haven-tag-pill">
                    <span class="live-beacon-dot"></span>
                    <span>{{ __('Real Express Voyages • Départs Officiels 10h00 & 21h00') }}</span>
                </div>

                <!-- Estate Haven Format Bold Title -->
                <h1 class="haven-hero-title">
                    {{ __('Voyagez Vers') }}<br>
                    <span>{{ __('Votre Destination') }}</span>
                </h1>

                <!-- Refined Two-Sentence Subtitle -->
                <p class="haven-hero-subtitle">
                    {{ __('Autocars VIP grand confort 75 & 80 places. Départs fixes 10h00 & 21h00.') }}<br>
                    {{ __('Trouvez le voyage qui vous ressemble avec réservation sécurisée et plan 3D.') }}
                </p>

                <!-- Primary Action Buttons -->
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <a href="#search-console" class="haven-btn-primary">
                        <span>{{ __('Explorer les Départs') }}</span>
                        <i class="fa-solid fa-arrow-right" style="font-size: 0.85rem;"></i>
                    </a>
                    <a href="{{ route('passenger.shipments.create') }}" class="haven-btn-pill-light" style="padding: 14px 24px; font-size: 0.95rem;">
                        <i class="fa-solid fa-box-open" style="color: #0028fc;"></i>
                        <span>{{ __('Fret & Colis Express') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Floating Architectural Search & Booking Deck -->
        <div class="haven-search-deck" id="search-console">
            <!-- Mode & Slot Filter Tabs -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
                <div class="console-mode-tabs" id="consoleModeTabs">
                    <button type="button" class="console-mode-tab active" onclick="setDepartureSlot('', this)">
                        <i class="fa-solid fa-bus"></i> {{ __('Tous les Départs') }}
                    </button>
                    <button type="button" class="console-mode-tab" onclick="setDepartureSlot('10:00', this)">
                        <i class="fa-solid fa-sun" style="color: #F59E0B;"></i> {{ __('Départ 10h00 (Matin)') }}
                    </button>
                    <button type="button" class="console-mode-tab" onclick="setDepartureSlot('21:00', this)">
                        <i class="fa-solid fa-moon" style="color: #6366F1;"></i> {{ __('Départ 21h00 (Soir)') }}
                    </button>
                </div>

                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-bolt" style="color: #F59E0B;"></i>
                    <span>{{ __('Réservation avec choix de place 3D') }}</span>
                </div>
            </div>

            <!-- Form Grid -->
            <form action="{{ route('trips.index') }}" method="GET" id="searchForm">
                <input type="hidden" name="slot" id="slotInput" value="{{ request('slot', '') }}">
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
                        <div style="display: flex; gap: 8px; margin-top: 3px;">
                            <span onclick="setDateQuick('today')" style="cursor: pointer; font-size: 0.7rem; font-weight: 700; color: #0028fc; text-transform: uppercase;">{{ __('Aujourd\'hui') }}</span>
                            <span style="font-size: 0.7rem; color: var(--border-color);">•</span>
                            <span onclick="setDateQuick('tomorrow')" style="cursor: pointer; font-size: 0.7rem; font-weight: 700; color: #0028fc; text-transform: uppercase;">{{ __('Demain') }}</span>
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
                <span style="font-weight: 800; font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">{{ __('Lignes Régulières :') }}</span>
                <a href="{{ route('trips.index', ['departure' => 'Douala', 'arrival' => 'Yaoundé']) }}" class="route-chip">
                    Douala ⇄ Yaoundé <span style="color: var(--text-light); font-size: 0.7rem;">(10h00 &bull; 21h00)</span>
                </a>
                <a href="{{ route('trips.index', ['departure' => 'Yaoundé', 'arrival' => 'Bafoussam']) }}" class="route-chip">
                    <i class="fa-solid fa-bus" style="font-size: 0.7rem;"></i> Yaoundé ⇄ Bafoussam <span style="color: var(--text-light); font-size: 0.7rem;">(VIP Ouest)</span>
                </a>
                <a href="{{ route('trips.index', ['departure' => 'Douala', 'arrival' => 'Bafoussam']) }}" class="route-chip">
                    Douala ⇄ Bafoussam <span style="color: var(--text-light); font-size: 0.7rem;">(Direct)</span>
                </a>
                <a href="{{ route('trips.index', ['departure' => 'Yaoundé', 'arrival' => 'Kribi']) }}" class="route-chip">
                    Yaoundé ⇄ Kribi <span style="color: var(--text-light); font-size: 0.7rem;">(Autoroute)</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Operational Network Precision Metrics -->
<section style="background: var(--bg-card); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 26px 0; margin-top: 48px;">
    <div class="container">
        <div class="grid grid-cols-4" style="gap: 24px; align-items: center;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="font-size: 1.9rem; font-weight: 900; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: var(--text-heading); line-height: 1;">
                    {{ $stats['total_trips'] ?? 24 }}+
                </div>
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-heading);">{{ __('Rotations Quotidiennes') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Départs fixes 10h00 & 21h00') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; border-left: 1px solid var(--border-color); padding-left: 20px;">
                <div style="font-size: 1.9rem; font-weight: 900; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: var(--text-heading); line-height: 1;">
                    11
                </div>
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-heading);">{{ __('Gares & Terminaux VIP') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Littoral • Centre • Ouest') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; border-left: 1px solid var(--border-color); padding-left: 20px;">
                <div style="font-size: 1.9rem; font-weight: 900; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: #10B981; line-height: 1;">
                    {{ $stats['avg_punctuality'] ?? 98.5 }}%
                </div>
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-heading);">{{ __('Taux de Ponctualité') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Départs à la minute certifiée') }}</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; border-left: 1px solid var(--border-color); padding-left: 20px;">
                <div style="font-size: 1.9rem; font-weight: 900; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: #D97706; line-height: 1;">
                    {{ $stats['rating_avg'] ?? 4.9 }}/5
                </div>
                <div>
                    <div style="font-size: 0.82rem; font-weight: 800; color: var(--text-heading);">{{ __('Satisfaction Voyageurs') }}</div>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Avis certifiés sur billets scannés') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Estate Haven Styled Featured Departures (Property Listing Aesthetic) -->
<section style="padding: 68px 0;">
    <div class="container">
        <!-- Section Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span class="live-beacon-dot"></span>
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted);">
                        {{ __('Tableau des Départs en Temps Réel') }}
                    </span>
                </div>
                <h2 style="font-size: clamp(1.8rem, 2.8vw, 2.2rem); color: var(--text-heading); font-weight: 850; margin: 0; letter-spacing: -0.03em;">
                    {{ __('Prochains Départs Prévus') }}
                </h2>
            </div>

            <a href="{{ route('trips.index') }}" class="haven-btn-pill-light" style="font-weight: 700; font-size: 0.88rem;">
                {{ __('Consulter tous les horaires') }} <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
            </a>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-3" style="gap: 24px;">
            @forelse($featuredTrips as $trip)
                <div class="haven-card" style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <!-- Top Metadata: Time Slot Badge & Trip Code -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @php
                                    $isMorning = $trip->departure_time->format('H:i') === '10:00';
                                @endphp
                                <span class="badge" style="background: {{ $isMorning ? 'rgba(245, 158, 11, 0.12)' : 'rgba(99, 102, 241, 0.12)' }}; color: {{ $isMorning ? '#D97706' : '#6366F1' }}; border: 1px solid {{ $isMorning ? 'rgba(245, 158, 11, 0.25)' : 'rgba(99, 102, 241, 0.25)' }}; font-weight: 800;">
                                    <i class="fa-solid {{ $isMorning ? 'fa-sun' : 'fa-moon' }}"></i> 
                                    {{ $trip->departure_time->format('H:i') }} ({{ $isMorning ? __('Matin') : __('Soir') }})
                                </span>
                                <span class="badge badge-road"><i class="fa-solid fa-bus"></i> {{ $trip->vehicle->name ?? __('VIP 75/80 pl.') }}</span>
                            </div>

                            <span style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); background: var(--bg-surface); padding: 3px 8px; border-radius: var(--radius-xs); border: 1px solid var(--border-color);">
                                {{ $trip->trip_code }}
                            </span>
                        </div>

                        <!-- Departure Track Line -->
                        <div class="transit-track-line" style="margin-bottom: 18px;">
                            <div>
                                <div class="tabular-time" style="font-size: 1.4rem;">{{ $trip->departure_time->format('H:i') }}</div>
                                <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading); margin-top: 2px;">{{ $trip->departure_city }}</div>
                                <div style="font-size: 0.74rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px;">
                                    {{ $trip->departure_station }}
                                </div>
                            </div>

                            <div class="track-divider">
                                <span class="track-duration">{{ __('Liaison Directe') }}</span>
                                <div class="track-line"></div>
                                <span style="font-size: 0.7rem; color: var(--text-light); font-weight: 700;">
                                    {{ $trip->departure_time->format('d M') }}
                                </span>
                            </div>

                            <div style="text-align: right;">
                                <div class="tabular-time" style="font-size: 1.4rem; color: var(--text-muted);">
                                    {{ $trip->arrival_time_estimated->format('H:i') }}
                                </div>
                                <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading); margin-top: 2px;">{{ $trip->arrival_city }}</div>
                                <div style="font-size: 0.74rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px;">
                                    {{ $trip->arrival_station }}
                                </div>
                            </div>
                        </div>

                        <!-- Amenities & Live Availability -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; gap: 6px;">
                                <span class="amenity-pill"><i class="fa-solid fa-snowflake"></i> Climatiseur</span>
                                <span class="amenity-pill"><i class="fa-solid fa-bolt"></i> Prises USB</span>
                            </div>

                            <div style="font-size: 0.8rem; font-weight: 700; color: {{ $trip->seats_available > 5 ? '#10B981' : '#D97706' }};">
                                <i class="fa-solid fa-chair"></i> {{ $trip->seats_available }} {{ __('Places libres') }}
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Price & Booking Action -->
                    <div style="border-top: 1px solid var(--border-color); padding-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.68rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em;">{{ __('Tarif Billet') }}</div>
                            <div style="font-size: 1.45rem; font-weight: 900; font-family: var(--font-heading); font-variant-numeric: tabular-nums; color: var(--text-heading);">
                                {{ number_format($trip->base_price, 0, ',', ' ') }} <span style="font-size: 0.74rem; font-weight: 700; color: var(--text-muted);">FCFA</span>
                            </div>
                        </div>

                        <a href="{{ route('trips.show', $trip) }}" class="haven-btn-primary" style="padding: 10px 20px; font-size: 0.88rem; border-radius: 9999px;">
                            <span>{{ __('Sélectionner') }}</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.72rem;"></i>
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

<!-- Quality, Passenger Safety & High-Security Freight Terminal -->
<section style="background: var(--bg-surface); padding: 70px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="grid grid-cols-2" style="gap: 36px; align-items: stretch;">
            <!-- Left: Operational Integrity & Regulatory Standards -->
            <div class="haven-card" style="padding: 36px; display: flex; flex-direction: column; justify-content: space-between; background: var(--bg-card);">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                        <span class="haven-tag-pill" style="margin-bottom: 0;">{{ __('Normes Institutionnelles') }}</span>
                    </div>
                    <h3 style="font-size: 1.55rem; font-weight: 850; color: var(--text-heading); line-height: 1.25; margin-bottom: 14px;">
                        {{ __('Sécurité Passagers & Cadre Réglementaire Homologué') }}
                    </h3>
                    <p style="color: var(--text-muted); font-size: 0.94rem; line-height: 1.6; margin-bottom: 24px;">
                        {{ __('Chaque rotation d\'autocar ou de train est auditée et surveillée conformément aux directives du Ministère des Transports du Cameroun (MINT).') }}
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.9rem;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <strong style="font-size: 0.92rem; color: var(--text-heading); display: block;">{{ __('Contrôle Technique des Véhicules & Équipages') }}</strong>
                                <span style="font-size: 0.82rem; color: var(--text-muted);">{{ __('Inspections pré-départ et repos obligatoire des conducteurs sur l\'axe Douala-Yaoundé.') }}</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.9rem;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <strong style="font-size: 0.92rem; color: var(--text-heading); display: block;">{{ __('Garantie d\'Annulation Flexible jusqu\'à 6h') }}</strong>
                                <span style="font-size: 0.82rem; color: var(--text-muted);">{{ __('Remboursement direct et instantané sur votre portefeuille électronique en cas d\'imprévu.') }}</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10B981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.9rem;">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <strong style="font-size: 0.92rem; color: var(--text-heading); display: block;">{{ __('Paiements Certifiés Orange Money & MTN MoMo') }}</strong>
                                <span style="font-size: 0.82rem; color: var(--text-muted);">{{ __('Cryptage TLS bancaire et génération d\'e-billet infalsifiable avec QR code sécurisé.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 28px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">{{ __('Partenaire Agréé MTN MoMo & Orange Money') }}</span>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #0028fc;">{{ __('Règlement 100% Sans Frais Cachés') }}</span>
                </div>
            </div>

            <!-- Right: High-Security Freight & Logistics Terminal -->
            <div class="haven-card" style="padding: 36px; background: #0F172A; color: #FFFFFF; border: none; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.4);">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                        <span class="badge" style="background: rgba(255,255,255,0.15); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.25);">
                            <i class="fa-solid fa-box"></i> {{ __('Fret & Messagerie Express') }}
                        </span>
                    </div>

                    <h3 style="font-size: 1.55rem; font-weight: 850; color: #FFFFFF; line-height: 1.25; margin-bottom: 14px;">
                        {{ __('Expédiez vos colis et plis avec traçabilité par code OTP') }}
                    </h3>

                    <p style="color: #94A3B8; font-size: 0.94rem; line-height: 1.6; margin-bottom: 26px;">
                        {{ __('Vos colis voyagent dans les soutes sécurisées de nos autocars VIP avec remise physique sous code secret unique.') }}
                    </p>

                    <!-- 3-Step Process (Tested Assertions Preserved) -->
                    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 28px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-family: monospace; font-size: 0.82rem; font-weight: 800; background: rgba(255,255,255,0.12); padding: 5px 11px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.25);">01</span>
                            <span style="font-size: 0.88rem; color: #E2E8F0;"><strong style="color: white;">{{ __('1. Récépissé :') }}</strong> {{ __('L\'expéditeur règle les frais par Orange Money ou MTN MoMo.') }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-family: monospace; font-size: 0.82rem; font-weight: 800; background: rgba(255,255,255,0.12); padding: 5px 11px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.25);">02</span>
                            <span style="font-size: 0.88rem; color: #E2E8F0;"><strong style="color: white;">{{ __('2. Code OTP SMS :') }}</strong> {{ __('Le destinataire reçoit un code secret à 4 chiffres.') }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-family: monospace; font-size: 0.82rem; font-weight: 800; background: rgba(255,255,255,0.12); padding: 5px 11px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.25);">03</span>
                            <span style="font-size: 0.88rem; color: #E2E8F0;"><strong style="color: white;">{{ __('3. Retrait en Gare :') }}</strong> {{ __('Présentation du code OTP et de la CNI au guichet.') }}</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 22px;">
                    <a href="{{ route('passenger.shipments.create') }}" class="btn" style="background: #FFFFFF; color: #0F172A; font-weight: 700; padding: 12px 22px; border-radius: 9999px;">
                        <i class="fa-solid fa-paper-plane"></i> {{ __('Déclarer une Expédition') }}
                    </a>
                    <a href="{{ route('shipments.track') }}" class="btn" style="border: 1px solid rgba(255,255,255,0.3); color: #FFFFFF; background: transparent; padding: 12px 22px; border-radius: 9999px;">
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

function setDepartureSlot(slot, btn) {
    document.getElementById('slotInput').value = slot;
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
