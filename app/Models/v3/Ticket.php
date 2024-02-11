<?php

namespace App\Models\v3;

use App\Models\dashboard\Admin;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
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
        'body',
        'user_id',
        'assign_to_admin_id',
        'admin_note',
        'estate_id',
        'priority',
        'type',
        'attachment',
        'status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

    public function assign_admin()
    {
        return $this->belongsTo(Admin::class, 'assign_to_admin_id');
    }

    public function ticket_chat()
    {
        return $this->hasMany(TicketChat::class, 'ticket_id')->orderBy('id','desc');
    }

    public function getAttachmentAttribute($value)
    {

        if ($value != null) {
            return url((@$this->attributes['attachment']));
        }
    }
}
