<?php

namespace App\Models\v4;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use SpatialTrait;

    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';
    protected $table = 'regions_final';
    protected $guarded = [

    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    protected $fillable = [
        'capital_city_id',
        'code',
        'name_ar',
        'name_en',
        'center',
        'boundaries',
        'population',
        'ejar_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $spatialFields = [
        'center',
        'boundaries',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at' , 'boundaries'];
    protected $appends = ['name'];

    public function getNameAttribute()
    {
        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'name_' . $local;
        return $this->$colum_name;
    }
}
