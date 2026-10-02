<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use App\Services\NotificationService;

class PaymentController extends Controller
{
    protected PaymentGatewayService $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processBookingPayment(Request $request, Booking $booking)
    {
        if ($booking->passenger_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return redirect()->route('passenger.bookings.history')
                ->with('error', __('Accès non autorisé : cette réservation appartient à un autre compte voyageur.'));
        }

        if ($booking->isExpired()) {
            $msg = $booking->isReserved()
                ? 'Cette réservation a expiré car le billet complet n\'a pas été réglé au moins 6 heures avant le départ.'
                : 'Le délai de paiement est écoulé. Cette réservation a expiré.';
            return redirect()->route('passenger.bookings.history')
                ->with('error', $msg);
        }

        $validated = $request->validate([
            'method' => 'required|in:orange_money,mtn_momo,wallet',
            'phone' => 'required_if:method,orange_money,mtn_momo|nullable|string|max:20',
        ]);

        $method = $validated['method'];
        $amount = ($booking->isAdvanceReservation() && !$booking->reservation_fee_paid)
            ? ($booking->reservation_fee > 0 ? $booking->reservation_fee : 500.00)
            : $booking->total_amount;

        // Direct E-Wallet Settlement
        if ($method === 'wallet') {
            $user = Auth::user();
            if ($user->wallet_balance < $amount) {
                return back()->with('error', __('messages.flash.insufficient_wallet', ['balance' => number_format($user->wallet_balance, 0, ',', ' ')]));
            }

            DB::transaction(function () use ($user, $booking, $amount) {
                $user->debitWallet($amount);

                $ref = 'PAY-RV-WAL-' . strtoupper(Str::random(8));
                Payment::create([
                    'payment_reference' => $ref,
                    'user_id' => $user->id,
                    'payable_type' => Booking::class,
                    'payable_id' => $booking->id,
                    'payment_type' => $booking->isAdvanceReservation() && !$booking->reservation_fee_paid ? 'reservation_fee' : 'ticket',
                    'amount' => $amount,
                    'currency' => 'XAF',
                    'method' => 'wallet',
                    'payer_phone' => $user->phone ?? 'N/A',
                    'transaction_ref' => 'WALLET-' . strtoupper(Str::random(10)),
                    'status' => 'successful',
                    'gateway_response' => [
                        'settled_via' => 'Real Voyage E-Wallet',
                        'debited_at' => now()->toIso8601String(),
                        'balance_after' => $user->wallet_balance,
                    ],
                ]);

                if ($booking->isAdvanceReservation() && !$booking->reservation_fee_paid) {
                    $booking->update([
                        'reservation_fee_paid' => true,
                        'status' => 'reserved',
                    ]);
                } else {
                    $booking->update([
                        'status' => 'confirmed',
                    ]);
                    event(new \App\Events\BookingConfirmedEvent($booking));
                }
            });

            return redirect()->route('passenger.bookings.ticket', $booking)
                ->with('success', __('messages.flash.wallet_payment_success', ['amount' => number_format($amount, 0, ',', ' ')]));
        }

        // Mobile Money Flow
        $payment = $this->paymentService->initiatePayment($booking, $method, $validated['phone']);

        return redirect()->route('passenger.payments.show', $payment);
    }

    public function processShipmentPayment(Request $request, Shipment $shipment)
    {
        if ($shipment->sender_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return redirect()->route('passenger.shipments.index')
                ->with('error', __('Accès non autorisé : cet envoi de colis appartient à un autre utilisateur.'));
        }

        $validated = $request->validate([
            'method' => 'required|in:orange_money,mtn_momo,wallet',
            'phone' => 'required_if:method,orange_money,mtn_momo|nullable|string|max:20',
        ]);

        $method = $validated['method'];
        $amount = $shipment->total_amount;

        // Direct E-Wallet Settlement
        if ($method === 'wallet') {
            $user = Auth::user();
            if ($user->wallet_balance < $amount) {
                return back()->with('error', __('messages.flash.insufficient_wallet', ['balance' => number_format($user->wallet_balance, 0, ',', ' ')]));
            }

            DB::transaction(function () use ($user, $shipment, $amount) {
                $user->debitWallet($amount);

                $ref = 'PAY-RV-WAL-' . strtoupper(Str::random(8));
                Payment::create([
                    'payment_reference' => $ref,
                    'user_id' => $user->id,
                    'payable_type' => Shipment::class,
                    'payable_id' => $shipment->id,
                    'payment_type' => 'shipment',
                    'amount' => $amount,
                    'currency' => 'XAF',
                    'method' => 'wallet',
                    'payer_phone' => $user->phone ?? 'N/A',
                    'transaction_ref' => 'WALLET-' . strtoupper(Str::random(10)),
                    'status' => 'successful',
                    'gateway_response' => [
                        'settled_via' => 'Real Voyage E-Wallet',
                        'debited_at' => now()->toIso8601String(),
                        'balance_after' => $user->wallet_balance,
                    ],
                ]);

                $shipment->update([
                    'status' => 'in_transit',
                ]);
            });

            return redirect()->route('passenger.shipments.show', $shipment)
                ->with('success', __('messages.flash.wallet_shipment_success', ['amount' => number_format($amount, 0, ',', ' ')]));
        }

        // Mobile Money Flow
        $payment = $this->paymentService->initiatePayment($shipment, $method, $validated['phone']);

        return redirect()->route('passenger.payments.show', $payment);
    }

    public function show(Payment $payment)
    {
        if ($payment->user_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return redirect()->route('passenger.dashboard')
                ->with('error', __('Accès non autorisé à cette transaction.'));
        }

        $payment->load(['payable', 'user']);

        return view('passenger.payments.show', compact('payment'));
    }

    public function simulate(Request $request, Payment $payment)
    {
        if ($payment->user_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            return redirect()->route('passenger.dashboard')
                ->with('error', __('Accès non autorisé à cette transaction.'));
        }

        $action = $request->input('action', 'success');
        $success = ($action === 'success');

        $this->paymentService->processSandboxPayment($payment, $success);

        if ($success) {
            if ($payment->payable instanceof Booking) {
                if ($payment->payment_type === 'reservation_fee') {
                    return redirect()->route('passenger.bookings.history')
                        ->with('success', __('messages.flash.reservation_fee_paid'));
                }

                return redirect()->route('passenger.bookings.ticket', $payment->payable)
                    ->with('success', __('messages.flash.momo_payment_success'));
            } else {
                return redirect()->route('passenger.shipments.show', $payment->payable)
                    ->with('success', __('messages.flash.shipment_paid'));
            }
        } else {
            return back()->with('error', __('messages.flash.payment_failed'));
        }
    }
}
