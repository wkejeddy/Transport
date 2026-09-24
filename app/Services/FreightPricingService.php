<?php

namespace App\Services;

class FreightPricingService
{
    /**
     * Legal Liability Policy Disclosure required for Real Voyage Freight operations.
     */
    public const LIABILITY_NOTICE = "In case of loss or damage, Real Voyage's liability and reimbursement is fixed between 2x and 5x the declared value.";
    public const LIABILITY_NOTICE_FR = "En cas de perte ou d'avarie, la responsabilité et le remboursement de Real Voyage sont strictement fixés entre 2x et 5x la valeur déclarée de la marchandise.";

    /**
     * Calculate freight fee details:
     * Strict Rule: Exactly 10% of the declared value of the goods.
     */
    public static function calculateFee(float $declaredValue, float $weightKg = 1.0, bool $insured = false): array
    {
        // 10% of declared value rule
        $calculatedFee = $declaredValue * 0.10;
        $cargoFee = max(1500.0, round($calculatedFee, 2));

        $insuranceFee = $insured ? round($declaredValue * 0.02, 2) : 0.0;
        $totalAmount = $cargoFee + $insuranceFee;

        return [
            'declared_value' => $declaredValue,
            'weight_kg' => $weightKg,
            'percentage_applied' => 10,
            'cargo_fee' => $cargoFee,
            'insurance_fee' => $insuranceFee,
            'total_amount' => $totalAmount,
            'liability_notice' => self::LIABILITY_NOTICE,
            'liability_notice_fr' => self::LIABILITY_NOTICE_FR,
        ];
    }

    /**
     * Helper to get raw fee amount directly.
     */
    public static function calculateFeeAmount(float $declaredValue): float
    {
        return max(1500.0, round($declaredValue * 0.10, 2));
    }
}
