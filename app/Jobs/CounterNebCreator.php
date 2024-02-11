<?php

namespace App\Jobs;


use App\Models\v2\City;
use App\Models\v2\Client;
use App\Models\v2\Employee;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\Neighborhood;
use App\Models\v2\RequestFund;
use App\Models\v2\RequestOffer;
use App\User;
use Illuminate\Support\Facades\Log;


class CounterNebCreator extends Job
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

        $city = Neighborhood::get();




        for ($i = 0; $i < count($city); $i++) {



            $city[$i]->request_fund_counter = @count($city[$i]->requestFund);
            $city[$i]->request_app_counter = @count($city[$i]->app_request);
            $city[$i]->estate_counter = @count($city[$i]->estate);

            $city[$i]->save();


        }

    }
}
