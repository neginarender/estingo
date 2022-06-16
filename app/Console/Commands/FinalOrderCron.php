<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RedisController;

class FinalOrderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finalorder:cron';

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
        \Log::info("Final Order Cron is working fine!");
        //
        $finalOrder = new RedisController;
        $finalOrder->createFinalOrderCron();
        $this->info('Final Order:Cron Command Run successfully!');
    }
}
