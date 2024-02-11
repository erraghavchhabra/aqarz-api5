<?php

namespace App\Jobs;


use App\Models\v2\City;
use App\Models\v2\Client;
use App\Models\v2\Employee;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\RequestFund;
use App\Models\v2\RequestOffer;
use App\User;
use Illuminate\Support\Facades\Log;


class CounterCityCreator extends Job
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

        $city = City::get();




        for ($i = 0; $i < count($city); $i++) {


            $requests_fund = RequestFund::WhereHas('city', function ($query) use ($city, $i) {
                $query->where('serial_city', $city[$i]->serial_city);

            })->count();

            $requests_app = EstateRequest::WhereHas('city', function ($query) use ($city, $i) {
                $query->where('serial_city', $city[$i]->serial_city);

            })->count();

            $estate_app = Estate::WhereHas('city', function ($query) use ($city, $i) {
                $query->where('serial_city', $city[$i]->serial_city);

            })->count();
            $city[$i]->count_fund_request = $requests_fund;
            $city[$i]->count_app_request = $requests_app;
            $city[$i]->count_app_estate = $estate_app;

            $city[$i]->save();


        }

    }
}
