<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Initiate a payment for a Booking or Shipment
     */
    public function initiatePayment($payable, string $method, string $payerPhone): Payment
    {
        $paymentType = 'ticket';
        $amount = $payable->total_amount;

        if ($payable instanceof Booking) {
            if ($payable->isAdvanceReservation() && !$payable->reservation_fee_paid) {
                $paymentType = 'reservation_fee';
                $amount = $payable->reservation_fee > 0 ? $payable->reservation_fee : 500.00;
            }
        } elseif ($payable instanceof Shipment) {
            $paymentType = 'shipment';
        }

        $ref = 'PAY-' . strtoupper(Str::random(10));
        $externalRef = ($method === 'orange_money' ? 'OM-CM-' : 'MOMO-CM-') . strtoupper(Str::random(8));

        $mode = config('payment.default_mode', 'sandbox');

        $payment = Payment::create([
            'payment_reference' => $ref,
            'user_id' => $payable instanceof Booking ? $payable->passenger_id : $payable->sender_id,
            'payable_type' => get_class($payable),
            'payable_id' => $payable->id,
            'payment_type' => $paymentType,
            'amount' => $amount,
            'currency' => 'XAF',
            'method' => $method,
            'payer_phone' => $payerPhone,
            'transaction_ref' => $externalRef,
            'status' => 'pending',
            'gateway_response' => [
                'initiated_at' => now()->toIso8601String(),
                'channel' => $method,
                'prompt' => "Composez le #150*50# (OM) ou *126# (MTN) pour valider {$amount} XAF",
                'mode' => $mode,
                'sandbox' => ($mode === 'sandbox'),
            ],
        ]);

        // If configured for live API integration
        if ($mode !== 'sandbox') {
            if ($method === 'orange_money' && config('payment.orange_money.merchant_key')) {
                $this->initiateOrangeMoneyLive($payment);
            } elseif ($method === 'mtn_momo' && config('payment.mtn_momo.api_key')) {
                $this->initiateMtnMoMoLive($payment);
            } elseif (config('payment.campay.app_username')) {
                $this->initiateCampayLive($payment);
            }
        }

        return $payment;
    }

    /**
     * Live Orange Money Web Payment API
     * https://developer.orange.com
     */
    protected function initiateOrangeMoneyLive(Payment $payment): void
    {
        try {
            $tokenResponse = Http::asForm()->withHeaders([
                'Authorization' => 'Basic ' . base64_encode(config('payment.orange_money.client_id') . ':' . config('payment.orange_money.client_secret')),
            ])->post('https://api.orange.com/oauth/v3/token', [
                'grant_type' => 'client_credentials',
            ]);

            if ($tokenResponse->successful()) {
                $accessToken = $tokenResponse->json('access_token');

                $webpayResponse = Http::withToken($accessToken)->post(config('payment.orange_money.base_url') . '/webpayment', [
                    'merchant_key' => config('payment.orange_money.merchant_key'),
                    'currency' => 'OUV',
                    'order_id' => $payment->payment_reference,
                    'amount' => $payment->amount,
                    'return_url' => config('payment.orange_money.return_url'),
                    'cancel_url' => config('payment.orange_money.cancel_url'),
                    'notif_url' => url('/api/v1/payments/webhook/orange'),
                    'lang' => 'fr',
                    'reference' => 'TransportCM ' . $payment->payment_reference,
                ]);

                if ($webpayResponse->successful()) {
                    $payment->update([
                        'gateway_response' => array_merge($payment->gateway_response ?? [], [
                            'payment_url' => $webpayResponse->json('payment_url'),
                            'pay_token' => $webpayResponse->json('pay_token'),
                        ]),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Orange Money Live API Error: ' . $e->getMessage());
        }
    }

    /**
     * Live MTN MoMo Collection Request-To-Pay API
     * https://momodeveloper.mtn.com
     */
    protected function initiateMtnMoMoLive(Payment $payment): void
    {
        try {
            $tokenResponse = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => config('payment.mtn_momo.primary_key'),
                'Authorization' => 'Basic ' . base64_encode(config('payment.mtn_momo.user_id') . ':' . config('payment.mtn_momo.api_key')),
            ])->post(config('payment.mtn_momo.base_url') . '/collection/token/');

            if ($tokenResponse->successful()) {
                $accessToken = $tokenResponse->json('access_token');
                $xRefId = Str::uuid()->toString();

                $rtpResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'X-Reference-Id' => $xRefId,
                    'X-Target-Environment' => config('payment.mtn_momo.target_environment'),
                    'Ocp-Apim-Subscription-Key' => config('payment.mtn_momo.primary_key'),
                    'X-Callback-Url' => config('payment.mtn_momo.callback_url'),
                ])->post(config('payment.mtn_momo.base_url') . '/collection/v1_0/requesttopay', [
                    'amount' => (string)$payment->amount,
                    'currency' => 'EUR', // Sandbox uses EUR, Live uses XAF
                    'externalId' => $payment->payment_reference,
                    'payer' => [
                        'partyIdType' => 'MSISDN',
                        'partyId' => '237' . ltrim($payment->payer_phone, '237'),
                    ],
                    'payerMessage' => 'Paiement TransportCM ' . $payment->payment_reference,
                    'payeeNote' => 'Transport Billet/Colis',
                ]);

                if ($rtpResponse->status() === 202) {
                    $payment->update([
                        'transaction_ref' => $xRefId,
                        'gateway_response' => array_merge($payment->gateway_response ?? [], [
                            'mtn_reference_id' => $xRefId,
                            'status' => 'PENDING_USSD_PROMPT',
                        ]),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('MTN MoMo Live API Error: ' . $e->getMessage());
        }
    }

    /**
     * Live Campay Unified Mobile Money API (Cameroon)
     * Collects payments from both Orange Money and MTN MoMo with 1 integration.
     * https://www.campay.net
     */
    protected function initiateCampayLive(Payment $payment): void
    {
        try {
            $tokenResponse = Http::post('https://www.campay.net/api/token/', [
                'username' => config('payment.campay.app_username'),
                'password' => config('payment.campay.app_password'),
            ]);

            if ($tokenResponse->successful()) {
                $token = $tokenResponse->json('token');

                $collectResponse = Http::withToken($token)->post('https://www.campay.net/api/collect/', [
                    'amount' => (string)$payment->amount,
                    'currency' => 'XAF',
                    'from' => '237' . ltrim($payment->payer_phone, '237'),
                    'description' => 'Paiement TransportCM ' . $payment->payment_reference,
                    'external_reference' => $payment->payment_reference,
                ]);

                if ($collectResponse->successful()) {
                    $payment->update([
                        'transaction_ref' => $collectResponse->json('reference'),
                        'gateway_response' => array_merge($payment->gateway_response ?? [], [
                            'campay_reference' => $collectResponse->json('reference'),
                            'ussd_code' => $collectResponse->json('ussd_code'),
                        ]),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Campay API Error: ' . $e->getMessage());
        }
    }

    /**
     * Process sandbox simulated payment confirmation
     */
    public function processSandboxPayment(Payment $payment, bool $simulateSuccess = true): bool
    {
        return DB::transaction(function () use ($payment, $simulateSuccess) {
            if ($simulateSuccess) {
                $payment->update([
                    'status' => 'successful',
                    'gateway_response' => array_merge($payment->gateway_response ?? [], [
                        'completed_at' => now()->toIso8601String(),
                        'telco_receipt' => 'TXN-' . rand(10000000, 99999999),
                        'status_code' => '200_SUCCESS',
                    ]),
                ]);

                // Update payable
                $payable = $payment->payable;
                if ($payable instanceof Booking) {
                    if ($payment->payment_type === 'reservation_fee') {
                        $trip = $payable->trip;
                        $deadline = $trip ? $trip->departure_time->copy()->subHours(6) : now()->addHours(2);

                        $payable->update([
                            'reservation_fee_paid' => true,
                            'status' => 'reserved',
                            'expires_at' => $deadline,
                        ]);
                        // Dispatch simulated SMS & WhatsApp confirmation for reservation hold
                        NotificationService::sendReservationFeeConfirmation($payable);
                    } else {
                        $payable->update([
                            'status' => 'confirmed',
                        ]);
                        // Dispatch simulated SMS & WhatsApp confirmation for e-ticket
                        NotificationService::sendBookingConfirmation($payable);
                    }
                } elseif ($payable instanceof Shipment) {
                    $payable->update([
                        'status' => 'in_transit',
                    ]);
                    // Dispatch simulated OTP SMS to recipient
                    NotificationService::sendCargoPickupOtp($payable);
                }

                Log::info("Payment {$payment->payment_reference} confirmed successfully.");
                return true;
            } else {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => array_merge($payment->gateway_response ?? [], [
                        'failed_at' => now()->toIso8601String(),
                        'error' => 'Paiement annulé par l\'utilisateur ou solde insuffisant.',
                        'status_code' => '400_FAILED',
                    ]),
                ]);

                $payable = $payment->payable;
                if ($payable instanceof Booking) {
                    // Only mark payment_failed if pending initial fee
                    if ($payable->status === 'pending') {
                        $payable->update([
                            'status' => 'payment_failed',
                        ]);
                    }
                }

                return false;
            }
        });
    }
}
