<?php
// app/Console/Kernel.php

namespace App\Console;

use App\Jobs\CheckExpiredPayments;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new CheckExpiredPayments)->everyFiveMinutes();
    }
}
