<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationProvider;
use App\Models\v3\City;
use App\Models\v3\ComfortRequestEstate;
use App\Models\v3\EstateRequest;
use App\Models\v3\Neighborhood;
use App\Models\v3\NotificationUser;
use App\Models\v3\RequestOffer;
use App\Models\v4\FcmToken;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MarketDemandsController extends Controller
{
    public function demandsRequest(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();



        $myRequestFundOffer = 0;
        if ($user != null) {
            $myRequestFundOffer = RequestOffer::whereHas('provider')->whereHas('estate')->whereHas('request')->where('provider_id',
                $user->id);
        } else {
            $myRequestFundOffer = 0;
        }
        $Mechanic = EstateRequest::where('deleted_at', null)->where(function ($q) use ($user) {
            $q->where('status', 'new')
                ->orWhere('status', 'complete')->whereHas('offers', function ($q) use ($user) {
                    $q->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)->orWhere('provider_id', $user->id);
                    })->where('status', 'accept');
                });
        });

        $allRequestFund = EstateRequest::where('deleted_at', null)->where(function ($q) use ($user) {
            $q->where('status', 'new')
                ->orWhere('status', 'complete')->whereHas('offers', function ($q) use ($user) {
                    $q->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)->orWhere('provider_id', $user->id);
                    })->where('status', 'accept');
                });
        });

        $date = date('Y-m-d');
        $RequestEstate = EstateRequest::whereDate('created_at', $date)->where('deleted_at', null)->where(function ($q) use ($user) {
            $q->where('status', 'new')
                ->orWhere('status', 'complete')->whereHas('offers', function ($q) use ($user) {
                    $q->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)->orWhere('provider_id', $user->id);
                    })->where('status', 'accept');
                });
        });
        $estate = '';

        if ($request->get('search') && $request->get('search') != null) {
            if ((filter_var($request->get('search'), FILTER_VALIDATE_INT) !== false)) {
                $Mechanic = $Mechanic->where('id', $request->get('search'));
            } else {
                $Mechanic = $Mechanic->where(function ($q) use ($request) {
                    $q->where('address', 'like', '%' . $request->get('search') . '%')->
                    orWhereHas('city', function ($query) use ($request) {
                        $query->where('name_ar', 'like', '%' . $request->get('search') . '%');
                    })->orWhereHas('neighborhood', function ($query) use ($request) {
                        $query->where('name_ar', 'like', '%' . $request->get('search') . '%');
                    });
                });
            }
        }


        if ($request->get('search') && (filter_var($request->get('search'), FILTER_VALIDATE_INT) == false) || !$request->get('search')) {

            if ($request->get('today') && $request->get('today') != null) {
                $Mechanic = $Mechanic->whereDate('created_at', $date);
            }

            if ($request->get('state_id') && $request->get('state_id') != null) {

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


            if ($request->get('city_id') && $request->get('city_id') != null && $request->get('neighborhood_id') && $request->get('neighborhood_id') != null) {
                $estate_city = explode(',', $request->get('city_id'));
                $user->saved_filter_city = $request->get('city_id');
                $user->save();
                $estate_neighborhood = explode(',', $request->get('neighborhood_id'));
                $user->saved_filter_city = $request->get('neighborhood_id');
                $user->save();
                $Mechanic = $Mechanic->where(function ($q) use ($request, $estate_city, $estate_neighborhood) {
                    $q->whereIn('city_id', $estate_city)->orWhereIn('neighborhood_id', $estate_neighborhood);
                });

                $allRequestFund = $allRequestFund->where(function ($q) use ($request, $estate_city, $estate_neighborhood) {
                    $q->whereIn('city_id', $estate_city)->orWhereIn('neighborhood_id', $estate_neighborhood);
                });

                $RequestEstate = $RequestEstate->where(function ($q) use ($request, $estate_city, $estate_neighborhood) {
                    $q->whereIn('city_id', $estate_city)->orWhereIn('neighborhood_id', $estate_neighborhood);
                });

            } else {
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
                $Mechanic = $Mechanic->where('price_from', ' >= ', $request->get('price_from'));
                $Mechanic = $Mechanic->where('price_to', ' <= ', $request->get('price_to'));
                $allRequestFund = $allRequestFund->where('price_from', ' >= ', $request->get('price_from'));
                $allRequestFund = $allRequestFund->where('price_to', ' <= ', $request->get('price_to'));
                $RequestEstate = $RequestEstate->where('price_from', ' >= ', $request->get('price_from'));
                $RequestEstate = $RequestEstate->where('price_to', ' <= ', $request->get('price_to'));


            }

            if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_to') != null && $request->get('area_from') != null) {
                $Mechanic = $Mechanic->where('area_from', ' >= ', $request->get('area_from'));
                $Mechanic = $Mechanic->where('area_to', ' <= ', $request->get('area_to'));
                $allRequestFund = $allRequestFund->where('area_from', ' >= ', $request->get('area_from'));
                $allRequestFund = $allRequestFund->where('area_to', ' <= ', $request->get('area_to'));
                $RequestEstate = $RequestEstate->where('area_from', ' >= ', $request->get('area_from'));
                $RequestEstate = $RequestEstate->where('area_to', ' <= ', $request->get('area_to'));
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
        }


        $Mechanic = $Mechanic->orderBy('id', 'desc')->paginate();
        foreach ($Mechanic->items() as $finiceingItem) {
            $offer = RequestOffer::where('request_id', $finiceingItem->id)
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
    }

    public function addRequestEstate(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();



        $rules = Validator::make($request->all(), [
            'operation_type_id' => 'required',
            'estate_type_id' => 'required',
            'request_type' => 'required',
            'city_id' => 'sometimes|required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request->merge([
            'user_id' => $user->id,
            'status' => 'new',
        ]);
        $EstateRequest = EstateRequest::create($request->only([
            'operation_type_id',
            'request_type',
            'estate_type_id',
            'area_from',
            'area_to',
            'price_from',
            'price_to',
            'room_numbers',
            'owner_name',
            'owner_mobile',
            'display_owner_mobile',
            'note',
            'status',
            'user_id',
            'city_id',
            'address',
            'bathroom_numbers',
            'neighborhood_id',
        ]));


        $EstateRequest = EstateRequest::find($EstateRequest->id);
        $EstateRequest->save();

        $user->count_request = $user->count_request + 1;
        $user->save();
        $EstateRequest = EstateRequest::find($EstateRequest->id);

        if ($request->get('estate_comforts')) {
            $comforts = explode(',', $request->get('estate_comforts'));
            for ($i = 0; $i < count($comforts); $i++) {
                $comfort = ComfortRequestEstate::create([
                    'estate_id' => $EstateRequest->id,
                    'comfort_id' => $comforts[$i],
                ]);
            }
        }

        if ($request->delete_id) {
            $offer = RequestOffer::where('request_id', $request->delete_id)->get();
            foreach ($offer as $item) {
                $push_data = [
                    'title' => 'تم تعديل الطلب #' . $request->delete_id,
                    'body' => 'تم تعديل الطلب رقم #' . $request->delete_id . '  والذي قدمت له عرض سابقاً يمكنك الإطلاع على العرض المحدث وتقديم عرضك من جديد' . $EstateRequest->link,
                    'id' => $EstateRequest->id,
                    'user_id' => $item->user_id,
                    'type' => 'request',
                ];
                $note = NotificationUser::create([
                    'user_id' => $item->user_id,
                    'title' => 'تم تعديل الطلب #' . $request->delete_id,
                    'type' => 'request',
                    'type_id' => $EstateRequest->id,
                ]);
                $client = User::find($item->user_id);
                if ($client) {
                    $fcm_token = FcmToken::where('user_id', $client->id)->get();
                    foreach ($fcm_token as $token) {
                        send_push($token->token, $push_data, $token->type);
                    }
                }
            }
        }
        dispatch(new NotificationProvider($EstateRequest));
        return response()->success(__("views.EstateRequest"), $EstateRequest);
    }

    public function send_offer_app_status(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();

        $rules = Validator::make($request->all(), [
            'offer_id' => 'required',
            'status' => 'required|in:preview,accept,reject,cancel,set_time,accept_time',
            'times' => 'required_if:status,set_time',
            'accept_time' => 'required_if:status,accept_time',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $RequestOffer = RequestOffer::find($request->get('offer_id'));
        if ($RequestOffer) {
            if ($RequestOffer) {

                $EstateRequest = EstateRequest::findOrFail($RequestOffer->request_id);
                if (!$EstateRequest) {
                    return response()->error(__("views.not found"), []);
                }

                if ($request->status == 'accept') {
                    if ($RequestOffer->status == 'accept') {
                        return response()->error(__("views.The offer has already been accepted"), []);
                    }
                    $check_offer = RequestOffer::where('request_id', $EstateRequest->id)->where('status', 'accept')->get();
                    if (count($check_offer) > 0) {
                        return response()->error(__("views.More than one offer cannot be accepted"), []);
                    }
                } elseif ($request->status == 'set_time') {
                    if ($RequestOffer->status != 'preview') {
                        return response()->error(__("views.offer not preview"), []);
                    }
                    $RequestOffer->times = $request->get('times');
                } elseif ($request->status == 'accept_time') {
                    if ($RequestOffer->status != 'set_time') {
                        return response()->error(__("views.offer not set_time"), []);
                    }
                    $RequestOffer->accept_time = $request->get('accept_time');
                    $RequestOffer->status = $request->get('status');
                }

                $RequestOffer->status = $request->get('status');
                $RequestOffer->save();

                if ($request->status == 'preview') {
                    $client = User::where('id', $RequestOffer->provider_id)->first();
                    $title = 'لديك عرض معاينة على الطلب #' . $EstateRequest->id;
                    $body = 'لديك عرض معاينة  على الطلب #' . $EstateRequest->id;
                } elseif ($request->status == 'accept') {
                    $client = User::where('id', $RequestOffer->provider_id)->first();
                    $title = 'تم قبول عرضك على الطلب #' . $EstateRequest->id;
                    $body = 'تم قبول عرضك على الطلب #' . $EstateRequest->id;

                    $EstateRequest->status = 'complete';
                    $EstateRequest->save();

                    $all_offer = RequestOffer::where('request_id', $EstateRequest->id)->where('id', '!=', $RequestOffer->id)->get();
                    foreach ($all_offer as $offer) {
                        $offer->status = 'reject';
                        $offer->save();
                        $title1 = 'تم رفض عرضك على الطلب #' . $EstateRequest->id;
                        $body1 = 'تم رفض عرضك على الطلب #' . $EstateRequest->id;
                        $client1 = User::where('id', $RequestOffer->provider_id)->first();
                        if ($client1) {
                            $push_data = [
                                'title' => $title1,
                                'body' => $body1,
                                'id' => $RequestOffer->id,
                                'user_id' => $client1->id,
                                'type' => 'offer',
                            ];

                            $note = NotificationUser::create([
                                'user_id' => $client1->id,
                                'title' => $title1,
                                'type' => 'offer',
                                'type_id' => $EstateRequest->id,
                            ]);
                            $fcm_token = FcmToken::where('user_id', $client1->id)->get();
                            foreach ($fcm_token as $token) {
                                send_push($token->token, $push_data, $token->type);
                            }
                        }
                    }

                } elseif ($request->status == 'cancel') {
                    $client = User::where('id', $RequestOffer->provider_id)->first();
                    $title = 'تم الغاء عرضك على الطلب #' . $EstateRequest->id;
                    $body = 'تم الغاء عرضك على الطلب #' . $EstateRequest->id;

                } elseif ($request->status == 'set_time') {
                    $client = User::where('id', $RequestOffer->user_id)->first();
                    $title = 'تم ارسال الاوقات المناسبة على عرضك #' . $EstateRequest->id;
                    $body = 'تم ارسال الاوقات المناسبة على عرضك #' . $EstateRequest->id;

                } elseif ($request->status == 'accept_time') {
                    $client = User::where('id', $RequestOffer->provider_id)->first();
                    $title = 'تم قبول وقت مناسب على عرضك #' . $EstateRequest->id;
                    $body = 'تم قبول وقت مناسب على عرضك #' . $EstateRequest->id;

                } else {
                    $client = User::where('id', $RequestOffer->provider_id)->first();
                    $title = 'تم رفض عرضك على الطلب #' . $EstateRequest->id;
                    $body = 'تم رفض عرضك على الطلب #' . $EstateRequest->id;
                }


                if ($client) {
                    $push_data = [
                        'title' => $title,
                        'body' => $body,
                        'id' => $RequestOffer->id,
                        'user_id' => $client->id,
                        'type' => 'offer',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $client->id,
                        'title' => $title,
                        'type' => 'offer',
                        'type_id' => $EstateRequest->id,
                    ]);
                    $fcm_token = FcmToken::where('user_id', $client->id)->get();
                    foreach ($fcm_token as $token) {
                        send_push($token->token, $push_data, $token->type);
                    }
                }

                return response()->success("Offer " . $request->get('status') . " ", $RequestOffer);
            } else {
                return response()->error(__("views.not found"), []);
            }
        } else {
            return response()->error(__("views.not found"));
        }


    }

    public function send_offer(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();
        $EstateRequest = EstateRequest::where('id', $request->get('request_id'))->first();
        if ($EstateRequest) {
            $client = User::where('id', $EstateRequest->user_id)
                ->orwhere('related_company', $EstateRequest->user_id)
                ->get();


            $estateArray = explode(',', $request->get('estate_id'));
            $user = User::find($user->id);
            $estate = $user->estate()
                ->whereHas('EstateFile')
                ->whereHas('user')
                ->whereIn('id', $estateArray)
                ->get();

            if (!$estate) {
                return response()->error(__("views.not found"));
            }

            if ($estate->count() == 1) {
                $checkOffer = RequestOffer::where('provider_id', $user->id)
                    ->where('estate_id', $estate[0]->id)
                    ->where('request_id', $request->get('request_id'))
                    ->where('user_id', $EstateRequest->user_id)
                    ->first();

                if ($checkOffer) {
                    return response()->error(__("views.you have already sent an offer"));
                }
            }

            for ($i = 0; $i < count($estate); $i++) {


                $checkOffer = RequestOffer::where('provider_id', $user->id)
                    ->where('estate_id', $estate[$i]->id)
                    ->where('request_id', $request->get('request_id'))
                    ->where('user_id', $EstateRequest->user_id)
                    ->first();


                if (!$checkOffer) {
                    if ($EstateRequest) {
                        $request->merge([
                            'provider_id' => $user->id,
                            'user_id' => $EstateRequest->user_id,
                            'estate_id' => $estate[$i]->id,
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
                        'user_id' => $clientItem->id,
                        'title' => 'لديك عرض جديد على الطلب  #' . $EstateRequest->id,
                        'type' => 'offer',
                        'type_id' => $EstateRequest->id,
                    ]);
                    if ($clientItem->device_token) {
                        $fcm_token = FcmToken::where('user_id', $clientItem->id)->get();
                        foreach ($fcm_token as $token) {
                            send_push($token->token, $push_data, $token->type);
                        }
                    }
                }
            }

            return response()->success(__("views.RequestOffer"), []);
        } else {
            return response()->error(__("views.No Data"));
        }

    }


}
