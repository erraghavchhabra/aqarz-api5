<?php

namespace App\Http\Controllers\DashBoard\Fund;


use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteFundResource;
use App\Http\Resources\FavoriteRequestResource;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\MsgDetResource;
use App\Http\Resources\MsgResource;
use App\Jobs\OtpJob;
use App\Models\dashboard\Admin;
use App\Models\v2\AreaEstate;
use App\Models\v2\Client;
use App\Models\v2\Estate;
use App\Models\v2\EstatePrice;
use App\Models\v2\EstateRequest;
use App\Models\v2\Favorite;
use App\Models\v2\FundRequestNeighborhood;
use App\Models\v2\FundRequestOffer;
use App\Models\v2\Msg;
use App\Models\v2\MsgDet;
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

class ProviderController extends Controller
{


    public function index(Request $request)
    {






        //  dd($request->get('query')['neighborhood_id']);

        $user = User::where('type', 'provider');




        if ($request->get('user_status')) {

            if ($request->get('user_status') == 'have_active') {


                $user = $user->where('is_pay','1');
            }

            elseif ($request->get('user_status') == 'waite_active') {
                $user_payment=UserPayment::where('status','0')->pluck('user_id');

                $user = $user->whereIn('id',$user_payment->toArray());

            } elseif ($request->get('user_status') == 'best_providers') {
                $user = $user   ->orderBy('count_offer', 'desc')
                    ->orderBy('count_request', 'desc');

                $user=$user->paginate();
                return response()->success("Best Providers", $user);
            }


        }

        if ($request->get('time')) {
            if ($request->get('time') == 'today') {
                $user = $user->whereDate(
                    'created_at',
                    '=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'tow_today') {
                $user = $user->whereDate(
                    'created_at',
                    '>=',

                    Carbon::yesterday()->format('Y-m-d')
                );
                $user = $user->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }
            if ($request->get('time') == 'week') {
                $user = $user->whereDate(
                    'created_at',
                    '>=',

                    Carbon::now()->subDays(6)->format('Y-m-d')
                );
                $user = $user->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse(date('Y-m-d'))
                );
            }



            /* if ($request->get('time') == 'today') {
                 $offers = $offers->where('');
             }*/

        }



        $user=$user->orderBy('id', 'desc')->paginate();
        return response()->success("Providers", $user);
    }






}
