<?php

namespace App\Models\v2;

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


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
