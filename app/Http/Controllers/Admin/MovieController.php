<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovieController extends Controller
{
    /**
     * Display a listing of the movies.
     */
    public function index()
    {
        $movies = Movie::with('showtimes')->latest()->get();

        return Inertia::render('Admin/Movies/Index', [
            'movies' => $movies
        ]);
    }

    /**
     * Store a newly created movie.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'genre' => 'required|string|max:255',
            'poster_url' => 'nullable|url|max:255',
            'trailer_url' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:10'
        ]);

        Movie::create($validated);

        return redirect()->route('admin.movies')
            ->with('success', 'Movie created successfully.');
    }

    /**
     * Update the specified movie.
     */
    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'genre' => 'required|string|max:255',
            'poster_url' => 'nullable|url|max:255',
            'trailer_url' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:10'
        ]);

        $movie->update($validated);

        return redirect()->route('admin.movies')
            ->with('success', 'Movie updated successfully.');
    }

    /**
     * Remove the specified movie.
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('admin.movies')
            ->with('success', 'Movie deleted successfully.');
    }
}
