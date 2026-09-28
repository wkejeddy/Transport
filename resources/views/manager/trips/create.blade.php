@extends('layouts.dashboard')

@section('title', __('Programmer un Nouveau Voyage - Espace Manager'))

@section('dashboard_content')
<div style="max-width: 920px; margin: 0 auto; box-sizing: border-box; width: 100%;">
    <!-- Navigation Back Link -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('manager.trips.index') }}" style="color: var(--text-muted); font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour aux voyages') }}
        </a>
    </div>

    <!-- Main Form Container Card -->
    <div class="card" style="padding: 28px; box-shadow: var(--shadow-lg); box-sizing: border-box; width: 100%; overflow: hidden;">
        <!-- Header -->
        <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
            <h1 style="font-size: 1.55rem; color: var(--text-heading); margin-bottom: 6px; font-weight: 850; letter-spacing: -0.02em;">
                {{ __('Programmer un Nouveau Voyage') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">
                {{ config('app.name', 'Real Express Voyages') }} &bull; {{ __('Direction Régionale') }} ({{ $branch->name ?? __('Gare Centrale') }})
            </p>
        </div>

        <form action="{{ route('manager.trips.store') }}" method="POST" id="createTripForm">
            @csrf

            <!-- SECTION 1: Identification & Affectation Véhicule -->
            <div class="form-section-card">
                <div class="form-section-title">
                    <i class="fa-solid fa-id-card"></i>
                    <span>{{ __('1. Identification & Flotte Assignée') }}</span>
                </div>

                <div class="grid grid-cols-2" style="gap: 16px;">
                    <!-- Trip Number -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="trip_number">{{ __('N° de Voyage *') }}</label>
                        <div class="input-affix-wrapper">
                            <input type="text" id="trip_number" name="trip_number" class="form-control" value="{{ old('trip_number', 'TR-RV-' . rand(100, 999)) }}" required style="font-family: monospace; font-weight: 700;">
                            <span class="input-affix-tag">{{ __('Code Réf') }}</span>
                        </div>
                    </div>

                    <!-- Vehicle Assignment -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="vehicle_id">{{ __('Véhicule Assigné *') }}</label>
                        <select id="vehicle_id" name="vehicle_id" class="form-control" required style="font-weight: 600;">
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">
                                    {{ $v->code }} &bull; {{ $v->name ?? $v->type }} ({{ $v->capacity_seats }} pl. / {{ $v->capacity_cargo }} kg)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Itinéraire & Gares (Départ / Arrivée) -->
            <div class="form-section-card">
                <div class="form-section-title">
                    <i class="fa-solid fa-route"></i>
                    <span>{{ __('2. Itinéraire & Gares Desservies') }}</span>
                </div>

                <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                    <!-- Departure City -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="departure_city">{{ __('Ville de Départ *') }}</label>
                        <select id="departure_city" name="departure_city" class="form-control" required style="font-weight: 600;">
                            @foreach($cities as $c)
                                <option value="{{ $c }}" {{ $c === 'Douala' ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Departure Station -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="departure_station">{{ __('Gare / Agence de Départ *') }}</label>
                        <input type="text" id="departure_station" name="departure_station" class="form-control" value="{{ old('departure_station', 'Gare de Bessengué / Agence Akwa') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-2" style="gap: 16px;">
                    <!-- Arrival City -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="arrival_city">{{ __('Ville d\'Arrivée *') }}</label>
                        <select id="arrival_city" name="arrival_city" class="form-control" required style="font-weight: 600;">
                            @foreach($cities as $c)
                                <option value="{{ $c }}" {{ $c === 'Yaoundé' ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Arrival Station -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="arrival_station">{{ __('Gare / Agence d\'Arrivée *') }}</label>
                        <input type="text" id="arrival_station" name="arrival_station" class="form-control" value="{{ old('arrival_station', 'Gare Voyageurs / Agence Mvan') }}" required>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Créneaux Officiels & Horaires Définis -->
            <div class="form-section-card" style="border-left: 4px solid var(--primary);">
                <div class="form-section-title">
                    <i class="fa-solid fa-clock"></i>
                    <span>{{ __('3. Créneaux Officiels de Départ (10h00 ou 21h00)') }}</span>
                </div>

                <div class="grid grid-cols-3" style="gap: 16px;">
                    <!-- 1. Departure Date -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="trip_date">{{ __('Date du Voyage *') }}</label>
                        <input type="date" id="trip_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required onchange="calculateSchedule()" style="font-weight: 600;">
                    </div>

                    <!-- 2. Official Departure Slot -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="departure_slot">{{ __('Créneau Fixe *') }}</label>
                        <select id="departure_slot" class="form-control" style="font-weight: 700;" required onchange="calculateSchedule()">
                            <option value="10:00" selected>☀️ 10h00 (Matin)</option>
                            <option value="21:00">🌙 21h00 (Soir / Nuit)</option>
                        </select>
                    </div>

                    <!-- 3. Estimated Duration in Hours -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="trip_duration">{{ __('Durée Estimée *') }}</label>
                        <select id="trip_duration" class="form-control" onchange="calculateSchedule()" style="font-weight: 600;">
                            <option value="3.5">3h 30 min (Kribi / Limbe)</option>
                            <option value="4.0" selected>4h 00 min (Axe Douala-Ydé)</option>
                            <option value="4.5">4h 30 min (Liaison Bafoussam)</option>
                            <option value="5.5">5h 30 min (Liaison Bertoua)</option>
                            <option value="6.0">6h 00 min (Grand Ouest)</option>
                        </select>
                    </div>
                </div>

                <!-- Hidden inputs submitted to server matching TripController validation -->
                <input type="hidden" id="departure_time" name="departure_time" value="{{ date('Y-m-d\T10:00', strtotime('+1 day')) }}">
                <input type="hidden" id="arrival_time_estimated" name="arrival_time_estimated" value="{{ date('Y-m-d\T14:00', strtotime('+1 day')) }}">

                <!-- Dedicated Schedule Confirmation Deck (Fixed Slot Geometry) -->
                <div class="schedule-preview-deck">
                    <!-- Left Slot: Departure -->
                    <div class="schedule-cell schedule-cell-left">
                        <span class="schedule-cell-tag">
                            <i class="fa-solid fa-bus-simple text-primary"></i> {{ __('Départ Confirmé') }}
                        </span>
                        <span class="schedule-cell-time" id="lblDeparture">--</span>
                    </div>

                    <!-- Center Slot: Arrow & Duration -->
                    <div class="schedule-cell schedule-cell-center">
                        <span class="schedule-duration-pill" id="lblDurationBadge">4h 00 min</span>
                        <i class="fa-solid fa-arrow-right" style="font-size: 0.95rem; color: var(--primary);"></i>
                    </div>

                    <!-- Right Slot: Arrival -->
                    <div class="schedule-cell schedule-cell-right">
                        <span class="schedule-cell-tag">
                            <i class="fa-solid fa-location-dot text-success"></i> {{ __('Arrivée Estimée') }}
                        </span>
                        <span class="schedule-cell-time" id="lblArrival">--</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Tarification Passagers & Fret au Kilogramme -->
            <div class="form-section-card">
                <div class="form-section-title">
                    <i class="fa-solid fa-coins"></i>
                    <span>{{ __('4. Grille Tarifaire Passagers & Fret') }}</span>
                </div>

                <div class="grid grid-cols-2" style="gap: 16px;">
                    <!-- Base Passenger Price -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="base_price">{{ __('Tarif Passager de Base *') }}</label>
                        <div class="input-affix-wrapper">
                            <input type="number" step="500" min="500" id="base_price" name="base_price" class="form-control" value="{{ old('base_price', '6000') }}" required style="font-weight: 700; font-size: 1rem;">
                            <span class="input-affix-tag">FCFA</span>
                        </div>
                    </div>

                    <!-- Cargo Price per Kg -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="cargo_price_per_kg">{{ __('Tarif Fret / Kilogramme *') }}</label>
                        <div class="input-affix-wrapper affix-wide">
                            <input type="number" step="50" min="50" id="cargo_price_per_kg" name="cargo_price_per_kg" class="form-control" value="{{ old('cargo_price_per_kg', '250') }}" required style="font-weight: 700; font-size: 1rem;">
                            <span class="input-affix-tag">FCFA / kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Configuration des Classes & Formules Autocar -->
            <div class="form-section-card">
                <div class="form-section-title">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>{{ __('5. Répartition des Sièges & Classes') }}</span>
                </div>

                <div class="class-config-deck">
                    <!-- Formula 1: VIP Prestige -->
                    <div class="class-config-card">
                        <div class="class-config-header">
                            <span class="class-config-name">
                                <i class="fa-solid fa-crown" style="color: #F59E0B;"></i>
                                {{ __('Formule VIP Prestige') }}
                            </span>
                            <span class="badge" style="background: rgba(245, 158, 11, 0.12); color: #D97706; font-size: 0.72rem; font-weight: 800;">
                                {{ __('Climatisé') }}
                            </span>
                        </div>

                        <input type="hidden" name="classes[0][class_code]" value="vip">
                        <input type="hidden" name="classes[0][class_name]" value="VIP Prestige (Climatisé)">

                        <div class="grid grid-cols-2" style="gap: 10px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label text-xs" style="font-size: 0.75rem;">{{ __('Sièges Disponibles') }}</label>
                                <div class="input-affix-wrapper">
                                    <input type="number" name="classes[0][seat_count]" class="form-control" value="30" min="1" required style="font-weight: 700;">
                                    <span class="input-affix-tag">pl.</span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label text-xs" style="font-size: 0.75rem;">{{ __('Prix du Siège') }}</label>
                                <div class="input-affix-wrapper">
                                    <input type="number" name="classes[0][price]" class="form-control" value="6000" min="500" step="500" required style="font-weight: 700;">
                                    <span class="input-affix-tag">FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formula 2: Classic Confort -->
                    <div class="class-config-card">
                        <div class="class-config-header">
                            <span class="class-config-name">
                                <i class="fa-solid fa-couch" style="color: #3B82F6;"></i>
                                {{ __('Formule Confort Classique') }}
                            </span>
                            <span class="badge" style="background: rgba(59, 130, 246, 0.12); color: #2563EB; font-size: 0.72rem; font-weight: 800;">
                                {{ __('Grand Confort') }}
                            </span>
                        </div>

                        <input type="hidden" name="classes[1][class_code]" value="classic">
                        <input type="hidden" name="classes[1][class_name]" value="Confort Classique">

                        <div class="grid grid-cols-2" style="gap: 10px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label text-xs" style="font-size: 0.75rem;">{{ __('Sièges Disponibles') }}</label>
                                <div class="input-affix-wrapper">
                                    <input type="number" name="classes[1][seat_count]" class="form-control" value="45" min="1" required style="font-weight: 700;">
                                    <span class="input-affix-tag">pl.</span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label text-xs" style="font-size: 0.75rem;">{{ __('Prix du Siège') }}</label>
                                <div class="input-affix-wrapper">
                                    <input type="number" name="classes[1][price]" class="form-control" value="4500" min="500" step="500" required style="font-weight: 700;">
                                    <span class="input-affix-tag">FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit CTA -->
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800; min-height: 50px; font-size: 1.05rem; display: flex; align-items: center; justify-content: center; gap: 10px; border-radius: var(--radius-md);">
                <i class="fa-solid fa-check"></i>
                <span>{{ __('Enregistrer et Publier le Voyage') }}</span>
            </button>
        </form>
    </div>
</div>

<script>
function calculateSchedule() {
    const dateVal = document.getElementById('trip_date').value;
    const slotVal = document.getElementById('departure_slot').value;
    const durationSelect = document.getElementById('trip_duration');
    const durationHours = parseFloat(durationSelect.value) || 4.0;
    const durationText = durationSelect.options[durationSelect.selectedIndex]?.text?.split('(')[0]?.trim() || `${durationHours}h`;

    if (!dateVal || !slotVal) return;

    // Parse year, month, day, hour, minute
    const [year, month, day] = dateVal.split('-').map(Number);
    const [hours, minutes] = slotVal.split(':').map(Number);

    const depDate = new Date(year, month - 1, day, hours, minutes, 0);
    const arrDate = new Date(depDate.getTime() + durationHours * 3600 * 1000);

    function formatIsoLocal(d) {
        const Y = d.getFullYear();
        const M = String(d.getMonth() + 1).padStart(2, '0');
        const D = String(d.getDate()).padStart(2, '0');
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        return `${Y}-${M}-${D}T${h}:${m}`;
    }

    function formatDisplay(d) {
        const D = String(d.getDate()).padStart(2, '0');
        const M = String(d.getMonth() + 1).padStart(2, '0');
        const Y = d.getFullYear();
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        return `${D}/${M}/${Y} à ${h}h${m}`;
    }

    document.getElementById('departure_time').value = formatIsoLocal(depDate);
    document.getElementById('arrival_time_estimated').value = formatIsoLocal(arrDate);

    const lblDep = document.getElementById('lblDeparture');
    const lblArr = document.getElementById('lblArrival');
    const lblDur = document.getElementById('lblDurationBadge');

    if (lblDep) lblDep.textContent = formatDisplay(depDate);
    if (lblArr) lblArr.textContent = formatDisplay(arrDate);
    if (lblDur) lblDur.textContent = durationText;
}

document.addEventListener('DOMContentLoaded', calculateSchedule);
</script>
@endsection
