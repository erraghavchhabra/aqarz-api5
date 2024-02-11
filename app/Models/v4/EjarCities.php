<?php

namespace App\Models\v4;

use Illuminate\Database\Eloquent\Model;

class EjarCities extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'ejar_cities';

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

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'name_ar',
        'name_en',
        'province_id',
        'redf_code',
        'lat',
        'lon',
        'ejar_id',
        'region_id',
    ];

    protected $fillable = [
        'name_ar',
        'name_en',
        'province_id',
        'redf_code',
        'region_id',
        'lat',
        'lon',
        'ejar_id',

    ];


    protected $appends = ['name'];

    public function getNameAttribute()
    {
        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'name_' . $local;
        return $this->$colum_name;
    }

}
