<?php

namespace App\Jobs;

use App\Models\v2\AttachmentEstate;
use App\Models\v2\MemberType;
use App\Models\v2\Comfort;
use App\Models\v2\EstateType;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageS3UploadEstate extends Job
{
    private $estate_id;
    private $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $file,$estate_id)
    {
        $this->estate_id = $estate_id;
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $path = $this->file->store('images', 's3');


        $atta = New AttachmentEstate();
        $atta->estate_id = $this->estate_id;
        $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        $atta->save();

    }
}
