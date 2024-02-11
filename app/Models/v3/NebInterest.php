<?php

namespace App\Models\v3;

use App\User;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NebInterest extends Model
{

    use SoftDeletes;
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';
    protected $table = 'neb_interests';
    protected $guarded = [

    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    protected $fillable = [
        'user_id',
        'neb_id',

    ];



    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function neb()
    {
        return $this->belongsTo(District::class,'neb_id','district_id');
    }

}
