<?php

namespace App\Jobs;

use App\Models\v3\Estate;
use App\Models\v3\EstateRequest;
use App\Models\v3\NotificationUser;
use App\Models\v4\FcmToken;
use App\User;

class NotificationProvider extends Job
{
    private $estate_request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EstateRequest $estate_request)
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

        $estate=Estate::where('neighborhood_id',$this->estate_request->neighborhood_id)->pluck('user_id');


        if(isset($estate))
        {


            for ($i=0;$i<count($estate->toArray());$i++)
            {

                $client=User::where('id',$estate->toArray()[$i])->first();

                if ($client) {
                    $push_data = [
                        'title'   =>  'لديك طلب جديد #' . $this->estate_request->id,
                        'body'    =>  'لديك طلب جديد #' . $this->estate_request->id,
                        'id'      => $this->estate_request->id,
                        'user_id' => $client->id,
                        'type'    => 'request',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $client->id,
                        'title'   => 'لديك طلب جديد #' . $this->estate_request->id,
                        'type'    => 'request',
                        'type_id' =>  $this->estate_request->id,
                    ]);
                    $fcm_token = FcmToken::where('user_id', $client->id)->get();
                    foreach ($fcm_token as $token) {
                        send_push($token->token, $push_data, $token->type);
                    }
                 return;
                }

            }
        }
      //  $user_mobile = checkIfMobileStartCode( $this->user->mobile,  $this->user->country_code);


    }
}
