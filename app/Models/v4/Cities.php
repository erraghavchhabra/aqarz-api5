<?php

namespace App\Models\v4;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    use SpatialTrait;
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'cities_final';
    protected $guarded = [

    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    protected $fillable = [
        'region_id',
        'name_ar',
        'name_en',
        'center',
        'ejar_id',
        'ejar_region_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $spatialFields = [
        'center',
    ];
    protected $appends = ['name' , 'region_name'];
    public function getNameAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'name_' . $local;
        return $this->$colum_name;
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function getRegionNameAttribute()
    {
        return @$this->region->name;
    }
}
