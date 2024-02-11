<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class OfferCreator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offer:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Fund Offer';

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


        dispatch(new \App\Jobs\OfferCreator())->onConnection('sync');


    }
}
