<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestOfferResource extends JsonResource
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
            'request_id'    => $this->request_id,
            'user_id'    => $this->user_id,
            'instument_number'    => $this->instument_number,
            'guarantees'    => $this->guarantees,
            'beneficiary_name'    => $this->beneficiary_name,
            'beneficiary_mobile'    => $this->beneficiary_mobile,
            'status'    => $this->status,
            'estate_id'    => $this->estate_id,
            'deleted_at'    => $this->deleted_at,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'rate'    => $this->rate,
            'estate'    => new EstateResource($this->estate),
            'times' => $this->times,
            'accept_time' => $this->accept_time
        ];
    }
}
