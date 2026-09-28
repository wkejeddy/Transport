<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Branch;
use App\Models\Terminal;
use App\Services\SeatMapService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['branch', 'classes', 'vehicle', 'departureTerminal', 'arrivalTerminal'])
            ->whereIn('status', ['scheduled', 'delayed']);

        $from = $request->input('from') ?: $request->input('departure');
        if (!empty($from)) {
            $query->where(function($q) use ($from) {
                $q->where('departure_city', 'like', '%' . $from . '%')
                  ->orWhereHas('departureTerminal', function($t) use ($from) {
                      $t->where('name', 'like', '%' . $from . '%');
                  });
            });
        }

        $to = $request->input('to') ?: $request->input('arrival');
        if (!empty($to)) {
            $query->where(function($q) use ($to) {
                $q->where('arrival_city', 'like', '%' . $to . '%')
                  ->orWhereHas('arrivalTerminal', function($t) use ($to) {
                      $t->where('name', 'like', '%' . $to . '%');
                  });
            });
        }

        if ($request->filled('departure_time')) {
            // Optional filter for fixed 10h00 or 21h30 departures
            $query->whereTime('departure_time', $request->departure_time);
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('departure_time', $date);
        } else {
            $query->where('departure_time', '>=', now());
        }

        if ($request->filled('class_code')) {
            $query->whereHas('classes', function ($q) use ($request) {
                $q->where('class_code', $request->class_code);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'time_asc');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'time_desc':
                $query->orderBy('departure_time', 'desc');
                break;
            default:
                $query->orderBy('departure_time', 'asc');
                break;
        }

        $trips = $query->paginate(10)->withQueryString();

        $terminals = Terminal::active()->orderBy('region')->orderBy('name')->get();
        $cities = ['Douala', 'Yaoundé', 'Bafoussam', 'Dschang', 'Mbouda'];

        return view('trips.index', compact('trips', 'terminals', 'cities'));
    }

    public function show(Trip $trip)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('register.passenger'))
                ->with('info', __('Veuillez créer un compte ou vous connecter avant de choisir votre autocar et vos sièges.'));
        }

        $trip->load(['branch', 'classes', 'vehicle', 'departureTerminal', 'arrivalTerminal']);
        
        // Generate structured 3D seat map with locked seats (01 & 16)
        $seatMap = SeatMapService::generateSeatMap($trip);

        // Find first genuinely available seat (never 01 or 16)
        $firstAvailableSeat = null;
        foreach ($seatMap['seats'] as $seat) {
            if ($seat['status'] === 'available') {
                $firstAvailableSeat = $seat['code'];
                break;
            }
        }

        // Schedule milestones
        $convocationTime = $trip->departure_time->copy()->subMinutes(45);
        $gateClosingTime = $trip->departure_time->copy()->subMinutes(15);
        $tripDuration = $trip->departure_time->diff($trip->arrival_time_estimated);

        return view('trips.show', [
            'trip' => $trip,
            'seatMap' => $seatMap,
            'seats' => array_values($seatMap['seats']),
            'availableSeatsCount' => $seatMap['available_count'],
            'firstAvailableSeat' => $firstAvailableSeat,
            'convocationTime' => $convocationTime,
            'gateClosingTime' => $gateClosingTime,
            'tripDuration' => $tripDuration,
        ]);
    }
}
