<?php

namespace App\Models\v2;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
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
        'video',
        'status',
        'type',

    ];




}
