<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Hall;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShowtimeController extends Controller
{
    /**
     * Display a listing of the showtimes.
     */
    public function index()
    {
        $showtimes = Showtime::with(['movie', 'hall'])
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Admin/Showtimes/Index', [
            'showtimes' => $showtimes,
        ]);
    }

    /**
     * Show the form for creating a new showtime.
     */
    public function create()
    {
        $movies = Movie::where('is_active', true)->get();
        $halls = Hall::where('is_active', true)->get();

        return Inertia::render('Admin/Showtimes/Create', [
            'movies' => $movies,
            'halls' => $halls,
        ]);
    }

    /**
     * Store a newly created showtime.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'hall_id' => 'required|exists:halls,id',
            'start_time' => 'required|date|after:now',
            'price' => 'required|numeric|min:0',
        ]);

        // Check for overlapping showtimes in the same hall
        $overlapping = Showtime::where('hall_id', $validated['hall_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [
                    $validated['start_time'],
                    now()->addMinutes(Movie::find($validated['movie_id'])->duration),
                ])
                ->orWhereBetween('start_time', [
                    now()->subMinutes(Movie::find($validated['movie_id'])->duration),
                    $validated['start_time'],
                ]);
            })
            ->exists();

        if ($overlapping) {
            return back()->with('error', 'This time slot overlaps with another showtime in the same hall.');
        }

        Showtime::create($validated);

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Showtime created successfully.');
    }

    /**
     * Show the form for editing the specified showtime.
     */
    public function edit(Showtime $showtime)
    {
        $movies = Movie::where('is_active', true)->get();
        $halls = Hall::where('is_active', true)->get();

        return Inertia::render('Admin/Showtimes/Edit', [
            'showtime' => $showtime,
            'movies' => $movies,
            'halls' => $halls,
        ]);
    }

    /**
     * Update the specified showtime.
     */
    public function update(Request $request, Showtime $showtime)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'hall_id' => 'required|exists:halls,id',
            'start_time' => 'required|date|after:now',
            'price' => 'required|numeric|min:0',
        ]);

        // Check for overlapping showtimes in the same hall
        $overlapping = Showtime::where('hall_id', $validated['hall_id'])
            ->where('id', '!=', $showtime->id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [
                    $validated['start_time'],
                    now()->addMinutes(Movie::find($validated['movie_id'])->duration),
                ])
                ->orWhereBetween('start_time', [
                    now()->subMinutes(Movie::find($validated['movie_id'])->duration),
                    $validated['start_time'],
                ]);
            })
            ->exists();

        if ($overlapping) {
            return back()->with('error', 'This time slot overlaps with another showtime in the same hall.');
        }

        $showtime->update($validated);

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Showtime updated successfully.');
    }

    /**
     * Remove the specified showtime.
     */
    public function destroy(Showtime $showtime)
    {
        if ($showtime->bookings()->exists()) {
            return back()->with('error', 'Cannot delete showtime with existing bookings.');
        }

        $showtime->delete();

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Showtime deleted successfully.');
    }
}
