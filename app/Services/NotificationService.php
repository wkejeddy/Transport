<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Trip;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Build WhatsApp Web prefilled share link
     */
    public static function getWhatsAppShareUrl(Booking $booking, ?string $phone = null): string
    {
        $targetPhone = $phone ? preg_replace('/[^0-9]/', '', $phone) : '';
        if ($targetPhone && !str_starts_with($targetPhone, '237') && strlen($targetPhone) === 9) {
            $targetPhone = '237' . $targetPhone;
        }

        $passengerName = $booking->passengers_data[0]['name'] ?? $booking->passenger->name ?? 'Passager';
        $seats = implode(', ', $booking->seat_numbers ?? []);
        $depTime = $booking->trip ? $booking->trip->departure_time->format('d/m/Y à H:i') : '';
        $route = $booking->trip ? "{$booking->trip->departure_city} ➔ {$booking->trip->arrival_city}" : '';
        $station = $booking->trip ? $booking->trip->departure_station : 'Gare Real Voyage';

        $text = "*REAL VOYAGE TRANSPORT S.A. - E-BILLET OFFICIEL*\n\n"
              . "👤 *Passager* : {$passengerName}\n"
              . "🎫 *Référence* : {$booking->booking_reference}\n"
              . "🛣️ *Trajet* : {$route}\n"
              . "⏰ *Départ* : {$depTime}\n"
              . "🏢 *Gare d'embarquement* : {$station}\n"
              . "💺 *Siège(s)* : {$seats}\n"
              . "💵 *Montant* : " . number_format($booking->total_amount, 0, ',', ' ') . " FCFA\n\n"
              . "ℹ️ _Présentez ce message et votre CNI au guichet d'embarquement 45 min avant le départ._";

        $encodedText = rawurlencode($text);
        if ($targetPhone) {
            return "https://api.whatsapp.com/send?phone={$targetPhone}&text={$encodedText}";
        }
        return "https://api.whatsapp.com/send?text={$encodedText}";
    }

    /**
     * Send simulated SMS & WhatsApp confirmation for paid confirmed booking
     */
    public static function sendBookingConfirmation(Booking $booking): array
    {
        $phone = $booking->passengers_data[0]['phone'] ?? $booking->passenger->phone ?? '6XXXXXXXX';
        $passengerName = $booking->passengers_data[0]['name'] ?? $booking->passenger->name ?? 'Passager';
        
        $smsContent = "Real Voyage : Bonjour {$passengerName}, votre billet N° {$booking->booking_reference} ({$booking->trip->departure_city} -> {$booking->trip->arrival_city}) du {$booking->trip->departure_time->format('d/m/Y H:i')} est CONFIRMÉ. Sièges : " . implode(', ', $booking->seat_numbers ?? ['S-01']) . ". Jeton QR : {$booking->qr_code_token}";
        
        $whatsappContent = "*Real Voyage - Billet Officiel*\n\nBonjour *{$passengerName}*,\nVotre voyage avec *Real Voyage* est confirmé !\n\n[Trajet] : {$booking->trip->departure_city} -> {$booking->trip->arrival_city}\n[Départ] : {$booking->trip->departure_time->format('d/m/Y à H:i')}\n[Gare] : {$booking->trip->departure_station}\n[Sièges] : " . implode(', ', $booking->seat_numbers ?? ['S-01']) . "\n\n_Consultez votre carte d'embarquement sur votre espace voyageur._";

        Log::info("[NOTIFICATION SMS] To: {$phone} | Message: {$smsContent}");
        Log::info("[NOTIFICATION WHATSAPP] To: {$phone} | Message: {$whatsappContent}");

        return [
            'status' => 'sent',
            'phone' => $phone,
            'sms' => $smsContent,
            'whatsapp' => $whatsappContent,
            'whatsapp_url' => self::getWhatsAppShareUrl($booking, $phone),
            'sent_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Send simulated SMS & WhatsApp confirmation for 500 FCFA Advance Reservation fee
     */
    public static function sendReservationFeeConfirmation(Booking $booking): array
    {
        $phone = $booking->passengers_data[0]['phone'] ?? $booking->passenger->phone ?? '6XXXXXXXX';
        $passengerName = $booking->passengers_data[0]['name'] ?? $booking->passenger->name ?? 'Passager';
        $departureStr = $booking->trip->departure_time->format('d/m/Y H:i');
        $deadlineStr = $booking->trip->departure_time->copy()->subHours(6)->format('d/m/Y H:i');

        $smsContent = "Real Voyage : Bonjour {$passengerName}, vos places (Sièges: " . implode(', ', $booking->seat_numbers ?? []) . ") pour le voyage N° {$booking->trip->trip_number} ({$booking->trip->departure_city} -> {$booking->trip->arrival_city}) du {$departureStr} sont RÉSERVÉES (Frais de réservation de 500 FCFA validés). Veuillez régler votre billet avant le {$deadlineStr} (au plus tard 6h avant départ).";

        $whatsappContent = "*Real Voyage - Confirmation de Pré-Réservation*\n\nBonjour *{$passengerName}*,\nVos places avec *Real Voyage* sont bloquées !\n\n[Trajet] : {$booking->trip->departure_city} -> {$booking->trip->arrival_city}\n[Départ] : {$departureStr}\n[Sièges Réservés] : " . implode(', ', $booking->seat_numbers ?? []) . "\n[Frais de Réservation] : 500 FCFA (réglés)\n[Important] : Vous devez solder le billet au plus tard à 6 heures du départ ({$deadlineStr}).";

        Log::info("[NOTIFICATION SMS] To: {$phone} | Message: {$smsContent}");
        Log::info("[NOTIFICATION WHATSAPP] To: {$phone} | Message: {$whatsappContent}");

        return [
            'status' => 'sent',
            'phone' => $phone,
            'sms' => $smsContent,
            'whatsapp' => $whatsappContent,
            'sent_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Send simulated 8-hour pre-departure payment reminder (2 hours remaining to decide)
     */
    public static function sendPreDeparturePaymentReminder(Booking $booking): array
    {
        $phone = $booking->passengers_data[0]['phone'] ?? $booking->passenger->phone ?? '6XXXXXXXX';
        $passengerName = $booking->passengers_data[0]['name'] ?? $booking->passenger->name ?? 'Passager';
        $departureStr = $booking->trip->departure_time->format('H:i');

        $smsContent = "Real Voyage RAPPEL : Bonjour {$passengerName}, votre voyage N° {$booking->trip->trip_number} part dans 8 heures (à {$departureStr}). Il vous reste 2 HEURES pour régler votre billet de voyage. Passé ce délai (à 6h du départ), votre réservation sera annulée et vos places libérées.";

        $whatsappContent = "*Real Voyage - Alerte Expiration Réservation (Reste 2h)*\n\nBonjour *{$passengerName}*,\nVotre voyage pour *{$booking->trip->arrival_city}* part dans 8 heures (à {$departureStr}).\n\n[Urgent] : Il vous reste 2 heures pour régler votre billet. À 6h du départ, vos sièges seront automatiquement remis en vente et la réservation annulée.\n\nConnectez-vous sur votre espace Real Voyage pour finaliser votre paiement.";

        Log::info("[NOTIFICATION REMINDER SMS] To: {$phone} | Message: {$smsContent}");
        Log::info("[NOTIFICATION REMINDER WHATSAPP] To: {$phone} | Message: {$whatsappContent}");

        return [
            'status' => 'sent',
            'phone' => $phone,
            'sms' => $smsContent,
            'whatsapp' => $whatsappContent,
            'sent_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Send simulated cancellation alert when booking expires at 6h before departure
     */
    public static function sendReservationCancellationAlert(Booking $booking): array
    {
        $phone = $booking->passengers_data[0]['phone'] ?? $booking->passenger->phone ?? '6XXXXXXXX';
        $passengerName = $booking->passengers_data[0]['name'] ?? $booking->passenger->name ?? 'Passager';

        $smsContent = "Real Voyage : Bonjour {$passengerName}, votre réservation N° {$booking->booking_reference} ({$booking->trip->departure_city} -> {$booking->trip->arrival_city}) a été ANNULÉE car le billet n'a pas été soldé 6h avant le départ. Vos sièges ont été libérés pour d'autres voyageurs.";

        $whatsappContent = "*Real Voyage - Réservation Annulée*\n\nBonjour *{$passengerName}*,\nLe délai de paiement du billet à 6h du départ est expiré. Vos places pour le trajet *{$booking->trip->departure_city} -> {$booking->trip->arrival_city}* ont été remises en vente.";

        Log::info("[NOTIFICATION CANCELLATION SMS] To: {$phone} | Message: {$smsContent}");
        Log::info("[NOTIFICATION CANCELLATION WHATSAPP] To: {$phone} | Message: {$whatsappContent}");

        return [
            'status' => 'sent',
            'phone' => $phone,
            'sms' => $smsContent,
            'whatsapp' => $whatsappContent,
            'sent_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Send simulated 4-digit OTP code to cargo parcel recipient
     */
    public static function sendCargoPickupOtp(Shipment $shipment): array
    {
        $phone = $shipment->recipient_phone;
        $recipientName = $shipment->recipient_name;
        
        $smsContent = "Real Voyage Fret : Bonjour {$recipientName}, un colis ({$shipment->tracking_code}) vous a été expédié par {$shipment->sender_name}. Votre CODE SECRET DE RETRAIT est : [ {$shipment->pickup_otp} ]. Présentez ce code et votre CNI en gare de {$shipment->recipient_city}.";

        Log::info("[NOTIFICATION OTP SMS] To: {$phone} | Message: {$smsContent}");

        return [
            'status' => 'sent',
            'phone' => $phone,
            'sms' => $smsContent,
            'sent_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Send real-time trip delay or update alert to all booked passengers
     */
    public static function broadcastTripAlert(Trip $trip, string $customMessage): array
    {
        $notifiedCount = $trip->bookings()->where('status', 'confirmed')->count();
        
        Log::info("[NOTIFICATION BROADCAST] Trip #{$trip->trip_number} | Message: {$customMessage} | Passengers: {$notifiedCount}");

        return [
            'status' => 'broadcasted',
            'trip_number' => $trip->trip_number,
            'passengers_count' => $notifiedCount,
            'message' => $customMessage,
            'sent_at' => now()->toIso8601String(),
        ];
    }
}