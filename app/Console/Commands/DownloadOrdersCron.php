<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DOFOController;

class DownloadOrdersCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'downloadorders:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Bulky orders with date';

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
        //

        // \Log::info("Download Orders Cron is working fine!");
        \Log::info("Orders Cron is working fine!");
        //
        $downloadOrders = new DOFOController;
        // $downloadOrders->cronDownloadOrders();
        $downloadOrders->csvJobsOrders();


        // $this->info('Download Orders New:Cron Command Run successfully!');
        $this->info('Orders Created:Cron Command Run successfully!');
    }
}
