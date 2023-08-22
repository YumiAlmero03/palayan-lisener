<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\Biometrics\ScheduleController as Biometrics;

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
        // $schedule->command('inspire')->hourly();
        $schedule->call(Biometrics::getBiometric('dev'))->name('biometric_update')->everyTwoMinutes()->withoutOverlapping();
        $schedule->call(Biometrics::getAttendance())->name('fetch_attendance')->everyMinute()->withoutOverlapping();
        $schedule->call(Biometrics::sendAttendance('dev'))->name('send_attendance')->everyMinute()->withoutOverlapping();
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
}
