<?php

namespace App\Http\Controllers\DashBoard\Admin;


use App\Http\Controllers\Controller;

use App\Jobs\OtpJob;
use App\Models\dashboard\Admin;

use App\Models\v2\Bank;
use App\Models\v2\Client;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\Plan;
use App\Models\v2\RequestOffer;
use App\Models\v2\Setting;
use App\Models\v2\UserPayment;
use App\Models\v2\UserPlan;
use App\Models\v3\AdminPermission;
use App\Models\v3\Permission;
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

class UserController extends Controller
{

    public function index(Request $request)
    {


        //  dd($request->get('query')['neighborhood_id']);

        $finiceing = User::query();

        $page = $request->get('page_number', 10);
        if ($request->get('status')) {

            if ($request->get('status') == 'active') {
                $finiceing = $finiceing->where('is_pay', '1');
            }
            if ($request->get('status') == 'not_active') {
                $finiceing = $finiceing->where('is_pay', '0');
            }


        }


        if ($request->get('is_verified')) {

            if ($request->get('is_verified') == '1') {
                $finiceing = $finiceing->where('mobile_verified_at', '!=', null)
                    ->orwhere('email_verified_at', '!=', null);
            }
            if ($request->get('is_verified') == '0') {
                $finiceing = $finiceing
                    ->where('mobile_verified_at', null)
                    ->where('email_verified_at', null);
            }


        }


        if ($request->get('type')) {


            $finiceing = $finiceing->where('type', $request->get('type'));


        }


        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d H:i:s");

            $finiceing = $finiceing->whereDate('created_at', '>', $date);
        }
        if ($request->get('to_date')) {

            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d H:i:s");
            $finiceing = $finiceing->whereDate('created_at', '<', $date);
        }


        if ($request->get('search')) {
            $search = trim($request->get('search'));
            $finiceing = $finiceing
                ->Where('name', 'like', '%' . $search . '%')
                ->orWhere('mobile', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('address', 'like', '%' . $search . '%')
                ->orWhere('lat', 'like', '%' . $search . '%')
                ->orWhere('lan', 'like', '%' . $search . '%')
                ->orWhere('user_name', 'like', '%' . $search . '%')
                ->orWhere('onwer_name', 'like', '%' . $search . '%')
                ->orWhere('experience', 'like', '%' . $search . '%');

        }
        $finiceing = $finiceing->paginate($page);


        return response()->success(__('All User'), $finiceing);

    }


    public function singleUserOld($id)
    {
        $finiceing = User::find($id);

        return response()->success("User", $finiceing);
    }


    public function singleUser($id)
    {
        $user = User::find($id);
        if ($user == null) {
            return response()->error(__('views.not found'));
        }
        $count_estate = Estate::where('user_id', $user->id)->count();
        $count_request = EstateRequest::where('user_id', $user->id)->count();
        $count_offer = RequestOffer::where('provider_id', $user->id)->count();
        $count_client = Client::where('user_id', $user->id)->count();
        $count_accept_offer = RequestOffer::where('provider_id', $user->id)
            ->where('status', 'accepted_customer')
            ->count();
        $count_accept_fund_offer = FundRequestOffer::where('provider_id', $user->id)
            ->where('status', 'accepted_customer')
            ->count();

        $new_estate = Estate::where('user_id', $user->id)->orderBy('id', 'desc')->limit(10)->get();
        $new_offer = RequestOffer::with('estate')->where('provider_id', $user->id)->orderBy('id',
            'desc')->limit(10)->get();
        $new_fund_offer = FundRequestOffer::where('provider_id', $user->id)->orderBy('id', 'desc')->limit(10)->get();
        // dd($categories->toArray());

        $array =
            [
                'user' => $user,
                //   'count_estate'=>$count_estate,
                'count_request' => $count_request,
                //  'count_offer'=>$count_offer,
                //   'count_client'=>$count_client,
                //   'count_accept_offer'=>$count_accept_offer,
                //    'count_accept_fund_offer'=>$count_accept_fund_offer,
                'new_estate' => $new_estate,
                'new_offer' => $new_offer,
                'new_fund_offer' => $new_fund_offer
            ];

        return response()->success("User", $array);


    }

