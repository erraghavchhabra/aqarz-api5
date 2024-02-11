<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {


        return [
            "district_id" => @$this->district_id,
            "city_id" => @$this->city_id,
            "region_id" => @$this->region_id,
            "name_ar" => @$this->name_ar,
            "name_en" => @$this->name_en,
            "name" => @$this->name,
            "center" => @$this->center,
            "district_id_string" => (string) $this->district_id,
            'full_address' =>  $this->name . ',' . @$this->city->name . ',' . @$this->city->region->name,
        ];
    }
}
