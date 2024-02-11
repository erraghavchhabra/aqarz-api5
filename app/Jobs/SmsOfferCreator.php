<?php

namespace App\Jobs;


use App\Helpers\JsonResponse;
use App\Models\v2\FundRequestSmsStatus;
use App\Models\v2\Invoice;
use App\Models\v2\RequestFund;
use App\Models\v2\UserPayment;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PDF;


class SmsOfferCreator extends Job
{
    private $req;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RequestFund $req)
    {
        $this->req = $req;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

       // $content = file_get_contents(url("api/sms/send?uuid=" .  $this->req->uuid . ""));





        $url = 'https://eservices.redf.gov.sa/Aqarz/api/Aqarz/SendSMSwhenReadyOffers';

        $data = [
            'uuid' =>trim($this->req->uuid),


        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_HEADER, 0);




        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Authorization: Basic YXFhcnpfcDpAcjNRcnojI3V5ITE3',

        ]);



        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'aqarz_p' . ":" . '@r3Qrz##uy!17');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result != false) {
            $headerArr = explode(PHP_EOL, $headerstring);
            foreach ($headerArr as $headerRow) {
                preg_match('/([a-zA-Z\-]+):\s(.+)$/', $headerRow, $matches);
                if (!isset($matches[0])) {
                    continue;
                }
                $header[$matches[1]] = $matches[2];
            }

//return [json_decode($body)];
         /*   return [
                'code'   => (json_decode($body)->code),
                'msg'    => (json_decode($body)->message),
                'status' => json_decode($body)->status
            ];*/




          //  $data = json_decode($content);



                Log::channel('slack')->info([
                    'uuid'=>$this->req->uuid,
                    'status'=>json_decode($body)->status,
                    'msg'=>json_decode($body)->message,
                    'code'=>json_decode($body)->code,

                ]);



                $msg=FundRequestSmsStatus::create([
                    'uuid'=>$this->req->uuid,
                    'request_id'=>$this->req->id,
                    'status'=>json_decode($body)->status,
                    'error_msg'=>json_decode($body)->message,
                    'code'=>json_decode($body)->code,

                ]);


        }


        else
        {
            Log::channel('slack')->info([
                'uuid'=>$this->req->uuid,
                'status'=>'no_connect',
                'msg'=>'no_connect',
                'code'=>'no_connect',

            ]);



            $msg=FundRequestSmsStatus::create([
                'uuid'=>$this->req->uuid,
                'request_id'=>$this->req->id,
                'status'=>0,
                'error_msg'=>'no_connect',
                'code'=>'no_connect',

            ]);
        }





     //   Log::channel('single')->info(json_encode($data));

      //  Log::channel('single')->info($data);
       // Log::channel('slack')->info($data);


    }
}
