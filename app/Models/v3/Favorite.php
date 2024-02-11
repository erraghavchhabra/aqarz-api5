<?php

namespace App\Models\v3;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
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
        'type_id',
        'status',
        'type'
    ];


    protected $appends = ['request', 'offer', 'fund' , 'estate'];


    public function getRequestAttribute()
    {

        if ($this->type == 'request') {
            $EstateRequest = EstateRequest::where('id', $this->type_id)->get();

            return $EstateRequest;
        } else {
            return null;
        }


    }

    public function getFundAttribute()
    {

        if ($this->type == 'fund') {
            $EstateRequest = RequestFund::where('id', $this->type_id)->get();
            return $EstateRequest;
        } else {
            return null;
        }


    }

    public function getOfferAttribute()
    {

        if ($this->type == 'offer') {
            $EstateRequest = Estate::where('id', $this->type_id)->get();
            return $EstateRequest;
        } else {
            return null;
        }


    }

    public function getEstateAttribute()
    {

        if ($this->type == 'estate') {
            $EstateRequest = Estate::where('id', $this->type_id)->get();
            return $EstateRequest;
        } else {
            return null;
        }


    }

    public function estate_data()
    {
        return $this->belongsTo(Estate::class , 'type_id');
    }

    public function request_data()
    {
        return $this->belongsTo(EstateRequest::class , 'type_id');
    }

    public function fund_data()
    {
        return $this->belongsTo(RequestFund::class , 'type_id');
    }


}
