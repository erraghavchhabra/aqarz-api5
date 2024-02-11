<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EstateNotesResource extends JsonResource
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
            'user_name' => @$this->user->name,
            'user_id' => @$this->user->id,
            'estate_id' => @$this->estate->id,
            'estate_name' => @$this->estate->estate_type_name.'_'.@$this->estate->operation_type_name,
            'notes' => @$this->notes,
            'created_at' => @$this->created_at,




        ];
    }


}
