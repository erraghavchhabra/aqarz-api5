<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmpUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'emp_name' => $this->emp_name,
            'emp_mobile' => $this->emp_mobile,
            'status' => $this->status,
            'from_user_table_email' => @$this->user_information->email,
            'from_user_table_count_estate' => $this->status == 'active' ?  @$this->user_information->count_estate : 0,
            'from_user_table_id' => @$this->user_information->id,
            'from_user_table_image' => @$this->user_information->logo,
            'from_user_table_name' => @$this->user_information->name,
            'from_user_table_onwer_name' => @$this->user_information->onwer_name,
            'from_user_table_status' => @$this->user_information->status,
            'from_user_table_is_employee' => @$this->user_information->is_employee,
            'have_user_record' => @$this->user_information?1:0,

        ];
    }
}
