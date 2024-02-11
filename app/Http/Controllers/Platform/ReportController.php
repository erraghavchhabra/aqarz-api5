<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\EstateExprensesResource;
use App\Http\Resources\OwnerFinancialReportResource;
use App\Models\v3\Estate;
use App\Models\v3\EstateExpense;
use App\Models\v3\FinancialBond;
use App\Models\v3\RentalContractInvoice;
use App\Models\v3\RentalContracts;
use App\Reports\MyReport;
use App\User;
use Carbon\Carbon;

use Grids;
use HTML;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Nayjest\Grids\Components\Base\RenderableRegistry;
use Nayjest\Grids\Components\ColumnHeadersRow;
use Nayjest\Grids\Components\ColumnsHider;
use Nayjest\Grids\Components\CsvExport;
use Nayjest\Grids\Components\ExcelExport;
use Nayjest\Grids\Components\Filters\DateRangePicker;
use Nayjest\Grids\Components\FiltersRow;
use Nayjest\Grids\Components\HtmlTag;
use Nayjest\Grids\Components\Laravel5\Pager;
use Nayjest\Grids\Components\OneCellRow;
use Nayjest\Grids\Components\RecordsPerPage;
use Nayjest\Grids\Components\RenderFunc;
use Nayjest\Grids\Components\ShowingRecords;
use Nayjest\Grids\Components\TFoot;
use Nayjest\Grids\Components\THead;
use Nayjest\Grids\Components\TotalsRow;
use Nayjest\Grids\EloquentDataProvider;
use Nayjest\Grids\FieldConfig;
use Nayjest\Grids\FilterConfig;
use Nayjest\Grids\Grid;
use Nayjest\Grids\GridConfig;
use PdfReport;
use ExcelReport;


class ReportController extends Controller
{
    public function __contruct()
    {
        $this->middleware("guest");
    }

