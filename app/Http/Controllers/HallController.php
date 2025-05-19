<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HallController extends Controller
{
    /**
     * Display a listing of the halls.
     */
    public function index(Request $request)
    {
        try {
            $query = Hall::query();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            // Sort by
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');

            $query->orderBy($sortBy, $sortDirection);

            // Cache the results for 1 hour
            $halls = Cache::remember('halls.index.' . md5($request->fullUrl()), 3600, function () use ($query) {
                return $query->with(['seats' => function ($query) {
                    $query->orderBy('row')
                          ->orderBy('number');
                }])->get();
            });

            // Get hall statistics
            $stats = Cache::remember('halls.stats', 3600, function () {
                return [
                    'total' => Hall::count(),
                    'active' => Hall::where('status', 'active')->count(),
                    'total_seats' => Hall::withCount('seats')->get()->sum('seats_count'),
                    'by_type' => Hall::selectRaw('type, count(*) as count')
                        ->groupBy('type')
                        ->get()
                ];
            });

            return Inertia::render('Halls/Index', [
                'halls' => $halls,
                'stats' => $stats,
                'filters' => $request->only(['status', 'type', 'search', 'sort_by', 'sort_direction'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in HallController@index: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching halls.');
        }
    }

    /**
     * Store a newly created hall.
     */
    public function store(Request $request)
    {
        try {
            $validator = $request->validate([
                'name' => 'required|string|max:255|unique:halls',
                'type' => 'required|in:standard,premium,vip',
                'status' => 'required|in:active,inactive,maintenance',
                'rows' => 'required|integer|min:1|max:20',
                'seats_per_row' => 'required|integer|min:1|max:30',
                'description' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $hall = Hall::create([
                'name' => $request->name,
                'type' => $request->type,
                'status' => $request->status,
                'description' => $request->description
            ]);

            // Create seats for the hall
            $seats = [];
            for ($row = 1; $row <= $request->rows; $row++) {
                for ($number = 1; $number <= $request->seats_per_row; $number++) {
                    $seats[] = [
                        'hall_id' => $hall->id,
                        'row' => $row,
                        'number' => $number,
                        'type' => $this->determineSeatType($row, $request->rows),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            DB::table('seats')->insert($seats);

            DB::commit();

            // Clear relevant caches
            Cache::forget('halls.index');
            Cache::forget('halls.stats');

            return redirect()->route('halls.show', $hall)
                ->with('success', 'Hall created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in HallController@store: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create hall. Please try again.']);
        }
    }

    /**
     * Display the specified hall.
     */
    public function show(Hall $hall)
    {
        try {
            // Cache the hall details for 1 hour
            $hall = Cache::remember('halls.show.' . $hall->id, 3600, function () use ($hall) {
                return $hall->load(['seats' => function ($query) {
                    $query->orderBy('row')
                          ->orderBy('number');
                }, 'showtimes' => function ($query) {
                    $query->where('start_time', '>=', now())
                          ->orderBy('start_time')
                          ->with('movie');
                }]);
            });

            // Get hall statistics
            $stats = Cache::remember('halls.stats.' . $hall->id, 3600, function () use ($hall) {
                return [
                    'total_seats' => $hall->seats()->count(),
                    'total_showtimes' => $hall->showtimes()->count(),
                    'upcoming_showtimes' => $hall->showtimes()
                        ->where('start_time', '>=', now())
                        ->count(),
                    'total_bookings' => $hall->showtimes()
                        ->withCount('bookings')
                        ->get()
                        ->sum('bookings_count')
                ];
            });

            return Inertia::render('Halls/Show', [
                'hall' => $hall,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in HallController@show: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching hall details.');
        }
    }

    /**
     * Update the specified hall.
     */
    public function update(Request $request, Hall $hall)
    {
        try {
            $validator = $request->validate([
                'name' => 'required|string|max:255|unique:halls,name,' . $hall->id,
                'type' => 'required|in:standard,premium,vip',
                'status' => 'required|in:active,inactive,maintenance',
                'description' => 'nullable|string'
            ]);

            $hall->update($validator);

            // Clear relevant caches
            Cache::forget('halls.index');
            Cache::forget('halls.stats');
            Cache::forget('halls.show.' . $hall->id);
            Cache::forget('halls.stats.' . $hall->id);

            return redirect()->route('halls.show', $hall)
                ->with('success', 'Hall updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in HallController@update: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update hall. Please try again.']);
        }
    }

    /**
     * Remove the specified hall.
     */
    public function destroy(Hall $hall)
    {
        try {
            // Check if hall has any active showtimes
            if ($hall->showtimes()->where('start_time', '>=', now())->exists()) {
                return back()->withErrors(['error' => 'Cannot delete hall with upcoming showtimes.']);
            }

            DB::beginTransaction();

            // Delete all seats
            $hall->seats()->delete();

            // Delete the hall
            $hall->delete();

            DB::commit();

            // Clear relevant caches
            Cache::forget('halls.index');
            Cache::forget('halls.stats');

            return redirect()->route('halls.index')
                ->with('success', 'Hall deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in HallController@destroy: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete hall. Please try again.']);
        }
    }

    /**
     * Get hall statistics.
     */
    public function stats()
    {
        try {
            $stats = Cache::remember('halls.stats', 3600, function () {
                return [
                    'total' => Hall::count(),
                    'active' => Hall::where('status', 'active')->count(),
                    'inactive' => Hall::where('status', 'inactive')->count(),
                    'maintenance' => Hall::where('status', 'maintenance')->count(),
                    'total_seats' => Hall::withCount('seats')->get()->sum('seats_count'),
                    'by_type' => Hall::selectRaw('type, count(*) as count')
                        ->groupBy('type')
                        ->get(),
                    'by_status' => Hall::selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->get()
                ];
            });

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error in HallController@stats: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching hall statistics.'], 500);
        }
    }

    /**
     * Determine seat type based on row position.
     */
    private function determineSeatType($row, $totalRows)
    {
        if ($row <= ceil($totalRows * 0.2)) {
            return 'premium';
        } elseif ($row <= ceil($totalRows * 0.4)) {
            return 'standard';
        } else {
            return 'economy';
        }
    }
} 