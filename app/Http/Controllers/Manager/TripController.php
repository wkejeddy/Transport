<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Vehicle;
use App\Models\TripAlert;
use App\Models\Branch;
use Carbon\Carbon;

class TripController extends Controller
{
    public function index()
    {
        $branch = Auth::user()->branch ?? Branch::first();
        $trips = Trip::query()
            ->when($branch, fn($q) => $q->where('branch_id', $branch->id))
            ->with(['vehicle', 'classes', 'bookings'])
            ->orderBy('departure_time', 'desc')
            ->paginate(10);

        return view('manager.trips.index', compact('trips', 'branch'));
    }

    public function create()
    {
        $branch = Auth::user()->branch ?? Branch::first();
        $vehicles = Vehicle::query()
            ->when($branch, fn($q) => $q->where('branch_id', $branch->id))
            ->where('status', 'active')
            ->get();

        $cities = [
            'Douala', 'Yaoundé', 'Bafoussam', 'Dschang', 'Mbouda', 'Bamenda', 'Kribi', 'Limbe', 'Bertoua', 'Ebolowa'
        ];

        return view('manager.trips.create', compact('branch', 'vehicles', 'cities'));
    }

    public function store(Request $request)
    {
        $branch = Auth::user()->branch ?? Branch::first();

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'trip_number' => 'required|string|max:50|unique:trips,trip_number',
            'departure_city' => 'required|string|max:100',
            'departure_station' => 'required|string|max:255',
            'arrival_city' => 'required|string|max:100',
            'arrival_station' => 'required|string|max:255',
            'departure_time' => 'required|date|after:now',
            'arrival_time_estimated' => 'required|date|after:departure_time',
            'base_price' => 'required|numeric|min:500',
            'cargo_price_per_kg' => 'required|numeric|min:50',
            'classes' => 'nullable|array',
            'classes.*.class_code' => 'required_with:classes|string',
            'classes.*.class_name' => 'required_with:classes|string',
            'classes.*.seat_count' => 'required_with:classes|integer|min:1',
            'classes.*.price' => 'required_with:classes|numeric|min:500',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        DB::transaction(function () use ($branch, $vehicle, $validated, $request) {
            $trip = Trip::create([
                'branch_id' => $branch?->id,
                'vehicle_id' => $vehicle->id,
                'trip_number' => $validated['trip_number'],
                'transport_mode' => 'road',
                'departure_city' => $validated['departure_city'],
                'departure_station' => $validated['departure_station'],
                'arrival_city' => $validated['arrival_city'],
                'arrival_station' => $validated['arrival_station'],
                'departure_time' => $validated['departure_time'],
                'arrival_time_estimated' => $validated['arrival_time_estimated'],
                'base_price' => $validated['base_price'],
                'seats_available' => $vehicle->capacity_seats,
                'cargo_available_kg' => $vehicle->capacity_cargo,
                'cargo_price_per_kg' => $validated['cargo_price_per_kg'],
                'status' => 'scheduled',
                'pricing_rules' => [
                    'dynamic_demand' => $request->boolean('dynamic_demand'),
                    'luggage_free_kg' => (int)$request->input('luggage_free_kg', 25),
                ],
            ]);

            // Save classes if specified
            if (!empty($validated['classes'])) {
                foreach ($validated['classes'] as $cls) {
                    TripClass::create([
                        'trip_id' => $trip->id,
                        'class_code' => $cls['class_code'],
                        'class_name' => $cls['class_name'],
                        'seat_count' => $cls['seat_count'],
                        'seats_available' => $cls['seat_count'],
                        'price' => $cls['price'],
                        'amenities' => ['Climatisation', 'Bagages sécurisés'],
                    ]);
                }
            } else {
                // Auto create default class
                TripClass::create([
                    'trip_id' => $trip->id,
                    'class_code' => 'classic',
                    'class_name' => 'Autocar Grand Confort',
                    'seat_count' => $vehicle->capacity_seats,
                    'seats_available' => $vehicle->capacity_seats,
                    'price' => $validated['base_price'],
                ]);
            }
        });

        return redirect()->route('manager.trips.index')->with('success', 'Voyage publié avec succès !');
    }

    public function manifest(Trip $trip)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $trip->branch_id && $trip->branch_id !== $branch->id) {
            abort(403);
        }

        $trip->load(['vehicle', 'classes', 'bookings.passenger', 'shipments.sender']);

        return view('manager.trips.manifest', compact('trip', 'branch'));
    }

    public function updateStatus(Request $request, Trip $trip)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $trip->branch_id && $trip->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,boarding,in_transit,completed,delayed,cancelled',
            'delay_reason' => 'nullable|string',
            'delayed_departure_time' => 'nullable|date',
        ]);

        $oldStatus = $trip->status;
        $trip->update($validated);

        // If delayed or cancelled, alert all booked passengers
        if (in_array($validated['status'], ['delayed', 'cancelled'])) {
            $bookedPassengers = $trip->bookings()
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->get()
                ->pluck('passenger_id')
                ->unique();

            $alertType = $validated['status'] === 'delayed' ? 'delay' : 'cancellation';
            $alertTitle = $validated['status'] === 'delayed' ? 'Alerte Retard: Voyage ' . $trip->trip_number : 'Alerte Annulation: Voyage ' . $trip->trip_number;
            $alertMsg = $validated['status'] === 'delayed' 
                ? "Votre voyage {$trip->trip_number} est retardé. Motif: {$validated['delay_reason']}."
                : "Votre voyage {$trip->trip_number} a été annulé par Real Voyage.";

            foreach ($bookedPassengers as $passengerId) {
                TripAlert::create([
                    'trip_id' => $trip->id,
                    'user_id' => $passengerId,
                    'type' => $alertType,
                    'title' => $alertTitle,
                    'message' => $alertMsg,
                ]);
            }
        }

        return back()->with('success', 'Statut du voyage mis à jour avec succès.');
    }
}
