@extends('layouts.dashboard')

@section('title', __('Gestion de Flotte & Matériel Roulant'))

@section('dashboard_content')
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
                {{ __('Parc d\'Autocars Real Voyage (75 / 80 places)') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                {{ __('Gérez les autocars affectés au terminal, leurs capacités assises et volumes de fret.') }}
            </p>
        </div>

        <a href="{{ route('manager.vehicles.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus-circle"></i> {{ __('Ajouter un Véhicule') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Code Immat.') }}</th>
                        <th>{{ __('Nom Commercial') }}</th>
                        <th>{{ __('Type de Véhicule') }}</th>
                        <th>{{ __('Capacité Sièges') }}</th>
                        <th>{{ __('Capacité Fret (Kg)') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $v)
                        <tr>
                            <td><strong style="color: var(--primary);">{{ $v->code }}</strong></td>
                            <td>{{ $v->name ?? '-' }}</td>
                            <td>{{ __($v->type_label) }}</td>
                            <td><strong>{{ $v->capacity_seats }}</strong> {{ __('places') }}</td>
                            <td>{{ number_format($v->capacity_cargo, 0, ',', ' ') }} kg</td>
                            <td>
                                @if($v->status === 'active')
                                    <span class="badge badge-success">{{ __('En Service') }}</span>
                                @elseif($v->status === 'maintenance')
                                    <span class="badge badge-warning">{{ __('En Maintenance') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('Inactif') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('manager.vehicles.edit', $v) }}" class="btn btn-sm btn-outline">
                                        <i class="fa-solid fa-pen"></i> {{ __('Modifier') }}
                                    </a>
                                    <form action="{{ route('manager.vehicles.destroy', $v) }}" method="POST" onsubmit="return confirm('{{ __('Confirmer la suppression ?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline" style="color: var(--danger); border-color: var(--danger-border);">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">
                                {{ __('Aucun véhicule enregistré dans votre flotte.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
