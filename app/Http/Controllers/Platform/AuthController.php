<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\EmpUserResource;
use App\Http\Resources\Platform\MySubscriptionResource;
use App\Http\Resources\UserResource;

use App\Jobs\OtpJob;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\Msg;
use App\Models\v3\MsgDet;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use App\User;
use Auth;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use QL\QueryList;


class AuthController extends Controller
{

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
            //  'device_token' => 'required',
            //  'device_type' => 'required',
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
            'confirmation_password_code',
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
        return Response::json([
            "status" => true,
            "message" => "User Profile",
            "data" => ['code' => $user->confirmation_code],
            'display' => 'otp'
        ]);


        return response()->success(__('views.We Send Activation Code To Your Mobile'), ['code' => $confirmation_code]);
        // return ['data' => $user];
    }

    public function login(Request $request)
    {


        // TODO when facebook user doesn't have email just phone number


        $rules = Validator::make($request->all(), [

            'username' => 'required_if:referer,local|max:255',
            'password' => 'required_if:referer,local|min:3',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $username = $request->username;
        $mobile = 0;
        $old_mobile = "";


        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $username_column = 'email';
        } else {

            $username_column = 'mobile';


            if (true) {
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
            }
        }

        $request->merge([
            $username_column => $request->username,
            'status' => '0'
        ]);
        $credentials = $request->only($username_column, 'password', 'status');

        $class = new User();

        $user = $class::where($username_column, $credentials[$username_column])->first();


        if (!$user && $mobile) { //  TODO remove later after 2019-12-30


            //$user = $class::where($username_column, $old_mobile)->where('country_code', 966)->first();
            $user = $class::where($username_column, $mobile)->first();
        }

        if ((!$user) || (app('hash')->check($credentials['password'], $user->password) === false)) {
            return JsonResponse::fail('UserPasswordMismatchError', 400);
        }


        if ($user->api_token == null) {
            // $user->api_token = hash('sha512', time());
            $user->api_token = $user->createToken('api_token')->plainTextToken;

        }


        $user->api_token = $user->createToken('api_token')->plainTextToken;

        $user->save();


        return JsonResponse::success($user, "User Profile");
        //  return ['data' => $user];
    }

    public function loginNew(Request $request)
    {
        // TODO when facebook user doesn't have email just phone number


        $rules = Validator::make($request->all(), [

            'username' => 'required_if:referer,local|max:255',
            //  'password'     => 'required_if:referer,local|min:3',
            //    'device_token' => 'sometimes|required',
            //   'device_type' => 'required',


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
                return Response::json([
                    "status" => true,
                    "message" => "User Profile",
                    "data" => ['code' => $userCheck->confirmation_code],
                    'display' => 'otp'
                ]);
                //   return response()->success("User Profile", ['code' => $userCheck->confirmation_code]);
            }

            return Response::json([
                "status" => true,
                "message" => "User Profile",
                "data" => ['code' => ''],
                'display' => 'password'
            ]);
            return response()->success("User Profile", ['code' => ""]);

        } elseif (!$userCheck) {
            $request->merge(['mobile' => $mobile]);
            $user = $this->store($request);

            return ($user);
            //  return response()->success("User Profile", ['statue' => false]);
        } elseif ($userCheck && !$userCheck->password) {
            dispatch(new OtpJob($userCheck));
            return Response::json([
                "status" => true,
                "message" => "User Profile",
                "data" => ['code' => $userCheck->confirmation_code],
                'display' => 'otp'
            ]);
            //return response()->success("User Profile", ['code' => $userCheck->confirmation_code]);

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

        if ((!$user) || (app('hash')->check($credentials['password'], $user->password) === false)) {
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


//        if ($user->api_token == null) {
//            $user->api_token = hash('sha512', time());
//        }

        if ($user->api_token == null || !is_numeric($user->api_token[0])) {
            //    $user->api_token = hash('sha512', time());
            $user->api_token = $user->createToken('api_token')->plainTextToken;

        }

        $user->device_token = $request->get('device_token');
        $user->save();


        return response()->success("User Profile", $user);
        //  return ['data' => $user];
    }


    public function forgetPassword(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|numeric',
            'country_code' => 'required',


        ]);


        if ($rules->fails()) {
            return response()->error($rules->errors()->first());
            //  return JsonResponse::fail($rules->errors()->first(), 400);
        }


        try {
            $mobile = '';
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
            $user = User::whereMobile($mobile)->first();
            if (!$user) {
                return response()->success(__("views.not found"), []);
                //   return response()->error(__('views.not found'));
                // return JsonResponse::fail(__('views.not found'));
            }

            $confirmation_password_code = substr(str_shuffle("0123456789"), 0, 6);
            $user->confirmation_password_code = $confirmation_password_code;
            $user->save();
            $country_code = $request->get('country_code', 966);

            $unifonicMessage = new UnifonicMessage();
            $unifonicClient = new UnifonicClient();
            $unifonicMessage->content = "Your Verification Code Is: ";
            $to = $country_code . $user->mobile;
            $co = $confirmation_password_code;
            $data = $unifonicClient->sendVerificationCode($to, $co, $unifonicMessage);
            Log::channel('single')->info($data);
            Log::channel('slack')->info($data);


            // return JsonResponse::success(['code' => $user->confirmation_password_code], __("views.Email Send"));
            return response()->success(__("views.Code Send"), ['code' => $user->confirmation_password_code]);
        } catch (\Exception $e) {
            return response()->success(__("views.not found"), []);
            //   return response()->error(__('views.not found'));
            //return JsonResponse::fail(__("views.not found"));
        }


    }


    public function show(Request $request)
    {


        //dd($request->header());
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }
        try {
            $user = $request->user();

            $user = User::where('id', $user->id)
                ->first();


            //   $user = UserResource::collection([$user]);

            //  $user->api_token = $request->user()->currentAccessToken();
            //  return JsonResponse::success($user[0], __('views.User Profile'));
            //  return ['data' => $user];
            return response()->success(__('views.User Profile'), $user);
        } catch (\Exception $e) {
            return response()->error($e->getMessage());
            // return JsonResponse::fail($e->getMessage(), 400);
        }


        // $user->api_token = $user->createToken('auth_token')->plainTextToken;


    }

    public function subscription_details(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        try {
            $user = $request->user();
            $user = User::where('id', $user->id)
                ->first();

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'is_iam_complete' => $user->is_iam_complete,
                'show_rate_request' => $user->show_rate_request,
                'user_plan' => $user->user_plan ? new MySubscriptionResource($user->user_plan) : null,
                'check_zatca' => $user->check_zatca,
                'have_platform_subscription' => $user->have_platform_subscription,
            ];
            return response()->success(__('views.User Profile'), $data);
        } catch (\Exception $e) {
            return response()->error($e->getMessage());
        }


        // $user->api_token = $user->createToken('auth_token')->plainTextToken;


    }


    public function logout()
    {


        if (auth()->check()) {
            auth()->user()->tokens()->delete();

            //     request()->session()->invalidate();

            //    request()->session()->regenerateToken();

            //  Auth::logout();
            return JsonResponse::success([], __('views.User Profile'));
        }
        return JsonResponse::fail(__('views.not authorized'));


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
            // 'type' => 'required',
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
                    'confirmation_code' => '',
                    'type' => 'provider',
                    //   'identity' => $request->get('identity'),
                    //  'email'              => $request->get('email')
                ]);
                $user = User::whereMobile($user->mobile)->first();
                if ($user->api_token == null) {
                    // $user->api_token = hash('sha512', time());
                    $user->api_token = $user->createToken('api_token')->plainTextToken;

                }


                $user->api_token = $user->createToken('api_token')->plainTextToken;

                $user->save();

                $checkIfEmp = Employee::where('emp_mobile', $mobile)
                    ->first();


                if ($checkIfEmp) {


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

    public function update(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);

            return response()->error(__('views.not authorized'));

        }
        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'email' => 'sometimes|required|unique:users,email,' . $user->id . ',id,deleted_at,NULL',
            'first_name' => 'sometimes|required',
            'last_name' => 'sometimes|required',
            'gender' => 'sometimes|required|string',
            'date_of_birth' => 'sometimes|required|date|before:2003-01-01',
            // 'user_name' => 'sometimes|required|unique:users,user_name',
            'user_name' => ['required', 'string', 'max:255', 'unique:users',
                'regex:/^[A-Za-z0-9]+(?:[ _-][A-Za-z0-9]+)*$/'
            ],
        ]);

        if ($rules->fails()) {
            //  return JsonResponse::fail($rules->errors()->first(), 400);
            return response()->error($rules->errors()->first());
        }

        try {

            $user = User::find($user->id);

            if (!$user) {
                return response()->error(__('views.not authorized'));

            }

            $user->update($request->only([

                'is_pay',
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
                'fal_license_number',
                'fal_license_expiry',

            ]));


            $user = User::find($user->id);


            $user = User::where('id', $user->id)
                ->first();


            $user = UserResource::collection([$user]);

            //  $user->api_token = $request->user()->currentAccessToken();
            // return JsonResponse::success($user[0], __('views.User Profile'));
            return response()->success(__('views.User Profile'), $user[0]);

        } catch (\Exception $e) {
            return response()->error($e->getMessage());
            // return JsonResponse::fail($e->getMessage(), 400);
        }


        //  return JsonResponse::success($user[0], __("views.User Profile"));
        //   return response(null, Response::HTTP_NO_CONTENT);
    }

    public function update_zatca(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'crn_number' => 'required|digits:15',
            'vat_name' => 'required',
            'vat_number' => 'required|digits:15',
            'zatca_city' => 'required',
            'zatca_city_subdivision' => 'required',
            'zatca_street' => 'required',
            'zatca_postal_zone' => 'required|digits:5',
        ]);

        if ($rules->fails()) {
            return response()->error($rules->errors()->first());
        }

        try {
            $user = User::find($user->id);
            if (!$user) {
                return response()->error(__('views.not authorized'));
            }

            $user->update($request->only([
                'crn_number',
                'vat_name',
                'vat_number',
                'zatca_city',
                'zatca_street',
                'zatca_postal_zone',
                'zatca_city_subdivision',
            ]));

            $user = User::where('id', $user->id)->first();
            $user = UserResource::collection([$user]);
            return response()->success(__('views.User Profile'), $user[0]);

        } catch (\Exception $e) {
            return response()->error($e->getMessage());
        }
    }


    public function ResetToken(Request $request)
    {

        $rules = Validator::make($request->all(), [

            'confirmation_password_code' => 'required|numeric',
            'mobile' => 'required|numeric',
            'country_code' => 'required',

            // 'country_code'              => 'sometimes|required',

        ]);


        if ($rules->fails()) {
            return response()->error($rules->errors()->first());
            //   return JsonResponse::fail($rules->errors()->first(), 400);
        }


        try {


            $confirmation_code = trim($request['confirmation_password_code']);


            $western_arabic = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $eastern_arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $confirmation_code = str_replace($eastern_arabic, $western_arabic, $confirmation_code);
            $confirmation_code = str_replace(['+', '-'], '', filter_var($confirmation_code, FILTER_SANITIZE_NUMBER_INT));

            /* $user = Client::where('confirmed', 1)->where('confirmation_code', $confirmation_code)->first(); */
            $user = User::where('confirmation_password_code', $confirmation_code)->first();


            if ($user) {


                // return JsonResponse::success(['code' => $request['confirmation_password_code']], "Code True");
                return response()->success(__("views.Code Send"), ['code' => $user->confirmation_password_code]);


            } else {


                return response()->error('code not valid');
                throw ValidationException::withMessages([
                    'confirmation_code' => [trans('messages.confirmation_mismatch')],
                ]);


            }

        } catch (\Exception $e) {

            return response()->error($e->getMessage());
            //   return JsonResponse::fail($e->getMessage(), 400);
        }

    }


    public function updatePasswordByPhone(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'code' => 'required|numeric',
            // 'country_code'              => 'sometimes|required',

        ]);


        if ($rules->fails()) {

            return response()->error($rules->errors()->first());
            //    return JsonResponse::fail($rules->errors()->first(), 400);
        }


        try {
            $user = User::where('confirmation_password_code', $request->get('code'))->first();


            if ($user) {

                $user->update(["password" => app('hash')->make($request->password), 'confirmation_password_code' => null]);


                $user = User::where('id', $user->id)
                    ->first();


                $user = UserResource::collection([$user]);
                return response()->success(__('views.User Profile'), $user[0]);
                //  $user->api_token = $request->user()->currentAccessToken();
                //  return JsonResponse::success($user[0], __('views.User Profile'));
                //   return JsonResponse::success($user, "User Password Updated!");

            }
            return response()->error("Incorrect Password!");
            //return JsonResponse::fail("Incorrect Password!");
        } catch (\Exception $ex) {
            return response()->error("Incorrect Password!");
            //  return JsonResponse::fail("Incorrect Password!");
        }


    }

    public function Mynotification(Request $request)
    {

        //dd($request->header());
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);

            return JsonResponse::fail(__('views.not authorized'));

        }

        try {
            $user = $request->user();
            $user = User::with('wallet')->find($user->id);
            // $user->api_token = $user->createToken('auth_token')->plainTextToken;

            if ($user == null) {
                return JsonResponse::fail(__('views.not authorized'));
            }
            $notifications = $user->notifications;

            //  $user = User::find($user->id);
            //  $user->api_token = $request->user()->currentAccessToken();
            return JsonResponse::success($notifications, __('views.User Profile'));
        } catch (\Exception $ex) {
            return JsonResponse::fail(__('views.not found'));
        }

        //
    }

    public function uploadAvatar(Request $request)
    {

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);

            return response()->error(__('views.not authorized'));

        }
        $user = $request->user();


        if (!$user) {
            return response()->error(__('views.not authorized'));

        }

        $rules = Validator::make($request->all(), [

            'logo' => 'required|image|mimes:jpeg,bmp,png',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        try {
            $user = $request->user();

            $user = User::find($user->id);

            if (!$user) {
                return response()->error(__('views.not authorized'));

            }


            $path = $request->file('logo')->store('public/users/photo');

            $path = str_replace('public', 'storage', $path);
            //    $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


            $user->update(['logo' => $path]);
            $user = User::find($user->id);
            $user = UserResource::collection([$user]);

            //  $user->api_token = $request->user()->currentAccessToken();
            return response()->success(__('views.User Profile'), $user[0]);

        } catch (\Exception $ex) {

            return response()->error(__('views.not found'));

        }


    }

    public function updatePassword(Request $request)
    {


        $rules = Validator::make($request->all(), [

            //  'password' => 'required',
            'password' => 'required',
            'old_password' => 'required',
            //  'password_confirmation' => 'required',

            // 'country_code'              => 'sometimes|required',

        ]);


        if ($rules->fails()) {

            //   return $rules->errors()->first();
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);

            return JsonResponse::fail(__('views.not authorized'));

        }
        $user = $request->user();

        $user = User::find($user->id);

        if ($user == null) {
            return JsonResponse::fail(__("views.not authorized"));
        }

        if ($user->password) {
            if (!\Hash::check($request->old_password, $user->password)) {
                return JsonResponse::fail(__("views.old password wrong"));
            }


            if ($user) {

                $user->update(["password" => app('hash')->make($request->password)]);

                $user = User::find($user->id);
                $user = UserResource::collection([$user]);

                //  $user->api_token = $request->user()->currentAccessToken();
                return JsonResponse::success($user[0], __('views.User Profile'));
                //  return JsonResponse::success(__("views.User Password Updated!"));

            }
            return JsonResponse::fail(__("views.Incorrect Password!"));
        }


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


    public function resendforgetPassword(Request $request)
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

    public function storeEmployee(Request $request)
    {


        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);

            return response()->error(__('views.not authorized'));

        }
        $user = $request->user();


        if (!$user) {
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


        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);

            return response()->error(__('views.not authorized'));

        }
        $user = $request->user();


        if (!$user) {
            return response()->error(__('views.not authorized'));

        }


        $user = User::find($user->id);
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
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);

            return response()->error(__('views.not authorized'));

        }
        $user = $request->user();


        if (!$user) {
            return response()->error(__('views.not authorized'));

        }


        $user = User::find($user->id);
        $client = Employee::with('user_information')->where('user_id', $user->id);
        //->pluck('emp_mobile');

        // $allClien = User::whereIn('mobile', $client->toArray());
        if ($request->get('emp_name')) {
            $search = trim($request->get('emp_name'));
            $client = $client->Where('emp_name', 'like', '%' . $search . '%');
        }

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $client = $client->paginate($size);

        $client = EmpUserResource::collection($client)->response()->getData(true);

        if ($client) {

            return response()->success("Clients", $client);
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }


    }
}
