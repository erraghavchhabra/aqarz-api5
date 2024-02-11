<?php

namespace App\Imports;

use App\Neighborhood;
use Maatwebsite\Excel\Concerns\ToModel;

class NeighborhoodImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new \App\Models\Neighborhood([
            'name_ar'     => $row[2],
            'name_en'     => $row[2],
            'city_id'     => '0',
            'neighborhood_serial'     => $row[4],
        ]);
    }
}
