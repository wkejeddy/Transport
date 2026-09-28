<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class BookingService
{
    /**
     * Create a new booking with pessimistic locking and validation.
     *
     * @param User $passenger
     * @param Trip $trip
     * @param array $data
     * @return Booking
     * @throws Exception
     */
    public function createBooking(User $passenger, Trip $trip, array $data): Booking
    {
        return DB::transaction(function () use ($passenger, $trip, $data) {
            // Lock trip row for concurrency protection
            $lockedTrip = Trip::where('id', $trip->id)->lockForUpdate()->firstOrFail();

            $bookingOption = $data['booking_option'] ?? 'immediate';
            $seatsCount = (int)($data['seats_count'] ?? 1);
            $seatNumbers = $data['seat_numbers'] ?? [];
            $passengersData = $data['passengers'] ?? [];
            $tripClassId = $data['trip_class_id'] ?? null;
            $isRoundTrip = (bool)($data['is_round_trip'] ?? false);
            $returnTripId = $data['return_trip_id'] ?? null;

            // 1. Advance reservation time check (> 8h before departure)
            if ($bookingOption === 'reserve') {
                if ($lockedTrip->departure_time <= now()->addHours(8)) {
                    throw new Exception(__('messages.flash.advance_reservation_time_error') ?: 'La réservation préalable doit être effectuée au moins 8 heures avant le départ.');
                }
                $bookingType = 'advance_reservation';
                $reservationFee = 500.00;
            } else {
                $bookingType = 'immediate';
                $reservationFee = 0.00;
            }

            // 2. Transport Class & Pricing
            $tripClass = null;
            $unitPrice = (float)$lockedTrip->base_price;
            $transportClass = 'standard';

            if (!empty($tripClassId)) {
                $tripClass = TripClass::where('trip_id', $lockedTrip->id)
                    ->where('id', $tripClassId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $unitPrice = (float)$tripClass->price;
                $transportClass = $tripClass->class_code;

                if ($tripClass->seats_available < $seatsCount) {
                    throw new Exception(__('messages.flash.not_enough_seats_class') ?: 'Pas assez de places disponibles dans cette classe.');
                }
            } elseif ($lockedTrip->seats_available < $seatsCount) {
                throw new Exception(__('messages.flash.not_enough_seats_trip') ?: 'Pas assez de places disponibles pour ce voyage.');
            }

            // 3. Crew Hardcoded Locks Enforcement
            if (!empty($seatNumbers)) {
                foreach ($seatNumbers as $seat) {
                    if ($lockedTrip->isSeatLocked($seat)) {
                        $rawNum = (int)ltrim((string)$seat, 'S-');
                        $seatLabel = ($rawNum === 1) ? __('messages.seatmap.driver') : __('messages.seatmap.convoyeur');
                        throw new Exception(__('messages.flash.seat_staff_locked', ['seat' => $seat, 'label' => $seatLabel]) ?: "Le siège {$seat} est strictement réservé au personnel de bord ({$seatLabel}) et ne peut être réservé.");
                    }
                }

                // 4. Double Booking Check with Active Bookings
                $bookedSeatNumbers = $lockedTrip->bookings()
                    ->whereIn('status', ['confirmed', 'pending', 'reserved', 'checked_in'])
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->pluck('seat_numbers')
                    ->flatten()
                    ->filter()
                    ->map(fn($s) => (string)$s)
                    ->toArray();

                foreach ($seatNumbers as $requestedSeat) {
                    $normReq = (string)$requestedSeat;
                    $numReq = ltrim($normReq, 'S-');
                    foreach ($bookedSeatNumbers as $alreadyBooked) {
                        $normBooked = (string)$alreadyBooked;
                        $numBooked = ltrim($normBooked, 'S-');
                        if ($normReq === $normBooked || $numReq === $numBooked) {
                            throw new Exception(__('messages.flash.seat_already_booked', ['seat' => $requestedSeat]) ?: "Le siège {$requestedSeat} vient d'être sélectionné par un autre passager. Veuillez en choisir un autre.");
                        }
                    }
                }
            } else {
                // Auto assign next available seats
                $seatNumbers = [];
                $taken = $lockedTrip->bookings()
                    ->whereIn('status', ['confirmed', 'pending', 'reserved', 'checked_in'])
                    ->pluck('seat_numbers')
                    ->flatten()
                    ->filter()
                    ->map(fn($s) => is_numeric($s) ? (int)$s : (int)filter_var($s, FILTER_SANITIZE_NUMBER_INT))
                    ->toArray();

                $capacity = $lockedTrip->vehicle->capacity_seats ?? 75;
                for ($s = 1; $s <= $capacity && count($seatNumbers) < $seatsCount; $s++) {
                    if (!in_array($s, [1, 16], true) && !in_array($s, $taken, true)) {
                        $seatNumbers[] = 'S-' . str_pad((string)$s, 2, '0', STR_PAD_LEFT);
                    }
                }
            }

            // 5. Calculate Total & Round-Trip discount
            $subtotal = $unitPrice * $seatsCount;
            $discount = 0.00;
            if ($isRoundTrip) {
                // 5% discount on round-trip ticket
                $discount = round($subtotal * 0.05, 2);
            }
            $totalAmount = max(0, $subtotal - $discount);

            // 6. Generate Reference & Expiration
            $bookingRef = 'RV-' . strtoupper(Str::random(4)) . '-' . date('dmy');
            $expiresAt = ($bookingType === 'advance_reservation') ? now()->addHours(2) : now()->addMinutes(30);

            // 7. Decrement available capacity
            $lockedTrip->decrement('seats_available', $seatsCount);
            if ($tripClass) {
                $tripClass->decrement('seats_available', $seatsCount);
            }

            // 8. Create Booking Record
            $booking = Booking::create([
                'booking_reference' => $bookingRef,
                'passenger_id' => $passenger->id,
                'trip_id' => $lockedTrip->id,
                'return_trip_id' => $returnTripId,
                'trip_class_id' => $tripClass?->id,
                'transport_class' => $transportClass,
                'booking_type' => $bookingType,
                'is_round_trip' => $isRoundTrip,
                'seats_count' => $seatsCount,
                'seat_numbers' => $seatNumbers,
                'passengers_data' => $passengersData,
                'total_amount' => $totalAmount,
                'round_trip_discount' => $discount,
                'reservation_fee' => $reservationFee,
                'reservation_fee_paid' => false,
                'status' => 'pending',
                'qr_code_token' => Str::random(32),
                'expires_at' => $expiresAt,
            ]);

            return $booking;
        });
    }

    /**
     * Reschedule a booking to a new trip and/or modify seat selection.
     *
     * Rules:
     * - Departure of current trip must be > 12 hours away.
     * - Fare difference calculated (switching to VIP or higher price class).
     * - If new trip costs less, refund difference directly to passenger's wallet_balance.
     * - If new trip costs more, debit difference from passenger's wallet_balance (must be sufficient).
     * - Re-assign seat numbers and invalidate the previous QR token.
     *
     * @param Booking $booking
     * @param Trip $newTrip
     * @param TripClass|null $newTripClass
     * @param array $seatNumbers
     * @param array $passengersData
     * @return Booking
     * @throws Exception
     */
    public function rescheduleBooking(
        Booking $booking,
        Trip $newTrip,
        ?TripClass $newTripClass = null,
        array $seatNumbers = [],
        array $passengersData = []
    ): Booking {
        return DB::transaction(function () use ($booking, $newTrip, $newTripClass, $seatNumbers, $passengersData) {
            // Lock current booking
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedBooking->status, ['cancelled', 'completed', 'checked_in'])) {
                throw new Exception('Impossible de modifier une réservation dont le statut est ' . $lockedBooking->status . '.');
            }

            // Lock old trip and old class
            $oldTrip = Trip::where('id', $lockedBooking->trip_id)->lockForUpdate()->firstOrFail();
            $oldTripClass = $lockedBooking->trip_class_id
                ? TripClass::where('id', $lockedBooking->trip_class_id)->lockForUpdate()->first()
                : null;

            // 1. Strict 12-hour rule verification on original departure time
            $hoursUntilDeparture = now()->diffInHours($oldTrip->departure_time, false);
            if ($hoursUntilDeparture <= 12) {
                throw new Exception(__('messages.flash.reschedule_denied_time') ?: 'Le report de voyage n\'est possible qu\'à plus de 12 heures avant le départ.');
            }

            // Lock passenger record for wallet balance update
            $passenger = User::where('id', $lockedBooking->passenger_id)->lockForUpdate()->firstOrFail();

            // Lock target new trip and class
            $lockedNewTrip = Trip::where('id', $newTrip->id)->lockForUpdate()->firstOrFail();
            $lockedNewTripClass = null;

            if ($newTripClass) {
                $lockedNewTripClass = TripClass::where('trip_id', $lockedNewTrip->id)
                    ->where('id', $newTripClass->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $seatsCount = $lockedBooking->seats_count;

            // 2. Validate availability on target new trip (excluding current booking if same trip)
            if ($lockedNewTripClass) {
                if ($lockedNewTripClass->seats_available < $seatsCount && $lockedNewTrip->id !== $oldTrip->id) {
                    throw new Exception(__('messages.flash.not_enough_seats_class') ?: 'Pas assez de places disponibles dans cette classe.');
                }
            } elseif ($lockedNewTrip->seats_available < $seatsCount && $lockedNewTrip->id !== $oldTrip->id) {
                throw new Exception(__('messages.flash.not_enough_seats_trip') ?: 'Pas assez de places disponibles pour ce voyage.');
            }

            // 3. Calculate new pricing and fare difference
            $newUnitPrice = $lockedNewTripClass ? (float)$lockedNewTripClass->price : (float)$lockedNewTrip->base_price;
            $newSubtotal = $newUnitPrice * $seatsCount;
            $newDiscount = $lockedBooking->is_round_trip ? round($newSubtotal * 0.05, 2) : 0.00;
            $newTotalAmount = max(0, $newSubtotal - $newDiscount);

            $oldTotalAmount = (float)$lockedBooking->total_amount;
            $fareDifference = $newTotalAmount - $oldTotalAmount;

            if ($fareDifference > 0) {
                // Higher fare: debit difference from passenger's wallet
                if ($passenger->wallet_balance < $fareDifference) {
                    throw new Exception(__('messages.flash.reschedule_insufficient_wallet', ['amount' => number_format($fareDifference, 0, ',', ' ')]) ?: "Solde E-Wallet insuffisant pour régler le supplément de {$fareDifference} FCFA.");
                }
                $passenger->debitWallet($fareDifference);
            } elseif ($fareDifference < 0) {
                // Lower fare: refund difference to passenger's wallet
                $refundAmount = abs($fareDifference);
                $passenger->creditWallet($refundAmount);
            }

            // 4. Release seats from old trip
            $oldTrip->increment('seats_available', $seatsCount);
            if ($oldTripClass) {
                $oldTripClass->increment('seats_available', $seatsCount);
            }

            // 5. Seat allocation on new trip
            if (!empty($seatNumbers)) {
                // Verify crew locks
                foreach ($seatNumbers as $seat) {
                    if ($lockedNewTrip->isSeatLocked($seat)) {
                        $rawNum = (int)ltrim((string)$seat, 'S-');
                        $seatLabel = ($rawNum === 1) ? __('messages.seatmap.driver') : __('messages.seatmap.convoyeur');
                        throw new Exception(__('messages.flash.seat_staff_locked', ['seat' => $seat, 'label' => $seatLabel]) ?: "Le siège {$seat} est réservé au personnel de bord ({$seatLabel}).");
                    }
                }

                // Verify double booking on new trip
                $bookedSeatNumbers = $lockedNewTrip->bookings()
                    ->where('id', '!=', $lockedBooking->id)
                    ->whereIn('status', ['confirmed', 'pending', 'reserved', 'checked_in'])
                    ->pluck('seat_numbers')
                    ->flatten()
                    ->filter()
                    ->map(fn($s) => (string)$s)
                    ->toArray();

                foreach ($seatNumbers as $requestedSeat) {
                    $normReq = (string)$requestedSeat;
                    $numReq = ltrim($normReq, 'S-');
                    foreach ($bookedSeatNumbers as $alreadyBooked) {
                        $normBooked = (string)$alreadyBooked;
                        $numBooked = ltrim($normBooked, 'S-');
                        if ($normReq === $normBooked || $numReq === $numBooked) {
                            throw new Exception(__('messages.flash.seat_already_booked', ['seat' => $requestedSeat]) ?: "Le siège {$requestedSeat} vient d'être sélectionné par un autre passager.");
                        }
                    }
                }
            } else {
                // Auto-assign available seats on new trip
                $seatNumbers = [];
                $taken = $lockedNewTrip->bookings()
                    ->where('id', '!=', $lockedBooking->id)
                    ->whereIn('status', ['confirmed', 'pending', 'reserved', 'checked_in'])
                    ->pluck('seat_numbers')
                    ->flatten()
                    ->filter()
                    ->map(fn($s) => is_numeric($s) ? (int)$s : (int)filter_var($s, FILTER_SANITIZE_NUMBER_INT))
                    ->toArray();

                $capacity = $lockedNewTrip->vehicle->capacity_seats ?? 75;
                for ($s = 1; $s <= $capacity && count($seatNumbers) < $seatsCount; $s++) {
                    if (!in_array($s, [1, 16], true) && !in_array($s, $taken, true)) {
                        $seatNumbers[] = 'S-' . str_pad((string)$s, 2, '0', STR_PAD_LEFT);
                    }
                }
            }

            // Decrement seats on new trip
            $lockedNewTrip->decrement('seats_available', $seatsCount);
            if ($lockedNewTripClass) {
                $lockedNewTripClass->decrement('seats_available', $seatsCount);
            }

            // 6. Invalidate previous QR code token by generating a fresh token
            $newQrToken = Str::random(32);

            // 7. Update Booking
            $lockedBooking->update([
                'trip_id' => $lockedNewTrip->id,
                'trip_class_id' => $lockedNewTripClass?->id,
                'transport_class' => $lockedNewTripClass ? $lockedNewTripClass->class_code : 'standard',
                'seat_numbers' => $seatNumbers,
                'passengers_data' => !empty($passengersData) ? $passengersData : $lockedBooking->passengers_data,
                'total_amount' => $newTotalAmount,
                'round_trip_discount' => $newDiscount,
                'qr_code_token' => $newQrToken,
            ]);

            return $lockedBooking->fresh(['trip.branch', 'trip.vehicle', 'tripClass', 'passenger']);
        });
    }

    /**
     * Cancel an active booking and release inventory.
     *
     * @param Booking $booking
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function cancelBooking(Booking $booking, string $reason = 'cancelled_by_user'): bool
    {
        return DB::transaction(function () use ($booking, $reason) {
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedBooking->status, ['cancelled', 'completed', 'checked_in'])) {
                return false;
            }

            $trip = Trip::where('id', $lockedBooking->trip_id)->lockForUpdate()->first();
            if ($trip) {
                $hoursUntilDeparture = now()->diffInHours($trip->departure_time, false);
                if ($hoursUntilDeparture <= 6) {
                    throw new Exception(__('messages.flash.cancellation_denied_time', ['hours' => max(0, round($hoursUntilDeparture, 1))]) ?: 'Annulation refusée à moins de 6h du départ.');
                }
            }

            $refundAmount = 0.00;
            $isConfirmedOrPaid = ($lockedBooking->status === 'confirmed' || ($lockedBooking->payment && $lockedBooking->payment->isSuccessful()));
            if ($isConfirmedOrPaid) {
                $refundAmount = (float)$lockedBooking->total_amount;
            }

            // Restore seats
            if ($trip) {
                $trip->increment('seats_available', $lockedBooking->seats_count);
            }
            if ($lockedBooking->tripClass) {
                $lockedBooking->tripClass->increment('seats_available', $lockedBooking->seats_count);
            }

            // Refund directly to passenger's E-Wallet
            if ($refundAmount > 0) {
                $passenger = User::where('id', $lockedBooking->passenger_id)->lockForUpdate()->first();
                if ($passenger) {
                    $passenger->creditWallet($refundAmount);
                }
            }

            $lockedBooking->update([
                'status' => 'cancelled',
            ]);

            return true;
        });
    }
}