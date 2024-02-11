<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NebResource extends JsonResource
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
            'id'                 => @$this->id,
            'name'               => @$this->name_ar,
            'serial_city'        => @$this->serial_city,
            'is_selected'        => @$this->is_selected,
            'is_fund_selected'   => @$this->is_fund_selected,
            'lat'                => @$this->latitude,
            'lan'                => @$this->longitude,
            'count_neighborhood' => @$this->count_neighborhood,
            'city_state'         => @$this->state_id,
            'count_fund_request'         => @$this->count_fund_request,
            'count_app_request'         => @$this->count_app_request,
            'count_app_estate'         => @$this->count_app_estate,
        ];
    }
}
