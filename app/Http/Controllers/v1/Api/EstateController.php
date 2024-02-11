<?php

namespace App\Http\Controllers\v1\Api;

//DEFINE('V',app('request')->header('v')?app('request')->header('v'):'v1');

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\RequestFundOfferResource;

use App\Models\v1\Estate;
use App\Models\v1\EstateRequest;
use App\Models\v1\Finance;
use App\Models\v1\FinanceOffer;
use App\Models\v1\FundRequestOffer;

use App\Models\v1\RequestFund;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class EstateController extends Controller
{

    public function home(Request $request)
    {


        /* $user = Auth::user();
         if ($user == null) {
             return response()->error("not authorized");
         }*/

        $attitude = $request->get('lat');
        $longitude = $request->get('lan');
        $distanceL = 500;
        //  if ($attitude && $longitude) {


        $EstateRequest = EstateRequest::select(\DB::raw("*,6371  * acos( cos( radians($attitude) ) * cos( radians( estate_requests.lat ) )
   * cos( radians(estate_requests.lan) - radians($longitude)) + sin(radians($attitude))
   * sin( radians(estate_requests.lat))) AS distance "))
            //  ->havingRaw("distance<=$distance")
            // ->select('id','distance')
            // ->select('id','c_latitude','c_longitude')
            //    ->limit(2)
            ->get();


        $distance = [];
        $ids = [];
        $i = 0;
        foreach ($EstateRequest as $EstateRequestItem) {


            // dd($MechanicItem);

            /*  $MechanicItem->distances=$this->helper->distance($request->latitude, $request->longitude, $MechanicItem->c_latitude, $MechanicItem->c_longitude, "K");
              $MechanicItem->ids= $MechanicItem->id;
  */
            if ($EstateRequestItem->distance <= $distanceL) {
                $distance[$i] = distance($request->lat, $request->lan,
                    $EstateRequestItem->lat, $EstateRequestItem->lan, "K");;
                $ids[$i] = $EstateRequestItem->id;
                $i++;
            }


        }


        $Mechanic = EstateRequest::whereIn('id', $ids);
        $j = 0;


        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }


        if ($request->get('estate_pay_type')) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                $Mechanic = $Mechanic->where('request_type', 'rent');
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                $Mechanic = $Mechanic->where('request_type', 'pay');
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0) {
            $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0) {
            $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search')) {


            $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('area_to') . '%');

        }


        $Mechanic = $Mechanic->get();


        foreach ($Mechanic as $MechanicItem) {

            $MechanicItem->distance = $distance[$j];
            $j++;
        }

        return response()->success("Customer Requests", $Mechanic);

    }


    public function homeList(Request $request)
    {


        /* $user = Auth::user();
         if ($user == null) {
             return response()->error("not authorized");
         }*/

        $attitude = $request->get('lat');
        $longitude = $request->get('lan');
        $distanceL = 500;
        //  if ($attitude && $longitude) {


        $Mechanic = EstateRequest::query();


        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }

        if ($request->get('estate_pay_type')) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                $Mechanic = $Mechanic->where('request_type', 'rent');
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                $Mechanic = $Mechanic->where('request_type', 'pay');
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0) {
            $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0) {
            $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search')) {


            $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('area_to') . '%');

        }


        $Mechanic = $Mechanic->paginate();


        return response()->success("Customer Requests", $Mechanic);

    }


    public function homeAqarz(Request $request)
    {


        /* $user = Auth::user();
         if ($user == null) {
             return response()->error("not authorized");
         }*/

        $attitude = $request->get('lat');
        $longitude = $request->get('lan');
        $distanceL = 500;


        $EstateRequest = Estate::select(\DB::raw("*,6371  * acos( cos( radians($attitude) ) * cos( radians( estates.lat ) )
   * cos( radians(estates.lan) - radians($longitude)) + sin(radians($attitude))
   * sin( radians(estates.lat))) AS distance "))
            ->where('available','1')
            ->get();


        $distance = [];
        $ids = [];
        $i = 0;
        foreach ($EstateRequest as $EstateRequestItem) {


            // dd($MechanicItem);

            /*  $MechanicItem->distances=$this->helper->distance($request->latitude, $request->longitude, $MechanicItem->c_latitude, $MechanicItem->c_longitude, "K");
              $MechanicItem->ids= $MechanicItem->id;
  */
            if ($EstateRequestItem->distance <= $distanceL) {
                $distance[$i] = distance($request->lat, $request->lan,
                    $EstateRequestItem->lat, $EstateRequestItem->lan, "K");;
                $ids[$i] = $EstateRequestItem->id;
                $i++;
            }


        }


        $Mechanic = Estate::whereIn('id', $ids);
        $j = 0;


        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }
        if ($request->get('request_type')) {
            $Mechanic = $Mechanic->where('request_type', $request->get('request_type'));
        }

        if ($request->get('estate_pay_type')) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                $Mechanic = $Mechanic->where('is_rent', 1);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {

                $Mechanic = $Mechanic->where('is_rent', null)
                    ->orWhere('is_rent', 0);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0) {
            $Mechanic->where('total_price', '>=', $request->get('price_from'));
            $Mechanic->where('total_price', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('price_to') != 0) {
            $Mechanic->where('total_area', '>=', $request->get('area_from'));
            $Mechanic->where('total_area', '<=', $request->get('area_to'));
        }
        if ($request->get('search')) {


            $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('area_to') . '%')
                ->orwhere('interface', 'like', '%' . $request->get('area_to') . '%')
                ->orwhere('rent_type', 'like', '%' . $request->get('area_to') . '%');
        }

        $Mechanic = $Mechanic->get();


        foreach ($Mechanic as $MechanicItem) {

            $MechanicItem->distance = $distance[$j];
            $j++;
        }

        return response()->success("Aqarz Estate", $Mechanic);

    }


    public function homeAqarzList(Request $request)
    {


        /* $user = Auth::user();
         if ($user == null) {
             return response()->error("not authorized");
         }*/


        $Mechanic = Estate::query();


        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }
        if ($request->get('request_type')) {
            $Mechanic = $Mechanic->where('request_type', $request->get('request_type'));
        }

        if ($request->get('estate_pay_type')) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                $Mechanic = $Mechanic->where('is_rent', 1);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {

                $Mechanic = $Mechanic->where('is_rent', null)
                    ->orWhere('is_rent', 0);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0) {
            $Mechanic->where('total_price', '>=', $request->get('price_from'));
            $Mechanic->where('total_price', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('price_to') != 0) {
            $Mechanic->where('total_area', '>=', $request->get('area_from'));
            $Mechanic->where('total_area', '<=', $request->get('area_to'));
        }
        if ($request->get('search')) {


            $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('area_to') . '%')
                ->orwhere('interface', 'like', '%' . $request->get('area_to') . '%')
                ->orwhere('rent_type', 'like', '%' . $request->get('area_to') . '%');
        }

        $Mechanic = $Mechanic->paginate();


        return response()->success("Aqarz Estate", $Mechanic);

    }


    public function single_estate($id)
    {
        $EstateRequest = Estate::with('plannedFile', 'EstateFile', 'comforts', 'user')->find($id);
        if (!$EstateRequest) {
            return response()->error("NOT Found", []);
        }
        $EstateRequest->seen_count += 1;
        $EstateRequest->save();
        return response()->success("EstateRequest", $EstateRequest);
    }

    public function single_request($id)
    {
        $EstateRequest = EstateRequest::with('user')->findOrFail($id);
        if (!$EstateRequest) {
            return response()->error("NOT Found", []);
        }
        $EstateRequest->seen_count += 1;
        $EstateRequest->save();
        return response()->success("EstateRequest", $EstateRequest);
    }

    public function active_finice_estate(Request $request)
    {


        $finiceing = Finance::where('status', 1);


        if ($request->get('estate_type_id')) {
            $finiceing = $finiceing->where('estate_type_id', $request->get('estate_type_id'));
        }
        if ($request->get('job_type')) {
            $finiceing = $finiceing->where('job_type', $request->get('job_type'));
        }
        if ($request->get('finance_interval')) {
            $finiceing = $finiceing->where('finance_interval', $request->get('finance_interval'));
        }
        if ($request->get('engagements')) {
            $finiceing = $finiceing->where('engagements', $request->get('engagements'));
        }
        if ($request->get('city_id')) {
            $finiceing = $finiceing->where('city_id', $request->get('city_id'));
        }
        if ($request->get('total_salary')) {
            $finiceing = $finiceing->where('total_salary', $request->get('total_salary'));
        }
        if ($request->get('available_amount')) {
            $finiceing = $finiceing->where('available_amount', $request->get('available_amount'));
        }

        $finiceing = $finiceing->get();

        return response()->success("Finance Requests", $finiceing);

    }


    public function send_offer_fund(Request $request)
    {
        $EstateRequest = RequestFund::where('uuid', $request->get('uuid'))->first();


        $user = Auth::user();

        $checkOffer = FundRequestOffer::where('provider_id', $user->id)
            ->where('estate_id', $request->get('estate_id'))
            ->where('uuid', $request->get('uuid'))->first();


        if ($checkOffer) {
            return response()->success("You Are Already  Fund Request Offer", $checkOffer);
        }

        if ($EstateRequest) {
            $request->merge([
                'provider_id' => $user->id,
            ]);

            $FundRequestOffer = FundRequestOffer::create($request->only([
                'uuid',
                'instument_number',
                'guarantees',
                'beneficiary_name',
                'beneficiary_mobile',
                'code',
                'status',
                'provider_id',
                'estate_id',


            ]));
        }


        $FundRequestOffer = FundRequestOffer::findOrFail($FundRequestOffer->id);


        /*  $EstateRequest->offer_numbers = $EstateRequest->offer_numbers!=null?$EstateRequest->offer_numbers.','.$finice->id:$finice->id;
          $EstateRequest->save();*/
        return response()->success("FundRequestOffer", $FundRequestOffer);
    }


    public function myEstate()
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $estate = Estate::where('user_id', $user->id)->get();
        if ($estate) {
            return response()->success("Estate", $estate);
        } else {
            return response()->error("not found", []);
        }

    }


    public function myRequest(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $Mechanic = EstateRequest::where('user_id', $user->id);

        $estate = '';

        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }


        if ($request->get('estate_pay_type')) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                $Mechanic = $Mechanic->where('request_type', 'rent');
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                $Mechanic = $Mechanic->where('request_type', 'pay');
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0) {
            $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0) {
            $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search')) {


            $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('area_to') . '%');

        }

        $Mechanic = $Mechanic->paginate();
        if ($estate) {
            return response()->success("EstateRequest", $Mechanic);
        } else {
            return response()->error("not found", []);
        }

    }


    public function demandsRequest(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $Mechanic = EstateRequest::query();


        $estate = '';

        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }


        if ($request->get('estate_pay_type')) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                $Mechanic = $Mechanic->where('request_type', 'rent');
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                $Mechanic = $Mechanic->where('request_type', 'pay');
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0) {
            $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0) {
            $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search')) {


            $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('area_to') . '%');

        }

        $Mechanic = $Mechanic->paginate();
        if ($Mechanic) {
            return response()->success("EstateRequest", $Mechanic);
        } else {
            return response()->error("not found", []);
        }

    }

    // public function


    public function approval_offer()
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }

        $finiceingOfferArray = FundRequestOffer::where('provider_id', $user->id)
            ->where('status', 'active')->get();
        if ($finiceingOfferArray) {
            return response()->success("Approval Offer", $finiceingOfferArray);
        } else {
            return response()->error("not found offer", []);
        }

    }


    public function send_offer_status(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $finiceingOfferArray = FinanceOffer::findOrFail($request->get('offer_id'));
        if ($finiceingOfferArray) {

            $finice = Finance::findOrFail($finiceingOfferArray->finance_id);
            if (!$finice) {
                return response()->error("not found Finance", []);
            }
            if ($finiceingOfferArray->status != 'rejected' && $finiceingOfferArray->status != 'accepted') {
                $finiceingOfferArray->status = $request->get('status');
                $finiceingOfferArray->save();
                $finice->status = $request->get('status') == 'accepted' ? 'provider_accepted' : 'provider_rejected';
                $finice->save();
                return response()->success("Offer " . $request->get('status') . " ", $finiceingOfferArray);
            }


        } else {
            return response()->error("not found offer", []);
        }
    }


    public function customer_offer(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error("not authorized");
        }


        $finiceingOfferArray = Finance::where('user_id', $user->id)
            ->where('status', 'provider_accepted')->get();
        if ($finiceingOfferArray) {

            return response()->success("Offer ", $finiceingOfferArray);

        } else {
            return response()->error("not found offer", []);
        }
    }


    public function send_customer_offer_status(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
            'code' => 'required',
            //   'status' => 'required',

        ]);


        $content = file_get_contents(url("api/code/check?uuid=" . $request->get('uuid') . "&code=" . $request->get('code') . ""));
        $symbols = json_decode($content, true);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        if ($symbols['status'] != true) {
            return response()->error("code is wrong", []);
        }
        $Finance = RequestFund::where('uuid', $request->get('uuid'))
            //->where('code',$request->get('uuid'))
            ->first();
        if ($Finance) {
            $offer = FundRequestOffer::where('uuid', $Finance->id)
                // ->where('code',$request->get('code'))
                ->where('status', 'active')->first();

            if (!$offer) {
                return response()->error("code is miss", []);
            }

            $offer->status = $request->get('status');
            $offer->save();
            $Finance->status = 'customer_accepted';
            $Finance->save();

            return response()->success("Offer " . $request->get('status') . " ", $Finance);
        } else {
            return response()->error("not found offer", []);
        }


    }

    public function show_fund_requests(Request $request)
    {


        $user = auth()->guard()->user();

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


        $finiceing = $finiceing->orderBy('id', 'desc')->paginate();

        return response()->success("Fund Requests", $finiceing);

    }


    public function fund_request_offer(Request $request)
    {


        $user = Auth::user();


//1,2,3,4,5,6,7,8,9,10


        // offer_numbers
        $FundRequestOffer = FundRequestOffer::where('provider_id', $user->id);


        if ($request->get('estate_type')) {
            $FundRequestOffer->whereHas('estate', function ($query) use($request) {
                $query->where('estate_type_id', $request->get('estate_type'));
            });
        }


        if ($request->get('city_id')) {
            $FundRequestOffer->whereHas('estate.city', function ($query) use($request) {
                $query->where('city_id', $request->get('city_id'));
            });
        }
        if ($request->get('neighborhood_id')) {
            $FundRequestOffer->whereHas('estate.neighborhood', function ($query) use($request) {
                $query->where('neighborhood_id', $request->get('neighborhood_id'));
            });
        }


        $FundRequestOffer = $FundRequestOffer->get();
        if (!$FundRequestOffer) {
            return response()->error("not found Offers", []);
        }


        $collection = RequestFundOfferResource::collection($FundRequestOffer);


        //     $stringOffer = implode(',', $finiceingOfferArray->toArray());
        //       $finice->offer_numbers = $finice->offer_numbers != null ? $finice->offer_numbers . ',' . $stringOffer : $stringOffer;
        //    $finice->save();

        return response()->success("Request Fund Offer", $collection);


    }

}
