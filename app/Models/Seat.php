<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seat extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hall_id',
        'row',
        'number',
        'column',
        'status',
        'type',
        'price_multiplier',
        'is_wheelchair_accessible',
        'is_love_seat',
        'is_vip',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'row' => 'integer',
        'number' => 'integer',
        'column' => 'integer',
        'price_multiplier' => 'float',
        'is_wheelchair_accessible' => 'boolean',
        'is_love_seat' => 'boolean',
        'is_vip' => 'boolean'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the hall that owns the seat.
     */
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Get the bookings for the seat.
     */
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_seat');
    }

    /**
     * Get the active bookings for the seat.
     */
    public function activeBookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_seat')
            ->where('bookings.status', 'active');
    }

    /**
     * Scope a query to only include available seats.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope a query to only include booked seats.
     */
    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    /**
     * Scope a query to only include maintenance seats.
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope a query to only include wheelchair accessible seats.
     */
    public function scopeWheelchairAccessible($query)
    {
        return $query->where('is_wheelchair_accessible', true);
    }

    /**
     * Scope a query to only include VIP seats.
     */
    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Check if the seat is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if the seat is booked.
     */
    public function isBooked(): bool
    {
        return $this->status === 'booked';
    }

    /**
     * Check if the seat is in maintenance.
     */
    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    /**
     * Mark the seat as available.
     */
    public function markAsAvailable(): bool
    {
        $this->status = 'available';
        return $this->save();
    }

    /**
     * Mark the seat as booked.
     */
    public function markAsBooked(): bool
    {
        $this->status = 'booked';
        return $this->save();
    }

    /**
     * Mark the seat as in maintenance.
     */
    public function markAsInMaintenance(): bool
    {
        $this->status = 'maintenance';
        return $this->save();
    }

    /**
     * Get the seat's full identifier.
     */
    public function getFullIdentifier(): string
    {
        return "{$this->row}-{$this->number}";
    }

    /**
     * Get the seat's price for a given showtime.
     */
    public function getPriceForShowtime(Showtime $showtime): float
    {
        return $showtime->price * $this->price_multiplier;
    }
}
