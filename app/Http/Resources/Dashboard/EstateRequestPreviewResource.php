<?php

namespace App\Http\Resources\Dashboard;

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
            'owner_id' => @$this->owner->id,
            'owner_name' => @$this->owner->onwer_name,
            'owner_mobile' => @$this->owner->mobile,
            'estate_name' => @$this->estate->estate_type_name . ' - ' . @$this->estate->operation_type_name,
            'estate_id' => @$this->estate->id,
            'estate_link' => @$this->estate->link,
            'time' => $this->time,
            'created_at' => $this->created_at,
        ];
    }
}
