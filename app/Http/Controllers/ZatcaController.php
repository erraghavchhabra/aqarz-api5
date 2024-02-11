<?php

namespace App\Http\Controllers;

use App\Zatca\EGS;


class ZatcaController extends Controller
{

    public function index()
    {

        $line_item = [
            'id' => '1',
            'name' => 'TEST NAME',
            'quantity' => 5,
            'tax_exclusive_price' => 10.00,
            'VAT_percent' => 0.15,
            'other_taxes' => [
            ],
            'discounts' => [
            ],
        ];


        $egs_unit = [
            'uuid' => uuid(),
            'custom_id' => 'EGS1-886431145',
            'model' => 'IOS',
            'CRN_number' => '454634645645654',
            'VAT_name' => 'Wesam Alzahir',
            'VAT_number' => '301121971500003',
            'location' => [
                'city' => 'Khobar',
                'city_subdivision' => 'West',
                'street' => 'King Fahahd st',
                'plot_identification' => '0000', // optional
                'building' => '0000', // optional
                'postal_zone' => '31952',
            ],
            'branch_name' => 'My Branch Name',
            'branch_industry' => 'estate',
            'cancelation' => [
                'cancelation_type' => 'INVOICE',
                'canceled_invoice_number' => '',
            ],
        ];

        $invoice = [
            'invoice_counter_number' => 1,
            'invoice_serial_number' => 'EGS1-886431145-1',
            'issue_date' => '2022-03-13',
            'issue_time' => '14:40:40',
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
        echo($egs->checkInvoiceCompliance($signed_invoice_string, $invoice_hash, $binary_security_token, $secret));
        echo PHP_EOL;
    }

    function uuid()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
