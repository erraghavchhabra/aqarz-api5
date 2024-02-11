<?php

namespace App\Http\Resources\v4;

use App\Models\v3\AttachmentEstate;
use App\Models\v3\Estate;
use App\Models\v3\Favorite;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SingleEstateResource extends JsonResource
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

        if ($this->operation_type_id == 2) {
            $number->push(['name' => __("views.is rent installment"), 'value' => (string)$this->is_rent_installment == 1 ? __('views.yes1') : __('views.no1'), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/kSCBdRRWLbzkNo5lNiZIh3gfo9mJyVZ5D4TLzTjC.png']);
        }

        if ($this->operation_type_id == 2 && $this->rent_installment_price > 0) {
            $number->push(['name' => __("views.rent installment price"), 'value' => (string)$this->rent_installment_price, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/1aYSUF6wRCVkqFRQ2Ta6WUizhj1vFn2uYYbc2uxD.png']);
        }


        if ($this->floor_number) {
            $number->push(['name' => __("views.floor number"), 'value' => (string)$this->floor_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/oWna0O5Hzk3jEfKu0a8wLOHYhtyjnuGCVeMTHhY2.png']);
        }

        if ($this->lounges_number > 0) {
            $number->push(['name' => __("views.lounges"), 'value' => (string)$this->lounges_number . ' ' . __("views.lounge"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/QvIar7FW5gvCHq1msziNrm2Jh4BQyotqdZMZrXVX.png']);
        }

        if ($this->bedroom_number > 0) {
            $number->push(['name' => __("views.bedroom"), 'value' => (string)$this->bedroom_number . ' ' . __("views.rooms"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/0TfuZbnbFrRS4Pf1wU1Gv7LCbef4BttLpJy0nx2Z.png']);
        }
        if ($this->bathrooms_number > 0) {
            $number->push(['name' => __("views.bathrooms"), 'value' => (string)$this->bathrooms_number . ' ' . __("views.bathroom"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/D9Opee0B5FQvQwav5joPwAvoYH4UFpMP5fCfVSGw.png']);
        }
        if ($this->boards_number > 0) {
            $number->push(['name' => __("views.boards"), 'value' => (string)$this->boards_number . ' ' . __("views.board"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/tjXnE83kK1Jy4NT1Xubo79Rw6Ohc1AUAhIWj4qIC.png']);
        }
        if ($this->kitchen_number > 0) {
            $number->push(['name' => __("views.kitchen"), 'value' => (string)$this->kitchen_number . ' ' . __("views.kitchens"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/shT1ioBRbq8JBmyoWOkuhwFS2Ky4NVW71Uln8eN0.png']);
        }
        if ($this->dining_rooms_number > 0) {
            $number->push(['name' => __("views.dining rooms"), 'value' => (string)$this->dining_rooms_number . ' ' . __("views.rooms"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/N9yPHjVBcaeiS5RUJdmZ2z5oDJq15MEFZRdnYOto.png']);
        }


        if ($this->estate_dimensions > 0) {
            $number->push(['name' => __("views.estate dimensions"), 'value' => (string)$this->estate_dimensions . ' ' . __("views.m"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/NSusBijjv2BUBNmBDa3RW3Cx31pv5Nua9YEsdVZF.png']);
            $explode = explode('*', $this->estate_dimensions);
            if (count($explode) > 1) {
                $number->push(['name' => __("views.estate width"), 'value' => (string)$explode[0] . ' ' . __("views.m"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/KR8A17fN5gX0SKsIxazW453GrxpJHSkQ5R8AXWzF.png']);
                $number->push(['name' => __("views.estate length"), 'value' => (string)$explode[1] . ' ' . __("views.m"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/iqRWIrws2lsG3ozqEWpDJSlylky8NkX7vtuv8VbW.png']);

            } else {
                $number->push(['name' => __("views.estate width"), 'value' => (string)$explode[0] . ' ' . __("views.m"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/KR8A17fN5gX0SKsIxazW453GrxpJHSkQ5R8AXWzF.png']);
                $number->push(['name' => __("views.estate length"), 'value' => '0' . ' ' . __("views.m"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/iqRWIrws2lsG3ozqEWpDJSlylky8NkX7vtuv8VbW.png']);
            }

        }

        if ($this->unit_counter > 0) {
            $number->push(['name' => __("views.unit counter"), 'value' => (string)$this->unit_counter, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/P7XW0E4gWcde4fvH0Se34enjQNY7OF6Xye4EfSQn.png']);
        }
        if ($this->unit_number > 0) {
            $number->push(['name' => __("views.unit number"), 'value' => (string)$this->unit_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/P7XW0E4gWcde4fvH0Se34enjQNY7OF6Xye4EfSQn.png']);
        }
//        if ($this->advertiser_number > 0) {
//            $number->push(['name' => 'advertiser_number', 'value' => (string) $this->advertiser_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/N9yPHjVBcaeiS5RUJdmZ2z5oDJq15MEFZRdnYOto.png']);
//        }
        if ($this->interface > 0) {

            $interface = $this->interface;
            $interface = str_replace('east', __("views.east"), $interface);
            $interface = str_replace('north', __("views.north"), $interface);
            $interface = str_replace('south', __("views.south"), $interface);
            $interface = str_replace('west', __("views.west"), $interface);
            $number->push(['name' => __("views.interface"), 'value' => (string)$interface, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/6cXcWUL0IOdqPa2M5bwGDY628LoMlwkNmhRUiIXn.png']);
        }
        if ($this->street_view > 0) {
            $number->push(['name' => __("views.street view"), 'value' => (string)$this->street_view . ' ' . __("views.m"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/nDqt1hAgkpYyrVqSy91EEr5JSwWvt6ODBtVLl2UJ.png']);
        }

        if ($this->street_name != null) {
            $number->push(['name' => __("views.street name"), 'value' => (string)$this->street_name, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/nDqt1hAgkpYyrVqSy91EEr5JSwWvt6ODBtVLl2UJ.png']);
        }

        if ($this->estate_age || $this->estate_age === '0') {
            $number->push(['name' => __("views.estate age"), 'value' => (string) $this->estate_age, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/CzWIYuDfZ2MZKs0OUqM1lolKdS9nEvaJUyaAvtVb.png']);
        }
//        if ($this->total_price > 0) {
//            $number->push(['name' => 'total_price', 'value' => (string) $this->total_price, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/N9yPHjVBcaeiS5RUJdmZ2z5oDJq15MEFZRdnYOto.png']);
//        }
        if ($this->elevators_number > 0) {
            $number->push(['name' => __("views.elevators"), 'value' => (string)$this->elevators_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/N9yPHjVBcaeiS5RUJdmZ2z5oDJq15MEFZRdnYOto.png']);
        }
        if ($this->parking_spaces_numbers > 0) {
            $number->push(['name' => __("views.parking"), 'value' => (string)$this->parking_spaces_numbers, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/IFJ3XdHlUwJyqEBiTBL7QIjdNTxGdNDdyWbq42HI.png']);
        }

        if ($this->pace_number > 0) {
            $number->push(['name' => __("views.land number"), 'value' => (string)$this->pace_number, 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/images/YSYY9YhsMCeA9ZHjZlk8j0AY9R1aOPuhvv8e9w5O.png']);
        }

        if ($this->total_area) {
            $number->push(['name' => __("views.total area"), 'value' => (string)$this->total_area . ' ' . __("views.m2"), 'img' => 'https://aqarz.s3.me-south-1.amazonaws.com/public/users/photo/DqDOwFugKFRu4OQpXSBgruo1HjKPp1a8saA87Ww7.png']);
        }

        $attachment = EstateFileResource::collection(AttachmentEstate::query()->where('estate_id', $this->id)->get());


        $user_collect = collect();
        $user_collect->push([
            'name' => @$this->user->onwer_name,
            'logo' => @$this->user->logo,
            'company_name' => @$this->user->name,
            'count_estate' => @$this->user->count_estate,
            'count_offer' => @$this->user->count_offer,
            'mobile' => @$this->user->mobile,
            'link' => @$this->user->link,
            'member_name' => @$this->user->member_name,
            'service_name' => @$this->user->service_name,
            'experience_name' => @$this->user->experience_name,
            'course_name' => @$this->user->course_name,
            'is_iam_complete' => @$this->user->is_iam_complete == 1 ? true : false,
        ]);


        return [
            'id' => $this->id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'neighborhood_id' => $this->neighborhood_id ?? null,
            'lat' => $this->lat,
            'lan' => $this->lan,
            'note' => $this->note,
            'advertiser_license_number' => $this->advertiser_license_number,
            'advertiser_number' => $this->advertiser_number,
            'advertiser_email' => $this->advertiser_email,
            'advertiser_mobile' => $this->advertiser_mobile,
            'advertiser_name' => $this->advertiser_name,
            'estate_type_name' => $this->estate_type_name,
            'operation_type_name' => $this->operation_type_name,
            'estate_type_id' => $this->estate_type_id,
            'operation_type_id' => $this->operation_type_id,
            'total_area' => $this->total_area,
            'time' => Carbon::parse($this->created_at)->diffForHumans(),
            'last_update' => Carbon::parse($this->updated_at)->diffForHumans(),
            'created_at' => @$date,
            'include_number' => $number->toArray(),
            'total_price' => $this->total_price,
            'in_fav' => @$this->in_fav,
            'rate' => @$this->rate,
            'first_image' => @$this->first_image,
            'attachment' => $attachment,
            'owner_name' => @$this->owner_name,
            'owner_mobile' => @$this->owner_mobile,
            'owner_logo' => @$this->user->logo . '',
            'city_name' => @$this->city_name,
            'neighborhood_name' => @$this->neighborhood_name,
            'full_address' => @$this->full_address,
            'owner_estate_name' => @$this->owner_estate_name,
            'owner_estate_mobile' => @$this->owner_estate_mobile,
            'estate_comforts' => ComfortsResource::collection($this->comforts),
            'last_modify' => Carbon::parse($this->updated_at)->diffForHumans(),
            'is_mortgage' => $this->is_mortgage == 1 ? __('views.yes') : __('views.no'),
            'is_obligations' => $this->is_obligations == 1 ? __('views.yes') : __('views.no'),
            'is_saudi_building_code' => $this->is_saudi_building_code == 1 ? __('views.yes') : __('views.no'),
            'is_disputes' => $this->is_disputes == 1 ? __('views.yes') : __('views.no'),
            'touching_information' => $this->touching_information,
            'similar_estates' => EstateResource::collection(Estate::where('estate_type_id', $this->estate_type_id)->where('operation_type_id', $this->operation_type_id)->where('id', '!=', $this->id)->limit(5)->get()),
            'company_name' => @$this->user->name,
            'user_id' => @$this->user_id,
            'link' => @$this->link,
            'street_name' => @$this->street_name,
            'seen_count' => @$this->seen_count,
            'land_number' => @$this->land_number,
            'planned_number' => @$this->planned_number,
            'company_logo' => @$this->user->company_logo,
            'user_estate_number' => @Estate::where('user_id', $this->user_id)->count() ?? 0,
            'appearing_count' => @$this->appearing_count ?? 0,
            'message_count' => @$this->message_count ?? 0,
            'message_whatsapp_count' => @$this->message_whatsapp_count ?? 0,
            'share_count' => @$this->share_count ?? 0,
            'favorite_count' => Favorite::where('type', 'estate')->where('type_id', $this->id)->count() ?? 0,
            'screenshot_count' => @$this->screenshot_count ?? 0,
            'show_video_count' => @$this->show_video_count ?? 0,
            'location_count' => @$this->location_count ?? 0,
            'call_count' => @$this->call_count ?? 0,
            'license_number' => @$this->license_number,
            'advertising_license_number' => @$this->advertising_license_number,
            'instrument_number' => @$this->instrument_number,
            'brokerage_and_marketing_license_number' => @$this->brokerage_and_marketing_license_number,
            'channels' => @$this->channels,
            'creation_date' => @$this->creation_date,
            'end_date' => @$this->end_date,
            'postal_code' => @$this->postal_code,
            'building_number' => @$this->building_number,
            'additional_code' => @$this->additional_code,
            'user_info' => $user_collect,
        ];
    }


}
