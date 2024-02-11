<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\CheckPointResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ContractInvoiceSelectResource;
use App\Http\Resources\ContractSelectResource;
use App\Http\Resources\EmpUserResource;
use App\Http\Resources\EstateExprensesResource;
use App\Http\Resources\EstateExprensesSelectResource;
use App\Http\Resources\EstateSelectResource;
use App\Http\Resources\EstateSelectSettingResource;
use App\Http\Resources\GroupSelectResource;
use App\Http\Resources\UserResource;

use App\Models\v3\Bank;
use App\Models\v3\Comfort;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\EstateExpense;
use App\Models\v3\EstateType;
use App\Models\v3\FinancialBond;
use App\Models\v3\GroupEstate;
use App\Models\v3\NotificationUser;
use App\Models\v3\OprationType;
use App\Models\v3\RentalContractInvoice;
use App\Models\v3\RentalContracts;
use App\Models\v3\TentPayUser;
use App\Models\v4\FcmToken;
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


class SettingController extends Controller
{


    public function OprationType()
    {

        $OprationType = OprationType::get();
        return response()->success("OprationType List", $OprationType);
    }


    public function EstateType()
    {


        $user = Auth::user();

        //  $value = Cache::get('cities');
        $value = EstateType::get();
        //dd($value->first());
        if ($user != null) {
            $type_ids = explode(',', $user->saved_filter_type);
            $type_fund_ids = explode(',', $user->saved_filter_fund_type);


            foreach ($value as $valueItem) {
                if (in_array($valueItem->id, $type_ids)) {

                    $valueItem->is_selected = 1;


                } else {
                    $valueItem->is_selected = 0;
                }
                if (in_array($valueItem->id, $type_fund_ids)) {


                    $valueItem->is_fund_selected = 1;


                } else {
                    $valueItem->is_fund_selected = 0;
                }


            }
        }


        return response()->success("EstateType List", $value);
    }


    public function banks()
    {


        $Bank = Bank::get();
        return response()->success("Bank List", $Bank);
    }

    public function comfort()
    {

        $Comfort = Comfort::get();
        return response()->success("Comfort", $Comfort);
    }


    public function empsSelect(Request $request)
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
//        $client = Employee::where('user_id', $user->id)->pluck('emp_mobile');
        $client = Employee::where('user_id', $user->id)->whereHas('user_information', function ($q) {
            $q->where('is_employee', '2');
        });

//        $allClien = User::whereIn('mobile', $client);

        if ($request->get('emp_name')) {
            $search = trim($request->get('emp_name'));
            $client = $client->Where('name', 'like', '%' . $search . '%');
        }

