<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TentPayUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $url = 'https://aqarz.sa/';
        return [
            'id' => $this->id,
            'name' => @$this->name,
            'customer_character' => @$this->customer_character,
            'created_at' => @$this->created_at,




        ];
    }


}
