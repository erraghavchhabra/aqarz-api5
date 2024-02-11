<?php

namespace App\Jobs;


use App\Models\v3\Favorite;
use App\Models\v3\FundRequestNeighborhood;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\RequestFund;

use App\User;
use Illuminate\Support\Facades\Log;


class AssertIdtoRequestFundOffer extends Job
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

       $offer=FundRequestOffer::whereHas('estate')
           ->whereHas('provider')
           ->whereHas('fund_request')
           ->where('request_id','=',null)
           ->get();

        foreach ($offer as $offerItem) {


            $offer = RequestFund::
                where('uuid',$offerItem->uuid)
                ->first();

            if($offer)
            {
                $offerItem->request_id = $offer->id;
                $offerItem->save();
            }

        }




    }
}
