<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;

use App\Models\Invoice;
use App\Models\v3\RequestFund;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class NebAssertRequestFundCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neb:fundRequest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add neb id to request fund';

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

$request_fund=RequestFund::where('fund_request_neighborhoods',null)->get();

foreach ($request_fund as $request_fundItem)
{
    dispatch(new \App\Jobs\NebAssertRequestFund($request_fundItem))->onConnection('sync');
}



    }
}
