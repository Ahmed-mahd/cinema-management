<?php

namespace App\Http\Controllers;

use App\Models\Showtime;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShowtimeController extends Controller
{
    /**
     * Display a listing of the showtimes.
     */
    public function index(Request $request)
    {
        try {
            $query = Showtime::query();

            // Filter by movie
            if ($request->has('movie_id')) {
                $query->where('movie_id', $request->movie_id);
            }

            // Filter by hall
            if ($request->has('hall_id')) {
                $query->where('hall_id', $request->hall_id);
            }

            // Filter by date
            if ($request->has('date')) {
                $query->whereDate('start_time', $request->date);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Sort by
            $sortBy = $request->get('sort_by', 'start_time');
            $sortDirection = $request->get('sort_direction', 'asc');

            $query->orderBy($sortBy, $sortDirection);

            // Cache the results for 1 hour
            $showtimes = Cache::remember('showtimes.index.' . md5($request->fullUrl()), 3600, function () use ($query) {
                return $query->with(['movie', 'hall'])->get();
            });

            return Inertia::render('Showtimes/Index', [
                'showtimes' => $showtimes,
                'filters' => $request->only(['movie_id', 'hall_id', 'date', 'status', 'type', 'sort_by', 'sort_direction'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ShowtimeController@index: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching showtimes.');
        }
    }

    /**
     * Display the specified showtime.
     */
    public function show(Showtime $showtime)
    {
        try {
            // Cache the showtime details for 1 hour
            $showtime = Cache::remember('showtimes.show.' . $showtime->id, 3600, function () use ($showtime) {
                return $showtime->load(['movie', 'hall.seats' => function ($query) {
                    $query->orderBy('row')
                          ->orderBy('number');
                }, 'bookings.seats']);
            });

            // Get booked seats for this showtime
            $bookedSeats = Cache::remember('showtimes.booked_seats.' . $showtime->id, 300, function () use ($showtime) {
                return $showtime->bookings()
                    ->where('status', '!=', 'cancelled')
                    ->with('seats')
                    ->get()
                    ->pluck('seats')
                    ->flatten()
                    ->pluck('id');
            });

            // Get available seats count
            $availableSeats = $showtime->available_seats;

            // Get booking statistics
            $bookingStats = Cache::remember('showtimes.booking_stats.' . $showtime->id, 300, function () use ($showtime) {
                return [
                    'total_bookings' => $showtime->bookings()->count(),
                    'active_bookings' => $showtime->bookings()->where('status', 'active')->count(),
                    'cancelled_bookings' => $showtime->bookings()->where('status', 'cancelled')->count(),
                    'total_revenue' => $showtime->bookings()->where('status', 'active')->sum('total_price')
                ];
            });

            return Inertia::render('Showtimes/Show', [
                'showtime' => $showtime,
                'bookedSeats' => $bookedSeats,
                'availableSeats' => $availableSeats,
                'bookingStats' => $bookingStats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ShowtimeController@show: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching showtime details.');
        }
    }

    /**
     * Get showtime statistics.
     */
    public function stats()
    {
        try {
            $stats = Cache::remember('showtimes.stats', 3600, function () {
                return [
                    'total' => Showtime::count(),
                    'active' => Showtime::where('status', 'active')->count(),
                    'upcoming' => Showtime::where('start_time', '>', now())->count(),
                    'today' => Showtime::whereDate('start_time', today())->count(),
                    'by_type' => Showtime::selectRaw('type, count(*) as count')
                        ->groupBy('type')
                        ->get(),
                    'by_status' => Showtime::selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->get(),
                    'revenue' => Showtime::with('bookings')
                        ->get()
                        ->sum(function ($showtime) {
                            return $showtime->bookings()
                                ->where('status', 'active')
                                ->sum('total_price');
                        })
                ];
            });

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error in ShowtimeController@stats: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching showtime statistics.'], 500);
        }
    }

    /**
     * Check seat availability.
     */
    public function checkAvailability(Request $request, Showtime $showtime)
    {
        try {
            $validator = Validator::make($request->all(), [
                'seat_ids' => 'required|array',
                'seat_ids.*' => 'exists:seats,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $seatIds = $request->seat_ids;
            $bookedSeats = $showtime->bookings()
                ->where('status', '!=', 'cancelled')
                ->with('seats')
                ->get()
                ->pluck('seats')
                ->flatten()
                ->pluck('id');

            $availableSeats = $showtime->hall->seats()
                ->whereIn('id', $seatIds)
                ->whereNotIn('id', $bookedSeats)
                ->get();

            return response()->json([
                'available' => $availableSeats->count() === count($seatIds),
                'available_seats' => $availableSeats,
                'booked_seats' => $bookedSeats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ShowtimeController@checkAvailability: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while checking seat availability.'], 500);
        }
    }
}
