<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shipment;
use App\Models\Branch;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        $query = Shipment::query()
            ->when($branch, fn($q) => $q->where('branch_id', $branch->id))
            ->with(['sender', 'trip', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('tracking_code', 'like', "%{$q}%")
                    ->orWhere('recipient_name', 'like', "%{$q}%")
                    ->orWhere('recipient_phone', 'like', "%{$q}%");
            });
        }

        $shipments = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('manager.shipments.index', compact('shipments', 'branch'));
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $shipment->branch_id && $shipment->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:registered,in_transit,arrived,cancelled',
        ]);

        $shipment->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Statut du colis mis à jour : ' . $validated['status']);
    }

    public function confirmDelivery(Request $request, Shipment $shipment)
    {
        $branch = Auth::user()->branch ?? Branch::first();
        if ($branch && $shipment->branch_id && $shipment->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'otp_code' => 'required|string',
            'collected_by_name' => 'required|string|max:255',
            'collected_by_cni' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if (trim($validated['otp_code']) !== trim($shipment->proof_of_delivery_code)) {
            return back()->with('error', 'Code OTP de retrait invalide. Demandez le code reçu par SMS au destinataire.');
        }

        $shipment->update([
            'status' => 'collected',
            'collected_at' => now(),
            'collected_by_name' => $validated['collected_by_name'],
            'collected_by_cni' => $validated['collected_by_cni'],
            'proof_of_delivery_notes' => $validated['notes'] ?? 'Remise effectuée et validée par OTP.',
        ]);

        return back()->with('success', 'Preuve de livraison validée ! Le colis est marqué comme remis au destinataire.');
    }
}
