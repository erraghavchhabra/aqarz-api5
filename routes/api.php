<?php
/*
$data = ['name' => "ProviderAPI"];
\Mail::send([], $data, function ($message)  {
    $message->to('abdullah@experto.sa', 'الملف اليومي')
        ->subject("تم ارسال الملف اليومي لسيرفر عقارز اسم الملف")
        ->from('aqarz@info.com', 'System')
        ->setBody("<p>تم ارسال الملف اليومي لسيرفر عقارز اسم الملف</p>", 'text/html');
});
*/
/*header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
//header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Origin: http://127.0.0.1:8000');
header('Access-Control-Allow-Origin : *');
header('Access-Control-Allow-Methods : *');
*/
// Route::get('IAM/login', 'v3\Api\AuthController@login_iam');


Route::get('IAM/login', 'v3\Api\AuthController@login_iam_live_test');
Route::get('IAM/logout', 'v3\Api\AuthController@logout');
Route::post('/login/auth/{id}/callback', 'v3\Api\AuthController@authCallback');
Route::post('/user/{id}', 'v3\Api\AuthController@getUserByid');
Route::post('/login/auth/callback/response', 'v3\Api\AuthController@authCallbackResponse');

Route::post('/check-license-number', 'v4\Api\AuthController@check_license_number');

use App\Helpers\JsonResponse;
use App\Imports\EstateImport;
use App\Models\v3\City;
use App\Models\v3\City3;
use App\Models\v3\District;
use App\Models\v3\Estate;
use App\Models\v3\FundRequestHasOffer;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\Neighborhood;
use App\Models\v3\NotificationUser;
use App\Models\v3\Region;
use App\Models\v3\ReportEstate;
use App\Models\v3\RequestFund;
use App\User;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

//$carbonTime = Carbon::createFromFormat('Y-m-d H:i:s', '2022-07-19 02:45:44', '+03:00');
//dd($carbonTime);
/*
$estate=Estate::doesntHave('user')->get();

dd(count($estate));
*/

Route::get('/user/iam', function (Request $request) {
    $user = \App\User::with('Iam_information')->
    whereHas('Iam_information')->get();
    $userId = [];
    $i = 0;


    foreach ($user as $userItem) {

        $userItem->onwer_name = $userItem->Iam_information->arabicName;
        //   $userItem->name = $userItem->Iam_information->arabicName;
        $userItem->save();
        $userId[$i] = ['i' => $i, 'user_id' => $userItem->id, 'user_changed_name' => $userItem->Iam_information->arabicName, 'user_change_now' => $userItem->onwer_name];
        $i++;
    }
    Log::channel('slack')->info(['data' => $userId, 'msg' => 'hard code createor']);
    return $userId;
});


Route::get('update_request_estate', function () {
    $request = \App\Models\v3\EstateRequest::all();
    foreach ($request as $requestItem) {
        $city = \App\Models\v3\City::find($requestItem->city_id);
        if ($city != null) {
            $newCity = \App\Models\v4\Cities::where('name', $city->name)->first();
        }
    }
});


Route::get('zatca', 'ZatcaController@index');


Route::get('update_city_ejar', function () {

    $city = \App\Models\v4\EjarCities::all();
    foreach ($city as $cityItem) {
        $city_check = \App\Models\v4\Cities::where('name_ar', $cityItem->name_ar)->orWhere('name_en', $cityItem->name_en)->get();

        if (count($city_check) > 0) {
            foreach ($city_check as $city_checkItem) {
                $city_checkItem->ejar_id = $cityItem->ejar_id;
                $city_checkItem->ejar_region_id = $cityItem->region_id;
                $city_checkItem->save();
            }
        } else {
            echo $cityItem->name_en . '   <br>';
        }

    }
});

Route::get('update_dis_ejar', function () {

    $city = \App\Models\v4\EjarDistricts::all();
    foreach ($city as $cityItem) {
        $city_check = \App\Models\v4\District::where('name_ar', 'like', '%' . $cityItem->name_ar . '%')->orWhere('name_en', 'like', '%' . $cityItem->name_en . '%')->get();

        if (count($city_check) > 0) {
            foreach ($city_check as $city_checkItem) {
                $city_checkItem->ejar_id = $cityItem->ejar_id;
                $city_checkItem->save();
            }
        } else {
            echo $cityItem->name_en . '   <br>';
        }

    }
});


Route::get('msg_last_update', function () {
    $msg = \App\Models\v3\Msg::all();
    foreach ($msg as $msgItem) {
        $dep = \App\Models\v3\MsgDet::where('msg_id', $msgItem->id)->orderBy('id', 'desc')->first();
        if ($dep != null) {
            $msgItem->last_msg_date = $dep->created_at;
            $msgItem->save();
        }
    }
});

Route::get('add_fcm_token', function () {
    $user = \App\User::all();
    foreach ($user as $userItem) {

        if ($userItem->device_token == null || $userItem->device_type == null)
            continue;
        \App\Models\v4\FcmToken::create([
            'user_id' => $userItem->id,
            'token' => $userItem->device_token,
            'type' => $userItem->device_type,
        ]);
    }

    return 'done';
});


Route::get('delete_request_offer', function () {
    $data = \App\Models\v3\RequestOffer::all();
    foreach ($data as $dataItem) {
        $estate = \App\Models\v3\Estate::find($dataItem->estate_id);
        if ($estate == null) {
            $dataItem->delete();
        }
    }
});


Route::get('user_iam_update', function () {
    $user = User::query()->where('is_iam_complete', 1)->get();
    $i = 0;
    foreach ($user as $userItem) {
        if ($userItem->advertiser_number == null) {
            $userItem->is_iam_complete = 0;
            $userItem->save();
        }
        $i++;
    }
    return $i;
});

Route::get('user_iam_update1', function () {
    $iam = \App\Models\v3\IamProvider::all();

    foreach ($iam as $iamItem) {
        $user = User::query()->find($iamItem->user_id);
        if ($user != null) {
            $user->is_iam_complete = 1;
            $iamItem->save();
        }
    }
});


Route::get('update_neighborhood', function (Request $request) {
    $n = Neighborhood::query();
    if ($request->from && $request->to) {
        $n = $n->where('id', '>=', $request->from)->where('id', '<', $request->to);
    }
    $n = $n->get();
    foreach ($n as $nItem) {
        $name = $nItem->name_ar;
        if (strpos($nItem->name_ar, 'حي') === false) {
            $nItem->name_ar = 'حي ' . $nItem->name_ar;
            $nItem->name_en = 'حي ' . $nItem->name_en;
            $check = explode(' ', $nItem->search_name);
            if (count($check) > 1) {
                if ($check[0] != $check[1]) {
                    $nItem->search_name = str_replace($name, 'حي ' . $name, $nItem->search_name);
                } else {
                    if (count($check) > 2) {
                        echo $nItem . ' ----> ';
                    } else {
                        $c2 = 'حي ' . $name;
                        $nItem->search_name = $check[0] . ' ' . $c2;
                    }

                }
            } else {
                $nItem->search_name = 'حي ' . $name;
            }
            $nItem->save();
        }

    }
});

Route::get('update_estate', function (Request $request) {
    $estate = Estate::query();
    if ($request->from && $request->to) {
        $estate = $estate->where('id', '>=', $request->from)->where('id', '<', $request->to);
    }
    $estate = $estate->get();
    $coll = collect();
    foreach ($estate as $estateItem) {
        if ($estateItem->lat && $estateItem->lan) {
            $location = $estateItem->lat . ' ' . $estateItem->lan;
            $dis = checkPint("$location");
            if ($dis != null) {
                $dis = \App\Models\v4\District::where('district_id', $dis)->first();
                $estateItem->full_address = $dis->name . ',' . @$dis->city->name . ',' . @$dis->city->region->name;
                $estateItem->city_id = $dis->city_id;
                $estateItem->neighborhood_id = $dis->district_id;
                $estateItem->save();
            } else {
                $coll->push(['estate_id' => $estateItem->id, 'location' => $estateItem->lat . ' ' . $estateItem->lan]);
            }
        }
    }

    return $coll->all();
});


Route::get('check_all_city_exist', function () {
    $city = City3::all();
    $c = collect();
    foreach ($city as $cityItem) {
        $n = Neighborhood::query()->where('search_name', 'like', '%' . $cityItem->name_ar . '%')->first();
        if (!$n) {
            $c->push($cityItem->name_ar);
        }
    }
    return $c;
});


