<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripApiController extends Controller
{
    public function search(Request $request)
    {
        $query = Trip::with(['branch', 'classes', 'vehicle'])
            ->whereIn('status', ['scheduled', 'delayed']);

        if ($request->filled('from')) {
            $query->where('departure_city', 'like', "%{$request->from}%");
        }

        if ($request->filled('to')) {
            $query->where('arrival_city', 'like', "%{$request->to}%");
        }

        $trips = $query->orderBy('departure_time')->take(30)->get();

        return response()->json([
            'status' => 'success',
            'count' => $trips->count(),
            'data' => $trips,
        ]);
    }

    public function show(Trip $trip)
    {
        $trip->load(['branch', 'classes', 'vehicle']);
        return response()->json([
            'status' => 'success',
            'data' => $trip,
        ]);
    }
}
