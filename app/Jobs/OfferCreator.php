<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\v2\RequestFund;
use App\Models\v2\AreaEstate;
use App\Models\v2\City;
use App\Models\v2\Estate;
use App\Models\v2\EstatePrice;
use App\Models\v2\FundRequestHasOffer;
use App\Models\v2\FundRequestNeighborhood;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\FundRequestSmsStatus;
use Illuminate\Support\Facades\Log;
use PDF;


class OfferCreator extends Job
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

        Log::channel('slack')->info(['data'=>[],'msg'=>'offer createor run in server']);

        $fund_request = RequestFund::get();
        $i = 0;
        $cityArray = ['4353', '2509', '4356'];

        //الدمام,
        //الخبر
        //الظهران
        $typeEstateArray = ['2', '4'];
        foreach ($fund_request as $fundItem) {
            $city = City::where('serial_city', $fundItem->city_id)->first();
            $cityArrayResult = null;
            if (in_array($city->city_id, $cityArray)) {
                $cityArrayResult = City::whereIn('serial_city', $cityArray)->pluck('state_id');
            }
            $area_range = AreaEstate::find($fundItem->area_estate_id);
            $price_range = EstatePrice::find($fundItem->estate_price_id);
            $estate = Estate::whereHas('EstateFile')
                ->where('in_fund_offer', '1')
                ->where('operation_type_id','=' ,1)
                ->where('status', 'completed')
                ->where('in_fund_offer', '=', '1')
                ->where('is_from_rent_system', '0')
                ->where('total_price', '<=', ($price_range->estate_price_to + ($price_range->estate_price_to * 0.15)))
                ->where('total_area', '<=', ($area_range->area_to + ($area_range->area_to * 0.15)));
            $EstateArrayResult = null;
            if (in_array($fundItem->estate_type_id, $typeEstateArray)) {
                $EstateArrayResult = $typeEstateArray;
            }

            if (isset($EstateArrayResult)) {
                $estate = $estate->whereIn('estate_type_id', $EstateArrayResult);
            } else {
                $estate = $estate->where('estate_type_id', $fundItem->estate_type_id);
            }

            $neb = FundRequestNeighborhood::where('request_fund_id', $fundItem->id)->first();

            if (isset($neb)) {
                $nebArray = FundRequestNeighborhood::where('request_fund_id', $fundItem->id)->pluck('neighborhood_id');

                $estate = $estate->whereIn('neighborhood_id', $nebArray->toArray());
            }
            if (isset($cityArrayResult)) {

                $estate = $estate->whereIn('city_id', $cityArrayResult->toArray());
            } else {
                //dd(444);
                $estate = $estate->where('city_id', $fundItem->city_id);

            }

            $estate = $estate->get();
            foreach ($estate as $estateItem) {


                $checkOffer = FundRequestOffer::where('uuid', $fundItem->uuid)
                    ->first();
                if (!$checkOffer) {
                    $content = file_get_contents(url("api/sms/send?uuid=" . trim($fundItem->uuid) . ""));


                    $data = json_decode($content);
                    // if ($data->status == false) {


                    //  return response()->error($data->msg, []);
                    // }
                    $msg = FundRequestSmsStatus::create([
                        'uuid' => $fundItem->uuid,
                        'request_id' => $fundItem->id,
                        'status' => $data->status,
                        'error_msg' => $data->msg,
                        'code' => $data->code,
                        'type' => 'send_sms',

                    ]);
                 //   Log::channel('slack')->info(json_encode($data->msg));
                    //   Log::channel('slack')->info(json_encode($request->all()));
                    //   Log::channel('slack')->info(json_encode($data));
                }

                $checkEstate = FundRequestOffer::where('uuid', $fundItem->uuid)
                    ->where('estate_id', $estateItem->id)->first();
                if (!$checkEstate) {
                    $i++;
                    $FundRequestOffer = FundRequestOffer::create([
                        'uuid' => $fundItem->uuid,
                        'provider_id' => $estateItem->user_id,
                        'estate_id' => $estateItem->id,
                        'status' => 'new',
                        'send_offer_type' => 'system',
                        'app_name' => 'عقارز',
                        'request_id' => $fundItem->id,


                    ]);


                    if ($FundRequestOffer) {


                        $checkHasOffer = FundRequestHasOffer::where('uuid', $FundRequestOffer->uuid)->first();

                        if (!$checkHasOffer) {
                            $FundRequestOffer = FundRequestHasOffer::create([
                                'uuid' => $FundRequestOffer->uuid,
                            ]);
                        }

                        /*   $estateItem->count_offers = $estateItem->count_offers+1;
                           $estateItem->save();*/


                    }
                }


            }
            $fundItem->count_offers = $fundItem->count_offers + $i;
            $fundItem->save();
           // Log::channel('slack')->info(['data'=>json_encode($fundItem),'msg'=>'offer createor run in server']);
           // Log::channel('single')->info(json_encode($fundItem));
        }


    }
}
