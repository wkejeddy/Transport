<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory;

    public const PERMANENTLY_LOCKED_SEATS = [1, 16];
    public const PERMANENTLY_LOCKED_SEAT_CODES = ['S-01', 'S-16', '01', '16', '1'];

    protected $fillable = [
        'branch_id',
        'vehicle_id',
        'trip_number',
        'transport_mode',
        'departure_city',
        'departure_station',
        'departure_terminal_id',
        'arrival_city',
        'arrival_station',
        'arrival_terminal_id',
        'departure_time',
        'arrival_time_estimated',
        'delayed_departure_time',
        'base_price',
        'seats_available',
        'cargo_available_kg',
        'cargo_price_per_kg',
        'status',
        'delay_reason',
        'pricing_rules',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'arrival_time_estimated' => 'datetime',
            'delayed_departure_time' => 'datetime',
            'base_price' => 'decimal:2',
            'cargo_price_per_kg' => 'decimal:2',
            'seats_available' => 'integer',
            'cargo_available_kg' => 'integer',
            'pricing_rules' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(TripClass::class, 'trip_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'trip_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'trip_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(TripAlert::class, 'trip_id');
    }

    public function isRoad(): bool
    {
        return true;
    }

    public function isRail(): bool
    {
        return false;
    }

    public function isDelayed(): bool
    {
        return $this->status === 'delayed';
    }

    public function departureTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'departure_terminal_id');
    }

    public function arrivalTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'arrival_terminal_id');
    }

    public function getEffectiveDepartureTimeAttribute(): Carbon
    {
        return $this->delayed_departure_time ?? $this->departure_time;
    }

    public static function isSeatLocked(string|int $seatNumber): bool
    {
        $normalized = is_numeric($seatNumber) ? (int)$seatNumber : (int)filter_var($seatNumber, FILTER_SANITIZE_NUMBER_INT);
        return in_array($normalized, self::PERMANENTLY_LOCKED_SEATS, true) || in_array((string)$seatNumber, self::PERMANENTLY_LOCKED_SEAT_CODES, true);
    }

    public function getOccupiedSeatNumbers(): array
    {
        return $this->bookings()
            ->whereIn('status', ['confirmed', 'pending', 'reserved', 'checked_in'])
            ->get()
            ->pluck('seat_numbers')
            ->flatten()
            ->filter()
            ->map(function ($s) {
                $num = is_numeric($s) ? (int)$s : (int)filter_var($s, FILTER_SANITIZE_NUMBER_INT);
                return str_pad($num, 2, '0', STR_PAD_LEFT);
            })
            ->toArray();
    }
}
