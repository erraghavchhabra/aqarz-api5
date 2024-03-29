<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
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

    ];

    protected $appends=['value'];
    public function getValueAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name='value_'.$local;
        return $this->$colum_name;
    }
   
}
