<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class DeleteDumyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:dumy-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Dumy Data';

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

      //  Log::channel('slack')->info();

      //  dispatch(new \App\Jobs\DeleteDumyData());


    }
}
