<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\EmpUserResource;
use App\Http\Resources\EstateRequestRateResource;
use App\Http\Resources\EstateSelectResource;
use App\Http\Resources\FinancialBondResource;
use App\Http\Resources\SingleCatchBondResource;
use App\Http\Resources\UserResource;

use App\Models\v3\Bank;
use App\Models\v3\Comfort;
use App\Models\v3\Employee;
use App\Models\v3\EstateDeposit;
use App\Models\v3\EstateExpense;
use App\Models\v3\EstateOwnerFinanceMovment;
use App\Models\v3\EstateType;
use App\Models\v3\FinancialBond;
use App\Models\v3\FinancialMovement;
use App\Models\v3\NotificationUser;
use App\Models\v3\OprationType;
use App\Models\v3\RateRequest;
use App\Models\v3\RentalContractInvoice;
use App\Models\v3\RentalContracts;
use App\Models\v3\RentContractFinancialMovement;
use App\Models\v3\TentPayUser;
use App\Models\v4\FcmToken;
use App\Models\v4\RateOfferRequest;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use App\User;
use Auth;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use QL\QueryList;


class RateRequestController extends Controller
{
    public function estate_request_rate(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();
        if ($user->show_rate_request == 0) {
            return response()->error(__('views.not authorized') , null , 403);
        }

        $page_number = $request->page_number ? $request->page_number : 15;
        $rate = RateRequest::query()->where('status', 'pending');
        $rate = $rate->orderBy('id', 'desc')->paginate($page_number);
        return response()->success(__('طلبات تقييم العقارات'), EstateRequestRateResource::collection($rate)->response()->getData(true));

    }

    public function estate_request_rate_show(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();
        if ($user->show_rate_request == 0) {
            return response()->error(__('views.not authorized') , null , 403);
        }

        $rate = RateRequest::query();
        $rate = $rate->where('id', $request->id)->first();
        return response()->success(__('طلب تقييم العقار'), new EstateRequestRateResource($rate));

    }

    public function send_estate_request_rate(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();
        if ($user->show_rate_request == 0) {
            return response()->error(__('views.not authorized') , null , 403);
        }

        $validator = Validator::make($request->all(), [
            'request_rate_id' => 'required|exists:rate_requests,id',
            'day' => 'required|numeric',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }

        $check = RateOfferRequest::where('user_id', $user->id)->where('request_rate_id', $request->request_rate_id)->first();
        if ($check) {
            return response()->error(__('views.already added'));
        }

        $rate = RateRequest::find($request->request_rate_id);
        if ($rate && $rate->status != 'pending') {
            return response()->error(__('views.status not pending'));
        }

        $rate_offer = new RateOfferRequest();
        $rate_offer->user_id = $user->id;
        $rate_offer->request_rate_id = $request->request_rate_id;
        $rate_offer->day = $request->day;
        $rate_offer->price = $request->price;
        $rate_offer->status = 'pending';
        $rate_offer->save();

        $client = User::find($rate->user_id);
        if ($client) {
            $push_data = [
                'title' => 'لديك عرض جديد على طلب تقييم العقار #' . $rate->id,
                'body' => 'لديك عرض جديد على طلب تقييم العقار #' . $rate->id,
                'id' => $rate->id,
                'type' => 'rate_request',
            ];

            $note = NotificationUser::create([
                'user_id' => $client->id,
                'title' => 'لديك عرض جديد على طلب تقييم العقار #' . $rate->id,
                'type' => 'rate_request',
                'type_id' => $rate->id,
            ]);
            $fcm_token = FcmToken::where('user_id', $client->id)->get();
            foreach ($fcm_token as $token) {
                send_push($token->token, $push_data, $token->type);
            }
        }
        return response()->success(__('views.added successfully'));

    }
}
