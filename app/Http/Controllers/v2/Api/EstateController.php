<?php

namespace App\Http\Controllers\v2\Api;

use App\Events\CreateRequestOfferEvent;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\RequestFundOfferResource;

use App\Http\Resources\RequestOfferResource;
use App\Models\v2\City;
use App\Models\v2\DeferredInstallment;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\Finance;
use App\Models\v2\FinanceOffer;
use App\Models\v2\FundRequestHasOffer;
use App\Models\v2\FundRequestNeighborhood;
use App\Models\v2\FundRequestOffer;

use App\Models\v2\FundRequestSmsStatus;
use App\Models\v2\NotificationUser;
use App\Models\v2\RateEstate;
use App\Models\v2\RateOffer;
use App\Models\v2\RateRequest;
use App\Models\v2\RequestFund;

use App\Models\v2\RequestOffer;
use App\Models\v3\Favorite;
use App\Models\v4\FcmToken;
use App\User;
use Auth;
use Grimzy\LaravelMysqlSpatial\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Aws\ElastiCache\ElastiCacheClient;


class EstateController extends Controller
{


    public function fund_requests(Request $request)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [


            'uuid' => 'required',
            // 'estate_type_id' => 'required',
            'estate_type_id' => 'sometimes|required|exists:estate_types,id',
            // 'estate_status'    => 'required',
            'estate_status' => 'sometimes|required|in:1,2',
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


            "neighborhood_id" => "sometimes|required",
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

            }
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


