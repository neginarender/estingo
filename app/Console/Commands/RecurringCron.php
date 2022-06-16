<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PurchaseHistoryController;

class RecurringCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:cron';

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

        Log::info("recurring Cron is working fine!");
        //

        $paymentStatus = new PurchaseHistoryController;
        $paymentStatus->checkRecurOrderPayment();
        $this->info('Demo:Recurring Cron Command Run successfully!');
    }
}
