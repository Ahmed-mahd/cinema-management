<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBookingPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Booking $booking
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Simulate payment processing
            sleep(2);

            // Update booking status
            $this->booking->update([
                'payment_status' => 'paid',
                'status' => 'active'
            ]);

            // Log successful payment
            Log::info('Payment processed successfully', [
                'booking_id' => $this->booking->id,
                'amount' => $this->booking->total_price
            ]);

            // Dispatch booking confirmation event
            event(new \App\Events\BookingCreated($this->booking));
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Update booking status to failed
        $this->booking->update([
            'payment_status' => 'failed',
            'status' => 'cancelled'
        ]);

        Log::error('Payment job failed', [
            'booking_id' => $this->booking->id,
            'error' => $exception->getMessage()
        ]);
    }
} 