<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Trip;
use App\Models\Booking;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test homepage loads successfully for visitors.
     */
    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test guests are redirected to passenger registration when trying to view bookings.
     */
    public function test_guest_is_redirected_to_registration(): void
    {
        $response = $this->get('/passenger/bookings');

        $response->assertRedirect(route('register.passenger'));
    }

    /**
     * Test passenger can initiate booking and reserve seats.
     */
    public function test_passenger_can_book_trip_seats(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();
        $initialSeats = $trip->seats_available;

        $response = $this->actingAs($passenger)->post("/passenger/trips/{$trip->id}/book", [
            'seats_count' => 1,
            'seat_numbers' => ['S-05'],
            'passengers' => [
                ['name' => 'Marc Abena', 'cni' => '102938475', 'phone' => '+237 677 889 900'],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertEquals($initialSeats - 1, $trip->fresh()->seats_available);

        $booking = Booking::where('passenger_id', $passenger->id)->latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('pending', $booking->status);
    }

    /**
     * Test passenger can view trip page with the 3D virtual bus diagram.
     */
    public function test_passenger_can_view_trip_with_3d_virtual_bus_diagram(): void
    {
        $passenger = User::where('role', 'passager')->first();
        $trip = Trip::where('status', 'scheduled')->first();

        $response = $this->actingAs($passenger)->get("/trips/{$trip->id}");

        $response->assertStatus(200);
        $response->assertSee('webglBusContainer', false);
        $response->assertSee('btnCamIso', false);
        $response->assertSee('three-bus-engine.js', false);
        $response->assertSee('three.min.js', false);
        $response->assertSee('OrbitControls.js', false);
        $response->assertSee('hud-webgl-badge', false);
    }
}

