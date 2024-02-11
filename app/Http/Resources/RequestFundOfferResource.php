<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestFundOfferResource extends JsonResource
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
            'uuid'   => $this->uuid,
            'instument_number'   => $this->instument_number,
            'guarantees'   => $this->guarantees,
            'beneficiary_name'   => $this->beneficiary_name,
            'beneficiary_mobile'   => $this->beneficiary_mobile,
            'estate_id'   => $this->estate->id,
            'estate_type_name'   => @$this->estate->estate_type_name,
            'sale_type_name'   => @$this->estate->is_rent==1?__('views.rent'):__('بيع'),
            'estate_pace_number'   => $this->estate->pace_number,
            'estate_planned_number'   => $this->estate->planned_number,
            'estate_total_area'   => $this->estate->total_area,
            'estate_estate_age'   => $this->estate->estate_age,
            'estate_floor_number'   => $this->estate->floor_number,
            'estate_street_view'   => $this->estate->street_view,
            'estate_total_price'   => $this->estate->total_price,
            'estate_meter_price'   => $this->estate->meter_price,
            'estate_lounges_number'   => $this->estate->lounges_number,
            'estate_rooms_number'   => $this->estate->rooms_number,
            'estate_bathrooms_number'   => $this->estate->bathrooms_number,
            'estate_boards_number'   => $this->estate->boards_number,
            'estate_kitchen_number'   => $this->estate->kitchen_number,
            'full_address'   => $this->estate->full_address,
            'estate_dining_rooms_number'   => $this->estate->dining_rooms_number,
            'estate_finishing_type'   => __('views.'.$this->estate->finishing_type),
           // 'estate_interface'   => __('views.'.$this->estate->interface),
            'estate_interface'   => $this->estate->interface,
           // 'estate_lat'   => $this->estate->lat,
          //  'estate_lan'   => $this->estate->lan,
            'estate_note'   => $this->estate->note,
            'estate_is_resident'   => $this->estate->is_resident==0?__('views.no'):__('views.yes'),
            'estate_is_checked'   => $this->estate->is_checked==0?__('views.no'):__('views.yes'),
            'estate_is_insured'   => $this->estate->is_insured==0?__('views.no'):__('views.yes'),

            'estate_city'   => $this->estate->city_name,
            'in_fav'   => $this->in_fav,
            'estate_neighborhood'   => $this->estate->neighborhood_name,
            'estate_exclusive_contract_file'   => $this->estate->exclusive_contract_file,
            'status_name'   => $this->status_name,
            'status'   => $this->status,
            'reason'   => $this->status=='close'?@$this->reason:null,
            'date'   => @$this->created_at,
            'show_count'   => @$this->show_count,
            'first_show_date'   => @$this->first_show_date,
            'request_preview_date'   => @$this->request_preview_date,
            'app_name'   => 'عقارز',
            'provider_name'   => @$this->provider->name,
            'provider_mobile'   => @$this->provider->mobile,
            'provider_id'   => @$this->provider->id,
            'estate_comfort_way'   => (ComfortResource::collection($this->estate->comforts)),
            'estate_attachment'   => (EstateFileResource::collection($this->estate->EstateFile)),
         //   'estate_comfort_way'   => (new ComfortResource($this->estate->comforts)),

         //   'estate_attachment'   => (new EstateFileResource($this->estate->EstateFile)),

        ];
    }
}
