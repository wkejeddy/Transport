@props([
    'seatMap',
    'trip',
])

@php
    $rows = $seatMap['rows'] ?? [];
    $totalSeats = $seatMap['total_seats'] ?? 75;
    $availableCount = $seatMap['available_count'] ?? 0;
@endphp

<div class="seatmap-3d-wrapper">
    <!-- Header with View Controls & Fleet Capacity -->
    <div class="seatmap-header">
        <div class="seatmap-header-info">
            <div class="seatmap-title">
                <i class="fa-solid fa-bus-simple text-primary"></i>
                <span>{{ __('Plan Cabine 3D') }} &bull; {{ __('Autocar') }} {{ $totalSeats }} {{ __('Places') }}</span>
            </div>
            <p class="seatmap-subtitle">
                {{ __("Sélectionnez vos sièges passagers. Sièges 01 & 16 strictement réservés à l'équipage Real Voyage.") }}
            </p>
        </div>

        <div class="seatmap-controls">
            <button type="button" class="btn-view-toggle active" id="btn3DView" onclick="setSeatMapView('3d')" title="{{ __('Perspective 3D isométrique') }}">
                <i class="fa-solid fa-cube"></i> <span>{{ __('Perspective 3D') }}</span>
            </button>
            <button type="button" class="btn-view-toggle" id="btn2DView" onclick="setSeatMapView('2d')" title="{{ __('Vue à plat dessus') }}">
                <i class="fa-solid fa-table-cells"></i> <span>{{ __('Vue Dessus (2D)') }}</span>
            </button>
        </div>
    </div>

    <!-- Executive Legend Bar (Adaptive Grid on Mobile) -->
    <div class="seatmap-legend-bar">
        <div class="legend-badge">
            <span class="seat-dot seat-dot-available"></span>
            <span>{{ __('Disponible') }}</span>
        </div>
        <div class="legend-badge">
            <span class="seat-dot seat-dot-selected"></span>
            <span>{{ __('Sélectionné') }}</span>
        </div>
        <div class="legend-badge">
            <span class="seat-dot seat-dot-booked"></span>
            <span>{{ __('Déjà Réservé') }}</span>
        </div>
        <div class="legend-badge">
            <span class="seat-dot seat-dot-locked"></span>
            <span class="legend-label-crew">{{ __('Équipage (01 & 16)') }}</span>
        </div>
        <div class="legend-badge legend-badge-seal">
            <i class="fa-solid fa-shield-halved text-primary"></i>
            <span>{{ __('Véhicule agréé Real Voyage S.A.') }}</span>
        </div>
    </div>

    <!-- Mobile Touch / Scroll Guidance Hint -->
    <div class="seatmap-mobile-hint">
        <i class="fa-solid fa-hand-pointer"></i>
        <span>{{ __('Touchez un siège disponible pour le réserver. Utilisez les boutons 3D / 2D pour adapter la vue.') }}</span>
    </div>

    <!-- 3D Perspective Stage -->
    <div class="bus-3d-stage mode-3d" id="bus3dStage">
        <div class="bus-coach-body" id="busCoachBody">
            <!-- Front Cabin Cockpit -->
            <div class="bus-cockpit">
                <div class="cockpit-windshield">
                    <div class="windshield-glare"></div>
                    <div class="cockpit-label">
                        <i class="fa-solid fa-compass"></i> {{ __('POSTE DE CONDUITE • AVANT') }}
                    </div>
                </div>

                <div class="cockpit-controls-row">
                    <div class="steering-wheel">
                        <i class="fa-solid fa-dharmachakra"></i>
                        <span class="control-label">{{ __('Poste Conduite') }}</span>
                    </div>

                    <div style="font-size: 0.72rem; color: #94A3B8; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-arrow-up text-primary"></i>
                        <span>{{ __('Sens de marche') }}</span>
                    </div>

                    <div class="cockpit-door">
                        <i class="fa-solid fa-door-open"></i>
                        <span class="control-label">{{ __('Accès Avant') }}</span>
                    </div>
                </div>
            </div>

            <!-- Central Passenger Cabin with Carrefour Rows -->
            <div class="passenger-cabin-grid">
                @foreach($rows as $row)
                    @php
                        $rowNum = $row['row_number'] ?? $loop->iteration;
                        $leftSeats = $row['left'] ?? [];
                        $rightSeats = $row['right'] ?? [];
                        $centerSeat = $row['center'] ?? null;
                        $door = $row['door'] ?? null;
                    @endphp
                    <div class="cabin-row" data-row="{{ $rowNum }}">
                        <!-- Row indicator on the left -->
                        <div class="row-num-tag">R{{ sprintf('%02d', $rowNum) }}</div>

                        <!-- Left Group: Seats (Columns 1, 2, 3) -->
                        <div class="seat-group-left">
                            @foreach($leftSeats as $seat)
                                @if($seat)
                                    @include('components.seat-single-card', ['seat' => $seat])
                                @else
                                    <div class="seat-placeholder"></div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Central Walking Aisle or Rear Bench Center Seat -->
                        <div class="cabin-aisle {{ $centerSeat ? 'has-center-seat' : '' }}">
                            @if($centerSeat)
                                @include('components.seat-single-card', ['seat' => $centerSeat])
                            @else
                                <span class="aisle-marker">&bull;</span>
                            @endif
                        </div>

                        <!-- Right Group: Seats (Columns 4, 5) or Curbside Entrance Door -->
                        <div class="seat-group-right">
                            @if($door)
                                <div class="cabin-door-bay" title="{{ $door }}">
                                    <i class="fa-solid fa-door-open"></i>
                                    <span>{{ $door }}</span>
                                </div>
                            @else
                                @foreach($rightSeats as $seat)
                                    @if($seat)
                                        @include('components.seat-single-card', ['seat' => $seat])
                                    @else
                                        <div class="seat-placeholder"></div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Rear Safety & Luggage Zone -->
            <div class="bus-rear">
                <div class="rear-label">
                    <i class="fa-solid fa-shield-cat"></i> {{ __('FOND DU VÉHICULE • ISSUES DE SECOURS ARRIÈRE') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   Real Voyage 3D Bus Seat Map - Executive Mobile-First Responsive System
   Fluid adaptation across all phone screens (320px - 430px) and desktops
   ========================================================================== */

.seatmap-3d-wrapper {
    /* Responsive Fluid Variables (Desktop Defaults) */
    --seat-w: 46px;
    --seat-h: 50px;
    --seat-gap: 6px;
    --seat-font: 0.78rem;
    --seat-sub-font: 0.52rem;
    --row-num-w: 26px;
    --aisle-w: 16px;
    --coach-pad-x: 16px;
    --stage-pad-x: 14px;
    --tilt-angle: 20deg;
    --tilt-y: -8px;

    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--border-color, #E2E8F0);
    border-radius: 14px;
    padding: 20px;
    box-shadow: var(--shadow-md);
    width: 100%;
    box-sizing: border-box;
}

/* Header & View Controls */
.seatmap-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border-subtle, #E2E8F0);
}

.seatmap-header-info {
    flex: 1 1 280px;
}

.seatmap-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-heading, #0F2942);
    display: flex;
    align-items: center;
    gap: 10px;
    line-height: 1.3;
}