            if ($RequestFund) {
                $array = explode(',', $request->get('neighborhood_id'));
                for ($i = 0; $i < count($array); $i++) {
                    $request_neighborhod = FundRequestNeighborhood::create([
                        'neighborhood_id' => $array[$i],
                        'request_fund_id' => $RequestFund->id,
                    ]);
                }

            }

        }

        CreateRequestOfferEvent::dispatch($RequestFund);

        $DeferredInstallment = RequestFund::find($RequestFund->id);
        $city = City::where('serial_city', $request->get('city_id'))->first();
        if ($city) {
            $city->count_fund_request = $city->count_fund_request + 1;
            $city->save();
        }
        return response()->success("RequestFund ", $DeferredInstallment);
        // return ['data' => $user];
    }

    public function home(Request $request)
    {


        /* $test=   Estate::search('تبوك')->get();

         return($test);*/
        /*  $lat = $request->get('lat');
          $lng = $request->get('lan');
          $radius = 1500000; // Value has to be in meters

          $Mechanic=    Estate::search('', function ($algolia, $query, $options) use ($lat, $lng, $radius) {
              $location =  [
                  'aroundLatLng' => $lat.','.$lng,
                  'aroundRadius' => $radius,
              ];

              $options = array_merge($options, $location);

              return $algolia->search($query, $options);
          });*/


        /* $user = Auth::user();
         if ($user == null) {
             return response()->error("not authorized");
         }*/

        $attitude = $request->get('lat');
        $longitude = $request->get('lan');
        $distanceL = 500;

        $distance = 50;


        //$otherCityPoint = City::where('id', 2)->value('center');


        /*$results = City::distance('center',$otherCityPoint,$distanceL)
         //   ->having('distance', '<=', $distanceL)->orderBy('distance', 'ASC')
            ->get();*/
        // distance($geometryColumn, $geometry, $distance)
        /* $query = City::distance($attitude, $longitude);
         $asc = $query->having('distance', '<=', $distanceL)->orderBy('distance', 'ASC')
             ->get();
       //  $asc= mb_convert_encoding($asc, 'UTF-8', 'UTF-8');
         return response()->success(__("views.Customer Requests"), $asc);*/
        // dd($asc);
        // $users_query = City::distanceSphere('center', $otherCityPoint, 5000)->get();

        /*   return($users_query);
           $users_query->whereHas('profile.city', function (Builder $query) use ($request,$otherCityPoint) {
               $query->distanceSphere('location', $otherCityPoint, $request->input('range') * 1000);  // 3 arg is meters
           });*/

        if ($attitude && $longitude) {
            $location = nearest($attitude, $longitude, $distanceL);
            $Mechanic = EstateRequest::whereBetween('lat', [$location->min_lat, $location->max_lat])
                ->whereBetween('lan', [$location->min_lng, $location->max_lng]);
        }
        /* $results = EstateRequest::distance($attitude, $longitude);
         $Mechanic = $results->having('distance', '<=', $distanceL);*/

        /*  $Mechanic = EstateRequest::select(\DB::raw("*,6371  * acos( cos( radians($attitude) ) * cos( radians( estate_requests.lat ) )
      * cos( radians(estate_requests.lan) - radians($longitude)) + sin(radians($attitude))
      * sin( radians(estate_requests.lat))) AS distance "))
               ->having('distance', '<', $distanceL);*/

        //  ->havingRaw("distance<=$distance")
        // ->select('id','distance')
        // ->select('id','c_latitude','c_longitude')
        //    ->pluck('distance', 'id');


        $distance = [];
        $ids = [];
        $i = 0;
        /*  foreach ($EstateRequest as $key => $value) {


              // dd($MechanicItem);

              /*  $MechanicItem->distances=$this->helper->distance($request->latitude, $request->longitude, $MechanicItem->c_latitude, $MechanicItem->c_longitude, "K");
                $MechanicItem->ids= $MechanicItem->id;
    */
        //   if ($EstateRequestItem->distance <= $distanceL) {
        /* $distance[$i] = distance($request->lat, $request->lan,
             $EstateRequestItem->lat, $EstateRequestItem->lan, "K");;*/
        /*      if ($value <= $distanceL) {
                  /* $distance[$i] = distance($request->lat, $request->lan,
                       $EstateRequestItem->lat, $EstateRequestItem->lan, "K");;*/
        /*         $ids[$i] = $key;
                 $i++;
             }
             //  }


         }


         $Mechanic = EstateRequest::whereIn('id', $ids);
         $j = 0;
 */

        if ($request->get('estate_type') && $request->get('estate_type') != null) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }


        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {
            $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_from') != null && $request->get('area_to') != null) {
            $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateRequest::find($request->get('search'))) {
                $Mechanic = $Mechanic->where('id', $request->get('search'));

            } else {
                $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('search') . '%');

            }


        }


        $Mechanic = $Mechanic->orderBy('updated_at', 'desc')->get();


        //    return $Mechanic;

        /*$Mechanic=      \DB::select('
        select *,6371 * acos( cos( radians(24.383015) ) * cos( radians( estate_requests.lat ) ) * cos( radians(estate_requests.lan) - radians(46.587682)) + sin(radians(24.383015)) * sin( radians(estate_requests.lat))) AS distance from `estate_requests`
        having `distance` < 50 order by `updated_at` DESC
        ');*/


        // return  $Mechanic;

        /*  foreach ($Mechanic as $MechanicItem) {

              $MechanicItem->distance = $distance[$j];
              $j++;
          }*/

        return response()->success(__("views.Customer Requests"), $Mechanic);

    }


    public function homeList(Request $request)
    {


        $Mechanic = EstateRequest::query();


        if ($request->get('estate_type') && $request->get('estate_type') != null) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }

        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {
            $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_from') != null && $request->get('area_to') != null) {
            $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }


        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $Mechanic = $Mechanic->whereIn('city_id', $estate);
        }

        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateRequest::find($request->get('search'))) {
                $Mechanic = $Mechanic->where('id', $request->get('search'));

            } else {
                $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('search') . '%');

            }


        }

        $Mechanic = $Mechanic->paginate();


        return response()->success(__("views.Customer Requests"), $Mechanic);

    }


    public function homeAqarz(Request $request)
    {


        /* $user = Auth::user();
         if ($user == null) {
             return response()->error("not authorized");
         }*/

        $attitude = $request->get('lat');
        $longitude = $request->get('lan');
        $distanceL = 100;


        /* $Mechanic = Estate::
         select(\DB::raw("*,6371  * acos( cos( radians($attitude) ) * cos( radians( estates.lat ) )
    * cos( radians(estates.lan) - radians($longitude)) + sin(radians($attitude))
    * sin( radians(estates.lat))) AS distance "))
             //  ->where('available', '1')
             //  ->limit(20)
             //  ->pluck('distance', 'id')//  ->get()
             ->having('distance', '<', $distanceL);;
 */

        if ($attitude && $longitude) {
            $location = nearest($attitude, $longitude, $distanceL);
            $Mechanic = Estate::whereBetween('lat', [$location->min_lat, $location->max_lat])
                ->whereBetween('lan', [$location->min_lng, $location->max_lng]);
        }

        //  return $EstateRequest;


        /* $distance = [];
         $ids = [];
         $i = 0;
         foreach ($EstateRequest as $key => $value) {


             //  dd($value);

             /*  $MechanicItem->distances=$this->helper->distance($request->latitude, $request->longitude, $MechanicItem->c_latitude, $MechanicItem->c_longitude, "K");
               $MechanicItem->ids= $MechanicItem->id;
   */
        /*     if ($value <= $distanceL) {
                 /* $distance[$i] = distance($request->lat, $request->lan,
                      $EstateRequestItem->lat, $EstateRequestItem->lan, "K");;*/
        //      $ids[$i] = $key;
        //  $i++;
        //   }


        /* }


        /* $Mechanic = Estate::whereIn('id', $ids);
         $j = 0;

 */
        $user = Auth::user();

        if ($user != null) {
            if ($user->hide_estate_id != null) {
                $hidded_aqarz = explode(',', $user->hide_estate_id);

                $Mechanic = $Mechanic->whereNotIn('id', $hidded_aqarz);
            }

        }

        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $Mechanic = $Mechanic->whereIn('city_id', $estate);
        }


        if ($request->get('state_id') && $request->get('state_id') != null) {

            $Mechanic = $Mechanic->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }
        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }
        if ($request->get('request_type') && $request->get('request_type') != null) {
            $Mechanic = $Mechanic->where('request_type', $request->get('request_type'));
        }

        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {
            $Mechanic->where('total_price', '>=', $request->get('price_from'));
            $Mechanic->where('total_price', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_from') != null && $request->get('area_to') != null) {
            $Mechanic->where('total_area', '>=', $request->get('area_from'));
            $Mechanic->where('total_area', '<=', $request->get('area_to'));
        }


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateRequest::find($request->get('search'))) {
                $Mechanic = $Mechanic->where('id', $request->get('search'));

            } else {
                $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                    ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                    ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
            }


        }

        $Mechanic = $Mechanic->orderBy('updated_at', 'desc')->get();


        /*  foreach ($Mechanic as $MechanicItem) {

              $MechanicItem->distance = $distance[$j];
              $j++;
          }*/

        return response()->success(__("views.Aqarz Estate"), $Mechanic);

    }


    public function homeCustomAqarz(Request $request)
    {
        $query = '';
        /*  $res = DB::select('
      SELECT cities_ksa.city_id,cities.id,cities.name_ar,cities_ksa.name_ar as name_ksa,cities.serial_city,cities_ksa.center FROM
    cities_ksa,cities WHERE
    cities_ksa.name_ar LIKE CONCAT("%", cities.name_ar, "%") and cities_ksa.state_code = cities.state_code ');


          dd($res);*/

        $rules = Validator::make($request->all(), [

            /*  'lat' => 'required',
              'lan' => 'required',
              'distance' => 'required',*/

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $Mechanic = '';
        $EstateRequest = '';

        if ($request->get('lat') && $request->get('lan') && $request->get('distance')) {


            $attitude = $request->get('lat');
            $longitude = $request->get('lan');
            $distanceL = $request->get('distance');


            if ($attitude && $longitude) {
                $location = nearest($attitude, $longitude, $distanceL);
                $Mechanic = Estate::whereBetween('lat', [$location->min_lat, $location->max_lat])
                    ->whereBetween('lan', [$location->min_lng, $location->max_lng]);
            }

//sample script
            /*$Mechanic = Estate::
            select(\DB::raw("*,6371  * acos( cos( radians($attitude) ) * cos( radians( estates.lat ) )
   * cos( radians(estates.lan) - radians($longitude)) + sin(radians($attitude))
   * sin( radians(estates.lat))) AS distance "))
                ->having('distance', '<', $distanceL);*/


            $distance = [];
            $ids = [];
            $i = 0;
            /*  foreach ($EstateRequest as $key => $value) {


                  //  dd($value);

                  /* $MechanicItem->distances=$this->helper->distance($request->latitude, $request->longitude, $MechanicItem->c_latitude, $MechanicItem->c_longitude, "K");
                   $MechanicItem->ids= $MechanicItem->id;*/

            /*    if ($value <= $distanceL) {
                    /* $distance[$i] = distance($request->lat, $request->lan,
                         $EstateRequestItem->lat, $EstateRequestItem->lan, "K");;*/
            /*       $ids[$i] = $key;
                   $i++;
               }

          // }


           $j = 0;
       }



*/
        } else {

            $Mechanic = Estate::query();

        }


        //  return $EstateRequest;


        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $Mechanic = $Mechanic->whereIn('city_id', $estate);
        }


        $user = Auth::user();

        if ($user != null) {
            if ($user->hide_estate_id != null) {
                $hidded_aqarz = explode(',', $user->hide_estate_id);

                $Mechanic = $Mechanic->whereNotIn('id', $hidded_aqarz);
            }

        }


        if ($request->get('state_id') && $request->get('state_id') != null) {

            $Mechanic = $Mechanic->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }
        if ($request->get('estate_type') && $request->get('estate_type') != null) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }
        if ($request->get('request_type') && $request->get('request_type') != null) {
            $Mechanic = $Mechanic->where('request_type', $request->get('request_type'));
        }

        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {
            $Mechanic->where('total_price', '>=', $request->get('price_from'));
            $Mechanic->where('total_price', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_from') != null && $request->get('area_to') != null) {
            $Mechanic->where('total_area', '>=', $request->get('area_from'));
            $Mechanic->where('total_area', '<=', $request->get('area_to'));
        }


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateRequest::find($request->get('search'))) {
                $Mechanic = $Mechanic->where('id', $request->get('search'));

            } else {
                $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                    ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                    ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
            }


        }

        $Mechanic = $Mechanic->orderBy('updated_at', 'desc')->paginate(30);
        /*  $time_end = microtime(true);
          $execution_time = ($time_end - $time_start);
          echo '<b>Total Execution Time:</b> '.($execution_time).'Milliseconds';*/

        return response()->success(__("views.Aqarz Estate"), $Mechanic);

    }


    public function homeAqarzList(Request $request)
    {


        $user = Auth::user();


        $Mechanic = Estate::query();


        if ($user != null) {
            if ($user->hide_estate_id != null) {
                $hidded_aqarz = explode(',', $user->hide_estate_id);

                $Mechanic = $Mechanic->whereNotIn('id', $hidded_aqarz);
            }

        }

        if ($request->get('city_id')) {

            $estate = explode(',', $request->get('city_id'));

            $Mechanic = $Mechanic->whereIn('city_id', $estate);
        }


        if ($request->get('estate_type')) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }
        if ($request->get('request_type')) {
            $Mechanic = $Mechanic->where('request_type', $request->get('request_type'));
        }

        if ($request->get('estate_pay_type')) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
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


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateRequest::find($request->get('search'))) {
                $Mechanic = $Mechanic->where('id', $request->get('search'));

            } else {
                $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                    ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                    ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
            }


        }


        if ($request->get('state_id')) {

            $Mechanic = $Mechanic->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }

        $Mechanic = $Mechanic->orderBy('updated_at', 'desc')->paginate();


        return response()->success(__("views.Aqarz Estate"), $Mechanic);

    }


    public function single_estate($id)
    {
        $EstateRequest = Estate::with('plannedFile', 'EstateFile', 'comforts', 'user', 'rates')->find($id);

//
        $user = Auth::user();


        if (!$EstateRequest) {
            return response()->error("NOT Found", []);
        }

        $EstateRequest->seen_count += 1;

        $EstateRequest->save();

        if($user != null)
        {
            $fav=Favorite::where('type','estate')
                ->where('user_id',$user->id)
                ->where('type_id',$id)->first();

            if($fav)
            {
                $EstateRequest->my_fav=1;
            }
            else
            {
                $EstateRequest->my_fav=0;
            }
        }
        else
        {
            $EstateRequest->my_fav=0;
        }
        return response()->success(__("views.Estate"), $EstateRequest);
    }


    public function smilier_estate($id)
    {

        $estate = Estate::find($id);
        $price = '';
        $priceArray = explode(',', $estate->total_price);
        for ($i = 0; $i < count($priceArray); $i++) {
            $price .= $priceArray[$i];
        }


        // dd( $price - 5000);
        if ($estate) {
            $EstateRequest = Estate::where('available', '1')
                ->where('estate_type_id', $estate->estate_type_id)
                ->where('operation_type_id', $estate->operation_type_id)
                ->where('city_id', $estate->city_id)
                ->where('total_price', '<', $price + 50000)
                ->where('total_price', '>', $price - 50000)
                ->limit(5)->get();
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);

        }


        return response()->success(__("views.Smilier Estate"), $EstateRequest);
    }


    public function user_estate($id)
    {
        $EstateRequest = Estate::where('available', '1')->where('user_id', $id)->get();

        return response()->success(__("views.User Estate"), $EstateRequest);
    }

    public function single_request($id)
    {
        $EstateRequest = EstateRequest::with('user')->findOrFail($id);
        if (!$EstateRequest) {
            return response()->error(__('views.No Data'), []);
        }
        $EstateRequest->seen_count += 1;
        $EstateRequest->save();
        return response()->success(__("views.EstateRequest"), $EstateRequest);
    }

    public function active_finice_estate(Request $request)
    {


        $finiceing = Finance::where('status', 1);


        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {
            $finiceing = $finiceing->where('estate_type_id', $request->get('estate_type_id'));
        }
        if ($request->get('job_type') && $request->get('job_type') != null) {
            $finiceing = $finiceing->where('job_type', $request->get('job_type'));
        }
        if ($request->get('finance_interval') && $request->get('finance_interval') != null) {
            $finiceing = $finiceing->where('finance_interval', $request->get('finance_interval'));
        }
        if ($request->get('engagements') && $request->get('engagements') != null) {
            $finiceing = $finiceing->where('engagements', $request->get('engagements'));
        }
        if ($request->get('city_id') && $request->get('city_id') != null) {
            $finiceing = $finiceing->where('city_id', $request->get('city_id'));
        }
        if ($request->get('total_salary') && $request->get('total_salary') != null) {
            $finiceing = $finiceing->where('total_salary', $request->get('total_salary'));
        }
        if ($request->get('available_amount') && $request->get('available_amount') != null) {
            $finiceing = $finiceing->where('available_amount', $request->get('available_amount'));
        }

        $finiceing = $finiceing->get();

        return response()->success("Finance Requests", $finiceing);

    }


    public function send_offer_fundToDO(Request $request)
    {

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $estate = Estate::whereHas('EstateFile')
            ->whereHas('user')->where('id', $request->get('estate_id'))->first();
        if ($estate) {
            if ($estate->operation_type_id != 1) {
                return response()->error(__("يجب ان تكون العروض المضافة من نوع بيع فقط"));
            }
        }

        if (!$estate) {
            return response()->error(__("views.estate dont match the request"));
        }
        $rules = Validator::make($request->all(), [


            'uuid' => 'sometimes|required|exists:request_funds,uuid',
            // 'estate_type_id' => 'required',
            //    'estate_id'  => 'sometimes|required|exists:estates,id',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $EstateRequest = RequestFund::where('uuid', $request->get('uuid'))->first();


        $checkOfferEx = FundRequestOffer::where('uuid', $request->get('uuid'))
            ->where('provider_id', $user->id)
            ->where('estate_id', $request->get('estate_id'))
            ->first();

        $checkOffer = FundRequestOffer::where('uuid', $request->get('uuid'))
            ->first();
        if (!$checkOffer) {
            $content = file_get_contents(url("api/sms/send?Uuid=" . trim($request->get('uuid')) . ""));


            $data = json_decode($content);
            // if ($data->status == false) {


            //  return response()->error($data->msg, []);
            // }
            $msg = FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'send_sms',

            ]);
            // Log::channel('slack')->info(json_encode($data->msg));
            //  Log::channel('slack')->info(json_encode($request->all()));
            //   Log::channel('slack')->info(json_encode($data));
        }

        if (!$checkOfferEx) {


            //   }

            $user = Auth::user();
            $user->count_fund_offer = $user->count_fund_offer + 1;
            $user->save();


            // $user->count_request =  $user->count_request+1;
            $user->save();
            $estate = explode(',', $request->get('estate_id'));
            for ($i = 0; $i < count($estate); $i++) {
                $checkOffer = FundRequestOffer::where('provider_id', $user->id)
                    ->where('estate_id', $estate[$i])
                    ->where('uuid', $request->get('uuid'))->first();


                if (!$checkOffer) {
                    $FundRequestOffer = '';
                    if ($EstateRequest) {
                        $request->merge([
                            'provider_id' => $user->id,
                            'estate_id' => $estate[$i],
                            'status' => 'new',
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
                            'request_id',


                        ]));


                        if ($FundRequestOffer) {


                            $checkHasOffer = FundRequestHasOffer::where('uuid', $request->get('uuid'))->first();

                            if (!$checkHasOffer) {
                                $FundRequestOffer = FundRequestHasOffer::create($request->only([
                                    'uuid',
                                ]));
                            }

                            $EstateRequest->count_offers = $EstateRequest->count_offers + 1;
                            $EstateRequest->save();


                        }

                        //  $FundRequestOffer = FundRequestOffer::findOrFail($FundRequestOffer->id);


                        /*  $EstateRequest->offer_numbers = $EstateRequest->offer_numbers!=null?$EstateRequest->offer_numbers.','.$finice->id:$finice->id;
                          $EstateRequest->save();*/
                        /*    return response()->success("FundRequestOffer", $FundRequestOffer);
                        } else {
                            return response()->error("not found", []);
                        }*/
                    }
                }


            }
        } else {
            if ($user == null) {
                return response()->error(__("views.offer send privies"));
            }
        }

        return response()->success(__("views.FundRequestOffer"), []);
    }

    public function send_offer_fund(Request $request)
    {

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $estate = Estate::whereHas('EstateFile')
            ->whereHas('user')->where('id', $request->get('estate_id'))->first();
        if ($estate) {
            if ($estate->operation_type_id != 1) {
                return response()->error(__("يجب ان تكون العروض المضافة من نوع بيع فقط"));
            }
        }

        if (!$estate) {
            return response()->error(__("views.estate dont match the request"));
        }
        $rules = Validator::make($request->all(), [


            'uuid' => 'sometimes|required|exists:request_funds,uuid',
            // 'estate_type_id' => 'required',
            //    'estate_id'  => 'sometimes|required|exists:estates,id',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $EstateRequest = RequestFund::where('uuid', $request->get('uuid'))->first();


        $checkOfferEx = FundRequestOffer::where('uuid', $request->get('uuid'))
            ->where('provider_id', $user->id)
            ->where('estate_id', $request->get('estate_id'))
            ->first();

        $checkOffer = FundRequestOffer::where('uuid', $request->get('uuid'))
            ->first();
        if (!$checkOffer) {
            $content = file_get_contents(url("api/sms/send?uuid=" . trim($request->get('uuid')) . ""));


            $data = json_decode($content);
            // if ($data->status == false) {


            //  return response()->error($data->msg, []);
            // }
            $msg = FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'send_sms',

            ]);
            // Log::channel('slack')->info(json_encode($data->msg));
            //  Log::channel('slack')->info(json_encode($request->all()));
            //   Log::channel('slack')->info(json_encode($data));
        }

        if (!$checkOfferEx) {


            //   }

            $user = Auth::user();
            $user->count_fund_offer = $user->count_fund_offer + 1;
            $user->save();


            // $user->count_request =  $user->count_request+1;
            $user->save();
            $estate = explode(',', $request->get('estate_id'));
            for ($i = 0; $i < count($estate); $i++) {
                $checkOffer = FundRequestOffer::where('provider_id', $user->id)
                    ->where('estate_id', $estate[$i])
                    ->where('uuid', $request->get('uuid'))->first();


                if (!$checkOffer) {
                    $FundRequestOffer = '';
                    if ($EstateRequest) {
                        $request->merge([
                            'provider_id' => $user->id,
                            'estate_id' => $estate[$i],
                            'status' => 'new',
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
                            'request_id',


                        ]));


                        if ($FundRequestOffer) {


                            $checkHasOffer = FundRequestHasOffer::where('uuid', $request->get('uuid'))->first();

                            if (!$checkHasOffer) {
                                $FundRequestOffer = FundRequestHasOffer::create($request->only([
                                    'uuid',
                                ]));
                            }

                            $EstateRequest->count_offers = $EstateRequest->count_offers + 1;
                            $EstateRequest->save();


                        }

                        //  $FundRequestOffer = FundRequestOffer::findOrFail($FundRequestOffer->id);


                        /*  $EstateRequest->offer_numbers = $EstateRequest->offer_numbers!=null?$EstateRequest->offer_numbers.','.$finice->id:$finice->id;
                          $EstateRequest->save();*/
                        /*    return response()->success("FundRequestOffer", $FundRequestOffer);
                        } else {
                            return response()->error("not found", []);
                        }*/
                    }
                }


            }
        } else {
            if ($user == null) {
                return response()->error(__("views.offer send privies"));
            }
        }

        return response()->success(__("views.FundRequestOffer"), []);
    }
    public function update_offer_fund(Request $request)
    {


        $rules = Validator::make($request->all(), [


            'id' => 'sometimes|required|exists:fund_request_offers,id',
            // 'estate_type_id' => 'required',
            'estate_id' => 'sometimes|required|exists:estates,id',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $checkOffer = FundRequestOffer::findOrFail($request->get('id'));
        if ($checkOffer) {
            $checkOffer->estate_id = $request->get('estate_id');
            $checkOffer->save();
        }


        return response()->success(__("views.Updated FundRequestOffer"), []);
    }

    public function delete_offer_fund(Request $request)
    {

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [


            'id' => 'sometimes|required|exists:fund_request_offers,id',
            // 'estate_type_id' => 'required',
            //    'estate_id'  => 'sometimes|required|exists:estates,id',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $checkOffer = FundRequestOffer::findOrFail($request->get('id'));
        if ($checkOffer) {

            $user->count_fund_offer = $user->count_fund_offer - 1;
            if ($checkOffer->status == 'accepted_customer') {
                $user->count_accept_offer = $user->count_accept_offer - 1;
            }
            if ($checkOffer->status == 'sending_code') {
                $user->count_preview_fund_offer = $user->count_preview_fund_offer - 1;
            }
            $user->save();

            $requestFund = RequestFund::where('uuid', $checkOffer->uuid)->first();

            if ($requestFund->count_offers == 1) {
                $requestFund->status = 'new';
            }
            $requestFund->count_offers = $requestFund->count_offers - 1;


            $requestFund->save();
            $checkOffer->delete();


        }


        return response()->success(__("views.Deleted Successfully"), []);
    }

    public function myEstate(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $estate = Estate::where('user_id', $user->id);
        //->where('available', '1');

        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $estate = $estate->whereIn('city_id', $estate);
        }


        $estate = $estate->orderBy('id', 'desc')->paginate(20);
        if ($estate) {
            return response()->success(__("views.Estates"), $estate);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }


    public function myRequest(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $Mechanic = EstateRequest::where('user_id', $user->id)
            ->orwhere('user_id', $user->related_company);

        $estate = '';

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


            $estate = explode(',', $request->get('estate_type_id'));


            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }
        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $Mechanic = $Mechanic->whereIn('city_id', $estate);
        }


        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {
            $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_from') != null && $request->get('area_to') != null) {
            $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search') && $request->get('search') != null) {


            $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('area_to') . '%');

        }

        $Mechanic = $Mechanic->paginate();
        if ($Mechanic) {
            return response()->success(__("views.EstateRequest"), $Mechanic);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }


    public function demandsRequest2(Request $request)
    {
        $user = Auth::user();


        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $Mechanic = EstateRequest::query()->get();


        $allRequestFund = EstateRequest::query();

        $estate = '';

        if ($request->get('today') && $request->get('today') != null) {
            $date = date('Y-m-d');
            $Mechanic = $Mechanic->whereDate('created_at', $date);
        }


        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


            $estate = explode(',', $request->get('estate_type_id'));


            $user->saved_filter_type = $request->get('estate_type_id');
            $user->save();
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
            $allRequestFund = $allRequestFund->whereIn('estate_type_id', $estate);
        }

        if ($request->get('city_id') && $request->get('city_id') != null) {


            $estate = explode(',', $request->get('city_id'));

            $user->saved_filter_city = $request->get('city_id');
            $user->save();
            $Mechanic = $Mechanic->whereIn('city_id', $estate);
            $allRequestFund = $allRequestFund->whereIn('city_id', $estate);
        }


        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
                //  $allRequestFund = $allRequestFund->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
                //  $allRequestFund = $allRequestFund->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
                // $allRequestFund = $allRequestFund->where('operation_type_id', 3);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {


            $Mechanic = $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic = $Mechanic->where('price_to', '<=', $request->get('price_to'));

            // $allRequestFund = $allRequestFund->where('price_from', '>=', $request->get('price_from'));
            // $allRequestFund = $allRequestFund->where('price_to', '<=', $request->get('price_to'));


        }


        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_to') != null && $request->get('area_from') != null) {


            $Mechanic = $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic = $Mechanic->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search') && $request->get('search') != null) {


            $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('area_to') . '%');

        }

        if ($request->get('myOwn') && $request->get('myOwn') != null) {

            $Mechanic = $Mechanic->whereHas('offers', function ($query) use ($user) {
                $query->where('provider_id', $user->id);
            });;
        }
        if ($request->get('price') && $request->get('price') != null) {

            if ($request->get('price') == 'low') {
                $Mechanic = $Mechanic->orderBy('price_to', 'asc');
            } else {
                $Mechanic = $Mechanic->orderBy('price_to', 'desc');
            }
        }


        $Mechanic = $Mechanic->orderBy('id', 'desc')->paginate();


        if ($Mechanic) {
            return response()->success(__("views.EstateRequest"), $Mechanic);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }

    public function demandsRequest(Request $request)
    {
        $user = Auth::user();


        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $myRequestFundOffer = 0;
        if ($user != null) {
            $myRequestFundOffer = RequestOffer::whereHas('provider')->whereHas('estate')->whereHas('request')->where('provider_id',
                $user->id);

        } else {
            $myRequestFundOffer = 0;

        }


        $Mechanic = EstateRequest::query();
        $allRequestFund = EstateRequest::query();

        $date = date('Y-m-d');
        $RequestEstate = EstateRequest::whereDate('created_at', $date);
        $estate = '';

        if ($request->get('today') && $request->get('today') != null) {
            $date = date('Y-m-d');
            $Mechanic = $Mechanic->whereDate('created_at', $date);
        }


        if ($request->get('state_id') && $request->get('state_id') != null
        ) {


            $Mechanic = $Mechanic->whereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });


            $allRequestFund = $allRequestFund->whereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });


            $RequestEstate = $RequestEstate->whereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


            $estate = explode(',', $request->get('estate_type_id'));


            $user->saved_filter_type = $request->get('estate_type_id');
            $user->save();
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
            $allRequestFund = $allRequestFund->whereIn('estate_type_id', $estate);
            $RequestEstate = $RequestEstate->whereIn('estate_type_id', $estate);
        }

        if ($request->get('city_id') && $request->get('city_id') != null) {


            $estate = explode(',', $request->get('city_id'));

            $user->saved_filter_city = $request->get('city_id');
            $user->save();
            $Mechanic = $Mechanic->whereIn('city_id', $estate);
            $allRequestFund = $allRequestFund->whereIn('city_id', $estate);
            $RequestEstate = $RequestEstate->whereIn('city_id', $estate);
        }
        if ($request->get('neighborhood_id') && $request->get('neighborhood_id') != null) {


            $estate = explode(',', $request->get('neighborhood_id'));

            $user->saved_filter_city = $request->get('neighborhood_id');
            $user->save();
            $Mechanic = $Mechanic->whereIn('neighborhood_id', $estate);
            $allRequestFund = $allRequestFund->whereIn('neighborhood_id', $estate);
            $RequestEstate = $RequestEstate->whereIn('neighborhood_id', $estate);
        }


        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $Mechanic = $Mechanic->where('operation_type_id', 2);
                $allRequestFund = $allRequestFund->where('operation_type_id', 2);
                $RequestEstate = $RequestEstate->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $Mechanic = $Mechanic->where('operation_type_id', 1);
                $allRequestFund = $allRequestFund->where('operation_type_id', 1);
                $RequestEstate = $RequestEstate->where('operation_type_id', 1);
            } else {
                $Mechanic = $Mechanic->where('operation_type_id', 3);
                $allRequestFund = $allRequestFund->where('operation_type_id', 3);
                $RequestEstate = $RequestEstate->where('operation_type_id', 3);
            }


        }


        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {


            $Mechanic = $Mechanic->where('price_from', '>=', $request->get('price_from'));
            $Mechanic = $Mechanic->where('price_to', '<=', $request->get('price_to'));

            $allRequestFund = $allRequestFund->where('price_from', '>=', $request->get('price_from'));
            $allRequestFund = $allRequestFund->where('price_to', '<=', $request->get('price_to'));

            $RequestEstate = $RequestEstate->where('price_from', '>=', $request->get('price_from'));
            $RequestEstate = $RequestEstate->where('price_to', '<=', $request->get('price_to'));


        }


        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_to') != null && $request->get('area_from') != null) {


            $Mechanic = $Mechanic->where('area_from', '>=', $request->get('area_from'));
            $Mechanic = $Mechanic->where('area_to', '<=', $request->get('area_to'));

            $allRequestFund = $allRequestFund->where('area_from', '>=', $request->get('area_from'));
            $allRequestFund = $allRequestFund->where('area_to', '<=', $request->get('area_to'));

            $RequestEstate = $RequestEstate->where('area_from', '>=', $request->get('area_from'));
            $RequestEstate = $RequestEstate->where('area_to', '<=', $request->get('area_to'));


        }


        if ($request->get('search') && $request->get('search') != null) {


            $Mechanic = $Mechanic->where('request_type', 'like', '%' . $request->get('area_to') . '%');

        }

        if ($request->get('myOwn') && $request->get('myOwn') != null) {

            $Mechanic = $Mechanic->whereHas('offers', function ($query) use ($user) {
                $query->where('provider_id', $user->id);
            });
        }
        if ($request->get('price') && $request->get('price') != null) {

            if ($request->get('price') == 'low') {
                $Mechanic = $Mechanic->orderBy('price_to', 'asc');
                $allRequestFund = $allRequestFund->orderBy('price_to', 'asc');
                $RequestEstate = $RequestEstate->orderBy('price_to', 'asc');
            } else {
                $Mechanic = $Mechanic->orderBy('price_to', 'desc');
                $RequestEstate = $RequestEstate->orderBy('price_to', 'desc');
            }
        }


        $Mechanic = $Mechanic->orderBy('id', 'desc')->paginate();
        foreach ($Mechanic->items() as $finiceingItem) {
            $offer = RequestOffer::where('id', $finiceingItem->request_id)
                ->where('provider_id', $user->id)->first();

            if ($offer) {
                $finiceingItem->has_my_offer = 1;
            } else {
                $finiceingItem->has_my_offer = 0;
            }
        }


        if ($user != null) {
            $myRequestFundOffer = $myRequestFundOffer->count();

        } else {
            $myRequestFundOffer = 0;

        }


        $allRequestFund = $allRequestFund->count();


        $RequestEstate = $RequestEstate->count();

        return Response::json([
            "status" => true,
            "allRequestd" => $allRequestFund,
            "Request" => $RequestEstate,
            "myRequestOffer" => $myRequestFundOffer,
            "message" => "طلبات السوق",
            "data" => $Mechanic,
            'code' => 200
        ]);


        if ($Mechanic) {
            return response()->success(__("views.EstateRequest"), $Mechanic);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }

    // public function


    public function approval_offer()
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $finiceingOfferArray = FundRequestOffer::where('provider_id', $user->id)
            ->where('status', 'active')->get();
        if ($finiceingOfferArray) {
            return response()->success(__("views.Approval Offer"), $finiceingOfferArray);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }


    public function send_offer_status(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $finiceingOfferArray = FinanceOffer::findOrFail($request->get('offer_id'));
        if ($finiceingOfferArray) {

            $finice = Finance::findOrFail($finiceingOfferArray->finance_id);
            if (!$finice) {
                return response()->error(__("views.not found"), []);
            }
            if ($finiceingOfferArray->status != 'rejected' && $finiceingOfferArray->status != 'accepted') {
                $finiceingOfferArray->status = $request->get('status');
                $finiceingOfferArray->save();
                $finice->status = $request->get('status') == 'accepted' ? 'provider_accepted' : 'provider_rejected';
                $finice->save();
                return response()->success("Offer " . $request->get('status') . " ", $finiceingOfferArray);
            }


        } else {
            return response()->error(__("views.not found"), []);
        }
    }


    public function customer_offer(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $finiceingOfferArray = Finance::where('user_id', $user->id)
            ->where('status', 'provider_accepted')->get();
        if ($finiceingOfferArray) {

            return response()->success("Offers", $finiceingOfferArray);

        } else {
            return response()->error("not found offer", []);
        }
    }

    public function send_customer_offer_statusToDO(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("veiws.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
            'code' => 'required',
            'offer_id' => 'required',
            //   'status' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $EstateRequest = \App\Models\v3\RequestFund::where('uuid', $request->get('uuid'))->first();

        $content = file_get_contents(url("api/code/check?Uuid=" . $request->get('Uuid') . "&OtpCode=" . $request->get('code') . "&OfferId=" . $request->get('offer_id') . ""));
        // $symbols = json_decode($content->body, true);


        $data = json_decode($content);


        if ($data->code == 1000) {
            $msg = \App\Models\v3\FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'check_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));


            /*   if ($data->status == false) {
                   return response()->error($data->msg, []);
               }*/
            $Finance = RequestFund::where('uuid', $request->get('uuid'))
                //->where('code',$request->get('uuid'))
                ->first();
            if ($Finance) {
                $offer = \App\Models\v3\FundRequestOffer::where('uuid', $Finance->uuid)
                    // ->where('code',$request->get('code'))
                    ->where('status', 'waiting_code')->first();

                if (!$offer) {
                    return response()->error(__("views.code is miss"), []);
                }

                $offer->status = $request->get('status');
                if ($request->get('status') == 'accepted_customer') {
                    $offer->accepted_at = date('Y-m-d');
                }
                if ($request->get('status') == 'rejected_customer') {
                    $offer->cancel_at = date('Y-m-d');
                }
                $offer->save();
                $Finance->status = $request->get('status');
                $Finance->save();

                return response()->success("Offer " . $request->get('status') . " ", $Finance);
            } else {
                return response()->error(__("views.not found"), []);
            }
        } else {
            $msg = FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'check_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));

            return response()->error(json_encode($data->msg), []);
        }


    }
    public function send_customer_offer_status(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("veiws.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
            'code' => 'required',
       //     'offer_id' => 'required',
            //   'status' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $EstateRequest = \App\Models\v3\RequestFund::where('uuid', $request->get('uuid'))->first();

        $content = file_get_contents(url("api/code/check?uuid=" . $request->get('uuid') . "&code=" . $request->get('code'). ""));
        // $symbols = json_decode($content->body, true);


        $data = json_decode($content);


        if ($data->code == 1000) {
            $msg = \App\Models\v3\FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'check_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));


            /*   if ($data->status == false) {
                   return response()->error($data->msg, []);
               }*/
            $Finance = RequestFund::where('uuid', $request->get('uuid'))
                //->where('code',$request->get('uuid'))
                ->first();
            if ($Finance) {
                $offer = \App\Models\v3\FundRequestOffer::where('uuid', $Finance->uuid)
                    // ->where('code',$request->get('code'))
                    ->where('status', 'waiting_code')->first();

                if (!$offer) {
                    return response()->error(__("views.code is miss"), []);
                }

                $offer->status = $request->get('status');
                if ($request->get('status') == 'accepted_customer') {
                    $offer->accepted_at = date('Y-m-d');
                }
                if ($request->get('status') == 'rejected_customer') {
                    $offer->cancel_at = date('Y-m-d');
                }
                $offer->save();
                $Finance->status = $request->get('status');
                $Finance->save();

                return response()->success("Offer " . $request->get('status') . " ", $Finance);
            } else {
                return response()->error(__("views.not found"), []);
            }
        } else {
            $msg = FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'check_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));

            return response()->error(json_encode($data->msg), []);
        }


    }

    public function send_customer_codeToDO(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
            'offer_id' => 'required',

            //   'status' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $EstateRequest = \App\Models\v3\RequestFund::where('uuid', $request->get('uuid'))->first();

        $content = file_get_contents(url("api/code/send?uuid=" . trim($request->get('uuid') . "&OfferId=". trim($request->get('offer_id') .""))));
        // $symbols = json_decode($content->body, true);


        $data = json_decode($content);


        if ($data->code == 1000) {
            $msg = \App\Models\v3\FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'send_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));
            $offer = \App\Models\v3\FundRequestOffer::where('uuid', $request->get('uuid'))
                // ->where('code',$request->get('code'))
                ->where('status', 'sending_code')
                ->first();
            $offer->status = 'waiting_code';
            $offer->accept_review_at = date('Y-m-d');
            $offer->save();
            return response()->success("تم إرسال الكود لهاتف العميل");
        } else {
            $msg = FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'send_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));

            return response()->error(json_encode($data->msg), []);
        }


        /*   if ($data->status == false) {
               return response()->error($data->msg, []);
           }*/


    }
    public function send_customer_code(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
         //   'offer_id' => 'required',

            //   'status' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $EstateRequest = \App\Models\v3\RequestFund::where('uuid', $request->get('uuid'))->first();

        $content = file_get_contents(url("api/code/send?uuid=" . trim($request->get('uuid')).""));
        // $symbols = json_decode($content->body, true);


        $data = json_decode($content);


        if ($data->code == 1000) {
            $msg = \App\Models\v3\FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'send_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));
            $offer = \App\Models\v3\FundRequestOffer::where('uuid', $request->get('uuid'))
                // ->where('code',$request->get('code'))
                ->where('status', 'sending_code')
                ->first();
            $offer->status = 'waiting_code';
            $offer->accept_review_at = date('Y-m-d');
            $offer->save();
            return response()->success("تم إرسال الكود لهاتف العميل");
        } else {
            $msg = FundRequestSmsStatus::create([
                'uuid' => $request->get('uuid'),
                'request_id' => $EstateRequest->id,
                'status' => $data->status,
                'error_msg' => $data->msg,
                'code' => $data->code,
                'type' => 'send_code',

            ]);
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($msg));

            return response()->error(json_encode($data->msg), []);
        }


        /*   if ($data->status == false) {
               return response()->error($data->msg, []);
           }*/


    }


    public function resendSendCode(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
         //   'offer_id' => 'required',
            //   'status' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $EstateRequest = \App\Models\v3\RequestFund::where('uuid', $request->get('uuid'))->first();

        $content = file_get_contents(url("api/code/send?uuid=" . trim($request->get('uuid')  ).""));
        $symbols = json_decode($content->body, true);


        $data = json_decode($content);


        Log::channel('slack')->info(json_encode($data->msg));


        return response()->success("تم إرسال الكود لهاتف العميل");
    }

    public function show_fund_requests(Request $request)
    {


//dd($date=date('Y-m-d'));
        $user = auth()->guard()->user();

        $myRequestFundOffer = 0;
        if ($user != null) {
            $myRequestFundOffer = FundRequestOffer::whereHas('provider')->whereHas('estate')->whereHas('fund_request')->where('provider_id',
                $user->id);

        } else {
            $myRequestFundOffer = 0;

        }

        $allRequestFund = RequestFund::query();

        $date = date('Y-m-d');
        $RequestFund = RequestFund::whereDate('created_at', $date);

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $finiceing = RequestFund::query();


        if ($request->get('search') && $request->get('search') != null) {


            // dd(filter_var($request->get('search'), FILTER_VALIDATE_INT));

            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && RequestFund::find($request->get('search'))) {
                $finiceing = $finiceing->where('id', $request->get('search'));

            } else {
                $finiceing = $finiceing->where('uuid', 'like', '%' . $request->get('search') . '%');

            }


        }


        if ($request->get('today') && $request->get('today') != null) {
            $date = date('Y-m-d');
            $finiceing = $finiceing->whereDate('created_at', $date);


            /*  if ($user != null) {
                  $myRequestFundOffer = $myRequestFundOffer->whereDate('created_at', $date);

              }

              $allRequestFund = $allRequestFund->whereDate('created_at', $date);

  */
        }


        if ($request->get('myOwn') && $request->get('myOwn') != null) {

            $offers = FundRequestOffer::where('provider_id', $user->id)// ->whereHas('provider')
            ;


            if ($request->get('offer_status') && $request->get('offer_status') != 'new' && $request->get('offer_status') != null) {

            } elseif ($request->get('offer_status') && $request->get('offer_status') == 'new') {
                $offers = $offers
                    ->where('status', $request->get('offer_status'))
                    ->orwhere('status', null);
            }

            $offers = $offers->whereHas('estate')
                ->whereHas('fund_request')
                ->pluck('uuid');


            $finiceing = $finiceing->with('offers')->whereHas('offers', function ($query) use ($user) {
                $query->where('provider_id', $user->id)
                    ->whereHas('provider')
                    ->whereHas('estate')
                    ->whereHas('fund_request');
            })->whereIn('uuid', $offers->toArray())->with('offers');


            if ($request->get('offer_status')) {
                $finiceing = $finiceing->where('status', $request->get('offer_status'));
            }
        }

        if ($request->get('state_id') && $request->get('state_id') != null
        ) {


            $finiceing = $finiceing->whereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($request) {
                    $query
                        ->whereHas('city', function ($query) use ($request) {
                            $query->where('state_id', $request->get('state_id'));
                        });

                });

            }

            $allRequestFund = $allRequestFund->whereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });


            $RequestFund = $RequestFund->whereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {

            $user->saved_filter_fund_type = $request->get('estate_type_id');
            $user->save();

            $estate_type = explode(',', $request->get('estate_type_id'));


            $finiceing = $finiceing->whereIn('estate_type_id', $estate_type);


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($estate_type) {
                    $query
                        ->whereIn('estate_type_id', $estate_type);

                });

            }

            $allRequestFund = $allRequestFund->whereIn('estate_type_id', $estate_type);


            $RequestFund = $RequestFund->whereIn('estate_type_id', $estate_type);


        }
        if ($request->get('area_estate_id') && $request->get('area_estate_id') != null) {
            $finiceing = $finiceing->where('area_estate_id', $request->get('area_estate_id'));


            $areaEstate = $request->get('area_estate_id');


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($areaEstate) {
                    $query
                        ->where('area_estate_id', $areaEstate);

                });

            }

            $allRequestFund = $allRequestFund->where('area_estate_id', $request->get('area_estate_id'));


            $RequestFund = $RequestFund->where('area_estate_id', $request->get('area_estate_id'));
        }
        if ($request->get('dir_estate_id') && $request->get('dir_estate_id') != null) {
            $finiceing = $finiceing->where('dir_estate_id', $request->get('dir_estate_id'));

            $dir = $request->get('dir_estate_id');


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($dir) {
                    $query
                        ->where('dir_estate_id', $dir);

                });

            }

            $allRequestFund = $allRequestFund->where('dir_estate_id', $request->get('dir_estate_id'));


            $RequestFund = $RequestFund->where('dir_estate_id', $request->get('dir_estate_id'));
        }
        if ($request->get('estate_price_id') && $request->get('estate_price_id') != null) {
            $finiceing = $finiceing->where('estate_price_id', $request->get('estate_price_id'));
            $price = $request->get('estate_price_id');


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($price) {
                    $query
                        ->where('estate_type_id', $price);

                });

            }

            $allRequestFund = $allRequestFund->where('estate_price_id', $request->get('estate_price_id'));


            $RequestFund = $RequestFund->where('estate_price_id', $request->get('estate_price_id'));
        }
        if ($request->get('city_id') && $request->get('city_id') != null) {

            $user->saved_filter_fund_city = $request->get('city_id');

            $user->save();
            $city = explode(',', $request->get('city_id'));
            $finiceing = $finiceing->whereIn('city_id', $city);


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($city) {
                    $query
                        ->whereIn('city_id', $city);

                });

            }

            $allRequestFund = $allRequestFund->whereIn('city_id', $city);


            $RequestFund = $RequestFund->whereIn('city_id', $city);
        }

        if ($request->get('neighborhood_id') && $request->get('neighborhood_id') != null) {
            //          $finiceing = $finiceing->where('neighborhood_id', $request->get('neighborhood_id'));
            $neb = explode(',', $request->get('neighborhood_id'));
            $finiceing = $finiceing->whereHas('neighborhood', function ($query) use ($neb) {
                $query->whereIn('neighborhood_id', $neb);
            });


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($neb) {
                    $query
                        ->whereHas('neighborhood', function ($query) use ($neb) {
                            $query->whereIn('neighborhood_id', $neb);
                        });

                });

            }

            $allRequestFund = $allRequestFund->whereHas('neighborhood', function ($query) use ($neb) {
                $query->whereIn('neighborhood_id', $neb);
            });


            $RequestFund = $RequestFund->whereHas('neighborhood', function ($query) use ($neb) {
                $query->whereIn('neighborhood_id', $neb);
            });
        }
        if ($request->get('street_view_id') && $request->get('street_view_id') != null) {
            $finiceing = $finiceing->where('street_view_id', $request->get('street_view_id'));


            $streetView = $request->get('street_view_id');


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($streetView) {
                    $query
                        ->where('street_view_id', $streetView);

                });

            }

            $allRequestFund = $allRequestFund->where('street_view_id', $request->get('street_view_id'));


            $RequestFund = $RequestFund->where('street_view_id', $request->get('street_view_id'));
        }


        if ($request->get('price_id') && $request->get('price_id') != null) {
            $finiceing = $finiceing->where('estate_price_id', $request->get('price_id'));


            $price_id = $request->get('price_id');


            if ($user != null) {
                $myRequestFundOffer = $myRequestFundOffer->whereHas('fund_request', function ($query) use ($price_id) {
                    $query
                        ->where('street_view_id', $price_id);

                });

            }

            $allRequestFund = $allRequestFund->where('estate_price_id', $request->get('price_id'));


            $RequestFund = $RequestFund->where('estate_price_id', $request->get('price_id'));
        }


        $finiceing = $finiceing->orderBy('id', 'desc')->paginate(50);
        foreach ($finiceing->items() as $finiceingItem) {
            $offer = FundRequestOffer::where('uuid', $finiceingItem->uuid)
                ->where('provider_id', $user->id)->first();

            if ($offer) {
                $finiceingItem->has_my_offer = 1;
            } else {
                $finiceingItem->has_my_offer = 0;
            }
        }

        if ($user != null) {
            $myRequestFundOffer = $myRequestFundOffer->count();

        } else {
            $myRequestFundOffer = 0;

        }

        $allRequestFund = $allRequestFund->count();


        $RequestFund = $RequestFund->count();

        return Response::json([
            "status" => true,
            "allRequestFund" => $allRequestFund,
            "RequestFund" => $RequestFund,
            "myRequestFundOffer" => $myRequestFundOffer,
            "message" => "طلبات الصندوق",
            "data" => $finiceing,
            'code' => 200
        ]);
        return response()->success(__("views.Fund Requests"), $finiceing);

    }


    public function fund_request_offer(Request $request)
    {


        $user = Auth::user();


        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

//1,2,3,4,5,6,7,8,9,10


        // offer_numbers
        $FundRequestOffer = FundRequestOffer::
        whereHas('provider')->whereHas('estate')->whereHas('fund_request')
            ->where('provider_id', $user->id);


        if ($request->get('uuid') && $request->get('uuid') != null) {
            $FundRequestOffer->where('uuid', $request->get('uuid'));

        }

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {
            $FundRequestOffer->whereHas('estate', function ($query) use ($request) {
                $query->where('estate_type_id', $request->get('estate_type_id'));
            });
        }


        if ($request->get('city_id') && $request->get('city_id') != null) {
            $FundRequestOffer->whereHas('estate.city', function ($query) use ($request) {
                $query->where('city_id', $request->get('city_id'));
            });
        }
        if ($request->get('neighborhood_id') && $request->get('neighborhood_id') != null) {
            $FundRequestOffer->whereHas('estate.neighborhood', function ($query) use ($request) {
                $query->where('neighborhood_id', $request->get('neighborhood_id'));
            });
        }


        $FundRequestOffer = $FundRequestOffer->get();
        if (!$FundRequestOffer) {
            return response()->error(__("views.not found"), []);
        }


        $collection = RequestFundOfferResource::collection($FundRequestOffer);


        //     $stringOffer = implode(',', $finiceingOfferArray->toArray());
        //       $finice->offer_numbers = $finice->offer_numbers != null ? $finice->offer_numbers . ',' . $stringOffer : $stringOffer;
        //    $finice->save();

        return response()->success(__("views.Request Fund Offer"), $collection);


    }


    public function send_offer(Request $request)
    {
        $EstateRequest = EstateRequest::where('id', $request->get('request_id'))->first();


        if ($EstateRequest) {
            $client = User::where('id', $EstateRequest->user_id)
                ->orwhere('related_company', $EstateRequest->user_id)
                ->get();


            $user = Auth::user();
            if ($user == null) {
                return response()->error(__("views.not authorized"));
            }

            //     $user->count_offer = $user->count_offer+1;
            // $user->count_request =  $user->count_request+1;
            //  $user->save();


            $estate = explode(',', $request->get('estate_id'));


            for ($i = 0; $i < count($estate); $i++) {


                $checkOffer = RequestOffer::where('provider_id', $user->id)
                    ->where('provider_id', $user->related_company)
                    ->where('estate_id', $estate[$i])
                    ->where('request_id', $request->get('request_id'))
                    ->where('user_id', $EstateRequest->user_id)
                    ->first();


                if (!$checkOffer) {
                    //  return response()->success("You Are Already   Request Offer", $checkOffer);


                    if ($EstateRequest) {
                        $request->merge([
                            'provider_id' => $user->id,
                            'user_id' => $EstateRequest->user_id,
                            'estate_id' => $estate[$i],
                        ]);

                        $FundRequestOffer = RequestOffer::create($request->only([
                            'request_id',
                            'user_id',
                            'instument_number',
                            'guarantees',
                            'beneficiary_name',
                            'beneficiary_mobile',
                            'status',
                            'provider_id',
                            'estate_id',


                        ]));


                    }
                }


            }


            if ($client) {


                foreach ($client as $clientItem) {
                    $push_data = [
                        'title' => 'لديك عرض جديد على الطلب  #' . $EstateRequest->id,
                        'body' => 'لديك عرض جديد على الطلب  #' . $EstateRequest->id,
                        'id' => $EstateRequest->id,
                        'user_id' => $clientItem->id,
                        'type' => 'offer',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $client->id,
                        'title' => 'لديك عرض جديد على الطلب  #' . $EstateRequest->id,
                        'type' => 'offer',
                        'type_id' =>  $EstateRequest->id,
                    ]);
                    $fcm_token = FcmToken::where('user_id', $client->id)->get();
                    foreach ($fcm_token as $token) {
                        send_push($token->token, $push_data, $token->type);
                    }
                }


            }


            return response()->success(__("views.RequestOffer"), []);
        } else {
            return response()->error(__("views.No Data"));
        }

    }


    public function customer_my_send_offer(Request $request)
    {


        $user = Auth::user();

        $checkOffer = '';
        if ($request->get('id')) {
            $checkOffer = RequestOffer::
            whereHas('provider')->whereHas('estate')->whereHas('request')
                ->with('estate')
                ->where('user_id', $user->id)
                ->where('status', '1')
                ->where('request_id', $request->get('id'))
                ->get();
        } else {
            $checkOffer = RequestOffer::whereHas('provider')->whereHas('estate')->whereHas('request')
                ->with('estate')
                ->where('status', '1')
                ->where('user_id', $user->id);


            if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {
                $checkOffer = $checkOffer->whereHas('estate', function ($query) use ($request) {
                    $query->where('estate_type_id', $request->get('estate_type_id'));
                });
            }

            if ($request->get('city') && $request->get('city') != null) {
                $checkOffer = $checkOffer->whereHas('estate', function ($query) use ($request) {
                    $query->where('city_id', $request->get('city_id'));
                });
            }
            //  ->where('status', '1')
            $checkOffer = $checkOffer->get();


        }


        if ($checkOffer) {

            $collection = RequestOfferResource::collection($checkOffer);
            return response()->success(__("views.RequestOffer"), $collection);
        } else {
            return response()->error(__("views.not found"), []);
        }


    }

    public function provider_my_send_offer(Request $request)
    {


        $user = Auth::user();


        $checkOffer = '';
        if ($request->get('id')) {
            $checkOffer = RequestOffer::
            whereHas('provider')->whereHas('estate')->whereHas('request')
                ->with('estate')
                ->where('provider_id', $user->id)
                ->where('request_id', $request->get('id'));


            if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {
                $checkOffer = $checkOffer->whereHas('estate', function ($query) use ($request) {
                    $query->where('estate_type_id', $request->get('estate_type_id'));
                });
            }


            //  ->where('status', '1')
            $checkOffer = $checkOffer->get();
        } else {
            $checkOffer = RequestOffer::whereHas('provider')->whereHas('estate')->whereHas('request')
                ->with('estate')
                ->where('provider_id', $user->id);


            if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {
                $checkOffer = $checkOffer->whereHas('estate', function ($query) use ($request) {
                    $query->where('estate_type_id', $request->get('estate_type_id'));
                });
            }

            if ($request->get('city') && $request->get('city') != null) {
                $checkOffer = $checkOffer->whereHas('estate', function ($query) use ($request) {
                    $query->where('city_id', $request->get('city_id'));
                });
            }
            //  ->where('status', '1')
            $checkOffer = $checkOffer->get();
        }


        if ($checkOffer) {

            $collection = RequestOfferResource::collection($checkOffer);
            return response()->success(__("views.RequestOffer"), $collection);
        } else {
            return response()->error(__("views.not found"), []);
        }


    }


    public function send_offer_app_status(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $RequestOffer = RequestOffer::find($request->get('offer_id'));


        if ($RequestOffer) {
            $client = User::where('id', $RequestOffer->provider_id)->first();
            if ($RequestOffer) {

                $EstateRequest = EstateRequest::findOrFail($RequestOffer->request_id);
                if (!$EstateRequest) {
                    return response()->error(__("views.not found"), []);
                }
                //  if ($RequestOffer->status != 'accepted_customer' && $RequestOffer->status != 'rejected_customer') {
                $RequestOffer->status = $request->get('status');
                $RequestOffer->save();
                /*  $EstateRequest->status = $request->get('status');
                  $EstateRequest->save();*/


                if ($client) {
                    $push_data = [
                        'title' => 'You Have New Update Status For Offer #' . $RequestOffer->id,
                        'body' => 'You Have New Update Status For Offer #' . $RequestOffer->id,
                        'id' => $RequestOffer->id,
                        'user_id' => $client->id,
                        'type' => 'offer',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $client->id,
                        'title' => 'لديك عرض جديد على الطلب #' . $EstateRequest->id,
                        'type' => 'offer',
                        'type_id' =>  $EstateRequest->id,
                    ]);
                    $fcm_token = FcmToken::where('user_id', $client->id)->get();
                    foreach ($fcm_token as $token) {
                        send_push($token->token, $push_data, $token->type);
                    }
                }

                return response()->success("Offer " . $request->get('status') . " ", $RequestOffer);
                //   }


            } else {
                return response()->error(__("views.not found"), []);
            }
        } else {
            return response()->error(__("views.not found"));
        }


    }

    public function approve_offer(Request $request)
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $finiceingOfferArray = RequestOffer::where('provider_id', $user->id)
            ->where('status', 'accepted_customer');


        if ($request->get('estate_type') && $request->get('estate_type_id') != null) {
            $finiceingOfferArray = $finiceingOfferArray->whereHas('estate', function ($query) use ($request) {
                $query->where('estate_type_id', $request->get('estate_type'));
            });
        }


        if ($request->get('city_id') && $request->get('city_id') != null) {
            $finiceingOfferArray = $finiceingOfferArray->whereHas('estate.city', function ($query) use ($request) {
                $query->where('city_id', $request->get('city_id'));
            });
        }
        if ($request->get('neighborhood_id') && $request->get('neighborhood_id') != null) {
            $finiceingOfferArray = $finiceingOfferArray->whereHas('estate.neighborhood',
                function ($query) use ($request) {
                    $query->where('neighborhood_id', $request->get('neighborhood_id'));
                });
        }


        $finiceingOfferArray = $finiceingOfferArray->get();


        if ($finiceingOfferArray) {
            $collection = RequestOfferResource::collection($finiceingOfferArray);
            return response()->success(__("views.Approval Offer"), $collection);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }

    public function myDeferredInstallment(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $attitude = $request->get('lat');
        $longitude = $request->get('lan');
        $distanceL = 500;
        //  if ($attitude && $longitude) {


        $Mechanic = DeferredInstallment::where('user_id', $user->id);


        if ($request->get('estate_type') && $request->get('estate_type') != null) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }


        /*   if ($request->get('rent_price_from') && $request->get('rent_price_to') && $request->get('rent_price_to') != 0) {
               $Mechanic->where('rent_price', '>=', $request->get('rent_price_from'));
               $Mechanic->where('rent_price', '<=', $request->get('rent_price_to'));
           }*/


        if ($request->get('search') && $request->get('search') != null) {


            $Mechanic = $Mechanic
                ->where('tenant_job_type', 'like', '%' . $request->get('search') . '%')
                ->orwhere('tenant_name', 'like', '%' . $request->get('search') . '%')
                ->orwhere('tenant_mobile', 'like', '%' . $request->get('search') . '%')
                ->orwhere('owner_name', 'like', '%' . $request->get('search') . '%')
                ->orwhere('owner_mobile', 'like', '%' . $request->get('search') . '%');

        }


        $Mechanic = $Mechanic->paginate();


        return response()->success(__("views.DeferredInstallment Requests"), $Mechanic);

    }

    public function myFinance(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        //  if ($attitude && $longitude) {


        $Mechanic = Finance::where('user_id', $user->id);


        if ($request->get('estate_type') && $request->get('estate_type') != null) {


            $estate = explode(',', $request->get('estate_type'));
            $Mechanic = $Mechanic->whereIn('estate_type_id', $estate);
        }


        /*  if ($request->get('estate_price_from') && $request->get('estate_price_to') && $request->get('estate_price_to') != 0) {
              $Mechanic->where('estate_price', '>=', $request->get('estate_price_from'));
              $Mechanic->where('estate_price', '<=', $request->get('estate_price_to'));
          }*/


        if ($request->get('search') && $request->get('search') != null) {


            $Mechanic = $Mechanic
                ->where('identity_number', 'like', '%' . $request->get('search') . '%')
                ->orwhere('mobile', 'like', '%' . $request->get('search') . '%')
                ->orwhere('total_salary', 'like', '%' . $request->get('search') . '%');
            /*->orwhere('owner_name', 'like', '%' . $request->get('search') . '%')
            ->orwhere('owner_mobile', 'like', '%' . $request->get('search') . '%');*/

        }


        $Mechanic = $Mechanic->paginate();


        return response()->success(__("views.Fiance Requests"), $Mechanic);

    }


    public function myRate(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        //  if ($attitude && $longitude) {


        $Mechanic = RateRequest::where('user_id', $user->id)
            ->orwhere('user_id', $user->related_company);


        if ($request->get('search') && $request->get('search') != null) {


            $Mechanic = $Mechanic
                ->where('name', 'like', '%' . $request->get('search') . '%')
                ->orwhere('mobile', 'like', '%' . $request->get('search') . '%')
                ->orwhere('email', 'like', '%' . $request->get('search') . '%');
            /*->orwhere('owner_name', 'like', '%' . $request->get('search') . '%')
            ->orwhere('owner_mobile', 'like', '%' . $request->get('search') . '%');*/

        }


        $Mechanic = $Mechanic->paginate();


        return response()->success(__("views.Rate Requests"), $Mechanic);

    }


    public function send_reject_offer_fund(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'uuid' => 'required',

            //   'status' => 'required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $Finance = RequestFund::where('uuid', $request->get('uuid'))
            //->where('code',$request->get('uuid'))
            ->first();
        if ($Finance) {
            $offer = FundRequestOffer::where('uuid', $Finance->uuid)
                // ->where('code',$request->get('code'))
                ->where('status', 'active')->first();

            if (!$offer) {
                return response()->error(__("views.code is miss"), []);
            }

            //    $offer->status = 'rejected_customer ';
            $offer->delete();
            $Finance->status = null;
            $Finance->save();

            return response()->success("Offer rejected_customer ", $Finance);
        } else {
            return response()->error(__("views.not found"), []);
        }


    }


    public function cancel_fund_offer($id)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $fund_offer = FundRequestOffer::findOrFail($id);


        /*   if($fund_offer->provider_id != $user->id)
           {
               return response()->error("the offer is not for this user", []);
           }*/

        if ($fund_offer) {
            $fund_offer->uuid = '';
            $fund_offer->save();

            if ($fund_offer->status == 'sending_code') {
                $requests = RequestFund::where('uuid', $fund_offer->uuid)->first();

                if ($requests) {
                    $requests->status = 'new';
                    $requests->save();
                }

            }
        }

        return response()->success(__("views.Cancel Successfully"), []);
    }


    public function cancel_offer($id)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $fund_offer = RequestOffer::find($id);


        if (!$fund_offer) {
            return response()->error(__("views.not found"));
        }

        /*  if($fund_offer->provider_id != $user->id)
          {
              return response()->error("the offer is not for this user", []);
          }*/

        if ($fund_offer) {
            $fund_offer->delete();
        }

        return response()->success(__("views.Cancel Successfully"), []);
    }


    public function rate_offer(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'offer_id' => 'required',
            'rate' => 'required|numeric|min:1|not_in:0',

            //   'status' => 'required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $chekRate = RateOffer::where('user_id', $user->id)
            ->where('offer_id', $request->get('offer_id'))
            ->first();
        if (!$chekRate) {
            $offer = RequestOffer::findOrFail($request->get('offer_id'));
            $provider = User::findOrFail($offer->provider_id);


            $request->merge([
                'request_id' => $offer->request_id,
                'user_id' => $user->id,
                'provider_id' => $offer->provider_id,

            ]);
            $rate = RateOffer::create($request->only([
                'request_id',
                'user_id',
                'provider_id',
                'offer_id',
                'rate',
                'note',


            ]));


            $sum_rate_provider = RateOffer::where('provider_id', $provider->id)->sum('rate');


            $count_rate_provider = RateOffer::where('provider_id', $provider->id)->count();

            //   dd($count_rate_provider==0);
            if ($count_rate_provider != 0) {
                $provider->rate = $sum_rate_provider / $count_rate_provider;
                $provider->save();
            }


            if ($provider) {
                $push_data = [
                    'title' => 'You Have New Rate  Offer #' . $offer->id,
                    'body' => 'You Have Rate Offer #' . $offer->id,
                    'id' => $offer->id,
                    'user_id' => $provider->id,
                    'type' => 'rate',
                ];

                $note = NotificationUser::create([
                    'user_id' => $provider->id,
                    'title' => 'لديك تقييم على العرض #' . $offer->id,
                    'type' => 'rate',
                    'type_id' =>  $offer->id,
                ]);
                $fcm_token = FcmToken::where('user_id', $provider->id)->get();
                foreach ($fcm_token as $token) {
                    send_push($token->token, $push_data, $token->type);
                }
            }
        }


        return response()->success(__("views.Rate Successfully"), []);
    }


    public function rate_estate(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'estate_id' => 'required',
            'rate' => 'required|numeric|min:1|not_in:0',

            //   'status' => 'required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $chekRate = RateEstate::where('user_id', $user->id)
            ->where('estate_id', $request->get('estate_id'))
            ->first();
        if (!$chekRate) {
            $offer = Estate::findOrFail($request->get('estate_id'));
            $provider = User::findOrFail($offer->user_id);


            $request->merge([

                'user_id' => $user->id,
                'provider_id' => $offer->user_id,

            ]);
            $rate = RateEstate::create($request->only([
                'user_id',
                'provider_id',
                'estate_id',
                'rate',
                'note',


            ]));


            $sum_rate_provider = RateEstate::where('provider_id', $provider->id)->sum('rate');


            $count_rate_provider = RateEstate::where('provider_id', $provider->id)->count();

            //   dd($count_rate_provider==0);
            if ($count_rate_provider != 0) {
                $offer->rate = $sum_rate_provider / $count_rate_provider;
                $offer->save();
            }

            if ($provider) {
                $push_data = [
                    'title' => 'You Have New Rate  Estate #' . $offer->id,
                    'body' => 'You Have Rate Estate #' . $offer->id,
                    'id' => $offer->id,
                    'user_id' => $provider->id,
                    'type' => 'rate_estate',
                ];

                $note = NotificationUser::create([
                    'user_id' => $provider->id,
                    'title' => 'لديك تقييم على العقار #' . $offer->id,
                    'type' => 'rate_estate',
                    'type_id' =>  $offer->id,
                ]);
                $fcm_token = FcmToken::where('user_id', $provider->id)->get();
                foreach ($fcm_token as $token) {
                    send_push($token->token, $push_data, $token->type);
                }
            }
        }


        return response()->success(__("views.Rate Successfully"), []);
    }
}
