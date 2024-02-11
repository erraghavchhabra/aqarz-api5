<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EstateGroupNotesResource extends JsonResource
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
            'user_name' => @$this->user->name,
            'user_id' => @$this->user->id,
            'estate_group_id' => @$this->group_estate_id,
            'estate_group_name' => @$this->GroupEstate->group_name,
            'notes' => @$this->notes,
            'created_at' => @$this->created_at,




        ];
    }


}
