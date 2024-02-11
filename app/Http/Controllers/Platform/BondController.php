<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\EmpUserResource;
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
use App\Models\v3\OprationType;
use App\Models\v3\RentalContractInvoice;
use App\Models\v3\RentalContracts;
use App\Models\v3\RentContractFinancialMovement;
use App\Models\v3\TentPayUser;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use App\User;
use App\Zatca\EGS;
use Auth;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use QL\QueryList;


class BondController extends Controller
{


    public function addCatchBond(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'estate_id' => 'required_if:for_owner,=,1|exists:estates,id',
            'type' => 'required|in:Catch,receipt',
            'dues_type' => 'required|in:Invoice,Expenses,Owner,Deposit',
            //  'client_id'=>'required|exists:tent_pay_users,id',
            'client_id' => 'required_if:for_owner,null|exists:tent_pay_users,id',
            'publication_date' => 'required',
            'contract_id' => 'sometimes|required|exists:rental_contracts,id',
//            'collector_emp_id' => 'required|exists:employees,id',
            'interval_from_date' => 'required',
            'interval_to_date' => 'required',
            'amount' => 'required',
            'statement' => 'required',
            'experiences_id' => 'required_if:dues_type,=,Expenses|exists:estate_expenses,id',
            'invoice_id' => 'required_if:dues_type,=,Invoice|exists:rent_invoices,id',

            //'for_owner'=>'required',

        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);


        $client = '';
        if ($request->get('client_id')) {
            $client = TentPayUser::find($request->get('client_id'));
            $request->merge([

                'client_name' => $client->name,
                'client_mobile' => $client->mobile,
                'client_identity' => $client->identification,


            ]);
        }
        $request->merge([

            'user_id' => $user->id,


        ]);

        $FinancialBond = FinancialBond::create($request->only([
            'type',
            'client_id',
            'experiences_id',
            'invoice_id',
            'dues_type',
            'user_id',
            'estate_id',
            'client_name',
            'client_mobile',
            'client_identity',
            'publication_date',
            'contract_id',
            'collector_emp_id',
            'interval_from_date',
            'interval_to_date',
            'amount',
            'statement',
            'status',
            'for_owner',
            'payment_type'


        ]));


        $FinancialBond = FinancialBond::find($FinancialBond->id);

