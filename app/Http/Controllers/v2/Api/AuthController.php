<?php

namespace App\Http\Controllers\v2\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\MsgDetResource;
use App\Http\Resources\MsgResource;
use App\Jobs\OtpJob;
use App\Models\v2\Client;
use App\Models\v2\Employee;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\Favorite;
use App\Models\v2\Msg;
use App\Models\v2\MsgDet;
use App\Models\v2\NotificationUser;
use App\Models\v2\RequestFund;
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
            'name' => 'required|max:255',
            'mobile' => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'email' => 'required|email|unique:users,email,null,id,deleted_at,NULL',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',

            'device_token' => 'required',
            'device_type' => 'required',
            'type' => 'required',
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
            'password' => app('hash')->make($request->input('password')),
            'confirmation_code' => $confirmation_code,
            'api_token' => hash('sha512', time()),
            'status' => 0,
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
        $client = Client::where('client_mobile', $user->mobile)
            ->first();

        if ($client) {
            $user->related_company = $client->user_id;
            $user->save();
        }

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


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
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


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($user->mobile, $country_code);


        /*   $user->mobile = $user_mobile;

           $user->save();*/
        //    if (app()->environment('production')) {
        dispatch(new OtpJob($user));

        // }

        $user = User::find($user->id);
        return response()->success(__('views.We Send Activation Code To Your Mobile'), ['code' => $confirmation_code]);
        // return ['data' => $user];
    }


    public function resendCodes(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile' => 'required',


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


    public function show()
    {


        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = User::find($user->id);
        return response()->success(__('views.User Profile'), $user);
        //  return ['data' => $user];
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


    public function userByName($user)
    {
        $user = User::where('user_name', $user)->first();


        if ($user == null) {
            return response()->error(__('views.not found'));
        }

        $user->count_visit = $user->count_visit + 1;
        $user->save();
        return response()->success(__('views.User Profile'), $user);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }


        /*   if ($request->get('country_code') && $request->get('mobile')) {
               $global_mobile = intval($request->get('country_code')) . intval($request->get('mobile'));
               $request->merge(['mobile' => $global_mobile]);
           }
   */
        $rules = Validator::make($request->all(), [

            'name' => 'sometimes|required',

            //   'city_id'         => 'sometimes|required',
            //   'neighborhood_id' => 'sometimes|required',
            //    'services_id' => 'sometimes|required',
            //         'members_id'  => 'sometimes|required',
            //   'mobile'       => 'sometimes|required|between:7,20|unique:users,mobile,' . $user->id . ',id,deleted_at,NULL',
            //   'email'        => 'sometimes|unique:users,email,' . $user->id . ',id,deleted_at,NULL',
            //   'password'     => 'sometimes|required|min:6|confirmed',
            //   'old_password' => 'required_with:password|min:6|check_password:' . $user->password,

            //    'password_confirmation' => 'sometimes|required',

            // 'device_id'             => 'required',

            'device_token' => 'sometimes|required',
            'device_type' => 'sometimes|required',
            //'email'        => 'sometimes|required',
            'email' => 'sometimes|required|unique:users,email,' . $user->id . ',id,deleted_at,NULL',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $user = User::find($user->id);

        $request->merge([
            'from_app' => true,
            //      'api_token' => hash('sha512', time()),
            'status' => 0,
            'user_name' => $request->get('user_name') != null ? $request->get('user_name') : $user->user_name,
            'is_edit_username' => $request->get('user_name') ? '1' : '0',
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
            'experiences_id',
            'courses_id',
            //  'city_id',
            //  'neighborhood_id',
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

        ]));


        $user = User::find($user->id);


        /* if ($request->get('services_id')) {
            // $array = explode(',', $request->get('services_id'));


         }*/
        if (mb_strpos($_SERVER['HTTP_USER_AGENT'], "okhttp") === false) {
            // return ['data' => $user];
            return response()->success(__("views.User Profile"), $user);
        }

        return response()->success(__("views.User Profile"), $user);
        //   return response(null, Response::HTTP_NO_CONTENT);
    }


    public function requestOtp(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|between:7,20|unique:users,mobile,null,id,deleted_at,NULL',
            'country_code' => 'sometimes|required|exists:countries,phone_code',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
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
            'mobile' => 'required',
            'code' => 'required',
            //     'email'                 => 'required|email|unique:users,email,null,id,deleted_at,NULL',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
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
                    "email_verified_at" => date(now()),
                    "status" => 'active',
                    'password' => app('hash')->make($request->get('password')),
                    'name' => $request->get('name'),
                    'code' => ''
                    //  'email'              => $request->get('email')
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
                  /*  $push_data = [
                        'title' => __('views.You Have New Employee #') . $checkIfEmp->id,
                        'body' => __('views.You Have New Employee #') . $checkIfEmp->id,
                        'id' => $checkIfEmp->id,
                        'user_id' => $checkIfEmp->user_id,
                        'type' => 'employee',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $checkIfEmp->user_id,
                        'title' => __('views.You Have New Employee #')  . $checkIfEmp->id,
                        'type' => 'employee',
                    ]);
                    $client=User::where('id',$checkIfEmp->user_id)->first();
                    if($client)
                    {
                        send_push($client->device_token, $push_data, $client->device_type);
                    }*/

                    $user->is_employee=1;
                    $user->employer_id=$checkIfEmp->user_id;
                  //  $user->count_emp=$user->count_emp+1;
                    $user->save();


                }



                return response()->success(__("views.Phone Verified"), $user);
            }

        }
        return response()->error(__("views.Incorrect Code"));
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
            return response()->error(__("views.not authorized"));
        }
        if ($user) {
            $user->update(["password" => app('hash')->make($request->password)]);
            return response()->success(__("views.User Password Updated!"));

        }
        return response()->error(__("views.Incorrect Password!"));
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

        $class = new User();

        $user = $class::where($username_column, $credentials[$username_column])->first();


        $country_code = $request->get('country_code', 966);
        $user_mobile = checkIfMobileStartCode($mobile, $country_code);
        $smscode = '';


        if ($user) {
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
        //    $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


        $user->update(['logo' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);

        return response()->success(__('views.User Profile'), $user);


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
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
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


        if ($collection && $collection != '') {

            return response()->success(__("views.Favorite"), $collection);
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }


    }


    public function storeFavoriteStatus(Request $request)
    {


        $rules = Validator::make($request->all(), [


            'type_id' => 'required',
            //  'type'=>'''request','offer','fund'
            'type' => 'required|in:request,offer,fund',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = Auth::user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

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
                ->where('type', $request->get('type'))
                ->first();

            if ($checkFav) {
                $checkFav->status = $checkFav->status == '1' ? '0' : '1';
                $checkFav->save();
            } else {
                $checkFav = Favorite::create([
                    'user_id' => $user->id,
                    'type_id' => $estate->id,
                    'status' => '1',
                    'type' => $request->get('type'),
                ]);
            }


            return response()->success(__("views.Done"), []);


        }

        return JsonResponse::fail(__('views.No Data'), 200);
    }


    public function client()
    {
        $user = Auth::user();

        $client = Client::where('user_id', $user->id)
            ->get();


        if ($client) {

            return response()->success("Clients", $client);
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }


    }


    public function storeClient(Request $request)
    {


        $rules = Validator::make($request->all(), [


            'client_name' => 'required',
            'client_mobile' => 'required',
            'source_type' => 'required',
            'request_type' => 'required',
            'ads_number' => 'required',
            'priority' => 'required',
            'remember' => 'required',
            //   'remember_date_time'=>'required',
            'remember_date_time' => 'required_if:remember,1',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = Auth::user();


        $checkFav = Client::create([
            'user_id' => $user->id,
            'client_name' => $request->get('client_name'),
            'client_mobile' => $request->get('client_mobile'),
            'source_type' => $request->get('source_type'),
            'request_type' => $request->get('request_type'),
            'ads_number' => $request->get('ads_number'),
            'priority' => $request->get('priority'),
            'remember' => $request->get('remember'),
            'remember_date_time' => $request->get('remember_date_time'),

        ]);

        $user->count_agent = $user->count_agent + 1;
        $user->save();
        return response()->success(__("views.Done"), []);


    }


    public function storeEmployee(Request $request)
    {


        $user = Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
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
            ->where('is_employee',0)
            ->first();

        if($checkIsCompny)
        {
            return response()->error(__("رقم الجوال غير مضاف كشركة"));
        }


        $checkIsCompny2 = User::where('mobile', $mobile)
            ->whereIn('is_employee',[1,2])
            ->where('employer_id','!=',null)
            ->first();

        if($checkIsCompny2)
        {
            return response()->error(__("الرقم مضاف لشركة سابقا"));
        }

        if ($checkEmp ) {









            $checkEmpChek = Employee::where('emp_mobile', $mobile)
                ->where('country_code', $request->get('country_code'))
                ->first();

            if(!$checkEmpChek)
            {
                $checkFav = Employee::create([
                    'user_id' => $user->id,
                    'emp_name' => $request->get('emp_name'),
                    'emp_mobile' => $mobile,
                    'country_code' => $request->get('country_code'),


                ]);
            }


            $checkEmp->is_employee='1';
            $checkEmp->employer_id=$user->id;
            $checkEmp->save();
            $user->count_emp=$user->count_emp+1;
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
            ->where('employer_id',$user->id)
            //    ->where('country_code',$request->get('country_code'))
            ->first();
        if ($checkEmp) {



            $checkEmp->is_employee='0';
            $checkEmp->employer_id=null;
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
        }


        return response()->success(__("تم الحذف بنجاح"), []);
    }

    public function my_employee(Request $request)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $client = Employee::where('user_id', $user->id)
           ;



        if($request->get('emp_name'))
        {
            $search=trim($request->get('emp_name'));
            $client=    $client->Where('emp_name', 'like', '%' . $search . '%');
        }


        $client=   $client->paginate();




        if ($client) {

            return response()->success("Clients", $client);
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }


    }

    public function deleteClient($id)
    {


        $user = Auth::user();
        $checkFav = Client::findOrFail($id);

        if ($checkFav) {
            $checkFav->delete();
            $user->count_agent=$user->count_agent-1;
            $user->save();
        }


        return response()->success(__("views.Done"), []);


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
            $msg = Msg::whereHas('sender')->whereHas('receiver')->where('receiver_id', $id)
                ->Where('sender_id', $user->id)
                ->orWhere('sender_id', $id)
                ->where('receiver_id', $user->id)
                ->first();


            if ($msg) {


                $msg = MsgDet::whereHas('sender')->whereHas('receiver')->where('receiver_id', $id)
                    ->Where('sender_id', $user->id)
                    ->orWhere('sender_id', $id)
                    ->where('receiver_id', $user->id)
                    ->get();

                MsgDet::where('sender_id', $id)
                    ->where('receiver_id', $user->id)->update(['seen' => '1']);


                foreach ($msg as $msgDet) {

                    $msgDet->from_me = $msgDet->sender_id == $user->id ? 1 : 0;


                }

               /* $msg = Msg::whereHas('sender')->whereHas('receiver')->where('receiver_id', $id)
                    ->Where('sender_id', $user->id)
                    ->orWhere('sender_id', $id)
                    ->where('receiver_id', $user->id)
                    ->get();*/

                $msg = MsgDetResource::collection($msg);
                return response()->success(__("views.Msgs"), $msg);
            }
            else {

                $msg = Msg::create([
                    'sender_id' => $id,
                    'receiver_id' => $user->id,
                    'title' => 'مرحبا كيف يمكنني مساعدتك',
                    'body' => 'مرحبا كيف يمكنني مساعدتك',
                ]);


                $msg_del = MsgDet::create([
                    'msg_id' => $msg->id,
                    'sender_id' => $id,
                    'receiver_id' => $user->id,
                    'title' => 'مرحبا كيف يمكنني مساعدتك',
                    'body' => 'مرحبا كيف يمكنني مساعدتك',

                ]);

                $msg = MsgDet::whereHas('sender')->whereHas('receiver')->where('receiver_id', $id)
                    ->Where('sender_id', $user->id)
                    ->orWhere('sender_id', $id)
                    ->where('receiver_id', $user->id)
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

        $msg = Msg::where('receiver_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->get();


        foreach ($msg as $msgDet) {

            $msg_count = MsgDet::whereHas('sender')->whereHas('receiver')->where('seen', '0')
                ->where('receiver_id', $user->id)
                ->where('msg_id', $msgDet->id)
                ->count();


            $msg_last = MsgDet::whereHas('sender')->whereHas('receiver')
                ->where('msg_id', $msgDet->id)
                ->orderBy('id','desc')
                ->first();


         //   dd( isset($msg_last->receiver) && $msg_last->receiver_id != $user->id ? @$msg_last->receiver->name:'');
       /*     dd(isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->name :
                isset($msg_last->receiver) && $msg_last->receiver_id != $user->id ? @$msg_last->receiver->name : '' ,
                $msg_last->sender->name);
*/


            $msgDet->from_me = $msgDet->sender_id == $user->id ? 1 : 0;
              $msgDet->display_name = isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->name :  @$msg_last->receiver->name;
          // $msgDet->display_name = @$msgDet->sender->name;
           //  $msgDet->display_logo = isset($msgDet->sender) && $msgDet->sender_id == $user->id ? @$msgDet->receiver->logo : isset($msgDet->sender) ? @$msgDet->sender->logo : 'Admin';
              $msgDet->display_logo = isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->logo : @$msg_last->receiver->logo;
               $msgDet->display_id = isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->id : @$msg_last->receiver->id;


            //    $msgDet->display_logo = @$msg_last->sender->logo;
          //  $msgDet->display_id = isset($msgDet->sender) && $msgDet->sender_id == $user->id ? @$msgDet->receiver->id : isset($msgDet->sender) ? @$msgDet->sender->id : 0;
        //    $msgDet->display_id = @$msg_last->sender->id ;
            $msgDet->count_not_read = $msg_count;;

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


        MsgDet::where('msg_id', $id)
            ->where('receiver_id', $user->id)
            ->update(['seen' => '1']);

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
        $msg = Msg::where('receiver_id', $request->get('user_id'))
            ->Where('sender_id', $user->id)
            ->orWhere('sender_id', $request->get('user_id'))
            ->where('receiver_id', $user->id)
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

            $msg->body = $request->get('body');
            $msg->title = $request->get('title');
            $msg->save();

            // foreach ($msg as $msgDetS) {

            $msg->from_me = $msg->sender_id == $user->id ? 1 : 0;


            //  }

            $client = User::find($request->get('user_id'));
            if ($client) {
                $push_data = [
                    'title' => 'لديك رسالة جديدة ',
                    'body' => $request->get('body'),
                    'id' => $msg->id,
                    'user_id' => $client->id,
                    'type' => 'chat',
                ];

                $note = NotificationUser::create([
                    'user_id' => $client->id,
                    'title' => 'لديك رسالة جديدة #' . $msg->id,
                    'type' => 'chat',
                    'type_id' =>  $msg->id,
                ]);
                $fcm_token = FcmToken::where('user_id', $client->id)->get();
                foreach ($fcm_token as $token) {
                    send_push($token->token, $push_data, $token->type);
                }
            }


            $msg = MsgDetResource::collection([$msg]);
            return response()->success(__("views.Msg Send"), $msg);
        }
        else {
            return response()->error(__("views.Massage is empty"));
        }

    }


    public function notification(Request $request)
    {


        $user = auth()->user();

        $count_one = NotificationUser::where('user_id', $user->id)
            ->whereIn('type',['request','fund_request','fund_offer','offer'])->count();

        $count_tow = NotificationUser::where('user_id', $user->id)
            ->whereIn('type',['chat','employee'])->count();

        $count_three = NotificationUser::where('user_id', $user->id)
            ->whereIn('type',['rate_offer','rate_estate'])->count();


        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = NotificationUser::where('user_id', $user->id);
        if($request->get('type'))
        {
            $types=explode(',',$request->get('type'));


        $user=$user->whereIn('type',$types);
        }
        $user  = $user->paginate();


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

    public function getUserByName($search = '')
    {
        if (mb_strlen($search) > 49) {
            return response()->error(__('the string too long'));
        }
        $search = trim($search);
        $user = User::where('name', 'like', '%' . $search . '%')->get();
        return response()->success(__('views.Users'), $user);
    }

    public function hideEstate($id)
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }

        $estate=Estate::find($id);

        if(!$estate)
        {
            return response()->error("لايوجد عقار بالرقم المرسل");
        }
        $userHideArray=explode(',',$user->hide_estate_id);
        if(!in_array($id,$userHideArray)&& isset($userHideArray))
        {
            $user->hide_estate_id=$user->hide_estate_id?$user->hide_estate_id.','.$id:$id;
            $user->save();
        }

        return response()->success("تم اخفاء العقار", []);

    }
}
