<?php

namespace App\Jobs;


use App\Models\v3\Client;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\EstateRequest;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\RequestFund;
use App\Models\v3\RequestOffer;
use App\User;
use Illuminate\Support\Facades\Log;


class CounterCreator extends Job
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

        $user = User::get();
          //where('type', 'provider')
        //    ->where('name','!=',null)
           // ->where('id','=',1770)





        foreach ($user as $userItem) {
            $count_estate = Estate::where('user_id', $userItem->id)->count();
            $count_request = EstateRequest::where('user_id', $userItem->id)->count();
            $count_offer = RequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('request')
                ->count();



            $count_client = Client::where('user_id', $userItem->id)->count();
            $count_accept_offer = RequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('request')
                ->where('status', 'accepted_customer')
                ->count();
            $count_accept_fund_offer = FundRequestOffer::where('provider_id', '142')
                ->where('status', 'accepted_customer')
                ->whereHas('provider')
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->count();


            $count_preview_fund_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->where('status', 'sending_code')
                ->whereHas('provider')
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->count();
            $count_fund_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->whereHas('provider')
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->count();



            $count_fund_pending_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->where('status', 'new')
               // ->orwhere('status', null)
                ->count();



            $array_fund_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->pluck('uuid');

            $count_fund_request=RequestFund::whereIn('uuid',$array_fund_offer->toArray())->count();


            $countEmp = Employee::where('user_id', $userItem->id)
               // ->whereIn('is_employee', [1, 2])
                ->count();

         /*   $countEmp = User::where('is_employee', 1)
                ->where('employer_id',$userItem->id)->count();
*/
            $userItem->update(
                [
                    'count_offer' => $count_offer,
                    'count_fund_pending_offer' => $count_fund_pending_offer,
                    'count_request' => $count_request,
                    'count_client' => $count_client,
                    'count_fund_offer' => $count_fund_offer,
                    'count_estate' => $count_estate,
                    'count_accept_offer' => $count_accept_offer,
                    'count_preview_fund_offer' => $count_preview_fund_offer,
                    'count_fund_request' => $count_fund_request,
                    'count_accept_fund_offer' => $count_accept_fund_offer,
                    'count_emp' => $countEmp,

                ]);


            Log::channel('slack')->info(['data'=>$userItem,'msg'=>'from job']);
        }

    }
}
