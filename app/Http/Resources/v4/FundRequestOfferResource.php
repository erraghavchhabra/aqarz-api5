<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\JsonResource;

class FundRequestOfferResource extends JsonResource
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
            'uuid'    => $this->uuid,
            'request_id'    => $this->request_id,
            'emp_id'    => $this->emp_id,
            'instument_number'    => $this->instument_number,
            'guarantees'    => $this->guarantees,
            'beneficiary_name'    => $this->beneficiary_name,
            'beneficiary_mobile'    => $this->beneficiary_mobile,
            'status'    => $this->status,
            'estate_id'    => $this->estate_id,
            'send_offer_type'    => $this->send_offer_type,
            'contact_status'    => $this->contact_status,
            'reason'    => $this->reason,
            'app_name'    => $this->app_name,
            'is_close'    => $this->is_close,
            'provider_id'    => $this->provider_id,
            'show_count'    => $this->show_count,
            'first_show_date'    => $this->first_show_date,
            'deleted_at'    => $this->deleted_at,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'start_at'    => $this->start_at,
            'review_at'    => $this->review_at,
            'accept_review_at'    => $this->accept_review_at,
            'accepted_at'    => $this->accepted_at,
            'cancel_at'    => $this->cancel_at,
            'request_preview_date'    => $this->request_preview_date,
            'hide_estate_id'    => $this->hide_estate_id,
            'in_fav'    => $this->in_fav,
            'status_name'    => $this->status_name,
            'estate_type_name'    => $this->estate_type_name,
            'estate'    => new EstateResource($this->estate),

        ];
    }
}
