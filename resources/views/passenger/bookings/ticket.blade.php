@extends('layouts.app')

@section('title', __('Billet Électronique - :ref', ['ref' => $booking->booking_reference]))

@section('content')
<div class="container-sm" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Top Action Bar -->
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('passenger.bookings.history') }}" style="color: var(--text-muted); font-weight: 600; font-size: 0.9rem;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('Mes réservations') }}
        </a>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('passenger.bookings.pdf', $booking) }}" class="btn btn-outline" target="_blank">
                <i class="fa-solid fa-file-pdf" style="color: var(--secondary);"></i> {{ __('Télécharger PDF') }}
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa-solid fa-print"></i> {{ __('Imprimer le Billet') }}
            </button>
        </div>
    </div>

    <!-- Official Boarding Pass Ticket -->
    <div class="ticket-container" style="background: var(--bg-card); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-xl); border: 2px solid var(--border-color); position: relative;">
        <!-- Header Banner -->
        <div style="background: var(--primary); color: white; padding: 24px 30px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fa-solid fa-bus"></i>
                </div>
                <div>
                    <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 800; letter-spacing: -0.02em;">
                        Real Voyage S.A.
                    </div>
                    <div style="font-size: 0.8rem; color: #E2E8F0;">
                        {{ __('Titre de Transport Routier Homologué • Gare de') }} {{ $booking->trip->branch->name ?? 'Douala' }}
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <span class="badge" style="background: white; color: var(--primary); font-size: 0.85rem; padding: 4px 14px; font-weight: 800;">
                    {{ __('CONFIRMÉ') }}
                </span>
                <div style="font-size: 0.75rem; color: #E2E8F0; margin-top: 4px;">
                    {{ __('RÉF :') }} <strong>{{ $booking->booking_reference }}</strong>
                </div>
            </div>
        </div>

        <!-- Ticket Body -->
        <div style="padding: 32px 30px;">
            <!-- Itinerary Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px dashed var(--border-color);">
                <div>
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ __('Origine') }}</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-heading); line-height: 1.1;">{{ $booking->trip->departure_city }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">{{ $booking->trip->departure_station }}</div>
                </div>

                <div style="text-align: center;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary);"><i class="fa-solid fa-arrow-right-long" style="font-size: 1.8rem;"></i></div>
                    <span class="badge badge-road" style="font-size: 0.7rem;">
                        {{ __($booking->tripClass->class_name ?? ucfirst($booking->transport_class)) }}
                    </span>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ __('Destination') }}</div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: var(--text-heading); line-height: 1.1;">{{ $booking->trip->arrival_city }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">{{ $booking->trip->arrival_station }}</div>
                </div>
            </div>

            <!-- Schedule & Seat Allocation Grid -->
            <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
                <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Programme & Horaires') }}</div>
                    <div style="font-weight: 900; font-size: 1.05rem; color: var(--text-heading);">{{ $booking->trip->departure_time->format('d/m/Y') }}</div>
                    <div style="font-size: 0.85rem; color: var(--primary); font-weight: 800;">
                        {{ __('Départ :') }} {{ $booking->trip->departure_time->format('H:i') }}
                    </div>
                    <div style="font-size: 0.72rem; color: #059669; font-weight: 700; margin-top: 2px;">
                        <i class="fa-solid fa-clock"></i> {{ __('Convocation :') }} {{ $booking->trip->departure_time->copy()->subMinutes(45)->format('H:i') }}
                    </div>
                </div>

                <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Sièges Attribués') }}</div>
                    <div style="font-weight: 900; font-size: 1.1rem; color: var(--primary);">
                        {{ implode(', ', $booking->seat_numbers ?? []) }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $booking->seats_count }} {{ __('place(s) réservée(s)') }}</div>
                </div>

                <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Passager Titulaire') }}</div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-heading);">{{ $booking->passenger->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Tél :') }} {{ $booking->passenger->phone }}</div>
                </div>

                <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">{{ __('Montant Réglé') }}</div>
                    <div style="font-weight: 900; font-size: 1.15rem; color: var(--primary);">
                        {{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA
                    </div>
                    <div style="font-size: 0.7rem; color: #059669; font-weight: 700;">{{ __('Payé Mobile Money') }}</div>
                </div>
            </div>

            <!-- Passenger Manifest Table (if group booking) -->
            @if(!empty($booking->passengers_data))
                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 24px;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">
                        {{ __('Passagers et Places Attribuées pour ce Titre :') }}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem; color: var(--text-main);">
                        @foreach($booking->passengers_data as $idx => $p)
                            @php
                                $assignedSeat = $booking->seat_numbers[$idx] ?? null;
                            @endphp
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-color); padding-bottom: 6px;">
                                <div>
                                    <i class="fa-solid fa-user-check" style="color: var(--primary);"></i>
                                    <strong>{{ $p['name'] }}</strong> • {{ __('CNI :') }} {{ $p['cni'] ?? __('Présenter pièce') }} • {{ __('Tél :') }} {{ $p['phone'] ?? '-' }}
                                </div>
                                @if($assignedSeat)
                                    <div>
                                        <span class="badge badge-primary" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-chair"></i> {{ __('Place :') }} <strong>{{ $assignedSeat }}</strong>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- QR Code Security Stub -->
            <div style="border-top: 2px dashed var(--border-color); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- Visual QR Code Mockup -->
                    <div style="width: 90px; height: 90px; background: white; border: 2px solid var(--border-color); padding: 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-qrcode" style="font-size: 4.5rem; color: #111827;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 900; font-size: 0.95rem; color: var(--text-heading);">{{ __('Pass Sécurisé d\'Embarquement') }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Jeton :') }} {{ $booking->qr_code_token }}</div>
                        <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700; margin-top: 4px;">
                            <i class="fa-solid fa-shield"></i> {{ __('Valable avec une pièce d\'identité officielle (CNI)') }}
                        </div>
                    </div>
                </div>

                <div style="text-align: right; font-size: 0.75rem; color: var(--text-muted);">
                    {{ __('Embarquement 30 minutes avant le départ.') }}<br>
                    {{ __('Service Clientèle Real Voyage :') }} <strong>+237 670 00 00 00</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
