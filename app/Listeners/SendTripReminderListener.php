<?php

namespace App\Listeners;

use App\Events\TripReminderEvent;
use App\Models\TripAlert;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTripReminderListener implements ShouldQueue
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
    public function handle(TripReminderEvent $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['trip.branch', 'passenger']);

        NotificationService::sendPreDeparturePaymentReminder($booking);

        if ($booking->trip && $booking->passenger) {
            TripAlert::create([
                'trip_id' => $booking->trip_id,
                'user_id' => $booking->passenger_id,
                'type' => 'info',
                'title' => 'Rappel Paiement Réservation (Reste 2h)',
                'message' => "Votre voyage N° {$booking->trip->trip_number} part dans 8h. Il vous reste 2h pour régler votre billet avant libération automatique de vos places à 6h du départ.",
                'is_read' => false,
            ]);
        }

        if ($booking->passenger && $booking->passenger->email) {
            Log::info("[EMAIL STUB] 8h Departure payment reminder sent to {$booking->passenger->email} for Booking #{$booking->booking_reference}");
        }
    }
}
