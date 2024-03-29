<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\JsonResource;

class ComfortsResource extends JsonResource
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
            'name'    => $this->name,
            'icon'    => $this->icon,

        ];
    }
}
