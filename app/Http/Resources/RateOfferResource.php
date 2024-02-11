<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RateOfferResource extends JsonResource
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
            'day' => $this->day,
            'price' => $this->price,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'time' => $this->time,
            'user_id' => @$this->user->id,
            'user_name' => @$this->user->onwer_name,
            'user_logo' => @$this->user->logo,
            'user_mobile' => @$this->user->mobile,

        ];
    }


}
