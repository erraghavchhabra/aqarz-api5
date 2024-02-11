<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BloggerResource extends JsonResource
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
            'id' => @$this->id,
            'title' => @$this->title,
            'body' => @$this->body,
            'image' => @$this->image,
            'count_commet' => @$this->count_commet,
            'created_at' => @$this->created_at,
            'updated_at' => @$this->updated_at,
            'status' => @$this->status,
            'comments'   => $this->comments,

        ];
    }
}
