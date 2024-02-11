<?php

namespace App\Models\v3;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationUser extends Model
{

    use SoftDeletes;


    protected $table = 'notification_users';
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
        'user_id',
        'title',
        'type',
        'type_id',
    ];

    protected $appends = ['time'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }


}
