<?php

namespace App\Http\Resources;


use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MsgDetResource extends JsonResource
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
            'id'        => $this->id,
            'title'     => $this->title,
            'body'      => $this->body,
            'msg_id' => $this->msg_id,
            'from_me'     => @$this->from_me,


            'sender_id'      => $this->sender_id,
            'receiver_id'    => $this->receiver_id,
            'sender_name'      => @$this->sender_name != null ? @$this->sender_name: @$this->sender_mobile,
            'receiver_name'    => @$this->receiver_name!=null ? @$this->receiver_name: @$this->receiver_mobile,
            'sender_photo'   => @$this->sender_photo,

            'receiver_photo' => @$this->receiver_photo,
            'parent_body' => @$this->parent_body,
            'parent_title' => @$this->parent_title,
            'parent_created_at' => @$this->parent_created_at,
            'created_at'     => @$this->created_at,
            'seen'=>@$this->seen,
            'time' => Carbon::parse($this->created_at)->diffForHumans(),


            //   'estate_comfort_way'   => (new ComfortResource($this->estate->comforts)),

            //   'estate_attachment'   => (new EstateFileResource($this->estate->EstateFile)),

        ];
    }
}
