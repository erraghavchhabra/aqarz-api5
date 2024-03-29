<?php

namespace App\Models\v1;

use App\Models\v2\FundRequestOffer;
use App\Models\v2\Neighborhood;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestFund extends Model
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


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'estate_type_id',
        'estate_status',
        'area_estate_id',
        'dir_estate_id',
        'estate_price_id',
        'street_view_id',
        'rooms_number_id',
        'city_id',
        'neighborhood_id',
        'status',
        'offer_numbers'

    ];

    protected $hidden = ['offer_numbers', 'status', 'deleted_at', 'updated_at'];

    protected $appends = [
        'in_fav',
        'estate_type_icon',
        'estate_type_name',
        'dir_estate',
        'street_view_range',
        'estate_price_range',
        'area_estate_range',
        'city_name',
        'neighborhood_name',
    ];







    public function offers()
    {
        return $this->hasMany(FundRequestOffer::class,'uuid','uuid');
    }

    public function estate_type()
    {
        return $this->belongsTo(EstateType::class);
    }


    public function area_estate()
    {
        return $this->belongsTo(AreaEstate::class);
    }

    public function estate_price()
    {
        return $this->belongsTo(EstatePrice::class);
    }

    public function street_view()
    {
        return $this->belongsTo(StreetView::class);
    }

    public function getInFavAttribute()
    {


        $fav = Favorite::where('type_id', $this->id)
            ->where('type', 'fund')
            ->where('status', '1')
            ->first();
        if ($fav) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getEstateTypeNameAttribute()
    {
        $estate_type_id = EstateType::find($this->estate_type_id);
        if ($estate_type_id) {
            return $estate_type_id->name;
        }

        return null;

        // return $this->estate_type->name;
    }

    public function getEstateTypeIconAttribute()
    {
        $estate_type_id = EstateType::find($this->estate_type_id);
        if ($estate_type_id) {
            return $estate_type_id->icon;
        }

        return null;

        // return $this->estate_type->name;
    }

    public function getAreaEstateRangeAttribute()
    {

        $area_estate_id = AreaEstate::find($this->area_estate_id);
        if ($area_estate_id) {
            return $area_estate_id->estate_price_range;
        }

        return null;

        //  return $this->estate_price->estate_price_range;
        //  return $this->area_estate->area_range;
    }

    public function getEstatePriceRangeAttribute()
    {
        $estate_price_id = EstatePrice::find($this->estate_price_id);
        if ($estate_price_id) {
            return $estate_price_id->estate_price_range;
        }

        return null;

        //  return $this->estate_price->estate_price_range;
    }


    public function getStreetViewRangeAttribute()
    {

        $street = StreetView::find($this->street_view_id);
        if ($street) {
            return $street->street_view_range;
        }

        return null;

    }


    public function getCityNameAttribute()
    {

        $city = City::where('serial_city', $this->city_id)->first();
        if ($city) {
            return $city->name;
        }

        return null;

    }


    public function getNeighborhoodNameAttribute()
    {


        $neighborhood = Neighborhood::where('neighborhood_serial', $this->neighborhood_id)
            ->first();
        if ($neighborhood) {
            return $neighborhood->name;
        }

        return null;

    }



    public function getDirEstateAttribute()
    {


        return dirctions($this->dir_estate_id);
    }

    public function getEstateStatusAttribute($value)
    {


        return state_estate($value);
    }
    public function neighborhood()
    {
        return $this->belongsToMany(Neighborhood::class,'fund_request_neighborhoods','request_fund_id','id');
    }


}
