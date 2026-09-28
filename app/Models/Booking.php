<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'passenger_id',
        'trip_id',
        'return_trip_id',
        'trip_class_id',
        'transport_class',
        'booking_type',
        'is_round_trip',
        'seats_count',
        'seat_numbers',
        'passengers_data',
        'total_amount',
        'round_trip_discount',
        'reservation_fee',
        'reservation_fee_paid',
        'status',
        'qr_code_token',
        'expires_at',
        'reminder_sent_at',
        'checked_in_at',
        'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'seat_numbers' => 'array',
            'passengers_data' => 'array',
            'is_round_trip' => 'boolean',
            'total_amount' => 'decimal:2',
            'round_trip_discount' => 'decimal:2',
            'reservation_fee' => 'decimal:2',
            'reservation_fee_paid' => 'boolean',
            'expires_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function returnTrip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'return_trip_id');
    }

    public function tripClass(): BelongsTo
    {
        return $this->belongsTo(TripClass::class, 'trip_class_id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payable_id')->where('payable_type', self::class);
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable')->latestOfMany();
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function isAdvanceReservation(): bool
    {
        return $this->booking_type === 'advance_reservation';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCheckedIn(): bool
    {
        return $this->status === 'checked_in';
    }

    public function isExpired(): bool
    {
        if ($this->isPending()) {
            return $this->expires_at && $this->expires_at->isPast();
        }

        if ($this->isReserved()) {
            // Expired if within 6 hours of departure
            return $this->trip && $this->trip->departure_time->subHours(6)->isPast();
        }

        return false;
    }

    /**
     * Get deadline to finalize ticket payment for advance reservation (6h before departure)
     */
    public function getReservationDeadlineAttribute()
    {
        return $this->trip ? $this->trip->departure_time->copy()->subHours(6) : null;
    }
}
