<?php

namespace App\Imports;

use App\Models\v2\RequestFund;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class FundRequestExport implements FromView, WithColumnWidths
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */


    /*
    public function collection()
    {
        return RequestFund::with('neighborhood','offers')->get();
    }


    public function map($invoice): array
    {
        return [
           'id'=>$invoice->id,
           'date'=>Date::dateTimeToExcel($invoice->created_at),
        ];


    }*/

    private $date;

    public function __construct(string $date = null)
    {
        $this->date = $date;
    }

    public function view(): View
    {
        return view('fundRequest', [
            'requests' => RequestFund::with('neighborhood', 'offers')
//                ->whereDate('created_at', $this->date)
                ->get()
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 50,
            'G' => 30,
            'H' => 40,
            'I' => 40,
            'J' => 20,
            'K' => 20,
        ];
    }
}
