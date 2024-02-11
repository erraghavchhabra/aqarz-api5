<?php

namespace App\Http\Resources\v4;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsShowResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'user_name' => @$this->user->name,
            'user_logo' => @$this->user->logo,
            'user_created' => convertDateToArabic(Carbon::parse(@$this->user->created_at)->format('Y-m-d')),
            'created_at' => convertDateToArabic(Carbon::parse($this->created_at)->format('Y-m-d')),
            'created_at_format' => convertDateToArabic(Carbon::parse($this->created_at)->translatedFormat('m-d')),
            'comments' => NewsCommentResource::collection($this->comments),
            'comments_count' =>  $this->comments->count(),
        ];
    }
}
