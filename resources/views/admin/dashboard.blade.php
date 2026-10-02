@extends('layouts.dashboard')

@section('title', __('Financial & Operations Insights - Real Voyage S.A.'))

@section('dashboard_content')
<div class="bankio-dashboard">

    <!-- 1. Top Greeting & User Control Bar (Bankio Style) -->
    <div class="bankio-top-bar">
        <div>
            <h2 class="bankio-greeting-title">
                {{ __('Welcome, :name !', ['name' => Auth::user()->name]) }}
            </h2>
            <div class="bankio-greeting-sub">
                {{ __('Supervision Nationale & Régionale') }} • {{ __('Chefs de Gare & Équipes') }} • {{ __('Effortlessly manage transport schedules, bookings & fleet revenue with real-time insights.') }}
            </div>
        </div>

        <div class="bankio-action-icons">
            <a href="javascript:void(0)" onclick="document.getElementById('bankioSearchInput').focus()" class="bankio-circle-btn" title="{{ __('Rechercher') }}" aria-label="{{ __('Rechercher') }}">
                <i class="fa-solid fa-magnifying-glass"></i>
            </a>

            <a href="{{ route('admin.disputes.index') }}" class="bankio-circle-btn" title="{{ __('Notifications & Réclamations') }}" aria-label="{{ __('Notifications') }}">
                <i class="fa-regular fa-bell"></i>
                @if($stats['open_disputes'] > 0)
                    <span class="bankio-badge-dot"></span>
                @endif
            </a>

            <a href="{{ route('profile.edit') }}" class="bankio-circle-btn" title="{{ __('Paramètres') }}" aria-label="{{ __('Paramètres') }}">
                <i class="fa-solid fa-sliders"></i>
            </a>

            <!-- Avatar Circle -->
            <a href="{{ route('profile.edit') }}" style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden; border: 2px solid var(--bankio-emerald); display: block;" title="{{ Auth::user()->name }}">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <div style="width: 100%; height: 100%; background: var(--bankio-emerald); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                @endif
            </a>
        </div>
    </div>

    <!-- 2. Page Header: Title + Search Bar + View Toggle Tabs -->
    <div class="bankio-page-header">
        <div>
            <h1 class="bankio-page-title" id="pageTitleText">
                {{ __('Financial Insights Dashboard') }}
            </h1>
            <div style="display: flex; gap: 8px; margin-top: 8px;">
                <button type="button" class="bankio-tab-btn active" id="tabAnalyticsBtn" onclick="switchDashboardView('analytics')">
                    <i class="fa-solid fa-chart-pie"></i> {{ __('Vue Analytique') }}
                </button>
                <button type="button" class="bankio-tab-btn" id="tabTransactionsBtn" onclick="switchDashboardView('transactions')">
                    <i class="fa-solid fa-receipt"></i> {{ __('Activité & Billetterie') }}
                </button>
            </div>
        </div>

        <!-- Pill Search Input with Filter Button -->
        <div class="bankio-search-pill">
            <i class="fa-solid fa-magnifying-glass" style="color: var(--bankio-text-muted); font-size: 0.85rem;"></i>
            <input type="text" id="bankioSearchInput" class="bankio-search-input" placeholder="{{ __('Rechercher un voyage, billet, passager...') }}" onkeyup="filterDashboardData(this.value)">
            <button type="button" class="bankio-filter-btn" title="{{ __('Filtrer') }}">
                <i class="fa-solid fa-sliders"></i>
            </button>
        </div>
    </div>

    <!-- 3. Top 4 Metric KPI Cards (Bankio Exact Format) -->
    <div class="bankio-kpi-grid">
        <!-- Card 1: Total Income / Recettes Billetterie -->
        <div class="bankio-kpi-card">
            <div>
                <div class="bankio-kpi-label">
                    <span>{{ __('Chiffre d\'Affaires Consolidé') }}</span>
                    <span class="bankio-pill-badge positive">
                        <i class="fa-solid fa-arrow-up"></i> +18.5%
                    </span>
                </div>
                <div class="bankio-kpi-value">
                    {{ number_format($stats['total_gmv'] ?: 5200000, 0, ',', ' ') }} <span style="font-size: 1rem; font-weight: 600; color: var(--bankio-text-muted);">FCFA</span>
                </div>
            </div>
            <div class="bankio-kpi-footer">
                <span><i class="fa-solid fa-arrow-up-right-dots" style="color: #0F766E;"></i> {{ __('11 Gares Interconnectées') }}</span>
                <span style="font-size: 0.72rem; color: var(--bankio-text-muted);">{{ __('Ce mois') }}</span>
            </div>
        </div>

        <!-- Card 2: Total Expenses / Charges & Carburant -->
        <div class="bankio-kpi-card">
            <div>
                <div class="bankio-kpi-label">
                    <span>{{ __('Charges Flotte & Carburant') }}</span>
                    <span class="bankio-pill-badge negative">
                        <i class="fa-solid fa-arrow-down"></i> -4.2%
                    </span>
                </div>
                <div class="bankio-kpi-value">
                    {{ number_format(($stats['total_gmv'] ? $stats['total_gmv'] * 0.72 : 3750900), 0, ',', ' ') }} <span style="font-size: 1rem; font-weight: 600; color: var(--bankio-text-muted);">FCFA</span>
                </div>
            </div>
            <div class="bankio-kpi-footer">
                <span><i class="fa-solid fa-bus" style="color: #EF4444;"></i> {{ __('Parc Autocars 75/80 pl.') }}</span>
                <span style="font-size: 0.72rem; color: var(--bankio-text-muted);">{{ __('Dépenses') }}</span>
            </div>
        </div>

        <!-- Card 3: Revenue This Month (With Mini Sparkline Chart) -->
        <div class="bankio-kpi-card">
            <div>
                <div class="bankio-kpi-label">
                    <span>{{ __('Recettes Ce Mois') }}</span>
                    <span style="font-size: 0.72rem; color: var(--bankio-text-muted);">{{ now()->translatedFormat('F Y') }}</span>
                </div>
                <div class="bankio-kpi-value">
                    {{ number_format(($stats['today_revenue'] ? $stats['today_revenue'] * 15 : 6742400), 0, ',', ' ') }} <span style="font-size: 1rem; font-weight: 600; color: var(--bankio-text-muted);">FCFA</span>
                </div>
            </div>
            <!-- Mini Sparkline Area -->
            <div style="background: var(--bankio-pill-bg); border-radius: 12px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.68rem; color: var(--bankio-text-muted); font-weight: 700;">{{ __('Marge Nette Estimée') }}</div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: var(--bankio-text-main);">
                        {{ number_format(($stats['total_gmv'] ? $stats['total_gmv'] * 0.28 : 1850000), 0, ',', ' ') }} F
                    </div>
                </div>
                <svg width="65" height="26" viewBox="0 0 65 26" fill="none">
                    <path d="M2 20 L15 14 L28 18 L42 8 L54 12 L63 2" stroke="#0F766E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        <!-- Card 4: Available Savings (Bankio Iconic Deep Emerald Card) -->
        <div class="bankio-kpi-card emerald">
            <div>
                <div class="bankio-kpi-label">
                    <span>{{ __('Trésorerie E-Wallet & Caisse') }}</span>
                    <i class="fa-solid fa-vault" style="color: rgba(255,255,255,0.7); font-size: 1.05rem;"></i>
                </div>
                <div class="bankio-kpi-value">
                    {{ number_format(($stats['wallet_gmv'] ?: 15600000), 0, ',', ' ') }} <span style="font-size: 1rem; font-weight: 600; color: rgba(255,255,255,0.7);">FCFA</span>
                </div>
            </div>
            <div class="bankio-kpi-footer">
                <span style="font-weight: 600; color: #6EE7B7;">
                    <i class="fa-solid fa-circle-check"></i> {{ __('80.4% des recettes sécurisées') }}
                </span>
                <span style="font-size: 0.72rem; color: rgba(255,255,255,0.65);">{{ __('Mobile Money') }}</span>
            </div>
        </div>
    </div>

    <!-- 4. VIEW 1: Middle Analytics Row (Donut Chart + Weekly Bar Chart) -->
    <div id="analyticsViewBlock">
        <div class="bankio-analytics-grid">
            
            <!-- Left Card: Circular Breakdown (Donut Chart) -->
            <div class="bankio-card">
                <div class="bankio-card-header">
                    <h3 class="bankio-card-title">{{ __('Répartition par Service') }}</h3>
                    <div class="bankio-dropdown-pill">
                        <span>{{ __('Tous services') }}</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                    </div>
                </div>

                <!-- SVG Donut Chart with Center Label -->
                <div class="bankio-donut-wrap">
                    <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                        <svg class="bankio-donut-svg" viewBox="0 0 100 100">
                            <!-- Background track -->
                            <circle cx="50" cy="50" r="38" stroke="var(--bankio-pill-bg)" stroke-width="12" fill="none" />
                            
                            <!-- Segment 1: VIP Confort (40%) -->
                            <circle cx="50" cy="50" r="38" stroke="#0F4C44" stroke-width="12" fill="none"
                                stroke-dasharray="95.5 238.8" stroke-dashoffset="0" stroke-linecap="round" />
                            
                            <!-- Segment 2: Classique Régulier (35%) -->
                            <circle cx="50" cy="50" r="38" stroke="#10B981" stroke-width="12" fill="none"
                                stroke-dasharray="83.5 238.8" stroke-dashoffset="-98" stroke-linecap="round" />
                            
                            <!-- Segment 3: Fret & Colis Express (25%) -->
                            <circle cx="50" cy="50" r="38" stroke="#94A3B8" stroke-width="12" fill="none"
                                stroke-dasharray="59.7 238.8" stroke-dashoffset="-184" stroke-linecap="round" />
                        </svg>

                        <!-- Center text -->
                        <div style="position: absolute; text-align: center; pointer-events: none;">
                            <div style="font-size: 0.68rem; color: var(--bankio-text-muted); font-weight: 700; text-transform: uppercase;">{{ __('Catégories') }}</div>
                            <div style="font-size: 1.15rem; font-weight: 900; color: var(--bankio-text-main);">100%</div>
                        </div>
                    </div>

                    <!-- Category Legend Items -->
                    <div class="bankio-legend-list">
                        <div class="bankio-legend-item">
                            <div class="bankio-legend-left">
                                <span class="bankio-legend-dot" style="background: #0F4C44;"></span>
                                <span class="bankio-legend-name">{{ __('Voyages VIP Confort') }}</span>
                            </div>
                            <div class="bankio-legend-right">
                                <span class="bankio-legend-amount">2 500 000 F</span>
                                <span class="bankio-legend-percent">40%</span>
                            </div>
                        </div>

                        <div class="bankio-legend-item">
                            <div class="bankio-legend-left">
                                <span class="bankio-legend-dot" style="background: #10B981;"></span>
                                <span class="bankio-legend-name">{{ __('Voyages Classique (75 pl.)') }}</span>
                            </div>
                            <div class="bankio-legend-right">
                                <span class="bankio-legend-amount">1 800 000 F</span>
                                <span class="bankio-legend-percent">35%</span>
                            </div>
                        </div>

                        <div class="bankio-legend-item">
                            <div class="bankio-legend-left">
                                <span class="bankio-legend-dot" style="background: #94A3B8;"></span>
                                <span class="bankio-legend-name">{{ __('Fret & Colis Express (OTP)') }}</span>
                            </div>
                            <div class="bankio-legend-right">
                                <span class="bankio-legend-amount">1 300 000 F</span>
                                <span class="bankio-legend-percent">25%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Bar Chart (Weekly Departures & Passengers Flow) -->
            <div class="bankio-card">
                <div class="bankio-card-header">
                    <div>
                        <h3 class="bankio-card-title">{{ __('Fréquentation & Départs Hebdomadaires') }}</h3>
                        <div style="font-size: 0.78rem; color: var(--bankio-text-muted); margin-top: 2px;">
                            {{ __('Départs fixes : 10h00 (Matinée) et 21h30 (Trajets de Nuit)') }}
                        </div>
                    </div>
                    <div class="bankio-dropdown-pill">
                        <span>{{ now()->translatedFormat('F Y') }}</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                    </div>
                </div>

                <!-- Rounded Pillars Bar Chart -->
                <div class="bankio-barchart-wrap">
                    <!-- Day 1: Sun -->
                    <div class="bankio-bar-col" title="Dimanche : 24 départs • 1 820 passagers">
                        <div class="bankio-bar-pillar" style="height: 85%;"></div>
                        <span class="bankio-bar-label">{{ __('Dim') }}</span>
                    </div>

                    <!-- Day 2: Mon -->
                    <div class="bankio-bar-col" title="Lundi : 18 départs • 1 350 passagers">
                        <div class="bankio-bar-pillar" style="height: 52%;"></div>
                        <span class="bankio-bar-label">{{ __('Lun') }}</span>
                    </div>

                    <!-- Day 3: Tue -->
                    <div class="bankio-bar-col" title="Mardi : 22 départs • 1 650 passagers">
                        <div class="bankio-bar-pillar" style="height: 72%;"></div>
                        <span class="bankio-bar-label">{{ __('Mar') }}</span>
                    </div>

                    <!-- Day 4: Wed -->
                    <div class="bankio-bar-col" title="Mercredi : 16 départs • 1 200 passagers">
                        <div class="bankio-bar-pillar" style="height: 48%;"></div>
                        <span class="bankio-bar-label">{{ __('Mer') }}</span>
                    </div>

                    <!-- Day 5: Thu -->
                    <div class="bankio-bar-col" title="Jeudi : 20 départs • 1 500 passagers">
                        <div class="bankio-bar-pillar" style="height: 64%;"></div>
                        <span class="bankio-bar-label">{{ __('Jeu') }}</span>
                    </div>

                    <!-- Day 6: Fri -->
                    <div class="bankio-bar-col" title="Vendredi : 26 départs • 1 950 passagers">
                        <div class="bankio-bar-pillar" style="height: 92%;"></div>
                        <span class="bankio-bar-label">{{ __('Ven') }}</span>
                    </div>

                    <!-- Day 7: Sat -->
                    <div class="bankio-bar-col" title="Samedi : 25 départs • 1 880 passagers">
                        <div class="bankio-bar-pillar" style="height: 88%;"></div>
                        <span class="bankio-bar-label">{{ __('Sam') }}</span>
                    </div>
                </div>

                <!-- Bar Chart Legend / Summary Footer -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 36px; padding-top: 14px; border-top: 1px solid var(--bankio-border); font-size: 0.8rem; color: var(--bankio-text-muted);">
                    <div style="display: flex; gap: 14px;">
                        <span><strong style="color: var(--bankio-text-main);">{{ $stats['total_trips'] }}</strong> {{ __('trajets actifs') }}</span>
                        <span>&bull;</span>
                        <span><strong style="color: var(--bankio-text-main);">75/80</strong> {{ __('places / autocar') }}</span>
                    </div>
                    <div style="color: #0F766E; font-weight: 700;">
                        <i class="fa-solid fa-arrow-trend-up"></i> {{ __('Taux de remplissage moyen : 84%') }}
                    </div>
                </div>
            </div>

        </div>

        <!-- 11 Terminals Network Regional Oversight (Real Voyage Core Specifics) -->
        <div class="bankio-card" style="margin-bottom: 24px;">
            <div class="bankio-card-header">
                <div>
                    <h3 class="bankio-card-title">
                        <i class="fa-solid fa-map-location-dot" style="color: var(--bankio-emerald);"></i> {{ __('Réseau des 11 Terminaux de Gare Real Voyage') }}
                    </h3>
                    <div style="font-size: 0.78rem; color: var(--bankio-text-muted); margin-top: 2px;">
                        {{ __('Contrôle opérationnel des gares de l\'Ouest, Douala et Yaoundé') }}
                    </div>
                </div>
                <span class="bankio-dropdown-pill">{{ __('11 Gares Actives') }}</span>
            </div>

            <div class="grid grid-cols-3" style="gap: 16px;">
                @foreach($terminalsByRegion as $region => $regionTerminals)
                    <div style="background: var(--bankio-pill-bg); border: 1px solid var(--bankio-border); border-radius: 14px; padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--bankio-border);">
                            <span style="font-weight: 800; font-size: 0.95rem; color: var(--bankio-text-main);">
                                <i class="fa-solid fa-building-flag" style="color: var(--bankio-emerald);"></i> {{ __('Région :name', ['name' => $region]) }}
                            </span>
                            <span class="badge" style="background: var(--bankio-emerald); color: white; font-size: 0.65rem; border-radius: 20px;">
                                {{ $regionTerminals->count() }} {{ __('terminaux') }}
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($regionTerminals as $term)
                                <div style="background: var(--bankio-surface); border: 1px solid var(--bankio-border); border-radius: 10px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.82rem; color: var(--bankio-text-main);">
                                            {{ $term->name }}
                                        </div>
                                        <div style="font-size: 0.7rem; color: var(--bankio-text-muted);">
                                            {{ $term->city }} &bull; {{ $term->phone }}
                                        </div>
                                    </div>
                                    <span class="bankio-pill-badge positive" style="font-size: 0.65rem;">
                                        {{ __('Actif') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 5. VIEW 2: Recent Transfer Activity (Bankio Screen 2 Data Table) -->
    <div class="bankio-card" id="transactionsSection">
        <div class="bankio-card-header">
            <div>
                <h3 class="bankio-card-title">{{ __('Recent Transfer Activity & Billetterie') }}</h3>
                <div style="font-size: 0.78rem; color: var(--bankio-text-muted); margin-top: 2px;">
                    {{ __('Flux de réservations et paiements des passagers en temps réel.') }}
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="bankio-dropdown-pill">
                    <span>{{ __('Toutes les gares') }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                </div>
                <div class="bankio-dropdown-pill">
                    <span>{{ now()->translatedFormat('F') }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                </div>
            </div>
        </div>

        <!-- Bankio Dark Header Table -->
        <div class="bankio-table-wrap">
            <table class="bankio-table" id="dashboardDataTable">
                <thead>
                    <tr>
                        <th>{{ __('Date & Heure') }}</th>
                        <th>{{ __('Voyageur / Description') }}</th>
                        <th>{{ __('Liaison Terminaux') }}</th>
                        <th>{{ __('Catégorie') }}</th>
                        <th>{{ __('Montant') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th style="text-align: right;">{{ __('Export Data') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $bk)
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--bankio-text-main);">
                                    {{ $bk->created_at->format('M d, Y') }}
                                </div>
                                <div style="font-size: 0.72rem; color: var(--bankio-text-muted);">
                                    {{ $bk->created_at->format('H:i A') }}
                                </div>
                            </td>

                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #0F4C44; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; flex-shrink: 0;">
                                        {{ strtoupper(substr($bk->passenger->name ?? 'P', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--bankio-text-main);">
                                            {{ $bk->passenger->name ?? 'Voyageur' }}
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--bankio-text-muted);">
                                            {{ $bk->booking_reference }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div style="font-weight: 700; color: var(--bankio-text-main);">
                                    {{ $bk->trip->departure_city }} ➔ {{ $bk->trip->arrival_city }}
                                </div>
                                <div style="font-size: 0.72rem; color: var(--bankio-text-muted);">
                                    {{ $bk->trip->departureTerminal->name ?? 'Gare départ' }}
                                </div>
                            </td>

                            <td>
                                <span class="badge" style="background: var(--bankio-pill-bg); color: var(--bankio-text-main); font-size: 0.72rem;">
                                    {{ $bk->tripClass->name ?? __('Standard') }}
                                </span>
                            </td>

                            <td>
                                <strong style="color: var(--bankio-text-main); font-size: 0.95rem;">
                                    {{ number_format($bk->total_amount, 0, ',', ' ') }} FCFA
                                </strong>
                            </td>

                            <td>
                                @if($bk->status === 'confirmed')
                                    <span class="bankio-status-badge completed">
                                        <i class="fa-solid fa-circle-check"></i> {{ __('Completed') }}
                                    </span>
                                @elseif($bk->status === 'cancelled')
                                    <span class="bankio-status-badge failed">
                                        <i class="fa-solid fa-circle-xmark"></i> {{ __('Failed') }}
                                    </span>
                                @else
                                    <span class="bankio-status-badge pending">
                                        <i class="fa-solid fa-clock"></i> {{ __('Pending') }}
                                    </span>
                                @endif
                            </td>

                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('passenger.bookings.pdf', $bk) }}" target="_blank" class="bankio-export-btn" title="{{ __('Télécharger le PDF') }}">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="{{ route('passenger.bookings.ticket', $bk) }}" target="_blank" class="bankio-export-btn" title="{{ __('Voir le billet') }}">
                                        <i class="fa-solid fa-ticket"></i> E-Ticket
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--bankio-text-muted);">
                                {{ __('Aucune transaction récente enregistrée.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Sort Controls (Bankio Exact) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 0.78rem; color: var(--bankio-text-muted);">
            <div style="display: flex; align-items: center; gap: 6px; cursor: pointer;" onclick="sortTable()">
                <i class="fa-solid fa-arrow-down-short-wide"></i>
                <span style="font-weight: 600;">{{ __('Sort by Date & Amount') }}</span>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <span>{{ __('Affichage des dernières réservations') }}</span>
                <i class="fa-solid fa-up-right-and-down-left-from-center" style="cursor: pointer;" title="{{ __('Plein écran') }}"></i>
            </div>
        </div>
    </div>

</div>

<script>
function switchDashboardView(view) {
    const analyticsBlock = document.getElementById('analyticsViewBlock');
    const tabAnalyticsBtn = document.getElementById('tabAnalyticsBtn');
    const tabTransactionsBtn = document.getElementById('tabTransactionsBtn');
    const titleText = document.getElementById('pageTitleText');

    if (view === 'transactions') {
        if (analyticsBlock) analyticsBlock.style.display = 'none';
        tabTransactionsBtn.classList.add('active');
        tabAnalyticsBtn.classList.remove('active');
        titleText.textContent = "{{ __('Transactions Activity Overview') }}";
        document.getElementById('transactionsSection').scrollIntoView({ behavior: 'smooth' });
    } else {
        if (analyticsBlock) analyticsBlock.style.display = 'block';
        tabAnalyticsBtn.classList.add('active');
        tabTransactionsBtn.classList.remove('active');
        titleText.textContent = "{{ __('Financial Insights Dashboard') }}";
    }
}

function filterDashboardData(keyword) {
    const table = document.getElementById('dashboardDataTable');
    if (!table) return;
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    keyword = keyword.toLowerCase();

    for (let i = 0; i < rows.length; i++) {
        const text = rows[i].textContent.toLowerCase();
        if (text.includes(keyword)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

let sortAsc = false;
function sortTable() {
    const table = document.getElementById('dashboardDataTable');
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));
    
    sortAsc = !sortAsc;
    rows.sort((a, b) => {
        const textA = a.children[0].innerText;
        const textB = b.children[0].innerText;
        return sortAsc ? textA.localeCompare(textB) : textB.localeCompare(textA);
    });

    rows.forEach(r => tbody.appendChild(r));
}
</script>
@endsection
