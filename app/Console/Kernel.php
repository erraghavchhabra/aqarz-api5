<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
       /* $schedule->command('telescope:prune')->monthly();
        $schedule->command('pdf:create')
            ->monthly();*/
             $schedule->command('daily:estate')
            ->cron('0 13 * * *')->timezone('Asia/Riyadh');

        $schedule->command('app:process-request-funds --y')
            ->twiceDaily();
     /*   $schedule->command('count:create')
            ->daily();*/

        $schedule->command('offer:expired')
            ->weekly();

        $schedule->command('estate:expired')
            ->daily();


        $schedule->command('count:realOffer')
            ->daily();



      /*  $schedule->command('delete:dumy-data')
            ->daily();*/


        // $schedule->command('inspire')->hourly();
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
