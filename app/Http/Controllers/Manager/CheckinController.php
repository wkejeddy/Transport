<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Branch;

class CheckinController extends Controller
{
    public function index(Request $request)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        $search = $request->get('q');
        $booking = null;

        if ($search) {
            $booking = Booking::with(['passenger', 'trip.vehicle', 'tripClass'])
                ->when($branch, function ($query) use ($branch) {
                    $query->whereHas('trip', function ($q) use ($branch) {
                        $q->where('branch_id', $branch->id);
                    });
                })
                ->where(function ($q) use ($search) {
                    $q->where('booking_reference', strtoupper(trim($search)))
                      ->orWhere('qr_code_token', trim($search));
                })
                ->first();
        }

        $todayTrips = Trip::query()
            ->when($branch, fn($q) => $q->where('branch_id', $branch->id))
            ->whereDate('departure_time', today())
            ->get();

        return view('manager.checkin.index', compact('booking', 'search', 'todayTrips', 'branch'));
    }

    public function process(Request $request, Booking $booking)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $booking->trip->branch_id && $booking->trip->branch_id !== $branch->id) {
            abort(403);
        }

        if ($booking->status === 'checked_in') {
            return back()->with('info', 'Passager déjà enregistré pour ce voyage.');
        }

        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Impossible de valider l\'embarquement : la réservation n\'est pas confirmée (Statut: ' . $booking->status . ').');
        }

        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
            'checked_in_by' => Auth::id(),
        ]);

        return back()->with('success', 'Embarquement validé avec succès pour ' . ($booking->passenger->name ?? 'le passager') . ' !');
    }
}
