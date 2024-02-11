<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EstateSelectSettingResource extends JsonResource
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
            'id' => $this->id,
            'estate_name' => $this->estate_name,
            'estate_type_name' => $this->estate_type_name,
            'user_id' => $this->user_id,
            'owner_name' => $this->user->onwer_name,


        ];
    }
}
