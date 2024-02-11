<?php

namespace App\Http\Controllers\DashBoard\Fund;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\MsgDetResource;
use App\Http\Resources\MsgResource;
use App\Jobs\OtpJob;
use App\Models\dashboard\Admin;
use App\Models\v2\AreaEstate;
use App\Models\v2\City;
use App\Models\v2\Client;
use App\Models\v2\Estate;
use App\Models\v2\EstatePrice;
use App\Models\v2\EstateRequest;
use App\Models\v2\EstateType;
use App\Models\v2\Favorite;
use App\Models\v2\FundRequestOffer;

use App\Models\v2\Neighborhood;
use App\Models\v2\RequestFund;

use App\User;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Auth;




class SettingController extends Controller
{


    public function index(Request $request)
    {


        $requests = RequestFund::query();


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $requests = $requests->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $requests = $requests->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $requests = $requests->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $requests = $requests->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $requests = $requests->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $requests = $requests->count();



      /*  $requests_content = RequestFund::query();


      /*  if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $requests_content = $requests_content->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $requests_content = $requests_content->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $requests_content = $requests_content->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $requests_content = $requests_content->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $requests_content = $requests_content->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

     //   }


      /*  $requests_content = $requests_content->get();*/


        $requests_has_offer = RequestFund::whereHas('offers');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $requests_has_offer = $requests_has_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $requests_has_offer = $requests_has_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $requests_has_offer = $requests_has_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $requests_has_offer = $requests_has_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $requests_has_offer = $requests_has_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $requests_has_offer = $requests_has_offer->count();


        $accept_requests_from_fund = RequestFund::where('status', 'sending_code');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $accept_requests_from_fund = $accept_requests_from_fund->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $accept_requests_from_fund = $accept_requests_from_fund->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $accept_requests_from_fund = $accept_requests_from_fund->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $accept_requests_from_fund = $accept_requests_from_fund->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $accept_requests_from_fund = $accept_requests_from_fund->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $accept_requests_from_fund = $accept_requests_from_fund->count();


        $accept_requests_from_customer = RequestFund::whereHas('offers')->where('status', 'customer_accepted');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $accept_requests_from_customer = $accept_requests_from_customer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $accept_requests_from_customer = $accept_requests_from_customer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $accept_requests_from_customer = $accept_requests_from_customer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $accept_requests_from_customer = $accept_requests_from_customer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $accept_requests_from_customer = $accept_requests_from_customer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $accept_requests_from_customer = $accept_requests_from_customer->count();


        $all_offer = FundRequestOffer::whereHas('estate')->whereHas('fund_request');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $all_offer = $all_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $all_offer = $all_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $all_offer = $all_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $all_offer = $all_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $all_offer = $all_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $all_offer = $all_offer->count();



        $active_offer = RequestFund::orderBy('count_offers', 'desc');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $active_offer = $active_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $active_offer = $active_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $active_offer = $active_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $active_offer = $active_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $active_offer = $active_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }

        $active_offer = $active_offer->limit('10')
            ->get();


     /*   $requests_dont_offer = RequestFund::doesntHave('offers');

       /* if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $requests_dont_offer = $requests_dont_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $requests_dont_offer = $requests_dont_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $requests_dont_offer = $requests_dont_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $requests_dont_offer = $requests_dont_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $requests_dont_offer = $requests_dont_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

       // }
//

      /*  $requests_dont_offer = $requests_dont_offer->limit('5')
            ->get();*/
        $custmer_accept_offer = FundRequestOffer::whereHas('estate')->whereHas('fund_request')
        ->where('status', 'customer_accepted');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $custmer_accept_offer = $custmer_accept_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $custmer_accept_offer = $custmer_accept_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $custmer_accept_offer = $custmer_accept_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $custmer_accept_offer = $custmer_accept_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $custmer_accept_offer = $custmer_accept_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $custmer_accept_offer = $custmer_accept_offer->count();

        $providers = User::where('type', 'provider');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $providers = $providers->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $providers = $providers->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $providers = $providers->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $providers = $providers->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $providers = $providers->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $providers = $providers->count();
      /*  $providers_active = User::where('type', 'provider')
            ->where('is_pay', '1');


    /*    if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $providers_active = $providers_active->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $providers_active = $providers_active->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $providers_active = $providers_active->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $providers_active = $providers_active->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $providers_active = $providers_active->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

      //  }


      //  $providers_active = $providers_active->count();
       /* $user_payment = UserPayment::where('status', '0')
            ->pluck('user_id');

        $providers_not_payment = User::where('type', 'provider')
            ->whereIn('id', $user_payment->toArray());
*/

