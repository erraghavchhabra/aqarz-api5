<?php

namespace App\Http\Controllers\DashBoard\Fund;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\ClickFirstTable;
use App\Http\Resources\ClickUpRequest;
use App\Http\Resources\ClickUpReviewTable;
use App\Http\Resources\EmpResource;
use App\Models\dashboard\Admin;
use App\Models\v3\EstatePrice;
use App\Models\v3\FundRequestNeighborhood;
use App\Models\v3\ContactStage;
use App\Models\v3\FieldPreviewStage;
use App\Models\v3\FinanceStage;
use App\Models\v3\FundOfferComment;
use App\Models\v3\FundRequestOfferClickUp;
use App\Models\v3\PreviewStage;
use App\Models\v3\RequestFund;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;


class ClickUpController extends Controller
{


    public function preview_fund_offer2()
    {
        $fund_preview_monthly = FundRequestOfferClickUp::first();


        //  $model = new FundRequestOfferClickUp();
        // dd($model->getFillable());
        return $fund_preview_monthly;
    }

    public function preview_fund_offer_old(Request $request)
    {

        $rules = Validator::make($request->all(), [
            //  'month_date' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $fund_review_offer = FundRequestOfferClickUp::where('status', 'sending_code');
        if ($request->get('month_date')) {
            $monthArray = explode('-', $request->get('month_date'));

            //  dd($monthArray[0]);
            $fund_review_offer = $fund_review_offer
                ->whereMonth('created_at', $monthArray[0])
                ->whereYear('created_at', $monthArray[1]);
        }
        $fund_review_offer = $fund_review_offer->get()->groupBy(function ($item) {
            $month = date("m", strtotime($item->created_at));
            $year = date("Y", strtotime($item->created_at));

            return $month . '-' . $year;
        })->toArray();
        $fund_review_offer = $this->paginate($fund_review_offer);
        // dd(array_keys($meals));
        return JsonResponse::success($fund_review_offer, __('عروض طلبات المعاينة'));
        //  return ['data' => $user];
    }


    public function preview_requests(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            //  'month_date' => 'required',

        ]);
        $page = $request->get('page_number', 10);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $fund_review_offer = '';
        if ($user->id != 2) {
            $fund_review_offer = RequestFund::
            whereHas('offers', function ($query) use ($user) {
                $query->where('assigned_id', $user->id);
            })->whereHas('contact_stages', function ($query) use ($user) {
                $query->where('assigned_id', $user->id);
            })->whereHas('preview_stages', function ($query) use ($user) {
                $query->where('assigned_id', $user->id);
            })->with('contact_stages', 'preview_stages.field_preview_stages', 'assigned')
                ->where('status', 'sending_code');
            //  ->where('emp_id', $user->id);

        } else {
            $fund_review_offer = RequestFund::whereHas('offers')
                //->whereHas('assigned')
                ->with('contact_stages')
                ->with('offers')
                ->with('finance_stages')
                ->with('preview_stages.field_preview_stages')
                ->where('status', 'sending_code');
            //->where('request_funds.id', 9600);

            // dd($fund_review_offer->where('id',9600)->get());


        }


        if ($request->get('form_date')) {
            $date = date_create($request->get('form_date'));
            $date = date_format($date, "Y-m-d");

            $fund_review_offer = $fund_review_offer->whereDate('created_at', '>', $date);
            //dd($fund_review_offer->get());
        }
        if ($request->get('to_date')) {

            $date = date_create($request->get('to_date'));
            $date = date_format($date, "Y-m-d");
            $fund_review_offer = $fund_review_offer->whereDate('created_at', '<', $date);

        }


        if ($request->get('estate_type_id')) {
            $fund_review_offer = $fund_review_offer->where('estate_type_id', $request->get('estate_type_id'));

        }
        if ($request->get('state_id')) {
            $fund_review_offer = $fund_review_offer->whereHas('fund_request', function ($q) use ($request) {


                $q->whereIn('state_id', $request->get('state_id'));
            });
        }
        if ($request->get('city_id')) {
            $fund_review_offer = $fund_review_offer->whereHas('fund_request', function ($q) use ($request) {


                $q->whereIn('city_id', $request->get('serial_city'));
            });
        }

        if ($request->get('estate_price_id')) {
            $fund_review_offer = $fund_review_offer->where('estate_price_id', $request->get('estate_price_id'));

        }


        if ($request->get('search')) {
            $search = trim($request->get('search'));


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && FundRequestOfferClickUp::find($request->get('search'))) {
                $fund_review_offer = $fund_review_offer->where('id', $request->get('search'));

            } else {
                $fund_review_offer = $fund_review_offer
                    ->Where('uuid', 'like', '%' . $search . '%')
                    //  ->orWhere('owner_name', 'like', '%' . $search . '%')
                    // ->orWhere('owner_mobile', 'like', '%' . $search . '%')
                    //     ->orWhere('owner_identity_number', 'like', '%' . $search . '%')
                    ->orWhere('paid_status', 'like', '%' . $search . '%')
                    ->orWhere('priority', 'like', '%' . $search . '%')
                    ->orWhere('contract_status', 'like', '%' . $search . '%')
                    ->orWhere('preview_status', 'like', '%' . $search . '%')
                    ->orWhere('funding_status', 'like', '%' . $search . '%')
                    ->orWhere('contact_status', 'like', '%' . $search . '%')
                    ->orWhere('send_offer_type', 'like', '%' . $search . '%');
                //    ->orWhere('employer_name', 'like', '%' . $search . '%');
            }


        }
        if ($request->get('neighborhood_id')) {
            $array_neb = explode(',', $request->get('neighborhood_id'));
            if (isset($array_neb) && count($array_neb) > 0 && $array_neb[0] != null) {

                //dd( $request->get('query')['neighborhood_id']);
                // dd($request->get('query')['neighborhood_id']);
                /*    $finiceing = $finiceing->whereHas('neighborhood', function ($q) use ($request) {


                        $q->whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
                    });
        */


                $nem = FundRequestNeighborhood::whereHas('fund_request')->whereIn('neighborhood_id',
                    $array_neb)
                    ->pluck('request_fund_id');


                $fund_review_offer = $fund_review_offer->whereIn('id', $nem->toArray());


                //  whereIn('neighborhood_id', $request->get('query')['neighborhood_id']);
            }
        }

