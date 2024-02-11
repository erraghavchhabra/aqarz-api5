<?php

namespace App\Http\Controllers\v1\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\CityResource;
use App\Models\v1\AreaEstate;
use App\Models\v1\Bank;
use App\Models\v1\Comfort;
use App\Models\v1\Contact;
use App\Models\v1\Content;
use App\Models\v1\Country;
use App\Models\v1\EstatePrice;
use App\Models\v1\EstateType;
use App\Models\v1\MemberType;
use App\Models\v1\Neighborhood;
use App\Models\v1\OprationType;
use App\Models\v1\RateRequestType;
use App\Models\v1\ServiceType;
use App\Models\v1\StreetView;
use App\Models\v1\UserPlan;
use App\Models\v3\Client;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\EstateRequest;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\RequestFund;
use App\Models\v3\RequestOffer;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class SettingController extends Controller
{

    public function userDataCheck()
    {
        $users = User::paginate(20);
        foreach ($users as $userItem) {
            $count_estate = Estate::where('user_id', $userItem->id)->count();
            $count_request = EstateRequest::where('user_id', $userItem->id)->count();
            $count_offer = RequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('request')
                ->count();


            $count_client = Client::where('user_id', $userItem->id)->count();
            $count_accept_offer = RequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('request')
                ->where('status', 'accepted_customer')
                ->count();
            $count_accept_fund_offer = FundRequestOffer::where('provider_id', '142')
                ->where('status', 'accepted_customer')
                ->whereHas('provider')
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->count();


            $count_preview_fund_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->where('status', 'sending_code')
                ->whereHas('provider')
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->count();
            $count_fund_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->whereHas('provider')
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->count();


            $count_fund_pending_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->where('status', 'new')
                // ->orwhere('status', null)
                ->count();


            $array_fund_offer = FundRequestOffer::where('provider_id', $userItem->id)
                ->whereHas('estate')
                ->whereHas('fund_request')
                ->pluck('uuid');

            $count_fund_request = RequestFund::whereIn('uuid', $array_fund_offer->toArray())->count();


            $countEmp = Employee::where('user_id', $userItem->id)
                // ->whereIn('is_employee', [1, 2])
                ->count();

            /*   $countEmp = User::where('is_employee', 1)
                   ->where('employer_id',$userItem->id)->count();
   */

            $userItem->real_count_offer = $count_offer;
            $userItem->real_count_fund_pending_offer = $count_fund_pending_offer;
            $userItem->real_count_request = $count_request;
            $userItem->real_count_client = $count_client;
            $userItem->real_count_fund_offer = $count_fund_offer;
            $userItem->real_count_estate = $count_estate;
            $userItem->real_count_accept_offer = $count_accept_offer;
            $userItem->real_count_preview_fund_offer = $count_preview_fund_offer;
            $userItem->real_count_fund_request = $count_fund_request;
            $userItem->real_count_accept_fund_offer = $count_accept_fund_offer;
            $userItem->real_count_emp = $countEmp;


           // Log::channel('slack')->info(['data' => $userItem, 'msg' => 'from job']);
        }

        return view('user_data_check', compact('users'));
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

        $EstateType = EstateType::get();
        return response()->success("EstateType List", $EstateType);
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
        $company_info = Content::where('key', 'company_info')->first()->$cloum;
        $about_us = Content::where('key', 'about_us')->first()->$cloum;
        $privacy_policy = Content::where('key', 'privacy_policy')->first()->$cloum;
        $face_book = Content::where('key', 'face_book')->first()->$cloum;
        $twitter = Content::where('key', 'twitter')->first()->$cloum;
        $insta = Content::where('key', 'insta')->first()->$cloum;
        $snapchat = Content::where('key', 'snapchat')->first()->$cloum;
        $linked = Content::where('key', 'linked')->first()->$cloum;
        $mobile = Content::where('key', 'mobile')->first()->$cloum;
        $email = Content::where('key', 'email')->first()->$cloum;
        $service_types = ServiceType::where('status', '1')->get();
        $member_types = MemberType::where('status', '1')->get();


        $array = [
            'member_types' => $member_types,
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
            'OprationType' => $this->OprationType()
        ];


        return response()->success("Settings List", $array);

        //return $x;

    }


    public function countries()
    {

        $Bank = Country::get();
        return response()->success("Country List", $Bank);
    }


    public function cities()
    {


        /*    $value = Cache::remember('cities', 10000000000000000000000000,function () {
                return $cities = City::where('status', '1')->get();
            });
    */
        $value = Cache::get('cities');
        $collection = CityResource::collection($value);

        return response()->success("Cities List", $collection);
    }

    public function neighborhoods($id)
    {


        $id = substr($id, 0, 5);

        $Neighborhood = Neighborhood::where('city_id', $id)->paginate();
        return response()->success("Neighborhood List", $Neighborhood);
    }


    public function comfort()
    {

        $Comfort = Comfort::get();
        return response()->success("Comfort", $Comfort);
    }


    public function rate_request_type()
    {

        $RateRequestType = RateRequestType::get();
        return response()->success("RateRequestType", $RateRequestType);
    }


    public function CodeCheck2(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
            'code' => 'required',


        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        return ['status' => true];
    }


    public function NewCodeCheck(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'Uuid' => 'required',
            'OtpCode' => 'required',
            'OfferId' => 'required',


        ]);


        $url = 'https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/approve';
