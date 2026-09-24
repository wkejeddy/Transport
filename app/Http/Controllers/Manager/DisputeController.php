<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispute;
use App\Models\Branch;

class DisputeController extends Controller
{
    public function index()
    {
        $branch = Auth::user()->branch ?? Branch::first();
        $disputes = Dispute::query()
            ->when($branch, fn($q) => $q->where('branch_id', $branch->id))
            ->with(['user', 'booking.trip', 'shipment'])
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('manager.disputes.index', compact('disputes', 'branch'));
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $dispute->branch_id && $dispute->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'manager_response' => 'required|string',
            'status' => 'required|in:resolved,rejected,in_review',
        ]);

        $dispute->update([
            'manager_response' => $validated['manager_response'],
            'manager_responded_at' => now(),
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Réponse enregistrée. Le dossier a été actualisé.');
    }
}
