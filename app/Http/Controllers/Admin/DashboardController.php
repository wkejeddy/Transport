<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Terminal;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Dispute;
use App\Models\Payment;
use App\Models\Branch;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'passengers_count' => User::where('role', 'passager')->count(),
            'staff_count' => User::whereIn('role', ['admin', 'manager'])->count(),
            'total_terminals' => Terminal::count(),
            
            'total_trips' => Trip::count(),
            'active_trips' => Trip::where('status', 'scheduled')->where('departure_time', '>=', now())->count(),
            'morning_departures_count' => Trip::whereTime('departure_time', '10:00:00')->count(),
            'night_departures_count' => Trip::whereTime('departure_time', '21:30:00')->count(),
            
            'total_bookings' => Booking::whereIn('status', ['confirmed', 'checked_in'])->count(),
            'today_bookings' => Booking::whereDate('created_at', today())->count(),
            'total_shipments' => Shipment::count(),
            'active_shipments' => Shipment::whereIn('status', ['registered', 'in_transit', 'arrived'])->count(),
            
            'total_gmv' => Payment::where('status', 'successful')->sum('amount'),
            'momo_gmv' => Payment::where('status', 'successful')->where('method', 'mtn_momo')->sum('amount'),
            'om_gmv' => Payment::where('status', 'successful')->where('method', 'orange_money')->sum('amount'),
            'wallet_gmv' => Payment::where('status', 'successful')->where('method', 'wallet')->sum('amount'),
            'today_revenue' => Payment::where('status', 'successful')->whereDate('created_at', today())->sum('amount'),
            
            'open_disputes' => Dispute::where('status', 'open')->count(),
        ];

        // Terminals breakdown by region
        $terminalsByRegion = Terminal::withCount(['departureTrips', 'arrivalTrips'])
            ->get()
            ->groupBy('region');

        $recentBookings = Booking::with(['trip.departureTerminal', 'trip.arrivalTerminal', 'passenger', 'payment'])->latest()->take(6)->get();
        $recentDisputes = Dispute::with(['branch', 'user'])->latest()->take(6)->get();
        $recentPayments = Payment::with(['user', 'payable'])->latest()->take(6)->get();

        return view('admin.dashboard', compact('stats', 'terminalsByRegion', 'recentBookings', 'recentDisputes', 'recentPayments'));
    }
}
