<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PayContractsResource extends JsonResource
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
            'payer_id'=> @$this->payer_id,
            'user_id'=> $this->user_id,
            'estate_id'=> $this->estate_id,
            'total_price'=> $this->total_price,
            'date_of_writing_the_contract'=> $this->date_of_writing_the_contract,
            'additional_contract_terms'=> $this->additional_contract_terms,
            'created_at' => @$this->created_at,
            'create_by' => @$this->create_by,
            'estate_name' => @$this->estate->estate_name,
            'estate_group_name' => @$this->estate->estate_group->group_name,
            'payer_name' => @$this->payer->name,
            'payer_mobile' => @$this->payer->mobile,
            'identification' => @$this->payer->identification,
            'estate_type_name' => @$this->estate->estate_type_name,
            'contract_number' => @$this->contract_number,
         //   'estate_group_name' => @$this->estate->estate_group->,







        ];
    }


}
