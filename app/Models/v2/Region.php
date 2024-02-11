<?php

namespace App\Models\v2;

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

    protected $guarded = [

    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'regions';
    protected $fillable = [
        'center',
        'boundaries',
        'name_ar'
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
