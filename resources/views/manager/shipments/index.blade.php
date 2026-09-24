@extends('layouts.dashboard')

@section('title', __('Gestion du Fret & Colis - Espace Manager'))

@section('dashboard_content')
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
                {{ __('Gestion du Fret & Colis Marchandises') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                {{ __('Suivez les colis reçus, modifiez leur statut d\'acheminement et validez la remise par code OTP.') }}
            </p>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card card-glass" style="padding: 20px; margin-bottom: 24px;">
        <form action="{{ route('manager.shipments.index') }}" method="GET">
            <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Rechercher par code de suivi (SH-CM-...), nom ou téléphone...') }}">
                </div>
                <div style="width: 200px;">
                    <select name="status" class="form-control">
                        <option value="">{{ __('Tous les statuts') }}</option>
                        <option value="registered" {{ request('status') === 'registered' ? 'selected' : '' }}>{{ __('Enregistré') }}</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>{{ __('En Transit') }}</option>
                        <option value="arrived" {{ request('status') === 'arrived' ? 'selected' : '' }}>{{ __('Arrivé en Gare') }}</option>
                        <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>{{ __('Livré / Retiré') }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> {{ __('Filtrer') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Shipments Table Card -->
    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('N° Suivi') }}</th>
                        <th>{{ __('Expéditeur') }}</th>
                        <th>{{ __('Destinataire') }}</th>
                        <th>{{ __('Destination') }}</th>
                        <th>{{ __('Poids & Catégorie') }}</th>
                        <th>{{ __('Frais Fret') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Action Guichet') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $sh)
                        <tr>
                            <td>
                                <strong style="color: var(--primary);">{{ $sh->tracking_code }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $sh->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-heading);">{{ $sh->sender->name ?? __('Expéditeur') }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $sh->sender->phone ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-heading);">{{ $sh->recipient_name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $sh->recipient_phone }}</div>
                            </td>
                            <td>
                                <strong>{{ $sh->recipient_city }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $sh->destination_station }}</div>
                            </td>
                            <td>
                                <div><strong>{{ $sh->weight_kg }} kg</strong></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ ucfirst($sh->item_category) }}</div>
                            </td>
                            <td>
                                <strong>{{ number_format($sh->total_amount, 0, ',', ' ') }} F</strong>
                                @if($sh->insured)
                                    <div style="font-size: 0.7rem; color: var(--primary); font-weight: 600;"><i class="fa-solid fa-shield"></i> {{ __('Assuré') }}</div>
                                @endif
                            </td>
                            <td>
                                @if($sh->status === 'collected')
                                    <span class="badge badge-success">{{ __('Livré') }}</span>
                                @elseif($sh->status === 'arrived')
                                    <span class="badge badge-warning">{{ __('En Gare / Agence') }}</span>
                                @elseif($sh->status === 'in_transit')
                                    <span class="badge badge-road">{{ __('En Route') }}</span>
                                @else
                                    <span class="badge badge-outline">{{ __('Enregistré') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($sh->status !== 'collected')
                                    <button type="button" onclick="openDeliveryModal('{{ $sh->id }}', '{{ $sh->tracking_code }}', '{{ $sh->recipient_name }}', '{{ $sh->recipient_phone }}')" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-signature"></i> {{ __('Remettre (OTP)') }}
                                    </button>

                                    <!-- Quick Status Update dropdown -->
                                    <form action="{{ route('manager.shipments.status', $sh) }}" method="POST" style="display: inline; margin-left: 6px;">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" style="font-size: 0.75rem; padding: 4px 6px; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-main);">
                                            <option value="">{{ __('Statut...') }}</option>
                                            <option value="in_transit" {{ $sh->status === 'in_transit' ? 'selected' : '' }}>{{ __('En transit') }}</option>
                                            <option value="arrived" {{ $sh->status === 'arrived' ? 'selected' : '' }}>{{ __('Arrivé en gare') }}</option>
                                        </select>
                                    </form>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--success-text); font-weight: 700;">
                                        <i class="fa-solid fa-check"></i> {{ __('Remis à :name', ['name' => $sh->collected_by_name]) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px;">
                                {{ __('Aucun colis trouvé.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $shipments->links() }}
        </div>
    </div>
</div>

<!-- OTP Delivery Modal (Dialog) -->
<div id="deliveryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; align-items: center; justify-content: center;">
    <div class="card card-glass" style="max-width: 500px; width: 90%; padding: 30px; box-shadow: var(--shadow-xl);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <h3 style="font-size: 1.2rem; color: var(--text-heading); margin: 0;">
                <i class="fa-solid fa-key" style="color: var(--primary);"></i> {{ __('Validation de Remise de Colis') }}
            </h3>
            <button onclick="closeDeliveryModal()" style="border: none; background: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="deliveryForm" action="" method="POST">
            @csrf
            <div style="background: var(--accent-50); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin-bottom: 16px; font-size: 0.85rem; color: var(--accent-text);">
                {{ __('Colis :') }} <strong id="modalTrackingCode" style="color: var(--text-heading);"></strong><br>
                {{ __('Destinataire attendu :') }} <strong id="modalRecipient" style="color: var(--text-heading);"></strong>
            </div>

            <div class="form-group">
                <label class="form-label" for="otp_code">
                    <i class="fa-solid fa-lock"></i> {{ __('Code OTP de Retrait (4 chiffres fournis par le destinataire) *') }}
                </label>
                <input type="text" id="otp_code" name="otp_code" class="form-control" required placeholder="{{ __('Ex: 4829') }}" style="font-size: 1.4rem; font-family: monospace; letter-spacing: 4px; text-align: center; font-weight: 900;">
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="collected_by_name">{{ __('Nom de la personne *') }}</label>
                    <input type="text" id="collected_by_name" name="collected_by_name" class="form-control" required placeholder="{{ __('Nom tel que sur CNI') }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="collected_by_cni">{{ __('N° de CNI / Passeport *') }}</label>
                    <input type="text" id="collected_by_cni" name="collected_by_cni" class="form-control" required placeholder="{{ __('N° CNI vérifié') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">{{ __('Observations (Optionnel)') }}</label>
                <input type="text" id="notes" name="notes" class="form-control" placeholder="{{ __('Colis remis en parfait état') }}">
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeDeliveryModal()" class="btn btn-outline" style="flex: 1;">{{ __('Annuler') }}</button>
                <button type="submit" class="btn btn-primary" style="flex: 2; font-weight: 800;">
                    <i class="fa-solid fa-check-double"></i> {{ __('Confirmer la Remise') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeliveryModal(shipmentId, trackingCode, recipientName, recipientPhone) {
    document.getElementById('deliveryForm').action = '/manager/shipments/' + shipmentId + '/deliver';
    document.getElementById('modalTrackingCode').textContent = trackingCode;
    document.getElementById('modalRecipient').textContent = recipientName + ' (' + recipientPhone + ')';
    document.getElementById('collected_by_name').value = recipientName;
    document.getElementById('deliveryModal').style.display = 'flex';
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').style.display = 'none';
}
</script>
@endsection
