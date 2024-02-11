<?php

namespace App\Jobs;


use App\Models\v2\Client;
use App\Models\v2\Employee;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\RequestFund;
use App\Models\v2\RequestOffer;
use App\Models\v3\City;
use App\Models\v3\Neighborhood;
use App\User;
use Illuminate\Support\Facades\Log;


class CounterCreatorCity extends Job
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

        $estate = Estate::get();
        $estate = Estate::whereIn('status',['completed,expired']);




        foreach ($estate as $estateItem) {

            $city = City::where('serial_city', $estateItem->city_id)->first();
            if ($city) {
                $city->count_app_estate = $city->count_app_estate - 1;
                $city->save();
            }
            $neb = Neighborhood::where('neighborhood_serial', $estateItem->neighborhood_id)->first();
            if ($neb) {
                $neb->estate_counter = $neb->estate_counter - 1;
                $neb->save();
            }




            Log::channel('slack')->info(['data'=>$estateItem,'msg'=>'from job']);
        }

    }
}
