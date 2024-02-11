<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EstateResourceV3 extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $date = date_create($this->created_at);
        $date = date_format($date, "Y-m-d H:i:s");
        $url = 'https://aqarz.sa/';
        return [
            'id' => $this->id,
            'group_name'=>@$this->GroupEstate->group_name,
            'estate_type_name' => $this->estate_type_name,
            'operation_type_name' => $this->operation_type_name,
            'estate_type_id' => $this->estate_type_id,
            'operation_type_id' => $this->operation_type_id,
            'lat' => $this->lat,
            'lan' => $this->lan,
            'status' => $this->status,
            'total_area' => $this->total_area,
            'total_price' => $this->total_price,
            'bedroom_number' => $this->bedroom_number,
            'bathrooms_number' => $this->bathrooms_number,

            'rate' => @$this->rate,
         //   'created_at' => @$this->created_at,
            'created_at' => @$date,
            'first_image' => @$this->first_image,
            'full_address' => @$this->full_address,
            'in_fav' => @$this->in_fav,
            'is_hide' => @$this->is_hide,
            'company_id' => @$this->company_id,
            'user_id' => @$this->user_id,
            'reason' => @$this->reason,
            'link' => @$url . 'estate/' . $this->id . '/show',
            'is_rent_installment' =>$this->is_rent_installment,
            'rent_installment_price' =>$this->rent_installment_price



            //   'estate_comfort_way'   => (ComfortResource::collection($this->estate->comforts)),
            //  'estate_attachment'   => (EstateFileResource::collection($this->estate->EstateFile)),

            //   'estate_comfort_way'   => (new ComfortResource($this->estate->comforts)),



        ];
    }


}