Route::get('add_district', function () {
    $d = \App\Models\v4\District::all();
    foreach ($d as $key => $deItem) {
        if ($deItem->boundaries != null) {
            $polygon = $deItem->boundaries[0]->jsonSerialize()->getCoordinates();
            $NumPoints = count($polygon);
            if ($polygon[$NumPoints - 1] == $polygon[0]) {
                $NumPoints--;
            } else {
                //Add the first point at the end of the array.
                $polygon[$NumPoints] = $polygon[0];
            }

            $x = 0;
            $y = 0;

            $lastPoint = $polygon[$NumPoints - 1];
            for ($i = 0; $i <= $NumPoints - 1; $i++) {
                $point = $polygon[$i];
                $x += ($lastPoint[0] + $point[0]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
                $y += ($lastPoint[1] + $point[1]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
                $lastPoint = $point;
            }
            $path = ComputeArea($polygon);
            $x /= -6 * $path;
            $y /= -6 * $path;
            $locationForSave = $y . ',' . $x;
            echo $key . '   ' . $locationForSave;
            echo '<br>';
            $sd = \App\Models\v4\District::find($deItem->district_id);
            $sd->center = $locationForSave;
            $sd->save();

        }


    }
});
/*
$request_fund = \App\Models\v3\RequestFund::get();
$array = [
    '0535056065',
    '0505888319',
    '0542444445',
    '0553445350',
    '0565338034',
    '0568286114',
    '0533370390',
    '0502144331',
    '0543213999',
    '0556102372',
    '0542096421',
    '0551916777',
    '0568288826',
    '0565477388',
    '0598506545',
    '0538479777',
    '0569853288',
    '0592233077',
    '0509023902',
    '0543438333',
    '0543383177',
    '0541133606',
    '0506801816',
    '0503837636',
    '0540382882',
    '0504811505',
    '0562900028',
    '0509933991',
    '0503081979',
    '0535150665',
    '0557279487',
    '0505557106',
    '0540005419',
    '0549232685',
    '0566154445',
    '0500040598',
    '0559953366',
    '0560566989',
    '0566674888',
    '0502287553',
    '0501251920',
    '0548957050',
    '0544940445',
    '0550555047',
    '0509988804',
    '0553249485',
    '0500043051',
    '0569993899',
    '0506092312',
    '0540531232',
    '0503075005',
    '0505817204',
    '0504418768',
    '0504222005',
    '0505859009',
    '0556856556',
    '0555793896',
    '0508599949',
    '0550950530',
    '0555320203',
    '0581366688',
    '0558321131',
    '0549990200',
    '0558536611',
    '0561778348',
    '058133001',
    '0506328553',
    '0557180084',
    '0544448140',
    '0501492080',
    '0538406734',
    '0505889072',
    '0545499967',
    '0505971305',
    '0504591159',
    '0506125436',
    '0505859897',
    '0509592550',
    '0599881474',
    '0560556688',
    '0502060105',
    '0505590058',
    '0543387738',
    '0559973344',
    '0503825089',
    '0566780001',
    '0580954066',
    '0555859913',
    '0558302216',
    '0500375572',
    '0501544465',
    '0508795098',
    '0506417738',
    '0540205555',
    '0565225777',
    '0590080859',
    '0530010030',
    '0596913666',
    '0545735742',
    '0563229938',
    '0555873130',
    '0544103330',
    '0594355585',
    '0559846122',
    '0551688537',
    '0566512341',
    '0553133276',
    '0500005408',
    '0566654773',
    '0505872000',
    '0566510400',
    '0598506545',
    '0533370390',
    '0504931741',
];
$result = [];
for ($i = 0; $i < count($array); $i++) {
    $str1 = substr($array[$i], 1);
    $str1 = '966' . $str1;
    // dd($str1);
    $str3 = '0540005419';
    $request_fund = RequestFund::withTrashed()->
    //where('beneficiary_mobile',$array[$i])
    where('beneficiary_mobile', 'like', '%' . $array[$i] . '%')
        ->orwhere('beneficiary_mobile', 'like', '%' . $str1 . '%')
        ->orderBy('id', 'desc')
        ->first();
    // dd($request_fund);

    if ($request_fund) {
        $result[$i] = ['uuid' => $request_fund->uuid,
            'mobile' => $array[$i], 'is_deleted' => $request_fund->deleted_at, 'id' => $request_fund->id];

        Log::channel('slack')->info(['uuid' => $request_fund->uuid,
            'mobile' => $array[$i], 'is_deleted' => $request_fund->deleted_at, 'id' => $request_fund->id]);
    }
}
Log::channel('slack')->info(['data' => $result]
);
dd($result);
*/
/*
$user = \App\User::with('employee')->
whereHas('employee')->get();
$userId = [];
$i = 0;
foreach ($user as $userItem) {
    $arreyEmp = $userItem->employee()->whereHas('estate')->pluck('id');
    $estates =Estate::whereIn('user_id',$arreyEmp->toArray())->first();
    $estatesOwn =Estate::where('user_id',$userItem->id)->first();

    if($estates)
    {

        $estatesUpdated =Estate::whereIn('user_id',$arreyEmp->toArray())->update([
            'company_id'=>$userItem->id
        ]) ;



    }

    if($estatesOwn)
    {
        $estatesUpdated =Estate::where('user_id',$userItem->id)->update([
            'company_id'=>$userItem->id
        ]) ;
    }
    $userItem->account_type = 'company';
    $userItem->save();
    $userId[$i] = ['emp_ids' => $arreyEmp,
        'company_id' => $userItem->id,
        'emp_estates' => Estate::whereIn('user_id',$arreyEmp->toArray())->pluck('id')->toArray(),
        'own_estates' => Estate::where('user_id',$userItem->id)->pluck('id')->toArray(),
        'user_type'=>$userItem->account_type
    ];
    $i++;

}
Log::channel('slack')->info(['data' => $userId, 'msg' => 'hard code createor for estate compny']);
dd($user);
*/

/*
$fund_request = \App\Models\v3\RequestFund::get();

foreach ($fund_request as $fund_requestItem) {
    $RequestOffer = FundRequestOffer::where('uuid',
        $fund_requestItem->uuid)
        ->whereHas('estate')
        ->whereHas('provider')
        ->whereHas('fund_request')
        ->where('status', '!=', 'expired')
        ->count();
    $RequestOfferexpired = FundRequestOffer::where('uuid',
        $fund_requestItem->uuid)
        ->whereHas('estate')
        ->whereHas('provider')
        ->whereHas('fund_request')
        ->where('status', '=', 'expired')
        ->count();

    $lastRequestOfferdeleted = FundRequestOffer::withTrashed()
        ->where('uuid', $fund_requestItem->uuid)
        ->where('deleted_at', '!=', null)
        ->whereHas('estate')
        ->whereHas('provider')
        ->whereHas('fund_request')
        // ->where('status','!=','expired')
        ->count();
    $RequestOfferactive = FundRequestOffer::where('uuid',
        $fund_requestItem->uuid)
        ->whereHas('estate')
        ->whereHas('provider')
        ->whereHas('fund_request')
        ->whereIn('status', ['sending_code', 'waiting_code'])
        ->count();


    /*
     *      'count_offers',
        'count_expired_offer',
        'count_deleted_offer',
        'count_active_offer',
     */
/*   $fund_requestItem->count_offers = $RequestOffer;
   $fund_requestItem->count_expired_offer = $RequestOfferexpired;
   $fund_requestItem->count_deleted_offer = $lastRequestOfferdeleted;
   $fund_requestItem->count_active_offer = $RequestOfferactive;
   $fund_requestItem->save();



}

// dd($RequestOffer,$RequestOfferexpired,$lastRequestOfferdeleted,$RequestOfferactive);
dd(333);*/
/*$lastRequestOffer = FundRequestOffer::whereHas('estate')
    ->whereHas('provider')
    ->whereHas('fund_request')
    ->where('status','!=','expired')
    //  ->whereNotIn('id', isset($finice_offer_ids) ? $finice_offer_ids : [0])
    ->orderBy('id', 'desc')
    ->get();

foreach ($lastRequestOffer as $lastRequestOfferItem)
{
    $check=FundRequestHasOffer::where('uuid',$lastRequestOfferItem->uuid)->first();
    if(!$check)
    {
        FundRequestHasOffer::create([
            'uuid'=> $lastRequestOfferItem->uuid,
            'display_status'=> 'yes',
        ]);
    }

}

dd(3333);*/
Route::get('/user', function (Request $request) {
    dd(Get_Address_From_Google_Maps('24.774265', '46.738586'));
    return $request->user();
});

Route::get('/report/estate', function (Request $request) {
    \Excel::import(new EstateImport(), public_path('reportsesate.xlsx'));
    return 123;
});


Route::get('/report/estate/export', function (Request $request) {
    return Excel::download(new \App\Imports\EstateExport(), 'report_estate' . '.xlsx');
    return 123;
});


Route::get('/check/notification', function (Request $request) {
    $rules = Validator::make($request->all(), [
        'type' => 'required|in:request,fund_request,fund_offer,offer,chat,rate_offer,rate_estate,employee,estate,estate_request,deferred_installment',

    ]);

    if ($rules->fails()) {
        return JsonResponse::fail($rules->errors()->first(), 400);
    }
    $device_token = 'ee9s5S5WcUz-j6rnq3Mfbd:APA91bGAjG6VQh5Ju4NueO9qfjj5R9df4oy8qUpG8PTT_l22qvh1eEv1ggGpWyA3yNf0awz0VBQn7O6fQn__banrsInmcT5eutwKynh4vzYfSYx2qbJaStHPGFcGYaA4w96tmr12v52A';

    $note_fund_offer = NotificationUser::create([
        'user_id' => 3417,
        'title' => __('views.You Offer Rejected  #') . '47218',
        'type' => 'fund_offer',
        'type_id' => 47218,
    ]);
    $note_estate_request = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'لديك طلب جديد #' . '59',
        'body' => 'لديك طلب جديد #' . '59',
        'type' => 'request',
        'type_id' => 59,
    ]);
    $note_fund_request = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'لديك طلب جديد #' . '11047',
        'type' => 'request',
        'type_id' => 11047,
    ]);
    $note_estate = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'test',
        'type' => 'estate',
        'type_id' => 2287,
    ]);
    $note_chat = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'لديك رسالة جديدة #' . '1387',
        'body' => 'لديك رسالة جديدة #' . '1387',
        'type' => 'chat',
        'type_id' => 1387,
    ]);
    $note_offer = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'لديك عرض جديد على الطلب  #' . '59',
        'body' => 'لديك عرض جديد على الطلب  #' . '59',
        'type' => 'offer',
        'type_id' => 59,

    ]);
    $note_rate = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'لديك تقييم على العرض #' . '5',
        'body' => 'لديك تقييم على العرض #' . '5',
        'type' => 'rate',
        'type_id' => 5,
    ]);
    $note_rate_estate = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'لديك تقييم على العقار #' . '2287',
        'type' => 'rate_estate',
        'type_id' => 2287,
    ]);
    $note_fund_request = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'يوجد طلب صندوق جديد #' . '2287',
        'type' => 'fund_request',
        'type_id' => 11047,
    ]);
    $note_emp = NotificationUser::create([
        'user_id' => 3417,
        'title' => 'لديك موظف جديد برقم :' . '104',
        'body' => 'لديك موظف جديد برقم :' . '104',
        'type' => 'employee',
        'type_id' => '104',
    ]);


    $push_data_rate = [
        'title' => 'You Have New Rate  Offer #' . '5',
        'body' => 'You Have Rate Offer #' . '5',
        'id' => 5,
        'user_id' => 3417,
        'type' => 'rate',
    ];
    $push_data_estate = [
        'title' => 'تم رفض العقار الخاص بك رقم # ' . '2287',
        'body' => 'test',
        'id' => 2287,
        'user_id' => 3417,
        'type' => 'estate',
    ];
    $push_data_chat = [
        'title' => 'لديك رسالة جديدة ',
        'body' => 'إشعار رسالة',
        'id' => 1387,
        'user_id' => 3417,
        'type' => 'chat',
    ];
    $push_data_fund_offer = [
        'title' => __('views.You Offer Rejected  #') . '47218',
        'body' => 'العرض غير مناسب',
        'id' => 47218,
        'user_id' => 3417,
        'type' => 'fund_offer',
    ];
    $push_data_estate_request = [
        'title' => 'لديك طلب جديد #' . '59',
        'body' => 'لديك طلب جديد #' . '59',
        'id' => 59,
        'user_id' => 3417,
        'type' => 'request',
    ];
    $push_data_offer = [
        'title' => 'لديك عرض جديد على الطلب  #' . '59',
        'body' => 'لديك عرض جديد على الطلب  #' . '59',
        'id' => 59,
        'user_id' => 3417,
        'type' => 'offer',
    ];
    $push_data_rate_estate = [
        'title' => 'You Have New Rate  Estate #' . '2287',
        'body' => 'You Have Rate Estate #' . '2287',
        'id' => 2287,
        'user_id' => 3417,
        'type' => 'rate_estate',
    ];
    $push_data_emp = [
        'title' => __('views.You Have New Employee #') . '104',
        'body' => __('views.You Have New Employee #') . '104',
        'id' => 104,
        'user_id' => 3417,
        'type' => 'employee',
    ];
    $push_data_fund_request = [
        'title' => 'لديك طلب صندوق جديد #' . '11047',
        'body' => 'لديك طلب صندوق جديد #' . '11047',
        'id' => 11047,
        'user_id' => 3417,
        'type' => 'fund_request',
    ];


    $client = User::where('id', 3417)->first();

    if ($client) {

        if ($request->get('type') == 'fund_offer') {
            send_push($device_token, $push_data_fund_offer, 'ios');
        }
        if ($request->get('type') == 'estate_request') {
            send_push($device_token, $push_data_estate_request, 'ios');

        }
        if ($request->get('type') == 'chat') {
            send_push($device_token, $push_data_chat, 'ios');

        }
        if ($request->get('type') == 'estate') {
            send_push($device_token, $push_data_estate, 'ios');

        }
        if ($request->get('type') == 'offer') {
            send_push($device_token, $push_data_offer, 'ios');

        }
        if ($request->get('type') == 'rate_offer') {
            send_push($device_token, $push_data_rate, 'ios');

        }
        if ($request->get('type') == 'rate_estate') {
            send_push($device_token, $push_data_rate_estate, 'ios');

        }
        if ($request->get('type') == 'employee') {
            send_push($device_token, $push_data_emp, 'ios');

        }
        if ($request->get('type') == 'fund_request') {
            send_push($device_token, $push_data_fund_request, 'ios');


        }

    }


    return 'true';

});


