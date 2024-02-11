<?php

namespace App\Jobs;


use App\Models\v2\Invoice;
use App\Models\v2\UserPayment;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use Illuminate\Support\Facades\Log;
use PDF;


class PaymentMsgCreator extends Job
{
    private $msg;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UserPayment $msg)
    {
        $this->msg = $msg;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $msgNew = UserPayment::findOrFail($this->msg->id);

        $user = $this->msg->user;

        //   $pdf = PDF::loadView('pdf_view', $data);


        $msgNew->is_send = 1;
        $msgNew->save();

        $to = $user->email;
        $from = 'Aqarz@info.com';
        $name = 'Aqarz';
        $subject = 'شكرا لك , تم استلام طلب الترقية الخاص بك';
        $message ='شكرا لك , شوف يتم التواصل معك في اقرب وقت ';


        $logo = url('logo.svg');
        $link = '#';

        $details = [
            'to'      => $to,
            'from'    => $from,
            'logo'    => $logo,
            'link'    => $link,
            'subject' => $subject,
            'name'    => $name,
            "message" => $message,
            "text_msg" => '',
        ];
        \Mail::to($to)->send(new \App\Mail\NewMail($details));




        $user_mobile = checkIfMobileStartCode($user->mobile, $user->country_code);
        $unifonicMessage = new UnifonicMessage();
        $unifonicClient = new UnifonicClient();
        $unifonicMessage->content = "Your Verification Code Is: ";
        $to = $user_mobile;
        $co = $message;
        $data = $unifonicClient->sendCustomer($to, $co);
        Log::channel('single')->info($data);
        Log::channel('slack')->info($data);


    }
}
