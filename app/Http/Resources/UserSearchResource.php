<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'    => $this->id ,
            'name'  => $this->name,
            'address'  => $this->address,
            'count_visit'=> $this->count_visit,
            'count_fund_offer'=> $this->count_fund_offer,
            'count_estate'=> $this->count_estate,
            'count_accept_offer'=> $this->count_accept_offer,
            'count_accept_fund_offer'=> $this->count_accept_fund_offer,
            'count_request'=> $this->count_request,
            'count_fund_pending_offer'=> $this->count_fund_pending_offer,
            'count_emp'=> $this->count_emp,
            'image'  => $this->logo,
        ];
    }
}
