<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands and scheduled tasks.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| Scheduled Tasks (Migrated from Laravel 12 Console Kernel)
|--------------------------------------------------------------------------
*/

Schedule::command('upgrade:daily')
    ->dailyAt('00:00')
    ->timezone('Asia/Shanghai')
    ->emailOutputTo(config('mint.email.ScheduleEmailOutputTo'))
    ->emailOutputOnFailure(config('mint.email.ScheduleEmailOutputOnFailure'));

Schedule::command('upgrade:weekly')
    ->weekly()
    ->timezone('Asia/Shanghai')
    ->emailOutputOnFailure(config('mint.email.ScheduleEmailOutputOnFailure'));

Schedule::command('app:index-open-search')
    ->everyFiveMinutes()
    ->timezone('Asia/Shanghai')
    ->emailOutputOnFailure(config('mint.email.ScheduleEmailOutputOnFailure'));
