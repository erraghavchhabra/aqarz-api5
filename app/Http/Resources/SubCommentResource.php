<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubCommentResource extends JsonResource
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
            "id" => @$this->id,
            "blogger_id" => @$this->blogger_id,
            "user_id" => @$this->user_id,
            "name" => @$this->name,
            "email" => @$this->email,
            "commet" => @$this->commet,
            "parent_id" => @$this->parent_id,
            "created_at" => @$this->created_at,
            "updated_at" => @$this->updated_at,
            'user' => @(new UserResource($this->user)),
        ];
    }
}
