<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Booking;
use App\Services\BookingService;
use App\Events\BookingConfirmedEvent;
use App\Events\TripReminderEvent;
use App\Events\AdvanceReservationExpiringEvent;
use App\Listeners\SendTicketNotificationListener;
use App\Listeners\SendTripReminderListener;
use App\Listeners\SendAdvanceReservationExpiringListener;
use Exception;

class RealVoyageArchitectureAndRescheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $otherManager;
    protected User $passenger;
    protected Branch $branch;
    protected Branch $otherBranch;
    protected Vehicle $vehicle;
    protected Trip $trip1;
    protected Trip $trip2;
    protected TripClass $trip1Vip;
    protected TripClass $trip2Classic;
    protected TripClass $trip2Vip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'DLA-HQ',
            'name' => 'Direction Régionale Douala',
            'region' => 'Littoral',
            'city' => 'Douala',
            'address' => 'Boulevard de la Liberté, Akwa',
            'phone' => '+237 670 000 001',
            'is_headquarters' => true,
            'is_active' => true,
        ]);

        $this->otherBranch = Branch::create([
            'code' => 'YDE-CTR',
            'name' => 'Direction Régionale Yaoundé',
            'region' => 'Centre',
            'city' => 'Yaoundé',
            'address' => 'Terminal Mvan',
            'phone' => '+237 670 000 002',
            'is_headquarters' => false,
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Directeur',
            'email' => 'admin@realvoyage.cm',
            'password' => bcrypt('AdminSecure@2026!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->manager = User::create([
            'name' => 'Chef de Gare Douala',
            'email' => 'manager.dla@realvoyage.cm',
            'password' => bcrypt('ManagerSecure@2026!'),
            'role' => 'manager',
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $this->otherManager = User::create([
            'name' => 'Chef de Gare Yaoundé',
            'email' => 'manager.yde@realvoyage.cm',
            'password' => bcrypt('ManagerSecure@2026!'),
            'role' => 'manager',
            'branch_id' => $this->otherBranch->id,
            'status' => 'active',
        ]);

        $this->passenger = User::create([
            'name' => 'Fokou Tagne',
            'email' => 'fokou@test.cm',
            'phone' => '+237671112233',
            'password' => bcrypt('PassengerSecure@2026!'),
            'role' => 'passager',
            'wallet_balance' => 30000.00,
            'status' => 'active',
        ]);

        $this->vehicle = Vehicle::create([
            'code' => 'BUS-RV-101',
            'branch_id' => $this->branch->id,
            'name' => 'Alizé Grand Tourisme',
            'type' => 'vip_bus',
            'capacity_seats' => 75,
            'capacity_cargo' => 2000,
            'status' => 'active',
        ]);

        // Trip 1: Depart in 24 hours (eligible for rescheduling)
        $this->trip1 = Trip::create([
            'branch_id' => $this->branch->id,
            'vehicle_id' => $this->vehicle->id,
            'trip_number' => 'RV-DLA-YDE-24H',
            'transport_mode' => 'road',
            'departure_city' => 'Douala',
            'departure_station' => 'Gare Bessengue',
            'arrival_city' => 'Yaoundé',
            'arrival_station' => 'Gare Mvan',
            'departure_time' => now()->addHours(24),
            'arrival_time_estimated' => now()->addHours(28),
            'base_price' => 5000.00,
            'seats_available' => 73,
            'cargo_available_kg' => 2000,
            'cargo_price_per_kg' => 100,
            'status' => 'scheduled',
        ]);

        $this->trip1Vip = TripClass::create([
            'trip_id' => $this->trip1->id,
            'class_code' => 'vip',
            'class_name' => 'VIP 1ère Classe',
            'seat_count' => 73,
            'seats_available' => 73,
            'price' => 8000.00,
        ]);

        // Trip 2: Alternate trip departing in 48 hours
        $this->trip2 = Trip::create([
            'branch_id' => $this->branch->id,
            'vehicle_id' => $this->vehicle->id,
            'trip_number' => 'RV-DLA-YDE-48H',
            'transport_mode' => 'road',
            'departure_city' => 'Douala',
            'departure_station' => 'Gare Bessengue',
            'arrival_city' => 'Yaoundé',
            'arrival_station' => 'Gare Mvan',
            'departure_time' => now()->addHours(48),
            'arrival_time_estimated' => now()->addHours(52),
            'base_price' => 5000.00,
            'seats_available' => 73,
            'cargo_available_kg' => 2000,
            'cargo_price_per_kg' => 100,
            'status' => 'scheduled',
        ]);

        $this->trip2Classic = TripClass::create([
            'trip_id' => $this->trip2->id,
            'class_code' => 'classic',
            'class_name' => 'Grand Confort',
            'seat_count' => 50,
            'seats_available' => 50,
            'price' => 5000.00,
        ]);

        $this->trip2Vip = TripClass::create([
            'trip_id' => $this->trip2->id,
            'class_code' => 'vip',
            'class_name' => 'VIP 1ère Classe',
            'seat_count' => 23,
            'seats_available' => 23,
            'price' => 10000.00,
        ]);
    }

    /**
     * Test Pessimistic Lock and Double Booking Collision Prevention
     */
    public function test_booking_service_pessimistic_lock_and_collision_prevention(): void
    {
        $service = app(BookingService::class);

        // First passenger successfully reserves S-05
        $booking1 = $service->createBooking($this->passenger, $this->trip1, [
            'seats_count' => 1,
            'seat_numbers' => ['S-05'],
            'passengers' => [
                ['name' => 'Fokou Tagne', 'cni' => '112233', 'phone' => '671112233'],
            ],
        ]);
        $this->assertNotNull($booking1);
        $this->assertEquals(['S-05'], $booking1->seat_numbers);

        // Second passenger attempts to book the exact same seat S-05
        $otherPassenger = User::create([
            'name' => 'Eto\'o Samuel',
            'email' => 'etoo@test.cm',
            'password' => bcrypt('PassSecure@2026!'),
            'role' => 'passager',
            'status' => 'active',
        ]);

        $this->expectException(Exception::class);
        $service->createBooking($otherPassenger, $this->trip1, [
            'seats_count' => 1,
            'seat_numbers' => ['S-05'],
            'passengers' => [
                ['name' => 'Eto\'o Samuel', 'cni' => '998877', 'phone' => '699887766'],
            ],
        ]);
    }

    /**
     * Test Crew Seats Hardcoded Lock (Seat 01 & Seat 16)
     */
    public function test_crew_seats_are_strictly_rejected_with_translation_key(): void
    {
        $service = app(BookingService::class);

        $this->expectException(Exception::class);
        $service->createBooking($this->passenger, $this->trip1, [
            'seats_count' => 1,
            'seat_numbers' => ['S-01'],
            'passengers' => [
                ['name' => 'Chauffeur Imposter', 'cni' => '000', 'phone' => '670000000'],
            ],
        ]);
    }

    /**
     * Test Trip Rescheduling (>12h away valid with VIP upgrade fee deducted from E-Wallet)
     */
    public function test_reschedule_flow_valid_over_12h_with_fare_upgrade(): void
    {
        $service = app(BookingService::class);

        // Initial booking on Trip 1: Standard 5,000 FCFA
        $booking = $service->createBooking($this->passenger, $this->trip1, [
            'seats_count' => 1,
            'seat_numbers' => ['S-07'],
            'passengers' => [
                ['name' => 'Fokou Tagne', 'cni' => '112233', 'phone' => '671112233'],
            ],
        ]);
        $initialQrToken = $booking->qr_code_token;
        $initialWallet = (float)$this->passenger->fresh()->wallet_balance; // 30,000

        // Reschedule to Trip 2 VIP (10,000 FCFA -> +5,000 FCFA fare difference)
        $updatedBooking = $service->rescheduleBooking(
            $booking,
            $this->trip2,
            $this->trip2Vip,
            ['S-08']
        );

        $this->assertEquals($this->trip2->id, $updatedBooking->trip_id);
        $this->assertEquals($this->trip2Vip->id, $updatedBooking->trip_class_id);
        $this->assertEquals(10000.00, (float)$updatedBooking->total_amount);
        $this->assertEquals(['S-08'], $updatedBooking->seat_numbers);
        $this->assertNotEquals($initialQrToken, $updatedBooking->qr_code_token);

        // Wallet was debited 5,000 FCFA
        $this->assertEquals($initialWallet - 5000.00, (float)$this->passenger->fresh()->wallet_balance);

        // Verify seats on old trip were restored and new trip decremented
        $this->assertEquals(73, $this->trip1->fresh()->seats_available);
        $this->assertEquals(22, $this->trip2Vip->fresh()->seats_available);
    }

    /**
     * Test Trip Rescheduling (>12h away valid with lower fare refund to E-Wallet)
     */
    public function test_reschedule_flow_valid_over_12h_with_fare_downgrade_refund(): void
    {
        $service = app(BookingService::class);

        // Initial booking on Trip 1 VIP: 8,000 FCFA
        $booking = $service->createBooking($this->passenger, $this->trip1, [
            'trip_class_id' => $this->trip1Vip->id,
            'seats_count' => 1,
            'seat_numbers' => ['S-10'],
            'passengers' => [
                ['name' => 'Fokou Tagne', 'cni' => '112233', 'phone' => '671112233'],
            ],
        ]);

        $initialWallet = (float)$this->passenger->fresh()->wallet_balance;

        // Reschedule to Trip 2 Classic (5,000 FCFA -> 3,000 FCFA refund to wallet)
        $updatedBooking = $service->rescheduleBooking(
            $booking,
            $this->trip2,
            $this->trip2Classic,
            ['S-12']
        );

        $this->assertEquals(5000.00, (float)$updatedBooking->total_amount);
        $this->assertEquals(['S-12'], $updatedBooking->seat_numbers);

        // Wallet was credited 3,000 FCFA
        $this->assertEquals($initialWallet + 3000.00, (float)$this->passenger->fresh()->wallet_balance);
    }

    /**
     * Test Trip Rescheduling strictly rejected if departure is within 12 hours
     */
    public function test_reschedule_flow_rejected_strictly_within_12h(): void
    {
        $service = app(BookingService::class);

        // Create urgent trip departing in 6 hours
        $urgentTrip = Trip::create([
            'branch_id' => $this->branch->id,
            'vehicle_id' => $this->vehicle->id,
            'trip_number' => 'RV-URGENT-6H',
            'transport_mode' => 'road',
            'departure_city' => 'Douala',
            'departure_station' => 'Gare Bessengue',
            'arrival_city' => 'Yaoundé',
            'arrival_station' => 'Gare Mvan',
            'departure_time' => now()->addHours(6),
            'arrival_time_estimated' => now()->addHours(10),
            'base_price' => 5000.00,
            'seats_available' => 73,
            'cargo_available_kg' => 2000,
            'cargo_price_per_kg' => 100,
            'status' => 'scheduled',
        ]);

        $booking = $service->createBooking($this->passenger, $urgentTrip, [
            'seats_count' => 1,
            'seat_numbers' => ['S-22'],
            'passengers' => [
                ['name' => 'Fokou Tagne', 'cni' => '112233', 'phone' => '671112233'],
            ],
        ]);

        $this->expectException(Exception::class);
        $service->rescheduleBooking($booking, $this->trip2);
    }

    /**
     * Test Passenger Manifest Generation & Role Authorization
     */
    public function test_manifest_generation_and_role_authorization(): void
    {
        $service = app(BookingService::class);

        // Create booking for Trip 1
        $service->createBooking($this->passenger, $this->trip1, [
            'seats_count' => 2,
            'seat_numbers' => ['S-04', 'S-05'],
            'passengers' => [
                ['name' => 'Fokou Tagne', 'cni' => '112233', 'phone' => '671112233'],
                ['name' => 'Fokou Alice', 'cni' => '445566', 'phone' => '674445566'],
            ],
        ]);

        // 1. Branch Manager can view their manifest
        $responseManager = $this->actingAs($this->manager)->get(route('manager.trips.manifest', $this->trip1));
        $responseManager->assertStatus(200);
        $responseManager->assertSee('Feuille de Route & Manifeste d\'Embarquement');
        $responseManager->assertSee('RV-DLA-YDE-24H');
        $responseManager->assertSee('Fokou Tagne');
        $responseManager->assertSee('Fokou Alice');
        $responseManager->assertSee('S-04');
        $responseManager->assertSee('S-05');

        // 2. Admin can view manifest
        $responseAdmin = $this->actingAs($this->admin)->get(route('manager.trips.manifest', $this->trip1));
        $responseAdmin->assertStatus(200);

        // 3. Manager of another branch is forbidden (403)
        $responseOther = $this->actingAs($this->otherManager)->get(route('manager.trips.manifest', $this->trip1));
        $responseOther->assertStatus(403);

        // 4. Passenger cannot access manager manifest
        $responsePassenger = $this->actingAs($this->passenger)->get(route('manager.trips.manifest', $this->trip1));
        $responsePassenger->assertRedirect(route('passenger.dashboard'));
    }

    /**
     * Test Thermal Receipt 80mm View
     */
    public function test_thermal_receipt_view_renders_for_passenger(): void
    {
        $service = app(BookingService::class);

        $booking = $service->createBooking($this->passenger, $this->trip1, [
            'seats_count' => 1,
            'seat_numbers' => ['S-06'],
            'passengers' => [
                ['name' => 'Fokou Tagne', 'cni' => '112233', 'phone' => '671112233'],
            ],
        ]);

        $response = $this->actingAs($this->passenger)->get(route('passenger.bookings.thermal', $booking));
        $response->assertStatus(200);
        $response->assertSee('REAL EXPRESS VOYAGES');
        $response->assertSee('Ticket Thermique 80mm');
        $response->assertSee($booking->booking_reference);
        $response->assertSee('S-06');
        $response->assertSee('JETON DE CONTROLE EMBARQUEMENT (QR)');
    }

    /**
     * Test Event & Listener Queued Pipeline
     */
    public function test_asynchronous_notification_events_are_dispatched(): void
    {
        Event::fake([
            BookingConfirmedEvent::class,
            TripReminderEvent::class,
            AdvanceReservationExpiringEvent::class,
        ]);

        $service = app(BookingService::class);

        $booking = $service->createBooking($this->passenger, $this->trip1, [
            'seats_count' => 1,
            'seat_numbers' => ['S-09'],
            'passengers' => [
                ['name' => 'Fokou Tagne', 'cni' => '112233', 'phone' => '671112233'],
            ],
        ]);

        // Pay via wallet
        $this->actingAs($this->passenger)->post(route('passenger.payments.booking', $booking), [
            'method' => 'wallet',
        ]);

        Event::assertDispatched(BookingConfirmedEvent::class, function ($e) use ($booking) {
            return $e->booking->id === $booking->id;
        });
    }
}
