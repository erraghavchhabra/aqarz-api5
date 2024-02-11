<?php

namespace App\Imports;

use App\City;
use App\Models\v3\ReportEstate;
use Maatwebsite\Excel\Concerns\ToModel;

class EstateImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ReportEstate([

            'estate_id'     => $row[0],


        ]);
    }
}
