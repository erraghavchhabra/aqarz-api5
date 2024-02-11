<?php

namespace App\Http\Controllers;

use App\Imports\CityImport;
use App\Imports\NeighborhoodImport;
use App\Models\v2\Invoice;
use App\Models\v2\Plan;
use App\Models\v2\UserPlan;
use App\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Http\Request;
use Excel;
use App\Payment\PayfortIntegration;

class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //  $this->middleware('auth');
    }


    public function log($messages)
    {
        $messages = "========================================================\n\n" . $messages . "\n\n";
        $file = storage_path('logs/trace.log');
        if (filesize($file) > 907200) {
            $fp = fopen($file, "r+");
            ftruncate($fp, 0);
            fclose($fp);
        }

        $myfile = fopen($file, "a+");
        fwrite($myfile, $messages);
        fclose($myfile);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function plans($uuid = null)
    {


        $user = User::where('unique_code', $uuid)->first();

       // dd($user);
        if (!$user) {
            return redirect()->route('home')->with('error', __('يجب عليك تسجيل الدخول '));

        }
        if ($uuid == null && \auth()->check()) {

            $uuid = auth()->user()->confirmation_code;
        }



   /*     else {
            return redirect()->route('home')->with('error', __('يجب عليك تقديم طلب دفع من التطبيق '));

        }*/


        $plans = Plan::where('status', '1')->get();
        return view('payment.plans', compact('plans', 'uuid'));
    }

    public function payment_form($uuid, $id)
    {


        $plan = Plan::find($id);


        $user = User::where('unique_code', $uuid)->first();

        if (!$user) {
            return redirect()->route('home')->with('error', __('يجب عليك تسجيل الدخول '));

        }
        if ($plan) {
            $paymentCheck = UserPlan::where('unique_code', $uuid)->first();

            if (!$paymentCheck) {
                $user_plan = UserPlan::create([
                    'plan_id'     => $plan->id,
                    'user_id'     => $user->id,
                    'status'      => '0',
                    'unique_code' => $uuid,
                    'payment_url' => url('subscribe/plan/' . $uuid),
                    'count_try'   => 0,
                    'total'       => $plan->price
                ]);
            }

        }

        //  session()->forget('unique_code');
        $payment = UserPlan::where('unique_code', $uuid)->first();


        /*  setcookie("payment_url", "", time() - 3600);
          setcookie("count_try", "", time() - 3600);
          setcookie("unique_code", "", time() - 3600);
          setcookie("user_name", "", time() - 3600);
          setcookie("user_email", "", time() - 3600);

          setcookie('payment_url', $payment->payment_url, time() + (86400 * 300), "/"); // 86400 = 1 day
          setcookie('count_try', $payment->count_try, time() + (86400 * 300), "/"); // 86400 = 1 day
          setcookie('unique_code', $payment->unique_code, time() + (86400 * 300), "/"); // 86400 = 1 day

          session()->put('unique_code', $payment->unique_code);

          setcookie('user_name', $payment->user->name, time() + (86400 * 300), "/"); // 86400 = 1 day
          setcookie('user_email', $payment->user->email, time() + (86400 * 300), "/"); // 86400 = 1 day*/


        //  dd($_COOKIE['payment_url']);


        if ($payment->count_try >= 1000) {
            $payment->delete();
            /*     setcookie('payment_url', 'null', time() + (86400 * 300), "/"); // 86400 = 1 day
                 setcookie('count_try', 'null', time() + (86400 * 300), "/"); // 86400 = 1 day
                 setcookie('unique_code', 'null', time() + (86400 * 300), "/"); // 86400 = 1 day
                 setcookie('user_name', 'null', time() + (86400 * 300), "/"); // 86400 = 1 day
                 setcookie('user_email', 'null', time() + (86400 * 300), "/"); // 86400 = 1 day*/
            //  session()->forget('unique_code');
            return redirect()->route('try');
        }

        $payment->count_try = $payment->count_try + 1;
        $payment->save();


        $objFort = new PayfortIntegration();
        $amount = $payment->plan->price;
        $currency = $objFort->currency;
        $totalAmount = $payment->plan->price;
        $itemName = $payment->plan->name_ar;
        $objFort->amount = $payment->plan->price;
        $objFort->itemName = $payment->plan->name_ar;


        return view('payment.payment_form',
            compact('amount', 'uuid', 'currency', 'totalAmount', 'objFort', 'itemName'));
    }


    public function order_form(Request $request)
    {


        $payment = UserPlan::with('plan')->where('unique_code', $request->get('uuid'))->first();


        $objFort = new PayfortIntegration();
        $amount = $payment->plan->price;
        $currency = $objFort->currency;
        $totalAmount = $payment->plan->price;
        $itemName = $payment->plan->name_ar;
        $objFort->amount = $payment->plan->price;
        $objFort->itemName = $payment->plan->name_ar;
        $objFort->customerEmail = $payment->user->email;
        $objFort->customer_name = $payment->user->name;
        $paymentMethod = $request->get('payment_method');

        $merchantPageData = $objFort->getMerchantPageData($paymentMethod, $request->get('uuid'));


        $postData = $merchantPageData['params'];
        $gatewayUrl = $merchantPageData['url'];
        $r = $request->get('r');
        $uuid = $request->get('uuid');

        return [
            'r'           => $r,
            'uuid'          => $uuid,
            'amount'        => $amount,
            'postData'      => $postData,
            'gatewayUrl'    => $gatewayUrl,
            'currency'      => $currency,
            'paymentMethod' => $paymentMethod,
            'totalAmount'   => $totalAmount,
            'itemName'      => $itemName
        ];
        /*   return view('payment.order_form',
               compact('r', 'uuid', 'amount', 'postData', 'gatewayUrl', 'currency', 'paymentMethod', 'totalAmount',
                   'objFort', 'itemName'));*/
    }


    public function get_payment_page(Request $request)
    {


        $payment = '';
        if ($request->get('uuid')) {
            $payment = UserPlan::where('unique_code', $request->get('uuid'))->first();
        } elseif ($request->get('3ds')) {
            $payment = UserPlan::where('unique_code', $request->get('3ds'))->first();
        } else {


            $user = User::where('email', $request->get('customer_email'))->first();


            $payment = UserPlan::where('user_id', $user->id)
                ->where('status', '0')
                ->first();


        }

        /*  $payment = '';
          if (isset($_COOKIE['unique_code'])) {
              $uuid = $_COOKIE['unique_code'];

              $payment = UserPlan::where('unique_code', $uuid)->first();
          }
          elseif (session('unique_code'))
          {
              $uuid = session('unique_code');

              $payment = UserPlan::where('unique_code', $uuid)->first();
          }
  */

        $roles = [

            'r' => 'required',
        ];


        $this->validate($request, $roles);

        if ($_REQUEST['r'] == 'getPaymentPage') {
            $objFort = new PayfortIntegration();
            $objFort->amount = $payment->plan->price;
            $objFort->itemName = $payment->plan->name_ar;
            $objFort->customerEmail = $payment->user->email;
            $objFort->customer_name = $payment->user->name;
            $objFort->user_plan_id = $payment->id;

            $objFort->processRequest(htmlspecialchars($_REQUEST['paymentMethod'], ENT_QUOTES, 'UTF-8'),
                $request->get('uuid'));
        } elseif ($_REQUEST['r'] == 'merchantPageReturn') {

            $objFort = new PayfortIntegration();
            $objFort->amount = $payment->plan->price;
            $objFort->itemName = $payment->plan->name_ar;
            $objFort->customerEmail = $payment->user->email;
            $objFort->customer_name = $payment->user->name;
            $objFort->user_plan_id = $payment->id;
            $objFort->processMerchantPageResponse();
        } elseif ($_REQUEST['r'] == 'processResponse') {
            $objFort = new PayfortIntegration();
            $objFort->amount = $payment->plan->price;
            $objFort->itemName = $payment->plan->name_ar;
            $objFort->customerEmail = $payment->user->email;
            $objFort->customer_name = $payment->user->name;
            $objFort->user_plan_id = $payment->id;

            $array = $objFort->processResponse();
            $respone = $array['params'];
            $reason = $array['reason'];
            $success = true;


            $response_message = $respone['response_message'];
            if (substr($respone['response_code'], 2) != '000') {
                $success = false;
                $reason = $response_message;
                $debugMsg = $reason;
                $this->log($debugMsg);
            }

            if (!$success) {
                $p = $respone;
                $p['error_msg'] = $reason;
                $payment->delete();
                return redirect()->route('error', $respone);
                // $return_url = $this->getUrl('error?' . http_build_query($p));
            } else {

                //   dd($respone);


                return redirect()->route('success', $respone);
                //  return view('payment.success', compact('respone'));
                // $return_url = $this->getUrl('success?' . http_build_query($params));
            }


        } else {
            echo 'Page Not Found!';
            exit;
        }

    }


    public function error(Request $request)
    {
        $data = $request->all();
        return view('payment.error', compact('data'));
    }

    public function success(Request $request)
    {

        $data = $request->all();


        $payment = UserPlan::with('plan', 'user')->where('fort_id', $request->get('fort_id'))->first();
        $user = $payment->user;

        $invoice = Invoice::create([
            'fort_id'           => $request->get('fort_id'),
            'plan_id'           => $payment->plan_id,
            'user_id'           => $payment->user_id,
            'user_plan_id'      => $payment->id,
            'total'             => $payment->total,
            'is_pay'            => 1,
            'payment_method_id' => 1,
            //   'status'            => 1,
        ]);


        $invoice = Invoice::with('user')->findOrFail($invoice->id);
        //  $this->createPDF($invoice);

        $payment->status = '1';
        $user->unique_code = null;
        $user->save();
        $fort_id = $request->get('fort_id');
        $payment->save();
        $to = $user->email;
        $from = 'Aqarz@info.com';
        $name = 'Aqarz';
        $subject = 'تم الدفع بنجاح';
        $message = $payment->fort_id . 'رمز تحققك من الدفع في حال واجهتك مشكلة هو : ';


        $logo = asset('logo.png');
        $link = '#';

        $details = [
            'to'       => $to,
            'from'     => $from,
            'logo'     => $logo,
            'link'     => $link,
            'subject'  => $subject,
            'name'     => $name,
            "message"  => $message,
            "text_msg" => '',
        ];
        \Mail::to($to)->send(new \App\Mail\NewMail($details));


        $user = User::findOrFail($payment->user->id);
        $user->is_pay = '1';
        $user->save();

        return view('payment.success', compact('data', 'fort_id'));
    }


    /*   public function createPDF($invoice)
       {



           view()->share('invoice', $invoice);
           $file_name = $invoice->fort_id . '_' . time() . '.pdf';


           //   $pdf = PDF::loadView('pdf_view', $data);


           return PDF::loadView('payment.invoice')->save(public_path('invoices\\' . $file_name))->stream('download.pdf');


           // download PDF file with download method

       }*/


    public function try(Request $request)
    {
        return view('payment.try');
    }


    public function paymentInvoices()
    {
        Artisan::call("pdf:create");


    }


}
