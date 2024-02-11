<?php

namespace App\Models\v3;

use Illuminate\Database\Eloquent\Model;

class RateRequestType extends Model
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

    protected $table='rate_request_types';
    protected $fillable = [

    ];

    protected $appends=['name'];
    public function getNameAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name='name_'.$local;
        return $this->$colum_name;
    }

}
