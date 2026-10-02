<!-- Real Voyage Luxury Cinematic Splash / Loading Interface -->
<div id="realVoyageSplash" class="rv-splash-overlay" aria-hidden="true">
    <!-- Cinematic Background with Dynamic Motion Blur Autocar -->
    <div class="rv-splash-bg" style="background-image: url('{{ asset('images/splash-bus-motion.jpg') }}');"></div>
    <div class="rv-splash-gradient"></div>

    <!-- Skip Button (Top Right) -->
    <button type="button" class="rv-splash-skip" onclick="dismissRealVoyageSplash()" title="{{ __('Passer l\'animation') }}">
        <span>{{ __('Passer') }}</span>
        <i class="fa-solid fa-forward-step"></i>
    </button>

    <!-- Centerpiece Brand & Journey Progress Card -->
    <div class="rv-splash-card">
        
        <!-- Glowing Animated Brand Logo -->
        <div class="rv-splash-logo-wrap">
            <div class="rv-splash-glow"></div>
            <img src="{{ asset('images/logo.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="Real Express Voyages" class="rv-splash-logo">
        </div>

        <!-- Title & Slogan -->
        <h1 class="rv-splash-title">
            {{ config('app.name', 'Real Express Voyages') }}
        </h1>
        <p class="rv-splash-tagline">
            {{ __('Plateforme Nationale de Transport Interurbain & Fret • Cameroun') }}
        </p>

        <!-- Route Indicator Dots (Douala - Yaoundé - Ouest) -->
        <div class="rv-splash-route-steps">
            <div class="rv-splash-step active">
                <span class="rv-splash-step-dot"></span>
                <span class="rv-splash-step-label">Douala</span>
            </div>
            <div class="rv-splash-step-line"></div>
            <div class="rv-splash-step active">
                <span class="rv-splash-step-dot"></span>
                <span class="rv-splash-step-label">Yaoundé</span>
            </div>
            <div class="rv-splash-step-line"></div>
            <div class="rv-splash-step active">
                <span class="rv-splash-step-dot"></span>
                <span class="rv-splash-step-label">Bafoussam</span>
            </div>
        </div>

        <!-- Animated Progress Highway Bar with Traveling Bus -->
        <div class="rv-splash-progress-track">
            <div class="rv-splash-progress-bar" id="rvSplashBar">
                <div class="rv-splash-bus-marker">
                    <i class="fa-solid fa-bus"></i>
                </div>
            </div>
        </div>

        <!-- Dynamic Status Message & Percentage -->
        <div class="rv-splash-status-row">
            <span id="rvSplashStatusText" class="rv-splash-status-text">
                <i class="fa-solid fa-circle-notch fa-spin"></i> {{ __('Synchronisation des 11 gares régionales...') }}
            </span>
            <span id="rvSplashPercent" class="rv-splash-percent">0%</span>
        </div>

        <!-- Security / Mobile Money Badges -->
        <div class="rv-splash-badges">
            <span class="rv-splash-badge">
                <i class="fa-solid fa-clock"></i> {{ __('Départs 10h00 & 21h30') }}
            </span>
            <span class="rv-splash-badge">
                <i class="fa-solid fa-mobile-screen-button"></i> {{ __('Orange Money • MTN MoMo') }}
            </span>
            <span class="rv-splash-badge">
                <i class="fa-solid fa-shield-halved"></i> {{ __('Billets QR Certifiés') }}
            </span>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   Real Voyage Splash Screen Styles
   ========================================================================== */
.rv-splash-overlay {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: #09121D;
    overflow: hidden;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.5s;
}

.rv-splash-overlay.is-hidden {
    opacity: 0 !important;
    visibility: hidden !important;
    transform: scale(1.04);
    pointer-events: none !important;
}

/* Cinematic Autocar Background */
.rv-splash-bg {
    position: absolute;
    inset: -10px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    animation: rvSplashKenBurns 12s ease-out infinite alternate;
    filter: brightness(0.92);
}

@keyframes rvSplashKenBurns {
    0% { transform: scale(1); }
    100% { transform: scale(1.08) translate(-1%, -1%); }
}

/* Atmospheric Overlay Gradient */
.rv-splash-gradient {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, rgba(15, 76, 68, 0.55) 0%, rgba(8, 20, 32, 0.88) 60%, rgba(4, 10, 18, 0.96) 100%);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Skip Button */
.rv-splash-skip {
    position: absolute;
    top: 24px;
    right: 24px;
    z-index: 10;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #FFFFFF;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 7px 14px;
    border-radius: 30px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(8px);
    transition: all 0.2s ease;
}

.rv-splash-skip:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateX(2px);
}

/* Center Glass Card */
.rv-splash-card {
    position: relative;
    z-index: 5;
    max-width: 520px;
    width: 100%;
    background: rgba(17, 27, 40, 0.72);
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.06);
    border-radius: 28px;
    padding: 38px 32px 30px;
    text-align: center;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    color: #FFFFFF;
    animation: rvCardEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes rvCardEntrance {
    0% { opacity: 0; transform: translateY(20px) scale(0.96); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

/* Glowing Logo Icon */
.rv-splash-logo-wrap {
    position: relative;
    width: 76px;
    height: 76px;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rv-splash-glow {
    position: absolute;
    inset: -12px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(52, 211, 153, 0.45) 0%, rgba(15, 76, 68, 0) 70%);
    animation: rvPulse 2s ease-in-out infinite alternate;
}

@keyframes rvPulse {
    0% { transform: scale(0.9); opacity: 0.6; }
    100% { transform: scale(1.2); opacity: 1; }
}

.rv-splash-logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
    position: relative;
    z-index: 2;
    filter: drop-shadow(0 6px 12px rgba(0,0,0,0.4));
}

/* Title & Subtitle */
.rv-splash-title {
    font-size: 1.65rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    color: #FFFFFF;
    margin: 0 0 6px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
}

.rv-splash-tagline {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.75);
    margin: 0 0 24px;
    line-height: 1.4;
}

