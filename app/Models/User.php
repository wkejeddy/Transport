<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'phone',
        'role',
        'avatar',
        'status',
        'wallet_balance',
        'branch_id',
        'branch_terminal_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
        ];
    }

    // Role helper methods
    public function isVisiteur(): bool
    {
        return $this->role === 'visiteur';
    }

    public function isPassager(): bool
    {
        return $this->role === 'passager';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'passenger_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'sender_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'raised_by_user_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(TripAlert::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'branch_terminal_id');
    }

    public function creditWallet(float $amount): void
    {
        $this->increment('wallet_balance', $amount);
    }

    public function debitWallet(float $amount): bool
    {
        if ($this->wallet_balance < $amount) {
            return false;
        }

        $this->decrement('wallet_balance', $amount);
        return true;
    }
}
