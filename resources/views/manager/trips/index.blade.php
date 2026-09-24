@extends('layouts.dashboard')

@section('title', __('Gestion des Voyages & Horaires - Espace Manager'))

@section('dashboard_content')
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
                {{ __('Voyages & Départs Programmés') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                {{ __('Publiez vos horaires, configurez les classes tarifaires et suivez les départs.') }}
            </p>
        </div>

        <a href="{{ route('manager.trips.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus-circle"></i> {{ __('Programmer un Voyage') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('N° Voyage') }}</th>
                        <th>{{ __('Trajet & Gares') }}</th>
                        <th>{{ __('Départ') }}</th>
                        <th>{{ __('Véhicule') }}</th>
                        <th>{{ __('Places Libres') }}</th>
                        <th>{{ __('Tarif Base') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trips as $trip)
                        <tr>
                            <td>
                                <strong style="color: var(--primary);">{{ $trip->trip_number }}</strong>
                            </td>
                            <td>
                                <strong>{{ $trip->departure_city }} &rarr; {{ $trip->arrival_city }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $trip->departure_station }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700;">{{ $trip->departure_time->format('d/m/Y') }}</div>
                                <div style="font-size: 0.8rem; color: var(--primary);">{{ $trip->departure_time->format('H:i') }}</div>
                            </td>
                            <td>
                                {{ $trip->vehicle->code ?? 'N/A' }} ({{ $trip->vehicle->type ?? __('Standard') }})
                            </td>
                            <td>
                                <strong style="color: {{ $trip->seats_available < 10 ? 'var(--secondary)' : 'var(--primary)' }};">
                                    {{ $trip->seats_available }}
                                </strong> / {{ $trip->vehicle->capacity_seats ?? 50 }}
                            </td>
                            <td>
                                <strong>{{ number_format($trip->base_price, 0, ',', ' ') }} F</strong>
                            </td>
                            <td>
                                @if($trip->status === 'completed')
                                    <span class="badge badge-success">{{ __('Terminé') }}</span>
                                @elseif($trip->status === 'delayed')
                                    <span class="badge badge-danger">{{ __('Retardé') }}</span>
                                @elseif($trip->status === 'in_transit')
                                    <span class="badge badge-road">{{ __('En Route') }}</span>
                                @elseif($trip->status === 'cancelled')
                                    <span class="badge badge-danger">{{ __('Annulé') }}</span>
                                @else
                                    <span class="badge badge-outline">{{ __('Programmé') }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('manager.trips.manifest', $trip) }}" class="btn btn-sm btn-outline">
                                    <i class="fa-solid fa-list-check"></i> {{ __('Manifeste') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                {{ __('Aucun voyage programmé pour le moment.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $trips->links() }}
        </div>
    </div>
</div>
@endsection
