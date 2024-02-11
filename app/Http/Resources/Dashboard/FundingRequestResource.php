<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class FundingRequestResource extends JsonResource
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
            'user_id' => @$this->user->id,
            'user_name' => @$this->user->name,
            'personal_id_number' => $this->personalIDNumber,
            'personal_name' => $this->personalName,
            'personal_mobile_number' => $this->personalMobileNumber,
            'personal_monthly_net_salary' => $this->personalMonthlyNetSalary,
            'employer_name' => $this->employerName,
            'employer_type' => $this->employerType,
            'real_estate_product_type' => $this->realEstateProductType,
            'real_estate_property_information' => $this->realEstatePropertyInformation,
            'real_estate_property_price' => $this->realEstatePropertyPrice,
            'rea_estate_property_age' => $this->realEstatePropertyAge,
            'lead_id' => $this->leadID,
            'request_id' => $this->requestID,
            'status_description' => $this->statusDescription,
            'status_code' => $this->statusCode,
        ];
    }
}
