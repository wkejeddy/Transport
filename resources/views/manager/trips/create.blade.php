@extends('layouts.dashboard')

@section('title', __('Programmer un Nouveau Voyage - Espace Manager'))

@section('dashboard_content')
<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('manager.trips.index') }}" style="color: var(--text-muted); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour aux voyages') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 32px; box-shadow: var(--shadow-lg);">
        <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
                {{ __('Programmer un Nouveau Voyage') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Real Voyage - Direction Régionale ({{ $branch->name ?? __('Gare Centrale') }})
            </p>
        </div>

        <form action="{{ route('manager.trips.store') }}" method="POST">
            @csrf

            <!-- Trip Number & Vehicle Assignment -->
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="trip_number">{{ __('N° de Voyage *') }}</label>
                    <input type="text" id="trip_number" name="trip_number" class="form-control" value="{{ old('trip_number', 'TR-RV-' . rand(100, 999)) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="vehicle_id">{{ __('Véhicule Assigné *') }}</label>
                    <select id="vehicle_id" name="vehicle_id" class="form-control" required>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">
                                {{ $v->code }} - {{ $v->name ?? $v->type }} ({{ $v->capacity_seats }} {{ __('places') }}, {{ $v->capacity_cargo }} kg {{ __('fret') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Departure & Arrival Itinerary -->
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="departure_city">{{ __('Ville de Départ *') }}</label>
                    <select id="departure_city" name="departure_city" class="form-control" required>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ $c === 'Douala' ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="departure_station">{{ __('Gare / Agence de Départ *') }}</label>
                    <input type="text" id="departure_station" name="departure_station" class="form-control" value="{{ old('departure_station', 'Gare de Bessengué / Agence Akwa') }}" required>
                </div>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="arrival_city">{{ __('Ville d\'Arrivée *') }}</label>
                    <select id="arrival_city" name="arrival_city" class="form-control" required>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ $c === 'Yaoundé' ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="arrival_station">{{ __('Gare / Agence d\'Arrivée *') }}</label>
                    <input type="text" id="arrival_station" name="arrival_station" class="form-control" value="{{ old('arrival_station', 'Gare Voyageurs / Agence Mvan') }}" required>
                </div>
            </div>

            <!-- Schedule -->
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="departure_time">{{ __('Date & Heure de Départ *') }}</label>
                    <input type="datetime-local" id="departure_time" name="departure_time" class="form-control" required value="{{ date('Y-m-d\TH:i', strtotime('+1 day 08:00')) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="arrival_time_estimated">{{ __('Date & Heure Estimée d\'Arrivée *') }}</label>
                    <input type="datetime-local" id="arrival_time_estimated" name="arrival_time_estimated" class="form-control" required value="{{ date('Y-m-d\TH:i', strtotime('+1 day 12:00')) }}">
                </div>
            </div>

            <!-- Pricing & Cargo Rates -->
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="base_price">{{ __('Tarif Passager de Base (FCFA) *') }}</label>
                    <input type="number" step="500" min="500" id="base_price" name="base_price" class="form-control" value="{{ old('base_price', '6000') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cargo_price_per_kg">{{ __('Tarif Fret / Kg (FCFA) *') }}</label>
                    <input type="number" step="50" min="50" id="cargo_price_per_kg" name="cargo_price_per_kg" class="form-control" value="{{ old('cargo_price_per_kg', '250') }}" required>
                </div>
            </div>

            <!-- Multi-Class Configuration Section -->
            <h4 style="font-size: 1rem; color: var(--primary); margin-top: 24px; margin-bottom: 14px; border-bottom: 2px solid var(--primary-50); padding-bottom: 8px;">
                <i class="fa-solid fa-layer-group"></i> {{ __('Configuration des Classes & Formules Autocar (VIP & Classique)') }}
            </h4>

            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
                <div style="font-weight: 700; font-size: 0.9rem; color: var(--road-color); margin-bottom: 12px;">
                    {{ __('Classes Disponibles dans l\'Autocar :') }}
                </div>
                <div class="grid grid-cols-2" style="gap: 12px;">
                    <div>
                        <label class="form-label" style="font-size: 0.8rem;">{{ __('Formule VIP (Sièges Confort + Climatisation)') }}</label>
                        <input type="hidden" name="classes[0][class_code]" value="vip">
                        <input type="hidden" name="classes[0][class_name]" value="VIP Prestige (Climatisé)">
                        <input type="number" name="classes[0][seat_count]" class="form-control" placeholder="{{ __('Places') }}" value="30" style="margin-bottom: 6px;">
                        <input type="number" name="classes[0][price]" class="form-control" placeholder="{{ __('Prix FCFA') }}" value="6000">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem;">{{ __('Formule Classique (Grand Confort)') }}</label>
                        <input type="hidden" name="classes[1][class_code]" value="classic">
                        <input type="hidden" name="classes[1][class_name]" value="Confort Classique">
                        <input type="number" name="classes[1][seat_count]" class="form-control" placeholder="{{ __('Places') }}" value="45" style="margin-bottom: 6px;">
                        <input type="number" name="classes[1][price]" class="form-control" placeholder="{{ __('Prix FCFA') }}" value="4500">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800;">
                <i class="fa-solid fa-check"></i> {{ __('Enregistrer et Publier le Voyage') }}
            </button>
        </form>
    </div>
</div>
@endsection
