<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'profile_photo',
        'last_login_at',
        'last_login_ip',
        'is_active',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'last_login_ip',
        'deleted_at'
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
            'last_login_at' => 'datetime',
            'is_active' => 'boolean'
        ];
    }
    
    /**
     * Get the bookings for the user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the active bookings for the user.
     */
    public function activeBookings(): HasMany
    {
        return $this->bookings()->where('status', 'active');
    }

    /**
     * Get the cancelled bookings for the user.
     */
    public function cancelledBookings(): HasMany
    {
        return $this->bookings()->where('status', 'cancelled');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Deactivate the user.
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Activate the user.
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Update the user's last login information.
     */
    public function updateLastLogin(string $ip): bool
    {
        $this->last_login_at = now();
        $this->last_login_ip = $ip;
        return $this->save();
    }

    /**
     * Get the user's full address.
     */
    public function getFullAddress(): string
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country
        ]));
    }

    /**
     * Get the user's booking statistics.
     */
    public function getBookingStats(): array
    {
        return [
            'total' => $this->bookings()->count(),
            'active' => $this->activeBookings()->count(),
            'cancelled' => $this->cancelledBookings()->count(),
            'total_spent' => $this->bookings()->sum('total_price')
        ];
    }
}
