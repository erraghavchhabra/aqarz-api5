<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MsgResource extends JsonResource
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
            'sender_id'      => $this->sender_id,
            'receiver_id'    => $this->receiver_id,
            'sender_name'      => @$this->sender_name != null?@$this->sender_name:@$this->sender_mobile,
            'receiver_name'    => @$this->receiver_name!=null?@$this->receiver_name:@$this->receiver_mobile,
            'sender_photo'   => @$this->ssender_photo,

            'receiver_photo' =>@$this->receiver_photo,
            'created_at'     => @$this->created_at,
            'from_me'     => @$this->from_me,
            'display_name'=>@$this->display_name,
            'display_logo'=>@$this->display_logo,
            'display_id'=>@$this->display_id,
            'time' => Carbon::parse($this->last_msg_date)->diffForHumans(),
            'count_not_read'=>@$this->count_not_read > 0 ? 1 : 0,


            //   'estate_comfort_way'   => (new ComfortResource($this->estate->comforts)),

            //   'estate_attachment'   => (new EstateFileResource($this->estate->EstateFile)),

        ];
    }
}
