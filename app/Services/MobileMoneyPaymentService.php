<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Exception;

class MobileMoneyPaymentService
{
    /**
     * Primary corporate account details for Real Voyage.
     */
    public const CORPORATE_SETTLEMENT_ACCOUNT = [
        'company' => 'Real Voyage Transport S.A.',
        'merchant_om' => 'OM-REALVOYAGE-CM',
        'merchant_momo' => 'MOMO-REALVOYAGE-CM',
        'bank_settlement_iban' => 'CM21 10005 00001 01234567890 44',
    ];

    /**
     * Process Mobile Money (MTN MoMo or Orange Money) payment into Real Voyage corporate merchant account.
     */
    public static function processMobileMoney(string $method, string $phone, float $amount, Model $payable, User $user): Payment
    {
        $prefix = ($method === 'orange_money') ? 'OM-RV-' : 'MOMO-RV-';
        $paymentRef = 'PAY-' . strtoupper(Str::random(10));
        $transactionRef = $prefix . strtoupper(Str::random(8));

        $payment = Payment::create([
            'payment_reference' => $paymentRef,
            'user_id' => $user->id,
            'payable_type' => get_class($payable),
            'payable_id' => $payable->id,
            'amount' => $amount,
            'currency' => 'XAF',
            'method' => $method, // orange_money or mtn_momo
            'payer_phone' => $phone,
            'transaction_ref' => $transactionRef,
            'status' => 'successful',
            'gateway_response' => [
                'status' => 'SUCCESS',
                'merchant' => self::CORPORATE_SETTLEMENT_ACCOUNT['company'],
                'settlement_account' => ($method === 'orange_money') 
                    ? self::CORPORATE_SETTLEMENT_ACCOUNT['merchant_om'] 
                    : self::CORPORATE_SETTLEMENT_ACCOUNT['merchant_momo'],
                'timestamp' => now()->toISOString(),
            ],
        ]);

        return $payment;
    }

    /**
     * Process payment using Passenger E-Wallet balance.
     */
    public static function processWalletPayment(User $user, float $amount, Model $payable): Payment
    {
        if ($user->wallet_balance < $amount) {
            throw new Exception("Solde de portefeuille insuffisant ({$user->wallet_balance} FCFA disponible, {$amount} FCFA requis).");
        }

        // Debit passenger wallet
        $user->debitWallet($amount);

        $payment = Payment::create([
            'payment_reference' => 'PAY-WLT-' . strtoupper(Str::random(10)),
            'user_id' => $user->id,
            'payable_type' => get_class($payable),
            'payable_id' => $payable->id,
            'amount' => $amount,
            'currency' => 'XAF',
            'method' => 'wallet',
            'payer_phone' => $user->phone ?? 'WALLET',
            'transaction_ref' => 'WLT-RV-' . strtoupper(Str::random(8)),
            'status' => 'successful',
            'gateway_response' => [
                'status' => 'SUCCESS',
                'payment_source' => 'PASSENGER_E_WALLET',
                'settlement_merchant' => self::CORPORATE_SETTLEMENT_ACCOUNT['company'],
                'remaining_wallet_balance' => (float)$user->fresh()->wallet_balance,
            ],
        ]);

        return $payment;
    }
}
