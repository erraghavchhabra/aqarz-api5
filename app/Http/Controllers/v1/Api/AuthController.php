<?php

namespace App\Http\Controllers\v1\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Jobs\OtpJob;
use App\Models\v1\Client;
use App\Models\v1\Estate;
use App\Models\v1\EstateRequest;
use App\Models\v1\Favorite;
use App\Models\v1\RequestFund;
use App\Models\v2\Msg;
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


    public function storeOld(Request $request)
    {


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
            'name'                  => 'required|max:255',
            'mobile'                => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'email'                 => 'required|email|unique:users,email,null,id,deleted_at,NULL',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',

            'device_token' => 'required',
            'device_type'  => 'required',
            'type'         => 'required',
            'country_code' => 'required'
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        if (!env('SKIP_CONFIRM')) {
            $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
        } else {
            $confirmation_code = 123456;
        }


        $request->merge([
            'password'          => app('hash')->make($request->input('password')),
            'confirmation_code' => $confirmation_code,
            'api_token'         => hash('sha512', time()),
            'status'            => 0,
            //  'mobile'            => $mobile,
        ]);

        $user = User::create($request->only([
            'name',
            'mobile',
            'email',
            'password',
            'device_id',
            'country_id',
            'city_id',
            'status',
            'device_token',
            'device_type',
            'api_token',
            'type',
            'country_code'

        ]));


        $user = User::find($user->id);


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($user->mobile, $country_code);


        if (app()->environment('production')) {
            dispatch(new OtpJob($user));

        }

        $user = User::find($user->id);
        return response()->success("User Profile", $user);
        // return ['data' => $user];
    }


    public function store(Request $request)
    {


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
            'mobile'       => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'device_token' => 'required',
            'device_type'  => 'required',
            'type'         => 'required',
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
            'api_token'         => hash('sha512', time()),
            'status'            => 0,
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
            'confirmation_code'

        ]));


        $user = User::find($user->id);


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($user->mobile, $country_code);


        $user->mobile = $user_mobile;


        //    if (app()->environment('production')) {
        dispatch(new OtpJob($user));

        // }

        $user = User::find($user->id);
        return response()->success("We Send Activation Code To Your Mobile ", ['code' => $confirmation_code]);
        // return ['data' => $user];
    }


    public function show()
    {


        $user = auth()->user();

        if ($user == null) {
            return response()->error("not authorized");
        }
        $user = User::find($user->id);
        return response()->success("User Profile", $user);
        //  return ['data' => $user];
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }


        /*   if ($request->get('country_code') && $request->get('mobile')) {
               $global_mobile = intval($request->get('country_code')) . intval($request->get('mobile'));
               $request->merge(['mobile' => $global_mobile]);
           }
   */
        $rules = Validator::make($request->all(), [

            'name'        => 'sometimes|required',
            //   'city_id'         => 'sometimes|required',
            //   'neighborhood_id' => 'sometimes|required',
      //      'services_id' => 'sometimes|required',
        //    'members_id'  => 'sometimes|required',
            //   'mobile'       => 'sometimes|required|between:7,20|unique:users,mobile,' . $user->id . ',id,deleted_at,NULL',
            //   'email'        => 'sometimes|unique:users,email,' . $user->id . ',id,deleted_at,NULL',
            //   'password'     => 'sometimes|required|min:6|confirmed',
            //   'old_password' => 'required_with:password|min:6|check_password:' . $user->password,

            //    'password_confirmation' => 'sometimes|required',

            // 'device_id'             => 'required',

            'device_token' => 'sometimes|required',
            'device_type'  => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $user = User::find($user->id);

        $request->merge([
            'from_app' => true,
            //      'api_token' => hash('sha512', time()),
            'status'   => 0,
        ]);


        $user->update($request->only([
            'is_pay',
            'name',
            'email',
            // 'password',
            'type',
            'device_token',
            'device_type',
            //    'mobile',
            //    'api_token',
            //    'country_code',
            //     'confirmation_code',
            //     'logo',
            'services_id',
            'members_id',
            //  'city_id',
            //  'neighborhood_id',
            'lat',
            'lan',
            'address'

        ]));


        $user = User::find($user->id);


        /* if ($request->get('services_id')) {
            // $array = explode(',', $request->get('services_id'));


         }*/
        if (mb_strpos($_SERVER['HTTP_USER_AGENT'], "okhttp") === false) {
            // return ['data' => $user];
            return response()->success("User Profile", $user);
        }

        return response()->success("User Profile", $user);
        //   return response(null, Response::HTTP_NO_CONTENT);
    }


    public function requestOtp(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile'       => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'country_code' => 'sometimes|required|exists:countries,phone_code',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = Auth::user();
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
    public function verify(Request $request)
    {


        $validator = $this->validate($request, [
            'mobile'                => 'required',
            'code'                  => 'required',
            //     'email'                 => 'required|email|unique:users,email,null,id,deleted_at,NULL',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
            'country_code'          => 'required',
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
        $user = User::where('mobile', $mobile)->first();
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
                    'password'           => app('hash')->make($request->get('password')),
                    'name'               => $request->get('name'),
                    'code'               => ''
                    //  'email'              => $request->get('email')
                ]);
                $user = User::whereMobile($user->mobile)->first();
             //   return response()->success("Phone Verified", $user);

                $msg=Msg::create([
                    'receiver_id'=>$user->id,
                    'title'=>'مرحبا بك',
                    'body'=>'مرحبا بك في عقارز،
 سعيدين كثيراً بإنضمامك، في عقارز نسعى أن نكون منصتك الأولى لكافة الخدمات العقارية سواء كانت البحث عن عقارز مناسب أو تقييم عقارك أو الحصول على تمويل مناسب، والعديد من الخدمات التي صممناها خصيصاً لتلبي كافة احتياجاتك.'
                ]);


                return response()->success("Phone Verified", $user);

            }

        }
        return response()->error("Incorrect Code");
    }


    public function fcm(Request $request)
    {
        $this->validate($request, [
            "firebase_token" => "required",
        ]);
        $user = $request->user();
        $user->update(["firebase_token" => $request->firebase_token, "device_type" => $request->device_type]);
        return response()->success("Firebase Token Saved");
    }


    public function updatePassword(Request $request)
    {
        $rules = Validator::make($request->all(), [

            'password' => 'required|min:6|confirmed',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = Auth::user();
        if ($user == null) {
            return response()->error("not authorized");
        }
        if ($user) {
            $user->update(["password" => app('hash')->make($request->password)]);
            return response()->success("User Password Updated!");

        }
        return response()->error("Incorrect Password!");
    }


    public function forgetPassword(Request $request)
    {
        $rules = Validator::make($request->all(), [

            'mobile'       => 'required',
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
        $user = User::where('confirmation_password_code', $confirmation_code)->first();


        if ($user) {


            return response()->success("Code True", ['code' => $request['confirmation_password_code']]);


            if (!env('SKIP_CONFIRM')) {
                $password = substr(str_shuffle("123456789"), 0, 6);
            } else {
                $password = 123456;
            }
            $hash = app('hash')->make($password);
            $user->password = $hash;
            $user->confirmation_code = null;
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
                "status"  => false,
                "message" => 'code not valid',
                'errors'  => null,

            ], 400);


            throw ValidationException::withMessages([
                'confirmation_code' => [trans('messages.confirmation_mismatch')],
            ]);
        }

    }


    public function updatePasswordByPhone(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
            'code'                  => 'required',
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

        $destinationPath = base_path('public/users/photo/');


        $extension = $request
            ->file('logo')
            ->getClientOriginalExtension();
        $photo = str_random(32) . '.' . $extension;
        // $request->file('photo')->storeAs('users/photo/', $photo);

        //  $request->file('photo')->storeAs(base_path('public/users/photo/'),$photo);
        $request->file('logo')->move($destinationPath, $photo);
        destroyFile($user->logo);


        $user->update(['logo' => 'public/users/photo/' . $photo]);

        return response()->success("User Profile", $user);


    }


    public function logout()
    {
        return response()->success("logout!", []);
    }


    public function favorite(Request $request)
    {


        $rules = Validator::make($request->all(), [


            'type' => 'required|in:request,offer,fund',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = Auth::user();

        $fav = Favorite::where('user_id', $user->id)
            ->where('status', '1');

        $collection = '';
        if ($request->get('type')) {
            $fav = $fav->where('type', $request->get('type'));
            $fav = $fav->get();



            if ($request->get('type') == 'offer') {


                $collection = FavoriteResource::collection($fav);
            }
            if ($request->get('type') == 'request') {
                $collection = FavoriteRequestResource::collection($fav);
            }
            if ($request->get('type') == 'fund') {
                $collection = FavoriteFundResource::collection($fav);
            }

        }


        if ($collection) {

            return response()->success("Favorite", $collection);
        } else {
            return JsonResponse::fail('No Data', 200);
        }


    }


    public function storeFavoriteStatus(Request $request)
    {


        $rules = Validator::make($request->all(), [


            'type_id' => 'required',
            //  'type'=>'''request','offer','fund'
            'type'      => 'required|in:request,offer,fund',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = Auth::user();

        if ($request->get('type') == 'offer') {
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
                ->where('type',$request->get('type'))
                ->first();

            if ($checkFav) {
                $checkFav->status = $checkFav->status == '1' ? '0' : '1';
                $checkFav->save();
            } else {
                $checkFav = Favorite::create([
                    'user_id'   => $user->id,
                    'type_id' => $estate->id,
                    'status'    => '1',
                    'type'      => $request->get('type'),
                ]);
            }


            return response()->success("Done", []);


        }

        return JsonResponse::fail('No Data', 200);
    }


    public function client()
    {
        $user = Auth::user();

        $client = Client::where('user_id', $user->id)
            ->get();


        if ($client) {

            return response()->success("Client", $client);
        } else {
            return JsonResponse::fail('No Data', 200);
        }


    }


    public function storeClient(Request $request)
    {


        $rules = Validator::make($request->all(), [


            'client_name'   => 'required',
            'client_mobile' => 'required',
            'source_type' => 'required',
            'request_type'=>'required',
            'ads_number'=>'required',
            'priority'=>'required',
            'remember'=>'required',
         //   'remember_date_time'=>'required',
            'remember_date_time'     => 'required_if:remember,1',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = Auth::user();


        $checkFav = Client::create([
            'user_id'       => $user->id,
            'client_name'   => $request->get('client_name'),
            'client_mobile' => $request->get('client_mobile'),
            'source_type' => $request->get('source_type'),
            'request_type' => $request->get('request_type'),
            'ads_number' => $request->get('ads_number'),
            'priority' => $request->get('priority'),
            'remember' => $request->get('remember'),
            'remember_date_time' => $request->get('remember_date_time'),

        ]);


        return response()->success("Done", []);


    }

    public function deleteClient($id)
    {


        $user = Auth::user();
        $checkFav = Client::findOrFail($id);

        if ($checkFav) {
            $checkFav->delete();
        }


        return response()->success("Done", []);


    }
}
