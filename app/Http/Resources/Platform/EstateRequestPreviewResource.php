<?php

namespace App\Http\Resources\Platform;

use App\Http\Resources\v4\ComfortsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EstateRequestPreviewResource extends JsonResource
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
            'id' => $this->id,
            'user_id' => @$this->user->id,
            'user_name' => @$this->user->onwer_name,
            'user_mobile' => @$this->user->mobile,
            'estate_name' => @$this->estate->estate_type_name . ' - ' . @$this->estate->operation_type_name,
            'estate_link' => @$this->estate->link,
            'time' => (string)@$this->created_at->diffForHumans(),
            'estate_type_name' => (string)@$this->estate->estate_type_name,
            'operation_type_name' => (string)@$this->estate->operation_type_name,
            'city_name' => @$this->estate->city_name,
            'neighborhood_name' => @$this->estate->neighborhood_name,
            'address' => (string)@$this->estate->full_address,
            "status" => $this->status,
            "area" => (string)@$this->estate->total_area,
            "total_price" => (string)@$this->estate->total_price,
            "created_at" => $this->created_at,
            "estate_id" => @$this->estate ? (string)@$this->estate_id : null,
            "estate_user_id" => @$this->estate->user_id ? (string)@$this->estate->user_id : null,
            'estate_comforts' => $this->estate ? count($this->estate->comforts) > 0 ? ComfortsResource::collection(@$this->estate->comforts) : null : null,
            'times' => $this->times,
            'accept_time' => $this->accept_time
        ];
    }
}
