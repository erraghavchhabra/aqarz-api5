<?php

namespace App\Models\v3;

use Illuminate\Database\Eloquent\Model;

class ComfortRequestEstate extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $table='estate_request_comforts';
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
    protected $fillable = [
        'estate_id',
        'comfort_id',
    ];


    public function comfort()
    {
        return $this->belongsTo(Comfort::class,'comfort_id','id');
    }

}
