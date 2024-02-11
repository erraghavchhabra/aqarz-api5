<?php

namespace App\Jobs;



use App\Models\v3\FundRequestNeighborhood;
use App\Models\v3\RequestFund;

use App\User;
use Illuminate\Support\Facades\Log;


class NebAssertRequestFund extends Job
{

    private $fund_request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RequestFund $fund_request)
    {
        $this->fund_request = $fund_request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $neb = FundRequestNeighborhood::where('request_fund_id', $this->fund_request->id)->pluck('neighborhood_id');

        Log::channel('slack')->info(['msg'=>'write the neb ids','data'=>$neb]);
        if ($neb) {
            $neb=implode(',',$neb->toArray());
            $this->fund_request->fund_request_neighborhoods=$neb;
            $this->fund_request->save();
        }


    }
}
