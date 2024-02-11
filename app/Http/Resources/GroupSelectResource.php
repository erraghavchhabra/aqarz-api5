<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupSelectResource extends JsonResource
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
            'guard_name' => $this->guard_name,
            'guard_mobile' => $this->guard_mobile,
            'guard_identity' => $this->guard_identity,
            'group_name' => $this->group_name,
            'estate_count' => @$this->estate->count(),
            'city_name' => @$this->city_name,





        ];
    }


}
