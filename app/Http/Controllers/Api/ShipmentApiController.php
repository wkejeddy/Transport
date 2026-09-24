<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;

class ShipmentApiController extends Controller
{
    public function track(string $code)
    {
        $shipment = Shipment::with(['branch', 'trip'])
            ->where('tracking_code', strtoupper(trim($code)))
            ->first();

        if (!$shipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Colis introuvable avec ce code de suivi.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'tracking_code' => $shipment->tracking_code,
                'status' => $shipment->status,
                'company' => 'Real Voyage Transport S.A.',
                'branch' => $shipment->branch?->name ?? 'Gare Centrale',
                'transport_mode' => 'road',
                'recipient_name' => $shipment->recipient_name,
                'recipient_city' => $shipment->recipient_city,
                'destination_station' => $shipment->destination_station,
                'weight_kg' => $shipment->weight_kg,
                'insured' => $shipment->insured,
                'created_at' => $shipment->created_at->toIso8601String(),
                'collected_at' => $shipment->collected_at ? $shipment->collected_at->toIso8601String() : null,
            ],
        ]);
    }
}
