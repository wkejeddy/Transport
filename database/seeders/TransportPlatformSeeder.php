<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use App\Models\Terminal;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\TripClass;
use App\Models\Booking;
use App\Models\Shipment;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\Dispute;
use App\Models\TripAlert;
use App\Services\FreightPricingService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransportPlatformSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User (Direction Générale Real Voyage - Wkej Eddy)
        $admin = User::updateOrCreate(
            ['role' => 'admin'],
            [
                'name' => 'Wkej Eddy',
                'email' => 'wkejeddy@gmail.com',
                'phone' => '+237 670 000 001',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('Ab12345678.'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Regional Branches (Douala, Yaoundé, West)
        $doualaBranch = Branch::firstOrCreate(
            ['code' => 'DLA'],
            [
                'name' => 'Direction Régionale Douala & Littoral',
                'region' => 'Littoral',
                'city' => 'Douala',
                'address' => 'Boulevard Maképé Missokè, Face Tradex',
                'phone' => '+237 233 42 77 10',
                'email' => 'douala@realvoyage.cm',
                'manager_name' => 'Chef de Gare Douala Maképé',
                'is_active' => true,
            ]
        );

        $yaoundeBranch = Branch::firstOrCreate(
            ['code' => 'YDE'],
            [
                'name' => 'Direction Régionale Yaoundé & Centre',
                'region' => 'Centre',
                'city' => 'Yaoundé',
                'address' => 'Quartier Éleveur, Axe Yaoundé-Soa',
                'phone' => '+237 222 20 55 40',
                'email' => 'yaounde@realvoyage.cm',
                'manager_name' => 'Chef de Gare Yaoundé Éleveur',
                'is_active' => true,
            ]
        );

        $westBranch = Branch::firstOrCreate(
            ['code' => 'WST'],
            [
                'name' => 'Direction Régionale Ouest',
                'region' => 'Ouest',
                'city' => 'Bafoussam',
                'address' => 'Carrefour Total, Entrée Marché A',
                'phone' => '+237 233 44 88 25',
                'email' => 'ouest@realvoyage.cm',
                'manager_name' => 'Chef de Gare Bafoussam',
                'is_active' => true,
            ]
        );

        // 3. Official Terminals across 3 Regions
        $terminalsData = [
            // Ouest
            [
                'name' => 'Terminal Dschang Centre',
                'region' => 'Ouest',
                'city' => 'Dschang',
                'address' => 'Avenue du Foréké, Face Entrée Principale Université',
                'phone' => '+237 233 45 19 80',
                'email' => 'dschang@realvoyage.cm',
                'latitude' => 5.4434,
                'longitude' => 10.0533,
                'branch_id' => $westBranch->id,
            ],
            [
                'name' => 'Terminal Mbouda Carrefour',
                'region' => 'Ouest',
                'city' => 'Mbouda',
                'address' => 'Grand Rond-point Central Bamboutos',
                'phone' => '+237 233 45 28 14',
                'email' => 'mbouda@realvoyage.cm',
                'latitude' => 5.6267,
                'longitude' => 10.2558,
                'branch_id' => $westBranch->id,
            ],
            [
                'name' => 'Terminal Central Bafoussam',
                'region' => 'Ouest',
                'city' => 'Bafoussam',
                'address' => 'Carrefour Total, Entrée Marché A',
                'phone' => '+237 233 44 31 09',
                'email' => 'bafoussam@realvoyage.cm',
                'latitude' => 5.4777,
                'longitude' => 10.4176,
                'branch_id' => $westBranch->id,
            ],
            // Douala
            [
                'name' => 'Terminal Douala PK14',
                'region' => 'Douala',
                'city' => 'Douala',
                'address' => 'Carrefour PK14, Axe Lourd Douala-Edéa/Yaoundé',
                'phone' => '+237 233 40 85 12',
                'email' => 'pk14@realvoyage.cm',
                'latitude' => 4.0921,
                'longitude' => 9.7892,
                'branch_id' => $doualaBranch->id,
            ],
            [
                'name' => 'Terminal Douala Maképé',
                'region' => 'Douala',
                'city' => 'Douala',
                'address' => 'Boulevard Maképé Missokè, Face Tradex',
                'phone' => '+237 233 40 92 34',
                'email' => 'makepe@realvoyage.cm',
                'latitude' => 4.0725,
                'longitude' => 9.7428,
                'branch_id' => $doualaBranch->id,
            ],
            [
                'name' => 'Terminal Douala Bépanda',
                'region' => 'Douala',
                'city' => 'Douala',
                'address' => 'Carrefour Casmando, Bépanda Omnisports',
                'phone' => '+237 233 40 18 66',
                'email' => 'bepanda@realvoyage.cm',
                'latitude' => 4.0610,
                'longitude' => 9.7214,
                'branch_id' => $doualaBranch->id,
            ],
            // Yaoundé
            [
                'name' => 'Terminal Yaoundé Éleveur',
                'region' => 'Yaoundé',
                'city' => 'Yaoundé',
                'address' => 'Quartier Éleveur, Axe Yaoundé-Soa',
                'phone' => '+237 222 21 73 05',
                'email' => 'eleveur@realvoyage.cm',
                'latitude' => 3.9012,
                'longitude' => 11.5387,
                'branch_id' => $yaoundeBranch->id,
            ],
            [
                'name' => 'Terminal Yaoundé Olembé',
                'region' => 'Yaoundé',
                'city' => 'Yaoundé',
                'address' => 'Stade Paul Biya Olembé, Entrée Sud',
                'phone' => '+237 222 21 84 90',
                'email' => 'olembe@realvoyage.cm',
                'latitude' => 3.9450,
                'longitude' => 11.5210,
                'branch_id' => $yaoundeBranch->id,
            ],
            [
                'name' => 'Terminal Yaoundé Terminus Mimboman',
                'region' => 'Yaoundé',
                'city' => 'Yaoundé',
                'address' => 'Terminus Mimboman, Face Collège Montesquieu',
                'phone' => '+237 222 21 99 31',
                'email' => 'mimboman@realvoyage.cm',
                'latitude' => 3.8682,
                'longitude' => 11.5543,
                'branch_id' => $yaoundeBranch->id,
            ],
            [
                'name' => 'Terminal Yaoundé Simbock',
                'region' => 'Yaoundé',
                'city' => 'Yaoundé',
                'address' => 'Carrefour Simbock, Route de Kribi/Mbankolo',
                'phone' => '+237 222 21 62 48',
                'email' => 'simbock@realvoyage.cm',
                'latitude' => 3.8210,
                'longitude' => 11.4720,
                'branch_id' => $yaoundeBranch->id,
            ],
            [
                'name' => 'Terminal Yaoundé Biyem-Assi',
                'region' => 'Yaoundé',
                'city' => 'Yaoundé',
                'address' => 'Rond-Point Express Biyem-Assi',
                'phone' => '+237 222 21 50 17',
                'email' => 'biyemassi@realvoyage.cm',
                'latitude' => 3.8375,
                'longitude' => 11.4925,
                'branch_id' => $yaoundeBranch->id,
            ],
        ];

        $terminals = [];
        foreach ($terminalsData as $tData) {
            $terminals[$tData['name']] = Terminal::create(array_merge($tData, [
                'status' => 'active',
                'is_active' => true,
            ]));
        }

        // 4. Managers assigned to Terminals & Branches
        $makepeTerminal = $terminals['Terminal Douala Maképé'];
        $manager = User::firstOrCreate(
            ['email' => 'manager@realvoyage.cm'],
            [
                'name' => 'Chef de Gare Douala Maképé',
                'phone' => '+237 694 28 15 90',
                'role' => 'manager',
                'status' => 'active',
                'branch_id' => $doualaBranch->id,
                'branch_terminal_id' => $makepeTerminal->id,
                'password' => Hash::make('password123'),
            ]
        );

        $eleveurTerminal = $terminals['Terminal Yaoundé Éleveur'];
        User::firstOrCreate(
            ['email' => 'manager.yaounde@realvoyage.cm'],
            [
                'name' => 'Chef de Gare Yaoundé Éleveur',
                'phone' => '+237 677 34 81 22',
                'role' => 'manager',
                'status' => 'active',
                'branch_id' => $yaoundeBranch->id,
                'branch_terminal_id' => $eleveurTerminal->id,
                'password' => Hash::make('password123'),
            ]
        );

        $bafoussamTerminal = $terminals['Terminal Central Bafoussam'];
        User::firstOrCreate(
            ['email' => 'manager.ouest@realvoyage.cm'],
            [
                'name' => 'Chef de Gare Bafoussam',
                'phone' => '+237 698 52 14 73',
                'role' => 'manager',
                'status' => 'active',
                'branch_id' => $westBranch->id,
                'branch_terminal_id' => $bafoussamTerminal->id,
                'password' => Hash::make('password123'),
            ]
        );

        // 5. Passenger Users with initial E-Wallet balance
        $passager1 = User::firstOrCreate(
            ['email' => 'passenger@transport.cm'],
            [
                'name' => 'Paul Ewane',
                'phone' => '+237 699 112 233',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 25000,
                'password' => Hash::make('password123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'passenger@realvoyage.cm'],
            [
                'name' => 'Voyageur Real Voyage',
                'phone' => '+237 699 112 000',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 20000,
                'password' => Hash::make('password123'),
            ]
        );

        $passager2 = User::firstOrCreate(
            ['email' => 'jeanne.ndongo@gmail.com'],
            [
                'name' => 'Jeanne Ndongo',
                'phone' => '+237 677 445 566',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 15000,
                'password' => Hash::make('password123'),
            ]
        );

        $passager3 = User::firstOrCreate(
            ['email' => 'alain.biya@yahoo.fr'],
            [
                'name' => 'Alain Biya',
                'phone' => '+237 690 998 877',
                'role' => 'passager',
                'status' => 'active',
                'wallet_balance' => 0,
                'password' => Hash::make('password123'),
            ]
        );

        // 6. Fleet Configuration: 75-Seater and 80-Seater Coaches
        $bus75_1 = Vehicle::firstOrCreate(
            ['code' => 'RV-75-001'],
            [
                'branch_id' => $doualaBranch->id,
                'name' => 'Scania Marcopolo G8 (75 Places)',
                'transport_mode' => 'road',
                'type' => 'classic_bus',
                'capacity_seats' => 75,
                'capacity_cargo' => 6000,
                'status' => 'active',
            ]
        );

        $bus75_2 = Vehicle::firstOrCreate(
            ['code' => 'RV-75-002'],
            [
                'branch_id' => $westBranch->id,
                'name' => 'Mercedes-Benz Travego Grand Confort (75 Places)',
                'transport_mode' => 'road',
                'type' => 'classic_bus',
                'capacity_seats' => 75,
                'capacity_cargo' => 6000,
                'status' => 'active',
            ]
        );

        $bus80_1 = Vehicle::firstOrCreate(
            ['code' => 'RV-80-001'],
            [
                'branch_id' => $doualaBranch->id,
                'name' => 'Volvo 9700 Double Essieu (80 Places)',
                'transport_mode' => 'road',
                'type' => 'vip_bus',
                'capacity_seats' => 80,
                'capacity_cargo' => 7500,
                'status' => 'active',
            ]
        );

        $bus80_2 = Vehicle::firstOrCreate(
            ['code' => 'RV-80-002'],
            [
                'branch_id' => $yaoundeBranch->id,
                'name' => 'Yutong Euro-6 Longue Distance (80 Places)',
                'transport_mode' => 'road',
                'type' => 'vip_bus',
                'capacity_seats' => 80,
                'capacity_cargo' => 7500,
                'status' => 'active',
            ]
        );

        // 7. Scheduled Trips with STRICT fixed departures: 10h00 and 21h30
        // Reminder: Seats 01 (Driver) & 16 (Convoyeur) are permanently locked out.
        $departureDate = now()->addDay()->setTime(10, 0, 0);
        $trip1 = Trip::firstOrCreate(
            ['trip_number' => 'RV-DLA-YAO-1000'],
            [
                'branch_id' => $doualaBranch->id,
                'vehicle_id' => $bus80_1->id,
                'departure_terminal_id' => $makepeTerminal->id,
                'arrival_terminal_id' => $eleveurTerminal->id,
                'transport_mode' => 'road',
                'departure_city' => 'Douala',
                'departure_station' => $makepeTerminal->name,
                'arrival_city' => 'Yaoundé',
                'arrival_station' => $eleveurTerminal->name,
                'departure_time' => $departureDate,
                'arrival_time_estimated' => (clone $departureDate)->addHours(4),
                'base_price' => 5000,
                'seats_available' => 76,
                'cargo_available_kg' => 7000,
                'cargo_price_per_kg' => 200,
                'status' => 'scheduled',
                'pricing_rules' => ['luggage_free_kg' => 30, 'service_level' => 'Grand Confort Climatise'],
            ]
        );

        TripClass::firstOrCreate(
            ['trip_id' => $trip1->id, 'class_code' => 'standard'],
            [
                'class_name' => 'Autocar Grand Confort 80 Places',
                'seat_count' => 80,
                'seats_available' => 76,
                'price' => 5000,
                'amenities' => ['Climatisation intégrale', 'Prises de recharge USB', 'Vidéo bordure', 'Sièges inclinables'],
            ]
        );

        // Trip 2: Yaoundé (Éleveur) -> Douala (Maképé) - Evening (21h00 tomorrow)
        $departureDateNight = now()->addDay()->setTime(21, 0, 0);
        $trip2 = Trip::firstOrCreate(
            ['trip_number' => 'RV-YAO-DLA-2100'],
            [
                'branch_id' => $yaoundeBranch->id,
                'vehicle_id' => $bus80_2->id,
                'departure_terminal_id' => $eleveurTerminal->id,
                'arrival_terminal_id' => $makepeTerminal->id,
                'transport_mode' => 'road',
                'departure_city' => 'Yaoundé',
                'departure_station' => $eleveurTerminal->name,
                'arrival_city' => 'Douala',
                'arrival_station' => $makepeTerminal->name,
                'departure_time' => $departureDateNight,
                'arrival_time_estimated' => (clone $departureDateNight)->addHours(4)->addMinutes(30),
                'base_price' => 5000,
                'seats_available' => 77,
                'cargo_available_kg' => 7000,
                'cargo_price_per_kg' => 200,
                'status' => 'scheduled',
            ]
        );

        TripClass::firstOrCreate(
            ['trip_id' => $trip2->id, 'class_code' => 'standard'],
            [
                'class_name' => 'Autocar Nuit Grand Confort 80 Places',
                'seat_count' => 80,
                'seats_available' => 77,
                'price' => 5000,
                'amenities' => ['Climatisation douce de nuit', 'Veilleuses individuelles', 'Soute volumineuse sécurisée'],
            ]
        );

        // Trip 3: Douala (PK14) -> Bafoussam (Central) - Morning (10h00 in 2 days)
        $departureDateWest = now()->addDays(2)->setTime(10, 0, 0);
        $trip3 = Trip::firstOrCreate(
            ['trip_number' => 'RV-DLA-BAF-1000'],
            [
                'branch_id' => $doualaBranch->id,
                'vehicle_id' => $bus75_1->id,
                'departure_terminal_id' => $terminals['Terminal Douala PK14']->id,
                'arrival_terminal_id' => $bafoussamTerminal->id,
                'transport_mode' => 'road',
                'departure_city' => 'Douala',
                'departure_station' => $terminals['Terminal Douala PK14']->name,
                'arrival_city' => 'Bafoussam',
                'arrival_station' => $bafoussamTerminal->name,
                'departure_time' => $departureDateWest,
                'arrival_time_estimated' => (clone $departureDateWest)->addHours(5),
                'base_price' => 4500,
                'seats_available' => 73,
                'cargo_available_kg' => 6000,
                'cargo_price_per_kg' => 200,
                'status' => 'scheduled',
            ]
        );

        TripClass::firstOrCreate(
            ['trip_id' => $trip3->id, 'class_code' => 'standard'],
            [
                'class_name' => 'Ligne Ouest 75 Places',
                'seat_count' => 75,
                'seats_available' => 73,
                'price' => 4500,
                'amenities' => ['Climatisation', 'Vidéo bordure', 'Coffre sécurisé'],
            ]
        );

        // Trip 4: Dschang -> Yaoundé (Olembé) - Night (21h00 in 2 days)
        $departureDateDschang = now()->addDays(2)->setTime(21, 0, 0);
        $trip4 = Trip::firstOrCreate(
            ['trip_number' => 'RV-DSCH-YAO-2100'],
            [
                'branch_id' => $westBranch->id,
                'vehicle_id' => $bus75_2->id,
                'departure_terminal_id' => $terminals['Terminal Dschang Centre']->id,
                'arrival_terminal_id' => $terminals['Terminal Yaoundé Olembé']->id,
                'transport_mode' => 'road',
                'departure_city' => 'Dschang',
                'departure_station' => $terminals['Terminal Dschang Centre']->name,
                'arrival_city' => 'Yaoundé',
                'arrival_station' => $terminals['Terminal Yaoundé Olembé']->name,
                'departure_time' => $departureDateDschang,
                'arrival_time_estimated' => (clone $departureDateDschang)->addHours(6),
                'base_price' => 6000,
                'seats_available' => 73,
                'cargo_available_kg' => 6000,
                'cargo_price_per_kg' => 250,
                'status' => 'scheduled',
            ]
        );

        TripClass::firstOrCreate(
            ['trip_id' => $trip4->id, 'class_code' => 'standard'],
            [
                'class_name' => 'Ligne Interurbaine Dschang - Yaoundé',
                'seat_count' => 75,
                'seats_available' => 73,
                'price' => 6000,
                'amenities' => ['Climatisation', 'Suspension pneumatique grand confort'],
            ]
        );

        // 8. Sample Bookings (Valid seats only - strictly non-locked)
        $booking1 = Booking::firstOrCreate(
            ['booking_reference' => 'BK-RV-2026-8921'],
            [
                'passenger_id' => $passager1->id,
                'trip_id' => $trip1->id,
                'trip_class_id' => $trip1->classes()->first()->id,
                'transport_class' => 'standard',
                'seats_count' => 2,
                'seat_numbers' => ['31', '32'],
                'passengers_data' => [
                    ['name' => 'Paul Ewane', 'cni' => '119823481', 'phone' => '+237 699 112 233'],
                    ['name' => 'Marie Ewane', 'cni' => '119823482', 'phone' => '+237 699 112 234'],
                ],
                'total_amount' => 10000,
                'status' => 'confirmed',
                'qr_code_token' => 'QR-RV-' . strtoupper(Str::random(12)),
                'expires_at' => now()->addMinutes(30),
            ]
        );

        Payment::firstOrCreate(
            ['payment_reference' => 'PAY-RV-MOMO-8921'],
            [
                'user_id' => $passager1->id,
                'payable_type' => Booking::class,
                'payable_id' => $booking1->id,
                'amount' => 10000,
                'currency' => 'XAF',
                'method' => 'mtn_momo',
                'payer_phone' => '+237 699 112 233',
                'transaction_ref' => 'MOMO-RV-994821',
                'status' => 'successful',
                'gateway_response' => ['status' => 'SUCCESS', 'receipt' => 'RV-MOMO-8921'],
            ]
        );

        $booking2 = Booking::firstOrCreate(
            ['booking_reference' => 'BK-RV-2026-4410'],
            [
                'passenger_id' => $passager2->id,
                'trip_id' => $trip2->id,
                'trip_class_id' => $trip2->classes()->first()->id,
                'transport_class' => 'standard',
                'seats_count' => 1,
                'seat_numbers' => ['12'],
                'passengers_data' => [
                    ['name' => 'Jeanne Ndongo', 'cni' => '102938475', 'phone' => '+237 677 445 566'],
                ],
                'total_amount' => 5000,
                'status' => 'confirmed',
                'qr_code_token' => 'QR-RV-' . strtoupper(Str::random(12)),
                'expires_at' => now()->addMinutes(30),
            ]
        );

        Payment::firstOrCreate(
            ['payment_reference' => 'PAY-RV-OM-4410'],
            [
                'user_id' => $passager2->id,
                'payable_type' => Booking::class,
                'payable_id' => $booking2->id,
                'amount' => 5000,
                'currency' => 'XAF',
                'method' => 'orange_money',
                'payer_phone' => '+237 677 445 566',
                'transaction_ref' => 'OM-RV-102944',
                'status' => 'successful',
                'gateway_response' => ['status' => 'SUCCESS', 'receipt' => 'RV-OM-4410'],
            ]
        );

        // 9. Freight Management: Strict 10% Declared Value Calculation
        $declaredValue1 = 200000;
        $freightFee1 = FreightPricingService::calculateFeeAmount($declaredValue1);

        $shipment1 = Shipment::firstOrCreate(
            ['tracking_code' => 'RV-FR-889102'],
            [
                'sender_id' => $passager1->id,
                'branch_id' => $doualaBranch->id,
                'trip_id' => $trip1->id,
                'origin_terminal_id' => $makepeTerminal->id,
                'destination_terminal_id' => $eleveurTerminal->id,
                'recipient_name' => 'Samuel Nguema',
                'recipient_phone' => '+237 677 889 900',
                'recipient_city' => 'Yaoundé',
                'destination_station' => $eleveurTerminal->name,
                'item_category' => 'electronics',
                'item_description' => 'Carton de matériel informatique et accessoires',
                'weight_kg' => 12.0,
                'declared_value' => $declaredValue1,
                'insured' => true,
                'insurance_fee' => 0,
                'cargo_fee' => $freightFee1,
                'total_amount' => $freightFee1,
                'status' => 'in_transit',
                'proof_of_delivery_code' => '4829',
            ]
        );

        Payment::firstOrCreate(
            ['payment_reference' => 'PAY-RV-WALLET-8891'],
            [
                'user_id' => $passager1->id,
                'payable_type' => Shipment::class,
                'payable_id' => $shipment1->id,
                'amount' => $freightFee1,
                'currency' => 'XAF',
                'method' => 'wallet',
                'payer_phone' => '+237 699 112 233',
                'transaction_ref' => 'WALLET-RV-8891',
                'status' => 'successful',
            ]
        );

        $declaredValue2 = 65000;
        $freightFee2 = FreightPricingService::calculateFeeAmount($declaredValue2);

        $shipment2 = Shipment::firstOrCreate(
            ['tracking_code' => 'RV-FR-551203'],
            [
                'sender_id' => $passager2->id,
                'branch_id' => $doualaBranch->id,
                'trip_id' => $trip3->id,
                'origin_terminal_id' => $terminals['Terminal Douala PK14']->id,
                'destination_terminal_id' => $bafoussamTerminal->id,
                'recipient_name' => 'Brigitte Fotso',
                'recipient_phone' => '+237 699 334 455',
                'recipient_city' => 'Bafoussam',
                'destination_station' => $bafoussamTerminal->name,
                'item_category' => 'documents',
                'item_description' => 'Dossier administratif et pièces justificatives d\'entreprise',
                'weight_kg' => 2.5,
                'declared_value' => $declaredValue2,
                'insured' => true,
                'insurance_fee' => 0,
                'cargo_fee' => $freightFee2,
                'total_amount' => $freightFee2,
                'status' => 'arrived',
                'proof_of_delivery_code' => '1934',
            ]
        );

        Payment::firstOrCreate(
            ['payment_reference' => 'PAY-RV-OM-5512'],
            [
                'user_id' => $passager2->id,
                'payable_type' => Shipment::class,
                'payable_id' => $shipment2->id,
                'amount' => $freightFee2,
                'currency' => 'XAF',
                'method' => 'orange_money',
                'payer_phone' => '+237 677 445 566',
                'transaction_ref' => 'OM-RV-331203',
                'status' => 'successful',
            ]
        );

        // 10. Customer Ratings & Corporate Feedback
        Rating::firstOrCreate(
            ['user_id' => $passager1->id, 'trip_id' => $trip1->id],
            [
                'branch_id' => $doualaBranch->id,
                'score' => 5,
                'punctuality_score' => 5,
                'comfort_score' => 5,
                'customer_service_score' => 5,
                'comment' => 'Départ ponctuel à 10h00 précises depuis Maképé. Autocar moderne, propre et sécurisé. Service fret impeccable.',
            ]
        );

        Rating::firstOrCreate(
            ['user_id' => $passager2->id, 'trip_id' => $trip2->id],
            [
                'branch_id' => $yaoundeBranch->id,
                'score' => 5,
                'punctuality_score' => 5,
                'comfort_score' => 5,
                'customer_service_score' => 5,
                'comment' => 'Voyage de nuit très calme et climatisé. Embarquement rapide avec vérification QR code au terminal.',
            ]
        );

        // 11. Support Ticket / Tracking Note
        Dispute::firstOrCreate(
            ['dispute_code' => 'DSP-RV-2026-001'],
            [
                'raised_by_user_id' => $passager3->id,
                'branch_id' => $yaoundeBranch->id,
                'booking_id' => null,
                'shipment_id' => $shipment1->id,
                'category' => 'delay',
                'title' => 'Demande d\'information sur horaire de retrait colis à Éleveur',
                'description' => 'Bonjour, pouvez-vous me confirmer l\'heure d\'ouverture du guichet colis au terminal d\'Éleveur pour récupérer mon colis ce matin ?',
                'status' => 'resolved',
                'escalated' => false,
            ]
        );
    }
}
