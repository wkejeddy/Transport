<?php

namespace App\Listeners;

use App\Events\BookingConfirmedEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTicketNotificationListener implements ShouldQueue
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
    public function handle(BookingConfirmedEvent $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['trip.branch', 'trip.vehicle', 'tripClass', 'passenger']);

        // Dispatch simulated SMS & WhatsApp confirmation + email log
        NotificationService::sendBookingConfirmation($booking);

        if ($booking->passenger && $booking->passenger->email) {
            Log::info("[EMAIL STUB] Ticket confirmation sent to {$booking->passenger->email} for Booking #{$booking->booking_reference}");
        }
    }
}
