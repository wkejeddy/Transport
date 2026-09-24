@props([
    'seatMap',
    'trip',
])

@php
    $rows = $seatMap['rows'] ?? [];
    $totalSeats = $seatMap['total_seats'] ?? 75;
    $availableCount = $seatMap['available_count'] ?? 0;
    $bookedCount = $seatMap['booked_count'] ?? 0;
    $lockedCount = $seatMap['locked_count'] ?? 2;
    $seatsDict = $seatMap['seats'] ?? [];
@endphp

<div class="virtual-bus-container" id="virtualBusContainer">
    <!-- Virtual Bus Executive Control Bar -->
    <div class="vbus-top-bar">
        <div class="vbus-brand-info">
            <div class="vbus-badge-fleet">
                <i class="fa-solid fa-bus-simple"></i>
                <span>{{ $trip->vehicle->name ?? __('Autocar Grand Confort') }}</span>
                <span class="vbus-capacity-pill">{{ $totalSeats }} {{ __('Places') }}</span>
            </div>
            <div class="vbus-meta-route">
                <strong>{{ $trip->departure_city }} &rarr; {{ $trip->arrival_city }}</strong> &bull; N° {{ $trip->trip_number }}
            </div>
        </div>

        <!-- 3D Camera Controls & Perspective Selector -->
        <div class="vbus-camera-deck">
            <div class="camera-btn-group" role="group" aria-label="{{ __('Angles de caméra 3D') }}">
                <button type="button" class="cam-btn active" id="camIsometricBtn" onclick="setVirtualBusPerspective('isometric')" title="{{ __('Perspective 3D Isométrique') }}">
                    <i class="fa-solid fa-cube"></i>
                    <span>{{ __('3D Isométrique') }}</span>
                </button>
                <button type="button" class="cam-btn" id="camAisleBtn" onclick="setVirtualBusPerspective('aisle')" title="{{ __('Vue Allée Centrale / Immersion') }}">
                    <i class="fa-solid fa-street-view"></i>
                    <span>{{ __('Vue Cabine') }}</span>
                </button>
                <button type="button" class="cam-btn" id="camPlanBtn" onclick="setVirtualBusPerspective('plan')" title="{{ __('Vue Plan dessus 2D') }}">
                    <i class="fa-solid fa-map"></i>
                    <span>{{ __('Plan 2D') }}</span>
                </button>
            </div>

            <!-- Cabin Zone Jumper -->
            <div class="zone-jumper-group">
                <span class="zone-label">{{ __('Secteur :') }}</span>
                <button type="button" class="zone-pill active" onclick="jumpToBusZone('all', this)">{{ __('Tout') }}</button>
                <button type="button" class="zone-pill" onclick="jumpToBusZone('front', this)">{{ __('Avant') }}</button>
                <button type="button" class="zone-pill" onclick="jumpToBusZone('mid', this)">{{ __('Milieu') }}</button>
                <button type="button" class="zone-pill" onclick="jumpToBusZone('rear', this)">{{ __('Arrière') }}</button>
            </div>
        </div>
    </div>

    <!-- Live Status Legend Bar -->
    <div class="vbus-legend-strip">
        <div class="legend-item">
            <span class="legend-swatch swatch-available"></span>
            <span>{{ __('Libre (:count)', ['count' => $availableCount]) }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-swatch swatch-selected"></span>
            <span>{{ __('Votre Sélection') }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-swatch swatch-booked"></span>
            <span>{{ __('Occupé') }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-swatch swatch-crew"></span>
            <span>{{ __('Équipage (01 & 16)') }}</span>
        </div>
        <div class="legend-quick-tip">
            <i class="fa-regular fa-lightbulb"></i> {{ __('Survolez ou touchez un fauteuil pour voir ses caractéristiques en direct.') }}
        </div>
    </div>

    <!-- 3D Virtual Bus Stage -->
    <div class="vbus-viewport-stage perspective-isometric" id="vbusViewport">
        <!-- Live Floating Seat Inspector Tooltip -->
        <div class="seat-live-tooltip" id="seatLiveTooltip" style="display: none;">
            <div class="tooltip-header">
                <span class="tooltip-seat-num" id="ttSeatNum">S-00</span>
                <span class="tooltip-badge" id="ttBadge">{{ __('Disponible') }}</span>
            </div>
            <div class="tooltip-body">
                <div class="tooltip-row">
                    <span class="tt-label"><i class="fa-solid fa-arrows-left-right"></i> {{ __('Emplacement :') }}</span>
                    <strong id="ttPosition">{{ __('Fenêtre') }}</strong>
                </div>
                <div class="tooltip-row">
                    <span class="tt-label"><i class="fa-solid fa-layer-group"></i> {{ __('Rangée & Zone :') }}</span>
                    <strong id="ttZone">{{ __('Rangée 01 • Avant') }}</strong>
                </div>
                <div class="tooltip-amenities" id="ttAmenities">
                    <span><i class="fa-solid fa-bolt text-warning"></i> USB 5V</span>
                    <span><i class="fa-solid fa-snowflake text-primary"></i> Aérateurs AC</span>
                    <span><i class="fa-solid fa-angles-down text-success"></i> Inclinable</span>
                </div>
                <div class="tooltip-price" id="ttPriceRow">
                    <span class="tt-label">{{ __('Tarif :') }}</span>
                    <span class="tt-amount" id="ttPrice">{{ number_format($trip->base_price, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <!-- 3D Bus Exterior & Interior Model -->
        <div class="real-virtual-coach" id="realVirtualCoach">
            
            <!-- Left Side Aerodynamic Mirrors & Windows Tint Rim -->
            <div class="coach-side-rail coach-side-left">
                <div class="side-mirror mirror-left">
                    <div class="mirror-arm"></div>
                    <div class="mirror-glass"></div>
                </div>
                <div class="tinted-window-strip">
                    @for($w = 1; $w <= 8; $w++)
                        <div class="side-window-pane"></div>
                    @endfor
                </div>
            </div>

            <!-- Right Side Aerodynamic Mirrors & Passenger Entrance Doors -->
            <div class="coach-side-rail coach-side-right">
                <div class="side-mirror mirror-right">
                    <div class="mirror-arm"></div>
                    <div class="mirror-glass"></div>
                </div>
                <div class="tinted-window-strip">
                    @for($w = 1; $w <= 8; $w++)
                        <div class="side-window-pane"></div>
                    @endfor
                </div>
            </div>

            <!-- Front Coach Hood & Cockpit -->
            <div class="coach-front-hood">
                <!-- Front Bumper & Headlights -->
                <div class="front-bumper">
                    <div class="headlight headlight-left">
                        <div class="light-beam"></div>
                    </div>
                    <div class="radiator-grill">
                        <div class="grill-bars"></div>
                        <div class="grill-logo">
                            <i class="fa-solid fa-route"></i> REAL VOYAGE
                        </div>
                    </div>
                    <div class="headlight headlight-right">
                        <div class="light-beam"></div>
                    </div>
                </div>

                <!-- Curved Aerodynamic Panoramic Windshield -->
                <div class="panoramic-windshield">
                    <div class="windshield-glare-effect"></div>
                    <div class="windshield-wipers">
                        <span class="wiper wiper-l"></span>
                        <span class="wiper wiper-r"></span>
                    </div>
                    <div class="windshield-destination-board">
                        <span class="dest-blinker">&bull;</span>
                        <span class="dest-text">{{ strtoupper($trip->departure_city) }} &rarr; {{ strtoupper($trip->arrival_city) }} &bull; {{ $trip->departure_time->format('H:i') }}</span>
                    </div>
                </div>

                <!-- Driver Cockpit Compartment -->
                <div class="cockpit-cabin">
                    <!-- Seat 01: Driver (Strictly Locked) -->
                    <div class="cockpit-seat-col">
                        <div class="seat-armchair seat-state-locked" 
                             data-seat="S-01" 
                             data-num="1" 
                             data-pos="{{ __('Poste de Conduite') }}" 
                             data-zone="{{ __('Cockpit') }}" 
                             data-locked="true"
                             title="{{ __('Siège 01 : Chauffeur Real Voyage (Réservé)') }}">
                            <div class="armchair-headrest headrest-crew">
                                <i class="fa-solid fa-id-badge"></i>
                                <span>01</span>
                            </div>
                            <div class="armchair-backrest">
                                <span class="crew-designation">{{ __('CHAUFFEUR') }}</span>
                            </div>
                            <div class="armchair-cushion"></div>
                        </div>
                    </div>

                    <!-- Steering Wheel & Instrument Console -->
                    <div class="cockpit-dash-col">
                        <div class="steering-console">
                            <div class="steering-wheel-3d">
                                <i class="fa-solid fa-dharmachakra"></i>
                            </div>
                            <div class="dash-gauges">
                                <span class="gauge-dial speedo">85 km/h</span>
                                <span class="gauge-dial tacho">GPS ACTIF</span>
                            </div>
                        </div>
                    </div>

                    <!-- Front Boarding Door & Non-slip Stairs -->
                    <div class="cockpit-door-col">
                        <div class="entrance-door front-entrance" title="{{ __('Porte d\'Embarquement Avant') }}">
                            <div class="door-handle">
                                <i class="fa-solid fa-door-open"></i>
                            </div>
                            <div class="stairs-steps">
                                <span class="step-line"></span>
                                <span class="step-line"></span>
                            </div>
                            <span class="door-caption">{{ __('ACCÈS 1') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Passenger Interior Cabin -->
            <div class="coach-passenger-cabin" id="coachCabin">
                <!-- Overhead AC Climate Units on Roof -->
                <div class="roof-ac-unit ac-unit-front">
                    <i class="fa-solid fa-wind"></i> {{ __('CLIMATISATION CENTRALE DIFFUSE • 22°C') }}
                </div>

                <!-- Rows of Contoured 3D Armchairs -->
                <div class="cabin-seating-deck">
                    @foreach($rows as $row)
                        @php
                            $rowNum = $row['row_number'] ?? $loop->iteration;
                            $leftSeats = $row['left'] ?? [];
                            $rightSeats = $row['right'] ?? [];
                            $centerSeat = $row['center'] ?? null;
                            $door = $row['door'] ?? null;
                            $zoneName = $row['zone'] ?? ($rowNum <= 4 ? 'front' : ($rowNum <= 12 ? 'mid' : 'rear'));
                        @endphp
                        <div class="seating-row zone-{{ $zoneName }}" data-row="{{ $rowNum }}" id="row-{{ $rowNum }}">
                            <!-- Row Number Plaque -->
                            <div class="row-plaque" title="{{ __('Rangée :num', ['num' => $rowNum]) }}">
                                <span>R{{ sprintf('%02d', $rowNum) }}</span>
                            </div>

                            <!-- Left Group (3 Seats: Window, Middle, Aisle) -->
                            <div class="seat-block block-left">
                                @foreach($leftSeats as $seat)
                                    @if($seat)
                                        @include('components.virtual-seat-item', ['seat' => $seat, 'trip' => $trip])
                                    @else
                                        <div class="seat-armchair-placeholder" style="width: 48px; height: 50px; visibility: hidden;"></div>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Central Walking Aisle or Rear Bench Center Seat -->
                            <div class="cabin-aisle-path {{ $centerSeat ? 'has-center-seat' : '' }}">
                                @if($centerSeat)
                                    @include('components.virtual-seat-item', ['seat' => $centerSeat, 'trip' => $trip])
                                @else
                                    <div class="carpet-runner">
                                        <span class="aisle-guide-arrow">&uarr;</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Right Group (2 Seats) or Entrance Door -->
                            <div class="seat-block block-right">
                                @if($door)
                                    <div class="mid-bus-service-bay-inline" style="display: flex; align-items: center; justify-content: center; width: 100%; min-width: 100px; padding: 6px 12px; background: rgba(16, 185, 129, 0.1); border: 1.5px dashed #10B981; border-radius: 8px; color: #10B981; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; gap: 6px;">
                                        <i class="fa-solid fa-door-open"></i>
                                        <span>{{ $door }}</span>
                                    </div>
                                @else
                                    @foreach($rightSeats as $seat)
                                        @if($seat)
                                            @include('components.virtual-seat-item', ['seat' => $seat, 'trip' => $trip])
                                        @else
                                            <div class="seat-armchair-placeholder" style="width: 48px; height: 50px; visibility: hidden;"></div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <!-- Row Window Frame Indicators -->
                            <div class="window-pane-glow"></div>
                        </div>
                    @endforeach
                </div>

                <!-- Rear Roof AC Unit -->
                <div class="roof-ac-unit ac-unit-rear">
                    <i class="fa-solid fa-snowflake"></i> {{ __('VENTILATION ARRIÈRE & PURIFICATEUR D\'AIR') }}
                </div>
            </div>

            <!-- Rear Coach Hood & Safety Tail -->
            <div class="coach-rear-tail">
                <div class="rear-safety-bulkhead">
                    <div class="emergency-hammer-station">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>{{ __('ISSUES DE SECOURS ARRIÈRE & EXTINCTEUR HOMOLOGUÉ') }}</span>
                    </div>
                </div>
                <div class="rear-bumper">
                    <div class="taillight taillight-left"></div>
                    <div class="rear-license-plate">
                        <span>{{ $trip->vehicle->license_plate ?? 'LT-2026-RV' }}</span>
                    </div>
                    <div class="taillight taillight-right"></div>
                </div>
            </div>

            <!-- Heavy Duty Wheels & Chassis Shadow -->
            <div class="coach-wheel wheel-front-left"></div>
            <div class="coach-wheel wheel-front-right"></div>
            <div class="coach-wheel wheel-rear-left"></div>
            <div class="coach-wheel wheel-rear-right"></div>
            <div class="coach-wheel wheel-tag-left"></div>
            <div class="coach-wheel wheel-tag-right"></div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   REAL VOYAGE 3D VIRTUAL BUS DIAGRAM & INTERACTIVE CABIN SYSTEM
   Fluid 3D perspective, contoured armchairs, cockpit & exterior aerodynamics
   ========================================================================== */

.virtual-bus-container {
    --vbus-bg: var(--bg-card, #FFFFFF);
    --vbus-border: var(--border-color, #E2E8F0);
    --vbus-primary: var(--primary, #0F2942);
    --vbus-accent: #10B981;
    --vbus-gold: #D97706;
    --vbus-seat-w: 48px;
    --vbus-seat-h: 56px;
    --vbus-gap: 8px;
    --vbus-tilt-x: 22deg;
    --vbus-tilt-y: 0deg;
    --vbus-zoom: 1;

    background: var(--vbus-bg);
    border: 1.5px solid var(--vbus-border);
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    position: relative;
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

/* Dark Mode Adaptation */
[data-theme="dark"] .virtual-bus-container {
    --vbus-bg: #0F172A;
    --vbus-border: #1E293B;
    --vbus-primary: #38BDF8;
    box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35);
}

/* Control Top Bar */
.vbus-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--vbus-border);
}

.vbus-brand-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.vbus-badge-fleet {
    font-size: 1.15rem;
    font-weight: 900;
    color: var(--text-heading, #0F2942);
    display: flex;
    align-items: center;
    gap: 10px;
}

[data-theme="dark"] .vbus-badge-fleet {
    color: #F8FAFC;
}

.vbus-capacity-pill {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-size: 0.75rem;
    font-weight: 800;
    padding: 2px 10px;
    border-radius: 20px;
}

[data-theme="dark"] .vbus-capacity-pill {
    background: rgba(16, 185, 129, 0.2);
    color: #34D399;
}

.vbus-meta-route {
    font-size: 0.85rem;
    color: var(--text-muted, #64748B);
}

/* Camera Deck & Controls */
.vbus-camera-deck {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.camera-btn-group {
    display: inline-flex;
    background: var(--bg-surface, #F1F5F9);
    padding: 4px;
    border-radius: 10px;
    border: 1px solid var(--vbus-border);
}

[data-theme="dark"] .camera-btn-group {
    background: #1E293B;
}

.cam-btn {
    border: none;
    background: transparent;
    padding: 7px 14px;
    border-radius: 7px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted, #64748B);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
}

.cam-btn:hover:not(.active) {
    color: var(--text-heading, #0F2942);
}

.cam-btn.active {
    background: var(--vbus-primary);
    color: #FFFFFF;
    box-shadow: 0 2px 8px rgba(15, 41, 66, 0.25);
}

/* Zone Jumpers */
.zone-jumper-group {
    display: flex;
    align-items: center;
    gap: 5px;
}

.zone-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-muted, #94A3B8);
}

.zone-pill {
    border: 1px solid var(--vbus-border);
    background: transparent;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--text-muted, #64748B);
    cursor: pointer;
    transition: all 0.15s ease;
}

.zone-pill:hover,
.zone-pill.active {
    background: rgba(16, 185, 129, 0.1);
    border-color: #10B981;
    color: #10B981;
}

/* Legend Strip */
.vbus-legend-strip {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 14px;
    margin-bottom: 16px;
    padding: 10px 16px;
    background: var(--bg-surface, #F8FAFC);
    border-radius: 10px;
    border: 1px solid var(--vbus-border);
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-heading, #334155);
}

[data-theme="dark"] .vbus-legend-strip {
    background: #131E33;
    color: #CBD5E1;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.legend-swatch {
    width: 14px;
    height: 14px;
    border-radius: 4px;
    display: inline-block;
}

.swatch-available {
    background: #FFFFFF;
    border: 2px solid #0F2942;
}

[data-theme="dark"] .swatch-available {
    background: #1E293B;
    border-color: #94A3B8;
}

.swatch-selected {
    background: #10B981;
    border: 2px solid #059669;
    box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
}

.swatch-booked {
    background: #CBD5E1;
    border: 2px solid #94A3B8;
}

[data-theme="dark"] .swatch-booked {
    background: #334155;
    border-color: #475569;
}

.swatch-crew {
    background: #0F2942;
    border: 2px solid #D97706;
}

.legend-quick-tip {
    margin-left: auto;
    font-size: 0.74rem;
    color: var(--text-muted, #64748B);
}

/* ==========================================================================
   3D Viewport Stage & Coach Geometry
   ========================================================================== */

.vbus-viewport-stage {
    perspective: 1300px;
    perspective-origin: 50% 15%;
    overflow-x: auto;
    overflow-y: visible;
    padding: 30px 10px 40px;
    background: radial-gradient(circle at 50% 30%, rgba(241, 245, 249, 0.8) 0%, rgba(226, 232, 240, 0.4) 100%);
    border: 1px solid var(--vbus-border);
    border-radius: 14px;
    position: relative;
    box-sizing: border-box;
    -webkit-overflow-scrolling: touch;
}

[data-theme="dark"] .vbus-viewport-stage {
    background: radial-gradient(circle at 50% 30%, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.9) 100%);
}

/* Perspective Modes */
.vbus-viewport-stage.perspective-isometric #realVirtualCoach {
    transform: rotateX(24deg) translateY(-10px) scale(var(--vbus-zoom));
}

.vbus-viewport-stage.perspective-aisle #realVirtualCoach {
    transform: rotateX(42deg) translateY(-25px) scale(1.05);
}

.vbus-viewport-stage.perspective-plan #realVirtualCoach {
    transform: rotateX(0deg) translateY(0) scale(1);
}

/* Real Virtual Coach Body Construction */
.real-virtual-coach {
    width: max-content;
    margin: 0 auto;
    background: #FFFFFF;
    border: 3px solid #0F2942;
    border-radius: 36px 36px 24px 24px;
    box-shadow: 0 25px 60px rgba(15, 41, 66, 0.22), 0 5px 15px rgba(0, 0, 0, 0.1);
    transform-style: preserve-3d;
    transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    box-sizing: border-box;
}

[data-theme="dark"] .real-virtual-coach {
    background: #0B132B;
    border-color: #38BDF8;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 20px rgba(56, 189, 248, 0.15);
}

/* Aerodynamic Side Rails & Tinted Windows */
.coach-side-rail {
    position: absolute;
    top: 40px;
    bottom: 30px;
    width: 14px;
    pointer-events: none;
    z-index: 5;
}

.coach-side-left {
    left: -14px;
}

.coach-side-right {
    right: -14px;
}

.tinted-window-strip {
    display: flex;
    flex-direction: column;
    gap: 12px;
    height: 100%;
    padding-top: 30px;
}

.side-window-pane {
    width: 8px;
    flex: 1;
    background: linear-gradient(90deg, rgba(30, 58, 138, 0.3) 0%, rgba(59, 130, 246, 0.5) 100%);
    border-radius: 3px;
    border: 1px solid rgba(255, 255, 255, 0.4);
}

.side-mirror {
    position: absolute;
    top: 20px;
    width: 22px;
    height: 38px;
    background: #0F2942;
    border-radius: 6px;
    border: 1.5px solid #E2E8F0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
}

.mirror-left {
    left: -20px;
    transform: rotateY(-20deg);
}

.mirror-right {
    right: -20px;
    transform: rotateY(20deg);
}

.mirror-glass {
    position: absolute;
    inset: 3px;
    background: linear-gradient(135deg, #93C5FD 0%, #3B82F6 100%);
    border-radius: 3px;
}

/* Front Hood & Panoramic Windshield */
.coach-front-hood {
    background: linear-gradient(180deg, #091E33 0%, #0F2942 100%);
    border-radius: 32px 32px 0 0;
    padding: 16px 20px 14px;
    color: #FFFFFF;
    border-bottom: 2.5px solid #1E3A5F;
}

.front-bumper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 0 8px;
}

.headlight {
    width: 32px;
    height: 18px;
    background: #F8FAFC;
    border: 2px solid #E2E8F0;
    border-radius: 8px;
    box-shadow: 0 0 12px rgba(255, 255, 255, 0.9);
    position: relative;
}

.light-beam {
    position: absolute;
    top: -24px;
    left: -10px;
    right: -10px;
    height: 30px;
    background: linear-gradient(180deg, rgba(254, 240, 138, 0.5) 0%, rgba(254, 240, 138, 0) 100%);
    border-radius: 50% 50% 0 0;
    pointer-events: none;
}

.radiator-grill {
    background: #071524;
    border: 1.5px solid #334155;
    padding: 4px 18px;
    border-radius: 8px;
    text-align: center;
}

.grill-logo {
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 1.2px;
    color: #F8FAFC;
}

/* Panoramic Windshield */
.panoramic-windshield {
    background: linear-gradient(180deg, #1E3A8A 0%, #0F2942 100%);
    border: 1.5px solid #38BDF8;
    border-radius: 20px 20px 8px 8px;
    padding: 10px 12px;
    position: relative;
    overflow: hidden;
    margin-bottom: 14px;
    box-shadow: inset 0 0 16px rgba(0, 0, 0, 0.4);
}

.windshield-glare-effect {
    position: absolute;
    top: -30px;
    left: 20%;
    width: 60%;
    height: 80px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.28) 0%, rgba(255, 255, 255, 0) 80%);
    transform: rotate(-25deg);
    pointer-events: none;
}

.windshield-destination-board {
    background: #020617;
    border: 1px solid #10B981;
    border-radius: 5px;
    padding: 4px 10px;
    text-align: center;
    font-family: monospace;
    font-size: 0.74rem;
    font-weight: 800;
    color: #34D399;
    letter-spacing: 0.8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
}

.dest-blinker {
    color: #EF4444;
    animation: blinkBeacon 1.2s infinite ease-in-out;
}

@keyframes blinkBeacon {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.2; }
}

/* Cockpit Cabin Inner */
.cockpit-cabin {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 6px;
    gap: 12px;
}

.cockpit-dash-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.steering-wheel-3d {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 4px solid #94A3B8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #E2E8F0;
    font-size: 1.4rem;
    background: #020617;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
}

.dash-gauges {
    display: flex;
    gap: 8px;
}

.gauge-dial {
    font-size: 0.6rem;
    font-weight: 800;
    background: rgba(0, 0, 0, 0.4);
    padding: 2px 6px;
    border-radius: 4px;
    color: #38BDF8;
    border: 1px solid rgba(56, 189, 248, 0.2);
}

/* Entrance Doors with Boarding Steps */
.entrance-door {
    background: #047857;
    border: 1.5px solid #34D399;
    border-radius: 8px;
    padding: 8px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: #FFFFFF;
    cursor: default;
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
}

.stairs-steps {
    display: flex;
    flex-direction: column;
    gap: 2px;
    width: 100%;
}

.step-line {
    height: 2px;
    background: #A7F3D0;
    border-radius: 1px;
}

.door-caption {
    font-size: 0.62rem;
    font-weight: 900;
    letter-spacing: 0.5px;
}

/* Main Passenger Cabin Interior */
.coach-passenger-cabin {
    padding: 16px 20px;
    position: relative;
    background: var(--vbus-bg);
}

.roof-ac-unit {
    background: var(--bg-surface, #F1F5F9);
    border: 1px dashed var(--vbus-border);
    border-radius: 6px;
    padding: 6px;
    text-align: center;
    font-size: 0.68rem;
    font-weight: 800;
    color: var(--text-muted, #64748B);
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

[data-theme="dark"] .roof-ac-unit {
    background: #1E293B;
    border-color: #334155;
    color: #94A3B8;
}

.ac-unit-rear {
    margin-top: 14px;
    margin-bottom: 0;
}

/* Seating Deck & Rows */
.cabin-seating-deck {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.seating-row {
    display: flex;
    align-items: center;
    gap: var(--vbus-gap);
    justify-content: space-between;
    position: relative;
    transition: background 0.2s ease;
}

.row-plaque {
    font-size: 0.68rem;
    font-weight: 900;
    color: var(--text-muted, #94A3B8);
    width: 28px;
    text-align: center;
    flex-shrink: 0;
    letter-spacing: 0.4px;
}

.seat-block {
    display: flex;
    gap: var(--vbus-gap);
    flex-shrink: 0;
}

/* Central Walking Aisle Runner */
.cabin-aisle-path {
    width: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    flex-shrink: 0;
}

.carpet-runner {
    width: 12px;
    height: 100%;
    min-height: 48px;
    background: repeating-linear-gradient(
        180deg,
        rgba(148, 163, 184, 0.25),
        rgba(148, 163, 184, 0.25) 4px,
        transparent 4px,
        transparent 8px
    );
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.aisle-guide-arrow {
    font-size: 0.65rem;
    color: var(--text-muted, #94A3B8);
    opacity: 0.6;
}

/* Mid-Bus Service Bay */
.mid-bus-service-bay {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    margin: 6px 0;
    background: var(--bg-surface, #F8FAFC);
    border: 1px dashed #10B981;
    border-radius: 8px;
}

[data-theme="dark"] .mid-bus-service-bay {
    background: #131E33;
}

.mid-emergency-exit {
    font-size: 0.68rem;
    font-weight: 800;
    color: #10B981;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ==========================================================================
   Contoured 3D Armchair Geometry
   ========================================================================== */

.seat-armchair {
    width: var(--vbus-seat-w);
    height: var(--vbus-seat-h);
    position: relative;
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex-shrink: 0;
}

/* Headrest Cushion */
.armchair-headrest {
    height: 16px;
    background: #E2E8F0;
    border: 1.5px solid #CBD5E1;
    border-radius: 6px 6px 4px 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 900;
    color: #0F2942;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    position: relative;
    transition: all 0.2s ease;
}

/* Backrest with Contoured Bolsters */
.armchair-backrest {
    flex: 1;
    margin: 2px 0;
    background: #FFFFFF;
    border: 1.5px solid #CBD5E1;
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
}

.seat-number-tag {
    font-size: 0.82rem;
    font-weight: 900;
    color: #0F2942;
    line-height: 1;
}

.seat-pos-tag {
    font-size: 0.52rem;
    font-weight: 700;
    color: #64748B;
    text-transform: uppercase;
    margin-top: 1px;
}

/* Bottom Cushion & Dropped Shadow */
.armchair-cushion {
    height: 12px;
    background: #F1F5F9;
    border: 1.5px solid #CBD5E1;
    border-radius: 3px 3px 6px 6px;
    box-shadow: 0 3px 0 #94A3B8, 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

/* Available State Hover Effect */
.seat-armchair.seat-state-available:hover {
    transform: translateY(-4px) scale(1.04);
}

.seat-armchair.seat-state-available:hover .armchair-headrest {
    border-color: #0F2942;
    background: #0F2942;
    color: #FFFFFF;
}

.seat-armchair.seat-state-available:hover .armchair-cushion {
    box-shadow: 0 5px 0 #0F2942, 0 6px 14px rgba(15, 41, 66, 0.25);
}

/* Touch Active */
.seat-armchair.seat-state-available:active {
    transform: translateY(1px) scale(0.96);
}

/* SELECTED STATE: Glowing Luxury Emerald */
.seat-armchair.seat-state-selected {
    transform: translateY(-3px);
}

.seat-armchair.seat-state-selected .armchair-headrest {
    background: #059669 !important;
    color: #FFFFFF !important;
    border-color: #047857 !important;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.6) !important;
}

.seat-armchair.seat-state-selected .armchair-backrest {
    background: #10B981 !important;
    border-color: #059669 !important;
}

.seat-armchair.seat-state-selected .seat-number-tag {
    color: #FFFFFF !important;
}

.seat-armchair.seat-state-selected .seat-pos-tag {
    color: #D1FAE5 !important;
}

.seat-armchair.seat-state-selected .armchair-cushion {
    background: #059669 !important;
    border-color: #047857 !important;
    box-shadow: 0 3px 0 #065F46, 0 6px 16px rgba(16, 185, 129, 0.4) !important;
}

/* Passenger Order Badge (P1, P2) */
.seat-passenger-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #F59E0B;
    color: #0F172A;
    font-size: 0.62rem;
    font-weight: 900;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
    padding: 0 3px;
    border-radius: 10px;
    border: 1.5px solid #FFFFFF;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    text-align: center;
    z-index: 10;
    animation: popBadge 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes popBadge {
    0% { transform: scale(0); opacity: 0; }
    80% { transform: scale(1.2); }
    100% { transform: scale(1); opacity: 1; }
}

/* BOOKED STATE */
.seat-armchair.seat-state-booked {
    cursor: not-allowed;
    opacity: 0.65;
}

.seat-armchair.seat-state-booked .armchair-headrest {
    background: #CBD5E1;
    color: #64748B;
    border-color: #94A3B8;
}

.seat-armchair.seat-state-booked .armchair-backrest {
    background: #E2E8F0;
    border-color: #CBD5E1;
}

.seat-armchair.seat-state-booked .armchair-cushion {
    background: #CBD5E1;
    border-color: #94A3B8;
    box-shadow: 0 2px 0 #94A3B8;
}

/* PERMANENT CREW LOCKS (Seats 01 & 16) */
.seat-armchair.seat-state-locked {
    cursor: not-allowed;
}

.headrest-crew {
    background: #0F2942 !important;
    color: #D97706 !important;
    border-color: #D97706 !important;
}

.seat-armchair.seat-state-locked .armchair-backrest {
    background: #091E33 !important;
    border-color: #1E3A5F !important;
}

.crew-designation {
    font-size: 0.52rem;
    font-weight: 900;
    color: #F8FAFC;
    letter-spacing: 0.6px;
    text-align: center;
}

.seat-armchair.seat-state-locked .armchair-cushion {
    background: #0F2942 !important;
    border-color: #091E33 !important;
    box-shadow: 0 3px 0 #071524 !important;
}

/* Rear Safety Tail & Taillights */
.coach-rear-tail {
    background: linear-gradient(180deg, #0F2942 0%, #071524 100%);
    border-radius: 0 0 24px 24px;
    padding: 14px 20px;
    color: #FFFFFF;
    border-top: 2px dashed #334155;
}

.rear-safety-bulkhead {
    text-align: center;
    margin-bottom: 10px;
}

.emergency-hammer-station {
    font-size: 0.68rem;
    font-weight: 800;
    color: #94A3B8;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.rear-bumper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 8px;
}

.taillight {
    width: 28px;
    height: 12px;
    background: #EF4444;
    border: 1.5px solid #FCA5A5;
    border-radius: 4px;
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.7);
}

.rear-license-plate {
    background: #F8FAFC;
    color: #0F172A;
    font-family: monospace;
    font-size: 0.72rem;
    font-weight: 900;
    padding: 2px 10px;
    border-radius: 4px;
    border: 1px solid #CBD5E1;
}

/* Heavy Duty Coach Wheels */
.coach-wheel {
    position: absolute;
    width: 14px;
    height: 38px;
    background: #020617;
    border-radius: 6px;
    border: 2px solid #334155;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.wheel-front-left { top: 90px; left: -12px; }
.wheel-front-right { top: 90px; right: -12px; }
.wheel-rear-left { bottom: 120px; left: -12px; }
.wheel-rear-right { bottom: 120px; right: -12px; }
.wheel-tag-left { bottom: 70px; left: -12px; }
.wheel-tag-right { bottom: 70px; right: -12px; }

/* ==========================================================================
   Interactive Live Tooltip (Seat Inspector)
   ========================================================================== */

.seat-live-tooltip {
    position: absolute;
    z-index: 100;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 12px 16px;
    color: #FFFFFF;
    font-size: 0.82rem;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.4);
    pointer-events: none;
    width: 220px;
    transition: opacity 0.15s ease, transform 0.15s ease;
    animation: fadeInTooltip 0.15s ease-out;
}

@keyframes fadeInTooltip {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.tooltip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    padding-bottom: 8px;
    margin-bottom: 8px;
}

.tooltip-seat-num {
    font-size: 1.1rem;
    font-weight: 900;
    color: #38BDF8;
    letter-spacing: 0.5px;
}

.tooltip-badge {
    font-size: 0.68rem;
    font-weight: 800;
    background: rgba(16, 185, 129, 0.25);
    color: #34D399;
    padding: 2px 8px;
    border-radius: 10px;
}

.tooltip-body {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.tooltip-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
}

.tt-label {
    color: #94A3B8;
}

.tooltip-amenities {
    display: flex;
    gap: 8px;
    font-size: 0.7rem;
    color: #E2E8F0;
    margin: 4px 0;
    padding: 4px 0;
    border-top: 1px dashed rgba(255, 255, 255, 0.1);
    border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
}

.tooltip-price {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2px;
}

.tt-amount {
    font-size: 0.95rem;
    font-weight: 900;
    color: #10B981;
}

/* ==========================================================================
   Responsive Adaptations
   ========================================================================== */

@media (max-width: 768px) {
    .virtual-bus-container {
        --vbus-seat-w: 42px;
        --vbus-seat-h: 50px;
        --vbus-gap: 6px;
        padding: 16px 10px;
    }

    .vbus-top-bar {
        flex-direction: column;
        align-items: flex-start;
    }

    .vbus-camera-deck {
        width: 100%;
        justify-content: space-between;
    }

    .seat-live-tooltip {
        width: 190px;
        font-size: 0.76rem;
    }
}

@media (max-width: 480px) {
    .virtual-bus-container {
        --vbus-seat-w: 35px;
        --vbus-seat-h: 44px;
        --vbus-gap: 4px;
        padding: 12px 6px;
    }

    .camera-btn-group {
        width: 100%;
    }

    .cam-btn {
        flex: 1;
        justify-content: center;
        padding: 6px 8px;
        font-size: 0.72rem;
    }

    .zone-jumper-group {
        width: 100%;
        justify-content: space-between;
    }

    .armchair-headrest {
        height: 13px;
        font-size: 0.62rem;
    }

    .seat-number-tag {
        font-size: 0.72rem;
    }

    .seat-pos-tag {
        display: none;
    }
}
</style>

<script>
/**
 * Real Voyage 3D Virtual Bus Controller
 */
function setVirtualBusPerspective(perspective) {
    const stage = document.getElementById('vbusViewport');
    const btns = {
        isometric: document.getElementById('camIsometricBtn'),
        aisle: document.getElementById('camAisleBtn'),
        plan: document.getElementById('camPlanBtn')
    };

    if (!stage) return;

    stage.classList.remove('perspective-isometric', 'perspective-aisle', 'perspective-plan');
    stage.classList.add('perspective-' + perspective);

    Object.keys(btns).forEach(key => {
        if (btns[key]) {
            if (key === perspective) btns[key].classList.add('active');
            else btns[key].classList.remove('active');
        }
    });
}

function jumpToBusZone(zone, btnElement) {
    // Update zone pill buttons
    document.querySelectorAll('.zone-pill').forEach(b => b.classList.remove('active'));
    if (btnElement) btnElement.classList.add('active');

    const stage = document.getElementById('vbusViewport');
    if (!stage) return;

    if (zone === 'all') {
        stage.scrollTo({ top: 0, behavior: 'smooth' });
    } else if (zone === 'front') {
        const el = document.getElementById('row-1');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else if (zone === 'mid') {
        const el = document.getElementById('row-8');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else if (zone === 'rear') {
        const el = document.getElementById('row-15') || document.getElementById('row-14');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Seat Inspector Tooltip Position & Data
function showSeatInspector(e, element) {
    const tooltip = document.getElementById('seatLiveTooltip');
    if (!tooltip) return;

    const seatCode = element.getAttribute('data-seat');
    const num = element.getAttribute('data-num');
    const pos = element.getAttribute('data-pos') || 'Siège';
    const zone = element.getAttribute('data-zone') || 'Cabine';
    const isLocked = element.getAttribute('data-locked') === 'true';
    const isBooked = element.classList.contains('seat-state-booked');
    const isSelected = element.classList.contains('seat-state-selected');

    document.getElementById('ttSeatNum').textContent = seatCode;
    document.getElementById('ttPosition').textContent = pos;
    document.getElementById('ttZone').textContent = zone;

    const badge = document.getElementById('ttBadge');
    if (isLocked) {
        badge.textContent = "{{ __('Équipage Real Voyage') }}";
        badge.style.background = '#D97706';
        badge.style.color = '#FFFFFF';
    } else if (isBooked) {
        badge.textContent = "{{ __('Déjà Réservé') }}";
        badge.style.background = '#64748B';
        badge.style.color = '#FFFFFF';
    } else if (isSelected) {
        badge.textContent = "{{ __('Votre Choix') }}";
        badge.style.background = '#10B981';
        badge.style.color = '#FFFFFF';
    } else {
        badge.textContent = "{{ __('Disponible') }}";
        badge.style.background = 'rgba(16, 185, 129, 0.25)';
        badge.style.color = '#34D399';
    }

    const rect = element.getBoundingClientRect();
    const stageRect = document.getElementById('vbusViewport').getBoundingClientRect();
    const scrollLeft = document.getElementById('vbusViewport').scrollLeft;
    const scrollTop = document.getElementById('vbusViewport').scrollTop;

    // Position tooltip near seat inside stage
    let top = (rect.top - stageRect.top) + scrollTop - 120;
    let left = (rect.left - stageRect.left) + scrollLeft + (rect.width / 2) - 110;

    if (top < 10) top = (rect.bottom - stageRect.top) + scrollTop + 10;
    if (left < 10) left = 10;

    tooltip.style.top = top + 'px';
    tooltip.style.left = left + 'px';
    tooltip.style.display = 'block';
}

function hideSeatInspector() {
    const tooltip = document.getElementById('seatLiveTooltip');
    if (tooltip) tooltip.style.display = 'none';
}
</script>