        if ($FinancialBond) {


            $request->merge([

                'user_id' => $user->id,
                'financialbond_id' => $FinancialBond->id,
                'owed_amount' => $request->get('amount'),
                'paid_amount' => 0,
                'remaining_amount' => $request->get('amount'),
                'status' => 'paid',
                'rental_contract_invoice_id' => null,


            ]);
            $RentContractFinancialMovement = RentContractFinancialMovement::create($request->only([
                'rental_contracts_id',
                'financialbond_id',
                'client_id',
                'owed_amount',
                'paid_amount',
                'remaining_amount',
                'rental_contract_invoice_id',
                'estate_expenses_id',
                'statement',
                'status',
                'user_id',
                'remaining_amount',

            ]));
            $statment = '';
            $estate_id = null;
            if ($request->get('dues_type') == 'Invoice') {
                $invoice = RentalContractInvoice::find($request->get('invoice_id'));

                if (!$invoice) {
                    return response()->success(__("views.No_Invoice_Found"), []);
                }
                $invoice->status = 'paid';
                $invoice->save();
                $statment = 'سند فاتورة عقد';
                //  $FinancialBond->payment_type=

            }
            if ($request->get('dues_type') == 'Expenses') {
                $invoice = EstateExpense::find($request->get('experiences_id'));
                $invoice->status = 'paid';
                $invoice->save();
                $statment = 'سند مصروف';
                $estate_id = $invoice->estate_id;
            }
            if ($request->get('dues_type') == 'Onwer') {
                $request->merge([

                    'user_id' => $user->id,
                    'estate_id' => $request->get('estate_id'),
                    'status' => 'paid',
                    'rental_contract_invoice_id' => $request->get('type') == 'Catch' ? 'owed' : 'reimbursed',


                ]);
                $RentContractFinancialMovement = EstateOwnerFinanceMovment::create($request->only([
                    'estate_id',
                    'user_id',
                    'amount',
                    'type',
                    'financialbond_id',
                    'status'

                ]));

            }
            if ($request->get('dues_type') == 'Deposit') {
                $request->merge([

                    'user_id' => $user->id,
                    'client_id' => $request->get('client_id'),
                    'estate_id' => $request->get('estate_id'),
                    'status' => 'paid',


                ]);
                $EstateDeposit = EstateDeposit::create($request->only([
                    'estate_id',
                    'user_id',
                    'amount',
                    'type',
                    'financialbond_id',
                    'status'

                ]));
                $estate_id = $request->get('estate_id');
            }

            $contract = RentalContracts::find($request->get('contract_id'));
            if (isset($contract) & $estate_id == null) {
                $estate_id = $contract->estate_id;
            }
            $request->merge([

                'user_id' => $user->id,
                'estate_id' => $estate_id,
                'customer_id' => $request->get('client_id'),
                'statment' => $statment,
                'owed_money' => 0,
                'paid_money' => $request->get('amount'),
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

            if ($request->get('dues_type') == 'Invoice' && $request->get('type') == 'receipt') {
                $amount = $request->get('amount');
                $amount = number_format($amount, 2, '.', '');
                $line_item = [
                    'id' => '1',
                    'name' => 'invoice number ' . $FinancialBond->id,
                    'quantity' => 1,
                    'tax_exclusive_price' => $amount,
                    'VAT_percent' => 0.15,
                    'other_taxes' => [
                    ],
                    'discounts' => [
                    ],
                ];


                $egs_unit = [
                    'uuid' => $this->uuid(),
                    'custom_id' => 'Cus-' . $user->id,
                    'model' => 'IOS',
                    'CRN_number' => $user->crn_number,
                    'VAT_name' => $user->vat_name,
                    'VAT_number' => $user->vat_number,
                    'location' => [
                        'city' => $user->zatca_city,
                        'city_subdivision' => $user->zatca_city_subdivision,
                        'street' => $user->zatca_street,
                        'plot_identification' => '0000', // optional
                        'building' => '0000', // optional
                        'postal_zone' => $user->zatca_postal_zone,
                    ],
                    'branch_name' => $user->name,
                    'branch_industry' => 'estate',
                    'cancelation' => [
                        'cancelation_type' => 'INVOICE',
                        'canceled_invoice_number' => '',
                    ],
                ];

                $invoice = [
                    'invoice_counter_number' => 1,
                    'invoice_serial_number' => $invoice->id,
                    'issue_date' => $request->publication_date,
                    'issue_time' => '00:00:00',
                    'previous_invoice_hash' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==', // AdditionalDocumentReference/PIH
                    'line_items' => [
                        $line_item,
                    ],
                ];


                $egs = new EGS($egs_unit);

                $egs->production = false;

                // New Keys & CSR for the EGS
                list($private_key, $csr) = $egs->generateNewKeysAndCSR('solution_name');

                // Issue a new compliance cert for the EGS
                list($request_id, $binary_security_token, $secret) = $egs->issueComplianceCertificate('123345', $csr);

                // Sign invoice
                list($signed_invoice_string, $invoice_hash, $qr) = $egs->signInvoice($invoice, $egs_unit, $binary_security_token, $private_key);

                // Check invoice compliance
                $egs->checkInvoiceCompliance($signed_invoice_string, $invoice_hash, $binary_security_token, $secret);
            }
        }

        return response()->success(__("views.FinancialBond"), $FinancialBond);
        // return ['data' => $user];
    }

    public function uuid()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function updateCatchBond($id, Request $request)
    {
        $FinancialBond = FinancialBond::find($id);

        if (!$FinancialBond) {
            return response()->success(__("views.not_found"), []);

            //  return response()->error(__('views.not found'));
        }
        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {

            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'estate_id' => 'sometimes|required_if:for_owner,=,1|exists:estates,id',
            'type' => 'sometimes|required|in:Catch,Receipt',
            'dues_type' => 'sometimes|required|in:Invoice,Expenses,Owner,Deposit',
            //  'client_id'=>'required|exists:tent_pay_users,id',
            'client_id' => 'sometimes|required_if:for_owner,null|exists:tent_pay_users,id',
            'publication_date' => 'sometimes|required',
            'contract_id' => 'sometimes|required|exists:rental_contracts,id',
            'collector_emp_id' => 'sometimes|required|exists:users,id',
            'interval_from_date' => 'sometimes|required',
            'interval_to_date' => 'sometimes|required',
            'amount' => 'sometimes|required',
            'statement' => 'sometimes|required',
            'experiences_id' => 'sometimes|required_if:dues_type,=,Expenses|exists:estate_expenses,id',
            'invoice_id' => 'sometimes|required_if:dues_type,=,Invoice|exists:rent_invoices,id',

            //'for_owner'=>'required',

        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $client = '';
        if ($request->get('client_id')) {
            $client = TentPayUser::find($request->get('client_id'));
            $request->merge([

                'client_name' => $client->name,
                'client_mobile' => $client->mobile,
                'client_identity' => $client->identification,


            ]);
        }
        $request->merge([

            'user_id' => $user->id,


        ]);

        $FinancialBond = $FinancialBond->update($request->only([
            'type',
            'client_id',
            'experiences_id',
            'invoice_id',
            'dues_type',
            'user_id',
            'estate_id',
            'client_name',
            'client_mobile',
            'client_identity',
            'publication_date',
            'contract_id',
            'collector_emp_id',
            'interval_from_date',
            'interval_to_date',
            'amount',
            'statement',
            'status',
            'for_owner'


        ]));


        $FinancialBond = FinancialBond::find($id);
        if ($FinancialBond) {

            $movment = RentContractFinancialMovement::where('financialbond_id', $id)->first();
            $request->merge([

                'user_id' => $user->id,
                'owed_amount' => $request->get('amount'),
                'paid_amount' => 0,
                'remaining_amount' => $request->get('amount'),
                'status' => 'paid',
                'rental_contract_invoice_id' => null,


            ]);
            $RentContractFinancialMovement = $movment->update($request->only([
                'rental_contracts_id',
                'financialbond_id',
                'client_id',
                'owed_amount',
                'paid_amount',
                'remaining_amount',
                'rental_contract_invoice_id',
                'estate_expenses_id',
                'statement',
                'status',
                'user_id',
                'remaining_amount',

            ]));

            if ($request->get('dues_type') == 'Invoice') {
                $invoice = RentalContractInvoice::where('financialbond_id', $id)->first();
                $invoice->owed_amount = $FinancialBond->amount;
                $invoice->status = 'paid';
                $invoice->save();
            }
            if ($request->get('dues_type') == 'Expenses') {
                $invoice = EstateExpense::find($request->get('estate_expenses_id'));
                $invoice->status = 'paid';
                $invoice->save();
            }
            if ($request->get('dues_type') == 'Onwer') {
                $request->merge([

                    'user_id' => $user->id,
                    'estate_id' => $request->get('estate_id'),
                    'status' => 'paid',
                    'financialbond_id' => $id,


                ]);
                $RentContractFinancialMovement = EstateOwnerFinanceMovment::where('financialbond_id', $id)->first();
                $RentContractFinancialMovement = $RentContractFinancialMovement->update($request->only([
                    'estate_id',
                    'user_id',
                    'amount',
                    'type',
                    'financialbond_id',
                    'status'

                ]));

            }
            if ($request->get('dues_type') == 'Deposit') {
                $request->merge([

                    'user_id' => $user->id,
                    'client_id' => $request->get('client_id'),
                    'estate_id' => $request->get('estate_id'),
                    'status' => 'paid',


                ]);
                $EstateDeposit = EstateDeposit::where('financialbond_id', $id)->first();

                $EstateDeposit = $EstateDeposit->update($request->only([
                    'estate_id',
                    'user_id',
                    'amount',
                    'type',
                    'financialbond_id',
                    'status'

                ]));

            }
        }

        return response()->success(__("views.FinancialBond"), $FinancialBond);
        // return ['data' => $user];
    }

    public function DeleteCatchBond($id, Request $request)
    {

        $FinancialBond = FinancialBond::where('id', $id)
            ->first();
        //    ->with('owner_movment')
        //  ->with('EstateDeposit')
        //  find($id);


        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$FinancialBond) {
            return response()->success(__("views.not_found"), []);

            //  return response()->error("NOT Found", []);
        }

        $user = $request->user();
        if ($FinancialBond) {

            $FinancialBond->delete();

        }

        return response()->success(__("views.Done"), []);
    }

    public function SingleCatchBond($id, Request $request)
    {

        $FinancialBond = FinancialBond::where('id', $id)
            ->with('owner_movment')
            ->with('EstateDeposit')
            ->with('client_movment')
            ->with('client')
            ->with('estate')
            ->with('user')
            ->with('collector_emp')
            ->first();
        //    ->with('owner_movment')
        //  ->with('EstateDeposit')
        //  find($id);


        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$FinancialBond) {
            return response()->success(__("views.not_found"), []);

            // return response()->error("NOT Found", []);
        }

        $user = $request->user();
           $FinancialBond = new SingleCatchBondResource($FinancialBond);

//            'tent_name' => @$this->tent->name,
//            'customer_character' => 'tent',//'tent','payer'
        //   return response()->success(__("views.Done"), $FinancialBond[0]);
        return response()->success(__("views.Done"), $FinancialBond);
    }


