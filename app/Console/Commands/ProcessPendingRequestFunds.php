<?php

namespace App\Console\Commands;

use App\Events\CreateRequestOfferEvent;
use App\Models\v2\RequestFund;
use Illuminate\Console\Command;

class ProcessPendingRequestFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-request-funds {--y : accept and continue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending request funds';

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
     * @return int
     */
    public function handle()
    {
        $query = RequestFund::whereNotIn('status',['accepted_customer','rejected_customer']);
        $total = $query->count();
        $this->info("Total Affected Items: ". $total );
        $continue = $this->option('y') ?true: $this->confirm("Do you want to continue",false);
        if($continue) {
            $bar = $this->output->createProgressBar($total);
            $query->chunk(1000,function($requests) use($bar){
                $requests->each(function($request){
                    CreateRequestOfferEvent::dispatch($request);
                });
                $bar->advance($requests->count());
            });
            $this->info("\n");
            $this->info($total." Requestfunds offer requests created");
        }


        return 0;
    }
}
