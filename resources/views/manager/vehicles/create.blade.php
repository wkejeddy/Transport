@extends('layouts.dashboard')

@section('title', __('Ajouter un Véhicule à la Flotte'))

@section('dashboard_content')
<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('manager.vehicles.index') }}" style="color: var(--text-muted); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour à la flotte') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 32px; box-shadow: var(--shadow-lg);">
        <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            {{ __('Ajouter un Véhicule / Matériel Roulant') }}
        </h1>

        <form action="{{ route('manager.vehicles.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="code">{{ __('Code / Immatriculation *') }}</label>
                    <input type="text" id="code" name="code" class="form-control" value="{{ old('code', 'BUS-RV-' . rand(100, 999)) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">{{ __('Nom Commercial (Optionnel)') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ __('Ex: VIP Océan Express, Prestige...') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="type">{{ __('Configuration & Modèle de Bus') }}</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="75_seater_bus">{{ __('Autocar Interurbain Grand Confort (75 places)') }}</option>
                    <option value="80_seater_bus">{{ __('Autocar Long Courrier Premium (80 places)') }}</option>
                    <option value="vip_coach">{{ __('Autocar VIP Exécutif (70 places)') }}</option>
                </select>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="capacity_seats">{{ __('Nombre de Sièges / Passagers *') }}</label>
                    <input type="number" id="capacity_seats" name="capacity_seats" class="form-control" value="{{ old('capacity_seats', 75) }}" required min="10" max="100">
                </div>

                <div class="form-group">
                    <label class="form-label" for="capacity_cargo">{{ __('Capacité de Fret en Soute (en Kg) *') }}</label>
                    <input type="number" id="capacity_cargo" name="capacity_cargo" class="form-control" value="{{ old('capacity_cargo', 3500) }}" required min="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="status">{{ __('Statut Opérationnel') }}</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="active">{{ __('En Service Actif') }}</option>
                    <option value="maintenance">{{ __('En Maintenance / Révision') }}</option>
                    <option value="inactive">{{ __('Hors Service') }}</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800;">
                <i class="fa-solid fa-plus-circle"></i> {{ __('Ajouter à la Flotte') }}
            </button>
        </form>
    </div>
</div>
@endsection
