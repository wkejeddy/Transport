<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;

class ShipmentController extends Controller
{
    public function trackingSearch(Request $request)
    {
        $trackingCode = $request->get('code');
        $shipment = null;

        if ($trackingCode) {
            $shipment = Shipment::with(['branch', 'trip', 'sender'])
                ->where('tracking_code', strtoupper(trim($trackingCode)))
                ->first();
        }

        return view('shipments.track', compact('shipment', 'trackingCode'));
    }
}
