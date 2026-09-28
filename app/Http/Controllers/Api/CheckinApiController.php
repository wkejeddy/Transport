<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Trip;

class CheckinApiController extends Controller
{
    public function scan(Request $request, Trip $trip)
    {
        $code = $request->input('token') ?? $request->input('booking_reference');

        $booking = Booking::with('passenger')
            ->where('trip_id', $trip->id)
            ->where(function ($q) use ($code) {
                $q->where('qr_code_token', $code)
                  ->orWhere('booking_reference', strtoupper(trim($code)));
            })
            ->first();

        if (!$booking) {
            return response()->json(['status' => 'error', 'message' => 'Billet non trouvé pour ce voyage.'], 404);
        }

        if ($booking->status === 'checked_in') {
            return response()->json([
                'status' => 'warning',
                'message' => 'Passager déjà enregistré pour ce voyage.',
                'data' => [
                    'booking_reference' => $booking->booking_reference,
                    'passenger' => $booking->passenger->name ?? 'Passager',
                    'checked_in_at' => $booking->checked_in_at ? $booking->checked_in_at->toIso8601String() : null,
                ]
            ]);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json(['status' => 'error', 'message' => 'Billet non valide (statut: ' . $booking->status . ')'], 400);
        }

        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Embarquement validé avec succès !',
            'passenger' => $booking->passenger->name ?? 'Passager',
            'seats' => $booking->seat_numbers,
            'checked_in_at' => now()->toIso8601String(),
        ]);
    }
}