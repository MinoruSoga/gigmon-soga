<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;

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
        $schedule->call('App\Http\Controllers\Api\ReportUsageController@index')->lastDayOfMonth('23:55');
        $schedule->call('App\Http\Controllers\Api\FreeTrialController@index')->monthlyOn(1, '00:15');

        // delete all companies that are marked as deleted
        $schedule->call('App\Http\Controllers\Admin\CompaniesController@deleteAllCompaniesMarkedAsDeleted')->monthlyOn(1, '00:30');
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
