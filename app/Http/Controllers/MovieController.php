<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    /**
     * Display a listing of the movies.
     */
    public function index(Request $request)
    {
        try {
            $query = Movie::query();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by genre
            if ($request->has('genre')) {
                $query->where('genre', $request->genre);
            }

            // Filter by language
            if ($request->has('language')) {
                $query->where('language', $request->language);
            }

            // Filter by age rating
            if ($request->has('age_rating')) {
                $query->where('age_rating', $request->age_rating);
            }

            // Search by title or description
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sort by
            $sortBy = $request->get('sort_by', 'release_date');
            $sortDirection = $request->get('sort_direction', 'desc');

            $query->orderBy($sortBy, $sortDirection);

            // Cache the results for 1 hour
            $movies = Cache::remember('movies.index.' . md5($request->fullUrl()), 3600, function () use ($query) {
                return $query->with(['showtimes' => function ($query) {
                    $query->where('start_time', '>=', now())
                          ->orderBy('start_time');
                }])->get();
            });

            return Inertia::render('Movies/Index', [
                'movies' => $movies,
                'filters' => $request->only(['status', 'genre', 'language', 'age_rating', 'search', 'sort_by', 'sort_direction'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in MovieController@index: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching movies.');
        }
    }

    /**
     * Display the specified movie.
     */
    public function show(Movie $movie)
    {
        try {
            // Cache the movie details for 1 hour
            $movie = Cache::remember('movies.show.' . $movie->id, 3600, function () use ($movie) {
                return $movie->load(['showtimes' => function ($query) {
                    $query->where('start_time', '>=', now())
                          ->orderBy('start_time')
                          ->with(['hall' => function ($query) {
                              $query->with('seats');
                          }]);
                }]);
            });

            // Get related movies
            $relatedMovies = Cache::remember('movies.related.' . $movie->id, 3600, function () use ($movie) {
                return Movie::where('genre', $movie->genre)
                    ->where('id', '!=', $movie->id)
                    ->where('status', 'active')
                    ->limit(4)
                    ->get();
            });

            return Inertia::render('Movies/Show', [
                'movie' => $movie,
                'relatedMovies' => $relatedMovies
            ]);
        } catch (\Exception $e) {
            Log::error('Error in MovieController@show: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching movie details.');
        }
    }

    /**
     * Get movie statistics.
     */
    public function stats()
    {
        try {
            $stats = Cache::remember('movies.stats', 3600, function () {
                return [
                    'total' => Movie::count(),
                    'active' => Movie::where('status', 'active')->count(),
                    'upcoming' => Movie::where('release_date', '>', now())->count(),
                    'now_showing' => Movie::where('release_date', '<=', now())
                        ->where('status', 'active')
                        ->count(),
                    'by_genre' => Movie::selectRaw('genre, count(*) as count')
                        ->groupBy('genre')
                        ->get(),
                    'by_language' => Movie::selectRaw('language, count(*) as count')
                        ->groupBy('language')
                        ->get(),
                    'by_age_rating' => Movie::selectRaw('age_rating, count(*) as count')
                        ->groupBy('age_rating')
                        ->get()
                ];
            });

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error in MovieController@stats: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching movie statistics.'], 500);
        }
    }
}
