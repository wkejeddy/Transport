<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Shipment;

class TripMonitorController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $tripsQuery = Trip::with(['branch', 'vehicle', 'classes']);

        if ($status) {
            $tripsQuery->where('status', $status);
        }

        $trips = $tripsQuery->orderBy('departure_time', 'desc')->paginate(10);
        $activeShipments = Shipment::with(['branch', 'trip'])->whereIn('status', ['registered', 'in_transit', 'arrived'])->latest()->take(10)->get();

        return view('admin.monitor.index', compact('trips', 'activeShipments', 'status'));
    }
}
