<?php

namespace App\Imports;

use App\City;
use Maatwebsite\Excel\Concerns\ToModel;

class CityImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new \App\Models\City([

            'name_ar'     => $row[1],
            'name_en'     => $row[1],
            'serial_city'     => $row[3],

        ]);
    }
}
