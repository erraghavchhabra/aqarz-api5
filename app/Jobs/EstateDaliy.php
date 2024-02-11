<?php

namespace App\Jobs;


use App\Imports\EstateDaliyExport;
use App\Models\v3\DaliyEstateFile;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use Excel;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class EstateDaliy extends Job
{



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
        $date = date_create(now());
        $date = date_format($date, "Y-m-d");

      //  $logDate = date("YmdHis");
        $logDate = date("Ymd");
      //  $logDate = '20210907';
       // $logDateTime = strtotime(now());
        $logDateTime = date("His");
        //  Excel::store(new EstateDaliyExport(), 'invoices.xlsx');
        $name = 'Aqarz_'.$logDate.'_'.$logDateTime;
     /*   $path = Excel::store(new EstateDaliyExport(), '\\estate_daily\\' . $name . '.csv', 'estate', 'Csv', [
            'Content-Type' => 'text/csv']);*/
    //   Excel::store(new EstateDaliyExport(),'\\estate_daily\\' . $name . '.xlsx', 'estate');
        $path_ftp = Excel::store(new EstateDaliyExport(), '\\estate_daily\\' . $name . '.csv', 'aqarz-ftp', 'Csv', [
            'Content-Type' => 'text/csv']);

//dd(4444);
        if ($path_ftp) {


            $unifonicMessage = new UnifonicMessage();
            $unifonicClient = new UnifonicClient();
            $unifonicMessage->content = "تم ارسال الملف اليومي لسيرفر عقارز اسم الملف";
            $to = '9665415555111';
            $co = $name.'تم ارسال الملف اليومي لسيرفر عقارز اسم الملف ';
            $data = $unifonicClient->sendSms1($to, $co);
            Log::channel('single')->info($data);
            Log::channel('slack')->info($data);


            $record = DaliyEstateFile::create([
                'status' => '1',
                'file_path' => 'estate_daily\\' . $name . '.csv',
            ]);


            Log::channel('slack')->info(['data' => $record, 'msg' => 'daliy estate report for : '.$name]);

        } else {
            Log::channel('slack')->info(['data' => [], 'msg' => 'not save daliy estate report for : '.$name]);

        }

        // $path= Excel::store(new EstateDaliyExport,'EstateDailyReport_' .$date. '_.csv', 's3' );
//        store($export, string $filePath, string $diskName = null, string $writerType = null, $diskOptions = [])
        /* $path=   Excel::store(new EstateDaliyExport('EstateDailyReport_' .$date.'_'.rand(0,10).'_.csv'), 'estate_daily', 'estate', 'Csv',[
                'Content-Type' => 'text/csv']);




            dd($path,'EstateDailyReport_' .$date.'_'.rand(0,10).'_.csv');
         /*   return Excel::download(new EstateDaliyExport(),
                'EstateDailyReport_' .$date. '_.csv', Excel::CSV);
            return (new EstateDaliyExport(),'EstateDailyReport_' .$date. '_.csv', Excel::CSV)->store('invoices.xlsx', 's3');
    */

    }
}
