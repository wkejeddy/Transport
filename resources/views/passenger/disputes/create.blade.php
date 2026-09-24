@extends('layouts.app')

@section('title', __('Ouvrir une Réclamation / Litige'))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Top Back Link -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('passenger.disputes.index') }}" style="color: var(--text-muted); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour aux réclamations') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 36px; box-shadow: var(--shadow-lg);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--danger-50); color: var(--danger); display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 12px; border: 1px solid var(--danger-border);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h1 style="font-size: 1.8rem; color: var(--text-heading);">{{ __('Signaler un Incident / Litige') }}</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
                {{ __('Procédure officielle d\'arbitrage avec escalade automatique sous 48h.') }}
            </p>
        </div>

        <form action="{{ route('passenger.disputes.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="branch_id">{{ __('Gare / Direction Régionale Real Voyage *') }}</label>
                <select name="branch_id" id="branch_id" class="form-control" required>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }} - {{ $b->region }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="booking_id">{{ __('Associer un Billet (Optionnel)') }}</label>
                    <select name="booking_id" id="booking_id" class="form-control">
                        <option value="">-- {{ __('Aucun billet spécifique') }} --</option>
                        @foreach($userBookings as $bk)
                            <option value="{{ $bk->id }}" {{ $selectedBookingId == $bk->id ? 'selected' : '' }}>
                                {{ $bk->booking_reference }} ({{ $bk->trip->departure_city }} - {{ $bk->trip->arrival_city }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="shipment_id">{{ __('Associer un Colis (Optionnel)') }}</label>
                    <select name="shipment_id" id="shipment_id" class="form-control">
                        <option value="">-- {{ __('Aucun colis spécifique') }} --</option>
                        @foreach($userShipments as $sh)
                            <option value="{{ $sh->id }}" {{ $selectedShipmentId == $sh->id ? 'selected' : '' }}>
                                {{ $sh->tracking_code }} ({{ __('Pour:') }} {{ $sh->recipient_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label" for="category">{{ __('Motif de la Réclamation *') }}</label>
                    <select name="category" id="category" class="form-control" required>
                        <option value="delay">{{ __('Retard important non communiqué') }}</option>
                        <option value="lost_package">{{ __('Colis ou Bagage perdu / manquant') }}</option>
                        <option value="damaged_item">{{ __('Colis ou bagage endommagé') }}</option>
                        <option value="denied_boarding">{{ __('Refus d\'embarquement injustifié') }}</option>
                        <option value="refund_request">{{ __('Demande de remboursement') }}</option>
                        <option value="other">{{ __('Autre motif de réclamation') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">{{ __('Objet Résumé *') }}</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required placeholder="{{ __('Ex: Retard de 2h sans explication à Bessengué') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">{{ __('Explication Détaillée des Faits *') }}</label>
                <textarea id="description" name="description" class="form-control" rows="4" required placeholder="{{ __('Décrivez précisément les faits survenus, les dates, les agents rencontrés et votre demande...') }}">{{ old('description') }}</textarea>
            </div>

            <div style="background: var(--accent-50); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 20px; font-size: 0.85rem; color: var(--accent-text);">
                <i class="fa-solid fa-clock"></i> {{ __('Un chronomètre de 48 heures s\'enclenchera dès la soumission. Si l\'agence n\'apporte pas de réponse satisfaisante, l\'administration de la plateforme interviendra directement.') }}
            </div>

            <button type="submit" class="btn btn-danger btn-lg" style="width: 100%; font-weight: 800;">
                <i class="fa-solid fa-paper-plane"></i> {{ __('Soumettre la Réclamation') }}
            </button>
        </form>
    </div>
</div>
@endsection