       /* if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $providers_not_payment = $providers_not_payment->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $providers_not_payment = $providers_not_payment->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $providers_not_payment = $providers_not_payment->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $providers_not_payment = $providers_not_payment->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $providers_not_payment = $providers_not_payment->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

      //  }


     //   $providers_not_payment = $providers_not_payment->count();

      /*  $providers_best = User::where('type', 'provider')
            ->orderBy('count_offer', 'desc')
            ->orderBy('count_request', 'desc')
            ->count();*/


        $requests_sending_code_offer = RequestFund::whereHas('offers', function ($q) use ($request) {


            $q->where('status', 'sending_code');
        });


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $requests_sending_code_offer = $requests_sending_code_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $requests_sending_code_offer = $requests_sending_code_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $requests_sending_code_offer = $requests_sending_code_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $requests_sending_code_offer = $requests_sending_code_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $requests_sending_code_offer = $requests_sending_code_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }


        $requests_sending_code_offer = $requests_sending_code_offer->count();

        $requests_dont_offer_count = RequestFund::doesntHave('offers');


        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $requests_dont_offer_count = $requests_dont_offer_count->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $requests_dont_offer_count = $requests_dont_offer_count->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $requests_dont_offer_count = $requests_dont_offer_count->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $requests_dont_offer_count = $requests_dont_offer_count->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $requests_dont_offer_count = $requests_dont_offer_count->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }




        $requests_dont_offer_count = $requests_dont_offer_count->count();


        $settings = [

            'requests'                      => $requests,
            'requests_has_offer'            => $requests_has_offer,
            'accept_requests_from_fund'     => $accept_requests_from_fund,
            'accept_requests_from_customer' => $accept_requests_from_customer,
            'all_offer'                     => $all_offer,
            'active_offer'                  => $active_offer,
            'custmer_accept_offer'          => $custmer_accept_offer,
      //      'requests_dont_offer'           => $requests_dont_offer,
       //     'requests_content'              => $requests_content,
          //  'offers'                        => $offers,
            'providers'                     => $providers,
        //    'providers_active'              => $providers_active,
         //   'providers_not_payment'         => $providers_not_payment,
            'requests_sending_code_offer'   => $requests_sending_code_offer,
            'requests_dont_offer_count'     => $requests_dont_offer_count,
        ];

      //  \Cache::forget('dashborad');
     /*   $settings = \Cache::remember('dashborad', 22*60, function() use ($settings) {
            return $settings;
        });*/

        return response()->success("DashBoard", $settings);


    }

    public function estate_type()
    {
        $estate_type = EstateType::query()->get();
        return response()->success("estate type", $estate_type);
    }

    public function estate_area()
    {

        $estate_area = AreaEstate::query()->get();
        return response()->success("estate area", $estate_area);

    }
    public function estate_price()
    {

        $estate_price = EstatePrice::query()->get();
        return response()->success("estate price", $estate_price);

    }
    public function estate_dir()
    {

        $estate_dir = $array = ['شمال', 'جنوب', 'شرق', 'غرب'];
        return response()->success("estate dir", $estate_dir);

    }



    public function state()
    {

        $regine = [
            '1'  => 'الرياض',
            '2'  => 'مكه المكرمة',
            '3'  => 'جازان',
            '4'  => 'الشرقية',
            '5'  => 'عسير',
            '6'  => 'القصيم',
            '7'  => 'حائل',
            '8'  => 'المدينة المنورة',
            '9'  => 'الباحة',
            '10' => 'الحدود الشمالية',
            '11' => 'تبوك',
            '12' => 'نجران',
            '13' => 'الجوف',
        ];
        return response()->success("regine", $regine);

    }
    public function cities()
    {

        $cities = City::query()->get();
        return response()->success("cities", $cities);

    }

    public function neighborhood($id)
    {

        $cities = Neighborhood::where('city_id',$id)->get();
        return response()->success("cities", $cities);

    }
}
