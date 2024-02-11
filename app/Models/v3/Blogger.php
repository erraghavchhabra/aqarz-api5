<?php

namespace App\Models\v3;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Blogger extends Model
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
        'body',
        'title',
        'status',
        'image',
        'count_commet',
    ];


    public function comments()
    {
      return  $this->hasMany(BloggerCommet::class,'blogger_id')
          ->where('parent_id',null);
    }

}
