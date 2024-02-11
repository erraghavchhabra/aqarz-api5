<?php

namespace App\Imports;

use App\Models\v2\Estate;
use App\Models\v3\ReportEstate;
use App\User;
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

class UserExport implements FromView, WithColumnWidths
{



    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */



    public function __construct()
    {

    }

    public function view(): View
    {


        return view('userExport', [
            'users' => User::query()->where('type' , 'provider')->get()
        ]);

    }
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 20,//id
            'B' => 40,//name
            'C' => 40,//mobile
            'D' => 40,//email
            'E' => 40,//fal_license_number
            'F' => 40,//fal_license_expiry
            'G' => 40,//created_at
            'H' => 40,//estate count
        ];
    }
}
