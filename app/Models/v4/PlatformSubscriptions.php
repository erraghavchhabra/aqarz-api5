<?php

namespace App\Models\v4;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

class PlatformSubscriptions extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'platform_subscriptions';
    protected $guarded = [

    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $fillable = [
        'plan_id',
        'price',
        'contract_number',
        'duration',
        'duration_type',
        'status',
        'start_time',
        'end_time',
        'payment_id',
        'user_id',
        'quantity',
        'contract_number_used',
    ];

    protected $appends = ['time' , 'plan_name'];

    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function plan()
    {
        return $this->belongsTo(PlatformPlan::class, 'plan_id');
    }

    public function getPlanNameAttribute()
    {
        return $this->plan->name;
    }

}
