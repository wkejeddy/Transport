<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Shipment;
use App\Models\Branch;
use App\Models\Terminal;
use App\Models\Trip;
use App\Services\FreightPricingService;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with(['branch', 'originTerminal', 'destinationTerminal', 'payment'])
            ->where('sender_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('passenger.shipments.index', compact('shipments'));
    }

    public function create()
    {
        $branch = Branch::first();
        $terminals = Terminal::active()->orderBy('region')->orderBy('name')->get();
        $terminalsByRegion = $terminals->groupBy('region');

        $liabilityNotice = FreightPricingService::LIABILITY_NOTICE;

        return view('passenger.shipments.create', compact('branch', 'terminals', 'terminalsByRegion', 'liabilityNotice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin_terminal_id' => 'required|exists:terminals,id',
            'destination_terminal_id' => 'required|exists:terminals,id|different:origin_terminal_id',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'item_category' => 'required|string',
            'item_description' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.5|max:500',
            'declared_value' => 'required|numeric|min:1000',
        ]);

        $originTerminal = Terminal::findOrFail($validated['origin_terminal_id']);
        $destinationTerminal = Terminal::findOrFail($validated['destination_terminal_id']);

        $weight = (float)$validated['weight_kg'];
        $declaredValue = (float)$validated['declared_value'];

        // Strict Freight Pricing Rule: Exactly 10% of declared value
        $freightFee = FreightPricingService::calculateFeeAmount($declaredValue);
        $totalAmount = $freightFee;

        $trackingCode = 'RV-FR-' . rand(100000, 999999);
        $deliveryOtp = (string)rand(1000, 9999);

        $shipment = Shipment::create([
            'tracking_code' => $trackingCode,
            'sender_id' => Auth::id(),
            'branch_id' => $originTerminal->branch_id ?? Branch::first()?->id,
            'trip_id' => null,
            'origin_terminal_id' => $originTerminal->id,
            'destination_terminal_id' => $destinationTerminal->id,
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'recipient_city' => $destinationTerminal->city,
            'destination_station' => $destinationTerminal->name,
            'item_category' => $validated['item_category'],
            'item_description' => $validated['item_description'],
            'weight_kg' => $weight,
            'declared_value' => $declaredValue,
            'insured' => true,
            'insurance_fee' => 0.00,
            'cargo_fee' => $freightFee,
            'total_amount' => $totalAmount,
            'status' => 'registered',
            'proof_of_delivery_code' => $deliveryOtp,
        ]);

        return redirect()->route('passenger.shipments.checkout', $shipment)
            ->with('success', __('messages.flash.shipment_created', ['amount' => number_format($totalAmount, 0, ',', ' ')]));
    }

    public function checkout(Shipment $shipment)
    {
        if ($shipment->sender_id !== Auth::id()) {
            abort(403);
        }

        if ($shipment->payment && $shipment->payment->isSuccessful()) {
            return redirect()->route('passenger.shipments.show', $shipment);
        }

        $shipment->load(['branch', 'originTerminal', 'destinationTerminal']);

        return view('passenger.shipments.checkout', compact('shipment'));
    }

    public function show(Shipment $shipment)
    {
        if ($shipment->sender_id !== Auth::id() && !Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        $shipment->load(['branch', 'originTerminal', 'destinationTerminal', 'sender', 'payment']);

        return view('passenger.shipments.show', compact('shipment'));
    }
}
