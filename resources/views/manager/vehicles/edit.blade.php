@extends('layouts.dashboard')

@section('title', __('Modifier le Véhicule :code', ['code' => $vehicle->code]))

@section('dashboard_content')
<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('manager.vehicles.index') }}" style="color: var(--text-muted); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour à la flotte') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 32px; box-shadow: var(--shadow-lg);">
        <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            {{ __('Modifier le Véhicule') }} {{ $vehicle->code }}
        </h1>

        <form action="{{ route('manager.vehicles.update', $vehicle) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="code">{{ __('Code / Immatriculation *') }}</label>
                    <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $vehicle->code) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">{{ __('Nom Commercial') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $vehicle->name) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="type">{{ __('Configuration & Modèle de Bus') }}</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="75_seater_bus" {{ $vehicle->type == '75_seater_bus' ? 'selected' : '' }}>{{ __('Autocar Interurbain Grand Confort (75 places)') }}</option>
                    <option value="80_seater_bus" {{ $vehicle->type == '80_seater_bus' ? 'selected' : '' }}>{{ __('Autocar Long Courrier Premium (80 places)') }}</option>
                    <option value="vip_coach" {{ $vehicle->type == 'vip_coach' ? 'selected' : '' }}>{{ __('Autocar VIP Exécutif (70 places)') }}</option>
                </select>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="capacity_seats">{{ __('Nombre de Sièges *') }}</label>
                    <input type="number" id="capacity_seats" name="capacity_seats" class="form-control" value="{{ old('capacity_seats', $vehicle->capacity_seats) }}" required min="10" max="100">
                </div>

                <div class="form-group">
                    <label class="form-label" for="capacity_cargo">{{ __('Capacité Fret (Kg) *') }}</label>
                    <input type="number" id="capacity_cargo" name="capacity_cargo" class="form-control" value="{{ old('capacity_cargo', $vehicle->capacity_cargo) }}" required min="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="status">{{ __('Statut Opérationnel') }}</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="active" {{ $vehicle->status == 'active' ? 'selected' : '' }}>{{ __('En Service Actif') }}</option>
                    <option value="maintenance" {{ $vehicle->status == 'maintenance' ? 'selected' : '' }}>{{ __('En Maintenance / Révision') }}</option>
                    <option value="inactive" {{ $vehicle->status == 'inactive' ? 'selected' : '' }}>{{ __('Hors Service') }}</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800;">
                <i class="fa-solid fa-save"></i> {{ __('Mettre à Jour le Véhicule') }}
            </button>
        </form>
    </div>
</div>
@endsection
