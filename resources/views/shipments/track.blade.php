@extends('layouts.app')

@section('title', __('Suivi de Colis & Fret - TransportCM'))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Search Box Card -->
    <div class="card" style="padding: 32px; margin-bottom: 30px; text-align: center; box-shadow: var(--shadow-md);">
        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--road-light); color: var(--road-color); display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 12px;">
            <i class="fa-solid fa-box-open"></i>
        </div>
        <h1 style="font-size: 1.8rem; color: var(--text-heading); margin-bottom: 8px;">
            {{ __('Suivi National de Colis & Fret') }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 24px;">
            {{ __('Entrez votre numéro de suivi (ex: SH-CM-889102 ou SH-CM-772910)') }}
        </p>

        <form action="{{ route('shipments.track') }}" method="GET" style="max-width: 500px; margin: 0 auto;">
            <div style="display: flex; gap: 10px;">
                <input type="text" name="code" value="{{ $trackingCode }}" class="form-control" placeholder="{{ __('Code de suivi (SH-CM-...)') }}" required style="text-transform: uppercase; font-weight: 700; text-align: center; font-size: 1.1rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0 24px; font-weight: 700;">
                    <i class="fa-solid fa-magnifying-glass"></i> {{ __('Suivre') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Tracking Result Box -->
    @if($trackingCode)
        @if($shipment)
            <div class="card" style="padding: 32px; box-shadow: var(--shadow-lg);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; flex-wrap: wrap; gap: 14px;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('N° de Suivi') }}</div>
                        <div style="font-size: 1.5rem; font-weight: 900; color: var(--primary);">{{ $shipment->tracking_code }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                            {{ __('Pris en charge par') }} <strong>Real Voyage</strong> ({{ $shipment->branch->name ?? __('Gare Centrale') }})
                        </div>
                    </div>

                    <div style="text-align: right;">
                        @if($shipment->status === 'collected')
                            <span class="badge badge-success" style="font-size: 0.9rem; padding: 6px 14px;"><i class="fa-solid fa-circle-check"></i> {{ __('Colis Remis (Livré)') }}</span>
                        @elseif($shipment->status === 'arrived')
                            <span class="badge badge-warning" style="font-size: 0.9rem; padding: 6px 14px;"><i class="fa-solid fa-building-circle-check"></i> {{ __('Arrivé en Gare / Guichet') }}</span>
                        @elseif($shipment->status === 'in_transit')
                            <span class="badge badge-road" style="font-size: 0.9rem; padding: 6px 14px;"><i class="fa-solid fa-truck-fast"></i> {{ __('En cours d\'Acheminement') }}</span>
                        @else
                            <span class="badge badge-outline" style="font-size: 0.9rem; padding: 6px 14px;"><i class="fa-solid fa-receipt"></i> {{ __('Enregistré en Agence') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Interactive Visual Timeline -->
                <div style="margin: 30px 0;">
                    <div style="display: flex; justify-content: space-between; position: relative;">
                        <!-- Line background -->
                        <div style="position: absolute; top: 20px; left: 10%; right: 10%; height: 4px; background: var(--border-color); z-index: 1;"></div>
                        
                        @php
                            $stepIndex = match($shipment->status) {
                                'registered' => 1,
                                'in_transit' => 2,
                                'arrived' => 3,
                                'collected' => 4,
                                default => 1
                            };
                        @endphp

                        <!-- Step 1 -->
                        <div style="text-align: center; z-index: 2; position: relative; width: 25%;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $stepIndex >= 1 ? 'var(--primary)' : 'var(--border-color)' }}; color: {{ $stepIndex >= 1 ? '#FFFFFF' : 'var(--text-muted)' }}; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 8px;">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-heading);">{{ __('Enregistrement') }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $shipment->created_at->format('d/m H:i') }}</div>
                        </div>

                        <!-- Step 2 -->
                        <div style="text-align: center; z-index: 2; position: relative; width: 25%;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $stepIndex >= 2 ? 'var(--primary)' : 'var(--border-color)' }}; color: {{ $stepIndex >= 2 ? '#FFFFFF' : 'var(--text-muted)' }}; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 8px;">
                                <i class="fa-solid fa-truck-moving"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-heading);">{{ __('En Transit') }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Sur la ligne') }}</div>
                        </div>

                        <!-- Step 3 -->
                        <div style="text-align: center; z-index: 2; position: relative; width: 25%;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $stepIndex >= 3 ? 'var(--primary)' : 'var(--border-color)' }}; color: {{ $stepIndex >= 3 ? '#FFFFFF' : 'var(--text-muted)' }}; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 8px;">
                                <i class="fa-solid fa-warehouse"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-heading);">{{ __('Arrivé en Gare') }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $shipment->recipient_city }}</div>
                        </div>

                        <!-- Step 4 -->
                        <div style="text-align: center; z-index: 2; position: relative; width: 25%;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $stepIndex >= 4 ? 'var(--primary)' : 'var(--border-color)' }}; color: {{ $stepIndex >= 4 ? '#FFFFFF' : 'var(--text-muted)' }}; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 8px;">
                                <i class="fa-solid fa-hand-holding-box"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-heading);">{{ __('Colis Retiré') }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                {{ $shipment->collected_at ? $shipment->collected_at->format('d/m H:i') : __('En attente') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipment Info Grid -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin-top: 30px;">
                    <div class="grid grid-cols-2" style="gap: 16px; font-size: 0.9rem;">
                        <div>
                            <span style="color: var(--text-muted);">{{ __('Destinataire :') }}</span>
                            <strong style="color: var(--text-heading);">{{ $shipment->recipient_name }}</strong> ({{ $shipment->recipient_phone }})
                        </div>
                        <div>
                            <span style="color: var(--text-muted);">{{ __('Lieu de retrait :') }}</span>
                            <strong style="color: var(--text-heading);">{{ $shipment->destination_station }} ({{ $shipment->recipient_city }})</strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted);">{{ __('Poids & Nature :') }}</span>
                            <strong style="color: var(--text-heading);">{{ $shipment->weight_kg }} kg</strong> • {{ __($shipment->item_category) }}
                        </div>
                        <div>
                            <span style="color: var(--text-muted);">{{ __('Assurance :') }}</span>
                            <strong style="color: var(--text-heading);">{{ $shipment->insured ? __('Oui (Valeur déclarée :amount FCFA)', ['amount' => number_format($shipment->declared_value, 0, ',', ' ')]) : __('Non') }}</strong>
                        </div>
                    </div>

                    @if($shipment->status === 'collected')
                        <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border-color); font-size: 0.85rem; color: var(--primary);">
                            <i class="fa-solid fa-signature"></i> {{ __('Remis à :') }} <strong>{{ $shipment->collected_by_name }}</strong> ({{ __('CNI :') }} {{ $shipment->collected_by_cni }}) {{ __('le') }} {{ $shipment->collected_at->format('d/m/Y à H:i') }}.
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card" style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 2.2rem; color: var(--secondary); margin-bottom: 10px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 6px; color: var(--text-heading);">{{ __('Aucun colis trouvé avec le code') }} "{{ $trackingCode }}"</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    {{ __('Veuillez vérifier les caractères saisis ou contacter l\'agence expéditrice.') }}
                </p>
            </div>
        @endif
    @endif
</div>
@endsection
