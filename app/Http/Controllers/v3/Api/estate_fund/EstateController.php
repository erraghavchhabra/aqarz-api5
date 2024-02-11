<?php

namespace App\Http\Controllers\v3\Api\estate_fund;

use App\Events\CreateRequestOfferEvent;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\OfferAttachDateDataResource;
use App\Http\Resources\OfferDateDataResource;
use App\Http\Resources\RequestFundOfferResource;

use App\Http\Resources\UserDateDataResource;
use App\Models\v3\AreaEstate;
use App\Models\v3\City;
use App\Models\v3\EstatePrice;
use App\Models\v3\EstateType;
use App\Models\v3\Neighborhood;
use App\Models\v3\Estate;
use App\Models\v3\FundRequestHasOffer;
use App\Models\v3\FundRequestNeighborhood;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\NotificationUser;
use App\Models\v3\RequestFund;
use App\Models\v3\StreetView;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;


class EstateController extends Controller
{

    public function cancel_fund_offer($id)
    {


        $user = auth()->guard('Fund')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $fund_offer = FundRequestOffer::findOrFail($id);


        if ($fund_offer) {
            $requests = RequestFund::where('uuid', $fund_offer->uuid)->first();
            $fund_offer->reason = 'العرض غير مناسب';
            //  $fund_offer->status = 'close';
            $fund_offer->is_close = 1;
            $fund_offer->save();

            if ($fund_offer->status == 'sending_code') {


                if ($requests) {
                    $requests->status = 'new';
                    $requests->delete();
                }

            }

            $estate = Estate::find($fund_offer->estate_id);


            if ($estate) {
                $note = NotificationUser::create([
                    'user_id' => 3417,
                    'title' => __('views.You Offer Rejected  #') . $fund_offer->id,
                    'type' => 'fund_offer',
                    'type_id' => $fund_offer->id,
                ]);
                $client = User::where('id', 3417)->first();
                $push_data = [
                    'title' => __('views.You Offer Rejected  #') . $fund_offer->id,
                    'body' => 'العرض غير مناسب',
                    'id' => $fund_offer->id,
                    'user_id' => 3417,
                    'type' => 'fund_offer',
                ];
                if ($client) {
                    send_push($client->device_token, $push_data, $client->device_type);
                }
            }


        }

        return response()->success(__("views.Cancel Successfully"), []);
    }


