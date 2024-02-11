<?php

namespace App\Models\v3;

use App\Http\Resources\v4\ComfortsResource;
use App\Models\v4\Cities;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

//use Malhal\Geographical\Geographical;

class EstateRequest extends Model
{
    // use Geographical;
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
        'operation_type_id',
        'request_type',
        'estate_type_id',
        'estate_use_type',
        'area_from',
        'area_to',
        'price_from',
        'price_to',
        'room_numbers',
        'bathroom_numbers',
        'owner_name',
        'owner_mobile',
        'display_owner_mobile',
        'note',
        'lat',
        'lan',
        'status',
        'user_id',
        'seen_count',
        'neighborhood_id',
        'city_id',
        'address',
    ];

    protected $appends = ['in_fav','operation_type_name', 'estate_type_name','city_name', 'neighborhood_name' , 'time' , 'link' , 'estate_comforts','estate_use_name'];

    protected $hidden=['operation_type','estate_type'];

    public function getLinkAttribute()
    {

        $url = 'https://aqarz.sa/';
        return $url . 'request/' . $this->id . '/show';

    }

    public function getEstateUseType($value)
    {
        return $this->estate_use_type;
    }

    public function operation_type()
    {
        return $this->belongsTo(OprationType::class);
    }

    public function estate_type()
    {
        return $this->belongsTo(EstateType::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function getOperationTypeNameAttribute()
    {


        return @$this->operation_type->name;
    }

    public function getDaysRemainToExpiredAttribute()
    {

        $today = strtotime(date('Y-m-d'));
        $expired_date = strtotime($this->created_at);

        $days = $today - $expired_date;

        if ($days > 30) {
            return 0;
        }
        return 30 - $days;
    }

    public function getEstateTypeNameAttribute()
    {


        return @$this->estate_type->name;
    }

    public function city()
    {
        return $this->belongsTo(City3::class,'city_id','city_id');
    }


    public function city_new()
    {
        return $this->belongsTo(Cities::class,'id','city_id');
    }

    public function neighborhood()
    {
        return $this->belongsTo(District::class,'neighborhood_id','district_id');
    }


    public function getNeighborhoodNameAttribute()
    {

        $neighborhood = District::where('district_id', $this->neighborhood_id)
            ->first();
        if ($neighborhood) {
            return @$neighborhood->name;
        }

        return null;

    }


    public function getCityNameAttribute()
    {

        $city = City3::where('city_id', $this->city_id)->first();
        if ($city) {
            return @$city->name;
        }

        return null;

    }


    public function getInFavAttribute()
    {


        $fav = Favorite::where('type_id', $this->id)
            ->where('type', 'request')
            ->where('user_id', Auth::id())
            ->where('status', '1')
            ->first();
        if ($fav) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getLatAttribute($value)
    {
        return number_format((float)$value, 5, '.', '');

    }

    public function getLanAttribute($value)
    {
        return number_format((float)$value, 5, '.', '');

    }

    public function offers()
    {
        return $this->hasMany(RequestOffer::class, 'request_id');
    }

    public function getTimeAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function comforts()
    {
        return $this->belongsToMany(Comfort::class, 'estate_request_comforts', 'estate_id');
    }

    public function getEstateComfortsAttribute()
    {
        return $this->comforts()->count() > 0 ? ComfortsResource::collection($this->comforts()->get()) : null;
    }

    public function getEstateUseNameAttribute()
    {

        if ($this->estate_use_type) {
            return @__('views.' . $this->estate_use_type);
        } else {
            return null;
        }

    }

}
