<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use App\Models\Terminal;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\TripClass;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class NewBusesAndPassengersSeeder extends Seeder
{
    /**
     * Run the database seeds:
     * - 5 New Luxury/VIP Buses with detailed Seat Layouts and Specifications
     * - 5 New Scheduled Trips with Departure/Arrival Times across major Cameroon axes
     * - 5 New Real Registered Passenger Accounts with pre-credited E-Wallets
     */
    public function run(): void
    {
        // 1. Fetch Branches and Terminals
        $doualaBranch = Branch::where('code', 'DLA-REG-01')->first() ?? Branch::first();
        $yaoundeBranch = Branch::where('code', 'YAO-REG-02')->first() ?? Branch::first();
        $westBranch = Branch::where('code', 'WST-REG-03')->first() ?? Branch::first();

        $terminalMakepe = Terminal::where('name', 'Terminal Douala Maképé')->first() 
            ?? Terminal::where('city', 'Douala')->first();
        $terminalPK14 = Terminal::where('name', 'Terminal Douala PK14')->first() 
            ?? $terminalMakepe;
        $terminalBepanda = Terminal::where('name', 'Terminal Douala Bépanda')->first() 
            ?? $terminalMakepe;

        $terminalEleveur = Terminal::where('name', 'Terminal Yaoundé Éleveur')->first() 
            ?? Terminal::where('city', 'Yaoundé')->first();
        $terminalOlembe = Terminal::where('name', 'Terminal Yaoundé Olembé')->first() 
            ?? $terminalEleveur;
        $terminalBiyemAssi = Terminal::where('name', 'Terminal Yaoundé Biyem-Assi')->first() 
            ?? $terminalEleveur;

        $terminalBafoussam = Terminal::where('name', 'Terminal Central Bafoussam')->first() 
            ?? Terminal::where('region', 'Ouest')->first();
        $terminalDschang = Terminal::where('name', 'Terminal Dschang Centre')->first() 
            ?? $terminalBafoussam;

        // -------------------------------------------------------------
        // 2. CREATE 5 NEW BUSES (VEHICLES)
        // -------------------------------------------------------------
        $busesData = [
            [
                'code' => 'RV-75-003',
                'branch_id' => $doualaBranch->id,
                'name' => 'Mercedes-Benz Tourismo VIP (75 Places)',
                'transport_mode' => 'road',
                'type' => 'vip_bus',
                'capacity_seats' => 75,
                'capacity_cargo' => 6500,
                'status' => 'active',
            ],
            [
                'code' => 'RV-80-003',
                'branch_id' => $yaoundeBranch->id,
                'name' => 'Scania Irizar i6S Prestige (80 Places)',
                'transport_mode' => 'road',
                'type' => 'vip_bus',
                'capacity_seats' => 80,
                'capacity_cargo' => 7500,
                'status' => 'active',
            ],
            [
                'code' => 'RV-75-004',
                'branch_id' => $westBranch->id,
                'name' => 'MAN Lion\'s Coach Supreme (75 Places)',
                'transport_mode' => 'road',
                'type' => 'classic_bus',
                'capacity_seats' => 75,
                'capacity_cargo' => 6000,
                'status' => 'active',
            ],
            [
                'code' => 'RV-80-004',
                'branch_id' => $doualaBranch->id,
                'name' => 'Volvo 9900 Royal Class (80 Places)',
                'transport_mode' => 'road',
                'type' => 'vip_bus',
                'capacity_seats' => 80,
                'capacity_cargo' => 8000,
                'status' => 'active',
            ],
            [
                'code' => 'RV-80-005',
                'branch_id' => $yaoundeBranch->id,
                'name' => 'Neoplan Cityliner Night Star (80 Places)',
                'transport_mode' => 'road',
                'type' => 'vip_bus',
                'capacity_seats' => 80,
                'capacity_cargo' => 8000,
                'status' => 'active',
            ],
        ];

        $createdBuses = [];
        foreach ($busesData as $bData) {
            $createdBuses[$bData['code']] = Vehicle::updateOrCreate(
                ['code' => $bData['code']],
                $bData
            );
        }

        // -------------------------------------------------------------
        // 3. CREATE 5 SCHEDULED TRIPS WITH SPECIFIC TIMES
        // -------------------------------------------------------------
        // Base tomorrow date
        $baseDate = now()->addDay();

        $tripsData = [
            // Bus 1: 06h30 Matin (Express Matinal Douala -> Yaoundé)
            [
                'trip_number' => 'RV-DLA-YAO-0630',
                'branch_id' => $doualaBranch->id,
                'vehicle_id' => $createdBuses['RV-75-003']->id,
                'departure_terminal_id' => $terminalMakepe->id,
                'arrival_terminal_id' => $terminalOlembe->id,
                'transport_mode' => 'road',
                'departure_city' => 'Douala',
                'departure_station' => $terminalMakepe->name,
                'arrival_city' => 'Yaoundé',
                'arrival_station' => $terminalOlembe->name,
                'departure_time' => (clone $baseDate)->setTime(6, 30, 0),
                'arrival_time_estimated' => (clone $baseDate)->setTime(10, 30, 0),
                'base_price' => 6000,
                'seats_available' => 73, // 75 - 2 locked seats (Driver S-01 & Convoyeur S-16)
                'cargo_available_kg' => 6000,
                'cargo_price_per_kg' => 200,
                'status' => 'scheduled',
                'pricing_rules' => [
                    'luggage_free_kg' => 30,
                    'service_level' => 'VIP Express Matinal',
                    'wifi' => 'Starlink Haut Débit',
                    'refreshment' => 'Collation et café inclus',
                ],
                'class' => [
                    'class_code' => 'vip',
                    'class_name' => 'Autocar VIP Tourismo 75 Places',
                    'seat_count' => 75,
                    'seats_available' => 73,
                    'price' => 6000,
                    'amenities' => [
                        'Climatisation intégrale',
                        'Connexion Wi-Fi Starlink gratuite',
                        'Sièges en cuir inclinables ergonomiques',
                        'Ports de recharge USB & Type-C individuels',
                        'Collation matinale & boisson offertes',
                    ],
                ],
            ],

            // Bus 2: 08h00 Matin (Direct Yaoundé -> Douala)
            [
                'trip_number' => 'RV-YAO-DLA-0800',
                'branch_id' => $yaoundeBranch->id,
                'vehicle_id' => $createdBuses['RV-80-003']->id,
                'departure_terminal_id' => $terminalEleveur->id,
                'arrival_terminal_id' => $terminalPK14->id,
                'transport_mode' => 'road',
                'departure_city' => 'Yaoundé',
                'departure_station' => $terminalEleveur->name,
                'arrival_city' => 'Douala',
                'arrival_station' => $terminalPK14->name,
                'departure_time' => (clone $baseDate)->setTime(8, 0, 0),
                'arrival_time_estimated' => (clone $baseDate)->setTime(12, 15, 0),
                'base_price' => 5500,
                'seats_available' => 78,
                'cargo_available_kg' => 7000,
                'cargo_price_per_kg' => 200,
                'status' => 'scheduled',
                'pricing_rules' => [
                    'luggage_free_kg' => 30,
                    'service_level' => 'Prestige Direct',
                ],
                'class' => [
                    'class_code' => 'vip',
                    'class_name' => 'Irizar i6S Prestige 80 Places',
                    'seat_count' => 80,
                    'seats_available' => 78,
                    'price' => 5500,
                    'amenities' => [
                        'Climatisation active',
                        'Écrans vidéo HD au plafond',
                        'Sièges inclinables grand confort',
                        'Prises 220V et ports USB',
                        'Soute grand volume sécurisée',
                    ],
                ],
            ],

            // Bus 3: 13h00 Après-midi (Liaison Ouest-Littoral Bafoussam -> Douala)
            [
                'trip_number' => 'RV-BAF-DLA-1300',
                'branch_id' => $westBranch->id,
                'vehicle_id' => $createdBuses['RV-75-004']->id,
                'departure_terminal_id' => $terminalBafoussam->id,
                'arrival_terminal_id' => $terminalBepanda->id,
                'transport_mode' => 'road',
                'departure_city' => 'Bafoussam',
                'departure_station' => $terminalBafoussam->name,
                'arrival_city' => 'Douala',
                'arrival_station' => $terminalBepanda->name,
                'departure_time' => (clone $baseDate)->setTime(13, 0, 0),
                'arrival_time_estimated' => (clone $baseDate)->setTime(17, 45, 0),
                'base_price' => 4500,
                'seats_available' => 73,
                'cargo_available_kg' => 5500,
                'cargo_price_per_kg' => 180,
                'status' => 'scheduled',
                'pricing_rules' => [
                    'luggage_free_kg' => 25,
                    'service_level' => 'Grand Confort Régional',
                ],
                'class' => [
                    'class_code' => 'standard',
                    'class_name' => 'MAN Lion\'s Coach 75 Places',
                    'seat_count' => 75,
                    'seats_available' => 73,
                    'price' => 4500,
                    'amenities' => [
                        'Climatisation douce',
                        'Suspension pneumatique grand confort',
                        'Sièges inclinables en velours',
                        'Prises USB de secours',
                    ],
                ],
            ],

            // Bus 4: 15h30 Après-midi (Ligne Universitaire Douala -> Dschang)
            [
                'trip_number' => 'RV-DLA-DSC-1530',
                'branch_id' => $doualaBranch->id,
                'vehicle_id' => $createdBuses['RV-80-004']->id,
                'departure_terminal_id' => $terminalMakepe->id,
                'arrival_terminal_id' => $terminalDschang->id,
                'transport_mode' => 'road',
                'departure_city' => 'Douala',
                'departure_station' => $terminalMakepe->name,
                'arrival_city' => 'Dschang',
                'arrival_station' => $terminalDschang->name,
                'departure_time' => (clone $baseDate)->setTime(15, 30, 0),
                'arrival_time_estimated' => (clone $baseDate)->setTime(20, 45, 0),
                'base_price' => 5000,
                'seats_available' => 78,
                'cargo_available_kg' => 7500,
                'cargo_price_per_kg' => 200,
                'status' => 'scheduled',
                'pricing_rules' => [
                    'luggage_free_kg' => 30,
                    'service_level' => 'Royal Class Interurbain',
                ],
                'class' => [
                    'class_code' => 'vip',
                    'class_name' => 'Volvo 9900 Royal Class 80 Places',
                    'seat_count' => 80,
                    'seats_available' => 78,
                    'price' => 5000,
                    'amenities' => [
                        'Climatisation bi-zone ultra silencieuse',
                        'Wi-Fi 4G à bord',
                        'Sièges grand confort inclinables avec repose-pieds',
                        'Ports USB individuels',
                    ],
                ],
            ],

            // Bus 5: 22h00 Nuit (Voyage de Nuit Yaoundé -> Bafoussam)
            [
                'trip_number' => 'RV-YAO-BAF-2200',
                'branch_id' => $yaoundeBranch->id,
                'vehicle_id' => $createdBuses['RV-80-005']->id,
                'departure_terminal_id' => $terminalBiyemAssi->id,
                'arrival_terminal_id' => $terminalBafoussam->id,
                'transport_mode' => 'road',
                'departure_city' => 'Yaoundé',
                'departure_station' => $terminalBiyemAssi->name,
                'arrival_city' => 'Bafoussam',
                'arrival_station' => $terminalBafoussam->name,
                'departure_time' => (clone $baseDate)->setTime(22, 0, 0),
                'arrival_time_estimated' => (clone $baseDate)->addDay()->setTime(3, 30, 0),
                'base_price' => 5000,
                'seats_available' => 78,
                'cargo_available_kg' => 7500,
                'cargo_price_per_kg' => 200,
                'status' => 'scheduled',
                'pricing_rules' => [
                    'luggage_free_kg' => 30,
                    'service_level' => 'Night Star Nocturne',
                ],
                'class' => [
                    'class_code' => 'vip',
                    'class_name' => 'Neoplan Cityliner Nuit 80 Places',
                    'seat_count' => 80,
                    'seats_available' => 78,
                    'price' => 5000,
                    'amenities' => [
                        'Climatisation silencieuse nocturne régulée',
                        'Veilleuses de lecture individuelles',
                        'Rideaux occultants thermiques',
                        'Couvertures hygiéniques fournies',
                        'Surveillance caméra en soute',
                    ],
                ],
            ],
        ];

        foreach ($tripsData as $tData) {
            $classData = $tData['class'];
            unset($tData['class']);

            $trip = Trip::updateOrCreate(
                ['trip_number' => $tData['trip_number']],
                $tData
            );

            TripClass::updateOrCreate(
                ['trip_id' => $trip->id, 'class_code' => $classData['class_code']],
                array_merge($classData, ['trip_id' => $trip->id])
            );
        }

        // -------------------------------------------------------------
        // 4. CREATE 5 REAL PASSENGERS WITH VERIFIED ACCOUNTS & WALLETS
        // -------------------------------------------------------------
        $passengersData = [
            [
                'name' => 'Dr. Estelle Mbarga',
                'email' => 'estelle.mbarga@gmail.com',
                'phone' => '+237 670 12 34 56',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 45000,
                'avatar' => 'https://ui-avatars.com/api/?name=Estelle+Mbarga&background=10B981&color=fff',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Boris Kamdem',
                'email' => 'boris.kamdem@yahoo.com',
                'phone' => '+237 691 23 45 67',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 30000,
                'avatar' => 'https://ui-avatars.com/api/?name=Boris+Kamdem&background=0284C7&color=fff',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Sandrine Fotso',
                'email' => 'sandrine.fotso@outlook.com',
                'phone' => '+237 655 34 56 78',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 18500,
                'avatar' => 'https://ui-avatars.com/api/?name=Sandrine+Fotso&background=EC4899&color=fff',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Patrick Abena',
                'email' => 'patrick.abena@gmail.com',
                'phone' => '+237 678 90 12 34',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 12000,
                'avatar' => 'https://ui-avatars.com/api/?name=Patrick+Abena&background=F59E0B&color=fff',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Nadine Bella',
                'email' => 'nadine.bella@yahoo.fr',
                'phone' => '+237 694 56 78 90',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 25000,
                'avatar' => 'https://ui-avatars.com/api/?name=Nadine+Bella&background=8B5CF6&color=fff',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($passengersData as $pData) {
            User::updateOrCreate(
                ['email' => $pData['email']],
                $pData
            );
        }
    }
}
