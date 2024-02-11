<?php

namespace App\Http\Controllers\DashBoard\Bank;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\MsgDetResource;
use App\Http\Resources\MsgResource;
use App\Jobs\OtpJob;
use App\Models\dashboard\Admin;

use App\Models\v2\AreaEstate;
use App\Models\v2\City;
use App\Models\v2\Client;
use App\Models\v2\DeferredInstallment;
use App\Models\v2\Estate;
use App\Models\v2\EstatePrice;
use App\Models\v2\EstateRequest;
use App\Models\v2\EstateType;
use App\Models\v2\Favorite;
use App\Models\v2\Finance;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\Msg;
use App\Models\v2\MsgDet;
use App\Models\v2\Neighborhood;
use App\Models\v2\RequestFund;
use App\Models\v2\UserPayment;
use App\Unifonic\UnifonicMessage;
use App\User;
use App\Helpers\JsonResponse;
use Carbon\Carbon;
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

class SettingController extends Controller
{


    public function index(Request $request)
    {



        $offers = Finance::query();


        if ($request->get('from_date') && $request->get('to_date')) {


            $offers = $offers->whereDate(
                'created_at',
                '>=',
                Carbon::parse($request->get('from_date'))
            );
            $offers = $offers->whereDate(
                'created_at',
                '<=',
                Carbon::parse($request->get('to_date'))
            );
        }


        $offers = $offers->paginate(10);
        $requests = Finance::query()->count();
        $requests_active = Finance::where('status', '1')->count();
        $requests_note_active = Finance::where('status', '0')->count();


        $DeferredInstallment = DeferredInstallment::query()->count();
        $DeferredInstallment_active = DeferredInstallment::where('status', '1')->count();
        $DeferredInstallment_note_active = DeferredInstallment::where('status', '0')->count();



        $settings=[
            'requests'=>$requests,
            'requests_active'=>$requests_active,
            'requests_note_active'=>$requests_note_active,
            'DeferredInstallment_active'=>$DeferredInstallment_active,
            'DeferredInstallment_note_active'=>$DeferredInstallment_note_active,
            'DeferredInstallment'=>$DeferredInstallment,
            'Finance'=>$offers,
        ];
        return response()->success("DashBoard", $settings);



    }

}
