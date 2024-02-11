<?php

namespace App\Models\v4;

use Illuminate\Database\Eloquent\Model;

class EjarDistricts extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'ejar_districts';

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
        'name_ar',
        'name_en',
        'city_id',
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
