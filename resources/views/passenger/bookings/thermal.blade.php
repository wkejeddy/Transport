<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Thermique 80mm - {{ $booking->booking_reference }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f4f4f4;
            color: #000;
            font-size: 12px;
            line-height: 1.35;
        }
        .thermal-wrapper {
            width: 80mm;
            max-width: 80mm;
            margin: 20px auto;
            background: #fff;
            padding: 12px 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            border: 1px solid #ddd;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .logo-title {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 9px;
            text-transform: uppercase;
        }
        .seat-badge {
            display: inline-block;
            font-size: 16px;
            font-weight: 900;
            padding: 3px 6px;
            border: 2px solid #000;
            margin: 4px 0;
        }
        .qr-box {
            margin: 10px auto;
            text-align: center;
        }
        .qr-mock {
            display: inline-block;
            padding: 8px;
            border: 2px solid #000;
            font-size: 10px;
            word-break: break-all;
            max-width: 100%;
        }
        .notice {
            font-size: 9px;
            line-height: 1.25;
            text-align: justify;
        }
        .btn-actions {
            text-align: center;
            margin: 15px auto;
            width: 80mm;
        }
        .btn {
            background: #000;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-family: inherit;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }
        @media print {
            body {
                background: transparent;
            }
            .thermal-wrapper {
                width: 80mm;
                max-width: 80mm;
                margin: 0;
                padding: 5px 4px;
                box-shadow: none;
                border: none;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="btn-actions no-print">
    <button onclick="window.print()" class="btn">🖨️ {{ __('Imprimer Format Thermique (80mm)') }}</button>
    <a href="{{ route('passenger.bookings.ticket', $booking) }}" style="display:inline-block; margin-top:8px; font-size:11px; color:#333; text-decoration:none;">← {{ __('Retour au billet standard') }}</a>
</div>

<div class="thermal-wrapper">
    <!-- Header -->
    <div class="text-center">
        <div class="logo-title">REAL EXPRESS VOYAGES</div>
        <div class="subtitle">Transport Interurbain & Fret National</div>
        <div style="font-size: 10px;">{{ $booking->trip->branch->name ?? 'Direction Régionale Cameroun' }}</div>
        <div style="font-size: 10px;">Tél: +237 670 000 001 / +237 699 000 002</div>
    </div>

    <div class="double-divider"></div>

    <!-- Booking Ref & Status -->
    <div class="text-center">
        <div style="font-size: 11px; font-weight: bold;">TITRE DE TRANSPORT • BILLET ELECTRONIQUE</div>
        <div style="font-size: 15px; font-weight: 900; margin: 3px 0;">REF: {{ $booking->booking_reference }}</div>
        <div style="font-size: 10px;">Émis le: {{ $booking->created_at->format('d/m/Y H:i:s') }}</div>
        <div style="font-size: 10px;">Statut: <strong>{{ strtoupper($booking->status) }}</strong></div>
    </div>

    <div class="divider"></div>

    <!-- Trip Info -->
    <div>
        <div class="row">
            <span class="font-bold">DEPART:</span>
            <span class="font-bold">{{ strtoupper($booking->trip->departure_city) }}</span>
        </div>
        <div style="font-size: 10px; margin-bottom: 4px;">Gare: {{ $booking->trip->departure_station }}</div>

        <div class="row">
            <span class="font-bold">DESTINATION:</span>
            <span class="font-bold">{{ strtoupper($booking->trip->arrival_city) }}</span>
        </div>
        <div style="font-size: 10px; margin-bottom: 4px;">Gare: {{ $booking->trip->arrival_station }}</div>

        <div class="divider"></div>

        <div class="row">
            <span>DATE DEPART:</span>
            <span class="font-bold">{{ $booking->trip->departure_time->format('d/m/Y') }}</span>
        </div>
        <div class="row">
            <span>HEURE DEPART:</span>
            <span class="font-bold" style="font-size: 13px;">{{ $booking->trip->departure_time->format('H:i') }}</span>
        </div>
        <div class="row">
            <span>CONVOCATION:</span>
            <span class="font-bold">{{ $booking->trip->departure_time->copy()->subMinutes(45)->format('H:i') }}</span>
        </div>
        <div class="row">
            <span>CLASSE:</span>
            <span>{{ strtoupper($booking->tripClass->class_name ?? $booking->transport_class) }}</span>
        </div>
        <div class="row">
            <span>AUTOCAR N°:</span>
            <span>{{ $booking->trip->vehicle->code ?? $booking->trip->vehicle->registration_plate ?? 'AUTOCAR-RV' }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Seats & Passenger -->
    <div class="text-center">
        <div style="font-size: 10px;">SIEGE(S) ASSIGNE(S):</div>
        <div class="seat-badge">
            {{ implode(', ', $booking->seat_numbers ?? ['S-01']) }}
        </div>
        <div style="font-size: 10px;">Total Places: {{ $booking->seats_count }}</div>
    </div>

    <div class="divider"></div>

    <!-- Passenger Details -->
    <div>
        <div class="font-bold" style="margin-bottom: 2px;">PASSAGER(S):</div>
        @php
            $passList = !empty($booking->passengers_data) ? $booking->passengers_data : [['name' => $booking->passenger->name ?? 'Passager', 'cni' => 'N/A', 'phone' => $booking->passenger->phone ?? 'N/A']];
        @endphp
        @foreach($passList as $idx => $p)
            <div style="font-size: 11px; font-weight: bold;">
                {{ $idx + 1 }}. {{ strtoupper($p['name'] ?? 'PASSAGER') }}
            </div>
            <div style="font-size: 10px; margin-bottom: 2px;">
                CNI: {{ $p['cni'] ?? 'N/A' }} | Tél: {{ $p['phone'] ?? 'N/A' }}
            </div>
        @endforeach
    </div>

    <div class="divider"></div>

    <!-- Payment Breakdown -->
    <div>
        <div class="row">
            <span>Tarif unitaire:</span>
            <span>{{ number_format($booking->tripClass->price ?? $booking->trip->base_price, 0, ',', ' ') }} XAF</span>
        </div>
        <div class="row">
            <span>Nbre de places:</span>
            <span>x {{ $booking->seats_count }}</span>
        </div>
        @if($booking->round_trip_discount > 0)
        <div class="row">
            <span>Remise A/R (5%):</span>
            <span>-{{ number_format($booking->round_trip_discount, 0, ',', ' ') }} XAF</span>
        </div>
        @endif
        <div class="row font-bold" style="font-size: 13px; margin-top: 4px;">
            <span>TOTAL REGLE:</span>
            <span>{{ number_format($booking->total_amount, 0, ',', ' ') }} XAF</span>
        </div>
        <div class="row" style="font-size: 10px;">
            <span>Mode Règlement:</span>
            <span>{{ strtoupper($booking->payment->method ?? 'PAYE') }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- QR Code Scan Area -->
    <div class="qr-box">
        <div class="font-bold" style="font-size: 10px; margin-bottom: 4px;">JETON DE CONTROLE EMBARQUEMENT (QR)</div>
        <div class="qr-mock">
            <div style="font-family: monospace; font-size: 8px; line-height: 1.1; margin-bottom: 4px;">
                ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■<br>
                ■ &nbsp; &nbsp; &nbsp; &nbsp; ■ ■ &nbsp; &nbsp; &nbsp; ■ &nbsp; &nbsp; &nbsp; &nbsp; ■<br>
                ■ &nbsp; ■■ &nbsp; ■ &nbsp; ■■ &nbsp; &nbsp; &nbsp; ■ &nbsp; ■■ &nbsp; ■<br>
                ■ &nbsp; ■■ &nbsp; ■ ■ &nbsp; ■■ &nbsp; &nbsp; ■ &nbsp; ■■ &nbsp; ■<br>
                ■ &nbsp; &nbsp; &nbsp; &nbsp; ■ &nbsp; &nbsp; ■ &nbsp; ■ &nbsp; ■ &nbsp; &nbsp; &nbsp; &nbsp; ■<br>
                ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■<br>
                ■ ■ &nbsp; &nbsp; &nbsp; ■■■■ &nbsp; ■■■ &nbsp; ■■ &nbsp; &nbsp; ■<br>
                ■ &nbsp; ■■■ &nbsp; &nbsp; &nbsp; ■■ &nbsp; &nbsp; &nbsp; ■■■■■ &nbsp; ■<br>
                ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
            </div>
            <span class="font-bold" style="font-size: 9px;">TOKEN: {{ substr($booking->qr_code_token, 0, 16) }}...</span>
        </div>
    </div>

    <div class="double-divider"></div>

    <!-- Conditions -->
    <div class="notice">
        • Billet strictement nominatif, personnel et non transférable.<br>
        • Présentation obligatoire de la CNI / Passeport valide à l'embarquement.<br>
        • Clôture de l'enregistrement des bagages 30 min avant le départ.<br>
        • Annulation autorisée jusqu'à 6h avant le départ avec avoir E-Wallet.<br>
        • Report de voyage possible jusqu'à 12h avant le départ.<br>
        • Service client: assistance@realvoyage.cm / WhatsApp +237 670 000 001.
    </div>

    <div class="double-divider"></div>
    <div class="text-center font-bold" style="font-size: 11px;">
        *** BON VOYAGE AVEC REAL EXPRESS VOYAGES ***
    </div>
</div>

</body>
</html>
