<?php

namespace App\Http\Controllers\DashBoard\Fund;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\MsgDetResource;
use App\Http\Resources\MsgResource;
use App\Http\Resources\OfferAttachDateDataResource;
use App\Http\Resources\OfferDateDataResource;
use App\Http\Resources\UserDateDataResource;
use App\Jobs\OtpJob;
use App\Models\dashboard\Admin;
use App\Models\v2\Client;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\Favorite;
use App\Models\v2\Msg;
use App\Models\v2\MsgDet;
use App\Models\v2\NotificationUser;
use App\Models\v2\RequestFund;
use App\Models\v3\FundRequestOffer;
use App\Models\v4\FcmToken;
use App\Unifonic\UnifonicMessage;
use App\User;
use App\Helpers\JsonResponse;
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


    public function store(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',

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
            'mobile' => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'device_token' => 'required',
            'device_type' => 'required',
            'type' => 'required',
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
            'api_token' => hash('sha512', time()),
            'status' => 0,

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
            'user_name'

        ]));


        $user = User::find($user->id);

        $user->user_name = 'aqarz_user_' . $user->id;
        $user->save();

        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($user->mobile, $country_code);


        $user->mobile = $user_mobile;


        //    if (app()->environment('production')) {
        dispatch(new OtpJob($user));

        // }

        $user = User::find($user->id);
        return response()->success(__('views.We Send Activation Code To Your Mobile'), ['code' => $confirmation_code]);
        // return ['data' => $user];
    }


    public function show()
    {


        $user = auth()->guard('Admin')->user();


        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = Admin::find($user->id);
        return response()->success(__('views.User Profile'), $user);
        //  return ['data' => $user];
    }


    public function update(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'name' => 'sometimes|required',
            'email' => 'sometimes|required',
            'mobile' => 'sometimes|required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $admin = auth()->guard('Admin')->user();

        Admin::find($admin->id)
            ->update($request->only([
                'name',
                'mobile',
                'email'


            ]));


        if ($request->get('password')) {
            if (!\Hash::check($request->get('old_password'), $admin->password)) {


                return response()->error(__('views.password_incorrect'));

            }
            $user = Admin::find($admin->id);
            $user->password = \Hash::make($request->get('password'));
            $user->save();
        }

        $user = Admin::find($user->id);
        return response()->success(__('views.User Profile'), $user);
    } //<--- End Method


    public function requestOtp(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'country_code' => 'sometimes|required|exists:countries,phone_code',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = auth()->guard('Bank')->user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        if ($user->mobile) {
            if (startsWith($request->get('mobile'), '0')) {
                $mobile = substr($user->mobile, 1, strlen($user->mobile));
            } else {
                if (startsWith($request->get('mobile'), '00')) {
                    $mobile = substr($user->mobile, 2, strlen($user->mobile));
                } else {
                    $mobile = trim($user->mobile);
                }
            }

        }
        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($mobile, $country_code);


        //  $data                     = $request->only('phone', 'country_code');
        $unifonicMessage = new UnifonicMessage();
        $unifonicMessage->content = "Your Verification Code Is: ";
        $to = $user_mobile;
        $data = $this->unifonicClient->sendVerificationCode($to, $unifonicMessage);
        Log::channel('single')->info($data);
        return response()->success("Otp Sent", null);

    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */


    public function fcm(Request $request)
    {
        $this->validate($request, [
            "firebase_token" => "required",
        ]);
        $user = $request->user();
        $user->update(["firebase_token" => $request->firebase_token, "device_type" => $request->device_type]);
        return response()->success("Firebase Token Saved");
    }


    public function forgetPassword(Request $request)
    {
        $rules = Validator::make($request->all(), [

            'mobile' => 'required',
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

        $class = new Admin();

        $user = $class::where($username_column, $credentials[$username_column])
            ->where('type', app('request')->header('role'))
            ->first();


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($mobile, $country_code);
        $smscode = '';
        if (!$user->confirmation_password_code) {
            //   if (!env('SKIP_CONFIRM')) {
            $smscode = substr(str_shuffle("0123456789"), 0, 6);
            //  } else {
            //     $smscode = 123456;
            //  }
            $user->confirmation_password_code = $smscode;
            $user->save();
        } else {
            $smscode = $user->confirmation_password_code;
        }


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

        return response()->json(["message" => __('auth.Check YOur Mobile !')]);
    }


    public function ResetToken(Request $request)
    {

        $rules = Validator::make($request->all(), [

            'confirmation_password_code' => 'required',
            // 'country_code'              => 'sometimes|required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $confirmation_code = trim($request['confirmation_password_code']);


        $western_arabic = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $eastern_arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $confirmation_code = str_replace($eastern_arabic, $western_arabic, $confirmation_code);
        $confirmation_code = str_replace(['+', '-'], '', filter_var($confirmation_code, FILTER_SANITIZE_NUMBER_INT));

        /* $user = Client::where('confirmed', 1)->where('confirmation_code', $confirmation_code)->first(); */
        $user = Admin::where('confirmation_password_code', $confirmation_code)->first();


        if ($user) {


            return response()->success("Code True", ['code' => $request['confirmation_password_code']]);


            if (!env('SKIP_CONFIRM')) {
                $password = substr(str_shuffle("123456789"), 0, 6);
            } else {
                $password = 123456;
            }
            $hash = app('hash')->make($password);
            $user->password = $hash;
            //   $user->confirmation_code = null;
            $user->save();
            $country_code = $request->get('country_code', 966);
            $user_mobile = checkIfMobileStartCode($user->mobile, $country_code);

            $unifonicMessage = new UnifonicMessage();
            $unifonicMessage->content = "Your New Password Is: $password";
            $to = $user_mobile;
            $data = $this->unifonicClient->send($to, $unifonicMessage);
            Log::channel('single')->info($data);


            $data = ['name' => "ProviderAPI"];
            Mail::send([], $data, function ($message) use ($user, $password) {
                $message->to($user->email, 'Forgot Password')
                    ->subject("New Password")
                    ->from('experto@mail.com', 'Virat Gandhi')
                    ->setBody("<p>" . $password . "</p>", 'text/html');
            });


            if (mb_strpos($_SERVER['HTTP_USER_AGENT'], "okhttp") === false) {
                return response()->success("Otp Sent", null);
            }
            return response(null, Response::HTTP_NO_CONTENT);
        } else {


            return \Illuminate\Support\Facades\Response::json([
                "status" => false,
                "message" => 'code not valid',
                'errors' => null,

            ], 400);


            throw ValidationException::withMessages([
                'confirmation_code' => [trans('messages.confirmation_mismatch')],
            ]);
        }

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
        $user = Admin::where('confirmation_password_code', $request->get('code'))->first();


        if ($user) {

            $user->update(["password" => app('hash')->make($request->password)]);
            return response()->success("Admin Password Updated!", $user);

        }
        return response()->error("Incorrect Password!");
    }


    public function updatePasswordByPhone2(Request $request)
    {


        $rules = Validator::make($request->all(), [

            //  'password' => 'required',
            'password' => 'required',
            //  'password_confirmation' => 'required',
            'old_password'
            // 'country_code'              => 'sometimes|required',

        ]);


        if ($rules->fails()) {

            return $rules->errors()->first();
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = \Auth::user();


        //  dd(\Hash::check($request->old_password, $user->password));

        if (!\Hash::check($request->old_password, $user->password)) {
            return response()->error("old password wrong");
        }

        if ($user == null) {
            return response()->error("not authorized");
        }
        if ($user) {
            $apiToken = base64_encode(Str::random(32));
            $user->update(["password" => app('hash')->make($request->password)]);
            return response()->success("User Password Updated!");

        }
        return response()->error("Incorrect Password!");
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
            return response()->error("not authorized");
        }


        $path = $request->file('logo')->store('public/users/photo', 's3');
        //    $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


        $user->update(['logo' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);

        return response()->success(__('views.User Profile'), $user);


    }


    protected function loggedOut(Request $request)
    {
        //
    }

    public function logout(Request $request)
    {
        auth()->guard('Admin')->logout();


        /* if ($response = $this->loggedOut($request)) {
             return $response;
         }*/

        return response()->success("logout!", []);
    }


    public function cancel_fund_offer($id)
    {


        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $fund_offer = FundRequestOffer::findOrFail($id);


        if ($fund_offer) {
            $requests = \App\Models\v3\RequestFund::where('uuid', $fund_offer->uuid)->first();
            $fund_offer->reason = 'العرض غير مناسب';
            //  $fund_offer->status = 'close';
            $fund_offer->is_close = 1;
            $fund_offer->cancel_at = date('Y-m-d');
            $fund_offer->save();

            if ($fund_offer->status == 'sending_code') {


                if ($requests) {
                    $requests->status = 'new';
                    // $requests->delete();
                }

            }


            $string = $user->fund_request_offer;
            $arr = explode(',', $string);
            $out = array();
            $x = 0;
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($x == 0) {
                    if ($arr[$i] != $fund_offer->request_id) {
                        $out[] = $arr[$i];
                        $x++;
                    }
                } else {
                    $out[] = $arr[$i];
                }


            }
            $string2 = implode(',', $out);

            $user->fund_request_offer = $string2;


            $user->save();
        }

        return response()->success(__("views.Cancel Successfully"), []);
    }


    public function offer_date_data()
    {
        $estate = FundRequestOffer::whereHas('estate')->whereHas('provider')
            ->orderBy('id', 'desc')
            ->paginate();
//OfferDateDataResource
        $collection = OfferDateDataResource::collection($estate);

        return response()->success("Estate", $collection);
    }

    public function provider_data()
    {
        $user = User::where('type', 'provider')
            ->orderBy('id', 'desc')
            ->paginate();

        $collection = UserDateDataResource::collection($user);

        return response()->success("Users", $collection);
    }

    public function provider_attchment_data()
    {
        $user = FundRequestOffer::whereHas('estate')->whereHas('provider')
            ->orderBy('id', 'desc')
            ->paginate();

        $collection = OfferAttachDateDataResource::collection($user);

        return response()->success("Users", $collection);
    }


    public function sendSms(Request $request)
    {


        $rules = Validator::make($request->all(), [
            // 'estate_id' => 'required',
            'estate_id' => 'required|exists:estates,id',

            'reason' => 'required',
            'send_sms' => 'required|in:1,0',
            'send_notification' => 'required|in:1,0',
        ]);
//            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9',
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        try {
            $estate = Estate::find($request->get('estate_id'));
            if ($estate) {

                $reason = 'تم رفض اعلانك العقاري رقم : ' . $estate->id . '  بسبب :' . $request->get('reason') . ' ' . '  ' . 'رابط العقار : ' . $estate->link;

                $country_code = $request->get('country_code', 966);
                $user = \App\User::find($estate->user_id);

                if ($user) {
                    $mobile = $user->country_code . $user->mobile;

                    // dd($mobile);
                    $mobileCheck = preg_match("/^(?:\+|00)?([1-9]){2}?\d{9,10}$/", $mobile);

                    if ($request->get('send_sms') && $mobileCheck != 1) {
                        return response()->error(__('رقم المستخدم المدخل غير صحيح يرجى تعديل رقم جوال المستخدم او مفتاح الدولة'));
                    }

                    if ($request->get('send_sms') && $mobileCheck) {

                        if ($user && $mobileCheck) {
                            $unifonicClient = new UnifonicClient();
                            $to = $mobile;
                            $body = $reason;
                            $data = $unifonicClient->sendSms($to, $body);
                            Log::channel('single')->info($data);
                            Log::channel('slack')->info($data);
                        }

                    }

                    if ($request->get('send_notification')) {

                        $push_data = [
                            'title' => 'تم رفض العقار الخاص بك رقم # ' . $estate->id,
                            'body' => $reason,
                            'id' => $estate->id,
                            'user_id' => $estate->user_id,
                            'type' => 'estate',
                        ];

                        $note = NotificationUser::create([
                            'user_id' => $estate->user_id,
                            'title' => $reason,
                            'type' => 'estate',
                            'type_id' => $estate->id,
                        ]);
                        //   $client = User::where('id', $estate->user_id)->first();

                        //     dd($client);
                        if ($user) {
                            $fcm_token = FcmToken::where('user_id', $user->id)->get();
                            foreach ($fcm_token as $token) {
                                send_push($token->token, $push_data, $token->type);
                            }
                        }
                    }


                    $estate->status = 'closed';
                    $estate->reason = $reason;
                    $estate->save();
                    return response()->success("تم الارسال بنجاح", []);

                } else {
                    return response()->error(__('لايوجد مالك للعقار'));
                }

                /*  $estate->is_close=$estate->is_close=='0'?'1':'0';
                  $estate->save();
                  $estate->is_complete= $estate->is_close=='0'?'1':'0';
                  */


            } else {
                return response()->error(__('لايوجد عرض بالرقم المرسل'));

            }
        } catch (\Exception $exception) {
            return response()->error(__('لايوجد عرض بالرقم المرسل'));
        }


    }

    public function accepte_estate(Request $request)
    {


        $rules = Validator::make($request->all(), [
            // 'estate_id' => 'required',
            'estate_id' => 'required|exists:estates,id',

        ]);
//            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9',
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        try {
            $estate = \App\Models\v3\Estate::find($request->get('estate_id'));


            if ($estate) {
                $country_code = $request->get('country_code', 966);
                $user = \App\User::find($estate->user_id);

                if ($user) {

                    // dd($mobile);


                    $push_data = [
                        'title' => 'تم قبول العقار الخاص بك رقم # ' . $estate->id,
                        'body' => 'تم قبول العقار الخاص بك رقم # ' . $estate->id,
                        'id' => $estate->id,
                        'user_id' => $estate->user_id,
                        'type' => 'estate',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $estate->user_id,
                        'title' => 'تم قبول العقار الخاص بك رقم # ' . $estate->id,
                        'type' => 'estate',
                        'type_id' => $estate->id,
                    ]);
                    $client = User::where('id', $estate->user_id)->first();

                    //     dd($client);
                    if ($client) {
                        $fcm_token = FcmToken::where('user_id', $client->id)->get();
                        foreach ($fcm_token as $token) {
                            send_push($token->token, $push_data, $token->type);
                        }
                    }

                    $estate->reason = null;
                    $estate->status = 'completed';

                    $estate->save();
                    return response()->success("تم الارسال بنجاح", []);
                } else {
                    return response()->error(__('لايوجد مالك للعقار'));

                }

                /*  $estate->is_close=$estate->is_close=='0'?'1':'0';
                  $estate->save();
                  $estate->is_complete= $estate->is_close=='0'?'1':'0';
                  */


            } else {
                return response()->error(__('لايوجد عرض بالرقم المرسل'));

            }
        } catch (\Exception $exception) {
            return response()->error(__('لايوجد عرض بالرقم المرسل'));
        }


    }

    public function sendSms2(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'offer_id' => 'required',
            'reason' => 'required',
            'send_sms' => 'required',
            'send_notification' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $offer = FundRequestOffer::whereHas('estate')
            ->whereHas('provider')
            ->whereHas('fund_request')
            ->find($request->get('offer_id'));

        if ($offer) {
            $country_code = $request->get('country_code', 966);
            $estate = \App\Models\v3\Estate::find($offer->estate_id);
            $user = \App\User::find($offer->provider_id);
            if ($request->get('send_sms')) {

                $unifonicClient = new UnifonicClient();
                $to = $country_code . $user->mobile;
                $body = $request->get('reason');
                $data = $unifonicClient->sendSms($to, $body);
                Log::channel('single')->info($data);
                Log::channel('slack')->info($data);
            }

            if ($request->get('send_notification')) {
                $push_data = [
                    'title' => __('views.You Offer Rejected  #') . $estate->id,
                    'body' => $request->get('reason'),
                    'id' => $estate->id,
                    'user_id' => $estate->user_id,
                    'type' => 'fund_offer',
                ];

                $note = NotificationUser::create([
                    'user_id' => $estate->user_id,
                    'title' => $request->get('reason'),
                    'type' => 'estate',
                    'type_id' => $estate->id,
                ]);
                $client = User::where('id', $estate->user_id)->first();
                if ($client) {
                    $fcm_token = FcmToken::where('user_id', $client->id)->get();
                    foreach ($fcm_token as $token) {
                        send_push($token->token, $push_data, $token->type);
                    }
                }
            }


            return response()->success("تم الارسال بنجاح", []);
        } else {
            return response()->error(__('لايوجد عرض بالرقم المرسل'));

        }

    }
}
