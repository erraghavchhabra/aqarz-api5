<?php

namespace App\Models\v4;

use App\Http\Resources\v4\NeighborhoodResource;
use App\Models\v3\Estate;
use App\Models\v3\Neighborhood;
use App\User;
use Illuminate\Database\Eloquent\Model;

class EstateRequestPreview extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'estate_request_preview';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s',
        'times'=>'array',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $appends = ['time'];


    protected $fillable = [
        'user_id',
        'estate_id',
        'owner_id',
        'statue',
        'times',
        'accept_time',
    ];

    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
