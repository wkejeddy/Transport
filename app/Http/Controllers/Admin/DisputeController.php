<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispute;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispute::with(['branch', 'user', 'booking.trip', 'shipment', 'adminResolver']);

        if ($request->get('filter') === 'escalated') {
            $query->where('escalated', true);
        }

        $disputes = $query->orderBy('escalated', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function arbitrate(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
            'status' => 'required|in:resolved,rejected',
        ]);

        $dispute->update([
            'admin_notes' => $validated['admin_notes'],
            'admin_resolved_by' => Auth::id(),
            'admin_resolved_at' => now(),
            'status' => $validated['status'],
        ]);

        return back()->with('success', "La réclamation #{$dispute->dispute_code} a été arbitrée par l'administration.");
    }
}
