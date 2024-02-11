<?php

namespace App\Http\Resources;

use App\Models\v3\FundRequestOfferClickUp;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClickUpRequestPrivewResourceV3 extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */

    public $collects = FundRequestOfferClickUp::class;

    public function toArray($request)
    {

        $url = 'https://aqarz.sa/';
        return [

            'id' => $this->id,
            'uuid' => $this->uuid,
            'request_id' => $this->request_id,
            'beneficiary_name' => $this->beneficiary_name,
            'beneficiary_mobile' => $this->beneficiary_mobile,
            'lat' => $this->estate->lat,
            'lan' => $this->estate->lan,
            'status' => $this->status,
            'estate_id' => $this->estate_id,
            'negotiation_price' => $this->negotiation_price,
            'price_per_ft' => $this->price_per_ft,
            'send_offer_type' => $this->send_offer_type,
            'is_paid' => $this->is_paid,
            'paid_status' => $this->paid_status,
            'priority' => $this->priority,
            'contract_status' => $this->contract_status,
            'stage_status' => $this->stage_status,
            'funding_status' => $this->funding_status,
            'preview_status' => $this->preview_status,
            'reason' => $this->reason,
            'assigned_commit' => $this->assigned_commit,
            'last_commit' => $this->last_commit,
            'assigned_id' => $this->assigned_id,
            'created_by_id' => $this->created_by_id,
            'commints_count' => $this->commints_count,
            'app_name' => $this->app_name,
            'is_close' => $this->is_close,
            'provider_id' => $this->provider_id,
            'show_count' => $this->show_count,
            'time_estimate' => $this->time_estimate,
            'first_show_date' => $this->first_show_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
            'start_date' => $this->start_date,
            'request_preview_date' => $this->request_preview_date,
            'status_name' => $this->status_name,
            'estate_type_name' => $this->estate->estate_type_name,
            'estate_type_id' => $this->estate->estate_type_id,
            'estate_full_address' => $this->estate->full_address,
            'estate_total_price' => $this->estate->total_price,
            'estate_total_area' => $this->estate->total_area,

            'estate_link' => @$this->estate->link,
            'request_link' => @$this->fund_request->link,
            'comments' => @$this->notes,




        ];
    }



}
