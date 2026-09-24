<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Terminal extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'region',
        'city',
        'name',
        'address',
        'email',
        'phone',
        'latitude',
        'longitude',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function managers(): HasMany
    {
        return $this->hasMany(User::class, 'branch_terminal_id');
    }

    public function departureTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'departure_terminal_id');
    }

    public function arrivalTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'arrival_terminal_id');
    }

    public function originShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_terminal_id');
    }

    public function destinationShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_terminal_id');
    }
}
