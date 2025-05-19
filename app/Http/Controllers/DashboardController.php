<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Booking;
use App\Models\Showtime;
use App\Models\Hall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        try {
            $user = auth()->user();

            // Cache dashboard data for 5 minutes
            $data = Cache::remember('dashboard.' . $user->id, 300, function () use ($user) {
                $data = [
                    'auth' => [
                        'user' => $user
                    ]
                ];

                // Common data for all users
                $data['upcomingShowtimes'] = Showtime::with(['movie', 'hall'])
                    ->where('start_time', '>=', now())
                    ->orderBy('start_time')
                    ->take(5)
                    ->get();

                $data['nowShowing'] = Movie::where('status', 'active')
                    ->where('release_date', '<=', now())
                    ->orderBy('release_date', 'desc')
                    ->take(5)
                    ->get();

                $data['comingSoon'] = Movie::where('status', 'active')
                    ->where('release_date', '>', now())
                    ->orderBy('release_date')
                    ->take(5)
                    ->get();

                if ($user->role === 'admin') {
                    // Admin dashboard data
                    $data['stats'] = $this->getAdminStats();
                    $data['recentBookings'] = Booking::with(['user', 'showtime.movie', 'showtime.hall'])
                        ->latest()
                        ->take(10)
                        ->get();
                    $data['recentUsers'] = User::latest()
                        ->take(5)
                        ->get();
                    $data['revenueChart'] = $this->getRevenueChart();
                    $data['bookingsChart'] = $this->getBookingsChart();
                    $data['moviesChart'] = $this->getMoviesChart();
                } else {
                    // User dashboard data
                    $data['recentBookings'] = $user->bookings()
                        ->with(['showtime.movie', 'showtime.hall', 'seats'])
                        ->latest()
                        ->take(5)
                        ->get();
                    $data['stats'] = $this->getUserStats($user);
                    $data['bookingHistory'] = $this->getUserBookingHistory($user);
                }

                return $data;
            });

            return Inertia::render('Dashboard', $data);
        } catch (\Exception $e) {
            Log::error('Error in DashboardController@index: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while loading the dashboard.');
        }
    }

    /**
     * Get admin dashboard statistics.
     */
    private function getAdminStats()
    {
        return [
            'total_movies' => Movie::count(),
            'active_movies' => Movie::where('status', 'active')->count(),
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::where('status', 'active')->count(),
            'total_revenue' => Booking::where('status', 'active')->sum('total_price'),
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_halls' => Hall::count(),
            'active_halls' => Hall::where('status', 'active')->count(),
            'total_showtimes' => Showtime::count(),
            'upcoming_showtimes' => Showtime::where('start_time', '>=', now())->count()
        ];
    }

    /**
     * Get user dashboard statistics.
     */
    private function getUserStats($user)
    {
        return [
            'total_bookings' => $user->bookings()->count(),
            'active_bookings' => $user->bookings()->where('status', 'active')->count(),
            'cancelled_bookings' => $user->bookings()->where('status', 'cancelled')->count(),
            'total_spent' => $user->bookings()->where('status', 'active')->sum('total_price'),
            'favorite_movies' => $user->bookings()
                ->with('showtime.movie')
                ->get()
                ->pluck('showtime.movie')
                ->unique('id')
                ->take(5)
        ];
    }

    /**
     * Get user booking history.
     */
    private function getUserBookingHistory($user)
    {
        $bookings = $user->bookings()
            ->with(['showtime.movie', 'showtime.hall', 'seats'])
            ->latest()
            ->get();

        return [
            'by_status' => $bookings->groupBy('status')
                ->map(function ($group) {
                    return $group->count();
                }),
            'by_month' => $bookings->groupBy(function ($booking) {
                return Carbon::parse($booking->created_at)->format('Y-m');
            })->map(function ($group) {
                return $group->count();
            }),
            'by_movie' => $bookings->groupBy('showtime.movie.title')
                ->map(function ($group) {
                    return $group->count();
                })
        ];
    }

    /**
     * Get revenue chart data.
     */
    private function getRevenueChart()
    {
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        });

        $revenue = Booking::where('status', 'active')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->get()
            ->groupBy(function ($booking) {
                return Carbon::parse($booking->created_at)->format('Y-m');
            })
            ->map(function ($group) {
                return $group->sum('total_price');
            });

        return $months->map(function ($month) use ($revenue) {
            return [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'revenue' => $revenue[$month] ?? 0
            ];
        });
    }

    /**
     * Get bookings chart data.
     */
    private function getBookingsChart()
    {
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        });

        $bookings = Booking::where('created_at', '>=', Carbon::now()->subMonths(6))
            ->get()
            ->groupBy(function ($booking) {
                return Carbon::parse($booking->created_at)->format('Y-m');
            })
            ->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'active' => $group->where('status', 'active')->count(),
                    'cancelled' => $group->where('status', 'cancelled')->count()
                ];
            });

        return $months->map(function ($month) use ($bookings) {
            $data = $bookings[$month] ?? ['total' => 0, 'active' => 0, 'cancelled' => 0];
            return [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'total' => $data['total'],
                'active' => $data['active'],
                'cancelled' => $data['cancelled']
            ];
        });
    }

    /**
     * Get movies chart data.
     */
    private function getMoviesChart()
    {
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        });

        $movies = Movie::where('created_at', '>=', Carbon::now()->subMonths(6))
            ->get()
            ->groupBy(function ($movie) {
                return Carbon::parse($movie->created_at)->format('Y-m');
            })
            ->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'active' => $group->where('status', 'active')->count()
                ];
            });

        return $months->map(function ($month) use ($movies) {
            $data = $movies[$month] ?? ['total' => 0, 'active' => 0];
            return [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'total' => $data['total'],
                'active' => $data['active']
            ];
        });
    }
} 