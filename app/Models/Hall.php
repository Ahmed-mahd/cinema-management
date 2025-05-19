<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Hall extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'capacity',
        'description',
        'type',
        'status',
        'rows',
        'columns',
        'slug',
        'features',
        'screen_type',
        'sound_system'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'capacity' => 'integer',
        'rows' => 'integer',
        'columns' => 'integer',
        'features' => 'array',
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

        static::creating(function ($hall) {
            $hall->slug = Str::slug($hall->name);
        });

        static::updating(function ($hall) {
            if ($hall->isDirty('name')) {
                $hall->slug = Str::slug($hall->name);
            }
        });
    }

    /**
     * Get the showtimes for the hall.
     */
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * Get the seats for the hall.
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get the active showtimes for the hall.
     */
    public function activeShowtimes(): HasMany
    {
        return $this->showtimes()->where('status', 'active');
    }

    /**
     * Scope a query to only include active halls.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include halls of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if the hall is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the hall's layout as a 2D array.
     */
    public function getLayout(): array
    {
        $layout = [];
        $seats = $this->seats()->orderBy('row')->orderBy('column')->get();

        foreach ($seats as $seat) {
            if (!isset($layout[$seat->row])) {
                $layout[$seat->row] = [];
            }
            $layout[$seat->row][$seat->column] = $seat;
        }

        return $layout;
    }

    /**
     * Get the hall's available seats count.
     */
    public function getAvailableSeatsCount(): int
    {
        return $this->seats()->where('status', 'available')->count();
    }

    /**
     * Get the hall's booked seats count.
     */
    public function getBookedSeatsCount(): int
    {
        return $this->seats()->where('status', 'booked')->count();
    }

    /**
     * Get the hall's maintenance seats count.
     */
    public function getMaintenanceSeatsCount(): int
    {
        return $this->seats()->where('status', 'maintenance')->count();
    }

    /**
     * Get the hall's statistics.
     */
    public function getStats(): array
    {
        return [
            'total_seats' => $this->capacity,
            'available_seats' => $this->getAvailableSeatsCount(),
            'booked_seats' => $this->getBookedSeatsCount(),
            'maintenance_seats' => $this->getMaintenanceSeatsCount(),
            'active_showtimes' => $this->activeShowtimes()->count()
        ];
    }
}
