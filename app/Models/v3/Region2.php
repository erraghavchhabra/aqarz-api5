<?php

namespace App\Models\v3;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class Region2 extends Model
{
    use SpatialTrait;

    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];

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

    protected $table = 'regions_ksa';
    protected $fillable = [
        'center',
        'boundaries',
        'code',
        'ejar_id',
    ];

    protected $hidden=
    [
       // 'center',
        'boundaries',
        ];
    protected $appends = ['name'];

    public function getNameAttribute()
    {

        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';
        $colum_name = 'name_' . $local;
        return $this->$colum_name;
    }


    protected $spatialFields = [
        'center',
    //    'boundaries',
    ];


    /*  public function getIconAttribute($value)
      {

          if($value != null)
              return url( (@$this->attributes['icon']));
      }*/

}
