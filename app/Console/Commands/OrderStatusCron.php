<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RedisController;

class OrderStatusCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orderstatus:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is will check the logged updated order and update it to order';

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
        \Log::info("Order Status Cron is working fine!");
        //
        $finalOrder = new RedisController;
        $finalOrder->updateOrderStatusCron();
        $this->info('Order Status:Cron Command Run successfully!');
    }
}
