<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'region',
        'city',
        'address',
        'phone',
        'email',
        'manager_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function managers(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id')->where('role', 'manager');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'branch_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'branch_id');
    }

    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class, 'branch_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
