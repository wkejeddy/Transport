@extends('layouts.dashboard')

@section('title', __('Mes Envois de Colis & Fret'))

@section('dashboard_content')
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
                {{ __('Mes Expéditions de Colis & Fret') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                {{ __('Suivez en temps réel l\'acheminement de vos paquets et partagez le code OTP avec les destinataires.') }}
            </p>
        </div>

        <a href="{{ route('passenger.shipments.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus-circle"></i> {{ __('Nouvel Envoi de Colis') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        @if($shipments->isEmpty())
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fa-solid fa-box-open" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 10px;"></i>
                <h3 style="font-size: 1.1rem; color: var(--text-heading);">{{ __('Aucun envoi de colis enregistré') }}</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 16px;">{{ __('Expédiez des marchandises par bus ou train en quelques clics.') }}</p>
                <a href="{{ route('passenger.shipments.create') }}" class="btn btn-sm btn-primary">{{ __('Expédier un colis') }}</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('N° Suivi') }}</th>
                            <th>{{ __('Compagnie') }}</th>
                            <th>{{ __('Destinataire') }}</th>
                            <th>{{ __('Destination') }}</th>
                            <th>{{ __('Poids') }}</th>
                            <th>{{ __('Code OTP Retrait') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shipments as $s)
                            <tr>
                                <td>
                                    <strong style="color: var(--primary);">{{ $s->tracking_code }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $s->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700;">Real Voyage</div>
                                    <span class="badge badge-road" style="font-size: 0.65rem;">
                                        {{ $s->branch->name ?? __('Gare Centrale') }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700;">{{ $s->recipient_name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $s->recipient_phone }}</div>
                                </td>
                                <td>
                                    <strong>{{ $s->recipient_city }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $s->destination_station }}</div>
                                </td>
                                <td>{{ $s->weight_kg }} kg</td>
                                <td>
                                    <span class="badge badge-warning" style="font-family: monospace; font-weight: 900; font-size: 0.95rem; padding: 3px 8px; border-radius: 4px;">
                                        {{ $s->proof_of_delivery_code }}
                                    </span>
                                </td>
                                <td>
                                    @if($s->status === 'collected')
                                        <span class="badge badge-success">{{ __('Livré') }}</span>
                                    @elseif($s->status === 'arrived')
                                        <span class="badge badge-warning">{{ __('En Gare') }}</span>
                                    @elseif($s->status === 'in_transit')
                                        <span class="badge badge-road">{{ __('En Transit') }}</span>
                                    @else
                                        <span class="badge badge-outline">{{ __('Enregistré') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('passenger.shipments.show', $s) }}" class="btn btn-sm btn-outline">
                                        {{ __('Détails') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px;">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
