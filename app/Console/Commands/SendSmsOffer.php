<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;

use App\Models\Invoice;
use App\Models\v2\RequestFund;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SendSmsOffer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Sms for customer fund';

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
        $EstateRequest = RequestFund::whereHas('offers')
      ->get();



      //  Log::channel('slack')->info('test123');
        /*  foreach ($EstateRequest as $EstateRequestItem)
          {



             // Log::channel('slack')->info($EstateRequestItem->uuid);
              dispatch(new \App\Jobs\SmsOfferCreator($EstateRequestItem));

          }*/

        foreach ($EstateRequest as $key => $EstateRequestItem) {


            dispatch(new \App\Jobs\SmsOfferCreator($EstateRequestItem))->delay($key * 2);
        }


    }
}
