<?php

namespace App\Http\Controllers\DashBoard\Fund;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\RequestFundOfferResource;

use App\Imports\FundOfferExport;
use App\Imports\FundRequestExport;
use App\Models\v1\Neighborhood;
use App\Models\v2\FundRequestHasOffer;
use App\Models\v2\FundRequestNeighborhood;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\RequestFund;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Excel;
use Rap2hpoutre\FastExcel\FastExcel;

class ExprotController extends Controller
{

    public function export_fund(Request $request)
    {

        $rules = Validator::make($request->all(), [

//            'from_date' => 'required',
//            'to_date'   => 'required',
            'type'      => 'required',

        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

//        $fromdate = $request->get('from_date');
//        $todate = $request->get('to_date');



        if ($request->get('type') == 'request') {
//            return Excel::download(new FundRequestExport(),
//                'fundRequests_' . Carbon::now(). '.xlsx');
           $data =  RequestFund::with('neighborhood', 'offers')->get();
            return (new FastExcel($data))->download('fundRequests_' . Carbon::now(). '.xlsx' , function ($data) {
                return [
                    'رقم الطلب' => $data->id,
                    'نوع العقار' => $data->estate_type_name_web,
                    'اتجاه العقار' => $data->dir_estate,
                    'رقم المستفيد' => $data->beneficiary_mobile,
                    'المدينة' => $data->city_name_web,
                    'الحي' => $data->neighborhood_name,
                    'تاريخ الطلب' => $data->created_at->format('d/m/Y - h:i A'),
                    'السعر المطلوب' => $data->estate_price_range,
                    'المساحة المطلوبة' => $data->street_view_range,
                    'العروض' => $data->offers()->count(),
                    'حالة العقار' => $data->estate_status,
                ];
            });

        } elseif ($request->get('type') == 'offers') {

            return Excel::store(new FundOfferExport($fromdate, $todate, 'all'), 'invoices.xlsx','real_public');

            return Excel::store(new FundOfferExport($fromdate, $todate, 'all'),
                'fundRequestsOffer_' . $request->get('from_date') . 'TO' . $request->get('to_date') . '.xlsx');

        } elseif ($request->get('type') == 'offers_view') {
            return Excel::download(new FundOfferExport($fromdate, $todate, 'sending_code'),
                'fundRequestsOffer_' . $request->get('from_date') . 'TO' . $request->get('to_date') . '.xlsx');


        } elseif ($request->get('type') == 'offer_complete') {
            return Excel::download(new FundOfferExport($fromdate, $todate, 'accepted_customer'),
                'fundRequestsOffer_' . $request->get('from_date') . 'TO' . $request->get('to_date') . '.xlsx');

        }


        return back();
    }

}
