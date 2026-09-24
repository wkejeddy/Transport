<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Booking;

class BookingController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'trip_class_id' => 'nullable|exists:trip_classes,id',
            'seats_count' => 'required|integer|min:1|max:10',
            'seat_numbers' => 'nullable|array',
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string|max:255',
            'passengers.*.cni' => 'nullable|string|max:50',
            'passengers.*.phone' => 'nullable|string|max:20',
            'booking_option' => 'nullable|in:immediate,reserve',
        ]);

        $bookingOption = $validated['booking_option'] ?? 'immediate';

        // If choosing advance reservation, verify trip is more than 8 hours away
        if ($bookingOption === 'reserve') {
            if ($trip->departure_time <= now()->addHours(8)) {
                return back()->with('error', __('messages.flash.advance_reservation_time_error'));
            }
            $bookingType = 'advance_reservation';
            $reservationFee = 500.00;
        } else {
            $bookingType = 'immediate';
            $reservationFee = 0.00;
        }

        $seatsCount = (int)$validated['seats_count'];
        $tripClass = null;
        $unitPrice = $trip->base_price;
        $transportClass = 'standard';

        if (!empty($validated['trip_class_id'])) {
            $tripClass = TripClass::where('trip_id', $trip->id)->findOrFail($validated['trip_class_id']);
            $unitPrice = $tripClass->price;
            $transportClass = $tripClass->class_code;

            if ($tripClass->seats_available < $seatsCount) {
                return back()->with('error', __('messages.flash.not_enough_seats_class'));
            }
        } elseif ($trip->seats_available < $seatsCount) {
            return back()->with('error', __('messages.flash.not_enough_seats_trip'));
        }

        $totalAmount = $unitPrice * $seatsCount;

        // Seat numbers verification & hardcoded locks enforcement (Seat 01 & 16)
        $seatNumbers = $validated['seat_numbers'] ?? [];
        if (!empty($seatNumbers)) {
            foreach ($seatNumbers as $seat) {
                if ($trip->isSeatLocked($seat)) {
                    $seatLabel = in_array((int)ltrim($seat, 'S-'), [1, 16]) && (int)ltrim($seat, 'S-') === 1 ? 'Chauffeur' : 'Convoyeur';
                    return back()->with('error', "Le siège {$seat} est strictement réservé au personnel de bord ({$seatLabel}) et ne peut être réservé.");
                }
            }

            // Check if any seat is already booked
            $occupiedSeats = $trip->getOccupiedSeatNumbers();
            foreach ($seatNumbers as $seat) {
                $seatFormatted = str_pad(ltrim($seat, 'S-'), 2, '0', STR_PAD_LEFT);
                if (in_array($seatFormatted, $occupiedSeats) || in_array((string)(int)$seatFormatted, $occupiedSeats)) {
                    return back()->with('error', __('messages.flash.seat_already_booked', ['seat' => $seat]));
                }
            }
        } else {
            // Auto-assign seats avoiding locked seats 01 and 16 and already booked seats
            $occupiedSeats = $trip->getOccupiedSeatNumbers();
            $capacity = $trip->vehicle ? $trip->vehicle->capacity_seats : 80;
            $availableSeats = [];

            for ($s = 1; $s <= $capacity; $s++) {
                $sCode = str_pad($s, 2, '0', STR_PAD_LEFT);
                if (!$trip->isSeatLocked($s) && !in_array($sCode, $occupiedSeats) && !in_array((string)$s, $occupiedSeats)) {
                    $availableSeats[] = $sCode;
                }
            }

            if (count($availableSeats) < $seatsCount) {
                return back()->with('error', __('messages.flash.not_enough_seats_coach'));
            }

            $seatNumbers = array_slice($availableSeats, 0, $seatsCount);
        }

        $booking = DB::transaction(function () use ($trip, $tripClass, $seatsCount, $seatNumbers, $validated, $totalAmount, $transportClass, $bookingType, $reservationFee) {
            // Decrement availability
            $trip->decrement('seats_available', $seatsCount);
            if ($tripClass) {
                $tripClass->decrement('seats_available', $seatsCount);
            }

            $ref = 'BK-RV-' . date('Y') . '-' . strtoupper(Str::random(6));
            $qrToken = 'QR-RV-' . strtoupper(Str::random(16));

            return Booking::create([
                'booking_reference' => $ref,
                'passenger_id' => Auth::id(),
                'trip_id' => $trip->id,
                'trip_class_id' => $tripClass ? $tripClass->id : null,
                'transport_class' => $transportClass,
                'booking_type' => $bookingType,
                'seats_count' => $seatsCount,
                'seat_numbers' => $seatNumbers,
                'passengers_data' => $validated['passengers'],
                'total_amount' => $totalAmount,
                'reservation_fee' => $reservationFee,
                'reservation_fee_paid' => false,
                'status' => 'pending',
                'qr_code_token' => $qrToken,
                'expires_at' => now()->addMinutes(2), // 2-minute hold to complete checkout
            ]);
        });

        $msg = ($bookingType === 'advance_reservation')
            ? 'Réservation pré-enregistrée ! Veuillez régler les frais de réservation de 500 FCFA sous 2 minutes pour garantir vos places.'
            : 'Réservation enregistrée ! Veuillez procéder au paiement sous 2 minutes pour obtenir votre E-Billet.';

        return redirect()->route('passenger.bookings.checkout', $booking)
            ->with('success', $msg);
    }

    public function checkout(Booking $booking)
    {
        if ($booking->passenger_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status === 'confirmed') {
            return redirect()->route('passenger.bookings.ticket', $booking);
        }

        if ($booking->isExpired()) {
            $msg = $booking->isReserved()
                ? 'Cette réservation a expiré car le solde du billet n\'a pas été réglé au moins 6 heures avant le départ. Vos places ont été libérées.'
                : 'Cette réservation a expiré après dépassement du délai de 2 minutes. Les places ont été libérées.';

            return redirect()->route('passenger.bookings.history')
                ->with('error', $msg);
        }

        $booking->load(['trip.branch', 'trip.departureTerminal', 'trip.arrivalTerminal', 'tripClass', 'payment']);

        return view('passenger.bookings.checkout', compact('booking'));
    }

    public function ticket(Booking $booking)
    {
        if ($booking->passenger_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        $booking->load(['trip.branch', 'trip.vehicle', 'trip.departureTerminal', 'trip.arrivalTerminal', 'tripClass', 'passenger', 'payment']);

        return view('passenger.bookings.ticket', compact('booking'));
    }

    public function pdf(Booking $booking)
    {
        if ($booking->passenger_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        $booking->load(['trip.branch', 'trip.vehicle', 'trip.departureTerminal', 'trip.arrivalTerminal', 'tripClass', 'passenger', 'payment']);

        return view('passenger.bookings.pdf', compact('booking'));
    }

    public function history(Request $request)
    {
        $query = Booking::with(['trip.branch', 'trip.departureTerminal', 'trip.arrivalTerminal', 'tripClass', 'payment'])
            ->where('passenger_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('passenger.bookings.history', compact('bookings'));
    }

    /**
     * Cancel a booking:
     * - Strictly permitted only if > 6 hours before departure.
     * - No cash refund: exact ticket amount credited directly to passenger's E-Wallet balance.
     */
    public function cancel(Booking $booking)
    {
        if ($booking->passenger_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status === 'cancelled') {
            return back()->with('info', __('messages.flash.booking_already_cancelled'));
        }

        $trip = $booking->trip;
        if (!$trip) {
            return back()->with('error', __('messages.flash.trip_not_found'));
        }

        // Strict 6-hour rule verification
        $hoursUntilDeparture = now()->diffInHours($trip->departure_time, false);
        if ($hoursUntilDeparture <= 6) {
            return back()->with('error', 'Annulation refusée : Conformément aux conditions d\'exploitation de Real Voyage, l\'annulation d\'un billet n\'est autorisée qu\'à plus de 6 heures avant le départ (Temps restant : ' . max(0, round($hoursUntilDeparture, 1)) . 'h).');
        }

        $refundAmount = 0;
        $isConfirmedOrPaid = ($booking->status === 'confirmed' || ($booking->payment && $booking->payment->isSuccessful()));

        if ($isConfirmedOrPaid) {
            $refundAmount = $booking->total_amount;
        }

        DB::transaction(function () use ($booking, $trip, $refundAmount) {
            // Restore seat availability
            $trip->increment('seats_available', $booking->seats_count);
            if ($booking->tripClass) {
                $booking->tripClass->increment('seats_available', $booking->seats_count);
            }

            // Refund directly to E-Wallet (No cash refund)
            if ($refundAmount > 0) {
                $passenger = $booking->passenger;
                if ($passenger) {
                    $passenger->creditWallet($refundAmount);
                }
            }

            $booking->update([
                'status' => 'cancelled',
            ]);
        });

        $formattedRefund = number_format($refundAmount, 0, ',', ' ');
        $successMsg = $refundAmount > 0
            ? "Réservation annulée avec succès. Le montant de {$formattedRefund} FCFA a été intégralement crédité sur votre E-Wallet Real Voyage pour vos prochains trajets (aucun remboursement en espèces)."
            : "Réservation annulée avec succès.";

        return back()->with('success', $successMsg);
    }
}
