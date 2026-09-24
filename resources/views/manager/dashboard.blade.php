@extends('layouts.dashboard')

@section('title', __('Espace Chef d\'Exploitation - Real Voyage S.A.'))

@section('dashboard_content')
<div>
    <!-- Header with Terminal Badge -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge" style="background: var(--bg-dark, #1E293B); color: #FFFFFF; font-size: 0.75rem; padding: 4px 10px; border-radius: 4px;">
                    Real Voyage Transport S.A.
                </span>
                @if($terminal)
                    <span class="badge" style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 4px;">
                        <i class="fa-solid fa-location-dot"></i> {{ $terminal->name }} ({{ __('Région') }} {{ $terminal->region }})
                    </span>
                @else
                    <span class="badge" style="background: #F1F5F9; color: #475569; font-size: 0.75rem; padding: 4px 10px; border-radius: 4px;">
                        {{ __("Direction de l'Exploitation") }}
                    </span>
                @endif
            </div>
            <h1 style="font-size: 1.8rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                {{ __('Tableau de Bord Gare & Exploitation') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
                {{ $terminal ? __('Supervision des flux de passagers, guichet fret et départs fixes de la gare :name.', ['name' => $terminal->name]) : __('Supervision centralisée des 11 terminaux Real Voyage S.A.') }}
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('manager.checkin.index') }}" class="btn btn-primary" style="font-weight: 700;">
                <i class="fa-solid fa-qrcode"></i> {{ __('Contrôle & Embarquement') }}
            </a>
            <a href="{{ route('manager.trips.create') }}" class="btn btn-outline" style="font-weight: 700;">
                <i class="fa-solid fa-plus-circle"></i> {{ __('Programmer Départ (10h00 / 21h30)') }}
            </a>
        </div>
    </div>

    <!-- Financial & Performance KPI Cards -->
    <div class="grid grid-cols-4" style="margin-bottom: 30px; gap: 16px;">
        <div class="card" style="padding: 20px; border-radius: 10px;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Recettes Locales Gare') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: var(--text-heading); margin: 6px 0;">
                {{ number_format($totalRevenue, 0, ',', ' ') }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">FCFA</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                {{ __('Billetterie + Fret Colis (10%)') }}
            </div>
        </div>

        <div class="card" style="padding: 20px; border-radius: 10px;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Billets Émis / Confirmés') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: var(--text-heading); margin: 6px 0;">
                {{ number_format($ticketRevenue, 0, ',', ' ') }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">FCFA</span>
            </div>
            <div style="font-size: 0.75rem; color: #059669; font-weight: 700;">
                {{ $totalBookings }} {{ __('passagers enregistrés') }}
            </div>
        </div>

        <div class="card" style="padding: 20px; border-radius: 10px;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Guichet Fret Express') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: var(--text-heading); margin: 6px 0;">
                {{ number_format($cargoRevenue, 0, ',', ' ') }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">FCFA</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                {{ __('Règle stricte des 10% valeur') }}
            </div>
        </div>

        <div class="card" style="padding: 20px; border-radius: 10px;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ __('Remplissage Flotte') }}</div>
            <div style="font-size: 1.7rem; font-weight: 900; color: {{ $occupancyRate >= 70 ? '#059669' : '#D97706' }}; margin: 6px 0;">
                {{ $occupancyRate }}%
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">
                {{ __('Autocars 75 et 80 places') }}
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="grid grid-cols-3" style="gap: 20px; margin-bottom: 30px;">
        <div class="card card-glass" style="padding: 22px; grid-column: span 2; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.05rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-chart-line text-primary"></i> {{ __('Recettes Hebdomadaires Gare') }}
                </h3>
                <span class="badge" style="background: var(--bg-surface); color: var(--text-muted); font-size: 0.7rem; padding: 4px 8px;">{{ __('Billets & Fret') }}</span>
            </div>
            <div style="height: 240px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="card card-glass" style="padding: 22px; border-radius: 10px;">
            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 1.05rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-chart-pie text-primary"></i> {{ __('Modes de Règlement') }}
                </h3>
            </div>
            <div style="height: 240px; position: relative; display: flex; align-items: center; justify-content: center;">
                <canvas id="paymentsSplitChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Upcoming Trips and Recent Shipments Grid -->
    <div class="grid grid-cols-2" style="gap: 24px;">
        <!-- Left: Upcoming Scheduled Trips -->
        <div class="card" style="padding: 24px; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.15rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-route text-primary"></i> {{ __('Prochains Départs Programmés') }}
                </h3>
                <a href="{{ route('manager.trips.index') }}" style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">
                    {{ __('Tous les départs') }} &rarr;
                </a>
            </div>

            @if($upcomingTrips->isEmpty())
                <p style="color: var(--text-muted); font-size: 0.85rem; padding: 16px 0;">{{ __('Aucun départ programmé.') }}</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($upcomingTrips as $t)
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading);">
                                    {{ $t->departure_city }} &rarr; {{ $t->arrival_city }}
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                    <i class="fa-regular fa-clock"></i> <strong>{{ $t->departure_time->format('H:i') }}</strong> ({{ $t->departure_time->format('d/m/Y') }})
                                    &bull; {{ $t->vehicle->capacity_seats ?? 75 }} {{ __('places') }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; font-weight: 800; color: #059669;">
                                    {{ $t->seats_available }} {{ __('places libres') }}
                                </div>
                                <a href="{{ route('manager.trips.manifest', $t) }}" class="btn btn-sm btn-outline" style="margin-top: 4px; font-size: 0.75rem; padding: 3px 8px;">
                                    {{ __('Manifeste') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Recent Cargo Shipments -->
        <div class="card" style="padding: 24px; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 1.15rem; color: var(--text-heading); font-weight: 800; margin: 0;">
                    <i class="fa-solid fa-box text-primary"></i> {{ __('Fret & Expéditions Récentes') }}
                </h3>
                <a href="{{ route('manager.shipments.index') }}" style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">
                    {{ __('Tous les colis') }} &rarr;
                </a>
            </div>

            @if($recentShipments->isEmpty())
                <p style="color: var(--text-muted); font-size: 0.85rem; padding: 16px 0;">{{ __('Aucun colis enregistré pour cette gare.') }}</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($recentShipments as $s)
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-heading);">{{ $s->tracking_code }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-main);">{{ __('Pour :') }} {{ $s->recipient_name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $s->originTerminal->name ?? __('Gare') }} &rarr; {{ $s->destinationTerminal->name ?? $s->destination_station }} &bull; {{ $s->weight_kg }} kg
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span class="badge" style="background: #ECFDF5; color: #059669; font-size: 0.7rem;">
                                    {{ strtoupper($s->status) }}
                                </span>
                                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-heading); margin-top: 4px;">
                                    {{ number_format($s->total_amount, 0, ',', ' ') }} F
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxRevenue = document.getElementById('revenueChart');
    if (ctxRevenue && typeof Chart !== 'undefined') {
        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: ["{{ __('Lun') }}", "{{ __('Mar') }}", "{{ __('Mer') }}", "{{ __('Jeu') }}", "{{ __('Ven') }}", "{{ __('Sam') }}", "{{ __('Dim') }}"],
                datasets: [
                    {
                        label: "{{ __('Billetterie (FCFA)') }}",
                        data: [150000, 210000, 180000, 290000, 340000, 520000, 440000],
                        borderColor: '#0F2942',
                        backgroundColor: 'rgba(15, 41, 66, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2.5,
                    },
                    {
                        label: "{{ __('Fret (10% Valeur)') }}",
                        data: [50000, 65000, 60000, 85000, 110000, 160000, 130000],
                        borderColor: '#334155',
                        backgroundColor: 'rgba(51, 65, 85, 0.08)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: '600' } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => (v / 1000) + 'k' }
                    }
                }
            }
        });
    }

    const ctxPayments = document.getElementById('paymentsSplitChart');
    if (ctxPayments && typeof Chart !== 'undefined') {
        new Chart(ctxPayments, {
            type: 'doughnut',
            data: {
                labels: ['Orange Money', 'MTN MoMo', "{{ __('E-Wallet Real Voyage') }}"],
                datasets: [{
                    data: [45, 35, 20],
                    backgroundColor: ['#EA580C', '#CA8A04', '#0F2942'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: '600' } } }
                },
                cutout: '68%'
            }
        });
    }
});
</script>
@endsection
