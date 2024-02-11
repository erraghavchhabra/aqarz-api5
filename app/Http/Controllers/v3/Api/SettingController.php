<?php

namespace App\Http\Controllers\v3\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllGolobalCitesWithNebResource;
use App\Http\Resources\CheckPointResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\GolobalCitesWithNebResource;
use App\Models\v3\AreaEstate;
use App\Models\v3\Bank;
use App\Models\v3\City;
use App\Models\v3\City3;
use App\Models\v3\City5;
use App\Models\v3\Client;
use App\Models\v3\Comfort;
use App\Models\v3\Contact;
use App\Models\v3\Content;
use App\Models\v3\Country;
use App\Models\v3\CourseType;
use App\Models\v3\DaliyEstateFile;
use App\Models\v3\District;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\EstatePrice;
use App\Models\v3\EstateRequest;
use App\Models\v3\EstateType;
use App\Models\v3\ExperienceType;
use App\Models\v3\FundRequestHasOffer;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\MemberType;
use App\Models\v3\NebInterest;
use App\Models\v3\Neighborhood;
use App\Models\v3\NotificationUser;
use App\Models\v3\OprationType;
use App\Models\v3\RateRequestType;
use App\Models\v3\Region;
use App\Models\v3\Report;
use App\Models\v3\RequestFund;
use App\Models\v3\RequestOffer;
use App\Models\v3\ServiceType;
use App\Models\v3\StreetView;
use App\Models\v3\Ticket;
use App\Models\v3\TicketChat;
use App\Models\v3\UserPayment;
use App\Models\v3\Video;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Config;
use Symfony\Component\Finder\Comparator\Comparator;


class SettingController extends Controller
{

    public function estate_report(Request $request)
    {

        $estate_report = DaliyEstateFile::query();

        if ($request->get('date')) {
            $estate_report = $estate_report->whereDate(
                'created_at',
                '=',
                Carbon::parse($request->get('date'))
            );
        }

        $estate_report = $estate_report->get();
        if (!$estate_report) {
            return response()->error(__("views.not found"));
        }
        return response()->success("Report", $estate_report);

    }

    public function testApi()
    {


        $url = 'https://eservices.redf.gov.sa/Aqarz/api/Aqarz/SendSMSwhenReadyOffers';

        $data = [
            'uuid' => 'd19dded7-8543-457e-826e-2b504420b2e0',


        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_HEADER, 0);


//  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Authorization: Basic YXFhcnpfcDpAcjNRcnojI3V5ITE3',

        ]);


        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'aqarz_p' . ":" . '@r3Qrz##uy!17');
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);
        $err = curl_error($ch);
        curl_close($ch);


        print_r($err);

        if ($result == false) {
            return 123;
        }

        $headerArr = explode(PHP_EOL, $headerstring);
        foreach ($headerArr as $headerRow) {
            preg_match('/([a-zA-Z\-]+):\s(.+)$/', $headerRow, $matches);
            if (!isset($matches[0])) {
                continue;
            }
            $header[$matches[1]] = $matches[2];
        }

