<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;


use App\Models\v2\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class PdfInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create invoices';

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


        $invoice = Invoice::with('user','user_plan')->where('is_created', '0')->get();





        foreach ($invoice as $invoiceItem) {

            echo $invoiceItem->id;
            dispatch(new PdfCreator($invoiceItem));
        }


    }
}
