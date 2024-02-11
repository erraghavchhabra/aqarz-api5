<?php

namespace App\Models\v3;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
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
    protected $fillable = [
        'user_id',
        'client_name',
        'client_mobile',
        'source_type',
        'request_type',
        'ads_number',
        'priority',
        'remember',
        'remember_date_time',

    ];


    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];
    protected $appends = ['estate_type_name'];

    public function estate_type()
    {
        return $this->belongsTo(EstateType::class, 'request_type');
    }

    public function getEstateTypeNameAttribute()
    {


        return @$this->estate_type->name;
    }
}
