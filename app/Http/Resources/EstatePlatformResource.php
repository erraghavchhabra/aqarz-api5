<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EstatePlatformResource extends JsonResource
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
            'id'=>$this->id,
            'postal_code'=>@$this->postal_code,
            'estate_name'=>@$this->estate_name,
            'instrument_date'=>@$this->instrument_date,
            'operation_type_id'=>$this->operation_type_id,
            'owner_estate_name'=>$this->owner_estate_name,
            'owner_estate_mobile'=>$this->owner_estate_mobile,
            'operation_type_name'=>$this->operation_type_name,
            'estate_type_id'=>$this->estate_type_id,
            'estate_status'=>$this->estate_status,
            'estate_type_name'=>$this->estate_type_name,
            'instrument_number'=>$this->instrument_number,
            'instrument_file'=>$this->instrument_file,
            'instrument_status'=>$this->instrument_status,
            'pace_number'=>$this->pace_number,
            'planned_number'=>$this->planned_number,
            'total_area'=>$this->total_area,
            'estate_age'=>$this->estate_age,
            'floor_number'=>$this->floor_number,
            'street_view'=>$this->street_view,
            'total_price'=>$this->total_price,
            'meter_price'=>$this->meter_price,
            'owner_management_commission'=>$this->owner_management_commission,
            'owner_management_commission_type'=>$this->owner_management_commission_type,
            'lounges_number'=>$this->lounges_number,
            'bathrooms_number'=>$this->bathrooms_number,
            'rooms_number'=>$this->rooms_number,
            'boards_number'=>$this->boards_number,
            'bedroom_number'=>$this->bedroom_number,
            'kitchen_number'=>$this->kitchen_number,
            'dining_rooms_number'=>$this->dining_rooms_number,
            'finishing_type'=>$this->finishing_type,
            'interface'=>$this->interface,
            'social_status'=>$this->social_status,
            'lat'=>$this->lat,
            'lan'=>$this->lan,
            'note'=>$this->note,
            'user_id'=>$this->user_id,
            'is_rent'=>$this->is_rent,
            'rent_type'=>$this->rent_type,
            'is_resident'=>$this->is_resident,
            'is_checked'=>$this->is_checked,
            'is_insured'=>$this->is_insured,
            'exclusive_contract_file'=>$this->exclusive_contract_file,
            'neighborhood_id'=>$this->neighborhood_id,
            'city_id'=>$this->city_id,
            'exclusive_marketing'=>$this->exclusive_marketing,
            'video'=>$this->video,
            'state_id'=>$this->state_id,
            'estate_use_type'=>$this->estate_use_type,
            'estate_dimensions'=>$this->estate_dimensions,
            'is_mortgage'=>$this->is_mortgage,
            'is_obligations'=>$this->is_obligations,
            'touching_information'=>$this->touching_information,
            'is_saudi_building_code'=>$this->is_saudi_building_code,
            'obligations_information'=>$this->obligations_information,
            'full_address'=>$this->full_address,
            'first_image'=>$this->first_image,
            'rent_price'=>$this->rent_price,
            'payment_value'=>$this->payment_value,
            'is_rent_installment'=>$this->is_rent_installment,
            'rent_installment_price'=>$this->rent_installment_price,
            'total_price_number'=>$this->total_price_number,
            'total_area_number'=>$this->total_area_number,
            'unit_counter'=>$this->unit_counter,
            'elevators_number'=>$this->elevators_number,
            'unit_number'=>$this->unit_number,
            'parking_spaces_numbers'=>$this->parking_spaces_numbers,
            'advertiser_license_number'=>$this->advertiser_license_number,
            'bank_id'=>$this->operation_type_id,
            'bank_name'=>$this->operation_type_id,
            'guard_name'=>@$this->guard_name,
            'guard_mobile'=>@$this->guard_mobile,
            'guard_identity'=>@$this->guard_identity,
            'building_number'=>$this->building_number,
            'city_name'=>$this->city_name,
            'neighborhood_name'=>$this->neighborhood_name,
            'interface_array'=>$this->interface_array,
            'estate_use_name'=>$this->estate_use_name,
            'advertiser_side_name'=>$this->advertiser_side_name,
            'state_name'=>$this->state_name,
            'finishing_type_name'=>$this->finishing_type_name,
            'additional_code'=>@$this->additional_code,
            'comfort_names'=>$this->comfort_names,
            'status'=>$this->status,

        ];
    }
}
