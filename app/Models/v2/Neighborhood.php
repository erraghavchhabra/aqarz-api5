<?php

namespace App\Models\v2;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
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
        'lat',
        'lan',
        'search_name',
        'estate_counter',
        'request_app_counter',
        'request_fund_counter',
    ];

    protected $table = 'neighborhoods';

    protected $hidden = ['requestFund','app_request','estate'];

   protected $appends = ['name'];
    ////, 'request_fund_counter','request_app_counter','estate_counter'];

    public function getNameAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'name_' . $local;
        return $this->$colum_name;
    }

    public function city()
    {
        return $this->hasOne(City::class, 'serial_city', 'city_id');
    }


    public function requestFund()
    {
        return $this->belongsToMany(RequestFund::class, 'fund_request_neighborhoods', 'neighborhood_id',
            'request_fund_id', 'neighborhood_serial')->groupBy('request_fund_id');
    }
    public function app_request()
    {
        return $this->hasMany(EstateRequest::class, 'neighborhood_id', 'neighborhood_serial');
    }

    public function getRequestFundCounterAttribute()
    {

        return @count($this->requestFund);
    }
    public function getRequestAppCounterAttribute()
    {

        return @count($this->app_request);
    }



    public function estate()
    {
        return $this->hasMany(Estate::class, 'neighborhood_id', 'neighborhood_serial');
    }

    public function getEstateCounterAttribute()
    {

        return @count($this->estate);
    }

}
