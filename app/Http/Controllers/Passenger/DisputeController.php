<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispute;
use App\Models\Branch;
use App\Models\Booking;
use App\Models\Shipment;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::with(['branch', 'booking', 'shipment', 'adminResolver'])
            ->where('raised_by_user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('passenger.disputes.index', compact('disputes'));
    }

    public function create(Request $request)
    {
        $branches = Branch::active()->get();
        $userBookings = Booking::where('passenger_id', Auth::id())->with('trip.branch')->get();
        $userShipments = Shipment::where('sender_id', Auth::id())->with('branch')->get();

        $selectedBookingId = $request->get('booking_id');
        $selectedShipmentId = $request->get('shipment_id');

        return view('passenger.disputes.create', compact('branches', 'userBookings', 'userShipments', 'selectedBookingId', 'selectedShipmentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'shipment_id' => 'nullable|exists:shipments,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'required|string',
        ]);

        $code = 'DSP-' . date('Y') . '-' . rand(100, 999);
        $branchId = $validated['branch_id'] ?? Branch::first()?->id;

        $dispute = Dispute::create([
            'dispute_code' => $code,
            'raised_by_user_id' => Auth::id(),
            'branch_id' => $branchId,
            'booking_id' => $validated['booking_id'] ?? null,
            'shipment_id' => $validated['shipment_id'] ?? null,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => 'open',
            'escalated' => false,
        ]);

        return redirect()->route('passenger.disputes.show', $dispute)
            ->with('success', 'Réclamation enregistrée ! L\'équipe Real Voyage dispose de 48 heures pour apporter une réponse avant escalade automatique vers la direction.');
    }

    public function show(Dispute $dispute)
    {
        if ($dispute->raised_by_user_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        $dispute->load(['branch', 'booking.trip', 'shipment', 'adminResolver', 'user']);

        return view('passenger.disputes.show', compact('dispute'));
    }
}
