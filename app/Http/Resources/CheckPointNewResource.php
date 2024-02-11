<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckPointNewResource extends JsonResource
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
            "boundaries" => @$this->boundaries,
        ];
    }
}
