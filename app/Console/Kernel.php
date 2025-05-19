<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up expired bookings daily at midnight
        $schedule->command('bookings:cleanup-expired')->daily();

        // Update movie statuses daily
        $schedule->command('movies:update-status')->daily();

        // Generate daily reports
        $schedule->command('reports:generate-daily')->dailyAt('23:55');

        // Clean up old logs weekly
        $schedule->command('logs:cleanup')->weekly();

        // Backup database daily
        $schedule->command('backup:run')->dailyAt('01:00');

        // Monitor system health every hour
        $schedule->command('system:health-check')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 