Route::get('/delete/eststed', function (Request $request) {
    $e = \App\Models\v3\Estate::whereMonth('created_at', '>', 10)
        ->where('is_complete', '1')
        ->get();

    dd($e);
    $ids = ReportEstate::pluck('estate_id');

    $e = \App\Models\v3\Estate::where('is_complete', '1')
        ->whereNotIn('id', $ids->toArray())
        ->where('id', '>', 0)
        // ->whereDate('created_at', '<=', $date)
        ->get();

    dd($e);
    $ids = ReportEstate::pluck('estate_id');
    \App\Models\v3\Estate::withTrashed()->whereIn('id', $ids->toArray())
        ->update(['deleted_at' => null]);

    dd(444);
    return Excel::download(new \App\Imports\EstateExport(), 'report_estate' . '.xlsx');
    return 123;
});
Route::get('/get/deleted_estate', function (Request $request) {
    return Excel::download(new \App\Imports\EstateDeletedExport(), 'report_estate' . '.xlsx');

});
Route::get('/get/v', function (Request $request) {
    //  dispatch(new \App\Jobs\FillStateCityEstateJob());
    echo '52';
});

Route::get('/get_ejar_city_import', function (Request $request) {

    $region = \App\Models\v3\Region::orderBy('ejar_id', 'asc')->get();
    foreach ($region as $r) {
        $id = $r->ejar_id;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://integration-test.housingapps.sa/sakani-queries-service/search/v1/cities?filter%5Bregion_id%5D=' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        $data = $response->data;
        foreach ($data as $d) {
            $ejar_city = \App\Models\v4\EjarCities::create([
                'ejar_id' => $d->id,
                'name_ar' => $d->attributes->name_ar,
                'name_en' => $d->attributes->name_en,
                'province_id' => $d->attributes->province_id,
                'redf_code' => $d->attributes->redf_code,
                'region_id' => $d->attributes->region_id,
                'lat' => $d->attributes->lat,
                'lon' => $d->attributes->lon,
            ]);
        }
    }
    return 'done';
});

Route::get('/get_ejar_districts_import', function (Request $request) {

    $city = \App\Models\v4\EjarCities::where('ejar_id', '>=', 4000)->where('ejar_id', '<', 4500)->orderBy('ejar_id', 'asc')->get();
    foreach ($city as $s) {
        $id = $s->ejar_id;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://integration-test.housingapps.sa/sakani-queries-service/search/v1/districts?filter%5Bcity_id%5D=' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        $data = @$response->data;
        if ($data != null && count($data) > 0) {
            foreach ($data as $d) {
                $ejar_city = \App\Models\v4\EjarDistricts::create([
                    'ejar_id' => $d->id,
                    'name_ar' => $d->attributes->name_ar,
                    'name_en' => $d->attributes->name_en,
                    'city_id' => $d->attributes->city_id,
                    'lat' => $d->attributes->lat,
                    'lon' => $d->attributes->lon,
                ]);
            }
        }
    }


    return 'done';
});

Route::get('/user/data/check', 'v1\Api\SettingController@userDataCheck')->name('userDataCheck');


Route::get('/count/refresh', function (Request $request) {


    dispatch(new \App\Jobs\CounterCreator());
    dispatch(new \App\Jobs\CounterCreatorCity());

    return 'count is updated';
});
Route::get('/fill/state/to/estate', function (Request $request) {

    dd(444);

    dispatch(new \App\Jobs\FillStateCityEstateJob());


    return 'count is updated';
});

