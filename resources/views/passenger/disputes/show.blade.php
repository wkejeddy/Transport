@extends('layouts.app')

@section('title', __('Dossier de Litige - :code', ['code' => $dispute->dispute_code]))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Top Back Link -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('passenger.disputes.index') }}" style="color: var(--text-muted); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Retour à mes réclamations') }}
        </a>
    </div>

    <!-- Dispute Header Card -->
    <div class="card card-glass" style="padding: 28px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted);">{{ __('DOSSIER N°') }} {{ $dispute->dispute_code }}</span>
                <h1 style="font-size: 1.5rem; color: var(--text-heading); margin: 4px 0;">
                    {{ $dispute->title }}
                </h1>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    {{ __('Gare / Agence concernée :') }} <strong>Real Voyage - {{ $dispute->branch->name ?? __('Direction') }}</strong> • {{ __('Ouvert le') }} {{ $dispute->created_at->format('d/m/Y') }} {{ __('à') }} {{ $dispute->created_at->format('H:i') }}
                </div>
            </div>

            <div style="text-align: right;">
                @if($dispute->status === 'resolved')
                    <span class="badge badge-success" style="font-size: 0.85rem; padding: 6px 14px;"><i class="fa-solid fa-check"></i> {{ __('Dossier Résolu') }}</span>
                @elseif($dispute->escalated)
                    <span class="badge badge-danger" style="font-size: 0.85rem; padding: 6px 14px;"><i class="fa-solid fa-bolt"></i> {{ __('Escaladé à l\'Admin') }}</span>
                @else
                    <span class="badge badge-warning" style="font-size: 0.85rem; padding: 6px 14px;"><i class="fa-solid fa-clock"></i> {{ __('En attente réponse agence') }}</span>
                @endif
            </div>
        </div>

        <!-- 48-Hour Escalation Countdown Banner -->
        @if(!$dispute->escalated && $dispute->status === 'open')
            <div style="background: var(--warning-50); border: 1px solid var(--warning-border); border-radius: var(--radius-md); padding: 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 0.85rem; color: var(--warning-text);">
                    <i class="fa-solid fa-stopwatch"></i> {{ __('Temps restant pour traitement par la direction de gare :') }}
                </div>
                <div style="font-weight: 900; font-size: 1.1rem; color: var(--warning-text);">
                    {{ $dispute->hours_remaining }} {{ __('heures') }}
                </div>
            </div>
        @endif

        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
            <div style="font-weight: 700; font-size: 0.9rem; margin-bottom: 6px; color: var(--text-muted);">{{ __('Votre déclaration initiale :') }}</div>
            <p style="font-size: 0.95rem; color: var(--text-heading); line-height: 1.6; margin: 0;">
                {{ $dispute->description }}
            </p>
        </div>

        <!-- Manager Response (if any) -->
        @if($dispute->manager_response)
            <div style="background: var(--accent-50); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--accent-text);">
                        <i class="fa-solid fa-reply"></i> {{ __('Réponse du Chef de Gare (:branch) :', ['branch' => $dispute->branch->name ?? 'Real Voyage']) }}
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                        {{ $dispute->manager_responded_at ? $dispute->manager_responded_at->format('d/m/Y H:i') : '' }}
                    </span>
                </div>
                <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.6; margin: 0;">
                    {{ $dispute->manager_response }}
                </p>
            </div>
        @endif

        <!-- Admin Arbitration Ruling (if any) -->
        @if($dispute->admin_notes)
            <div style="background: var(--success-50); border: 2px solid var(--success-border); border-radius: var(--radius-md); padding: 18px;">
                <div style="font-weight: 800; font-size: 0.95rem; color: var(--success-text); margin-bottom: 6px;">
                    <i class="fa-solid fa-gavel"></i> {{ __('Décision de l\'Administration Plateforme TransportCM :') }}
                </div>
                <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.6; margin: 0;">
                    {{ $dispute->admin_notes }}
                </p>
                <div style="font-size: 0.75rem; color: var(--success-text); margin-top: 6px;">
                    {{ __('Arbitré le') }} {{ $dispute->admin_resolved_at ? $dispute->admin_resolved_at->format('d/m/Y') . ' ' . __('à') . ' ' . $dispute->admin_resolved_at->format('H:i') : '' }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
