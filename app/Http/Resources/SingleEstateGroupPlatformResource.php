<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleEstateGroupPlatformResource extends JsonResource
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
            'owner_estate_name'=>$this->owner_estate_name,
            'group_name'=>$this->group_name,
            'postal_code'=>$this->postal_code,
            'additional_code'=>$this->additional_code,
            'owner_estate_mobile'=>$this->owner_estate_mobile,
            'instrument_number'=>$this->instrument_number,
            'instrument_file'=>$this->instrument_file,
            'instrument_status'=>$this->pace_number,
            'unit_counter'=>$this->unit_counter,
            'unit_number'=>$this->unit_number,
            'image'=>$this->image,
            'street_name'=>$this->street_name,


            'owner_management_commission'=>$this->owner_management_commission,
            'owner_management_commission_type'=>$this->owner_management_commission_type,

            'lat'=>$this->lat,
            'lan'=>$this->lan,
            'user_id'=>$this->user_id,
            'full_address'=>$this->full_address,
            'first_image'=>$this->image,
            'bank_id'=>$this->operation_type_id,
            'bank_name'=>$this->operation_type_id,
            'guard_name'=>$this->guard_name,
            'guard_mobile'=>$this->guard_mobile,
            'guard_identity'=>$this->guard_identity,
            'building_number'=>$this->building_number,
            'city_name'=>$this->city_name,
            'neighborhood_name'=>$this->neighborhood_name,
            'count_estate'=>@$this->count_estate,
            'group_estate_notes'=>@$this->group_estate_notes,
            'owner_birth_day'=>@$this->owner_birth_day,
            'interface'=>@$this->interface,
            'rent_contract'=>@RentContractResource::collection($this->rent_contract)->response()->getData(true)

        ];
    }
}
