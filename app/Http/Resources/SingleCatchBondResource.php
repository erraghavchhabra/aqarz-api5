<?php

namespace App\Http\Resources;

use App\Http\Resources\Platform\CollectorEmpResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SingleCatchBondResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->collector_emp)
        {
            $collector_emp = [
                'emp_name' => @$this->collector_emp->emp_name,
                'emp_mobile' => @$this->collector_emp->emp_mobile,
                'country_code' => @$this->collector_emp->country_code,
            ];
        }else{
            $collector_emp = [
                'emp_name' => @$this->user->name,
                'emp_mobile' => @$this->user->mobile,
                'country_code' => @$this->user->country_code,
            ];
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
            'experiences_id' => $this->experiences_id,
            'invoice_id' => $this->invoice_id,
            'client_name' => $this->client_name,
            'client_mobile' => $this->client_mobile,
            'client_identity' => $this->client_identity,
            'client_character' => @$this->client->customer_character,
            'publication_date' => $this->publication_date,
            'contract_id' => $this->contract_id,
            'estate_id' => $this->estate_id,
            'collector_emp_id' => $this->collector_emp_id,
            'interval_from_date' => $this->interval_from_date,
            'interval_to_date' => $this->interval_to_date,
            'amount' => $this->amount,
            'statement' => $this->statement,
            'status' => $this->status,
            'dues_type' => $this->dues_type,
            'for_owner' => $this->for_owner,
            'payment_type' => $this->payment_type,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'owner_movment' => $this->owner_movment,
            'estate_deposit' => $this->estate_deposit,
            'customer_character' => $this->customer_character,
            'estate_name' => @$this->estate->estate_name,
            'estate_owner' => @$this->estate->user->onwer_name,
            'estate_group_name' => @$this->estate->estate_group->group_name,
            'collector_emp' => $collector_emp,
        ];
    }


}
