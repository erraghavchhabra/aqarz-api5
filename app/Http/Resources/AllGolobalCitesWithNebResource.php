<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AllGolobalCitesWithNebResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        //dd($this->center->jsonSerialize()->getCoordinates());

        return [
            'city_id' => @$this->city_id,
            'city_name' => @$this->name_ar,
            'region_id' => @$this->region_id,
            'lan'=>@$this->center->jsonSerialize()->getCoordinates()[0],
            'lat'=>@$this->center->jsonSerialize()->getCoordinates()[1],
            'neighborhoods'=>@CheckPointNewResource::collection(@$this->neb),

        ];
    }
}
