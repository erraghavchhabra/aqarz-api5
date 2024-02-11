<?php

namespace App\Http\Resources\Platform;

use Illuminate\Http\Resources\Json\JsonResource;

class MySubscriptionResource   extends JsonResource
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
            'plan_id' => $this->plan_id,
            'price' => $this->price,
            'contract_number' => $this->contract_number,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'user_id' => $this->user_id,
            'contract_number_used' => $this->contract_number_used,
            'time' => $this->time,
            'plan_name' => $this->plan_name,
        ];
    }
}
