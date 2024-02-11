<?php

namespace App\Jobs;


use App\Models\v2\Invoice;
use PDF;


class PdfCreator extends Job
{
    private $invoice;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        view()->share('invoice', $this->invoice);
        $file_name = $this->invoice->fort_id . '_' . time() . '.pdf';

        $invoiceNew = Invoice::findOrFail($this->invoice->id);

        $user = $this->invoice->user;
        $payment = $this->invoice->user_plan;
        //   $pdf = PDF::loadView('pdf_view', $data);


        PDF::loadView('payment.invoice')->save(base_path('invoices\\' . $file_name))->stream('download.pdf');
        $invoiceNew->pdf_file = 'invoices\\' . $file_name;
        $invoiceNew->is_created = 1;
        $invoiceNew->save();

        $to = $user->email;
        $from = 'Aqarz@info.com';
        $name = 'Aqarz';
        $subject = 'فاتورة الدفع الخاصة بك';
        $message =url('invoices/' . $file_name) . 'فاتورة الدفع الخاصة بك : ';


        $logo = asset('logo.png');
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


    }
}
