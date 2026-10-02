<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseUnpaidBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transport:release-unpaid-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release unpaid 30-minute bookings, send 8h departure reminders, and auto-cancel uncompleted reservations 6h prior to departure';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        // 1. Process standard 30-minute expired pending bookings
        $expiredPending = Booking::where('status', 'pending')
            ->where('expires_at', '<=', $now)
            ->with(['trip', 'tripClass'])
            ->get();

        $releasedPendingCount = $expiredPending->count();

        foreach ($expiredPending as $booking) {
            DB::transaction(function () use ($booking) {
                // Restore trip seats
                if ($booking->trip) {
                    $booking->trip->increment('seats_available', $booking->seats_count);
                }

                // Restore class seats if applicable
                if ($booking->tripClass) {
                    $booking->tripClass->increment('seats_available', $booking->seats_count);
                }

                // Update booking status
                $booking->update([
                    'status' => 'cancelled',
                ]);
            });
        }

        // 2. Process 8-hour pre-departure payment reminders for advance reservations
        // At 8 hours before departure, passenger receives alert that 2 hours remain to complete payment
        $eightHoursAhead = $now->copy()->addHours(8);
        $sixHoursAhead = $now->copy()->addHours(6);

        $dueReminders = Booking::where('status', 'reserved')
            ->whereNull('reminder_sent_at')
            ->whereHas('trip', function ($q) use ($eightHoursAhead, $sixHoursAhead) {
                $q->where('departure_time', '<=', $eightHoursAhead)
                  ->where('departure_time', '>', $sixHoursAhead);
            })
            ->with(['trip.branch', 'passenger'])
            ->get();

        $remindersCount = $dueReminders->count();

        foreach ($dueReminders as $booking) {
            // Dispatch queued reminder event pipeline (SMS, WhatsApp, TripAlert)
            event(new \App\Events\TripReminderEvent($booking));

            $booking->update([
                'reminder_sent_at' => $now,
            ]);
        }

        // 3. Process 6-hour pre-departure auto-cancellation for advance reservations
        // If ticket not paid by 6 hours before departure, cancel reservation and make seats available
        $dueCancellations = Booking::where('status', 'reserved')
            ->whereHas('trip', function ($q) use ($sixHoursAhead) {
                $q->where('departure_time', '<=', $sixHoursAhead);
            })
            ->with(['trip.branch', 'tripClass', 'passenger'])
            ->get();

        $cancelledReservationsCount = $dueCancellations->count();

        foreach ($dueCancellations as $booking) {
            DB::transaction(function () use ($booking) {
                // Restore seats to inventory
                if ($booking->trip) {
                    $booking->trip->increment('seats_available', $booking->seats_count);
                }

                if ($booking->tripClass) {
                    $booking->tripClass->increment('seats_available', $booking->seats_count);
                }

                $booking->update([
                    'status' => 'cancelled',
                ]);
            });

            // Dispatch queued cancellation alert event pipeline
            event(new \App\Events\AdvanceReservationExpiringEvent($booking));
        }

        $this->info("Processed: {$releasedPendingCount} 30-min expired, {$remindersCount} 8h reminders, {$cancelledReservationsCount} 6h cancelled reservations.");
        Log::info("transport:release-unpaid-bookings processed: {$releasedPendingCount} pending 30-min expired, {$remindersCount} reminders sent, {$cancelledReservationsCount} 6h reservations cancelled.");

        return Command::SUCCESS;
    }
}