    public function add_neb(Request $request)
    {


        $user = auth()->guard('Fund')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [


            'name_ar' => 'required',
            'name_en' => 'required',
            'lat' => 'required',
            'lan' => 'required',
            'status' => 'required',
            'neighborhood_serial' => 'required',
            'city_serial' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $Neighborhood = Neighborhood::create([
            'name_ar' => $request->get('name_ar'),
            'name_en' => $request->get('name_en'),
            'lat' => $request->get('lat'),
            'lan' => $request->get('lan'),
            'status' => $request->get('status'),
            'neighborhood_serial' => $request->get('neighborhood_serial'),
            'city_id' => $request->get('city_serial'),
        ]);


        /*   if($fund_offer->provider_id != $user->id)
           {
               return response()->error("the offer is not for this user", []);
           }*/


        return response()->success(__("views.Done"), $Neighborhood);
    }

    // انشاء طلب من خلال الصندوق
    public function fund_requests(Request $request)
    {


        /* $neb = FundRequestNeighborhood::query();
         foreach ($neb as $nebItem) {
             $check = Neighborhood::find($nebItem->neighborhood_id);
             if ($check) {
                 $nebItem->neighborhood_id = $check->neighborhood_serial;
                 $nebItem->save();
             }
         }*/

        $user = auth()->guard('Fund')->user();


        if ($user == null) {
            return response()->error("not authorized");
        }
        $rules = Validator::make($request->all(), [


            'uuid' => 'required',
            // 'estate_type_id' => 'required',
            'estate_type_id' => 'sometimes|required|exists:estate_types,id',
            // 'estate_status'    => 'required',
            'estate_status' => 'sometimes|required|in:1,2,3',
            //  'area_estate_id' => 'required',
            'area_estate_id' => 'sometimes|required|exists:area_estates,id',
            //  'dir_estate_id'             => 'required',
            'dir_estate_id' => 'required|in:1,2,3,4,5',
            // 'estate_price_id'             => 'required',
            'estate_price_id' => 'sometimes|required|exists:estate_prices,id',
            //   'street_view_id'            => 'required',
            'street_view_id' => 'sometimes|required|exists:street_views,id',
            //  'rooms_number_id'          => 'required',
            'rooms_number_id' => 'sometimes|required|in:1,2,3,4,5,6',
            // 'city_id'          => 'required',
            'city_id' => 'sometimes|required|exists:cities,serial_city',
            //   'neighborhood_id'          => 'required',
            //  'neighborhood_id'          => 'sometimes|required|exists:neighborhoods,neighborhood_serial',


            //     "neighborhood_id" => "sometimes|required",
            //  'neighborhood_id.*' => 'sometimes|required|exists:neighborhoods,neighborhood_serial',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $request->merge([
            'from_app' => true,
            //      'api_token' => hash('sha512', time()),
            'status' => 'new',
            'user_name' => $request->get('user_name') != null ? $request->get('user_name') : $user->user_name,
            'is_edit_username' => $request->get('user_name') ? '1' : '0',
        ]);


        $checkRequest = RequestFund::where('uuid', $request->get('uuid'))
            ->first();

        if ($checkRequest) {
            $checkRequest->update($request->only([
                'uuid',
                'estate_type_id',
                'estate_status',
                'area_estate_id',
                'dir_estate_id',
                'estate_price_id',
                'street_view_id',
                'rooms_number_id',
                'city_id',
                //  'neighborhood_id',
                'status',
            ]));


            $RequestFund = RequestFund::find($checkRequest->id);


            if ($RequestFund) {
                $array = explode(',', $request->get('neighborhood_id'));
                for ($i = 0; $i < count($array); $i++) {
                    $request_neighborhod = FundRequestNeighborhood::create([
                        'neighborhood_id' => $array[$i],
                        'request_fund_id' => $RequestFund->id,
                    ]);
                }

                if ($request->get('neighborhood_id')) {
                    $RequestFund->fund_request_neighborhoods = $RequestFund->fund_request_neighborhoods . ',' . $request->get('neighborhood_id');
                    $RequestFund->save();
                }
////////////////////////////////////////adding new attribute

                $nebName = \App\Models\v3\Neighborhood::whereIn('neighborhood_serial', $array)->pluck('name_ar');

                if ($nebName) {
                    $neb = implode(',', $nebName->toArray());
                    $RequestFund->neighborhood_name = $neb;
                    $RequestFund->save();
                }

                $estate_type_id = EstateType::find($RequestFund->estate_type_id);
                if ($estate_type_id) {
                    //   return $estate_type_id->name;

                    $RequestFund->estate_type_name = $estate_type_id->name_ar;
                    $RequestFund->estate_type_icon = $estate_type_id->icon;
                    $RequestFund->save();
                }

                $area_estate_id = AreaEstate::find($RequestFund->area_estate_id);
                if ($area_estate_id) {


                    $RequestFund->area_estate_range = $area_estate_id->area_range;
                    $RequestFund->save();

                }

                $estate_price_id = EstatePrice::find($RequestFund->estate_price_id);
                if ($estate_price_id) {

                    $RequestFund->estate_price_range = $estate_price_id->estate_price_range;
                    $RequestFund->save();
                }

                $street = StreetView::find($RequestFund->street_view_id);
                if ($street) {


                    $RequestFund->street_view_range = $street->street_view_range;
                    $RequestFund->save();
                }

                $city = City::where('serial_city', $RequestFund->city_id)->first();
                if ($city) {


                    $RequestFund->city_name = $city->name_ar;
                    $RequestFund->save();
                }
                $RequestFund->dir_estate = @dirctions($RequestFund->dir_estate_id);
                $RequestFund->save();


                $url = 'https://aqarz.sa/';


                $RequestFund->link = $url . 'fund/request/' . $RequestFund->id . '/show';
                $RequestFund->save();

//////////////////////////////////////
            }

            return response()->success("RequestFund ", $RequestFund);
        } else {

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
                //  'neighborhood_id',
                'status',
            ]));

            Log::channel('slack')->info(['data' => $RequestFund, 'msg' => 'from add new fund requst']);

