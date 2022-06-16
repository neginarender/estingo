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
        //Commands\InventoryCron::class
        Commands\paymentCron::class
        // Commands\DownloadOrdersCron::class,
        // Commands\RecurringCron::class,
        // Commands\RecurringorderCron::class,
        // Commands\SubOrderCron::class,
        // Commands\FinalOrderCron::class,
        // Commands\OrderStatusCron::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         //$schedule->command('inventory:cron')->everyMinute();
         $schedule->command('checkpayment:cron')->everyMinute();
         //$schedule->command('checkpayment:cron')->hourly();
         //$schedule->command('downloadorders:cron')->everyMinute();
         // $schedule->command('recurring:cron')->everyMinute();
        //$schedule->command('recurringorder:cron')->everyMinute();
        // $schedule->command('recurringorder:cron')->dailyAt('03:00');
        // $schedule->command('suborder:cron')->hourly();
        // $schedule->command('suborder:cron')->dailyAt('02:00');
        //$schedule->command('finalorder:cron')->everyMinute();
        // $schedule->command('orderstatus:cron')->everyMinute();
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
