@extends('layouts.app')

@section('title', __('Détails Envoi de Colis - :code', ['code' => $shipment->tracking_code]))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Top Action Bar -->
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="{{ route('passenger.shipments.index') }}" style="color: var(--text-muted); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Mes envois de colis') }}
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print"></i> {{ __('Imprimer le Récépissé') }}
        </button>
    </div>

    <!-- Receipt Card -->
    <div class="card ticket-container" style="padding: 0; overflow: hidden; border: 2px solid var(--border-color); box-shadow: var(--shadow-xl); background: var(--bg-card);">
        <div style="background: var(--primary); color: white; padding: 24px 30px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div>
                    <div style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800;">
                        TransportCM {{ __('Bordereau d\'Expédition Fret') }}
                    </div>
                    <div style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.85);">{{ __('Récépissé Officiel d\'Envoi') }}</div>
                </div>
            </div>

            <div style="text-align: right;">
                <span class="badge badge-success" style="font-size: 0.85rem; padding: 4px 12px; font-weight: 800;">
                    {{ strtoupper(__($shipment->status)) }}
                </span>
                <div style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.9); margin-top: 4px;">
                    {{ __('SUIVI :') }} <strong>{{ $shipment->tracking_code }}</strong>
                </div>
            </div>
        </div>

        <div style="padding: 30px;">
            <!-- Secure OTP Code Callout Banner -->
            <div style="background: var(--warning-50); border: 2px solid var(--warning-border); border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--warning-text); text-transform: uppercase;">
                        <i class="fa-solid fa-key"></i> {{ __('Code OTP Secret de Retrait Destinataire :') }}
                    </div>
                    <div style="font-size: 0.85rem; color: var(--warning-text); margin-top: 2px;">
                        {{ __('Le destinataire devra présenter ce code et sa CNI au guichet pour retirer le colis.') }}
                    </div>
                </div>

                <div style="font-size: 2.2rem; font-weight: 900; font-family: monospace; letter-spacing: 4px; color: var(--warning-text); background: var(--bg-card); padding: 6px 16px; border-radius: 8px; border: 1.5px solid var(--warning-border);">
                    {{ $shipment->proof_of_delivery_code }}
                </div>
            </div>

            <!-- Sender & Recipient Grid -->
            <div class="grid grid-cols-2" style="gap: 20px; margin-bottom: 24px;">
                <div style="background: var(--bg-surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">{{ __('Expéditeur') }}</div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-heading);">{{ $shipment->sender->name }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">{{ __('Tél :') }} {{ $shipment->sender->phone }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">{{ __('E-mail :') }} {{ $shipment->sender->email }}</div>
                </div>

                <div style="background: var(--bg-surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">{{ __('Destinataire (Réceptionnaire)') }}</div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-heading);">{{ $shipment->recipient_name }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">{{ __('Tél :') }} {{ $shipment->recipient_phone }}</div>
                    <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">{{ __('Destination :') }} {{ $shipment->destination_station }} ({{ $shipment->recipient_city }})</div>
                </div>
            </div>

            <!-- Package Specs & Financials -->
            <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px; font-size: 0.9rem;">
                <div style="background: var(--bg-surface); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">{{ __('Poids') }}</div>
                    <div style="font-weight: 800; color: var(--text-heading);">{{ $shipment->weight_kg }} kg</div>
                </div>

                <div style="background: var(--bg-surface); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">{{ __('Catégorie') }}</div>
                    <div style="font-weight: 800; color: var(--text-heading);">{{ ucfirst($shipment->item_category) }}</div>
                </div>

                <div style="background: var(--bg-surface); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">{{ __('Assurance') }}</div>
                    <div style="font-weight: 800; color: {{ $shipment->insured ? 'var(--primary)' : 'var(--text-muted)' }};">
                        {{ $shipment->insured ? __('Oui (Valeur :amount F)', ['amount' => number_format($shipment->declared_value, 0, ',', ' ')]) : __('Non assurée') }}
                    </div>
                </div>

                <div style="background: var(--bg-surface); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">{{ __('Total Réglé') }}</div>
                    <div style="font-weight: 900; color: var(--primary); font-size: 1rem;">
                        {{ number_format($shipment->total_amount, 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>

            <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; font-size: 0.85rem; color: var(--text-muted);">
                <strong style="color: var(--text-heading);">{{ __('Description déclarée :') }}</strong> {{ $shipment->item_description }}
            </div>
        </div>
    </div>
</div>
@endsection
