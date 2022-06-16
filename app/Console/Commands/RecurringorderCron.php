<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\v4\RecurringController;

class RecurringorderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurringorder:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For Recurring Orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        \Log::info("Recurring Order Cron is working fine!");
        //

        $paymentStatus = new RecurringController;
        // $paymentStatus->checkRecurOrderPayment();
        $paymentStatus->OrderRecurringPayment();
        $this->info('Demo:Recurring cron command run successfully!');
    }
}