        $fund_review_offer = $fund_review_offer->orderBy('id', 'desc')->paginate($page);

        $collection = ClickUpRequest::collection($fund_review_offer)->response()->getData(true);

        return JsonResponse::success($collection, __('عروض طلبات المعاينة'));
        //  return ['data' => $user];
    }


    public function preview_fund_rquest_add_contact_stage(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            'request_id' => 'required',
            'contact_status' => 'required',
            'implementation_cases' => 'required',
            'assigned_id' => 'required',
            'notes' => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_fund = RequestFund::find($request->get('request_id'));

        if ($request_fund) {
            $cotnact_stage = ContactStage::create([
                'request_id' => $request_fund->id,
                'request_uuid' => $request_fund->uuid,
                'emp_id' => $user->id,
                'assigned_id' => $request->get('assigned_id'),
                'contact_status' => $request->get('contact_status'),
                'implementation_cases' => $request->get('implementation_cases'),
                'notes' => $request->get('notes'),
            ]);
            $request_fund = RequestFund::with('contact_stages')->find($request->get('request_id'));

            return JsonResponse::success($request_fund, __('تم إضافة الملاحظة بنجاح'));
        }
        return response()->error(__('لايوجد طلب'));

    }

    public function preview_fund_rquest_add_preview_stage(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            'request_id' => 'required',
            'preview_date' => 'required',
            'assigned_id' => 'required',
            'preview_time' => 'required',
            'ascertainment_status' => 'required',
            'notes' => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_fund = RequestFund::find($request->get('request_id'));

        if ($request_fund) {
            $cotnact_stage = PreviewStage::create([
                'request_id' => $request_fund->id,
                'request_uuid' => $request_fund->uuid,
                'emp_id' => $user->id,
                'assigned_id' => $request->get('assigned_id'),
                'preview_date' => $request->get('preview_date'),
                'preview_time' => $request->get('preview_time'),
                'ascertainment_status' => $request->get('ascertainment_status'),
                'notes' => $request->get('notes'),
            ]);
            $request_fund = RequestFund::with('preview_stages')->find($request->get('request_id'));

            return JsonResponse::success($request_fund, __('تم إضافة المعاينة بنجاح'));
        }
        return response()->error(__('لايوجد طلب'));

    }

    public function preview_fund_rquest_add_field_preview_stage(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            'preview_stage_id' => 'required',
            'field_emp_name' => 'required',
            'attendance_status' => 'required',
            'assigned_id' => 'required',
            'preview_status' => 'required',
            'estate_visited_count' => 'required',
            'notes' => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_fund = PreviewStage::find($request->get('preview_stage_id'));

        if ($request_fund) {
            $cotnact_stage = FieldPreviewStage::create([
                'request_id' => $request_fund->id,
                'request_uuid' => $request_fund->uuid,
                'emp_id' => $user->id,
                'preview_stage_id' => $request->get('preview_stage_id'),
                'assigned_id' => $request->get('assigned_id'),
                'field_emp_name' => $request->get('field_emp_name'),
                'attendance_status' => $request->get('attendance_status'),
                'preview_status' => $request->get('preview_status'),
                'estate_visited_count' => $request->get('estate_visited_count'),
                'notes' => $request->get('notes'),
            ]);
            $request_fund = PreviewStage::with('field_preview_stages')->find($request->get('preview_stage_id'));

            return JsonResponse::success($request_fund, __('تم إضافة المعاينة الميدانية بنجاح'));
        }
        return response()->error(__('لايوجد طلب'));

    }

    public function preview_fund_rquest_add_finance_stage(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            'request_id' => 'required',
            'funding_status' => 'required',
            'assigned_id' => 'required',
            'contract_status' => 'required',
            'attachments' => 'sometimes|required',
            'cancel_cause' => 'sometimes|required',
            'notes' => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request_fund = RequestFund::find($request->get('request_id'));

        if ($request_fund) {
            $FinanceStage = FinanceStage::create([
                'request_id' => $request_fund->id,
                'request_uuid' => $request_fund->uuid,
                'emp_id' => $user->id,
                'funding_status' => $request->get('funding_status'),
                'assigned_id' => $request->get('assigned_id'),
                'contract_status' => $request->get('contract_status'),
                'cancel_cause' => $request->get('cancel_cause'),
                'notes' => $request->get('notes'),
            ]);


            if ($request->hasFile('attachments')) {
                $path = $request->file('attachments')->store('images', 's3');
                $request_fund = FinanceStage::find($FinanceStage->id);
                $request_fund->attachments = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $request_fund->save();

            }

            $request_fund = RequestFund::with('finance_stages')->find($request->get('request_id'));

            return JsonResponse::success($request_fund, __('تم إضافة التمويل  بنجاح'));
        }
        return response()->error(__('لايوجد طلب'));

    }


    public function preview_fund_offer(Request $request)
    {

        $rules = Validator::make($request->all(), [
            //  'month_date' => 'required',

        ]);
        $page = $request->get('page_number', 10);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $fund_review_offer = FundRequestOfferClickUp::
        whereHas('fund_request')
            ->whereHas('estate')
            ->whereHas('provider')
            ->with('fund_request')
            ->with('estate')
            ->with('provider')
            ->where('status', 'sending_code');


        if ($request->get('month_date')) {

            $monthArray = explode('-', $request->get('month_date'));

            //  dd($monthArray[0]);
            $fund_review_offer = $fund_review_offer
                ->whereMonth('created_at', $monthArray[0])
                ->whereYear('created_at', $monthArray[1]);
        }

        if ($request->get('estate_type_id')) {
            $fund_review_offer = $fund_review_offer->whereHas('fund_request', function ($q) use ($request) {


                $q->whereIn('estate_type_id', $request->get('estate_type_id'));
            });
        }
        if ($request->get('state_id')) {
            $fund_review_offer = $fund_review_offer->whereHas('fund_request', function ($q) use ($request) {


                $q->whereIn('state_id', $request->get('state_id'));
            });
        }
        if ($request->get('city_id')) {
            $fund_review_offer = $fund_review_offer->whereHas('fund_request', function ($q) use ($request) {


                $q->whereIn('city_id', $request->get('serial_city'));
            });
        }

        if ($request->get('search')) {
            $search = trim($request->get('search'));


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && FundRequestOfferClickUp::find($request->get('search'))) {
                $fund_review_offer = $fund_review_offer->where('id', $request->get('search'));

            } else {
                $fund_review_offer = $fund_review_offer
                    ->Where('uuid', 'like', '%' . $search . '%')
                    //  ->orWhere('owner_name', 'like', '%' . $search . '%')
                    // ->orWhere('owner_mobile', 'like', '%' . $search . '%')
                    //     ->orWhere('owner_identity_number', 'like', '%' . $search . '%')
                    ->orWhere('paid_status', 'like', '%' . $search . '%')
                    ->orWhere('priority', 'like', '%' . $search . '%')
                    ->orWhere('contract_status', 'like', '%' . $search . '%')
                    ->orWhere('preview_status', 'like', '%' . $search . '%')
                    ->orWhere('funding_status', 'like', '%' . $search . '%')
                    ->orWhere('contact_status', 'like', '%' . $search . '%')
                    ->orWhere('send_offer_type', 'like', '%' . $search . '%');
                //    ->orWhere('employer_name', 'like', '%' . $search . '%');
            }


        }


        $fund_review_offer = $fund_review_offer->orderBy('id', 'desc')->paginate();

        $collection = ClickUpReviewTable::collection($fund_review_offer);

        return JsonResponse::success($collection, __('عروض طلبات المعاينة'));
        //  return ['data' => $user];
    }


    public function settings(Request $request)
    {

        /*  $paid_status = [
              'pending' => 'إنتظار',
              'under_conversion' => 'قيد التحويل',
              'was_received' => 'تم التحويل',
          ];*/

        $paid_status = [
            ['id' => 'pending', 'name' => 'إنتظار'],
            ['id' => 'under_conversion', 'name' => 'قيد التحويل'],
            ['id' => 'was_received', 'name' => 'تم التحويل'],
            // 'under_conversion' => 'قيد التحويل',
            //'was_received' => 'تم التحويل',
        ];

        $priority = [
            ['id' => 'urgent', 'name' => 'عاجل'],
            ['id' => 'high', 'name' => 'عالي'],
            ['id' => 'normal', 'name' => 'عادي'],
            ['id' => 'low', 'name' => 'منخفض'],
            // 'under_conversion' => 'قيد التحويل',
            //'was_received' => 'تم التحويل',
        ];


        /*  $priority = [
              'urgent' => 'عاجل',
              'high' => 'عالي',
              'normal' => 'عادي',
              'low' => 'منخفض',
          ];*/

        /*   $contract_status = [
              'contract_processing' => 'قيد المعالجة',
              'send_contract' => 'تم الإرسال',
              'signing_contract' => 'تم التوقيع',
          ];*/

        $contract_status = [
            ['id' => 'contract_processing', 'name' => 'قيد المعالجة'],
            ['id' => 'send_contract', 'name' => 'تم الإرسال'],
            ['id' => 'signing_contract', 'name' => 'تم التوقيع'],
            // ['id' => 'low','name'=>'منخفض'],
            // 'under_conversion' => 'قيد التحويل',
            //'was_received' => 'تم التحويل',
        ];


        /*  $stage_status =
             [
                 'email_capture' => 'التقاط البريد',
                 'marketing_qualified' => 'مؤهل تسويق',
                 'sales_qualified' => 'مؤهل للبيع',
                 'demo' => 'تجريبي',
                 'proposal' => 'شخصي',
                 'negotiations' => 'مفاوضات',
                 'hand_off_to_success' => 'تسليم للنجاح',
                 'launched' => 'انطلقت',
                 'follow_up' => 'متابعة',
             ];*/
        $stage_status = [
            ['id' => 'email_capture', 'name' => 'التقاط البريد'],
            ['id' => 'marketing_qualified', 'name' => 'مؤهل تسويق'],
            ['id' => 'sales_qualified', 'name' => 'مؤهل للبيع'],
            ['id' => 'demo', 'name' => 'تجريبي'],
            ['id' => 'proposal', 'name' => 'شخصي'],
            ['id' => 'negotiations', 'name' => 'مفاوضات'],
            ['id' => 'hand_off_to_success', 'name' => 'تسليم للنجاح'],
            ['id' => 'launched', 'name' => 'انطلقت'],
            ['id' => 'follow_up', 'name' => 'متابعة'],
            // 'under_conversion' => 'قيد التحويل',
            //'was_received' => 'تم التحويل',
        ];
        /*   $funding_status =
              [
                  'unworthy' => 'لا يستحق',
                  'under_review' => 'قيد المراجعة',
                  'poor_financial_ability' => 'ضعف القدرة المالية',
                  'underway' => 'قيد التنفيذ',
                  'funded' => 'تم التمويل',
              ];*/
        $funding_status = [
            ['id' => 'unworthy', 'name' => 'لا يستحق'],
            ['id' => 'under_review', 'name' => 'قيد المراجعة'],
            ['id' => 'poor_financial_ability', 'name' => 'ضعف القدرة المالية'],
            ['id' => 'underway', 'name' => 'قيد التنفيذ'],
            ['id' => 'funded', 'name' => 'تم التمويل'],

            // 'under_conversion' => 'قيد التحويل',
            //'was_received' => 'تم التحويل',
        ];

        /*  $preview_status =
            [
                'schedule_preview' => 'معاينة الجدول الزمني',
                'successful_preview' => 'تمت المعاينة بنجاح',
                'failed_preview_provide_an_alternative' => 'توفر المعاينة الفاشلة بديلاً',
                'failed_preview_cancel_order' => 'فشل معاينة إلغاء الطلب',
                'not_serious_customer' => 'ليس زبونًا جادًا',
                'the_property_has_been_sold_providing_an_alternative' => 'تم بيع العقار لتوفير بديل',
                'coordinated_with_the_office' => 'بالتنسيق مع المكتب',
                'waiting_for_eligibility_verification' => 'في انتظار التحقق من الأهلية',
                'searching_for_a_property' => 'البحث عن عقار',
            ];
*/


        $preview_status = [
            ['id' => 'schedule_preview', 'name' => 'معاينة الجدول الزمني'],
            ['id' => 'successful_preview', 'name' => 'تمت المعاينة بنجاح'],
            ['id' => 'failed_preview_provide_an_alternative', 'name' => 'توفر المعاينة الفاشلة بديلا'],
            ['id' => 'failed_preview_cancel_order', 'name' => 'فشل معاينة إلغاء الطلب'],
            ['id' => 'not_serious_customer', 'name' => 'ليس زبونًا جادًا'],
            ['id' => 'the_property_has_been_sold_providing_an_alternative', 'name' => 'تم بيع العقار لتوفير بديل'],
            ['id' => 'coordinated_with_the_office', 'name' => 'بالتنسيق مع المكتب'],
            ['id' => 'waiting_for_eligibility_verification', 'name' => 'في انتظار التحقق من الأهلية'],
            ['id' => 'searching_for_a_property', 'name' => 'البحث عن عقار'],
        ];
        /*    $contact_status =
               [
                   'answered' => 'تم الرد',
                   'no_response' => 'لايوجد رد',
                   'recall' => 'إعادة اتصال',
                   'whats_up' => 'واتس اب'
               ];*/
        $contact_status = [
            ['id' => 'answered', 'name' => 'تم الرد'],
            ['id' => 'no_response', 'name' => 'تمت المعاينة بنجاح'],
            ['id' => 'recall', 'name' => 'لايوجد رد'],
            ['id' => 'whats_up', 'name' => 'واتس اب'],
        ];
        $admins = Admin::where('status', 'active')->get();

//'looking_for_a_property','looking_for_financing','not_serious','not_serious_purchasing_period_unknown',
//'not_serious_illogical_request','unworthy','no_response','the_phone_is_incorrect','duplicate_number',
//'no_offers','bank_procedures','sale_has_ended'
        $implementation_cases = [
            ['id' => 'looking_for_a_property', 'name' => 'يبحث عن عقار'],
            ['id' => 'looking_for_financing', 'name' => 'يبحث عن تمويل'],
            ['id' => 'not_serious', 'name' => 'غير جاد'],
            ['id' => 'unworthy', 'name' => 'لا يستحق'],
            ['id' => 'the_phone_is_incorrect', 'name' => 'الرقم غير صحيح'],
            ['id' => 'duplicate_number', 'name' => 'الرقم مكرر'],
            ['id' => 'no_offers', 'name' => 'لايوجد عروض'],
            ['id' => 'sale_has_ended', 'name' => 'انتهى البيع'],
            ['id' => 'bank_procedures', 'name' => 'الإجراءات المصرفية'],
            ['id' => 'no_response', 'name' => 'لايوجد إستجابة'],
            ['id' => 'not_serious_purchasing_period_unknown', 'name' => 'ليس طلبًا جادًا غير منطقي'],
            ['id' => 'not_serious_purchasing_period_unknown', 'name' => 'فترة شراء غير جدية غير معروفة'],
        ];

        /*  $implementation_cases =
             [
                 'looking_for_a_property' => 'يبحث عن عقار',
                 'looking_for_financing' => 'يبحث عن تمويل',
                 'not_serious' => 'غير جاد',
                 'unworthy' => 'لا يستحق',
                 'the_phone_is_incorrect' => 'الرقم غير صحيح',
                 'duplicate_number' => 'الرقم مكرر',
                 'no_offers' => 'لايوجد عروض',
                 'sale_has_ended' => 'انتهى البيع',
                 'bank_procedures' => 'الإجراءات المصرفية',
                 'no_response' => 'لايوجد إستجابة',
                 'not_serious_illogical_request' => 'ليس طلبًا جادًا غير منطقي',
                 'not_serious_purchasing_period_unknown' => 'فترة شراء غير جدية غير معروفة'
             ];*/
        $EstatePrice = EstatePrice::where('status', '1')->get();
        $Neighborhood = DB::table('neighborhoods')->get();
        $array =
            [
                'contact_status' => $contact_status,
                'EstatePrice' => $EstatePrice,
                'preview_status' => $preview_status,
                'funding_status' => $funding_status,
                'stage_status' => $stage_status,
                'contract_status' => $contract_status,
                'priority' => $priority,
                'paid_status' => $paid_status,
                'implementation_cases' => $implementation_cases,
                'admins' => $admins,
                'Neighborhood' => $Neighborhood,
            ];


        return JsonResponse::success($array, __('الإعدادات العامة لمتابعة الطلبات'));
    }

    public function paginate($items, $perPage = 15, $page, $options = [])
    {


        // $items = $items instanceof Collection ? $items : Collection::make($items);
        //  $items = array_values($items);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        // return $items->forPage($page, $perPage);
        //  dd($items->count());
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    public function preview_fund_offer_add_comment(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            'comment' => 'required',
            'fund_offer_id' => 'required',
            'assign_id' => 'sometimes|required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $fund_offer = FundRequestOfferClickUp::find($request->get('fund_offer_id'));

        if ($fund_offer) {
            $comment = FundOfferComment::create([
                'fund_offer_id' => $request->get('fund_offer_id'),
                'comment' => $request->get('comment'),
                'user_id' => $user->id,
                'assign_id' => $request->get('assign_id'),
            ]);
            $fund_offer->last_commit = $request->get('comment');
            $fund_offer->commints_count = $fund_offer->commints_count + 1;
            $fund_offer->save();

            return JsonResponse::success($comment, __('تم إضافة الملاحظة بنجاح'));
        }
        return response()->error(__('لايوجد عرض بالرقم المرسل'));

    }

    public function update_priew_fund_request_offer(Request $request)
    {


        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            'fund_offer_id' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $FundRequestOfferClickUp = FundRequestOfferClickUp::find($request->get('fund_offer_id'))
            ->update($request->only([
                'priority',
                'assigned_commit',
                'assigned_id',
                'commints_count',
                'created_by_id',
                'closed_at',
                'last_commit',
                'time_estimate',
                'start_date',
                'contract_status',
                'negotiation_price',
                'is_paid',
                'paid_status',
                'stage_status',
                'price_per_ft',
                'preview_status',
                'funding_status',
                'contact_status',
                'uuid',
                'request_id',
                'instument_number',
                'guarantees',
                'beneficiary_name',
                'beneficiary_mobile',
                'status',
                'estate_id',
                'provider_id',
                'reason',
                'send_offer_type',
                'first_show_date',
                'show_count',
                'request_preview_date',
                'app_name',

            ]));

        $FundRequestOfferClickUp = FundRequestOfferClickUp::find($request->get('fund_offer_id'));
        return JsonResponse::success($FundRequestOfferClickUp, __('تم تحديث المعلومات بنجاح'));
        //   return response()->success(__('تم تحديث المعلومات بنجاح'), $FundRequestOfferClickUp);
    } //<--- End Method


    public function preview_fund_offer_show(Request $request)
    {

        $rules = Validator::make($request->all(), [
            'fund_offer_id' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $fund_review_offer = FundRequestOfferClickUp::with('notes')->find($request->get('fund_offer_id'));

        if ($fund_review_offer) {
            $array[] = [

                'id' => $fund_review_offer->id,
                'uuid' => $fund_review_offer->uuid,
                'request_id' => $fund_review_offer->request_id,
                'beneficiary_name' => $fund_review_offer->beneficiary_name,
                'beneficiary_mobile' => $fund_review_offer->beneficiary_mobile,
                'lat' => $fund_review_offer->estate->lat,
                'lan' => $fund_review_offer->estate->lan,

                'onwer_name' => @$fund_review_offer->estate->user->onwer_name,
                'onwer_mobile' => @$fund_review_offer->estate->user->mobile,
                'status' => $fund_review_offer->status,
                'estate_id' => $fund_review_offer->estate_id,
                'negotiation_price' => $fund_review_offer->negotiation_price,
                'price_per_ft' => $fund_review_offer->price_per_ft,
                'send_offer_type' => $fund_review_offer->send_offer_type,
                'is_paid' => $fund_review_offer->is_paid,
                'paid_status' => $fund_review_offer->paid_status,
                'priority' => $fund_review_offer->priority,
                'contract_status' => $fund_review_offer->contract_status,
                'stage_status' => $fund_review_offer->stage_status,
                'funding_status' => $fund_review_offer->funding_status,
                'preview_status' => $fund_review_offer->preview_status,
                'contact_status' => $fund_review_offer->contact_status,
                'reason' => $fund_review_offer->reason,
                'assigned_commit' => $fund_review_offer->assigned_commit,
                'last_commit' => $fund_review_offer->last_commit,
                'assigned_id' => $fund_review_offer->assigned_id,
                'created_by_id' => $fund_review_offer->created_by_id,
                'commints_count' => $fund_review_offer->commints_count,
                'app_name' => $fund_review_offer->app_name,
                'is_close' => $fund_review_offer->is_close,
                'provider_id' => $fund_review_offer->provider_id,
                'show_count' => $fund_review_offer->show_count,
                'time_estimate' => $fund_review_offer->time_estimate,
                'first_show_date' => $fund_review_offer->first_show_date,
                'created_at' => $fund_review_offer->created_at,
                'updated_at' => $fund_review_offer->updated_at,
                'closed_at' => $fund_review_offer->closed_at,
                'start_date' => $fund_review_offer->start_date,
                'request_preview_date' => $fund_review_offer->request_preview_date,
                'status_name' => $fund_review_offer->status_name,
                'estate_type_name' => $fund_review_offer->estate->estate_type_name,
                'estate_type_id' => $fund_review_offer->estate->estate_type_id,
                'estate_full_address' => $fund_review_offer->estate->full_address,
                'estate_total_price' => $fund_review_offer->estate->total_price,
                'estate_total_area' => $fund_review_offer->estate->total_area,
                'estate_link' => @$fund_review_offer->estate->link,
                'request_link' => @$fund_review_offer->fund_request->link,
                'comments' => @$fund_review_offer->notes,


            ];
            return JsonResponse::success($array, __('عرض طلبات المعاينة'));

        }

        return response()->error(__('لايوجد عرض بالرقم المرسل'));
        //  return ['data' => $user];
    }

    public function arrayPaginator($array, $request, $page)
    {


        $perPage = $page;
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(
            array_slice(
                $array,
                $offset,
                $perPage,
                true
            ),
            count($array),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function get_emp()
    {
        $admins = Admin::where('status', 'active')->get();
        if ($admins) {
            $admins = EmpResource::collection($admins);
            return response()->success( __('تم  بنجاح'),$admins);
        }
        return response()->error(__('هناك مشكلة '));
    }

    public function emp_assigned(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $rules = Validator::make($request->all(), [
            'type' => 'required',
            'type_id' => 'required',
            'assigned_id' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $type = 'App\Models\v3\\' . ucfirst($request->get('type'));
        $type_object = $type::where('id', $request->get('type_id'))
            ->first();

        if ($type_object) {
            $type_object->assigned_id = $request->get('assigned_id');
            $type_object->save();
            return JsonResponse::success($type_object, __('تم إضافة التعيين بنجاح'));
        }
        return response()->error(__('هناك مشكلة في عملية اضافة التعيين'));
    }

    public function show_fund_request($id)
    {
        $fund_review_offer = RequestFund::whereHas('offers')
            //->whereHas('assigned')
            ->with('contact_stages')
            ->with('offers')
            ->with('finance_stages')
            ->with('preview_stages.field_preview_stages')
            ->where('status', 'sending_code')
            ->where('id', $id)->first();

        if ($fund_review_offer) {

            return JsonResponse::success($fund_review_offer, __('تم  بنجاح'));
        }
        return response()->error(__('هناك مشكلة '));
    }
}
