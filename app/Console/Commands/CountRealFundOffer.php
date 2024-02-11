<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CountRealFundOffer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count:realOffer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Real Offer';

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

      //  Log::channel('slack')->info('testst');

        dispatch(new \App\Jobs\CountRealFundOffer());


    }
}
