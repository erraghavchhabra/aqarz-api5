<?php

namespace App\Http\Controllers\v4\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\MsgDetResource;
use App\Http\Resources\MsgResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserSearchResource;
use App\Http\Resources\v4\ComfortsResource;
use App\Http\Resources\v4\SearchResource;
use App\Http\Resources\v4\SearchWebResource;
use App\Jobs\OtpJob;
use App\Models\v3\Client;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\EstateRequest;
use App\Models\v3\Favorite;
use App\Models\v3\IamProvider;
use App\Models\v3\Msg;
use App\Models\v3\MsgDet;
use App\Models\v3\NotificationUser;
use App\Models\v3\RateRequest;
use App\Models\v3\RequestFund;
use App\Models\v4\EstateRequestPreview;
use App\Models\v4\FcmToken;
use App\Models\v4\FundingRequest;
use App\Models\v4\RateOfferRequest;
use App\Models\v4\Search;
use App\Unifonic\UnifonicMessage;
use App\User;
use App\Helpers\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Unifonic\Client as UnifonicClient;
use Auth;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use phpseclib\Crypt\RSA;
use Ramsey\Uuid\Uuid;

class AuthController extends Controller
{
    protected $unifonicClient;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UnifonicClient $unifonicClient)
    {
        //
        $this->unifonicClient = $unifonicClient;
    }

    public function loginNew(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'username' => 'required_if:referer,local|max:255',
            'device_token' => 'sometimes|required',
            'device_type' => 'required',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $username_column = 'mobile';
        $old_mobile = trim($request->get('username'));
        $mobile = 0;
        if (startsWith($old_mobile, '0')) {
            $mobile = substr($old_mobile, 1, strlen($old_mobile));
        } else {
            if (startsWith($old_mobile, '00')) {
                $mobile = substr($old_mobile, 2, strlen($old_mobile));
            } else {
                $mobile = trim($request->get('username'));
            }
        }

        $global_mobile = intval($request->get('country_code')) . intval($mobile);
        $request->merge(['username' => $global_mobile]);

        $userCheck = User::where($username_column, $mobile)->first();

        if ($userCheck && !$request->get('password')) {
            if (!$userCheck->password) {
                //   dd(333);
                dispatch(new OtpJob($userCheck));
                return response()->success("User Profile", ['code' => $userCheck->confirmation_code]);
            }

            return response()->success("User Profile", ['code' => ""]);

        } elseif (!$userCheck) {
            $request->merge(['mobile' => $mobile]);
            $user = $this->store($request);

            return ($user);
            return response()->success("User Profile", ['statue' => false]);
        } elseif ($userCheck && !$userCheck->password) {
            return response()->success("User Profile", ['code' => $userCheck->confirmation_code]);
            $request->merge(['mobile' => $mobile]);
            $user = $this->store($request);
            return ($user);
            return response()->success("User Profile", ['statue' => false]);
        }

        $request->merge([
            $username_column => $request->username,
            'status' => true
        ]);
        $credentials = $request->only($username_column, 'password', 'status');
        $class = new \App\User();
        $user = $class::where($username_column, $credentials[$username_column])->first();

        if (!$user && $mobile) { //  TODO remove later after 2019-12-30
            $user = $class::where($username_column, $mobile)->first();
        }

        if ((!$user) ||
            (app('hash')->check($credentials['password'], $user->password) === false)) {
            return JsonResponse::fail('UserPasswordMismatchError', 400);
        }

        if ($user->api_token == null) {
            $user->api_token = hash('sha512', time());
        }
        $user->device_token = $request->get('device_token');
        $user->device_type = $request->get('device_type');
        $user->save();

        if ($request->get('device_token')) {
            if ($user) {
                $fcm_token = FcmToken::where('token', $request->get('device_token'))->where('type', $request->get('device_type'))->first();
                if ($fcm_token) {
                    if ($fcm_token->user_id != $user->id) {
                        $fcm_token->user_id = $user->id;
                        $fcm_token->type = $request->get('device_type');
                        $fcm_token->save();
                    }
                } else {

                    FcmToken::create([
                        'user_id' => $user->id,
                        'token' => $request->get('device_token'),
                        'type' => $request->get('device_type')
                    ]);
                }

            }
        }

        return response()->success("User Profile", $user);
    }

    public function store(Request $request)
    {


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


            $checkUser = User::where('mobile', $mobile)
                ->where('password', null)->first();


            if ($checkUser) {
                $checkUser->delete();
            }
        }

        $request->merge([
            'mobile' => $mobile,
        ]);
        $rules = Validator::make($request->all(), [

            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9|unique:users,mobile,null,id,deleted_at,NULL',

            // 'lat'    => 'required',
            //     'lan'    => 'required',

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


        $request->merge([
            'mobile' => $mobile,
        ]);


        $rules = Validator::make($request->all(), [
            /*    'name'                  => 'required|max:255',
                'mobile'                => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
                'email'                 => 'required|email|unique:users,email,null,id,deleted_at,NULL',
                'password'              => 'required|min:6|confirmed',
                'password_confirmation' => 'required',

                'device_token' => 'required',
                'device_type'  => 'required',
                'type'         => 'required',
                'country_code' => 'required'*/

            //'mobile' => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'device_token' => 'required',
            'device_type' => 'required',
            //  'type' => 'required',
            'country_code' => 'required'
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        /*
                if (!env('SKIP_CONFIRM')) {
                    $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
                } else {
                    $confirmation_code = 123456;
                }*/

        $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
        $restore_code = substr(str_shuffle("0123456789"), 0, 6);
        $confirmation_password_code = substr(str_shuffle("0123456789"), 0, 6);
        $request->merge([
            //  'password'          => app('hash')->make($request->input('password')),
            'confirmation_code' => $confirmation_code,
            'restore_code' => $restore_code,
            'confirmation_password_code' => $confirmation_password_code,
            'api_token' => hash('sha512', time()),
            'status' => 'not_active',

            //  'mobile'            => $mobile,
        ]);


        $user = User::create($request->only([
            'name',
            'mobile',
            'email',
            'device_id',
            'country_id',

            'status',
            'device_token',
            'device_type',
            'api_token',
            'type',
            'country_code',
            'confirmation_code',
            'user_name',
            'lat',
            'lan',

        ]));


        $user = User::find($user->id);

        $user->user_name = 'aqarz_user_' . $user->id;
        $user->save();

        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($user->mobile, $country_code);


        /*   $user->mobile = $user_mobile;

           $user->save();*/
        //    if (app()->environment('production')) {
//        dispatch(new OtpJob($user));

        $unifonicMessage = new UnifonicMessage();
        $unifonicClient = new UnifonicClient();
        $unifonicMessage->content = "Your Verification Code Is: ";
        $to = $country_code . $user->mobile;
        $co = $confirmation_code;
        $data = $unifonicClient->sendVerificationCode($to, $co, $unifonicMessage);
        Log::channel('single')->info($data);
        Log::channel('slack')->info($data);

        // }

        $user = User::find($user->id);
        return response()->success(__('views.We Send Activation Code To Your Mobile'), ['code' => $confirmation_code]);
        // return ['data' => $user];
    }


    public function verifyNew(Request $request)
    {
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
            $request->merge([
                'mobile' => $mobile,
            ]);
        }


        $rules = Validator::make($request->all(), [
            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9',
            'code' => 'required',
            'type' => 'required',
            'country_code' => 'required',
        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


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
        $country_code = $request->get('country_code', 966);

        $user_mobile = checkIfMobileStartCode($mobile, $country_code);
        $user = User::where('mobile', $mobile)->first();

        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $success = 'false';
        if ($user->confirmation_code == $request->get('code')) {
            $success = 'true';
        }

        //    $verification = $this->unifonicClient->verify($user_mobile, $request->code);

        // $developer_code = '123456';
        if (isset($success) && $success == 'true' && $request->get('code') == $user->confirmation_code) {
            {
                $user = User::where('mobile', $mobile)->first();

                //Update Current User Verifed_at timestamp
                $user->update([
                    "mobile_verified_at" => date(now()),
                    "email_verified_at" => date(now()),
                    "status" => 'active',
                    'name' => $request->get('name'),
                    'code' => '',
                    'type' => $request->get('type'),
                ]);
                $user = User::whereMobile($user->mobile)->first();
                $msg = Msg::create([
                    'receiver_id' => $user->id,
                    'title' => 'مرحبا بك',
                    'body' => 'مرحبا بك في عقارز،
 سعيدين كثيراً بإنضمامك، في عقارز نسعى أن نكون منصتك الأولى لكافة الخدمات العقارية سواء كانت البحث عن عقارز مناسب أو تقييم عقارك أو الحصول على تمويل مناسب، والعديد من الخدمات التي صممناها خصيصاً لتلبي كافة احتياجاتك.'
                ]);


                $msg_del = MsgDet::create([
                    'msg_id' => $msg->id,
                    'receiver_id' => $user->id,
                    'title' => 'مرحبا بك',
                    'body' => 'مرحبا بك في عقارز،
 سعيدين كثيراً بإنضمامك، في عقارز نسعى أن نكون منصتك الأولى لكافة الخدمات العقارية سواء كانت البحث عن عقارز مناسب أو تقييم عقارك أو الحصول على تمويل مناسب، والعديد من الخدمات التي صممناها خصيصاً لتلبي كافة احتياجاتك.'

                ]);


                $checkIfEmp = Employee::where('emp_mobile', $mobile)
                    ->first();


                if ($checkIfEmp) {
                    $user->is_employee = 1;
                    $user->employer_id = $checkIfEmp->user_id;
                    $user->save();
                }

                if ($request->get('type') == 'provider') {

                }


                if ($request->get('device_token')) {
                    if ($user) {
                        $user->device_token = $request->get('device_token');
                        $user->device_type = $request->get('device_type');
                        $user->save();
                        $fcm_token = FcmToken::where('token', $request->get('device_token'))->where('type', $request->get('device_type'))->first();
                        if ($fcm_token) {
                            if ($fcm_token->user_id != $user->id) {
                                $fcm_token->user_id = $user->id;
                                $fcm_token->type = $request->get('device_token');
                                $fcm_token->save();
                            }
                        } else {

                            FcmToken::create([
                                'user_id' => $user->id,
                                'token' => $request->get('device_token'),
                                'type' => $request->get('device_type')
                            ]);
                        }

                    }
                }


                return response()->success(__("views.Phone Verified"), $user);
            }

        }
        return response()->error(__("views.Incorrect Code"));
    }


    public function save_search(Request $request)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $rules = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $search = Search::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'property_type' => $request->property_type,
            'bedrooms' => $request->bedrooms,
            'bathroom' => $request->bathroom,
            'price_min' => $request->price_min,
            'price_max' => $request->price_max,
            'name' => $request->name,
            'size_min' => $request->size_min,
            'size_max' => $request->size_max,
            'directions' => $request->directions,
            'neighborhoods_id' => $request->neighborhoods_id,
            'receving_update' => $request->receving_update,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);
        return response()->success(__('views.save success'), $search);

    }

    public function save_search_web(Request $request)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $rules = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $search = Search::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'property_type' => $request->estate_type,
            'operation_type_id' => $request->operation_type_id,
            'bedrooms' => $request->bedrooms,
            'bathroom' => $request->bathrooms_number,
            'dining_rooms_number' => $request->dining_rooms_number,
            'price_min' => $request->price_from,
            'price_max' => $request->price_to,
            'name' => $request->name,
            'size_min' => $request->area_from,
            'size_max' => $request->area_to,
            'directions' => $request->directions,
            'neighborhoods_id' => $request->neighborhoods_id,
            'receving_update' => $request->receving_update,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);
        return response()->success(__('views.save success'), $search);

    }

    public function delete_search(Request $request)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $rules = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $search = Search::where('id', $request->id)->first();
        if ($search) {
            $search->delete();
        } else {
            return response()->error(__('views.not found'));
        }
        return response()->success(__('views.delete success'));

    }

    public function search()
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $search = Search::where('user_id', $user->id)->get();

        return response()->success(__('views.success'), SearchResource::collection($search));
    }

    public function search_web()
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $search = Search::where('user_id', $user->id)->get();

        return response()->success(__('views.success'), SearchWebResource::collection($search));
    }

    public function favorite(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'type' => 'required|in:request,estate,fund',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $fav = Favorite::where('user_id', $user->id)
            ->where('status', '1');

        $collection = '';
        if ($request->get('type')) {
            $fav = $fav->where('type', $request->get('type'));
            if ($request->get('type') == 'estate') {
                $fav = $fav->has('estate_data')->paginate();
                $collection = FavoriteResource::collection($fav);
            }
            if ($request->get('type') == 'request') {
                $fav = $fav->has('request_data')->paginate();
                $collection = FavoriteRequestResource::collection($fav);
            }
            if ($request->get('type') == 'fund') {
                $fav = $fav->has('fund_data')->paginate();
                $collection = FavoriteFundResource::collection($fav);
            }
        }
        if ($collection && $collection != '') {
            return response()->success(__("views.Favorite"), $collection->response()->getData(true));
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }
    }


    public function storeFavoriteStatus(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'type_id' => 'required',
            'type' => 'required|in:request,offer,fund,estate',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        if ($request->get('type') == 'estate') {
            $estate = Estate::findOrFail($request->get('type_id'));
        }
        if ($request->get('type') == 'request') {
            $estate = EstateRequest::findOrFail($request->get('type_id'));
        }
        if ($request->get('type') == 'fund') {
            $estate = RequestFund::findOrFail($request->get('type_id'));
        }

        if ($estate) {
            $checkFav = Favorite::where('user_id', $user->id)
                ->where('type_id', $estate->id)
                ->where('type', $request->get('type'))
                ->first();

            if ($checkFav) {
                $checkFav->status = $checkFav->status == '1' ? '0' : '1';
                $checkFav->save();
                if ($request->get('type') == 'fund' && $checkFav->status == '1') {
                    $user->fund_request_fav = $user->fund_request_fav ? $user->fund_request_fav . ',' . $checkFav->type_id : $checkFav->type_id;
                    $user->save();
                } elseif ($request->get('type') == 'fund' && $checkFav->status != '1') {
                    $string = $user->fund_request_fav;
                    $arr = explode(',', $string);
                    $out = array();
                    $x = 0;
                    for ($i = 0; $i < count($arr) - 1; $i++) {
                        if ($x == 0) {
                            if ($arr[$i] != $checkFav->type_id) {
                                $out[] = $arr[$i];
                                $x++;
                            }
                        } else {
                            $out[] = $arr[$i];
                        }
                    }
                    $string2 = implode(',', $out);
                    $user->fund_request_fav = $string2;
                    $user->save();
                }
            } else {
                $checkFav = Favorite::create([
                    'user_id' => $user->id,
                    'type_id' => $estate->id,
                    'status' => '1',
                    'type' => $request->get('type'),
                ]);

                if ($request->get('type') == 'fund') {
                    $user->fund_request_fav = $user->fund_request_fav ? $user->fund_request_fav . ',' . $estate->id : $estate->id;
                    $user->save();
                }
            }
            return response()->success(__("views.Done"), []);
        }

        return JsonResponse::fail(__('views.No Data'), 200);
    }

    public function show()
    {
        $user = auth()->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = User::with('Iam_information')->find($user->id);
        return response()->success(__('views.User Profile'), $user);
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $rules = Validator::make($request->all(), [
            'device_token' => 'required',
            'device_type' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $fcm = FcmToken::where('token', $request->get('device_token'))->where('type', $request->get('device_type'))->first();
        if ($fcm) {
            $fcm->delete();
        }
        return response()->success(__('views.success logout'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
//            'name' => 'sometimes|required',
//            'device_token' => 'sometimes|required',
//            'advertiser_number' => 'sometimes|required',
//            'license_number' => 'sometimes|required_if:account_type,==,company',
//            'device_type' => 'sometimes|required',
            'email' => 'email|unique:users,email,' . $user->id . ',id,deleted_at,NULL',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);

        if ($request->get('email'))
        {
            $check_email = User::where('email', $request->get('email'))->where('id', '!=', $user->id)->first();
            if ($check_email) {
                return JsonResponse::fail(__('views.email already exists'), 400);
            }
        }

        if ($request->get('user_name'))
        {
            $check_user_name = User::where('user_name', $request->get('user_name'))->where('id', '!=', $user->id)->first();
            if ($check_user_name) {
                return JsonResponse::fail(__('views.user_name already exists'), 400);
            }
        }

        $request->merge([
            'from_app' => true,
            'status' => 0,
            'user_name' => $request->get('user_name') != null ? $request->get('user_name') : $user->user_name,
            'is_edit_username' => $request->get('user_name') ? '1' : '0',
        ]);

        $user->update($request->only([
            'name',
            'email',
            'advertiser_number',
            'type',
            'device_token',
            'device_type',
            'license_number',
            'services_id',
            'members_id',
            'experiences_id',
            'courses_id',
            'lat',
            'lan',
            'address',
            'user_name',
            'is_edit_username',
            'onwer_name',
            'office_staff',
            'experience',
            'bio',
            'account_type',
            'advertiser_number',
            'fal_license_expiry',
            'fal_license_number',
        ]));
        $user = User::find($user->id);

        if ($request->company_logo) {
            $path = $request->file('company_logo')->store('public/users/photo', 's3');
            $user->update(['company_logo' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);
        }


        if (mb_strpos($_SERVER['HTTP_USER_AGENT'], "okhttp") === false) {
            return response()->success(__("views.update Profile success"), $user);
        }
        return response()->success(__("views.update Profile success"), $user);
    }

    public function updatePasswordByPhone2(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($rules->fails()) {
            return $rules->errors()->first();
        }
        $user = \Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        if (!$user->password) {
            if ($user == null) {
                return response()->error(__("views.not authorized"));
            }
            if ($user) {
                $apiToken = base64_encode(Str::random(32));
                $user->update(["password" => app('hash')->make($request->password)]);
                return response()->success(__("views.User Password Updated!"));

            }
            return response()->error(__("views.Incorrect Password!"));
        }
        if ($user->password) {
            if (!\Hash::check($request->old_password, $user->password)) {
                return response()->error(__("views.old password wrong"));
            }
            if ($user == null) {
                return response()->error(__("views.not authorized"));
            }
            if ($user) {
                $apiToken = base64_encode(Str::random(32));
                $user->update(["password" => app('hash')->make($request->password)]);
                return response()->success(__("views.User Password Updated!"));
            }
            return response()->error(__("views.Incorrect Password!"));
        }
    }

    public function uploadAvatar(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,bmp,png',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $path = $request->file('logo')->store('public/users/photo', 's3');
        $user->update(['logo' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);
        return response()->success(__('views.User Profile'), $user);


    }

    public function my_employee(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $client = Employee::where('user_id', $user->id);
        if ($request->get('emp_name')) {
            $search = trim($request->get('emp_name'));
            $client = $client->Where('emp_name', 'like', '%' . $search . '%');
        }
        $client = $client->paginate();
        if ($client) {
            return response()->success("Clients", $client);
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }
    }

    public function storeEmployee(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $user = User::find($user->id);
        if ($user->account_type != 'company') {
            $user->account_type = 'company';
            $user->save();

            /*  $estateCheck=Estate::where('user_id',$user->id)->update([
                  'company_id'=>$user->id
              ]);*/
        }
        $estateCheck = Estate::where('user_id', $user->id)->update([
            'company_id' => $user->id
        ]);

        $rules = Validator::make($request->all(), [


            'emp_name' => 'required',
            'emp_mobile' => 'required',
            'country_code' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $mobile = 0;

        if ($request->get('emp_mobile')) {
            if (startsWith($request->get('emp_mobile'), '0')) {
                $mobile = substr($request->get('emp_mobile'), 1, strlen($request->get('emp_mobile')));
            } else {
                if (startsWith($request->get('emp_mobile'), '00')) {
                    $mobile = substr($request->get('emp_mobile'), 2, strlen($request->get('emp_mobile')));
                } else {
                    $mobile = trim($request->get('emp_mobile'));
                }
            }
        }


        if ($user->mobile == $mobile) {
            return response()->error(__('لاتستطيع اضافة رقمك كموظف'));
        }

        $checkEmp = User::where('mobile', $mobile)
            //    ->where('country_code',$request->get('country_code'))
            ->first();


        $checkIsCompny = User::where('mobile', $mobile)
            ->where('is_employee', 0)
            ->first();

        if ($checkIsCompny) {
            return response()->error(__("رقم الجوال غير مضاف كشركة"));
        }


        $checkIsCompny2 = User::where('mobile', $mobile)
            ->whereIn('is_employee', [1, 2])
            ->where('employer_id', '!=', null)
            ->first();

        if ($checkIsCompny2) {
            return response()->error(__("الرقم مضاف لشركة سابقا"));
        }

        if ($checkEmp) {


            $checkEmpChek = Employee::where('emp_mobile', $mobile)
                ->where('country_code', $request->get('country_code'))
                ->first();

            if (!$checkEmpChek) {
                $checkFav = Employee::create([
                    'user_id' => $user->id,
                    'emp_name' => $request->get('emp_name'),
                    'emp_mobile' => $mobile,
                    'country_code' => $request->get('country_code'),


                ]);
            }

            $estates = Estate::where('user_id', $checkEmp->id)->first();

            if ($estates) {

                $estatesUpdated = Estate::where('user_id', $checkEmp->id)->update([
                    'company_id' => $user->id
                ]);


            }

            $checkEmp->is_employee = '1';
            $checkEmp->employer_id = $user->id;
            $checkEmp->save();
            $user->count_emp = $user->count_emp + 1;
            $user->save();
            return response()->success(__("views.Done"), []);
            //   return response()->error(__('views.emp found in user list') . ' # ' . $checkEmp->id);
        }


        $checkEmp2 = Employee::where('emp_mobile', $mobile)
            ->where('country_code', $request->get('country_code'))
            ->first();

        if ($checkEmp2) {
            return response()->error(__('views.emp found in emp list') . ' # ' . $checkEmp2->id);
        }
        $checkFav = Employee::create([
            'user_id' => $user->id,
            'emp_name' => $request->get('emp_name'),
            'emp_mobile' => $mobile,
            'country_code' => $request->get('country_code'),


        ]);


        $user->count_emp = $user->count_emp + 1;
        $user->save();
        return response()->success(__("views.Done"), []);
    }

    public function deleteEmployee(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [


            'emp_mobile' => 'required',
            'country_code' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $mobile = 0;

        if ($request->get('emp_mobile')) {
            if (startsWith($request->get('emp_mobile'), '0')) {
                $mobile = substr($request->get('emp_mobile'), 1, strlen($request->get('emp_mobile')));
            } else {
                if (startsWith($request->get('emp_mobile'), '00')) {
                    $mobile = substr($request->get('emp_mobile'), 2, strlen($request->get('emp_mobile')));
                } else {
                    $mobile = trim($request->get('emp_mobile'));
                }
            }
        }
        $checkEmp = User::where('mobile', $mobile)
            ->where('employer_id', $user->id)
            //    ->where('country_code',$request->get('country_code'))
            ->first();
        if ($checkEmp) {
            $estates = Estate::where('user_id', $checkEmp->id)->first();

            if ($estates) {

                $estatesUpdated = Estate::where('user_id', $checkEmp->id)->update([
                    'company_id' => null
                ]);


            }

            $checkEmp->is_employee = '0';
            $checkEmp->employer_id = null;
            $checkEmp->save();
            /* $user->count_emp=$user->count_emp-1;
             $user->save();*/


            //  return response()->success(__("views.Done"), []);
            //   return response()->error(__('views.emp found in user list') . ' # ' . $checkEmp->id);
        }
        $checkEmp2 = Employee::where('emp_mobile', $mobile)
            ->where('country_code', $request->get('country_code'))
            ->first();

        if ($checkEmp2) {

            $checkEmp2->delete();
            $user->count_emp = $user->count_emp - 1;
            $user->save();
            return response()->success(__("تم الحذف بنجاح"), []);
        }


    }

    public function user($id)
    {

        if ((filter_var($id, FILTER_VALIDATE_INT) !== false)) {

            $user = User::find($id);
        } else {
            $user = User::where('user_name', $id)->first();
        }


        if ($user == null) {
            return response()->error(__('views.not found'));
        }

        $user->count_visit = $user->count_visit + 1;
        $user->save();
        return response()->success(__('views.User Profile'), $user);
    }

    public function myMsg($id = null)
    {


        $user = Auth::user();


        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        if ($id != null) {

            $user_msg = User::find($id);
            if ($user_msg == null) {
                return response()->error(__("views.No User For Send Msg"));
            }
            /*   $msg = Msg::whereHas('sender')->whereHas('receiver')
            ->where('receiver_id', $id)
                   ->Where('sender_id', $user->id)
                   ->orWhere('sender_id', $id)
                   ->where('receiver_id', $user->id)
                   ->first();*/
            $msg = DB::table('msgs')
                ->whereRaw('receiver_id = ' . $id)
                ->whereRaw('sender_id = ' . $user->id)
                ->orwhereRaw('sender_id = ' . $id)
                ->whereRaw('receiver_id = ' . $user->id)
                ->first();

            if ($msg) {


                /* $msg = MsgDet::whereHas('sender')->whereHas('receiver')
                ->where('receiver_id', $id)
                     ->Where('sender_id', $user->id)
                     ->orWhere('sender_id', $id)
                     ->where('receiver_id', $user->id)
                     ->toSql();
 */

                $msg = DB::table('msgs_details')
                    ->join('users as u1', 'msgs_details.sender_id', '=', 'u1.id')
                    ->join('users as u2', 'msgs_details.receiver_id', '=', 'u2.id')
                    ->join('msgs as parent', 'parent.id', '=', 'msgs_details.msg_id')
                    ->select('u1.name as sender_name',
                        'u2.name as receiver_name',
                        'u2.mobile as receiver_mobile',
                        'u1.mobile as sender_mobile',
                        'msgs_details.*', 'u1.logo as sender_photo', 'u2.logo as receiver_photo',
                        'parent.body as parent_body',
                        'parent.title as parent_title',
                        'parent.created_at as parent_created_at'
                    )
                    ->whereRaw('  msgs_details.receiver_id' . '=' . $id)
                    ->whereRaw('  msgs_details.sender_id' . '=' . $user->id)
                    ->orwhereRaw('  msgs_details.sender_id' . '=' . $id)
                    ->whereRaw('  msgs_details.receiver_id' . '=' . $user->id)
                    ->whereRaw('  u1.deleted_at is null')
                    ->whereRaw('  u2.deleted_at is null')
                    ->whereRaw('  msgs_details.receiver_id' . '=' . $user->id)
                    // ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
                    ->get();


                /*  MsgDet::where('sender_id', $id)
                      ->where('receiver_id', $user->id)->update(['seen' => '1']);*/
                $res = DB::select('  UPDATE msgs_details
    SET seen = "1"   WHERE sender_id = ' . $id . ' and receiver_id =' . $user->id);


                foreach ($msg as $msgDet) {

                    $msgDet->from_me = $msgDet->sender_id == $user->id ? 1 : 0;


                }


                $msg = MsgDetResource::collection($msg);
                return response()->success(__("views.Msgs"), $msg);
            } else {

                $msg = Msg::create([
                    'sender_id' => $id,
                    'receiver_id' => $user->id,
                    'title' => 'مرحبا كيف يمكنني مساعدتك',
                    'body' => 'مرحبا كيف يمكنني مساعدتك',
                    'last_msg_date' => date('Y-m-d H:i:s'),
                ]);

                $msg_del = MsgDet::create([
                    'msg_id' => $msg->id,
                    'sender_id' => $id,
                    'receiver_id' => $user->id,
                    'title' => 'مرحبا كيف يمكنني مساعدتك',
                    'body' => 'مرحبا كيف يمكنني مساعدتك',

                ]);


                $msg = DB::table('msgs_details')
                    ->join('users as u1', 'msgs_details.sender_id', '=', 'u1.id')
                    ->join('users as u2', 'msgs_details.receiver_id', '=', 'u2.id')
                    ->join('msgs_details as parent', 'parent.id', '=', 'msgs_details.msg_id')
                    ->select('u1.name as sender_name',
                        'u2.name as receiver_name',
                        'msgs_details.*', 'u1.logo as sender_photo', 'u2.logo as receiver_photo',
                        'parent.body as parent_body',
                        'parent.title as parent_title',
                        'parent.created_at as parent_created_at',
                        'u2.mobile as receiver_mobile',
                        'u1.mobile as sender_mobile'
                    )
                    ->whereRaw('  msgs_details.receiver_id' . '=' . $id)
                    ->whereRaw('  msgs_details.sender_id' . '=' . $user->id)
                    ->orwhereRaw('  msgs_details.sender_id' . '=' . $id)
                    ->whereRaw('  msgs_details.receiver_id' . '=' . $user->id)
                    ->whereRaw('  u1.deleted_at is null')
                    ->whereRaw('  u2.deleted_at is null')
                    ->whereRaw('  msgs_details.receiver_id' . '=' . $user->id)
                    // ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
                    ->get();


                MsgDet::where('sender_id', $id)
                    ->where('receiver_id', $user->id)->update(['seen' => '1']);

                foreach ($msg as $msgDet) {

                    $msgDet->from_me = $msgDet->sender_id == $user->id ? 1 : 0;


                }


                $msg = MsgDetResource::collection($msg);
                return response()->success(__("views.Msgs"), $msg);
            }


            //  $msg = MsgResource::collection($msg);

        }

        /*$msg = Msg::where('receiver_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->get();*/
        /*     $msg = DB::table('msgs')
                 ->join('users as u1', 'msgs.sender_id', '=', 'u1.id')
                 ->join('users as u2', 'msgs.receiver_id', '=', 'u2.id')
                 ->join('msgs_details', 'msgs.id', '=', 'msgs_details.msg_id')
                 ->select('u1.name as sender_name',
                     'u2.name as receiver_name ',
                     'msg_id',
                     'msgs_details.*',
                     'msgs.*',
                     'msgs.id as order_id',
                     'u1.logo as sender_photo',
                     'u2.logo as receiver_photo',
                     'msgs.body as parent_body',
                     'msgs.title as parent_title',
                     'msgs.created_at as parent_created_at'
                 )

                 ->whereRaw('  msgs.receiver_id = ' . $user->id)
                 ->orwhereRaw('  msgs.sender_id = ' . $user->id)
                 ->orderByRaw(' parent_created_at', 'DESC')
                 ->groupByRaw(' msgs_details.msg_id')
                 ->get();
     */
        /*
         * select `u1`.`name` as `sender_name`, `u2`.`name` as `receiver_name `, `msg_id`, `msgs_details`.*, `msgs`.*, `msgs`.`id` as `order_id`, `u1`.`logo` as `sender_photo`, `u2`.`logo` as `receiver_photo`, `msgs`.`body` as `parent_body`, `msgs`.`title` as `parent_title`, `msgs`.`created_at` as `parent_created_at` from `msgs` inner join `users` as `u1` on `msgs`.`sender_id` = `u1`.`id` inner join `users` as `u2` on `msgs`.`receiver_id` = `u2`.`id` inner join `msgs_details` on `msgs`.`id` = `msgs_details`.`msg_id` where msgs.receiver_id = 142 or msgs.sender_id = 142 group by msgs_details.msg_id order by parent_created_at DESC
         */
        $msg = DB::select(' select `u1`.`name` as `sender_name`,
        `u2`.`name` as `receiver_name`,
        `u2`.`mobile` as `receiver_mobile`,
        `u1`.`mobile` as `sender_mobile `,
        `msg_id`, `msgs_details`.*, `msgs`.*,
        `msgs`.`id` as `order_id`, `u1`.`logo` as `sender_photo`,
        `u2`.`logo` as `receiver_photo`,
        `msgs`.`body` as `parent_body`,
        `msgs`.`title` as `parent_title`,
        `msgs`.`created_at` as `parent_created_at` ,
        `msgs`.`last_msg_date` as `parent_last_msg_date` from
  `msgs` inner join `users` as `u1` on `msgs`.`sender_id` = `u1`.`id`
      inner join `users` as `u2` on `msgs`.`receiver_id` = `u2`.`id`
      inner join `msgs_details` on `msgs`.`id` = `msgs_details`.`msg_id`
 where msgs.receiver_id = ' . $user->id . ' or msgs.sender_id =' . $user->id . ' group by msgs_details.msg_id order by parent_last_msg_date DESC
');


        foreach ($msg as $msgDet) {

            $msg_count = MsgDet::whereHas('sender')
                ->whereHas('receiver')
                ->where('seen', '0')
                ->where('receiver_id', $user->id)
                ->where('msg_id', $msgDet->id)
                ->count();


            $msg_last = MsgDet::whereHas('sender')->whereHas('receiver')
                ->where('msg_id', $msgDet->id)
                ->orderBy('id', 'desc')
                ->first();


            //   dd( isset($msg_last->receiver) && $msg_last->receiver_id != $user->id ? @$msg_last->receiver->name:'');
            /*     dd(isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->name :
                     isset($msg_last->receiver) && $msg_last->receiver_id != $user->id ? @$msg_last->receiver->name : '' ,
                     $msg_last->sender->name);
     */

            $sender = User::where('id', $msgDet->sender_id)->first();
            $receiver = User::where('id', $msgDet->receiver_id)->first();
            $msgDet->receiver_name = @$receiver->onwer_name;
            $msgDet->sender_name = @$sender->onwer_name;
            $msgDet->from_me = $msgDet->sender_id == $user->id ? 1 : 0;
            $msgDet->display_name = isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->onwer_name : @$msg_last->receiver->onwer_name;
            // $msgDet->display_name = @$msgDet->sender->name;
            //  $msgDet->display_logo = isset($msgDet->sender) && $msgDet->sender_id == $user->id ? @$msgDet->receiver->logo : isset($msgDet->sender) ? @$msgDet->sender->logo : 'Admin';
            $msgDet->display_logo = isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->logo : @$msg_last->receiver->logo;
            $msgDet->display_id = isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->id : @$msg_last->receiver->id;


            //    $msgDet->display_logo = @$msg_last->sender->logo;
            //  $msgDet->display_id = isset($msgDet->sender) && $msgDet->sender_id == $user->id ? @$msgDet->receiver->id : isset($msgDet->sender) ? @$msgDet->sender->id : 0;
            //    $msgDet->display_id = @$msg_last->sender->id ;
            $msgDet->count_not_read = $msg_count;

        }

        $msg = MsgResource::collection($msg);

        //  return($msg);

        //   $msg = MsgResource::collection($msg);

        return response()->success(__("views.Msgs"), $msg);
    }

    public function MsgDet($id)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $msg = MsgDet::where('msg_id', $id)
            ->get();


        $msg = DB::table('msgs_details')
            ->join('users as u1', 'msgs_details.sender_id', '=', 'u1.id')
            ->join('users as u2', 'msgs_details.receiver_id', '=', 'u2.id')
            ->join('msgs as parent', 'parent.id', '=', 'msgs_details.msg_id')
            ->select('u1.name as sender_name',
                'u2.name as receiver_name ',
                'u2.mobile as receiver_mobile ',
                'u1.mobile as sender_mobile ',
                'msgs_details.*', 'u1.logo as sender_photo', 'u2.logo as receiver_photo',
                'parent.body as parent_body',
                'parent.title as parent_title',
                'parent.created_at as parent_created_at'
            )
            ->whereRaw('  u1.deleted_at is null')
            ->whereRaw('  u2.deleted_at is null')
            ->whereRaw('  msgs_details.msg_id  =' . $id)
            // ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
            ->get();


        /*  MsgDet::where('msg_id', $id)
              ->where('receiver_id', $user->id)
              ->update(['seen' => '1']);*/
        $res = DB::select('  UPDATE msgs_details
    SET seen = "1"   WHERE receiver_id = ' . $user->id);
        foreach ($msg as $msgDet) {

            $msgDet->from_me = $msgDet->sender_id == $user->id ? 1 : 0;


        }

        /*  $parent = Msg::findOrFail($id);

          foreach ($msg as $msgItem) {
              $msgItem->form_me = $msgItem->sender_id == $user->id ? 1 : 0;

          }

          $msg = MsgResource::collection($msg);*/

        $msg = MsgDetResource::collection($msg);
        return response()->success(__("views.Msgs"), $msg);
    }


    public function sendMsg(Request $request)
    {

        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [


            'user_id' => 'required|exists:users,id',
            'title' => 'required',
            'body' => 'required',
            //  'parent_id' => 'sometimes|required'


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        if ($user->id == $request->get('user_id')) {
            return response()->error(__("same user id "));
        }
        /*$msg = Msg::where('receiver_id', $request->get('user_id'))
            ->Where('sender_id', $user->id)
            ->orWhere('sender_id', $request->get('user_id'))
            ->where('receiver_id', $user->id)
            ->first();*/

        $msg = DB::table('msgs')
            ->join('users as u1', 'msgs.sender_id', '=', 'u1.id')
            ->join('users as u2', 'msgs.receiver_id', '=', 'u2.id')
            ->select('u1.name as sender_name',
                'u2.name as receiver_name ',
                'msgs.*', 'u1.logo as sender_photo', 'u2.logo as receiver_photo',
                'msgs.body as parent_body',
                'msgs.title as parent_title',
                'msgs.created_at as parent_created_at'
            )
            ->whereRaw('u1.deleted_at is null')
            ->whereRaw('u2.deleted_at is null')
            ->whereRaw('receiver_id = ' . $request->get('user_id'))
            ->whereRaw('sender_id = ' . $user->id)
            ->orwhereRaw('sender_id = ' . $request->get('user_id'))
            ->whereRaw('receiver_id = ' . $user->id)
            ->first();


        //      dd($msg,$user,$request->all());


        if ($msg) {
            $msgDet = MsgDet::create([
                'receiver_id' => $request->get('user_id'),
                'sender_id' => $user->id,
                'title' => $request->get('title'),
                'body' => $request->get('body'),
                'msg_id' => $msg->id,
            ]);

            // $res = DB::select('  UPDATE msgs SET body =  '.$request->get('body').' , title='.$request->get('title').'   WHERE id = ' . $msg->id);
            DB::table('msgs')
                ->where('id', $msg->id)
                ->update(array('body' => $request->get('body'), 'title' => $request->get('title'), 'last_msg_date' => date('Y-m-d H:i:s')));

            $msg->from_me = $msg->sender_id == $user->id ? 1 : 0;


            $client = User::find($request->get('user_id'));
            //    dd([$msg]);
            if ($client) {
                $push_data = [
                    'title' => 'لديك رسالة جديدة من ' . $user->onwer_name,
                    'body' => $request->get('body'),
                    'id' => $msg->id,
                    'user_id' => $user->id,
                    'type' => 'chat',
                ];

                $note = NotificationUser::create([
                    'user_id' => $client->id,
                    'title' => 'لديك رسالة جديدة #' . $msg->id,
                    'type' => 'chat',
                    'type_id' => $msg->id,
                ]);
                $fcm_token = FcmToken::where('user_id', $client->id)->get();
                foreach ($fcm_token as $token) {
                    send_push($token->token, $push_data, $token->type);
                }
            }


            $msg = MsgResource::collection([$msg]);

            return response()->success(__("views.Msg Send"), [$msg]);
        } else {
            return response()->error(__("views.Massage is empty"));
        }

    }


    public function notification(Request $request)
    {


        $user = auth()->user();

        $count_one = NotificationUser::where('user_id', $user->id)
            ->whereIn('type', ['request', 'fund_request', 'fund_offer', 'offer'])->count();

        $count_tow = NotificationUser::where('user_id', $user->id)
            ->whereIn('type', ['chat', 'employee'])->count();

        $count_three = NotificationUser::where('user_id', $user->id)
            ->whereIn('type', ['rate_offer', 'rate_estate'])->count();


        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = NotificationUser::where('user_id', $user->id);
        if ($request->get('type')) {
            $types = explode(',', $request->get('type'));


            $user = $user->whereIn('type', $types);
        }
        $user = $user->orderBy('id', 'desc')->paginate();


        return \Illuminate\Support\Facades\Response::json([
            "status" => true,
            "follow_request" => $count_one,
            "mange_request" => $count_tow,
            "news_report" => $count_three,
            "message" => "اشعاراتي",
            "data" => $user,
            'code' => 200
        ]);


        return response()->success(__('views.NotificationUser'), $user);
        //  return ['data' => $user];
    }

    public function forgetPassword(Request $request)
    {
        $rules = Validator::make($request->all(), [


            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9',

            'country_code' => 'sometimes|required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $username = $request->mobile;
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


        $old_mobile = "";
        $username_column = 'mobile';


        $request->merge([
            $username_column => $mobile,

        ]);

        $credentials = $request->only($username_column, 'password', 'status');

        $class = new User();

        $user = $class::where($username_column, $credentials[$username_column])->first();


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($mobile, $country_code);
        $smscode = '';


        if ($user) {
//            if (!$user->confirmation_password_code) {
            //   if (!env('SKIP_CONFIRM')) {
            $smscode = substr(str_shuffle("0123456789"), 0, 6);
            //  } else {
            //     $smscode = 123456;
            //  }
            $user->confirmation_password_code = $smscode;
            $user->save();
//            } else {
//                $smscode = $user->confirmation_password_code;
//            }


            $unifonicMessage = new UnifonicMessage();
            $unifonicClient = new UnifonicClient();
            $unifonicMessage->content = "Your Verification Code Is: ";
            $to = $country_code . $user->mobile;
            $co = $smscode;
            $data = $unifonicClient->sendVerificationCode($to, $co, $unifonicMessage);
            Log::channel('single')->info($data);
            Log::channel('slack')->info($data);
            //  return $data;

            return response()->success("Otp Sent", ['code' => $smscode]);
        } else {
            return response()->json(["message" => __('views.Check Your Mobile !')]);
        }


        /*
                $data        = array('name' => "ProviderAPI");
                $newPassword = base64_encode(Str::random(12));






                $user->update(['password' => app('hash')->make($newPassword)]);
                Mail::send(array(), $data, function ($message) use ($user, $newPassword) {
                    $message->to($user->email, 'Forgot Password')
                        ->subject("Forgot Password")
                        ->from('xyz@gmail.com', 'Virat Gandhi')
                        ->setBody("<p>" . $newPassword . "</p>", 'text/html');
                });*/


    }

    public function updatePasswordByPhone(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'code' => 'required',
            // 'country_code'              => 'sometimes|required',

        ]);


        if ($rules->fails()) {


            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::where('confirmation_password_code', $request->get('code'))->first();


        if ($user) {

            $user->update(["password" => app('hash')->make($request->password)]);
            return response()->success("User Password Updated!", $user);

        }
        return response()->error("Incorrect Password!");
    }

    public function DeleteMyAccount()
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        $user = User::query()->find($user->id);
        if (!$user) {
            return response()->error(__('views.not found'));
        }
        $restore_code = substr(str_shuffle("0123456789"), 0, 6);

        $user->restore_code = $restore_code;
        $user->restored_at = null;
        $user->save();
//        $user->delete();
        return response()->success(__('views.Done'), []);
    }

    public function RequestRestoreMyAccount(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9',


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


        $request->merge([
            'mobile' => $mobile,
        ]);


        $rules = Validator::make($request->all(), [


            'mobile' => 'required',

            'country_code' => 'required'
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        /*
                if (!env('SKIP_CONFIRM')) {
                    $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
                } else {
                    $confirmation_code = 123456;
                }*/

        $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
        $request->merge([
            //  'password'          => app('hash')->make($request->input('password')),
            'confirmation_code' => $confirmation_code,


            //  'mobile'            => $mobile,
        ]);


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($request->get('mobile'), $country_code);


        $user = User::withTrashed()
            ->where('mobile', $mobile)
            // ->whereDate('deleted_at', '<=', Carbon::now()->addDays(30))
            ->orderBy('id', 'desc')
            ->first();

        // dd($user->deleted_at);
        if ($user) {
            $user->restore_code = $confirmation_code;
            $user->save();


            //    if (app()->environment('production')) {
            dispatch(new OtpJob($user));

            // }

            $user = User::find($user->id);
            return response()->success(__('views.We Send Activation Code To Your Mobile',
                ['code' => $confirmation_code]),
                ['code' => $confirmation_code]);
        } else {
            return response()->error(__('views.not authorized'));
        }


        // return ['data' => $user];
    }

    public function RestoreMyAccount(Request $request)
    {


        $validator = $this->validate($request, [
            'mobile' => 'required',
            'code' => 'required',
            'country_code' => 'required',

        ]);

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
        $country_code = $request->get('country_code', 966);


        $user_mobile = checkIfMobileStartCode($mobile, $country_code);
        $user = User::withTrashed()
            ->where('mobile', $mobile)
            ->where('restore_code', $request->get('code'))
            ->whereDate('deleted_at', '<', Carbon::now()->addDays(30))
            ->orderBy('id', 'desc')
            ->first();
        if (!$user) {
            return response()->error(__("views.not found"));
        }

        $success = 'false';
        if ($user->restore_code == $request->get('code')) {
            $success = 'true';
        }

        //    $verification = $this->unifonicClient->verify($user_mobile, $request->code);


        // $developer_code = '123456';
        if (isset($success) && $success == 'true' && $request->get('code') == $user->restore_code) {
            {


                $user = User::withTrashed()
                    ->where('mobile', $mobile)
                    ->where('restore_code', $request->get('code'))
                    ->whereDate('deleted_at', '<', Carbon::now()->addDays(30))
                    ->orderBy('id', 'desc')
                    ->first();

                $userRestore = User::withTrashed()->find($user->id)->restore();

                //Update Current User Verifed_at timestamp
                $user->update([
                    // "mobile_verified_at" => date(now()),
                    // "email_verified_at" => date(now()),
                    "restored_at" => date(now()),
                    "status" => 'active',
                    'restore_code' => ''
                    //  'email'              => $request->get('email')
                ]);
                $user = User::whereMobile($user->mobile)->first();


                return response()->success(__("views.Phone Verified"), $user);
            }

        }
        return response()->error(__("views.Incorrect Code"));
    }

    public function resendCodes(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9',


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


        $request->merge([
            'mobile' => $mobile,
        ]);


        $rules = Validator::make($request->all(), [
            /*    'name'                  => 'required|max:255',
                'mobile'                => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
                'email'                 => 'required|email|unique:users,email,null,id,deleted_at,NULL',
                'password'              => 'required|min:6|confirmed',
                'password_confirmation' => 'required',

                'device_token' => 'required',
                'device_type'  => 'required',
                'type'         => 'required',
                'country_code' => 'required'*/

            'mobile' => 'required',

            'country_code' => 'required'
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        /*
                if (!env('SKIP_CONFIRM')) {
                    $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
                } else {
                    $confirmation_code = 123456;
                }*/

        $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
        $request->merge([
            //  'password'          => app('hash')->make($request->input('password')),
            'confirmation_code' => $confirmation_code,


            //  'mobile'            => $mobile,
        ]);


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($request->get('mobile'), $country_code);


        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            $user->confirmation_code = $confirmation_code;
            $user->save();


            //    if (app()->environment('production')) {
            dispatch(new OtpJob($user));

            // }

            $user = User::find($user->id);
            return response()->success(__('views.We Send Activation Code To Your Mobile',
                ['code' => $confirmation_code]),
                ['code' => $confirmation_code]);
        } else {
            return response()->error(__('views.not authorized'));
        }


        // return ['data' => $user];
    }


    public function myOrder(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $order = collect();


        //myRequest
        $myRequest = EstateRequest::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orwhere('user_id', $user->related_company);
        });

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


            $estate = explode(',', $request->get('estate_type_id'));


            $myRequest = $myRequest->whereIn('estate_type_id', $estate);
        }

        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $myRequest = $myRequest->whereIn('city_id', $estate);
        }

        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $myRequest = $myRequest->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $myRequest = $myRequest->where('operation_type_id', 1);
            } else {
                $myRequest = $myRequest->where('operation_type_id', 3);
            }


        }

        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {
            $myRequest->where('price_from', '>=', $request->get('price_from'));
            $myRequest->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_from') != null && $request->get('area_to') != null) {
            $myRequest->where('area_from', '>=', $request->get('area_from'));
            $myRequest->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search') && $request->get('search') != null) {
            $myRequest = $myRequest->where('request_type', 'like', '%' . $request->get('area_to') . '%');
        }

        foreach ($myRequest->get() as $item) {
            $order->push([
                'id' => $item->id,
                'type' => 'my_request',
                'estate_type_name' => (string)$item->estate_type_name,
                'operation_type_name' => (string)$item->operation_type_name,
                'city_name' => $item->city_name ?? null,
                'neighborhood_name' => $item->neighborhood_name ?? null,
                'address' => (string)$item->address,
                'time' => (string)$item->time,
                "area_from" => (string)$item->area_from,
                "area_to" => (string)$item->area_to,
                "price_from" => (string)$item->price_from,
                "price_to" => (string)$item->price_to,
                "room_numbers" => (string)$item->room_numbers,
                "created_at" => $item->created_at,
                "estate_use_type" => $item->estate_use_type,
                "estate_use_name" => $item->estate_use_name,
                "estate_id" => null,
                "statue" => null,
                "estate_user_id" => null,
                'estate_comforts' => count($item->comforts) > 0 ? ComfortsResource::collection($item->comforts) : null,
                'offer_count' => $item->offers->count(),
                'offer_pending_count' => $item->offers->where('status', 'pending')->count(),
                'offer_preview_count' => $item->offers->whereIn('status', ['set_time', 'accept_time', 'preview'])->count(),
                'offer_wait_accept_count' => $item->offers->where('status', 'accept')->count(),
                'offer_reject_count' => $item->offers->whereIn('status', ['reject', 'cancel'])->count(),
                'times' => null,
                'accept_time' => null
            ]);
        }

        //show_rate_Request
        $RateRequest = RateRequest::where('user_id', $user->id)->get();
        foreach ($RateRequest as $item) {
            $order->push([
                'id' => $item->id,
                'type' => 'rate_Request',
                'estate_type_name' => (string)$item->estate_type_name,
                'operation_type_name' => (string)$item->operation_type_name,
                'city_name' => null,
                'neighborhood_name' => null,
                'address' => (string)$item->address,
                'time' => (string)$item->created_at->diffForHumans(),
                "area_from" => null,
                "area_to" => null,
                "price_from" => null,
                "price_to" => null,
                "room_numbers" => null,
                "statue" => $item->statue,
                "area" => (string)$item->area,
                "created_at" => $item->created_at,
                "estate_use_type" => null,
                "estate_use_name" => null,
                "estate_id" => $item->estate_id ? (string)$item->estate_id : null,
                "estate_user_id" => null,
                'estate_comforts' => null,
                'offer_count' => RateOfferRequest::where('request_rate_id', $item->id)->count(),
                'offer_pending_count' => 0,
                'offer_preview_count' => 0,
                'offer_wait_accept_count' => 0,
                'offer_reject_count' => 0,
                'times' => null,
                'accept_time' => null,
                'personal_id_number' => null,
                'personal_name' => null,
                'personal_mobile_number' => null,
                'personal_monthly_net_salary' => null,
                'employer_name' => null,
                'employer_type' => null,
                'real_estate_product_type' => null,
                'real_estate_property_information' => null,
                'real_estate_property_price' => null,
                'rea_estate_property_age' => null,
            ]);
        }


        //show review Request
        $reviewRequest = EstateRequestPreview::where('user_id', $user->id)->get();
        foreach ($reviewRequest as $item) {
            $order->push([
                'id' => $item->id,
                'type' => 'review_Request',
                'estate_type_name' => (string)@$item->estate->estate_type_name,
                'operation_type_name' => (string)@$item->estate->operation_type_name,
                'city_name' => @$item->estate->city_name,
                'neighborhood_name' => @$item->estate->neighborhood_name,
                'address' => (string)@$item->estate->full_address,
                'time' => (string)@$item->created_at->diffForHumans(),
                "area_from" => null,
                "area_to" => null,
                "price_from" => null,
                "price_to" => null,
                "room_numbers" => null,
                "status" => $item->status,
                "area" => (string)@$item->estate->total_area,
                "total_price" => (string)@$item->estate->total_price,
                "created_at" => $item->created_at,
                "estate_use_type" => null,
                "estate_use_name" => null,
                "estate_id" => (string)@$item->estate_id,
                "estate_user_id" => @$item->estate->user_id ? (string)@$item->estate->user_id : null,
                'estate_comforts' => $item->estate ? count($item->estate->comforts) > 0 ? ComfortsResource::collection(@$item->estate->comforts) : null : null,
                'offer_count' => null,
                'offer_pending_count' => 0,
                'offer_preview_count' => 0,
                'offer_wait_accept_count' => 0,
                'offer_reject_count' => 0,
                'times' => $item->times,
                'accept_time' => $item->accept_time,
                'personal_id_number' => null,
                'personal_name' => null,
                'personal_mobile_number' => null,
                'personal_monthly_net_salary' => null,
                'employer_name' => null,
                'employer_type' => null,
                'real_estate_product_type' => null,
                'real_estate_property_information' => null,
                'real_estate_property_price' => null,
                'rea_estate_property_age' => null,
            ]);
        }

        $fundRequest = FundingRequest::where('user_id', $user->id)->get();
        foreach ($fundRequest as $item) {
            $order->push([
                'id' => $item->id,
                'type' => 'fund_request',
                'estate_type_name' => null,
                'operation_type_name' => null,
                'city_name' => null,
                'neighborhood_name' => null,
                'address' => null,
                'time' => (string)@$item->created_at->diffForHumans(),
                "area_from" => null,
                "area_to" => null,
                "price_from" => null,
                "price_to" => null,
                "room_numbers" => null,
                "status" => null,
                "area" => null,
                "total_price" => null,
                "created_at" => $item->created_at,
                "estate_use_type" => null,
                "estate_use_name" => null,
                "estate_id" => null,
                "estate_user_id" =>null,
                'estate_comforts' => null,
                'offer_count' => null,
                'offer_pending_count' => 0,
                'offer_preview_count' => 0,
                'offer_wait_accept_count' => 0,
                'offer_reject_count' => 0,
                'times' => null,
                'accept_time' => null,
                'personal_id_number' => $item->personalIDNumber,
                'personal_name' => $item->personalName,
                'personal_mobile_number' => $item->personalMobileNumber,
                'personal_monthly_net_salary' => $item->personalMonthlyNetSalary,
                'employer_name' => $item->employerName,
                'employer_type' => $item->employerType,
                'real_estate_product_type' => $item->realEstateProductType,
                'real_estate_property_information' => $item->realEstatePropertyInformation,
                'real_estate_property_price' => $item->realEstatePropertyPrice,
                'rea_estate_property_age' => $item->realEstatePropertyAge,
            ]);
        }



        if ($request->type) {
            if ($request->type == 'my_request') {
                $order = $order->where('type', 'my_request');
            } else if ($request->type == 'rate_Request') {
                $order = $order->where('type', 'rate_Request');
            } else if ($request->type == 'review_Request') {
                $order = $order->where('type', 'review_Request');
            }  else if ($request->type == 'fund_request') {
                $order = $order->where('type', 'fund_request');
            } else {
                $order = $order->where('type', 'ss');
            }
        }

        $order = $order->sortByDesc('created_at');
        return response()->success(__('views.success'), $order->values()->all());
    }

    public function myOffer(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $order = collect();


        //myRequest
        $myRequest = EstateRequest::whereHas('offers', function ($q) use ($user) {
            $q->where('provider_id', $user->id);
        });

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


            $estate = explode(',', $request->get('estate_type_id'));


            $myRequest = $myRequest->whereIn('estate_type_id', $estate);
        }

        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $myRequest = $myRequest->whereIn('city_id', $estate);
        }

        if ($request->get('estate_pay_type') && $request->get('estate_pay_type') != null) {

            if ($request->get('estate_pay_type') == 'is_rent') {
                //    $Mechanic = $Mechanic->where('request_type', 'rent');
                $myRequest = $myRequest->where('operation_type_id', 2);
            }
            if ($request->get('estate_pay_type') == 'is_pay') {
                //  $Mechanic = $Mechanic->where('request_type', 'pay');
                $myRequest = $myRequest->where('operation_type_id', 1);
            } else {
                $myRequest = $myRequest->where('operation_type_id', 3);
            }


        }

        if ($request->get('price_from') && $request->get('price_to') && $request->get('price_to') != 0 && $request->get('price_from') != null && $request->get('price_to') != null) {
            $myRequest->where('price_from', '>=', $request->get('price_from'));
            $myRequest->where('price_to', '<=', $request->get('price_to'));
        }

        if ($request->get('area_from') && $request->get('area_to') && $request->get('area_to') != 0 && $request->get('area_from') != null && $request->get('area_to') != null) {
            $myRequest->where('area_from', '>=', $request->get('area_from'));
            $myRequest->where('area_to', '<=', $request->get('area_to'));
        }

        if ($request->get('search') && $request->get('search') != null) {
            $myRequest = $myRequest->where('request_type', 'like', '%' . $request->get('area_to') . '%');
        }

        foreach ($myRequest->get() as $item) {
            $order->push([
                'id' => $item->id,
                'type' => 'my_request',
                'estate_type_name' => (string)$item->estate_type_name,
                'operation_type_name' => (string)$item->operation_type_name,
                'city_name' => $item->city_name ?? null,
                'neighborhood_name' => $item->neighborhood_name ?? null,
                'address' => (string)$item->address,
                'time' => (string)$item->time,
                "area_from" => (string)$item->area_from,
                "area_to" => (string)$item->area_to,
                "price_from" => (string)$item->price_from,
                "price_to" => (string)$item->price_to,
                "room_numbers" => (string)$item->room_numbers,
                "created_at" => $item->created_at,
                "estate_use_type" => $item->estate_use_type,
                "estate_use_name" => $item->estate_use_name,
                "estate_id" => null,
                "statue" => null,
                "estate_user_id" => null,
                'estate_comforts' => count($item->comforts) > 0 ? ComfortsResource::collection($item->comforts) : null,
                'offer_count' => $item->offers->where('provider_id', $user->id)->count(),
                'offer_pending_count' => $item->offers->where('provider_id', $user->id)->where('status', 'pending')->count(),
                'offer_preview_count' => $item->offers->where('provider_id', $user->id)->whereIn('status', ['set_time', 'accept_time', 'preview'])->count(),
                'offer_wait_accept_count' => $item->offers->where('provider_id', $user->id)->where('status', 'accept')->count(),
                'offer_reject_count' => $item->offers->where('provider_id', $user->id)->whereIn('status', ['reject', 'cancel'])->count(),
                'times' => null,
                'accept_time' => null,
                'user_name' => @$item->user->onwer_name,
            ]);
        }


        //show review Request
        $reviewRequest = EstateRequestPreview::where('owner_id', $user->id)->get();
        foreach ($reviewRequest as $item) {
            $order->push([
                'id' => $item->id,
                'type' => 'review_Request',
                'estate_type_name' => (string)@$item->estate->estate_type_name,
                'operation_type_name' => (string)@$item->estate->operation_type_name,
                'city_name' => @$item->estate->city_name,
                'neighborhood_name' => @$item->estate->neighborhood_name,
                'address' => (string)@$item->estate->full_address,
                'time' => (string)@$item->created_at->diffForHumans(),
                "area_from" => null,
                "area_to" => null,
                "price_from" => null,
                "price_to" => null,
                "room_numbers" => null,
                "status" => $item->status,
                "area" => (string)@$item->estate->total_area,
                "total_price" => (string)@$item->estate->total_price,
                "created_at" => $item->created_at,
                "estate_id" => (string)@$item->estate_id,
                "estate_user_id" => @$item->estate->user_id ? (string)@$item->estate->user_id : null,
                'estate_comforts' => $item->estate ? count($item->estate->comforts) > 0 ? ComfortsResource::collection(@$item->estate->comforts) : null : null,
                'offer_count' => null,
                'offer_pending_count' => 0,
                'offer_preview_count' => 0,
                'offer_wait_accept_count' => 0,
                'offer_reject_count' => 0,
                'times' => $item->times,
                'accept_time' => $item->accept_time,
                'user_name' => @$item->user->onwer_name,
            ]);
        }

        if ($request->type) {
            if ($request->type == 'my_request') {
                $order = $order->where('type', 'my_request');
            } else if ($request->type == 'review_Request') {
                $order = $order->where('type', 'review_Request');
            } else {
                $order = $order->where('type', 'ss');
            }
        }

        $order = $order->sortByDesc('created_at');
        return response()->success(__('views.success'), $order->values()->all());
    }


    public function check_license_number(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $validator = Validator::make($request->all(), [
            'license_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }

        $license_number = $request->license_number;
        $user = User::find($user->id);

        if ($user->is_iam_complete == 1)
        {
         $advertiserId = @$user->identity;



            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api2.aqarz.sa/advertisement_validator.php',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('adLicenseNumber' => $license_number ,'advertiserId' => $advertiserId),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response);

            if ($response)
            {
               $code = @$response->Header->Status->Code;
               if ($code)
               {
                   if ($code == 200)
                   {

                       $user->update(['license_number' => $license_number]);
                       return response()->success(__('views.success'), $response);

                   }else{
                          return response()->error(@$response->Body->error->message);
                   }
               }else{
                   return response()->error("error in server");
               }
            }else{
                return response()->error("error in server");
            }

        }else{
            return response()->error(__("views.you must verify iam first"));
        }
    }

}
