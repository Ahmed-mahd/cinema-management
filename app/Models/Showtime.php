<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Showtime extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'movie_id',
        'hall_id',
        'start_time',
        'end_time',
        'price',
        'status',
        'type',
        'available_seats',
        'total_seats'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
        'available_seats' => 'integer',
        'total_seats' => 'integer',
        'status' => 'string'
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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($showtime) {
            $showtime->available_seats = $showtime->total_seats;
        });
    }

    /**
     * Get the movie that owns the showtime.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    /**
     * Get the hall that owns the showtime.
     */
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Get the bookings for the showtime.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the active bookings for the showtime.
     */
    public function activeBookings(): HasMany
    {
        return $this->bookings()->where('status', 'active');
    }

    /**
     * Scope a query to only include active showtimes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include upcoming showtimes.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope a query to only include today's showtimes.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    /**
     * Check if the showtime is fully booked.
     */
    public function isFullyBooked(): bool
    {
        return $this->available_seats <= 0;
    }

    /**
     * Check if the showtime is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the showtime is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time > now();
    }

    /**
     * Update available seats.
     */
    public function updateAvailableSeats(int $count): bool
    {
        $this->available_seats = max(0, $this->available_seats - $count);
        return $this->save();
    }
}
