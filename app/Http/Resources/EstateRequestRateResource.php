<?php

namespace App\Http\Resources;

use App\Http\Resources\v4\EstateResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EstateRequestRateResource extends JsonResource
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
            'id' => $this->id,
            'estate_type_id' => $this->estate_type_id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'note' => $this->note,
            'lat' => $this->lat,
            'lan' => $this->lan,
            'address' => $this->address,
            'status' => $this->status,
            'operation_type_id' => $this->operation_type_id,
            'user_id' => $this->user_id,
            'estate_id' => $this->estate_id,
            'purpose_evaluation' => $this->purpose_evaluation,
            'entity_evaluation' => $this->entity_evaluation,
            'area' => $this->area,
            'estate_type_name' => $this->estate_type_name,
            'operation_type_name' => $this->operation_type_name,
            'estate_use_type' => __('views.' . $this->estate_use_type),
            'time' => $this->created_at->diffForHumans(),
            'estate' => new EstateResource($this->estate),
            'have_offer' => $this->have_offer,
            'user_offer' => new RateOfferResource($this->offer_user),
        ];
    }
}
