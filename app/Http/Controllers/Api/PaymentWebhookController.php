<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected PaymentGatewayService $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Webhook signature and timestamp replay validation
     */
    protected function verifyWebhookSecurity(Request $request, string $provider): bool
    {
        // Replay attack protection if timestamp provided (5-minute tolerance)
        $timestamp = $request->header('X-Timestamp') ?? $request->header('Timestamp');
        if ($timestamp && is_numeric($timestamp)) {
            if (abs(time() - (int)$timestamp) > 300) {
                Log::warning("{$provider} Webhook rejected: timestamp drift exceeds 5 minutes.");
                return false;
            }
        }

        $configuredSecret = config("payment.{$provider}.webhook_secret");
        if (!empty($configuredSecret)) {
            $receivedSignature = $request->header('X-Signature') ?? $request->header('X-Callback-Signature');
            $expectedSignature = hash_hmac('sha256', $request->getContent(), $configuredSecret);
            if ($receivedSignature && !hash_equals($expectedSignature, $receivedSignature)) {
                Log::warning("{$provider} Webhook rejected: invalid HMAC signature.");
                return false;
            }
        }

        return true;
    }

    public function mtnMomoCallback(Request $request)
    {
        Log::info('MTN MoMo Webhook Received:', $request->all());

        if (!$this->verifyWebhookSecurity($request, 'mtn_momo')) {
            return response()->json(['error' => 'Unauthorized / Signature mismatch'], 401);
        }

        $ref = $request->input('reference') ?? $request->input('payment_reference') ?? $request->input('externalId');
        $status = strtoupper($request->input('status', ''));

        $payment = Payment::where('payment_reference', $ref)
            ->orWhere('transaction_ref', $ref)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $isSuccess = in_array($status, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED', '200']);
        $this->paymentService->processSandboxPayment($payment, $isSuccess);

        return response()->json(['message' => 'MTN MoMo webhook processed', 'status' => $payment->fresh()->status]);
    }

    public function orangeMoneyCallback(Request $request)
    {
        Log::info('Orange Money Webhook Received:', $request->all());

        if (!$this->verifyWebhookSecurity($request, 'orange_money')) {
            return response()->json(['error' => 'Unauthorized / Signature mismatch'], 401);
        }

        $ref = $request->input('notif_token') ?? $request->input('payment_reference') ?? $request->input('order_id');
        $status = strtoupper($request->input('status', ''));

        $payment = Payment::where('payment_reference', $ref)
            ->orWhere('transaction_ref', $ref)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $isSuccess = in_array($status, ['SUCCESS', 'SUCCESSFUL', '200', 'PAID']);
        $this->paymentService->processSandboxPayment($payment, $isSuccess);

        return response()->json(['message' => 'Orange Money webhook processed', 'status' => $payment->fresh()->status]);
    }
}