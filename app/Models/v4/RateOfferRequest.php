<?php

namespace App\Models\v4;

use App\Http\Resources\v4\NeighborhoodResource;
use App\Models\v3\Neighborhood;
use App\User;
use Illuminate\Database\Eloquent\Model;

class RateOfferRequest extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'rate_offer';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $appends = ['time'];


    protected $fillable = [
        'user_id',
        'request_rate_id',
        'day',
        'price',
        'status',
    ];


    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
