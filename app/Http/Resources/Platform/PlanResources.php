<?php

namespace App\Http\Resources\Platform;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResources      extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
            'contract_number' => $this->contract_number,
            'color' => $this->color,
            'type' => $this->type,
        ];
    }
}