//code/send check code  https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/approve
        //approve offer  https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/approve/otp
        /// first offer https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/notification
        ///

        $data = [
            'Uuid' => trim($request->get('Uuid')),
            'OtpCode' => trim($request->get('OtpCode')),
            'OfferId' => trim($request->get('OfferId')),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Authorization: Basic YXFhcnpfcDpAcjNRcnojI3V5ITE3',
            'ClientId: "broker"',
            'ClientSecrets: [ "2r5u8x/A?D(G+KbPeSgVkYp3s6v9y$B&E)H@McQfTjWmZq4t7w!z%C*F-JaNdRgU" ]',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic YXFhcnpfcDpAcjNRcnojI3V5ITE3',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
    }
 // ارسال كود التفعيل للمستخدم
    public function NewSendCode(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'Uuid' => 'required',
            'OfferId' => 'required',


        ]);


        $url = 'https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/approve/otp';

        $data = [
            'Uuid' => trim($request->get('Uuid')),
            'OfferId' => trim($request->get('OfferId')),


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
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
    }
    //اعادة الارسال
    public function NewresendSendCode(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'Uuid' => 'required',
            'OfferId' => 'required',


        ]);


        $url = 'https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/approve/otp';

        $data = [
            'Uuid' => trim($request->get('Uuid')),
            'OfferId' => trim($request->get('OfferId')),


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
            'ClientId: "broker"',
            'ClientSecrets: [ "2r5u8x/A?D(G+KbPeSgVkYp3s6v9y$B&E)H@McQfTjWmZq4t7w!z%C*F-JaNdRgU" ]',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',
            'ClientId: "broker"',
            'ClientSecrets: [ "2r5u8x/A?D(G+KbPeSgVkYp3s6v9y$B&E)H@McQfTjWmZq4t7w!z%C*F-JaNdRgU" ]',
            /*"ClientId": "broker",
                       "ClientSecrets": [ "2r5u8x/A?D(G+KbPeSgVkYp3s6v9y$B&E)H@McQfTjWmZq4t7w!z%C*F-JaNdRgU" ]
             *
             * */
        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
    }
 //ارسال رسالة للمستفيد بحالة كان اول عرض
    public function NewSmsCode(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'uuid' => 'required',


        ]);


        $url = 'https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/notification';

        $data = [
            'Uuid' => trim($request->get('Uuid')),


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
            'ClientId: "broker"',
            'ClientSecrets: [ "2r5u8x/A?D(G+KbPeSgVkYp3s6v9y$B&E)H@McQfTjWmZq4t7w!z%C*F-JaNdRgU" ]',

        ]);


        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'aqarz_p' . ":" . '@r3Qrz##uy!17');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
    }

// التحقق من الكود اذا كان صحيح او لا
    public function CodeCheck(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'uuid' => 'required',
            'code' => 'required',
            // 'offer_id' => 'required',


        ]);


        $url = 'https://eservices.redf.gov.sa/Aqarz/api/Aqarz/CheckCode';
