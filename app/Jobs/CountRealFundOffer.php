<?php

namespace App\Jobs;


use App\Models\v2\Client;
use App\Models\v2\Employee;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\RequestFund;
use App\Models\v2\RequestOffer;
use App\Models\v3\Content;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CountRealFundOffer extends Job
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

        $offerReal = FundRequestOffer::
        whereHas('provider')->whereHas('estate')->whereHas('fund_request')
            ->count();



        $setting=Content::where('key','offerReal')->first();


        //   ->update(['value_ar',$offerReal,'value_en'=>$offerReal]);

        $setting->value_ar=$offerReal;
        $setting->value_en=$offerReal;
        return true;
    }
}
