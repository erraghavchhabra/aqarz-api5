<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Comfort extends Model
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

    protected $table='means_of_comforts';
    protected $fillable = [

    ];

    protected $appends=['name'];
    public function getNameAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name='name_'.$local;
        return $this->$colum_name;
    }
    public function getIconAttribute($value)
    {

        if($value != null)
            return url( (@$this->attributes['icon']));
    }
   
}
