<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'trip_id',
        'score',
        'punctuality_score',
        'comfort_score',
        'customer_service_score',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'punctuality_score' => 'integer',
            'comfort_score' => 'integer',
            'customer_service_score' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
