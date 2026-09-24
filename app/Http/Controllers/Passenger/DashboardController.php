<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Shipment;
use App\Models\TripAlert;
use App\Models\Dispute;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $activeBookings = Booking::with(['trip.branch', 'tripClass'])
            ->where('passenger_id', $user->id)
            ->whereIn('status', ['pending', 'reserved', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $activeShipments = Shipment::with(['branch', 'trip'])
            ->where('sender_id', $user->id)
            ->whereIn('status', ['registered', 'in_transit', 'arrived'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $alerts = TripAlert::with('trip')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats = [
            'total_bookings' => Booking::where('passenger_id', $user->id)->count(),
            'total_shipments' => Shipment::where('sender_id', $user->id)->count(),
            'total_disputes' => Dispute::where('raised_by_user_id', $user->id)->count(),
            'confirmed_trips' => Booking::where('passenger_id', $user->id)->where('status', 'confirmed')->count(),
        ];

        return view('passenger.dashboard', compact('activeBookings', 'activeShipments', 'alerts', 'stats'));
    }
}
