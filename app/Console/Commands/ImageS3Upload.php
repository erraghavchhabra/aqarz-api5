<?php

namespace App\Console\Commands;

use App\Jobs\PdfCreator;


use App\Models\v2\AttachmentEstate;
use App\Models\v2\Comfort;
use App\Models\v2\EstateType;
use App\Models\v2\Invoice;
use App\Models\v2\MemberType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class ImageS3Upload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's3:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Image in S3 server';

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




        $estate_type=AttachmentEstate::query()->get();
        foreach ($estate_type as $estate_typeItem)
        {


          //  dd($estate_typeItem->icon);
            dispatch(new \App\Jobs\ImageS3Upload($estate_typeItem));

        }









    }
}
