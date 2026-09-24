<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\Booking;
use App\Models\Vehicle;

class HomeController extends Controller
{
    public function index()
    {
        $branches = Branch::active()->get();

        $featuredTrips = Trip::with(['branch', 'classes', 'vehicle'])
            ->where('status', 'scheduled')
            ->where('departure_time', '>=', now())
            ->orderBy('departure_time')
            ->take(6)
            ->get();

        $upcomingTrips = Trip::with(['branch', 'classes', 'vehicle'])
            ->where('status', 'scheduled')
            ->where('departure_time', '>=', now())
            ->orderBy('departure_time')
            ->take(4)
            ->get();

        $cities = [
            'Douala', 'Yaoundé', 'Bafoussam', 'Bamenda', 'Kribi', 'Limbe', 'Bertoua', 'Ebolowa'
        ];

        $stats = [
            'total_trips' => Trip::where('status', 'scheduled')->count(),
            'total_vehicles' => Vehicle::where('status', 'active')->count(),
            'active_shipments' => Shipment::whereIn('status', ['registered', 'in_transit', 'arrived'])->count(),
            'avg_punctuality' => 98.5,
            'rating_avg' => 4.9,
            'total_reviews' => 124,
        ];

        return view('home', compact(
            'branches',
            'featuredTrips', 
            'upcomingTrips', 
            'cities', 
            'stats'
        ));
    }
}
