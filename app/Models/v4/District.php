<?php

namespace App\Models\v4;

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
    protected $table = 'districts_final';
    protected $primaryKey = 'district_id';
    protected $guarded = [

    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    protected $fillable = [
        'district_id',
        'city_id',
        'region_id',
        'name_ar',
        'name_en',
        'boundaries',
        'center',
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
    protected $appends = ['name' , 'full_name'];

    public function getNameAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'name_' . $local;
        return $this->$colum_name;
    }

    public function getFullNameAttribute()
    {
        return $this->getNameAttribute() . ' - ' . $this->city->getNameAttribute();
    }

    public function city()
    {
        return $this->hasOne(Cities::class ,  'id' , 'city_id');
    }

    public function region()
    {
        return $this->hasOne(Region::class ,  'id' , 'region_id');
    }
}
