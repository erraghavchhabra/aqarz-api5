<?php

namespace App\Models\v4;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class PlatformPlan extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'platform_plan';
    protected $guarded = [

    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s',
        'duration' => 'integer',
        'contract_number' => 'integer',
        'price' => 'float',
    ];
    protected $fillable = [
        'name_ar',
        'name_en',
        'contract_number',
        'duration',
        'duration_type',
        'price',
        'status',
        'color',
        'type',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $appends = ['name'];

    public function getNameAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'name_' . $local;
        return $this->$colum_name;
    }
}
