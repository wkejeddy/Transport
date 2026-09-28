<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\NotificationService;
use Exception;

class RealVoyageEnhancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $passenger;
    protected Branch $branch;
    protected Vehicle $vehicle;
    protected Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'DLA-MVAN',
            'name' => 'Agence Douala Mvan',
            'region' => 'Littoral',
            'city' => 'Douala',
            'address' => 'Boulevard de la Liberte, Akwa',
            'phone' => '+237 670 00 00 01',
            'is_headquarters' => true,
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Directeur General',
            'email' => 'admin@realvoyage.cm',
            'password' => bcrypt('AdminSecure@2026!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->manager = User::create([
            'name' => 'Chef de Gare Douala',
            'email' => 'manager@realvoyage.cm',
            'password' => bcrypt('ManagerSecure@2026!'),
            'role' => 'manager',
            'branch_id' => $this->branch->id,
            'status' => 'active',
        ]);

        $this->passenger = User::create([
            'name' => 'Kamga Emmanuel',
            'email' => 'passenger@test.cm',
            'phone' => '+237671234567',
            'password' => bcrypt('PassengerSecure@2026!'),
            'role' => 'passager',
            'wallet_balance' => 50000.00,
            'status' => 'active',
        ]);

        $this->vehicle = Vehicle::create([
            'branch_id' => $this->branch->id,
            'model' => 'Marcopolo Paradiso 1200 G7',
            'registration_plate' => 'LT-888-RV',
            'capacity_seats' => 75,
            'capacity_cargo' => 1500,
            'driver_name' => 'Jean-Paul Nkoa',
            'convoyeur_name' => 'Serge Atangana',
            'status' => 'active',
        ]);

        $this->trip = Trip::create([
            'branch_id' => $this->branch->id,
            'vehicle_id' => $this->vehicle->id,
            'trip_number' => 'RV-DLA-YDE-01',
            'transport_mode' => 'road',
            'departure_city' => 'Douala',
            'departure_station' => 'Gare Akwa Direct',
            'arrival_city' => 'Yaounde',
            'arrival_station' => 'Gare Mvan Voyage',
            'departure_time' => now()->addHours(12),
            'arrival_time_estimated' => now()->addHours(16),
            'base_price' => 5000.00,
            'seats_available' => 73,
            'cargo_available_kg' => 1500,
            'cargo_price_per_kg' => 100,
            'status' => 'scheduled',
        ]);

        TripClass::create([
            'trip_id' => $this->trip->id,
            'class_code' => 'vip',
            'class_name' => 'VIP 1ere Classe',
            'seat_count' => 73,
            'seats_available' => 73,
            'price' => 5000.00,
        ]);
    }

    public function test_booking_service_creates_booking_and_locks_seats(): void
    {
        $service = app(BookingService::class);

        $booking = $service->createBooking($this->passenger, $this->trip, [
            'seats_count' => 2,
            'seat_numbers' => ['S-05', 'S-06'],
            'passengers' => [
                ['name' => 'Kamga Emmanuel', 'cni' => '1122334455', 'phone' => '+237671234567'],
                ['name' => 'Kamga Marie', 'cni' => '9988776655', 'phone' => '+237679887766'],
            ],
            'booking_option' => 'immediate',
        ]);

        $this->assertNotNull($booking->id);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals(10000.00, $booking->total_amount);
        $this->assertContains('S-05', $booking->seat_numbers);
        $this->assertContains('S-06', $booking->seat_numbers);

        $this->assertEquals(71, $this->trip->fresh()->seats_available);
    }

    public function test_booking_service_prevents_double_booking_same_seat(): void
    {
        $service = app(BookingService::class);

        $service->createBooking($this->passenger, $this->trip, [
            'seats_count' => 1,
            'seat_numbers' => ['S-10'],
            'passengers' => [
                ['name' => 'First Booker', 'cni' => '123', 'phone' => '670000000'],
            ],
        ]);

        $this->expectException(Exception::class);
        $service->createBooking($this->passenger, $this->trip, [
            'seats_count' => 1,
            'seat_numbers' => ['S-10'],
            'passengers' => [
                ['name' => 'Second Booker', 'cni' => '456', 'phone' => '671111111'],
            ],
        ]);
    }

    public function test_booking_service_applies_round_trip_discount(): void
    {
        $service = app(BookingService::class);

        $booking = $service->createBooking($this->passenger, $this->trip, [
            'seats_count' => 1,
            'seat_numbers' => ['S-20'],
            'passengers' => [
                ['name' => 'Kamga Emmanuel', 'cni' => '1122334455', 'phone' => '+237671234567'],
            ],
            'is_round_trip' => true,
        ]);

        $this->assertEquals(250.00, $booking->round_trip_discount);
        $this->assertEquals(4750.00, $booking->total_amount);
        $this->assertTrue($booking->is_round_trip);
    }

    public function test_checkin_validation_and_single_use_security(): void
    {
        $service = app(BookingService::class);
        $booking = $service->createBooking($this->passenger, $this->trip, [
            'seats_count' => 1,
            'seat_numbers' => ['S-04'],
            'passengers' => [
                ['name' => 'Kamga Emmanuel', 'cni' => '1122334455', 'phone' => '+237671234567'],
            ],
        ]);

        $response = $this->actingAs($this->manager)->post(route('manager.checkin.process', $booking));
        $response->assertSessionHas('error');

        $booking->update(['status' => 'confirmed']);

        $response = $this->actingAs($this->manager)->post(route('manager.checkin.process', $booking));
        $response->assertSessionHas('success');
        $this->assertEquals('checked_in', $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->checked_in_at);
        $this->assertEquals($this->manager->id, $booking->fresh()->checked_in_by);

        $response = $this->actingAs($this->manager)->post(route('manager.checkin.process', $booking));
        $response->assertSessionHas('info');
    }

    public function test_whatsapp_notification_url_generation(): void
    {
        $service = app(BookingService::class);
        $booking = $service->createBooking($this->passenger, $this->trip, [
            'seats_count' => 1,
            'seat_numbers' => ['S-07'],
            'passengers' => [
                ['name' => 'Kamga Emmanuel', 'cni' => '1122334455', 'phone' => '671234567'],
            ],
        ]);

        $url = NotificationService::getWhatsAppShareUrl($booking);
        $this->assertStringContainsString('https://api.whatsapp.com/send', $url);
        $this->assertStringContainsString('REAL%20VOYAGE%20TRANSPORT', $url);
        $this->assertStringContainsString($booking->booking_reference, $url);
    }

    public function test_manifest_view_renders_for_agency_manager(): void
    {
        $response = $this->actingAs($this->manager)->get(route('manager.trips.manifest', $this->trip));
        $response->assertStatus(200);
        $response->assertSee('REAL EXPRESS VOYAGES');
        $response->assertSee($this->trip->trip_number);
        $response->assertSee($this->vehicle->driver_name);
        $response->assertSee($this->vehicle->convoyeur_name);
    }

    public function test_analytics_metrics_on_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Douala');
    }
}