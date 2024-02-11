<?php

namespace App\Console\Commands;

use App\Jobs\PaymentMsgCreator;
use App\Jobs\PdfCreator;


use App\Models\v2\Invoice;
use App\Models\v2\UserPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class SendPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create payment msg';

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


        $invoice = UserPayment::with('user')->where('is_send', '0')->get();





        foreach ($invoice as $invoiceItem) {


            dispatch(new PaymentMsgCreator($invoiceItem));
        }


    }
}
