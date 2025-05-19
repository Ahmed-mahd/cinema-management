<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Filter by role
            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sort by
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            $query->orderBy($sortBy, $sortDirection);

            // Cache the results for 5 minutes
            $users = Cache::remember('users.index.' . md5($request->fullUrl()), 300, function () use ($query) {
                return $query->with(['bookings' => function ($query) {
                    $query->latest();
                }])->get();
            });

            // Get user statistics
            $stats = Cache::remember('users.stats', 300, function () {
                return [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'inactive' => User::where('status', 'inactive')->count(),
                    'by_role' => User::selectRaw('role, count(*) as count')
                        ->groupBy('role')
                        ->get(),
                    'by_status' => User::selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->get()
                ];
            });

            return Inertia::render('Users/Index', [
                'users' => $users,
                'stats' => $stats,
                'filters' => $request->only(['role', 'status', 'search', 'sort_by', 'sort_direction'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UserController@index: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching users.');
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        try {
            // Cache the user details for 5 minutes
            $user = Cache::remember('users.show.' . $user->id, 300, function () use ($user) {
                return $user->load(['bookings' => function ($query) {
                    $query->with(['showtime.movie', 'showtime.hall', 'seats'])
                          ->latest();
                }]);
            });

            // Get user statistics
            $stats = Cache::remember('users.stats.' . $user->id, 300, function () use ($user) {
                return [
                    'total_bookings' => $user->bookings()->count(),
                    'active_bookings' => $user->bookings()->where('status', 'active')->count(),
                    'cancelled_bookings' => $user->bookings()->where('status', 'cancelled')->count(),
                    'total_spent' => $user->bookings()->where('status', 'active')->sum('total_price'),
                    'by_status' => $user->bookings()
                        ->selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->get(),
                    'by_payment_status' => $user->bookings()
                        ->selectRaw('payment_status, count(*) as count')
                        ->groupBy('payment_status')
                        ->get()
                ];
            });

            return Inertia::render('Users/Show', [
                'user' => $user,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UserController@show: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching user details.');
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        try {
            $validator = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'role' => 'required|in:user,admin,staff',
                'status' => 'required|in:active,inactive',
                'password' => 'nullable|string|min:8|confirmed'
            ]);

            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            DB::commit();

            // Clear relevant caches
            Cache::forget('users.index');
            Cache::forget('users.stats');
            Cache::forget('users.show.' . $user->id);
            Cache::forget('users.stats.' . $user->id);

            return redirect()->route('users.show', $user)
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in UserController@update: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update user. Please try again.']);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        try {
            // Check if user has any active bookings
            if ($user->bookings()->where('status', 'active')->exists()) {
                return back()->withErrors(['error' => 'Cannot delete user with active bookings.']);
            }

            DB::beginTransaction();

            // Soft delete the user
            $user->delete();

            DB::commit();

            // Clear relevant caches
            Cache::forget('users.index');
            Cache::forget('users.stats');

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in UserController@destroy: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete user. Please try again.']);
        }
    }

    /**
     * Get user statistics.
     */
    public function stats()
    {
        try {
            $stats = Cache::remember('users.stats', 3600, function () {
                return [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'inactive' => User::where('status', 'inactive')->count(),
                    'by_role' => User::selectRaw('role, count(*) as count')
                        ->groupBy('role')
                        ->get(),
                    'by_status' => User::selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->get(),
                    'total_bookings' => User::withCount('bookings')->get()->sum('bookings_count'),
                    'total_revenue' => User::withSum('bookings', 'total_price')->get()->sum('bookings_sum_total_price')
                ];
            });

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error in UserController@stats: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching user statistics.'], 500);
        }
    }
} 