    public function Ownermanagement(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'type' => 'required|in:Owner_Financial_Report,Estate_Financial_Report,Tent_Report,Maintenance_Report,Collect_Payments_Report',
            // 'owner_id' => 'sometimes|required_if:type,=,Owner_Financial_Report|exists:users,id',
            'estate_id' => 'sometimes|required_if:type,=,Estate_Financial_Report|exists:estates,id',
            'tent_id' => 'sometimes|required_if:type,=,Tent_Report|exists:tent_pay_users,id',
            'payment_status' => 'sometimes|required_if:type,=,Collect_Payments_Report|exists:rent_invoices,id',
          //  'from_date' => 'required',
            'to_date' => 'after:from_date',
            //'for_owner'=>'required',

        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        if ($request->get('type') == 'Owner_Financial_Report') {
            $Owner_Financial_Report = RentalContracts::
            with('estate')
                ->with('estate_expenses')
                ->with('rent_contract_financial_movements')
//                ->with('rent_invoices')
                ->where('user_id', $user->id);


            if ($request->get('from_date') && $request->get('to_date')) {
                $Owner_Financial_Report = $Owner_Financial_Report->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $Owner_Financial_Report = $Owner_Financial_Report->get();

            $Owner_Financial_Report = OwnerFinancialReportResource::collection($Owner_Financial_Report);
            //->response()->getData(true);;

            return response()->success(__("views.Owner_Financial_Report"), $Owner_Financial_Report);

        }

        if ($request->get('type') == 'Estate_Financial_Report') {


            $Estate_Financial_Report = RentalContracts::
            with('estate')
                ->with('estate_expenses')
                ->with('rent_contract_financial_movements')
                ->where('estate_id', $request->get('estate_id'));


            if ($request->get('from_date') && $request->get('to_date')) {
                $Estate_Financial_Report = $Estate_Financial_Report->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }

            $Estate_Financial_Report = $Estate_Financial_Report->get();

            $Estate_Financial_Report = OwnerFinancialReportResource::collection($Estate_Financial_Report);
            //->response()->getData(true);;

            return response()->success(__("views.Estate_Financial_Report"), $Estate_Financial_Report);

        }
        if ($request->get('type') == 'Tent_Report') {


            $Tent_Report = RentalContracts::
            with('estate')
                ->with('estate_expenses')
                ->with('rent_contract_financial_movements')
                ->where('tent_id', $request->get('tent_id'));

            if ($request->get('from_date') && $request->get('to_date')) {
                $Tent_Report = $Tent_Report->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $Tent_Report = $Tent_Report->get();
            $Tent_Report = OwnerFinancialReportResource::collection($Tent_Report);
            //->response()->getData(true);;

            return response()->success(__("views.Estate_expense"), $Tent_Report);

        }
        if ($request->get('type') == 'Maintenance_Report') {

            $Maintenance_Report = EstateExpense::query();


            if ($request->get('tent_id')) {
                $Maintenance_Report = $Maintenance_Report->where('tent_id', $request->get('tent_id'));
            }
            if ($request->get('estate_id')) {
                $Maintenance_Report = $Maintenance_Report->where('estate_id', $request->get('estate_id'));
            }
            if ($request->get('rent_contract_id')) {
                $Maintenance_Report = $Maintenance_Report->where('rent_contract_id', $request->get('rent_contract_id'));
            }

            if ($request->get('from_date') && $request->get('to_date')) {
                $Maintenance_Report = $Maintenance_Report->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $Maintenance_Report = $Maintenance_Report->get();
            // $user_finan = OwnerFinancialReportResource::collection($user_finan);
            //->response()->getData(true);;

            return response()->success(__("views.Estate_expense"), $Maintenance_Report);

        }
        if ($request->get('type') == 'Collect_Payments_Report') {

            $Collect_Payments_Report = RentalContractInvoice::query();


            if ($request->get('payment_status')) {
                $Collect_Payments_Report = $Collect_Payments_Report->where('status', $request->get('payment_status'));
            }
            if ($request->get('estate_id')) {
                $Collect_Payments_Report = $Collect_Payments_Report->whereHas('rental_contract', function ($q) use ($request) {


                    $q->whereIn('estate_id', $request->get('estate_id'));
                });
            }
            if ($request->get('contract_status')) {
                $Collect_Payments_Report = $Collect_Payments_Report
                    ->whereHas('rental_contract', function ($q) use ($request) {


                        $q->whereIn('status', $request->get('contract_status'));
                    });
            }
            if ($request->get('collector_name')) {
                $Collect_Payments_Report = $Collect_Payments_Report
                    ->Where('rent_price', 'like', '%' . $request->get('collector_name') . '%');
            }
            if ($request->get('tent_id')) {
                $Collect_Payments_Report = $Collect_Payments_Report->where('client_id', $request->get('tent_id'));
            }

            if ($request->get('days_to_pay')) {
                $date = Carbon::now()->format('Y-m-d');
                $Collect_Payments_Report = $Collect_Payments_Report
                    ->where('collection_date', '>', $date)
                    ->where('status', 'not_paid')

                    //  ->where('collection_date','>=',\DB::raw('DATEDIFF(NOW(),collection_date)'))
                    ->whereRaw('DATEDIFF(collection_date,NOW()) = ' . $request->get('days_to_pay'));

            }


            if ($request->get('from_date') && $request->get('to_date')) {
                $Collect_Payments_Report = $Collect_Payments_Report->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $Collect_Payments_Report = $Collect_Payments_Report->get();
            // $user_finan = OwnerFinancialReportResource::collection($user_finan);
            //->response()->getData(true);;

            return response()->success(__("views.Collect_Payments_Report"), $Collect_Payments_Report);

        }
    }


    public function TenantsDues(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        $rules = Validator::make($request->all(), [

            'type' => 'required|in:late_rents,rent_entitlement,payments_due',
            // 'owner_id' => 'sometimes|required_if:type,=,Owner_Financial_Report|exists:users,id',
            // 'estate_id' => 'sometimes|required_if:type,=,Estate_Financial_Report|exists:estates,id',
            // 'tent_id' => 'sometimes|required_if:type,=,Tent_Report|exists:tent_pay_users,id',
            //  'payment_status' => 'sometimes|required_if:type,=,Collect_Payments_Report|exists:rent_invoices,id',
            //   'from_date' => 'required',
            //  'to_date' => 'required',
            //'for_owner'=>'required',

        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        if ($request->get('type') == 'late_rents') {
            $date = Carbon::now()->format('Y-m-d');
            $RentalContractInvoice = RentalContractInvoice::
            where('status', 'not_paid')
                ->where('collection_date', '<', $date);


            if ($request->get('tent_id')) {
                $RentalContractInvoice = $RentalContractInvoice->where('client_id', $request->get('tent_id'));
            }

            if ($request->get('rental_contracts_id')) {

                $RentalContractInvoice = $RentalContractInvoice
                    ->where('rental_contracts_id', $request->get('rental_contracts_id'));


            }

            if ($request->get('from_date') && $request->get('to_date')) {
                $RentalContractInvoice = $RentalContractInvoice->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $RentalContractInvoice = $RentalContractInvoice->get();
            // $user_finan = OwnerFinancialReportResource::collection($user_finan);
            //->response()->getData(true);;

            return response()->success(__("views.RentalContractInvoice"), $RentalContractInvoice);

        }
        if ($request->get('type') == 'rent_entitlement') {
            $date = Carbon::now()->format('Y-m-d');
            $rent_entitlement = RentalContractInvoice::
            where('status', 'not_paid')
                ->where('collection_date', '>', $date);

            if ($request->get('tent_id')) {
                $rent_entitlement = $rent_entitlement->where('client_id', $request->get('tent_id'));
            }

            if ($request->get('rental_contracts_id')) {

                $rent_entitlement = $rent_entitlement
                    ->where('rental_contracts_id', $request->get('rental_contracts_id'));


            }


            if ($request->get('from_date') && $request->get('to_date')) {
                $rent_entitlement = $rent_entitlement->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $rent_entitlement = $rent_entitlement->get();
            // $user_finan = OwnerFinancialReportResource::collection($user_finan);
            //->response()->getData(true);;

            return response()->success(__("views.rent_entitlement"), $rent_entitlement);

        }
        if ($request->get('type') == 'payments_due') {

            $date = Carbon::now()->format('Y-m-d');
            $payments_due = RentalContractInvoice::
            where('status', 'not_paid')
                ->where('collection_date', '>', $date);
            if ($request->get('payment_status')) {
                $payments_due = $payments_due->where('status', $request->get('payment_status'));
            }
            if ($request->get('estate_id')) {
                $payments_due = $payments_due->whereHas('rental_contract', function ($q) use ($request) {


                    $q->whereIn('estate_id', $request->get('estate_id'));
                });
            }
            if ($request->get('contract_status')) {
                $payments_due = $payments_due
                    ->whereHas('rental_contract', function ($q) use ($request) {


                        $q->whereIn('status', $request->get('contract_status'));
                    });
            }

            if ($request->get('tent_id')) {
                $payments_due = $payments_due->where('client_id', $request->get('tent_id'));
            }

            if ($request->get('days_to_pay')) {
                $date = Carbon::now()->format('Y-m-d');
                $payments_due = $payments_due
                    ->where('collection_date', '>', $date)
                    //  ->where('collection_date','>=',\DB::raw('DATEDIFF(NOW(),collection_date)'))
                    ->whereRaw('DATEDIFF(collection_date,NOW()) = ' . $request->get('days_to_pay'));

            }
            if ($request->get('owner_id')) {
                $payments_due = $payments_due
                    //  ->with('rental_contract.estate')
                    ->whereHas('rental_contract.estate', function ($q) use ($request) {


                        $q->where('user_id', $request->get('owner_id'));

                    });

                // dd($payments_due);
            }
            if ($request->get('rental_contracts_id')) {

                $payments_due = $payments_due
                    ->where('rental_contracts_id', $request->get('rental_contracts_id'));


            }


            if ($request->get('from_date') && $request->get('to_date')) {
                $payments_due = $payments_due->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $payments_due = $payments_due->get();
            // $user_finan = OwnerFinancialReportResource::collection($user_finan);
            //->response()->getData(true);;

            return response()->success(__("views.payments_due"), $payments_due);

        }

    }

    public function FinancialBonds(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [

            'type' => 'required|in:Bonds,CatchBonds,ReceiptBonds',


        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $FinancialBond = FinancialBond::query();
        if ($request->get('type') == 'Bonds') {


            if ($request->get('collector_name')) {
                $FinancialBond = $FinancialBond->whereHas('collector_emp', function ($q) use ($request) {


                    $q->Where('name', 'like', '%' . $request->get('collector_name') . '%');
                });

            }
            if ($request->get('owner_id')) {
                $FinancialBond = $FinancialBond
                    //  ->with('rental_contract.estate')
                    ->whereHas('estate', function ($q) use ($request) {


                        $q->where('user_id', $request->get('owner_id'));

                    });

                // dd($payments_due);
            }
            if ($request->get('estate_id')) {
                $FinancialBond = $FinancialBond->where('estate_id', $request->get('estate_id'));

                // dd($payments_due);
            }
            if ($request->get('rental_contracts_id')) {

                $FinancialBond = $FinancialBond
                    ->where('contract_id', $request->get('rental_contracts_id'));


            }

            if ($request->get('from_date') && $request->get('to_date')) {
                $FinancialBond = $FinancialBond->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $FinancialBond = $FinancialBond->get();


            return response()->success(__("views.FinancialBond"), $FinancialBond);

        }
        if ($request->get('type') == 'CatchBonds') {

            $FinancialBond = $FinancialBond->where('type', 'Catch');


            if ($request->get('collector_name')) {
                $FinancialBond = $FinancialBond->whereHas('collector_emp', function ($q) use ($request) {


                    $q->Where('name', 'like', '%' . $request->get('collector_name') . '%');
                });

            }
            if ($request->get('owner_id')) {
                $FinancialBond = $FinancialBond
                    //  ->with('rental_contract.estate')
                    ->whereHas('contract.estate', function ($q) use ($request) {


                        $q->where('user_id', $request->get('owner_id'));

                    });

                // dd($payments_due);
            }
            if ($request->get('estate_id')) {
                $FinancialBond = $FinancialBond->where('estate_id', $request->get('estate_id'));

                // dd($payments_due);
            }
            if ($request->get('rental_contracts_id')) {

                $FinancialBond = $FinancialBond
                    ->where('contract_id', $request->get('rental_contracts_id'));


            }
            if ($request->get('tent_id')) {
                $FinancialBond = $FinancialBond->where('client_id', $request->get('tent_id'));
            }
            if ($request->get('payment_type')) {
                $FinancialBond = $FinancialBond->whereHas('invoice', function ($q) use ($request) {


                    $q->where('payment_type', $request->get('payment_type'));

                });
            }
            if ($request->get('from_date') && $request->get('to_date')) {
                $FinancialBond = $FinancialBond->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $FinancialBond = $FinancialBond->get();


            return response()->success(__("views.FinancialBond"), $FinancialBond);

        }
        if ($request->get('type') == 'ReceiptBonds') {

            $FinancialBond = $FinancialBond->where('type', 'receipt');


            if ($request->get('collector_name')) {
                $FinancialBond = $FinancialBond->whereHas('collector_emp', function ($q) use ($request) {


                    $q->Where('name', 'like', '%' . $request->get('collector_name') . '%');
                });

            }
            if ($request->get('owner_id')) {
                $FinancialBond = $FinancialBond
                    //  ->with('rental_contract.estate')
                    ->whereHas('contract.estate', function ($q) use ($request) {


                        $q->where('user_id', $request->get('owner_id'));

                    });

                // dd($payments_due);
            }
            if ($request->get('estate_id')) {
                $FinancialBond = $FinancialBond->where('estate_id', $request->get('estate_id'));

                // dd($payments_due);
            }
            if ($request->get('client_id')) {
                $FinancialBond = $FinancialBond->where('client_id', $request->get('tent_id'));
            }
            if ($request->get('payment_type')) {
                $FinancialBond = $FinancialBond->whereHas('invoice', function ($q) use ($request) {


                    $q->where('payment_type', $request->get('payment_type'));

                });
            }
            if ($request->get('from_date') && $request->get('to_date')) {
                $FinancialBond = $FinancialBond->whereDate(
                    'created_at',
                    '>=',
                    Carbon::parse($request->get('from_date'))
                )->whereDate(
                    'created_at',
                    '<=',
                    Carbon::parse($request->get('to_date'))
                );

            }
            $FinancialBond = $FinancialBond->get();


            return response()->success(__("views.FinancialBond"), $FinancialBond);

        }


    }


    public function test()
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('optimize');
        $exitCode = \Illuminate\Support\Facades\Artisan::call('view:clear');
        $exitCode = \Illuminate\Support\Facades\Artisan::call('route:clear');
        $exitCode = \Illuminate\Support\Facades\Artisan::call('config:clear');
    }

    public function rent_invoices($id)
    {
       $rent_invoice = RentalContractInvoice::where('rental_contracts_id', $id)->get();
        return response()->success(__("views.Rent Invoices"), $rent_invoice);

    }
}
