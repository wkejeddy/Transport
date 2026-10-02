<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenPassengersAndTenTripsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\TransportPlatformSeeder::class);
        $this->seed(\Database\Seeders\TenPassengersAndTenTripsSeeder::class);
    }

    /**
     * Verify all 10 new passengers exist with correct wallet balances, active status and passager role.
     */
    public function test_ten_passengers_exist_with_active_wallets(): void
    {
        $passengers = [
            'henriette.ngonlend@realvoyage.cm' => ['Henriette Ngo Nlend', 55000.00],
            'arnaud.kouam@realvoyage.cm'       => ['Arnaud Kouam Tchinda', 40000.00],
            'clarisse.eyenga@realvoyage.cm'     => ['Clarisse Eyenga Ondoa', 25000.00],
            'alain.mvondo@realvoyage.cm'        => ['Alain Bertrand Mvondo', 35000.00],
            'gisele.kenmogne@realvoyage.cm'     => ['Gisèle Kenmogne Wambo', 60000.00],
            'fabrice.djoko@realvoyage.cm'       => ['Fabrice Djoko Simo', 18000.00],
            'beatrice.bilong@realvoyage.cm'     => ['Béatrice Bilong Nyobe', 48000.00],
            'christian.ndongo@realvoyage.cm'    => ['Christian Ndongo Essomba', 32000.00],
            'solange.tcheutchoua@realvoyage.cm' => ['Solange Tcheutchoua', 70000.00],
            'lionel.aboubakar@realvoyage.cm'    => ['Lionel Aboubakar Bakary', 22500.00],
        ];

        $this->assertCount(10, $passengers);

        foreach ($passengers as $email => [$name, $wallet]) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "Passenger with email {$email} should exist");
            $this->assertEquals($name, $user->name);
            $this->assertEquals('passager', $user->role);
            $this->assertEquals('active', $user->status);
            $this->assertEquals($wallet, (float)$user->wallet_balance);
        }
    }

    /**
     * Verify all 10 new trips exist with scheduled departure times, vehicles, and classes.
     */
    public function test_ten_scheduled_trips_exist_across_cameroon(): void
    {
        $trips = [
            'RV-DLA-KRI-1000' => ['Douala', 'Kribi', 4500.00],
            'RV-KRI-DLA-2100' => ['Kribi', 'Douala', 4500.00],
            'RV-YAO-BER-1000' => ['Yaoundé', 'Bertoua', 7000.00],
            'RV-BER-YAO-2100' => ['Bertoua', 'Yaoundé', 7000.00],
            'RV-DLA-LMB-1000' => ['Douala', 'Limbe', 3000.00],
            'RV-LMB-DLA-2100' => ['Limbe', 'Douala', 3000.00],
            'RV-BAF-BMD-1000' => ['Bafoussam', 'Bamenda', 3500.00],
            'RV-YAO-EBO-1000' => ['Yaoundé', 'Ebolowa', 3500.00],
            'RV-DLA-YAO-2100' => ['Douala', 'Yaoundé', 6000.00],
            'RV-YAO-DLA-2100' => ['Yaoundé', 'Douala', 6000.00],
        ];

        $this->assertCount(10, $trips);

        foreach ($trips as $num => [$from, $to, $price]) {
            $trip = Trip::with(['vehicle', 'classes', 'branch'])->where('trip_number', $num)->first();
            $this->assertNotNull($trip, "Trip {$num} should exist");
            $this->assertEquals($from, $trip->departure_city);
            $this->assertEquals($to, $trip->arrival_city);
            $this->assertEquals($price, (float)$trip->base_price);
            $this->assertEquals('scheduled', $trip->status);
            $this->assertNotNull($trip->vehicle);
            $this->assertNotEmpty($trip->classes);
        }
    }

    /**
     * Verify trips index renders all new destinations and trips.
     */
    public function test_trips_listing_displays_new_destinations(): void
    {
        $response = $this->get(route('trips.index'));
        $response->assertStatus(200);
        $response->assertSee('RV-DLA-KRI-1000');
        $response->assertSee('RV-YAO-BER-1000');
        $response->assertSee('RV-DLA-LMB-1000');
    }
}
