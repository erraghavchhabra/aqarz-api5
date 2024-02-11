<?php

namespace App\Models\v2;

use App\User;
use Illuminate\Database\Eloquent\Model;

class MsgDet extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $table='msgs_details';
    protected $guarded = [

    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'body',
        'msg_id',
        'title',
        'seen',
    ];

    protected $with = ['sender', 'receiver'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function parent()
    {
        return $this->belongsTo(Msg::class, 'msg_id');
    }


}
