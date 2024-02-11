<?php

namespace App\Http\Controllers\DashBoard\Bank;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Models\v2\Finance;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class FinanceRequestController extends Controller
{


    public function index(Request $request)
    {


        //  dd($request->get('query')['neighborhood_id']);

        $finiceing = Finance::with('user');


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

        if ($request->get('state_id')) {
            $finiceing = $finiceing->WhereHas('city', function ($query) use ($request) {
                $query->where('state_id', $request->get('state_id'));
            });
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
        if ($request->get('search')) {
            $finiceing = $finiceing->WhereHas('user', function ($query) use ($request) {
                $query->where('name', $request->get('search'));
            });


        }

        $finiceing = $finiceing->orderBy('id', 'desc')->paginate($page);
        return response()->success("Finance Requests", $finiceing);

    }



    public function singleFinance($id)
    {
        $finiceing = Finance::find($id);

        return response()->success("Finance", $finiceing);
    }

    public function updateStatus(Request $request)
    {


        $rules = Validator::make($request->all(), [
            'id'     => 'required',
            'status' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $request_order = Finance::where('id', $request->get('id'))->first();


        if ($request) {
            $request_order->status = '' . $request->get('status') . '';
            $request_order->save();


            return response()->success(__('Finance Requests'), $request_order);

        } else {


            return response()->error(__('views.some_error'));

        }
    }

}
