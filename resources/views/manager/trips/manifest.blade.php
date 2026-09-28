@extends('layouts.app')

@section('title', __('Manifeste Officiel de Bord - :num', ['num' => $trip->trip_number]))

@section('content')
<style>
@media print {
    .no-print, nav, footer, .theme-toggle, .btn {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
        font-size: 10pt !important;
    }
    .manifest-card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        padding: 10px !important;
    }
    .data-table th, .data-table td {
        border: 1px solid #333 !important;
        padding: 4px 6px !important;
        font-size: 9pt !important;
    }
    .badge {
        border: 1px solid #000 !important;
        background: transparent !important;
        color: #000 !important;
    }
    .signature-box {
        border: 1px solid #000 !important;
    }
    .stats-summary {
        border: 1px solid #000 !important;
        background: transparent !important;
    }
}
</style>

@php
    $totalSeats = $trip->vehicle->capacity_seats ?? 75;
    $activeBookings = $trip->bookings->where('status', '!=', 'cancelled');
    $occupiedSeats = $activeBookings->whereIn('status', ['confirmed', 'checked_in'])->sum('seats_count');
    $reservedSeats = $activeBookings->where('status', 'reserved')->sum('seats_count');
    $checkedInSeats = $activeBookings->where('status', 'checked_in')->sum('seats_count');
    $availableSeats = $trip->seats_available;

    // Flatten and sort all passenger seats by seat number
    $passengersManifest = collect();
    foreach ($activeBookings as $b) {
        $pList = !empty($b->passengers_data)
            ? $b->passengers_data
            : [['name' => $b->passenger->name ?? 'Passager', 'cni' => 'N/A', 'phone' => $b->passenger->phone ?? 'N/A']];

        foreach ($pList as $idx => $p) {
            $seat = $b->seat_numbers[$idx] ?? ($b->seat_numbers[0] ?? 'S-??');
            $numericSeat = (int)filter_var($seat, FILTER_SANITIZE_NUMBER_INT) ?: 999;
            $baggageCount = $p['baggage_count'] ?? ($b->baggage_count ?? 1);

            $passengersManifest->push([
                'seat_label' => $seat,
                'numeric_seat' => $numericSeat,
                'booking_reference' => $b->booking_reference,
                'passenger_name' => $p['name'] ?? ($b->passenger->name ?? 'Passager'),
                'cni' => $p['cni'] ?? '-',
                'phone' => $p['phone'] ?? ($b->passenger->phone ?? '-'),
                'origin' => $trip->departure_city,
                'destination' => $trip->arrival_city,
                'class' => $b->tripClass->class_name ?? ucfirst($b->transport_class),
                'status' => $b->status,
                'is_checked_in' => $b->isCheckedIn(),
                'checked_in_at' => $b->checked_in_at,
                'baggage_count' => $baggageCount,
            ]);
        }
    }
    $sortedPassengers = $passengersManifest->sortBy('numeric_seat')->values();
@endphp

