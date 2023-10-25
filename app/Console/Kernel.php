<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('upgrade:daily')
                 ->dailyAt('00:00')
                 ->emailOutputTo(config("mint.email.ScheduleEmailOutputTo"))
				 ->emailOutputOnFailure(config("mint.email.ScheduleEmailOutputOnFailure"));

        $schedule->command('upgrade:weekly')
                 ->weekly()
                 ->emailOutputOnFailure(config("mint.email.ScheduleEmailOutputOnFailure"));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

	/**
	 * Get the timezone that should be used by default for scheduled events.
	 *
	 * @return \DateTimeZone|string|null
	 */
	protected function scheduleTimezone()
	{
		return 'Asia/Shanghai';
	}
}
