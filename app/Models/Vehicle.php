<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'transport_mode',
        'type',
        'capacity_seats',
        'capacity_cargo',
        'seat_layout',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'seat_layout' => 'array',
            'capacity_seats' => 'integer',
            'capacity_cargo' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'vip_bus' => 'Autocar VIP Climatisé (75 Places)',
            'classic_bus' => 'Autocar Classique Grand Confort (80 Places)',
            'coaster' => 'Minibus Coaster Express',
        ];

        return $labels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}
