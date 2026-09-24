@extends('layouts.dashboard')

@section('title', __('Direction Générale - Supervision Real Voyage S.A.'))

@section('dashboard_content')
<div>
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span class="badge" style="background: var(--bg-dark, #1E293B); color: #FFFFFF; font-size: 0.75rem; padding: 4px 10px; border-radius: 4px;">
                    Real Voyage Transport S.A.
                </span>
                <span class="badge" style="background: #ECFDF5; color: #059669; font-size: 0.75rem; font-weight: 700;">
                    <i class="fa-solid fa-circle-check"></i> {{ __('Siège Direction Générale') }}
                </span>
            </div>
            <h1 style="font-size: 1.8rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                {{ __('Supervision Nationale & Régionale') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
                {{ __('Contrôle consolidé des 11 gares (Ouest, Douala, Yaoundé), horaires stricts (10h00 & 21h30), flotte 75/80 places et flux financiers.') }}
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('manager.trips.create') }}" class="btn btn-primary" style="font-weight: 700;">
                <i class="fa-solid fa-plus"></i> {{ __('Créer un Départ Fixe') }}
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="font-weight: 700;">
                <i class="fa-solid fa-users-gear"></i> {{ __('Chefs de Gare & Équipes') }}
            </a>
        </div>
    </div>

    <!-- Platform Consolidated GMV & Financial Cards -->
    <div class="grid grid-cols-4" style="margin-bottom: 30px; gap: 16px;">
        <!-- Total Platform GMV -->
        <div class="card" style="padding: 20px; background: var(--primary-gradient); color: white; border: none; border-radius: 12px; box-shadow: var(--shadow-md);">
            <div style="font-size: 0.75rem; font-weight: 700; color: rgba(255, 255, 255, 0.8); text-transform: uppercase;">{{ __('Chiffre d\'Affaires Consolidé') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: #34D399; margin: 6px 0;">
                {{ number_format($stats['total_gmv'], 0, ',', ' ') }} <span style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.75);">FCFA</span>
            </div>
            <div style="font-size: 0.72rem; color: rgba(255, 255, 255, 0.8); display: flex; gap: 6px; flex-wrap: wrap;">
                <span>OM: <strong>{{ number_format($stats['om_gmv'], 0, ',', ' ') }} F</strong></span>
                <span>&bull;</span>
                <span>MoMo: <strong>{{ number_format($stats['momo_gmv'], 0, ',', ' ') }} F</strong></span>
                <span>&bull;</span>
                <span>Wallet: <strong>{{ number_format($stats['wallet_gmv'], 0, ',', ' ') }} F</strong></span>
            </div>
        </div>

        <!-- Trips & Fixed Schedules -->
        <div class="card" style="padding: 20px; border-radius: 12px;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Départs Fixes Programmés') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: var(--text-heading); margin: 6px 0;">
                {{ $stats['total_trips'] }} <span style="font-size: 0.85rem; color: var(--text-muted);">{{ __('trajets') }}</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 8px;">
                <span><i class="fa-solid fa-sun" style="color: #F59E0B;"></i> {{ __('10h00 (Matin)') }}</span>
                <span>&bull;</span>
                <span><i class="fa-solid fa-moon" style="color: #3B82F6;"></i> {{ __('21h30 (Nuit)') }}</span>
            </div>
        </div>

        <!-- Total Bookings & Freight -->
        <div class="card" style="padding: 20px; border-radius: 12px;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Billetterie & Fret Express') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: var(--text-heading); margin: 6px 0;">
                {{ $stats['total_bookings'] }} <span style="font-size: 0.85rem; color: var(--text-muted);">{{ __('billets') }}</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                + {{ $stats['total_shipments'] }} {{ __('colis fret (10% valeur déclarée)') }}
            </div>
        </div>

        <!-- 11 Terminals Network -->
        <div class="card" style="padding: 20px; border-radius: 12px;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Réseau de Terminaux') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: var(--text-heading); margin: 6px 0;">
                11 <span style="font-size: 0.85rem; color: var(--text-muted);">{{ __('gares actives') }}</span>
            </div>
            <div style="font-size: 0.75rem; color: #059669; font-weight: 700;">
                {{ __('Ouest') }} (3) &bull; {{ __('Douala') }} (3) &bull; {{ __('Yaoundé') }} (5)
            </div>
        </div>
    </div>

    <!-- Real Voyage 11 Terminals Regional Oversight Cards -->
    <div class="card card-glass" style="padding: 24px; margin-bottom: 30px; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 1.15rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                <i class="fa-solid fa-map-location-dot text-primary"></i> {{ __('Répartition Opérationnelle des 11 Terminaux par Région') }}
            </h3>
            <span class="badge" style="background: var(--bg-surface); color: var(--text-heading); font-size: 0.75rem;">{{ __('Flotte 75 & 80 Places') }}</span>
        </div>

        <div class="grid grid-cols-3" style="gap: 20px;">
            @foreach($terminalsByRegion as $region => $regionTerminals)
                <div style="background: var(--bg-surface); border: 1.5px solid var(--border-subtle); border-radius: 10px; padding: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 2px solid var(--border-subtle); padding-bottom: 8px;">
                        <span style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                            <i class="fa-solid fa-building text-primary"></i> {{ __('Région') }} {{ $region }}
                        </span>
                        <span class="badge" style="background: var(--primary); color: white; font-size: 0.7rem;">
                            {{ $regionTerminals->count() }} {{ __('gares') }}
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($regionTerminals as $term)
                            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 6px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-heading);">
                                        {{ $term->name }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ $term->phone }} &bull; {{ $term->city }}
                                    </div>
                                </div>
                                <span class="badge" style="background: #ECFDF5; color: #059669; font-size: 0.65rem;">
                                    {{ __('Opérationnel') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Bookings Table -->
    @if($recentBookings->isNotEmpty())
        <div class="card" style="margin-bottom: 30px; padding: 24px; border-radius: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 1.15rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-ticket text-primary"></i> {{ __('Dernières Réservations Enregistrées (Real Voyage)') }}
                </h3>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Réf Billet') }}</th>
                            <th>{{ __('Passager') }}</th>
                            <th>{{ __('Liaison Terminaux') }}</th>
                            <th>{{ __('Départ Fixe') }}</th>
                            <th>{{ __('Sièges') }}</th>
                            <th>{{ __('Montant') }}</th>
                            <th>{{ __('Statut') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $bk)
                            <tr>
                                <td><strong style="color: var(--text-heading);">{{ $bk->booking_reference }}</strong></td>
                                <td>
                                    <strong>{{ $bk->passenger->name ?? 'Passager' }}</strong><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $bk->passenger->phone ?? '' }}</span>
                                </td>
                                <td>
                                    {{ $bk->trip->departure_city }} &rarr; {{ $bk->trip->arrival_city }}
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">
                                        {{ $bk->trip->departureTerminal->name ?? '' }}
                                    </div>
                                </td>
                                <td><strong>{{ $bk->trip->departure_time->format('H:i') }}</strong> ({{ $bk->trip->departure_time->format('d/m/Y') }})</td>
                                <td><span class="badge" style="background: var(--bg-surface); color: var(--text-heading); font-weight: 700;">{{ is_array($bk->seat_numbers) ? implode(', ', $bk->seat_numbers) : $bk->seat_numbers }}</span></td>
                                <td><strong>{{ number_format($bk->total_amount, 0, ',', ' ') }} FCFA</strong></td>
                                <td>
                                    <span class="badge {{ $bk->status === 'confirmed' ? 'badge-success' : 'badge-warning' }}">
                                        {{ strtoupper($bk->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
