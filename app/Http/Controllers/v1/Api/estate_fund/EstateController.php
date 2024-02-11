<?php

namespace App\Http\Controllers\v1\Api\estate_fund;

use App\Events\CreateRequestOfferEvent;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\RequestFundOfferResource;

use App\Models\v1\FundRequestOffer;
use App\Models\v2\RequestFund;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class EstateController extends Controller
{



    public function fund_requests(Request $request)
    {



        dd(4444);

        $user = auth()->guard('Fund')->user();
        if ($user == null) {
            return response()->error("not authorized");
        }
        $rules = Validator::make($request->all(), [


            'uuid' => 'required|unique:request_funds',
            // 'estate_type_id' => 'required',
            'estate_type_id'          => 'sometimes|required|exists:estate_types,id',
            // 'estate_status'    => 'required',
            'estate_status' => 'required|in:1,2',
            //  'area_estate_id' => 'required',
            'area_estate_id'          => 'sometimes|required|exists:area_estates,id',
            //  'dir_estate_id'             => 'required',
            'dir_estate_id' => 'required|in:1,2,3,4',
            // 'estate_price_id'             => 'required',
            'estate_price_id'          => 'sometimes|required|exists:estate_prices,id',
            //   'street_view_id'            => 'required',
            'street_view_id'          => 'sometimes|required|exists:street_views,id',
            //  'rooms_number_id'          => 'required',
            'rooms_number_id' => 'required|in:1,2,3,4,5,6',
            // 'city_id'          => 'required',
            'city_id'          => 'sometimes|required|exists:cities,serial_city',
            'neighborhood_id'          => 'sometimes|required|exists:neighborhoods,neighborhood_serial',
        //    "neighborhood_id" => "sometimes|required|array|min:1",
        //    'neighborhood_id.*'          => 'sometimes|required|exists:neighborhoods,neighborhood_serial',



        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request->merge([

            // 'user_id' => $user->id,
        ]);
        $RequestFund = RequestFund::create($request->only([
            'uuid',
            'estate_type_id',
            'estate_status',
            'area_estate_id',
            'dir_estate_id',
            'estate_price_id',
            'street_view_id',
            'rooms_number_id',
            'city_id',
            'neighborhood_id',
            'status',
        ]));


        CreateRequestOfferEvent::dispatch($RequestFund);

        $DeferredInstallment = RequestFund::find($RequestFund->id);
        return response()->success("RequestFund ", $DeferredInstallment);
        // return ['data' => $user];
    }


    public function show_fund_requests(Request $request)
    {





        $user = auth()->guard('Fund')->user();

        if ($user == null) {
            return response()->error("not authorized");
        }
        $finiceing = RequestFund::query();


        if ($request->get('estate_type_id')) {
            $finiceing = $finiceing->where('estate_type_id', $request->get('estate_type_id'));
        }
        if ($request->get('area_estate_id')) {
            $finiceing = $finiceing->where('area_estate_id', $request->get('area_estate_id'));
        }
        if ($request->get('dir_estate_id')) {
            $finiceing = $finiceing->where('dir_estate_id', $request->get('dir_estate_id'));
        }
        if ($request->get('estate_price_id')) {
            $finiceing = $finiceing->where('estate_price_id', $request->get('estate_price_id'));
        }
        if ($request->get('city_id')) {
            $finiceing = $finiceing->where('city_id', $request->get('city_id'));
        }
        if ($request->get('neighborhood_id')) {
            $finiceing = $finiceing->where('neighborhood_id', $request->get('neighborhood_id'));
        }
        if ($request->get('street_view_id')) {
            $finiceing = $finiceing->where('street_view_id', $request->get('street_view_id'));
        }
        if ($request->get('neighborhood_id')) {
            $finiceing = $finiceing->where('neighborhood_id', $request->get('neighborhood_id'));
        }

        $finiceing = $finiceing->paginate();

        return response()->success("Fund Requests", $finiceing);

    }


    public function active_fund_requests($id)
    {


        $finiceing = RequestFund::find($id);

        if ($finiceing) {
            $finiceing->status = 1;
            $finiceing->save();
            return response()->success("RequestFund", $finiceing);

        } else {
            return response()->error("not found", []);
        }


    }

    public function deactive_fund_requests($id)
    {


        $finiceing = RequestFund::findOrFail($id);
        if ($finiceing) {
            $finiceing->status = 0;
            $finiceing->save();
            return response()->success("RequestFund", $finiceing);

        } else {
            return response()->error("not found", []);
        }


    }


    public function fund_request_offer(Request $request)
    {

        $rules = Validator::make($request->all(), [


            'uuid' => 'required',



        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


//1,2,3,4,5,6,7,8,9,10
        $finice = RequestFund::find($request->get('uuid'));


        if($finice)
        {
            $finice_offer_ids = explode(',', $finice->offer_numbers);
            if (!$finice) {
                return response()->error("not found", []);
            }

            // offer_numbers
            $FundRequestOffer = FundRequestOffer::where('uuid',
                $request->get('uuid'))
                ->whereNotIn('id', $finice_offer_ids)
                ->limit(5)->get();





            if (!$FundRequestOffer) {
                return response()->error("not found Offers", []);
            }


            $collection = RequestFundOfferResource::collection($FundRequestOffer);


            //     $stringOffer = implode(',', $finiceingOfferArray->toArray());
            //       $finice->offer_numbers = $finice->offer_numbers != null ? $finice->offer_numbers . ',' . $stringOffer : $stringOffer;
            //    $finice->save();

            return response()->success("Request Fund Offer", $collection);
        }
        else
        {
            return JsonResponse::fail('No Request Fund Find Wrong UUID', 400);
        }



    }


    public function reject_request_offer(Request $request)
    {

        $rules = Validator::make($request->all(), [


            'uuid' => 'required',
            'id' => 'required',



        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


//1,2,3,4,5,6,7,8,9,10
        $finice = RequestFund::findOrFail($request->get('uuid'));
        $finice_offer_ids = explode(',', $finice->offer_numbers);
        if (!$finice) {
            return response()->error("not found", []);
        }

        // offer_numbers
        $FundRequestOffer = FundRequestOffer::where('uuid',
            $request->get('uuid'))
            ->where('id', $request->get('id'))->first();

        if (!$FundRequestOffer) {
            return response()->error("not found Offers", []);
        }





        $finice->offer_numbers = $finice->offer_numbers != null ? $finice->offer_numbers . ',' . $FundRequestOffer->id : $FundRequestOffer->id;
        $finice->save();

        return response()->success("تم رفض العرض بنجاح", []);

    }



    public function reset_offer(Request $request)
    {
        $finice = RequestFund::find($request->get('uuid'));

        if (!$finice) {
            return response()->error("not found", []);
        }
        $finice->offer_numbers = null;
        $finice->save();

        return response()->success("Display Offers Are Empty", []);
    }




    public function accept_offer(Request $request)
    {



        $rules = Validator::make($request->all(), [


            'uuid' => 'required',
           // 'instument_number' => 'required',
          //  'guarantees' => 'required',
            'beneficiary_name' => 'required',
            'beneficiary_mobile' => 'required',
            'id' => 'required',



        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }



        $offer = FundRequestOffer::findOrFail($request->get('id'));

        if(!$offer)
        {
            return response()->error("not found offer", []);
        }
        $finice = RequestFund::findOrFail($offer->uuid);
        $offer->status='active';
       // $offer->instument_number=$request->get('instument_number');
      //  $offer->guarantees=$request->get('guarantees');
        $offer->beneficiary_name=$request->get('beneficiary_name');
        $offer->beneficiary_mobile=$request->get('beneficiary_mobile');
        $offer->save();


        $deleteOffer=FundRequestOffer::where('id','!=',$offer->id)
            ->where('uuid',$offer->uuid)->delete();
        $finice->status='Waiting_provider_accepted';
        $finice->offer_numbers=null;
        $finice->save();
        return response()->success("Offer Accepted", []);

    }
}
