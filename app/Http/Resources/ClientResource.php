<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'name'  => $this->name,
            'mobile'  => $this->mobile,
            'customer_character'  => $this->customer_character,
            'identification'  => $this->identification,
        ];
    }
}
