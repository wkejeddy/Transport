<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Trip;
use App\Models\Dispute;
use App\Models\Payment;
use App\Models\Terminal;
use App\Models\Branch;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $branch = $user->branch ?? Branch::first();

        $branchId = $branch?->id;
        $terminal = $user->terminal;

        // Base trip query filtered by branch/terminal
        $tripsQuery = Trip::query();
        if ($branchId) {
            $tripsQuery->where('branch_id', $branchId);
        }
        if ($terminal) {
            $tripsQuery->where(function ($q) use ($terminal) {
                $q->where('departure_terminal_id', $terminal->id)
                  ->orWhere('arrival_terminal_id', $terminal->id);
            });
        }

        $totalTrips = (clone $tripsQuery)->count();
        $activeTrips = (clone $tripsQuery)->whereIn('status', ['scheduled', 'boarding', 'in_transit'])->count();

        // Bookings filtered by branch/terminal
        $bookingsQuery = Booking::whereHas('trip', function ($q) use ($branchId, $terminal) {
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            if ($terminal) {
                $q->where('departure_terminal_id', $terminal->id);
            }
        })->where('status', 'confirmed');

        $totalBookings = (clone $bookingsQuery)->count();
        $ticketRevenue = (clone $bookingsQuery)->sum('total_amount');

        // Cargo filtered by branch/terminal
        $shipmentsQuery = Shipment::query();
        if ($branchId) {
            $shipmentsQuery->where('branch_id', $branchId);
        }
        if ($terminal) {
            $shipmentsQuery->where(function ($q) use ($terminal) {
                $q->where('origin_terminal_id', $terminal->id)
                  ->orWhere('destination_terminal_id', $terminal->id);
            });
        }

        $cargoRevenue = (clone $shipmentsQuery)
            ->whereHas('payment', function ($q) {
                $q->where('status', 'successful');
            })->sum('total_amount');

        $totalRevenue = $ticketRevenue + $cargoRevenue;

        // Occupancy calculation for terminal trips
        $recentTrips = (clone $tripsQuery)->with('vehicle')->latest()->take(10)->get();
        $totalCapacity = $recentTrips->sum(function ($trip) {
            return $trip->vehicle->capacity_seats ?? 80;
        });
        $seatsRemaining = $recentTrips->sum('seats_available');
        $seatsSold = max(0, $totalCapacity - $seatsRemaining);
        $occupancyRate = $totalCapacity > 0 ? round(($seatsSold / $totalCapacity) * 100, 1) : 0;

        // Route Occupancy & Analytics for this Branch
        $routeMetrics = (clone $tripsQuery)->select('departure_city', 'arrival_city')
            ->selectRaw('COUNT(*) as trips_count')
            ->selectRaw('SUM(seats_available) as seats_left')
            ->groupBy('departure_city', 'arrival_city')
            ->get()
            ->map(function ($r) {
                $cap = $r->trips_count * 75;
                $sold = max(0, $cap - (int)$r->seats_left);
                return [
                    'label' => "{$r->departure_city} ➔ {$r->arrival_city}",
                    'occupancy' => $cap > 0 ? min(100, round(($sold / $cap) * 100, 1)) : 0,
                    'trips' => $r->trips_count,
                ];
            });

        $openDisputesQuery = Dispute::where('status', 'open');
        if ($branchId) {
            $openDisputesQuery->where('branch_id', $branchId);
        }
        $openDisputes = $openDisputesQuery->orderBy('created_at')->get();

        $upcomingTrips = (clone $tripsQuery)
            ->with(['vehicle', 'classes', 'departureTerminal', 'arrivalTerminal'])
            ->where('departure_time', '>=', now())
            ->orderBy('departure_time')
            ->take(5)
            ->get();

        $recentShipments = (clone $shipmentsQuery)
            ->with(['originTerminal', 'destinationTerminal'])
            ->latest()
            ->take(5)
            ->get();

        return view('manager.dashboard', compact(
            'branch',
            'terminal',
            'totalTrips',
            'activeTrips',
            'totalBookings',
            'ticketRevenue',
            'cargoRevenue',
            'totalRevenue',
            'occupancyRate',
            'routeMetrics',
            'openDisputes',
            'upcomingTrips',
            'recentShipments'
        ));
    }
}