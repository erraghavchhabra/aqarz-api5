<?php

namespace App\Models\v1;

use App\Models\v2\RequestOffer;
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


    protected $appends = ['request', 'offer', 'fund'];


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
            $EstateRequest = RequestOffer::where('id', $this->type_id)->get();
            return $EstateRequest;
        } else {
            return null;
        }


    }


}
