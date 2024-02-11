<?php

namespace App\Http\Resources\Platform;

use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
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
            'id' => $this->district_id,
            'city_id' => $this->city_id,
            'city_name' => @$this->city->name,
            'region_id' => $this->region_id,
            'region_name' => @$this->region->name,
            'name' => $this->name,
            'center' => $this->center,
            'address' => $this->full_name,
        ];
    }
}
