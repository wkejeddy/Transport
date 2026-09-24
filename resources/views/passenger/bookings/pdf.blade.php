<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Billet & Titre de Transport - :ref', ['ref' => $booking->booking_reference]) }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0F172A;
            background: #FFFFFF;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.4;
        }
        .ticket-wrapper {
            border: 2px solid #0F2942;
            border-radius: 12px;
            padding: 24px;
            background: #FFFFFF;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 800;
            color: #0F2942;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-confirmed {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #34D399;
        }
        .grid-2 {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }
        .section-box {
            flex: 1;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 14px;
        }
        .label {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .value {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
        }
        .route-banner {
            background: #0F2942;
            color: #FFFFFF;
            padding: 16px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .passengers-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .passengers-table th {
            background: #F1F5F9;
            color: #475569;
            text-align: left;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 700;
            border-bottom: 1px solid #CBD5E1;
        }
        .passengers-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 12px;
        }
        .qr-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 2px dashed #CBD5E1;
            padding-top: 16px;
            margin-top: 20px;
        }
        .footer-terms {
            margin-top: 20px;
            font-size: 10px;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
            padding-top: 10px;
            line-height: 1.5;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 20px auto;">
        <!-- Print Action Header for Browser View -->
        <div class="no-print" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('passenger.bookings.ticket', $booking) }}" style="color: #0F2942; text-decoration: none; font-weight: 700;">
                &larr; {{ __('Retour à l\'espace voyageur') }}
            </a>
            <button onclick="window.print()" style="background: #0F2942; color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 14px;">
                {{ __('Imprimer / Exporter en PDF') }}
            </button>
        </div>

        <div class="ticket-wrapper">
            <!-- Header -->
            <div class="header">
                <div>
                    <div class="brand-title">{{ config('app.name', 'Travel') }}</div>
                    <div style="font-size: 11px; color: #64748B;">{{ __('Plateforme Nationale Unifiée • République du Cameroun') }}</div>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-confirmed">{{ __('BILLET CONFIRMÉ & PAYÉ') }}</span>
                    <div style="font-size: 12px; font-weight: 800; margin-top: 4px; color: #0F172A;">
                        {{ __('Réf :') }} {{ $booking->booking_reference }}
                    </div>
                </div>
            </div>

            <!-- Route Banner -->
            <div class="route-banner">
                <div>
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85;">{{ __('Ligne Interurbaine Real Voyage') }}</div>
                    <div style="font-size: 20px; font-weight: 800; margin-top: 2px;">
                        {{ $booking->trip->departure_city }} &rarr; {{ $booking->trip->arrival_city }}
                    </div>
                    <div style="font-size: 12px; margin-top: 4px; opacity: 0.9;">
                        {{ __('Opérateur :') }} <strong>Real Voyage S.A.</strong> ({{ $booking->trip->branch->name ?? __('Gare Centrale') }})
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; opacity: 0.85;">{{ __('Classe de Voyage') }}</div>
                    <div style="font-size: 15px; font-weight: 800; color: #FFFFFF;">
                        {{ ucfirst($booking->transport_class) }}
                    </div>
                    <div style="font-size: 12px; opacity: 0.9;">
                        {{ __('N° Voyage :') }} <strong>{{ $booking->trip->trip_number }}</strong>
                    </div>
                </div>
            </div>

            <!-- Timings and Boarding Details -->
            <div class="grid-2">
                <div class="section-box">
                    <div class="label">{{ __('Date & Heure de Départ') }}</div>
                    <div class="value">{{ $booking->trip->departure_time->format('d/m/Y') }} {{ __('à') }} {{ $booking->trip->departure_time->format('H:i') }}</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                        {{ __('Gare / Agence :') }} <strong>{{ $booking->trip->departure_station }}</strong>
                    </div>
                </div>
                <div class="section-box">
                    <div class="label">{{ __('Arrivée Estimée') }}</div>
                    <div class="value">{{ $booking->trip->arrival_time_estimated->format('d/m/Y') }} {{ __('à') }} {{ $booking->trip->arrival_time_estimated->format('H:i') }}</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                        {{ __('Gare Destination :') }} <strong>{{ $booking->trip->arrival_station }}</strong>
                    </div>
                </div>
            </div>

            <!-- Passenger Manifest Table -->
            <div style="font-size: 12px; font-weight: 800; color: #0F172A; margin-bottom: 8px; text-transform: uppercase;">
                {{ __('Manifeste des Passagers Enregistrés (:count Place(s))', ['count' => $booking->seats_count]) }}
            </div>
            <table class="passengers-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>{{ __('Nom & Prénom') }}</th>
                        <th>{{ __('N° CNI / Passeport') }}</th>
                        <th>{{ __('Téléphone') }}</th>
                        <th>{{ __('Siège Assigné') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pList = $booking->passengers_data ?? []; @endphp
                    @foreach($pList as $idx => $p)
                        <tr>
                            <td><strong>{{ $idx + 1 }}</strong></td>
                            <td><strong>{{ $p['name'] ?? __('Passager') }}</strong></td>
                            <td>{{ $p['cni'] ?? __('CNI Vérifiée') }}</td>
                            <td>{{ $p['phone'] ?? 'N/A' }}</td>
                            <td>
                                <span style="background: #0F2942; color: #FFFFFF; font-weight: 800; padding: 2px 8px; border-radius: 4px; font-family: monospace;">
                                    {{ $booking->seat_numbers[$idx] ?? 'S-01' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Financial & Payment Details -->
            <div class="grid-2">
                <div class="section-box">
                    <div class="label">{{ __('Mode de Règlement Fiscal') }}</div>
                    <div class="value">{{ $booking->payment->provider_display ?? __('Mobile Money (Orange / MTN)') }}</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                        {{ __('ID Transaction :') }} {{ $booking->payment->transaction_reference ?? 'TXN-OK' }}
                    </div>
                </div>
                <div class="section-box" style="text-align: right; background: #F8FAFC; border-color: #CBD5E1;">
                    <div class="label" style="color: #475569;">{{ __('Montant Total Acquitté') }}</div>
                    <div class="value" style="font-size: 18px; color: #0F2942;">
                        {{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA
                    </div>
                    <div style="font-size: 10px; color: #64748B;">{{ __('TVA & Timbre fiscal inclus') }}</div>
                </div>
            </div>

            <!-- QR Section for Gate Scanning -->
            <div class="qr-section">
                <div>
                    <div style="font-size: 14px; font-weight: 800; color: #0F172A;">{{ __('Jeton de Contrôle Embarquement') }}</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                        {{ __('Présentez ce QR Code ou la référence au guichet/contrôleur.') }}
                    </div>
                    <div style="font-family: monospace; font-weight: 700; font-size: 13px; color: #0F2942; margin-top: 6px;">
                        {{ $booking->qr_code_token }}
                    </div>
                </div>
                <div style="text-align: center; border: 1px solid #0F2942; padding: 8px; border-radius: 8px;">
                    <!-- Visual QR Stamp Representation -->
                    <div style="width: 80px; height: 80px; background: #0F172A; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 10px; font-family: monospace; text-align: center; border-radius: 4px;">
                        [ QR SCAN ]<br>{{ substr($booking->qr_code_token, 0, 8) }}
                    </div>
                    <div style="font-size: 9px; font-weight: 700; margin-top: 4px; color: #0F2942;">{{ __('HOMOLOGUÉ') }}</div>
                </div>
            </div>

            <!-- Legal Footer & Terms -->
            <div class="footer-terms">
                <strong>{{ __('Conditions Générales de Transport :') }}</strong> {{ __('Billet nominatif et non transférable. Présentation obligatoire d\'une pièce d\'identité originale en cours de validité à l\'embarquement. Présentation requise 30 minutes avant le départ pour les bus et 45 minutes pour les trains Camrail. En cas de réclamation, médiation garantie sous 48h sur TransportCM.') }}
            </div>
        </div>
    </div>
</body>
</html>
