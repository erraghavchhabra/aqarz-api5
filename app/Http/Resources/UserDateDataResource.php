<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDateDataResource extends JsonResource
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
            'id'    => $this->id ,
            'name'  => $this->name,
            'email'  => $this->email,
            'mobile'  => $this->mobile,
            'created_date'  => $this->created_at,
            'address'  => $this->address,
            'count_fund_offer'  => $this->count_fund_offer,
            'count_fund_request'  => $this->count_fund_request,
            'count_preview_fund_offer'  => $this->count_preview_fund_offer,
            'is_pay'  => @$this->is_pay=='1'?__('views.yes'):__('views.no'),
            'is_certified'  => @$this->is_certified=='1'?__('views.yes'):__('views.no'),
            'is_fund_certified'  => @$this->is_fund_certified=='1'?__('views.yes'):__('views.no'),
            'user_plan_price'  => @$this->user_plan->total,
            'user_plan_interval'  => @$this->user_plan->plan->name_ar,
            'user_plan_start_date'  => @$this->user_plan->date_start,
            'user_plan_end_date'  => @$this->user_plan->date_end,
        ];
    }
}
