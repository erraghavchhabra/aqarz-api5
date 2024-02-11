<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteFundResource extends JsonResource
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
            'id'      => @$this->fund_data->id,
            'uuid'      => @$this->fund_data->uuid,
            'estate_type_id'      => @$this->fund_data->estate_type_id,
            'estate_status'      => @$this->fund_data->estate_status,
            'area_estate_id'      => @$this->fund_data->area_estate_id,
            'dir_estate_id'      => @$this->fund_data->dir_estate_id,
            'estate_price_id'      => @$this->fund_data->estate_price_id,
            'street_view_id'      => @$this->fund_data->street_view_id,
            'offer_numbers'      => @$this->fund_data->offer_numbers,
            'fund_request_neighborhoods'      => @$this->fund_data->fund_request_neighborhoods,
            'count_offers'      => @$this->fund_data->count_offers,
            'count_expired_offer'      => @$this->fund_data->count_expired_offer,
            'count_deleted_offer'      => @$this->fund_data->count_deleted_offer,
            'count_active_offer'      => @$this->fund_data->count_active_offer,
            'city_id'      => @$this->fund_data->city_id,
            'state_id'      => @$this->fund_data->state_id,
            'assigned_id'      => @$this->fund_data->assigned_id,
            'neighborhood_id'      => @$this->fund_data->neighborhood_id,
            'rooms_number_id'      => @$this->fund_data->rooms_number_id,
            'status'      => @$this->fund_data->status,
            'estate_type_name'      => @$this->fund_data->estate_type_name,
            'dir_estate'      => @$this->fund_data->dir_estate,
            'street_view_range'      => @$this->fund_data->street_view_range,
            'estate_price_range'      => @$this->fund_data->estate_price_range,
            'area_estate_range'      => @$this->fund_data->area_estate_range,
            'city_name'      => @$this->fund_data->city_name,
            'neighborhood_name'      => @$this->fund_data->neighborhood_name,
            'beneficiary_name'      => @$this->fund_data->beneficiary_name,
            'beneficiary_mobile'      => @$this->fund_data->beneficiary_mobile,
            'is_send_beneficiary_information'      => @$this->fund_data->is_send_beneficiary_information,
            'link'      => @$this->fund_data->link,
            'estate_type_icon'      => @$this->fund_data->estate_type_icon,
            'created_at'      => @$this->fund_data->created_at,
            'estate_type_name_web'      => @$this->fund_data->estate_type_name_web,
            'city_name_web'      => @$this->fund_data->city_name_web,
            'status_name'      => @$this->fund_data->status_name,
            'time'      => @$this->fund_data->time,


        ];
    }
}
