@extends('layouts.dashboard')

@section('title', __('Centre d\'Arbitrage des Litiges Escaladés (48h) - Administration'))

@section('dashboard_content')
<div>
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
            {{ __('Centre d\'Arbitrage & Médiation des Litiges') }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            {{ __('Dossiers automatiquement escaladés après 48 heures sans résolution par l\'agence de transport.') }}
        </p>
    </div>

    <!-- Filter Buttons -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('admin.disputes.index', ['filter' => 'escalated']) }}" class="btn btn-sm {{ request('filter') === 'escalated' ? 'btn-danger' : 'btn-outline' }}">
            <i class="fa-solid fa-bolt"></i> {{ __('Litiges Escaladés (48h dépassé)') }}
        </a>
        <a href="{{ route('admin.disputes.index') }}" class="btn btn-sm {{ !request('filter') ? 'btn-primary' : 'btn-outline' }}">
            {{ __('Tous les litiges plateforme') }}
        </a>
    </div>

    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('N° Dossier') }}</th>
                        <th>{{ __('Passager') }}</th>
                        <th>{{ __('Agence Mise en Cause') }}</th>
                        <th>{{ __('Objet') }}</th>
                        <th>{{ __('Motif') }}</th>
                        <th>{{ __('Escalade 48h') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Arbitrage Admin') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $d)
                        <tr style="{{ $d->escalated && $d->status !== 'resolved' ? 'background: var(--danger-50);' : '' }}">
                            <td><strong style="color: var(--primary);">{{ $d->dispute_code }}</strong></td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-heading);">{{ $d->user->name ?? '-' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $d->user->phone ?? '-' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-heading);">Real Voyage ({{ $d->branch->name ?? 'Gare' }})</div>
                            </td>
                            <td>
                                <strong style="color: var(--text-heading);">{{ $d->title }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $d->description }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-outline">{{ ucfirst(str_replace('_', ' ', $d->category)) }}</span>
                            </td>
                            <td>
                                @if($d->escalated)
                                    <span class="badge badge-danger"><i class="fa-solid fa-bolt"></i> {{ __('Escaladé') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('En attente gare') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $d->status === 'resolved' ? 'badge-success' : 'badge-warning' }}">
                                    {{ strtoupper($d->status) }}
                                </span>
                            </td>
                            <td>
                                @if($d->status !== 'resolved')
                                    <button type="button" onclick="openArbitrateModal('{{ $d->id }}', '{{ $d->dispute_code }}', '{{ addslashes($d->title) }}', '{{ addslashes($d->branch->name ?? 'Real Voyage') }}', '{{ addslashes($d->manager_response ?? __('Aucune réponse de la gare après 48h.')) }}')" class="btn btn-sm btn-secondary">
                                        <i class="fa-solid fa-gavel"></i> {{ __('Arbitrer') }}
                                    </button>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--success-text); font-weight: 700;">
                                        <i class="fa-solid fa-check-double"></i> {{ __('Tranché') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px;">
                                {{ __('Aucun litige enregistré.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $disputes->links() }}
        </div>
    </div>
</div>

<!-- Admin Arbitrate Modal -->
<div id="arbitrateModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; align-items: center; justify-content: center;">
    <div class="card card-glass" style="max-width: 600px; width: 90%; padding: 30px; box-shadow: var(--shadow-xl);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <h3 style="font-size: 1.2rem; color: var(--text-heading); margin: 0;">
                <i class="fa-solid fa-gavel" style="color: var(--primary);"></i> {{ __('Arbitrage Administratif') }} <span id="arbCode"></span>
            </h3>
            <button onclick="closeArbitrateModal()" style="border: none; background: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 18px; font-size: 0.85rem;">
            <div style="font-weight: 700; margin-bottom: 4px; color: var(--text-heading);" id="arbTitle"></div>
            <div>{{ __('Gare / Terminal :') }} <strong id="arbAgency" style="color: var(--text-heading);"></strong></div>
            <div style="margin-top: 6px; color: var(--text-muted);">{{ __('Position Gare :') }} <em id="arbManagerResp"></em></div>
        </div>

        <form id="arbitrateForm" action="" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="status">{{ __('Décision de l\'Administration :') }}</label>
                <select name="status" class="form-control" required>
                    <option value="resolved">{{ __('Résolu en faveur du passager (Remboursement / Dédommagement)') }}</option>
                    <option value="rejected">{{ __('Rejeté (Aucune faute avérée de la compagnie)') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="admin_notes">{{ __('Motif de la Décision & Instructions *') }}</label>
                <textarea id="admin_notes" name="admin_notes" class="form-control" rows="4" required placeholder="{{ __('Saisissez la décision formelle d\'arbitrage notifiée aux deux parties...') }}"></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeArbitrateModal()" class="btn btn-outline" style="flex: 1;">{{ __('Annuler') }}</button>
                <button type="submit" class="btn btn-secondary" style="flex: 2; font-weight: 800;">
                    <i class="fa-solid fa-gavel"></i> {{ __('Rendre la Décision') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openArbitrateModal(disputeId, code, title, agency, resp) {
    document.getElementById('arbitrateForm').action = '/admin/disputes/' + disputeId + '/arbitrate';
    document.getElementById('arbCode').textContent = code;
    document.getElementById('arbTitle').textContent = title;
    document.getElementById('arbAgency').textContent = agency;
    document.getElementById('arbManagerResp').textContent = resp;
    document.getElementById('arbitrateModal').style.display = 'flex';
}

function closeArbitrateModal() {
    document.getElementById('arbitrateModal').style.display = 'none';
}
</script>
@endsection
