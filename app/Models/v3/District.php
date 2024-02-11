<?php

namespace App\Models\v3;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use SpatialTrait;
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';
    protected $table = 'districts';
    protected $guarded = [

    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
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
       // 'center',
            'boundaries',
    ];
    protected $appends = ['name'];
    public function getNameAttribute()
    {

        //    $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';

        //     dd($local);
        $colum_name = 'name_ar';
        return $this->$colum_name;
    }
}
