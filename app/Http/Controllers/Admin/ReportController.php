<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $timeRange = $request->input('timeRange', 'week');
        $startDate = $this->getStartDate($timeRange);

        // Get total revenue
        $totalRevenue = Booking::where('created_at', '>=', $startDate)
            ->where('status', 'active')
            ->sum('total_price');

        // Get total bookings
        $totalBookings = Booking::where('created_at', '>=', $startDate)
            ->where('status', 'active')
            ->count();

        // Calculate average ticket price
        $avgTicketPrice = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;

        // Calculate occupancy rate
        $totalSeats = Showtime::where('start_time', '>=', $startDate)
            ->with('hall')
            ->get()
            ->sum('hall.capacity');
        $bookedSeats = Booking::where('created_at', '>=', $startDate)
            ->where('status', 'active')
            ->with('seats')
            ->get()
            ->sum('seats.count');
        $occupancyRate = $totalSeats > 0 ? ($bookedSeats / $totalSeats) * 100 : 0;

        // Get revenue trend data
        $revenueData = $this->getRevenueTrendData($startDate);

        // Get booking distribution data
        $bookingData = $this->getBookingDistributionData($startDate);

        // Get movie distribution data
        $movieDistributionData = $this->getMovieDistributionData($startDate);

        // Get top performing movies
        $topMovies = $this->getTopMovies($startDate);

        return Inertia::render('Admin/Reports/Index', [
            'stats' => [
                'total_revenue' => $totalRevenue,
                'total_bookings' => $totalBookings,
                'avg_ticket_price' => $avgTicketPrice,
                'occupancy_rate' => round($occupancyRate, 2),
                'revenue' => $revenueData,
                'bookings' => $bookingData,
                'movie_distribution' => $movieDistributionData,
                'top_movies' => $topMovies,
            ],
        ]);
    }

    private function getStartDate($timeRange)
    {
        return match ($timeRange) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subWeek(),
        };
    }

    private function getRevenueTrendData($startDate)
    {
        $revenueData = [];
        $labels = [];
        $data = [];

        $bookings = Booking::where('created_at', '>=', $startDate)
            ->where('status', 'active')
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($bookings as $booking) {
            $labels[] = Carbon::parse($booking->date)->format('M d');
            $data[] = $booking->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getBookingDistributionData($startDate)
    {
        $bookingData = [];
        $labels = [];
        $data = [];

        $bookings = Booking::where('created_at', '>=', $startDate)
            ->where('status', 'active')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($bookings as $booking) {
            $labels[] = Carbon::parse($booking->date)->format('M d');
            $data[] = $booking->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getMovieDistributionData($startDate)
    {
        $movieDistributionData = [];
        $labels = [];
        $data = [];

        $movies = Movie::whereHas('showtimes.bookings', function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate)
                ->where('status', 'active');
        })
            ->withCount(['showtimes.bookings' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate)
                    ->where('status', 'active');
            }])
            ->orderByDesc('showtimes_bookings_count')
            ->limit(5)
            ->get();

        foreach ($movies as $movie) {
            $labels[] = $movie->title;
            $data[] = $movie->showtimes_bookings_count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getTopMovies($startDate)
    {
        return Movie::whereHas('showtimes.bookings', function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate)
                ->where('status', 'active');
        })
            ->withCount(['showtimes.bookings' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate)
                    ->where('status', 'active');
            }])
            ->withSum(['showtimes.bookings' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate)
                    ->where('status', 'active');
            }], 'total_price')
            ->orderByDesc('showtimes_bookings_sum_total_price')
            ->limit(5)
            ->get()
            ->map(function ($movie) {
                return [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'poster_url' => $movie->poster_url,
                    'revenue' => $movie->showtimes_bookings_sum_total_price,
                    'bookings' => $movie->showtimes_bookings_count,
                ];
            });
    }
} 