@extends('layouts.dashboard')

@section('title', __('Contrôle & Embarquement des Passagers (Guichet)'))

@section('dashboard_content')
<div>
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.6rem; color: var(--text-heading); margin-bottom: 4px;">
            {{ __('Guichet & Contrôle d\'Embarquement') }}
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            {{ __('Scannez le QR Code ou saisissez la référence du billet pour valider la montée à bord des passagers.') }}
        </p>
    </div>

    <!-- Search & Scanner Container -->
    <div class="card card-glass" style="padding: 28px; margin-bottom: 24px; box-shadow: var(--shadow-md);">
        <form action="{{ route('manager.checkin.index') }}" method="GET" style="max-width: 650px; margin: 0 auto;" id="checkinSearchForm">
            <label class="form-label" style="text-align: center; font-size: 1rem; margin-bottom: 14px; display: block;">
                <i class="fa-solid fa-qrcode" style="color: var(--primary);"></i> {{ __('Référence du Billet ou Token QR Code') }}
            </label>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="q" id="ticketInput" value="{{ $search }}" class="form-control" placeholder="{{ __('Ex: BK-2026-CM8921 ou QR-TK-...') }}" required autofocus style="text-transform: uppercase; font-weight: 700; font-size: 1.1rem; text-align: center; flex: 1;">
                
                <button type="button" onclick="startCameraScanner()" class="btn btn-secondary" style="padding: 0 18px; font-weight: 700;" title="{{ __('Scanner avec la caméra') }}">
                    <i class="fa-solid fa-camera"></i> {{ __('Caméra') }}
                </button>

                <button type="submit" class="btn btn-primary" style="padding: 0 24px; font-weight: 700;">
                    <i class="fa-solid fa-magnifying-glass"></i> {{ __('Vérifier') }}
                </button>
            </div>
        </form>

        <!-- Live Camera Scanner Overlay (Hidden by default) -->
        <div id="cameraScannerBox" style="display: none; max-width: 480px; margin: 20px auto 0; text-align: center; border: 2px dashed var(--primary); border-radius: var(--radius-lg); padding: 16px; background: var(--bg-surface);">
            <div style="font-weight: 800; font-size: 0.9rem; color: var(--primary); margin-bottom: 10px;">
                <i class="fa-solid fa-video"></i> {{ __('Alignez le QR Code du billet face à la caméra') }}
            </div>
            <video id="scannerVideo" style="width: 100%; height: 260px; object-fit: cover; border-radius: var(--radius-md); background: #000;" playsinline></video>
            <div style="margin-top: 12px; display: flex; justify-content: center; gap: 10px;">
                <button type="button" onclick="stopCameraScanner()" class="btn btn-sm btn-outline">
                    <i class="fa-solid fa-xmark"></i> {{ __('Fermer la caméra') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Check-in Result Card -->
    @if($search)
        @if($booking)
            <div class="card card-glass" style="padding: 32px; border: 2px solid {{ $booking->isCheckedIn() ? '#10B981' : ($booking->isConfirmed() ? 'var(--primary)' : 'var(--danger)') }}; box-shadow: var(--shadow-lg);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">{{ __('BILLET N°') }} {{ $booking->booking_reference }}</span>
                        <h2 style="font-size: 1.6rem; color: var(--text-heading); margin: 4px 0;">
                            {{ $booking->passenger->name ?? __('Passager') }}
                        </h2>
                        <div style="font-size: 0.9rem; color: var(--text-muted);">
                            {{ __('Tél :') }} <strong>{{ $booking->passenger->phone }}</strong> • {{ __('E-mail :') }} {{ $booking->passenger->email }}
                        </div>
                    </div>

                    <div style="text-align: right;">
                        @if($booking->isCheckedIn())
                            <span class="badge badge-success" style="font-size: 1rem; padding: 6px 16px;">
                                <i class="fa-solid fa-circle-check"></i> {{ __('PASSAGER EMBARQUÉ') }}
                            </span>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                {{ __('Validé à') }} {{ $booking->checked_in_at ? $booking->checked_in_at->format('H:i:s') : '' }}
                            </div>
                        @elseif($booking->isConfirmed())
                            <span class="badge badge-road" style="font-size: 1rem; padding: 6px 16px;">
                                <i class="fa-solid fa-ticket"></i> {{ __('BILLET VALIDE (PAYÉ)') }}
                            </span>
                        @else
                            <span class="badge badge-danger" style="font-size: 1rem; padding: 6px 16px;">
                                {{ __('STATUT :') }} {{ strtoupper($booking->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Trip details -->
                <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px; font-size: 0.9rem;">
                    <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Trajet') }}</div>
                        <div style="font-weight: 800; color: var(--text-heading);">{{ $booking->trip->departure_city }} &rarr; {{ $booking->trip->arrival_city }}</div>
                    </div>

                    <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Date & Heure') }}</div>
                        <div style="font-weight: 800; color: var(--primary);">{{ $booking->trip->departure_time->format('d/m/Y H:i') }}</div>
                    </div>

                    <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Sièges Réservés') }}</div>
                        <div style="font-weight: 900; color: var(--primary); font-size: 1.1rem;">
                            {{ implode(', ', $booking->seat_numbers ?? []) }}
                        </div>
                    </div>

                    <div style="background: var(--bg-surface); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ __('Classe') }}</div>
                        <div style="font-weight: 800; color: var(--text-heading);">{{ __($booking->tripClass->class_name ?? ucfirst($booking->transport_class)) }}</div>
                    </div>
                </div>

                <!-- Passenger group list -->
                @if(!empty($booking->passengers_data))
                    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 24px;">
                        <div style="font-weight: 700; font-size: 0.85rem; margin-bottom: 8px; color: var(--text-muted);">{{ __('Passagers enregistrés sous ce billet :') }}</div>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 6px;">
                            @foreach($booking->passengers_data as $idx => $p)
                                <li style="font-size: 0.9rem; color: var(--text-heading);">
                                    <i class="fa-solid fa-user-check" style="color: var(--primary);"></i> <strong>{{ $p['name'] }}</strong> ({{ __('CNI :') }} {{ $p['cni'] ?? __('Non renseignée') }}) - {{ __('Tél :') }} {{ $p['phone'] ?? '-' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Validation Action Button -->
                @if(!$booking->isCheckedIn() && $booking->isConfirmed())
                    <form action="{{ route('manager.checkin.process', $booking) }}" method="POST" onsubmit="playSuccessBeep()">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 900; font-size: 1.2rem; height: 56px;">
                            <i class="fa-solid fa-circle-check"></i> {{ __('VALIDER L\'EMBARQUEMENT DU PASSAGER') }}
                        </button>
                    </form>
                @elseif($booking->isCheckedIn())
                    <div style="text-align: center; background: rgba(16, 185, 129, 0.12); border: 1px solid #10B981; border-radius: var(--radius-md); padding: 16px; color: #10B981; font-weight: 800;">
                        <i class="fa-solid fa-check-double"></i> {{ __('Passager déjà enregistré et monté à bord.') }}
                    </div>
                @else
                    <div style="text-align: center; background: rgba(239, 68, 68, 0.12); border: 1px solid var(--danger); border-radius: var(--radius-md); padding: 16px; color: var(--danger); font-weight: 800;">
                        <i class="fa-solid fa-ban"></i> {{ __('Impossible d\'embarquer : le paiement n\'a pas été validé.') }}
                    </div>
                @endif
            </div>
        @else
            <div class="card card-glass" style="text-align: center; padding: 40px; color: var(--secondary);">
                <i class="fa-solid fa-circle-xmark" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                <h3 style="font-size: 1.2rem; color: var(--text-heading);">{{ __('Billet introuvable pour votre agence') }}</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem;">{{ __('Vérifiez la référence saisie ou assurez-vous que le billet appartient bien à vos trajets.') }}</p>
            </div>
        @endif
    @endif
</div>

<script>
let videoStream = null;

function playSuccessBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime); // High pitch confirm A5
        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.25);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.25);
    } catch(e) {}
}

function startCameraScanner() {
    const box = document.getElementById('cameraScannerBox');
    const video = document.getElementById('scannerVideo');
    box.style.display = 'block';

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                videoStream = stream;
                video.srcObject = stream;
                video.play();
            })
            .catch(err => {
                alert("{{ __('Accès caméra non disponible sur cet appareil. Veuillez saisir la référence manuellement.') }}");
                box.style.display = 'none';
            });
    }
}

function stopCameraScanner() {
    const box = document.getElementById('cameraScannerBox');
    if (videoStream) {
        videoStream.getTracks().forEach(t => t.stop());
        videoStream = null;
    }
    box.style.display = 'none';
}
</script>
@endsection