Route::group(['middleware' => 'cors'], function () {

    Route::prefix('/dashboard')->group(function () {


        Route::get('/export_fund', 'DashBoard\Fund\ExprotController@export_fund')->name('admin.settings.export_fund');


        Route::get('/get/v', function (Request $request) {
            echo '16';
        });

        Route::post('login', 'Auth\LoginController@login');
        Route::get('/operation/type', 'DashBoard\SettingController@OprationType');
        Route::get('/comfort', 'DashBoard\SettingController@comfort');
        Route::get('estate/type', 'DashBoard\SettingController@estate_type');
        Route::get('member/type', 'DashBoard\SettingController@member_type');
        Route::get('experiences/type', 'DashBoard\SettingController@experiences_type');
        Route::get('course/type', 'DashBoard\SettingController@course_type');
        Route::get('service/type', 'DashBoard\SettingController@service_type');
        Route::get('estate/area', 'DashBoard\SettingController@estate_area');
        Route::get('estate/price', 'DashBoard\SettingController@estate_price');
        Route::get('estate/dir', 'DashBoard\SettingController@estate_dir');
        Route::get('cities', 'DashBoard\SettingController@cities');
        Route::get('state', 'DashBoard\SettingController@state');
        Route::get('getDashMap', 'DashBoard\SettingController@getDashMap');
        Route::get('getDashYear', 'DashBoard\SettingController@getDashYear');
        Route::get('getMeanSideBar', 'DashBoard\SettingController@getMeanSideBar');
        Route::get('getMeanSideBarUser', 'DashBoard\SettingController@getMeanSideBarUser');

        Route::group(['middleware' => ['admin', 'local']], function () {
            Route::get('dashboard/details', 'DashBoard\SettingController@dashboard');
            Route::get('estate_request_location', 'DashBoard\SettingController@estate_request_location');
            Route::get('fund_request_show/{id}', 'DashBoard\SettingController@fund_request_show');
            Route::get('app_request_show/{id}', 'DashBoard\SettingController@app_request_show');
            Route::get('offices/details', 'DashBoard\SettingController@offices');
            Route::get('estate_request_preview', 'DashBoard\SettingController@estate_request_preview');
            Route::get('estate_request_rate', 'DashBoard\SettingController@estate_request_rate');
            Route::get('estate_request_rate/{id}', 'DashBoard\SettingController@estate_request_rate_details');
            Route::post('/estate/{id}/update', 'DashBoard\SettingController@updateEstate');
            Route::get('/funding_request', 'DashBoard\SettingController@funding_request');


            //offices
            Route::post('/offices/{id}/update', 'DashBoard\OfficesController@update');
            Route::get('/offices/{id}', 'DashBoard\OfficesController@show');


            //region
            Route::get('/region', 'DashBoard\RegionController@index');

            //cities
            Route::get('/all_cities', 'DashBoard\CitiesController@index');
            Route::get('/cities/{id}', 'DashBoard\CitiesController@show');
            Route::post('/cities', 'DashBoard\CitiesController@store');
            Route::post('/cities/{id}/update', 'DashBoard\CitiesController@update');
            Route::post('/cities/{id}/delete', 'DashBoard\CitiesController@destroy');

            //news
            Route::get('/news', 'DashBoard\NewsController@index');
            Route::get('/news/{id}', 'DashBoard\NewsController@show');
            Route::post('/news', 'DashBoard\NewsController@store');
            Route::post('/news/{id}/update', 'DashBoard\NewsController@update');
            Route::post('/news/{id}/delete', 'DashBoard\NewsController@destroy');

            //districts
            Route::get('/all_districts', 'DashBoard\DistrictController@index');
            Route::get('/districts/{id}', 'DashBoard\DistrictController@show');
            Route::post('/districts', 'DashBoard\DistrictController@store');
            Route::post('/districts/{id}/update', 'DashBoard\DistrictController@update');
            Route::post('/districts/{id}/delete', 'DashBoard\DistrictController@destroy');

            //platform plans
            Route::get('/platform_plans', 'DashBoard\PlatformPlansController@index');
            Route::post('/platform_plans', 'DashBoard\PlatformPlansController@store');
            Route::post('/platform_plans/{id}/update', 'DashBoard\PlatformPlansController@update');
            Route::post('/platform_plans/{id}/delete', 'DashBoard\PlatformPlansController@destroy');


            // show subscription

            Route::get('/subscription', 'DashBoard\PlatformSubscrtptionController@index');
        });

        Route::get('neighborhood/{id}', 'DashBoard\SettingController@neighborhood');
        Route::get('plans', 'DashBoard\SettingController@plans');
        Route::get('plan/{id}/show', 'DashBoard\SettingController@showPlan');

        Route::group(['middleware' => ['admin', 'local'], 'prefix' => 'fund'], function () {


            Route::post('cancel/{id}/fund/offer', 'DashBoard\Fund\AuthController@cancel_fund_offer');
            Route::get('offer/date/data', 'DashBoard\Fund\AuthController@offer_date_data');
            Route::get('provider/date/data', 'DashBoard\Fund\AuthController@provider_data');
            Route::get('provider/attchment/data', 'DashBoard\Fund\AuthController@provider_attchment_data');
            Route::post('send/sms', 'DashBoard\Fund\AuthController@sendSms');
            Route::post('accepte/estate', 'DashBoard\Fund\AuthController@accepte_estate');


            Route::get('profile', 'DashBoard\Fund\AuthController@show');
            Route::post('/update/my/profile', 'DashBoard\Fund\AuthController@update');
            Route::post('/forget/password', 'DashBoard\Fund\AuthController@forgetPassword');
            Route::post('/confirm/password/code', 'DashBoard\Fund\AuthController@ResetToken');
            Route::post('/reset/password', 'DashBoard\Fund\AuthController@updatePasswordByPhone');
            Route::get('logout', 'DashBoard\Fund\AuthController@logout');
            Route::get('dashboard', 'DashBoard\Fund\SettingController@index');
            Route::get('unaccepted/estates', 'DashBoard\SettingController@UnacceptedEstates');


            Route::get('requests', 'DashBoard\Fund\RequestController@index');
            Route::get('deleted/requests', 'DashBoard\Fund\RequestController@deltedindex');
            Route::get('request/{id}/show', 'DashBoard\Fund\RequestController@singleRequest');
            Route::get('offer/{id}/show', 'DashBoard\Fund\RequestController@singleOffer');
            Route::post('reject/{id}/offer', 'DashBoard\Fund\RequestController@rejectOffer');


            Route::get('provider/{id}/show', 'DashBoard\Fund\RequestController@singleProvider');


            Route::get('offers', 'DashBoard\Fund\RequestController@offers');


            Route::get('available/estate/offers/{uuid}', 'DashBoard\Fund\RequestController@availableOffers');
            Route::get('availableOfferstest', 'DashBoard\Fund\RequestController@availableOfferstest');
            Route::get('available/estate/list/', 'DashBoard\Fund\RequestController@availableEstate');
            Route::get('available/fund/request/{id}', 'DashBoard\Fund\RequestController@availableFundRequest');
            Route::get('estate/{id}/show', 'DashBoard\Fund\RequestController@singleEstate');
            Route::post('send/offer/fund/dash', 'DashBoard\Fund\RequestController@send_offer_fund_dash');
            Route::get('providers', 'DashBoard\Fund\ProviderController@index');

            //////////////////////banks


            Route::get('finance/requests', 'DashBoard\Bank\FinanceRequestController@index');
            Route::post('finance/update/status', 'DashBoard\Bank\FinanceRequestController@updateStatus');


            Route::get('deferred/installments/requests', 'DashBoard\Bank\DeferredInstallmentsRequestController@index');
            Route::post('deferred/installments/update/status', 'DashBoard\Bank\DeferredInstallmentsRequestController@updateStatus');
            Route::post('deferred/installments/add/comment', 'DashBoard\Bank\DeferredInstallmentsRequestController@addComment');
            Route::post('deferred/installments/display/in/app', 'DashBoard\Bank\DeferredInstallmentsRequestController@displayInApp');


            Route::get('finance/{id}/show', 'DashBoard\Bank\FinanceRequestController@singleFinance');
            Route::get('deferred/installments/{id}/show', 'DashBoard\Bank\DeferredInstallmentsRequestController@singleDeferredOffer');
///////////////////////click up


            Route::get('clickup/settings', 'DashBoard\Fund\ClickUpController@settings');
            Route::get('clickup/show/{id}/fund/request', 'DashBoard\Fund\ClickUpController@show_fund_request');

            Route::get('clickup/preview/requests', 'DashBoard\Fund\ClickUpController@preview_requests');


            Route::post('clickup/preview/requests/add/contact/stage', 'DashBoard\Fund\ClickUpController@preview_fund_rquest_add_contact_stage');
            Route::post('clickup/preview/requests/add/preview/stage', 'DashBoard\Fund\ClickUpController@preview_fund_rquest_add_preview_stage');
            Route::post('clickup/preview/requests/add/field/preview/stage', 'DashBoard\Fund\ClickUpController@preview_fund_rquest_add_field_preview_stage');
            Route::post('clickup/preview/requests/add/finance/stage', 'DashBoard\Fund\ClickUpController@preview_fund_rquest_add_finance_stage');
            Route::post('clickup/emp/assigned', 'DashBoard\Fund\ClickUpController@emp_assigned');
            Route::get('clickup/get/emp', 'DashBoard\Fund\ClickUpController@get_emp');
            Route::get('clickup/preview/fund/offer/show', 'DashBoard\Fund\ClickUpController@preview_fund_offer_show');
            Route::get('/reports', 'DashBoard\SettingController@reports');
            Route::get('/contacts', 'DashBoard\SettingController@contacts');
            Route::get('/closed/estate', 'DashBoard\SettingController@ClosedEstate');
            Route::get('/deleted/estate', 'DashBoard\SettingController@DeletedEstate');
            Route::post('/add/estate', 'DashBoard\SettingController@addEstate');
            Route::post('/delete/estate', 'DashBoard\SettingController@deleteEstate');
            Route::post('/response/contact', 'DashBoard\SettingController@response_contact');

        });
        Route::group(['middleware' => ['admin'], 'prefix' => 'bank'], function () {
            Route::get('profile', 'DashBoard\Bank\AuthController@show');
            Route::post('/update/my/profile', 'DashBoard\Bank\AuthController@update');
            Route::post('/forget/password', 'DashBoard\Bank\AuthController@forgetPassword');
            Route::post('/confirm/password/code', 'DashBoard\Bank\AuthController@ResetToken');
            Route::post('/reset/password', 'DashBoard\Bank\AuthController@updatePasswordByPhone');
            Route::get('logout', 'DashBoard\Bank\AuthController@logout');
            Route::get('dashboard', 'DashBoard\Bank\SettingController@index');


            /*  Route::get('finance/requests', 'DashBoard\Bank\FinanceRequestController@index');
              Route::post('finance/update/status', 'DashBoard\Bank\FinanceRequestController@updateStatus');


              Route::get('deferred/installments/requests', 'DashBoard\Bank\DeferredInstallmentsRequestController@index');
              Route::post('deferred/installments/update/status',
                  'DashBoard\Bank\DeferredInstallmentsRequestController@updateStatus');


              Route::get('finance/{id}/show', 'DashBoard\Bank\FinanceRequestController@singleFinance');
              Route::get('deferred/installments/{id}/show', 'DashBoard\Bank\DeferredInstallmentsRequestController@singleDeferredOffer');*/
        });


        Route::group(['middleware' => ['admin'], 'prefix' => 'admin'], function () {


            Route::get('/all/tickets', 'DashBoard\Admin\TicketController@AllTicket');
            Route::get('/all/reports', 'DashBoard\Admin\TicketController@AllReports');
            Route::get('/ticket/{id}/show', 'DashBoard\Admin\TicketController@TicketShow');
            Route::post('/replay/{id}/ticket', 'DashBoard\Admin\TicketController@ReplayTicket');
            Route::post('/delete/{id}/ticket', 'DashBoard\Admin\TicketController@DeleteTicket');


            Route::get('profile', 'DashBoard\Admin\AuthController@show');

            ///admin and role system
            Route::get('permissions', 'DashBoard\Admin\UserController@permissions');
            Route::post('add/permissions/admin', 'DashBoard\Admin\UserController@add_permissions');
            Route::post('create/admin', 'DashBoard\Admin\UserController@create_admin');
            Route::post('update/admin', 'DashBoard\Admin\UserController@update_admin');
            Route::post('/update_password', 'DashBoard\Admin\UserController@update_password');

            Route::get('admins', 'DashBoard\Admin\UserController@admins');

            //////////////////


            Route::get('settings', 'DashBoard\Admin\AuthController@settings');
            Route::post('change/days/beta', 'DashBoard\Admin\AuthController@changeDaysBeta');
            Route::post('/update/my/profile', 'DashBoard\Admin\AuthController@update');
            Route::post('/forget/password', 'DashBoard\Admin\AuthController@forgetPassword');
            Route::post('/confirm/password/code', 'DashBoard\Admin\AuthController@ResetToken');
            Route::post('/reset/password', 'DashBoard\Admin\AuthController@updatePasswordByPhone');
            Route::get('logout', 'DashBoard\Admin\AuthController@logout');
            Route::get('users', 'DashBoard\Admin\UserController@index');
            Route::get('user/{id}/show', 'DashBoard\Admin\UserController@singleUser');
            Route::post('user/create/beta', 'DashBoard\Admin\UserController@createBetaUser');
            Route::post('user/create/plan', 'DashBoard\Admin\UserController@chooseUserPlan');
            Route::post('user/upgrade', 'DashBoard\Admin\UserController@userUpgrade');
            Route::post('user/update/status', 'DashBoard\Admin\UserController@updateStatus');
            Route::post('user/update/certified', 'DashBoard\Admin\UserController@updateCertified');
            Route::post('user/update', 'DashBoard\Admin\UserController@update');
            Route::post('send/notification', 'DashBoard\SettingController@send_push');


        });
    });


    Route::prefix('/platform')->middleware('local')->group(function () {

        //  dd(base_path(env('WKHTML_PDF_BINARY', '/usr/local/bin/wkhtmltopdf')));
        Route::get('/clear', function () {

            $exitCode = \Illuminate\Support\Facades\Artisan::call('optimize');
            $exitCode = \Illuminate\Support\Facades\Artisan::call('view:clear');
            $exitCode = \Illuminate\Support\Facades\Artisan::call('route:clear');
            $exitCode = \Illuminate\Support\Facades\Artisan::call('config:clear');

        });


        Route::get('/report2', "Platform\ReportController@test");
        Route::get('/getExample4', "Platform\ReportController@getExample4");
//dd(\HTML::style('js/daterangepicker/daterangepicker-bs3.css'));
        Route::post('login', 'Platform\AuthController@loginNew');
        //  Route::get('news', 'Platform\AuthController@getNews');
        Route::get('/comfort', 'Platform\SettingController@comfort');
        Route::get('/estate/type', 'Platform\SettingController@EstateType');
        Route::get('/operation/type', 'Platform\SettingController@OprationType');
        Route::get('/banks', 'Platform\SettingController@banks');
        Route::get('/get_age', 'Platform\SettingController@get_age');
        Route::get('/check/point', 'Platform\SettingController@checkPint');
        Route::get('/plan', 'Platform\PlanController@plan');
        Route::get('/redirect_payment', 'Platform\PlanController@redirect_payment');

        Route::post('verify', 'Platform\AuthController@verifyNew');
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/emp/select', 'Platform\SettingController@empsSelect');
            Route::get('/dashboard', 'Platform\SettingController@dashboard');
            Route::get('/client/select', 'Platform\SettingController@clientSelect');
            Route::get('/estate/select', 'Platform\SettingController@estateSelect');
            Route::get('/contract/exprensess/select', 'Platform\SettingController@contractNotPaidExprensessSelect');
            Route::get('/estate/exprensess/select', 'Platform\SettingController@estateNotPaidExprensessSelect');
            Route::get('/contract/invoices/select', 'Platform\SettingController@contractNotPaidInvoiceSelect');
            Route::get('/contract/select', 'Platform\SettingController@contractSelect');
            Route::get('/group/select', 'Platform\SettingController@groupSelect');
            Route::post('/check/employee', 'Platform\SettingController@checkEmployee');


            Route::get('profile', 'Platform\AuthController@show');
            Route::get('subscription_details', 'Platform\AuthController@subscription_details');
            Route::post('/update/my/profile', 'Platform\AuthController@update');
            Route::post('/update_zatca_info', 'Platform\AuthController@update_zatca');
            Route::post('/forget/password', 'Platform\AuthController@forgetPassword');
            Route::post('/confirm/password/code', 'Platform\AuthController@ResetToken');
            Route::post('/reset/password', 'Platform\AuthController@updatePasswordByPhone');
            Route::get('logout', 'Platform\AuthController@logout');
            Route::post('/update/password', 'Platform\AuthController@updatePasswordByPhone2');
            Route::post('profile/image', 'Platform\AuthController@uploadAvatar');
            Route::post('resend/code/password', 'Platform\AuthController@resendforgetPassword');

            Route::post('/check_plan', 'Platform\PlanController@check_plan');
            Route::post('/subscription', 'Platform\PlanController@subscription');
            Route::get('/my_subscription', 'Platform\PlanController@my_subscription');


            Route::get('/my/estate', 'Platform\EstateController@myEstate');
            Route::post('/add/estate', 'Platform\EstateController@addEstate');
            Route::post('/update/{id}/estate', 'Platform\EstateController@UpdateEstate');//طلب  انشاء غقار
            Route::post('/estate/{id}/status', 'Platform\EstateController@EstateStatus');//طلب  انشاء غقار
            Route::post('/delete/{id}/estate', 'Platform\EstateController@DeleteEstate');//طلب  انشاء غقار
            Route::get('/single/{id}/estate', 'Platform\EstateController@single_estate');
            Route::post('/addImg/estate', 'Platform\EstateController@addImgEstate');
            Route::post('/deleteImg/estate', 'Platform\EstateController@deleteImgEstate');

///estate exprenses///////////////////////////////////////////////////
            Route::post('/add/estate/expense', 'Platform\EstateController@addEstateExpenses');
            Route::post('/update/{id}/estate/expense', 'Platform\EstateController@updateEstateExpenses');
            Route::post('/delete/{id}/estate/expense', 'Platform\EstateController@delete_estate_expense');
            Route::get('/single/{id}/estate/expense', 'Platform\EstateController@single_estate_expense');
            Route::get('/all/estate/expense', 'Platform\EstateController@allEstateExpenses');
///////////////////////////////////////////////////////////////////////////

            ///estate notes///////////////////////////////////////////////////
            Route::post('/add/estate/notes', 'Platform\EstateController@addEstateNotes');
            Route::post('/update/{id}/estate/notes', 'Platform\EstateController@updateEstateNotes');
            Route::post('/delete/{id}/estate/notes', 'Platform\EstateController@delete_estate_notes');
            Route::get('/single/{id}/estate/notes', 'Platform\EstateController@single_estate_notes');
            Route::get('/all/estate/notes', 'Platform\EstateController@allEstateNotes');
///////////////////////////////////////////////////////////////////////////

            ////////////////////////// groub estate
            Route::get('/all/estate/group', 'Platform\EstateController@allEstateGroup');
            Route::get('/single/{id}/group/estate', 'Platform\EstateController@single_group_estate');
            Route::post('/add/estate/group', 'Platform\EstateController@addEstateGroup');
            Route::post('/update/{id}/estate/group', 'Platform\EstateController@updateGroup');
            Route::post('/delete/{id}/estate/group', 'Platform\EstateController@deleteGroup');

            //////////////////////////////////////////////////////////////////


            /////estate group notes
            ///estate notes///////////////////////////////////////////////////
            Route::post('/add/estate/group/notes', 'Platform\EstateController@addEstateGroupNotes');
            Route::post('/update/{id}/estate/group/notes', 'Platform\EstateController@updateEstateGroupNotes');
            Route::post('/delete/{id}/estate/group/notes', 'Platform\EstateController@delete_estate_group_notes');
            Route::get('/single/{id}/estate/group/notes', 'Platform\EstateController@single_estate_group_notes');
            Route::get('/all/estate/group/notes', 'Platform\EstateController@allEstateGroupNotes');
///////////////////////////////////////////////////////////////////////////


            ///////////////////////////////////////////tenants
            Route::get('/all/tenants/paying/users', 'Platform\EstateController@AllTentPayUsers');
            Route::get('/tenants/paying/users/list', 'Platform\EstateController@AllTentPayUsersList');
            Route::post('/add/tenants/paying/users', 'Platform\EstateController@addTentPayUsers');
            Route::post('/update/{id}/tenants/paying/users', 'Platform\EstateController@updateTentPayUsers');
            Route::post('/delete/{id}/tenants/paying/users', 'Platform\EstateController@DeleteTentPayUsers');
            Route::get('/single/{id}/tenants/paying/users', 'Platform\EstateController@SingleTentPayUsers');

////////////////////////////////////////////////////////////////////

            ///////////////////////////////////////////tenants notes
            Route::post('/add/tenants/paying/user/notes', 'Platform\EstateController@addTentPayUserNotes');
            Route::post('/update/{id}/tenants/paying/user/notes', 'Platform\EstateController@updateTentPayUserNotes');
            Route::post('/delete/{id}/tenants/paying/user/notes', 'Platform\EstateController@DeleteTentPayUserNotes');
            Route::get('/single/{id}/tenants/paying/user/notes', 'Platform\EstateController@SingleTentPayUserNotes');
            Route::get('/all/tenants/paying/user/notes', 'Platform\EstateController@allTentPayUserNotes');


            ////////////////////////////////////////////////

            ///////////////////////////////////////////tenants contract
            Route::post('/add/rent/contract', 'Platform\TentController@addTentContract');
            Route::post('/update/{id}/rent/contract', 'Platform\TentController@updateTentContract');
            Route::post('/delete/{id}/rent/contract', 'Platform\TentController@DeleteTentContract');
            Route::get('/single/{id}/rent/contract', 'Platform\TentController@SingleTentContract');
            Route::get('/all/rent/contract', 'Platform\TentController@allTentContract');
            Route::post('/add/rent/contract', 'Platform\TentController@addTentContract');


            //ejar
            Route::get('ejar/get_properties', 'v4\Api\EjarController@get_properties');
            Route::get('ejar/get_unity', 'v4\Api\EjarController@get_unity');
            Route::post('ejar/add_properties', 'v4\Api\EjarController@add_properties');
            Route::post('ejar/add_unit', 'v4\Api\EjarController@add_unit');
            Route::post('ejar/add_contract', 'v4\Api\EjarController@add_contract');
            Route::post('ejar/add_property_and_unit_contract/{contract_id}', 'v4\Api\EjarController@add_property_and_unit_contract');
            Route::post('ejar/select_parties_contract/{contract_id}', 'v4\Api\EjarController@select_parties_contract');
            Route::post('ejar/financial_information_contract/{contract_id}', 'v4\Api\EjarController@financial_information_contract');
            Route::post('ejar/contract_unit_services_contract/{contract_id}', 'v4\Api\EjarController@contract_unit_services_contract');
            Route::post('ejar/rental_fee_contract/{contract_id}', 'v4\Api\EjarController@rental_fee_contract');
            Route::post('ejar/update_fee_contract/{contract_id}', 'v4\Api\EjarController@update_fee_contract');
            Route::post('ejar/contract_terms_contract/{contract_id}', 'v4\Api\EjarController@contract_terms_contract');
            Route::post('ejar/submit_contract/{contract_id}', 'v4\Api\EjarController@submit_contract');
            Route::post('ejar/contracts', 'v4\Api\EjarController@ejar_contracts');
            Route::post('ejar/contract_status/{contract_id}', 'v4\Api\EjarController@ejar_contract_status');
            Route::post('ejar/contract_delete/{contract_id}', 'v4\Api\EjarController@ejar_contract_delete');
            Route::get('ejar/get_ejar_data', 'v4\Api\EjarController@get_ejar_data');
            Route::get('ejar/download/{contract_id}', 'v4\Api\EjarController@download');


            Route::post('/entity_endpoints', 'Platform\EjarController@entity_endpoints');
            Route::post('/individual_entities/find_or_create', 'Platform\EjarController@individual_entities');
            Route::post('/organization_entities/get_organization', 'Platform\EjarController@organization_entities');


            Route::post('/add/rent/contract/invoice', 'Platform\TentController@addTentContractInvoice');
            Route::post('/collect/rent/contract/invoice', 'Platform\TentController@CollectContractInvoice');
            Route::post('/delete/{id}/rent/contract/invoice', 'Platform\TentController@DeleteTentContractInvoice');

            ////////////////////////////////////////////////

            ///rent contract notes///////////////////////////////////////////////////
            Route::post('/add/rent/contract/notes', 'Platform\TentController@addTentContractNotes');
            Route::post('/update/{id}/rent/contract/notes', 'Platform\TentController@updateTentContractNotes');
            Route::post('/delete/{id}/rent/contract/notes', 'Platform\TentController@DeleteTentContractNotes');
            Route::get('/single/{id}/rent/contract/notes', 'Platform\TentController@SingleTentContractNotes');
            Route::get('/all/rent/contract/notes', 'Platform\TentController@allTentContractNotes');
///////////////////////////////////////////////////////////////////////////


            ///////////////////////////////////////////payer contract
            Route::post('/add/payer/contract', 'Platform\TentController@addPayerContract');
            Route::post('/update/{id}/payer/contract', 'Platform\TentController@updatePayerContract');
            Route::post('/delete/{id}/payer/contract', 'Platform\TentController@DeletePayerContract');
            Route::get('/single/{id}/payer/contract', 'Platform\TentController@SinglePayerContract');
            Route::get('/all/payer/contract', 'Platform\TentController@allPayerContract');


            ///pay contract notes///////////////////////////////////////////////////
            Route::post('/add/pay/contract/notes', 'Platform\TentController@addPayContractNotes');
            Route::post('/update/{id}/pay/contract/notes', 'Platform\TentController@updatePayContractNotes');
            Route::post('/delete/{id}/pay/contract/notes', 'Platform\TentController@DeletePayContractNotes');
            Route::get('/single/{id}/pay/contract/notes', 'Platform\TentController@SinglePayContractNotes');
            Route::get('/all/pay/contract/notes', 'Platform\TentController@allPayContractNotes');
///////////////////////////////////////////////////////////////////////////

////emp system
            Route::post('/add/employee', 'Platform\AuthController@storeEmployee');
            //  Route::post('/add/employee', 'Platform\AuthController@login');
            Route::post('/delete/employee', 'Platform\AuthController@deleteEmployee');
            Route::get('/my/employee', 'Platform\AuthController@my_employee');
/////////////////////////////////////////////////

            /////FinancialBond
            ///Catch Bond///////////////////               ////////////////////////////////
            Route::post('/add/catch/bond', 'Platform\BondController@addCatchBond');
            Route::post('/update/{id}/catch/bond', 'Platform\BondController@updateCatchBond');
            Route::post('/delete/{id}/catch/bond', 'Platform\BondController@DeleteCatchBond');
            Route::get('/single/{id}/catch/bond', 'Platform\BondController@SingleCatchBond');
            Route::get('/all/catch/bond', 'Platform\BondController@allCatchBond');
///////////////////////////////////////////////////////////////////////////

            Route::get('/all/financial/movements', 'Platform\EstateController@financial_movements');
            Route::get('/all/account/statement', 'Platform\EstateController@account_statement');

////////////////////report system
            Route::get('/owner/management/report', 'Platform\ReportController@Ownermanagement');
            Route::get('/rent_invoices/{id}', 'Platform\ReportController@rent_invoices');
            Route::get('/tenant/dues/report', 'Platform\ReportController@TenantsDues');
            Route::get('/financialBonds/bonds/report', 'Platform\ReportController@FinancialBonds');

            Route::prefix('/ejar')->group(function () {
                Route::get('/regions/', 'v4\Api\EjarController@regions');
                Route::get('/region/{id}/cities', 'v4\Api\EjarController@cities');
                Route::get('/cities/{id}/districts', 'v4\Api\EjarController@districts');

            });

            Route::get('estate_request_preview', 'Platform\EstateController@estate_request_preview');
            Route::get('estate_request_preview/{id}', 'Platform\EstateController@estate_request_preview_show');
            Route::get('estate_request_rate', 'Platform\RateRequestController@estate_request_rate');
            Route::get('estate_request_rate_show/{id}', 'Platform\RateRequestController@estate_request_rate_show');
            Route::post('send_estate_request_rate', 'Platform\RateRequestController@send_estate_request_rate');


            //market demands
            Route::get('/market/demands', 'v4\Api\EstateController@demandsRequest');
            Route::post('/estate/Request', 'v4\Api\RealEstateController@addRequestEstate');
            Route::post('/send/offer/app/status', 'v4\Api\EstateController@send_offer_app_status');
            Route::post('/send/offer/Request', 'v4\Api\EstateController@send_offer');


            // filter all system estate
            Route::get('/home/estate/custom/list', 'v4\Api\EstateController@homeCustomAqarz');
            Route::get('/home/estate/custom/list/count', 'v4\Api\EstateController@homeCustomAqarzNumber');

            //request preview
            Route::post('/request_preview/{id}', 'v4\Api\EstateController@request_preview');
            Route::post('request_preview/{id}/times', 'v4\Api\EstateController@request_preview_times');
            Route::post('request_preview/{id}/times_update', 'v4\Api\EstateController@request_preview_times_update');
            Route::post('request_preview/{id}/complete', 'v4\Api\EstateController@request_preview_complete');
            Route::post('request_preview/{id}/delete', 'v4\Api\EstateController@request_preview_delete');


            //rate request

            Route::post('/rate/Request', 'v4\Api\RealEstateController@addRateRequest');
            Route::get('/show_rate_Request', 'v4\Api\RealEstateController@show_rate_Request');
            Route::get('/show_rate_Request/{id}', 'v4\Api\RealEstateController@show_rate_Request_details');
            Route::post('/offer_rate_Request_change_status', 'v4\Api\RealEstateController@offer_rate_Request_change_status');
            Route::post('/delete_rate_Request/{id}', 'v4\Api\RealEstateController@delete_rate_Request');

            //show order
            Route::get('/show_order', 'v4\Api\EstateController@show_order');
            Route::get('/my/order', 'v4\Api\AuthController@myOrder');

            //show offer
            Route::get('/my/offer', 'v4\Api\AuthController@myOffer');

            //msg system
            Route::get('/my/msg/{id?}', 'v4\Api\AuthController@myMsg');
            Route::get('/msg/{id}/det', 'v4\Api\AuthController@MsgDet');
            Route::post('/send/msg', 'v4\Api\AuthController@sendMsg');

            //favorite
            Route::get('/my/favorite', 'v4\Api\AuthController@favorite');
            Route::post('/add/delete/favorite', 'v4\Api\AuthController@storeFavoriteStatus');

            //notification
            Route::get('/notification', 'v4\Api\AuthController@notification');

            //save_search
            Route::post('/save_search', 'v4\Api\AuthController@save_search');
            Route::post('/delete_search', 'v4\Api\AuthController@delete_search');
            Route::get('/search', 'v4\Api\AuthController@search');


        });
    });

    Route::get('/estate/daily', function () {

        //dd(444);

        // dd( Carbon::now()->subDays(4)->format('Y-m-d'));
        /*  $test=  \App\Models\v3\Estate::where('id','>',0)
              ->whereDate('created_at', '=', date('Y-m-d'))->get();

          dd($test);*/
        /*  dispatch(new \App\Jobs\CountRealFundOffer())->onConnection('sync');

        dd(444);*/
        /*$test=  \App\Models\v3\Estate::first();

        return($test);*/
        dispatch(new \App\Jobs\EstateDaliy())->onConnection('sync');
    });
    Route::post('add/estate/site', 'DashBoard\SettingController@addEstate');//طلب  انشاء غقار
});

