<?php

namespace App\Jobs;


use App\Models\v3\Favorite;
use App\Models\v3\FundRequestNeighborhood;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\RequestFund;

use App\User;
use Illuminate\Support\Facades\Log;


class OfferUserRequestFund extends Job
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

       $user=User::query()->get();
        foreach ($user as $userItem) {


            $offer = FundRequestOffer::whereHas('estate')
                ->whereHas('provider')
                ->whereHas('fund_request')
                ->where('provider_id',$userItem->id)
                ->pluck('request_id');


            $offerItem = implode(',', $offer->toArray());
            $userItem->fund_request_offer = $offerItem;
            $userItem->save();
        }




    }
}
