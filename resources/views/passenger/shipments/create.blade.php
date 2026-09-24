@extends('layouts.app')

@section('title', __('Expédier un Colis / Fret - Real Voyage S.A.'))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Top Back Link -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('passenger.shipments.index') }}" style="color: var(--text-muted); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour à mes envois') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 36px; box-shadow: var(--shadow-lg);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 54px; height: 54px; border-radius: 50%; background: var(--primary-50); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px;">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <h1 style="font-size: 1.8rem; color: var(--text-heading); font-weight: 800;">{{ __('Service Fret & Messagerie Real Voyage S.A.') }}</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
                {{ __('Acheminement sécurisé entre les 11 terminaux régionaux (Ouest, Douala, Yaoundé) avec traçabilité et code de retrait OTP.') }}
            </p>
        </div>

        <form action="{{ route('passenger.shipments.store') }}" method="POST" id="shipmentForm">
            @csrf

            <!-- 1. Agency Overview Banner -->
            <div style="background: var(--bg-surface); border: 1.5px solid var(--border-subtle); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 1.05rem; color: var(--text-heading);">
                            Real Voyage Transport S.A.
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ __('Réseau Interurbain Ouest • Douala • Yaoundé • Départs 10h00 & 21h30') }}
                        </div>
                    </div>
                </div>
                <span class="badge badge-success" style="font-size: 0.75rem; font-weight: 700; padding: 6px 12px;">
                    <i class="fa-solid fa-shield-halved"></i> {{ __('Fret Garanti & Assuré') }}
                </span>
            </div>

            <!-- 2. Terminals: Origin & Destination Routing -->
            <h4 style="font-size: 1rem; color: var(--text-heading); margin-top: 24px; margin-bottom: 16px; border-bottom: 2px solid var(--border-subtle); padding-bottom: 8px; font-weight: 800;">
                <i class="fa-solid fa-route text-primary"></i> 1. {{ __('Axe de Transport & Terminaux Real Voyage') }}
            </h4>

            <div class="grid grid-cols-2" style="gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="origin_terminal_id" style="font-weight: 700;">{{ __('Terminal de Dépôt (Origine) *') }}</label>
                    <select id="origin_terminal_id" name="origin_terminal_id" class="form-control" required style="border-radius: 8px;">
                        <option value="">{{ __("-- Sélectionnez le terminal d'expédition --") }}</option>
                        @foreach($terminalsByRegion as $region => $terms)
                            <optgroup label="{{ __('Région') }} {{ $region }}">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('origin_terminal_id') == $term->id ? 'selected' : '' }}>
                                        {{ $term->name }} ({{ $term->city }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="destination_terminal_id" style="font-weight: 700;">{{ __('Terminal de Retrait (Destination) *') }}</label>
                    <select id="destination_terminal_id" name="destination_terminal_id" class="form-control" required style="border-radius: 8px;">
                        <option value="">{{ __("-- Sélectionnez le terminal de destination --") }}</option>
                        @foreach($terminalsByRegion as $region => $terms)
                            <optgroup label="{{ __('Région') }} {{ $region }}">
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('destination_terminal_id') == $term->id ? 'selected' : '' }}>
                                        {{ $term->name }} ({{ $term->city }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 3. Recipient Details -->
            <h4 style="font-size: 1rem; color: var(--text-heading); margin-top: 24px; margin-bottom: 16px; border-bottom: 2px solid var(--border-subtle); padding-bottom: 8px; font-weight: 800;">
                <i class="fa-solid fa-user-tag text-primary"></i> 2. {{ __('Destinataire Autorisé') }}
            </h4>

            <div class="grid grid-cols-2" style="gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="recipient_name" style="font-weight: 700;">{{ __('Nom & Prénom du Destinataire *') }}</label>
                    <input type="text" id="recipient_name" name="recipient_name" class="form-control" value="{{ old('recipient_name') }}" required placeholder="{{ __('Ex: Samuel Nguema') }}" style="border-radius: 8px;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="recipient_phone" style="font-weight: 700;">{{ __('Téléphone du Destinataire (Reçoit le code OTP) *') }}</label>
                    <input type="text" id="recipient_phone" name="recipient_phone" class="form-control" value="{{ old('recipient_phone') }}" required placeholder="+237 6XX XX XX XX" style="border-radius: 8px;">
                </div>
            </div>

            <!-- 4. Package Specifications & 10% Declared Value Pricing -->
            <h4 style="font-size: 1rem; color: var(--text-heading); margin-top: 24px; margin-bottom: 16px; border-bottom: 2px solid var(--border-subtle); padding-bottom: 8px; font-weight: 800;">
                <i class="fa-solid fa-scale-balanced text-primary"></i> 3. {{ __('Caractéristiques du Colis & Valeur Déclarée') }}
            </h4>

            <div class="grid grid-cols-2" style="gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="item_category" style="font-weight: 700;">{{ __('Catégorie de Marchandise *') }}</label>
                    <select id="item_category" name="item_category" class="form-control" required style="border-radius: 8px;">
                        <option value="electronics">{{ __('Appareils Électroniques & Informatiques') }}</option>
                        <option value="general">{{ __('Marchandises Générales / Effets personnels') }}</option>
                        <option value="clothing">{{ __('Vêtements & Textiles') }}</option>
                        <option value="documents">{{ __('Plis & Documents Officiels') }}</option>
                        <option value="perishables">{{ __('Vivres & Produits Agroalimentaires non périssables') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="weight_kg" style="font-weight: 700;">{{ __('Poids approximatif (en Kg) *') }}</label>
                    <input type="number" step="0.5" min="0.5" max="500" id="weight_kg" name="weight_kg" class="form-control" value="{{ old('weight_kg', '5.0') }}" required style="border-radius: 8px;">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="item_description" style="font-weight: 700;">{{ __('Description sommaire du contenu *') }}</label>
                <textarea id="item_description" name="item_description" class="form-control" rows="2" placeholder="{{ __('Ex: Carton contenant 1 unité centrale, 1 écran et câblages') }}" required style="border-radius: 8px;">{{ old('item_description') }}</textarea>
            </div>

            <!-- Declared Value Input (Strict 10% Fee Basis) -->
            <div style="background: var(--bg-surface); border: 1.5px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 24px;">
                <div class="form-group" style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" for="declared_value" style="font-weight: 800; color: var(--text-heading); font-size: 0.95rem; margin: 0;">
                            {{ __('Valeur Déclarée Réelle du Colis (en FCFA) *') }}
                        </label>
                        <span class="badge" style="background: var(--primary); color: white; font-size: 0.72rem; padding: 4px 8px; border-radius: 4px;">
                            {{ __('Tarification : Strictement 10%') }}
                        </span>
                    </div>
                    <input type="number" min="1000" step="1000" id="declared_value" name="declared_value" class="form-control" value="{{ old('declared_value', '50000') }}" required oninput="calculateFreightFee()" style="border-radius: 8px; font-size: 1.1rem; font-weight: 700;">
                    <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 4px;">
                        {{ __("Le tarif d'acheminement est calculé automatiquement à hauteur exacte de 10% de la valeur déclarée.") }}
                    </small>
                </div>

                <!-- Mandatory Liability & Reimbursement Disclosure -->
                <div style="background: var(--primary-50); border: 1px solid var(--primary-100); border-left: 4px solid var(--primary); border-radius: 6px; padding: 12px 14px; margin-top: 14px; font-size: 0.83rem; color: var(--primary-text); line-height: 1.45;">
                    <div style="font-weight: 800; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-info"></i> {{ __('Engagement de Responsabilité Real Voyage :') }}
                    </div>
                    {{ $liabilityNotice ?? __("En cas de perte ou d'avarie constatée, la responsabilité et le remboursement de Real Voyage sont strictement fixés entre 2x et 5x la valeur déclarée du colis (selon conclusions du constat d'expertise contradictoire en gare).") }}
                </div>
            </div>

            <!-- Transparent Real-time Calculation Summary -->
            <div style="background: var(--primary-gradient); color: white; border-radius: 12px; padding: 22px; margin-bottom: 24px; box-shadow: var(--shadow-lg);">
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 8px; color: rgba(255, 255, 255, 0.8);">
                    <span>{{ __('Valeur Déclarée Prise en Charge :') }}</span>
                    <strong id="previewDeclaredValue" style="color: #FFFFFF;">50 000 FCFA</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 12px; color: rgba(255, 255, 255, 0.8);">
                    <span>{{ __('Frais de Transport Fret (Taux fixe 10%) :') }}</span>
                    <strong id="previewRatePercent" style="color: var(--primary-text);">10%</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: 900; border-top: 1px solid rgba(255, 255, 255, 0.2); padding-top: 12px; align-items: center;">
                    <span>{{ __('Total Frais de Fret à Régler :') }}</span>
                    <span id="previewTotalFee" style="color: var(--success-text); font-size: 1.6rem;">5 000 FCFA</span>
                </div>
                <div style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.75); margin-top: 8px; text-align: right;">
                    <i class="fa-solid fa-wallet"></i> {{ __('Paiement accepté par E-Wallet Real Voyage, MTN MoMo ou Orange Money') }}
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 800; height: 50px; border-radius: 8px;">
                <i class="fa-solid fa-lock"></i> {{ __('Enregistrer et Procéder au Règlement (10%)') }}
            </button>
        </form>
    </div>
</div>

<script>
function calculateFreightFee() {
    const declaredInput = document.getElementById('declared_value');
    const declaredVal = parseFloat(declaredInput.value) || 0;

    // Strict 10% rule
    const fee = Math.round(declaredVal * 0.10);

    document.getElementById('previewDeclaredValue').textContent = new Intl.NumberFormat('fr-FR').format(declaredVal) + ' FCFA';
    document.getElementById('previewTotalFee').textContent = new Intl.NumberFormat('fr-FR').format(fee) + ' FCFA';
}

document.addEventListener('DOMContentLoaded', function() {
    calculateFreightFee();
});
</script>
@endsection