            if ($RequestFund) {
                $array = explode(',', $request->get('neighborhood_id'));
                for ($i = 0; $i < count($array); $i++) {

                    $neb = Neighborhood::where('neighborhood_serial', $array[$i])->first();
                    if ($neb) {
                        $neb->request_fund_counter = $neb->request_fund_counter + 1;
                        $neb->save();

                        $RequestFund->fund_request_neighborhoods = $request->get('neighborhood_id');
                        $RequestFund->save();
                    }


                    $request_neighborhod = FundRequestNeighborhood::create([
                        'neighborhood_id' => $array[$i],
                        'request_fund_id' => $RequestFund->id,
                    ]);
                }


                ////////////////////////////////////////adding new attribute

                $nebName = \App\Models\v3\Neighborhood::whereIn('neighborhood_serial', $array)->pluck('name_ar');

                if ($nebName) {
                    $neb = implode(',', $nebName->toArray());
                    $RequestFund->neighborhood_name = $neb;
                    $RequestFund->save();
                }

                $estate_type_id = EstateType::find($RequestFund->estate_type_id);
                if ($estate_type_id) {
                    //   return $estate_type_id->name;

                    $RequestFund->estate_type_name = $estate_type_id->name_ar;
                    $RequestFund->estate_type_icon = $estate_type_id->icon;
                    $RequestFund->save();
                }

                $area_estate_id = AreaEstate::find($RequestFund->area_estate_id);
                if ($area_estate_id) {


                    $RequestFund->area_estate_range = $area_estate_id->area_range;
                    $RequestFund->save();

                }

                $estate_price_id = EstatePrice::find($RequestFund->estate_price_id);
                if ($estate_price_id) {

                    $RequestFund->estate_price_range = $estate_price_id->estate_price_range;
                    $RequestFund->save();
                }

                $street = StreetView::find($RequestFund->street_view_id);
                if ($street) {


                    $RequestFund->street_view_range = $street->street_view_range;
                    $RequestFund->save();
                }

                $city = City::where('serial_city', $RequestFund->city_id)->first();
                if ($city) {


                    $RequestFund->city_name = $city->name_ar;
                    $RequestFund->save();
                }
                $RequestFund->dir_estate = @dirctions($RequestFund->dir_estate_id);
                $RequestFund->save();


                $url = 'https://aqarz.sa/';


                $RequestFund->link = $url . 'fund/request/' . $RequestFund->id . '/show';
                $RequestFund->save();

//////////////////////////////////////


                Log::channel('slack')->info(['data' => $RequestFund, 'msg' => 'from add new fund requst neb added']);


            }


            CreateRequestOfferEvent::dispatch($RequestFund);


            $DeferredInstallment = RequestFund::find($RequestFund->id);
            $city = City::where('serial_city', $request->get('city_id'))->first();
            if ($city) {
                $city->count_fund_request = $city->count_fund_request + 1;
                $city->save();
            }

            dispatch(new \App\Jobs\NotificationInterset($DeferredInstallment));

