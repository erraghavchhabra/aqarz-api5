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
use App\Models\v2\Favorite;
use App\Models\v2\FundRequestHasOffer;
use App\Models\v2\FundRequestNeighborhood;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\FundRequestSmsStatus;
use App\Models\v2\Msg;
use App\Models\v2\MsgDet;
use App\Models\v2\NotificationUser;
use App\Models\v2\RequestFund;
use App\Models\v2\RequestOffer;
use App\Models\v2\UserPayment;
use App\Models\v4\FcmToken;
use App\Unifonic\UnifonicMessage;
use App\User;
use App\Helpers\JsonResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Unifonic\Client as UnifonicClient;
use Auth;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

class RequestController extends Controller
{


    public function index(Request $request)
    {


        //  dd($request->get('query')['neighborhood_id']);

        $finiceing = RequestFund::with('neighborhood', 'offers');

        $page = $request->get('page_number', 10);

        if ($request->get('offer_status')) {

            if ($request->get('offer_status') == 'have_offers') {
                $finiceing = $finiceing->whereHas('offers');
            }
            /*  elseif($request->get('offer_status')=='have_offers')
              {
                  $finiceing = $finiceing->doesntHave('offers');
              }*/
            if ($request->get('offer_status') == 'have_active_offers') {
                $finiceing = $finiceing->where('status', 'sending_code');
            }

            if ($request->get('offer_status') == 'dont_have_active_offers') {
                $finiceing = $finiceing->doesntHave('offers');
            }


            if ($request->get('offer_status') == 'complete') {
                $finiceing = $finiceing
                    ->whereHas('offers')
                    ->where('status', 'customer_accepted');
            }

            if ($request->get('offer_status') == 'rejected_customer') {
                $finiceing = $finiceing
                    ->whereHas('offers')
                    ->where('status', 'rejected_customer');
            }

        }

        if ($request->get('time')) {


            if ($request->get('time') == 'today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }
        //  dd($finiceing);


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


        if ($request->neighborhood_id) {
            $array_neb = explode(',', $request->neighborhood_id);
            if (isset($array_neb) && count($array_neb) > 0 && $array_neb[0] != null) {

                //dd( $request->get('query')['neighborhood_id']);
                // dd($request->get('query')['neighborhood_id']);
                /*    $finiceing = $finiceing->whereHas('neighborhood', function ($q) use ($request) {


                        $q->whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                    });
        */


                $nem = FundRequestNeighborhood::whereHas('fund_request')->whereIn('neighborhood_id',
                    $array_neb)
                    ->pluck('request_fund_id');


                $finiceing = $finiceing->whereIn('id', $nem->toArray());


                //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
            }
        }

        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            $finiceing = $finiceing->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {

            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");
            $finiceing = $finiceing->whereDate('created_at', '<', $date);
        }


        if ($request->get('state_id')) {
            $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }

        if ($request->get('uuid')) {
            $finiceing = $finiceing->where('uuid', $request->get('uuid'));
        }
        if ($request->get('search')) {
            $finiceing = $finiceing->where('status', $request->get('search'))
                ->orWhereHas('city', function ($query) use ($request) {
                    $query->where('name_ar', $request->get('search'));
                })
                ->orWhereHas('city', function ($query) use ($request) {
                    $query->where('name_ar', $request->get('search'));
                })
                ->orWhereHas('area_estate', function ($query) use ($request) {
                    $query->where('area_range', $request->get('search'));
                })
                ->orWhereHas('estate_price', function ($query) use ($request) {
                    $query->where('estate_price_range', $request->get('search'));
                });
        }
        $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);

        return response()->success("Fund Request", $finiceing);
    }


    public function deltedindex(Request $request)
    {


        //  dd($request->get('query')['neighborhood_id']);

        $finiceing = RequestFund::onlyTrashed()->with('neighborhood', 'offers');

        $page = $request->get('page_number', 10);

        if ($request->get('offer_status')) {

            if ($request->get('offer_status') == 'have_offers') {
                $finiceing = $finiceing->whereHas('offers');
            }
            /*  elseif($request->get('offer_status')=='have_offers')
              {
                  $finiceing = $finiceing->doesntHave('offers');
              }*/
            if ($request->get('offer_status') == 'have_active_offers') {
                $finiceing = $finiceing->where('status', 'sending_code');
            }

            if ($request->get('offer_status') == 'dont_have_active_offers') {
                $finiceing = $finiceing->doesntHave('offers');
            }


            if ($request->get('offer_status') == 'complete') {
                $finiceing = $finiceing
                    ->whereHas('offers')
                    ->where('status', 'customer_accepted');
            }

            if ($request->get('offer_status') == 'rejected_customer') {
                $finiceing = $finiceing
                    ->whereHas('offers')
                    ->where('status', 'rejected_customer');
            }

        }

        if ($request->get('time')) {


            if ($request->get('time') == 'today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }
        //  dd($finiceing);


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


        if ($request->neighborhood_id) {
            $array_neb = explode(',', $request->neighborhood_id);
            if (isset($array_neb) && count($array_neb) > 0 && $array_neb[0] != null) {

                //dd( $request->get('query')['neighborhood_id']);
                // dd($request->get('query')['neighborhood_id']);
                /*    $finiceing = $finiceing->whereHas('neighborhood', function ($q) use ($request) {


                        $q->whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                    });
        */


                $nem = FundRequestNeighborhood::whereHas('fund_request')->whereIn('neighborhood_id',
                    $array_neb)
                    ->pluck('request_fund_id');


                $finiceing = $finiceing->whereIn('id', $nem->toArray());


                //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
            }
        }

        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            $finiceing = $finiceing->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {

            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");
            $finiceing = $finiceing->whereDate('created_at', '<', $date);
        }


        if ($request->get('state_id')) {
            $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }

        if ($request->get('uuid')) {
            $finiceing = $finiceing->where('uuid', $request->get('uuid'));
        }
        if ($request->get('search')) {
            $finiceing = $finiceing->where('status', $request->get('search'))
                ->orWhereHas('city', function ($query) use ($request) {
                    $query->where('name_ar', $request->get('search'));
                })
                ->orWhereHas('city', function ($query) use ($request) {
                    $query->where('name_ar', $request->get('search'));
                })
                ->orWhereHas('area_estate', function ($query) use ($request) {
                    $query->where('area_range', $request->get('search'));
                })
                ->orWhereHas('estate_price', function ($query) use ($request) {
                    $query->where('estate_price_range', $request->get('search'));
                });
        }
        $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);

        return response()->success("Fund Request", $finiceing);
    }


    public function offers(Request $request)
    {

        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);

        $finiceing = FundRequestOffer::whereHas('provider')->whereHas('estate')->whereHas('fund_request')
            ->with('fund_request', 'estate', 'estate.comforts', 'estate.EstateFile',
                'provider');

        if ($request->get('status') && $request->get('status') != 'all') {

            $finiceing = $finiceing->where('status', $request->get('status'));
        }

        if ($request->get('time')) {


            if ($request->get('time') == 'today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }
        if ($request->get('uuid')) {
            $finiceing = $finiceing->where('uuid', $request->get('uuid'));
        }
        //  dd($finiceing);


        if ($request->get('estate_type_id')) {


            $finiceing = $finiceing->whereHas('estate', function ($q) use ($request) {


                $q->where('estate_type_id', $request->get('estate_type_id'));
            });

        }
        if ($request->get('area_estate_id')) {


            $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));
            $finiceing = $finiceing->whereHas('estate', function ($q) use ($request, $area_range) {


                $q->where('total_area', '>', $area_range->area_from)
                    ->where('total_area', '<', $area_range->area_to);
            });
        }
        if ($request->get('dir_estate_id')) {


            $array = ['north', 'south', 'east', 'west'];
            $finiceing = $finiceing->whereHas('estate', function ($q) use ($request, $array) {


                $q->where('interface', $array[$request->get('dir_estate_id')]);
                //  ->where('total_area','<', $area_range->area_to);
            });

        }
        if ($request->get('estate_price_id')) {

            $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


            $finiceing = $finiceing->whereHas('estate', function ($q) use ($request, $price_range) {


                $q->where('total_price', '>', $price_range->estate_price_from)
                    ->where('total_price', '<', $price_range->estate_price_to);
            });
        }
        if ($request->get('city_id')) {
            $finiceing = $finiceing->whereHas('estate', function ($q) use ($request) {


                $q->where('city_id', $request->get('city_id'));
                //  ->where('total_area','<', $area_range->area_to);
            });
        }


        if ($request->get('state_id')) {
            $finiceing = $finiceing->WhereHas('estate.city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }


        if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


            $finiceing = $finiceing->whereHas('estate', function ($q) use ($request) {


                $q->where('neighborhood_id', $request->get('neighborhood_id'));
                //  ->where('total_area','<', $area_range->area_to);
            });


            //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
        }

        if ($request->get('search')) {
            $finiceing = $finiceing->where('status', $request->get('search'))
                ->orWhereHas('estate', function ($query) use ($request) {
                    $query->where('owner_name', $request->get('search'))
                        ->where('owner_mobile', $request->get('search'));
                })
                ->orWhereHas('provider', function ($query) use ($request) {
                    $query->where('name', $request->get('search'));
                })
                ->orwhere('beneficiary_name', $request->get('search'))
                ->orwhere('beneficiary_mobile', $request->get('search'));

        }
        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                  $q->whereDate('created_at', '>', $date);
                  //  ->where('total_area','<', $area_range->area_to);
              });*/

            $finiceing = $finiceing->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {
            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                   $q->whereDate('created_at', '<', $date);
                   //  ->where('total_area','<', $area_range->area_to);
               });*/
            $finiceing = $finiceing->whereDate('created_at', '<', $date);

        }



        // $collection = RequestFundOfferResource::collection($finiceing);


        $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);

        return response()->success("Fund Request", $finiceing);
    }


    public function availableOffers(Request $request, $uuid)
    {


        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);

        $request_fund = RequestFund::where('uuid', $uuid)->first();

        $cityArray = ['4353', '2509', '4356'];
        $typeEstateArray = ['2', '4'];


        //  dd($request_fund);
        if ($request_fund) {


            $city = City::where('serial_city', $request_fund->city_id)->first();
            $cityArrayResult = null;
            if (in_array($city->city_id, $cityArray)) {
                $cityArrayResult = City::whereIn('serial_city', $cityArray)->pluck('state_id');
            }

            if ($city) {
                $area_range = AreaEstate::find($request_fund->area_estate_id);
                $price_range = EstatePrice::find($request_fund->estate_price_id);


                $array_dir = dirctions($request_fund->dir_estate_id);


                if ($request->get('search_type') == 'all') {
                    /*  $finiceing = Estate::whereHas('user', function ($q) {


                          /*  $q->where('is_fund_certified', 1)
                                ->where('is_pay', 1);*/
                    /*    })



                            ->whereHas('city', function ($q) use ($city, $cityArrayResult) {


                                if (isset($cityArrayResult)) {
                                    $q->whereIn('state_id', $cityArrayResult->toArray());
                                } else {
                                    $q->where('state_id', $city->state_id);
                                }


                                //  ->whereIn('neighborhood_id', 1);
                            })
                            ->with('comforts', 'EstateFile', 'user');*/

                    $finiceing = Estate::where('in_fund_offer', '=', '1');

                    if (isset($cityArrayResult)) {
                        $finiceing = $finiceing->whereIn('city_id', $cityArrayResult->toArray());
                    } else {
                        $finiceing = $finiceing->where('city_id', $request_fund->city_id);
                    }
                } else {
                    $finiceing = Estate::whereHas('EstateFile')->whereHas('user', function ($q) {


                        //  $q->where('is_fund_certified', 1)
                        //      ->where('is_pay', 1);
                    });

                    //   ->where('city_id', $request_fund->city_id)

                    $EstateArrayResult = null;
                    if (in_array($request_fund->estate_type_id, $typeEstateArray)) {
                        $EstateArrayResult = $typeEstateArray;
                    }


                    //   dd($EstateArrayResult);

                    if (isset($EstateArrayResult)) {
                        $finiceing->whereIn('estate_type_id', $EstateArrayResult);
                    } else {
                        $finiceing->where('estate_type_id', $request_fund->estate_type_id);
                    }

                    $neb = FundRequestNeighborhood::where('request_fund_id', $request_fund->id)->first();

                    if (isset($neb)) {
                        $nebArray = FundRequestNeighborhood::where('request_fund_id', $request_fund->id)->pluck('neighborhood_id');

                        $finiceing->whereIn('neighborhood_id', $nebArray->toArray());
                    }


                    $finiceing = $finiceing
                        ->where('in_fund_offer', '=', '1')
                        //   ->where('total_area', '>', $area_range->area_from)
                        //   ->where('total_area', '<', $area_range->area_to)
                        //   ->where('interface', $array_dir)
                        //    ->where('total_price', '>', $price_range->estate_price_from)
                        //    ->where('total_price', '>=', $price_range->estate_price_to)
                        //   ->whereRaw('(total_price+(total_price*0.15)) <='.$price_range->estate_price_to)
                        //    ->whereRaw('(total_area+(total_area*0.15))  <='.$area_range->area_to)
                        ->where('total_price', '<=', ($price_range->estate_price_to + ($price_range->estate_price_to * 0.15)))
                        ->where('total_area', '<=', ($area_range->area_to + ($area_range->area_to * 0.15)))
                        //  ->whereIn('neighborhood_id', 1);
//price <= property_price + (property_price * 0.15)
                        //    1.15<=1.5
                        //price هو السعر المطلوب في الطلب من الصندوق
                        //property_price سعر العقار الموجود في العرض
                        ->with('comforts', 'EstateFile', 'user');


                    //     dd($request_fund->city_id);
                    if (isset($cityArrayResult)) {

                        $finiceing = $finiceing->whereIn('city_id', $cityArrayResult->toArray());
                    } else {
                        //dd(444);
                        $finiceing = $finiceing->where('city_id', $request_fund->city_id);

                    }
                }


                if ($request->get('status') && $request->get('status') != 'all') {

                    $finiceing = $finiceing->where('status', $request->get('status'));
                }

                if ($request->get('time')) {


                    if ($request->get('time') == 'today') {
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }
                    if ($request->get('time') == 'tow_today') {
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '>=',

                            Carbon::yesterday()->format('Y-m-d')
                        );
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }
                    if ($request->get('time') == 'week') {
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }


                    /* if ($request->get('time') == 'today') {
                         $offers = $offers->where('');
                     }*/

                }
                if ($request->get('uuid')) {
                    $finiceing = $finiceing->where('uuid', $request->get('uuid'));
                }
                //  dd($finiceing);


                if ($request->get('estate_type_id')) {
                    $EstateArrayResult = null;
                    if (in_array($request->get('estate_type_id'), $typeEstateArray)) {
                        $EstateArrayResult = $typeEstateArray;
                    }


                    if (isset($EstateArrayResult)) {
                        $finiceing->whereIn('estate_type_id', $EstateArrayResult);
                    } else {
                        $finiceing->where('estate_type_id', $request->get('estate_type_id'));
                    }


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $finiceing->where('total_area', '>', $area_range->area_from)
                        ->where('total_area', '<', $area_range->area_to);

                }
                if ($request->get('dir_estate_id')) {


                    $array = ['north', 'south', 'east', 'west'];


                    $finiceing->where('interface', $array[$request->get('dir_estate_id')]);
                    //  ->where('total_area','<', $area_range->area_to);


                }
                if ($request->get('estate_price_id')) {

                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $finiceing->where('total_price', '>', $price_range->estate_price_from)
                        ->where('total_price', '<', $price_range->estate_price_to);

                }


                if ($request->get('state_id')) {
                    $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                        $query->where('state_id', $request->get('state_id'));
                    });
                }


                if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


                    $finiceing = $finiceing->whereHas('estate', function ($q) use ($request) {


                        $q->where('neighborhood_id', $request->get('neighborhood_id'));
                        //  ->where('total_area','<', $area_range->area_to);
                    });


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }
                if ($request->get('form_date')) {
                    $date = date_create($request->get('form_date'));
                    $date = date_format($date, "Y-m-d H:i:s");

                    /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                          $q->whereDate('created_at', '>', $date);
                          //  ->where('total_area','<', $area_range->area_to);
                      });*/

                    $finiceing = $finiceing->whereDate('created_at', '>', $date);
                }
                if ($request->get('to_date')) {
                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d H:i:s");

                    /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                           $q->whereDate('created_at', '<', $date);
                           //  ->where('total_area','<', $area_range->area_to);
                       });*/
                    $finiceing = $finiceing->whereDate('created_at', '<', $date);

                }


                if ($request->get('search')) {
                    $finiceing = $finiceing->where('status', $request->get('search'))
                        ->orWhereHas('estate', function ($query) use ($request) {
                            $query->where('owner_name', $request->get('search'))
                                ->where('owner_mobile', $request->get('search'));
                        })
                        ->orWhereHas('provider', function ($query) use ($request) {
                            $query->where('name', $request->get('search'));
                        })
                        ->orwhere('beneficiary_name', $request->get('search'))
                        ->orwhere('beneficiary_mobile', $request->get('search'));

                }


                $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);

                return response()->success("Fund Request Offer", $finiceing);
            }


            // $collection = RequestFundOfferResource::collection($finiceing);
        } else {
            return response()->error(__('views.some_error'));
        }


    }


    public function availableOfferstest(Request $request)
    {



        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);

        $request_fund = RequestFund::get();

        $cityArray = ['4353', '2509', '4356'];
        $typeEstateArray = ['2', '4'];

        $finiceingCount = 0;
        foreach ($request_fund as $request_fundItem) {


            if ($request_fundItem) {


                $city = City::where('serial_city', $request_fundItem->city_id)->first();
                $cityArrayResult = null;
                if (in_array($city->city_id, $cityArray)) {
                    $cityArrayResult = City::whereIn('serial_city', $cityArray)->pluck('state_id');
                }

                if ($city) {
                    $area_range = AreaEstate::find($request_fundItem->area_estate_id);
                    $price_range = EstatePrice::find($request_fundItem->estate_price_id);


                    $array_dir = dirctions($request_fundItem->dir_estate_id);



                        $finiceing = Estate::whereHas('user', function ($q) {


                            //  $q->where('is_fund_certified', 1)
                            //      ->where('is_pay', 1);
                        });

                        //   ->where('city_id', $request_fund->city_id)

                        $EstateArrayResult = null;
                        if (in_array($request_fundItem->estate_type_id, $typeEstateArray)) {
                            $EstateArrayResult = $typeEstateArray;
                        }


                        //   dd($EstateArrayResult);

                        if (isset($EstateArrayResult)) {
                            $finiceing->whereIn('estate_type_id', $EstateArrayResult);
                        } else {
                            $finiceing->where('estate_type_id', $request_fundItem->estate_type_id);
                        }

                        $neb = FundRequestNeighborhood::where('request_fund_id', $request_fundItem->id)->first();

                        if (isset($neb)) {
                            $nebArray = FundRequestNeighborhood::where('request_fund_id', $request_fundItem->id)->pluck('neighborhood_id');

                            $finiceing->whereIn('neighborhood_id', $nebArray->toArray());
                        }


                        $finiceing = $finiceing
                            ->where('in_fund_offer', '=', '1')
                            //   ->where('total_area', '>', $area_range->area_from)
                            //   ->where('total_area', '<', $area_range->area_to)
                            //   ->where('interface', $array_dir)
                            //    ->where('total_price', '>', $price_range->estate_price_from)
                            //    ->where('total_price', '>=', $price_range->estate_price_to)
                            //   ->whereRaw('(total_price+(total_price*0.15)) <='.$price_range->estate_price_to)
                            //    ->whereRaw('(total_area+(total_area*0.15))  <='.$area_range->area_to)
                            ->where('total_price', '<=', ($price_range->estate_price_to + ($price_range->estate_price_to * 0.15)))
                            ->where('total_area', '<=', ($area_range->area_to + ($area_range->area_to * 0.15)))
                            //  ->whereIn('neighborhood_id', 1);
//price <= property_price + (property_price * 0.15)
                            //    1.15<=1.5
                            //price هو السعر المطلوب في الطلب من الصندوق
                            //property_price سعر العقار الموجود في العرض
                            ->with('comforts', 'EstateFile', 'user');


                        //     dd($request_fund->city_id);
                        if (isset($cityArrayResult)) {

                            $finiceing = $finiceing->whereIn('city_id', $cityArrayResult->toArray());
                        } else {
                            //dd(444);
                            $finiceing = $finiceing->where('city_id', $request_fundItem->city_id);

                        }



                    if ($request->get('status') && $request->get('status') != 'all') {

                        $finiceing = $finiceing->where('status', $request->get('status'));
                    }

                    if ($request->get('time')) {


                        if ($request->get('time') == 'today') {
                            $finiceing = $finiceing->whereDate(
                                'created_at',
                                '=',
                                Carbon::parse(date('Y-m-d'))
                            );
                        }
                        if ($request->get('time') == 'tow_today') {
                            $finiceing = $finiceing->whereDate(
                                'created_at',
                                '>=',

                                Carbon::yesterday()->format('Y-m-d')
                            );
                            $finiceing = $finiceing->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );
                        }
                        if ($request->get('time') == 'week') {
                            $finiceing = $finiceing->whereDate(
                                'created_at',
                                '>=',

                                Carbon::now()->subDays(6)->format('Y-m-d')
                            );
                            $finiceing = $finiceing->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );
                        }


                        /* if ($request->get('time') == 'today') {
                             $offers = $offers->where('');
                         }*/

                    }
                    if ($request->get('uuid')) {
                        $finiceing = $finiceing->where('uuid', $request->get('uuid'));
                    }
                    //  dd($finiceing);


                    if ($request->get('estate_type_id')) {
                        $EstateArrayResult = null;
                        if (in_array($request->get('estate_type_id'), $typeEstateArray)) {
                            $EstateArrayResult = $typeEstateArray;
                        }


                        if (isset($EstateArrayResult)) {
                            $finiceing->whereIn('estate_type_id', $EstateArrayResult);
                        } else {
                            $finiceing->where('estate_type_id', $request->get('estate_type_id'));
                        }


                    }
                    if ($request->get('area_estate_id')) {


                        $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                        $finiceing->where('total_area', '>', $area_range->area_from)
                            ->where('total_area', '<', $area_range->area_to);

                    }
                    if ($request->get('dir_estate_id')) {


                        $array = ['north', 'south', 'east', 'west'];


                        $finiceing->where('interface', $array[$request->get('dir_estate_id')]);
                        //  ->where('total_area','<', $area_range->area_to);


                    }
                    if ($request->get('estate_price_id')) {

                        $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                        $finiceing->where('total_price', '>', $price_range->estate_price_from)
                            ->where('total_price', '<', $price_range->estate_price_to);

                    }


                    if ($request->get('state_id')) {
                        $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                            $query->where('state_id', $request->get('state_id'));
                        });
                    }


                    if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


                        $finiceing = $finiceing->whereHas('estate', function ($q) use ($request) {


                            $q->where('neighborhood_id', $request->get('neighborhood_id'));
                            //  ->where('total_area','<', $area_range->area_to);
                        });


                        //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                    }
                    if ($request->get('form_date')) {
                        $date = date_create($request->get('form_date'));
                        $date = date_format($date, "Y-m-d H:i:s");

                        /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                              $q->whereDate('created_at', '>', $date);
                              //  ->where('total_area','<', $area_range->area_to);
                          });*/

                        $finiceing = $finiceing->whereDate('created_at', '>', $date);
                    }
                    if ($request->get('to_date')) {
                        $date = date_create($request->get('to_date'));
                        $date = date_format($date, "Y-m-d H:i:s");

                        /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                               $q->whereDate('created_at', '<', $date);
                               //  ->where('total_area','<', $area_range->area_to);
                           });*/
                        $finiceing = $finiceing->whereDate('created_at', '<', $date);

                    }


                    if ($request->get('search')) {
                        $finiceing = $finiceing->where('status', $request->get('search'))
                            ->orWhereHas('estate', function ($query) use ($request) {
                                $query->where('owner_name', $request->get('search'))
                                    ->where('owner_mobile', $request->get('search'));
                            })
                            ->orWhereHas('provider', function ($query) use ($request) {
                                $query->where('name', $request->get('search'));
                            })
                            ->orwhere('beneficiary_name', $request->get('search'))
                            ->orwhere('beneficiary_mobile', $request->get('search'));

                    }


                    $finiceingCount += $finiceing->count();


                   // return response()->success("Fund Request Offer", $finiceing);
                }


                // $collection = RequestFundOfferResource::collection($finiceing);
            }
        }

        dd($finiceingCount);
        //  dd($request_fund);



    }

    public function availableEstate(Request $request)
    {


        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);


        $finiceing = Estate::query();


        if ($request->get('status') && $request->get('status') != 'all') {

            $finiceing = $finiceing->where('status', $request->get('status'));
        }

        if ($request->get('time')) {


            if ($request->get('time') == 'today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $finiceing = $finiceing->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }


            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }
        if ($request->get('uuid')) {
            $finiceing = $finiceing->where('uuid', $request->get('uuid'));
        }
        //  dd($finiceing);


        if ($request->get('estate_type_id')) {


            $finiceing->where('estate_type_id', $request->get('estate_type_id'));


        }
        if ($request->get('area_estate_id')) {


            $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


            $finiceing->where('total_area', '>', $area_range->area_from)
                ->where('total_area', '<', $area_range->area_to);

        }
        if ($request->get('dir_estate_id')) {


            $array = ['north', 'south', 'east', 'west'];


            $finiceing->where('interface', $array[$request->get('dir_estate_id')]);
            //  ->where('total_area','<', $area_range->area_to);


        }
        if ($request->get('estate_price_id')) {

            $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


            $finiceing->where('total_price', '>', $price_range->estate_price_from)
                ->where('total_price', '<', $price_range->estate_price_to);

        }


        if ($request->get('state_id')) {
            $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }


        if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


            $finiceing = $finiceing->whereHas('estate', function ($q) use ($request) {


                $q->where('neighborhood_id', $request->get('neighborhood_id'));
                //  ->where('total_area','<', $area_range->area_to);
            });


            //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
        }
        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                  $q->whereDate('created_at', '>', $date);
                  //  ->where('total_area','<', $area_range->area_to);
              });*/

            $finiceing = $finiceing->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {
            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                   $q->whereDate('created_at', '<', $date);
                   //  ->where('total_area','<', $area_range->area_to);
               });*/
            $finiceing = $finiceing->whereDate('created_at', '<', $date);

        }


        if ($request->get('search')) {
            $finiceing = $finiceing->where('status', $request->get('search'))
                ->orWhereHas('estate', function ($query) use ($request) {
                    $query->where('owner_name', $request->get('search'))
                        ->where('owner_mobile', $request->get('search'));
                })
                ->orWhereHas('provider', function ($query) use ($request) {
                    $query->where('name', $request->get('search'));
                })
                ->orwhere('beneficiary_name', $request->get('search'))
                ->orwhere('beneficiary_mobile', $request->get('search'));

        }


        $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);

        return response()->success("Fund Request Offer", $finiceing);


        // $collection = RequestFundOfferResource::collection($finiceing);


    }


    public function availableFundRequest(Request $request, $id)
    {

        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);

        $estate = Estate::where('id', $id)->first();

        $cityArray = ['4353', '2509', '4356'];
        $typeEstateArray = ['2', '4'];


        //  dd($request_fund);
        if ($estate) {


            $city = City::where('serial_city', $estate->city_id)->first();
            $cityArrayResult = null;
            if (in_array($city->city_id, $cityArray)) {
                $cityArrayResult = City::whereIn('serial_city', $cityArray)->pluck('state_id');
            }

            if ($city) {


                if ($request->get('search_type') == 'all') {
                    /*  $finiceing = Estate::whereHas('user', function ($q) {


                          /*  $q->where('is_fund_certified', 1)
                                ->where('is_pay', 1);*/
                    /*    })



                            ->whereHas('city', function ($q) use ($city, $cityArrayResult) {


                                if (isset($cityArrayResult)) {
                                    $q->whereIn('state_id', $cityArrayResult->toArray());
                                } else {
                                    $q->where('state_id', $city->state_id);
                                }


                                //  ->whereIn('neighborhood_id', 1);
                            })
                            ->with('comforts', 'EstateFile', 'user');*/

                    $finiceing = RequestFund::query();

                    if (isset($cityArrayResult)) {
                        $finiceing = $finiceing->whereIn('city_id', $cityArrayResult->toArray());
                    } else {
                        $finiceing = $finiceing->where('city_id', $estate->city_id);
                    }
                } else {
                    /*  $finiceing = RequestFund::whereHas('user', function ($q) {


                          $q->where('is_fund_certified', 1)
                              ->where('is_pay', 1);
                      });*/

                    //   ->where('city_id', $request_fund->city_id)
                    $finiceing = RequestFund::query();
                    $EstateArrayResult = null;
                    if (in_array($estate->estate_type_id, $typeEstateArray)) {
                        $EstateArrayResult = $typeEstateArray;
                    }


                    if (isset($EstateArrayResult)) {
                        $finiceing->whereIn('estate_type_id', $EstateArrayResult);
                    } else {
                        $finiceing->where('estate_type_id', $estate->estate_type_id);
                    }
                    /* $finiceing=   $finiceing->where('total_area', '>', $area_range->area_from)
                         ->where('total_area', '<', $area_range->area_to)
                         ->where('interface', $array_dir)
                         ->where('total_price', '>', $price_range->estate_price_from)
                         ->where('total_price', '<', $price_range->estate_price_to)
                         //  ->whereIn('neighborhood_id', 1);

                         ->with('comforts', 'EstateFile', 'user');
     */

                    if (isset($cityArrayResult)) {
                        $finiceing->whereIn('city_id', $cityArrayResult->toArray());
                    } else {
                        $finiceing->where('city_id', $estate->city_id);
                    }
                }


                if ($request->get('status') && $request->get('status') != 'all') {

                    $finiceing = $finiceing->where('status', $request->get('status'));
                }

                if ($request->get('time')) {


                    if ($request->get('time') == 'today') {
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }
                    if ($request->get('time') == 'tow_today') {
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '>=',

                            Carbon::yesterday()->format('Y-m-d')
                        );
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }
                    if ($request->get('time') == 'week') {
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $finiceing = $finiceing->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }


                    /* if ($request->get('time') == 'today') {
                         $offers = $offers->where('');
                     }*/

                }
                if ($request->get('uuid')) {
                    $finiceing = $finiceing->where('uuid', $request->get('uuid'));
                }
                //  dd($finiceing);


                if ($request->get('estate_type_id')) {
                    $EstateArrayResult = null;
                    if (in_array($request->get('estate_type_id'), $typeEstateArray)) {
                        $EstateArrayResult = $typeEstateArray;
                    }


                    if (isset($EstateArrayResult)) {
                        $finiceing->whereIn('estate_type_id', $EstateArrayResult);
                    } else {
                        $finiceing->where('estate_type_id', $request->get('estate_type_id'));
                    }


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $finiceing->where('total_area', '>', $area_range->area_from)
                        ->where('total_area', '<', $area_range->area_to);

                }
                if ($request->get('dir_estate_id')) {


                    $array = ['north', 'south', 'east', 'west'];


                    $finiceing->where('interface', $array[$request->get('dir_estate_id')]);
                    //  ->where('total_area','<', $area_range->area_to);


                }
                if ($request->get('estate_price_id')) {

                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $finiceing->where('total_price', '>', $price_range->estate_price_from)
                        ->where('total_price', '<', $price_range->estate_price_to);

                }


                if ($request->get('state_id')) {
                    $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                        $query->where('state_id', $request->get('state_id'));
                    });
                }


                if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


                    $finiceing = $finiceing->whereHas('estate', function ($q) use ($request) {


                        $q->where('neighborhood_id', $request->get('neighborhood_id'));
                        //  ->where('total_area','<', $area_range->area_to);
                    });


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }
                if ($request->get('form_date')) {
                    $date = date_create($request->get('form_date'));
                    $date = date_format($date, "Y-m-d H:i:s");

                    /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                          $q->whereDate('created_at', '>', $date);
                          //  ->where('total_area','<', $area_range->area_to);
                      });*/

                    $finiceing = $finiceing->whereDate('created_at', '>', $date);
                }
                if ($request->get('to_date')) {
                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d H:i:s");

                    /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                           $q->whereDate('created_at', '<', $date);
                           //  ->where('total_area','<', $area_range->area_to);
                       });*/
                    $finiceing = $finiceing->whereDate('created_at', '<', $date);

                }


                if ($request->get('search')) {
                    $finiceing = $finiceing->where('status', $request->get('search'))
                        ->orWhereHas('estate', function ($query) use ($request) {
                            $query->where('owner_name', $request->get('search'))
                                ->where('owner_mobile', $request->get('search'));
                        })
                        ->orWhereHas('provider', function ($query) use ($request) {
                            $query->where('name', $request->get('search'));
                        })
                        ->orwhere('beneficiary_name', $request->get('search'))
                        ->orwhere('beneficiary_mobile', $request->get('search'));

                }


                $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);

                return response()->success("Fund Request Offer", $finiceing);
            }


            // $collection = RequestFundOfferResource::collection($finiceing);
        } else {
            return response()->error(__('views.some_error'));
        }
    }


    public function send_offer_fund_dash(Request $request)
    {

        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [


            'uuid' => 'sometimes|required|exists:request_funds,uuid',
            // 'estate_type_id' => 'required',
            'estate_id' => 'sometimes|required|exists:estates,id',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $EstateRequest = RequestFund::where('uuid', $request->get('uuid'))->first();
        $estate = Estate::whereHas('EstateFile')
            ->whereHas('user')
            ->where('id', $request->get('estate_id'))->first();

        if(!$estate)
        {
            return response()->error(__("views.estate dont match the request"));
        }

        $checkOfferEx = FundRequestOffer::where('uuid', $request->get('uuid'))
           // ->where('provider_id', $user->id)
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
            Log::channel('slack')->info(json_encode($data->msg));
            Log::channel('slack')->info(json_encode($request->all()));
            //   Log::channel('slack')->info(json_encode($data));
        }

        if (!$checkOfferEx) {


            //   }

            $user = User::find($estate->user_id)->first();
            //  $user->count_offer = $user->count_offer + 1;
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
        }
        else {
            if ($user == null) {
                return response()->error(__("views.offer send privies"));
            }
        }

        return response()->success(__("views.FundRequestOffer"), []);
    }

    public function singleEstate($id)
    {
        $estate = Estate::with('EstateFile')->find($id);
        if ($estate && $estate->user)
        {
            $estate->estate_iam_name = @$estate->user->Iam_information->name;
            $estate->estate_iam_dobHijri = @$estate->user->Iam_information->dobHijri;
            $estate->estate_iam_dob = @$estate->user->Iam_information->dob;
            $estate->estate_user_mobile = @$estate->user->mobile;
            $estate->estate_user_identity = @$estate->user->identity;
            $estate->estate_user_license_number = @$estate->user->license_number;
        }else{
            return response()->error(__("views.not found"));
        }
        return response()->success("Estate", $estate);
    }

    public function singleRequest($id)
    {


        $finiceing = RequestFund::with('neighborhood', 'offers')->find($id);

        return response()->success("Fund Request", $finiceing);

    }

    public function singleOffer($id)
    {


        $finiceing = FundRequestOffer::whereHas('provider')->whereHas('estate')->whereHas('fund_request')
            ->with('fund_request', 'estate', 'estate.comforts', 'estate.EstateFile',
                'provider')->find($id);

        return response()->success("Fund Request Offer", $finiceing);

    }


    public function rejectOffer($id,Request $request)
    {

        $rules = Validator::make($request->all(), [

            'reason'                => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $finiceing = FundRequestOffer::whereHas('provider')->whereHas('estate')->whereHas('fund_request')->find($id);
        $finiceing->reason=$request->get('reason');
        $finiceing->save();
        $estate=Estate::find($finiceing->estate_id);
        if($estate)
        {
            $estate->status='closed';
            $estate->save();


            $push_data = [
                'title' => __('views.You Offer Rejected  #') . $finiceing->id,
                'body' =>$request->get('reason'),
                'id' => $finiceing->id,
                'user_id' => $estate->user_id,
                'type' => 'fund_offer',
            ];

            $note = NotificationUser::create([
                'user_id' => $estate->user_id,
                'title' => 'تم رفض عرضك رقم :' . $finiceing->id,
                'type' => 'fund_offer',
                'type_id' => $finiceing->id,
            ]);
            $client=User::where('id',$estate->user_id)->first();
            if($client)
            {
                $fcm_token = FcmToken::where('user_id', $client->id)->get();
                foreach ($fcm_token as $token) {
                    send_push($token->token, $push_data, $token->type);
                }
            }
        }


        return response()->success("Fund Request Offer Close", $finiceing);

    }

    public function singleProvider1($id)
    {


        $finiceing = User::where('type', 'provider')->find($id);

        return response()->success("Provider", $finiceing);

    }

    public function singleProvider($id)
    {
        $user = User::find($id);
        if ($user == null) {
            return response()->error(__('views.not found'));
        }
        $count_estate = Estate::where('user_id', $user->id)->count();
        $count_request = EstateRequest::where('user_id', $user->id)->count();
        $count_offer = RequestOffer::where('provider_id', $user->id)->count();
        $count_client = Client::where('user_id', $user->id)->count();
        $count_accept_offer = RequestOffer::where('provider_id', $user->id)
            ->where('status', 'accepted_customer')
            ->count();
        $count_accept_fund_offer = FundRequestOffer::where('provider_id', $user->id)
            ->where('status', 'accepted_customer')
            ->count();

        $new_estate = Estate::where('user_id', $user->id)->orderBy('id', 'desc')->limit(10)->get();
        $new_offer = RequestOffer::with('estate')->where('provider_id', $user->id)->orderBy('id',
            'desc')->limit(10)->get();
        $new_fund_offer = FundRequestOffer::where('provider_id', $user->id)->orderBy('id', 'desc')->limit(10)->get();
        // dd($categories->toArray());

        $array =
            [
                'user' => $user,
                // 'count_estate'=>$count_estate,
                'count_request' => $count_request,
                //   'count_offer'=>$count_offer,
                //   'count_client'=>$count_client,
                //   'count_accept_offer'=>$count_accept_offer,
                //  'count_accept_fund_offer'=>$count_accept_fund_offer,
                'new_estate' => $new_estate,
                'new_offer' => $new_offer,
                'new_fund_offer' => $new_fund_offer
            ];

        return response()->success("User", $array);


    }


}
