<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_reference',
        'user_id',
        'payable_type',
        'payable_id',
        'payment_type',
        'amount',
        'currency',
        'method',
        'payer_phone',
        'transaction_ref',
        'status',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    public function isOrangeMoney(): bool
    {
        return $this->method === 'orange_money';
    }

    public function isMtnMomo(): bool
    {
        return $this->method === 'mtn_momo';
    }

    public function isWallet(): bool
    {
        return $this->method === 'wallet';
    }
}
