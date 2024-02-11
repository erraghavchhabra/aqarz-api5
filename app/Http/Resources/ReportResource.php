<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportResource extends JsonResource
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
            'report_type' => $this->report_type,
            'estate_id' => $this->estate_id,
            'body' => $this->body,
            'user_id' => $this->operation_type_id,
            'created_at' => $this->created_at,
            'reporter' => @$this->user,
            'estate' => @$this->estate()->with('user')->first(),
          //  'estate_owner' => @$this->estate->user,



        ];
    }


}
