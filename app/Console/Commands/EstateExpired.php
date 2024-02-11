<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class EstateExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estate:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check estate date';

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

        if (strpos(URL::full(), 'apibeta') == false) {
            dispatch(new \App\Jobs\EstateExpired())->onConnection('sync');
        }

    }
}
