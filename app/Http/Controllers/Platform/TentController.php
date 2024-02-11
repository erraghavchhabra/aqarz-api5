<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;


use App\Http\Resources\PayContractNoteResource;
use App\Http\Resources\PayContractsResource;
use App\Http\Resources\RentContractNoteResource;
use App\Http\Resources\RentContractResource;
use App\Http\Resources\RentContractSingleResource;
use App\Models\v3\Estate;
use App\Models\v3\EstateExpense;
use App\Models\v3\FinancialMovement;
use App\Models\v3\InvoiceBankTransfer;
use App\Models\v3\PayContractNote;
use App\Models\v3\PayContracts;
use App\Models\v3\RentalContractInvoice;
use App\Models\v3\RentalContracts;
use App\Models\v3\RentContractFinancialMovement;
use App\Models\v3\RentContractNote;
use App\Models\v3\TentPayUser;
use App\Models\v4\EjarEstateData;
use App\User;
use Auth;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use QL\QueryList;


class TentController extends Controller
{
//
    public function addTentContract(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();

        $rules = Validator::make($request->all(), [
            'tent_id' => 'required|exists:tent_pay_users,id',
            'estate_id' => 'required|exists:estates,id',
            'count_month' => 'required',
            'payment_value' => 'required',
            'rent_total_amount' => 'required',
            'date_of_writing_the_contract' => 'required',
            'additional_contract_terms' => 'sometimes|required',
            'payment_type' => 'required',
            'annual_increase' => 'sometimes|required',
            'refundable_insurance' => 'sometimes|required',
            'rental_commission' => 'sometimes|required',
            'maintenance_and_services' => 'sometimes|required',
            'electricity' => 'sometimes|required',
            'waters' => 'sometimes|required',
            'cleanliness' => 'sometimes|required',
            'property_management' => 'sometimes|required',
            'services' => 'sometimes|required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        if ($request->get('estate_id')) {
            $estate = Estate::find($request->get('estate_id'));
            $code = Str::random(5);
            $request->merge([
                'estate_group_id' => $estate->group_estate_id,
//                'contract_number' => uniqid()
                'contract_number' => $this->uniqe_code()
            ]);
        }

        $user = User::find($user->id);
        $request->merge([
            'user_id' => $user->id,
        ]);

        $RentalContracts = RentalContracts::create($request->only([
            'tent_id',
            'user_id',
            'estate_id',
            'start_date',
            'payment_value',
            'count_month',
            'end_date',
            'rent_total_amount',
            'contract_number',
            'date_of_writing_the_contract',
            'additional_contract_terms',
            'payment_type',
            'annual_increase',
            'refundable_insurance',
            'rental_commission',
            'maintenance_and_services',
            'electricity',
            'waters',
            'cleanliness',
            'property_management',
            'services',
            'estate_group_id'
        ]));


        $RentalContracts = RentalContracts::find($RentalContracts->id);

        $now = strtotime($request->get('start_date'));
        $your_date = strtotime($request->get('end_date'));
        $datediff = $your_date - $now;

        $diff = round($datediff / (60 * 60 * 24));
        $RentalContracts->contract_interval = $diff;
        $RentalContracts->save();
        $RentalContracts = RentalContracts::find($RentalContracts->id);


        for ($i = 0; $i < $request->get('count_month'); $i++) {

            $collective_date = date('Y-m-d', strtotime($request->get('start_date') . ' + ' . ($i + 1) . ' months'));
            $period_from = date('Y-m-d', strtotime($request->get('start_date') . ' + 5 days'));
            $period_to = date('Y-m-d', strtotime($request->get('start_date') . ' + 20 days'));
            $request->merge([
                'bond_type' => 'rental_commission',
                'client_id' => $request->get('tent_id'),
                'collection_date' => $collective_date,
                'period_from' => $period_from,
                'period_to' => $period_to,
                'rental_contracts_id' => $RentalContracts->id,
                'status' => 'not_paid',
                'paid_amount' => 0,
                'owed_amount' => $request->get('payment_value'),
            ]);

            $RentalContractInvoice = RentalContractInvoice::create($request->only([
                'bond_type',
                'collection_date',
                'period_from',
                'period_to',
                'collector_name',
                'statement',
                'rental_contracts_id',
                'status',
                'user_id',
                'paid_amount',
                'owed_amount',
            ]));
        }
        $request->merge([
            'user_id' => $user->id,
            'customer_id' => $request->get('tent_id'),
            'statment' => 'انشاء عقد ايجار جديد',
            'owed_money' => $request->get('rent_total_amount'),
            'paid_money' => 0,
            'total_money' => $request->get('rent_total_amount'),
        ]);

        $FinancialMovement = FinancialMovement::create($request->only([
            'estate_id',
            'user_id',
            'customer_id',
            'statment',
            'owed_money',
            'paid_money',
            'total_money',
        ]));

        return response()->success(__("views.RentalContracts"), []);
    }

    public function uniqe_code()
    {
        $code = rand(10000, 99999);
        $RentalContracts = RentalContracts::where('contract_number', $code)->first();
        if ($RentalContracts) {
            $this->uniqe_code();
        } else {
            return $code;
        }
    }

    public function updateTentContract(Request $request, $id)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'tent_id' => 'sometimes|required|exists:tent_pay_users,id',
            'estate_id' => 'sometimes|required|exists:estates,id',
            'contract_number' => 'sometimes|required',
            'date_of_writing_the_contract' => 'sometimes|required',
            'additional_contract_terms' => 'sometimes|required',
            'payment_type' => 'sometimes|required',
            'annual_increase' => 'sometimes|required',
            'refundable_insurance' => 'sometimes|required',
            'rental_commission' => 'sometimes|required',
            'maintenance_and_services' => 'sometimes|required',
            'electricity' => 'sometimes|required',
            'waters' => 'sometimes|required',
            'cleanliness' => 'sometimes|required',
            'property_management' => 'sometimes|required',
            'services' => 'sometimes|required',
            'start_date' => 'sometimes|required',
            'end_date' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);

