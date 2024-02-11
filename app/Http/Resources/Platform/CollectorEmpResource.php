<?php

namespace App\Http\Resources\Platform;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CollectorEmpResource extends JsonResource
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
            'emp_name' => @$this->emp_name,
            'emp_mobile' => @$this->emp_mobile,
            'country_code' => @$this->country_code,
        ];
    }


}
