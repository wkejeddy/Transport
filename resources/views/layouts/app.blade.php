@php
    $serverTheme = request()->cookie('realvoyage_theme') ?: (request()->cookie('transportcm_theme') ?: 'light');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $serverTheme }}" class="{{ $serverTheme === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Plateforme Nationale de Gestion des Transports - Cameroun')) - {{ config('app.name', 'Travel') }}</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="{{ __('Plateforme unifiée de transport multimodal au Cameroun : Réservez vos billets de bus et de train Camrail, expédiez et suivez vos colis avec paiement Mobile Money (Orange Money & MTN MoMo).') }}">
    
    <!-- Instant Theme Loader (Zero FOUC Client Hydration & Preference Sync) -->
    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('realvoyage_theme') || localStorage.getItem('transportcm_theme');
                const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const activeTheme = storedTheme ? storedTheme : (systemDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', activeTheme);
                if (activeTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    <!-- PWA & Mobile Web App Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#111827">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Real Voyage">
    <link rel="apple-touch-icon" href="/images/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192.png">

    <!-- Favicon & Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Leaflet.js & Chart.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Seamless Automated Bilingual Translation Engine Styles (Zero Google Banner/FOUC) -->
    <style>
        .goog-te-banner-frame.skiptranslate,
        .goog-te-banner-frame,
        iframe.goog-te-banner-frame,
        #goog-gt-tt,
        .goog-te-balloon-frame,
        .goog-tooltip,
        .goog-tooltip:hover {
            display: none !important;
            visibility: hidden !important;
        }
        body {
            top: 0px !important;
            position: static !important;
        }
        .goog-text-highlight {
            background-color: transparent !important;
            box-shadow: none !important;
        }
        #google_translate_element {
            display: none !important;
        }
        font {
            background-color: transparent !important;
            box-shadow: none !important;
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Real Voyage Cinematic App Splash Screen -->
    @include('components.splash-screen')

    @if(!request()->routeIs('login', 'register*'))
    <!-- Top Navigation Bar (Minimalist: Name & Logo Only + Menu Bar Button) -->
    <header class="navbar">
        <div class="container navbar-inner">
            <!-- Brand Logo & Name -->
            <a href="{{ route('home') }}" class="brand-logo" style="gap: 12px;">
                <img src="{{ asset('images/logo.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="Real Express Voyages" style="width: 42px; height: 42px; object-fit: contain; flex-shrink: 0;">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 900; line-height: 1.1; font-size: 1.25rem; letter-spacing: -0.02em;">{{ config('app.name', 'Real Express Voyages') }}</span>
                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">{{ __('Transport Interurbain & Fret') }}</span>
                </div>
            </a>

            <!-- Desktop Navigation Links (Clean Minimalist format) -->
            <nav class="desktop-nav" aria-label="{{ __('Navigation Principale') }}">
                <a href="{{ route('home') }}" class="desktop-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    {{ __('Accueil') }}
                </a>
                <a href="{{ route('trips.index') }}" class="desktop-nav-link {{ request()->routeIs('trips.*') ? 'active' : '' }}">
                    {{ __('Horaires & Départs') }}
                </a>
                <a href="{{ route('passenger.shipments.create') }}" class="desktop-nav-link {{ request()->routeIs('passenger.shipments.*') ? 'active' : '' }}">
                    {{ __('Fret Express') }}
                </a>
                <a href="{{ route('shipments.track') }}" class="desktop-nav-link {{ request()->routeIs('shipments.track') ? 'active' : '' }}">
                    {{ __('Suivi de Colis') }}
                </a>
            </nav>

            <!-- Right Actions: Rounded Pill CTA (Book a Tour equivalent), Language & Theme -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Modern Bilingual Switcher [FR | EN] -->
                @include('components.bilingual-switcher')

                @auth
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : (Auth::user()->isManager() ? route('manager.dashboard') : route('passenger.dashboard')) }}" class="haven-btn-pill-light desktop-only" style="font-weight: 700;">
                        <i class="fa-solid fa-user-circle"></i> {{ __('Mon Espace') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-text desktop-only" style="font-weight: 600;">{{ __('Connexion') }}</a>
                    <a href="{{ route('trips.index') }}" class="haven-btn-pill-light desktop-only">
                        {{ __('Réserver un Billet') }}
                    </a>
                @endauth


                <!-- Menu Bar Toggle Button -->
                <button type="button" class="menu-bar-toggle" id="menuBarOpen" aria-label="{{ __('Ouvrir le Menu') }}" title="{{ __('Menu Principal') }}">
                    <i class="fa-solid fa-bars-staggered"></i>
                    <span>{{ __('Menu') }}</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Slide-Out Menu Bar Drawer (Contains all Navigation, Profile, Language, Theme & Actions) -->
    <div class="menu-bar-overlay" id="menuBarOverlay"></div>
    <aside class="menu-bar-drawer" id="menuBarDrawer" aria-label="{{ __('Menu Principal') }}">
        <!-- Header of Menu Bar -->
        <div class="menu-bar-header">
            <a href="{{ route('home') }}" class="brand-logo" style="font-size: 1.15rem; gap: 10px;">
                <img src="{{ asset('images/logo.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="Real Express Voyages" style="width: 36px; height: 36px; object-fit: contain; flex-shrink: 0;">
                <div>{{ config('app.name', 'Real Express Voyages') }}</div>
            </a>

            <button type="button" id="menuBarClose" class="menu-bar-close-btn" aria-label="{{ __('Fermer le Menu') }}" title="{{ __('Fermer') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Menu Bar Content (Scrollable) -->
        <div class="menu-bar-body">
            <!-- User Account / Profile Box -->
            <div class="menu-user-card">
                @auth
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div class="menu-user-avatar" style="overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            @endif
                        </div>
                        <div style="overflow: hidden; flex: 1;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ Auth::user()->name }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                {{ Auth::user()->phone ?? Auth::user()->email }}
                            </div>
                            <div style="margin-top: 4px;">
                                @if(Auth::user()->isAdmin())
                                    <span class="badge badge-danger" style="font-size: 0.65rem;">{{ __('Administrateur') }}</span>
                                @elseif(Auth::user()->isManager())
                                    <span class="badge badge-road" style="font-size: 0.65rem;">{{ __('Manager Agence') }}</span>
                                @else
                                    <span class="badge badge-success" style="font-size: 0.65rem;">{{ __('Passager Certifié') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">
                                <i class="fa-solid fa-chart-line"></i> {{ __('Supervision Admin') }}
                            </a>
                        @elseif(Auth::user()->isManager())
                            <a href="{{ route('manager.dashboard') }}" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
                                <i class="fa-solid fa-building"></i> {{ __('Espace Agence') }}
                            </a>
                        @else
                            <a href="{{ route('passenger.dashboard') }}" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
                                <i class="fa-solid fa-ticket"></i> {{ __('Mon Espace Voyageur') }}
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center; font-weight: 700;">
                            <i class="fa-solid fa-user-gear"></i> {{ __('Mon Profil & Sécurité') }}
                        </a>
                    </div>
                @else
                    <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 12px; line-height: 1.4;">
                        {{ __('Accédez à vos réservations de billets, vos alertes de voyage et au suivi de vos colis.') }}
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('login') }}" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center; font-weight: 700;">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i> {{ __('Connexion') }}
                        </a>
                        <a href="{{ route('register.passenger') }}" class="btn btn-primary btn-sm" style="flex: 1; justify-content: center; font-weight: 700;">
                            <i class="fa-solid fa-user-plus"></i> {{ __('Créer un Compte') }}
                        </a>
                    </div>
                @endauth
            </div>


            <!-- Preferences & Display Tools -->
            <div style="margin-bottom: 20px;">
                <div class="menu-section-label">{{ __('Préférences & Affichage') }}</div>
                <div class="menu-tools-grid">
                    <!-- Language Selector in Drawer -->
                    <div class="menu-tool-card" style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-globe" style="color: var(--primary); font-size: 1.1rem;"></i>
                            <div>
                                <div style="font-weight: 700; font-size: 0.88rem;">{{ __('Langue d\'affichage') }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ app()->getLocale() === 'fr' ? 'Français' : 'English' }}</div>
                            </div>
                        </div>
                        @include('components.bilingual-switcher')
                    </div>

                    <!-- Theme Switcher -->
                    <button type="button" class="menu-tool-card" onclick="toggleTheme()" aria-label="{{ __('Mode Sombre / Clair') }}" style="width: 100%; border: 1px solid var(--border-color); cursor: pointer; text-align: left; background: var(--bg-surface);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-moon mobile-theme-icon" style="color: var(--primary); font-size: 1.1rem;"></i>
                            <div>
                                <div style="font-weight: 700; font-size: 0.88rem;">{{ __('Mode Sombre / Clair') }}</div>
                                <div style="font-size: 0.72rem; color: var(--text-muted);">{{ __('Adapter la luminosité') }}</div>
                            </div>
                        </div>
                        <span class="badge badge-outline" style="font-size: 0.75rem;" id="themeLabelText">
                            <i class="fa-solid fa-circle-half-stroke"></i> {{ __('Changer') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Bar Footer -->
        <div class="menu-bar-footer">
            @auth
                <form action="{{ route('logout') }}" method="POST" style="margin-bottom: 14px;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center; color: var(--danger); border-color: var(--danger-border); font-weight: 700;">
                        <i class="fa-solid fa-power-off"></i> {{ __('Se Déconnecter') }}
                    </button>
                </form>
            @endauth

            <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center; line-height: 1.4;">
                <div><i class="fa-solid fa-headset" style="color: var(--primary);"></i> {{ __('Assistance Voyageurs 24/7 :') }} <strong>+237 233 42 00 00</strong></div>
                <div style="margin-top: 4px; font-size: 0.7rem; color: var(--text-light);">{{ __('Plateforme Nationale Sécurisée • République du Cameroun') }}</div>
            </div>
        </div>
    </aside>
    @endif

    <!-- Main Content Body -->
    <main>
        @if(!request()->routeIs('login', 'register*'))
        <div class="container" style="padding-top: 16px;">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i>
                    <div style="flex: 1;">{{ __(session('success')) }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation" style="font-size: 1.25rem;"></i>
                    <div style="flex: 1;">{{ __(session('error')) }}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info" style="background: var(--primary-50); border: 1.5px solid var(--primary-100); color: var(--primary); padding: 14px 18px; border-radius: var(--radius-md); display: flex; align-items: center; gap: 12px; margin-bottom: 20px; font-weight: 600;">
                    <i class="fa-solid fa-circle-info" style="font-size: 1.25rem; color: var(--primary);"></i>
                    <div style="flex: 1;">{{ __(session('info')) }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.25rem;"></i>
                    <div style="flex: 1;">
                        <ul style="margin: 0; padding-left: 18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
        @endif

        @yield('content')
    </main>

    @if(!request()->routeIs('login', 'register*'))
    <!-- Mobile Sticky Bottom Nav Bar -->
    <nav class="mobile-bottom-bar no-print">
        <div class="mobile-bottom-inner">
            <a href="{{ route('home') }}" class="bottom-tab {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fa-solid fa-compass"></i>
                <span>{{ __('Explorer') }}</span>
            </a>
            <a href="{{ route('trips.index') }}" class="bottom-tab {{ request()->routeIs('trips.*') ? 'active' : '' }}">
                <i class="fa-solid fa-ticket"></i>
                <span>{{ __('Trajets') }}</span>
            </a>
            <a href="{{ route('shipments.track') }}" class="bottom-tab {{ request()->routeIs('shipments.track') ? 'active' : '' }}">
                <i class="fa-solid fa-truck-fast"></i>
                <span>{{ __('Colis') }}</span>
            </a>
            @auth
                @if(Auth::user()->isManager())
                    <a href="{{ route('manager.dashboard') }}" class="bottom-tab {{ request()->is('manager*') ? 'active' : '' }}">
                        <i class="fa-solid fa-building-user"></i>
                        <span>{{ __('Espace Agence') }}</span>
                    </a>
                @elseif(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="bottom-tab {{ request()->is('admin*') ? 'active' : '' }}">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>{{ __('Administration') }}</span>
                    </a>
                @else
                    <a href="{{ route('passenger.dashboard') }}" class="bottom-tab {{ request()->is('passenger*') ? 'active' : '' }}">
                        <i class="fa-solid fa-circle-user"></i>
                        <span>{{ __('Mon Espace') }}</span>
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="bottom-tab {{ request()->routeIs('login') ? 'active' : '' }}">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>{{ __('Connexion') }}</span>
                </a>
            @endauth
        </div>
    </nav>

    <!-- Footer -->
    <footer style="background: #0F172A !important; color: #E2E8F0; padding: 60px 0 30px; margin-top: 60px; border-top: 1px solid #1E293B;" class="no-print">
        <div class="container">
            <div class="grid grid-cols-4" style="gap: 40px; margin-bottom: 40px;">
                <div>
                    <div class="brand-logo" style="color: white; margin-bottom: 16px; gap: 12px;">
                        <img src="{{ asset('images/logo.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="Real Express Voyages" style="width: 44px; height: 44px; object-fit: contain; flex-shrink: 0;">
                        <span>{{ config('app.name', 'Real Express Voyages') }}</span>
                    </div>
                    <p style="font-size: 0.88rem; color: #94A3B8; line-height: 1.6; margin-bottom: 16px;">
                        {{ __('Plateforme nationale unifiée de billetterie multimodale (Autocars & Trains Camrail) et transport de colis avec paiement Mobile Money au Cameroun.') }}
                    </p>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span class="badge badge-outline" style="color: #CBD5E1; border-color: #334155;">
                            Orange Money
                        </span>
                        <span class="badge badge-outline" style="color: #CBD5E1; border-color: #334155;">
                            MTN MoMo
                        </span>
                    </div>
                </div>

                <div>
                    <h4 style="color: white; font-size: 1rem; margin-bottom: 18px;">{{ __('Services Voyageurs') }}</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
                        <li><a href="{{ route('trips.index', ['mode' => 'road']) }}" style="color: #94A3B8;">{{ __('Réservation Autocar & Bus') }}</a></li>
                        <li><a href="{{ route('trips.index') }}" style="color: #94A3B8;">{{ __('Horaires & Départs') }}</a></li>
                        <li><a href="{{ route('passenger.shipments.create') }}" style="color: #94A3B8;">{{ __('Expédition de Colis') }}</a></li>
                        <li><a href="{{ route('shipments.track') }}" style="color: #94A3B8;">{{ __('Suivi de Colis en Direct') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 style="color: white; font-size: 1rem; margin-bottom: 18px;">{{ __('Lignes Populaires') }}</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
                        <li><a href="{{ route('trips.index', ['departure' => 'Douala', 'arrival' => 'Yaoundé']) }}" style="color: #94A3B8;">{{ __('Douala') }} &harr; {{ __('Yaoundé') }}</a></li>
                        <li><a href="{{ route('trips.index', ['departure' => 'Yaoundé', 'arrival' => 'Ngaoundéré']) }}" style="color: #94A3B8;">{{ __('Yaoundé') }} &harr; {{ __('Ngaoundéré') }}</a></li>
                        <li><a href="{{ route('trips.index', ['departure' => 'Douala', 'arrival' => 'Bafoussam']) }}" style="color: #94A3B8;">{{ __('Douala') }} &harr; {{ __('Bafoussam') }}</a></li>
                        <li><a href="{{ route('trips.index', ['departure' => 'Yaoundé', 'arrival' => 'Garoua']) }}" style="color: #94A3B8;">{{ __('Yaoundé') }} &harr; {{ __('Garoua') }}</a></li>
                    </ul>
                </div>

                @guest
                <div>
                    <h4 style="color: white; font-size: 1rem; margin-bottom: 18px;">{{ __('Espace Client') }}</h4>
                    <p style="font-size: 0.85rem; color: #94A3B8; margin-bottom: 14px;">
                        {{ __('Créez un compte voyageur pour réserver vos places et suivre vos expéditions.') }}
                    </p>
                    <a href="{{ route('register.passenger') }}" class="btn btn-sm btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-user-plus"></i> {{ __('Créer un Compte Client') }}
                    </a>
                </div>
                @else
                <div>
                    <h4 style="color: white; font-size: 1rem; margin-bottom: 18px;">{{ __('Mon Espace') }}</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
                        @if(Auth::user()->isAdmin())
                            <li><a href="{{ route('admin.dashboard') }}" style="color: #94A3B8;"><i class="fa-solid fa-chart-line"></i> {{ __('Supervision Admin') }}</a></li>
                            <li><a href="{{ route('admin.monitor.index') }}" style="color: #94A3B8;"><i class="fa-solid fa-tower-broadcast"></i> {{ __('Suivi Départs') }}</a></li>
                            <li><a href="{{ route('admin.disputes.index') }}" style="color: #94A3B8;"><i class="fa-solid fa-scale-balanced"></i> {{ __('Service Client / Litiges') }}</a></li>
                        @elseif(Auth::user()->isManager())
                            <li><a href="{{ route('manager.dashboard') }}" style="color: #94A3B8;"><i class="fa-solid fa-gauge-high"></i> {{ __('Tableau de Bord') }}</a></li>
                            <li><a href="{{ route('manager.trips.index') }}" style="color: #94A3B8;"><i class="fa-solid fa-route"></i> {{ __('Gestion Trajets') }}</a></li>
                            <li><a href="{{ route('manager.shipments.index') }}" style="color: #94A3B8;"><i class="fa-solid fa-boxes-stacked"></i> {{ __('Gestion Fret & Colis') }}</a></li>
                        @else
                            <li><a href="{{ route('passenger.dashboard') }}" style="color: #94A3B8;"><i class="fa-solid fa-ticket"></i> {{ __('Mon Espace Voyageur') }}</a></li>
                            <li><a href="{{ route('passenger.bookings.history') }}" style="color: #94A3B8;"><i class="fa-solid fa-clock-rotate-left"></i> {{ __('Mes Billets & Voyages') }}</a></li>
                            <li><a href="{{ route('passenger.shipments.index') }}" style="color: #94A3B8;"><i class="fa-solid fa-box-open"></i> {{ __('Mes Envois de Colis') }}</a></li>
                        @endif
                        <li><a href="{{ route('profile.edit') }}" style="color: #94A3B8;"><i class="fa-solid fa-user-gear"></i> {{ __('Mon Profil & Sécurité') }}</a></li>
                    </ul>
                </div>
                @endguest
            </div>

            <div style="border-top: 1px solid #1E293B; padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; font-size: 0.8rem; color: #64748B;">
                <div>
                    &copy; {{ date('Y') }} {{ config('app.name', 'Travel') }} • {{ __('Plateforme Nationale Unifiée • République du Cameroun') }}
                </div>
                <div style="display: flex; gap: 16px;">
                    <span>{{ __('République du Cameroun') }}</span>
                    <span>•</span>
                    <span>{{ __('Paix - Travail - Patrie') }}</span>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <!-- Theme Switcher & Mobile Drawer Scripts -->
    <script>
        function updateThemeElements(theme) {
            const icons = document.querySelectorAll('#themeIcon, .mobile-theme-icon, #themeIconNav');
            icons.forEach(icon => {
                if (theme === 'dark') {
                    icon.className = 'fa-solid fa-sun';
                    icon.style.color = '#FCD116';
                } else {
                    icon.className = 'fa-solid fa-moon';
                    icon.style.color = '';
                }
            });

            // Update drawer theme button label
            const labelText = document.getElementById('themeLabelText');
            if (labelText) {
                const isFr = '{{ app()->getLocale() }}' === 'fr';
                if (theme === 'dark') {
                    labelText.innerHTML = `<i class="fa-solid fa-sun" style="color: #FCD116;"></i> ${isFr ? 'Sombre' : 'Dark'}`;
                } else {
                    labelText.innerHTML = `<i class="fa-solid fa-moon"></i> ${isFr ? 'Clair' : 'Light'}`;
                }
            }

            // Sync meta theme-color for mobile status bar
            const metaThemeColor = document.querySelector('meta[name="theme-color"]');
            if (metaThemeColor) {
                metaThemeColor.setAttribute('content', theme === 'dark' ? '#111827' : '#F8FAFC');
            }
        }

        // Backward compatibility
        function updateThemeIcons(theme) {
            updateThemeElements(theme);
        }

        function applyTheme(theme, animate = true) {
            if (animate) {
                document.documentElement.classList.add('theme-transitioning');
            }

            document.documentElement.setAttribute('data-theme', theme);
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            try {
                localStorage.setItem('realvoyage_theme', theme);
                localStorage.setItem('transportcm_theme', theme);
                document.cookie = `realvoyage_theme=${theme};path=/;max-age=31536000;SameSite=Lax`;
                document.cookie = `transportcm_theme=${theme};path=/;max-age=31536000;SameSite=Lax`;
            } catch (e) {}

            updateThemeElements(theme);

            // Broadcast theme change event for WebGL 3D, Leaflet, and Charts
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));

            if (animate) {
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transitioning');
                }, 300);
            }
        }

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark', true);
        }

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('realvoyage_theme') && !localStorage.getItem('transportcm_theme')) {
                    applyTheme(e.matches ? 'dark' : 'light', true);
                }
            });
        }

        // Global Password Visibility Toggle
        function togglePasswordVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                if (isPassword) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    btn.setAttribute('aria-label', "{{ __('Masquer') }}");
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    btn.setAttribute('aria-label', "{{ __('Afficher') }}");
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Register PWA Service Worker for Offline access
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register("{{ asset('sw.js') }}").catch(function() {});
            }

            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeElements(currentTheme);

            const menuBarOpenBtn = document.getElementById('menuBarOpen');
            const menuBarCloseBtn = document.getElementById('menuBarClose');
            const menuBarDrawer = document.getElementById('menuBarDrawer');
            const menuBarOverlay = document.getElementById('menuBarOverlay');

            function openMenuBar() {
                if (menuBarDrawer && menuBarOverlay) {
                    menuBarDrawer.classList.add('active');
                    menuBarOverlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeMenuBar() {
                if (menuBarDrawer && menuBarOverlay) {
                    menuBarDrawer.classList.remove('active');
                    menuBarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }

            if (menuBarOpenBtn) menuBarOpenBtn.addEventListener('click', openMenuBar);
            if (menuBarCloseBtn) menuBarCloseBtn.addEventListener('click', closeMenuBar);
            if (menuBarOverlay) menuBarOverlay.addEventListener('click', closeMenuBar);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && menuBarDrawer && menuBarDrawer.classList.contains('active')) {
                    closeMenuBar();
                }
            });
        });
    </script>

    <!-- Hidden Google Translate Element Anchor -->
    <div id="google_translate_element" style="display:none;" aria-hidden="true"></div>

    <script>
        // Google Translate Element Initialization Callback
        function googleTranslateElementInit() {
            if (window.google && window.google.translate) {
                new window.google.translate.TranslateElement({
                    pageLanguage: 'fr',
                    includedLanguages: 'fr,en',
                    autoDisplay: false,
                    layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE
                }, 'google_translate_element');
            }
        }

        // Global switchAppLanguage function accessible everywhere
        window.switchAppLanguage = function(targetLang) {
            if (!['fr', 'en'].includes(targetLang)) return;

            // 1. Immediately update UI state in all switcher buttons
            document.querySelectorAll('.bilingual-switch-container').forEach(function(el) {
                el.setAttribute('data-current-locale', targetLang);
                el.querySelectorAll('.bilingual-pill-btn').forEach(function(btn) {
                    const isTarget = btn.getAttribute('onclick') && btn.getAttribute('onclick').includes("'" + targetLang + "'");
                    btn.classList.toggle('active', isTarget);
                    btn.setAttribute('aria-pressed', isTarget ? 'true' : 'false');
                });
            });

            // 2. Set Cookies for Google Translate & App Locale
            const expires = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString();
            const googVal = targetLang === 'en' ? '/fr/en' : '/fr/fr';

            // Root path cookies
            document.cookie = 'googtrans=' + googVal + '; path=/; expires=' + expires;
            document.cookie = 'realvoyage_locale=' + targetLang + '; path=/; expires=' + expires;
            document.cookie = 'transportcm_locale=' + targetLang + '; path=/; expires=' + expires;

            // Domain cookies if applicable
            const host = window.location.hostname;
            if (host && host !== 'localhost' && !host.match(/^[0-9.]+$/)) {
                document.cookie = 'googtrans=' + googVal + '; path=/; domain=.' + host + '; expires=' + expires;
            }

            // 3. Immediately switch language via server endpoint to re-render all views in full translation
            window.location.href = '/lang/' + targetLang;
        };
    </script>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>

    @yield('scripts')
</body>
</html>
