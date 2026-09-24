@props([
    'seatMap',
    'trip',
])

@php
    $totalSeats = $seatMap['total_seats'] ?? 75;
    $availableCount = $seatMap['available_count'] ?? 0;
    $bookedCount = $seatMap['booked_count'] ?? 0;
    $seatsList = array_values($seatMap['seats'] ?? []);
@endphp

<div class="webgl-bus-wrapper" id="webglBusWrapper">
    <!-- Top HUD Bar: Title, Capacity, Controls -->
    <div class="webgl-hud-header">
        <div class="hud-brand">
            <div class="hud-brand-title">
                <i class="fa-solid fa-bus text-primary"></i>
                <span>{{ __('Autocar Grand Tourisme HD (Tri-Essieu)') }} &bull; {{ $totalSeats }} {{ __('Places') }}</span>
                <span class="hud-webgl-badge">3D Temps Réel</span>
            </div>
            <div class="hud-brand-subtitle">
                {{ __('Modèle 3D fidèle (Marcopolo / Scania). Disposition Cameroun 2+3, 2 portes d\'accès (Avant & Milieu), soutes à bagages et poste chauffeur à gauche.') }}
            </div>
        </div>

        <!-- 3D Camera Angles & Roof Mode -->
        <div class="hud-controls-deck">
            <!-- Camera Angles -->
            <div class="hud-btn-group" title="{{ __('Angles de caméra') }}">
                <button type="button" class="hud-btn" id="btnCamIso" onclick="change3DCamera('isometric', this)" title="{{ __('Vue Isométrique 3D Extérieure') }}">
                    <i class="fa-solid fa-cubes"></i> <span>{{ __('3D Isométrique') }}</span>
                </button>
                <button type="button" class="hud-btn active" id="btnCamInterior" onclick="change3DCamera('interior', this)" title="{{ __('Vue Intérieure Allée Centrale & Fauteuils VIP') }}">
                    <i class="fa-solid fa-person-walking text-primary"></i> <span>{{ __('Cabine Intérieure') }}</span>
                </button>
                <button type="button" class="hud-btn" id="btnCamCockpit" onclick="change3DCamera('cockpit', this)" title="{{ __('Poste Chauffeur à Gauche') }}">
                    <i class="fa-solid fa-compass"></i> <span>{{ __('Chauffeur') }}</span>
                </button>
                <button type="button" class="hud-btn" id="btnCamTop" onclick="change3DCamera('top', this)" title="{{ __('Vue Aérienne Découverte (Plan)') }}">
                    <i class="fa-solid fa-border-top-left"></i> <span>{{ __('Aérien') }}</span>
                </button>
                <button type="button" class="hud-btn" id="btnCamFirstPerson" onclick="viewFirstPersonSeat(this)" title="{{ __('Vue Passager 1ère Personne (à hauteur des yeux)') }}">
                    <i class="fa-solid fa-street-view text-warning"></i> <span>{{ __('Vue Passager') }}</span>
                </button>
            </div>

            <!-- 4D Time-of-Day Lighting Engine -->
            <div class="hud-btn-group hud-time-group" title="{{ __('Moteur d\'ambiance 4D & Éclairage') }}">
                <button type="button" class="hud-btn hud-time-btn active" id="btnTimeDay" onclick="change3DTimeOfDay('day', this)" title="{{ __('Plein Jour (Lumière Naturelle)') }}">
                    <i class="fa-solid fa-sun text-warning"></i> <span>{{ __('Jour') }}</span>
                </button>
                <button type="button" class="hud-btn hud-time-btn" id="btnTimeSunset" onclick="change3DTimeOfDay('sunset', this)" title="{{ __('Crépuscule (Golden Hour)') }}">
                    <i class="fa-solid fa-cloud-sun text-danger"></i> <span>{{ __('Crépuscule') }}</span>
                </button>
                <button type="button" class="hud-btn hud-time-btn" id="btnTimeNight" onclick="change3DTimeOfDay('night', this)" title="{{ __('Nuit (Phares Xénon & Néon Intérieur)') }}">
                    <i class="fa-solid fa-moon text-info"></i> <span>{{ __('Nuit') }}</span>
                </button>
            </div>

            <!-- Door Animation Toggle -->
            <button type="button" class="hud-btn hud-door-toggle" id="btnDoorToggle" onclick="toggle3DDoor(this)" title="{{ __('Ouvrir / Fermer les 2 portes d\'embarquement (Avant & Milieu)') }}">
                <i class="fa-solid fa-door-closed" id="doorToggleIcon"></i> <span id="doorToggleText">{{ __('Ouvrir les 2 Portes') }}</span>
            </button>

            <!-- Roof Transparency Toggle -->
            <button type="button" class="hud-btn hud-roof-toggle active" id="btnRoofToggle" onclick="toggle3DRoof(this)" title="{{ __('Rendre le toit transparent pour voir l\'intérieur') }}">
                <i class="fa-solid fa-eye"></i> <span id="roofToggleText">{{ __('Toit Transparent') }}</span>
            </button>
        </div>
    </div>

    <!-- WebGL Canvas Stage -->
    <div class="webgl-canvas-viewport" id="webglBusContainer">
        <!-- Floating Live 3D Raycaster HUD Card -->
        <div class="three-seat-hud" id="threeSeatHud" style="display: none;">
            <div class="hud-seat-icon">
                <i class="fa-solid fa-chair"></i>
            </div>
            <div class="hud-seat-info">
                <div class="hud-seat-title">
                    <strong id="threeHudCode">S-00</strong>
                    <span class="hud-pill" id="threeHudStatus">{{ __('Disponible') }}</span>
                </div>
                <div class="hud-seat-specs">
                    <span><i class="fa-solid fa-bolt text-warning"></i> USB</span>
                    <span><i class="fa-solid fa-snowflake text-primary"></i> AC</span>
                    <span><i class="fa-solid fa-tag text-success"></i> {{ number_format($trip->base_price, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <!-- 3D Overlay Interaction Hints -->
        <div class="webgl-interaction-hint">
            <span><i class="fa-solid fa-arrows-spin"></i> {{ __('Faites glisser pour tourner à 360°') }}</span>
            <span>&bull;</span>
            <span><i class="fa-solid fa-magnifying-glass-plus"></i> {{ __('Molette ou pincez pour zoomer') }}</span>
            <span>&bull;</span>
            <span><i class="fa-solid fa-hand-pointer text-success"></i> {{ __('Touchez un fauteuil pour réserver') }}</span>
        </div>
    </div>

    <!-- Status Legend Strip -->
    <div class="webgl-legend-strip">
        <div class="legend-badge">
            <span class="swatch swatch-available"></span>
            <span>{{ __('Libre (:count)', ['count' => $availableCount]) }}</span>
        </div>
        <div class="legend-badge">
            <span class="swatch swatch-selected"></span>
            <span>{{ __('Votre Sélection (Émeraude)') }}</span>
        </div>
        <div class="legend-badge">
            <span class="swatch swatch-booked"></span>
            <span>{{ __('Occupé (:count)', ['count' => $bookedCount]) }}</span>
        </div>
        <div class="legend-badge">
            <span class="swatch swatch-crew"></span>
            <span>{{ __('Équipage (01 Chauffeur & 16 Convoyeur)') }}</span>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   Real Voyage True WebGL 3D Bus Experience
   ========================================================================== */

.webgl-bus-wrapper {
    background: var(--bg-card, #FFFFFF);
    border: 1.5px solid var(--border-color, #E2E8F0);
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 12px 35px rgba(15, 41, 66, 0.1);
    position: relative;
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

[data-theme="dark"] .webgl-bus-wrapper {
    background: #0F172A;
    border-color: #1E293B;
    box-shadow: 0 16px 45px rgba(0, 0, 0, 0.45);
}

.webgl-hud-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color, #E2E8F0);
}

.hud-brand-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-heading, #0F2942);
    display: flex;
    align-items: center;
    gap: 10px;
    line-height: 1.3;
}

.hud-webgl-badge {
    background: linear-gradient(135deg, #059669 0%, #10B981 100%);
    color: #FFFFFF;
    font-size: 0.7rem;
    font-weight: 900;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}

.hud-brand-subtitle {
    font-size: 0.8rem;
    color: var(--text-muted, #64748B);
    margin-top: 3px;
}

.hud-controls-deck {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.hud-btn-group {
    display: inline-flex;
    background: var(--bg-surface, #F1F5F9);
    padding: 4px;
    border-radius: 8px;
    border: 1px solid var(--border-color, #CBD5E1);
}

[data-theme="dark"] .hud-btn-group {
    background: #1E293B;
    border-color: #334155;
}

.hud-btn {
    border: none;
    background: transparent;
    padding: 7px 13px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted, #64748B);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.hud-btn:hover:not(.active) {
    color: var(--text-heading, #0F2942);
}

.hud-btn.active {
    background: var(--primary, #0F2942);
    color: #FFFFFF;
    box-shadow: 0 2px 6px rgba(15, 41, 66, 0.25);
}

.hud-roof-toggle {
    background: var(--bg-surface, #F1F5F9);
    border: 1.5px solid #10B981;
    color: #059669;
    padding: 7px 14px;
    border-radius: 8px;
}

.hud-roof-toggle.active {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
}

.hud-door-toggle {
    background: var(--bg-surface, #F1F5F9);
    border: 1.5px solid var(--border-color, #CBD5E1);
    color: var(--text-heading, #0F2942);
    padding: 7px 14px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

[data-theme="dark"] .hud-door-toggle {
    background: #1E293B;
    border-color: #334155;
    color: #F8FAFC;
}

.hud-door-toggle.active {
    background: rgba(2, 132, 199, 0.15);
    border-color: #0284C7;
    color: #0284C7;
}

.hud-time-btn.active {
    background: #0F172A !important;
    color: #38BDF8 !important;
    box-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
}

/* WebGL Viewport Container */
.webgl-canvas-viewport {
    width: 100%;
    height: 520px;
    min-height: 480px;
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 16px;
    background: radial-gradient(circle at 50% 40%, rgba(241, 245, 249, 0.7) 0%, rgba(226, 232, 240, 0.9) 100%);
    border: 1px solid var(--border-color, #E2E8F0);
    box-sizing: border-box;
    cursor: grab;
}

.webgl-canvas-viewport:active {
    cursor: grabbing;
}

[data-theme="dark"] .webgl-canvas-viewport {
    background: radial-gradient(circle at 50% 40%, #131E33 0%, #070D1E 100%);
    border-color: #1E293B;
}

.webgl-canvas-viewport canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
    outline: none;
}

/* 3D Raycaster Floating HUD */
.three-seat-hud {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 10;
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 12px;
    padding: 10px 16px;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    pointer-events: none;
    animation: fadeInHud 0.2s ease;
}

@keyframes fadeInHud {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.hud-seat-icon {
    font-size: 1.4rem;
    color: #38BDF8;
}

.hud-seat-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.hud-pill {
    font-size: 0.65rem;
    font-weight: 800;
    padding: 1px 7px;
    border-radius: 10px;
}

.hud-pill-available {
    background: rgba(16, 185, 129, 0.25);
    color: #34D399;
}

.hud-pill-selected {
    background: #10B981;
    color: #FFFFFF;
}

.hud-pill-booked {
    background: #64748B;
    color: #FFFFFF;
}

.hud-pill-crew {
    background: #D97706;
    color: #FFFFFF;
}

.hud-seat-specs {
    font-size: 0.72rem;
    color: #CBD5E1;
    display: flex;
    gap: 8px;
    margin-top: 2px;
}

/* 3D Interaction Hints Overlay */
.webgl-interaction-hint {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 6px 16px;
    font-size: 0.72rem;
    font-weight: 700;
    color: #E2E8F0;
    display: flex;
    align-items: center;
    gap: 10px;
    pointer-events: none;
    white-space: nowrap;
    z-index: 5;
}

/* Legend Strip */
.webgl-legend-strip {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 14px;
    padding: 10px 14px;
    background: var(--bg-surface, #F8FAFC);
    border-radius: 8px;
    border: 1px solid var(--border-color, #E2E8F0);
    font-size: 0.78rem;
    color: var(--text-heading, #334155);
    font-weight: 600;
}

[data-theme="dark"] .webgl-legend-strip {
    background: #131E33;
    color: #CBD5E1;
}

.legend-badge {
    display: flex;
    align-items: center;
    gap: 8px;
}

.swatch {
    width: 13px;
    height: 13px;
    border-radius: 3px;
    display: inline-block;
}

.swatch-available {
    background: #FFFFFF;
    border: 1.5px solid #0F2942;
}

.swatch-selected {
    background: #10B981;
    border: 1.5px solid #059669;
    box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
}

.swatch-booked {
    background: #64748B;
    border: 1.5px solid #475569;
}

.swatch-crew {
    background: #0F2942;
    border: 1.5px solid #D97706;
}

@media (max-width: 768px) {
    .webgl-canvas-viewport {
        height: 420px;
        min-height: 380px;
    }

    .webgl-hud-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .hud-controls-deck {
        width: 100%;
        justify-content: space-between;
    }

    .webgl-interaction-hint {
        font-size: 0.65rem;
        padding: 4px 10px;
        gap: 6px;
        bottom: 8px;
    }
}
</style>

<script>
let bus3DEngine = null;

function initRealVoyage3DBus() {
    if (typeof THREE === 'undefined' || typeof THREE.OrbitControls === 'undefined' || typeof RealVoyageBus3D === 'undefined') {
        console.warn('Three.js or Bus Engine loading...');
        setTimeout(initRealVoyage3DBus, 150);
        return;
    }

    const seats = @json($seatsList);
    const totalCapacity = {{ $totalSeats }};
    const defaultSeat = "{{ $firstAvailableSeat ?? 'S-02' }}";

    bus3DEngine = new RealVoyageBus3D({
        containerId: 'webglBusContainer',
        seats: seats,
        totalSeats: totalCapacity,
        basePrice: {{ $trip->base_price }},
        initialSelected: typeof selectedSeats !== 'undefined' ? selectedSeats : [defaultSeat],
        onSeatToggle: function(seatCode, newSelectedList) {
            if (typeof window.sync3DSeatSelection === 'function') {
                window.sync3DSeatSelection(seatCode, newSelectedList);
            }
        }
    });
}

function change3DCamera(view, btn) {
    document.querySelectorAll('.hud-btn-group .hud-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    if (bus3DEngine) bus3DEngine.setCameraView(view);
}

function toggle3DRoof(btn) {
    if (!bus3DEngine) return;
    const isTrans = !bus3DEngine.isRoofTransparent;
    bus3DEngine.setRoofTransparency(isTrans);
    
    if (isTrans) {
        btn.classList.add('active');
        document.getElementById('roofToggleText').textContent = "{{ __('Toit Transparent') }}";
    } else {
        btn.classList.remove('active');
        document.getElementById('roofToggleText').textContent = "{{ __('Toit Fermé') }}";
    }
}

function change3DTimeOfDay(mode, btn) {
    document.querySelectorAll('.hud-time-group .hud-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    if (bus3DEngine) bus3DEngine.setTimeOfDay(mode);
}

function toggle3DDoor(btn) {
    if (!bus3DEngine) return;
    bus3DEngine.toggleDoorAnimation();
    const isOpen = bus3DEngine.isDoorOpen;
    const txt = document.getElementById('doorToggleText');
    const icon = document.getElementById('doorToggleIcon');
    if (isOpen) {
        btn.classList.add('active');
        if (txt) txt.textContent = "{{ __('Fermer les 2 Portes') }}";
        if (icon) {
            icon.classList.remove('fa-door-closed');
            icon.classList.add('fa-door-open');
        }
    } else {
        btn.classList.remove('active');
        if (txt) txt.textContent = "{{ __('Ouvrir les 2 Portes') }}";
        if (icon) {
            icon.classList.remove('fa-door-open');
            icon.classList.add('fa-door-closed');
        }
    }
}

function viewFirstPersonSeat(btn) {
    if (!bus3DEngine) return;
    document.querySelectorAll('.hud-controls-deck .hud-btn-group:first-child .hud-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    
    // Choose selected seat or default
    let seat = null;
    if (bus3DEngine.selectedSeats && bus3DEngine.selectedSeats.size > 0) {
        seat = Array.from(bus3DEngine.selectedSeats)[0];
    } else if (typeof selectedSeats !== 'undefined' && selectedSeats.length > 0) {
        seat = selectedSeats[0];
    } else {
        seat = 'S-02';
    }
    
    bus3DEngine.flyToFirstPersonSeat(seat);
}

document.addEventListener('DOMContentLoaded', () => {
    initRealVoyage3DBus();
});
</script>
