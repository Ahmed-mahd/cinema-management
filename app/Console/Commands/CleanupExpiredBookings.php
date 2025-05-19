<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cleanup-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired bookings that were not paid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired bookings...');

        try {
            $expiredBookings = Booking::where('status', 'pending')
                ->where('payment_status', 'pending')
                ->where('created_at', '<=', now()->subHours(24))
                ->get();

            $count = $expiredBookings->count();

            if ($count > 0) {
                foreach ($expiredBookings as $booking) {
                    $booking->update([
                        'status' => 'cancelled',
                        'payment_status' => 'expired',
                        'cancellation_reason' => 'Payment not received within 24 hours'
                    ]);

                    // Release the seats back to available
                    $booking->showtime->updateAvailableSeats(-count($booking->seats));
                }

                $this->info("Successfully cleaned up {$count} expired bookings.");
                Log::info("Cleaned up {$count} expired bookings");
            } else {
                $this->info('No expired bookings found.');
            }
        } catch (\Exception $e) {
            $this->error('An error occurred while cleaning up expired bookings.');
            Log::error('Failed to cleanup expired bookings: ' . $e->getMessage());
        }
    }
} 