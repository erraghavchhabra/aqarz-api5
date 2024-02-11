<?php

namespace App\Jobs;

use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\NebInterest;
use App\Models\v2\NotificationUser;
use App\Models\v2\RequestFund;
use App\User;
use Illuminate\Support\Facades\Log;

class NotificationInterset extends Job
{
    private $estate_request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RequestFund $estate_request)
    {
        $this->estate_request = $estate_request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $estate_fund_request = $this->estate_request;


        if (isset($estate_fund_request)) {


            $neb_name = $estate_fund_request->neighborhood_name;



            $neb_name_array=[];
            if ($neb_name) {
                $neb_name_array=    explode(',', $neb_name);
            }
            $interst = NebInterest::WhereHas('neb', function ($query) use ($neb_name_array) {
                $query->whereIn('name_ar', $neb_name_array);
            })->get();



            if($interst)
            {
                foreach ($interst as $interstItem) {

                    $client = User::where('id', $interstItem->user->id)->first();



                    if ($client) {
                        $push_data = [
                            'title' => 'تم اضافة طلب جديد' . $estate_fund_request->id,
                            'body' => 'يمكنك الاطلاع على الطلب الجديد المضاف من نوع ' . $estate_fund_request->estate_type_name.' في حي '.$neb_name,
                            'id' => $estate_fund_request->id,
                            'user_id' => $client->id,
                            'type' => 'request',
                        ];

                        $note = NotificationUser::create([
                            'user_id' => $client->id,
                            'title' => 'يمكنك الاطلاع على الطلب الجديد المضاف من نوع ' . $estate_fund_request->estate_type_name.' في حي '.$neb_name,
                            'type' => 'request',
                            'type_id' =>  $estate_fund_request->id,
                        ]);


                        send_push($client->device_token, $push_data, $client->device_type);

                        Log::channel('slack')->info(['data'=>$note,'msg'=>'from job neb intr']);


                       // dd(4444);

                    }

                }
            }

        }
        //  $user_mobile = checkIfMobileStartCode( $this->user->mobile,  $this->user->country_code);


    }
}
