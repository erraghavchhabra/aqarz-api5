<?php

namespace App\Http\Controllers\Platform;


use App\Http\Controllers\Controller;
use App\Http\Resources\Platform\MySubscriptionResource;
use App\Http\Resources\Platform\PlanResources;
use App\Models\v4\PlatformPlan;
use App\Models\v4\PlatformSubscriptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    public function plan()
    {
        $plan = PlatformPlan::where('status', 'active')->get();
        return response()->success(__("views.Done"), PlanResources::collection($plan));
    }

    public function check_plan(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:platform_plan,id',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }


        $user_plan = $user->platform_plan ? $user->platform_plan->where('status', 'active')->first() : null;


        $plan = PlatformPlan::find($request->plan_id);
        if (!$plan)
            return response()->error(__("views.not found"));

        $data = [
            'plan' => new PlanResources($plan),
            'user_plan' => $user_plan,
        ];

        return response()->success(__("views.Done"), $data);
    }

    public function redirect_payment(Request $request)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tap.company/v2/charges/' . $request->tap_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . config('payment.secret_key_test'),
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $result = json_decode($response);
        if (@$result->status == 'CAPTURED') {
            $order_id = $result->reference->order;
            $subscription = PlatformSubscriptions::find($order_id);
            if ($subscription->status != 'active') {

                $plan = PlatformPlan::find($subscription->plan_id);
                $duration_type = $plan->duration_type;
                $duration_number = $plan->duration;
                if ($duration_type == 'month') {
                    if ($duration_number == 1) {
                        $end_date = Carbon::now()->addMonth($duration_number);
                    } else {
                        $end_date = Carbon::now()->addMonths($duration_number);

                    }
                } elseif ($duration_type  == 'day'){
                    $end_date = Carbon::now()->addDays($duration_number);
                }else {
                    $end_date = Carbon::now()->addYear($duration_number);
                }

                $subscription->status = 'active';
                $subscription->start_time = Carbon::now();
                $subscription->end_time = $end_date;
                $subscription->save();
            }


            \App\Models\v4\PaymentLog::create([
                'payment_id' => $result->id,
                'action_id' => $result->object,
                'user_id' => $subscription->user_id,
                'price' => $subscription->price,
                'response_summary' => $result->status,
                'response_code' => @$result->response->code,
                'approved' => @$result->response->message,
                'currency' => $result->currency,
                'status' => $result->status,
                'plan_subscription_id' => $subscription->id,
                'source_scheme' => $result->source->payment_method
            ]);
            return redirect()->away('https://platform.aqarz.sa/subscriptions?status=success');
        } else {
            return redirect()->away('https://platform.aqarz.sa/subscriptions?status=fail');
        }


    }

    public function subscription(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:platform_plan,id',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }

        $plan = PlatformPlan::find($request->plan_id);
        if (!$plan)
            return response()->error(__("views.not found"));

        $user_plan = $user->platform_plan ? $user->platform_plan->where('status', 'active')->first() : null;

        if ($user_plan) {
//            if ($user_plan->id == $plan->id) {
            return response()->error(__("views.already subscribed"));
//            }
        }

//        $user->plan_id = $plan->id;
//        $user->save();

        $plan_subscription = PlatformSubscriptions::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'duration' => $plan->duration,
            'duration_type' => $plan->duration_type,
            'contract_number' => $request->quantity && $request->quantity > 0 ? $request->quantity : $plan->contract_number,
            'price' => $request->quantity && $request->quantity > 0 ? $plan->price * $request->quantity : $plan->price,
            'quantity' => $request->quantity ? $request->quantity : 0,
            'status' => 'pending'
        ]);

//        $user->subscription_pending = $plan_subscription;
//        $user->save();

        $plan_subscription->payment_url = url('payment?subscription_id=' . $plan_subscription->id);

        return response()->success(__("views.Done"), $plan_subscription);
    }

    public function my_subscription(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();

        $user_plan = PlatformSubscriptions::where('user_id', $user->id)->where('status','!=' , 'pending')->get();

        return response()->success(__("views.Done"), MySubscriptionResource::collection($user_plan));
    }

}
