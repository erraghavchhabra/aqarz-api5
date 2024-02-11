<?php

namespace App\Http\Resources\v4;

use App\Models\v3\EstateType;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchWebResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        $number = collect();

        if ($this->type) {
            $number->push(['name' => __("views.type"), 'value' => __("views." . $this->type), 'title' => __("views.type") . ' ' . __("views." . $this->type)]);
        }

        if ($this->property_type) {

            $array = explode(',', $this->property_type);
            $property_type = collect();
            foreach ($array as $key => $value) {
                $type = EstateType::find($value);
                if ($type) {
                    $property_type->push($type->name);
                }
            }
            $number->push(['name' => __("views.property_type"), 'value' => $property_type->implode(', '), 'title' => __("views.property_type") . ' ' . $property_type->implode(', ')]);
        }


        if ($this->bathroom > 0) {
            $number->push(['name' => __("views.bathrooms"), 'value' => $this->bathroom . ' ' . __("views.bathroom"), 'title' => __("views.bathrooms") . ' ' . $this->bathroom . ' ' . __("views.bathroom")]);
        }

        if ($this->bedrooms > 0) {
            $number->push(['name' => __("views.bedroom"), 'value' => $this->bedrooms . ' ' . __("views.rooms"), 'title' => __("views.bedroom") . ' ' . $this->bedrooms . ' ' . __("views.rooms")]);

        }


        if ($this->price_min) {
            $number->push(['name' => __("views.min price"), 'value' => $this->price_min . ' ' . __("views.sar"), 'title' => __("views.min price") . ' ' . $this->price_min . ' ' . __("views.sar")]);
        }

        if ($this->price_max) {
            $number->push(['name' => __("views.price_max"), 'value' => $this->price_max . ' ' . __("views.sar"), 'title' => __("views.price_max") . ' ' . $this->price_max . ' ' . __("views.sar")]);
        }

        if ($this->size_min) {
            $number->push(['name' => __("views.size_min"), 'value' => $this->size_min . ' ' . __("views.m"), 'title' => __("views.size_min") . ' ' . $this->size_min . ' ' . __("views.m")]);
        }

        if ($this->size_max) {
            $number->push(['name' => __("views.size_max"), 'value' => $this->size_max . ' ' . __("views.m"), 'title' => __("views.size_max") . ' ' . $this->size_max . ' ' . __("views.m")]);
        }


        $interface = $this->directions;
        $interface = str_replace('east', __("views.east"), $interface);
        $interface = str_replace('north', __("views.north"), $interface);
        $interface = str_replace('south', __("views.south"), $interface);
        $interface = str_replace('west', __("views.west"), $interface);


        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'estate_type' => $this->property_type,
            'operation_type_id' => $this->operation_type_id,
            'bedrooms' => $this->bedrooms,
            'bathrooms_number' => $this->bathroom,
            'dining_rooms_number' => $this->dining_rooms_number,
            'price_from' => $this->price_min,
            'price_to' => $this->price_max,
            'name' => $this->name,
            'area_from' => $this->size_min,
            'area_to' => $this->size_max,
            'directions' => $interface,
            'neighborhoods_id' => $this->neighborhoods_id,
            'time' => $this->time,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'receving_update' => __('views.' . $this->receving_update),
            'include' => $number->toArray(),

        ];
    }
}

