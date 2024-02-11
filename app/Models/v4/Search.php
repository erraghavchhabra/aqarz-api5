<?php

namespace App\Models\v4;

use App\Http\Resources\v4\NeighborhoodResource;
use App\Models\v3\Neighborhood;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
     protected $table = 'search';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s',
        'neighborhoods_id' => 'array',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $appends = ['time'];


    protected $fillable = [
        'user_id',
        'type',
        'property_type',
        'bedrooms',
        'bathroom',
        'price_min',
        'price_max',
        'name',
        'size_min',
        'size_max',
        'directions',
        'neighborhoods_id',
        'receving_update',
        'operation_type_id',
        'dining_rooms_number',
        'lat',
        'lng',
    ];

    public function getNeighborhoodsIdAttribute($value)
    {
        $neighborhoods_id = explode(',', $value);
        $neighborhoods = Neighborhood::whereIn('id', $neighborhoods_id)->get();

        return NeighborhoodResource::collection($neighborhoods);
    }

    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

}
