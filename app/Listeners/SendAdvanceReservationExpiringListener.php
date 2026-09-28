<?php

namespace App\Listeners;

use App\Events\AdvanceReservationExpiringEvent;
use App\Models\TripAlert;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAdvanceReservationExpiringListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(AdvanceReservationExpiringEvent $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['trip.branch', 'passenger']);

        NotificationService::sendReservationCancellationAlert($booking);

        if ($booking->trip && $booking->passenger) {
            TripAlert::create([
                'trip_id' => $booking->trip_id,
                'user_id' => $booking->passenger_id,
                'type' => 'cancellation',
                'title' => 'Réservation Expirée & Annulée (6h avant départ)',
                'message' => "Votre réservation N° {$booking->booking_reference} ({$booking->trip->departure_city} -> {$booking->trip->arrival_city}) a été annulée faute de règlement à 6h du départ. Vos places ont été libérées.",
                'is_read' => false,
            ]);
        }

        if ($booking->passenger && $booking->passenger->email) {
            Log::info("[EMAIL STUB] 6h Cancellation alert sent to {$booking->passenger->email} for Booking #{$booking->booking_reference}");
        }
    }
}