.seatmap-subtitle {
    font-size: 0.82rem;
    color: var(--text-muted, #64748B);
    margin: 4px 0 0 0;
    line-height: 1.4;
}

.seatmap-controls {
    display: flex;
    gap: 6px;
    background: var(--bg-surface, #F1F5F9);
    padding: 4px;
    border-radius: 8px;
    border: 1px solid var(--border-color, #E2E8F0);
}

.btn-view-toggle {
    border: none;
    background: transparent;
    padding: 7px 14px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted, #475569);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}

.btn-view-toggle:hover:not(.active) {
    background: var(--bg-card-hover, #E2E8F0);
    color: var(--text-heading, #0F2942);
}

.btn-view-toggle.active {
    background: var(--primary, #0F2942);
    color: #FFFFFF;
    box-shadow: 0 2px 6px rgba(15, 41, 66, 0.18);
}

/* Legend Bar */
.seatmap-legend-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 14px;
    margin-bottom: 14px;
    padding: 10px 14px;
    background: var(--bg-surface, #F8FAFC);
    border: 1px solid var(--border-subtle, #EDF2F7);
    border-radius: 8px;
    font-size: 0.78rem;
    color: var(--text-main, #334155);
    font-weight: 600;
}

.legend-badge {
    display: flex;
    align-items: center;
    gap: 7px;
    white-space: nowrap;
}

.legend-badge-seal {
    margin-left: auto;
    color: var(--text-muted, #64748B);
    font-size: 0.75rem;
}

.seat-dot {
    width: 13px;
    height: 13px;
    border-radius: 3px;
    display: inline-block;
    flex-shrink: 0;
}

.seat-dot-available {
    background: var(--bg-card, #FFFFFF);
    border: 1.5px solid var(--primary, #0F2942);
}

.seat-dot-selected {
    background: #10B981;
    border: 1.5px solid #059669;
}

.seat-dot-booked {
    background: var(--bg-surface-elevated, #E2E8F0);
    border: 1.5px solid var(--border-color, #CBD5E1);
}

.seat-dot-locked {
    background: #0F2942;
    border: 1.5px solid #091E33;
}

/* Mobile Guidance Hint */
.seatmap-mobile-hint {
    display: none;
    align-items: center;
    gap: 8px;
    background: var(--primary-50, #EFF6FF);
    border: 1px solid var(--primary-100, #BFDBFE);
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 14px;
    font-size: 0.75rem;
    color: var(--primary-text, #1E40AF);
    font-weight: 600;
    line-height: 1.35;
}

/* 3D Perspective Stage Container */
.bus-3d-stage {
    perspective: 1100px;
    perspective-origin: 50% 12%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    padding: 24px var(--stage-pad-x) 32px;
    background: var(--bg-surface, #F8FAFC);
    border: 1px solid var(--border-color, #E2E8F0);
    border-radius: 12px;
    transition: all 0.3s ease;
    box-sizing: border-box;
    position: relative;
}

/* Smooth thin scrollbar for horizontal overflow containment */
.bus-3d-stage::-webkit-scrollbar {
    height: 5px;
}
.bus-3d-stage::-webkit-scrollbar-track {
    background: var(--bg-surface, #F1F5F9);
    border-radius: 3px;
}
.bus-3d-stage::-webkit-scrollbar-thumb {
    background: var(--border-color, #CBD5E1);
    border-radius: 3px;
}

/* Bus Coach Body: Centered and strictly proportional */
.bus-coach-body {
    width: max-content;
    max-width: 100%;
    margin: 0 auto;
    background: var(--bg-card, #FFFFFF);
    border: 3px solid var(--border-color, #0F2942);
    border-radius: 26px 26px 18px 18px;
    box-shadow: var(--shadow-lg);
    transform-style: preserve-3d;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-sizing: border-box;
}

/* 3D Mode vs 2D Mode transforms */
.bus-3d-stage.mode-3d .bus-coach-body {
    transform: rotateX(var(--tilt-angle)) translateY(var(--tilt-y));
}

.bus-3d-stage.mode-2d .bus-coach-body {
    transform: rotateX(0deg) translateY(0);
}

/* Cockpit & Windshield */
.bus-cockpit {
    background: #0F2942;
    border-radius: 22px 22px 0 0;
    padding: 12px var(--coach-pad-x) 10px;
    color: #FFFFFF;
    border-bottom: 2px solid #1E3A5F;
}

.cockpit-windshield {
    background: linear-gradient(180deg, #1E3A8A 0%, #0F2942 100%);
    border: 1px solid #3B82F6;
    border-radius: 14px 14px 6px 6px;
    padding: 7px;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: 10px;
}

.windshield-glare {
    position: absolute;
    top: -25px;
    left: 15%;
    width: 70%;
    height: 60px;
    background: linear-gradient(135deg, rgba(255,255,255,0.22) 0%, rgba(255,255,255,0) 80%);
    transform: rotate(-18deg);
    pointer-events: none;
}

.cockpit-label {
    font-size: clamp(0.62rem, 1.8vw, 0.72rem);
    font-weight: 800;
    letter-spacing: 0.8px;
    color: #93C5FD;
}

.cockpit-controls-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 4px;
}

.steering-wheel {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    font-size: 0.65rem;
    color: #94A3B8;
    font-weight: 700;
}

.steering-wheel i {
    font-size: 1.3rem;
    color: #E2E8F0;
}

.cockpit-door {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    font-size: 0.65rem;
    color: #10B981;
    font-weight: 700;
}

.cockpit-door i {
    font-size: 1.15rem;
}

/* Passenger Cabin Grid */
.passenger-cabin-grid {
    padding: 14px var(--coach-pad-x);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.cabin-row {
    display: flex;
    align-items: center;
    gap: var(--seat-gap);
    justify-content: space-between;
}

.row-num-tag {
    font-size: clamp(0.55rem, 1.4vw, 0.68rem);
    font-weight: 800;
    color: var(--text-muted, #94A3B8);
    width: var(--row-num-w);
    text-align: center;
    flex-shrink: 0;
}

.seat-group-left {
    display: flex;
    gap: var(--seat-gap);
    flex-shrink: 0;
}

.seat-group-right {
    display: flex;
    gap: var(--seat-gap);
    flex-shrink: 0;
}

.cabin-aisle {
    width: var(--aisle-w);
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--text-light, #CBD5E1);
    font-size: 0.72rem;
    flex-shrink: 0;
    user-select: none;
}

.cabin-aisle.has-center-seat {
    width: var(--seat-w);
}

.seat-placeholder {
    width: var(--seat-w);
    height: var(--seat-h);
    visibility: hidden;
    flex-shrink: 0;
}

.cabin-door-bay {
    width: calc((var(--seat-w) * 2) + var(--seat-gap));
    height: var(--seat-h);
    background: rgba(16, 185, 129, 0.08);
    border: 1.5px dashed #10B981;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #059669;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.6px;
    user-select: none;
    box-sizing: border-box;
    text-transform: uppercase;
}

/* Single Seat 3D Component */
.seat-item {
    position: relative;
    user-select: none;
    -webkit-user-select: none;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
    cursor: pointer;
    flex-shrink: 0;
}

.seat-cushion {
    width: var(--seat-w);
    height: var(--seat-h);
    border-radius: 7px 7px 9px 9px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1.5px solid var(--border-color, #CBD5E1);
    background: var(--bg-card, #FFFFFF);
    color: var(--text-heading, #0F2942);
    position: relative;
    box-shadow: 0 3px 0 var(--border-color, #CBD5E1), 0 3px 6px rgba(0, 0, 0, 0.07);
    box-sizing: border-box;
    padding: 2px 1px;
}

/* Headrest accent */
.seat-cushion::before {
    content: '';
    position: absolute;
    top: 2px;
    width: 48%;
    height: 4px;
    border-radius: 2px;
    background: rgba(148, 163, 184, 0.25);
}

.seat-code {
    font-size: var(--seat-font);
    font-weight: 800;
    line-height: 1.1;
    font-family: var(--font-sans, sans-serif);
}

.seat-sub-pos {
    font-size: var(--seat-sub-font);
    color: var(--text-muted, #94A3B8);
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    margin-top: 1px;
}

.booked-icon {
    font-size: 0.68rem;
    margin-bottom: 2px;
    line-height: 1;
}

/* Available Hover & Touch Active */
.seat-item.seat-available:hover .seat-cushion {
    border-color: var(--primary, #0F2942);
    box-shadow: 0 5px 0 var(--primary, #0F2942), 0 6px 12px rgba(15, 41, 66, 0.15);
    transform: translateY(-3px);
}

.seat-item.seat-available:active .seat-cushion {
    transform: translateY(1px) scale(0.95);
    box-shadow: 0 1px 0 var(--primary, #0F2942);
}

/* Selected State */
.seat-item.seat-selected .seat-cushion {
    background: #10B981 !important;
    color: #FFFFFF !important;
    border-color: #059669 !important;
    box-shadow: 0 3px 0 #047857, 0 5px 12px rgba(16, 185, 129, 0.28) !important;
    transform: translateY(-2px);
}

.seat-item.seat-selected .seat-cushion::before {
    background: rgba(255, 255, 255, 0.35);
}

.seat-item.seat-selected .seat-sub-pos {
    color: #D1FAE5 !important;
}

/* Booked State */
.seat-item.seat-booked .seat-cushion {
    background: var(--bg-surface, #F1F5F9);
    color: var(--text-muted, #94A3B8);
    border-color: var(--border-subtle, #E2E8F0);
    box-shadow: none;
    cursor: not-allowed;
    opacity: 0.72;
}

/* Permanently Locked Seats (01 & 16 Crew) */
.seat-item.seat-locked .seat-cushion,
.seat-cushion-crew {
    background: #0F2942 !important;
    color: #FFFFFF !important;
    border-color: #091E33 !important;
    box-shadow: 0 2px 0 #071524 !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
}

.seat-cushion-crew .crew-icon {
    font-size: 0.72rem;
    color: #F59E0B;
    margin-bottom: 2px;
    line-height: 1;
}

.crew-tag {
    font-size: clamp(0.42rem, 1.1vw, 0.54rem);
    color: #CBD5E1;
    font-weight: 700;
    letter-spacing: 0.1px;
    text-transform: uppercase;
    line-height: 1;
    margin-top: 1px;
}

/* Passenger Order Badge (P1, P2...) */
.seat-item .seat-passenger-badge {
    position: absolute;
    top: -5px;
    right: -4px;
    background: #F59E0B;
    color: #0F172A;
    font-size: 0.62rem;
    font-weight: 900;
    min-width: 17px;
    height: 17px;
    line-height: 17px;
    padding: 0 4px;
    border-radius: 9px;
    border: 1.5px solid #FFFFFF;
    box-shadow: 0 2px 5px rgba(0,0,0,0.25);
    text-align: center;
    z-index: 10;
    pointer-events: none;
    animation: popInBadge 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes popInBadge {
    0% { transform: scale(0); opacity: 0; }
    80% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

/* Rear Emergency Section */
.bus-rear {
    background: var(--bg-surface, #F8FAFC);
    border-radius: 0 0 16px 16px;
    border-top: 2px dashed var(--border-color, #CBD5E1);
    padding: 9px;
    text-align: center;
}

.rear-label {
    font-size: clamp(0.58rem, 1.5vw, 0.7rem);
    font-weight: 700;
    color: var(--text-muted, #64748B);
    letter-spacing: 0.4px;
}

/* ==========================================================================
   Responsive Breakpoints for Mobile Adaptation
   ========================================================================== */

/* Tablets & Small Laptops (<= 768px) */
@media (max-width: 768px) {
    .seatmap-3d-wrapper {
        padding: 16px 12px;
        --seat-w: 42px;
        --seat-h: 46px;
        --seat-gap: 5px;
        --tilt-angle: 16deg;
        --tilt-y: -6px;
    }

    .seatmap-legend-bar {
        gap: 10px 14px;
    }
}

/* Mobile Phones (<= 640px) */
@media (max-width: 640px) {
    .seatmap-3d-wrapper {
        padding: 14px 8px;
        border-radius: 10px;
        --seat-w: 36px;
        --seat-h: 41px;
        --seat-gap: 4px;
        --seat-font: 0.70rem;
        --seat-sub-font: 0.48rem;
        --row-num-w: 20px;
        --aisle-w: 12px;
        --coach-pad-x: 10px;
        --stage-pad-x: 6px;
        --tilt-angle: 14deg;
        --tilt-y: -4px;
    }

    .seatmap-title {
        font-size: 1.02rem;
    }

    .seatmap-subtitle {
        font-size: 0.78rem;
    }

    .seatmap-controls {
        width: 100%;
    }

    .btn-view-toggle {
        flex: 1;
        justify-content: center;
        padding: 8px 10px;
        font-size: 0.76rem;
    }

    .seatmap-legend-bar {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 10px;
        padding: 10px 12px;
        font-size: 0.74rem;
    }

    .legend-badge-seal {
        grid-column: 1 / -1;
        justify-content: center;
        margin-left: 0;
        padding-top: 6px;
        border-top: 1px dashed var(--border-subtle, #E2E8F0);
    }

    .seatmap-mobile-hint {
        display: flex;
    }

    .bus-3d-stage {
        padding: 16px 4px 22px;
    }

    .bus-coach-body {
        border-width: 2.5px;
        border-radius: 20px 20px 14px 14px;
    }

    .bus-cockpit {
        border-radius: 17px 17px 0 0;
        padding: 10px var(--coach-pad-x) 8px;
    }

    .control-label {
        font-size: 0.6rem;
    }

    .seat-item .seat-passenger-badge {
        font-size: 0.55rem;
        min-width: 15px;
        height: 15px;
        line-height: 15px;
        top: -4px;
        right: -3px;
        padding: 0 2px;
    }
}

/* Standard Mobile Viewports (<= 420px: iPhone 12/13/14/15, Galaxy S series, Pixel) */
@media (max-width: 420px) {
    .seatmap-3d-wrapper {
        padding: 10px 4px;
        --seat-w: 32px;
        --seat-h: 38px;
        --seat-gap: 3px;
        --seat-font: 0.66rem;
        --seat-sub-font: 0.44rem;
        --row-num-w: 18px;
        --aisle-w: 9px;
        --coach-pad-x: 6px;
        --stage-pad-x: 2px;
        --tilt-angle: 12deg;
        --tilt-y: -2px;
    }

    .cockpit-controls-row {
        padding: 0;
    }

    .steering-wheel i {
        font-size: 1.1rem;
    }

    .cockpit-door i {
        font-size: 1rem;
    }

    .passenger-cabin-grid {
        gap: 6px;
    }

    .seat-cushion {
        border-radius: 5px 5px 7px 7px;
        box-shadow: 0 2px 0 #CBD5E1, 0 2px 4px rgba(15, 41, 66, 0.06);
    }
}

/* Extra Compact Phones (<= 350px: Galaxy Z Fold Cover, iPhone SE1) */
@media (max-width: 350px) {
    .seatmap-3d-wrapper {
        --seat-w: 28px;
        --seat-h: 34px;
        --seat-gap: 2px;
        --seat-font: 0.58rem;
        --seat-sub-font: 0.38rem;
        --row-num-w: 16px;
        --aisle-w: 6px;
        --coach-pad-x: 4px;
        --stage-pad-x: 0px;
        --tilt-angle: 10deg;
    }

    .seatmap-legend-bar {
        grid-template-columns: 1fr;
    }

    .legend-label-crew {
        font-size: 0.7rem;
    }
}
</style>

<script>
function setSeatMapView(mode) {
    const stage = document.getElementById('bus3dStage');
    const btn3D = document.getElementById('btn3DView');
    const btn2D = document.getElementById('btn2DView');

    if (!stage) return;

    if (mode === '2d') {
        stage.classList.remove('mode-3d');
        stage.classList.add('mode-2d');
        if (btn2D) btn2D.classList.add('active');
        if (btn3D) btn3D.classList.remove('active');
    } else {
        stage.classList.remove('mode-2d');
        stage.classList.add('mode-3d');
        if (btn3D) btn3D.classList.add('active');
        if (btn2D) btn2D.classList.remove('active');
    }
}

// Auto-select 3D view on load
document.addEventListener('DOMContentLoaded', function() {
    const stage = document.getElementById('bus3dStage');
    if (stage && !stage.classList.contains('mode-3d') && !stage.classList.contains('mode-2d')) {
        stage.classList.add('mode-3d');
    }
});
</script>
