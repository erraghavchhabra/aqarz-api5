<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
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
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'image' => $this->image,
            'user_name' => @$this->user->name,
            'user_logo' => @$this->user->logo,
            'user_created' => @$this->user->created_at->format('Y-M-d'),
            'created_at' => @$this->created_at->format('d M'),
            'view' => $this->view,
        ];
    }
}