    public function updateStatus(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_order = User::where('id', $request->get('id'))->first();


        if ($request->get('status') == 'active') {
            $status = '1';
        } else {
            $status = '0';
        }


        if ($request->get('status')) {
            //  $request_order->is_pay = "" . $status . "";
            $request_order->status = $request->get('status');
            $request_order->save();


            return response()->success(__('User Status Updated'), $request_order);

        } else {


            return response()->error(__('views.some_error'));

        }
    }

    public function updateCertified(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'id' => 'required',
            'is_certified' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_order = User::where('id', $request->get('id'))->first();


        if ($request->get('is_certified') == 'active') {
            $status = '1';
        } else {
            $status = '0';
        }


        if ($request->get('is_certified')) {
            //  $request_order->is_pay = "" . $status . "";
            $request_order->is_certified = $status;
            $request_order->save();


            return response()->success(__('User Status Updated'), $request_order);

        } else {


            return response()->error(__('views.some_error'));

        }
    }

    public function userUpgrade(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'user_id' => 'required',
            'plan_id' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $userplan = UserPlan::where('user_id', $request->get('user_id'))
            ->where('date_end', '>', date('Y-m-d'))
            ->first();
        if ($userplan) {
            return response()->error(__('views.you_have_active_plan'));
        }

        $user = User::where('id', $request->get('user_id'))->first();
        $plan = Plan::find($request->get('plan_id'));


        if ($plan) {
            $userPlan = UserPlan::create(
                [
                    'plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'date_start' => date('Y-m-d'),
                    'date_end' => date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $plan->days . ' days')),
                    'status' => 1,
                    'payment_url',
                    'unique_code' => uniqid(),
                    'total' => $plan->price
                ]
            );


            $user->is_pay = 1;
            $user->save();


