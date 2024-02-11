<?php

namespace App\Models\v2;

//use App\search\geo_search;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Laravel\Scout\Searchable;

class Estate extends Model
{

    use SoftDeletes;
   /* use Searchable;
    use geo_search;*/
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];
   /* public function toSearchableArray()
    {
        $record = $this->toArray();

        $record['_geoloc'] = [
            'lat' => $record['lat'],
            'lng' => $record['lan'],
        ];

        unset($record['created_at'], $record['updated_at']); // Remove unrelevant data
        unset($record['lat'], $record['lan']);

        return $record;
    }*/

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'operation_type_id',
        'estate_type_id',
        'instrument_number',
        'instrument_file',
        'pace_number',
        'planned_number',
        'total_area',
        'estate_age',
        'floor_number',
        'street_view',
        'total_price',
        'meter_price',
        'owner_name',
        'owner_mobile',
        'lounges_number',
        'bathrooms_number',
        'boards_number',
        'kitchen_number',
        'dining_rooms_number',
        'finishing_type',
        'interface',
        'social_status',
        'lat',
        'lan',
        'note',
        'status',
        'user_id',
        'is_rent',
        'rent_type',
        'is_resident',
        'is_checked',
        'is_insured',
        'exclusive_contract_file',
        'neighborhood_id',
        'city_id',
        'address',
        'exclusive_marketing',
        'in_fund_offer',
        'is_from_rent_system',
        'video',
        'state_id',
        'estate_use_type',
        'estate_dimensions',
        'is_mortgage',
        'is_obligations',
        'touching_information',
        'is_saudi_building_code',
        'elevators_number',
        'parking_spaces_numbers',
        'advertiser_side',
        'advertiser_character',
        'obligations_information',
    ];


    protected $casts = [
        'lat' => 'float',
        'lan' => 'float',
    ];

    protected $appends = [
        'link',
        'in_fav',
        'operation_type_name',
        'estate_type_name',
        'first_image',
        'city_name',
        'neighborhood_name',
        'estate_type_name_web',
        'city_name_web',
        'neighborhood_name_web',
        'interface_array',
        'estate_use_name',
        'advertiser_side_name',
        'advertiser_character_name',
        'state_name',
        'finishing_type_name',
        'comfort_names',
    ];
    protected $hidden = ['interface_array', 'estate_type', 'operation_type'];

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

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'serial_city');
    }
    public function status()
    {
        return $this->belongsTo(\App\Models\v3\Region::class, 'state_id', 'id');
    }
    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class, 'neighborhood_id', 'neighborhood_serial');
    }


    public function getInFavAttribute()
    {


        $fav = Favorite::where('type_id', $this->id)
            ->where('type', 'offer')
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


        return @$this->estate_type->name;
    }

    public function getEstateUseNameAttribute()
    {

        if ($this->estate_use_type) {
            return @__('views.' . $this->estate_use_type);
        } else {
    return null;
        }

    }

    public function getAdvertiserSideNameAttribute()
    {
        if ($this->advertiser_side) {
            return @__('views.' . $this->advertiser_side);
        } else {
            return null;
        }


    }

    public function getAdvertiserCharacterNameAttribute()
    {
        if ($this->advertiser_character) {
            return @__('views.' . $this->advertiser_character);
        } else {
            return null;
        }



    }
    public function getFinishingTypeNameAttribute()
    {
        if ($this->finishing_type) {
            return @__('views.' . $this->finishing_type);
        } else {
            return null;
        }



    }



    public function getEstateTypeNameWebAttribute()
    {


        return @$this->estate_type->name_ar;
    }

    public function getInstrumentFileAttribute()
    {

        if (isset($this->attributes['instrument_file'])) {
            return url((@$this->attributes['instrument_file']));
        } else {
            return null;
        }

    }


    public function getExclusiveContractFileAttribute($value)
    {
        if ($value) {
            return url($value);
        }
    }

    public function getFirstImageAttribute()
    {

        $img = AttachmentEstate::where('estate_id', $this->id)->first();
        if ($img) {
            return @$img->file;
        }
    }


    public function getComfortNamesAttribute()
    {

        $comfort = \App\Models\v3\ComfortEstate::with('comfort')->where('estate_id', $this->id)->get();
       $str='';
        if ($comfort) {
            foreach ($comfort as $comfortItem)
            {
                $str .=$comfortItem->comfort->name_ar.',';
            }
            return $str;
        }

        return null;
    }

    public function plannedFile()
    {
        return $this->hasMany(AttachmentPlanned::class, 'estate_id');
    }

    public function rates()
    {
        return $this->hasMany(RateEstate::class, 'estate_id');
    }

    public function EstateFile()
    {
        return $this->hasMany(AttachmentEstate::class, 'estate_id');
    }

    public function comforts()
    {
        return $this->belongsToMany(Comfort::class, 'estate_comforts', 'estate_id');
    }

    public function getNeighborhoodNameAttribute()
    {

        return @$this->neighborhood->name_ar;

    }


    public function getCityNameAttribute()
    {

        return @$this->city->name_ar;

    }


    public function getNeighborhoodNameWebAttribute()
    {

        return @$this->neighborhood->name_ar;

    }


    public function getCityNameWebAttribute()
    {

        return @$this->city->name_ar;

    }
    public function getStateNameAttribute()
    {

        return @$this->status->name_ar;


      //  return null;

    }

    public function getLinkAttribute()
    {

        $url = 'https://aqarz.sa/';
        return @$url . 'estate/' . $this->id . '/show';

    }

    public function getLatAttribute($value)
    {
        return number_format((float)$value, 5, '.', '');

    }

    public function getLanAttribute($value)
    {
        return number_format((float)$value, 5, '.', '');

    }


    public function getInterfaceArrayAttribute()
    {

        $array = explode(',', $this->interface);

        return @$array;

    }
}
