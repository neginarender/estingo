<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InventoryCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        \Log::info("inventory api hit!");
        ECom_inventory_Status();
        $this->info('Demo:Cron Cummand Run successfully!');
    }
}
