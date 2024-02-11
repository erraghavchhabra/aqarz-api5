<?php

namespace App\Models\v2;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class City3 extends Model
{
    use SpatialTrait;
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';
    protected $table = 'cities_ksa';
    protected $guarded = [

    ];

    protected $fillable = [
        'serial_city',
        'name_ar',
        'latitude',
        'longitude',
        'state_id',
        'city_id',
        'center',
        'state_code'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $spatialFields = [
        'center',
        //    'boundaries',
    ];

}
