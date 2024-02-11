<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClickFirstTable extends JsonResource
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
            'id' => @$this->id,
            'beneficiary_name' => @$this->beneficiary_name,
            'beneficiary_mobile' => @$this->beneficiary_mobile,
            'estate_type_name' => @$this->estate_type_name,
            'status_name' => @$this->status_name,
            'status' => @$this->status,
            'city_name' => @$this->city_name,
            'neighborhood_name' => @$this->neighborhood_name,
            'EstatePrice' => @$this->estate_price_range,
           // 'comments'   => $this->notes,

        ];
    }
}
