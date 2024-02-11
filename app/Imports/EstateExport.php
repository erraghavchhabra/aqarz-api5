<?php

namespace App\Imports;

use App\Models\v2\Estate;
use App\Models\v3\ReportEstate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class EstateExport implements FromView, WithColumnWidths
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


    public function __construct()
    {

    }

    public function view(): View
    {

   //     $date= Carbon::parse(date('Y-m-d'));
        $date=   Carbon::now()->format('Y-m-d');

        $ids=ReportEstate::pluck('estate_id');

      //  dd($ids->toArray());
       // dd(array_search(21,$ids->toArray(),true));
/*dd(in_array('21',$ids->toArray()));
        dd(\App\Models\v3\Estate::whereIn('id',$ids->toArray())
            ->limit(20)->get());
*/


        return view('EstateDaliyExport', [
            'estate' => \App\Models\v3\Estate::where('user_id','!=',142)
                ->whereMonth('created_at', '>', 10)
            ->get()
        ]);

    }
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'D' =>NumberFormat::FORMAT_GENERAL,
            'H' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'I' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'J' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'K' => NumberFormat::FORMAT_NUMBER,
            'L' => NumberFormat::FORMAT_NUMBER,
            'Q' => NumberFormat::FORMAT_GENERAL,
            'R' => NumberFormat::FORMAT_GENERAL,

            'T' => NumberFormat::FORMAT_GENERAL,
            'Y' => NumberFormat::FORMAT_GENERAL,
            'Z' => NumberFormat::FORMAT_GENERAL,
            'AA' => NumberFormat::FORMAT_GENERAL,
            'AC' => NumberFormat::FORMAT_GENERAL,
            'AD' =>NumberFormat::FORMAT_GENERAL,
            'AE' => NumberFormat::FORMAT_GENERAL,
            'AF' => NumberFormat::FORMAT_GENERAL,

            'AI' => NumberFormat::FORMAT_GENERAL,
            'AJ' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'AK' =>NumberFormat::FORMAT_NUMBER_00,
            'Al' =>NumberFormat::FORMAT_NUMBER_00,
            'AM' => NumberFormat::FORMAT_NUMBER_00,
            'AS' => NumberFormat::FORMAT_GENERAL,
            'AU' => NumberFormat::FORMAT_GENERAL,
            'AW' => NumberFormat::FORMAT_GENERAL,
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 20,//Ad_Id
            'B' => 40,//Advertiser_character
            'C' => 40,//Advertiser_name
            'D' => 50,//Advertiser_mobile_number
            'E' => 40,//The_main_type_of_ad
            'F' => 100,//Ad_description
            'G' => 40,//Ad_subtype
            'H' => 50,//Advertisement_publication_date
            'I' => 50,//Ad_update_date
            'J' => 50,//Ad_expiration
            'K' => 20,//Ad_status
            'L' => 20,//Ad_Views
            'M' => 30,//District_Name
            'N' => 30,//City_Name
            'O' => 40,//Neighbourhood_Name
            'P' => 50,//Street_Name
            'Q' => 50,//Longitude
            'R' => 50,//Lattitude
            'S' => 20,//Furnished
            'T' => 20,//Kitchen
            'U' => 100,//Air_Condition
            'V' => 30,//facilities
            'W' => 30,//Using_For
            'X' => 30,//Property_Type
            'Y' => 50,//The_Space
            'Z' => 50,//Land_Number
            'AA' => 50,//Plan_Number
            'AC' => 20,//Number_Of_Units
            'AD' => 20,//Floor_Number
            'AE' => 20,//Unit_Number
            'AF' => 20,//Rooms_Number
            'AG' => 30,//Rooms_Type
            'AH' => 30,//Real_Estate_Facade
            'AI' => 20,//Street_Width
            'AJ' => 50,//Construction_Date
            'AK' => 20,//Rental_Price
            'Al' => 20,//Selling_Price
            'AM' => 20,//Selling_Meter_Price
            'AN' => 100,//Property limits and lenghts
            'AO' => 20,//Is there a mortgage or restriction that prevents or limits the use of the property
            'AP' => 100,//Rights and obligations over real estate that are not documented in the real estate document
            'AQ' => 100,//Information that may affect the property
            'AR' => 20,//Property disputes,Availability of elevators
            'AS' => 20,//Number of elevators
            'AT' => 20,//vailability of Parking
            'AU' => 20,//Number of parking
            'AV' => 20,//Advertiser category
            'AW' => 100,//Advertiser license number
            'AX' => 100,//Advertiser's email

        ];
    }
}
