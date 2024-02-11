<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RentContractResource extends JsonResource
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
            'start_date' => @$this->start_date,
            'end_date' => @$this->end_date,

//            'tent_id'=> @$this->tent_id,
//            'user_id'=> @$this->user_id,
//            'estate_id'=> @$this->estate_id,
//            'payment_type'=> @$this->payment_type,
            'contract_number'=> @$this->contract_number,
//            'date_of_writing_the_contract'=> @$this->date_of_writing_the_contract,
//            'additional_contract_terms'=> @$this->additional_contract_terms,

//            'annual_increase'=> @$this->annual_increase,
//            'refundable_insurance'=> @$this->refundable_insurance,
//            'rental_commission'=> @$this->rental_commission,
//            'maintenance_and_services'=> @$this->maintenance_and_services,
//            'electricity'=> @$this->electricity,
//            'waters'=> @$this->waters,
//            'cleanliness'=> @$this->cleanliness,
//            'property_management'=> @$this->property_management,
            'rent_total_amount'=> @$this->rent_total_amount,
//            'payment_value' => @$this->payment_value,
//            'count_month' => @$this->count_month,
//            'services'=> @$this->services,
            'created_at' => @$this->created_at,
            'estate_name' => @$this->estate->estate_name,
            'estate_group_name' => @$this->estate->estate_group->group_name,
            'tent_name' => @$this->tent->name,
//            'tent_identification' => @$this->tent->identification,
//            'tent_mobile' => @$this->tent->identification,
            'status' => @$this->status,

            'contract_interval' => @$this->contract_interval,


//            'rent_contract_financial_movements_total_due' => @$this->rent_contract_financial_movements()->sum('owed_amount'),
//            'rent_contract_financial_movements_paid' => @$this->rent_contract_financial_movements()->sum('paid_amount'),
//            'rent_contract_financial_movements_remaining' => @$this->rent_contract_financial_movements()->sum('remaining_amount'),
//            'total_estate_expenses_due' => @$this->estate_expenses()->where('status','not_paid')->sum('price'),
//            'total_estate_receivables' => @$this->estate_expenses()->where('status','not_paid')->sum('price')+$this->rent_invoices()
//            ->where('status','not_paid')->sum('owed_amount'),
//            'rental_contracts_notes' => @$this->rent_contract_notes,
//            'rent_invoices' => @$this->rent_invoices,
//            'rent_invoices' => null,
           // 'tent' => @$this->tent,
//            'create_by_name' => @$this->user->name,
//            'customer_character' => @$this->tent->customer_character,
//            'owner_name' => @$this->user->onwer_name,
//            'owner_identification' => @$this->user->identity,
//            'owner_mobile' => @$this->user->mobile,

           // 'owner_identity' => @$this->user->onwer_name,




        ];
    }


}
