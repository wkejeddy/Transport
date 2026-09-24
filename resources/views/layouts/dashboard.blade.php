@extends('layouts.app')

@section('content')
<div class="dashboard-layout">
    <!-- Sidebar Backdrop for Mobile -->
    <div class="mobile-drawer-overlay" id="sidebarBackdrop"></div>

    <!-- Collapsible Responsive Sidebar -->
    <aside class="sidebar" id="dashboardSidebar">
        <div>
            <!-- User Profile Summary Header in Sidebar -->
            <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; position: relative;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; box-shadow: var(--shadow-sm); overflow: hidden; flex-shrink: 0;">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <div style="overflow: hidden; flex: 1;">
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ Auth::user()->name }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                        @if(Auth::user()->isAdmin())
                            <span class="badge badge-danger" style="font-size: 0.65rem; padding: 2px 6px;">{{ __('Administrateur') }}</span>
                        @elseif(Auth::user()->isManager())
                            <span class="badge badge-road" style="font-size: 0.65rem; padding: 2px 6px;">{{ __('Manager Agence') }}</span>
                        @else
                            <span class="badge badge-success" style="font-size: 0.65rem; padding: 2px 6px;">{{ __('Passager') }}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" title="{{ __('Modifier mon profil') }}" style="color: var(--text-muted); font-size: 0.9rem; padding: 6px; border-radius: 50%;" class="btn-icon">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>

            <!-- Role-Specific Sidebar Menu Links -->
            <ul class="sidebar-menu">
                @if(Auth::user()->isAdmin())
                    <!-- Administrator Menu -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i> {{ __('Supervision Générale') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.trips.index') }}" class="sidebar-link {{ request()->routeIs('manager.trips.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-route"></i> {{ __('Voyages & Horaires') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.vehicles.index') }}" class="sidebar-link {{ request()->routeIs('manager.vehicles.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-bus"></i> {{ __('Flotte & Autocars') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.checkin.index') }}" class="sidebar-link {{ request()->routeIs('manager.checkin.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-qrcode"></i> {{ __('Guichet & Embarquement') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.shipments.index') }}" class="sidebar-link {{ request()->routeIs('manager.shipments.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> {{ __('Fret & Colis') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.disputes.index') }}" class="sidebar-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-headset"></i> {{ __('Service Client & Réclamations') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear"></i> {{ __('Personnel & Utilisateurs') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.branch.edit') }}" class="sidebar-link {{ request()->routeIs('manager.branch.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-building-flag"></i> {{ __('Direction de Gare') }}
                        </a>
                    </li>
                @elseif(Auth::user()->isManager())
                    <!-- Transport Agency Manager Menu -->
                    <li>
                        <a href="{{ route('manager.dashboard') }}" class="sidebar-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-gauge-high"></i> {{ __('Tableau de Bord') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.trips.index') }}" class="sidebar-link {{ request()->routeIs('manager.trips.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-route"></i> {{ __('Voyages & Départs') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.vehicles.index') }}" class="sidebar-link {{ request()->routeIs('manager.vehicles.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-bus"></i> {{ __('Parc Autocars (75/80 pl.)') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.checkin.index') }}" class="sidebar-link {{ request()->routeIs('manager.checkin.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-qrcode"></i> {{ __('Guichet & Embarquement') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.shipments.index') }}" class="sidebar-link {{ request()->routeIs('manager.shipments.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> {{ __('Fret & Colis (OTP)') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.disputes.index') }}" class="sidebar-link {{ request()->routeIs('manager.disputes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-headset"></i> {{ __('Réclamations Client') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('manager.branch.edit') }}" class="sidebar-link {{ request()->routeIs('manager.branch.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-building-flag"></i> {{ __('Direction de Gare') }}
                        </a>
                    </li>
                @else
                    <!-- Passenger User Menu -->
                    <li>
                        <a href="{{ route('passenger.dashboard') }}" class="sidebar-link {{ request()->routeIs('passenger.dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-house-user"></i> {{ __('Mon Espace') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('passenger.bookings.history') }}" class="sidebar-link {{ request()->routeIs('passenger.bookings.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-ticket"></i> {{ __('Mes Billets & Voyages') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('passenger.shipments.index') }}" class="sidebar-link {{ request()->routeIs('passenger.shipments.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-box-open"></i> {{ __('Mes Envois de Colis') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('passenger.disputes.index') }}" class="sidebar-link {{ request()->routeIs('passenger.disputes.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-triangle-exclamation"></i> {{ __('Réclamations & Litiges') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('trips.index') }}" class="sidebar-link">
                            <i class="fa-solid fa-magnifying-glass"></i> {{ __('Réserver un Trajet') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('passenger.shipments.create') }}" class="sidebar-link">
                            <i class="fa-solid fa-paper-plane"></i> {{ __('Expédier un Colis') }}
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Sidebar Footer Action -->
        <div style="border-top: 1px solid var(--border-color); padding-top: 16px; display: flex; flex-direction: column; gap: 4px;">
            <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear"></i> {{ __('Mon Profil & Sécurité') }}
            </a>
            <a href="{{ route('home') }}" class="sidebar-link">
                <i class="fa-solid fa-arrow-left"></i> {{ __('Retour au Site Public') }}
            </a>
        </div>
    </aside>

    <!-- Main Dashboard Workspace -->
    <div class="dashboard-content">
        <!-- Mobile Sidebar Open Toggle Button -->
        <div class="sidebar-mobile-toggle no-print">
            <button type="button" id="sidebarToggleBtn" class="btn btn-sm btn-outline">
                <i class="fa-solid fa-bars-staggered"></i> {{ __('Menu Dashboard') }}
            </button>
        </div>

        @yield('dashboard_content')
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebar = document.getElementById('dashboardSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (toggleBtn && sidebar && backdrop) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                backdrop.classList.toggle('active');
            });

            backdrop.addEventListener('click', function() {
                sidebar.classList.remove('active');
                backdrop.classList.remove('active');
            });
        }
    });
</script>
@endsection
