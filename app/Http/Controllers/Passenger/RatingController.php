<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Rating;
use App\Models\Branch;

class RatingController extends Controller
{
    public function store(Request $request, ?Branch $branch = null)
    {
        $validated = $request->validate([
            'trip_id' => 'nullable|exists:trips,id',
            'score' => 'required|integer|min:1|max:5',
            'punctuality_score' => 'nullable|integer|min:1|max:5',
            'comfort_score' => 'nullable|integer|min:1|max:5',
            'customer_service_score' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $branchId = $branch?->id ?? Branch::first()?->id;

        Rating::create([
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
            'trip_id' => $validated['trip_id'] ?? null,
            'score' => $validated['score'],
            'punctuality_score' => $validated['punctuality_score'] ?? $validated['score'],
            'comfort_score' => $validated['comfort_score'] ?? $validated['score'],
            'customer_service_score' => $validated['customer_service_score'] ?? $validated['score'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Merci pour votre évaluation ! Votre avis contribue à l\'amélioration continue de Real Voyage.');
    }
}
