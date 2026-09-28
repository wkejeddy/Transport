<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Booking;
use App\Services\BookingService;
use Exception;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

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
            'is_round_trip' => 'nullable|boolean',
            'return_trip_id' => 'nullable|exists:trips,id',
        ]);

        try {
            $booking = $this->bookingService->createBooking(Auth::user(), $trip, $validated);

            $msg = ($booking->isAdvanceReservation())
                ? 'Réservation pré-enregistrée ! Veuillez régler les frais de réservation de 500 FCFA sous 2 minutes pour garantir vos places.'
                : 'Réservation enregistrée ! Veuillez procéder au paiement sous 2 minutes pour obtenir votre E-Billet.';

            return redirect()->route('passenger.bookings.checkout', $booking)
                ->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
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

    public function thermal(Booking $booking)
    {
        if ($booking->passenger_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        $booking->load(['trip.branch', 'trip.vehicle', 'trip.departureTerminal', 'trip.arrivalTerminal', 'tripClass', 'passenger', 'payment']);

        return view('passenger.bookings.thermal', compact('booking'));
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
     * Reschedule a booking:
     * - Only permitted if > 12 hours before departure.
     * - Computes fare difference and settles via wallet or refunds to wallet.
     * - Re-assigns seats and updates QR code token.
     */
    public function reschedule(Request $request, Booking $booking)
    {
        if ($booking->passenger_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'new_trip_id' => 'required|exists:trips,id',
            'trip_class_id' => 'nullable|exists:trip_classes,id',
            'seat_numbers' => 'nullable|array',
            'passengers' => 'nullable|array',
        ]);

        $newTrip = Trip::findOrFail($validated['new_trip_id']);
        $newTripClass = !empty($validated['trip_class_id'])
            ? TripClass::findOrFail($validated['trip_class_id'])
            : null;

        try {
            $updatedBooking = $this->bookingService->rescheduleBooking(
                $booking,
                $newTrip,
                $newTripClass,
                $validated['seat_numbers'] ?? [],
                $validated['passengers'] ?? []
            );

            return redirect()->route('passenger.bookings.ticket', $updatedBooking)
                ->with('success', __('messages.flash.reschedule_success') ?: 'Voyage reporté avec succès ! Nouveau billet généré.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
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
            return back()->with('info', __('messages.flash.booking_already_cancelled') ?: 'Cette réservation est déjà annulée.');
        }

        try {
            $this->bookingService->cancelBooking($booking);

            $refundAmount = (float)$booking->total_amount;
            $formattedRefund = number_format($refundAmount, 0, ',', ' ');
            $successMsg = $refundAmount > 0
                ? "Réservation annulée avec succès. Le montant de {$formattedRefund} FCFA a été intégralement crédité sur votre E-Wallet Real Voyage pour vos prochains trajets (aucun remboursement en espèces)."
                : "Réservation annulée avec succès.";

            return back()->with('success', $successMsg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}