<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClickUpReviewTable extends JsonResource
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
            'offer_id' => @$this->id,
            'created_at' => @$this->created_at,
            'time_at' => @$this->created_at->format('H:i:s'),
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
