<?php

namespace App\Jobs;


use App\Models\v2\Client;
use App\Models\v2\Employee;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\FundRequestHasOffer;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\RequestFund;
use App\Models\v2\RequestOffer;
use App\User;
use Illuminate\Support\Facades\Log;


class DeleteDumyData extends Job
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

        $dumyOffer = FundRequestOffer::whereDoesntHave('provider')
            ->orwhereDoesntHave('estate')
            ->orwhereDoesntHave('fund_request')
            ->get();


      //  dd(count($dumyOffer));

        $uuid = '';
        $id = '';


        foreach ($dumyOffer as $dumyOfferItem) {

            $uuid = $dumyOfferItem->uuid;
            $id = $dumyOfferItem->id;


            $dumyOfferItem->delete();
            $checkIfReminOffer = FundRequestOffer::whereHas('provider')
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->where('uuid', $uuid)
                ->first();
            if (!$checkIfReminOffer) {
                FundRequestHasOffer::where('uuid', $uuid)->delete();
            }
            Log::channel('slack')->info(['data' => $id, 'msg' => 'from job delete dumy data']);
            Log::channel('single')->info(['data' => $id, 'msg' => 'from job delete dumy data']);
        }

    }
}
