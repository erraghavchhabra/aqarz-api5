<?php


namespace App\Http\Controllers;

use App\Payment;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MoeenBasra\Payfort\PayfortFacade as Payfort;

class PayfortController
{
    /** @var  \MoeenBasra\Payfort\MerchantPage\MerchantPage */
    protected $intent;

    public function __construct()
    {
        $this->intent = Payfort::configure(config('payfort'));
    }

    public function create()
    {


        $response = $this->intent->prepareTokenizationData([
            'token_name'         => Uuid::uuid4()->getHex(),
            'merchant_reference' => Uuid::uuid4()->getHex()->toString(),
            'return_url'         => config('app.url') . '/payfort/tokenization',
        ]);





        return response()->json([
            'type' => 'form',
            'url'  => $this->intent->getClient()->getTokenizationUrl(),
            'data' => $response,
        ]);
    }

    public function handleTokenResponse(Request $request)
    {

//http://aqarz.local.com:8080/payfort/tokenization?
//response_code=18000&
//card_number=400555******0001&
//card_holder_name=test&
//signature=0ca35cf3406e0b087bf13294b721d9a3e68e998f87b7fc6200e534929d4fae26
//&merchant_identifier=aa12219e&
//expiry_date=2105&
//access_code=jrrEGwFoiF1DFXKbHbsZ&
//language=en&
//service_command=TOKENIZATION&
//response_message=Success&
//merchant_reference=77786187675e435a8aad2f4a2ff59397&
//token_name=0e79ba2f14ba486a9aa0d6db9c9c87b7&
//return_url=http%3A%2F%2Faqarz.local.com%3A8080%2Fpayfort%2Ftokenization&currency=SAR&card_bin=400555&status=18





        $input = $request->all();

        Log::info('tokenization response received from payfort:' . PHP_EOL . print_r($input, 1));

        $this->intent->verifyResponse($input);

        // create new payment object
        $payment = new Payment();

        /** set customer token from the input */
        $payment->setCustomerToken(Arr::get($input, 'token_name'));

     // dd($payment->setCustomerToken(Arr::get($input, 'token_name')));

        // prepare payment data
        $data = $this->intent->authorization([
            'command'            => 'PURCHASE',
            'merchant_reference' => Uuid::uuid4()->getHex()->toString(),
            'token_name'         => $payment->getCustomerToken(),
            'amount'             => $this->intent->convertAmountToPayfortFormat($payment->getAmount()),
            'customer_email'     => $payment->getCustomerEmail(),
            'customer_ip'        => $payment->getCustomerIp(),
            'return_url'         => config('app.url') . '/payfort/response',
        ]);






        if (isset($data['3ds_url'])) {
            return redirect()->away($data['3ds_url']);
        }



        return response()->json($data);
    }

    public function handleResponse(Request $request)
    {
        $input = $request->all();



        Log::info('purchase response received from payfort:' . PHP_EOL . print_r($input, 1));


        $this->intent->verifyResponse($input);

        return response()->json($input);
    }

    public function handleError(Request $request)
    {
        $input = $request->all();

        Log::info('error received from payfort:' . PHP_EOL . print_r($input, 1));

        $this->intent->verifyResponse($input);

        return response()->json($input);
    }

    public function handleCallback(Request $request)
    {
        $input = $request->all();

        Log::info('callback received from payfort:' . PHP_EOL . print_r($input, 1));

        $this->intent->verifyResponse($input);

        return response()->json($input);
    }
}
