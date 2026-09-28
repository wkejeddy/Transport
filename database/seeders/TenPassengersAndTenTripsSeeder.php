<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Terminal;
use Carbon\Carbon;

class TenPassengersAndTenTripsSeeder extends Seeder
{
    /**
     * Run the database seeds:
     * - 10 New Active Real Voyage Passengers with credited E-Wallets
     * - 10 New Scheduled Intercity Trips connecting major Cameroon corridors
     */
    public function run(): void
    {
        // -----------------------------------------------------------------
        // 1. INSERT 10 PASSENGERS
        // -----------------------------------------------------------------
        $passengersData = [
            [
                'name' => 'Henriette Ngo Nlend',
                'email' => 'henriette.ngonlend@realvoyage.cm',
                'phone' => '+237 670 11 22 33',
                'wallet_balance' => 55000.00,
            ],
            [
                'name' => 'Arnaud Kouam Tchinda',
                'email' => 'arnaud.kouam@realvoyage.cm',
                'phone' => '+237 699 22 33 44',
                'wallet_balance' => 40000.00,
            ],
            [
                'name' => 'Clarisse Eyenga Ondoa',
                'email' => 'clarisse.eyenga@realvoyage.cm',
                'phone' => '+237 677 33 44 55',
                'wallet_balance' => 25000.00,
            ],
            [
                'name' => 'Alain Bertrand Mvondo',
                'email' => 'alain.mvondo@realvoyage.cm',
                'phone' => '+237 690 44 55 66',
                'wallet_balance' => 35000.00,
            ],
            [
                'name' => 'Gisèle Kenmogne Wambo',
                'email' => 'gisele.kenmogne@realvoyage.cm',
                'phone' => '+237 675 55 66 77',
                'wallet_balance' => 60000.00,
            ],
            [
                'name' => 'Fabrice Djoko Simo',
                'email' => 'fabrice.djoko@realvoyage.cm',
                'phone' => '+237 698 66 77 88',
                'wallet_balance' => 18000.00,
            ],
            [
                'name' => 'Béatrice Bilong Nyobe',
                'email' => 'beatrice.bilong@realvoyage.cm',
                'phone' => '+237 671 77 88 99',
                'wallet_balance' => 48000.00,
            ],
            [
                'name' => 'Christian Ndongo Essomba',
                'email' => 'christian.ndongo@realvoyage.cm',
                'phone' => '+237 693 88 99 00',
                'wallet_balance' => 32000.00,
            ],
            [
                'name' => 'Solange Tcheutchoua',
                'email' => 'solange.tcheutchoua@realvoyage.cm',
                'phone' => '+237 678 99 00 11',
                'wallet_balance' => 70000.00,
            ],
            [
                'name' => 'Lionel Aboubakar Bakary',
                'email' => 'lionel.aboubakar@realvoyage.cm',
                'phone' => '+237 694 00 11 22',
                'wallet_balance' => 22500.00,
            ],
        ];

        foreach ($passengersData as $p) {
            User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'phone' => $p['phone'],
                    'password' => bcrypt('Passager@2026!'),
                    'role' => 'passager',
                    'wallet_balance' => $p['wallet_balance'],
                    'status' => 'active',
                ]
            );
        }

        // -----------------------------------------------------------------
        // 2. RETRIEVE OR CREATE BRANCHES & BUS FLEET
        // -----------------------------------------------------------------
        $dlaBranch = Branch::where('region', 'Douala')->orWhere('code', 'DLA')->first() ?? Branch::first();
        $ydeBranch = Branch::where('region', 'Yaounde')->orWhere('code', 'YDE')->first() ?? Branch::first();
        $wstBranch = Branch::where('region', 'Ouest')->orWhere('code', 'WST')->first() ?? Branch::first();

        // Ensure 5 dedicated buses exist for the new lines
        $buses = [
            'BUS-VIP-101' => [
                'name' => 'Marcopolo Paradiso G8 VIP (75 Places)',
                'branch_id' => $dlaBranch->id,
                'capacity_seats' => 75,
                'type' => 'vip_bus',
            ],
            'BUS-VIP-102' => [
                'name' => 'Scania Touring HD VIP (80 Places)',
                'branch_id' => $ydeBranch->id,
                'capacity_seats' => 80,
                'type' => 'vip_bus',
            ],
            'BUS-CLS-103' => [
                'name' => 'Mercedes-Benz Tourismo Express (75 Places)',
                'branch_id' => $wstBranch->id,
                'capacity_seats' => 75,
                'type' => 'classic_bus',
            ],
            'BUS-CLS-104' => [
                'name' => 'Volvo 9700 Grand Confort (80 Places)',
                'branch_id' => $dlaBranch->id,
                'capacity_seats' => 80,
                'type' => 'classic_bus',
            ],
            'BUS-VIP-105' => [
                'name' => 'Neoplan Skyliner Royal Star (80 Places)',
                'branch_id' => $ydeBranch->id,
                'capacity_seats' => 80,
                'type' => 'vip_bus',
            ],
        ];

        $vehicleModels = [];
        foreach ($buses as $code => $busInfo) {
            $vehicleModels[$code] = Vehicle::updateOrCreate(
                ['code' => $code],
                [
                    'branch_id' => $busInfo['branch_id'],
                    'name' => $busInfo['name'],
                    'transport_mode' => 'road',
                    'type' => $busInfo['type'],
                    'capacity_seats' => $busInfo['capacity_seats'],
                    'capacity_cargo' => 7000,
                    'status' => 'active',
                ]
            );
        }

        // -----------------------------------------------------------------
        // 3. INSERT 10 SCHEDULED TRIPS ACROSS CAMEROON CORRIDORS
        // -----------------------------------------------------------------
        $baseDate = Carbon::tomorrow();

        $tripsData = [
            [
                'trip_number' => 'RV-DLA-KRI-1000',
                'branch_id' => $dlaBranch->id,
                'vehicle_code' => 'BUS-VIP-101',
                'departure_city' => 'Douala',
                'departure_station' => 'Gare Akwa Direct',
                'arrival_city' => 'Kribi',
                'arrival_station' => 'Gare Touristique Kribi Plage',
                'departure_time' => $baseDate->copy()->setTime(10, 0),
                'arrival_time_estimated' => $baseDate->copy()->setTime(13, 0),
                'base_price' => 4500.00,
                'cargo_price_per_kg' => 100,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 6000.00, 'seats' => 25],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 4500.00, 'seats' => 48],
                ],
            ],
            [
                'trip_number' => 'RV-KRI-DLA-2100',
                'branch_id' => $dlaBranch->id,
                'vehicle_code' => 'BUS-VIP-101',
                'departure_city' => 'Kribi',
                'departure_station' => 'Gare Touristique Kribi Plage',
                'arrival_city' => 'Douala',
                'arrival_station' => 'Gare Akwa Direct',
                'departure_time' => $baseDate->copy()->setTime(21, 0),
                'arrival_time_estimated' => $baseDate->copy()->addDay()->setTime(0, 0),
                'base_price' => 4500.00,
                'cargo_price_per_kg' => 100,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 6000.00, 'seats' => 25],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 4500.00, 'seats' => 48],
                ],
            ],
            [
                'trip_number' => 'RV-YAO-BER-1000',
                'branch_id' => $ydeBranch->id,
                'vehicle_code' => 'BUS-VIP-102',
                'departure_city' => 'Yaoundé',
                'departure_station' => 'Gare Mvan Voyage',
                'arrival_city' => 'Bertoua',
                'arrival_station' => 'Gare Centrale Bertoua',
                'departure_time' => $baseDate->copy()->setTime(10, 0),
                'arrival_time_estimated' => $baseDate->copy()->setTime(15, 30),
                'base_price' => 7000.00,
                'cargo_price_per_kg' => 120,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 9500.00, 'seats' => 30],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 7000.00, 'seats' => 48],
                ],
            ],
            [
                'trip_number' => 'RV-BER-YAO-2100',
                'branch_id' => $ydeBranch->id,
                'vehicle_code' => 'BUS-VIP-102',
                'departure_city' => 'Bertoua',
                'departure_station' => 'Gare Centrale Bertoua',
                'arrival_city' => 'Yaoundé',
                'arrival_station' => 'Gare Mvan Voyage',
                'departure_time' => $baseDate->copy()->setTime(21, 0),
                'arrival_time_estimated' => $baseDate->copy()->addDay()->setTime(2, 30),
                'base_price' => 7000.00,
                'cargo_price_per_kg' => 120,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 9500.00, 'seats' => 30],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 7000.00, 'seats' => 48],
                ],
            ],
            [
                'trip_number' => 'RV-DLA-LMB-1000',
                'branch_id' => $dlaBranch->id,
                'vehicle_code' => 'BUS-CLS-104',
                'departure_city' => 'Douala',
                'departure_station' => 'Gare Bonabéri Express',
                'arrival_city' => 'Limbe',
                'arrival_station' => 'Gare Océan Limbe',
                'departure_time' => $baseDate->copy()->setTime(10, 0),
                'arrival_time_estimated' => $baseDate->copy()->setTime(11, 45),
                'base_price' => 3000.00,
                'cargo_price_per_kg' => 80,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 4500.00, 'seats' => 20],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 3000.00, 'seats' => 58],
                ],
            ],
            [
                'trip_number' => 'RV-LMB-DLA-2100',
                'branch_id' => $dlaBranch->id,
                'vehicle_code' => 'BUS-CLS-104',
                'departure_city' => 'Limbe',
                'departure_station' => 'Gare Océan Limbe',
                'arrival_city' => 'Douala',
                'arrival_station' => 'Gare Bonabéri Express',
                'departure_time' => $baseDate->copy()->setTime(21, 0),
                'arrival_time_estimated' => $baseDate->copy()->setTime(22, 45),
                'base_price' => 3000.00,
                'cargo_price_per_kg' => 80,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 4500.00, 'seats' => 20],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 3000.00, 'seats' => 58],
                ],
            ],
            [
                'trip_number' => 'RV-BAF-BMD-1000',
                'branch_id' => $wstBranch->id,
                'vehicle_code' => 'BUS-CLS-103',
                'departure_city' => 'Bafoussam',
                'departure_station' => 'Gare Centrale Bafoussam',
                'arrival_city' => 'Bamenda',
                'arrival_station' => 'Gare Commerciale Bamenda',
                'departure_time' => $baseDate->copy()->setTime(10, 0),
                'arrival_time_estimated' => $baseDate->copy()->setTime(12, 30),
                'base_price' => 3500.00,
                'cargo_price_per_kg' => 90,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 5000.00, 'seats' => 25],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 3500.00, 'seats' => 48],
                ],
            ],
            [
                'trip_number' => 'RV-YAO-EBO-1000',
                'branch_id' => $ydeBranch->id,
                'vehicle_code' => 'BUS-VIP-105',
                'departure_city' => 'Yaoundé',
                'departure_station' => 'Gare Mvan Voyage',
                'arrival_city' => 'Ebolowa',
                'arrival_station' => 'Gare Régionale Ebolowa',
                'departure_time' => $baseDate->copy()->setTime(10, 0),
                'arrival_time_estimated' => $baseDate->copy()->setTime(12, 45),
                'base_price' => 3500.00,
                'cargo_price_per_kg' => 90,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 5000.00, 'seats' => 30],
                    ['code' => 'classic', 'name' => 'Grand Confort', 'price' => 3500.00, 'seats' => 48],
                ],
            ],
            [
                'trip_number' => 'RV-DLA-YAO-2100',
                'branch_id' => $dlaBranch->id,
                'vehicle_code' => 'BUS-VIP-101',
                'departure_city' => 'Douala',
                'departure_station' => 'Gare Bessengue',
                'arrival_city' => 'Yaoundé',
                'arrival_station' => 'Gare Mvan Voyage',
                'departure_time' => $baseDate->copy()->setTime(21, 0),
                'arrival_time_estimated' => $baseDate->copy()->addDay()->setTime(1, 0),
                'base_price' => 6000.00,
                'cargo_price_per_kg' => 110,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 9000.00, 'seats' => 35],
                    ['code' => 'classic', 'name' => 'Prestige Direct', 'price' => 6000.00, 'seats' => 38],
                ],
            ],
            [
                'trip_number' => 'RV-YAO-DLA-2100',
                'branch_id' => $ydeBranch->id,
                'vehicle_code' => 'BUS-VIP-105',
                'departure_city' => 'Yaoundé',
                'departure_station' => 'Gare Mvan Voyage',
                'arrival_city' => 'Douala',
                'arrival_station' => 'Gare Bessengue',
                'departure_time' => $baseDate->copy()->setTime(21, 0),
                'arrival_time_estimated' => $baseDate->copy()->addDay()->setTime(1, 0),
                'base_price' => 6000.00,
                'cargo_price_per_kg' => 110,
                'classes' => [
                    ['code' => 'vip', 'name' => 'VIP 1ère Classe', 'price' => 9000.00, 'seats' => 35],
                    ['code' => 'classic', 'name' => 'Prestige Direct', 'price' => 6000.00, 'seats' => 43],
                ],
            ],
        ];

        foreach ($tripsData as $t) {
            $veh = $vehicleModels[$t['vehicle_code']] ?? Vehicle::first();
            $availableSeats = $veh->capacity_seats - 2; // excluding driver & convoyeur

            $trip = Trip::updateOrCreate(
                ['trip_number' => $t['trip_number']],
                [
                    'branch_id' => $t['branch_id'],
                    'vehicle_id' => $veh->id,
                    'transport_mode' => 'road',
                    'departure_city' => $t['departure_city'],
                    'departure_station' => $t['departure_station'],
                    'arrival_city' => $t['arrival_city'],
                    'arrival_station' => $t['arrival_station'],
                    'departure_time' => $t['departure_time'],
                    'arrival_time_estimated' => $t['arrival_time_estimated'],
                    'base_price' => $t['base_price'],
                    'seats_available' => $availableSeats,
                    'cargo_available_kg' => 6000,
                    'cargo_price_per_kg' => $t['cargo_price_per_kg'],
                    'status' => 'scheduled',
                ]
            );

            // Re-seed or create classes
            $trip->classes()->delete();
            foreach ($t['classes'] as $cls) {
                TripClass::create([
                    'trip_id' => $trip->id,
                    'class_code' => $cls['code'],
                    'class_name' => $cls['name'],
                    'seat_count' => $cls['seats'],
                    'seats_available' => $cls['seats'],
                    'price' => $cls['price'],
                    'amenities' => ['Climatisation', 'Prises USB', 'Wi-Fi 4G', 'Bagages sécurisés'],
                ]);
            }
        }
    }
}
