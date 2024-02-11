<?php

namespace App\Models\v3;

use App\Models\v4\RateOfferRequest;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        'estate_type_id',
        'name',
        'email',
        'mobile',
        'note',
        'lat',
        'lan',
        'address',
        'status',
        'operation_type_id',
        'user_id',
        'estate_id',
        'purpose_evaluation',
        'entity_evaluation',
        'area',
        'estate_use_type',
    ];

    protected $appends = ['estate_type_name', 'operation_type_name' , 'have_offer'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

    public function operation_type()
    {
        return $this->belongsTo(OprationType::class);
    }

    public function getOperationTypeNameAttribute()
    {


        return @$this->operation_type->name_ar;
    }

    public function offer_user()
    {
        return $this->hasOne(RateOfferRequest::class, 'request_rate_id')->where('user_id', Auth::id());
    }

    public function offer()
    {
        return $this->hasMany(RateOfferRequest::class, 'request_rate_id');
    }


    public function getEstateTypeNameAttribute()
    {

        $rate_request = EstateType::where('id', $this->estate_type_id)->first();

        //   dd($rate_request);
        if ($rate_request) {

            return $rate_request->name_ar;


        }

    }

    public function getHaveOfferAttribute()
    {
        $rate_request = RateOfferRequest::where('request_rate_id', $this->id)->where('user_id', Auth::id())->first();
        if ($rate_request) {
            return true;
        } else {
            return false;
        }
    }
}
