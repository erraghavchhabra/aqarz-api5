<?php

namespace App\Jobs;


use App\Imports\EstateDaliyExport;
use App\Models\v3\AttachmentEstate;
use App\Models\v3\DaliyEstateFile;
use App\Models\v3\Estate;
use Excel;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class ResizeEstateImage extends Job
{

    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $att = Estate::where('is_updated_image', 0)
            ->where('first_image', '!=', null)
            ->get();


        foreach ($att as $attItem) {


            $temp = public_path('temp/');
            $path_thumbnail = public_path('uploads/thumbnail/');
            $content = file_get_contents($attItem->first_image);

            if ($content) {
                $extension = explode('/', $attItem->first_image);
                $extension = explode('.', $extension[count($extension) - 1]);
                $photo = str_random(32) . '.' . $extension[count($extension) - 1];
                $thumbnail = str_random(32) . '_thumbnail' . '.' . $extension[count($extension) - 1];

                file_put_contents($temp . $photo, $content);


//dd(getimagesize(public_path('/ESt4TFMYnLaIbR2wx78O1QkKCPIBdoxT.jpg')));


                $original = $temp . $photo;
                $width = getWidth($original);
                $height = getHeight($original);

                if ($width > $height) {

                    if ($width > 1280) : $_scale = 1280;
                    else: $_scale = 900; endif;

                    // Thumbnail
                    $scaleT = 280 / $width;
                    $uploaded = resizeImage($original, $width, $height, $scaleT, $temp . $thumbnail);

                } else {

                    if ($width > 1280) : $_scale = 960;
                    else: $_scale = 800; endif;


                    // Thumbnail
                    $scaleT = 190 / $width;
                    $uploaded = resizeImage($original, $width, $height, $scaleT, $temp . $thumbnail);

                }
                $s3filePath = '/assets/' . $thumbnail;
                $s3 = \Storage::disk('s3');

# finally upload your file to s3 bucket
                $test = $s3->put($s3filePath, file_get_contents($temp . $thumbnail), 'public');
                \File::delete($temp . $thumbnail);
                \File::delete($temp . $photo);
                $path = 'https://aqarz.s3.me-south-1.amazonaws.com' . $s3filePath;

                $attItem->first_image = $path;
                $attItem->is_updated_image = 1;
                $attItem->save();
                Log::channel('slack')->info(['data' => $attItem, 'msg' => 'update estate first image : ' . $attItem->id]);
            }


        }

    }



}
