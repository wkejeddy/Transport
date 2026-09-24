<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;
use App\Models\Branch;

class VehicleController extends Controller
{
    public function index()
    {
        $branch = Auth::user()->branch ?? Branch::first();
        $vehicles = Vehicle::query()
            ->when($branch, fn($q) => $q->where('branch_id', $branch->id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manager.vehicles.index', compact('vehicles', 'branch'));
    }

    public function create()
    {
        $branch = Auth::user()->branch ?? Branch::first();
        return view('manager.vehicles.create', compact('branch'));
    }

    public function store(Request $request)
    {
        $branch = Auth::user()->branch ?? Branch::first();

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vehicles,code',
            'name' => 'nullable|string|max:255',
            'type' => 'required|string',
            'capacity_seats' => 'required|integer|min:1|max:500',
            'capacity_cargo' => 'required|integer|min:0|max:50000',
            'status' => 'required|in:active,maintenance,inactive',
        ]);

        Vehicle::create([
            'branch_id' => $branch?->id,
            'code' => $validated['code'],
            'name' => $validated['name'],
            'transport_mode' => 'road',
            'type' => $validated['type'],
            'capacity_seats' => $validated['capacity_seats'],
            'capacity_cargo' => $validated['capacity_cargo'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('manager.vehicles.index')->with('success', 'Véhicule ajouté au parc Real Voyage !');
    }

    public function edit(Vehicle $vehicle)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $vehicle->branch_id && $vehicle->branch_id !== $branch->id) {
            abort(403);
        }

        return view('manager.vehicles.edit', compact('vehicle', 'branch'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $vehicle->branch_id && $vehicle->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vehicles,code,' . $vehicle->id,
            'name' => 'nullable|string|max:255',
            'type' => 'required|string',
            'capacity_seats' => 'required|integer|min:1|max:500',
            'capacity_cargo' => 'required|integer|min:0|max:50000',
            'status' => 'required|in:active,maintenance,inactive',
        ]);

        $vehicle->update($validated);

        return redirect()->route('manager.vehicles.index')->with('success', 'Véhicule mis à jour.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $vehicle->branch_id && $vehicle->branch_id !== $branch->id) {
            abort(403);
        }

        $vehicle->delete();

        return redirect()->route('manager.vehicles.index')->with('success', 'Véhicule supprimé de la flotte.');
    }
}
