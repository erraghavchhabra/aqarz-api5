<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OfficesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $url = 'https://aqarz.sa/';


        return [
            'id'                 => @$this->id,
            'name'               => @$this->name,
            'onwer_name'               => @$this->onwer_name,
            'count_fund_offer'               => @$this->count_fund_offer,
            'count_preview_fund_offer'               => @$this->count_preview_fund_offer,
            'count_accept_fund_offer'               => @$this->count_accept_fund_offer,
            'created_at'               => @$this->created_at,
            'mobile'        => @$this->mobile,
            'rate'        => @$this->rate,
            'dash_estate_count'        => @$this->dash_estate_count,
            'dash_fund_offer_count'        => @$this->dash_fund_offer_count,
            'is_pay'        => @$this->is_pay ,
            'is_certified '        => @$this->is_certified ,
            'country_code'        => @$this->country_code,
            'link'        => @$url . $this->user_name,
            'dash_offer_count'        => @$this->dash_offer_count,

        ];
    }
}
