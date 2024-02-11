<?php

namespace App\Http\Controllers\DashBoard\Bank;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Models\v2\DeferredInstallment;

use App\Models\v2\NotificationUser;
use App\Models\v3\DeferredInstallmentComment;
use App\Models\v4\FcmToken;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class DeferredInstallmentsRequestController extends Controller
{


    public function index(Request $request)
    {


        //  dd($request->get('query')['neighborhood_id']);

        $finiceing = DeferredInstallment::with('user');

        $page = $request->get('page_number', 10);
        if ($request->get('status')) {

            if ($request->get('status') == 'active') {
                $finiceing = $finiceing->where('status', '1');
            }
            if ($request->get('status') == 'not_active') {
                $finiceing = $finiceing->where('status', '2');
            }
            if ($request->get('status') == 'waiting') {
                $finiceing = $finiceing->where('status', '3');
            }


        }

        //  dd($finiceing);


        if ($request->get('estate_type_id')) {
            $finiceing = $finiceing->where('estate_type_id', $request->get('estate_type_id'));
        }
        if ($request->get('area_estate_id')) {
            $finiceing = $finiceing->where('area_estate_id', $request->get('area_estate_id'));
        }
        if ($request->get('dir_estate_id')) {
            $finiceing = $finiceing->where('dir_estate_id', $request->get('dir_estate_id'));
        }
        if ($request->get('estate_price_id')) {
            $finiceing = $finiceing->where('estate_price_id', $request->get('estate_price_id'));
        }
        if ($request->get('city_id')) {
            $finiceing = $finiceing->where('city_id', $request->get('city_id'));
        }

        if ($request->get('neighborhood_id')) {

            //dd( $request->get('query')['neighborhood_id']);
            // dd($request->get('query')['neighborhood_id']);
            $finiceing = $finiceing->whereHas('neighborhood', function ($q) use ($request) {


                $q->whereIn('neighborhood_id', $request->get('neighborhood_id'));
            });


            //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
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

        if ($request->get('state_id')) {
            $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
        }


        if ($request->get('search')) {
            $search = trim($request->get('search'));



            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && DeferredInstallment::find($request->get('search'))) {
                $finiceing = $finiceing->where('id', $request->get('search'));

            }
            else {
                $finiceing = $finiceing

                    ->Where('rent_price', 'like', '%' . $search . '%')
                    //  ->orWhere('owner_name', 'like', '%' . $search . '%')
                    // ->orWhere('owner_mobile', 'like', '%' . $search . '%')
                    //     ->orWhere('owner_identity_number', 'like', '%' . $search . '%')
                    ->orWhere('tenant_name', 'like', '%' . $search . '%')
                    ->orWhere('tenant_mobile', 'like', '%' . $search . '%')
                    ->orWhere('tenant_identity_number', 'like', '%' . $search . '%')
                    ->orWhere('tenant_job_type', 'like', '%' . $search . '%');
                //    ->orWhere('employer_name', 'like', '%' . $search . '%');
            }




        }
        $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);


        return response()->success(__('DeferredInstallment Requests'), $finiceing);

    }


    public function singleDeferredOffer($id)
    {
        $finiceing = DeferredInstallment::with('user','comment')->find($id);

        return response()->success("DeferredInstallment", $finiceing);
    }

    public function updateStatus(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'id'   => 'required',
            'status'  => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_order = DeferredInstallment::where('id', $request->get('id'))->first();


        if ($request_order) {
            $request_order->status = '' . $request->get('status') . '';
            $request_order->save();


            return response()->success(__('DeferredInstallment Requests'), $request_order);

        } else {


            return response()->error(__('views.some_error'));

        }
    }

    public function addComment(Request $request)
    {

        $user = auth()->guard('Fund')->user();
        $rules = Validator::make($request->all(), [
            'id'   => 'required',
            'comment'  => 'required',
            'display_in_app'  => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_order = DeferredInstallment::where('id', $request->get('id'))->first();


        if ($request_order) {

            $comment=DeferredInstallmentComment::create([
                'comment'=>$request->get('comment'),
                'user_id'=>$user->id,
                'deferred_installment_id'=>$request->get('id'),
                'display_in_app'=>$request->get('display_in_app'),

            ]);

            if($request->get('display_in_app')==1)
            {
                $client=User::where('id',$request_order->user_id)->first();

                if ($client) {
                    $push_data = [
                        'title'   =>  $request->get('comment'),
                        'body'    =>  $request->get('comment'),
                        'id'      => $request_order->id,
                        'user_id' => $client->id,
                        'type'    => 'deferred_installment',
                    ];

                    $note = NotificationUser::create([
                        'user_id' => $client->id,
                        'title'   => $request->get('comment'),
                        'type'    => 'request',
                        'type_id' =>  $request_order->id,
                    ]);

                    $fcm_token = FcmToken::where('user_id', $client->id)->get();
                    foreach ($fcm_token as $token) {
                        send_push($token->token, $push_data, $token->type);
                    }

                }
            }


            return response()->success('تمت الإضافة بنجاح', $comment);

        }
        else {


            return response()->error(__('views.some_error'));

        }
    }


    public function displayInApp(Request $request)
    {
        $user = auth()->guard('Fund')->user();
        $rules = Validator::make($request->all(), [
            'id'   => 'required',
            'display_in_app'  => 'required',

        ]);



        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $request_order = DeferredInstallment::where('id', $request->get('id'))->first();


        if ($request_order) {


            $request_order->display_in_app=$request->get('display_in_app');
            $request_order->save();
            return response()->success('تمت الإضافة بنجاح', $request_order);

        }
        else {


            return response()->error(__('views.some_error'));

        }
    }

}
