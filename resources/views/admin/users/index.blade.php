@extends('layouts.dashboard')

@section('title', __('Gestion des Utilisateurs - Administration'))

@section('dashboard_content')
<div>
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
            {{ __('Gestion des Utilisateurs de la Plateforme') }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            {{ __('Administrez les comptes Passagers, Managers d\'Agences et Administrateurs.') }}
        </p>
    </div>

    <!-- Filter Card -->
    <div class="card" style="padding: 20px; margin-bottom: 24px;">
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Rechercher par nom, e-mail ou téléphone...') }}">
                </div>
                <div style="width: 200px;">
                    <select name="role" class="form-control">
                        <option value="">{{ __('Tous les rôles') }}</option>
                        <option value="passager" {{ request('role') === 'passager' ? 'selected' : '' }}>{{ __('Passagers') }}</option>
                        <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>{{ __('Managers d\'Agences') }}</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('Administrateurs') }}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> {{ __('Filtrer') }}
                </button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Nom Utilisateur') }}</th>
                        <th>{{ __('E-mail') }}</th>
                        <th>{{ __('Téléphone') }}</th>
                        <th>{{ __('Rôle') }}</th>
                        <th>{{ __('Gare / Terminal') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td><strong>{{ $u->name }}</strong></td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone ?? '-' }}</td>
                            <td>
                                @if($u->isAdmin())
                                    <span class="badge badge-danger"><i class="fa-solid fa-shield"></i> {{ __('Admin') }}</span>
                                @elseif($u->isManager())
                                    <span class="badge badge-road"><i class="fa-solid fa-building"></i> {{ __('Manager') }}</span>
                                @else
                                    <span class="badge badge-success"><i class="fa-solid fa-user"></i> {{ __('Passager') }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $u->branch->name ?? '-' }}
                            </td>
                            <td>
                                @if($u->status === 'active')
                                    <span class="badge badge-success">{{ __('Actif') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('Suspendu') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(!$u->isAdmin())
                                    <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline" style="color: {{ $u->status === 'active' ? 'var(--secondary)' : 'var(--primary)' }};">
                                            {{ $u->status === 'active' ? __('Suspendre') : __('Réactiver') }}
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Protégé') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
