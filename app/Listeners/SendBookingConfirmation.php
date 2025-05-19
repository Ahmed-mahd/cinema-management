<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;
        
        try {
            // Send email confirmation
            Mail::to($booking->user->email)->send(new \App\Mail\BookingConfirmation($booking));
            
            // Log the confirmation
            Log::info('Booking confirmation sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'booking_number' => $booking->booking_number
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 