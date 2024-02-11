<?php

namespace App\Models\v3;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundRequestHasOffer extends Model
{

    use SoftDeletes;

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
    protected $table = 'fund_request_has_offers';
    protected $fillable = [
        'display_status',
        'uuid',


    ];


  protected $hidden=['status','updated_at','deleted_at','id'];


    public function fund_request()
    {
        return $this->belongsTo(RequestFund::class, 'uuid');
    }


    public function getDisplayStatusAttribute($value)
    {
        return __('views.'.$value);
    }




}
