<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Movie;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Booking;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Create regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);

        // Create movies
        $movies = [
            [
                'title' => 'The Shawshank Redemption',
                'description' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.',
                'duration' => 142,
                'release_date' => '1994-09-23',
                'genre' => 'Drama',
                'rating' => 9.3
            ],
            [
                'title' => 'The Godfather',
                'description' => 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.',
                'duration' => 175,
                'release_date' => '1972-03-24',
                'genre' => 'Crime',
                'rating' => 9.2
            ],
            [
                'title' => 'The Dark Knight',
                'description' => 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.',
                'duration' => 152,
                'release_date' => '2008-07-18',
                'genre' => 'Action',
                'rating' => 9.0
            ]
        ];

        foreach ($movies as $movie) {
            Movie::create($movie);
        }

        // Create halls
        $halls = [
            [
                'name' => 'Hall 1',
                'capacity' => 100,
                'description' => 'Main hall with premium sound system'
            ],
            [
                'name' => 'Hall 2',
                'capacity' => 80,
                'description' => 'Standard hall'
            ],
            [
                'name' => 'Hall 3',
                'capacity' => 60,
                'description' => 'Small hall for intimate screenings'
            ]
        ];

        foreach ($halls as $hall) {
            $createdHall = Hall::create($hall);
            
            // Create seats for each hall
            for ($i = 1; $i <= $hall['capacity']; $i++) {
                Seat::create([
                    'hall_id' => $createdHall->id,
                    'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT)
                ]);
            }
        }

        // Create showtimes
        $showtimes = [
            [
                'movie_id' => 1,
                'hall_id' => 1,
                'date' => now()->addDays(1)->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '16:22',
                'price' => 12.99
            ],
            [
                'movie_id' => 2,
                'hall_id' => 2,
                'date' => now()->addDays(1)->format('Y-m-d'),
                'start_time' => '18:00',
                'end_time' => '20:55',
                'price' => 14.99
            ],
            [
                'movie_id' => 3,
                'hall_id' => 3,
                'date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '20:00',
                'end_time' => '22:32',
                'price' => 13.99
            ]
        ];

        foreach ($showtimes as $showtime) {
            Showtime::create($showtime);
        }

        // Create some bookings
        $bookings = [
            [
                'user_id' => 2,
                'showtime_id' => 1,
                'total_amount' => 25.98,
                'status' => 'confirmed'
            ],
            [
                'user_id' => 2,
                'showtime_id' => 2,
                'total_amount' => 14.99,
                'status' => 'pending'
            ]
        ];

        foreach ($bookings as $booking) {
            $createdBooking = Booking::create($booking);
            
            // Assign seats to bookings
            if ($booking['status'] === 'confirmed') {
                $createdBooking->seats()->attach([1, 2]); // Attach first two seats
            } else {
                $createdBooking->seats()->attach([3]); // Attach third seat
            }
        }
    }
}
