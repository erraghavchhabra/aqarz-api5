<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AccountStatementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $url = 'https://aqarz.sa/';
        return [
            'id' => $this->id,
            'owed_money' => @$this->owed_money,
            'paid_money' => $this->paid_money,
            'statment' => $this->statment,



        ];
    }


}
