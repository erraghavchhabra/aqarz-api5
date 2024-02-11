<?php

namespace App\Http\Resources\v4;

use App\Models\v3\AttachmentEstate;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MyEstateResourse extends JsonResource
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

        $number = collect();
        if ($this->total_area) {
            $number->push([ 'name' => __("views.total area") ,  'count' => $this->total_area, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/DqDOwFugKFRu4OQpXSBgruo1HjKPp1a8saA87Ww7.png']);
        }

        if ($this->lounges_number > 0) {
            $number->push([ 'name' => __("views.lounges") , 'count' => $this->lounges_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/QvIar7FW5gvCHq1msziNrm2Jh4BQyotqdZMZrXVX.png']);
        }
        if ($this->bedroom_number > 0) {
            $number->push(['name' => __("views.bedroom"), 'count' => $this->bedroom_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/0TfuZbnbFrRS4Pf1wU1Gv7LCbef4BttLpJy0nx2Z.png']);
        }
        if ($this->bathrooms_number > 0) {
            $number->push([ 'name' => __("views.bathrooms"), 'count' => $this->bathrooms_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/D9Opee0B5FQvQwav5joPwAvoYH4UFpMP5fCfVSGw.png']);
        }
        if ($this->boards_number > 0) {
            $number->push(['name' => __("views.boards"), 'count' => $this->boards_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/tjXnE83kK1Jy4NT1Xubo79Rw6Ohc1AUAhIWj4qIC.png']);
        }
        if ($this->kitchen_number > 0) {
            $number->push(['name' => __("views.kitchen"), 'count' => $this->kitchen_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/shT1ioBRbq8JBmyoWOkuhwFS2Ky4NVW71Uln8eN0.png']);
        }
        if ($this->dining_rooms_number > 0) {
            $number->push(['name' => __("views.dining rooms") , 'count' => $this->dining_rooms_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/N9yPHjVBcaeiS5RUJdmZ2z5oDJq15MEFZRdnYOto.png']);
        }

        $attachment = EstateFileResource::collection(AttachmentEstate::query()->where('estate_id', $this->id)->get());

        return [
            'id' => $this->id,
            'group_name' => @$this->GroupEstate->group_name,
            'estate_type_name' => $this->estate_type_name,
            'operation_type_name' => $this->operation_type_name,
            'estate_type_id' => $this->estate_type_id,
            'operation_type_id' => $this->operation_type_id,
            'lat' => $this->lat,
            'lan' => $this->lan,
            'status' => $this->status,
            'total_area' => $this->total_area,
            'total_price' => $this->total_price,
            'price_format' => number_format_short($this->total_price),
            'bedroom_number' => $this->bedroom_number,
            'bathrooms_number' => $this->bathrooms_number,
            'rate' => @$this->rate,
            'created_at' => @$date,
            'first_image' => @$this->first_image,
            'full_address' => @$this->full_address,
            'in_fav' => @$this->in_fav,
            'is_hide' => @$this->is_hide,
            'company_id' => @$this->company_id,
            'user_id' => @$this->user_id,
            'reason' => @$this->reason,
            'link' => @$url . 'estate/' . $this->id . '/show',
            'is_rent_installment' => $this->is_rent_installment,
            'rent_installment_price' => $this->rent_installment_price,
            'time' => Carbon::parse($this->created_at)->diffForHumans(),
            'last_update' => Carbon::parse($this->updated_at)->diffForHumans(),
            'include_number' => $number->toArray(),
            'attachment' => $attachment,
        ];
    }


}
