<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferDateDataResource extends JsonResource
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
            'estate_id' => @$this->estate->id,
            'uuid' => @$this->uuid,
            'nid' => null ,
            'estate_load_date' => @$this->estate->created_at,
            'accept_offer_date' => @$this->status=='accepted_customer'?$this->updated_at:null,
            'request_preview_offer_date' => @$this->status=='sending_code'?$this->updated_at:null,

            'real_estate_broker_name' => @$this->provider->name,
            'real_estate_broker_mobile' => @$this->provider->mobile,
            'file_date' => @$this->estate->created_at,


        ];
    }
}
