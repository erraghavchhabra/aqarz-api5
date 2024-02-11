<?php

namespace App\Models\v3;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
//use Malhal\Geographical\Geographical;


class City extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';
   // use SpatialTrait;
   // use Geographical;

    protected $guarded = [

    ];
   /* protected $spatialFields = [
        'center',
        //    'boundaries',
    ];*/
    public function toSearchableArray()
    {
        $record = $this->toArray();

        $record['_geoloc'] = [
            'lat' => $record['lat'],
            'lng' => $record['lan'],
        ];

        unset($record['created_at'], $record['updated_at']); // Remove unrelevant data
        unset($record['lat'], $record['lan']);

        return $record;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'serial_city',
        'name_ar',
        'latitude',
        'longitude',
        'state_id',
        'city_id',
        'center',
        'state_code',
        'count_fund_request',
        'count_app_request',
        'count_app_estate',
        'count_neighborhood',
    ];

    protected $hidden=[
        'center'
    ];
    protected $appends = ['name'];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    public function getNameAttribute()
    {

        //    $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';

        //     dd($local);
        $colum_name = 'name_ar';
        return $this->$colum_name;
    }


    public function neb()
    {
        return $this->hasMany(Neighborhood::class, 'city_id', 'serial_city');
    }

    public function fund_request()
    {
        return $this->hasMany(RequestFund::class, 'city_id', 'serial_city')
            ;
    }


    public function app_request()
    {
        return $this->hasMany(EstateRequest::class, 'city_id', 'serial_city');
    }

    public function estate()
    {
        return $this->hasMany(Estate::class, 'city_id', 'serial_city');
    }
}