    public function allCatchBond(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        $typeEstateArray = ['2', '4'];

        $user = User::with('employee')->find($user->id);

        $FinancialBond = FinancialBond::where('user_id', $user->id);


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && FinancialBond::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $FinancialBond = $FinancialBond->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                $FinancialBond = $FinancialBond->where('type', $request->get('search'));
                $FinancialBond = $FinancialBond->orwhere('due_date', $request->get('search'));
                $FinancialBond = $FinancialBond->orwhere('client_name', $request->get('search'));
                $FinancialBond = $FinancialBond->orwhere('client_identity', $request->get('search'));
                $FinancialBond = $FinancialBond->orwhere('publication_date', $request->get('search'));


            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $FinancialBond = $FinancialBond->whereRaw('  id =' . $request->get('search'));

            }


        }


        if ($request->get('type')) {
            $FinancialBond = $FinancialBond->where('type', $request->get('type'));

        }

        $FinancialBond = $FinancialBond->orderByRaw(DB::Raw(' `financial_bonds`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $FinancialBond = $FinancialBond->paginate($size);

        $FinancialBond = FinancialBondResource::collection($FinancialBond)->response()->getData(true);

        if ($FinancialBond) {
            return response()->success(__("views.FinancialBond"), $FinancialBond);
        } else {
            return response()->success(__("views.not_found"), []);

            // return response()->error(__("views.not found"), []);
        }

    }
}
