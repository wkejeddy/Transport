<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Payment;
use App\Models\Dispute;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransportPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * 1. Test homepage and search accessibility for Guests (Visiteur)
     */
    public function test_guest_can_access_homepage_and_search_trips(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Douala');
        $response->assertSee('Yaoundé');

        $searchResponse = $this->get('/trips?from=Douala');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Douala');
    }

    /**
     * 2. Test Role-based access control (Passenger vs Manager vs Admin)
     */
    public function test_role_based_access_control(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $manager = User::where('role', 'manager')->first();
        $admin = User::where('role', 'admin')->first();

        // Guest cannot access dashboard or booking and is redirected to register
        $this->get('/passenger/dashboard')->assertRedirect(route('register.passenger'));

        // Passenger can access passenger dashboard but not manager or admin
        $this->actingAs($passenger)->get('/passenger/dashboard')->assertStatus(200);
        $this->actingAs($passenger)->get('/admin/dashboard')->assertRedirect('/passenger/dashboard');

        // Manager can access manager dashboard
        $this->actingAs($manager)->get('/manager/dashboard')->assertStatus(200);

        // Admin can access admin dashboard
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
    }

    /**
     * 3. Test Agency Registration & Admin Approval workflow
     */
    public function test_agency_registration_and_admin_verification(): void
    {
        // In dedicated single agency mode, public agency registration is disabled (404)
        $response = $this->get('/register/agency');
        $response->assertStatus(404);

        $postResponse = $this->post('/register/agency', [
            'name' => 'Jean Manager Express',
            'email' => 'jean.manager@nouveautransport.cm',
        ]);
        $postResponse->assertStatus(404);

        // Verify regional branches exist and are active
        $branch = Branch::where('code', 'DLA')->first();
        $this->assertNotNull($branch);
        $this->assertTrue($branch->is_active);
    }

    /**
     * 4. Test Passenger Booking & 2-Minute Seat Lock
     */
    public function test_passenger_booking_flow_and_seat_reservation(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();
        $initialSeats = $trip->seats_available;

        $response = $this->actingAs($passenger)->post("/passenger/trips/{$trip->id}/book", [
            'seats_count' => 2,
            'seat_numbers' => ['S-02', 'S-03'],
            'passengers' => [
                ['name' => 'Paul Ewane', 'cni' => '119827364', 'phone' => '+237 699 112 233'],
                ['name' => 'Marie Ewane', 'cni' => '119827365', 'phone' => '+237 699 112 234'],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertEquals($initialSeats - 2, $trip->fresh()->seats_available);

        $booking = Booking::where('passenger_id', $passenger->id)->latest('id')->first();
        $this->assertEquals('pending', $booking->status);
        $this->assertNotNull($booking->expires_at);
    }

    /**
     * 5. Test 2-Minute Unpaid Booking Auto-Release Command
     */
    public function test_unpaid_booking_auto_release_command(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();
        $initialSeats = $trip->seats_available;

        // Create an expired pending booking
        $booking = Booking::create([
            'booking_reference' => 'BK-EXP-' . rand(1000, 9999),
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'classic',
            'seats_count' => 3,
            'seat_numbers' => ['S-88', 'S-89', 'S-90'],
            'total_amount' => 18000,
            'status' => 'pending',
            'qr_code_token' => 'QR-EXP-' . rand(1000, 9999),
            'expires_at' => now()->subMinutes(3), // Expired!
        ]);

        $trip->decrement('seats_available', 3);
        $this->assertEquals($initialSeats - 3, $trip->fresh()->seats_available);

        // Run auto-release command
        Artisan::call('transport:release-unpaid-bookings');

        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertEquals($initialSeats, $trip->fresh()->seats_available);
    }

    /**
     * 6. Test Mobile Money Payment & E-Ticket Generation
     */
    public function test_mobile_money_payment_and_e_ticket(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();

        // Create fresh pending booking
        $booking = Booking::create([
            'booking_reference' => 'BK-TEST-' . rand(1000, 9999),
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'classic',
            'seats_count' => 1,
            'seat_numbers' => ['S-15'],
            'passengers_data' => [['name' => $passenger->name, 'cni' => '12345', 'phone' => $passenger->phone]],
            'total_amount' => $trip->base_price,
            'status' => 'pending',
            'qr_code_token' => 'QR-TK-' . rand(1000, 9999),
            'expires_at' => now()->addMinutes(2),
        ]);

        // Initiate Orange Money Payment
        $gatewayService = app(PaymentGatewayService::class);
        $payment = $gatewayService->initiatePayment($booking, 'orange_money', '+237 699 112 233');

        $this->assertEquals('pending', $payment->status);

        // Simulate successful sandbox validation
        $gatewayService->processSandboxPayment($payment, true);

        $this->assertEquals('successful', $payment->fresh()->status);
        $this->assertEquals('confirmed', $booking->fresh()->status);

        // View E-ticket
        $ticketResponse = $this->actingAs($passenger)->get("/passenger/bookings/{$booking->id}/ticket");
        $ticketResponse->assertStatus(200);
        $ticketResponse->assertSee($booking->booking_reference);
    }

    /**
     * 7. Test Cargo Shipment & OTP Proof of Delivery Verification
     */
    public function test_cargo_shipment_and_otp_delivery(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $manager = User::where('role', 'manager')->whereNotNull('branch_id')->first()
            ?? User::where('role', 'manager')->first();
        $branch = $manager->branch ?? Branch::first();

        // Register package
        $shipment = Shipment::create([
            'tracking_code' => 'SH-TEST-' . rand(1000, 9999),
            'sender_id' => $passenger->id,
            'branch_id' => $branch->id,
            'recipient_name' => 'Alphonse Ebanda',
            'recipient_phone' => '+237 677 001 002',
            'recipient_city' => 'Yaoundé',
            'destination_station' => 'Gare Voyageurs Guichet Fret',
            'item_category' => 'electronics',
            'item_description' => 'Carton de matériel informatique',
            'weight_kg' => 10.0,
            'declared_value' => 200000,
            'insured' => true,
            'insurance_fee' => 2000,
            'cargo_fee' => 2500,
            'total_amount' => 4500,
            'status' => 'in_transit',
            'proof_of_delivery_code' => '7841',
        ]);

        // Attempt delivery with wrong OTP
        $failResponse = $this->actingAs($manager)->post("/manager/shipments/{$shipment->id}/deliver", [
            'otp_code' => '0000',
            'collected_by_name' => 'Alphonse Ebanda',
            'collected_by_cni' => '1192837465',
        ]);
        $failResponse->assertSessionHas('error');
        $this->assertNotEquals('collected', $shipment->fresh()->status);

        // Deliver with correct OTP code
        $successResponse = $this->actingAs($manager)->post("/manager/shipments/{$shipment->id}/deliver", [
            'otp_code' => '7841',
            'collected_by_name' => 'Alphonse Ebanda',
            'collected_by_cni' => '1192837465',
            'notes' => 'Colis remis intact.',
        ]);
        $successResponse->assertSessionHas('success');
        $this->assertEquals('collected', $shipment->fresh()->status);
        $this->assertNotNull($shipment->fresh()->collected_at);
    }

    /**
     * 8. Test 48-Hour Dispute Auto-Escalation Command
     */
    public function test_dispute_48_hour_auto_escalation(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $branch = Branch::first();

        // Create an open dispute
        $dispute = Dispute::create([
            'dispute_code' => 'DSP-AUTO-' . rand(100, 999),
            'raised_by_user_id' => $passenger->id,
            'branch_id' => $branch->id,
            'title' => 'Retard excessif sans information',
            'category' => 'delay',
            'description' => 'Départ retardé de plus de 3 heures.',
            'status' => 'open',
            'escalated' => false,
        ]);

        // Explicitly backdate created_at in DB
        DB::table('disputes')->where('id', $dispute->id)->update([
            'created_at' => now()->subHours(50),
        ]);

        $this->assertFalse($dispute->fresh()->escalated);

        // Run auto-escalate command
        Artisan::call('transport:escalate-disputes');

        $this->assertTrue($dispute->fresh()->escalated);
        $this->assertNotNull($dispute->fresh()->escalated_at);
    }

    /**
     * 9. Test Visitor can browse fleet/trips but must create an account before choosing a bus
     */
    public function test_visitor_can_browse_fleet_but_must_register_to_book(): void
    {
        $trip = Trip::first();

        // Guest can freely view trips index
        $this->get(route('trips.index'))->assertStatus(200);

        // Guest choosing a bus is redirected to register an account first
        $this->get(route('trips.show', $trip))
            ->assertRedirect(route('register.passenger'))
            ->assertSessionHas('info');

        // Guest trying to book a seat directly is also redirected to register
        $response = $this->post(route('passenger.bookings.store', $trip), [
            'seats_count' => 1,
            'passengers' => [
                ['name' => 'Visiteur Anonyme', 'cni' => '123456789', 'phone' => '+237670000000']
            ]
        ]);

        $response->assertRedirect(route('register.passenger'));

        // Once authenticated, passenger can choose the bus and view 3D seat map
        $passenger = User::where('role', 'passager')->first();
        $this->actingAs($passenger)
            ->get(route('trips.show', $trip))
            ->assertStatus(200);
    }

    /**
     * 10. Test Advance Reservation with 500 FCFA Fee & Seat Lock
     */
    public function test_advance_reservation_with_500_fcfa_fee(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();

        // Ensure trip departs in more than 8 hours (e.g. tomorrow)
        $trip->update(['departure_time' => now()->addHours(24)]);
        $initialSeats = $trip->seats_available;

        $response = $this->actingAs($passenger)->post("/passenger/trips/{$trip->id}/book", [
            'seats_count' => 1,
            'seat_numbers' => ['S-05'],
            'booking_option' => 'reserve',
            'passengers' => [
                ['name' => 'Alice Kembe', 'cni' => '119800112', 'phone' => '+237 677 112 233'],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertEquals($initialSeats - 1, $trip->fresh()->seats_available);

        $booking = Booking::where('passenger_id', $passenger->id)->latest('id')->first();
        $this->assertEquals('advance_reservation', $booking->booking_type);
        $this->assertEquals(500.00, (float)$booking->reservation_fee);
        $this->assertFalse((bool)$booking->reservation_fee_paid);
        $this->assertEquals('pending', $booking->status);

        // Initiate payment for 500 FCFA fee
        $gatewayService = app(PaymentGatewayService::class);
        $payment = $gatewayService->initiatePayment($booking, 'mtn_momo', '+237 677 112 233');

        $this->assertEquals(500.00, (float)$payment->amount);
        $this->assertEquals('reservation_fee', $payment->payment_type);

        // Simulate successful 500 FCFA payment
        $gatewayService->processSandboxPayment($payment, true);

        $booking->refresh();
        $this->assertTrue((bool)$booking->reservation_fee_paid);
        $this->assertEquals('reserved', $booking->status);
        $this->assertTrue($booking->isReserved());
        // Seat remains decremented
        $this->assertEquals($initialSeats - 1, $trip->fresh()->seats_available);
    }

    /**
     * 11. Test Advance Reservation rejected if departure is within 8 hours
     */
    public function test_advance_reservation_rejected_if_trip_within_8_hours(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();

        // Set departure to 4 hours from now
        $trip->update(['departure_time' => now()->addHours(4)]);

        $response = $this->actingAs($passenger)->post("/passenger/trips/{$trip->id}/book", [
            'seats_count' => 1,
            'seat_numbers' => ['S-06'],
            'booking_option' => 'reserve',
            'passengers' => [
                ['name' => 'Alice Kembe', 'cni' => '119800112', 'phone' => '+237 677 112 233'],
            ],
        ]);

        $response->assertSessionHas('error');
    }

    /**
     * 12. Test 8-Hour Departure Reminder Notification and TripAlert
     */
    public function test_8_hour_departure_reminder_notification(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();

        // Set trip departure in 7.5 hours (within the 6h - 8h window)
        $trip->update(['departure_time' => now()->addHours(7)->addMinutes(30)]);

        $booking = Booking::create([
            'booking_reference' => 'BK-REM-' . rand(1000, 9999),
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'classic',
            'booking_type' => 'advance_reservation',
            'seats_count' => 1,
            'seat_numbers' => ['S-07'],
            'passengers_data' => [['name' => $passenger->name, 'cni' => '12345', 'phone' => $passenger->phone]],
            'total_amount' => $trip->base_price,
            'reservation_fee' => 500.00,
            'reservation_fee_paid' => true,
            'status' => 'reserved',
            'qr_code_token' => 'QR-REM-' . rand(1000, 9999),
            'expires_at' => $trip->departure_time->copy()->subHours(6),
            'reminder_sent_at' => null,
        ]);

        // Run scheduler command
        Artisan::call('transport:release-unpaid-bookings');

        $booking->refresh();
        $this->assertNotNull($booking->reminder_sent_at);
        $this->assertEquals('reserved', $booking->status);

        // Verify TripAlert created for user
        $alert = \App\Models\TripAlert::where('user_id', $passenger->id)
            ->where('title', 'like', '%Rappel%')
            ->latest('id')
            ->first();
        $this->assertNotNull($alert);
    }

    /**
     * 13. Test 6-Hour Departure Auto-Cancellation and Seat Release
     */
    public function test_6_hour_departure_auto_cancellation_and_seat_release(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();
        $initialSeats = $trip->seats_available;

        // Set trip departure in 5 hours (< 6 hours away)
        $trip->update(['departure_time' => now()->addHours(5)]);
        $trip->decrement('seats_available', 2);

        $booking = Booking::create([
            'booking_reference' => 'BK-CANC-' . rand(1000, 9999),
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'classic',
            'booking_type' => 'advance_reservation',
            'seats_count' => 2,
            'seat_numbers' => ['S-08', 'S-09'],
            'passengers_data' => [['name' => $passenger->name, 'cni' => '12345', 'phone' => $passenger->phone]],
            'total_amount' => $trip->base_price * 2,
            'reservation_fee' => 500.00,
            'reservation_fee_paid' => true,
            'status' => 'reserved',
            'qr_code_token' => 'QR-CANC-' . rand(1000, 9999),
            'expires_at' => $trip->departure_time->copy()->subHours(6),
        ]);

        // Run scheduler command
        Artisan::call('transport:release-unpaid-bookings');

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        // Seats restored to available
        $this->assertEquals($initialSeats, $trip->fresh()->seats_available);

        // Verify cancellation alert created
        $alert = \App\Models\TripAlert::where('user_id', $passenger->id)
            ->where('type', 'cancellation')
            ->latest('id')
            ->first();
        $this->assertNotNull($alert);
    }

    /**
     * 14. Test Completing Ticket Payment for Reserved Booking
     */
    public function test_completing_ticket_payment_for_reserved_booking(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();
        $trip->update(['departure_time' => now()->addHours(12)]);

        $booking = Booking::create([
            'booking_reference' => 'BK-FULL-' . rand(1000, 9999),
            'passenger_id' => $passenger->id,
            'trip_id' => $trip->id,
            'transport_class' => 'classic',
            'booking_type' => 'advance_reservation',
            'seats_count' => 1,
            'seat_numbers' => ['S-10'],
            'passengers_data' => [['name' => $passenger->name, 'cni' => '12345', 'phone' => $passenger->phone]],
            'total_amount' => $trip->base_price,
            'reservation_fee' => 500.00,
            'reservation_fee_paid' => true,
            'status' => 'reserved',
            'qr_code_token' => 'QR-FULL-' . rand(1000, 9999),
            'expires_at' => $trip->departure_time->copy()->subHours(6),
        ]);

        // Pay the ticket fare
        $gatewayService = app(PaymentGatewayService::class);
        $payment = $gatewayService->initiatePayment($booking, 'orange_money', '+237 699 112 233');

        $this->assertEquals((float)$trip->base_price, (float)$payment->amount);
        $this->assertEquals('ticket', $payment->payment_type);

        $gatewayService->processSandboxPayment($payment, true);

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
        $this->assertTrue($booking->isConfirmed());
    }
}
