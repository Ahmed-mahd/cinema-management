<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Seat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class SeatController extends Controller
{
    /**
     * Display a listing of seats for a hall.
     */
    public function index(Hall $hall): Response
    {
        $seats = $hall->seats()->orderBy('row')->orderBy('number')->get();
        
        return Inertia::render('Seats/Index', [
            'hall' => $hall,
            'seats' => $seats
        ]);
    }

    /**
     * Update a seat's attributes.
     */
    public function update(Request $request, Seat $seat): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:available,unavailable,maintenance',
            'type' => 'sometimes|string|in:standard,premium,vip',
            'price_adjustment' => 'sometimes|numeric'
        ]);

        $seat->update($validated);

        return response()->json([
            'message' => 'Seat updated successfully',
            'seat' => $seat
        ]);
    }

    /**
     * Bulk update seats for a hall.
     */
    public function bulkUpdate(Request $request, Hall $hall): JsonResponse
    {
        $validated = $request->validate([
            'seats' => 'required|array',
            'seats.*.id' => 'required|exists:seats,id',
            'seats.*.status' => 'sometimes|string|in:available,unavailable,maintenance',
            'seats.*.type' => 'sometimes|string|in:standard,premium,vip',
            'seats.*.price_adjustment' => 'sometimes|numeric'
        ]);

        foreach ($validated['seats'] as $seatData) {
            $seat = Seat::find($seatData['id']);
            if ($seat && $seat->hall_id === $hall->id) {
                $seat->update(array_diff_key($seatData, ['id' => 0]));
            }
        }

        return response()->json([
            'message' => 'Seats updated successfully',
            'count' => count($validated['seats'])
        ]);
    }

    /**
     * Get seat statistics for a hall.
     */
    public function stats(Hall $hall): JsonResponse
    {
        $stats = [
            'total' => $hall->seats()->count(),
            'available' => $hall->seats()->where('status', 'available')->count(),
            'unavailable' => $hall->seats()->where('status', 'unavailable')->count(),
            'maintenance' => $hall->seats()->where('status', 'maintenance')->count(),
            'types' => [
                'standard' => $hall->seats()->where('type', 'standard')->count(),
                'premium' => $hall->seats()->where('type', 'premium')->count(),
                'vip' => $hall->seats()->where('type', 'vip')->count(),
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Get seat availability for a hall.
     */
    public function availability(Request $request, Hall $hall): JsonResponse
    {
        $validated = $request->validate([
            'showtime_id' => 'sometimes|exists:showtimes,id'
        ]);

        $seats = $hall->seats()->orderBy('row')->orderBy('number')->get();
        
        if (isset($validated['showtime_id'])) {
            // Get booked seats for this showtime
            $bookedSeatIds = \App\Models\Booking::where('showtime_id', $validated['showtime_id'])
                ->whereIn('status', ['active', 'pending'])
                ->join('booking_seat', 'bookings.id', '=', 'booking_seat.booking_id')
                ->pluck('booking_seat.seat_id')
                ->toArray();
                
            // Mark seats as booked
            $seats = $seats->map(function($seat) use ($bookedSeatIds) {
                $seat->is_booked = in_array($seat->id, $bookedSeatIds);
                return $seat;
            });
        }

        return response()->json([
            'seats' => $seats
        ]);
    }
} 