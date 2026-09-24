<?php

namespace App\Services;

use App\Models\Trip;

class SeatMapService
{
    /**
     * Permanent seat locks for Real Voyage crew:
     * Seat 01: Driver (Chauffeur)
     * Seat 16: Driver's Assistant (Convoyeur - positioned opposite Entrance 1)
     */
    public const LOCKED_SEATS = [
        1 => [
            'code' => 'S-01',
            'role' => 'Driver',
            'label' => 'Chauffeur',
            'icon' => 'fa-id-badge',
        ],
        16 => [
            'code' => 'S-16',
            'role' => "Driver's Assistant",
            'label' => 'Convoyeur',
            'icon' => 'fa-user-shield',
        ],
    ];

    /**
     * Check if a given seat number or seat code is permanently locked.
     */
    public static function isPermanentlyLocked(string|int $seat): bool
    {
        $num = is_numeric($seat) ? (int)$seat : (int)filter_var($seat, FILTER_SANITIZE_NUMBER_INT);
        return array_key_exists($num, self::LOCKED_SEATS);
    }

    /**
     * Get the exact blueprint schema for 75-seater or 80-seater Carrefour buses.
     * Configuration:
     * - Left group: 3 seats (Col 1: Window, Col 2: Middle, Col 3: Aisle)
     * - Central Aisle
     * - Right group: 2 seats (Col 4: Aisle, Col 5: Window) with Curbside Entrances
     * - Row 1: Seat 01 (Col 2), Seats 02 & 03 (Col 4 & 5)
     * - Row 4: Seats 14, 15, 16 on Left; ENTRÉE 1 on Right (Seat 16 = Convoyeur)
     * - Mid Entrée: ENTRÉE 2 on Right (Row 13 for 75s, Row 14 for 80s)
     * - Rear Bench: 6 seats across the back row (Col 1, 2, 3, Aisle/Center, 4, 5)
     */
    public static function getBlueprintRows(int $capacity): array
    {
        if ($capacity >= 78) {
            // 80-Seater Carrefour Layout (17 rows total)
            return [
                1 => ['left' => [null, 1, null], 'right' => [2, 3], 'door' => null],
                2 => ['left' => [4, 5, 6], 'right' => [7, 8], 'door' => null],
                3 => ['left' => [9, 10, 11], 'right' => [12, 13], 'door' => null],
                4 => ['left' => [14, 15, 16], 'right' => [], 'door' => 'ENTREE 1'],
                5 => ['left' => [17, 18, 19], 'right' => [20, 21], 'door' => null],
                6 => ['left' => [22, 23, 24], 'right' => [25, 26], 'door' => null],
                7 => ['left' => [27, 28, 29], 'right' => [30, 31], 'door' => null],
                8 => ['left' => [32, 33, 34], 'right' => [35, 36], 'door' => null],
                9 => ['left' => [37, 38, 39], 'right' => [40, 41], 'door' => null],
                10 => ['left' => [42, 43, 44], 'right' => [45, 46], 'door' => null],
                11 => ['left' => [47, 48, 49], 'right' => [50, 51], 'door' => null],
                12 => ['left' => [52, 53, 54], 'right' => [55, 56], 'door' => null],
                13 => ['left' => [57, 58, 59], 'right' => [60, 61], 'door' => null],
                14 => ['left' => [62, 63, 64], 'right' => [], 'door' => 'ENTREE 2'],
                15 => ['left' => [65, 66, 67], 'right' => [68, 69], 'door' => null],
                16 => ['left' => [70, 71, 72], 'right' => [73, 74], 'door' => null],
                17 => ['left' => [75, 76, 77], 'center' => 78, 'right' => [79, 80], 'door' => null],
            ];
        }

        // 75-Seater Carrefour Layout (16 rows total)
        return [
            1 => ['left' => [null, 1, null], 'right' => [2, 3], 'door' => null],
            2 => ['left' => [4, 5, 6], 'right' => [7, 8], 'door' => null],
            3 => ['left' => [9, 10, 11], 'right' => [12, 13], 'door' => null],
            4 => ['left' => [14, 15, 16], 'right' => [], 'door' => 'ENTREE 1'],
            5 => ['left' => [17, 18, 19], 'right' => [20, 21], 'door' => null],
            6 => ['left' => [22, 23, 24], 'right' => [25, 26], 'door' => null],
            7 => ['left' => [27, 28, 29], 'right' => [30, 31], 'door' => null],
            8 => ['left' => [32, 33, 34], 'right' => [35, 36], 'door' => null],
            9 => ['left' => [37, 38, 39], 'right' => [40, 41], 'door' => null],
            10 => ['left' => [42, 43, 44], 'right' => [45, 46], 'door' => null],
            11 => ['left' => [47, 48, 49], 'right' => [50, 51], 'door' => null],
            12 => ['left' => [52, 53, 54], 'right' => [55, 56], 'door' => null],
            13 => ['left' => [57, 58, 59], 'right' => [], 'door' => 'ENTREE 2'],
            14 => ['left' => [60, 61, 62], 'right' => [63, 64], 'door' => null],
            15 => ['left' => [65, 66, 67], 'right' => [68, 69], 'door' => null],
            16 => ['left' => [70, 71, 72], 'center' => 73, 'right' => [74, 75], 'door' => null],
        ];
    }

