<?php

namespace App\Http\Controllers\DashBoard;


use App\Http\Controllers\Controller;

use App\Http\Resources\Dashboard\EstateRequestPreviewResource;
use App\Http\Resources\Dashboard\FundingRequestResource;
use App\Http\Resources\EstateRequestRateResource;
use App\Http\Resources\OfficesResource;

use App\Http\Resources\v4\DistrictResource;
use App\Http\Resources\v4\NeighborhoodResource;
use App\Http\Resources\v4\RateRequestShowResource;
use App\Models\v3\AreaEstate;
use App\Models\v3\AttachmentEstate;
use App\Models\v3\AttachmentPlanned;
use App\Models\v3\City;

use App\Models\v3\City3;
use App\Models\v3\Comfort;
use App\Models\v3\ComfortEstate;
use App\Models\v3\Contact;
use App\Models\v3\CourseType;
use App\Models\v3\District;
use App\Models\v3\Estate;
use App\Models\v3\EstatePrice;
use App\Models\v3\EstateRequest;
use App\Models\v3\EstateType;

use App\Models\v3\ExperienceType;
use App\Models\v3\FundRequestNeighborhood;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\MemberType;

use App\Models\v3\Neighborhood;
use App\Models\v3\OprationType;
use App\Models\v3\Plan;
use App\Models\v3\RateRequest;
use App\Models\v3\Region;
use App\Models\v3\Report;
use App\Models\v3\RequestFund;
use App\Models\v3\RequestOffer;
use App\Models\v3\ServiceType;
use App\Models\v3\UserPayment;
use App\Models\v4\Cities;
use App\Models\v4\EstateRequestPreview;
use App\Models\v4\FundingRequest;
use App\Unifonic\UnifonicMessage;
use App\User;
use App\Helpers\JsonResponse;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class SettingController extends Controller
{

    public function dashboard(Request $request)
    {


        $page = $request->get('page_number', 15);
        if ($request->get('type')) {
            if ($request->get('type') == 'fund_request') {
                $fund_request = DB::table('request_funds')
                    ->whereRaw('request_funds.deleted_at is null');

                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $fund_request = $fund_request->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $fund_request = $fund_request->whereDate(
                                    'created_at',
                                    '=',
                                    $request->get('from_date')
                                );
                            } else {
                                $fund_request = $fund_request->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {

                    $fund_request = $fund_request->whereDate('created_at', '>', $request->get('from_date'));


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {
                    $fund_request = $fund_request->whereDate('created_at', '<', $request->get('to_date'));
                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $fund_request = $fund_request->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $fund_request = $fund_request->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $fund_request = $fund_request->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $fund_request = $fund_request->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $fund_request = $fund_request->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }

                //  dd($finiceing);


                if ($request->get('estate_type_id')) {


                    $fund_request = $fund_request->where('estate_type_id', $request->get('estate_type_id'));


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $fund_request = $fund_request->where('area_estate_id', $request->get('area_estate_id'));
                }

                if ($request->get('estate_price_id')) {

                    $fund_request = $fund_request->where('estate_price_id', $request->get('estate_price_id'));

                }
                if ($request->get('city_id')) {
                    $fund_request = $fund_request->where('city_id', $request->get('city_id'));
                }


                if ($request->get('state_id') && !$request->get('city_id')) {
                    $fund_request = $fund_request->where('state_id', $request->get('state_id'));
                } elseif ($request->get('state_id') && $request->get('city_id')) {

                }


                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);
                    $fund_ids = FundRequestNeighborhood::whereIn('neighborhood_id', $neighborhood_id_array)->pluck('request_fund_id');

                    $fund_request = $fund_request->whereIn('request_funds.id', $fund_ids->toArray());
                }

                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && RequestFund::find($request->get('search'))) {
                        $fund_request = $fund_request->where('request_funds.id', $request->get('search'));

                    } else {
                        $fund_request = $fund_request->where('status', $request->get('search'))
                            ->orWhere('uuid', 'like', '%' . $request->get('search') . '%');
                    }


                }


                $fund_request = $fund_request->orderBy('id', 'desc')->paginate($page);


                $information = [

                    'fund_request' => $fund_request,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }

            if ($request->get('type') == 'app_request') {
                $app_request = EstateRequest::withCount('offers');
                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $app_request = $app_request->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $app_request = $app_request->whereDate(
                                    'created_at',
                                    $request->get('to_date')
                                );
                            } else {
                                $app_request = $app_request->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $app_request = $app_request->whereDate('created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $app_request = $app_request->whereDate('created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $app_request = $app_request->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $app_request = $app_request->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $app_request = $app_request->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $app_request = $app_request->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $app_request = $app_request->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }


                if ($request->get('estate_type_id')) {


                    $app_request = $app_request->where('estate_type_id', $request->get('estate_type_id'));


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $app_request = $app_request->where('area_from', '>', $area_range->area_from)
                        ->where('area_to', '<', $area_range->area_to);

                }

                if ($request->get('estate_price_id')) {


                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $app_request = $app_request->where('price_from', '>', $price_range->estate_price_from)
                        ->where('price_to', '<', $price_range->estate_price_to);
                }
                if ($request->get('city_id')) {


                    $app_request = $app_request->where('city_id', $request->get('city_id'));;
                }
                if ($request->get('state_id')) {
                    $app_request = $app_request->whereHas('city', function ($q) use ($request) {

                        $q->where('state_id', $request->get('state_id'));
                    });
                }
                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);
                    //   $neb_ids = Neighborhood::whereIn('neighborhood_serial',$request->neighborhood_id)->pluck('neighborhood_serial');

                    $app_request = $app_request->whereIn('neighborhood_id', $neighborhood_id_array);

                    //  ->where('total_area','<', $area_range->area_to);


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }

                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && EstateRequest::find($request->get('search'))) {
                        $app_request = $app_request->where('id', $request->get('search'));

                    } else {
                        $app_request = $app_request->where('status', $request->get('search'))
                            ->orWhere('request_type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('owner_name', 'like', '%' . $request->get('search') . '%');
                    }


                }


                $app_request = $app_request->orderBy('id', 'desc')->paginate($page);


                $information = [

                    'app_request' => $app_request,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }

            if ($request->get('type') == 'fund_request_offer') {
                $fund_request_offer = \App\Models\v3\FundRequestOffer::
                join('request_funds', 'request_funds.uuid', '=', 'fund_request_offers.uuid')
                    ->join('estates', 'fund_request_offers.estate_id', '=', 'estates.id')
                    ->whereRaw('fund_request_offers.deleted_at is null')
                    ->whereRaw('request_funds.deleted_at is null')
                    ->whereRaw('estates.deleted_at is null')
                    ->select('request_funds.*', 'estates.*', 'fund_request_offers.*', 'fund_request_offers.id as offer_id');


                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $fund_request_offer = $fund_request_offer->whereDate(
                            'fund_request_offers.created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $fund_request_offer = $fund_request_offer->whereDate(
                                    'fund_request_offers.created_at',
                                    $request->get('to_date')
                                );
                            } else {
                                $fund_request_offer = $fund_request_offer->whereBetween('fund_request_offers.created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $fund_request_offer = $fund_request_offer->whereDate('fund_request_offers.created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $fund_request_offer = $fund_request_offer->whereDate('fund_request_offers.created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $fund_request_offer = $fund_request_offer->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $fund_request_offer = $fund_request_offer->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $fund_request_offer = $fund_request_offer->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $fund_request_offer = $fund_request_offer->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $fund_request_offer = $fund_request_offer->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }


                if ($request->get('estate_type_id')) {


                    $fund_request_offer = $fund_request_offer->whereRaw('estates.estate_type_id =' . $request->get('estate_type_id'));


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $fund_request_offer = $fund_request_offer->whereRaw('estates.total_area > ' . $area_range->area_from)
                        ->whereRaw('estates.total_area < ' . $area_range->area_to);


                }

                if ($request->get('estate_price_id')) {

                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $fund_request_offer = $fund_request_offer->whereRaw('estates.total_price > ' . $price_range->estate_price_from)
                        ->whereRaw('estates.total_price < ' . $price_range->estate_price_to);
                }
                if ($request->get('city_id')) {


                    $fund_request_offer = $fund_request_offer->whereRaw('estates.city_id = ' . $request->get('city_id'));

                }


                if ($request->get('state_id')) {

                    $fund_request_offer = $fund_request_offer->whereRaw('estates.state_id = ' . $request->get('state_id'));

                }


                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);

                    $array = array_map('intval', $neighborhood_id_array);
                    // $array = implode(",", $array);
                    //   $query .= ' and city_id IN ' . $array;
                    $array = join(",", $array);

                    $fund_request_offer = $fund_request_offer->whereRaw('estates.neighborhood_id   IN (' . $array . ') ');

                    //  ->where('total_area','<', $area_range->area_to);


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }


                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && FundRequestOffer::find($request->get('search'))) {
                        $fund_request_offer = $fund_request_offer->where('fund_request_offers.id', $request->get('search'));

                    } else {
                        $fund_request_offer = $fund_request_offer->where('status', $request->get('search'))
                            ->orWhere('request_type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('uuid', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('beneficiary_name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('send_offer_type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('paid_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('priority', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('contract_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('stage_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('funding_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('preview_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('contact_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('owner_name', 'like', '%' . $request->get('search') . '%');
                    }


                }

                $fund_request_offer = $fund_request_offer->orderBy('fund_request_offers.id', 'desc')->paginate($page);


                $information = [

                    'fund_request_offer' => $fund_request_offer,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }

            if ($request->get('type') == 'request_offer') {
                $request_offer = RequestOffer::whereHas('estate')
                    ->whereHas('provider')
                    ->whereHas('request')
                    ->with('estate')
                    ->with('provider');


                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $request_offer = $request_offer->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $request_offer = $request_offer->whereDate(
                                    'created_at',
                                    $request->get('to_date')
                                );
                            } else {
                                $request_offer = $request_offer->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $request_offer = $request_offer->whereDate('created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $request_offer = $request_offer->whereDate('created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $request_offer = $request_offer->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $request_offer = $request_offer->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $request_offer = $request_offer->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $request_offer = $request_offer->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $request_offer = $request_offer->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }
                if ($request->get('estate_type_id')) {


                    $request_offer = $request_offer->WhereHas('estate', function ($query) use ($request) {
                        $query->where('estates.estate_type_id', $request->get('estate_type_id'));
                    });

                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));

                    $request_offer = $request_offer->whereHas('estate', function ($q) use ($request, $area_range) {


                        $q->where('total_area', '>', $area_range->area_from)
                            ->where('total_area', '<', $area_range->area_to);
                    });

                }

                if ($request->get('estate_price_id')) {

                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $request_offer = $request_offer->whereHas('estate', function ($q) use ($request, $price_range) {


                        $q->where('total_price', '>', $price_range->estate_price_from)
                            ->where('total_price', '<', $price_range->estate_price_to);
                    });
                }
                if ($request->get('city_id')) {


                    $request_offer = $request_offer->WhereHas('estate', function ($query) use ($request) {
                        $query->where('estates.city_id', $request->get('city_id'));
                    });

                }


                if ($request->get('state_id')) {
                    $request_offer = $request_offer->WhereHas('estate', function ($query) use ($request) {
                        $query->where('estates.state_id', $request->get('state_id'));
                    });
                }


                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);

                    $neb_ids = $neighborhood_id_array;

                    $request_offer = $request_offer->WhereHas('estate', function ($query) use ($neb_ids) {
                        $query->whereIn('estates.neighborhood_id', $neb_ids);
                    });
                    //  ->where('total_area','<', $area_range->area_to);


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }

                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && RequestOffer::find($request->get('search'))) {
                        $request_offer = $request_offer->where('id', $request->get('search'));

                    } else {
                        $request_offer = $request_offer->where('status', $request->get('search'))
                            ->orWhere('guarantees', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('status', 'like', '%' . $request->get('search') . '%');
                    }


                }

                $request_offer = $request_offer->orderBy('id', 'desc')->paginate($page);


                $information = [

                    'request_offer' => $request_offer,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }


            if ($request->get('type') == 'estate') {
                $estate = Estate::whereHas('user')->where(function ($q) use ($request) {
                    $q->where(function ($q) {
                        $q->where('neighborhood_id', '!=', null)->where('city_id', '!=', null);
                    })->orWhere(function ($q) {
                        $q->where('city_id', '!=', null)->where('neighborhood_id', null)->where('neighborhood_name_request', null);
                    });

                });

                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $estate = $estate->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $estate = $estate->whereDate(
                                    'created_at',
                                    '=',
                                    $request->get('from_date')
                                );
                            } else {
                                $estate = $estate->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get(!$request->get('time')) && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $estate = $estate->whereDate('created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $estate = $estate->whereDate('created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $estate = $estate->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $estate = $estate->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $estate = $estate->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $estate = $estate->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $estate = $estate->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }


                if ($request->get('estate_type_id')) {


                    $estate = $estate->where('estate_type_id', $request->get('estate_type_id'));


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));

                    $estate = $estate->where('total_area', '>', $area_range->area_from)
                        ->where('total_area', '<', $area_range->area_to);


                }

                if ($request->get('estate_price_id')) {

                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $estate = $estate->where('total_price', '>', $price_range->estate_price_from)
                        ->where('total_price', '<', $price_range->estate_price_to);
                }
                if ($request->get('city_id')) {
                    $estate = $estate->where('city_id', $request->get('city_id'));
                }

                if ($request->get('state_id') && !$request->get('city_id')) {
                    $estate = $estate->where('state_id', $request->get('state_id'));
                } elseif ($request->get('state_id') && $request->get('city_id')) {

                }


                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);
                    $neb_ids = $neighborhood_id_array;


                    $estate = $estate->whereIn('neighborhood_id', $neb_ids);

                    //  ->where('total_area','<', $area_range->area_to);


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }

                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && Estate::find($request->get('search'))) {
                        $estate = $estate->where('id', $request->get('search'));

                    } else {
                        $estate = $estate->where('status', $request->get('search'))
                            ->orWhere('operation_type_name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('estate_type_name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('interface', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('advertiser_side', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('advertiser_character', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('finishing_type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('social_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('rent_type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('street_name', 'like', '%' . $request->get('search') . '%');
                    }


                }

                $estate = $estate->orderBy('id', 'desc')->paginate($page);


                $information = [

                    'estate' => $estate,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }

            if ($request->get('type') == 'fund_review_request_offer') {
                $fund_review_request_offer = \App\Models\v3\FundRequestOffer::
                join('request_funds', 'request_funds.uuid', '=', 'fund_request_offers.uuid')
                    ->join('estates', 'fund_request_offers.estate_id', '=', 'estates.id')
                    ->whereRaw('fund_request_offers.deleted_at is null')
                    ->whereRaw('request_funds.deleted_at is null')
                    ->whereRaw('fund_request_offers.status = "sending_code"')
                    ->whereRaw('estates.deleted_at is null')
                    ->select('estates.*', 'fund_request_offers.*', 'request_funds.*', 'fund_request_offers.id as offer_id');


                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {
                        $fund_review_request_offer = $fund_review_request_offer->whereDate(
                            'fund_request_offers.created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $fund_review_request_offer = $fund_review_request_offer->whereDate(
                                    'fund_request_offers.created_at',
                                    '=',
                                    $request->get('to_date')
                                );
                            } else {
                                $fund_review_request_offer = $fund_review_request_offer->whereBetween('fund_request_offers.created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get(!$request->get('time')) && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $fund_review_request_offer = $fund_review_request_offer->whereDate('fund_request_offers.created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $fund_review_request_offer = $fund_review_request_offer->whereDate('fund_request_offers.created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $fund_review_request_offer = $fund_review_request_offer->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $fund_review_request_offer = $fund_review_request_offer->whereDate(
                            'fund_request_offers.created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $fund_review_request_offer = $fund_review_request_offer->whereDate(
                            'fund_request_offers.created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $fund_review_request_offer = $fund_review_request_offer->whereDate(
                            'fund_request_offers.created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $fund_review_request_offer = $fund_review_request_offer->whereDate(
                            'fund_request_offers.created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }


                if ($request->get('estate_type_id')) {


                    $fund_review_request_offer = $fund_review_request_offer->whereRaw('estates.estate_type_id =' . $request->get('estate_type_id'));


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $fund_review_request_offer = $fund_review_request_offer->whereRaw('estates.total_area > ' . $area_range->area_from)
                        ->whereRaw('estates.total_area < ' . $area_range->area_to);


                }

                if ($request->get('estate_price_id')) {

                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $fund_review_request_offer = $fund_review_request_offer->whereRaw('estates.total_price > ' . $price_range->estate_price_from)
                        ->whereRaw('estates.total_price < ' . $price_range->estate_price_to);
                }
                if ($request->get('city_id')) {


                    $fund_review_request_offer = $fund_review_request_offer->whereRaw('estates.city_id = ' . $request->get('city_id'));

                }


                if ($request->get('state_id')) {


                    $fund_review_request_offer = $fund_review_request_offer->whereRaw('estates.state_id = ' . $request->get('state_id'));

                }


                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);
                    $neb_ids = $neighborhood_id_array;

                    $array = array_map('intval', $neb_ids);
                    // $array = implode(",", $array);
                    //   $query .= ' and city_id IN ' . $array;
                    $array = join(",", $array);

                    $fund_review_request_offer = $fund_review_request_offer->whereRaw('estates.neighborhood_id   IN (' . $array . ') ');

                    //  ->where('total_area','<', $area_range->area_to);


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }


                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && FundRequestOffer::find($request->get('search'))) {
                        $fund_review_request_offer = $fund_review_request_offer->where('fund_request_offers.id', $request->get('search'));

                    } else {
                        $fund_review_request_offer = $fund_review_request_offer->where('fund_request_offers.status', $request->get('search'))
                            ->orWhere('fund_request_offers.uuid', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('fund_request_offers.beneficiary_name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('send_offer_type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('paid_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('priority', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('contract_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('stage_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('funding_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('preview_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('contact_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('owner_name', 'like', '%' . $request->get('search') . '%');
                    }


                }

                $fund_review_request_offer = $fund_review_request_offer->orderBy('id', 'desc')->paginate($page);


                $information = [

                    'fund_review_request_offer' => $fund_review_request_offer,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }


            if ($request->get('type') == 'fund_request_deleted') {
                $fund_request_deleted = RequestFund::withTrashed()->whereRaw('deleted_at is not null');

                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $fund_request_deleted = $fund_request_deleted->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $fund_request_deleted = $fund_request_deleted->whereDate(
                                    'created_at',
                                    $request->get('to_date')
                                );
                            } else {
                                $fund_request_deleted = $fund_request_deleted->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $fund_request_deleted = $fund_request_deleted->whereDate('created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $fund_request_deleted = $fund_request_deleted->whereDate('created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $fund_request_deleted = $fund_request_deleted->whereDate(
                            'deleted_at_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $fund_request_deleted = $fund_request_deleted->whereDate(
                            'deleted_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $fund_request_deleted = $fund_request_deleted->whereDate(
                            'deleted_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $fund_request_deleted = $fund_request_deleted->whereDate(
                            'deleted_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $fund_request_deleted = $fund_request_deleted->whereDate(
                            'deleted_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }
                if ($request->get('estate_type_id')) {


                    $fund_request_deleted = $fund_request_deleted->where('estate_type_id', $request->get('estate_type_id'));


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $fund_request_deleted = $fund_request_deleted->where('area_estate_id', $request->get('area_estate_id'));
                }

                if ($request->get('estate_price_id')) {

                    $fund_request_deleted = $fund_request_deleted->where('estate_price_id', $request->get('estate_price_id'));

                }
                if ($request->get('city_id')) {


                    $fund_request_deleted = $fund_request_deleted->where('city_id', $request->get('city_id'));;
                }


                if ($request->get('state_id')) {
                    $fund_request_deleted = $fund_request_deleted->where('state_id', $request->get('state_id'));

                }


                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);
                    $fund_ids = FundRequestNeighborhood::whereIn('neighborhood_id', $neighborhood_id_array)->pluck('request_fund_id');

                    $fund_request_deleted = $fund_request_deleted->whereIn('id', $fund_ids->toArray());

                    //  ->where('total_area','<', $area_range->area_to);


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }

                if ($request->get('search')) {

                    //  dd(RequestFund::withTrashed()->find($request->get('search')));
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && RequestFund::withTrashed()->find($request->get('search'))) {
                        $fund_request_deleted = $fund_request_deleted->where('id', $request->get('search'));

                    } else {
                        $fund_request_deleted = $fund_request_deleted->where('status', $request->get('search'))
                            ->orWhere('uuid', 'like', '%' . $request->get('search') . '%');
                    }


                }

                $fund_request_deleted = $fund_request_deleted->orderBy('id', 'desc')->paginate($page);


                $information = [

                    'fund_request_deleted' => $fund_request_deleted,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }

            if ($request->get('type') == 'fund_request_offer_deleted') {
                $fund_request_offer_deleted = DB::table('fund_request_offers')
                    ->join('estates', 'fund_request_offers.estate_id', '=', 'estates.id')
                    ->select('estates.*', 'fund_request_offers.*', 'fund_request_offers.id as offer_id')
                    ->whereRaw('fund_request_offers.deleted_at is not null');

                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                            'fund_request_offers.deleted_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                                    'fund_request_offers.deleted_at',
                                    '=',
                                    $request->get('to_date')
                                );
                            } else {
                                $fund_request_offer_deleted = $fund_request_offer_deleted->whereBetween('fund_request_offers.deleted_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate('fund_request_offers.deleted_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate('fund_request_offers.deleted_at', '<', $date);
                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                            'fund_request_offers.deleted_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                            'fund_request_offers.deleted_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                            'fund_request_offers.deleted_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                            'fund_request_offers.deleted_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                            'fund_request_offers.deleted_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }


                if ($request->get('estate_type_id')) {


                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereRaw('estates.estate_type_id =' . $request->get('estate_type_id'));


                }
                if ($request->get('area_estate_id')) {


                    $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereRaw('estates.total_area > ' . $area_range->area_from)
                        ->whereRaw('estates.total_area < ' . $area_range->area_to);


                }

                if ($request->get('estate_price_id')) {

                    $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereRaw('estates.total_price > ' . $price_range->estate_price_from)
                        ->whereRaw('estates.total_price < ' . $price_range->estate_price_to);
                }
                if ($request->get('city_id')) {


                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereRaw('estates.city_id = ' . $request->get('city_id'));

                }


                if ($request->get('state_id')) {


                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereRaw('estates.state_id = ' . $request->get('state_id'));

                }


                if (isset($request->neighborhood_id)) {

                    $neighborhood_id_array = explode(',', $request->neighborhood_id);
                    $neb_ids = $neighborhood_id_array;

                    $array = array_map('intval', $neb_ids);
                    // $array = implode(",", $array);
                    //   $query .= ' and city_id IN ' . $array;
                    $array = join(",", $array);

                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereRaw('estates.neighborhood_id   IN (' . $array . ') ');

                    //  ->where('total_area','<', $area_range->area_to);


                    //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                }

                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && FundRequestOffer::find($request->get('search'))) {
                        $fund_request_offer_deleted = $fund_request_offer_deleted->where('id', $request->get('search'));

                    } else {
                        $fund_request_offer_deleted = $fund_request_offer_deleted->where('fund_request_offers.status', $request->get('search'))
                            ->orWhere('uuid', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('beneficiary_name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('send_offer_type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('paid_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('priority', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('contract_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('stage_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('funding_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('preview_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('contact_status', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('owner_name', 'like', '%' . $request->get('search') . '%');
                    }


                }


                $fund_request_offer_deleted = $fund_request_offer_deleted->orderBy('id', 'desc')->paginate($page);


                $information = [

                    'fund_request_offer_deleted' => $fund_request_offer_deleted,


                ];

                return response()->success(__('إحصائيات النظام'), $information);
            }


            if ($request->get('type') == 'offices') {
                $users = User::where('type', 'provider')
//                    ->orderBy('count_fund_offer', 'desc')
//                    ->orderBy('count_offer', 'desc')
//                    ->orderBy('count_estate', 'desc');
                ;
                //  ->paginate();


                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $users = $users->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $users = $users->whereDate(
                                    'created_at',
                                    $request->get('to_date')
                                );
                            } else {
                                $users = $users->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $users = $users->whereDate('created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $users = $users->whereDate('created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $users = $users->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $users = $users->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $users = $users->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $users = $users->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $users = $users->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }


                if ($request->get('city_id')) {


                    $users = $users->where('city_id', $request->get('city_id'));


                }


                if ($request->get('state_id')) {
                    $users = $users->WhereHas('city', function ($query) use ($request) {
                        $query->where('state_id', $request->get('state_id'));
                    });
                }


                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && User::find($request->get('search'))) {
                        $users = $users->where('id', $request->get('search'));

                    } else {
                        $users = $users->where('status', $request->get('search'))
                            ->orWhere('name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('email', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('mobile', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('onwer_name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('address', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('user_name', 'like', '%' . $request->get('search') . '%');
                    }


                }


                $users = $users->orderBy('id', 'desc')->paginate($page);
                foreach ($users as $userItem) {

                    $estate_count = Estate::where('user_id', $userItem->id);
                    $fund_offer_count = FundRequestOffer::where('provider_id', $userItem->id);
                    $offer_count = RequestOffer::where('provider_id', $userItem->id);

                    if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                        $today_date = date('Y-m-d');
                        if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {
                            $estate_count = $estate_count->whereDate('created_at', '=', Carbon::parse(date('Y-m-d')));
                            $fund_offer_count = $fund_offer_count->whereDate('created_at', '=', Carbon::parse(date('Y-m-d')));
                            $offer_count = $offer_count->whereDate('created_at', '=', Carbon::parse(date('Y-m-d')));

                        } else {
                            if ($request->get('to_date') && $request->get('from_date')) {
                                if ($request->get('from_date') == $request->get('to_date')) {
                                    $estate_count = $estate_count->whereDate('created_at', '=', $request->get('to_date'));
                                    $fund_offer_count = $fund_offer_count->whereDate('created_at', '=', $request->get('to_date'));
                                    $offer_count = $offer_count->whereDate('created_at', '=', $request->get('to_date'));

                                } else {
                                    $estate_count = $estate_count->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                                    $fund_offer_count = $fund_offer_count->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                                    $offer_count = $offer_count->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                                }
                            }
                        }


                    } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                        $date = date_create($request->get('from_date'));
                        $date = date_format($date, "Y-m-d");

                        $estate_count = $estate_count->whereDate('created_at', '>', $date);
                        $fund_offer_count = $fund_offer_count->whereDate('created_at', '>', $date);
                        $offer_count = $offer_count->whereDate('created_at', '>', $date);


                    } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                        $date = date_create($request->get('to_date'));
                        $date = date_format($date, "Y-m-d");
                        $estate_count = $estate_count->whereDate('created_at', '<', $date);
                        $fund_offer_count = $fund_offer_count->whereDate('created_at', '<', $date);
                        $offer_count = $offer_count->whereDate('created_at', '<', $date);


                    } elseif (!$request->get('to_date') && !$request->get('from_date') && $request->get('time')) {
                        if ($request->get('time') == 'today') {
                            $estate_count = $estate_count
                                ->whereDate(
                                    'created_at',
                                    '=',
                                    Carbon::parse(date('Y-m-d'))
                                );

                            $fund_offer_count = $fund_offer_count
                                ->whereDate(
                                    'created_at',
                                    '=',
                                    Carbon::parse(date('Y-m-d'))
                                );
                            $offer_count = $offer_count
                                ->whereDate(
                                    'created_at',
                                    '=',
                                    Carbon::parse(date('Y-m-d'))
                                );
                        }
                        if ($request->get('time') == 'week') {


                            $estate_count = $estate_count->whereDate(
                                'created_at',
                                '>=',

                                Carbon::now()->subDays(6)->format('Y-m-d')
                            );
                            $estate_count = $estate_count->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );


                            $fund_offer_count = $fund_offer_count->whereDate(
                                'created_at',
                                '>=',

                                Carbon::now()->subDays(6)->format('Y-m-d')
                            );
                            $fund_offer_count = $fund_offer_count->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );


                            $offer_count = $offer_count->whereDate(
                                'created_at',
                                '>=',

                                Carbon::now()->subDays(6)->format('Y-m-d')
                            );
                            $offer_count = $offer_count->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );
                        }
                        if ($request->get('time') == 'month') {

                            $estate_count = $estate_count->whereDate(
                                'created_at',
                                '>=',

                                Carbon::now()->subDays(29)->format('Y-m-d')
                            );
                            $estate_count = $estate_count->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );


                            $fund_offer_count = $fund_offer_count->whereDate(
                                'created_at',
                                '>=',

                                Carbon::now()->subDays(29)->format('Y-m-d')
                            );
                            $fund_offer_count = $fund_offer_count->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );


                            $offer_count = $offer_count->whereDate(
                                'created_at',
                                '>=',

                                Carbon::now()->subDays(29)->format('Y-m-d')
                            );
                            $offer_count = $offer_count->whereDate(
                                'created_at',
                                '<=',
                                Carbon::parse(date('Y-m-d'))
                            );
                        }
                    }


                    $estate_count = $estate_count->count();
                    $fund_offer_count = $fund_offer_count->count();
                    $offer_count = $offer_count->count();
                    $userItem->dash_estate_count = $estate_count;
                    $userItem->dash_fund_offer_count = $fund_offer_count;
                    $userItem->dash_offer_count = $offer_count;


                }

                //    $collection = OfficesResource::collection($users);
                $information = [

                    'offices' => $users,


                ];
                return response()->success(__('إحصائيات النظام'), $information);


            }


            if ($request->get('type') == 'users') {
                $users = User::where('type', 'user');
                //  ->paginate();

                if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

                    $today_date = date('Y-m-d');
                    if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                        $users = $users->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );

                    } else {
                        if ($request->get('to_date') && $request->get('from_date')) {
                            if ($request->get('from_date') == $request->get('to_date')) {
                                $users = $users->whereDate(
                                    'created_at',
                                    $request->get('to_date')
                                );
                            } else {
                                $users = $users->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                            }
                        }
                    }


                } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $users = $users->whereDate('created_at', '>', $date);


                } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $users = $users->whereDate('created_at', '<', $date);


                }


                if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
                    if ($request->get('time') == 'today') {


                        $users = $users->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'week') {

                        $users = $users->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(6)->format('Y-m-d')
                        );
                        $users = $users->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );


                    } elseif ($request->get('time') == 'month') {


                        $users = $users->whereDate(
                            'created_at',
                            '>=',

                            Carbon::now()->subDays(30)->format('Y-m-d')
                        );
                        $users = $users->whereDate(
                            'created_at',
                            '<=',
                            Carbon::parse(date('Y-m-d'))
                        );
                    }

                }


                if ($request->get('city_id')) {


                    $users = $users->where('city_id', $request->get('city_id'));


                }


                if ($request->get('state_id')) {
                    $users = $users->WhereHas('city', function ($query) use ($request) {
                        $query->where('state_id', $request->get('state_id'));
                    });
                }


                if ($request->get('search')) {
                    if ((filter_var($request->get('search'),
                                FILTER_VALIDATE_INT) !== false) && User::find($request->get('search'))) {
                        $users = $users->where('id', $request->get('search'));

                    } else {
                        $users = $users->where('status', $request->get('search'))
                            ->orWhere('name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('email', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('type', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('onwer_name', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('address', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('user_name', 'like', '%' . $request->get('search') . '%');
                    }


                }

                $users = $users->orderBy('id', 'desc')->paginate($page);

                //  $collection = OfficesResource::collection($users);
                $information = [

                    'users' => $users,


                ];
                return response()->success(__('إحصائيات النظام'), $information);


            }
        }


        $users = DB::table('users')
            ->whereRaw('type = "provider"')
            ->orderBy('count_fund_offer', 'desc')
            ->orderBy('count_offer', 'desc')
            ->orderBy('count_estate', 'desc');
        // ->count();

        $users2 = DB::table('users')
            ->whereRaw('type = "user"');

        $fund_request_offer_deleted = DB::table('fund_request_offers')
            ->whereRaw('fund_request_offers.deleted_at is not null');;
        $fund_request_deleted = RequestFund::withTrashed()->whereRaw('deleted_at is not null');
        $estate = Estate::query();
        $request_offer = RequestOffer::whereHas('estate')
            ->whereHas('provider')
            ->whereHas('request')
            ->with('estate')
            ->with('provider');

        $fund_request_offer = FundRequestOffer::whereHas('provider')
            ->whereHas('estate')
            ->whereHas('fund_request');


        $app_request = EstateRequest::withCount('offers');
        $fund_request = DB::table('request_funds')
            ->whereRaw('request_funds.deleted_at is null');;
        $fund_review_request_offer = FundRequestOffer::whereHas('provider')
            ->whereHas('estate')
            ->whereHas('fund_request')
            ->whereRaw('fund_request_offers.status = "sending_code"');


        $unaccepted_estates = Estate::whereHas('user')->where('status', 'new');


        if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

            $today_date = date('Y-m-d');
            if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                $fund_request = $fund_request->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $users = $users->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $users2 = $users2->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $app_request = $app_request->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $fund_request_offer = $fund_request_offer->whereDate(
                    'fund_request_offers.created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $request_offer = $request_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $estate = $estate->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $fund_request_deleted = $fund_request_deleted->whereDate(
                    'deleted_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                    'deleted_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );


                $fund_review_request_offer = $fund_review_request_offer->whereDate(
                    'fund_request_offers.updated_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $unaccepted_estates = $unaccepted_estates->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );


            } else {
                if ($request->get('from_date')) {
                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d");

                    $fund_request = $fund_request->whereDate('created_at', '>', $date);
                    $app_request = $app_request->whereDate('created_at', '>', $date);
                    $fund_request_offer = $fund_request_offer->whereDate('fund_request_offers.created_at', '>', $date);
                    $request_offer = $request_offer->whereDate('request_offers.created_at', '>', $date);
                    $estate = $estate->whereDate('estates.created_at', '>', $date);
                    $fund_review_request_offer = $fund_review_request_offer->where('fund_request_offers.updated_at', '>', $date);


                    $fund_request_deleted = $fund_request_deleted->where('deleted_at', '>=', $date);
                    $fund_request_offer_deleted = $fund_request_offer_deleted->where('deleted_at', '>=', $date);
                    $users = $users->where('created_at', '>=', $date);
                    $users2 = $users2->where('created_at', '>=', $date);
                    $unaccepted_estates = $unaccepted_estates->where('created_at', '>=', $date);


                    //$fund_request_deleted_count= $fund_request_deleted_count->whereDate('deleted_at', '>', $date);


                }
                if ($request->get('to_date')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d");
                    $fund_request = $fund_request->whereDate('created_at', '<', $date);
                    $app_request = $app_request->whereDate('created_at', '<', $date);
                    $fund_request_offer = $fund_request_offer->whereDate('fund_request_offers.created_at', '<', $date);
                    $request_offer = $request_offer->whereDate('created_at', '<', $date);
                    $estate = $estate->whereDate('created_at', '<', $date);
                    //  $fund_review_request_offer = $fund_review_request_offer->where('updated_at', '<', $date);

                    $fund_review_request_offer = $fund_review_request_offer->where('fund_request_offers.updated_at', '<', $date);

                    /* $fund_request_count = $fund_request_count->whereDate('created_at', '<', $date);
                     $app_request_count = $app_request_count->whereDate('created_at', '<', $date);
                     $fund_request_offer_count = $fund_request_offer_count->whereDate('created_at', '<', $date);
                     $request_offer_count = $request_offer_count->whereDate('created_at', '<', $date);
                     $estate_count = $estate_count->whereDate('created_at', '<', $date);
                     $fund_review_request_offer_count = $fund_review_request_offer_count->whereDate('updated_at', '<', $date);
                    */

                    $fund_request_deleted = $fund_request_deleted->whereDate('deleted_at', '<', $date);
                    $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate('deleted_at', '<', $date);
                    // $fund_request_deleted_count= $fund_request_deleted_count->whereDate('deleted_at', '<', $date);
                    $users = $users->where('created_at', '<', $date);
                    $users2 = $users2->where('created_at', '<', $date);
                    $unaccepted_estates = $unaccepted_estates->where('created_at', '<', $date);

                }
            }
        }


        if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
            if ($request->get('time') == 'today') {


                $fund_request = $fund_request->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $users = $users->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $users2 = $users2->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $app_request = $app_request->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $fund_request_offer = $fund_request_offer->whereDate(
                    'fund_request_offers.created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $request_offer = $request_offer->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $estate = $estate->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

                $fund_request_deleted = $fund_request_deleted->whereDate(
                    'deleted_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                    'deleted_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );


                $fund_review_request_offer = $fund_review_request_offer->whereDate(
                    'fund_request_offers.updated_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
                $unaccepted_estates = $unaccepted_estates->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );


            } elseif ($request->get('time') == 'week') {

                $fund_request = $fund_request->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $fund_request = $fund_request->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $users = $users->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $users = $users->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $users2 = $users2->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $users2 = $users2->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $unaccepted_estates = $unaccepted_estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $unaccepted_estates = $unaccepted_estates->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $fund_request_deleted = $fund_request_deleted->whereDate(
                    'deleted_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $fund_request_deleted = $fund_request_deleted->whereDate(
                    'deleted_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );

                $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                    'fund_request_offers.deleted_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                    'fund_request_offers.deleted_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );


                $app_request = $app_request->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $app_request = $app_request->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
                $estate = $estate->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $estate = $estate->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $fund_review_request_offer = $fund_review_request_offer->whereDate(
                    'fund_request_offers.updated_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $fund_review_request_offer = $fund_review_request_offer->whereDate(
                    'fund_request_offers.updated_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
                $fund_request_offer = $fund_request_offer->whereDate(
                    'fund_request_offers.created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $fund_request_offer = $fund_request_offer->whereDate(
                    'fund_request_offers/created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            } elseif ($request->get('time') == 'month') {
                $fund_request_deleted = $fund_request_deleted->whereDate(
                    'deleted_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $fund_request_deleted = $fund_request_deleted->whereDate(
                    'deleted_at',
                    '<=',

                    Carbon::parse(date('Y-m-d'))
                );
                $users = $users->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $users = $users->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $users2 = $users2->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $users2 = $users2->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $unaccepted_estates = $unaccepted_estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(29)->format('Y-m-d')
                );
                $unaccepted_estates = $unaccepted_estates->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
                $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                    'fund_request_offers.deleted_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $fund_request_offer_deleted = $fund_request_offer_deleted->whereDate(
                    'fund_request_offers.deleted_at',
                    '<=',

                    Carbon::parse(date('Y-m-d'))
                );


                $fund_request_offer = $fund_request_offer->whereDate(
                    'fund_request_offers.created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $fund_request_offer = $fund_request_offer->whereDate(
                    'fund_request_offers.created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );

                $fund_request = $fund_request->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $fund_request = $fund_request->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
                $app_request = $app_request->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $app_request = $app_request->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $estate = $estate->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $estate = $estate->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


                $fund_review_request_offer = $fund_review_request_offer->whereDate(
                    'fund_request_offers.updated_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $fund_review_request_offer = $fund_review_request_offer->whereDate(
                    'fund_request_offers.updated_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
                $request_offer = $request_offer->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $request_offer = $request_offer->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }

        }


        $fund_request = $fund_request->count();
        $app_request = $app_request->count();
        $fund_request_offer = $fund_request_offer->count();
        $request_offer = $request_offer->count();
        $estate = $estate->count();
        $fund_review_request_offer = $fund_review_request_offer->count();
        $fund_request_deleted = $fund_request_deleted->count();
        $fund_request_offer_deleted = $fund_request_offer_deleted->count();
        $users = $users->count();
        $users2 = $users2->count();
        $unaccepted_estates = $unaccepted_estates->count();

        $information = [

            'fund_request' => $fund_request,
            'app_request' => $app_request,
            'fund_request_offer' => $fund_request_offer,
            'request_offer' => $request_offer,
            'estate' => $estate,
            'fund_review_request_offer' => $fund_review_request_offer,
            'fund_request_deleted' => $fund_request_deleted,
            'fund_request_offer_deleted' => $fund_request_offer_deleted,
            'offices' => $users,
            'users' => $users2,
            'unaccepted_estates' => $unaccepted_estates,


        ];

        return response()->success(__('إحصائيات النظام'), $information);

    }

    public function fund_request_show($id)
    {
        $fund_request = RequestFund::with('estate_type', 'city', 'area_estate', 'estate_price', 'offers', 'contact_stages',
            'preview_stages', 'finance_stages', 'street_view', 'neighborhood', 'assigned')->find($id);
        if ($fund_request) {
            return response()->success(__('تم العثور على الطلب'), $fund_request);
        } else {
            return response()->error(__('لم يتم العثور على الطلب'), 404);
        }

    }

    public function app_request_show($id)
    {
        $app_request = EstateRequest::withCount('offers')->with('offers', 'operation_type', 'estate_type', 'user', 'comforts')->find($id);
        if ($app_request) {
            return response()->success(__('تم العثور على الطلب'), $app_request);
        } else {
            return response()->error(__('لم يتم العثور على الطلب'), 404);
        }

    }


    public function offices(Request $request)
    {


        $users = User::where('type', 'provider')
            ->orderBy('count_fund_offer', 'desc')
            ->orderBy('count_offer', 'desc')
            ->orderBy('count_estate', 'desc')
            ->paginate();
        $estate_count = '';


        $fund_offer_count = '';


        $offer_count = '';

        foreach ($users as $userItem) {
            $estate_count = Estate::where('user_id', $userItem->id);
            $fund_offer_count = FundRequestOffer::where('provider_id', $userItem->id);
            $offer_count = RequestOffer::where('provider_id', $userItem->id);


            $estate_month_count = Estate::whereDate(
                'created_at',
                '>=',

                Carbon::now()->subDays(30)->format('Y-m-d')
            )->where('user_id', $userItem->id);
            $estate_month_count = $estate_month_count->whereDate(
                'created_at',
                '<=',
                Carbon::parse(date('Y-m-d'))
            )->count();


            $fund_offer_month_count = FundRequestOffer::whereDate(
                'created_at',
                '>=',

                Carbon::now()->subDays(30)->format('Y-m-d')
            )->where('provider_id', $userItem->id);
            $fund_offer_month_count = $fund_offer_month_count->whereDate(
                'created_at',
                '<=',
                Carbon::parse(date('Y-m-d'))
            )->count();


            $offer_month_count = RequestOffer::whereDate(
                'created_at',
                '>=',

                Carbon::now()->subDays(30)->format('Y-m-d')
            )->where('provider_id', $userItem->id);
            $offer_month_count = $offer_month_count->whereDate(
                'created_at',
                '<=',
                Carbon::parse(date('Y-m-d'))
            )->count();


            if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {


                if ($request->get('from_date')) {


                    $date = date_create($request->get('from_date'));
                    $date = date_format($date, "Y-m-d H:i:s");

                    $estate_count = $estate_count->whereDate('created_at', '>', $date);
                    $fund_offer_count = $fund_offer_count->whereDate('created_at', '>', $date);
                    $offer_count = $offer_count->whereDate('created_at', '>', $date);


                }
                if ($request->get('to_date')) {

                    $date = date_create($request->get('to_date'));
                    $date = date_format($date, "Y-m-d H:i:s");
                    $estate_count = $estate_count->whereDate('created_at', '<', $date);
                    $fund_offer_count = $fund_offer_count->whereDate('created_at', '<', $date);
                    $offer_count = $offer_count->whereDate('created_at', '<', $date);


                }


            } elseif (!$request->get('to_date') && !$request->get('from_date') && $request->get('time')) {
                if ($request->get('time') == 'today') {
                    $estate_count = $estate_count->where('user_id', $userItem->id)
                        ->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        )
                        ->count();

                    $fund_offer_count = $fund_offer_count->where('provider_id', $userItem->id)
                        ->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        )
                        ->count();
                    $offer_count = $offer_count->where('provider_id', $userItem->id)
                        ->whereDate(
                            'created_at',
                            '=',
                            Carbon::parse(date('Y-m-d'))
                        )
                        ->count();
                }
                if ($request->get('time') == 'week') {


                    $estate_count = $estate_count->whereDate(
                        'created_at',
                        '>=',

                        Carbon::now()->subDays(6)->format('Y-m-d')
                    )
                        ->where('user_id', $userItem->id);
                    $estate_count = $estate_count->whereDate(
                        'created_at',
                        '<=',
                        Carbon::parse(date('Y-m-d'))
                    )->count();


                    $fund_offer_count = $fund_offer_count->whereDate(
                        'created_at',
                        '>=',

                        Carbon::now()->subDays(6)->format('Y-m-d')
                    )
                        ->where('provider_id', $userItem->id);
                    $fund_offer_count = $fund_offer_count->whereDate(
                        'created_at',
                        '<=',
                        Carbon::parse(date('Y-m-d'))
                    )->count();


                    $offer_count = $offer_count->whereDate(
                        'created_at',
                        '>=',

                        Carbon::now()->subDays(6)->format('Y-m-d')
                    )->where('provider_id', $userItem->id);
                    $offer_count = $offer_count->whereDate(
                        'created_at',
                        '<=',
                        Carbon::parse(date('Y-m-d'))
                    )->count();
                }
                if ($request->get('time') == 'month') {

                    $estate_count = $estate_count->whereDate(
                        'created_at',
                        '>=',

                        Carbon::now()->subDays(29)->format('Y-m-d')
                    )
                        ->where('user_id', $userItem->id);
                    $estate_count = $estate_count->whereDate(
                        'created_at',
                        '<=',
                        Carbon::parse(date('Y-m-d'))
                    )->count();


                    $fund_offer_count = $fund_offer_count->whereDate(
                        'created_at',
                        '>=',

                        Carbon::now()->subDays(29)->format('Y-m-d')
                    )
                        ->where('provider_id', $userItem->id);
                    $fund_offer_count = $fund_offer_count->whereDate(
                        'created_at',
                        '<=',
                        Carbon::parse(date('Y-m-d'))
                    )->count();


                    $offer_count = $offer_count->whereDate(
                        'created_at',
                        '>=',

                        Carbon::now()->subDays(29)->format('Y-m-d')
                    )->where('provider_id', $userItem->id);
                    $offer_count = $offer_count->whereDate(
                        'created_at',
                        '<=',
                        Carbon::parse(date('Y-m-d'))
                    )->count();
                }
            }


            $userItem->dash_estate_count = $estate_count;
            $userItem->dash_fund_offer_count = $fund_offer_count;
            $userItem->dash_offer_count = $offer_count;


        }

        $collection = OfficesResource::collection($users);

        return response()->success(__('إحصائيات المكاتب'), $collection);
        //  dd($request->all());


    }

    public function UnacceptedEstates(Request $request)
    {


        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);


        $estates = Estate::with('EstateFile')->where('status', 'new');


        if ($request->get('status') && $request->get('status') != 'all') {

            $estates = $estates->where('status', $request->get('status'));
        }

        if ($request->get('time')) {


            if ($request->get('time') == 'today') {
                $estates = $estates->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }

            if ($request->get('time') == 'week') {
                $estates = $estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $estates = $estates->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'month') {
                $estates = $estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(29)->format('Y-m-d')
                );
                $estates = $estates->whereDate(
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


            $estates->where('estate_type_id', $request->get('estate_type_id'));


        }
        if ($request->get('area_estate_id')) {


            $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


            $estates->where('total_area', '>', $area_range->area_from)
                ->where('total_area', '<', $area_range->area_to);

        }
        if ($request->get('dir_estate_id')) {


            $array = ['north', 'south', 'east', 'west'];


            $estates->where('interface', $array[$request->get('dir_estate_id')]);
            //  ->where('total_area','<', $area_range->area_to);


        }
        if ($request->get('estate_price_id')) {

            $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


            $estates = $estates->where('total_price', '>', $price_range->estate_price_from)
                ->where('total_price', '<', $price_range->estate_price_to);

        }
        if ($request->get('state_id')) {
            $estates = $estates->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }


        if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


            $estates = $estates->where('neighborhood_id', $request->get('neighborhood_id'));


            //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
        }
        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                  $q->whereDate('created_at', '>', $date);
                  //  ->where('total_area','<', $area_range->area_to);
              });*/

            $estates = $estates->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {
            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                   $q->whereDate('created_at', '<', $date);
                   //  ->where('total_area','<', $area_range->area_to);
               });*/
            $estates = $estates->whereDate('created_at', '<', $date);

        }


        if ($request->get('search')) {
            $estates = $estates->where('status', $request->get('search'))
                ->orwhere('operation_type_name', $request->get('search'))
                ->orwhere('estate_type_name', $request->get('search'))
                ->orwhere('full_address', $request->get('search'))
                ->orwhere('interface', $request->get('search'));

        }


        $estates = $estates->orderBy('id', 'desc')->paginate($page);

        return response()->success("Un Accepted Estates", $estates);


        // $collection = RequestFundOfferResource::collection($finiceing);


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

    public function cities(Request $request)
    {
        if ($request->type == 'new') {
            $cities = Cities::query();

            if ($request->get('state_id')) {
                $cities = $cities->where('region_id', $request->get('state_id'));
            }
            $cities = $cities->get();

        } else {
            $cities = City::query();

            if ($request->get('state_id')) {
                $cities = $cities->where('state_id', $request->get('state_id'));
            }
            $cities = $cities->get();
        }


        return response()->success("cities", $cities);

    }

    public function neighborhood($id, Request $request)
    {
        if ($request->type == 'new') {

            $cities = \App\Models\v4\District::where('city_id', $id)->get();
            return response()->success("cities", DistrictResource::collection($cities));

        } else {
            $cities = Neighborhood::where('city_id', $id)->get();
            return response()->success("cities", $cities);

        }


    }

    public function state(Request $request)
    {

        if ($request->type == 'new') {
            $region = \App\Models\v4\Region::all();
            $array = [];
            foreach ($region as $item) {
                $array[] = ['id' => $item->id, 'value' => $item->name];
            }
        } else {
            $regine = [
                '1' => 'الرياض',
                '2' => 'مكه المكرمة',
                '3' => 'جازان',
                '4' => 'الشرقية',
                '5' => 'عسير',
                '6' => 'القصيم',
                '7' => 'حائل',
                '8' => 'المدينة المنورة',
                '9' => 'الباحة',
                '10' => 'الحدود الشمالية',
                '11' => 'تبوك',
                '12' => 'نجران',
                '13' => 'الجوف',
            ];
            $array = [];


            for ($i = 0; $i < count($regine); $i++) {


                $array[$i] = ['id' => $i + 1, 'value' => $regine[$i + 1]];
            }
        }

        return response()->success("regine", $array);

    }


    public function getDashMap()
    {

        $regine = [
            '1' => 'الرياض',
            '2' => 'مكه المكرمة',
            '3' => 'جازان',
            '4' => 'الشرقية',
            '5' => 'عسير',
            '6' => 'القصيم',
            '7' => 'حائل',
            '8' => 'المدينة المنورة',
            '9' => 'الباحة',
            '10' => 'الحدود الشمالية',
            '11' => 'تبوك',
            '12' => 'نجران',
            '13' => 'الجوف',
        ];

        $array = [];

        for ($i = 0; $i < count($regine); $i++) {


            $requests = RequestFund::WhereHas('city', function ($query) use ($i) {
                $query->where('state_id', $i + 1);

            });


            $providers = User::where('type', 'provider')->WhereHas('city', function ($query) use ($i) {
                $query->where('state_id', $i + 1);

            });


            $offers = FundRequestOffer::WhereHas('estate.city', function ($query) use ($i) {
                $query->where('state_id', $i + 1);

            });
            $array[$regine[$i + 1]] = ['requests' => $requests->count(), 'offer' => $offers->count(), 'providers' => $providers->count()];
        }


        //  return response()->json($articles);

        return response()->success("places", $array);
    }

    public function getDashYear(Request $request)
    {

        $regine = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',

        ];

        $array = [];

        for ($i = 0; $i < count($regine); $i++) {
            $requests = \DB::table('request_funds')
                ->whereMonth('created_at', $regine[$i])
                ->whereYear('created_at', '2021')
                ->count();

            $offers = \DB::table('fund_request_offers')
                ->whereMonth('created_at', $regine[$i])
                ->whereYear('created_at', $request->get('year'))
                ->count();


            $providers = User::where('type', 'provider')
                ->whereMonth('created_at', $regine[$i])
                ->whereYear('created_at', $request->get('year'))
                ->count();

            $monthNum = $i + 1;
            $dateObj = DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj->format('F');
            $array[$monthName] = ['requests' => $requests, 'offers' => $offers, 'providers' => $providers];
        }

        /*  $array = \Cache::remember('dash_year', 22*60, function() use ($array) {
              return $array;
          });*/
        return response()->success("Get Dash Year", $array);
    }


    public function getMeanSideBar(Request $request)
    {

        /*     sub: [
           { type: "link", path: '/requests', name: 'جميع الطلبات', value: 0 },
           { type: "link", path: '/requests?status=have_offers', name: 'طلبات استقبلت عروض', value: 0 },
           { type: "link", path: '/requests?status=have_active_offers', name: 'طلبات المعاينة', value: 0 },
           { type: "link", path: '/requests?status=dont_have_active_offers', name: 'طلبات بدون عروض', value: 0 },
           { type: "link", path: '/requests?status=complete', name: 'طلبات منفذة', value: 0 },
           { type: "link", path: '/requests?status=rejected_customer', name: 'طلبات مرفوضة', value: 0 },
     */
        $sub = [
            ['name' => __('views.all_requests'), 'path' => '/requests', 'value' => $requests = RequestFund::count()],
            ['name' => __('views.all_requests_have_offer'), 'path' => '/requests?status=have_offers', 'value' => RequestFund::whereHas('offers')
                ->whereHas('offers.provider')
                ->whereHas('offers.estate')
                ->count()],
            ['name' => __('views.all_requests_has_active_offer'), 'path' => 'requests?status=have_active_offers', 'value' => RequestFund::whereHas('offers')
                ->whereHas('offers.provider')
                ->whereHas('offers.estate')
                ->where('status', 'sending_code')
                ->count()],
            ['name' => __('views.all_requests_has_not_offer'), 'path' => 'requests?status=have_active_offers', 'value' => RequestFund::doesntHave('offers')
                ->count()],
            ['name' => __('views.all_requests_complete'), 'path' => 'requests?status=have_active_offers', 'value' => $requests = RequestFund::whereHas('offers')
                ->whereHas('offers.provider')
                ->whereHas('offers.estate')
                ->where('status', 'customer_accepted')
                ->count()],

            ['name' => __('views.all_requests_complete'), 'path' => 'requests?status=have_active_offers', 'value' => $requests = RequestFund::whereHas('offers')
                ->whereHas('offers.provider')
                ->whereHas('offers.estate')
                ->where('status', 'rejected_customer')
                ->count()],


            // __('views.all_requests_have_offer'),
            //  __('views.all_requests_has_active_offer'),
            //  __('views.all_requests_has_not_offer'),
            //       __('views.all_requests_complete'),
            //     __('views.all_requests_reject'),


        ];


        /*  $array = \Cache::remember('dash_year', 22*60, function() use ($array) {
              return $array;
          });*/
        return response()->success("Sub Side Request Bar", $sub);
    }

    public function getMeanSideBarUser(Request $request)
    {

        /*   sub: [
      { type: "link", path: '/fund/providers', name: 'جميع العقاريين', value: 0 },
      { type: "link", path: '/fund/providers?status=have_active', name: 'مفعل', value: 0 },
      { type: "link", path: '/fund/providers?status=waite_active', name: 'في انتظار التفعيل ', value: 0 },
      { type: "link", path: '/fund/providers?status=best_providers', name: 'الأكثر نشاطاً', value: 0 },
    ]
     */
        $user_payment = UserPayment::where('status', '0')->pluck('user_id');

        $userWatie = User::where('type', 'provider')->whereIn('id', $user_payment->toArray());

        $sub = [
            ['name' => __('views.all_providers'), 'path' => '/fund/providers', 'value' => User::where('type', 'provider')->count()],
            ['name' => __('views.all_active_providers'), 'path' => '/fund/providers?status=have_active', 'value' =>
                User::where('type', 'provider')
                    ->where('is_pay', '1')
                    ->count()],
            ['name' => __('views.all_waite_active_providers'), 'path' => '/fund/providers?status=waite_active', 'value' =>
                $userWatie],


            ['name' => __('views.best_providers'), 'path' => '//fund/providers?status=best_providers', 'value' =>
                User::where('type', 'provider')
                    ->where('is_pay', '1')
                    ->orderBy('count_request', 'desc')->count()],


        ];


        /*  $array = \Cache::remember('dash_year', 22*60, function() use ($array) {
              return $array;
          });*/
        return response()->success("Sub Side Users Bar", $sub);
    }

    public function member_type(Request $request)
    {

        $member = MemberType::get();

        return response()->success("Member Types", $member);

    }

    public function experiences_type(Request $request)
    {

        $experiences = ExperienceType::where('status', '1')->get();

        return response()->success("experiences Types", $experiences);

    }

    public function course_type(Request $request)
    {

        $course = CourseType::where('status', '1')->get();

        return response()->success("course Types", $course);

    }


    public function plans(Request $request)
    {

        $plans = Plan::where('status', 1)
            ->where('id', '!=', 4)
            ->get();

        return response()->success("Plans", $plans);

    }

    public function showPlan($id)
    {

        $plans = Plan::find($id);


        return response()->success("Plan", $plans);

    }


    public function service_type(Request $request)
    {

        $service = ServiceType::get();

        return response()->success("Service Types", $service);

    }


    public function send_push(Request $request)
    {

        $rules = Validator::make($request->all(), [
            'type' => 'required',
            'title' => 'required',
            'body' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        send_push_to_topic('aqarz', $request->get('title'), $request->get('body'), '0');


        return response()->success("Notification sent");

    }


    public function reports(Request $request)
    {


        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);


        $report = Report::with('estate')->with('user');


        $report = $report->orderBy('id', 'desc')->paginate($page);

        return response()->success("Reports", $report);


        // $collection = RequestFundOfferResource::collection($finiceing);


    }


    public function contacts(Request $request)
    {


        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);


        $contact = Contact::query();


        $contact = $contact->orderBy('id', 'desc')->paginate($page);

        return response()->success("Contact", $contact);


        // $collection = RequestFundOfferResource::collection($finiceing);


    }


    public function response_contact(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'email' => 'required',
            'title' => 'required',
            'body' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $data = ['name' => "ProviderAPI"];
        Mail::send([], $data, function ($message) use ($request) {
            $message->to($request->email, 'Forgot Password')
                ->subject($request->get('title'))
                ->from('info@aqarzapp.com', 'Aqarz')
                ->setBody("<p>" . $request->get('body') . "</p>", 'text/html');
        });
    }


    public function ClosedEstate(Request $request)
    {


        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);


        $estates = Estate::where('status', 'closed');


        if ($request->get('status') && $request->get('status') != 'all') {

            $estates = $estates->where('status', $request->get('status'));
        }

        if ($request->get('time')) {


            if ($request->get('time') == 'today') {
                $estates = $estates->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }

            if ($request->get('time') == 'week') {
                $estates = $estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $estates = $estates->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'month') {
                $estates = $estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(29)->format('Y-m-d')
                );
                $estates = $estates->whereDate(
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


            $estates->where('estate_type_id', $request->get('estate_type_id'));


        }
        if ($request->get('area_estate_id')) {


            $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


            $estates->where('total_area', '>', $area_range->area_from)
                ->where('total_area', '<', $area_range->area_to);

        }
        if ($request->get('dir_estate_id')) {


            $array = ['north', 'south', 'east', 'west'];


            $estates->where('interface', $array[$request->get('dir_estate_id')]);
            //  ->where('total_area','<', $area_range->area_to);


        }
        if ($request->get('estate_price_id')) {

            $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


            $estates = $estates->where('total_price', '>', $price_range->estate_price_from)
                ->where('total_price', '<', $price_range->estate_price_to);

        }


        if ($request->get('state_id')) {
            $estates = $estates->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }


        if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


            $estates = $estates->where('neighborhood_id', $request->get('neighborhood_id'));


            //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
        }
        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                  $q->whereDate('created_at', '>', $date);
                  //  ->where('total_area','<', $area_range->area_to);
              });*/

            $estates = $estates->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {
            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                   $q->whereDate('created_at', '<', $date);
                   //  ->where('total_area','<', $area_range->area_to);
               });*/
            $estates = $estates->whereDate('created_at', '<', $date);

        }


        if ($request->get('search')) {
            $estates = $estates->where('status', $request->get('search'))
                ->orwhere('operation_type_name', $request->get('search'))
                ->orwhere('estate_type_name', $request->get('search'))
                ->orwhere('full_address', $request->get('search'))
                ->orwhere('interface', $request->get('search'));

        }


        $estates = $estates->orderBy('id', 'desc')->paginate($page);

        return response()->success("Closed Estates", $estates);


        // $collection = RequestFundOfferResource::collection($finiceing);


    }

    public function DeletedEstate(Request $request)
    {


        $page = $request->get('page_number', 10);
        //  dd($request->get('query')['neighborhood_id']);


        $estates = Estate::withTrashed()->where('deleted_at', '!=', null);


        if ($request->get('status') && $request->get('status') != 'all') {

            $estates = $estates->where('status', $request->get('status'));
        }

        if ($request->get('time')) {


            if ($request->get('time') == 'today') {
                $estates = $estates->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }

            if ($request->get('time') == 'week') {
                $estates = $estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $estates = $estates->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'month') {
                $estates = $estates->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(29)->format('Y-m-d')
                );
                $estates = $estates->whereDate(
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


            $estates->where('estate_type_id', $request->get('estate_type_id'));


        }
        if ($request->get('area_estate_id')) {


            $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));


            $estates->where('total_area', '>', $area_range->area_from)
                ->where('total_area', '<', $area_range->area_to);

        }
        if ($request->get('dir_estate_id')) {


            $array = ['north', 'south', 'east', 'west'];


            $estates->where('interface', $array[$request->get('dir_estate_id')]);
            //  ->where('total_area','<', $area_range->area_to);


        }
        if ($request->get('estate_price_id')) {

            $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


            $estates = $estates->where('total_price', '>', $price_range->estate_price_from)
                ->where('total_price', '<', $price_range->estate_price_to);

        }


        if ($request->get('state_id')) {
            $estates = $estates->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }


        if (isset($request->neighborhood_id) && count($request->neighborhood_id) > 0 && $request->neighborhood_id[0] != null) {


            $estates = $estates->where('neighborhood_id', $request->get('neighborhood_id'));


            //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
        }
        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*  $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                  $q->whereDate('created_at', '>', $date);
                  //  ->where('total_area','<', $area_range->area_to);
              });*/

            $estates = $estates->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {
            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            /*   $finiceing = $finiceing->whereHas('fund_request', function ($q) use ($request, $date) {


                   $q->whereDate('created_at', '<', $date);
                   //  ->where('total_area','<', $area_range->area_to);
               });*/
            $estates = $estates->whereDate('created_at', '<', $date);

        }


        if ($request->get('search')) {
            $estates = $estates->where('status', $request->get('search'))
                ->orwhere('operation_type_name', $request->get('search'))
                ->orwhere('estate_type_name', $request->get('search'))
                ->orwhere('full_address', $request->get('search'))
                ->orwhere('interface', $request->get('search'));

        }


        $estates = $estates->orderBy('id', 'desc')->paginate($page);

        return response()->success("Closed Estates", $estates);


        // $collection = RequestFundOfferResource::collection($finiceing);


    }


    public function addEstate(Request $request)
    {

        // return response()->success(__("views.Estate"), []);

        // dd($request->all()); ///ارجاع الداتا المرسلة
        // return true;
        // return response()->success(__("views.Estate"), []);
        //  dd($request->all());

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [


            'operation_type_id' => 'required',


            'estate_type_id' => 'required|exists:estate_types,id',
            //  'state_id' => 'required|exists:regions,id',
            // 'city_id' => 'required|exists:cities,serial_city',
            //  'neighborhood_id' => 'required|exists:neighborhoods,neighborhood_serial',
            'lat' => 'sometimes|required',
            'lan' => 'sometimes|required',

            'estate_use_type' => 'required',
            'total_area' => 'required',
            //'estate_dimensions' => 'required',
            //  'interface' => 'required',
            'is_mortgage' => 'required',
            'is_obligations' => 'required',
            'is_saudi_building_code' => 'required',
            'advertiser_side' => 'required',
            'advertiser_character' => 'required',
            'owner_name' => 'sometimes|required',
            'owner_mobile' => 'sometimes|required',


            /*'photo' => 'required|array',
            'photo.*' => 'required|image|mimes:jpg,jpeg,png',
*/

        ]);


        //dd($request->all());
        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }
        //  checkPint("26.226648900000000000 50.203490600000000000");
        $cit = \App\Models\v3\EstateType::where('id', $request->get('estate_type_id'))->first();
        $cit2 = \App\Models\v3\OprationType::where('id', $request->get('operation_type_id'))->first();


        if ($request->get('lat') && $request->get('lan')) {
            // City3 District  Region
            $location = $request->get('lat') . ' ' . $request->get('lan');
            $dis = checkPint("$location");


            if ($dis != null) {
                $city = City3::where('city_id', $dis->city_id)->first();
                $Neighborhood = District::where('district_id', $dis->district_id)->first();

                $cityFund = City::Where('name_ar', 'like', '%' . $city->name_ar . '%')->first();
                $NeighborhoodFund = Neighborhood::Where('name_ar', 'like', '%' . $Neighborhood->name_ar . '%')->first();
                $Region = Region::where('id', $cityFund->state_id)->first();


                $neb_name = $Neighborhood ? $Neighborhood->name_ar : '--';
                $city_name = $city ? $city->name_ar : '--';
                $Region_name = $Region ? $Region->name_ar : '--';
                $full_address = $neb_name . ',' . $city_name . ',' . $Region_name;

                $request->merge([

                    'full_address' => $full_address,
                ]);

                if ($cityFund) {
                    $request->merge([

                        'city_id' => $cityFund->serial_city,


                    ]);
                }

                if ($NeighborhoodFund) {
                    $request->merge([


                        'neighborhood_id' => $NeighborhoodFund->neighborhood_serial,

                    ]);
                }


            } else {
                $request->merge([

                    'full_address' => $request->get('full_address'),
                ]);
            }
        }


        $request->merge([

            'user_id' => $user->id,
            'estate_age' => $age,
            'in_fund_offer' => 1,
            'operation_type_name' => $cit2->name_ar,
            'estate_type_name' => $cit->name_ar,
            //   'full_address' => $Neighborhood->name_ar.','.$city->name_ar.','.$Region->name_ar,
        ]);


        /*  if (isset(Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'])&& Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
              return response()->error(__("views.the location must be inside Saudi Arabia"));
          }*/
        // return response()->error($request->get('interface'));

        if ($request->get('interface')) {
            if (is_array($request->get('interface'))) {
                $interface = implode(',', $request->get('interface'));

                $request->merge([

                    'interface' => $interface,
                ]);
            }
        }
        $estate = Estate::create($request->only([
            'elevators_number',
            'parking_spaces_numbers',
            'unit_number',
            'is_disputes',

            'operation_type_id',
            'is_mortgage',
            'is_obligations',
            'touching_information',
            'is_saudi_building_code',
            'advertiser_side',
            'advertiser_character',
            'estate_use_type',
            'estate_type_id',
            'instrument_number',
            'pace_number',
            'planned_number',
            'total_area',
            'estate_age',
            'floor_number',
            'street_view',
            'total_price',
            'meter_price',
            'owner_name',
            'owner_mobile',
            'lounges_number',
            'rooms_number',
            'bathrooms_number',
            'boards_number',
            'kitchen_number',
            'dining_rooms_number',

            'finishing_type',
            'interface',
            'social_status',


            'lat',
            'lan',
            'note',
            'status',
            'user_id',
            'is_rent',
            'rent_type',
            'is_resident',
            'is_checked',
            'is_insured',
            'neighborhood_id',
            'city_id',
            'state_id',
            'address',
            'in_fund_offer',
            'estate_dimensions',
            'obligations_information',
            'bedroom_number',
            'rooms_number',
            'rent_price',
            'operation_type_name',
            'estate_type_name',
            'first_image',
            'full_address',
            'is_rent_installment',
            'rent_installment_price',
            'unit_counter',
            'advertiser_license_number',
            'advertiser_email'

        ]));
        //  return response()->error(__("views.the location must be inside Saudi Arabia"));


        $estate = Estate::find($estate->id);

        /*
                if ($request->hasFile('video') && $request->File('video') != null) {


                    $extension = $request->file('video')->getClientOriginalExtension();
                    $array = $this->video_extensions();

                    if (in_array($extension, $array)) {
                        $path = $request->file('video')->store('video', 's3');

                        if ($path != null && $path != false) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                        }
                    }


                }
        */
        if ($request->hasFile('instrument_file')) {


            $path = $request->file('instrument_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        if ($request->hasFile('exclusive_contract_file')) {


            $path = $request->file('exclusive_contract_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;

        }


        if (($request->hasFile('attachment_planned'))) {
//////

            $xy = $request->file('attachment_planned');
            $first_image = '';
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');


                $atta = new AttachmentPlanned();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $atta->save();


            }


        }


        /*  if (($request->get('attachment_estate'))) {
  //////
              $arrayImg = explode(',', $request->get('attachment_estate'));
              $attachment = AttachmentEstate::whereIn('id', $arrayImg)->update(['estate_id' => $estate->id]);


          }*/

        if ($request->get('estate_comforts')) {


            $comforts = explode(',', $request->get('estate_comforts'));

            for ($i = 0; $i < count($comforts); $i++) {
                $comfort = ComfortEstate::create([
                    'estate_id' => $estate->id,
                    'comfort_id' => $comforts[$i],

                ]);
            }

        }
        if (($request->hasFile('photo'))) {
//////

            $xy = $request->file('photo');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');

                $atta = AttachmentEstate::create([
                    'estate_id' => $estate->id,
                    'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                ]);
                //    $atta = new AttachmentEstate();
                //   $atta->estate_id = $estate->id;
                //    $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                //   $atta->save();
                if ($i == 0) {
                    $estate->first_image = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                    $estate->save();
                }


            }


        }


        if (($request->hasFile('video'))) {
//////

            $xy = $request->file('video');
            foreach ($xy as $i => $value) {
                $extension = $value->getClientOriginalExtension();
                $array = $this->video_extensions();
                if (in_array($extension, $array)) {
                    $path = $value->store('videos', 's3');

                    if ($path != null && $path != false) {


                        $atta = AttachmentEstate::create([
                            'estate_id' => $estate->id,
                            'type' => 'videos',
                            'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                        ]);

                        if ($i == 0) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                            $estate->save();
                        }
                    }
                }


            }


        }

        $estate->save();
        $user->count_estate = $user->count_estate + 1;
        // $user->count_request =  $user->count_request+1;
        $user->save();
        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);

        $city = City::where('serial_city', $request->get('city_id'))->first();
        if ($city) {
            $city->count_app_estate = $city->count_app_estate + 1;
            $city->save();
        }

        $neb = Neighborhood::where('neighborhood_serial', $request->get('neighborhood_id'))->first();
        if ($neb) {
            $neb->estate_counter = $neb->estate_counter + 1;
            $neb->save();
        }

        $price = explode(',', $estate->total_price);
        $area = explode(',', $estate->total_area);
        $full_number = '';

        $full_area = '';
        for ($i = 0; $i < count($price); $i++) {
            $full_number .= $price[$i];
        }
        for ($i = 0; $i < count($area); $i++) {
            $full_area .= $area[$i];
        }

        $estate->total_price_number = $full_number;
        $estate->total_area_number = $full_area;
        $estate->save();

        return response()->success(__("views.Estate"), $estate);
        // return ['data' => $user];
    }

    public function uploadimg(Request $request)
    {


        if ($request->hasFile('image')) {


            $path = $request->file('image')->store('images', 's3');
            return 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path; // في حالة انه تم ارسال الصورة راح يرفعها وترجع
        }
        dd($request->all()); ///ارجاع الداتا المرسلة
        // return true;
        return response()->success(__("views.Estate"), []);
        //  dd($request->all());

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [


            'operation_type_id' => 'required',


            'estate_type_id' => 'required|exists:estate_types,id',
            //  'state_id' => 'required|exists:regions,id',
            // 'city_id' => 'required|exists:cities,serial_city',
            //  'neighborhood_id' => 'required|exists:neighborhoods,neighborhood_serial',
            'lat' => 'sometimes|required',
            'lan' => 'sometimes|required',

            'estate_use_type' => 'required',
            'total_area' => 'required',
            //'estate_dimensions' => 'required',
            //  'interface' => 'required',
            'is_mortgage' => 'required',
            'is_obligations' => 'required',
            'is_saudi_building_code' => 'required',
            'advertiser_side' => 'required',
            'advertiser_character' => 'required',
            'owner_name' => 'sometimes|required',
            'owner_mobile' => 'sometimes|required',


            'photo' => 'required|array',
            'photo.*' => 'required|image|mimes:jpg,jpeg,png',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }
        //  checkPint("26.226648900000000000 50.203490600000000000");
        $cit = \App\Models\v3\EstateType::where('id', $request->get('estate_type_id'))->first();
        $cit2 = \App\Models\v3\OprationType::where('id', $request->get('operation_type_id'))->first();


        if ($request->get('lat') && $request->get('lan')) {
            // City3 District  Region
            $location = $request->get('lat') . ' ' . $request->get('lan');
            $dis = checkPint("$location");


            if ($dis != null) {
                $city = City3::where('city_id', $dis->city_id)->first();
                $Neighborhood = District::where('district_id', $dis->district_id)->first();

                $cityFund = City::Where('name_ar', 'like', '%' . $city->name_ar . '%')->first();
                $NeighborhoodFund = Neighborhood::Where('name_ar', 'like', '%' . $Neighborhood->name_ar . '%')->first();
                $Region = Region::where('id', $cityFund->state_id)->first();


                $neb_name = $Neighborhood ? $Neighborhood->name_ar : '--';
                $city_name = $city ? $city->name_ar : '--';
                $Region_name = $Region ? $Region->name_ar : '--';
                $full_address = $neb_name . ',' . $city_name . ',' . $Region_name;

                $request->merge([

                    'full_address' => $full_address,
                ]);

                if ($cityFund) {
                    $request->merge([

                        'city_id' => $cityFund->serial_city,


                    ]);
                }

                if ($NeighborhoodFund) {
                    $request->merge([


                        'neighborhood_id' => $NeighborhoodFund->neighborhood_serial,

                    ]);
                }


            } else {
                $request->merge([

                    'full_address' => $request->get('full_address'),
                ]);
            }
        }


        $request->merge([

            'user_id' => $user->id,
            'estate_age' => $age,
            'in_fund_offer' => 1,
            'operation_type_name' => $cit2->name_ar,
            'estate_type_name' => $cit->name_ar,
            //   'full_address' => $Neighborhood->name_ar.','.$city->name_ar.','.$Region->name_ar,
        ]);


        /*  if (isset(Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'])&& Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
              return response()->error(__("views.the location must be inside Saudi Arabia"));
          }*/

        $estate = Estate::create($request->only([
            'elevators_number',
            'parking_spaces_numbers',
            'unit_number',
            'is_disputes',

            'operation_type_id',
            'is_mortgage',
            'is_obligations',
            'touching_information',
            'is_saudi_building_code',
            'advertiser_side',
            'advertiser_character',
            'estate_use_type',
            'estate_type_id',
            'instrument_number',
            'pace_number',
            'planned_number',
            'total_area',
            'estate_age',
            'floor_number',
            'street_view',
            'total_price',
            'meter_price',
            'owner_name',
            'owner_mobile',
            'lounges_number',
            'rooms_number',
            'bathrooms_number',
            'boards_number',
            'kitchen_number',
            'dining_rooms_number',
            'finishing_type',
            'interface',
            'social_status',
            'lat',
            'lan',
            'note',
            'status',
            'user_id',
            'is_rent',
            'rent_type',
            'is_resident',
            'is_checked',
            'is_insured',
            'neighborhood_id',
            'city_id',
            'state_id',
            'address',
            'in_fund_offer',
            'estate_dimensions',
            'obligations_information',
            'bedroom_number',
            'rooms_number',
            'rent_price',
            'operation_type_name',
            'estate_type_name',
            'first_image',
            'full_address',
            'is_rent_installment',
            'rent_installment_price',
            'unit_counter',
            'advertiser_license_number',
            'advertiser_email'

        ]));


        $estate = Estate::find($estate->id);

        /*
                if ($request->hasFile('video') && $request->File('video') != null) {


                    $extension = $request->file('video')->getClientOriginalExtension();
                    $array = $this->video_extensions();

                    if (in_array($extension, $array)) {
                        $path = $request->file('video')->store('video', 's3');

                        if ($path != null && $path != false) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                        }
                    }


                }
        */
        if ($request->hasFile('instrument_file')) {


            $path = $request->file('instrument_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        if ($request->hasFile('exclusive_contract_file')) {


            $path = $request->file('exclusive_contract_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;

        }


        if (($request->hasFile('attachment_planned'))) {
//////

            $xy = $request->file('attachment_planned');
            $first_image = '';
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');


                $atta = new AttachmentPlanned();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $atta->save();


            }


        }


        /*  if (($request->get('attachment_estate'))) {
  //////
              $arrayImg = explode(',', $request->get('attachment_estate'));
              $attachment = AttachmentEstate::whereIn('id', $arrayImg)->update(['estate_id' => $estate->id]);


          }*/

        if ($request->get('estate_comforts')) {


            $comforts = explode(',', $request->get('estate_comforts'));

            for ($i = 0; $i < count($comforts); $i++) {
                $comfort = ComfortEstate::create([
                    'estate_id' => $estate->id,
                    'comfort_id' => $comforts[$i],

                ]);
            }

        }
        if (($request->hasFile('photo'))) {
//////

            $xy = $request->file('photo');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');

                $atta = AttachmentEstate::create([
                    'estate_id' => $estate->id,
                    'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                ]);
                //    $atta = new AttachmentEstate();
                //   $atta->estate_id = $estate->id;
                //    $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                //   $atta->save();
                if ($i == 0) {
                    $estate->first_image = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                    $estate->save();
                }


            }


        }


        if (($request->hasFile('video'))) {
//////

            $xy = $request->file('video');
            foreach ($xy as $i => $value) {
                $extension = $value->getClientOriginalExtension();
                $array = $this->video_extensions();
                if (in_array($extension, $array)) {
                    $path = $value->store('videos', 's3');

                    if ($path != null && $path != false) {


                        $atta = AttachmentEstate::create([
                            'estate_id' => $estate->id,
                            'type' => 'videos',
                            'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                        ]);

                        if ($i == 0) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                            $estate->save();
                        }
                    }
                }


            }


        }

        $estate->save();
        $user->count_estate = $user->count_estate + 1;
        // $user->count_request =  $user->count_request+1;
        $user->save();
        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);

        $city = City::where('serial_city', $request->get('city_id'))->first();
        if ($city) {
            $city->count_app_estate = $city->count_app_estate + 1;
            $city->save();
        }

        $neb = Neighborhood::where('neighborhood_serial', $request->get('neighborhood_id'))->first();
        if ($neb) {
            $neb->estate_counter = $neb->estate_counter + 1;
            $neb->save();
        }

        $price = explode(',', $estate->total_price);
        $area = explode(',', $estate->total_area);
        $full_number = '';

        $full_area = '';
        for ($i = 0; $i < count($price); $i++) {
            $full_number .= $price[$i];
        }
        for ($i = 0; $i < count($area); $i++) {
            $full_area .= $area[$i];
        }

        $estate->total_price_number = $full_number;
        $estate->total_area_number = $full_area;
        $estate->save();

        return response()->success(__("views.Estate"), $estate);
        // return ['data' => $user];
    }

    public function deleteEstate(Request $request)
    {


        //  dd($request->all());

        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [


            'estate_id' => 'required|exists:estates,id',
            'reason' => 'required',


        ]);
//whereDoesntHave
        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $estate = Estate::find($request->get('estate_id'));

        //dd($estate,$request->get('estate_id'));

        if (!$estate) {
            return response()->error(__("views.not found"));

        }
        //  $checkEstateRequest = RequestOffer::where('estate_id', $estate->id)->first();

        //  $checkEstateFundRequest = FundRequestOffer::where('estate_id', $estate->id)->first();

        /*  if ($checkEstateRequest || $checkEstateFundRequest) {
              return response()->error(__("views.cant delete estate is have offer"));
          }*/
        $city = City::where('serial_city', $estate->city_id)->first();
        if ($city) {
            $city->count_app_estate = $city->count_app_estate - 1;
            $city->save();
        }
        $neb = Neighborhood::where('neighborhood_serial', $estate->neighborhood_id)->first();
        if ($neb) {
            $neb->estate_counter = $neb->estate_counter - 1;
            $neb->save();
        }
        $estate->reason = $request->get('reason');
        $userOwner = User::find($estate->user_id);
        if ($userOwner) {
            $userOwner->count_estate = $userOwner->count_estate - 1;
            $userOwner->save();
        }

        $estate->save();
        $estate->delete();

        $data = \App\Models\v3\RequestOffer::where('estate_id', $request->get('estate_id'))->get();
        foreach ($data as $dataItem) {
            $dataItem->delete();
        }

        $request_review = EstateRequestPreview::where('estate_id', $request->get('estate_id'))->get();
        foreach ($request_review as $request_reviewItem) {
            $request_reviewItem->delete();
        }


        return response()->success(__("views.Estate Deleted"), []);
        // return ['data' => $user];
    }

    public function updateEstate(Request $request, $id)
    {

        $rules = Validator::make($request->all(), [


        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }

        $cit = null;
        $cit2 = null;
        if ($request->get('estate_type_id')) {
            $cit = \App\Models\v3\EstateType::where('id', $request->get('estate_type_id'))->first();
        }
        if ($request->get('operation_type_id')) {
            $cit2 = \App\Models\v3\OprationType::where('id', $request->get('operation_type_id'))->first();
        }


        if ($cit != null) {
            $request->merge([
                'estate_type_name' => $cit->name_ar
            ]);
        }
        if ($cit2 != null) {
            $request->merge([
                'operation_type_name' => $cit2->name_ar,
            ]);
        }

        if ($request->get('lat') && $request->get('lan')) {
            // City3 District  Region
            $location = $request->get('lat') . ' ' . $request->get('lan');
            $dis = checkPint("$location");
            if ($dis != null) {
                $dis = District::find($dis);
                $city = City3::where('city_id', $dis->city_id)->first();
                //  $city = City3::where('city_id', $dis->city_id)->first();
                $Neighborhood = District::where('district_id', $dis->district_id)->first();
                $cityFund = City::Where('name_ar', 'like', '%' . $city->name_ar . '%')->first();
                $NeighborhoodFund = Neighborhood::Where('search_name', 'like', '%' . $Neighborhood->name_ar . '%')->Where('search_name', 'like', '%' . $city->name_ar . '%')->first();

                $Region = Region::where('id', $cityFund->state_id)->first();

                $neb_name = $Neighborhood ? $Neighborhood->name_ar : '--';
                $city_name = $city ? $city->name_ar : '--';
                $Region_name = $Region ? $Region->name_ar : '--';
                $full_address = $neb_name . ',' . $city_name . ',' . $Region_name;
                $request->merge([
                    'full_address' => $full_address,
                ]);

                if ($cityFund) {
                    $request->merge([
                        'city_id' => $cityFund->serial_city,
                    ]);
                }

                if ($NeighborhoodFund) {
                    $request->merge([
                        'neighborhood_id' => $NeighborhoodFund->neighborhood_serial,
                    ]);
                }


            } else {
                $request->merge([
                    'full_address' => $request->get('full_address'),
                ]);
            }
        } else {
            $request->merge([
                'full_address' => $request->get('full_address')
            ]);
        }

        $request->merge([
            'estate_age' => $age,
        ]);


        $estate = Estate::find($id)
            ->update($request->only([
                'operation_type_id',
                'elevators_number',
                'unit_number',
                'is_disputes',
                'parking_spaces_numbers',
                'operation_type_name',
                'estate_type_name',
                'is_mortgage',
                'is_obligations',
                'touching_information',
                'is_saudi_building_code',
                'advertiser_side',
                'advertiser_character',
                'estate_use_type',
                'estate_type_id',
                'instrument_number',
                'pace_number',
                'planned_number',
                'land_number',
                'total_area',
                'estate_age',
                'floor_number',
                'street_view',
                'total_price',
                'meter_price',
                'owner_name',
                'owner_mobile',
                'lounges_number',
                'rooms_number',
                'bathrooms_number',
                'boards_number',
                'kitchen_number',
                'dining_rooms_number',
                'finishing_type',
                'interface',
                'social_status',
                'lat',
                'lan',
                'note',
                'status',
                'user_id',
                'is_rent',
                'rent_type',
                'is_resident',
                'is_checked',
                'is_insured',
                'neighborhood_id',
                'city_id',
                'state_id',
                'address',
                'in_fund_offer',
                'estate_dimensions',
                'obligations_information',
                'bedroom_number',
                'rooms_number',
                'rent_price',
                'full_address',
                'is_rent_installment',
                'rent_installment_price',
                'unit_counter',
                'advertiser_license_number',
                'advertiser_email',
                'advertiser_mobile',
                'advertiser_number',
                'advertiser_name',
                'street_name',
                'license_number',
                'advertising_license_number',
                'brokerageAndMarketingLicenseNumber',
                'channels',
                'creation_date',
                'end_date',
            ]));

        $estate = Estate::find($id);

        if ($request->get('estate_comforts')) {
            $comfort = ComfortEstate::where('estate_id', $id)->delete();
            $comforts = explode(',', $request->get('estate_comforts'));

            for ($i = 0; $i < count($comforts); $i++) {
                $comfort = ComfortEstate::create([
                    'estate_id' => $estate->id,
                    'comfort_id' => $comforts[$i],
                ]);
            }
        }
        if (($request->hasFile('photo'))) {
            $att = AttachmentEstate::where('estate_id', $id)
                ->where('type', 'images')
                ->delete();

            $xy = $request->file('photo');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');
                $atta = AttachmentEstate::create([
                    'estate_id' => $estate->id,
                    'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                ]);

                //  $atta = new AttachmentEstate();
                //  $atta->estate_id = $estate->id;
                //  $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                //   $atta->save();
                if ($i == 0) {
                    $estate->first_image = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                    $estate->save();
                }


            }


        }
        if (($request->hasFile('video'))) {
//////


            $att = AttachmentEstate::where('estate_id', $id)
                ->where('type', 'videos')
                ->delete();


            $xy = $request->file('video');
            foreach ($xy as $i => $value) {
                $extension = $value->getClientOriginalExtension();
                $array = $this->video_extensions();
                if (in_array($extension, $array)) {
                    $path = $value->store('videos', 's3');

                    if ($path != null && $path != false) {


                        $atta = AttachmentEstate::create([
                            'estate_id' => $estate->id,
                            'type' => 'videos',
                            'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                        ]);

                        if ($i == 0) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                            $estate->save();
                        }
                    }
                }


            }


        }

        if (($request->hasFile('attachment_planned'))) {
//////

            $att = AttachmentPlanned::where('estate_id', $id)->delete();
            $xy = $request->file('attachment_planned');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');


                $atta = new AttachmentPlanned();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $atta->save();


            }


        }

        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);


        $price = explode(',', $estate->total_price);
        $area = explode(',', $estate->total_area);
        $full_number = '';

        $full_area = '';
        for ($i = 0; $i < count($price); $i++) {
            $full_number .= $price[$i];
        }
        for ($i = 0; $i < count($area); $i++) {
            $full_area .= $area[$i];
        }

        $estate->total_price_number = $full_number;
        $estate->total_area_number = $full_area;
        $estate->save();

        return response()->success(__("views.Update Successfully"), $estate);
    }

    public function OprationType()
    {
        $OprationType = OprationType::get();
        return response()->success("OprationType List", $OprationType);
    }

    public function comfort()
    {

        $Comfort = Comfort::get();
        return response()->success("Comfort", $Comfort);
    }

    public function estate_request_preview(Request $request)
    {
        $page_number = $request->page_number ? $request->page_number : 15;
        $EstateRequestPreview = EstateRequestPreview::query();

        if ($request->search) {
            $EstateRequestPreview->where('estate_id', $request->search);
        }

        if ($request->estate_type_id) {
            $EstateRequestPreview->whereHas('estate', function ($q) use ($request) {
                $q->where('estate_type_id', $request->estate_type_id);
            });
        }

        if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

            $today_date = date('Y-m-d');
            if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                $EstateRequestPreview = $EstateRequestPreview->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

            } else {
                if ($request->get('to_date') && $request->get('from_date')) {
                    if ($request->get('from_date') == $request->get('to_date')) {
                        $EstateRequestPreview = $EstateRequestPreview->whereDate(
                            'created_at',
                            $request->get('to_date')
                        );
                    } else {
                        $EstateRequestPreview = $EstateRequestPreview->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                    }
                }
            }


        } elseif ($request->get('from_date') && !$request->get('to_date') && !$request->get('time')) {
            $date = date_create($request->get('from_date'));
            $date = date_format($date, "Y-m-d");

            $EstateRequestPreview = $EstateRequestPreview->whereDate('created_at', '>', $date);


        } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {

            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d");
            $EstateRequestPreview = $EstateRequestPreview->whereDate('created_at', '<', $date);


        }

        $EstateRequestPreview = $EstateRequestPreview->orderBy('id', 'desc')->paginate($page_number);

        return response()->success(__('طلبات معاينة العقارات'), EstateRequestPreviewResource::collection($EstateRequestPreview)->response()->getData(true));

    }

    public function estate_request_rate(Request $request)
    {
        $page_number = $request->page_number ? $request->page_number : 15;
        $rate = RateRequest::query();
        $rate = $rate->orderBy('id', 'desc')->paginate($page_number);
        return response()->success(__('طلبات تقييم العقارات'), EstateRequestRateResource::collection($rate)->response()->getData(true));

    }

    public function estate_request_rate_details($id)
    {
        $rate = RateRequest::query()->find($id);
        return response()->success(__('طلب  تقييم العقار'), new RateRequestShowResource($rate));

    }

    public function estate_request_location(Request $request)
    {
        $page = $request->get('page_number', 15);
        $estate = Estate::whereHas('user')->where(function ($q) use ($request) {
            $q->where(function ($q) {
                $q->where('neighborhood_id', null)->where('neighborhood_name_request', '!=', null);
            })->orWhere(function ($q) {
                $q->where('city_id', null)->where('city_name_request', '!=', null);
            });

        });
        if ($request->get('to_date') && $request->get('from_date') && !$request->get('time')) {

            $today_date = date('Y-m-d');
            if ($request->get('from_date') == $request->get('to_date') && $today_date == $request->get('from_date')) {


                $estate = $estate->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );

            } else {
                if ($request->get('to_date') && $request->get('from_date')) {
                    if ($request->get('from_date') == $request->get('to_date')) {
                        $estate = $estate->whereDate(
                            'created_at',
                            '=',
                            $request->get('from_date')
                        );
                    } else {
                        $estate = $estate->whereBetween('created_at', [$request->get('from_date'), $request->get('to_date')]);
                    }
                }
            }


        } elseif ($request->get('from_date') && !$request->get(!$request->get('time')) && !$request->get('time')) {
            $date = date_create($request->get('from_date'));
            $date = date_format($date, "Y-m-d");

            $estate = $estate->whereDate('created_at', '>', $date);


        } elseif ($request->get('to_date') && !$request->get('from_date') && !$request->get('time')) {
            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d");
            $estate = $estate->whereDate('created_at', '<', $date);


        }

        if ($request->get('time') && !$request->get('to_date') && !$request->get('from_date')) {
            if ($request->get('time') == 'today') {


                $estate = $estate->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );


            } elseif ($request->get('time') == 'week') {

                $estate = $estate->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $estate = $estate->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );


            } elseif ($request->get('time') == 'month') {


                $estate = $estate->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(30)->format('Y-m-d')
                );
                $estate = $estate->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }

        }
        if ($request->get('estate_type_id')) {


            $estate = $estate->where('estate_type_id', $request->get('estate_type_id'));


        }
        if ($request->get('area_estate_id')) {


            $area_range = AreaEstate::findOrFail($request->get('area_estate_id'));

            $estate = $estate->where('total_area', '>', $area_range->area_from)
                ->where('total_area', '<', $area_range->area_to);


        }

        if ($request->get('estate_price_id')) {

            $price_range = EstatePrice::findOrFail($request->get('estate_price_id'));


            $estate = $estate->where('total_price', '>', $price_range->estate_price_from)
                ->where('total_price', '<', $price_range->estate_price_to);
        }
        if ($request->get('city_id')) {
            $estate = $estate->where('city_id', $request->get('city_id'));
        }

        if ($request->get('state_id') && !$request->get('city_id')) {
            $estate = $estate->where('state_id', $request->get('state_id'));
        } elseif ($request->get('state_id') && $request->get('city_id')) {

        }

        if (isset($request->neighborhood_id)) {

            $neighborhood_id_array = explode(',', $request->neighborhood_id);
            $neb_ids = $neighborhood_id_array;


            $estate = $estate->whereIn('neighborhood_id', $neb_ids);

            //  ->where('total_area','<', $area_range->area_to);


            //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
        }

        if ($request->get('search')) {
            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && Estate::find($request->get('search'))) {
                $estate = $estate->where('id', $request->get('search'));

            } else {
                $estate = $estate->where('status', $request->get('search'))
                    ->orWhere('operation_type_name', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('estate_type_name', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('interface', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('advertiser_side', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('advertiser_character', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('finishing_type', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('social_status', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('rent_type', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('street_name', 'like', '%' . $request->get('search') . '%');
            }


        }

        $estate = $estate->orderBy('id', 'desc')->paginate($page);


        $information = [

            'estate' => $estate,


        ];

        return response()->success(__('العقارات'), $information);

    }


    public function funding_request(Request $request)
    {
        $page = $request->get('page_number', 15);
        $fund_request = FundingRequest::query();

        $fund_request = $fund_request->orderBy('id', 'desc')->paginate($page);

        return response()->success(__('طلبات تمويل العقارات'), FundingRequestResource::collection($fund_request)->response()->getData(true));
    }

}
