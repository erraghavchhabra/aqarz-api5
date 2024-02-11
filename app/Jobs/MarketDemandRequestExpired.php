<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\v3\EstateRequest;
use App\Models\v3\RequestFund;
use App\Models\v2\AreaEstate;
use App\Models\v2\City;
use App\Models\v2\Estate;
use App\Models\v2\EstatePrice;
use App\Models\v2\FundRequestHasOffer;
use App\Models\v2\FundRequestNeighborhood;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\FundRequestSmsStatus;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PDF;


class MarketDemandRequestExpired extends Job
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


        $request_request = EstateRequest::whereDate(
            'created_at', '>=', Carbon::now()->subDays(60)->format('Y-m-d'))
            ->where('status', 'new')
            ->get();


        foreach ($request_request as $requestItem) {

            $user = User::where('id', $requestItem->user_id)
                ->first();

            if ($user) {
                if($user->count_request>0)
                {
                    $user->count_request = $user->count_request - 1;
                    $user->save();
                }

            }

            $requestItem->delete();
            Log::channel('slack')->info(json_encode($requestItem));
            Log::channel('single')->info(json_encode($requestItem));
        }


    }
}