        if ($request->get('estate_id')) {
            $estate = Estate::find($request->get('estate_id'));
            $request->merge([

                'estate_group_id' => $estate->group_estate_id,


            ]);
        }

        $request->merge([

            'user_id' => $user->id,


        ]);
        $RentalContracts = RentalContracts::find($id);

        if (!$RentalContracts) {
            return response()->success(__("views.not_found"), []);

            // return response()->error(__('views.not found'));
        }


        $RentalContracts = $RentalContracts->update($request->only([
            'tent_id',
            'user_id',
            'estate_id',
            'estate_group_id',
            'contract_number',
            'date_of_writing_the_contract',
            'additional_contract_terms',
            'payment_type',
            'annual_increase',
            'refundable_insurance',
            'rental_commission',
            'maintenance_and_services',
            'electricity',
            'waters',
            'cleanliness',
            'property_management',
            'services',
            'start_date',
            'end_date',
            'estate_group_id'


        ]));


        $RentalContracts = RentalContracts::find($id);

        if ($request->get('start_date') && $request->get('end_date')) {
            $now = strtotime($request->get('start_date'));
            $your_date = strtotime($request->get('end_date'));
            $datediff = $your_date - $now;

            $diff = round($datediff / (60 * 60 * 24));
            $RentalContracts->contract_interval = $diff;
            $RentalContracts->save();
            $RentalContracts = RentalContracts::find($RentalContracts->id);
        }


