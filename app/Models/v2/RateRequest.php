<?php

namespace App\Models\v2;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RateRequest extends Model
{
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
    protected $fillable = [
        'estate_type_id',
        'name',
        'email',
        'mobile',
        'note',
        'lat',
        'lan',
        'address',
        'status',
        'user_id',
        'estate_id'
    ];

    //  protected $with = ['rate_request_types'];
    protected $appends = ['estate_type_name'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }




    public function getEstateTypeNameAttribute()
    {

        $rate_request = EstateType::where('id', $this->estate_type_id)->first();

        //   dd($rate_request);
        if ($rate_request) {

            return $rate_request->name_ar;


        }

    }
}
