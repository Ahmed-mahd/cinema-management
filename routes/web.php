<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\ShowtimeController as AdminShowtimeController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\HallController as AdminHallController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Welcome page - no authentication required
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'auth' => [
            'user' => auth()->user(),
        ],
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Authentication Routes
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Movie routes - fix order
    Route::get('/movies/stats', [MovieController::class, 'stats'])->name('movies.stats');
    Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
    Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');

    // Showtime routes - fix order
    Route::get('/showtimes/stats', [ShowtimeController::class, 'stats'])->name('showtimes.stats');
    Route::get('/showtimes/{showtime}', [ShowtimeController::class, 'show'])->name('showtimes.show');

    // Booking routes - fix order
    Route::get('/bookings/stats', [BookingController::class, 'stats'])->name('bookings.stats');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Hall routes - fix order
    Route::get('/halls/stats', [HallController::class, 'stats'])->name('halls.stats.overview');
    Route::get('/halls', [HallController::class, 'index'])->name('halls.index');
    Route::post('/halls', [HallController::class, 'store'])->name('halls.store');
    Route::get('/halls/{hall}', [HallController::class, 'show'])->name('halls.show');
    Route::put('/halls/{hall}', [HallController::class, 'update'])->name('halls.update');
    Route::delete('/halls/{hall}', [HallController::class, 'destroy'])->name('halls.destroy');

    // Seat routes
    Route::get('/halls/{hall}/seats', [SeatController::class, 'index'])->name('seats.index');
    Route::put('/seats/{seat}', [SeatController::class, 'update'])->name('seats.update');
    Route::post('/halls/{hall}/seats/bulk-update', [SeatController::class, 'bulkUpdate'])->name('seats.bulkUpdate');
    Route::get('/halls/{hall}/seats/stats', [SeatController::class, 'stats'])->name('seats.stats');
    Route::get('/halls/{hall}/seats/availability', [SeatController::class, 'availability'])->name('seats.availability');

    // User routes (admin only) - fix order
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users/stats', [UserController::class, 'stats'])->name('users.stats');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Movie management
        Route::get('/movies', [AdminMovieController::class, 'index'])->name('movies');
        Route::post('/movies', [AdminMovieController::class, 'store'])->name('movies.store');
        Route::put('/movies/{movie}', [AdminMovieController::class, 'update'])->name('movies.update');
        Route::delete('/movies/{movie}', [AdminMovieController::class, 'destroy'])->name('movies.destroy');

        // Showtime management
        Route::get('/showtimes', [AdminShowtimeController::class, 'index'])->name('showtimes');
        Route::post('/showtimes', [AdminShowtimeController::class, 'store'])->name('showtimes.store');
        Route::put('/showtimes/{showtime}', [AdminShowtimeController::class, 'update'])->name('showtimes.update');
        Route::delete('/showtimes/{showtime}', [AdminShowtimeController::class, 'destroy'])->name('showtimes.destroy');

        // Booking management
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings');
        Route::put('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');

        // Halls
        Route::get('/halls', [AdminHallController::class, 'index'])->name('halls.index');
        Route::get('/halls/create', [AdminHallController::class, 'create'])->name('halls.create');
        Route::post('/halls', [AdminHallController::class, 'store'])->name('halls.store');
        Route::get('/halls/{hall}/edit', [AdminHallController::class, 'edit'])->name('halls.edit');
        Route::put('/halls/{hall}', [AdminHallController::class, 'update'])->name('halls.update');
        Route::delete('/halls/{hall}', [AdminHallController::class, 'destroy'])->name('halls.destroy');
        Route::get('/halls/{hall}/stats', [AdminHallController::class, 'stats'])->name('halls.stats');

        // Reports
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    });
});
