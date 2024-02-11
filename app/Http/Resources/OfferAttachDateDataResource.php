<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferAttachDateDataResource extends JsonResource
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
            'estate_id' => @$this->estate->id,
            'estate_load_date' => @$this->estate->created_at,
            'exclusive_contract_file' => @$this->estate->exclusive_contract_file,
            'estate_planned'   => @(EstateFileResource::collection($this->estate->plannedFile)),
            'estate_attachment'   => @(EstateFileResource::collection($this->estate->EstateFile)),


        ];
    }
}
