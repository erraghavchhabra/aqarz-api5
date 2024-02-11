<?php

namespace App\Models\v3;

use App\Models\dashboard\Admin;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketChat extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attachment',
        'ticket_id',
        'user_id',
        'replay_admin_id',
        'attachment',
        'message',
        'from_type',
        'status',
        'admin_note',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'replay_admin_id');
    }
    public function getAttachmentAttribute($value)
    {

        if ($value != null) {
            return url((@$this->attributes['attachment']));
        }
    }

}
