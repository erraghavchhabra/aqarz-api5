<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

class CreateRequestOffer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $requestFund;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 2;
    public function __construct(RequestFund $requestFund)
    {
        $this->requestFund = $requestFund;
    }

    public function failed(Exception $exception)
    {
        Log::channel('slack')->info(['msg'=>'fail offer job','errot'=>json_encode($exception)]);
        // Send user notification of failure, etc...
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fundItem = $this->requestFund;
        $cityArray = ['4353', '2509', '4356'];
        $typeEstateArray = ['2', '4'];
        $counter = 0;
        $city = City::where('serial_city', $fundItem->city_id)->first();
            $cityArrayResult = null;
            if (in_array($city->city_id, $cityArray)) {
                $cityArrayResult = City::whereIn('serial_city', $cityArray)->pluck('state_id');
            }
            $area_range = AreaEstate::find($fundItem->area_estate_id);
            $price_range = EstatePrice::find($fundItem->estate_price_id);
            $estate = Estate::whereHas('EstateFile')
                ->where('status', 'completed')
                ->where('in_fund_offer', '=', '1') // يكون من 1من عقارات الصندوق
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

// ارسال رسالة لاول عرض فقط لكل العقارات
                $checkOffer = FundRequestOffer::where('uuid', $fundItem->uuid)
                    ->first();
                if (!$checkOffer) {
                    $content = file_get_contents(url("api/sms/send?uuid=" . trim($fundItem->uuid) . ""));


                    $data = json_decode($content);
                    // if ($data->status == false) {


                    //  return response()->error($data->msg, []);
                    // }
                    // حفظ الرسائل المرسلة للتاكد منها
                    $msg = FundRequestSmsStatus::create([
                        'uuid' => $fundItem->uuid,
                        'request_id' => $fundItem->id,
                        'status' => $data->status,
                        'error_msg' => $data->msg,
                        'code' => $data->code,
                        'type' => 'send_sms',

                    ]);
                   // Log::channel('slack')->info(json_encode($data->msg));
                    //   Log::channel('slack')->info(json_encode($request->all()));
                    //   Log::channel('slack')->info(json_encode($data));
                }
 //انشاء العروض من خلال النظام
                $checkEstate = FundRequestOffer::where('uuid', $fundItem->uuid)
                    ->where('estate_id', $estateItem->id)->first();
                if (!$checkEstate) {
                    $counter ++;
                    $FundRequestOffer = FundRequestOffer::create([
                        'uuid' => $fundItem->uuid,
                        'provider_id' => $estateItem->user_id,
                        'estate_id' => $estateItem->id,
                        'status' => 'new',
                        'send_offer_type' => 'system',
                        'app_name' => 'عقارز',
                        'request_id' => $fundItem->id,


                    ]);

 //تخزين العقارات الي الها عروض
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
            $fundItem->count_offers = $fundItem->count_offers + $counter;
            $fundItem->save();
           // Log::channel('slack')->info(json_encode($fundItem));
            //Log::channel('single')->info(json_encode($fundItem));
    }
}
