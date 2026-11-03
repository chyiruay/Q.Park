<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /**
         * Drain the queue once a minute instead of running a resident worker. The only
         * queued job is the order confirmation email, a few a day — a process sitting
         * idle 24/7 would cost more than it saves on compute that scales to zero.
         *
         * shortcut: confirmations are delayed up to ~60s; switch to a resident
         * `queue:work` if anything is ever queued that must run immediately.
         */
        $schedule->command('queue:work --stop-when-empty --tries=3')
            ->everyMinute()
            ->withoutOverlapping();
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
