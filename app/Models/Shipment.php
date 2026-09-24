<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code',
        'sender_id',
        'branch_id',
        'trip_id',
        'recipient_name',
        'recipient_phone',
        'recipient_city',
        'destination_station',
        'origin_terminal_id',
        'destination_terminal_id',
        'item_category',
        'item_description',
        'weight_kg',
        'declared_value',
        'insured',
        'insurance_fee',
        'cargo_fee',
        'total_amount',
        'status',
        'proof_of_delivery_code',
        'proof_of_delivery_notes',
        'collected_at',
        'collected_by_name',
        'collected_by_cni',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'declared_value' => 'decimal:2',
            'insured' => 'boolean',
            'insurance_fee' => 'decimal:2',
            'cargo_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'collected_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function originTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origin_terminal_id');
    }

    public function destinationTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'destination_terminal_id');
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }
}
