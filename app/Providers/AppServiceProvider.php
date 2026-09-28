<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\BookingConfirmedEvent;
use App\Events\TripReminderEvent;
use App\Events\AdvanceReservationExpiringEvent;
use App\Listeners\SendTicketNotificationListener;
use App\Listeners\SendTripReminderListener;
use App\Listeners\SendAdvanceReservationExpiringListener;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            BookingConfirmedEvent::class,
            SendTicketNotificationListener::class,
        );

        Event::listen(
            TripReminderEvent::class,
            SendTripReminderListener::class,
        );

        Event::listen(
            AdvanceReservationExpiringEvent::class,
            SendAdvanceReservationExpiringListener::class,
        );
    }
}
