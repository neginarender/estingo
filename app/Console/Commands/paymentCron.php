<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\PaytmController;

class paymentCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkpayment:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is will check the logged order payment and update it to order';

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
        \Log::info("Razorpay Cron is working fine!");
        //
        $paymentStatus = new RazorpayController;
        $paytmPaymentStatus = new PaytmController;
        $paymentStatus->checkRazorpayOrderPayment();
        $paytmPaymentStatus->updateTransactionStatus();
        $this->info('Paytm New:Cron Cummand Run successfully!');
        $this->info('Razorpay New:Cron Cummand Run successfully!');
    }
}