Route::post('test/upload', 'DashBoard\SettingController@uploadimg');//طلب  انشاء غقار

$v_number = app('request')->header('v') ? app('request')->header('v') : 'v4';


Route::group(['middleware' => ['local'], 'namespace' => $v_number], function () {


    Route::get('/check/location/data', 'Api\SettingController@check_location');

    Route::get('/send/sms', 'Api\SettingController@sendSms');
    Route::get('/title/global/cities', 'Api\SettingController@GolobalCites');
    Route::get('/title/global/{id}/neb', 'Api\SettingController@GolobalNeb');
    Route::get('/title/global/neb', 'Api\SettingController@AllGolobalNeb');
    Route::get('/GolobalCitesWithNeb', 'Api\SettingController@GolobalCitesWithNeb');
    Route::get('/get_age', 'Api\SettingController@get_age');


    Route::get('/website_home', 'Api\SettingController@website_home');


    Route::get('/news', 'Api\NewsController@index');
    Route::get('/news/{id}', 'Api\NewsController@show');
    Route::post('/news/{id}/addComment', 'Api\NewsController@addComment');


    Route::post('/add/neb/interest', 'Api\SettingController@Addinterest');
    Route::post('/remove/neb/interest', 'Api\SettingController@Removeinterest');
    Route::get('/my/interest', 'Api\SettingController@myInterest');


    Route::get('/check/location', 'Api\SettingController@checkLocation');
    Route::post('/check/employee', 'Api\SettingController@checkEmployee');

    Route::get('/code/check', 'Api\SettingController@CodeCheck');
    //   Route::post('/code/check/new', 'Api\SettingController@NewCodeCheck');
    Route::get('/code/send', 'Api\SettingController@SendCode');
    Route::get('/sms/send', 'Api\SettingController@SmsCode');
    Route::post('provider/code/send', 'Api\EstateController@send_customer_code');
    Route::post('resend/code/send', 'Api\EstateController@resendSendCode');
    Route::get('/user/{id}', 'Api\AuthController@user');
    Route::get('search/user/{q?}', 'Api\AuthController@getUserByName');
    Route::get('/user/byusername/{name}', 'Api\AuthController@userByName');
    Route::get('/providers/{q?}', 'Api\AuthController@getProvidersByName');
    Route::get('/best/provider', 'Api\SettingController@bestProvider');

    Route::get('/operation/type', 'Api\SettingController@OprationType');
    Route::get('/estate/area/range', 'Api\SettingController@EstateAreaRange');
    Route::get('/estate/price/range', 'Api\SettingController@EstatePriceRange');
    Route::get('/estate/street/view/range', 'Api\SettingController@EstateStreetViewRange');
    Route::get('/estate/type', 'Api\SettingController@EstateType');
    Route::get('/banks', 'Api\SettingController@banks');
    Route::get('/country', 'Api\SettingController@countries');
    Route::get('/cities/', 'Api\SettingController@cities');
    Route::get('cities_page', 'Api\SettingController@cities_page');
    Route::get('all_cities_neighborhoods', 'Api\SettingController@all_cities_neighborhoods');

    Route::get('cities/with/neb', 'Api\SettingController@citis_with_neb');

    Route::get('/regions/', 'Api\SettingController@regions');
    Route::get('/neighborhoods/{id}/list', 'Api\SettingController@neighborhoods');
    Route::get('/neighborhoods', 'Api\SettingController@neighborhoods_all');
    Route::get('/settings', 'Api\SettingController@settings');
    Route::get('pages', 'Api\SettingController@pages');

    Route::get('/comfort', 'Api\SettingController@comfort');
    Route::get('/rate_request_type', 'Api\SettingController@rate_request_type');

    Route::post('/register', 'Api\AuthController@store');
    Route::post('login', 'Api\AuthController@loginNew');
    Route::post('delete/my/account', 'Api\AuthController@DeleteMyAccount');
    Route::post('active/my/account', 'Api\AuthController@RestoreMyAccount');
    Route::post('restore/active/my/account', 'Api\AuthController@RequestRestoreMyAccount');
    Route::post('verify', 'Api\AuthController@verify');
    Route::post('verifyNew', 'Api\AuthController@verifyNew');
    Route::get('logout', 'Api\AuthController@logout');
    Route::post('logout', 'Api\AuthController@logout');

    Route::post('/forget/password', 'Api\AuthController@forgetPassword');
    Route::post('/confirm/password/code', 'Api\AuthController@ResetToken');
    Route::post('/reset/password', 'Api\AuthController@updatePasswordByPhone');


    Route::post('/contact/us', 'Api\SettingController@contact');
    Route::get('/check/point', 'Api\SettingController@checkPint');

    //  $location = '28.484647285978' . ' ' . '36.465996682962';
    // $dis = checkPint("$location");
    Route::get('/bloggers', 'Api\BloggerController@bloggers');
    Route::get('/blogger/{id}/show', 'Api\BloggerController@blogger_show');
    Route::post('/blogger/{id}/edit', 'Api\BloggerController@blogger_commet_edit');
    Route::post('/blogger/add/comment', 'Api\BloggerController@blogger_commet_send');
    Route::post('/blogger/remove/comment', 'Api\BloggerController@blogger_commet_remove');


    Route::group(['middleware' => 'api'], function () {
        Route::post('/funding-request', 'Api\SettingController@funding_request');


        Route::post('/report', 'Api\SettingController@sendReport');


        Route::post('/save_search_web', 'Api\AuthController@save_search_web');
        Route::post('/save_search', 'Api\AuthController@save_search');
        Route::post('/delete_search', 'Api\AuthController@delete_search');
        Route::get('/search_web', 'Api\AuthController@search_web');


        Route::post('/open/ticket', 'Api\TicketController@openTicket');
        Route::get('/my/ticket', 'Api\TicketController@myTicket');
        Route::get('/ticket/{id}/show', 'Api\TicketController@TicketShow');
        Route::post('/replay/{id}/ticket', 'Api\TicketController@ReplayTicket');
        Route::post('/delete/{id}/ticket', 'Api\TicketController@DeleteTicket');


        Route::get('/show/videos', 'Api\SettingController@showVideos');
        Route::get('/single/{id}/video', 'Api\SettingController@single_video');

        Route::post('/updateDeviceToken', 'Api\SettingController@updateDeviceToken');
        Route::get('/notification', 'Api\AuthController@notification');
        Route::get('/count/{id}/call', 'Api\SettingController@CountCall');
        Route::post('/resend/code', 'Api\AuthController@resendCodes');
        Route::get('/test/api', 'Api\SettingController@testApi');
        Route::post('/rate/offer', 'Api\EstateController@rate_offer');
        Route::post('/rate/estate', 'Api\EstateController@rate_estate');

        Route::get('/upgrade', 'Api\SettingController@Chooseplans');

        Route::post('/profile/image', 'Api\AuthController@uploadAvatar');

        Route::get('/my/profile', 'Api\AuthController@show');
        Route::get('/my/msg/{id?}', 'Api\AuthController@myMsg');
        Route::get('/msg/{id}/det', 'Api\AuthController@MsgDet');
        Route::post('/send/msg', 'Api\AuthController@sendMsg');


        Route::get('/my/favorite', 'Api\AuthController@favorite');
        Route::post('/add/delete/favorite', 'Api\AuthController@storeFavoriteStatus');


        Route::get('/my/client', 'Api\AuthController@client');
        Route::post('/add/client', 'Api\AuthController@storeClient');
        Route::post('/delete/{id}/client', 'Api\AuthController@deleteClient');

        Route::post('/add/employee', 'Api\AuthController@storeEmployee');
        Route::post('/delete/employee', 'Api\AuthController@deleteEmployee');
        Route::get('/my/employee', 'Api\AuthController@my_employee');


        Route::post('/update/my/profile', 'Api\AuthController@update');
        Route::post('/update/password', 'Api\AuthController@updatePasswordByPhone2');


        //Phone Verifications
        Route::post('mobile/verify', 'Api\AuthController@verify');
        Route::post('mobile/verification/request', 'Api\AuthController@requestOtp');


        //real estate opration
        Route::post('/deferredInstallment/Request', 'Api\RealEstateController@deferredInstallment');//طلب تقسيط مؤجل
        Route::post('/finance/Request', 'Api\RealEstateController@finance');//طلب  تمويل
        Route::post('/add/estate', 'Api\RealEstateController@addEstate');//طلب  انشاء غقار
        Route::get('/get/state', 'Api\RealEstateController@getState');//طلب  انشاء غقار


        Route::post('/delete/{id}/estate', 'Api\RealEstateController@DeleteEstate');//طلب  انشاء غقار
        Route::post('/update/{id}/estate', 'Api\RealEstateController@UpdateEstate');//طلب  انشاء غقار
        Route::post('/update/{id}/statistics', 'Api\RealEstateController@updateStatistics');//طلب  انشاء غقار
        Route::post('/make/up/{id}/estate', 'Api\RealEstateController@MakeUpEstate');//طلب  انشاء غقار


        Route::post('/addImg/planned', 'Api\RealEstateController@addImgPlanned');
        Route::post('/deleteImg/planned', 'Api\RealEstateController@deleteImgPlanned');
        Route::post('/addImg/estate', 'Api\RealEstateController@addImgEstate');
        Route::post('/deleteImg/estate', 'Api\RealEstateController@deleteImgEstate');
        Route::post('/rate/Request', 'Api\RealEstateController@addRateRequest');
        Route::get('/show_rate_Request', 'Api\RealEstateController@show_rate_Request');
        Route::get('/show_rate_Request/{id}', 'Api\RealEstateController@show_rate_Request_details');
        Route::post('/offer_rate_Request_change_status', 'Api\RealEstateController@offer_rate_Request_change_status');
        Route::post('/delete_rate_Request/{id}', 'Api\RealEstateController@delete_rate_Request');
        Route::post('/request_preview/{id}', 'Api\EstateController@request_preview');
        Route::post('request_preview/{id}/times', 'Api\EstateController@request_preview_times');
        Route::post('request_preview/{id}/times_update', 'Api\EstateController@request_preview_times_update');

        Route::post('request_preview/{id}/complete', 'Api\EstateController@request_preview_complete');
        Route::post('request_preview/{id}/delete', 'Api\EstateController@request_preview_delete');

        Route::get('/my/request', 'Api\EstateController@myRequest');//طلب  تمويل


        Route::get('/my/order', 'Api\AuthController@myOrder'); //متابعة الطلبات
        Route::get('/my/offer', 'Api\AuthController@myOffer'); //متابعة الطلبات
        Route::get('estate_request/{id}', 'Api\EstateController@estate_request_show'); //متابعة الطلبات

        Route::get('my/deferredInstallment', 'Api\EstateController@myDeferredInstallment');//طلب تقسيط مؤجل
        Route::get('my/finance', 'Api\EstateController@myFinance');//طلب تقسيط مؤجل
        Route::get('my/rate', 'Api\EstateController@myRate');//طلب تقسيط مؤجل
        Route::post('/send/offer/Request', 'Api\EstateController@send_offer');//طلب  تمويل
        Route::get('customer/my/offer/Request', 'Api\EstateController@customer_my_send_offer');//طلب  تمويل
        Route::get('provider/my/offer/Request', 'Api\EstateController@provider_my_send_offer');//طلب  تمويل
        Route::post('/estate/Request', 'Api\RealEstateController@addRequestEstate');
        Route::post('/update_estate_Request', 'Api\RealEstateController@updateRequestEstate');
        Route::post('/delete_request_estate/{id}', 'Api\RealEstateController@deleteRequestEstate');
        Route::get('/request_estate/{id}', 'Api\RealEstateController@ShowRequestEstate');
        Route::post('/send/offer/app/status', 'Api\EstateController@send_offer_app_status');//طلب  تمويل
        Route::get('/approve/offer', 'Api\EstateController@approve_offer');//طلب  تمويل


//Deferred installment

        Route::get('/home', 'Api\EstateController@home');
        Route::get('/rate/{id}/details', 'Api\EstateController@rate_estate_det');
        Route::get('/home/list', 'Api\EstateController@homeList');


        Route::get('/home/estate', 'Api\EstateController@homeAqarz');
        Route::get('/hide/{id}/estate', 'Api\AuthController@hideEstate');
        Route::get('/home/estate/custom/list', 'Api\EstateController@homeCustomAqarz');
        Route::get('/home/estate/custom/list/count', 'Api\EstateController@homeCustomAqarzNumber');
        Route::get('/home/estate/list', 'Api\EstateController@homeAqarzList');

        Route::get('/my/estate', 'Api\EstateController@myEstate');
        Route::get('/show_order', 'Api\EstateController@show_order');
        Route::get('/market/demands', 'Api\EstateController@demandsRequest');//طلب  تمويل
        Route::get('/my_request_offer/{id}', 'Api\EstateController@my_request_offer');//طلب  تمويل

        Route::get('/single/{id}/estate', 'Api\EstateController@single_estate');


        Route::get('estate/{id}/check', 'Api\EstateController@check_estate');

        Route::get('/single/{id}/request', 'Api\EstateController@single_request');
        Route::post('/delete/{id}/request', 'Api\EstateController@delete_request');
        Route::get('/smilier/{id}/estate', 'Api\EstateController@smilier_estate');
        Route::get('/user/{id}/estate', 'Api\EstateController@user_estate');
        Route::get('/customer/offer', 'Api\EstateController@customer_offer');

        Route::get('/fund/Request', 'Api\EstateController@show_fund_requests');


        Route::post('cancel/{id}/fund/offer', 'Api\EstateController@cancel_fund_offer');
        Route::post('cancel/{id}/offer', 'Api\EstateController@cancel_offer');


        //   Route::group(['middleware' => 'IsPay'], function () {
        Route::get('/my/fund/request/offer', 'Api\EstateController@fund_request_offer');

        Route::get('/finance/Request', 'Api\EstateController@active_finice_estate');//طلب  تمويل
        Route::post('/reject/fund/offer', 'Api\EstateController@send_reject_offer_fund');//طلب  تمويل
        Route::post('/send/offer/fund/Request', 'Api\EstateController@send_offer_fund');//طلب  تمويل


        Route::post('/delete/offer/fund/Request', 'Api\EstateController@delete_offer_fund');//طلب  تمويل
        Route::post('/update/offer/fund/Request', 'Api\EstateController@update_offer_fund');//طلب  تمويل


        Route::get('/approval/offer', 'Api\EstateController@approval_offer');//طلب  تمويل
        Route::post('/send/offer/status', 'Api\EstateController@send_offer_status');//طلب  تمويل
        Route::post('/send/customer/offer/status', 'Api\EstateController@send_customer_offer_status');//طلب  تمويل

        //  });

    });


    //   Route::prefix('/fund')->group(function () {

    Route::get('estate/fund/fund/Request/offer', 'Api\estate_fund\EstateController@fund_request_offer');//طلب  تمويل
    Route::post('estate/fund/accept/offers', 'Api\estate_fund\EstateController@accept_offer');//طلب  تمويل
    Route::get('estate/fund/reject/request/offer', 'Api\estate_fund\EstateController@reject_request_offer');//طلب  تمويل


    // Route::get('/finance/Request', 'Api\RealEstateController@finance');//طلب  تمويل

    Route::post('estate/fund/login', 'Auth\LoginController@loginEstateFund');
    Route::post('estate/fund/beneficiary/information', 'Api\estate_fund\EstateController@beneficiary_information');
    Route::get('estate/fund/{id}/record/offers', 'Api\estate_fund\EstateController@record_offer');//طلب  تمويل

    Route::group(['middleware' => 'EstateFund'], function () {
        Route::get('estate/fund/offer/date/data', 'Api\estate_fund\EstateController@offer_date_data');
        Route::get('estate/fund/provider/date/data', 'Api\estate_fund\EstateController@provider_data');
        Route::get('estate/fund/provider/attchment/data', 'Api\estate_fund\EstateController@provider_attchment_data');
        Route::get('estate/fund/fund/Request', 'Api\estate_fund\EstateController@show_fund_requests');
        Route::get('/estate/fund/all/fund/Request', 'Api\estate_fund\EstateController@all_show_fund_requests');
        Route::post('estate/fund/cancel/{id}/fund/offer', 'Api\estate_fund\EstateController@cancel_fund_offer');
        Route::post('estate/fund/add_neb', 'Api\estate_fund\EstateController@add_neb');
        Route::get('estate/fund/cancel/offer', 'Api\estate_fund\EstateController@fund_request_close_offer');

        Route::get('estate/fund/Request/offer', 'Api\estate_fund\EstateController@fund_request_all_offer');
        Route::post('estate/fund/fund/Request/{uuid}/delete', 'Api\estate_fund\EstateController@delete_fund_requests');

        Route::post('estate/fund/fund/Request', 'Api\estate_fund\EstateController@fund_requests');//طلب  تمويل
        Route::get('estate/fund/fund/Request/has/offer', 'Api\estate_fund\EstateController@Request_has_offer');//طلب  تمويل
        Route::get('estate/fund/fund/Request/has/{uuid}/offer', 'Api\estate_fund\EstateController@check_Request_has_offer');
        Route::get('estate/fund/fund/Request/has/{uuid}/offer/change/status', 'Api\estate_fund\EstateController@change_Request_has_offer');


        Route::get('estate/fund/active/fund/{id}/requests', 'Api\estate_fund\EstateController@active_fund_requests');
        Route::get('estate/fund/deactive/fund/{id}/requests', 'Api\estate_fund\EstateController@deactive_fund_requests');
        Route::get('estate/fund/reset/offers', 'Api\estate_fund\EstateController@reset_offer');//طلب  تمويل

    });
    // });


    Route::post('rent/login', 'Auth\LoginController@loginRent');

    Route::group(['middleware' => 'rent'], function () {
        Route::post('rent/add/estate', 'Api\rent\RealEstateController@addEstate');//طلب  انشاء غقار
        Route::get('rent/estate', 'Api\rent\RealEstateController@myEstate');
        Route::get('rent/estate/request/offer', 'Api\rent\RealEstateController@myRequestOffer');
        Route::get('rent/estate/request', 'Api\rent\RealEstateController@myRequest');


    });


    //ejar

    Route::prefix('/ejar')->group(function () {
        Route::get('/regions/', 'Api\EjarController@regions');
        Route::get('/region/{id}/cities', 'Api\EjarController@cities');
        Route::get('/cities/{id}/districts', 'Api\EjarController@districts');

        Route::post('/entity_endpoints', 'Api\EjarController@entity_endpoints');
        Route::post('/individual_entities/find_or_create', 'Api\EjarController@individual_entities');
        Route::post('/organization_entities/get_organization', 'Api\EjarController@organization_entities');


        Route::get('get_properties', 'Api\EjarController@get_properties');
        Route::get('get_unity', 'Api\EjarController@get_unity');
        Route::post('add_properties', 'Api\EjarController@add_properties');
        Route::post('add_unit', 'Api\EjarController@add_unit');
        Route::post('add_contract', 'Api\EjarController@add_contract');
        Route::post('add_property_and_unit_contract/{contract_id}', 'Api\EjarController@add_property_and_unit_contract');
        Route::post('select_parties_contract/{contract_id}', 'Api\EjarController@select_parties_contract');
        Route::post('financial_information_contract/{contract_id}', 'Api\EjarController@financial_information_contract');
        Route::post('contract_unit_services_contract/{contract_id}', 'Api\EjarController@contract_unit_services_contract');
        Route::post('rental_fee_contract/{contract_id}', 'Api\EjarController@rental_fee_contract');
        Route::post('update_fee_contract/{contract_id}', 'Api\EjarController@update_fee_contract');
        Route::post('contract_terms_contract/{contract_id}', 'Api\EjarController@contract_terms_contract');
        Route::post('submit_contract/{contract_id}', 'Api\EjarController@submit_contract');
        Route::post('contracts', 'Api\EjarController@ejar_contracts');
        Route::post('contract_status/{contract_id}', 'Api\EjarController@ejar_contract_status');
        Route::post('contract_delete/{contract_id}', 'Api\EjarController@ejar_contract_delete');
        Route::get('get_ejar_data', 'Api\EjarController@get_ejar_data');
        Route::get('download/{contract_id}', 'Api\EjarController@download');
    });


});
