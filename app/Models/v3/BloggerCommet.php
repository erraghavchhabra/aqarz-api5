<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Self_;

class BloggerCommet extends Model
{
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
        'blogger_id',
        'commet',
        'status',
        'parent_id',
    ];
    protected $appends = ['user_info'];

    public function sub_comments()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function blogger()
    {
        return $this->belongsTo(Blogger::class, 'blogger_id');
    }

   /* public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
*/
    public function getUserInfoAttribute()
    {

        $user = User::find($this->user_id);
        if ($user) {
            return [
                'name' => @$user->name,
                'image' => @$user->logo,
                'id' => @$user->id
            ];
        }

        return null;


    }
}
