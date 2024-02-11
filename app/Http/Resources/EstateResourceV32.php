<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EstateResourceV32 extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */

    /* public function __construct($request)
     {
         // $request->merge(request_only(['Auth-Role'], getallheaders()));
         $url = 'https://aqarz.sa/';
         $this->collection = [
             'id' => $this->id,
             'estate_type_name' => $this->estate_type_name,
             'operation_type_name' => $this->operation_type_name,
             'estate_type_id' => $this->estate_type_id,
             'operation_type_id' => $this->operation_type_id,
             'lat' => $this->lat,
             'lan' => $this->lan,
             'total_area' => $this->total_area,
             'total_price' => $this->total_price,
             'bedroom_number' => $this->bedroom_number,
             'bathrooms_number' => $this->bathrooms_number,
             'rate' => @$this->rate,
             'created_at' => @$this->created_at,
             'first_image' => @$this->first_image,
             'link' => @$url . 'estate/' . $this->id . '/show',
         ];
     }*/

    public function toArray($request)
    {

        dd($this);
        $url = 'https://aqarz.sa/';
        return [

            'id' => $this->id,
            'estate_type_name' => $this->estate_type_name,
            'operation_type_name' => $this->operation_type_name,
            'estate_type_id' => $this->estate_type_id,
            'operation_type_id' => $this->operation_type_id,
            'lat' => $this->lat,
            'lan' => $this->lan,
            'total_area' => $this->total_area,
            'total_price' => $this->total_price,
            'bedroom_number' => $this->bedroom_number,
            'bathrooms_number' => $this->bathrooms_number,
            'rate' => @$this->rate,
            'created_at' => @$this->created_at,
            'first_image' => @$this->first_image,
            'link' => @$url . 'estate/' . $this->id . '/show',




        ];
    }

    /*  public function withResponse($request, $response)
      {
          $jsonResponse = json_decode($response->getContent(), true);
          unset($jsonResponse['links'], $jsonResponse['meta']);
          $response->setContent(json_encode($jsonResponse));
      }*/

}
