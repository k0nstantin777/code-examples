<?php

namespace App\Console;

use App\Domains\Webhook\Jobs\WebhookEventsCreate;
use App\Domains\Webhook\Jobs\WebhooksCalling;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule) : void
    {
        $schedule->job(app(WebhookEventsCreate::class))->everyTenMinutes();
        $schedule->job(app(WebhooksCalling::class))->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
