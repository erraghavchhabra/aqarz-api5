<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
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
            'name'  => $this->name,
            'logo'   => url('image/' . $this->logo ),
            'coupon_logo'   => @$this->coupon_logo ?  url('image/' . $this->coupon_logo ) : null,
            'url'   => $this->url,
        ];
    }
}