        $client = $client->get()->toArray();
        if ($client) {

            return response()->success("Clients", $client);
        } else {
            return response()->success(__('views.No Data'), []);
        }


    }


    public function clientSelect(Request $request)
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

        $clients = TentPayUser::where('user_id', $user->id);
        if ($request->get('search')) {
            $search = trim($request->get('search'));
            $clients = $clients
                ->Where('name', 'like', '%' . $search . '%')
                ->orWhere('mobile', 'like', '%' . $search . '%')
                ->orWhere('identification', 'like', '%' . $search . '%');

        }

        if ($request->get('type')) {

            $clients = $clients
                ->Where('customer_character', $request->get('type'));


        }
        $clients = $clients->get();


        $clients = ClientResource::collection($clients);
        if ($clients) {

            return response()->success("Clients", $clients);
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }


    }


    public function estateSelect(Request $request)
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


        $estate = $user->estate();
        if ($request->get('type')) {
            if ($request->get('type') == 'rent') {
                $estate = $estate->where('operation_type_id', 2);
            } else {
                $estate = $estate->where('operation_type_id', '!=', 2);
            }
        }


        $estate = $estate->orderBy('id', 'desc')->get();


        $estate = EstateSelectResource::collection($estate);

        if ($estate) {

            return response()->success("Estates", $estate);
        } else {
            return JsonResponse::fail(__('views.No Data'), 200);
        }


    }


    public function contractNotPaidExprensessSelect(Request $request)
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

            'rental_contract_id' => 'required|exists:rental_contracts,id',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $RentalContracts = RentalContracts::find($request->get('rental_contract_id'));

        if (!$RentalContracts) {
            return response()->success(__("views.not_found"), []);

            // return response()->error(__('views.not found'));
        }

        $experansess = EstateExpense::where('rent_contract_id', $RentalContracts->id);
        if ($request->get('type')) {
            $experansess = $experansess->where('status', $request->get('type'));
        }

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $experansess = $experansess->paginate($size);


        $experansess = EstateExprensesSelectResource::collection($experansess)->response()->getData(true);

        if ($experansess) {

            return response()->success("experansess", $experansess);
        } else {
            return response()->success(__('views.No Data'), []);
        }


    }

    public function estateNotPaidExprensessSelect(Request $request)
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

            'estate_id' => 'required|exists:estates,id',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $experansess = EstateExpense::where('rent_contract_id', null)
            ->where('estate_id', $request->get('estate_id'));


        if ($request->get('type')) {
            $experansess = $experansess->where('status', $request->get('type'));
        }
        $experansess = $experansess->get();

        $experansess = EstateExprensesSelectResource::collection($experansess);

        if ($experansess) {

            return response()->success("experansess", $experansess);
        } else {
            return response()->success(__('views.No Data'), []);
        }


    }

    public function groupSelect(Request $request)
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

            // 'estate_id' => 'required|exists:estates,id',


        ]);
        $group = $user->group_estate();

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $estate->whereRaw('  city_id   IN ("' . $estate . '") ');

            //  $estate = $estate->whereIn('city_id', $estate);
        }

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


            if (in_array($request->get('estate_type_id'), $typeEstateArray)) {

                // $estate = $estate->whereIn('estate_type_id', $typeEstateArray);
                $array = array_map('intval', $typeEstateArray);
                // $array = implode(",", $array);
                //   $query .= ' and city_id IN ' . $array;
                $array = join(",", $array);
                //  $array = '(' . $array . ')';


                $group = $group->whereRaw('  estate_type_id   IN (2,4) ');
                //  dd($estate->toSql());

            } else {
                $group = $group->whereRaw('estate_type_id =' . $request->get('estate_type_id'));
            }


        }
        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && Estate::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $group = $group->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                // $group = $group->whereRaw(' interface  like % ' . $request->get('search') . ' % ');
                // $group = $group->whereRaw('  rent_type like % ' . $request->get('search') . ' % ');

                $group = $group->Where('group_name', 'like', '%' . $request->get('search') . '%');
                $group = $group->orWhere('city_name', 'like', '%' . $request->get('search') . '%');
                $group = $group->orWhere('state_name', 'like', '%' . $request->get('search') . '%');
                $group = $group->orWhere('neighborhood_name', 'like', '%' . $request->get('search') . '%');
                $group = $group->orWhere('interface', 'like', '%' . $request->get('search') . '%');

            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $group = $group->whereRaw('  id =' . $request->get('search'));

            }


        }


        $group = $group->orderByRaw(DB::Raw(' `estate_groups`.`id` desc '));


        $group = $group->get();
        $group = GroupSelectResource::collection($group);

        if ($group) {

            return response()->success("groups", $group);
        } else {
            return response()->success(__('views.No Data'), []);
        }


    }


    public function contractNotPaidInvoiceSelect(Request $request)
    {

        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }

        $user = User::find($user->id);
        $rules = Validator::make($request->all(), [
            'rental_contract_id' => 'required|exists:rental_contracts,id',
        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $RentalContracts = null;
        if ($request->get('rental_contract_id')) {
            $RentalContracts = RentalContracts::find($request->get('rental_contract_id'));
        }
        if ($request->get('client_id')) {
            $invoices = RentalContractInvoice::where('client_id', $request->get('client_id'))->get();

            if ($request->get('type')) {
                $invoices = $invoices->where('status', $request->get('type'));
            }
            $invoices = ContractInvoiceSelectResource::collection($invoices);

            if ($invoices) {

                return response()->success("invoices", $invoices);
            } else {
                return response()->success(__('views.No Data'), []);
            }
        }


        if (!$RentalContracts) {
            return response()->success(__("views.not_found"), []);

            //return response()->error(__('views.not found'));
        }
        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $invoices = RentalContractInvoice::where('rental_contracts_id', $RentalContracts->id)->paginate($size);

        if ($request->get('type')) {
            $invoices = $invoices->where('status', $request->get('type'));
        }

        $invoices = ContractInvoiceSelectResource::collection($invoices)->response()->getData(true);

        if ($invoices) {

            return response()->success("invoices", $invoices);
        } else {
            return response()->success(__('views.No Data'), []);
        }


    }

    public function contractSelect(Request $request)
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

            //    'rental_contract_id' => 'required|exists:rental_contracts,id',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);
        $RentalContracts = RentalContracts::query()->where('user_id', $user->id);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        if ($request->get('rental_contract_id')) {
            $RentalContracts = $RentalContracts->where('id', $request->get('rental_contract_id'));
        }


        $RentalContracts = $RentalContracts->orderBy('id', 'desc')->get();

        $RentalContracts = ContractSelectResource::collection($RentalContracts);
        return response()->success("RentalContracts", $RentalContracts);


    }

    public function dashboard(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }
        try {
            $user = $request->user();

            $user = User::where('id', $user->id)
                ->first();
            $estate_count = $user->estate->count();
            $group_count = $user->group_estate->count();
            $TentPayUser_count = $user->TentPayUser->count();
            $TentContract_count = $user->TentContract->where('status', '!=' , 'pending')->count();
            $PayContract_count = $user->PayContract->count();
            $emp_count = Employee::where('user_id', $user->id)->count();
            $Catch_FinancialBond_count = FinancialBond::where('user_id', $user->id)->where('type', 'Catch')->count();
            $receipt_FinancialBond_count = FinancialBond::where('user_id', $user->id)->where('type', 'receipt')->count();
            $new_estate = Estate::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get();
            $new_estate_group = GroupEstate::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get();
            $new_estate_group = GroupSelectResource::collection($new_estate_group);
            $new_estate = EstateSelectSettingResource::collection($new_estate);
            $dashBoard = [
                'estate_count' => $estate_count,
                'group_count' => $group_count,
                'TentPayUser_count' => $TentPayUser_count,
                'TentContract_count' => $TentContract_count,
                'PayContract_count' => $PayContract_count,
                'emp_count' => $emp_count,
                'new_estate' => $new_estate,
                'new_estate_group' => $new_estate_group,
                'Catch_FinancialBond_count' => $Catch_FinancialBond_count,
                'receipt_FinancialBond_count' => $receipt_FinancialBond_count,


            ];
            return response()->success(__('views.Dashboard'), $dashBoard);
        } catch (\Exception $e) {
            return response()->error($e->getMessage());
            // return JsonResponse::fail($e->getMessage(), 400);
        }
    }

    public function checkEmployee(Request $request)
    {

        $user = $request->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [
            'mobile' => 'required',
            'country_code' => 'sometimes|required',
            'is_emp' => 'sometimes|required'

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


        $checkIsCompny = User::where('mobile', $mobile)
            ->where('is_employee', 0)
            ->first();

        if ($checkIsCompny) {
            return response()->error(__("رقم الجوال غير مضاف كشركة"));
        }


        $checkIsCompny2 = User::where('mobile', $mobile)
            ->whereIn('is_employee', [1, 2])
            ->whereNotIn('employer_id', [null, $user->id])
            ->first();

        if ($checkIsCompny2) {
            return response()->error(__("الرقم مضاف لشركة سابقا"));
        }

        $checkIfEmp = Employee::where('emp_mobile', $mobile)
            ->first();

        if ($checkIfEmp && $request->get('is_emp') == 'yes') {
            $push_data = [
                'title' => __('views.You Have New Employee #') . $checkIfEmp->id,
                'body' => __('views.You Have New Employee #') . $checkIfEmp->id,
                'id' => $checkIfEmp->id,
                'user_id' => $checkIfEmp->user_id,
                'type' => 'employee',
            ];

            $note = NotificationUser::create([
                'user_id' => $checkIfEmp->user_id,
                'title' => 'لديك موظف جديد برقم :' . $checkIfEmp->id,
                'type' => 'employee',
                'type_id' => $checkIfEmp->id,
            ]);
            $client = User::where('id', $checkIfEmp->user_id)->first();
            if ($client) {
                $fcm_token = FcmToken::where('user_id', $client->id)->get();
                foreach ($fcm_token as $token) {
                    send_push($token->token, $push_data, $token->type);
                }
            }

            $user->is_employee = '2';
            $user->save();
            //    $client->count_emp=$client->count_emp+1;
            //  $client->save();

            return response()->success(__("views.Done"), $user);

        }
        elseif ($checkIfEmp && $request->get('is_emp') == 'no') {
            $user->is_employee = '0';
            $user->employer_id = null;
            $user->save();
            return response()->success(__("views.Done"), $user);
        }
        elseif (!$checkIfEmp) {
            $user->is_employee = '0';
            $user->employer_id = null;
            $user->save();
            return response()->success(__("views.Done"), $user);
        }
        else {
            return response()->error(__('انت لست موظف ضمن المكاتب'));
        }


    }

    public function get_age()
    {
        $age = collect();
        $age->push(__("views.new_age"));
        for ($i = 1; $i <= 35; $i++) {
            $age->push($i .' '. __("views.year"));
        }
        $age->push('+35 '. __("views.year"));

        return response()->success("Age", $age);
    }

    public function checkPint(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'lat' => 'sometimes|required',
            'lan' => 'sometimes|required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $location = $request->get('lat') . ' ' . $request->get('lan');
        $dis = checkPint("$location");
        $dis = \App\Models\v4\District::where('district_id', $dis)->first();
        if (!$dis) {
            return response()->error(__("views.not found"));
        }
        $dis = CheckPointResource::collection([$dis]);
        return response()->success(__("views.Done"), $dis[0]);
    }

}
