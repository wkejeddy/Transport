@extends('layouts.dashboard')

@section('title', __('Gestion des Réclamations (Délai 48h) - Espace Manager'))

@section('dashboard_content')
<div>
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
            {{ __('File de Traitement des Réclamations Clients') }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            {{ __('Répondez aux réclamations dans un délai strict de 48 heures pour éviter l\'escalade automatique à l\'Administrateur.') }}
        </p>
    </div>

    <!-- Info Box -->
    <div class="alert alert-warning" style="margin-bottom: 24px;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
        <div>
            <strong>{{ __('Impact sur votre Scorecard :') }}</strong> {{ __('Les litiges non résolus ou escaladés réduisent automatiquement la note globale de votre agence. Traitez chaque dossier avec diligence.') }}
        </div>
    </div>

    <!-- Disputes List Card -->
    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('N° Dossier') }}</th>
                        <th>{{ __('Passager') }}</th>
                        <th>{{ __('Objet') }}</th>
                        <th>{{ __('Motif') }}</th>
                        <th>{{ __('Compte à rebours 48h') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $d)
                        <tr>
                            <td>
                                <strong style="color: var(--primary);">{{ $d->dispute_code }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $d->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 700;">{{ $d->user->name ?? __('Client') }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $d->user->phone ?? '-' }}</div>
                            </td>
                            <td>
                                <strong>{{ $d->title }}</strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $d->description }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-outline">{{ ucfirst(str_replace('_', ' ', $d->category)) }}</span>
                            </td>
                            <td>
                                @if($d->escalated)
                                    <span class="badge badge-danger"><i class="fa-solid fa-bolt"></i> {{ __('Escaladé Admin') }}</span>
                                @elseif($d->status === 'open')
                                    <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> {{ $d->hours_remaining }}h {{ __('restantes') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('Traité') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($d->status === 'resolved')
                                    <span class="badge badge-success">{{ __('Résolu') }}</span>
                                @else
                                    <button type="button" onclick="openResolveModal('{{ $d->id }}', '{{ $d->dispute_code }}', '{{ addslashes($d->title) }}', '{{ addslashes($d->description) }}', '{{ addslashes($d->manager_response ?? '') }}')" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-reply"></i> {{ __('Traiter') }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">
                                {{ __('Aucune réclamation en cours pour votre agence.') }}
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

<!-- Resolve Dispute Modal -->
<div id="resolveModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 600px; width: 90%; padding: 30px; box-shadow: var(--shadow-xl);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <h3 style="font-size: 1.2rem; color: var(--bg-dark); margin: 0;">
                <i class="fa-solid fa-scale-balanced" style="color: var(--primary);"></i> {{ __('Traitement Réclamation') }} <span id="modalDisputeCode"></span>
            </h3>
            <button onclick="closeResolveModal()" style="border: none; background: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 18px;">
            <div style="font-weight: 700; font-size: 0.9rem;" id="modalDisputeTitle"></div>
            <p style="font-size: 0.85rem; color: #475569; margin: 4px 0 0;" id="modalDisputeDesc"></p>
        </div>

        <form id="resolveForm" action="" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="status">{{ __('Décision de l\'Agence :') }}</label>
                <select name="status" class="form-control" required>
                    <option value="resolved">{{ __('Résoudre le dossier (Accord / Solution apportée)') }}</option>
                    <option value="in_review">{{ __('Mettre en cours d\'instruction interne') }}</option>
                    <option value="rejected">{{ __('Rejeter la réclamation (Non fondée)') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="manager_response">{{ __('Réponse Explicative apportée au Client *') }}</label>
                <textarea id="manager_response" name="manager_response" class="form-control" rows="4" required placeholder="{{ __('Détaillez la solution, l\'avoir offert, le remboursement ou la réponse officielle de l\'agence...') }}"></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeResolveModal()" class="btn btn-outline" style="flex: 1;">{{ __('Fermer') }}</button>
                <button type="submit" class="btn btn-primary" style="flex: 2; font-weight: 800;">
                    <i class="fa-solid fa-paper-plane"></i> {{ __('Enregistrer la Réponse') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(disputeId, code, title, desc, existingResponse) {
    document.getElementById('resolveForm').action = '/manager/disputes/' + disputeId + '/resolve';
    document.getElementById('modalDisputeCode').textContent = code;
    document.getElementById('modalDisputeTitle').textContent = title;
    document.getElementById('modalDisputeDesc').textContent = desc;
    document.getElementById('manager_response').value = existingResponse || '';
    document.getElementById('resolveModal').style.display = 'flex';
}

function closeResolveModal() {
    document.getElementById('resolveModal').style.display = 'none';
}
</script>
@endsection
