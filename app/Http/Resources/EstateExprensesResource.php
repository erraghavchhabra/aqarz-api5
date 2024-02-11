<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EstateExprensesResource extends JsonResource
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
            'type_name' => @$this->type_name,
            'price' => $this->price,
            'rent_contract_id' => $this->rent_contract_id,
            'type' => $this->type,
            'statement_note' => $this->statement_note,
            'user_name' => @$this->user->name,
            'user_id' => @$this->user->id,
            'estate_id' => @$this->estate->id,
            'estate_name' => @$this->estate->estate_name,
            'estate_group_name' => @$this->estate->estate_group->group_name,
            'tent_name' => @$this->rent_contract->tent->name,
            'tent_id' => @$this->rent_contract->tent->id,
            'due_date' => @$this->due_date,
            'created_at' => @$this->created_at,


        ];
    }


}