/* Route Steps (Douala - Yaoundé - Bafoussam) */
.rv-splash-route-steps {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 0 10px;
}

.rv-splash-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.rv-splash-step-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #34D399;
    box-shadow: 0 0 10px #34D399;
}

.rv-splash-step-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #E2E8F0;
    letter-spacing: 0.3px;
}

.rv-splash-step-line {
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, rgba(52, 211, 153, 0.6), rgba(52, 211, 153, 0.2));
    margin: 0 10px;
    margin-bottom: 14px;
}

/* Highway Progress Track with Traveling Bus */
.rv-splash-progress-track {
    position: relative;
    height: 8px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    overflow: visible;
    margin-bottom: 14px;
}

.rv-splash-progress-bar {
    position: relative;
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #059669, #34D399);
    border-radius: 10px;
    box-shadow: 0 0 12px rgba(52, 211, 153, 0.6);
    transition: width 0.15s ease-out;
}

.rv-splash-bus-marker {
    position: absolute;
    right: -12px;
    top: -12px;
    width: 24px;
    height: 24px;
    background: #FFFFFF;
    color: #0F4C44;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    transform: rotate(0deg);
}

/* Status Label & Percentage Row */
.rv-splash-status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.76rem;
    color: rgba(255, 255, 255, 0.85);
    margin-bottom: 22px;
    font-weight: 600;
}

.rv-splash-percent {
    font-weight: 800;
    color: #34D399;
}

/* Bottom Feature Badges */
.rv-splash-badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 18px;
}

.rv-splash-badge {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.68rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.rv-splash-badge i {
    color: #34D399;
}

@media (max-width: 600px) {
    .rv-splash-card {
        padding: 30px 20px 24px;
    }
    .rv-splash-title {
        font-size: 1.35rem;
    }
    .rv-splash-badges {
        display: none;
    }
}
</style>

<script>
/**
 * Real Voyage Splash Screen Controller
 * - Exact 10-second cinematic time-lapse duration (10 000 ms)
 * - Precise timestamp-based interpolation for smooth 60fps animation
 * - Milestone status updates across the 10 seconds
 */
(function() {
    const splash = document.getElementById('realVoyageSplash');
    if (!splash) return;

    // Check if user forces preview with ?splash=1 or first visit
    const urlParams = new URLSearchParams(window.location.search);
    const forceSplash = urlParams.has('splash');
    const alreadySeen = sessionStorage.getItem('realvoyage_splash_dismissed');

    if (alreadySeen && !forceSplash) {
        splash.classList.add('is-hidden');
        setTimeout(() => { if (splash && splash.parentNode) splash.parentNode.removeChild(splash); }, 100);
        return;
    }

    const progressBar = document.getElementById('rvSplashBar');
    const percentText = document.getElementById('rvSplashPercent');
    const statusText = document.getElementById('rvSplashStatusText');

    const statusMilestones = [
        { at: 0, text: "{{ __('Initialisation du système de transport...') }}" },
        { at: 15, text: "{{ __('Connexion aux 11 gares (Douala, Yaoundé, Ouest)...') }}" },
        { at: 35, text: "{{ __('Chargement des départs fixes (10h00 Matin & 21h30 Nuit)...') }}" },
        { at: 55, text: "{{ __('Sécurisation des passerelles Orange Money & MTN MoMo...') }}" },
        { at: 75, text: "{{ __('Vérification de la flotte d\'autocars VIP (75/80 pl.)...') }}" },
        { at: 90, text: "{{ __('Génération des E-Billets avec QR Code officiel...') }}" },
        { at: 100, text: "{{ __('Prêt ! Bienvenue à bord de Real Voyage.') }}" }
    ];

    const TOTAL_DURATION_MS = 10000; // Exactly 10.0 seconds
    const startTime = performance.now();

    function updateSplashProgress(now) {
        const elapsed = now - startTime;
        const progressFraction = Math.min(1, elapsed / TOTAL_DURATION_MS);
        const percent = Math.floor(progressFraction * 100);

        if (progressBar) progressBar.style.width = percent + '%';
        if (percentText) percentText.textContent = percent + '%';

        // Update current status message based on percentage
        for (let i = statusMilestones.length - 1; i >= 0; i--) {
            if (percent >= statusMilestones[i].at) {
                if (statusText) {
                    statusText.innerHTML = (percent === 100) 
                        ? `<i class="fa-solid fa-circle-check" style="color: #34D399;"></i> ${statusMilestones[i].text}`
                        : `<i class="fa-solid fa-circle-notch fa-spin"></i> ${statusMilestones[i].text}`;
                }
                break;
            }
        }

        if (progressFraction < 1) {
            requestAnimationFrame(updateSplashProgress);
        } else {
            // Reached 100% at 10 seconds -> brief hold and smooth dismiss
            setTimeout(dismissRealVoyageSplash, 400);
        }
    }

    requestAnimationFrame(updateSplashProgress);
})();

function dismissRealVoyageSplash() {
    const splash = document.getElementById('realVoyageSplash');
    if (!splash) return;
    sessionStorage.setItem('realvoyage_splash_dismissed', 'true');
    splash.classList.add('is-hidden');
    setTimeout(() => {
        if (splash && splash.parentNode) {
            splash.parentNode.removeChild(splash);
        }
    }, 600);
}
</script>
