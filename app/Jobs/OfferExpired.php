<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\v3\RequestFund;
use App\Models\v2\AreaEstate;
use App\Models\v2\City;
use App\Models\v2\Estate;
use App\Models\v2\EstatePrice;
use App\Models\v2\FundRequestHasOffer;
use App\Models\v2\FundRequestNeighborhood;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\FundRequestSmsStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PDF;


class OfferExpired extends Job
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


        $fund_request = FundRequestOffer::whereDate(
            'created_at', '>=', Carbon::now()->subDays(30)->format('Y-m-d'))
            ->where('status', 'new')
            ->get();


        foreach ($fund_request as $fundItem) {

            $fund_request_request = RequestFund::where('uuid', $fundItem->uuid)
                ->first();

            if ($fund_request_request) {
                $fund_request_request->count_expired_offer = $fund_request_request->count_expired_offer + 1;
                $fund_request_request->save();
            }
            $fundItem->status = 'expired';
            $fundItem->save();
            Log::channel('slack')->info(json_encode($fundItem));
            Log::channel('single')->info(json_encode($fundItem));
        }


    }
}
