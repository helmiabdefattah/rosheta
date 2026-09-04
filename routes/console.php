<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

/*
 * Demo sandbox housekeeping. Nothing else in this application is scheduled, so
 * the scheduler itself must be running for demos to be cleaned up:
 *   local  : php artisan schedule:work
 *   server : * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('demo:purge')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
