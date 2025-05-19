<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'duration',
        'release_date',
        'genre',
        'poster_url',
        'trailer_url',
        'rating',
        'slug',
        'status',
        'language',
        'director',
        'cast',
        'age_rating'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'release_date' => 'date',
        'duration' => 'integer',
        'rating' => 'float',
        'cast' => 'array',
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

        static::creating(function ($movie) {
            $movie->slug = Str::slug($movie->title);
        });

        static::updating(function ($movie) {
            if ($movie->isDirty('title')) {
                $movie->slug = Str::slug($movie->title);
            }
        });
    }

    /**
     * Get the showtimes for the movie.
     */
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * Get the active showtimes for the movie.
     */
    public function activeShowtimes(): HasMany
    {
        return $this->showtimes()->where('status', 'active');
    }

    /**
     * Scope a query to only include active movies.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include upcoming movies.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('release_date', '>', now());
    }

    /**
     * Scope a query to only include now showing movies.
     */
    public function scopeNowShowing($query)
    {
        return $query->where('release_date', '<=', now())
                    ->where('status', 'active');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
