<?php

namespace App\Http\Controllers\v3\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\MsgDetResource;
use App\Http\Resources\MsgResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserSearchResource;
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
use App\Models\v3\RequestFund;
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

    public function loginNew(Request $request)
    {
        // TODO when facebook user doesn't have email just phone number


        $rules = Validator::make($request->all(), [

            'username' => 'required_if:referer,local|max:255',
            //  'password'     => 'required_if:referer,local|min:3',
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
        //  dd($mobile);


        $global_mobile = intval($request->get('country_code')) . intval($mobile);
        $request->merge(['username' => $global_mobile]);


        $userCheck = User::where($username_column, $mobile)->first();


        if ($userCheck && !$request->get('password')) {
//dd($userCheck->password,$userCheck->id);
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


            //$user = $class::where($username_column, $old_mobile)->where('country_code', 966)->first();
            $user = $class::where($username_column, $mobile)->first();
        }

        if ((!$user) ||
            (app('hash')->check($credentials['password'], $user->password) === false)) {
            /*  return response([
                  'error' => [
                      'status'      => 422,
                      'name'        => 'UserPasswordMismatchError',
                      'description' => trans('messages.incorrect login'),
                      'details'     => [trans('messages.phone or password is incorrect')]
                  ]
              ], 422);/*


  */
            return JsonResponse::fail('UserPasswordMismatchError', 400);
        }


        if ($user->api_token == null) {
            $user->api_token = hash('sha512', time());

        }
        $user->device_token = $request->get('device_token');
        $user->save();


        return response()->success("User Profile", $user);
        //  return ['data' => $user];
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

    public function show()
    {


        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = User::with('Iam_information')->find($user->id);
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
            'advertiser_number' => 'sometimes|required',
            'license_number' => 'sometimes|required',
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
            'name',
            'email',
            'advertiser_number',
            // 'password',
            'type',
            'device_token',
            'device_type',
            'license_number',
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
            'advertiser_number',

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

            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9|unique:users,mobile,null,id,deleted_at,NULL',
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

                    $user->is_employee = 1;
                    $user->employer_id = $checkIfEmp->user_id;
                    //  $user->count_emp=$user->count_emp+1;
                    $user->save();


                }


                return response()->success(__("views.Phone Verified"), $user);
            }

        }
        return response()->error(__("views.Incorrect Code"));
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
            //  'identity' => 'required_if:type=provider',
            //   'identity' => 'required_if:type,=,provider',

            //     'email'                 => 'required|email|unique:users,email,null,id,deleted_at,NULL',
            //  'password' => 'required|min:6|confirmed',
            //     'password_confirmation' => 'required',
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
                    //     'password' => app('hash')->make($request->get('password')),
                    'name' => $request->get('name'),
                    'code' => '',
                    'type' => $request->get('type'),
                    //   'identity' => $request->get('identity'),
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

                    $user->is_employee = 1;
                    $user->employer_id = $checkIfEmp->user_id;
                    //  $user->count_emp=$user->count_emp+1;
                    $user->save();


                }


                if ($request->get('type') == 'provider') {
                    /*  $request->merge([
                          'user_id' => $user->id,
                      ]);*/
                    //  dd(444);
                    /*  $url = $this->login_iam($request);

                      return $url;*/

                    // return response()->success(__("views.Phone Verified"), $user);
                }

                return response()->success(__("views.Phone Verified"), $user);
            }

        }
        return response()->error(__("views.Incorrect Code"));
    }


    public function login_iam(Request $request)
    {

        // dd(GUID());
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = User::find($user->id);
        $uuid = GUID();
        $time = time();
        $url = 'https://iambeta.elm.sa/authservice/authorize?' .
            'scope=openid&' .
            'response_type=id_token&' .
            'response_mode=form_post&' .
            'client_id=16373065&' .
            'redirect_uri=https://apibeta.aqarz.sa/api/login/auth/' . $user->id . '/callback&' .
            'nonce=' . $uuid . '&' .
            'ui_locales=ar&' .
            'prompt=login' .
            '&max_age=' . $time;

        $fp = fopen(public_path('certificate_new.pem'), "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        $pkeyid = openssl_pkey_get_private($priv_key);

        $pubkeyid = openssl_pkey_get_public($priv_key);

        $binary_signature = "";


        /////

        $rsa = new RSA();

        //  $rsa->setPassword('Aqarz@All');
        //$chek = $rsa->loadKey(file_get_contents(public_path('certificate_new_p.pem')));


        $chek = $rsa->loadKey(file_get_contents(public_path('certificate_new_p.pem')));


        // $rsa->setPassword('Aqarz@All');

        $urlBase = hash('sha256', $url);
        $rsa->setHash('sha256');
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $signature = $rsa->sign($url);
        $signature = base64_encode($signature);
        // $rsa->loadKey(($rsa->getPublicKey())); // public key


        $rsa->loadKey($rsa->getPublicKey());
        // echo $rsa->verify($urlBase, $signature) ? 'verified' : 'unverified';


        $cheek = $rsa->verify($url, base64_decode($signature));


        $signature = urlencode($signature);

        $algo = "SHA256";


        $param_name = 'state';


        $urlBase = hash('sha256', $url);

        openssl_sign($url, $binary_signature, $pkeyid, $algo);


        $binary_signature = base64_encode($binary_signature);


        $ok = openssl_verify($url, base64_decode($binary_signature), $pubkeyid, OPENSSL_ALGO_SHA256);


        $binary_signature = urlencode($binary_signature);


        $join = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        $completeUrl = $url . $join . $param_name . '=' . $binary_signature;


        // return($completeUrl);

        if ($ok) {
            return response()->success(__('رابط الدخول'), $completeUrl);


            //    $url = $completeUrl;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            print_r($output);
            print_r($curl_error);


            //   dd(444);

            // $html = \View::make($completeUrl)->render();
            $html = file_get_contents($completeUrl);

            dd($html);
            dd($output, $curl_error);

            return response()->success(__('رابط الدخول'), $completeUrl);

            return $completeUrl;


            $client = new Client();


            $res = $client->request('GET', $completeUrl);
            echo $res->getStatusCode();
            // 200
            echo $res->getHeader('application/x-www-form-urlencoded');
            // 'application/json; charset=utf8'
            echo $res->getBody();

            dd($completeUrl);
        }

    }

    public function login_iam_live_test(Request $request)
    {

        // dd(GUID());
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }


        $user = User::find($user->id);
        $uuid = GUID();
        $time = time();
        $url = 'https://iam.elm.sa/authservice/authorize?' .
            'scope=openid&' .
            'response_type=id_token&' .
            'response_mode=form_post&' .
            'client_id=25289439&' .
            'redirect_uri=https://aqarz.sa/api/login/auth/' . $user->id . '/callback&' .
            'nonce=' . $uuid . '&' .
            'ui_locales=ar&' .
            'prompt=login' .
            '&max_age=' . $time;

        $fp = fopen(public_path('certificatelive.pem'), "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        $pkeyid = openssl_pkey_get_private($priv_key);

        $pubkeyid = openssl_pkey_get_public($priv_key);

        $binary_signature = "";


        /////

        $rsa = new RSA();

        //  $rsa->setPassword('Aqarz@All');
        //$chek = $rsa->loadKey(file_get_contents(public_path('certificate_new_p.pem')));


        $chek = $rsa->loadKey(file_get_contents(public_path('certificatelive_m.pem')));


        // $rsa->setPassword('Aqarz@All');

        $urlBase = hash('sha256', $url);
        $rsa->setHash('sha256');
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $signature = $rsa->sign($url);
        $signature = base64_encode($signature);
        // $rsa->loadKey(($rsa->getPublicKey())); // public key


        $rsa->loadKey($rsa->getPublicKey());
        // echo $rsa->verify($urlBase, $signature) ? 'verified' : 'unverified';


        $cheek = $rsa->verify($url, base64_decode($signature));


        $signature = urlencode($signature);

        $algo = "SHA256";


        $param_name = 'state';


        $urlBase = hash('sha256', $url);

        openssl_sign($url, $binary_signature, $pkeyid, $algo);


        $binary_signature = base64_encode($binary_signature);


        $ok = openssl_verify($url, base64_decode($binary_signature), $pubkeyid, OPENSSL_ALGO_SHA256);


        $binary_signature = urlencode($binary_signature);


        $join = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        $completeUrl = $url . $join . $param_name . '=' . $binary_signature;


        // return($completeUrl);

        if ($ok) {
            return response()->success(__('رابط الدخول'), $completeUrl);


            //    $url = $completeUrl;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            print_r($output);
            print_r($curl_error);


            //   dd(444);

            // $html = \View::make($completeUrl)->render();
            $html = file_get_contents($completeUrl);


            return response()->success(__('رابط الدخول'), $completeUrl);

            return $completeUrl;


            $client = new Client();


            $res = $client->request('GET', $completeUrl);
            echo $res->getStatusCode();
            // 200
            echo $res->getHeader('application/x-www-form-urlencoded');
            // 'application/json; charset=utf8'
            echo $res->getBody();

            dd($completeUrl);
        }

    }


    public function login_iam_live(Request $request)
    {

        // dd(GUID());
        /*  $user = auth()->user();

          if ($user == null) {
              return response()->error(__('views.not authorized'));
          }
          $user = User::find($user->id);*/
        $uuid = GUID();
        $time = time();
        $url = 'https://iam.elm.sa/authservice/authorize?' .
            'scope=openid&' .
            'response_type=id_token&' .
            'response_mode=form_post&' .
            'client_id=16373065&' .
            'redirect_uri=https://apibeta.aqarz.sa/api/login/auth/callback&' .
            'nonce=' . $uuid . '&' .
            'ui_locales=ar&' .
            'prompt=login' .
            '&max_age=' . $time;

        $fp = fopen(public_path('certificatelive.pem'), "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        $pkeyid = openssl_pkey_get_private($priv_key);

        $pubkeyid = openssl_pkey_get_public($priv_key);

        $binary_signature = "";


        /////

        $rsa = new RSA();

        //  $rsa->setPassword('Aqarz@All');
        //$chek = $rsa->loadKey(file_get_contents(public_path('certificate_new_p.pem')));


        $chek = $rsa->loadKey(file_get_contents(public_path('certificatelive_m.pem')));


        // $rsa->setPassword('Aqarz@All');

        $urlBase = hash('sha256', $url);
        $rsa->setHash('sha256');
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $signature = $rsa->sign($url);
        $signature = base64_encode($signature);
        // $rsa->loadKey(($rsa->getPublicKey())); // public key


        $rsa->loadKey($rsa->getPublicKey());
        // echo $rsa->verify($urlBase, $signature) ? 'verified' : 'unverified';


        $cheek = $rsa->verify($url, base64_decode($signature));


        $signature = urlencode($signature);

        $algo = "SHA256";


        $param_name = 'state';


        $urlBase = hash('sha256', $url);

        openssl_sign($url, $binary_signature, $pkeyid, $algo);


        $binary_signature = base64_encode($binary_signature);


        $ok = openssl_verify($url, base64_decode($binary_signature), $pubkeyid, OPENSSL_ALGO_SHA256);


        $binary_signature = urlencode($binary_signature);


        $join = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        $completeUrl = $url . $join . $param_name . '=' . $binary_signature;


        // return($completeUrl);

        if ($ok) {
            return response()->success(__('رابط الدخول'), $completeUrl);


            //    $url = $completeUrl;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            print_r($output);
            print_r($curl_error);


            //   dd(444);

            // $html = \View::make($completeUrl)->render();
            $html = file_get_contents($completeUrl);

            dd($html);
            dd($output, $curl_error);

            return response()->success(__('رابط الدخول'), $completeUrl);

            return $completeUrl;


            $client = new Client();


            $res = $client->request('GET', $completeUrl);
            echo $res->getStatusCode();
            // 200
            echo $res->getHeader('application/x-www-form-urlencoded');
            // 'application/json; charset=utf8'
            echo $res->getBody();

            dd($completeUrl);
        }

    }

    public function logout()
    {
        return response()->success('تم تسجيل الخروج بنجاح', []);
    }


    public function authCallback(Request $request, $id)
    {


        // dd($request->all());
        $rules = Validator::make($request->all(), [

            'id_token' => 'required',
            'state' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        //    $jwt='eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMDEwMDU2MzQ3IiwiZW5nbGlzaE5hbWUiOiJBbGkgTW9oYW1tZWQgQWRlbCBLaGFsaWQiLCJhcmFiaWNGYXRoZXJOYW1lIjoi2YXYrdmF2K8iLCJlbmdsaXNoRmF0aGVyTmFtZSI6Ik1vaGFtbWVkIiwiZ2VuZGVyIjoiTWFsZSIsImlzcyI6Imh0dHBzOlwvXC93d3cuaWFtLmdvdi5zYVwvdXNlcmF1dGgiLCJjYXJkSXNzdWVEYXRlR3JlZ29yaWFuIjoiVGh1IE5vdiAxNiAwMDowMDowMCBBU1QgMjAxNyIsImVuZ2xpc2hHcmFuZEZhdGhlck5hbWUiOiJBZGVsIiwidXNlcmlkIjoiMTAxMDA1NjM0NyIsImlkVmVyc2lvbk5vIjoiNCIsImFyYWJpY05hdGlvbmFsaXR5Ijoi2KfZhNi52LHYqNmK2Kkg2KfZhNiz2LnZiNiv2YrYqSIsImFyYWJpY05hbWUiOiLYudmE2Yog2YXYrdmF2K8g2LnYp9iv2YQg2K7Yp9mE2K8iLCJhcmFiaWNGaXJzdE5hbWUiOiLYudmE2YoiLCJuYXRpb25hbGl0eUNvZGUiOiIxMTMiLCJpcWFtYUV4cGlyeURhdGVIaWpyaSI6IjE0NDhcLzA5XC8xMSIsImV4cCI6MTYzNjMwNTA0OSwibGFuZyI6ImFyIiwiaWF0IjoxNjM2MzA0ODk5LCJqdGkiOiJodHRwczpcL1wvaWFtYmV0YS5lbG0uc2EsQzc3REVCODYtNUY4Mi00QjQxLUE3NkUtQ0FCODNGODQxMTY2IiwiaXFhbWFFeHBpcnlEYXRlR3JlZ29yaWFuIjoiVGh1IEZlYiAxOCAwMDowMDowMCBBU1QgMjAyNyIsImlkRXhwaXJ5RGF0ZUdyZWdvcmlhbiI6IlRodSBGZWIgMTggMDA6MDA6MDAgQVNUIDIwMjciLCJpc3N1ZUxvY2F0aW9uQXIiOiLYp9mE2LHZitin2LYiLCJkb2JIaWpyaSI6IjE0MDFcLzA1XC8xNyIsImNhcmRJc3N1ZURhdGVIaWpyaSI6IjE0MzlcLzAyXC8yNyIsImVuZ2xpc2hGaXJzdE5hbWUiOiJBbGkiLCJpc3N1ZUxvY2F0aW9uRW4iOiJSeWFkaCIsImFyYWJpY0dyYW5kRmF0aGVyTmFtZSI6Iti52KfYr9mEIiwiYXVkIjoiaHR0cHM6XC9cL2FwaWJldGEuYXFhcnouc2FcL2FwaVwvIiwibmJmIjoxNjM2MzA0NzQ5LCJuYXRpb25hbGl0eSI6IlNhdWRpIEFyYWJpYSIsImRvYiI6IlN1biBNYXIgMjkgMDA6MDA6MDAgQVNUIDE5ODEiLCJlbmdsaXNoRmFtaWx5TmFtZSI6IktoYWxpZCIsImlkRXhwaXJ5RGF0ZUhpanJpIjoiMTQ0OFwvMDlcLzExIiwiYXNzdXJhbmNlX2xldmVsIjoiIiwiYXJhYmljRmFtaWx5TmFtZSI6Itiu2KfZhNivIn0.UweW_luXxLkwNuXLSHXT65OZ6C6tRRAO7DTBSNIWIH63EnhMLT5J4gqMwW4sNabR4mEwqEjnjfXbqhZNpXWaVPuNPILR9QmGUYrbQB22-XbcCvrX-BEOAL9mu6ZR-LEkCZkkFf0FgjdNvAjyxjidUiX386q6VJ2qdAYoOwppiwLXQCC-kuOm_teR-ksHteHAUV-3HQQCglAIJKOyNe_LCenHkiwzMEykErRENutuNgATnu78T6JXlY8NiqjUZZe5YGNbpoBj1ZSRsP-VW9zU1l_OzGdABZlEo9ZG3NUgpLiOgdioP5iiNDXF6aReV-5KabwKr7gEJyHTfA8Sb7dwMQ';
        //   $key='cSyCMJCAuMQHnHkZetIRJHYTZWy8odOPcHqEJqIH5dU=';

        // $jwt = 'eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMDI5MjY3MDQyIiwiZW5nbGlzaE5hbWUiOiJBQkRVTFJBSE1BTiAgICAgU0FFRUQgICAgICAgICAgIEdIVVJNQU4gICAgICAgICBBTFNIRUhSSSAgICAgICAiLCJhcmFiaWNGYXRoZXJOYW1lIjoi2KjZhiDYs9i52YrYryAgICAgICAgIiwiZW5nbGlzaEZhdGhlck5hbWUiOiJTQUVFRCAgICAgICAgICAiLCJnZW5kZXIiOiJNYWxlIiwiaXNzIjoiaHR0cHM6XC9cL3d3dy5pYW0uZ292LnNhXC91c2VyYXV0aCIsImNhcmRJc3N1ZURhdGVHcmVnb3JpYW4iOiJXZWQgSnVuIDIyIDAzOjAwOjAwIEFTVCAyMDE2IiwiZW5nbGlzaEdyYW5kRmF0aGVyTmFtZSI6IkdIVVJNQU4gICAgICAgICIsInVzZXJpZCI6IjEwMjkyNjcwNDIiLCJpZFZlcnNpb25ObyI6IjUiLCJhcmFiaWNOYXRpb25hbGl0eSI6Itin2YTYudix2KjZitipINin2YTYs9i52YjYr9mK2KkgICAgIiwiYXJhYmljTmFtZSI6Iti52KjYr9in2YTYsdit2YXZhiAgICAgICDYqNmGINiz2LnZitivICAgICAgICAg2KjZhiDYutix2YXYp9mGICAgICAgICDYp9mE2LTZh9ix2YogICAgICAgICAiLCJhcmFiaWNGaXJzdE5hbWUiOiLYudio2K_Yp9mE2LHYrdmF2YYgICAgICAiLCJuYXRpb25hbGl0eUNvZGUiOiIxMTMiLCJpcWFtYUV4cGlyeURhdGVIaWpyaSI6IjE0NDdcLzA5XC8xMSIsImV4cCI6MTYzODk4OTk4OSwibGFuZyI6ImFyIiwiaWF0IjoxNjM4OTg5ODM5LCJqdGkiOiJodHRwczpcL1wvaWFtLmVsbS5zYSw3MTQ0MzQwRC0xQzM5LTRCODQtOEQ3My01OTc5ODM5NDk1QTUiLCJpcWFtYUV4cGlyeURhdGVHcmVnb3JpYW4iOiJTYXQgRmViIDI4IDAzOjAwOjAwIEFTVCAyMDI2IiwiaWRFeHBpcnlEYXRlR3JlZ29yaWFuIjoiU2F0IEZlYiAyOCAwMzowMDowMCBBU1QgMjAyNiIsImlzc3VlTG9jYXRpb25BciI6Itin2K3ZiNin2YQg2KfZhNiv2LHYudmK2KkgICAgICAgICAgICAgICAgICIsImRvYkhpanJpIjoiMTQwM1wvMDJcLzIwIiwiY2FyZElzc3VlRGF0ZUhpanJpIjoiMTQzN1wvMDlcLzE3IiwiZW5nbGlzaEZpcnN0TmFtZSI6IkFCRFVMUkFITUFOICAgICIsImFyYWJpY0dyYW5kRmF0aGVyTmFtZSI6Itio2YYg2LrYsdmF2KfZhiAgICAgICAiLCJhdWQiOiJodHRwczpcL1wvYXFhcnouc2FcLyIsIm5iZiI6MTYzODk4OTY4OSwibmF0aW9uYWxpdHkiOiJTYXVkaSBBcmFiaWEgICAgICAgICIsImRvYiI6IlN1biBEZWMgMDUgMDM6MDA6MDAgQVNUIDE5ODIiLCJlbmdsaXNoRmFtaWx5TmFtZSI6IkFMU0hFSFJJICAgICAgICIsImlkRXhwaXJ5RGF0ZUhpanJpIjoiMTQ0N1wvMDlcLzExIiwicHJlZmVycmVkTGFuZyI6ImFyIiwiYXNzdXJhbmNlX2xldmVsIjoiIiwiYXJhYmljRmFtaWx5TmFtZSI6Itin2YTYtNmH2LHZiiAgICAgICAgICJ9.ktePsnB41j_ajlWIs5bLklbg5_CDAqqOO3LKG3S4tj2HA__H2J9q3h0dMMNYQHc8W4EVKB4rmf2Yg1Qp5aoekElqOoQaTLst8ZTL49tl4cm2PEOhPS8-9DYMNrOcwx_LIqxxTY4yEf_UjGFRIgKrG1xKHeL8nyt6B4qH_sMzo26BO2fWt_IuPWuYIztPXXurUfq0hyyKmE9B3JPhVnCRbN3lBjk776iBt8M6vyS5O2CLgZHbB5Wv3C8Qn-_j7OvG-t7G8mdSMM5DvhXnEh802-XCdXkWwfpSaddFOJih41GpkGOIAo-n4sP0cyg_k9cq9fPbxeGn_CAqwWTfVIg4-Q';

        $jwt = $request->get('id_token');
        $data = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $jwt)[1]))));


        // dd(preg_replace('!\s+!', ' ', $data->arabicFatherName));

        if ($data !== null) {
            if ($data->sub) {
                $user = User::
                // where('identity', $data->sub)
                //   ->
                where('id', $id)->first();
                if ($user) {
                    $user->type = 'provider';
                    $user->is_iam_complete = '1';
                    $user->is_certified = '1';
                    $user->save();
                    $chekIamData = IamProvider::where('user_id', $user->id)->first();

                    if (!$chekIamData) {
                        $iam_data = IamProvider::create([
                            'user_id' => $user->id,
                            'sub' => @$data->sub,
                            'englishName' => @preg_replace('!\s+!', ' ', $data->englishName),
                            'arabicFatherName' => @preg_replace('!\s+!', ' ', $data->arabicFatherName),
                            'englishFatherName' => @preg_replace('!\s+!', ' ', $data->englishFatherName),
                            'gender' => @preg_replace('!\s+!', ' ', $data->gender),
                            'iss' => @$data->iss,
                            'cardIssueDateGregorian' => @$data->cardIssueDateGregorian,
                            'englishGrandFatherName' => @preg_replace('!\s+!', ' ', $data->englishGrandFatherName),
                            'userid' => @$data->userid,
                            'idVersionNo' => @$data->idVersionNo,
                            'arabicNationality' => @preg_replace('!\s+!', ' ', $data->arabicNationality),
                            'arabicName' => @preg_replace('!\s+!', ' ', $data->arabicName),
                            'arabicFirstName' => @preg_replace('!\s+!', ' ', $data->arabicFirstName),
                            'nationalityCode' => @$data->nationalityCode,
                            'iqamaExpiryDateHijri' => @$data->iqamaExpiryDateHijri,
                            'exp' => @$data->exp,
                            'lang' => @$data->lang,
                            'iat' => @$data->iat,
                            'jti' => @$data->jti,
                            'iqamaExpiryDateGregorian' => @$data->iqamaExpiryDateGregorian,
                            'idExpiryDateGregorian' => @$data->idExpiryDateGregorian,
                            'issueLocationAr' => @preg_replace('!\s+!', ' ', $data->issueLocationAr),
                            'dobHijri' => @$data->dobHijri,
                            'englishFirstName' => @preg_replace('!\s+!', ' ', $data->englishFirstName),
                            'cardIssueDateHijri' => @$data->cardIssueDateHijri,
                            'issueLocationEn' => @$data->issueLocationEn,
                            'arabicGrandFatherName' => @preg_replace('!\s+!', ' ', $data->arabicGrandFatherName),
                            'aud' => @$data->aud,
                            'nbf' => @$data->nbf,
                            'nationality' => @preg_replace('!\s+!', ' ', $data->nationality),
                            'dob' => @$data->dob,
                            'englishFamilyName' => @preg_replace('!\s+!', ' ', $data->englishFamilyName),
                            'idExpiryDateHijri' => @$data->idExpiryDateHijri,
                            'assurance_level' => @preg_replace('!\s+!', ' ', $data->assurance_level),
                            'arabicFamilyName' => @preg_replace('!\s+!', ' ', $data->arabicFamilyName),
                        ]);
                    } else {
                        $request->merge([
                            'sub' => @$data->sub,
                            'englishName' => @preg_replace('!\s+!', ' ', $data->englishName),
                            'arabicFatherName' => @preg_replace('!\s+!', ' ', $data->arabicFatherName),
                            'englishFatherName' => @preg_replace('!\s+!', ' ', $data->englishFatherName),
                            'gender' => @preg_replace('!\s+!', ' ', $data->gender),
                            'iss' => @$data->iss,
                            'cardIssueDateGregorian' => @$data->cardIssueDateGregorian,
                            'englishGrandFatherName' => @preg_replace('!\s+!', ' ', $data->englishGrandFatherName),
                            'userid' => @$data->userid,
                            'idVersionNo' => @$data->idVersionNo,
                            'arabicNationality' => @preg_replace('!\s+!', ' ', $data->arabicNationality),
                            'arabicName' => @preg_replace('!\s+!', ' ', $data->arabicName),
                            'arabicFirstName' => @preg_replace('!\s+!', ' ', $data->arabicFirstName),
                            'nationalityCode' => @$data->nationalityCode,
                            'iqamaExpiryDateHijri' => @$data->iqamaExpiryDateHijri,
                            'exp' => @$data->exp,
                            'lang' => @$data->lang,
                            'iat' => @$data->iat,
                            'jti' => @$data->jti,
                            'iqamaExpiryDateGregorian' => @$data->iqamaExpiryDateGregorian,
                            'idExpiryDateGregorian' => @$data->idExpiryDateGregorian,
                            'issueLocationAr' => @preg_replace('!\s+!', ' ', $data->issueLocationAr),
                            'dobHijri' => @$data->dobHijri,
                            'englishFirstName' => @preg_replace('!\s+!', ' ', $data->englishFirstName),
                            'cardIssueDateHijri' => @$data->cardIssueDateHijri,
                            'issueLocationEn' => @$data->issueLocationEn,
                            'arabicGrandFatherName' => @preg_replace('!\s+!', ' ', $data->arabicGrandFatherName),
                            'aud' => @$data->aud,
                            'nbf' => @$data->nbf,
                            'nationality' => @preg_replace('!\s+!', ' ', $data->nationality),
                            'dob' => @$data->dob,
                            'englishFamilyName' => @preg_replace('!\s+!', ' ', $data->englishFamilyName),
                            'idExpiryDateHijri' => @$data->idExpiryDateHijri,
                            'assurance_level' => @preg_replace('!\s+!', ' ', $data->assurance_level),
                            'arabicFamilyName' => @preg_replace('!\s+!', ' ', $data->arabicFamilyName),
                        ]);
                        $chekIamData->update($request->only([
                            'sub',
                            'englishName',
                            'arabicFatherName',
                            'englishFatherName',
                            'gender',
                            'iss',
                            'cardIssueDateGregorian',
                            'englishGrandFatherName',
                            'userid', 'idVersionNo',
                            'arabicNationality',
                            'arabicName',
                            'arabicFirstName',
                            'nationalityCode',
                            'iqamaExpiryDateHijri',
                            'exp',
                            'lang',
                            'iat',
                            'jti',
                            'iqamaExpiryDateGregorian',
                            'idExpiryDateGregorian',
                            'issueLocationAr',
                            'dobHijri',
                            'englishFirstName',
                            'cardIssueDateHijri',
                            'issueLocationEn',
                            'arabicGrandFatherName',
                            'aud',
                            'nbf',
                            'nationality',
                            'dob',
                            'englishFamilyName',
                            'idExpiryDateHijri',
                            'assurance_level',
                            'arabicFamilyName',

                        ]));
                    }

                    $user->identity = $data->sub;
                    $user->onwer_name = $request->get('arabicFirstName');
                    $user->save();
                    $user = User::with('Iam_information')->find($user->id);

                    if (isset($_SERVER['HTTP_USER_AGENT'])) {
                        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
                        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
                        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
                        $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
                        $webOS = stripos($_SERVER['HTTP_USER_AGENT'], "webOS");
                        if (!$iPod && !$iPhone && !$iPad && !$Android && !$webOS) {
                            return \Redirect::to('https://aqarz.sa/add-aqar');
                        }

                    }


                    return response()->success('تم التسجيل بنجاح', $user);
                }
            }
        } else {
            return response()->error(__('هناك مشكلة في تسجيل نفاذ'));
        }
        return response()->error(__('هناك مشكلة في تسجيل نفاذ'));
        //    $data={
        //"status":true,
        //"code":200,
        //"message":"User Profile",
        //"data":
        //{
        //"sub":"1010056347",
        //"englishName":"Ali Mohammed Adel Khalid",
        //"arabicFatherName":"\u0645\u062d\u0645\u062f",
        //"englishFatherName":"Mohammed",
        //"gender":"Male",
        //"iss":"https:\/\/www.iam.gov.sa\/userauth",
        //"cardIssueDateGregorian":"Thu Nov 16 00:00:00 AST 2017",
        //"englishGrandFatherName":"Adel",
        //"userid":"1010056347",
        //"idVersionNo":"4",
        //"arabicNationality":"\u0627\u0644\u0639\u0631\u0628\u064a\u0629 \u0627\u0644\u0633\u0639\u0648\u062f\u064a\u0629",
        //"arabicName":"\u0639\u0644\u064a \u0645\u062d\u0645\u062f \u0639\u0627\u062f\u0644 \u062e\u0627\u0644\u062f",
        //"arabicFirstName":"\u0639\u0644\u064a",
        //"nationalityCode":"113",
        //"iqamaExpiryDateHijri":"1448\/09\/11",
        //"exp":1636584482,
        //"lang":"ar",
        //"iat":1636584332,
        //"jti":"https:\/\/iambeta.elm.sa,
        //91CEEC51-412C-4C30-9217-4AE1EA065AFE",
        //"iqamaExpiryDateGregorian":"Thu Feb 18 00:00:00 AST 2027",
        //"idExpiryDateGregorian":"Thu Feb 18 00:00:00 AST 2027",
        //"issueLocationAr":"\u0627\u0644\u0631\u064a\u0627\u0636",
        //"dobHijri":"1401\/05\/17",
        //"cardIssueDateHijri":"1439\/02\/27",
        //"englishFirstName":"Ali",
        //"issueLocationEn":"Ryadh",
        //"arabicGrandFatherName":"\u0639\u0627\u062f\u0644",
        //"aud":"https:\/\/apibeta.aqarz.sa\/api\/",
        //"nbf":1636584182,
        //"nationality":"Saudi Arabia",
        //"dob":"Sun Mar 29 00:00:00 AST 1981",
        //"englishFamilyName":"Khalid",
        //"idExpiryDateHijri":"1448\/09\/11",
        //"assurance_level":"",
        //"arabicFamilyName":"\u062e\u0627\u0644\u062f"}}

        return response()->success(__('views.User Profile'), $data);
    }

    public function getUserByid($id)
    {
        $user = User::where('id', $id)->first();


        if ($user == null) {
            return response()->error("not authorized");
        }
        return response()->success('المستخدم', $user);

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
            $user->confirmation_password_code = null;
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

            // 'country_code'              => 'sometimes|required',

        ]);


        if ($rules->fails()) {

            return $rules->errors()->first();
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = \Auth::user();

        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }

        //  dd(\Hash::check($request->old_password, $user->password));

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
        //    $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


        $user->update(['logo' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);

        return response()->success(__('views.User Profile'), $user);


    }


    /*public function logout()
    {
        return response()->success("logout!", []);
    }*/


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
            'type' => 'required|in:request,offer,fund,estate',


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

    public function deleteClient($id)
    {


        $user = Auth::user();
        $checkFav = Client::findOrFail($id);

        if ($checkFav) {
            $checkFav->delete();
            $user->count_agent = $user->count_agent - 1;
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
        `msgs`.`created_at` as `parent_created_at` from
  `msgs` inner join `users` as `u1` on `msgs`.`sender_id` = `u1`.`id`
      inner join `users` as `u2` on `msgs`.`receiver_id` = `u2`.`id`
      inner join `msgs_details` on `msgs`.`id` = `msgs_details`.`msg_id`
 where msgs.receiver_id = ' . $user->id . ' or msgs.sender_id =' . $user->id . ' group by msgs_details.msg_id order by parent_created_at DESC
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


            $msgDet->from_me = $msgDet->sender_id == $user->id ? 1 : 0;
            $msgDet->display_name = isset($msg_last->sender) && $msg_last->sender_id != $user->id ? @$msg_last->sender->name : @$msg_last->receiver->name;
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
                ->update(array('body' => $request->get('body'), 'title' => $request->get('title')));

            $msg->from_me = $msg->sender_id == $user->id ? 1 : 0;


            $client = User::find($request->get('user_id'));
            //    dd([$msg]);
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
                    'type_id' => $msg->id,
                ]);
                send_push($client->device_token, $push_data, $client->device_type);
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

    public function getUserByName($search = '')
    {
        if (mb_strlen($search) > 49) {
            return response()->error(__('the string too long'));
        }
        $search = trim($search);
        $user = User::where('name', 'like', '%' . $search . '%')->get();
        $user = UserSearchResource::collection($user);
        return response()->success(__('views.Users'), $user);
    }


    public function getProvidersByName(Request $request)
    {


        $user = '';
        if ($request->get('best')) {
            $user = User::where('type', 'provider')
                ->where('id', '!=', 142)
                //->orderBy('count_visit', 'desc')

                ->orderBy('count_fund_offer', 'desc')
                ->orderBy('count_offer', 'desc')
                ->orderBy('count_accept_fund_offer', 'desc')
                // ->orderBy('count_agent', 'desc')
                ->limit(5)
                ->get();
        } else {
            $user = User::where('type', 'provider');
            if ($request->get('search')) {
                if (mb_strlen($request->get('search')) > 49) {
                    return response()->error(__('the string too long'));
                }

                $search = trim($request->get('search'));
                $user = $user->where('name', 'like', '%' . $search . '%');
            }
            $user = $user->paginate();
        }

        return response()->success(__('views.Users'), $user);
    }


    public function hideEstate($id)
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->error("not authorized");
        }

        $estate = Estate::find($id);

        if (!$estate) {
            return response()->error("لايوجد عقار بالرقم المرسل");
        }
        $userHideArray = explode(',', $user->hide_estate_id);
        if (!in_array($id, $userHideArray) && isset($userHideArray)) {
            $user->hide_estate_id = $user->hide_estate_id ? $user->hide_estate_id . ',' . $id : $id;
            $user->save();
            $estate->is_hide = '1';
            $estate->save();
        }

        return response()->success("تم اخفاء العقار", []);

    }


    public function sendSms()
    {
        Artisan::call("send:sms");
    }
}