//return [json_decode($body)];
        return [
            'code' => (json_decode($body)->code),
            'msg' => (json_decode($body)->message),
            'status' => json_decode($body)->status
        ];
    }

    public function bestProvider()
    {


        // dd(Config::get('filesystems'));

        $user = User::where('type', 'provider')
            ->where('id', '!=', 142)
            //->orderBy('count_visit', 'desc')

            ->orderBy('count_fund_offer', 'desc')
            ->orderBy('count_offer', 'desc')
            ->orderBy('count_accept_fund_offer', 'desc')
            // ->orderBy('count_agent', 'desc')
            ->limit(5)
            ->get();


        if ($user == null) {
            return response()->error(__("views.not found"));
        }
        return response()->success("Best Provider", $user);

    }

    public function EstateAreaRange()
    {

        $AreaEstate = AreaEstate::where('status', '1')->get();
        return response()->success("AreaEstate List", $AreaEstate);
    }


    public function EstatePriceRange()
    {

        $EstatePrice = EstatePrice::where('status', '1')->get();
        return response()->success("EstatePrice List", $EstatePrice);
    }

    public function EstateStreetViewRange()
    {

        $StreetView = StreetView::where('status', '1')->get();

        return response()->success("StreetView List", $StreetView);
    }

    public function OprationType()
    {

        $OprationType = OprationType::get();
        return response()->success("OprationType List", $OprationType);
    }


    public function EstateType()
    {


        $user = Auth::user();

        //  $value = Cache::get('cities');
        $value = EstateType::get();
        //dd($value->first());
        if ($user != null) {
            $type_ids = explode(',', $user->saved_filter_type);
            $type_fund_ids = explode(',', $user->saved_filter_fund_type);


            foreach ($value as $valueItem) {
                if (in_array($valueItem->id, $type_ids)) {

                    $valueItem->is_selected = 1;


                } else {
                    $valueItem->is_selected = 0;
                }
                if (in_array($valueItem->id, $type_fund_ids)) {


                    $valueItem->is_fund_selected = 1;


                } else {
                    $valueItem->is_fund_selected = 0;
                }


            }
        }


        return response()->success("EstateType List", $value);
    }


    public function banks()
    {


        $Bank = Bank::get();
        return response()->success("Bank List", $Bank);
    }


    public function settings()
    {

        $data = Content::query()->get();
        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $cloum = 'value_' . $local;


        $AreaEstate = AreaEstate::where('status', '1')->get();
        $Comfort = Comfort::get();
        $regine = Region::get();

        /* $requests = RequestFund::WhereHas('city', function ($query)  {
             $query->where('state_id', 4);

         })->count();


      dd($requests);*/
        for ($i = 0; $i < count($regine); $i++) {


            $estate = Estate::WhereHas('city', function ($query) use ($i, $regine) {
                $query->where('state_id', 11);

            })->whereIn('status',['completed','expired']);


        //    return $estate->get();

            $regine[$i]['estate'] = $estate->count();

            //$array[$regine[$i + 1]] = ['requests' => $requests->count(), 'offer' => $offers->count(),'providers'=>$providers->count()];
            //  $array[$regine[$i + 1]] = ['requests' => $requests->count(), 'offer' => $offers->count(),'providers'=>$providers->count()];
        }
        $company_info = Content::where('key', 'company_info')->first()->$cloum;
        $offerReal = Content::where('key', 'offerReal')->first()->$cloum;
        $about_us = Content::where('key', 'about_us')->first()->$cloum;
        $privacy_policy = Content::where('key', 'privacy_policy')->first()->$cloum;
        $face_book = Content::where('key', 'face_book')->first()->$cloum;
        $twitter = Content::where('key', 'twitter')->first()->$cloum;
        $insta = Content::where('key', 'insta')->first()->$cloum;
        $snapchat = Content::where('key', 'snapchat')->first()->$cloum;
        $linked = Content::where('key', 'linked')->first()->$cloum;
        $mobile = Content::where('key', 'mobile')->first()->$cloum;
        $email = Content::where('key', 'email')->first()->$cloum;
        $tutorial = Content::where('key', 'tutorial')->first()->$cloum;
        $video_url = Content::where('key', 'video_url')->first()->$cloum;
        $privacy_and_confidentiality_statement = Content::where('key', 'Privacy_and_confidentiality_statement')->first()->$cloum;
        $terms_and_conditions = Content::where('key', 'terms_and_conditions')->first()->$cloum;


        $service_types = ServiceType::where('status', '1')->get();
        $experience_types = ExperienceType::where('status', '1')->get();
        $course_types = CourseType::where('status', '1')->get();
        $member_types = MemberType::where('status', '1')->get();
        $user = Auth::user();
        $date = date('Y-m-d');
        $Mechanic = EstateRequest::whereDate('created_at', $date)->count();
        $RequestFund = RequestFund::whereDate('created_at', $date)->count();
        $allRequest = EstateRequest::count();
        $allRequestFund = RequestFund::count();

        $user = auth()->user();
        if ($user != null) {
            $myRequestFundOffer = FundRequestOffer::whereHas('provider')->whereHas('estate')->whereHas('fund_request')->where('provider_id',
                $user->id)->count();
            $myRequestOffer = RequestOffer::
            whereHas('provider')->whereHas('estate')->whereHas('request')
                ->where('provider_id', $user->id)->count();
        } else {
            $myRequestFundOffer = 0;
            $myRequestOffer = 0;
        }

        $requestReal = RequestFund::where('id', '>', 0)->count();

        /*$offerReal = FundRequestOffer::
        whereHas('provider')->whereHas('estate')->whereHas('fund_request')
            ->count();*/
        /*   $offerReal = DB::table('fund_request_offers')
               ->join('users as u1', 'fund_request_offers.provider_id', '=', 'u1.id')
               ->join('estates as u2', 'fund_request_offers.estate_id', '=', 'u2.id')
               ->join('request_funds as u3', 'fund_request_offers.uuid', '=', 'u3.uuid')
               ->select(DB::Raw('COUNT(fund_request_offers.id)   as count'))
               ->whereRaw('u1.deleted_at is null')
               ->whereRaw('u2.deleted_at is null')
               ->whereRaw('u3.deleted_at is null')
               ->toSql();

           dd($offerReal);*/
        //  $value = Cache::get('cities');

        //dd($value->first());
        $value = '';
        /*  if ($user != null) {
              $city_ids = explode(',', $user->saved_filter_city);
              $value = City::where('status', '1')
                  ->whereIn('serial_city',$city_ids)
                  ->get();


          }*/


        // $collection = CityResource::collection($value);
        $videos = Video::where('status', '1')->get();
        $EstatePrice = EstatePrice::where('status', '1')->get();
        $StreetView = StreetView::where('status', '1')->get();
        $array = [

            'member_types' => $member_types,
            'privacy_and_confidentiality_statement' => $privacy_and_confidentiality_statement,
            'terms_and_conditions' => $terms_and_conditions,
            'videos' => $videos,
            'service_types' => $service_types,
            'company_info' => $company_info,
            'about_us' => $about_us,
            'privacy_policy' => $privacy_policy,
            'face_book' => $face_book,
            'twitter' => $twitter,
            'insta' => $insta,
            'linked' => $linked,
            'snapchat' => $snapchat,
            'mobile' => $mobile,
            'email' => $email,
            'estate_types' => $this->EstateType(),
            'banks' => $this->banks(),
            'OprationType' => $this->OprationType(),
            'allRequest' => $allRequest,
            'allRequestFund' => $allRequestFund,
            'marketDemands' => $Mechanic,
            'RequestFund' => $RequestFund,
            'myRequestFundOffer' => $myRequestFundOffer,
            'myRequestOffer' => $myRequestOffer,
            'requestReal' => $requestReal,
            'offerReal' => (int)$offerReal,
            'experience_types' => $experience_types,
            'course_types' => $course_types,
            'video_url' => $video_url,
            'tutorial' => $tutorial,
            'regine' => $regine,
            'Comfort' => $Comfort,
            'AreaEstate' => $AreaEstate,
            'EstatePrice' => $EstatePrice,
            'StreetView' => $StreetView,
            //   'selected_city'   => @$collection,
        ];


        return response()->success("Settings List", $array);

        //return $x;

    }


    public function countries()
    {

        $Bank = Country::get();
        return response()->success("Country List", $Bank);
    }


    public function cities(Request $request)
    {


        /*    $value = Cache::remember('cities', 10000000000000000000000000,function () {
                return $cities = City::where('status', '1')->get();
            });
    */

        $user = Auth::user();

        $value = DB::table('cities')
            ->select('cities.*');

        if ($request->get('is_all') != '1' && $request->get('is_all') != '2' && $request->get('is_all') != '3' && $request->get('is_all') == '0') {
            //  $value = $value->whereHas('fund_request');
            $value = DB::table('request_funds')
                ->Join('cities', 'cities.serial_city', '=', 'request_funds.city_id')
                ->select('cities.*')
                ->whereRaw(' request_funds.city_id = cities.serial_city')
                ->groupBy('cities.id');

        }
        if ($request->get('is_all') != '1' && $request->get('is_all') != '0' && $request->get('is_all') != '3' && $request->get('is_all') == '2') {
            // $value = $value->whereHas('app_request');
            /*  $value = $value->join('estate_requests', 'cities.serial_city', '=', 'estate_requests.city_id')
                  ->whereRaw('estate_requests.deleted_at is null');
  */

            $value = DB::table('estate_requests')
                ->Join('cities', 'cities.serial_city', '=', 'estate_requests.city_id')
                ->select('cities.*')
                ->whereRaw(' estate_requests.city_id = cities.serial_city')
                ->groupBy('cities.id');


        }


        if ($request->get('is_all') != '1' && $request->get('is_all') != '0' && $request->get('is_all') != '2' && $request->get('is_all') == '3') {
            // $value = $value->whereHas('estate');

            /*  $value = $value->join('estates', 'cities.serial_city', '=', 'estates.city_id')
                  ->whereRaw('estates.deleted_at is null');*/
            $value = DB::table('estate')
                ->Join('cities', 'cities.serial_city', '=', 'estate.city_id')
                ->select('cities.*')
                ->whereRaw(' estate.city_id = cities.serial_city')
                ->groupBy('cities.id');


        }

        if ($request->get('state_id')) {

            $value = $value->whereRaw('state_id =' . $request->get('state_id'));

            // $value = $value->where('state_id', $request->get('state_id'));
        }

        if ($request->get('name')) {

            // $value = $value->Where('name_ar', 'like', '%' . $request->get('name') . '%');
            //    $value = $value->where('name_ar', 'like', '%' . $request->get('name') . '%');
            $value = $value->whereRaw('name_ar LIKE "%' . $request->get('name') . '%"');


        }


        $value = $value->get();


        //  return($value);
        //dd($value->first());
        if ($user != null) {
            $city_ids = explode(',', $user->saved_filter_city);
            $city_fund_ids = explode(',', $user->saved_filter_fund_city);


            foreach ($value as $valueItem) {
                if (in_array($valueItem->serial_city, $city_ids)) {

                    $valueItem->is_selected = 1;


                } else {
                    $valueItem->is_selected = 0;
                }
                if (in_array($valueItem->serial_city, $city_fund_ids)) {


                    $valueItem->is_fund_selected = 1;


                } else {
                    $valueItem->is_fund_selected = 0;
                }


            }
        }


        $collection = CityResource::collection($value);

        return response()->success("Cities List", $collection);
    }

    public function cities3(Request $request)
    {


        /*    $value = Cache::remember('cities', 10000000000000000000000000,function () {
                return $cities = City::where('status', '1')->get();
            });
    */

        $user = Auth::user();

        //  $value = Cache::get('cities');
        // $value = City::query();
        $value = DB::table('neighborhoods')
            ->join('cities', 'cities.serial_city', '=', 'neighborhoods.city_id')
            ->join('regions', 'cities.state_id', '=', 'regions.id')
            ->select('cities.id', 'cities.name_ar', 'neighborhoods.id')
            ->get();
        $value = DB::table('cities')
            ->select('cities.id', 'cities.name_ar')
            ->get();

        dd($value);
        if ($request->get('is_all') != '1' && $request->get('is_all') != '2' && $request->get('is_all') != '3' && $request->get('is_all') == '0') {
            //  $value = $value->whereHas('fund_request');
            $value = $value->join('request_funds', 'cities.serial_city', '=', 'request_funds.city_id')
                ->whereRaw('request_funds.deleted_at is null');

        }
        if ($request->get('is_all') != '1' && $request->get('is_all') != '0' && $request->get('is_all') != '3' && $request->get('is_all') == '2') {
            // $value = $value->whereHas('app_request');
            $value = $value->join('estate_requests', 'cities.serial_city', '=', 'estate_requests.city_id')
                ->whereRaw('estate_requests.deleted_at is null');
        }


        if ($request->get('is_all') != '1' && $request->get('is_all') != '0' && $request->get('is_all') != '2' && $request->get('is_all') == '3') {
            $value = $value->whereHas('estate');

            $value = $value->join('estates', 'cities.serial_city', '=', 'estates.city_id')
                ->whereRaw('estates.deleted_at is null');
        }

        if ($request->get('state_id')) {

            $value = $value->whereRaw('state_id =' . $request->get('state_id'));

            // $value = $value->where('state_id', $request->get('state_id'));
        }

        if ($request->get('name')) {

            // $value = $value->Where('name_ar', 'like', '%' . $request->get('name') . '%');
            $value = $value->where('name_ar', 'like', '%' . $request->get('name') . '%');


        }


        $value = $value->get();


        //  return($value);
        //dd($value->first());
        if ($user != null) {
            $city_ids = explode(',', $user->saved_filter_city);
            $city_fund_ids = explode(',', $user->saved_filter_fund_city);


            foreach ($value as $valueItem) {
                if (in_array($valueItem->serial_city, $city_ids)) {

                    $valueItem->is_selected = 1;


                } else {
                    $valueItem->is_selected = 0;
                }
                if (in_array($valueItem->serial_city, $city_fund_ids)) {


                    $valueItem->is_fund_selected = 1;


                } else {
                    $valueItem->is_fund_selected = 0;
                }


            }
        }
        $array = [];


        /* for ($i = 0; $i < count($value); $i++) {


             $requests_fund = RequestFund::WhereHas('city', function ($query) use ($value, $i) {
                 $query->where('serial_city', $value[$i]->serial_city);

             })->count();

             $requests_app = EstateRequest::WhereHas('city', function ($query) use ($value, $i) {
                 $query->where('serial_city', $value[$i]->serial_city);

             })->count();

             $estate_app = Estate::WhereHas('city', function ($query) use ($value, $i) {
                 $query->where('serial_city', $value[$i]->serial_city);

             })->count();
             $value[$i]->count_fund_request = $requests_fund;
             $value[$i]->count_app_request = $requests_app;
             $value[$i]->count_app_estate = $estate_app;

             $value[$i]->save();


         }*/

        $collection = CityResource::collection($value);
        //   $redis = Redis::connection();

        //  Comparator::equal($p1, $p2)
        // dd(Comparator::equal($collection,json_decode($redis->get('user_details2'))));
        //  return([json_decode($redis->get('user_details2')),$collection]);
        /*   if(!$redis->get('user_details2'))
           {


               $redis->set('user_details2', json_encode($collection));

           }


           $response = $redis->get('user_details2');


           $response = json_decode($response);


           return $response;
   */
        //  $key ='cities';
        /* $allPosts = Cache::remember($key, 1800, function($value) {
             return CityResource::collection($value);
         });*/
        /*   if (!Cache::has('cities')) {

               dd(4444);
               $collection = Cache::remember('cities', 10, function () use ($collection) {
                   return $collection;
               });
           }

           return Cache::get('cities');
   dd(444);*/
        return response()->success("Cities List", $collection);
    }

    public function cities_page(Request $request)
    {


        /*    $value = Cache::remember('cities', 10000000000000000000000000,function () {
                return $cities = City::where('status', '1')->get();
            });
    */

        $user = Auth::user();

        //  $value = Cache::get('cities');
        $value = City::where('status', '1');


        if ($request->get('is_all') != '1' && $request->get('is_all') == '0') {
            $value = $value->whereHas('fund_request')->whereHas('estate');
        }
        if ($request->get('is_all') != '1' && $request->get('is_all') != '0' && $request->get('is_all') == '2') {
            $value = $value->whereHas('app_request')->whereHas('estate');
        }

        if ($request->get('state_id')) {
            $value = $value->where('state_id', $request->get('state_id'));
        }

        if ($request->get('name')) {

            $value = $value->Where('name_ar', 'like', '%' . $request->get('name') . '%');


        }


        $value = $value->paginate(50);
        //dd($value->first());
        if ($user != null) {
            $city_ids = explode(',', $user->saved_filter_city);
            $city_fund_ids = explode(',', $user->saved_filter_fund_city);


            foreach ($value as $valueItem) {
                if (in_array($valueItem->serial_city, $city_ids)) {

                    $valueItem->is_selected = 1;


                } else {
                    $valueItem->is_selected = 0;
                }
                if (in_array($valueItem->serial_city, $city_fund_ids)) {


                    $valueItem->is_fund_selected = 1;


                } else {
                    $valueItem->is_fund_selected = 0;
                }


            }
        }
        $array = [];

        /*
                for ($i = 0; $i < count($value); $i++) {


                    $requests_fund = RequestFund::WhereHas('city', function ($query) use ($value, $i) {
                        $query->where('serial_city', $value[$i]->serial_city);

                    })->count();

                    $requests_app = EstateRequest::WhereHas('city', function ($query) use ($value, $i) {
                        $query->where('serial_city', $value[$i]->serial_city);

                    })->count();

                    $estate_app = Estate::WhereHas('city', function ($query) use ($value, $i) {
                        $query->where('serial_city', $value[$i]->serial_city);

                    })->count();
                    $value[$i]->count_fund_request = $requests_fund;
                    $value[$i]->count_app_request = $requests_app;
                    $value[$i]->count_app_estate = $estate_app;


                }

        */
        $collection = CityResource::collection($value);

        return response()->success("Cities List", $value);
    }

    public function GolobalCites(Request $request)
    {


        //     $users = \App\Models\v3\District::where('city_id',3)->get()->toJson();
        $cities = \App\Models\v3\City3::query();


        if ($request->get('state_id')) {
            $cities = $cities->where('state_id', $request->get('state_id'));
        }

        if ($request->get('name')) {

            $cities = $cities->Where('name_ar', 'like', '%' . $request->get('name') . '%');


        }


        $cities = $cities->get();
        //  ->toJson();
        return response()->success("Cities List", $cities);

        //    return $cities;

        return response()->success("Cities List", $cities);
    }


    public function GolobalNeb(Request $request, $id)
    {

        $neb = \App\Models\v3\District::where('city_id', $id);
        //  $cities = \App\Models\v3\City3::query();


        if ($request->get('name')) {

            $neb = $neb->Where('name_ar', 'like', '%' . $request->get('name') . '%');


        }

        $neb = $neb->get();

        $user = Auth::user();


        foreach ($neb as $nebItem) {
            if ($user != null) {
                $myInterset = NebInterest::where('user_id', $user->id)
                    ->where('neb_id', $nebItem->district_id)
                    ->first();
                if ($myInterset) {
                    $nebItem->in_my_interset = '1';
                } else {
                    $nebItem->in_my_interset = '0';
                }


            } else {
                $nebItem->in_my_interset = '0';
            }
        }

        //    $neb=   $neb->toJson();

        return response()->success("Neb List", $neb);
        return $neb;

        return response()->success("Cities List", $cities);
    }

    public function AllGolobalNeb(Request $request)
    {

        $cities = City3::with('neb.boundaries')->get();
        foreach ($cities as $city) {
            Storage::put('log.txt', $city);
        }
       return $cities;
    }

    public function GolobalCitesWithNeb(Request $request)
    {


        //     $users = \App\Models\v3\District::where('city_id',3)->get()->toJson();
        $cities = \App\Models\v3\City3::with('neb');


        if ($request->get('state_id')) {
            $cities = $cities->where('state_id', $request->get('state_id'));
        }

        if ($request->get('name')) {

            $cities = $cities->Where('name_ar', 'like', '%' . $request->get('name') . '%');


        }


        $cities = $cities->get();
        //  ->toJson();
        $cities = GolobalCitesWithNebResource::collection($cities);
        return response()->success("Cities List", $cities);

        //    return $cities;

        return response()->success("Cities List", $cities);
    }

    public function Addinterest(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'neb_ids' => 'required',


        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }


        $array = explode(',', $request->get('neb_ids'));


        if ($array) {
            for ($i = 0; $i < count($array); $i++) {
                $check = NebInterest::where('user_id', $user->id)
                    ->where('neb_id', $array[$i])->first();

                if (!$check) {
                    $interset = NebInterest::create([
                        'user_id' => $user->id,
                        'neb_id' => $array[$i],

                    ]);
                }

            }
        }


        return response()->success("Done", []);
    }

    public function Removeinterest(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'neb_ids' => 'required',


        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }


        $array = explode(',', $request->get('neb_ids'));


        if ($array) {
            for ($i = 0; $i < count($array); $i++) {
                $check = NebInterest::where('user_id', $user->id)
                    ->where('neb_id', $array[$i])->first();

                if ($check) {
                    $check->delete();
                }

            }
        }


        return response()->success("Done Deleted", []);
    }

    public function myInterest()
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }
        $interiest = NebInterest::where('user_id', $user->id)->pluck('neb_id');


        // dd(count($interiest));
        $cities = City3::whereHas('neb', function ($q) use ($interiest) {
            $q->whereIn('district_id', $interiest->toArray());
        })->with(['neb' => function ($q) use ($interiest) {
            $q->whereIn('district_id', $interiest->toArray())
                ->select('name_ar', 'city_id', 'district_id');
        }
        ])->select('name_ar', 'city_id')->get();


        return response()->success("Neighborhood List", $cities);

    }

    public function neighborhoods($id, Request $request)
    {


//dd($request->all());
        /*
         *select * from `neighborhoods` where exists
         *  (select * from `request_funds`
         * inner join `fund_request_neighborhoods`
         * on `request_funds`.`neighborhood_serial` = `fund_request_neighborhoods`.`neighborhood_id`
         * where `neighborhoods`.`neighborhood_serial` = `fund_request_neighborhoods`.`request_fund_id`
         *  and `request_funds`.`deleted_at` is null) and `city_id` = 2574)
         */

        $id = substr($id, 0, 5);


        $Neighborhood = DB::table('neighborhoods')
            ->select('neighborhoods.*', 'neighborhoods.name_ar as name');

        if ($request->get('is_all') != '1' && $request->get('is_all') != '2' && $request->get('is_all') != '3' && $request->get('is_all') == '0') {


            $Neighborhood = $Neighborhood
                ->join('fund_request_neighborhoods', 'fund_request_neighborhoods.neighborhood_id', '=', 'neighborhoods.neighborhood_serial')
                ->whereRaw('fund_request_neighborhoods.deleted_at is null ');
        }


        if ($request->get('is_all') != '1' && $request->get('is_all') != '0' && $request->get('is_all') != '3' && $request->get('is_all') == '2') {
            //$value = $Neighborhood->whereHas('app_request');


            $Neighborhood = $Neighborhood
                ->join('estate_requests', 'estate_requests.neighborhood_id', '=', 'neighborhoods.neighborhood_serial')
                ->whereRaw('estate_requests.deleted_at is null ');
        }


        if ($request->get('is_all') != '1' && $request->get('is_all') != '0' && $request->get('is_all') != '2' && $request->get('is_all') == '3') {
            //  $value = $Neighborhood->whereHas('estate');


            $Neighborhood = $Neighborhood
                ->join('estates', 'estates.neighborhood_id', '=', 'neighborhoods.neighborhood_serial')
                ->whereRaw('estates.deleted_at is null ');
        }


        if ($request->get('name')) {

            /*  $Neighborhood = $Neighborhood
                  ->Where('search_name', 'like', '%' . $request->get('name') . '%');
  */
            $Neighborhood = $Neighborhood->whereRaw('search_name  like % ' . $request->get('name') . ' % ');

        }


        $Neighborhood = $Neighborhood->whereRaw('neighborhoods.city_id =' . $id)->get();

        /*
                $redis = Redis::connection();

               // $redis->set('user_details', json_encode($Neighborhood));


                $response = $redis->get('user_details');

                $response = json_decode($response);*/
        return response()->success("Neighborhood List", $Neighborhood);
    }


    public function citis_with_neb(Request $request)
    {

        $Neighborhood = DB::table('neighborhoods')
            // ->Join('cities','cities.serial_city','=', 'neighborhoods.city_id')
            // ->select('neighborhoods.*','cities.state_id');
            //   ->whereRaw('cities.deleted_at is null')
            //  ->whereRaw('neighborhoods.deleted_at is null')
            //  ->toSql();



        ;
        //  $Neighborhood = Neighborhood::query();
        if ($request->get('city_id')) {

            /*    $Neighborhood = $Neighborhood
                    ->WhereHas('city', function ($query) use ($request) {
                        $query->where('city_id', $request->get('city_id'));
                    });*/
            $Neighborhood = $Neighborhood->whereRaw('city_id =' . $request->get('city_id'));

        }
        if ($request->get('state_id')) {

            /*  $Neighborhood = $Neighborhood
                  ->WhereHas('city', function ($query) use ($request) {
                      $query->where('state_id', $request->get('state_id'));
                  });*/

            $Neighborhood = $Neighborhood->whereRaw('state_id =' . $request->get('state_id'));
            //->get();


        }
        if ($request->get('name')) {

            /* $Neighborhood = $Neighborhood
                 ->Where('search_name', 'like', '%' . $request->get('name') . '%');*/
            $Neighborhood = $Neighborhood->whereRaw('search_name LIKE "%' . $request->get('name') . '%"');

        }


        $Neighborhood = $Neighborhood->paginate();


        return response()->success("Neighborhood List", $Neighborhood);
    }

    public function comfort()
    {

        $Comfort = Comfort::get();
        return response()->success("Comfort", $Comfort);
    }

    public function showVideos()
    {

        $videos = Video::where('status', '1')->get();
        return response()->success("Videos", $videos);
    }

    public function single_video($id)
    {

        $video = Video::find($id);
        if ($video) {
            return response()->success("Video", $video);
        } else {
            return response()->error("no video found");
        }

    }


    public function rate_request_type()
    {

        $RateRequestType = RateRequestType::get();
        return response()->success("RateRequestType", $RateRequestType);
    }


    public function CodeCheck(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
            'code' => 'required',
            'offer_id' => 'required',


        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        return ['status' => true];
    }


    public function Contact(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'mobile' => 'required',
            'msg' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $contact = Contact::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'mobile' => $request->get('mobile'),
            'msg' => $request->get('msg'),
        ]);


        return response()->success("تم إرسال رسالتك بنجاح", []);
    }


    /*  public function plans()
      {

          $Plans = Plan::where('status', 1)->get();
          return response()->success("Plans List", $Plans);
      }*/

    public function Chooseplans(Request $request)
    {

        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }
        $uuid = uniqid();
        $user->unique_code = $uuid;
        $user->save();
        $text = 'رابط الدفع الخاص بك هو : ';
        $message = url('plans/' . $user->unique_code);


        $checkPay = UserPayment::where('is_send', 0)
            ->where('user_id', $user->id)->first();
        if (!$checkPay) {
            $user_payment = UserPayment::create([
                'user_id' => $user->id,
                'uuid' => $uuid,
            ]);
        }


        //  ini_set("smtp_port", "465");
        /*
                $to = $user->email;
                $from = 'Aqarz@info.com';
                $name = 'Aqarz';
                $subject = 'رابط الدفع';


                $logo = asset('logo.svg');
                $link = '#';

                $details = [
                    'to'       => $to,
                    'from'     => $from,
                    'logo'     => $logo,
                    'link'     => $link,
                    'subject'  => $subject,
                    'name'     => $name,
                    "message"  => $message,
                    "text_msg" => $text,
                ];


               // var_export (dns_get_record ( "host.name.tld") );

               // dd(444);
                \Mail::to($to)->send(new \App\Mail\NewMail($details));

               /* if (Mail::failures()) {
                    return response()->json([
                        'status'  => false,
                        'data'    => $details,
                        'message' => 'Nnot sending mail.. retry again...'
                    ]);
                }*/


        /* $user_mobile = checkIfMobileStartCode($user->mobile, $user->country_code);
         $unifonicMessage = new UnifonicMessage();
         $unifonicClient = new UnifonicClient();
         $unifonicMessage->content = "Your Verification Code Is: ";
         $to = $user_mobile;
         $co = $message;
         $data = $unifonicClient->sendCustomer($to, $co);
         Log::channel('single')->info($data);
         Log::channel('slack')->info($data);
         //  return $data;
 */
        return response()->success("تم تسجيل طلبك بنجاح");
        /*   }
       else {
       return response()->error("Not Found Plan");
       }*/


        /* if ($Plans) {


             $user_plan = UserPlan::create([
                 'plan_id'     => $Plans->id,
                 'user_id'     => $user->id,
                 'status'      => '0',
                 'unique_code' => $uuid,
                 'payment_url' => url('subscribe/plan/' . $uuid),
                 'count_try'   => 0,
                 'total'       => $Plans->price
             ]);

             $user_plan = UserPlan::findOrFail($user_plan->id);

             $message = url('subscribe/plan/' . $user_plan->unique_code) . 'رابط الدفع الخاص بك هو : ';


             //  ini_set("smtp_port", "465");

             $to = $user->email;
             $from = 'Aqarz@info.com';
             $name = 'Aqarz';
             $subject = 'رابط الدفع';


             $logo = asset('logo.png');
             $link = '#';

             $details = [
                 'to'      => $to,
                 'from'    => $from,
                 'logo'    => $logo,
                 'link'    => $link,
                 'subject' => $subject,
                 'name'    => $name,
                 "message" => $message
             ];


             \Mail::to($to)->send(new \App\Mail\NewMail($details));

             if (Mail::failures()) {
                 return response()->json([
                     'status'  => false,
                     'data'    => $details,
                     'message' => 'Nnot sending mail.. retry again...'
                 ]);
             }


             return response()->success("تم ارسال رابط الدفع الي هاتفك والبريد الالكتروني");
         } else {
             return response()->error("Not Found Plan");
         }*/

    }

    public function CountCall($id)
    {
        $user = User::findOrFail($id);
        if ($user) {
            $user->count_call = $user->count_call + 1;
            $user->save();
        }
        return response()->success("Count Call Plus", []);

    }


    public function checkLocation(Request $request)
    {
        $rules = Validator::make($request->all(), [


            'lat' => 'required',
            'lan' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        if (Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
            return response()->success("Saudi Location", ['status' => false]);
        } else {
            return response()->success("Saudi Location", ['status' => true]);
        }
    }


    public function updateDeviceToken(Request $request)
    {
        $rules = Validator::make($request->all(), [


            'device_token' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $user->device_token = $request->get('device_token');
        $user->save();

        return response()->success(__("views.Done"), $user);


    }


    public function regions()
    {

        $regine = Region::get();

        /* $requests = RequestFund::WhereHas('city', function ($query)  {
             $query->where('state_id', 4);

         })->count();


      dd($requests);*/
        for ($i = 0; $i < count($regine); $i++) {


            $requests = RequestFund::WhereHas('city', function ($query) use ($i, $regine) {
                $query->where('state_id', $regine[$i]->id);

            });


            $providers = User::where('type', 'provider')->WhereHas('city', function ($query) use ($i, $regine) {
                $query->where('state_id', $regine[$i]->id);

            });


            $offers = FundRequestOffer::WhereHas('estate.city', function ($query) use ($i, $regine) {
                $query->where('state_id', $regine[$i]->id);

            });

            $estate = Estate::WhereHas('city', function ($query) use ($i, $regine) {
                $query->where('state_id', $regine[$i]->id);

            });


            $app_request = EstateRequest::WhereHas('city', function ($query) use ($i, $regine) {
                $query->where('state_id', $regine[$i]->id);


            });


            $regine[$i]['requests'] = $requests->count();
            $regine[$i]['offers'] = $offers->count();
            $regine[$i]['providers'] = $providers->count();
            $regine[$i]['estate'] = $estate->count();
            $regine[$i]['app_request'] = $app_request->count();
            //$array[$regine[$i + 1]] = ['requests' => $requests->count(), 'offer' => $offers->count(),'providers'=>$providers->count()];
            //  $array[$regine[$i + 1]] = ['requests' => $requests->count(), 'offer' => $offers->count(),'providers'=>$providers->count()];
        }

        //  dd($Comfort);

        // $Comfort=    mb_convert_encoding($Comfort['boundaries'], 'UTF-8', 'UTF-8');

        // return JsonResponse::create($Comfort, 200, array('Content-Type'=>'application/json; charset=utf-8' ));
        /* $regine = \Cache::remember('regine', 22*60, function() use ($regine) {
             return $regine;
         });*/
        //    \Cache::forget('regions');
        /*    $regine=     \Cache::rememberForever('regions', function () use ($regine) {
                return $regine;
            });*/
        return response()->success("Region", $regine);
        return response()->json($Comfort, 200, [], JSON_UNESCAPED_UNICODE);
        // return ['data'=>$Comfort];
    }


    public function checkEmployee(Request $request)
    {

        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [

            // 'mobile'       => 'required',
            'mobile' => 'required',
            'country_code' => 'sometimes|required',
            'is_emp' => 'sometimes|required'

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $mobile = 0;

        if ($request->get('mobile')) {
            if (startsWith($request->get('mobile'), '0')) {
                $mobile = substr($request->get('mobile'), 1, strlen($request->get('mobile')));
            } else {
                if (startsWith($request->get('mobile'), '00')) {
                    $mobile = substr($request->get('mobile'), 2, strlen($request->get('mobile')));
                } else {
                    $mobile = trim($request->get('mobile'));
                }
            }
        }


        $checkIsCompny = User::where('mobile', $mobile)
            ->where('is_employee', 0)
            ->first();

        if ($checkIsCompny) {
            return response()->error(__("رقم الجوال غير مضاف كشركة"));
        }


        $checkIsCompny2 = User::where('mobile', $mobile)
            ->whereIn('is_employee', [1, 2])
            ->whereNotIn('employer_id', [null, $user->id])
            ->first();

        if ($checkIsCompny2) {
            return response()->error(__("الرقم مضاف لشركة سابقا"));
        }

        $checkIfEmp = Employee::where('emp_mobile', $mobile)
            ->first();


        if ($checkIfEmp && $request->get('is_emp') == 'yes') {
            $push_data = [
                'title' => __('views.You Have New Employee #') . $checkIfEmp->id,
                'body' => __('views.You Have New Employee #') . $checkIfEmp->id,
                'id' => $checkIfEmp->id,
                'user_id' => $checkIfEmp->user_id,
                'type' => 'employee',
            ];

            $note = NotificationUser::create([
                'user_id' => $checkIfEmp->user_id,
                'title' => 'لديك موظف جديد برقم :' . $checkIfEmp->id,
                'type' => 'employee',
                'type_id' => $checkIfEmp->id,
            ]);
            $client = User::where('id', $checkIfEmp->user_id)->first();
            if ($client) {
                send_push($client->device_token, $push_data, $client->device_type);
            }

            $user->is_employee = '2';
            $user->save();
            //    $client->count_emp=$client->count_emp+1;
            //  $client->save();

            return response()->success(__("views.Done"), $user);

        } elseif ($checkIfEmp && $request->get('is_emp') == 'no') {
            $user->is_employee = '0';
            $user->employer_id = null;
            $user->save();
            return response()->success(__("views.Done"), $user);
        } elseif (!$checkIfEmp) {
            $user->is_employee = '0';
            $user->employer_id = null;
            $user->save();
            return response()->success(__("views.Done"), $user);
        } else {
            return response()->error(__('انت لست موظف ضمن المكاتب'));
        }


    }


    public function sendReport(Request $request)
    {
        $rules = Validator::make($request->all(), [


            'report_type' => 'required',
            //  'body' => 'required',
            'estate_id' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = \Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $reprot = Report::create([
            'body' => $request->get('body'),
            'user_id' => $user->id,
            'estate_id' => $request->get('estate_id'),
            'report_type' => $request->get('report_type'),
        ]);

        $Ticket = Ticket::create([
            'body' => $request->get('body'),
            'user_id' => $user->id,
            'estate_id' => $request->get('estate_id'),


        ]);
        $TicketFirstChat = TicketChat::create([
            'message' => $request->get('body'),
            'user_id' => $user->id,
            'ticket_id' => $Ticket->id,
            'from_type' => 'user',

        ]);


        return response()->success(__("views.Done"), $reprot);


    }


    public function check_location(Request $request)
    {

        $rules = Validator::make($request->all(), [


            'lat' => 'required',
            'lan' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $neb_name = (checkPint("" . $request->get('lat') . " " . $request->get('lan')));
        if ($neb_name) {
            return response()->success(__("views.Done"), $neb_name);
        } else {
            return response()->error(__('الموقع غير مخزن'));

        }

    }


    public function checkPint(Request $request)
    {

        $rules = Validator::make($request->all(), [


            'lat' => 'sometimes|required',
            'lan' => 'sometimes|required',


        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $location = $request->get('lat') . ' ' . $request->get('lan');
      //  dd($location);
        $dis = checkPint("$location");

        $dis = District::find($dis);

        if (!$dis) {
            return response()->error(__("views.not found"));
        }
        $dis = CheckPointResource::collection([$dis]);
        return response()->success(__("views.Done"), $dis[0]);

    }
}
