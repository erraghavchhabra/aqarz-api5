<?php

namespace App\Http\Resources;

use App\Models\v3\RentalContracts;
use App\Models\v4\EjarEstateData;
use Illuminate\Http\Resources\Json\JsonResource;

class EstateSelectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = $request->user();
        $have_platform_subscription = $user->have_platform_subscription;
        return [
            'id' => $this->id,
            'estate_name' => $this->estate_name,
            'estate_type_name' => $this->estate_type_name,
            'user_id' => $this->user_id,
            'owner_name' => $this->user->onwer_name,
            'rent_price' => $this->rent_price,
            'rent_type' => $this->rent_type,
            'ejar_show' => EjarEstateData::where('estate_id', $this->id)->first() ? true : false,
            'not_available' => RentalContracts::where('estate_id' , $this->id)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first() ? true : false,
            'have_platform_subscription' => $have_platform_subscription,
        ];
    }
}
