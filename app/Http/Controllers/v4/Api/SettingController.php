<?php

namespace App\Http\Controllers\v4\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CheckPointResource;
use App\Http\Resources\v4\EstateResource;
use App\Http\Resources\v4\NewsResource;
use App\Http\Resources\v4\RealEstateAdviceResources;
use App\Models\v3\AreaEstate;
use App\Models\v3\Bank;
use App\Models\v3\City;
use App\Models\v3\City3;
use App\Models\v3\Comfort;
use App\Models\v3\Contact;
use App\Models\v3\Content;
use App\Models\v3\CourseType;
use App\Models\v3\District;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\EstatePrice;
use App\Models\v3\EstateRequest;
use App\Models\v3\EstateType;
use App\Models\v3\ExperienceType;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\MemberType;
use App\Models\v3\Neighborhood;
use App\Models\v3\NotificationUser;
use App\Models\v3\OprationType;
use App\Models\v3\Region;
use App\Models\v3\Report;
use App\Models\v3\RequestFund;
use App\Models\v3\RequestOffer;
use App\Models\v3\ServiceType;
use App\Models\v3\StreetView;
use App\Models\v3\Ticket;
use App\Models\v3\TicketChat;
use App\Models\v3\Video;
use App\Models\v4\Cities;
use App\Models\v4\FundingRequest;
use App\Models\v4\News;
use App\User;
use Carbon\Carbon;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class SettingController extends Controller
{

    public function settings()
    {
        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $cloum = 'value_' . $local;

        $AreaEstate = AreaEstate::where('status', '1')->get();
        $Comfort = Comfort::get();
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
        $policies_terms = Content::where('key', 'policies_terms')->first()->$cloum;
        $real_estate_appraisal = Content::where('key', 'real_estate_appraisal')->first()->$cloum;
        $real_estate_price = Content::where('key', 'real_estate_price')->first()->$cloum;
        $real_estate_index = Content::where('key', 'real_estate_index')->first()->$cloum;
        $real_estate_advice = Content::where('key', 'real_estate_advice')->first()->$cloum;
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
        $EstatePrice = EstatePrice::where('status', '1')->get();
        $StreetView = StreetView::where('status', '1')->get();


        $age = collect();
        $age->push(__("views.new_age"));
        for ($i = 1; $i <= 35; $i++) {
            $age->push($i . ' ' . __("views.year"));
        }
        $age->push('+35 ' . __("views.year"));


        $array = [

            'member_types' => $member_types,
            'privacy_and_confidentiality_statement' => $privacy_and_confidentiality_statement,
            'terms_and_conditions' => $terms_and_conditions,
            'policies_terms' => $policies_terms,
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
            'Comfort' => $Comfort,
            'AreaEstate' => $AreaEstate,
            'EstatePrice' => $EstatePrice,
            'StreetView' => $StreetView,
            'real_estate_appraisal' => $real_estate_appraisal,
            'real_estate_price' => $real_estate_price,
            'real_estate_index' => $real_estate_index,
            'real_estate_advice' => $real_estate_advice,
            'age' => $age,
        ];
        return response()->success("Settings List", $array);
    }

    public function pages(Request $request)
    {
        $type = $request->type;
        $content = Content::query();
        if ($type == 'real_estate_price') {
            $data = $content->where('key', 'real_estate_price')->first();
            $array = [
                'text' => $data->value,
                'image' => $data->image,
            ];
            return response()->success("real estate price", $array);
        } elseif ($type == 'real_estate_index') {
            $data = $content->where('key', 'real_estate_index')->first();
            $array = [
                'text' => $data->value,
                'image' => $data->image,
            ];
            return response()->success("real estate index", $array);
        } elseif ($type == 'real_estate_advice') {
            $data = $content->where('key', 'real_estate_advice')->get();
            return response()->success("real estate advice", RealEstateAdviceResources::collection($data));
        } else {
            return response()->success("no data find");

        }
    }

    public function EstateType()
    {
        $user = Auth::user();
        $value = EstateType::get();
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

    public function OprationType()
    {
        $OprationType = OprationType::get();
        return response()->success("OprationType List", $OprationType);
    }

    public function neighborhoods_all()
    {
        $neighborhoods = \App\Models\v4\District::all();
        $neighborhoods_array = [];
        foreach ($neighborhoods as $neighborhood) {
            $center = $neighborhood->center;
            $center = explode(',', $center);
            $neighborhood->lat = @$center[0];
            $neighborhood->lan = @$center[1];
            $neighborhoods_array[] = [
                'id' => $neighborhood->district_id,
                'name' => $neighborhood->name,
                'full_name' => $neighborhood->full_name,
                'city_id' => $neighborhood->city_id,
                'neighborhood_serial' => $neighborhood->district_id,
                'lat' => (string)$neighborhood->lat,
                'lan' => (string)$neighborhood->lan,
            ];
        }
        return response()->success("Neighborhood List", $neighborhoods_array);
    }

    public function sendReport(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'report_type' => 'required',
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
        $dis = checkPint("$location");
        $dis = \App\Models\v4\District::where('district_id', $dis)->first();
        if (!$dis) {
            return response()->error(__("views.not found"));
        }
        $dis = CheckPointResource::collection([$dis]);
        return response()->success(__("views.Done"), $dis[0]);
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

    public function CountCall($id)
    {
        $user = User::findOrFail($id);
        if ($user) {
            $user->count_call = $user->count_call + 1;
            $user->save();
        }
        return response()->success("Count Call Plus", []);

    }

    public function bestProvider()
    {


        // dd(Config::get('filesystems'));

        $user = User::where('type', 'provider')
            ->where('id', '!=', 142)
            //->orderBy('count_visit', 'desc')

            ->orderBy('count_estate', 'desc')
//            ->orderBy('count_fund_offer', 'desc')
//            ->orderBy('count_offer', 'desc')
//            ->orderBy('count_accept_fund_offer', 'desc')
            // ->orderBy('count_agent', 'desc')
            ->limit(5)
            ->get();


        if ($user == null) {
            return response()->error(__("views.not found"));
        }
        return response()->success("Best Provider", $user);

    }

    public function all_cities_neighborhoods()
    {

        $cities = Cities::all();
        $cities_array = [];
        foreach ($cities as $city) {
            $center = $city->center;
            $city->latitude = $center->getLat();
            $city->longitude = $center->getLng();

            $cities_array[] = [
                'id' => $city->id,
                'name' => $city->name,
                'full_name' => $city->region->name . ' - ' . $city->name,
                'city_id' => $city->id,
                'serial' => $city->id,
                'lat' => (string)$city->latitude,
                'lan' => (string)$city->longitude,
                'type' => 'city',
            ];
        }


        $neighborhoods = \App\Models\v4\District::all();
        $neighborhoods_array = [];
        foreach ($neighborhoods as $neighborhood) {
            $center = $neighborhood->center;
            $center = explode(',', $center);
            $neighborhood->lat = @$center[0];
            $neighborhood->lan = @$center[1];
            $neighborhoods_array[] = [
                'id' => $neighborhood->district_id,
                'name' => $neighborhood->name,
                'full_name' => @$neighborhood->city->region->name . ' - ' . @$neighborhood->city->name . ' - ' . $neighborhood->name,
                'city_id' => $neighborhood->city_id,
                'serial' => $neighborhood->district_id,
                'lat' => (string)$neighborhood->lat,
                'lan' => (string)$neighborhood->lan,
                'type' => 'neighborhood',
            ];
        }

        $cities_array = array_merge($cities_array, $neighborhoods_array);
        return response()->success("Cities and Neighborhoods", $cities_array);
    }

    public function get_age()
    {
        $age = collect();
        $age->push(__("views.new_age"));
        for ($i = 1; $i <= 35; $i++) {
            $age->push($i . ' ' . __("views.year"));
        }
        $age->push('+35 ' . __("views.year"));

        return response()->success("Age", $age);
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

    public function website_home(Request $request)
    {
        $request->request->add(['is_list' => 1, 'web' => 1]);
        $estate = Estate::select('estates.*')
            ->whereRaw(' deleted_at is null')
            ->whereIN('status', ['completed', 'expired'])->orderBy('id', 'desc')->limit(6)->get();

        $news = News::query()->orderBy('id', 'desc')->limit(3)->get();

        $data = [
            'estate' => EstateResource::collection($estate),
            'news' => NewsResource::collection($news),
        ];
        return response()->success(__('views.website_home'), $data);

    }


    public function funding_request(Request $request)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $rules = Validator::make($request->all(), [
            'personal_id_number' => 'required|numeric',
            'personal_name' => 'required|min:3',
            'personal_mobile_number' => 'required',
            'personal_monthly_net_salary' => 'required|numeric',
            'employer_name' => 'required|min:3',
            'employer_type' => 'required|in:1,2,3',
            'real_estate_product_type' => 'required|in:1,2,3',
            'real_estate_property_information' => 'required|min:3',
            'real_estate_property_price' => 'required|numeric',
            'rea_estate_property_age' => 'required|numeric',
        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $time = Carbon::now()->format('Y-m-d\TH:i:s.511P');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://gwt.alrajhibank.com.sa:9443/api-factory/sit/aqarz-lead/v1/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
  "clientTimestamp": "'.$time.'",
  "personalIDNumber": "'.$request->personal_id_number.'",
  "personalName": "'.$request->personal_name.'",
  "personalMobileNumber": "'.$request->personal_mobile_number.'",
  "personalMonthlyNetSalary": "'.$request->personal_monthly_net_salary.'",
  "employerName": "'.$request->employer_name.'",
  "employerType": "'.$request->employer_type.'",
  "realEstateProductType": "'.$request->real_estate_product_type.'",
  "realEstatePropertyInformation": "'.$request->real_estate_property_information.'",
  "realEstatePropertyPrice": "'.$request->real_estate_property_price.'",
  "realEstatePropertyAge": "'.$request->rea_estate_property_age.'"
}',
            CURLOPT_HTTPHEADER => array(
                'X-IBM-Client-Id: df522f2e67b060134b9a6c0c141f11e1',
                'X-IBM-Client-Secret: fe02b4372d86c2f22ec7595e076969b5',
                'Accept-Language: EN',
                'x-signature: asdas',
                'Content-Type: application/json',
                'Cookie: BIGipServerAPI-Factory-9443.app~API-Factory-9443_pool=!/9c4NOVe0PxJIdGjwJzvMiYVFIf3DPi9fMN+ehE5cRWNL0s6v0JmkNTdrbZx8tyKVSrUfysiPRkN4Q==; TS019fdacf=01ac7f8f7beae13dd389e2d61740607f9ca62b7e8257bb6e5eada3d1151eb32f3991e00dc15b78d8218dbdec17a0f1f1fa02a4f81bdd61104d7ae982ae5c464fa3634ba75d'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        if (strpos($response, '500') !== false) {
            return response()->error('خطأ في الاتصال بالخادم');
        }

        $response = json_decode($response, true);

        FundingRequest::create([
            'user_id' => $user->id,
            'personalIDNumber' => $request->personal_id_number,
            'personalName' => $request->personal_name,
            'personalMobileNumber' => $request->personal_mobile_number,
            'personalMonthlyNetSalary' => $request->personal_monthly_net_salary,
            'employerName' => $request->employer_name,
            'employerType' => $request->employer_type,
            'realEstateProductType' => $request->real_estate_product_type,
            'realEstatePropertyInformation' => $request->real_estate_property_information,
            'realEstatePropertyPrice' => $request->real_estate_property_price,
            'realEstatePropertyAge' => $request->rea_estate_property_age,
            'statusCode' => $response['header']['statusCode'],
            'statusDescription' => $response['header']['statusDescription'],
            'requestID' => $response['header']['requestID'],
            'leadID' => $response['body']['leadID'],
        ]);
        return response()->success(__('views.funding_request'), $response);

    }
}
