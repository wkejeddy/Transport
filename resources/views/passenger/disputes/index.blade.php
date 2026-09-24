@extends('layouts.dashboard')

@section('title', __('Mes Réclamations & Litiges'))

@section('dashboard_content')
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
                {{ __('Centre de Réclamations & Litiges') }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                {{ __('Suivez la résolution de vos litiges avec médiation garantie et escalade automatique sous 48h.') }}
            </p>
        </div>

        <a href="{{ route('passenger.disputes.create') }}" class="btn btn-danger">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('Ouvrir une Réclamation') }}
        </a>
    </div>

    <!-- 48-Hour Auto-Escalation Info Banner -->
    <div class="alert alert-info" style="margin-bottom: 24px;">
        <i class="fa-solid fa-stopwatch" style="font-size: 1.2rem;"></i>
        <div>
            <strong>{{ __('Garantie Protection Passager 48h :') }}</strong> {{ __('Dès l\'ouverture de votre réclamation, l\'agence dispose de 48 heures pour apporter une solution. Passé ce délai sans accord, la réclamation est automatiquement escaladée aux administrateurs de la plateforme pour arbitrage et éventuel remboursement.') }}
        </div>
    </div>

    <div class="card card-glass" style="padding: 0; overflow: hidden;">
        @if($disputes->isEmpty())
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fa-solid fa-headset" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 10px;"></i>
                <h3 style="font-size: 1.1rem; color: var(--text-heading);">{{ __('Aucune réclamation enregistrée') }}</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem;">{{ __('Vos voyages et expéditions se déroulent sans incident signalé.') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('N° Dossier') }}</th>
                            <th>{{ __('Agence Concernée') }}</th>
                            <th>{{ __('Objet') }}</th>
                            <th>{{ __('Catégorie') }}</th>
                            <th>{{ __('Délai 48h') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disputes as $d)
                            <tr>
                                <td>
                                    <strong style="color: var(--primary);">{{ $d->dispute_code }}</strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $d->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700;">Real Voyage ({{ $d->branch->name ?? __('Gare Centrale') }})</div>
                                </td>
                                <td>
                                    <strong>{{ $d->title }}</strong>
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
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> {{ __('Traité') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($d->status === 'resolved')
                                        <span class="badge badge-success">{{ __('Résolu') }}</span>
                                    @elseif($d->status === 'in_review')
                                        <span class="badge badge-road">{{ __('En cours') }}</span>
                                    @elseif($d->status === 'rejected')
                                        <span class="badge badge-danger">{{ __('Rejeté') }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ __('Ouvert') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('passenger.disputes.show', $d) }}" class="btn btn-sm btn-outline">
                                        {{ __('Consulter') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px;">
                {{ $disputes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
