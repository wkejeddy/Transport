@extends('layouts.dashboard')

@section('title', __('Direction de Gare / Terminal Régional'))

@section('dashboard_content')
<div style="max-width: 800px; margin: 0 auto;">
    <div class="card card-glass" style="padding: 32px; box-shadow: var(--shadow-lg);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin: 0;">
                <i class="fa-solid fa-building-flag" style="color: var(--primary);"></i> {{ __('Direction de Gare :') }} {{ $branch->name ?? 'Gare Centrale' }}
            </h1>
            <span class="badge badge-road" style="font-size: 0.85rem;">
                {{ $branch->region ?? 'Région' }} • {{ $branch->code ?? 'RV' }}
            </span>
        </div>

        <form action="{{ route('manager.branch.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="name">{{ __('Nom de la Gare / Terminal *') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $branch->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="city">{{ __('Ville d\'Implantation *') }}</label>
                    <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $branch->city ?? '') }}" required>
                </div>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="phone">{{ __('Téléphone Gare / Guichet *') }}</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $branch->phone ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">{{ __('E-mail Officiel') }}</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $branch->email ?? '') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">{{ __('Adresse Complète / Localisation Terminal') }}</label>
                <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $branch->address ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="manager_name">{{ __('Chef de Gare / Responsable d\'Exploitation') }}</label>
                <input type="text" id="manager_name" name="manager_name" class="form-control" value="{{ old('manager_name', $branch->manager_name ?? Auth::user()->name) }}">
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800; margin-top: 10px;">
                <i class="fa-solid fa-save"></i> {{ __('Enregistrer les Coordonnées de la Gare') }}
            </button>
        </form>
    </div>
</div>
@endsection
