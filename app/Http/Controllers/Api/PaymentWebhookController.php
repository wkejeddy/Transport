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

    public function mtnMomoCallback(Request $request)
    {
        Log::info('MTN MoMo Webhook Received:', $request->all());

        $ref = $request->input('reference') ?? $request->input('payment_reference');
        $status = $request->input('status'); // SUCCESSFUL, FAILED

        $payment = Payment::where('payment_reference', $ref)
            ->orWhere('transaction_ref', $ref)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $isSuccess = ($status === 'SUCCESSFUL' || $status === 'SUCCESS');
        $this->paymentService->processSandboxPayment($payment, $isSuccess);

        return response()->json(['message' => 'MTN MoMo webhook processed']);
    }

    public function orangeMoneyCallback(Request $request)
    {
        Log::info('Orange Money Webhook Received:', $request->all());

        $ref = $request->input('notif_token') ?? $request->input('payment_reference') ?? $request->input('order_id');
        $status = $request->input('status'); // SUCCESS, FAIL

        $payment = Payment::where('payment_reference', $ref)
            ->orWhere('transaction_ref', $ref)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $isSuccess = ($status === 'SUCCESS' || $status === '200');
        $this->paymentService->processSandboxPayment($payment, $isSuccess);

        return response()->json(['message' => 'Orange Money webhook processed']);
    }
}