<div class="container" style="padding-top: 30px; padding-bottom: 60px;">
    <!-- Top Action Bar -->
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('manager.trips.index') }}" style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour aux voyages') }}
        </a>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa-solid fa-print"></i> {{ __('Imprimer Manifeste Officiel') }}
            </button>
            <a href="{{ route('manager.checkin.index') }}" class="btn btn-outline">
                <i class="fa-solid fa-qrcode"></i> {{ __('Guichet d\'Embarquement') }}
            </a>
        </div>
    </div>

    <!-- Official Manifest Container -->
    <div class="card manifest-card" style="padding: 30px; margin-bottom: 24px; border: 2px solid var(--border-color); background: var(--bg-card);">
        <!-- Manifest Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--border-color); padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 14px;">
                    <img src="{{ asset('images/logo.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="Real Express Voyages" style="width: 50px; height: 50px; object-fit: contain; flex-shrink: 0;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.4rem; font-weight: 900; letter-spacing: -0.02em;">REAL EXPRESS VOYAGES</h2>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ __('Feuille de Route & Manifeste d\'Embarquement Passagers & Fret') }}
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 1.1rem; font-weight: 900; color: var(--primary);">
                    {{ __('VOYAGE N°') }} {{ $trip->trip_number }}
                </div>
                <div style="font-size: 0.85rem; font-weight: 700;">
                    {{ __('Gare Émettrice :') }} {{ $branch->name ?? $trip->departure_station }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                    {{ __('Date d\'Édition :') }} {{ now()->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>

        <!-- Summary Counters (Total seats, Occupied, Reserved, Available, Checked-in) -->
        <div class="stats-summary" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; background: var(--bg-surface); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); text-align: center;">
            <div style="border-right: 1px solid var(--border-color);">
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Total Sièges') }}</div>
                <div style="font-size: 1.4rem; font-weight: 900; color: var(--text-heading);">{{ $totalSeats }}</div>
            </div>
            <div style="border-right: 1px solid var(--border-color);">
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Occupés (Payés)') }}</div>
                <div style="font-size: 1.4rem; font-weight: 900; color: var(--primary);">{{ $occupiedSeats }}</div>
            </div>
            <div style="border-right: 1px solid var(--border-color);">
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Pré-Réservés') }}</div>
                <div style="font-size: 1.4rem; font-weight: 900; color: #D97706;">{{ $reservedSeats }}</div>
            </div>
            <div style="border-right: 1px solid var(--border-color);">
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Disponibles') }}</div>
                <div style="font-size: 1.4rem; font-weight: 900; color: #059669;">{{ $availableSeats }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Embarqués (Validés)') }}</div>
                <div style="font-size: 1.4rem; font-weight: 900; color: #2563EB;">{{ $checkedInSeats }}</div>
            </div>
        </div>

        <!-- Trip Metadata Grid -->
        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 20px; background: var(--bg-surface); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Itinéraire') }}</div>
                <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                    {{ $trip->departure_city }} ➔ {{ $trip->arrival_city }}
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $trip->departure_station }}</div>
            </div>

            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Horaire de Départ') }}</div>
                <div style="font-weight: 800; font-size: 1rem; color: var(--primary);">
                    {{ $trip->departure_time->format('d/m/Y - H:i') }}
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    {{ __('Arrivée estimée :') }} {{ $trip->arrival_time_estimated->format('H:i') }}
                </div>
            </div>

            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Matériel Roulant') }}</div>
                <div style="font-weight: 800; font-size: 1rem; color: var(--text-heading);">
                    {{ $trip->vehicle->model ?? 'Autocar Grand Tourisme' }}
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    {{ __('Immatriculation :') }} <strong>{{ $trip->vehicle->registration_plate ?? $trip->vehicle->code ?? 'LT-980-AA' }}</strong>
                </div>
            </div>

            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ __('Équipage de Bord') }}</div>
                <div style="font-size: 0.85rem;">
                    <strong>{{ __('Siège 01 (Chauffeur) :') }}</strong> {{ $trip->vehicle->driver_name ?? 'Jean-Paul Nkoa' }}
                </div>
                <div style="font-size: 0.85rem;">
                    <strong>{{ __('Siège 16 (Convoyeur) :') }}</strong> {{ $trip->vehicle->convoyeur_name ?? 'Serge Atangana' }}
                </div>
            </div>
        </div>

        <!-- Delay / Status Update Bar (Only on Screen) -->
        <div class="card no-print" style="margin-bottom: 24px; padding: 16px; background: var(--bg-surface); border: 1px dashed var(--border-color);">
            <form action="{{ route('manager.trips.status', $trip) }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                @csrf
                <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-heading);">
                    <i class="fa-solid fa-tower-broadcast" style="color: var(--primary);"></i> {{ __('Statut en Temps Réel :') }}
                </div>
                <select name="status" class="form-control" style="width: auto; height: 38px;">
                    <option value="scheduled" {{ $trip->status === 'scheduled' ? 'selected' : '' }}>{{ __('Programmé') }}</option>
                    <option value="boarding" {{ $trip->status === 'boarding' ? 'selected' : '' }}>{{ __('Embarquement en cours') }}</option>
                    <option value="in_transit" {{ $trip->status === 'in_transit' ? 'selected' : '' }}>{{ __('En Route (Départ effectué)') }}</option>
                    <option value="completed" {{ $trip->status === 'completed' ? 'selected' : '' }}>{{ __('Arrivé à destination') }}</option>
                    <option value="delayed" {{ $trip->status === 'delayed' ? 'selected' : '' }}>{{ __('Retardé') }}</option>
                    <option value="cancelled" {{ $trip->status === 'cancelled' ? 'selected' : '' }}>{{ __('Annulé') }}</option>
                </select>
                <input type="text" name="delay_reason" value="{{ $trip->delay_reason }}" class="form-control" style="flex: 1; min-width: 200px; height: 38px;" placeholder="{{ __('Motif du retard (diffusé par SMS/Alerte aux passagers)') }}">
                <button type="submit" class="btn btn-sm btn-primary" style="height: 38px;">
                    {{ __('Actualiser Statut') }}
                </button>
            </form>
        </div>

        <!-- Passenger Manifest Table Ordered by Seat Number -->
        <div style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 1.1rem; margin: 0; font-weight: 800;">
                    <i class="fa-solid fa-users" style="color: var(--primary);"></i> 
                    {{ __('Manifeste des Passagers par Ordre de Siège (:count passagers)', ['count' => $sortedPassengers->count()]) }}
                </h3>
                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">
                    {{ __('Capacité Totale :') }} {{ $totalSeats }} {{ __('places') }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-surface); text-align: left;">
                            <th style="padding: 8px 10px;">{{ __('Siège') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Réf. Billet') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Nom & Prénom(s) du Passager') }}</th>
                            <th style="padding: 8px 10px;">{{ __('N° CNI / ID') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Téléphone') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Destination') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Bagages') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Statut Embarquement') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sortedPassengers as $p)
                            <tr>
                                <td style="font-weight: 900; color: var(--primary); text-align: center;">
                                    <span class="badge badge-road">{{ $p['seat_label'] }}</span>
                                </td>
                                <td><strong>{{ $p['booking_reference'] }}</strong></td>
                                <td><strong>{{ $p['passenger_name'] }}</strong></td>
                                <td>{{ $p['cni'] }}</td>
                                <td>{{ $p['phone'] }}</td>
                                <td>{{ $p['destination'] }}</td>
                                <td>{{ $p['baggage_count'] }} colis</td>
                                <td>
                                    @if($p['is_checked_in'])
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> {{ __('Embarqué') }} ({{ $p['checked_in_at'] ? $p['checked_in_at']->format('H:i') : '' }})</span>
                                    @elseif($p['status'] === 'reserved')
                                        <span class="badge badge-warning">{{ __('Pré-Réservé (En attente solde)') }}</span>
                                    @else
                                        <span class="badge badge-info">{{ __('Confirmé (En attente guichet)') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                    {{ __('Aucun passager enregistré pour ce départ.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Freight & Cargo Manifest Section -->
        @if($trip->shipments && $trip->shipments->count() > 0)
            <div style="margin-bottom: 24px;">
                <h3 style="font-size: 1.1rem; margin-bottom: 12px; font-weight: 800;">
                    <i class="fa-solid fa-boxes-packing" style="color: var(--accent);"></i>
                    {{ __('Bordereau de Fret & Colis (:count expéditions)', ['count' => $trip->shipments->count()]) }}
                </h3>
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-surface); text-align: left;">
                            <th style="padding: 8px 10px;">{{ __('Code Suivi') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Expéditeur') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Destinataire & Contact') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Poids (kg)') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Description Colis') }}</th>
                            <th style="padding: 8px 10px;">{{ __('Statut') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trip->shipments as $sh)
                            <tr>
                                <td><strong>{{ $sh->tracking_code }}</strong></td>
                                <td>{{ $sh->sender->name ?? '-' }}</td>
                                <td>{{ $sh->recipient_name }} ({{ $sh->recipient_phone }})</td>
                                <td>{{ $sh->weight_kg }} kg</td>
                                <td>{{ ucfirst($sh->item_category) }} - {{ $sh->description }}</td>
                                <td><span class="badge badge-outline">{{ strtoupper($sh->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Official Sign-off & Security Stamp Block -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid var(--border-color);">
            <div class="grid grid-cols-3" style="gap: 20px;">
                <div class="signature-box" style="border: 1px dashed var(--border-color); padding: 16px; min-height: 110px; border-radius: 8px; text-align: center;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-heading); margin-bottom: 30px;">
                        {{ __('Chef de Gare / Contrôle Embarquement') }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Signature & Cachet Officiel') }}</div>
                </div>

                <div class="signature-box" style="border: 1px dashed var(--border-color); padding: 16px; min-height: 110px; border-radius: 8px; text-align: center;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-heading); margin-bottom: 30px;">
                        {{ __('Conducteur Titulaire (Chauffeur)') }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Vu et pris en charge') }}</div>
                </div>

                <div class="signature-box" style="border: 1px dashed var(--border-color); padding: 16px; min-height: 110px; border-radius: 8px; text-align: center;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-heading); margin-bottom: 30px;">
                        {{ __('Convoyeur / Agent de Sécurité') }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Contrôle bagages et passagers') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection