@extends('layouts.app')

@section('content')
<div class="dashboard-layout">
    <!-- Sidebar Backdrop for Mobile -->
    <div class="mobile-drawer-overlay" id="sidebarBackdrop"></div>

    <!-- Collapsible Responsive Sidebar -->
    <aside class="sidebar" id="dashboardSidebar">
        <div>
            <!-- Bankio Brand Header with Agency Chevron -->
            <div class="bankio-sidebar-brand">
                <a href="{{ route('home') }}" class="bankio-brand-title">
                    <img src="{{ asset('images/logo.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="Real Voyage" style="width: 32px; height: 32px; object-fit: contain;">
                    <span>Real Voyage</span>
                </a>
                <span style="font-size: 0.75rem; color: var(--bankio-text-muted); cursor: pointer;" title="{{ __('Agence active') }}">
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </div>

            <!-- User Quick Profile Pill in Sidebar -->
            <div style="background: var(--bankio-pill-bg); border-radius: 12px; padding: 10px 12px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--bankio-emerald); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem; overflow: hidden; flex-shrink: 0;">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <div style="overflow: hidden; flex: 1;">
                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--bankio-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ Auth::user()->name }}
                    </div>
                    <div style="font-size: 0.7rem; color: var(--bankio-text-muted);">
                        @if(Auth::user()->isAdmin())
                            <span class="badge badge-danger" style="font-size: 0.6rem; padding: 1px 5px;">{{ __('Administrateur') }}</span>
                        @elseif(Auth::user()->isManager())
                            <span class="badge badge-road" style="font-size: 0.6rem; padding: 1px 5px;">{{ __('Manager Agence') }}</span>
                        @else
                            <span class="badge badge-success" style="font-size: 0.6rem; padding: 1px 5px;">{{ __('Passager') }}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" title="{{ __('Modifier mon profil') }}" style="color: var(--bankio-text-muted); font-size: 0.85rem; padding: 4px;">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>

            <!-- MAIN MENU Header -->
            <div class="bankio-section-heading">{{ __('MAIN MENU') }}</div>

            <!-- Role-Specific Sidebar Menu Links -->
            <div style="display: flex; flex-direction: column; gap: 2px;">
                @if(Auth::user()->isAdmin())
                    <!-- Administrator Menu -->
                    <a href="{{ route('admin.dashboard') }}" class="bankio-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Dashboard & Analytics') }}</span>
                    </a>
                    <a href="{{ route('manager.trips.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.trips.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-route" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Voyages & Horaires') }}</span>
                    </a>
                    <a href="{{ route('manager.vehicles.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.vehicles.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bus" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Flotte & Autocars') }}</span>
                    </a>
                    <a href="{{ route('manager.checkin.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.checkin.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-qrcode" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Guichet & Embarquement') }}</span>
                    </a>
                    <a href="{{ route('manager.shipments.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.shipments.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Fret & Colis Express') }}</span>
                    </a>
                    <a href="{{ route('admin.disputes.index') }}" class="bankio-menu-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-headset" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Service Client') }}</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="bankio-menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Personnel & Gares') }}</span>
                    </a>
                @elseif(Auth::user()->isManager())
                    <!-- Transport Agency Manager Menu -->
                    <a href="{{ route('manager.dashboard') }}" class="bankio-menu-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Tableau de Bord') }}</span>
                    </a>
                    <a href="{{ route('manager.trips.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.trips.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-route" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Voyages & Départs') }}</span>
                    </a>
                    <a href="{{ route('manager.vehicles.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.vehicles.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bus" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Parc Autocars (75/80 pl.)') }}</span>
                    </a>
                    <a href="{{ route('manager.checkin.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.checkin.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-qrcode" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Guichet & Embarquement') }}</span>
                    </a>
                    <a href="{{ route('manager.shipments.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.shipments.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Fret & Colis (OTP)') }}</span>
                    </a>
                    <a href="{{ route('manager.disputes.index') }}" class="bankio-menu-link {{ request()->routeIs('manager.disputes.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-headset" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Réclamations Client') }}</span>
                    </a>
                    <a href="{{ route('manager.branch.edit') }}" class="bankio-menu-link {{ request()->routeIs('manager.branch.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-building-flag" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Direction de Gare') }}</span>
                    </a>
                @else
                    <!-- Passenger User Menu -->
                    <a href="{{ route('passenger.dashboard') }}" class="bankio-menu-link {{ request()->routeIs('passenger.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-house-user" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Mon Espace') }}</span>
                    </a>
                    <a href="{{ route('passenger.bookings.history') }}" class="bankio-menu-link {{ request()->routeIs('passenger.bookings.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-ticket" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Mes Billets & Voyages') }}</span>
                    </a>
                    <a href="{{ route('passenger.shipments.index') }}" class="bankio-menu-link {{ request()->routeIs('passenger.shipments.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box-open" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Mes Envois de Colis') }}</span>
                    </a>
                    <a href="{{ route('passenger.disputes.index') }}" class="bankio-menu-link {{ request()->routeIs('passenger.disputes.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-triangle-exclamation" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Réclamations') }}</span>
                    </a>
                @endif
            </div>

            <!-- OTHERS Header -->
            <div class="bankio-section-heading" style="margin-top: 14px;">{{ __('OTHERS') }}</div>
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <a href="{{ route('profile.edit') }}" class="bankio-menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved" style="width: 18px; text-align: center;"></i>
                    <span>{{ __('Sécurité & Profil') }}</span>
                </a>
                <a href="{{ route('home') }}" class="bankio-menu-link">
                    <i class="fa-solid fa-house" style="width: 18px; text-align: center;"></i>
                    <span>{{ __('Site Public') }}</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin-top: 4px;">
                    @csrf
                    <button type="submit" class="bankio-menu-link" style="width: 100%; border: none; background: transparent; cursor: pointer; color: var(--danger); font-family: inherit; font-size: inherit; text-align: left;">
                        <i class="fa-solid fa-power-off" style="width: 18px; text-align: center;"></i>
                        <span>{{ __('Se Déconnecter') }}</span>
                    </button>
                </form>
            </div>
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
