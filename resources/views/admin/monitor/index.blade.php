@extends('layouts.dashboard')

@section('title', __('Supervision de la Flotte & Départs - Real Voyage'))

@section('dashboard_content')
<div>
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
            {{ __('Supervision des Rotations d\'Autocars & Expéditions') }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            {{ __('Monitorez en temps réel l\'ensemble des départs d\'autocars Real Voyage et du fret interurbain (Douala, Yaoundé, Ouest).') }}
        </p>
    </div>

    <!-- Filters Card -->
    <div class="card" style="padding: 20px; margin-bottom: 24px;">
        <form action="{{ route('admin.monitor.index') }}" method="GET">
            <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <div style="width: 240px;">
                    <select name="status" class="form-control">
                        <option value="">{{ __('Tous les statuts de voyage') }}</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>{{ __('Programmé') }}</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>{{ __('En Route') }}</option>
                        <option value="delayed" {{ request('status') === 'delayed' ? 'selected' : '' }}>{{ __('Retardé') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Terminé') }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> {{ __('Actualiser Vue') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Trips Supervision Table Card -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 30px;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('N° Trajet') }}</th>
                        <th>{{ __('Gare / Branche') }}</th>
                        <th>{{ __('Autocar') }}</th>
                        <th>{{ __('Itinéraire') }}</th>
                        <th>{{ __('Départ') }}</th>
                        <th>{{ __('Immat.') }}</th>
                        <th>{{ __('Places Libres') }}</th>
                        <th>{{ __('Statut Trajet') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trips as $t)
                        <tr>
                            <td><strong>{{ $t->trip_number }}</strong></td>
                            <td>Real Voyage ({{ $t->branch->name ?? 'Gare Centrale' }})</td>
                            <td><span class="badge badge-road"><i class="fa-solid fa-bus"></i> {{ $t->vehicle->name ?? 'Coach 75/80 pl.' }}</span></td>
                            <td><strong>{{ $t->departure_city }} &rarr; {{ $t->arrival_city }}</strong></td>
                            <td>{{ $t->departure_time->format('d/m/Y H:i') }}</td>
                            <td>{{ $t->vehicle->code ?? '-' }}</td>
                            <td>{{ $t->seats_available }} / {{ $t->vehicle->capacity_seats ?? 50 }}</td>
                            <td>
                                @if($t->status === 'delayed')
                                    <span class="badge badge-danger">{{ __('Retard (:reason)', ['reason' => $t->delay_reason ?? __('Signalé')]) }}</span>
                                @elseif($t->status === 'in_transit')
                                    <span class="badge badge-road">{{ __('En Route') }}</span>
                                @elseif($t->status === 'completed')
                                    <span class="badge badge-success">{{ __('Terminé') }}</span>
                                @else
                                    <span class="badge badge-outline">{{ __('Programmé') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $trips->links() }}
        </div>
    </div>
</div>
@endsection