            return response()->success("User Profile", $user);
        } else {


            return response()->error(__('views.some_error'));

        }


    }


    public function update(Request $request)
    {


        /*   if ($request->get('country_code') && $request->get('mobile')) {
               $global_mobile = intval($request->get('country_code')) . intval($request->get('mobile'));
               $request->merge(['mobile' => $global_mobile]);
           }
   */
        $rules = Validator::make($request->all(), [

            //'name' => 'sometimes|required',
            'id' => 'required',

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
            'email' => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $user = User::find($request->get('id'));

        $status = 0;
        if ($request->get('status') == 'active') {
            $status = '1';
        } else {
            $status = '0';
        }


        $request->merge([
            'from_app' => true,
            'status' => $status,
            //      'api_token' => hash('sha512', time()),
            'user_name' => $request->get('user_name') != null ? $request->get('user_name') : $user->user_name,
            'is_edit_username' => $request->get('user_name') ? '1' : '0',
        ]);


        $user->update($request->only([
            'is_pay',
            'name',
            'email',
            'status',
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
            'address',
            'user_name',
            'is_edit_username',
            'onwer_name',
            'office_staff',
            'experience',

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


    public function createBetaUser(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'user_id' => 'required',
            //  'plan_id' => 'required',
            //  'days_number' => 'required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $userplan = UserPlan::where('user_id', $request->get('user_id'))
            ->where('date_end', '>', date('Y-m-d'))
            ->first();
        if ($userplan) {
            return response()->error(__('views.you_have_active_plan'));
        }


        $setting = Setting::first();
        $count_days = 0;
        if ($request->get('days_number')) {
            $count_days = $request->get('days_number');
        } else {
            $count_days = $setting->count_beta_days;
        }


        $user = User::where('id', $request->get('user_id'))->first();
        $plan = Plan::find(4);


        if ($plan) {
            $userPlan = UserPlan::create(
                [
                    'plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'date_start' => date('Y-m-d'),
                    'date_end' => date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $count_days . ' days')),
                    'status' => 1,
                    'payment_url',
                    'unique_code' => uniqid(),
                    'total' => 0
                ]
            );


            $user->is_pay = 1;
            $user->save();


            return response()->success("User Profile", $user);
        } else {


            return response()->error(__('views.some_error'));

        }


    }


    public function chooseUserPlan(Request $request)
    {

        $rules = Validator::make($request->all(), [
            'plan_id' => 'required',
            'user_id' => 'required',
            'payment_method_id' => 'required',
            //  'plan_id' => 'required',
            //  'days_number' => 'required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        // dd($request->all());

        $unique_code = uniqid();

        $user = User::where('id', $request->get('user_id'))->first();

        $user->unique_code = $unique_code;
        $user->save();

        $plan = Plan::find($request->get('plan_id'));


        if (!$user) {
            return response()->error(__('views.not found'));

        }


        if ($request->get('payment_method_id') == 2) {
            if ($plan) {

                $user_plan = UserPlan::create([
                    'plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'status' => '0',
                    'unique_code' => $unique_code,
                    'payment_method' => $request->get('payment_method_id'),
                    'payment_url' => null,
                    'count_try' => 0,
                    'total' => $plan->price
                ]);


                $text = 'شكرا لإشتراكك معنا ونرحب بإنضمامك لعائلة عقارز';
                $message = 'http://aqarz.sa/plans/' . $user->unique_code;
                ini_set("smtp_port", "465");
                $banks = Bank::where('status', '1')->get();
                $userDet = UserPlan::with('user', 'plan')->where('unique_code',
                    $unique_code)->first();


                $to = $user->email;


                $from = 'info@aqarz.sa';
                $name = 'Aqarz';
                $subject = 'شكرا لإشتراكك معنا';


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
                    'banks' => $banks,
                    'userDet' => $userDet->user,
                    'planDet' => $userDet->plan,
                ];


                // var_export (dns_get_record ( "host.name.tld") );

                // dd(444);
                //        \Mail::to($to)->send(new \App\Mail\NewBankMail($details));

                /* if (Mail::failures()) {
                     return response()->json([
                         'status'  => false,
                         'data'    => $details,
                         'message' => 'Nnot sending mail.. retry again...'
                     ]);
                 }*/


                $user_mobile = checkIfMobileStartCode($user->mobile, $user->country_code);
                $unifonicMessage = new UnifonicMessage();
                $unifonicClient = new UnifonicClient();
                $unifonicMessage->content = "تم ارسال معلومات الدفع الخاصة بك الي البريدالالكتروني ";
                $to = $user_mobile;
                $co = $message;
                $data = $unifonicClient->sendCustomer($to, $co);
                \Log::channel('single')->info($data);
                \Log::channel('slack')->info($data);

            }


        } else {

            $text = 'رابط الدفع الخاص بك هو : ';
            $message = 'http://aqarz.sa/plans/' . $user->unique_code;
            ini_set("smtp_port", "465");

            $to = $user->email;


            $from = 'info@aqarz.sa';
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
            //      \Mail::to($to)->send(new \App\Mail\NewMailLink($details));

            /* if (Mail::failures()) {
                 return response()->json([
                     'status'  => false,
                     'data'    => $details,
                     'message' => 'Nnot sending mail.. retry again...'
                 ]);
             }*/


            $user_mobile = checkIfMobileStartCode($user->mobile, $user->country_code);
            $unifonicMessage = new UnifonicMessage();
            $unifonicClient = new UnifonicClient();
            $unifonicMessage->content = "Your Verification Code Is: ";
            $to = $user_mobile;
            $co = $message;
            $data = $unifonicClient->sendCustomer($to, $co);
            \Log::channel('single')->info($data);
            \Log::channel('slack')->info($data);


            //  return $data;
        }
        return response()->success(trans('تم ارسال بيانات الدفع بنجاح'), []);

        /*  \Session::flash('success', trans('تم ارسال بيانات الدفع بنجاح'));

          return redirect()->route('admin.payment_requests.index');*/
    }


    public function create_admin(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not found'));

        }


        $rules = Validator::make($request->all(), [

            'mobile' => 'required|between:7,20|unique:admins,mobile,null,id,deleted_at,NULL',
            'email' => 'required|between:7,20|unique:admins,email,null,id,deleted_at,NULL',
            'password' => 'required|string|min:8|confirmed',
            'name' => 'required|string',
            'country_code' => 'required'

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


        $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
        $request->merge([
            'password' => app('hash')->make($request->input('password')),
            'confirmation_code' => $confirmation_code,
            'api_token' => hash('sha512', time()),
            'status' => 'active',

        ]);

        $user = Admin::create($request->only([
            'name',
            'mobile',
            'email',

            'status',
            'password',

            'api_token',
            'country_code',

            'confirmation_password_code',


        ]));


        $user = Admin::find($user->id);

        return response()->success(__('تم الانشاء بنجاح'), $user);
        // return ['data' => $user];
    }

    public function update_admin(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not found'));

        }

        if ($request->get('admin_id') == 1) {
            return response()->error(__('لاتستطيع تعديل الادمن الاساسي'));

        }


        $rules = Validator::make($request->all(), [

            /* 'mobile' => 'required|between:7,20|unique:admins,mobile,null,id,deleted_at,NULL',
             'email' => 'required|between:7,20|unique:admins,email,null,id,deleted_at,NULL',
             'password' => 'required|string|min:8|confirmed',
             'name' => 'required|string',
             'country_code' => 'required',*/
            'admin_id' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $mobile = null;
        $admin = Admin::find($request->get('admin_id'));
        if (!$admin) {
            return response()->error(__('views.not found'));

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

            $request->merge([
                'mobile' => $mobile,
            ]);
        }


        $confirmation_code = substr(str_shuffle("0123456789"), 0, 6);
        $request->merge([
            'confirmation_code' => $confirmation_code,
            'api_token' => $request->status == 'block' ? null : hash('sha512', time()),
            //   'status'            => 0,
        ]);

        if ($request->get('password')) {
            $request->merge([
                'password' => app('hash')->make($request->input('password')),
            ]);
        }

        $admin->update($request->only([
            'name',
            'mobile',
            'country_code',
            'email',
            'password',
            'status',
            'api_token',
            'country_code',
            'confirmation_password_code',
        ]));

        $user = Admin::find($admin->id);

        return response()->success(__('تم التعديل بنجاح بنجاح'), $admin);
        // return ['data' => $user];
    }

    public function update_password(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not found'));
        }


        $rules = Validator::make($request->all(), [
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
            'admin_id' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $admin = Admin::find($request->get('admin_id'));
        if (!$admin) {
            return response()->error(__('views.not found'));

        }
        $user = Admin::find($admin->id);
        $user->password = \Hash::make($request->get('new_password'));
        $user->save();
        return response()->success(__('views.password update success'));
    }

    public function permissions(Request $request)
    {
        $permissions = Permission::where('status', 1);
        if ($request->get('search')) {
            $search = trim($request->get('search'));
            $permissions = $permissions
                ->Where('slug', 'like', '%' . $search . '%')
                ->orWhere('display_name', 'like', '%' . $search . '%');

        }
        $permissions = $permissions->get();


        return response()->success("كافة الصلاحيات", $permissions);
    }

    public function admins(Request $request)
    {
        $admins = Admin::query();
        if ($request->get('search')) {
            $search = trim($request->get('search'));
            $admins = $admins
                ->Where('name', 'like', '%' . $search . '%');

        }
        if ($request->page_number && $request->page_number > 0) {
            $admins = $admins->orderBy('id' , 'desc')->paginate($request->page_number);
        } else {
            $admins = $admins->orderBy('id' , 'desc')->paginate();
        }


        return response()->success("كافة المدراء", $admins);
    }

    public function add_permissions(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'permissions' => 'required',
            'admin_id' => 'required',

        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        // dd($request->all());


        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not found'));

        }

        $deletePermission = AdminPermission::where('admin_id', $request->get('admin_id'))
            ->delete();
        /*   $permission_array=explode(',',$request->get('permissions'));
           dd($permission_array);*/
        $permissions = AdminPermission::create([
            'admin_id' => $request->get('admin_id'),
            'permissions' => $request->get('permissions'),
        ]);
        return response()->success("تم الحفظ بنجاح", $permissions);
    }
}
//52و43و42

