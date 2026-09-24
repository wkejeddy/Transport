@extends('layouts.dashboard')

@section('title', __('Manifeste Voyage :number', ['number' => $trip->trip_number]))

@section('dashboard_content')
<div>
    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <a href="{{ route('manager.trips.index') }}" style="color: var(--text-muted); font-weight: 600; font-size: 0.85rem;">
                <i class="fa-solid fa-arrow-left"></i> {{ __('Retour aux voyages') }}
            </a>
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-top: 4px;">
                {{ __('Manifeste de Bord :') }} {{ $trip->trip_number }}
            </h1>
            <div style="font-size: 0.9rem; color: var(--text-muted);">
                {{ $trip->departure_city }} &rarr; {{ $trip->arrival_city }} • {{ __('Départ :') }} <strong>{{ $trip->departure_time->format('d/m/Y') }} {{ __('à') }} {{ $trip->departure_time->format('H:i') }}</strong>
            </div>
        </div>

        <button onclick="window.print()" class="btn btn-outline no-print">
            <i class="fa-solid fa-print"></i> {{ __('Imprimer Manifeste') }}
        </button>
    </div>

    <!-- Status & Delay Update Box -->
    <div class="card card-glass no-print" style="margin-bottom: 24px; padding: 20px; background: var(--bg-surface);">
        <h4 style="font-size: 0.95rem; color: var(--text-heading); margin-bottom: 12px;">
            <i class="fa-solid fa-tower-broadcast" style="color: var(--primary);"></i> {{ __('Mettre à Jour le Statut du Voyage (Alerte Passagers)') }}
        </h4>

        <form action="{{ route('manager.trips.status', $trip) }}" method="POST">
            @csrf
            <div class="grid grid-cols-4" style="gap: 12px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem;">{{ __('Statut Actuel') }}</label>
                    <select name="status" class="form-control" id="statusSelect" onchange="toggleDelayInputs()">
                        <option value="scheduled" {{ $trip->status === 'scheduled' ? 'selected' : '' }}>{{ __('Programmé') }}</option>
                        <option value="boarding" {{ $trip->status === 'boarding' ? 'selected' : '' }}>{{ __('Embarquement en cours') }}</option>
                        <option value="in_transit" {{ $trip->status === 'in_transit' ? 'selected' : '' }}>{{ __('En Route (Départ effectué)') }}</option>
                        <option value="completed" {{ $trip->status === 'completed' ? 'selected' : '' }}>{{ __('Arrivé à destination (Terminé)') }}</option>
                        <option value="delayed" {{ $trip->status === 'delayed' ? 'selected' : '' }}>{{ __('Retardé (Émettre Alerte Retard)') }}</option>
                        <option value="cancelled" {{ $trip->status === 'cancelled' ? 'selected' : '' }}>{{ __('Annulé (Alerte Annulation)') }}</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0; grid-column: span 2;" id="delayReasonGroup">
                    <label class="form-label" style="font-size: 0.8rem;">{{ __('Motif du Retard / Information Passagers') }}</label>
                    <input type="text" name="delay_reason" value="{{ $trip->delay_reason }}" class="form-control" placeholder="{{ __('Ex: Inspection technique ou ralentissement voie') }}">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 46px;">
                        {{ __('Actualiser Statut') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Passengers Manifest Table Card -->
    <div class="card" style="padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.2rem; color: var(--bg-dark); margin: 0;">
                <i class="fa-solid fa-users" style="color: var(--primary);"></i> {{ __('Liste des Passagers Enregistrés (:count passagers)', ['count' => $trip->bookings->where('status', '!=', 'cancelled')->sum('seats_count')]) }}
            </h3>
            <a href="{{ route('manager.checkin.index') }}" class="btn btn-sm btn-outline no-print">
                <i class="fa-solid fa-qrcode"></i> {{ __('Guichet d\'Embarquement') }}
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('N° Réf') }}</th>
                        <th>{{ __('Passager Titulaire') }}</th>
                        <th>{{ __('Téléphone') }}</th>
                        <th>{{ __('Sièges') }}</th>
                        <th>{{ __('Classe') }}</th>
                        <th>{{ __('Montant') }}</th>
                        <th>{{ __('Statut d\'Embarquement') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trip->bookings->where('status', '!=', 'cancelled') as $b)
                        <tr>
                            <td><strong>{{ $b->booking_reference }}</strong></td>
                            <td>
                                <div style="font-weight: 700;">{{ $b->passenger->name ?? __('Passager') }}</div>
                            </td>
                            <td>{{ $b->passenger->phone ?? '-' }}</td>
                            <td>
                                <span class="badge badge-road">{{ implode(', ', $b->seat_numbers ?? []) }}</span>
                            </td>
                            <td>{{ __($b->tripClass->class_name ?? ucfirst($b->transport_class)) }}</td>
                            <td>{{ number_format($b->total_amount, 0, ',', ' ') }} F</td>
                            <td>
                                @if($b->isCheckedIn())
                                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> {{ __('Embarqué') }} ({{ $b->checked_in_at ? $b->checked_in_at->format('H:i') : '' }})</span>
                                @else
                                    <span class="badge badge-warning">{{ __('En attente guichet') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                {{ __('Aucun passager enregistré pour le moment.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cargo Manifest -->
    <div class="card" style="padding: 24px;">
        <h3 style="font-size: 1.2rem; color: var(--bg-dark); margin-bottom: 16px;">
            <i class="fa-solid fa-boxes-packing" style="color: var(--accent);"></i> {{ __('Manifeste Fret & Bagages Chargés (:count colis)', ['count' => $trip->shipments->count()]) }}
        </h3>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('N° Suivi') }}</th>
                        <th>{{ __('Expéditeur') }}</th>
                        <th>{{ __('Destinataire') }}</th>
                        <th>{{ __('Poids') }}</th>
                        <th>{{ __('Catégorie') }}</th>
                        <th>{{ __('Statut') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trip->shipments as $sh)
                        <tr>
                            <td><strong>{{ $sh->tracking_code }}</strong></td>
                            <td>{{ $sh->sender->name ?? '-' }}</td>
                            <td>{{ $sh->recipient_name }} ({{ $sh->recipient_phone }})</td>
                            <td>{{ $sh->weight_kg }} kg</td>
                            <td>{{ ucfirst($sh->item_category) }}</td>
                            <td><span class="badge badge-outline">{{ strtoupper($sh->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                {{ __('Aucun colis de fret assigné à ce départ.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleDelayInputs() {
    // Dynamic styling check
}
</script>
@endsection
