<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Terminal;
use App\Services\FreightPricingService;
use App\Services\SeatMapService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RealVoyageFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    /**
     * 1. Test permanent locks on Seat 01 (Driver/Chauffeur) and Seat 16 (Convoyeur)
     */
    public function test_driver_and_convoyeur_seats_are_strictly_locked_from_public_booking(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();

        // Attempt to book Seat 01 (Driver)
        $responseDriver = $this->actingAs($passenger)->from("/trips/{$trip->id}")->post("/passenger/trips/{$trip->id}/book", [
            'seats_count' => 1,
            'seat_numbers' => ['01'],
            'passengers' => [
                ['name' => 'Jean Test', 'cni' => '119827364', 'phone' => '+237 699 000 111'],
            ],
        ]);

        $responseDriver->assertRedirect("/trips/{$trip->id}");
        $responseDriver->assertSessionHas('error');
        $this->assertStringContainsString('personnel de bord', session('error'));

        // Attempt to book Seat 16 (Convoyeur / Assistant)
        $responseConvoyeur = $this->actingAs($passenger)->from("/trips/{$trip->id}")->post("/passenger/trips/{$trip->id}/book", [
            'seats_count' => 1,
            'seat_numbers' => ['S-16'],
            'passengers' => [
                ['name' => 'Paul Test', 'cni' => '119827365', 'phone' => '+237 699 000 222'],
            ],
        ]);

        $responseConvoyeur->assertRedirect("/trips/{$trip->id}");
        $responseConvoyeur->assertSessionHas('error');
        $this->assertStringContainsString('personnel de bord', session('error'));

        // Verify SeatMapService marks them locked
        $seatMap = SeatMapService::generateSeatMap($trip);
        $this->assertEquals('locked', $seatMap['seats'][1]['status']);
        $this->assertEquals('locked', $seatMap['seats'][16]['status']);
    }

    /**
     * Test exact Carrefour blueprint seat dispositions for 75 and 80-seater buses.
     */
    public function test_carrefour_blueprints_layout_for_75_and_80_seater_buses(): void
    {
        $trip = Trip::where('status', 'scheduled')->first();

        // 1. Test 80-Seater Layout
        $trip->vehicle->update(['capacity_seats' => 80]);
        $map80 = SeatMapService::generateSeatMap($trip->fresh());

        $this->assertEquals(80, $map80['total_seats']);
        $this->assertEquals(17, $map80['total_rows']);
        $this->assertCount(80, $map80['seats']);

        // Check Row 1: Left [null, 1, null], Right [2, 3]
        $this->assertEquals(1, $map80['rows'][1]['left'][1]['number']);
        $this->assertEquals(2, $map80['rows'][1]['right'][0]['number']);
        $this->assertEquals(3, $map80['rows'][1]['right'][1]['number']);

        // Check Row 4: Left [14, 15, 16], Door 'ENTREE 1'
        $this->assertEquals(16, $map80['rows'][4]['left'][2]['number']);
        $this->assertEquals('ENTREE 1', $map80['rows'][4]['door']);

        // Check Row 14: Left [62, 63, 64], Door 'ENTREE 2'
        $this->assertEquals('ENTREE 2', $map80['rows'][14]['door']);
        $this->assertEquals(62, $map80['rows'][14]['left'][0]['number']);

        // Check Row 17: Rear bench with 6 seats (75, 76, 77, 78, 79, 80)
        $this->assertEquals(75, $map80['rows'][17]['left'][0]['number']);
        $this->assertEquals(78, $map80['rows'][17]['center']['number']);
        $this->assertEquals(80, $map80['rows'][17]['right'][1]['number']);

        // 2. Test 75-Seater Layout
        $trip->vehicle->update(['capacity_seats' => 75]);
        $map75 = SeatMapService::generateSeatMap($trip->fresh());

        $this->assertEquals(75, $map75['total_seats']);
        $this->assertEquals(16, $map75['total_rows']);
        $this->assertCount(75, $map75['seats']);

        // Check Row 4: Door 'ENTREE 1'
        $this->assertEquals('ENTREE 1', $map75['rows'][4]['door']);

        // Check Row 13: Door 'ENTREE 2'
        $this->assertEquals('ENTREE 2', $map75['rows'][13]['door']);
        $this->assertEquals(57, $map75['rows'][13]['left'][0]['number']);

        // Check Row 16: Rear bench with 6 seats (70, 71, 72, 73, 74, 75)
        $this->assertEquals(70, $map75['rows'][16]['left'][0]['number']);
        $this->assertEquals(73, $map75['rows'][16]['center']['number']);
        $this->assertEquals(75, $map75['rows'][16]['right'][1]['number']);
    }

    /**
     * 2. Test cancellation strictly permitted > 6h with 100% E-Wallet refund (zero cash refund)
     */
    public function test_cancellation_permitted_strictly_over_6h_before_departure_with_wallet_refund(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $initialWallet = (float)$passenger->wallet_balance;

        $trip = Trip::where('status', 'scheduled')->first();
        // Set departure to 24 hours in future (> 6h)
        $trip->update([
            'departure_time' => now()->addHours(24),
            'seats_available' => 50,
        ]);

        $booking = Booking::create([
            'booking_reference' => 'BK-TEST-CANCEL-01',
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'standard',
            'seats_count' => 1,
            'seat_numbers' => ['08'],
            'passengers_data' => [['name' => 'Test User', 'cni' => '1198234', 'phone' => '+237 699 111 222']],
            'total_amount' => 5000,
            'status' => 'confirmed',
            'qr_code_token' => 'QR-TEST-CANCEL-01',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($passenger)->post(route('passenger.bookings.cancel', $booking));
        $response->assertSessionHas('success');

        // Check status updated to cancelled
        $this->assertEquals('cancelled', $booking->fresh()->status);

        // Check seat released
        $this->assertEquals(51, $trip->fresh()->seats_available);

        // Check E-Wallet credited with exact ticket amount
        $this->assertEquals($initialWallet + 5000, (float)$passenger->fresh()->wallet_balance);
    }

    /**
     * 3. Test cancellation rejected when <= 6 hours before departure
     */
    public function test_cancellation_rejected_strictly_within_6h_of_departure(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $initialWallet = (float)$passenger->wallet_balance;

        $trip = Trip::where('status', 'scheduled')->first();
        // Set departure to 4 hours in future (<= 6h)
        $trip->update([
            'departure_time' => now()->addHours(4),
            'seats_available' => 50,
        ]);

        $booking = Booking::create([
            'booking_reference' => 'BK-TEST-NOCANCEL-02',
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'standard',
            'seats_count' => 1,
            'seat_numbers' => ['09'],
            'passengers_data' => [['name' => 'Test User', 'cni' => '1198234', 'phone' => '+237 699 111 222']],
            'total_amount' => 5000,
            'status' => 'confirmed',
            'qr_code_token' => 'QR-TEST-NOCANCEL-02',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($passenger)->post(route('passenger.bookings.cancel', $booking));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('6 heures', session('error'));

        // Verify status remains confirmed and no wallet refund occurred
        $this->assertEquals('confirmed', $booking->fresh()->status);
        $this->assertEquals($initialWallet, (float)$passenger->fresh()->wallet_balance);
        $this->assertEquals(50, $trip->fresh()->seats_available);
    }

    /**
     * 4. Test instant booking checkout via E-Wallet
     */
    public function test_booking_payment_via_e_wallet(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $passenger->update(['wallet_balance' => 15000]);

        $trip = Trip::where('status', 'scheduled')->first();

        $booking = Booking::create([
            'booking_reference' => 'BK-TEST-WAL-03',
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'standard',
            'seats_count' => 1,
            'seat_numbers' => ['10'],
            'passengers_data' => [['name' => 'Test User', 'cni' => '1198234', 'phone' => '+237 699 111 222']],
            'total_amount' => 5000,
            'status' => 'pending',
            'qr_code_token' => 'QR-TEST-WAL-03',
            'expires_at' => now()->addMinutes(2),
        ]);

        $response = $this->actingAs($passenger)->post(route('passenger.payments.booking', $booking), [
            'method' => 'wallet',
        ]);

        $response->assertRedirect(route('passenger.bookings.ticket', $booking));
        $response->assertSessionHas('success');

        // Verify wallet debited by 5,000 FCFA
        $this->assertEquals(10000, (float)$passenger->fresh()->wallet_balance);
        // Verify booking confirmed
        $this->assertEquals('confirmed', $booking->fresh()->status);
    }

    /**
     * 5. Test Freight Pricing Rule: Exactly 10% of Declared Value
     */
    public function test_freight_pricing_is_strictly_10_percent_of_declared_value(): void
    {
        $declaredValue = 180000.0;
        $pricing = FreightPricingService::calculateFee($declaredValue);

        $this->assertEquals(18000.0, $pricing['cargo_fee']);
        $this->assertEquals(18000.0, $pricing['total_amount']);
        $this->assertStringContainsString('between 2x and 5x', $pricing['liability_notice']);

        $passenger = User::where('role', 'passager')->first();
        $terminals = Terminal::all();
        $origin = $terminals->first();
        $destination = $terminals->last();

        $response = $this->actingAs($passenger)->post(route('passenger.shipments.store'), [
            'origin_terminal_id' => $origin->id,
            'destination_terminal_id' => $destination->id,
            'recipient_name' => 'Arthur Mbia',
            'recipient_phone' => '+237 677 000 333',
            'item_category' => 'electronics',
            'item_description' => 'Matériel informatique test',
            'weight_kg' => 8.0,
            'declared_value' => $declaredValue,
        ]);

        $response->assertStatus(302);

        $shipment = Shipment::where('sender_id', $passenger->id)->latest('id')->first();
        $this->assertNotNull($shipment);
        $this->assertEquals(18000.0, (float)$shipment->cargo_fee);
        $this->assertEquals(18000.0, (float)$shipment->total_amount);
        $this->assertEquals($origin->id, $shipment->origin_terminal_id);
        $this->assertEquals($destination->id, $shipment->destination_terminal_id);
    }

    /**
     * 6. Test 11 Terminals Network seeded across 3 Regions
     */
    public function test_11_terminals_network_configured_across_three_regions(): void
    {
        $westTerminals = Terminal::where('region', 'Ouest')->pluck('city')->toArray();
        $doualaTerminals = Terminal::where('region', 'Douala')->get();
        $yaoundeTerminals = Terminal::where('region', 'Yaoundé')->get();

        $this->assertCount(3, $westTerminals);
        $this->assertContains('Dschang', $westTerminals);
        $this->assertContains('Mbouda', $westTerminals);
        $this->assertContains('Bafoussam', $westTerminals);

        $this->assertCount(3, $doualaTerminals);
        $this->assertCount(5, $yaoundeTerminals);
        $this->assertEquals(11, Terminal::count());
    }
}