            return response()->success("RequestFund ", $RequestFund);
        }


        return response()->success("RequestFund ", $DeferredInstallment);
        // return ['data' => $user];
    }


    public function delete_fund_requests($uuid)
    {


        $user = auth()->guard('Fund')->user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $request = RequestFund::where('uuid', $uuid)->first();


        if ($request) {
            $offerUser = FundRequestOffer::where('uuid', $uuid)->get();


            foreach ($offerUser as $offerUserItem) {
                $provider = User::find($offerUserItem->provider_id);


                if ($provider) {
                    $provider->count_fund_offer = $provider->count_fund_offer - 1;
                    if ($request->status == 'accepted_customer') {
                        $provider->count_accept_offer = $provider->count_accept_offer - 1;
                    }
                    if ($request->status == 'sending_code') {
                        $provider->count_preview_fund_offer = $provider->count_preview_fund_offer - 1;
                    }
                    $provider->save();


                }


            }


            $offer = FundRequestOffer::where('uuid', $uuid)->delete();
            $offer2 = FundRequestHasOffer::where('uuid', $uuid)->delete();

            $city = City::where('serial_city', $request->city_id)->first();
            if ($city) {
                $city->count_fund_request = $city->count_fund_request - 1;
                $city->save();
            }

            $nebRequest = FundRequestNeighborhood::where('request_fund_id', $request->id)->get();

            if (count($nebRequest)) {
                foreach ($nebRequest as $nebRequestItem) {
                    $neb = Neighborhood::where('neighborhood_serial', $nebRequestItem->neighborhood_id)->first();
                    if ($neb) {
                        $neb->request_fund_counter = $neb->request_fund_counter - 1;
                        $neb->save();
                    }
                }

            }


            $request->delete();

            return response()->success("Request And Offer Deleted", []);
        } else {
            return response()->error("No Request Found");
        }
    }

    public function show_fund_requests(Request $request)
    {


        $user = auth()->guard('Fund')->user();

        if ($user == null) {
            return response()->error("not authorized");
        }
        $finiceing = RequestFund::query();

        if ($request->get('search')) {
            $finiceing = $finiceing->where('uuid', $request->get('search'));
        }
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


    public function all_show_fund_requests(Request $request)
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

        $finiceing = $finiceing->get();

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
            //  'more_offer' => 'sometimes|required'

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


//1,2,3,4,5,6,7,8,9,10
        $finice = RequestFund::where('uuid', $request->get('uuid'))->first();


        $finice_offer_ids = '';

        if ($finice) {

            $finice_offer_ids = explode(',', $finice->offer_numbers);


            $lastRequestOffer = FundRequestOffer::where('uuid',
                $request->get('uuid'))
                ->whereHas('estate')
                ->whereHas('provider')
                ->whereHas('fund_request')
                ->where('status','!=','expired')
              //  ->whereNotIn('id', isset($finice_offer_ids) ? $finice_offer_ids : [0])
                ->orderBy('id', 'desc')
                ->first();


            $FundRequestOffer = FundRequestOffer::where('uuid',
                $request->get('uuid'))
                ->whereNotIn('id', isset($finice_offer_ids) ? $finice_offer_ids : [0])
                ->whereHas('estate')
                ->whereHas('provider')
                ->whereHas('fund_request')
                ->where('status','!=','expired')
                //   ->limit(5)
                ->pluck('id');


            if ($request->get('more_offer') && $request->get('more_offer') == 1) {


                if ($lastRequestOffer && (!in_array($lastRequestOffer->id, $FundRequestOffer->toArray()))) {


                    $stringOffer = implode(',', $FundRequestOffer->toArray());
                    $finice->offer_numbers = $finice->offer_numbers != null ? $finice->offer_numbers . ',' . $stringOffer : $stringOffer;
                    $finice->save();
                    $finice_offer_ids = explode(',', $finice->offer_numbers);

                }


            }


            if (!$finice) {
                return response()->error("not found", []);
            }


            // offer_numbers
            $FundRequestOfferObject = FundRequestOffer::where('uuid',
                $request->get('uuid'))
                ->whereHas('estate')
                ->whereHas('provider')
                ->where('status','!=','expired')
                // ->where('status','<>','close')
                ->whereHas('fund_request');


            $msg = '';
            $code = '';
            if ($lastRequestOffer && (!in_array($lastRequestOffer->id, $FundRequestOffer->toArray()))) {

                $msg = 'Request Fund Offer';
                $code = 200;
                $FundRequestOfferObject = $FundRequestOfferObject->whereNotIn('id', $finice_offer_ids);
            } else {

                $msg = 'This is the Last offer for this request';
                $code = 201;


                //   $FundRequestOfferObject = $FundRequestOfferObject->whereIn('id', $finice_offer_ids);
            }


            $FundRequestOfferObject = $FundRequestOfferObject
                //     ->whereStatus('!=','close')
                ->where('is_close', '!=', 1)
                ->where('status','!=','expired')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();


            if (count($FundRequestOfferObject) <= 0) {
                return response()->error("لايوجد عروض متاحة لهذا الطلب", []);
            }


            $collection = RequestFundOfferResource::collection($FundRequestOfferObject);

            return Response::json([
                "status" => false,
                "message" => $msg,
                "result" => $collection,
                'errors' => null,
                'code' => $code
            ]);
            return response()->success($msg, $collection);
        } else {
            return JsonResponse::fail('No Request Fund Find Wrong UUID', 400);
        }


    }


    public function fund_request_close_offer(Request $request)
    {


        $FundRequestOfferObject = FundRequestOffer::whereHas('estate')
            ->where('is_close', 1);


        if ($request->get('uuid')) {
            $FundRequestOfferObject = $FundRequestOfferObject->where('uuid', $request->get('uuid'));
        }


        $FundRequestOfferObject = $FundRequestOfferObject->orderBy('id', 'desc')->get();
        //dd($FundRequestOfferObject);

        if (count($FundRequestOfferObject) <= 0) {
            return response()->error("لايوجد عروض متاحة لهذا الطلب", []);
        }


        $collection = RequestFundOfferResource::collection($FundRequestOfferObject);


        return response()->success('done', $collection);


    }

    public function fund_request_all_offer(Request $request)
    {

        /*  $rules = Validator::make($request->all(), [


              'uuid' => 'required',
              //  'more_offer' => 'sometimes|required'

          ]);

          if ($rules->fails()) {
              return JsonResponse::fail($rules->errors()->first(), 400);
          }
  */

//1,2,3,4,5,6,7,8,9,10
        // $finice = RequestFund::where('uuid', $request->get('uuid'))->first();


        if (true) {


            // offer_numbers
            $FundRequestOfferObject = FundRequestOffer::
            whereHas('estate')
                ->whereHas('provider')->whereHas('fund_request');


            $FundRequestOfferObject = $FundRequestOfferObject
                ->whereHas('estate')
                ->orderBy('id', 'desc')->get();


            //dd($FundRequestOfferObject);

            if (count($FundRequestOfferObject) <= 0) {
                return response()->error("لايوجد عروض متاحة لهذا الطلب", []);
            }


            $collection = RequestFundOfferResource::collection($FundRequestOfferObject);

            return Response::json([
                "status" => false,
                "message" => 'All offer for uuid : ' . $request->get('uuid'),
                "result" => $collection,
                'errors' => null,
                'code' => '200'
            ]);
            return response()->success($msg, $collection);
        } else {
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
        $finice = RequestFund::where('uuid', $request->get('uuid'))->first();


        $finice_offer_ids = explode(',', $finice->offer_numbers);
        if (!$finice) {
            return response()->error("not found", []);
        }

        // offer_numbers
        $FundRequestOffer = FundRequestOffer::where('uuid',
            $request->get('uuid'))
            ->where('id', $request->get('id'))->first();

        $estate = Estate::find($FundRequestOffer->estate_id);


        if ($estate) {
            $note = NotificationUser::create([
                'user_id' => $estate->user_id,
                'title' => 'تم رفض عرضك رقم :' . $FundRequestOffer->id,
                'type' => 'fund_offer',
                'type_id' => $FundRequestOffer->id,
            ]);
            $client = User::where('id', $estate->user_id)->first();
            $push_data = [
                'title' => 'تم رفض عرضك رقم :' . $FundRequestOffer->id,
                'body' => 'العرض غير مناسب',
                'id' => $FundRequestOffer->id,
                'user_id' => $FundRequestOffer->provider_id,
                'type' => 'fund_offer',
            ];
            if ($client) {
                //  send_push('cnfvlhrz9g4:APA91bHUdUG-9PQjJJnvL3bcg5svj4j1PyYOltl7FEwq4WzDoKzEsK9oUHe6WLVwV8fHltk63kANdNDX5QZ2qBKhDvlTZm8_lCvlplNipmBCnsf7bgFFmQ9R8ArvOPddMK-s5RxWqucp', $push_data, 'android');

                send_push($client->device_token, $push_data, $client->device_type);
            }
        }


        if ($FundRequestOffer) {
            $FundRequestOffer->reason = 'العرض غير مناسب';
            //  $fund_offer->status = 'close';
            $FundRequestOffer->is_close = 1;
            $FundRequestOffer->cancel_at = date('Y-m-d');
            $FundRequestOffer->save();
            //  $FundRequestOffer->delete();
        }


        if (!$FundRequestOffer) {
            return response()->error("not found Offers", []);
        }


        $finice->offer_numbers = $finice->offer_numbers != null ? $finice->offer_numbers . ',' . $FundRequestOffer->id : $FundRequestOffer->id;
        $finice->save();

        return response()->success("تم رفض العرض بنجاح", []);

    }

// عمل اعادة كاونت لعرض الطلبات
    public function reset_offer(Request $request)
    {
        $finice = RequestFund::where('uuid', $request->get('uuid'))->first();

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

        if (!$offer) {
            return response()->error("not found offer", []);
        }
        $finice = RequestFund::where('uuid', $offer->uuid)->first();


        // $offer->instument_number=$request->get('instument_number');
        //  $offer->guarantees=$request->get('guarantees');
        $offer->beneficiary_name = $request->get('beneficiary_name');
        $offer->beneficiary_mobile = $request->get('beneficiary_mobile');
        $offer->request_preview_date = date(now());
        $offer->status = 'sending_code';
        $offer->review_at = date('Y-m-d');
        $offer->save();


        /* $deleteOffer = FundRequestOffer::where('id', '!=', $offer->id)
             ->where('uuid', $offer->uuid)->delete();*/
        $finice->status = 'sending_code';
        //$finice->offer_numbers = null;

        $finice->save();
        return response()->success("Offer Accepted", []);

    }

     // لعرض كل الطلبات الي الها عروض
    public function Request_has_offer()
    {

        $has_offer = FundRequestHasOffer::query()
            ->where('display_status', 'yes')
            ->get();

        if ($has_offer) {
            return response()->success("Request Has Offer", $has_offer);
        } else {
            return JsonResponse::fail('No Request Has Offer at This Time', 400);
        }

    }

//فحص طلب اذا الو عروض او لا
    public function check_Request_has_offer($uuid)
    {

        $has_offer = FundRequestHasOffer::where('uuid', $uuid)->first();

        if ($has_offer) {
            return response()->success("Request Has Offer", $has_offer);
        } else {
            return JsonResponse::fail('Doesnt Has Offer', 400);
        }

    }

    public function change_Request_has_offer($uuid)
    {

        $has_offer = FundRequestHasOffer::where('uuid', $uuid)->first();

        if ($has_offer) {
            $has_offer->display_status = 'no';
            $has_offer->save();
            return response()->success("Request Has Offer", $has_offer);
        } else {
            return JsonResponse::fail('Doesnt Has Offer', 400);
        }

    }

    // تسجيل تاريخ اول عرض ارتسل
    public function record_offer($id)
    {
        $FundRequestOfferObject = FundRequestOffer::find($id);


        if ($FundRequestOfferObject) {

            if ($FundRequestOfferObject->show_count == 0) {
                $FundRequestOfferObject->show_count = 1;
                $FundRequestOfferObject->first_show_date = date(now());
                $FundRequestOfferObject->save();
            } else {
                $FundRequestOfferObject->show_count = $FundRequestOfferObject->show_count + 1;

                $FundRequestOfferObject->save();
            }
            return response()->success("Count is Increase", $FundRequestOfferObject);
        } else {
            return JsonResponse::fail('Doesnt Has Offer', 400);
        }
    }

    public function offer_date_data()
    {
        $estate = FundRequestOffer::whereHas('estate')->whereHas('provider')->paginate();
//OfferDateDataResource
        $collection = OfferDateDataResource::collection($estate);

        return response()->success("Estate", $collection);
    }

    public function provider_data()
    {
        $user = User::where('type', 'provider')->paginate();

        $collection = UserDateDataResource::collection($user);

        return response()->success("Users", $collection);
    }

    public function provider_attchment_data()
    {
        $user = FundRequestOffer::whereHas('estate')->whereHas('provider')->paginate();

        $collection = OfferAttachDateDataResource::collection($user);

        return response()->success("Users", $collection);
    }

    //اضافة بيانات المستفيد بالطلب
    public function beneficiary_information(Request $request)
    {
        $rules = Validator::make($request->all(), [


            'uuid' => 'required',
            // 'instument_number' => 'required',
            //  'guarantees' => 'required',
            'beneficiary_name' => 'required',
            'beneficiary_mobile' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $finice = RequestFund::where('uuid', $request->get('uuid'))->first();
        if (!$finice) {
            return response()->error("not found Request", []);
        }


        // $offer->instument_number=$request->get('instument_number');
        //  $offer->guarantees=$request->get('guarantees');
        $finice->beneficiary_name = $request->get('beneficiary_name');
        $finice->beneficiary_mobile = $request->get('beneficiary_mobile');
        $finice->is_send_beneficiary_information = 1;
        $finice->save();

        return response()->success("Information Sent", $finice);
    }
}
