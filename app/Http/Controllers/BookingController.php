<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a listing of the user's bookings.
     */
    public function index(Request $request)
    {
        try {
            $query = auth()->user()->bookings();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by payment status
            if ($request->has('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            // Search by booking number
            if ($request->has('search')) {
                $query->where('booking_number', 'like', "%{$request->search}%");
            }

            // Sort by
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $query->orderBy($sortBy, $sortDirection);

            // Cache the results for 5 minutes
            $bookings = Cache::remember('bookings.user.' . auth()->id() . '.' . md5($request->fullUrl()), 300, function () use ($query) {
                return $query->with(['showtime.movie', 'showtime.hall', 'seats'])
                    ->get();
            });

            // Get booking statistics
            $stats = Cache::remember('bookings.stats.user.' . auth()->id(), 300, function () {
                return [
                    'total' => auth()->user()->bookings()->count(),
                    'active' => auth()->user()->bookings()->where('status', 'active')->count(),
                    'cancelled' => auth()->user()->bookings()->where('status', 'cancelled')->count(),
                    'total_spent' => auth()->user()->bookings()->where('status', 'active')->sum('total_price')
                ];
            });

            return Inertia::render('Bookings/Index', [
                'bookings' => $bookings,
                'stats' => $stats,
                'filters' => $request->only(['status', 'payment_status', 'start_date', 'end_date', 'search', 'sort_by', 'sort_direction'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BookingController@index: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching bookings.');
        }
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        try {
            $validator = $request->validate([
                'showtime_id' => 'required|exists:showtimes,id',
                'seat_ids' => 'required|array',
                'seat_ids.*' => 'exists:seats,id',
                'payment_method' => 'required|in:card,cash,online'
            ]);

            $showtime = Showtime::findOrFail($request->showtime_id);

            // Check if showtime is active
            if (!$showtime->isActive()) {
                return back()->withErrors(['showtime' => 'This showtime is not active.']);
            }

            // Check if showtime is in the future
            if ($showtime->start_time <= now()) {
                return back()->withErrors(['showtime' => 'This showtime has already started.']);
            }

            // Check if seats are available
            $bookedSeats = $showtime->bookings()
                ->where('status', '!=', 'cancelled')
                ->with('seats')
                ->get()
                ->pluck('seats')
                ->flatten()
                ->pluck('id');

            $requestedSeats = collect($request->seat_ids);
            if ($bookedSeats->intersect($requestedSeats)->isNotEmpty()) {
                return back()->withErrors(['seats' => 'Some selected seats are already booked.']);
            }

            // Calculate total price
            $seats = $showtime->hall->seats()->whereIn('id', $request->seat_ids)->get();
            $totalPrice = $seats->sum(function ($seat) use ($showtime) {
                return $seat->getPriceForShowtime($showtime);
            });

            DB::beginTransaction();

            $booking = Booking::create([
                'user_id' => auth()->id(),
                'showtime_id' => $showtime->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'booking_number' => 'BK-' . strtoupper(Str::random(8))
            ]);

            $booking->seats()->attach($request->seat_ids);

            // Update showtime available seats
            $showtime->updateAvailableSeats(count($request->seat_ids));

            DB::commit();

            // Clear relevant caches
            Cache::forget('showtimes.show.' . $showtime->id);
            Cache::forget('showtimes.booked_seats.' . $showtime->id);
            Cache::forget('showtimes.booking_stats.' . $showtime->id);
            Cache::forget('bookings.user.' . auth()->id());

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking created successfully. Please complete the payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in BookingController@store: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create booking. Please try again.']);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        try {
            $this->authorize('view', $booking);

            // Cache the booking details for 5 minutes
            $booking = Cache::remember('bookings.show.' . $booking->id, 300, function () use ($booking) {
                return $booking->load(['showtime.movie', 'showtime.hall', 'seats', 'user']);
            });

            return Inertia::render('Bookings/Show', [
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BookingController@show: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching booking details.');
        }
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Booking $booking)
    {
        try {
            $this->authorize('cancel', $booking);

            if ($booking->status === 'cancelled') {
                return back()->withErrors(['error' => 'Booking is already cancelled.']);
            }

            if ($booking->showtime->start_time <= now()) {
                return back()->withErrors(['error' => 'Cannot cancel booking for a showtime that has already started.']);
            }

            DB::beginTransaction();

            $booking->cancel($request->cancellation_reason ?? null);

            // Update showtime available seats
            $booking->showtime->updateAvailableSeats(-count($booking->seats));

            DB::commit();

            // Clear relevant caches
            Cache::forget('showtimes.show.' . $booking->showtime_id);
            Cache::forget('showtimes.booked_seats.' . $booking->showtime_id);
            Cache::forget('showtimes.booking_stats.' . $booking->showtime_id);
            Cache::forget('bookings.user.' . auth()->id());

            return redirect()->route('bookings.index')
                ->with('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in BookingController@cancel: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to cancel booking. Please try again.']);
        }
    }

    /**
     * Get booking statistics.
     */
    public function stats()
    {
        try {
            $stats = Cache::remember('bookings.stats', 3600, function () {
                return [
                    'total' => Booking::count(),
                    'active' => Booking::where('status', 'active')->count(),
                    'cancelled' => Booking::where('status', 'cancelled')->count(),
                    'total_revenue' => Booking::where('status', 'active')->sum('total_price'),
                    'by_status' => Booking::selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->get(),
                    'by_payment_status' => Booking::selectRaw('payment_status, count(*) as count')
                        ->groupBy('payment_status')
                        ->get(),
                    'by_payment_method' => Booking::selectRaw('payment_method, count(*) as count')
                        ->groupBy('payment_method')
                        ->get()
                ];
            });

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error in BookingController@stats: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching booking statistics.'], 500);
        }
    }
}
