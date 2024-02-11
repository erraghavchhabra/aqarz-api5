<?php

namespace App\Http\Controllers\v3\Auth;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    public function login(Request $request)
    {

        dd(44);
        // TODO when facebook user doesn't have email just phone number


        $rules = Validator::make($request->all(), [

            'username'     => 'required_if:referer,local|max:255',
            'password'     => 'required_if:referer,local|min:3',
            'device_token' => 'sometimes|required',
            'device_type'  => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $username = $request->username;
        $mobile = 0;
        $old_mobile = "";


        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $username_column = 'email';
        }


        else {

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
            'status'         => true
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


        if( $user->api_token==null)
        {
            $user->api_token = hash('sha512', time());

        }
        $user->device_token = $request->get('device_token');
        $user->save();


        return response()->success("User Profile", $user);
        //  return ['data' => $user];
    }


    public function loginNew(Request $request)
    {


        dd(4433);
        // TODO when facebook user doesn't have email just phone number


        $rules = Validator::make($request->all(), [

            'username'     => 'required_if:referer,local|max:255',
          //  'password'     => 'required_if:referer,local|min:3',
            'device_token' => 'sometimes|required',
            'device_type'  => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $username = $request->username;
        $mobile = 0;
        $old_mobile = "";


        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $username_column = 'email';
        }


        else {

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
            'status'         => true
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


        if( $user->api_token==null)
        {
            $user->api_token = hash('sha512', time());

        }
        $user->device_token = $request->get('device_token');
        $user->save();


        return response()->success("User Profile", $user);
        //  return ['data' => $user];
    }

    public function login_site(Request $request)
    {
        // TODO when facebook user doesn't have email just phone number


        $rules = Validator::make($request->all(), [

            'username'     => 'required_if:referer,local|max:255',
            'password'     => 'required_if:referer,local|min:3',
          //  'device_token' => 'sometimes|required',
         //   'device_type'  => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $username = $request->username;
        $mobile = 0;
        $old_mobile = "";


        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $username_column = 'email';
        }


        else {

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
            'status'         => true
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


        if( $user->api_token==null)
        {
            $user->api_token = hash('sha512', time());

        }
       // $user->device_token = $request->get('device_token');
        $user->save();
        Auth::loginUsingId($user->id);
        return redirect()->intended(route('home'));

      //  return response()->success("User Profile", $user);
        //  return ['data' => $user];
    }









    public function loginViaEmail(Request $request)
    {


        $validate = Validator::make($request->all(), [
            'email'          => 'required',
            'password'       => 'required',
            'firebase_token' => ['required'],
            'device_type'    => ['required'],
            'latitude'       => ['required'],
            'longitude'      => ['required'],
        ]);
        if ($validate->fails()) {
            return JsonResponse::fail($validate->errors()->first(), 400);
        }


        if ($user = $this->authService->loginViaEmail($request->get('email'),
            $request->get('password'))) {
            $data['firebase_token'] = $request->get('firebase_token');
            $deviceData = $request->only('firebase_token', 'device_type', 'latitude', 'longitude');
            $deviceData['user_id'] = $user->id;
            $userdata = User::find($user->id);
            $user['login_history'] = $userdata->LoginHistory;
            //$userUpdate = User::where('id',$user->id)->update(['firebase_token' => $request->get('firebase_token')]);
            return JsonResponse::success($user);
        }

        return JsonResponse::fail(__('auth.invalid_credentials'));
    }

    public function loginEstateFund(Request $request)
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
            'status'         => true
        ]);
        $credentials = $request->only($username_column, 'password', 'status');

        $class = new \App\EstateFund();

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



        if( $user->api_token==null)
        {
            $user->api_token = hash('sha512', time());
            $user->save();

        }


        return response()->success("User Profile", $user);
        //  return ['data' => $user];
    }

    public function loginRent(Request $request)
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
            'status'         => true
        ]);
        $credentials = $request->only($username_column, 'password', 'status');

        $class = new \App\Rent();

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



        if( $user->api_token==null)
        {
            $user->api_token = hash('sha512', time());
            $user->save();

        }


        return response()->success("User Profile", $user);
        //  return ['data' => $user];
    }
}
