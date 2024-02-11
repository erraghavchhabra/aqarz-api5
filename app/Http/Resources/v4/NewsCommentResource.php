<?php

namespace App\Http\Resources\v4;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsCommentResource extends JsonResource
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
            'id' => $this->id,
            'name'=> $this->name,
            'logo'=> @$this->user->logo,
            'comment'=> $this->comment,
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y'),
            'replies' => NewsCommentResource::collection($this->replies),
        ];
    }
}
