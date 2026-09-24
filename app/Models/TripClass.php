<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'class_code',
        'class_name',
        'seat_count',
        'seats_available',
        'price',
        'amenities',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'seat_count' => 'integer',
            'seats_available' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }
}
