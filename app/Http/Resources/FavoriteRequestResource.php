<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteRequestResource extends JsonResource
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
            'id'      => @$this->request_data->id,
            'operation_type_id'      => @$this->request_data->operation_type_id,
            'user_id'      => @$this->request_data->user_id,
            'request_type'      => @$this->request_data->request_type,
            'estate_type_id'      => @$this->request_data->estate_type_id,
            'area_from'      => @$this->request_data->area_from,
            'area_to'      => @$this->request_data->area_to,
            'price_from'      => @$this->request_data->price_from,
            'price_to'      => @$this->request_data->price_to,
            'room_numbers'      => @$this->request_data->room_numbers,
            'owner_name'      => @$this->request_data->owner_name,
            'owner_mobile'      => @$this->request_data->owner_mobile,
            'display_owner_mobile'      => @$this->request_data->display_owner_mobile,
            'note'      => @$this->request_data->note,
            'seen_count'      => @$this->request_data->seen_count,
            'lat'      => @$this->request_data->lat,
            'lan'      => @$this->request_data->lan,
            'city_id'      => @$this->request_data->city_id,
            'neighborhood_id'      => @$this->request_data->neighborhood_id,
            'address'      => @$this->request_data->address,
            'status'      => @$this->request_data->status,
            'created_at'      => @$this->request_data->created_at,
            'in_fav'      => @$this->request_data->in_fav,
            'operation_type_name'      => @$this->request_data->operation_type_name,
            'estate_type_name'      => @$this->request_data->estate_type_name,
            'city_name'      => @$this->request_data->city_name,
            'neighborhood_name'      => @$this->request_data->neighborhood_name,
            'time'      => @$this->request_data->time,
            'link'      => @$this->request_data->link,
        ];
    }
}
