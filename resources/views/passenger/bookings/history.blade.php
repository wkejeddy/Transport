@extends('layouts.dashboard')

@section('title', __('Historique des Réservations & E-Billets'))

@section('dashboard_content')
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
                {{ __('Mes Billets & E-Tickets') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                {{ __('Consultez l\'historique complet de vos voyages par bus et train.') }}
            </p>
        </div>

        <a href="{{ route('trips.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus-circle"></i> {{ __('Réserver un autre trajet') }}
        </a>
    </div>

    <!-- Bookings Table Card -->
    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        @if($bookings->isEmpty())
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fa-solid fa-ticket" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 10px;"></i>
                <h3 style="font-size: 1.1rem; color: var(--text-heading);">{{ __('Aucune réservation trouvée') }}</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 16px;">{{ __('Vous n\'avez pas encore réservé de billet.') }}</p>
                <a href="{{ route('trips.index') }}" class="btn btn-sm btn-primary">{{ __('Explorer les départs') }}</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Référence') }}</th>
                            <th>{{ __('Compagnie & Mode') }}</th>
                            <th>{{ __('Trajet') }}</th>
                            <th>{{ __('Date & Heure') }}</th>
                            <th>{{ __('Places & Classe') }}</th>
                            <th>{{ __('Montant') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $b)
                            <tr>
                                <td>
                                    <strong style="color: var(--text-heading);">{{ $b->booking_reference }}</strong>
                                </td>
                                <td>
                                    <div style="font-weight: 700;">Real Voyage</div>
                                    <span class="badge badge-road" style="font-size: 0.65rem;">
                                        {{ $b->trip->branch->name ?? __('Gare Centrale') }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $b->trip->departure_city }} &rarr; {{ $b->trip->arrival_city }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $b->trip->departure_station }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700;">{{ $b->trip->departure_time->format('d/m/Y') }}</div>
                                    <div style="font-size: 0.8rem; color: var(--primary);">{{ $b->trip->departure_time->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div><strong>{{ $b->seats_count }}</strong> {{ __('place(s)') }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ __('Sièges:') }} {{ implode(', ', $b->seat_numbers ?? []) }}
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--primary);">{{ number_format($b->total_amount, 0, ',', ' ') }} F</strong>
                                </td>
                                <td>
                                    @if($b->status === 'confirmed')
                                        <span class="badge badge-success">{{ __('Confirmé') }}</span>
                                    @elseif($b->status === 'checked_in')
                                        <span class="badge badge-road">{{ __('Embarqué') }}</span>
                                    @elseif($b->status === 'reserved')
                                        <span class="badge badge-warning"><i class="fa-solid fa-shield-check"></i> {{ __('Réservé (500 F payés)') }}</span>
                                    @elseif($b->status === 'pending')
                                        <span class="badge badge-outline"><i class="fa-solid fa-clock"></i> {{ __('En attente (2 min)') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ __('Annulé') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($b->status === 'confirmed' || $b->status === 'checked_in')
                                        <a href="{{ route('passenger.bookings.ticket', $b) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-qrcode"></i> {{ __('Billet') }}
                                        </a>
                                    @elseif($b->status === 'reserved')
                                        <a href="{{ route('passenger.bookings.checkout', $b) }}" class="btn btn-sm btn-primary" style="font-weight: 700;">
                                            <i class="fa-solid fa-money-bill-wave"></i> {{ __('Solder Billet') }}
                                        </a>
                                    @elseif($b->status === 'pending')
                                        <a href="{{ route('passenger.bookings.checkout', $b) }}" class="btn btn-sm btn-om">
                                            {{ __('Payer (2m)') }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px;">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
