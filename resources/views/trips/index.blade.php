@extends('layouts.app')

@section('title', __('Voyages & Départs Autocars - Real Voyage'))

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Filter Header -->
    <div class="card" style="margin-bottom: 30px; padding: 24px;">
        <form action="{{ route('trips.index') }}" method="GET">
            <div class="grid grid-cols-4" style="gap: 16px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem;"><i class="fa-solid fa-clock"></i> {{ __('Horaire de Départ') }}</label>
                    <select name="departure_time" class="form-control">
                        <option value="">{{ __('Tous les départs (10h00 / 21h00)') }}</option>
                        <option value="10:00:00" {{ request('departure_time') === '10:00:00' ? 'selected' : '' }}>{{ __('Départ Matin (10h00)') }}</option>
                        <option value="21:00:00" {{ request('departure_time') === '21:00:00' ? 'selected' : '' }}>{{ __('Départ Soirée (21h00)') }}</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem;"><i class="fa-solid fa-location-dot"></i> {{ __('Ville de Départ') }}</label>
                    <select name="from" class="form-control">
                        <option value="">{{ __('Toutes les origines') }}</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ request('from') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem;"><i class="fa-solid fa-location-crosshairs"></i> {{ __('Ville d\'Arrivée') }}</label>
                    <select name="to" class="form-control">
                        <option value="">{{ __('Toutes les destinations') }}</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ request('to') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 46px; font-weight: 700;">
                        <i class="fa-solid fa-filter"></i> {{ __('Filtrer les Trajets') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results List Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h2 style="font-size: 1.4rem; color: var(--text-heading);">
            {{ __('Voyages disponibles') }} ({{ $trips->total() }})
        </h2>
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            {{ __('Triés par heure de départ') }}
        </div>
    </div>

    @if($trips->isEmpty())
        <div class="card" style="text-align: center; padding: 50px 20px;">
            <div style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px;">
                <i class="fa-solid fa-bus-simple"></i>
            </div>
            <h3 style="font-size: 1.3rem; margin-bottom: 8px; color: var(--text-heading);">{{ __('Aucun voyage trouvé pour ces critères') }}</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">
                {{ __('Essayez de modifier votre ville de départ ou d\'élargir la date de recherche.') }}
            </p>
            <a href="{{ route('trips.index') }}" class="btn btn-outline">{{ __('Réinitialiser les filtres') }}</a>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 18px;">
            @foreach($trips as $trip)
                <div class="card card-hover" style="padding: 24px;">
                    <div class="grid grid-cols-4" style="align-items: center;">
                        <!-- Col 1: Agency & Mode -->
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span class="badge badge-road"><i class="fa-solid fa-bus"></i> {{ __('Autocar VIP') }}</span>
                                <span class="badge badge-outline" style="font-weight: 700;">
                                    <i class="fa-solid fa-couch"></i> {{ $trip->vehicle->name ?? __('Coach 75/80 places') }}
                                </span>
                            </div>
                            <div style="font-weight: 800; font-size: 1.1rem; color: var(--text-heading);">
                                Real Voyage ({{ $trip->branch->name ?? __('Gare Centrale') }})
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                N° {{ $trip->trip_number }} • Immat. {{ $trip->vehicle->license_plate ?? $trip->vehicle->code ?? 'BUS-RV' }}
                            </div>
                        </div>

                        <!-- Col 2: Itinerary & Schedule -->
                        <div style="grid-column: span 2;">
                            <div style="display: flex; align-items: center; justify-content: space-between; position: relative;">
                                <!-- Departure -->
                                <div style="text-align: left;">
                                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">
                                        {{ $trip->departure_time->format('H:i') }}
                                    </div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-heading);">{{ $trip->departure_city }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $trip->departure_station }}</div>
                                </div>

                                <!-- Route Line -->
                                <div style="flex: 1; margin: 0 16px; text-align: center;">
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">
                                        {{ $trip->departure_time->diff($trip->arrival_time_estimated)->format('%hh %Imin') }}
                                    </div>
                                    <div style="height: 2px; background: var(--border-color); position: relative;">
                                        <i class="fa-solid fa-bus" style="position: absolute; top: -7px; left: 45%; color: var(--primary); font-size: 14px; background: var(--bg-card); padding: 0 4px;"></i>
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--success); font-weight: 600; margin-top: 4px;">
                                        {{ $trip->departure_time->format('d M Y') }}
                                    </div>
                                </div>

                                <!-- Arrival -->
                                <div style="text-align: right;">
                                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);">
                                        {{ $trip->arrival_time_estimated->format('H:i') }}
                                    </div>
                                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-heading);">{{ $trip->arrival_city }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $trip->arrival_station }}</div>
                                </div>
                            </div>

                            <!-- Classes Badges Preview -->
                            <div style="display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap;">
                                @foreach($trip->classes as $cls)
                                    <span style="font-size: 0.75rem; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 3px 8px; border-radius: 4px; font-weight: 600; color: var(--text-main);">
                                        {{ __($cls->class_name) }}: <strong>{{ number_format($cls->price, 0, ',', ' ') }} FCFA</strong>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Col 3: Pricing & Action -->
                        <div style="text-align: right; border-left: 1px solid var(--border-color); padding-left: 20px;">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('À partir de') }}</div>
                            <div style="font-size: 1.4rem; font-weight: 900; color: var(--primary);">
                                {{ number_format($trip->base_price, 0, ',', ' ') }} <span style="font-size: 0.8rem; font-weight: 600;">XAF</span>
                            </div>
                            <div style="font-size: 0.75rem; color: {{ $trip->seats_available < 10 ? 'var(--secondary)' : 'var(--text-muted)' }}; margin-bottom: 12px;">
                                <i class="fa-solid fa-chair"></i> {{ $trip->seats_available }} {{ __('places disponibles') }}
                            </div>

                            @auth
                                <a href="{{ route('trips.show', $trip) }}" class="btn btn-primary" style="width: 100%; font-weight: 700;">
                                    <i class="fa-solid fa-chair"></i> {{ __('Choisir Sièges & Réserver') }}
                                </a>
                            @else
                                <a href="{{ route('trips.show', $trip) }}" class="btn btn-primary" style="width: 100%; font-weight: 700;" title="{{ __('Créez un compte voyageur ou connectez-vous pour choisir cet autocar') }}">
                                    <i class="fa-solid fa-user-plus"></i> {{ __('Créer un Compte & Choisir') }}
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 30px;">
            {{ $trips->links() }}
        </div>
    @endif

    <!-- Interactive Multimodal Corridor Route Map -->
    <div class="card card-glass" style="margin-top: 40px; padding: 24px; border-radius: var(--radius-xl);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="font-size: 1.15rem; color: var(--text-heading); margin-bottom: 2px;">
                    <i class="fa-solid fa-map-location-dot" style="color: var(--primary);"></i> {{ __('Réseau & Corridors Multimodaux du Cameroun') }}
                </h3>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                    {{ __('Gares ferroviaires Camrail, agences d\'autocars homologuées et axes interurbains en temps réel.') }}
                </p>
            </div>
            <div style="display: flex; gap: 12px; font-size: 0.8rem;">
                <span><i class="fa-solid fa-circle" style="color: var(--road-color);"></i> {{ __('Axe Routier (Bus)') }}</span>
                <span><i class="fa-solid fa-circle" style="color: var(--rail-color);"></i> {{ __('Axe Ferroviaire (Camrail)') }}</span>
            </div>
        </div>

        <div id="corridorMap" style="height: 360px; width: 100%; border-radius: var(--radius-lg); border: 1.5px solid var(--border-color); z-index: 1;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapElement = document.getElementById('corridorMap');
    if (mapElement && typeof L !== 'undefined') {
        // Center of Cameroon (around Yaoundé / Central region)
        const map = L.map('corridorMap').setView([5.3698, 12.3547], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Major hubs and stations
        const hubs = [
            { name: "Douala", coords: [4.0511, 9.7679], desc: "Hub Littoral • Gares Routières & Gare Bessengué (Camrail)", mode: "both" },
            { name: "Yaoundé", coords: [3.8480, 11.5021], desc: "Capitale • Gare Voyageurs & Gare de Yaoundé (Camrail)", mode: "both" },
            { name: "Ngaoundéré", coords: [7.3276, 13.5847], desc: "Terminus Transcam • Grand Nord Rail & Route", mode: "both" },
            { name: "Bafoussam", coords: [5.4778, 10.4176], desc: "Hub Région Ouest • Départs Toutes Compagnies", mode: "road" },
            { name: "Bamenda", coords: [5.9631, 10.1591], desc: "Hub Nord-Ouest • Lignes Directes", mode: "road" },
            { name: "Garoua", coords: [9.3014, 13.3977], desc: "Région du Nord • Liaisons Routières & Fret", mode: "road" },
            { name: "Maroua", coords: [10.5956, 14.3247], desc: "Extrême-Nord • Départs Quotidiens", mode: "road" },
            { name: "Bertoua", coords: [4.5773, 13.6846], desc: "Région de l'Est • Fret & Voyageurs", mode: "road" },
            { name: "Kribi", coords: [2.9376, 9.9077], desc: "Port Autonome & Balnéaire", mode: "road" }
        ];

        hubs.forEach(h => {
            const marker = L.circleMarker(h.coords, {
                radius: 8,
                fillColor: h.mode === 'both' ? '#0F2942' : '#334155',
                color: '#FFFFFF',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);

            marker.bindPopup(`<strong>${h.name}</strong><br><span style="font-size:12px">${h.desc}</span>`);
        });

        // Draw Transcam Railway line (Douala -> Yaoundé -> Ngaoundéré)
        const railLine = [
            [4.0511, 9.7679], // Douala
            [3.8000, 10.1333], // Édéa
            [3.8480, 11.5021], // Yaoundé
            [4.5800, 12.2500], // Nanga Eboko
            [5.8500, 13.3000], // Bélabo
            [7.3276, 13.5847]  // Ngaoundéré
        ];
        L.polyline(railLine, { color: '#334155', weight: 4, dashArray: '6, 8' }).addTo(map).bindPopup("Ligne Ferroviaire Transcam (Camrail)");

        // Draw N3 Highway (Douala -> Yaoundé)
        const roadN3 = [
            [4.0511, 9.7679], // Douala
            [3.8000, 10.1333], // Édéa
            [3.8480, 11.5021]  // Yaoundé
        ];
        L.polyline(roadN3, { color: '#0F2942', weight: 4 }).addTo(map).bindPopup("Axe Lourd National N3 (Douala ⇄ Yaoundé)");
    }
});
</script>
@endsection