//code/send check code  https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/approve
        //approve offer  https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/approve/otp
        /// first offer https://api-dlp-uat.redf.gov.sa/external-api/api/aqarz/offers/notification
        ///

        $data = [
            'uuid' => trim($request->get('uuid')),
            'code' => trim($request->get('code')),
            //'offer_id' => trim($request->get('offer_id')),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Authorization: Basic YXFhcnpfcDpAcjNRcnojI3V5ITE3',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic YXFhcnpfcDpAcjNRcnojI3V5ITE3',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
    }

    public function SendCode(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'uuid' => 'required',


        ]);


        $url = 'https://eservices.redf.gov.sa/Aqarz/api/Aqarz/Approvaloffers';

        $data = [
            'uuid' => trim($request->get('uuid')),


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
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
    }


    public function resendSendCode(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'uuid' => 'required',


        ]);


        $url = 'https://eservices.redf.gov.sa/Aqarz/api/Aqarz/Approvaloffers';

        $data = [
            'uuid' => trim($request->get('uuid')),


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
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
    }

    public function SmsCode(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'uuid' => 'required',


        ]);


        $url = 'https://eservices.redf.gov.sa/Aqarz/api/Aqarz/SendSMSwhenReadyOffers';

        $data = [
            'uuid' => trim($request->get('uuid')),


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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);


        if ($result == false) {
            return JsonResponse::fail('plaese try again connection failed', 400);
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


        $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/CheckCode';

        $data = [
            'uuid' => $request->get('uuid'),
            'code' => $request->get('code'),

        ];
        $data_json = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);


        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',

        ]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerstring = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        curl_close($ch);

        return (json_decode($body));

        dd(444);

        dd(444);


        //var_dump(json_decode($return, true));


//return $return;
        dd(444);
        //dd($code);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        // dd($return);
        return $code;
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


        return response()->success("Message Send Successfully ", []);
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


        //  ini_set("smtp_port", "465");

        $to = $user->email;
        $from = 'Aqarz@info.com';
        $name = 'Aqarz';
        $subject = 'رابط الدفع';


        $logo = asset('logo.svg');
        $link = '#';

        $details = [
            'to' => $to,
            'from' => $from,
            'logo' => $logo,
            'link' => $link,
            'subject' => $subject,
            'name' => $name,
            "message" => $message,
            "text_msg" => $text,
        ];


        // var_export (dns_get_record ( "host.name.tld") );

        // dd(444);
        \Mail::to($to)->send(new \App\Mail\NewMail($details));

        if (Mail::failures()) {
            return response()->json([
                'status' => false,
                'data' => $details,
                'message' => 'Nnot sending mail.. retry again...'
            ]);
        }


        $user_mobile = checkIfMobileStartCode($user->mobile, $user->country_code);
        $unifonicMessage = new UnifonicMessage();
        $unifonicClient = new UnifonicClient();
        $unifonicMessage->content = "Your Verification Code Is: ";
        $to = $user_mobile;
        $co = $message;
        $data = $unifonicClient->sendCustomer($to, $co);
        Log::channel('single')->info($data);
        //  return $data;

        return response()->success("تم ارسال رابط البوابة الي هاتفك والبريد الالكتروني");
        /*   }
       else {
       return response()->error("Not Found Plan");
       }*/


        if ($Plans) {


            $user_plan = UserPlan::create([
                'plan_id' => $Plans->id,
                'user_id' => $user->id,
                'status' => '0',
                'unique_code' => $uuid,
                'payment_url' => url('subscribe/plan/' . $uuid),
                'count_try' => 0,
                'total' => $Plans->price
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
                'to' => $to,
                'from' => $from,
                'logo' => $logo,
                'link' => $link,
                'subject' => $subject,
                'name' => $name,
                "message" => $message
            ];


            \Mail::to($to)->send(new \App\Mail\NewMail($details));

            if (Mail::failures()) {
                return response()->json([
                    'status' => false,
                    'data' => $details,
                    'message' => 'Nnot sending mail.. retry again...'
                ]);
            }


            return response()->success("تم ارسال رابط الدفع الي هاتفك والبريد الالكتروني");
        } else {
            return response()->error("Not Found Plan");
        }

    }

    public function sendSms()
    {
        Artisan::call("send:sms");
    }
}