    /**
     * Generate 3D seat grid layout for 75-seater or 80-seater bus matching Carrefour blueprints.
     */
    public static function generateSeatMap(Trip $trip): array
    {
        $rawCapacity = $trip->vehicle->capacity_seats ?? 75;
        $totalCapacity = ($rawCapacity >= 78) ? 80 : 75;

        // Get all already booked seats for this trip
        $bookedSeats = $trip->bookings()
            ->whereIn('status', ['confirmed', 'pending', 'checked_in'])
            ->get()
            ->pluck('seat_numbers')
            ->flatten()
            ->filter()
            ->map(fn($s) => is_numeric($s) ? (int)$s : (int)filter_var($s, FILTER_SANITIZE_NUMBER_INT))
            ->toArray();

        $blueprintRows = self::getBlueprintRows($totalCapacity);
        $totalRows = count($blueprintRows);

        $seatList = [];
        $rows = [];

        foreach ($blueprintRows as $rowNum => $spec) {
            $zone = $rowNum <= 4 ? 'front' : ($rowNum <= ($totalCapacity === 80 ? 13 : 12) ? 'mid' : 'rear');
            $rowSeatsAll = [];
            $leftSeatsData = [];
            $rightSeatsData = [];
            $centerSeatData = null;

            // Process Left Seats (Columns 1, 2, 3)
            foreach ($spec['left'] as $colIdx => $seatNum) {
                if ($seatNum === null) {
                    $leftSeatsData[] = null;
                    continue;
                }

                $column = $colIdx + 1; // 1, 2, 3
                $seatData = self::buildSeatData(
                    $seatNum,
                    $rowNum,
                    $column,
                    'left',
                    $zone,
                    $trip->base_price,
                    $bookedSeats
                );

                $seatList[$seatNum] = $seatData;
                $rowSeatsAll[] = $seatData;
                $leftSeatsData[] = $seatData;
            }

            // Process Center Seat (Rear bench aisle position: 78 or 73)
            if (!empty($spec['center'])) {
                $centerNum = $spec['center'];
                $centerSeatData = self::buildSeatData(
                    $centerNum,
                    $rowNum,
                    3.5,
                    'center',
                    $zone,
                    $trip->base_price,
                    $bookedSeats,
                    true
                );

                $seatList[$centerNum] = $centerSeatData;
                $rowSeatsAll[] = $centerSeatData;
            }

            // Process Right Seats (Columns 4, 5)
            foreach ($spec['right'] as $colIdx => $seatNum) {
                if ($seatNum === null) {
                    $rightSeatsData[] = null;
                    continue;
                }

                $column = $colIdx + 4; // 4, 5
                $seatData = self::buildSeatData(
                    $seatNum,
                    $rowNum,
                    $column,
                    'right',
                    $zone,
                    $trip->base_price,
                    $bookedSeats
                );

                $seatList[$seatNum] = $seatData;
                $rowSeatsAll[] = $seatData;
                $rightSeatsData[] = $seatData;
            }

            $rows[$rowNum] = [
                'row_number' => $rowNum,
                'zone' => $zone,
                'left' => $leftSeatsData,
                'right' => $rightSeatsData,
                'center' => $centerSeatData,
                'door' => $spec['door'] ?? null,
                'seats' => $rowSeatsAll,
            ];
        }

        // Sort $seatList by seat number ascending
        ksort($seatList);

        return [
            'total_seats' => $totalCapacity,
            'bus_type' => $totalCapacity . '-Seater Luxury Coach (Carrefour)',
            'total_rows' => $totalRows,
            'available_count' => count(array_filter($seatList, fn($s) => $s['status'] === 'available')),
            'booked_count' => count(array_filter($seatList, fn($s) => $s['status'] === 'booked')),
            'locked_count' => count(array_filter($seatList, fn($s) => $s['status'] === 'locked')),
            'seats' => $seatList,
            'rows' => $rows,
            'crew_locks' => self::LOCKED_SEATS,
        ];
    }

    /**
     * Build unified seat data array with ergonomic attributes.
     */
    protected static function buildSeatData(
        int $number,
        int $rowNum,
        float|int $column,
        string $side,
        string $zone,
        float $price,
        array $bookedSeats,
        bool $isRearCenter = false
    ): array {
        $isLocked = array_key_exists($number, self::LOCKED_SEATS);
        $isBooked = in_array($number, $bookedSeats, true);

        $status = 'available';
        $lockInfo = null;

        if ($isLocked) {
            $status = 'locked';
            $lockInfo = self::LOCKED_SEATS[$number];
        } elseif ($isBooked) {
            $status = 'booked';
        }

        $isWindow = in_array($column, [1, 5], true);
        $isAisle = in_array($column, [3, 4], true) || $isRearCenter;

        return [
            'number' => $number,
            'code' => 'S-' . str_pad($number, 2, '0', STR_PAD_LEFT),
            'status' => $status,
            'price' => $price,
            'lock_info' => $lockInfo,
            'row' => $rowNum,
            'column' => $column,
            'side' => $side,
            'is_window' => $isWindow,
            'is_aisle' => $isAisle,
            'is_rear_center' => $isRearCenter,
            'zone' => $zone,
            'has_usb' => true,
            'has_ac_vent' => true,
            'is_reclining' => true,
        ];
    }
}
