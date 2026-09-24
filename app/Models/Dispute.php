<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_code',
        'raised_by_user_id',
        'branch_id',
        'booking_id',
        'shipment_id',
        'title',
        'category',
        'description',
        'status',
        'escalated',
        'escalated_at',
        'manager_response',
        'manager_responded_at',
        'admin_notes',
        'admin_resolved_by',
        'admin_resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'escalated' => 'boolean',
            'escalated_at' => 'datetime',
            'manager_responded_at' => 'datetime',
            'admin_resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by_user_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function adminResolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_resolved_by');
    }

    public function shouldAutoEscalate(): bool
    {
        return $this->status === 'open' && !$this->escalated && $this->created_at->diffInHours(now()) >= 48;
    }

    public function getHoursRemainingAttribute(): int
    {
        if ($this->escalated || $this->status !== 'open') {
            return 0;
        }
        $elapsed = $this->created_at->diffInHours(now());
        return max(0, 48 - (int)$elapsed);
    }
}
