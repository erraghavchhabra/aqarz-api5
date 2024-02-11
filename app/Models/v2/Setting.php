<?php

namespace App\Models\v2;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
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
        'count_beta_days',
        'video_url',
        'tutorial',

    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',

    ];

}
