<?php

namespace App\Http\Resources;

use App\Http\Resources\v4\estate_dataFileResource;
use App\Http\Resources\v4\estate_dataResource;
use App\Http\Resources\v4\EstateFileResource;
use App\Models\v3\AttachmentEstate;
use App\Models\v3\Attachmentestate_data;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $date = date_create($this->estate_data->created_at);
        $date = date_format($date, "Y-m-d H:i:s");
        $url = 'https://aqarz.sa/';

        $number = collect();
        if (@$this->estate_data->total_area) {
            $number->push(['name' => __("views.total_area"), 'count' => $this->estate_data->total_area, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/DqDOwFugKFRu4OQpXSBgruo1HjKPp1a8saA87Ww7.png']);
        }

        if (@$this->estate_data->lounges_number > 0) {
            $number->push(['name' => __("views.lounges"), 'count' => $this->estate_data->lounges_number .' ' . __("views.lounge") , 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/QvIar7FW5gvCHq1msziNrm2Jh4BQyotqdZMZrXVX.png']);
        }
        if (@$this->estate_data->bedroom_number > 0) {
            $number->push(['name' => __("views.bedroom"), 'count' => $this->estate_data->bedroom_number .' ' . __("views.rooms"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/0TfuZbnbFrRS4Pf1wU1Gv7LCbef4BttLpJy0nx2Z.png']);
        }
        if (@$this->estate_data->bathrooms_number > 0) {
            $number->push(['name' => __("views.bathrooms"), 'count' => $this->estate_data->bathrooms_number .' ' . __("views.bathroom"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/D9Opee0B5FQvQwav5joPwAvoYH4UFpMP5fCfVSGw.png']);
        }
        if (@$this->estate_data->boards_number > 0) {
            $number->push(['name' => __("views.boards"), 'count' => $this->estate_data->boards_number .' ' . __("views.board"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/tjXnE83kK1Jy4NT1Xubo79Rw6Ohc1AUAhIWj4qIC.png']);
        }
        if (@$this->estate_data->kitchen_number > 0) {
            $number->push(['name' =>  __("views.kitchen"), 'count' => $this->estate_data->kitchen_number .' ' . __("views.kitchens"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/shT1ioBRbq8JBmyoWOkuhwFS2Ky4NVW71Uln8eN0.png']);
        }
        if (@$this->estate_data->dining_rooms_number > 0) {
            $number->push(['name' => __("views.dining rooms"), 'count' => $this->estate_data->dining_rooms_number .' ' . __("views.rooms"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/N9yPHjVBcaeiS5RUJdmZ2z5oDJq15MEFZRdnYOto.png']);
        }


        $attachment = EstateFileResource::collection(AttachmentEstate::query()->where('estate_id', $this->estate_data->id)->get());



        return [
            'id' => $this->estate_data->id,
            'group_name' => @$this->estate_data->Groupestate_data->group_name,
            'estate_data_type_name' => @$this->estate_data->estate_type_name,
            'operation_type_name' => @$this->estate_data->operation_type_name,
            'estate_data_type_id' => @$this->estate_data->estate_type_id,
            'operation_type_id' => @$this->estate_data->operation_type_id,
            'lat' => @$this->estate_data->lat,
            'lan' => @$this->estate_data->lan,
            'status' => @$this->estate_data->status,
            'total_area' => @$this->estate_data->total_area,
            'total_price' => @$this->estate_data->total_price,
            'price_format' => number_format_short(@$this->estate_data->total_price),
            'bedroom_number' => @$this->estate_data->bedroom_number,
            'bathrooms_number' => @$this->estate_data->bathrooms_number,
            'rate' => @$this->estate_data->rate,
            'created_at' => @$date,
            'first_image' => @$this->estate_data->first_image,
            'full_address' => @$this->estate_data->full_address,
            'in_fav' => @$this->estate_data->in_fav,
            'is_hide' => @$this->estate_data->is_hide,
            'company_id' => @$this->estate_data->company_id,
            'user_id' => @$this->estate_data->user_id,
            'reason' => @$this->estate_data->reason,
            'owner_name' => @$this->estate_data->owner_name,
            'link' => @$url . 'estate_data/' . @$this->estate_data->id . '/show',
            'is_rent_installment' => @$this->estate_data->is_rent_installment,
            'rent_installment_price' => @$this->estate_data->rent_installment_price,
            'time' => Carbon::parse(@$this->estate_data->created_at)->diffForHumans(),
            'include_number' => @$number->toArray(),
            'attachment' => @$attachment,
            'estate_type_name' => @$this->estate_data->estate_type_name,
            'user_mobile' => @User::find($this->user_id)->mobile,
        ];
    }
}
