<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewBusesAndPassengersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that the 5 new buses are seeded with correct capacities and types
     */
    public function test_five_new_buses_are_available(): void
    {
        $busCodes = [
            'RV-75-003',
            'RV-80-003',
            'RV-75-004',
            'RV-80-004',
            'RV-80-005',
        ];

        foreach ($busCodes as $code) {
            $bus = Vehicle::where('code', $code)->first();
            $this->assertNotNull($bus, "Bus {$code} must exist");
            $this->assertEquals('active', $bus->status);
            $this->assertContains($bus->capacity_seats, [75, 80]);
        }
    }

    /**
     * Test that the 5 new trips exist with scheduled departure times and prices
     */
    public function test_five_new_scheduled_trips_with_times(): void
    {
        $tripNumbers = [
            'RV-DLA-YAO-0630' => ['Douala', 'Yaoundé', 6, 30],
            'RV-YAO-DLA-0800' => ['Yaoundé', 'Douala', 8, 0],
            'RV-BAF-DLA-1300' => ['Bafoussam', 'Douala', 13, 0],
            'RV-DLA-DSC-1530' => ['Douala', 'Dschang', 15, 30],
            'RV-YAO-BAF-2200' => ['Yaoundé', 'Bafoussam', 22, 0],
        ];

        foreach ($tripNumbers as $num => [$from, $to, $hour, $min]) {
            $trip = Trip::with(['vehicle', 'classes'])->where('trip_number', $num)->first();
            $this->assertNotNull($trip, "Trip {$num} must exist");
            $this->assertEquals($from, $trip->departure_city);
            $this->assertEquals($to, $trip->arrival_city);
            $this->assertEquals($hour, $trip->departure_time->hour);
            $this->assertEquals($min, $trip->departure_time->minute);
            $this->assertEquals('scheduled', $trip->status);
            $this->assertNotNull($trip->vehicle);
            $this->assertNotEmpty($trip->classes);
        }
    }

    /**
     * Test that the 5 new passengers exist and have active wallets
     */
    public function test_five_new_passengers_exist(): void
    {
        $passengers = [
            'estelle.mbarga@gmail.com' => ['Dr. Estelle Mbarga', 45000],
            'boris.kamdem@yahoo.com'   => ['Boris Kamdem', 30000],
            'sandrine.fotso@outlook.com' => ['Sandrine Fotso', 18500],
            'patrick.abena@gmail.com'   => ['Patrick Abena', 12000],
            'nadine.bella@yahoo.fr'     => ['Nadine Bella', 25000],
        ];

        foreach ($passengers as $email => [$name, $expectedBalance]) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "Passenger {$email} must exist");
            $this->assertEquals($name, $user->name);
            $this->assertEquals('passager', $user->role);
            $this->assertEquals('active', $user->status);
            $this->assertEquals($expectedBalance, $user->wallet_balance);
        }
    }

    /**
     * Test that trip listing displays the new trips
     */
    public function test_trips_listing_shows_new_trips(): void
    {
        $response = $this->get(route('trips.index'));
        $response->assertStatus(200);
        $response->assertSee('RV-DLA-YAO-0630');
        $response->assertSee('06:30');
        $response->assertSee('RV-YAO-DLA-0800');
        $response->assertSee('08:00');
    }
}