        return response()->success(__("views.RentalContracts"), $RentalContracts);
        // return ['data' => $user];
    }

    public function DeletePayerContract($id , Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();

        $user = User::find($user->id);


        $RentalContracts = PayContracts::find($id);

        if (!$RentalContracts) {
            return response()->success(__("views.not_found"), []);
        }

        $RentalContracts->delete();

        return response()->success(__("views.RentalContracts"), null);
    }


    public function allTentContract(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));

        }

        $user = $request->user();
        $RentalContracts = RentalContracts::with('estate', 'tent')->where('user_id', $user->id)->where('status', '!=' , 'pending');
        foreach ($RentalContracts->get() as $RentalContract) {
            if ($RentalContract->end_date < date('Y-m-d')) {
                $RentalContract->status = 'not_active';
                $RentalContract->save();
            }
        }
        if ($request->get('search') && $request->get('search') != null) {

            $RentalContracts = $RentalContracts->where(function ($q) use ($request) {
                $q->where('id', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('contract_number', 'like', '%' . $request->get('search') . '%');
                });
//            if ((filter_var($request->get('search'),
//                        FILTER_VALIDATE_INT) !== false) && RentalContracts::find($request->get('search'))) {
//                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
//                //   $query .= ' and id   = ' . $request->get('search');
//
//
//            } elseif ((filter_var($request->get('search'),
//                    FILTER_VALIDATE_INT) == false)) {
//                $RentalContracts = $RentalContracts
//                    ->orwhere('payment_type', 'like', '%' . $request->get('search') . '%');
//
//
//            }
//            if ((filter_var($request->get('search'),
//                    FILTER_VALIDATE_INT) !== false)) {
//                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
//                //   $query .= ' and id   = ' . $request->get('search');
//                $RentalContracts = $RentalContracts->whereRaw('  id =' . $request->get('search'));
//
//            }


        }
        if ($request->get('estate_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $RentalContracts = $RentalContracts->whereRaw('  estate_id =' . $request->get('estate_id'));

        }
        $RentalContracts = $RentalContracts->orderByRaw(DB::Raw(' `rental_contracts`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }


        $RentalContracts = $RentalContracts->paginate($size);
        $RentalContracts = RentContractResource::collection($RentalContracts)->response()->getData(true);;
        if ($RentalContracts) {
            return response()->success(__("views.RentalContracts"), $RentalContracts);
        } else {
            return response()->success(__("views.not_found"), []);

            //    return response()->error(__("views.not found"), []);
        }
    }

    public function SingleTentContract(Request $request, $id)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));*

        }

        $user = $request->user();
        $RentalContracts = RentalContracts::with('estate', 'tent', 'rent_contract_financial_movements', 'estate_expenses', 'rent_invoices')->find($id);

        // return($RentalContracts);

        $RentalContracts = RentContractSingleResource::collection([$RentalContracts]);
        if ($RentalContracts) {
            return response()->success(__("views.RentalContracts"), $RentalContracts[0]);
        } else {
            return response()->success(__("views.not_found"), []);

            // return response()->error(__("views.not found"), []);
        }
    }

    public function addTentContractInvoice(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'rental_contracts_id' => 'required|exists:rental_contracts,id',
            'client_id' => 'required|exists:tent_pay_users,id',
            'bond_type' => 'required',
            'collection_date' => 'required',
            'period_from' => 'required',
            'period_to' => 'required',
            'owed_amount' => 'required',
            'paid_amount' => 'required',
            'collector_name' => 'required',
            'statement' => 'required',
            'end_date' => 'after:start_date',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $user = User::find($user->id);


        $request->merge([

            'user_id' => $user->id,
            'client_id' => $request->get('client_id'),


        ]);

        $RentalContractInvoice = RentalContractInvoice::create($request->only([
            'bond_type',
            'collection_date',
            'period_from',
            'period_to',
            'collector_name',
            'statement',
            'rental_contracts_id',
            'status',
            'user_id',
            'paid_amount',
            'owed_amount',
            'client_id',


        ]));

        if ($RentalContractInvoice) {


            $remaining_amount = $request->get('owed_amount') - $request->get('paid_amount');
            $status = '';
            if ($remaining_amount > 0) {
                $status = 'paid_with_remaining';
                //'paid','not_paid','paid_with_remaining'
            } elseif ($remaining_amount == 0) {
                $status = 'paid';
            }
            $request->merge([

                'user_id' => $user->id,
                'status' => $status,
                'remaining_amount' => $remaining_amount,
                'rental_contract_invoice_id' => $RentalContractInvoice->id,


            ]);
            $RentContractFinancialMovement = RentContractFinancialMovement::create($request->only([
                'rental_contracts_id',
                'owed_amount',
                'paid_amount',
                'remaining_amount',
                'rental_contract_invoice_id',
                'statement',
                'status',
                'user_id',
                'remaining_amount',

            ]));
        }


        $RentalContractInvoice = RentalContractInvoice::find($RentalContractInvoice->id);
        $remaining_amount = $request->get('owed_amount') - $request->get('paid_amount');

        $request->merge([

            'user_id' => $user->id,
            'customer_id' => $request->get('client_id'),
            'statment' => 'فاتورة جديد للعقد',
            'owed_money' => $request->get('owed_amount'),
            'paid_money' => $request->get('paid_amount'),
            'total_money' => $remaining_amount,


        ]);

        $FinancialMovement = FinancialMovement::create($request->only([
            'estate_id',
            'user_id',
            'customer_id',
            'statment',
            'owed_money',
            'paid_money',
            'total_money',


        ]));


        return response()->success(__("views.RentalContractInvoice"), $RentalContractInvoice);
        // return ['data' => $user];
    }

    public function CollectContractInvoice(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [
//'rental_commission','maintenance_and_services','electricity_expenses','water_expenses','rent_payment','value_added_tax','property_management_expenses','services'
            'rental_contracts_id' => 'required|exists:rental_contracts,id',
            'bond_type' => 'required',
            'collection_date' => 'required',
            'paid_amount' => 'required',
            'payment_type' => 'required|in:cash,Banktransfer,NetWork',
            'collector_name' => 'required',
            'statement' => 'required',
            'expenses_id' => 'sometimes|required_if:bond_type,!=,rental_commission|exists:estate_expenses,id',
            'invoice_id' => 'sometimes|required_if:bond_type,=,rental_commission|exists:rent_invoices,id',
            'bank_converter_from_id' => 'sometimes|required_if:payment_type,=,Banktransfer|exists:banks,id',
            'bank_converter_to_id' => 'sometimes|required_if:payment_type,=,Banktransfer|exists:banks,id',
            'bank_transfer_number' => 'sometimes|required_if:payment_type,=,Banktransfer',
            'bank_transfer_photo' => 'sometimes|required_if:payment_type,=,Banktransfer',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $user = User::find($user->id);
        $rental_contracts_id = '';
        if ($request->get('rental_contracts_id')) {
            $rental_contracts_id = RentalContracts::find($request->get('rental_contracts_id'));
            if (!$rental_contracts_id) {
                return response()->success(__('views.not_found_rent_contract'), []);
            }
        }


        if ($request->get('bond_type') == 'rental_commission') {
            $RentalContractInvoice = RentalContractInvoice::where('id', $request->get('invoice_id'))
                ->where('status', 'not_paid')->first();

            if (!$RentalContractInvoice) {
                return response()->success(__("views.not_found_invoice"), []);
                // return response()->error(__('views.not_found_invoice'));
            }
            $RentalContractInvoice->collection_date = $request->get('collection_date');
            $RentalContractInvoice->paid_amount = $RentalContractInvoice->paid_amount + $request->get('paid_amount');
            $RentalContractInvoice->owed_amount = $RentalContractInvoice->owed_amount - $request->get('paid_amount');
            $RentalContractInvoice->status = $RentalContractInvoice->owed_amount - $request->get('paid_amount') == 0 ? 'paid_with_rest' : 'paid';
            $RentalContractInvoice->statement = $request->get('statement');
            $RentalContractInvoice->payment_type = $request->get('payment_type');
            $RentalContractInvoice->save();
            $request->merge([

                'user_id' => $user->id,
                'client_id' => $rental_contracts_id->tent_id,
                'tent_id' => $request->get('client_id'),


            ]);

            if ($rental_contracts_id) {
                $request->merge([


                    'client_id' => $rental_contracts_id->tent_id,
                    'tent_id' => $rental_contracts_id->tent_id,


                ]);
            }


            if ($RentalContractInvoice) {


                $remaining_amount = $request->get('owed_amount') - $request->get('paid_amount');
                $status = '';
                if ($remaining_amount > 0) {
                    $status = 'paid_with_remaining';
                    //'paid','not_paid','paid_with_remaining'
                } elseif ($remaining_amount == 0) {
                    $status = 'paid';
                }
                $request->merge([

                    'user_id' => $user->id,
                    'status' => $status,
                    'remaining_amount' => $remaining_amount,
                    'rental_contract_invoice_id' => $RentalContractInvoice->id,


                ]);
                $RentContractFinancialMovement = RentContractFinancialMovement::create($request->only([
                    'rental_contracts_id',
                    'owed_amount',
                    'paid_amount',
                    'remaining_amount',
                    'rental_contract_invoice_id',
                    'statement',
                    'status',
                    'user_id',
                    'remaining_amount',

                ]));
            }


            $RentalContractInvoice = RentalContractInvoice::find($RentalContractInvoice->id);
            $remaining_amount = $request->get('owed_amount') - $request->get('paid_amount');

            $request->merge([

                'user_id' => $user->id,
                'customer_id' => $request->get('client_id'),
                'statment' => 'فاتورة جديد للعقد',
                'owed_money' => $request->get('owed_amount'),
                'paid_money' => $request->get('paid_amount'),
                'total_money' => $remaining_amount,


            ]);

            $FinancialMovement = FinancialMovement::create($request->only([
                'estate_id',
                'user_id',
                'customer_id',
                'statment',
                'owed_money',
                'paid_money',
                'total_money',


            ]));

            if ($request->get('payment_type') == 'Banktransfer') {
                $RentalContractInvoice = InvoiceBankTransfer::create($request->only([
                    'tent_id',
                    'expenses_id',
                    'invoice_id',
                    'rent_contracts_id',
                    'user_id',

                    'bank_converter_from_id',
                    'bank_converter_to_id',
                    'bank_transfer_number',
                    'bank_transfer_photo',


                ]));


                if ($request->hasFile('bank_transfer_photo')) {
                    $RentalContractInvoice = InvoiceBankTransfer::find($RentalContractInvoice->id);
                    if (!$RentalContractInvoice) {
                        return response()->success(__("views.not_found_invoice"), []);
                    }
                    $path = $request->file('bank_transfer_photo')->store('images/invoices', 's3');
                    $RentalContractInvoice->bank_transfer_photo = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                    $RentalContractInvoice->save();
                }

            }

        } else {
            $EstateExpense = EstateExpense::where('id', $request->get('expenses_id'))
                ->where('status', 'not_paid')->first();

            if (!$EstateExpense) {
                return response()->success(__("views.not_found_expenses"), []);

                // return response()->error(__('views.not_found_expenses'));
            }
            if ($EstateExpense->price != $request->get('paid_amount')) {
                return response()->success(__("views.must_paid_all_expense_price"), []);

                // return response()->error(__('views.not_found_expenses'));
            }
            $EstateExpense->due_date = $request->get('collection_date');
            $EstateExpense->price = $request->get('paid_amount');
            $EstateExpense->status = 'paid';
            $EstateExpense->statement_note = $request->get('statement');
            $EstateExpense->payment_type = $request->get('payment_type');
            $EstateExpense->save();
            $request->merge([
                'user_id' => $user->id,
            ]);

            if ($rental_contracts_id) {
                $request->merge([


                    'client_id' => $rental_contracts_id->tent_id,
                    'tent_id' => $rental_contracts_id->tent_id,


                ]);
            }


            if ($EstateExpense) {


                $request->merge([

                    'user_id' => $user->id,
                    'status' => 'paid',
                    'remaining_amount' => 0,
                    'estate_expenses_id' => $EstateExpense->id,


                ]);
                $RentContractFinancialMovement = RentContractFinancialMovement::create($request->only([
                    'rental_contracts_id',
                    'owed_amount',
                    'paid_amount',
                    'remaining_amount',
                    'estate_expenses_id',
                    'statement',
                    'status',
                    'user_id',
                    'remaining_amount',

                ]));
            }


            $EstateExpense = EstateExpense::find($EstateExpense->id);

            $request->merge([

                'user_id' => $user->id,
                'customer_id' => $EstateExpense->tent_id,
                'statment' => 'دفع مصروف مستحق',
                'owed_money' => 0,
                'paid_money' => $request->get('paid_amount'),
                // 'rent_contract_id' => $request->get('rental_contracts_id'),
                'total_money' => 0,


            ]);

            $FinancialMovement = FinancialMovement::create($request->only([
                'estate_id',
                'user_id',
                'customer_id',
                'statment',
                'owed_money',
                'paid_money',
                'total_money',


            ]));


            if ($request->hasFile('bank_transfer_photo')) {
                $RentalContractInvoice = InvoiceBankTransfer::create($request->only([
                    'tent_id',
                    'expenses_id',
                    'invoice_id',
                    'rent_contracts_id',
                    'user_id',

                    'bank_converter_from_id',
                    'bank_converter_to_id',
                    'bank_transfer_number',
                    'bank_transfer_photo',


                ]));
                $RentalContractInvoice = InvoiceBankTransfer::find($RentalContractInvoice->id);
                if (!$RentalContractInvoice) {
                    return response()->success(__("views.not_found_invoice"), []);
                }
                $path = $request->file('bank_transfer_photo')->store('images/expenses', 's3');
                $RentalContractInvoice->bank_transfer_photo = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $RentalContractInvoice->save();
            }
        }

        return response()->success(__("views.Done"), []);

        // return ['data' => $user];
    }

    public function addTentContractNotes(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'rental_contracts_id' => 'required|exists:rental_contracts,id',
            'notes' => 'required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);


        $request->merge([

            'user_id' => $user->id,
            'rental_contracts_id' => $request->get('rental_contracts_id'),


        ]);
        $rental_contracts_id = RentalContracts::find($request->get('rental_contracts_id'));

        if (!$rental_contracts_id) {
            return response()->success(__("views.not_found"), []);

            //return response()->error(__("views.not found"), []);
        }
        $RentContractNote = RentContractNote::create($request->only([
            'rental_contracts_id',
            'user_id',
            'notes',


        ]));


        $RentContractNote = RentContractNote::find($RentContractNote->id);


        return response()->success(__("views.RentContractNote"), $RentContractNote);
        // return ['data' => $user];
    }

    public function updateTentContractNotes($id, Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'notes' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $RentContractNote = RentContractNote::find($id);
        if (!$RentContractNote) {
            return response()->success(__("views.not_found"), []);

            //return response()->error(__("views.not found"), []);
        }
        $request->merge([

            'user_id' => $user->id,


        ]);

        $RentContractNote = $RentContractNote->update($request->only([
            'notes',


        ]));


        $RentContractNote = RentContractNote::find($id);


        return response()->success(__("views.RentContractNote"), $RentContractNote);

        // return ['data' => $user];
    }

    public function DeleteTentContractNotes($id, Request $request)
    {

        $RentContractNote = RentContractNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$RentContractNote) {
            return response()->success(__("views.not_found"), []);

            //return response()->error("NOT Found", []);
        }
        $RentContractNote->delete();
        return response()->success(__("views.Done"), []);
    }

    public function SingleTentContractNotes($id, Request $request)
    {

        $RentContractNote = RentContractNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$RentContractNote) {
            return response()->success(__("views.not_found"), []);

            // return response()->error("NOT Found", []);
        }

        return response()->success(__("views.RentContractNote"), $RentContractNote);
    }


    public function allTentContractNotes(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        $typeEstateArray = ['2', '4'];

        $user = User::with('employee')->find($user->id);


        $RentContractNote = RentContractNote::query();


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && RentContractNote::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $RentContractNote = $RentContractNote->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                //  $estate_notes = $estate_notes->where('type', $request->get('search'));
                //    $estate_notes = $estate_notes->orwhere('due_date', $request->get('search'));


            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $RentContractNote = $RentContractNote->whereRaw('  id =' . $request->get('search'));

            }


        }

        if ($request->get('rental_contracts_id')) {
            $RentContractNote = $RentContractNote->whereRaw('  rental_contracts_id =' . $request->get('rental_contracts_id'));
        }


        $RentContractNote = $RentContractNote->orderByRaw(DB::Raw(' `rent_contract_notes`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $RentContractNote = $RentContractNote->paginate($size);

        $RentContractNote = RentContractNoteResource::collection($RentContractNote)->response()->getData(true);;

        if ($RentContractNote) {
            return response()->success(__("views.RentContractNote"), $RentContractNote);
        } else {
            return response()->success(__("views.not_found"), []);

            //  return response()->error(__("views.not found"), []);
        }

    }

    public function addPayerContract(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'payer_id' => 'required|exists:tent_pay_users,id',
            'estate_id' => 'required|exists:estates,id',
            'total_price' => 'required',
            'date_of_writing_the_contract' => 'required',
            'create_by' => 'required',
            'additional_contract_terms' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        if ($request->get('estate_id')) {
            $estate = Estate::find($request->get('estate_id'));


            $request->merge([

                'estate_group_id' => $estate->group_estate_id,


            ]);
        }


        $user = User::find($user->id);


        $request->merge([

            'user_id' => $user->id,
            'contract_number' => $this->uniqe_code()


        ]);

        $PayContracts = PayContracts::create($request->only([
            'user_id',
            'estate_id',
            'estate_group_id',
            'payer_id',
            'status',
            'date_of_writing_the_contract',
            'total_price',
            'additional_contract_terms',
            'create_by',
            'contract_number',


        ]));


        $PayContracts = PayContracts::find($PayContracts->id);

        $PayContracts = PayContracts::find($PayContracts->id);

        return response()->success(__("views.PayContracts"), $PayContracts);
        // return ['data' => $user];
    }


    public function updatePayerContract(Request $request, $id)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'payer_id' => 'sometimes|required|exists:tent_pay_users,id',
            'estate_id' => 'sometimes|required|exists:estates,id',
            'total_price' => 'sometimes|required',
            'date_of_writing_the_contract' => 'sometimes|required',
            'create_by' => 'sometimes|required',
            'additional_contract_terms' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);

        if ($request->get('estate_id')) {
            $estate = Estate::find($request->get('estate_id'));
            $request->merge([

                'estate_group_id' => $estate->group_estate_id,


            ]);
        }

        $request->merge([

            'user_id' => $user->id,


        ]);
        $PayContracts = PayContracts::find($id);

        if (!$PayContracts) {
            return response()->success(__("views.not_found"), []);

            //  return response()->error(__('views.not found'));
        }


        $PayContracts = $PayContracts->update($request->only([
            'user_id',
            'estate_id',
            'estate_group_id',
            'payer_id',
            'status',
            'date_of_writing_the_contract',
            'total_price',
            'additional_contract_terms',
            'create_by',


        ]));


        $PayContracts = PayContracts::find($id);


        return response()->success(__("views.PayContracts"), $PayContracts);
        // return ['data' => $user];
    }


    public function allPayerContract(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        $PayContracts = PayContracts::with('estate', 'payer')->where('user_id', $user->id)->where('deleted_at' , null);

        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && PayContracts::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $PayContracts = $PayContracts->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                $PayContracts = $PayContracts
                    ->where('date_of_writing_the_contract', 'like', '%' . $request->get('search') . '%')
                    ->orwhere('status', 'like', '%' . $request->get('search') . '%');


            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $PayContracts = $PayContracts->whereRaw('  id =' . $request->get('search'));

            }


        }
        if ($request->get('estate_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $PayContracts = $PayContracts->whereRaw('  estate_id =' . $request->get('estate_id'));

        }
        $PayContracts = $PayContracts->orderByRaw(DB::Raw(' `pay_contracts`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $PayContracts = $PayContracts->paginate($size);
        $PayContracts = PayContractsResource::collection($PayContracts)->response()->getData(true);;
        if ($PayContracts) {
            return response()->success(__("views.PayContracts"), $PayContracts);
        } else {
            return response()->success(__("views.not_found"), []);

            //   return response()->error(__("views.not found"), []);
        }
    }

    public function SinglePayerContract(Request $request, $id)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        $PayContracts = PayContracts::with('estate', 'payer')->find($id);

        // return($RentalContracts);

        $PayContracts = PayContractsResource::collection([$PayContracts]);
        if ($PayContracts) {
            return response()->success(__("views.PayContracts"), $PayContracts[0]);
        } else {
            return response()->success(__("views.not_found"), []);

            //  return response()->error(__("views.not found"), []);
        }
    }


    public function addPayContractNotes(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'pay_contracts_id' => 'required|exists:pay_contracts,id',
            'notes' => 'required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);


        $request->merge([

            'user_id' => $user->id,
            'pay_contracts_id' => $request->get('pay_contracts_id'),


        ]);
        $pay_contracts_id = PayContracts::find($request->get('pay_contracts_id'));

        if (!$pay_contracts_id) {
            return response()->success(__("views.not_found"), []);

            // return response()->error(__("views.not found"), []);
        }
        $PayContractNote = PayContractNote::create($request->only([
            'pay_contracts_id',
            'user_id',
            'notes',


        ]));


        $PayContractNote = PayContractNote::find($PayContractNote->id);


        return response()->success(__("views.PayContractNote"), $PayContractNote);
        // return ['data' => $user];
    }

    public function updatePayContractNotes($id, Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'notes' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $PayContractNote = PayContractNote::find($id);
        if (!$PayContractNote) {
            return response()->success(__("views.not_found"), []);

            //return response()->error(__("views.not found"), []);
        }
        $request->merge([

            'user_id' => $user->id,


        ]);

        $PayContractNote = $PayContractNote->update($request->only([
            'notes',


        ]));


        $PayContractNote = PayContractNote::find($id);


        return response()->success(__("views.PayContractNote"), $PayContractNote);

        // return ['data' => $user];
    }

    public function DeletePayContractNotes($id, Request $request)
    {

        $PayContractNote = PayContractNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$PayContractNote) {
            return response()->error("NOT Found", []);
        }
        $PayContractNote->delete();
        return response()->success(__("views.Done"), []);
    }

    public function SinglePayContractNotes($id, Request $request)
    {

        $PayContractNote = PayContractNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$PayContractNote) {
            return response()->error("NOT Found", []);
        }

        return response()->success(__("views.PayContractNote"), $PayContractNote);
    }


    public function allPayContractNotes(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $PayContractNote = PayContractNote::query();


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && PayContractNote::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $PayContractNote = $PayContractNote->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                //  $estate_notes = $estate_notes->where('type', $request->get('search'));
                //    $estate_notes = $estate_notes->orwhere('due_date', $request->get('search'));


            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $PayContractNote = $PayContractNote->whereRaw('  id =' . $request->get('search'));

            }


        }

        if ($request->get('pay_contracts_id')) {
            $PayContractNote = $PayContractNote->whereRaw('  pay_contracts_id =' . $request->get('pay_contracts_id'));

        }


        $PayContractNote = $PayContractNote->orderByRaw(DB::Raw(' `pay_contract_notes`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $PayContractNote = $PayContractNote->paginate($size);

        $PayContractNote = PayContractNoteResource::collection($PayContractNote)->response()->getData(true);;

        if ($PayContractNote) {
            return response()->success(__("views.PayContractNote"), $PayContractNote);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }

    public function entity_endpoints(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'ejar_entity_type' => 'required',
            'ejar_id_number' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_id_type' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_date_of_birth_hijri' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_registration_number' => 'required_if:ejar_entity_type,==,organization_entities',
            'ejar_registration_date' => 'required_if:ejar_entity_type,==,organization_entities',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        return response()->success(__("views.endpoints"), $this->ejar_entity_id($request, 'full_data'));
    }

    public function organization_entities(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'ejar_entity_type' => 'required',
            'ejar_registration_number' => 'required_if:ejar_entity_type,==,organization_entities',
            'ejar_registration_date' => 'required_if:ejar_entity_type,==,organization_entities',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request->merge(['ejar_registration_date' => convert_number_to_english($request->get('ejar_registration_date'))]);
        return response()->success(__("views.endpoints"), $this->ejar_entity_id($request, 'full_data'));
    }


    public function individual_entities(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'ejar_entity_type' => 'required',
            'ejar_id_number' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_id_type' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_date_of_birth_hijri' => 'required_if:ejar_entity_type,==,individual_entities',
            ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $request->merge(['ejar_date_of_birth_hijri' => convert_number_to_english($request->get('ejar_date_of_birth_hijri'))]);

        return response()->success(__("views.endpoints"), $this->ejar_entity_id($request, 'full_data'));
    }
}
